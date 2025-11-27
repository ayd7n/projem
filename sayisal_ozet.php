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

// Page-level permission check
if (!yetkisi_var('page:view:raporlar')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Count various entities in the database
$musteri_count = 0;
$musteri_sistem_giris_yetkisi_count = 0;
$personel_count = 0;
$personel_sistem_giris_yetkisi_count = 0;
$tank_count = 0;
$is_merkezi_count = 0;
$esans_is_emri_count = 0;
$montaj_is_emri_count = 0;
$esans_count = 0;
$urun_count = 0;
$siparis_count = 0;
$siparis_olusturulan_count = 0;
$siparis_onay_bekleyen_count = 0;
$siparis_teslim_edilen_count = 0;
$siparis_iptal_edilen_count = 0;
$siparis_kalem_count = 0;
$malzeme_count = 0;
$tedarikci_count = 0;
$stok_hareket_count = 0;
$cerceve_sozlesme_count = 0;
$cerceve_sozlesme_gecerli_count = 0;
$cerceve_sozlesme_dolmus_count = 0;
$gider_yonetimi_count = 0;
$siparis_kalemi_ortalama_tutar = 0;
$siparis_edilen_toplam_adet = 0;
$siparis_ortalama_adet = 0;
$toplam_urun_stogu = 0;
$toplam_malzeme_stogu = 0;
$kritik_urun_stogu = 0;
$kritik_malzeme_stogu = 0;
$urun_agaci_urun_count = 0;
$urun_agaci_bilesen_count = 0;
$lokasyon_count = 0;
$urun_agaci_count = 0;

try {
    // Count customers
    $result = $connection->query("SELECT COUNT(*) FROM musteriler");
    $musteri_count = $result->fetch_row()[0];

    // Count customers with system access
    $result = $connection->query("SELECT COUNT(*) FROM musteriler WHERE giris_yetkisi = 1");
    $musteri_sistem_giris_yetkisi_count = $result->fetch_row()[0];

    // Count staff
    $result = $connection->query("SELECT COUNT(*) FROM personeller");
    $personel_count = $result->fetch_row()[0];

    // Count staff with system access
    $result = $connection->query("SELECT COUNT(*) FROM personeller WHERE sistem_sifresi IS NOT NULL AND sistem_sifresi != ''");
    $personel_sistem_giris_yetkisi_count = $result->fetch_row()[0];

    // Count tanks
    $result = $connection->query("SELECT COUNT(*) FROM tanklar");
    $tank_count = $result->fetch_row()[0];

    // Count assembly centers (is merkezleri)
    $result = $connection->query("SELECT COUNT(*) FROM is_merkezleri");
    $is_merkezi_count = $result->fetch_row()[0];

    // Count esans work orders
    $result = $connection->query("SELECT COUNT(*) FROM esans_is_emirleri");
    $esans_is_emri_count = $result->fetch_row()[0];

    // Count assembly work orders
    $result = $connection->query("SELECT COUNT(*) FROM montaj_is_emirleri");
    $montaj_is_emri_count = $result->fetch_row()[0];

    // Count essences
    $result = $connection->query("SELECT COUNT(*) FROM esanslar");
    $esans_count = $result->fetch_row()[0];

    // Count products
    $result = $connection->query("SELECT COUNT(*) FROM urunler");
    $urun_count = $result->fetch_row()[0];

    // Count total orders
    $result = $connection->query("SELECT COUNT(*) FROM siparisler");
    $siparis_count = $result->fetch_row()[0];

    // Count orders by status
    $result = $connection->query("SELECT COUNT(*) FROM siparisler WHERE durum = 'beklemede'");
    $siparis_olusturulan_count = $result->fetch_row()[0];  // Created orders (waiting for approval)

    $result = $connection->query("SELECT COUNT(*) FROM siparisler WHERE durum = 'onaylandi'");
    $siparis_onay_bekleyen_count = $result->fetch_row()[0];  // Approved orders (pending delivery)

    $result = $connection->query("SELECT COUNT(*) FROM siparisler WHERE durum = 'tamamlandi'");
    $siparis_teslim_edilen_count = $result->fetch_row()[0];  // Delivered orders (completed)

    $result = $connection->query("SELECT COUNT(*) FROM siparisler WHERE durum = 'iptal_edildi'");
    $siparis_iptal_edilen_count = $result->fetch_row()[0];  // Cancelled orders

    // Count order items
    $result = $connection->query("SELECT COUNT(*) FROM siparis_kalemleri");
    $siparis_kalem_count = $result->fetch_row()[0];

    // Get average order value
    $result = $connection->query("SELECT COALESCE(AVG(toplam_tutar), 0) FROM siparis_kalemleri");
    $row = $result->fetch_row();
    $siparis_kalemi_ortalama_tutar = floatval($row[0]);

    // Get total ordered quantity and average
    $result = $connection->query("SELECT COALESCE(SUM(adet), 0) FROM siparis_kalemleri");
    $row = $result->fetch_row();
    $siparis_edilen_toplam_adet = floatval($row[0]);

    $result = $connection->query("SELECT COALESCE(AVG(adet), 0) FROM siparis_kalemleri");
    $row = $result->fetch_row();
    $siparis_ortalama_adet = floatval($row[0]);

    // Count materials
    $result = $connection->query("SELECT COUNT(*) FROM malzemeler");
    $malzeme_count = $result->fetch_row()[0];

    // Count suppliers
    $result = $connection->query("SELECT COUNT(*) FROM tedarikciler");
    $tedarikci_count = $result->fetch_row()[0];

    // Count stock movements
    $result = $connection->query("SELECT COUNT(*) FROM stok_hareket_kayitlari");
    $stok_hareket_count = $result->fetch_row()[0];

    // Count framework contracts
    $result = $connection->query("SELECT COUNT(*) FROM cerceve_sozlesmeler");
    $cerceve_sozlesme_count = $result->fetch_row()[0];

    // Count active framework contracts
    $result = $connection->query("SELECT COUNT(*) FROM cerceve_sozlesmeler_gecerlilik WHERE gecerli_mi = 1");
    $cerceve_sozlesme_gecerli_count = $result->fetch_row()[0];

    // Count expired framework contracts - using BINARY to avoid collation issues
    $result = $connection->query("SELECT COUNT(*) FROM cerceve_sozlesmeler_gecerlilik WHERE BINARY gecerlilik_durumu = BINARY 'Suresi Dolmus'");
    $cerceve_sozlesme_dolmus_count = $result->fetch_row()[0];

    // Count expenses
    $result = $connection->query("SELECT COUNT(*) FROM gider_yonetimi");
    $gider_yonetimi_count = $result->fetch_row()[0];

    // Count locations
    $result = $connection->query("SELECT COUNT(*) FROM lokasyonlar");
    $lokasyon_count = $result->fetch_row()[0];

    // Count product trees
    $result = $connection->query("SELECT COUNT(*) FROM urun_agaci");
    $urun_agaci_count = $result->fetch_row()[0];

    // Count distinct products and components in product trees
    $result = $connection->query("SELECT COUNT(DISTINCT urun_kodu) FROM urun_agaci");
    $urun_agaci_urun_count = $result->fetch_row()[0];

    $result = $connection->query("SELECT COUNT(DISTINCT bilesen_kodu) FROM urun_agaci");
    $urun_agaci_bilesen_count = $result->fetch_row()[0];

    // Get total stock levels
    $result = $connection->query("SELECT COALESCE(SUM(stok_miktari), 0) FROM urunler");
    $row = $result->fetch_row();
    $toplam_urun_stogu = floatval($row[0]);

    $result = $connection->query("SELECT COALESCE(SUM(stok_miktari), 0) FROM malzemeler");
    $row = $result->fetch_row();
    $toplam_malzeme_stogu = floatval($row[0]);

    // Count critical stock items
    $result = $connection->query("SELECT COUNT(*) FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
    $kritik_urun_stogu = $result->fetch_row()[0];

    $result = $connection->query("SELECT COUNT(*) FROM malzemeler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
    $kritik_malzeme_stogu = $result->fetch_row()[0];

} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sayısal Özet - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
        }

        .main-content {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 700;
        }

        .navbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            box-shadow: var(--shadow);
        }

        .navbar-brand {
            color: var(--accent, #d4af37) !important;
            font-weight: 700;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white;
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            border: none;
            box-shadow: var(--shadow);
        }

        .dropdown-item {
            color: var(--text-primary);
        }

        .dropdown-item:hover {
            background-color: var(--bg-color);
            color: var(--primary);
        }

        .summary-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 100%;
            text-align: center;
        }

        .summary-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1rem 0;
        }

        .summary-label {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .summary-icon {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .table th {
            background-color: var(--primary);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="page-header">
            <h1><i class="fas fa-list-ol"></i> Sayısal Özet</h1>
            <p class="text-muted">Sistemdeki verilerin sayısal özetini görüntüleyin.</p>
        </div>

        <!-- Detailed Summary Table -->
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h3><i class="fas fa-table"></i> Detaylı Sayısal Özet Tablosu</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Veri Türü</th>
                                <th>Toplam Sayı</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Kullanıcılar ve Erişim -->
                            <tr>
                                <td>1</td>
                                <td><i class="fas fa-users text-primary"></i> Müşteri </td>
                                <td class="font-weight-bold"><?php echo number_format($musteri_count); ?></td>
                                <td>Sistemde kayıtlı toplam müşteri sayısı</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><i class="fas fa-sign-in-alt text-primary"></i> Müşteri Sistem Girişi </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($musteri_sistem_giris_yetkisi_count); ?></td>
                                <td>Sistem erişim yetkisine sahip müşteri sayısı</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><i class="fas fa-user-tie text-success"></i> Personel </td>
                                <td class="font-weight-bold"><?php echo number_format($personel_count); ?></td>
                                <td>Çalışan personel sayısı</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td><i class="fas fa-sign-in-alt text-success"></i> Personel Sistem Girişi </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($personel_sistem_giris_yetkisi_count); ?></td>
                                <td>Sisteme erişim yetkisine sahip personel sayısı</td>
                            </tr>

                            <!-- Tesis ve Ekipman -->
                            <tr>
                                <td>5</td>
                                <td><i class="fas fa-map-marker-alt text-info"></i> Lokasyonlar </td>
                                <td class="font-weight-bold"><?php echo number_format($lokasyon_count); ?></td>
                                <td>Sistemdeki toplam lokasyon sayısı</td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td><i class="fas fa-industry text-info"></i> Montaj Merkezi </td>
                                <td class="font-weight-bold"><?php echo number_format($is_merkezi_count); ?></td>
                                <td>Üretim için kullanılan montaj merkezleri</td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td><i class="fas fa-oil-can text-warning"></i> Tank </td>
                                <td class="font-weight-bold"><?php echo number_format($tank_count); ?></td>
                                <td>Toplam tank sayısı</td>
                            </tr>

                            <!-- Ürünler, Malzemeler ve Stok -->
                            <tr>
                                <td>8</td>
                                <td><i class="fas fa-box text-primary"></i> Ürün </td>
                                <td class="font-weight-bold"><?php echo number_format($urun_count); ?></td>
                                <td>Sistemde tanımlı toplam ürün sayısı</td>
                            </tr>
                            <tr>
                                <td>9</td>
                                <td><i class="fas fa-cubes text-warning"></i> Malzeme </td>
                                <td class="font-weight-bold"><?php echo number_format($malzeme_count); ?></td>
                                <td>Stok takibi yapılan malzeme sayısı</td>
                            </tr>
                            <tr>
                                <td>10</td>
                                <td><i class="fas fa-wine-bottle text-danger"></i> Esans </td>
                                <td class="font-weight-bold"><?php echo number_format($esans_count); ?></td>
                                <td>Toplam esans çeşidi sayısı</td>
                            </tr>
                            <tr>
                                <td>11</td>
                                <td><i class="fas fa-boxes text-success"></i> Toplam Ürün Stoğu </td>
                                <td class="font-weight-bold"><?php echo number_format($toplam_urun_stogu); ?></td>
                                <td>Sistemdeki toplam ürün stok miktarı</td>
                            </tr>
                            <tr>
                                <td>12</td>
                                <td><i class="fas fa-cubes text-warning"></i> Toplam Malzeme Stoğu </td>
                                <td class="font-weight-bold"><?php echo number_format($toplam_malzeme_stogu, 2); ?></td>
                                <td>Sistemdeki toplam malzeme stok miktarı</td>
                            </tr>
                            <tr>
                                <td>13</td>
                                <td><i class="fas fa-exclamation-triangle text-warning"></i> Kritik Ürün Stoğu </td>
                                <td class="font-weight-bold"><?php echo number_format($kritik_urun_stogu); ?></td>
                                <td>Kritik seviyenin altındaki ürün sayısı</td>
                            </tr>
                            <tr>
                                <td>14</td>
                                <td><i class="fas fa-exclamation-triangle text-danger"></i> Kritik Malzeme Stoğu </td>
                                <td class="font-weight-bold"><?php echo number_format($kritik_malzeme_stogu); ?></td>
                                <td>Kritik seviyenin altındaki malzeme sayısı</td>
                            </tr>
                            <tr>
                                <td>15</td>
                                <td><i class="fas fa-exchange-alt text-warning"></i> Stok Hareketleri </td>
                                <td class="font-weight-bold"><?php echo number_format($stok_hareket_count); ?></td>
                                <td>Sistemdeki toplam stok hareketi sayısı</td>
                            </tr>

                            <!-- Üretim ve Planlama -->
                            <tr>
                                <td>16</td>
                                <td><i class="fas fa-sitemap text-success"></i> Ürün Ağaçları </td>
                                <td class="font-weight-bold"><?php echo number_format($urun_agaci_count); ?></td>
                                <td>Sistemdeki toplam ürün ağacı sayısı</td>
                            </tr>
                            <tr>
                                <td>17</td>
                                <td><i class="fas fa-sitemap text-primary"></i> Ürün Ağaçlarında Ürün </td>
                                <td class="font-weight-bold"><?php echo number_format($urun_agaci_urun_count); ?></td>
                                <td>Ürün ağaçlarında tanımlı farklı ürün sayısı</td>
                            </tr>
                            <tr>
                                <td>18</td>
                                <td><i class="fas fa-cogs text-secondary"></i> Ürün Ağaçlarında Bileşen </td>
                                <td class="font-weight-bold"><?php echo number_format($urun_agaci_bilesen_count); ?>
                                </td>
                                <td>Ürün ağaçlarında kullanılan farklı bileşen sayısı</td>
                            </tr>
                            <tr>
                                <td>19</td>
                                <td><i class="fas fa-wine-bottle text-danger"></i> Esans İş Emri </td>
                                <td class="font-weight-bold"><?php echo number_format($esans_is_emri_count); ?></td>
                                <td>Oluşturulmuş esans üretimi iş emirleri</td>
                            </tr>
                            <tr>
                                <td>20</td>
                                <td><i class="fas fa-tools text-secondary"></i> Montaj İş Emri </td>
                                <td class="font-weight-bold"><?php echo number_format($montaj_is_emri_count); ?></td>
                                <td>Oluşturulmuş montaj iş emirleri</td>
                            </tr>

                            <!-- Siparişler ve Satış -->
                            <tr>
                                <td>21</td>
                                <td><i class="fas fa-shopping-cart text-success"></i> Sipariş </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_count); ?></td>
                                <td>Oluşturulmuş toplam sipariş sayısı</td>
                            </tr>
                            <tr>
                                <td>22</td>
                                <td><i class="fas fa-file-alt text-warning"></i> Oluşturulan Sipariş </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_olusturulan_count); ?>
                                </td>
                                <td>Toplam oluşturulan sipariş sayısı (beklemede)</td>
                            </tr>
                            <tr>
                                <td>23</td>
                                <td><i class="fas fa-clock text-primary"></i> Onay Bekleyen Sipariş </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_onay_bekleyen_count); ?>
                                </td>
                                <td>Onay bekleyen sipariş sayısı</td>
                            </tr>
                            <tr>
                                <td>24</td>
                                <td><i class="fas fa-check-circle text-success"></i> Teslim Edilen Sipariş </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_teslim_edilen_count); ?>
                                </td>
                                <td>Teslim edilen (tamamlanan) sipariş sayısı</td>
                            </tr>
                            <tr>
                                <td>25</td>
                                <td><i class="fas fa-times-circle text-danger"></i> İptal Edilen Sipariş </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_iptal_edilen_count); ?>
                                </td>
                                <td>İptal edilen sipariş sayısı</td>
                            </tr>
                            <tr>
                                <td>26</td>
                                <td><i class="fas fa-shopping-bag text-info"></i> Sipariş Kalemleri </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_kalem_count); ?></td>
                                <td>Sistemdeki toplam sipariş kalemi sayısı</td>
                            </tr>
                            <tr>
                                <td>27</td>
                                <td><i class="fas fa-box-open text-info"></i> Toplam Sipariş Adedi </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_edilen_toplam_adet); ?>
                                </td>
                                <td>Sistemdeki sipariş kalemlerinde toplam adet</td>
                            </tr>
                            <tr>
                                <td>28</td>
                                <td><i class="fas fa-box text-info"></i> Ortalama Sipariş Adedi </td>
                                <td class="font-weight-bold"><?php echo number_format($siparis_ortalama_adet, 2); ?>
                                </td>
                                <td>Bir sipariş kalemindeki ortalama adet</td>
                            </tr>
                            <tr>
                                <td>29</td>
                                <td><i class="fas fa-percentage text-info"></i> Ortalama Sipariş Değeri </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($siparis_kalemi_ortalama_tutar, 2); ?></td>
                                <td>Bir sipariş kaleminin ortalama değeri (₺)</td>
                            </tr>

                            <!-- Tedarikçiler ve Sözleşmeler -->
                            <tr>
                                <td>30</td>
                                <td><i class="fas fa-truck text-info"></i> Tedarikçi </td>
                                <td class="font-weight-bold"><?php echo number_format($tedarikci_count); ?></td>
                                <td>Sistemde tanımlı tedarikçi sayısı</td>
                            </tr>
                            <tr>
                                <td>31</td>
                                <td><i class="fas fa-file-contract text-warning"></i> Çerçeve Sözleşmeler </td>
                                <td class="font-weight-bold"><?php echo number_format($cerceve_sozlesme_count); ?></td>
                                <td>Sistemdeki toplam çerçeve sözleşme sayısı</td>
                            </tr>
                            <tr>
                                <td>32</td>
                                <td><i class="fas fa-file-contract text-success"></i> Aktif Çerçeve Sözleşmeleri </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($cerceve_sozlesme_gecerli_count); ?></td>
                                <td>Sistemdeki toplam geçerli çerçeve sözleşme sayısı</td>
                            </tr>
                            <tr>
                                <td>33</td>
                                <td><i class="fas fa-file-contract text-danger"></i> Süresi Dolmuş Sözleşmeler </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($cerceve_sozlesme_dolmus_count); ?></td>
                                <td>Sistemdeki toplam süresi dolmuş sözleşme sayısı</td>
                            </tr>

                            <!-- Finans ve Raporlama -->
                            <tr>
                                <td>34</td>
                                <td><i class="fas fa-money-bill-wave text-danger"></i> Gider Yönetimi </td>
                                <td class="font-weight-bold"><?php echo number_format($gider_yonetimi_count); ?></td>
                                <td>Sistemdeki toplam gider yönetimi kaydı sayısı</td>
                            </tr>
                        </tbody>
                        <tfoot class="font-weight-bold">
                            <tr>
                                <td colspan="2">TOPLAM METRİK SAYISI</td>
                                <td>34</td>
                                <td>Sistemde izlenen farklı metrik sayısı</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>