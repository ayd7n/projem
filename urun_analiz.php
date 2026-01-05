<?php
// Page disabled per project request - redirect to dashboard
header('Location: kokpit.php');
exit;

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
// - Sipariş için gereken: mevcut stoktan karşılanamayan miktar
// - Toplam açık: hem kritik seviyeyi hem de siparişi karşılayacak miktar
$siparis_icin_gereken = max(0, $siparis_miktari - $stok);
$stok_sonrasi = max(0, $stok - $siparis_miktari);
$uretimle_birlikte = $stok_sonrasi + $uretimde;

if ($kritik > 0) {
    $acik = max(0, $kritik - $uretimle_birlikte) + $siparis_icin_gereken;
} else {
    $acik = $siparis_icin_gereken;
}
$fazlalik = $acik <= 0 ? abs($acik) : 0;
// Eğer açık negatif çıkarsa (yani fazlalık varsa) açık'ı 0 yap, fazlalık zaten hesaplandı
if ($acik < 0) $acik = 0;


// 3. Bileşen Analizi (Ürün Ağacı)
$bilesenler = [];
$uretilebilir_limit = PHP_INT_MAX;
$kritik_bilesen_turleri = ['kutu', 'takm', 'esans'];
$bilesen_gruplari = []; // Türlerine göre grupla

$bom_query = "SELECT ua.*, 
              COALESCE(m.stok_miktari, ur.stok_miktari, e.stok_miktari, 0) as stok,
              COALESCE(m.malzeme_ismi, ur.urun_ismi, e.esans_ismi, ua.bilesen_kodu) as isim
              FROM urun_agaci ua
              LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
              LEFT JOIN urunler ur ON ua.bilesen_kodu = ur.urun_kodu
              LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
              WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
$stmt = $connection->prepare($bom_query);
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$bom_result = $stmt->get_result();

$esans_bilgileri = []; // Esans detayları için

while ($b = $bom_result->fetch_assoc()) {
    $gerekli = floatval($b['bilesen_miktari']);
    $mevcut_stok = floatval($b['stok']);
    $uretilebilir = ($gerekli > 0) ? floor($mevcut_stok / $gerekli) : 0;
    
    $tur = strtolower($b['bilesenin_malzeme_turu']);
    
    // Esans ise detayları çek
    if ($tur === 'esans') {
        $esans_id = $b['bilesen_kodu'];
        
        // Açık esans emirlerini bul
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
    
    // Limiti güncelle
    if ($uretilebilir < $uretilebilir_limit) {
        $uretilebilir_limit = $uretilebilir;
    }
    
    $b['uretilebilir_adet'] = $uretilebilir;
    $bilesenler[] = $b;
}
$stmt->close();

if (empty($bilesenler)) $uretilebilir_limit = 0;

// 4. Kokpit Özeti İçin Ek Kontroller
$required_types = ['kutu', 'takm', 'etiket', 'paket', 'jelatin', 'esans'];
$existing_types = [];
$sozlesme_eksik_malzemeler = [];
$sozlesme_eksik_kodlar = [];
$esans_agaci_eksik = [];

// Mevcut tipleri ve sözleşmeleri kontrol et
foreach ($bilesenler as $b) {
    // Tip kontrolü
    $type = strtolower($b['bilesenin_malzeme_turu']);
    if (!in_array($type, $existing_types)) {
        $existing_types[] = $type;
    }

    // Sözleşme kontrolü
    $malzeme_kodu = $b['bilesen_kodu'];
    // Malzeme tablosundan mı geliyor? (Genelde evet, ama ürün veya esans da olabilir)
    // Basitlik için sadece malzemeler tablosunda varsa kontrol edelim, yoksa es geçelim (veya ürün/esans ise sözleşme aranmaz varsayalım)
    
    // Malzeme mi diye koddan anlamak zor, join ile kontrol edelim.
    // Ancak performans için basit bir query yapalım bu malzeme için
    $sozlesme_sql = "SELECT COUNT(*) as cnt FROM cerceve_sozlesmeler_gecerlilik WHERE malzeme_kodu = ? AND gecerli_mi = 1";
    $s_stmt = $connection->prepare($sozlesme_sql);
    $s_stmt->bind_param('s', $malzeme_kodu);
    $s_stmt->execute();
    $s_res = $s_stmt->get_result()->fetch_assoc();
    if ($s_res['cnt'] == 0) {
        // Esans veya Yarı Mamül (Ürün) değilse sözleşme eksik sayalım.
        // Bunu anlamak için türüne bakalım.
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

// Esans Reçete Kontrolü (Zaten $esans_bilgileri var, onu kullanalım ama reçete var mı bakmamıştık)
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
    <title><?php echo htmlspecialchars($urun['urun_ismi']); ?> - Analiz | İDO KOZMETİK</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
        }
        /* Navbar Styles from kokpit.php */
        .navbar {
            background: linear-gradient(45deg, #4a0e63, #7c2a99);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* Page Specific Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .bg-purple { background-color: #6f42c1; color: white; }
        .text-purple { color: #6f42c1; }
        .hero-section {
            background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            margin-top: 0; /* Reset margin */
        }
        .big-number { font-size: 2.5rem; font-weight: 700; }
        .equation-box {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            font-size: 1.2rem;
            color: #555;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .status-badge {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 50px;
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="kokpit.php" class="btn btn-outline-light btn-sm mb-2"><i class="fas fa-arrow-left"></i> Geri Dön</a>
                    <h1 class="display-4 font-weight-bold mb-0"><?php echo htmlspecialchars($urun['urun_ismi']); ?></h1>
                    <p class="lead opacity-75">Ürün Kodu: <?php echo htmlspecialchars($urun['urun_kodu']); ?> | Birim: <?php echo $urun['birim']; ?></p>
                </div>
                <div class="text-right">
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
        
        <!-- ADIM 1: Analiz Ön Kontrolü (Veri Bütünlüğü) -->
        <?php if (!empty($eksik_bilesenler) || !empty($esans_agaci_eksik)): ?>
            <div class="card shadow border-0 mb-4 bg-white" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="nr-auto p-3 bg-danger-light rounded-circle text-danger mr-4">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                        <div class="w-100">
                            <h4 class="text-danger font-weight-bold mb-2">Dikkat: Eksik Veriyle Analiz Yapılıyor</h4>
                            <p class="text-muted mb-3">Bu ürünün sağlıklı analiz edilebilmesi için aşağıdaki eksik verilerin tamamlanması önerilir. Analiz mevcut verilerle devam etmektedir.</p>
                            
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
                            
                            <hr>
                            <a href="urunler.php?search=<?php echo urlencode($urun['urun_ismi']); ?>" class="btn btn-danger btn-sm"><i class="fas fa-edit"></i> Ürün Düzenlemeye Git</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
            <!-- Veri Sorunu Olsa Bile Analiz Devam Eder -->

            <!-- ADIM 2: Sözleşme Kontrolü (Uyarı) -->
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

        <!-- Üst İstatistikler -->
        <div class="row mb-4">
            <!-- Varlıklar -->
            <div class="col-md-6">
                <div class="card h-100 border-left-primary" style="border-left: 5px solid #17a2b8;">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="fas fa-boxes"></i> Mevcut Varlıklar</h5>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="d-block text-muted">Depo Stoğu</span>
                                <span class="h4"><?php echo number_format($stok, 0, ',', '.'); ?></span>
                            </div>
                            <div class="text-center text-muted h4">+</div>
                            <div>
                                <span class="d-block text-muted">Üretimdeki</span>
                                <span class="h4"><?php echo number_format($uretimde, 0, ',', '.'); ?></span>
                            </div>
                            <div class="text-center text-info h4">=</div>
                            <div class="text-right">
                                <span class="d-block text-info font-weight-bold">TOPLAM MEVCUT</span>
                                <span class="h3 text-info"><?php echo number_format($toplam_mevcut, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hedefler -->
            <div class="col-md-6">
                <div class="card h-100 border-left-danger" style="border-left: 5px solid #dc3545;">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><i class="fas fa-bullseye"></i> Hedefler</h5>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="d-block text-muted">Sipariş Emri</span>
                                <span class="h4"><?php echo number_format($siparis_miktari, 0, ',', '.'); ?></span>
                            </div>
                            <div class="text-center text-muted h4">+</div>
                            <div>
                                <span class="d-block text-muted">Kritik Stok</span>
                                <span class="h4"><?php echo number_format($kritik, 0, ',', '.'); ?></span>
                            </div>
                            <div class="text-center text-danger h4">=</div>
                            <div class="text-right">
                                <span class="d-block text-danger font-weight-bold">TOPLAM HEDEF</span>
                                <span class="h3 text-danger"><?php echo number_format($toplam_hedef, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matematiksel Denklem -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="equation-box">
                    <h5 class="text-muted text-uppercase mb-3" style="font-size: 0.9rem; letter-spacing: 2px;">Net İhtiyaç Hesaplaması</h5>
                    <div class="d-flex justify-content-center align-items-center flex-wrap">
                        <div class="px-3">
                            <span class="d-block text-danger font-weight-bold display-4"><?php echo number_format($toplam_hedef, 0, ',', '.'); ?></span>
                            <small class="text-muted">HEDEF</small>
                        </div>
                        <div class="px-2 display-4 text-muted">-</div>
                        <div class="px-3">
                            <span class="d-block text-info font-weight-bold display-4"><?php echo number_format($toplam_mevcut, 0, ',', '.'); ?></span>
                            <small class="text-muted">MEVCUT</small>
                        </div>
                        <div class="px-2 display-4 text-muted">=</div>
                        <div class="px-3">
                            <?php if ($acik > 0): ?>
                                <span class="d-block text-danger font-weight-bold display-4"><?php echo number_format($acik, 0, ',', '.'); ?></span>
                                <span class="badge badge-danger px-3 py-2 mt-2">AÇIK (İHTİYAÇ)</span>
                            <?php else: ?>
                                <span class="d-block text-success font-weight-bold display-4"><?php echo number_format($fazlalik, 0, ',', '.'); ?></span>
                                <span class="badge badge-success px-3 py-2 mt-2">FAZLALIK (YETERLİ)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alt Kısım: Bileşenler ve Sonuç -->
        <div class="row">
            <!-- Bileşen Tablosu -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-layer-group text-primary mr-2"></i> Ürün Ağacı ve Stok Yeterliliği</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Bileşen</th>
                                    <th>Tür</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Birim İhtiyaç</th>
                                    <th class="text-center">Üretilebilir Kapasite</th>
                                    <th class="text-center">Sipariş İhtiyacı</th>
                                    <th class="text-center">Sözleşme</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bilesenler as $b): 
                                    $yetersiz = $b['uretilebilir_adet'] < $acik;
                                    $sinirlayici = $b['uretilebilir_adet'] == $uretilebilir_limit;
                                ?>
                                <tr class="<?php echo ($sinirlayici && $acik > 0) ? 'table-warning font-weight-bold' : ''; ?>">
                                    <td><?php echo htmlspecialchars($b['isim']); ?></td>
                                    <td><span class="badge badge-light border"><?php echo strtoupper($b['bilesenin_malzeme_turu']); ?></span></td>
                                    <td class="text-center"><?php echo number_format($b['stok'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?php echo number_format($b['bilesen_miktari'], 2, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <span class="h5"><?php echo number_format($b['uretilebilir_adet'], 0, ',', '.'); ?></span> <small>Ürünlük</small>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            // Sipariş İhtiyacı Hesabı
                                            if ($acik > 0) {
                                                $gereken_toplam_malzeme = $acik * $b['bilesen_miktari'];
                                                $eksik_malzeme = max(0, $gereken_toplam_malzeme - $b['stok']);
                                                
                                                if ($eksik_malzeme > 0) {
                                                    echo '<span class="badge badge-danger p-2 shadow-sm" style="font-size: 0.9rem;">';
                                                    echo '<i class="fas fa-shopping-cart mr-1"></i> ' . number_format(ceil($eksik_malzeme), 0, ',', '.') . ' Sipariş Ver';
                                                    echo '</span>';
                                                    echo '<div class="small text-muted mt-1">(' . number_format($gereken_toplam_malzeme, 0, ',', '.') . ' gerekli)</div>';
                                                } else {
                                                    echo '<span class="text-muted"><i class="fas fa-check text-success"></i> Stok Yeterli</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (in_array($b['bilesen_kodu'], $sozlesme_eksik_kodlar)): ?>
                                            <span class="badge badge-warning" title="Çerçeve Sözleşme Yok">
                                                <i class="fas fa-exclamation-triangle"></i> Yok
                                            </span>
                                        <?php elseif (in_array(strtolower($b['bilesenin_malzeme_turu']), ['esans', 'yari mamul'])): ?>
                                            <span class="text-muted small">-</span>
                                        <?php else: ?>
                                            <span class="text-success" title="Sözleşme Mevcut">
                                                <i class="fas fa-check-circle"></i> Var
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($b['uretilebilir_adet'] == 0): ?>
                                            <span class="badge badge-danger">TÜKENDİ</span>
                                        <?php elseif ($yetersiz && $acik > 0): ?>
                                            <span class="badge badge-warning">YETERSİZ</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">YETERLİ</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($sinirlayici && $acik > 0): ?>
                                            <div class="small text-danger mt-1"><i class="fas fa-exclamation-circle"></i> Kısıtlayan</div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bilesenler)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Bu ürün için bileşen tanımlanmamış.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light text-center">
                         Mevcut stoklarla en fazla <strong class="text-primary h5"><?php echo number_format($uretilebilir_limit, 0, ',', '.'); ?></strong> adet ürün üretilebilir.
                    </div>
                </div>

                <!-- Esans Özel Durumu -->
                <?php if (!empty($esans_bilgileri)): ?>
                <div class="card shadow-sm border-purple mb-4">
                    <div class="card-header bg-purple text-white py-3">
                        <h5 class="m-0"><i class="fas fa-flask mr-2"></i> Esans Durumu</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($esans_bilgileri as $esans): 
                             // Net Esans İhtiyacı Hesabı (Main Page Logic Mirror)
                             $toplam_gereken_esans = ceil($onerilen * ($esans['isim']  ? 1 : 1)); // Basitleştirilmiş, aslında reçeteden gelmeli ama burada satır bazlı gidiyoruz
                             // Not: Reçete miktarını yukarıda $bilesenler döngüsünde alabilirdik ama basit tutuyorum.
                             // Main logic'tekine sadık kalalım:
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <h6 class="font-weight-bold mb-0"><?php echo htmlspecialchars($esans['isim']); ?></h6>
                                <small class="text-muted">Kod: <?php echo $esans['kod']; ?></small>
                            </div>
                            <div class="text-right">
                                <?php if ($esans['acik_emir'] > 0): ?>
                                    <span class="badge badge-success p-2"><i class="fas fa-check mr-1"></i> <?php echo number_format($esans['acik_emir'], 0, ',', '.'); ?> Adet Açık Emir Var</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary p-2">Açık İş Emri Yok</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="alert alert-info mb-0 mt-3">
                            <i class="fas fa-info-circle"></i> Eğer önerilen üretim miktarı için esans yetersizse ve açık iş emri yoksa, sistem yeni esans üretim emri açmanızı önerecektir.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sağ Panel: Sonuç Kartı -->
            <div class="col-lg-4">
                <div class="card bg-white shadow-lg sticky-top" style="top: 20px;">
                    <div class="card-body text-center p-5">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-4">Sonuç ve Öneri</h6>
                        
                        <?php if ($onerilen > 0): ?>
                            <div class="display-3 font-weight-bold text-primary mb-2"><?php echo number_format($onerilen, 0, ',', '.'); ?></div>
                            <div class="text-muted mb-4">ADET ÜRETİLMELİ</div>
                            
                            <hr>
                            
                            <p class="text-left mb-4">
                                <i class="fas fa-check text-success mr-2"></i> <strong><?php echo number_format($acik, 0, ',', '.'); ?></strong> adet ihtiyacınız var.<br>
                                <i class="fas fa-check text-success mr-2"></i> Malzemeleriniz <strong><?php echo number_format($uretilebilir_limit, 0, ',', '.'); ?></strong> adet üretime müsade ediyor.
                            </p>
                            
                            <a href="isemirleri.php?action=yeni&urun_kodu=<?php echo $urun_kodu; ?>&miktar=<?php echo $onerilen; ?>" class="btn btn-primary btn-block btn-lg rounded-pill shadow">
                                <i class="fas fa-hammer mr-2"></i> İş Emri Oluştur
                            </a>
                        <?php else: ?>
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-5x"></i>
                            </div>
                            <h4 class="font-weight-bold text-success">İşlem Gerekmiyor</h4>
                            <p class="text-muted">Stoklarınız yeterli veya üretim için gerekli malzeme yok.</p>
                            
                            <div class="mt-4 text-left p-3 bg-light rounded">
                                <small class="d-block text-muted mb-1">Durum Özeti:</small>
                                <?php if ($acik <= 0): ?>
                                    <div class="text-success"><i class="fas fa-check mr-1"></i> Stok İhtiyacı Karşılıyor</div>
                                <?php elseif ($uretilebilir_limit == 0): ?>
                                    <div class="text-danger"><i class="fas fa-times mr-1"></i> Malzeme Yetersiz (Stoklar 0)</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
