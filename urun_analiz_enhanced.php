<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['urun_kodu'])) {
    echo '<div class="alert alert-danger m-4">Ürün kodu belirtilmedi!</div>';
    exit;
}

$urun_kodu = $_GET['urun_kodu'];

// 1. Ürün Temel Bilgilerini Çek
$urun_query = "SELECT * FROM urunler WHERE urun_kodu = ?";
$stmt = $connection->prepare($urun_query);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$urun = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$urun) {
    echo '<div class="alert alert-danger m-4">Ürün bulunamadı!</div>';
    exit;
}

// 2. Veri Hazırlığı (Kokpit mantığıyla aynı)
$stok = floatval($urun['stok_miktari']);
$kritik = floatval($urun['kritik_stok_seviyesi']);

// Üretimdeki Miktar
$uretim_query = "SELECT SUM(planlanan_miktar) as toplam FROM montaj_is_emirleri WHERE urun_kodu = ? AND durum IN ('baslatildi', 'uretimde')";
$stmt = $connection->prepare($uretim_query);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$uretim_result = $stmt->get_result()->fetch_assoc();
$uretimde = floatval($uretim_result['toplam'] ?? 0);
$stmt->close();

$toplam_mevcut = $stok + $uretimde;

// Onaylanmış Siparişler
$siparis_query = "SELECT SUM(sk.adet) as toplam FROM siparis_kalemleri sk 
                  JOIN siparisler s ON sk.siparis_id = s.siparis_id 
                  WHERE sk.urun_kodu = ? AND s.durum = 'onaylandi'";
$stmt = $connection->prepare($siparis_query);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$siparis_result = $stmt->get_result()->fetch_assoc();
$siparis_miktari = floatval($siparis_result['toplam'] ?? 0);
$stmt->close();

// Toplam Hedef
$toplam_hedef = $siparis_miktari + $kritik;

// Açık (Net İhtiyaç) Hesabı
$siparis_icin_gereken = max(0, $siparis_miktari - $stok);
$stok_sonrasi = max(0, $stok - $siparis_miktari);
$uretimle_birlikte = $stok_sonrasi + $uretimde;

if ($kritik > 0) {
    $acik = max(0, $kritik - $uretimle_birlikte) + $siparis_icin_gereken;
} else {
    $acik = $siparis_icin_gereken;
}
$fazlalik = $acik <= 0 ? abs($acik) : 0;
if ($acik < 0) $acik = 0;

// 3. Bileşen Analizi (Ürün Ağacı) + YENİ: Maliyet Bilgileri
$bilesenler = [];
$uretilebilir_limit = PHP_INT_MAX;
$kritik_bilesen_turleri = ['kutu', 'takm', 'esans'];
$toplam_maliyet = 0;
$maliyet_dagilimi = [];

$bom_query = "SELECT ua.*, 
              COALESCE(m.stok_miktari, ur.stok_miktari, e.stok_miktari, 0) as stok,
              COALESCE(m.malzeme_ismi, ur.urun_ismi, e.esans_ismi, ua.bilesen_kodu) as isim,
              m.malzeme_kodu as malzeme_kodu_check
              FROM urun_agaci ua
              LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
              LEFT JOIN urunler ur ON ua.bilesen_kodu = ur.urun_kodu
              LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
              WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
$stmt = $connection->prepare($bom_query);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$bom_result = $stmt->get_result();

$esans_bilgileri = [];

while ($b = $bom_result->fetch_assoc()) {
    $gerekli = floatval($b['bilesen_miktari']);
    $mevcut_stok = floatval($b['stok']);
    $uretilebilir = ($gerekli > 0) ? floor($mevcut_stok / $gerekli) : 0;
    
    $tur = strtolower($b['bilesenin_malzeme_turu']);
    
    // YENİ: Tedarikçi ve Fiyat Bilgisi Çek
    $tedarikci = '-';
    $birim_fiyat = 0;
    $toplam_bilesen_maliyet = 0;
    
    if ($b['malzeme_kodu_check']) {
        $sozlesme_sql = "SELECT cs.tedarikci_adi, cs.birim_fiyati 
                         FROM cerceve_sozlesmeler_gecerlilik csg
                         JOIN cerceve_sozlesmeler cs ON csg.sozlesme_id = cs.sozlesme_id
                         WHERE csg.malzeme_kodu = ? AND csg.gecerli_mi = 1
                         LIMIT 1";
        $sozlesme_stmt = $connection->prepare($sozlesme_sql);
        $sozlesme_stmt->bind_param('s', $b['bilesen_kodu']);
        $sozlesme_stmt->execute();
        $sozlesme_res = $sozlesme_stmt->get_result()->fetch_assoc();
        
        if ($sozlesme_res) {
            $tedarikci = $sozlesme_res['tedarikci_adi'];
            $birim_fiyat = floatval($sozlesme_res['birim_fiyati']);
            $toplam_bilesen_maliyet = $gerekli * $birim_fiyat;
            $toplam_maliyet += $toplam_bilesen_maliyet;
            
            // Maliyet dağılımı için
            $maliyet_dagilimi[] = [
                'label' => $b['isim'],
                'value' => $toplam_bilesen_maliyet
            ];
        }
        $sozlesme_stmt->close();
    }
    
    $b['tedarikci'] = $tedarikci;
    $b['birim_fiyat'] = $birim_fiyat;
    $b['toplam_maliyet'] = $toplam_bilesen_maliyet;
    
    // Esans ise detayları çek
    if ($tur === 'esans') {
        $esans_id = $b['bilesen_kodu'];
        
        $e_emir_sql = "SELECT SUM(planlanan_miktar) as miktar FROM esans_is_emirleri WHERE esans_kodu = ? AND durum IN ('olusturuldu', 'uretimde')";
        $e_stmt = $connection->prepare($e_emir_sql);
        $e_stmt->bind_param('s', $esans_id);
        $e_stmt->execute();
        $e_res = $e_stmt->get_result()->fetch_assoc();
        $acik_esans_emri = floatval($e_res['miktar'] ?? 0);
        $e_stmt->close();
        
        $esans_bilgileri[] = [
            'kod' => $esans_id,
            'isim' => $b['isim'],
            'acik_emir' => $acik_esans_emri
        ];
    }
    
    if ($uretilebilir < $uretilebilir_limit) {
        $uretilebilir_limit = $uretilebilir;
    }
    
    $b['uretilebilir_adet'] = $uretilebilir;
    $bilesenler[] = $b;
}
$stmt->close();

if (empty($bilesenler)) $uretilebilir_limit = 0;

// Birim maliyet hesaplama
$birim_maliyet = $toplam_maliyet;
$onerilen_uretim_maliyeti = $toplam_maliyet * max(1, $acik);

// 4. Üretim Geçmişi
$uretim_gecmisi = [];
$gecmis_sql = "SELECT is_emri_numarasi, planlanan_miktar, durum, olusturma_tarihi, tamamlanma_tarihi 
               FROM montaj_is_emirleri 
               WHERE urun_kodu = ? 
               ORDER BY olusturma_tarihi DESC 
               LIMIT 10";
$stmt = $connection->prepare($gecmis_sql);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$gecmis_result = $stmt->get_result();
while ($row = $gecmis_result->fetch_assoc()) {
    $uretim_gecmisi[] = $row;
}
$stmt->close();

// 5. Stok Hareket Geçmişi (Son 15 işlem)
$stok_hareketleri = [];
$hareket_sql = "SELECT hareket_turu, miktar, hareket_tarihi, aciklama 
                FROM stok_hareketi 
                WHERE urun_kodu = ? 
                ORDER BY hareket_tarihi DESC 
                LIMIT 15";
$stmt = $connection->prepare($hareket_sql);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$hareket_result = $stmt->get_result();
while ($row = $hareket_result->fetch_assoc()) {
    $stok_hareketleri[] = $row;
}
$stmt->close();

// 6. Kokpit Özeti İçin Ek Kontroller
$required_types = ['kutu', 'takm', 'etiket', 'paket', 'jelatin', 'esans'];
$existing_types = [];
$sozlesme_eksik_malzemeler = [];
$sozlesme_eksik_kodlar = [];
$esans_agaci_eksik = [];

// Mevcut tipleri ve sözleşmeleri kontrol et
foreach ($bilesenler as $b) {
    $type = strtolower($b['bilesenin_malzeme_turu']);
    if (!in_array($type, $existing_types)) {
        $existing_types[] = $type;
    }

    $malzeme_kodu = $b['bilesen_kodu'];
    $sozlesme_sql = "SELECT COUNT(*) as cnt FROM cerceve_sozlesmeler_gecerlilik WHERE malzeme_kodu = ? AND gecerli_mi = 1";
    $s_stmt = $connection->prepare($sozlesme_sql);
    $s_stmt->bind_param('s', $malzeme_kodu);
    $s_stmt->execute();
    $s_res = $s_stmt->get_result()->fetch_assoc();
    if ($s_res['cnt'] == 0) {
        if (!in_array($type, ['esans', 'yari mamul'])) {
             $sozlesme_eksik_malzemeler[] = $b['isim'];
             $sozlesme_eksik_kodlar[] = $b['bilesen_kodu'];
        }
    }
    $s_stmt->close();
}

// Eksik Tipler
$eksik_bilesenler = [];
foreach ($required_types as $type) {
    if (!in_array($type, $existing_types)) {
        switch ($type) {
            case 'kutu': $eksik_bilesenler[] = 'Kutu'; break;
            case 'takm': $eksik_bilesenler[] = 'Takım'; break;
            case 'etiket': $eksik_bilesenler[] = 'Etiket'; break;
            case 'paket': $eksik_bilesenler[] = 'Paket'; break;
            case 'jelatin': $eksik_bilesenler[] = 'Jelatin'; break;
            case 'esans': $eksik_bilesenler[] = 'Esans'; break;
        }
    }
}

// Esans Reçete Kontrolü
foreach ($esans_bilgileri as $eb) {
    $esans_adi_sql = "SELECT esans_ismi FROM esanslar WHERE esans_kodu = ?";
    $ea_stmt = $connection->prepare($esans_adi_sql);
    $ea_stmt->bind_param('s', $eb['kod']);
    $ea_stmt->execute();
    $ea_res = $ea_stmt->get_result()->fetch_assoc();
    $esans_ismi_gercek = $ea_res ? $ea_res['esans_ismi'] : null;
    $ea_stmt->close();

    if ($esans_ismi_gercek) {
        $recete_sql = "SELECT COUNT(*) as cnt FROM urun_agaci WHERE agac_turu = 'esans' AND urun_ismi = ?";
        $r_stmt = $connection->prepare($recete_sql);
        $r_stmt->bind_param('s', $esans_ismi_gercek);
        $r_stmt->execute();
        $r_res = $r_stmt->get_result()->fetch_assoc();
        if ($r_res['cnt'] == 0) {
            $esans_agaci_eksik[] = $esans_ismi_gercek;
        }
        $r_stmt->close();
    }
}

// Önerilen Üretim
$onerilen = ($acik > 0) ? min($acik, $uretilebilir_limit) : 0;

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($urun['urun_ismi']); ?> - Detaylı Analiz | İDO KOZMETİK</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Navbar Styles */
        .navbar {
            background: linear-gradient(45deg, #4a0e63, #7c2a99);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        
        .bg-purple { background-color: #6f42c1; color: white; }
        .text-purple { color: #6f42c1; }
        .bg-gradient-purple {
            background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }
        
        /* Stats Cards */
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #6f42c1;
            margin-bottom: 1rem;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #6f42c1;
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        /* Table Enhancements */
        .table-enhanced {
            font-size: 0.9rem;
        }
        .table-enhanced th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .table-enhanced td {
            vertical-align: middle;
        }
        
        /* Progress Bar Custom */
        .progress-custom {
            height: 10px;
            border-radius: 10px;
            background: #e9ecef;
        }
        .progress-bar-custom {
            border-radius: 10px;
        }
        
        /* Badge Styles */
        .badge-cost {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .section-header i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: #6f42c1;
        }
        .section-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6f42c1;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #dee2e6;
        }
        
        /* Quick Actions */
        .quick-action-btn {
            width: 100%;
            margin-bottom: 10px;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1rem 0;
            }
            .stat-card .stat-value {
                font-size: 1.5rem;
            }
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="kokpit.php"><i class="fas fa-arrow-left mr-2"></i> Tedarik Zinciri Kokpiti</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white mr-3"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Header -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <a href="kokpit.php" class="btn btn-outline-light btn-sm mb-2"><i class="fas fa-arrow-left"></i> Geri Dön</a>
                    <h1 class="display-4 font-weight-bold mb-0"><?php echo htmlspecialchars($urun['urun_ismi']); ?></h1>
                    <p class="lead opacity-75">Ürün Kodu: <?php echo htmlspecialchars($urun['urun_kodu']); ?> | Birim: <?php echo $urun['birim']; ?></p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="bg-white text-purple p-3 rounded-lg shadow">
                        <small class="d-block text-uppercase font-weight-bold">Önerilen Üretim</small>
                        <span class="display-4 font-weight-bold"><?php echo number_format($onerilen, 0, ',', '.'); ?></span>
                        <small>Adet</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <!-- ADIM 1: Veri Bütünlüğü Kontrolü -->
        <?php if (!empty($eksik_bilesenler) || !empty($esans_agaci_eksik)): ?>
            <div class="card shadow border-0 mb-4 bg-white" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="nr-auto p-3 bg-danger-light rounded-circle text-danger mr-4">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                        <div class="w-100">
                            <h4 class="text-danger font-weight-bold mb-2">Dikkat: Eksik Veriyle Analiz Yapılıyor</h4>
                            <p class="text-muted mb-3">Bu ürünün sağlıklı analiz edilebilmesi için aşağıdaki eksik verilerin tamamlanması önerilir.</p>
                            
                            <?php if (!empty($eksik_bilesenler)): ?>
                                <div class="alert alert-danger mb-2">
                                    <h6 class="alert-heading font-weight-bold"><i class="fas fa-sitemap mr-1"></i> Ürün Ağacı Eksikleri</h6>
                                    <p class="mb-0 small">Şu bileşen türleri ürün ağacında tanımlanmamış: <strong><?php echo implode(', ', $eksik_bilesenler); ?></strong></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($esans_agaci_eksik)): ?>
                                <div class="alert alert-danger mb-0">
                                    <h6 class="alert-heading font-weight-bold"><i class="fas fa-flask mr-1"></i> Esans Reçetesi Eksik</h6>
                                    <p class="mb-0 small">Şu esansların kendi üretim reçeteleri (BOM) sisteme girilmemiş: <strong><?php echo implode(', ', $esans_agaci_eksik); ?></strong></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ADIM 2: Sözleşme Kontrolü -->
        <?php if (!empty($sozlesme_eksik_malzemeler)): ?>
        <div class="alert alert-warning shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div style="font-size: 2rem;" class="mr-3"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <h5 class="alert-heading font-weight-bold mb-1">Dikkat: Çerçeve Sözleşme Eksik</h5>
                    <p class="mb-0">
                        Analiz yapılıyor ancak bazı bileşenlerin tedarik sözleşmesi bulunmuyor: 
                        <strong><?php echo implode(', ', array_slice($sozlesme_eksik_malzemeler, 0, 5)); ?><?php if(count($sozlesme_eksik_malzemeler)>5) echo '...'; ?></strong>.
                        Maliyet hesaplamaları eksik olabilir.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($sozlesme_eksik_malzemeler)): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 py-2">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle mr-2 text-success"></i>
                <div>
                    <span class="font-weight-bold">Sözleşme Kontrolü:</span> Tüm bileşenlerin tedarik sözleşmesi mevcut.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="row mb-4">
            <!-- Mevcut Varlıklar -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="fas fa-warehouse"></i> Depo Stok</div>
                    <div class="stat-value"><?php echo number_format($stok, 0, ',', '.'); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="fas fa-cog fa-spin"></i> Üretimde</div>
                    <div class="stat-value text-info"><?php echo number_format($uretimde, 0, ',', '.'); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="fas fa-shopping-cart"></i> Sipariş</div>
                    <div class="stat-value text-warning"><?php echo number_format($siparis_miktari, 0, ',', '.'); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Birim Maliyet</div>
                    <div class="stat-value text-success" style="font-size: 1.3rem;">₺<?php echo number_format($birim_maliyet, 2, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Left Column: Charts and Analysis -->
            <div class="col-lg-8">
                
                <!-- Stok vs İhtiyaç Grafiği -->
                <div class="card">
                    <div class="card-body">
                        <div class="section-header">
                            <i class="fas fa-chart-bar"></i>
                            <h5>Stok Durumu ve İhtiyaç Analizi</h5>
                        </div>
                        <div class="chart-container">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Maliyet Dağılımı -->
                <?php if (!empty($maliyet_dagilimi)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="section-header">
                            <i class="fas fa-chart-pie"></i>
                            <h5>Maliyet Dağılımı</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="costChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Toplam Maliyet Özeti</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Birim Maliyet:</td>
                                        <td class="text-right font-weight-bold">₺<?php echo number_format($birim_maliyet, 2, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Önerilen Üretim (<?php echo number_format($onerilen, 0, ',', '.'); ?> adet):</td>
                                        <td class="text-right font-weight-bold text-danger">₺<?php echo number_format($onerilen_uretim_maliyeti, 2, ',', '.'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Detaylı Bileşen Tablosu -->
                <div class="card">
                    <div class="card-body">
                        <div class="section-header">
                            <i class="fas fa-layer-group"></i>
                            <h5>Detaylı Bileşen Analizi</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-enhanced table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Bileşen</th>
                                        <th>Tür</th>
                                        <th>Tedarikçi</th>
                                        <th class="text-right">Birim Fiyat</th>
                                        <th class="text-center">Gerekli</th>
                                        <th class="text-right">Toplam Maliyet</th>
                                        <th class="text-center">Stok Durumu</th>
                                        <th class="text-center">Üretilebilir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bilesenler as $b): 
                                        $yetersiz = $b['uretilebilir_adet'] < $acik;
                                        $stok_yuzde = ($b['bilesen_miktari'] > 0) ? min(100, ($b['stok'] / $b['bilesen_miktari']) * 100) : 100;
                                        $stok_color = $stok_yuzde >= 100 ? 'success' : ($stok_yuzde >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($b['isim']); ?></strong></td>
                                        <td><span class="badge badge-light border"><?php echo strtoupper($b['bilesenin_malzeme_turu']); ?></span></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($b['tedarikci']); ?></small></td>
                                        <td class="text-right"><small>₺<?php echo number_format($b['birim_fiyat'], 2, ',', '.'); ?></small></td>
                                        <td class="text-center"><?php echo number_format($b['bilesen_miktari'], 2, ',', '.'); ?></td>
                                        <td class="text-right font-weight-bold">₺<?php echo number_format($b['toplam_maliyet'], 2, ',', '.'); ?></td>
                                        <td>
                                            <div class="progress progress-custom">
                                                <div class="progress-bar progress-bar-custom bg-<?php echo $stok_color; ?>" style="width: <?php echo $stok_yuzde; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo number_format($b['stok'], 2, ',', '.'); ?> / <?php echo number_format($b['bilesen_miktari'], 2, ',', '.'); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?php echo $b['uretilebilir_adet'] == 0 ? 'danger' : ($yetersiz ? 'warning' : 'success'); ?>">
                                                <?php echo number_format($b['uretilebilir_adet'], 0, ',', '.'); ?> adet
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Üretim Geçmişi -->
                <?php if (!empty($uretim_gecmisi)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="section-header">
                            <i class="fas fa-history"></i>
                            <h5>Son Üretim İşlemleri</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>İş Emri No</th>
                                        <th class="text-center">Miktar</th>
                                        <th class="text-center">Durum</th>
                                        <th>Oluşturma</th>
                                        <th>Tamamlanma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uretim_gecmisi as $uretim): ?>
                                    <tr>
                                        <td><small class="font-weight-bold"><?php echo htmlspecialchars($uretim['is_emri_numarasi']); ?></small></td>
                                        <td class="text-center"><?php echo number_format($uretim['planlanan_miktar'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-<?php 
                                                echo $uretim['durum'] == 'tamamlandi' ? 'success' : 
                                                    ($uretim['durum'] == 'uretimde' ? 'info' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($uretim['durum']); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('d.m.Y H:i', strtotime($uretim['olusturma_tarihi'])); ?></small></td>
                                        <td><small><?php echo $uretim['tamamlanma_tarihi'] ? date('d.m.Y H:i', strtotime($uretim['tamamlanma_tarihi'])) : '-'; ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Summary and Actions -->
            <div class="col-lg-4">
                
                <!-- Sonuç Kartı -->
                <div class="card bg-white shadow-lg sticky-top" style="top: 20px;">
                    <div class="card-body text-center p-4">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3"><i class="fas fa-clipboard-check"></i> Sonuç ve Öneri</h6>
                        
                        <?php if ($onerilen > 0): ?>
                            <div class="display-3 font-weight-bold text-primary mb-2"><?php echo number_format($onerilen, 0, ',', '.'); ?></div>
                            <div class="text-muted mb-3">ADET ÜRETİLMELİ</div>
                            
                            <hr>
                            
                            <div class="text-left mb-3">
                                <p class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?php echo number_format($acik, 0, ',', '.'); ?></strong> adet ihtiyacınız var.</p>
                                <p class="mb-2"><i class="fas fa-check text-success mr-2"></i> Malzemeleriniz <strong><?php echo number_format($uretilebilir_limit, 0, ',', '.'); ?></strong> adet üretime müsade ediyor.</p>
                                <p class="mb-0"><i class="fas fa-money-bill-wave text-info mr-2"></i> Tahmini maliyet: <strong class="text-success">₺<?php echo number_format($onerilen_uretim_maliyeti, 2, ',', '.'); ?></strong></p>
                            </div>
                            
                            <button class="quick-action-btn btn btn-primary" onclick="openWorkOrderModal()">
                                <i class="fas fa-hammer mr-2"></i> İş Emri Oluştur
                            </button>
                        <?php else: ?>
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-5x"></i>
                            </div>
                            <h4 class="font-weight-bold text-success">İşlem Gerekmiyor</h4>
                            <p class="text-muted">Stoklarınız yeterli veya üretim için gerekli malzeme yok.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hızlı Eylemler -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-bolt"></i> Hızlı Eylemler</h6>
                        <button class="quick-action-btn btn btn-outline-info" onclick="exportPDF()">
                            <i class="fas fa-file-pdf mr-2"></i> PDF Rapor İndir
                        </button>
                        <button class="quick-action-btn btn btn-outline-success" onclick="exportExcel()">
                            <i class="fas fa-file-excel mr-2"></i> Excel'e Aktar
                        </button>
                        <a href="kokpit.php" class="quick-action-btn btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Kokpite Dön
                        </a>
                    </div>
                </div>

                <!-- Stok Hareket Geçmişi -->
                <?php if (!empty($stok_hareketleri)): ?>
                <div class="card">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-exchange-alt"></i> Son Stok Hareketleri</h6>
                        <div class="timeline">
                            <?php foreach (array_slice($stok_hareketleri, 0, 5) as $hareket): ?>
                            <div class="timeline-item">
                                <small class="text-muted d-block"><?php echo date('d.m.Y H:i', strtotime($hareket['hareket_tarihi'])); ?></small>
                                <strong class="<?php echo $hareket['hareket_turu'] == 'giris' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $hareket['hareket_turu'] == 'giris' ? '+' : '-'; ?><?php echo number_format($hareket['miktar'], 0, ',', '.'); ?>
                                </strong>
                                <small class="text-muted ml-2"><?php echo htmlspecialchars($hareket['aciklama'] ?? '-'); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <!-- Work Order Modal -->
    <div class="modal fade" id="workOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-purple text-white">
                    <h5 class="modal-title"><i class="fas fa-hammer"></i> Yeni İş Emri Oluştur</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="workOrderForm">
                        <div class="form-group">
                            <label>Ürün</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($urun['urun_ismi']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Üretim Miktarı</label>
                            <input type="number" class="form-control" id="workOrderAmount" value="<?php echo $onerilen; ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label>Notlar (Opsiyonel)</label>
                            <textarea class="form-control" rows="3" id="workOrderNotes"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="submitWorkOrder()">
                        <i class="fas fa-check"></i> Onayla ve Oluştur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        // Stok Durumu Grafiği
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: ['Mevcut Durum', 'Hedef'],
                datasets: [{
                    label: 'Depo Stok',
                    data: [<?php echo $stok; ?>, 0],
                    backgroundColor: 'rgba(111, 66, 193, 0.8)',
                    borderColor: 'rgba(111, 66, 193, 1)',
                    borderWidth: 1
                }, {
                    label: 'Üretimde',
                    data: [<?php echo $uretimde; ?>, 0],
                    backgroundColor: 'rgba(23, 162, 184, 0.8)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 1
                }, {
                    label: 'Sipariş',
                    data: [0, <?php echo $siparis_miktari; ?>],
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }, {
                    label: 'Kritik Stok',
                    data: [0, <?php echo $kritik; ?>],
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true
                    },
                    x: {
                        stacked: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Toplam Mevcut: <?php echo number_format($toplam_mevcut, 0); ?> | Toplam Hedef: <?php echo number_format($toplam_hedef, 0); ?>'
                    }
                }
            }
        });

        <?php if (!empty($maliyet_dagilimi)): ?>
        // Maliyet Dağılımı Grafiği
        const costCtx = document.getElementById('costChart').getContext('2d');
        const costChart = new Chart(costCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($maliyet_dagilimi, 'label')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($maliyet_dagilimi, 'value')); ?>,
                    backgroundColor: [
                        'rgba(111, 66, 193, 0.8)',
                        'rgba(245, 87, 108, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₺' + context.parsed.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Modal Functions
        function openWorkOrderModal() {
            $('#workOrderModal').modal('show');
        }

        function submitWorkOrder() {
            const amount = document.getElementById('workOrderAmount').value;
            const notes = document.getElementById('workOrderNotes').value;
            
            // Burada AJAX ile iş emri oluşturma işlemi yapılabilir
            alert('İş emri oluşturma özelliği yakında eklenecek!\nMiktar: ' + amount + '\nNot: ' + notes);
            $('#workOrderModal').modal('hide');
        }

        // Export Functions
        function exportPDF() {
            alert('PDF export özelliği yakında eklenecek!');
        }

        function exportExcel() {
            alert('Excel export özelliği yakında eklenecek!');
        }
    </script>
</body>
</html>
