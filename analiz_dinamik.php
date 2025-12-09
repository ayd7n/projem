<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Kritik seviyeleri al
$kritik_seviyeler = [];
$result = $conn->query("SELECT urun_kodu, kritik_stok_seviyesi FROM urunler");
while ($row = $result->fetch_assoc()) {
    $kritik_seviyeler[$row['urun_kodu']] = $row['kritik_stok_seviyesi'];
}

// Toplam sipariş adetleri (beklemede + onaylandı)
$siparisler = [];
$result = $conn->query("
    SELECT sk.urun_kodu, SUM(sk.adet) as toplam_adet
    FROM siparisler s
    JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
    WHERE s.durum IN ('beklemede', 'onaylandi')
    GROUP BY sk.urun_kodu
");
while ($row = $result->fetch_assoc()) {
    $siparisler[$row['urun_kodu']] = $row['toplam_adet'];
}

// Mevcut stoklar
$stoklar = [];
$result = $conn->query("SELECT urun_kodu, stok_miktari FROM urunler");
while ($row = $result->fetch_assoc()) {
    $stoklar[$row['urun_kodu']] = $row['stok_miktari'];
}

// Mevcut iş emirlerinden gelecek üretim
$gelecek_urun = [];
$result = $conn->query("SELECT urun_kodu, planlanan_miktar FROM montaj_is_emirleri WHERE durum = 'uretimde'");
while ($row = $result->fetch_assoc()) {
    $gelecek_urun[$row['urun_kodu']] = ($gelecek_urun[$row['urun_kodu']] ?? 0) + $row['planlanan_miktar'];
}

// Mevcut iş emirlerinden gelecek esans
$gelecek_esans = [];
$result = $conn->query("SELECT esans_kodu, planlanan_miktar FROM esans_is_emirleri WHERE durum = 'uretimde'");
while ($row = $result->fetch_assoc()) {
    $gelecek_esans[$row['esans_kodu']] = ($gelecek_esans[$row['esans_kodu']] ?? 0) + $row['planlanan_miktar'];
}

// Esans stokları ve isimleri
$esans_stoklar = [];
$esans_isimleri = [];
$result = $conn->query("SELECT esans_kodu, esans_ismi, stok_miktari FROM esanslar");
while ($row = $result->fetch_assoc()) {
    $esans_stoklar[$row['esans_kodu']] = $row['stok_miktari'];
    $esans_isimleri[$row['esans_kodu']] = $row['esans_ismi'];
}

// Malzeme stokları ve isimleri
$malzeme_stoklar = [];
$malzeme_isimleri = [];
$result = $conn->query("SELECT malzeme_kodu, malzeme_ismi, stok_miktari FROM malzemeler");
while ($row = $result->fetch_assoc()) {
    $malzeme_stoklar[$row['malzeme_kodu']] = $row['stok_miktari'];
    $malzeme_isimleri[$row['malzeme_kodu']] = $row['malzeme_ismi'];
}

// Ürün isimleri
$urun_isimleri = [];
$result = $conn->query("SELECT urun_kodu, urun_ismi FROM urunler");
while ($row = $result->fetch_assoc()) {
    $urun_isimleri[$row['urun_kodu']] = $row['urun_ismi'];
}

// Mevcut malzeme siparişleri
$malzeme_siparisler = [];
$result = $conn->query("SELECT malzeme_kodu, SUM(miktar) as toplam_miktar FROM malzeme_siparisler WHERE durum = 'siparis_verildi' GROUP BY malzeme_kodu");
while ($row = $result->fetch_assoc()) {
    $malzeme_siparisler[$row['malzeme_kodu']] = $row['toplam_miktar'];
}

// Ürün ağacı
$urun_agaci = [];
$result = $conn->query("SELECT urun_kodu, bilesenin_malzeme_turu, bilesen_kodu, bilesen_miktari FROM urun_agaci");
while ($row = $result->fetch_assoc()) {
    $urun_agaci[$row['urun_kodu']][] = $row;
}

// Esans ağacı
$esans_agaci = [];
$result = $conn->query("SELECT ua.urun_kodu as esans_id, ua.bilesen_kodu, ua.bilesen_miktari FROM urun_agaci ua JOIN esanslar e ON ua.urun_kodu = e.esans_id WHERE ua.agac_turu = 'esans'");
while ($row = $result->fetch_assoc()) {
    $esans_agaci[$row['esans_id']][] = $row;
}

// Hesaplamalar
$uretim_ihtiyaclari = [];
foreach ($siparisler as $urun_kodu => $toplam_siparis) {
    $mevcut_stok = $stoklar[$urun_kodu] ?? 0;
    $gelecek = $gelecek_urun[$urun_kodu] ?? 0;
    $kritik = $kritik_seviyeler[$urun_kodu] ?? 0;
    $uretilecek = max(0, $toplam_siparis + $kritik - ($mevcut_stok + $gelecek));
    $uretim_ihtiyaclari[$urun_kodu] = [
        'toplam_siparis' => $toplam_siparis,
        'mevcut_stok' => $mevcut_stok,
        'gelecek' => $gelecek,
        'kritik' => $kritik,
        'uretilecek' => $uretilecek
    ];
}

// Esans ihtiyaçları
$esans_ihtiyaclari = [];
foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
    $uretilecek_urun = $data['uretilecek'];
    if (isset($urun_agaci[$urun_kodu])) {
        foreach ($urun_agaci[$urun_kodu] as $bilesen) {
            if ($bilesen['bilesenin_malzeme_turu'] == 'esans') {
                $esans_kodu = $bilesen['bilesen_kodu'];
                $miktar = $bilesen['bilesen_miktari'] * $uretilecek_urun;
                $esans_ihtiyaclari[$esans_kodu] = ($esans_ihtiyaclari[$esans_kodu] ?? 0) + $miktar;
            }
        }
    }
}

// Esans üretilecek
$esans_uretilecek = [];
foreach ($esans_ihtiyaclari as $esans_kodu => $toplam_ihtiyac) {
    $mevcut_stok = $esans_stoklar[$esans_kodu] ?? 0;
    $gelecek = $gelecek_esans[$esans_kodu] ?? 0;
    $uretilecek = max(0, $toplam_ihtiyac - $mevcut_stok - $gelecek);
    $esans_uretilecek[$esans_kodu] = $uretilecek;
}

// Malzeme ihtiyaçları
$malzeme_ihtiyaclari = [];
foreach ($esans_uretilecek as $esans_kodu => $uretilecek_esans) {
    $esans_id = null;
    $result = $conn->query("SELECT esans_id FROM esanslar WHERE esans_kodu = '$esans_kodu'");
    if ($row = $result->fetch_assoc()) {
        $esans_id = $row['esans_id'];
    }
    if ($esans_id && isset($esans_agaci[$esans_id])) {
        foreach ($esans_agaci[$esans_id] as $bilesen) {
            $malzeme_kodu = $bilesen['bilesen_kodu'];
            $miktar = $bilesen['bilesen_miktari'] * $uretilecek_esans;
            $malzeme_ihtiyaclari[$malzeme_kodu] = ($malzeme_ihtiyaclari[$malzeme_kodu] ?? 0) + $miktar;
        }
    }
}

// Etiket ihtiyaçları (ürün ağacından)
$etiket_ihtiyaclari = [];
foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
    $uretilecek_urun = $data['uretilecek'];
    if (isset($urun_agaci[$urun_kodu])) {
        foreach ($urun_agaci[$urun_kodu] as $bilesen) {
            if ($bilesen['bilesenin_malzeme_turu'] == 'etiket') {
                $etiket_kodu = $bilesen['bilesen_kodu'];
                $miktar = $bilesen['bilesen_miktari'] * $uretilecek_urun;
                $etiket_ihtiyaclari[$etiket_kodu] = ($etiket_ihtiyaclari[$etiket_kodu] ?? 0) + $miktar;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dinamik Üretim Analizi - Parfüm ERP</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        'primary-dark': '#4f46e5',
                        'primary-light': '#818cf8',
                        secondary: '#8b5cf6',
                        accent: '#f59e0b',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#3b82f6'
                    },
                    fontFamily: {
                        sans: ['Ubuntu', 'Segoe UI', 'sans-serif']
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards'
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 text-gray-900 font-sans min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-purple-800 to-purple-600 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a class="text-yellow-400 font-bold text-lg flex items-center gap-2" href="navigation.php">
                    <i class="fas fa-spa"></i> IDO KOZMETIK
                </a>
                <div class="hidden md:flex items-center space-x-4">
                    <a class="hover:text-yellow-300 transition-colors" href="navigation.php">Ana Sayfa</a>
                    <a class="hover:text-yellow-300 transition-colors" href="change_password.php">Parolamı Değiştir</a>
                    <div class="relative">
                        <button class="flex items-center space-x-1 hover:text-yellow-300 transition-colors" id="userMenuButton">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?></span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden" id="userMenu">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                            </a>
                        </div>
                    </div>
                </div>
                <button class="md:hidden" id="mobileMenuButton">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="text-center mb-4">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-1 flex items-center justify-center gap-2">
                    <i class="fas fa-chart-line text-primary"></i> Dinamik Üretim Analizi
                </h1>
                <p class="text-gray-600 text-xs">Siparişlerin karşılanması için gerekli üretim planlaması ve malzeme ihtiyaç analizi</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3 max-w-4xl mx-auto">
                    <i class="fas fa-info-circle text-blue-600 mr-1 text-sm"></i>
                    <strong class="text-blue-800 text-sm">Bilgi:</strong> <span class="text-sm">Bu sayfadaki tüm değerler gerçek zamanlı olarak veritabanından çekilen verilere göre dinamik olarak hesaplanmaktadır. Sayfa her yenilendiğinde güncel stok durumları, sipariş bilgileri ve üretim ihtiyaçları otomatik olarak güncellenir.</span>
                </div>
            </div>


            <!-- Production Guide Card -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-clipboard-list"></i> Net Kılavuz: Yapılacak İşlemler
                    </h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <div class="border-l-4 border-primary bg-gray-50 rounded-r-lg p-4 hover:translate-x-1 transition-transform duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                                        <i class="fas fa-shopping-cart text-green-600"></i> Eksik Malzemeler İçin Yeni Sipariş Ver (Paralel)
                                    </h4>
                                    <p class="text-gray-700 mb-2">Toplam ihtiyaçlara göre yeni siparişler ver (mevcut siparişleri beklemeden):</p>
                                    <ul class="space-y-1">
                                        <?php foreach ($malzeme_ihtiyaclari as $malzeme_kodu => $ihtiyac) {
                                            $mevcut_siparis = $malzeme_siparisler[$malzeme_kodu] ?? 0;
                                            $mevcut_stok = $malzeme_stoklar[$malzeme_kodu] ?? 0;
                                            $yeni_siparis = max(0, $ihtiyac - $mevcut_siparis - $mevcut_stok);
                                            $malzeme_ismi = $malzeme_isimleri[$malzeme_kodu] ?? $malzeme_kodu;
                                            if ($yeni_siparis > 0) {
                                                echo "<li class='text-sm'><strong>$malzeme_ismi</strong>: $yeni_siparis birim (toplam ihtiyaç " . number_format($ihtiyac, 2) . " - mevcut sipariş $mevcut_siparis - stok $mevcut_stok = $yeni_siparis).</li>";
                                            }
                                        } ?>
                                        <?php foreach ($etiket_ihtiyaclari as $etiket_kodu => $ihtiyac) {
                                            $mevcut_siparis = $malzeme_siparisler[$etiket_kodu] ?? 0;
                                            $mevcut_stok = $malzeme_stoklar[$etiket_kodu] ?? 0;
                                            $yeni_siparis = max(0, $ihtiyac - $mevcut_siparis - $mevcut_stok);
                                            $etiket_ismi = $malzeme_isimleri[$etiket_kodu] ?? $etiket_kodu;
                                            if ($yeni_siparis > 0) {
                                                echo "<li class='text-sm'><strong>$etiket_ismi</strong>: $yeni_siparis birim (toplam ihtiyaç " . number_format($ihtiyac, 2) . " - mevcut sipariş $mevcut_siparis - stok $mevcut_stok = $yeni_siparis).</li>";
                                            }
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-warning bg-gray-50 rounded-r-lg p-4 hover:translate-x-1 transition-transform duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-warning to-yellow-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                                        <i class="fas fa-truck text-orange-600"></i> Mevcut Siparişlerin Teslimini Bekle
                                    </h4>
                                    <ul class="space-y-1">
                                        <?php
                                        $siparis_var = false;
                                        foreach ($malzeme_siparisler as $malzeme_kodu => $miktar) {
                                            if ($miktar > 0) {
                                                $siparis_var = true;
                                                $malzeme_ismi = $malzeme_isimleri[$malzeme_kodu] ?? $malzeme_kodu;
                                                echo "<li class='text-sm'>$malzeme_ismi: $miktar birim.</li>";
                                            }
                                        }
                                        if (!$siparis_var) {
                                            echo "<li class='text-sm text-gray-500'>Henüz bekleyen malzeme siparişi yok.</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-info bg-gray-50 rounded-r-lg p-4 hover:translate-x-1 transition-transform duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-info to-blue-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                                        <i class="fas fa-cogs text-blue-600"></i> Mevcut İş Emirlerini Tamamla
                                    </h4>
                                    <ul class="space-y-1">
                                        <?php
                                        $emir_var = false;
                                        foreach ($gelecek_esans as $esans_kodu => $miktar) {
                                            if ($miktar > 0) {
                                                $emir_var = true;
                                                $esans_ismi = $esans_isimleri[$esans_kodu] ?? $esans_kodu;
                                                echo "<li class='text-sm'><strong>$esans_ismi</strong>: $miktar birim üretimi tamamlanmalı.</li>";
                                            }
                                        } ?>
                                        <?php foreach ($gelecek_urun as $urun_kodu => $miktar) {
                                            if ($miktar > 0) {
                                                $emir_var = true;
                                                $urun_ismi = $urun_isimleri[$urun_kodu] ?? $urun_kodu;
                                                echo "<li class='text-sm'><strong>$urun_ismi</strong>: $miktar adet montajı tamamlanmalı.</li>";
                                            }
                                        }
                                        if (!$emir_var) {
                                            echo "<li class='text-sm text-gray-500'>Tamamlanması gereken mevcut iş emri yok.</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-primary bg-gray-50 rounded-r-lg p-4 hover:translate-x-1 transition-transform duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                                        <i class="fas fa-calendar-check text-indigo-600"></i> Yeni Siparişlerin Teslimini Bekle
                                    </h4>
                                    <p class="text-gray-700 text-sm">Yeni verilen siparişlerin teslim tarihlerini kontrol et ve malzemeler ulaştıktan sonra devam et.</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-success bg-gray-50 rounded-r-lg p-4 hover:translate-x-1 transition-transform duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-success to-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                                        <i class="fas fa-plus-circle text-green-600"></i> Ek Üretim İş Emirleri Aç
                                    </h4>
                                    <p class="text-gray-700 text-sm mb-2">Yeni malzemeler ulaştıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri aç:</p>
                                    <ul class="space-y-1">
                                        <?php foreach ($esans_uretilecek as $esans_kodu => $miktar) {
                                            if ($miktar > 0) {
                                                $esans_ismi = $esans_isimleri[$esans_kodu] ?? $esans_kodu;
                                                echo "<li class='text-sm'><strong>$esans_ismi</strong>: " . number_format($miktar, 2) . " birim.</li>";
                                            }
                                        } ?>
                                        <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
                                            if ($data['uretilecek'] > 0) {
                                                $urun_ismi = $urun_isimleri[$urun_kodu] ?? $urun_kodu;
                                                echo "<li class='text-sm'><strong>$urun_ismi</strong>: " . $data['uretilecek'] . " adet.</li>";
                                            }
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                        <strong class="text-yellow-800">Uyarı:</strong> Adım 1 paralel olarak başlatılabilir. Diğer adımlar sıralı olmalı. Malzeme teslim tarihleri kritik.
                    </div>
                </div>
            </div>

            <!-- Production Needs Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-calculator"></i> Üretim İhtiyaçları
                    </h2>
                </div>
                <div class="p-4">
                    <p class="text-gray-600 mb-4 text-sm">Siparişler karşılandıktan sonra kritik stok seviyesinin altında kalmaması için üretim hesaplanmıştır.</p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data): ?>
                            <?php if ($data['uretilecek'] > 0): ?>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <h3 class="text-base font-bold text-gray-900 mb-3 pb-2 border-b-2 border-primary flex items-center gap-2">
                                    <?php echo $urun_isimleri[$urun_kodu] ?? $urun_kodu; ?>
                                </h3>
                                <ul class="space-y-2 mb-3">
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="flex items-center gap-1"><i class="fas fa-shopping-cart text-green-600 text-xs"></i><strong>Toplam Sipariş:</strong></span>
                                        <span><?php echo $data['toplam_siparis']; ?></span>
                                    </li>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="flex items-center gap-1"><i class="fas fa-box-open text-blue-600 text-xs"></i><strong>Stok:</strong></span>
                                        <span><?php echo $data['mevcut_stok']; ?></span>
                                    </li>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="flex items-center gap-1"><i class="fas fa-clock text-orange-600 text-xs"></i><strong>Gelecek:</strong></span>
                                        <span><?php echo $data['gelecek']; ?> adet</span>
                                    </li>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="flex items-center gap-1"><i class="fas fa-exclamation-triangle text-red-600 text-xs"></i><strong>Kritik:</strong></span>
                                        <span><?php echo $data['kritik']; ?></span>
                                    </li>
                                    <li class="flex justify-between items-center border-t pt-1 text-sm">
                                        <span class="flex items-center gap-1"><i class="fas fa-industry text-indigo-600 text-xs"></i><strong>Üretilecek:</strong></span>
                                        <span class="text-red-600 font-bold"><?php echo $data['uretilecek']; ?></span>
                                    </li>
                                </ul>

                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-1">
                                        <i class="fas fa-cubes text-indigo-600 text-xs"></i> Bileşen İhtiyaçları
                                    </h4>
                                    <?php
                                    $esans_toplam = 0;
                                    $malzeme_toplam = [];
                                    $urun_bilesenler = [];
                                    $result = $conn->query("SELECT bilesenin_malzeme_turu, bilesen_kodu, bilesen_miktari FROM urun_agaci WHERE urun_kodu = '$urun_kodu'");
                                    while ($row = $result->fetch_assoc()) {
                                        $urun_bilesenler[] = $row;
                                        if ($row['bilesenin_malzeme_turu'] == 'esans') {
                                            $esans_toplam += $row['bilesen_miktari'] * $data['uretilecek'];
                                        } elseif ($row['bilesenin_malzeme_turu'] == 'malzeme') {
                                            $malzeme_toplam[$row['bilesen_kodu']] = ($malzeme_toplam[$row['bilesen_kodu']] ?? 0) + $row['bilesen_miktari'] * $data['uretilecek'];
                                        }
                                    }
                                    if ($esans_toplam > 0): ?>
                                        <div class="bg-white border border-gray-200 rounded p-2 mb-2 text-sm">
                                            <strong class="text-gray-900">Esans toplam:</strong> <?php echo number_format($esans_toplam, 2); ?> birim
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($malzeme_toplam)): ?>
                                        <h5 class="text-xs font-bold mb-2 flex items-center gap-1">
                                            <i class="fas fa-tools text-green-600 text-xs"></i> Gerekli Malzemeler
                                        </h5>
                                        <div class="space-y-1">
                                            <?php foreach ($malzeme_toplam as $malzeme_kodu => $miktar): ?>
                                                <div class="bg-white border border-gray-200 rounded p-2 text-sm">
                                                    <strong class="text-gray-900"><?php echo $malzeme_isimleri[$malzeme_kodu] ?? $malzeme_kodu; ?>:</strong>
                                                    <?php echo number_format($miktar, 2); ?> birim
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Production Orders Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-file-alt"></i> Mevcut Üretim İş Emirleri
                    </h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1">
                                <i class="fas fa-flask text-orange-600 text-sm"></i> Esans Üretim İş Emirleri
                            </h3>
                            <div class="space-y-2">
                                <?php
                                $esans_emri_var = false;
                                foreach ($gelecek_esans as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $esans_emri_var = true;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <strong class="text-gray-900"><?php echo $esans_isimleri[$esans_kodu] ?? $esans_kodu; ?></strong><br>
                                                    <small class="text-gray-600 text-xs">Planlanan: <?php echo $miktar; ?> birim</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">Üretimde</span><br>
                                                    <small class="text-gray-500 text-xs">Tamamlanan: 0 birim</small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                endforeach;
                                if (!$esans_emri_var): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-green-500 text-lg mb-1"></i>
                                        <p class="text-gray-500 text-sm">Tamamlanması gereken esans iş emri yok</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1">
                                <i class="fas fa-industry text-blue-600 text-sm"></i> Montaj Üretim İş Emirleri
                            </h3>
                            <div class="space-y-2">
                                <?php
                                $urun_emri_var = false;
                                foreach ($gelecek_urun as $urun_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $urun_emri_var = true;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <strong class="text-gray-900"><?php echo $urun_isimleri[$urun_kodu] ?? $urun_kodu; ?></strong><br>
                                                    <small class="text-gray-600 text-xs">Planlanan: <?php echo $miktar; ?> adet</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Üretimde</span><br>
                                                    <small class="text-gray-500 text-xs">Tamamlanan: 0 adet</small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                endforeach;
                                if (!$urun_emri_var): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-green-500 text-lg mb-1"></i>
                                        <p class="text-gray-500 text-sm">Tamamlanması gereken montaj iş emri yok</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material Orders Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-clipboard-list"></i> Malzeme Sipariş Durumları
                    </h2>
                </div>
                <div class="p-4">
                    <p class="text-gray-600 mb-4 text-sm">Eksik malzemeler için mevcut siparişler:</p>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-gradient-to-r from-primary to-primary-dark text-white">
                                    <th class="px-3 py-2 text-left font-semibold text-sm">Malzeme Kodu</th>
                                    <th class="px-3 py-2 text-left font-semibold text-sm">Malzeme İsmi</th>
                                    <th class="px-3 py-2 text-left font-semibold text-sm">Sipariş Miktarı</th>
                                    <th class="px-3 py-2 text-left font-semibold text-sm">Durum</th>
                                    <th class="px-3 py-2 text-right font-semibold text-sm">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $malzeme_sayisi = 0;
                                foreach ($malzeme_siparisler as $malzeme_kodu => $miktar):
                                    // Get material name
                                    $result = $conn->query("SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = '$malzeme_kodu'");
                                    $row = $result->fetch_assoc();
                                    $malzeme_ismi = $row['malzeme_ismi'] ?? $malzeme_kodu;
                                    ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-3 py-2 font-semibold"><?php echo $malzeme_kodu; ?></td>
                                        <td class="px-3 py-2"><?php echo $malzeme_ismi; ?></td>
                                        <td class="px-3 py-2 font-semibold"><?php echo number_format($miktar, 2); ?> birim</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-block px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full">Sipariş Verildi</span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <a href="malzeme_siparisler.php?filter=<?php echo $malzeme_kodu; ?>" class="inline-block px-2 py-1 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition-colors">
                                                Takip Et
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                    $malzeme_sayisi++;
                                endforeach;
                                if ($malzeme_sayisi == 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500 text-sm">
                                            <i class="fas fa-check-circle text-green-500 text-lg mb-1 block"></i> Eksik malzeme için bekleyen sipariş yok
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Production Guide Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-wrench"></i> Üretim Kılavuzu
                    </h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1">
                                <i class="fas fa-check-circle text-green-600 text-sm"></i> Mevcut İş Emirleri Tamamlanması
                            </h3>
                            <div class="space-y-2">
                                <?php
                                $emir_tamamlanacak = false;
                                foreach ($gelecek_esans as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $emir_tamamlanacak = true;
                                        $esans_ismi = $esans_isimleri[$esans_kodu] ?? $esans_kodu;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-2 text-sm">
                                            <i class="fas fa-flask text-orange-600 mr-1 text-xs"></i> <?php echo $miktar; ?> birim <?php echo $esans_ismi; ?> esansı üretimi tamamlanmalı
                                        </div>
                                        <?php
                                    }
                                endforeach; ?>
                                <?php foreach ($gelecek_urun as $urun_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $emir_tamamlanacak = true;
                                        $urun_ismi = $urun_isimleri[$urun_kodu] ?? $urun_kodu;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-2 text-sm">
                                            <i class="fas fa-industry text-blue-600 mr-1 text-xs"></i> <?php echo $miktar; ?> adet <?php echo $urun_ismi; ?> montajı tamamlanmalı
                                        </div>
                                        <?php
                                    }
                                endforeach;
                                if (!$emir_tamamlanacak): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-green-500 text-lg mb-1"></i>
                                        <p class="text-gray-500 text-sm">Tamamlanması gereken iş emri yok</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1">
                                <i class="fas fa-plus-circle text-indigo-600 text-sm"></i> Ek Üretim İş Emirleri Açılması
                            </h3>
                            <p class="text-gray-700 text-xs mb-3">Mevcut iş emirleri tamamlandıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri açılmalı:</p>
                            <div class="space-y-2">
                                <?php
                                $yeni_emir_var = false;
                                foreach ($esans_uretilecek as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $yeni_emir_var = true;
                                        $esans_ismi = $esans_isimleri[$esans_kodu] ?? $esans_kodu;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-2 text-sm">
                                            <i class="fas fa-flask text-orange-600 mr-1 text-xs"></i> <?php echo $esans_ismi; ?> için <?php echo number_format($miktar, 2); ?> birim. Yeni esans iş emri açılmalı.
                                        </div>
                                        <?php
                                    }
                                endforeach; ?>
                                <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data):
                                    if ($data['uretilecek'] > 0) {
                                        $yeni_emir_var = true;
                                        $urun_ismi = $urun_isimleri[$urun_kodu] ?? $urun_kodu;
                                        ?>
                                        <div class="bg-gray-50 border border-gray-200 rounded p-2 text-sm">
                                            <i class="fas fa-industry text-blue-600 mr-1 text-xs"></i> <?php echo $urun_ismi; ?> için <?php echo $data['uretilecek']; ?> adet. Yeni montaj iş emri açılmalı.
                                        </div>
                                        <?php
                                    }
                                endforeach;
                                if (!$yeni_emir_var): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-green-500 text-lg mb-1"></i>
                                        <p class="text-gray-500 text-sm">Yeni açılacak iş emri yok</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2 m-0">
                        <i class="fas fa-clipboard-check"></i> Özet
                    </h2>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-chart-line text-indigo-600 mt-0.5 text-sm"></i>
                            <div class="text-sm">
                                <strong class="text-gray-900">Kritik stok seviyelerine göre üretim planlandı.</strong>
                            </div>
                        </div>
                        <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data): ?>
                            <?php if ($data['uretilecek'] > 0): ?>
                                <?php $urun_ismi = $urun_isimleri[$urun_kodu] ?? $urun_kodu; ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-box text-blue-600 mt-0.5 text-sm"></i>
                                    <div class="text-sm">
                                        Toplam üretim ihtiyacı: <strong><?php echo $urun_ismi; ?></strong> için
                                        <span class="inline-block px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full"><?php echo $data['uretilecek']; ?> adet</span>
                                        (sipariş sonrası kritik seviye için).
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (array_sum($esans_uretilecek) > 0): ?>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-flask text-orange-600 mt-0.5 text-sm"></i>
                                <div class="text-sm">
                                    Esans üretimi:
                                    <?php
                                    $esans_list = [];
                                    foreach ($esans_uretilecek as $esans_kodu => $miktar) {
                                        if ($miktar > 0) {
                                            $esans_ismi = $esans_isimleri[$esans_kodu] ?? $esans_kodu;
                                            $esans_list[] = "<strong>" . $esans_ismi . "</strong> için <span class='inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full'>" . number_format($miktar, 2) . " birim</span>";
                                        }
                                    }
                                    echo implode(", ", $esans_list);
                                    ?>, bekleyen.
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-tasks text-green-600 mt-0.5 text-sm"></i>
                            <div class="text-sm">
                                Mevcut iş emirleri tamamlanmalı, ardından eksik kalan için yeni iş emirleri açılmalı.
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-shopping-cart text-red-600 mt-0.5 text-sm"></i>
                            <div class="text-sm">
                                Malzeme siparişleri artırılmalı veya yeni siparişler verilmeli.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/98 border-t border-gray-200 py-3 z-50 backdrop-blur-md">
        <div class="flex justify-around items-center">
            <a href="navigation.php" class="flex flex-col items-center text-gray-600 hover:text-primary transition-colors py-1 px-2 rounded">
                <i class="fas fa-home text-lg mb-1"></i>
                <span class="text-xs font-medium">Ana Sayfa</span>
            </a>
            <a href="#operations" class="flex flex-col items-center text-gray-600 hover:text-primary transition-colors py-1 px-2 rounded">
                <i class="fas fa-cogs text-lg mb-1"></i>
                <span class="text-xs font-medium">İşlemler</span>
            </a>
            <a href="#reports" class="flex flex-col items-center text-gray-600 hover:text-primary transition-colors py-1 px-2 rounded">
                <i class="fas fa-chart-pie text-lg mb-1"></i>
                <span class="text-xs font-medium">Raporlar</span>
            </a>
            <a href="ayarlar.php" class="flex flex-col items-center text-gray-600 hover:text-primary transition-colors py-1 px-2 rounded">
                <i class="fas fa-cog text-lg mb-1"></i>
                <span class="text-xs font-medium">Ayarlar</span>
            </a>
            <a href="logout.php" class="flex flex-col items-center text-gray-600 hover:text-primary transition-colors py-1 px-2 rounded" title="Çıkış Yap">
                <i class="fas fa-sign-out-alt text-lg mb-1"></i>
                <span class="text-xs font-medium">Çıkış</span>
            </a>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');

        mobileMenuButton?.addEventListener('click', () => {
            // Add mobile menu logic if needed
        });

        userMenuButton?.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuButton?.contains(e.target) && !userMenu?.contains(e.target)) {
                userMenu?.classList.add('hidden');
            }
        });
    </script>

    <?php
    $conn->close();
    ?>
</body>

</html>
