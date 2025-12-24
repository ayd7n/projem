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
if (!yetkisi_var('page:view:kar_zarar_raporu')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Tarih aralığı parametreleri
$baslangic_tarihi = isset($_GET['baslangic']) ? $_GET['baslangic'] : date('Y-m-01');
$bitis_tarihi = isset($_GET['bitis']) ? $_GET['bitis'] : date('Y-m-t');

// Gelir hesaplaması - tamamlanan siparişlerin toplam tutarı (iptal_edilmemiş siparişler)
$gelir_query = "SELECT SUM(sk.toplam_tutar) AS toplam_gelir FROM siparis_kalemleri sk JOIN siparisler s ON sk.siparis_id = s.siparis_id WHERE s.durum != 'iptal_edildi'";
$gelir_result = mysqli_query($connection, $gelir_query);
$gelir_row = mysqli_fetch_assoc($gelir_result);
$toplam_gelir = $gelir_row['toplam_gelir'] ?? 0;

// Gider hesaplaması - seçilen tarih aralığındaki giderler
$gider_query = "SELECT SUM(tutar) AS toplam_gider FROM gider_yonetimi WHERE tarih BETWEEN ? AND ?";
$gider_stmt = mysqli_prepare($connection, $gider_query);
mysqli_stmt_bind_param($gider_stmt, 'ss', $baslangic_tarihi, $bitis_tarihi);
mysqli_stmt_execute($gider_stmt);
$gider_result = mysqli_stmt_get_result($gider_stmt);
$gider_row = mysqli_fetch_assoc($gider_result);
$toplam_gider = $gider_row['toplam_gider'] ?? 0;

// Kar/Zarar hesaplaması
$kar_zarar = $toplam_gelir - $toplam_gider;
$durum = $kar_zarar >= 0 ? 'Kar' : 'Zarar';

// Detaylı gider analizi - kategorilere göre
$gider_detay_query = "SELECT kategori, SUM(tutar) as toplam FROM gider_yonetimi WHERE tarih BETWEEN ? AND ? GROUP BY kategori ORDER BY toplam DESC";
$gider_detay_stmt = mysqli_prepare($connection, $gider_detay_query);
mysqli_stmt_bind_param($gider_detay_stmt, 'ss', $baslangic_tarihi, $bitis_tarihi);
mysqli_stmt_execute($gider_detay_stmt);
$gider_detay_result = mysqli_stmt_get_result($gider_detay_stmt);
$gider_kategorileri = [];
while ($row = mysqli_fetch_assoc($gider_detay_result)) {
    $gider_kategorileri[] = $row;
}

// Detaylı gelir analizi - ürünlere göre (sadece tamamlanmış siparişler)
$gelir_detay_query = "SELECT sk.urun_ismi, SUM(sk.toplam_tutar) as toplam_gelir, SUM(sk.adet) as toplam_adet FROM siparis_kalemleri sk JOIN siparisler s ON sk.siparis_id = s.siparis_id WHERE s.durum != 'iptal_edildi' GROUP BY sk.urun_ismi ORDER BY toplam_gelir DESC";
$gelir_detay_result = mysqli_query($connection, $gelir_detay_query);
$gelir_kategorileri = [];
while ($row = mysqli_fetch_assoc($gelir_detay_result)) {
    $gelir_kategorileri[] = $row;
}



?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kar-Zarar Raporu - Parfüm ERP</title>
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
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            text-align: center;
        }

        .summary-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .summary-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .summary-card .label {
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        .gelir-card .icon {
            color: #28a745;
        }

        .gider-card .icon {
            color: #dc3545;
        }

        .kar-card .icon {
            color: #007bff;
        }

        .zarar-card .icon {
            color: #ffc107;
        }

        .details-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .details-card h4 {
            color: var(--primary);
            margin-bottom: 1.5rem;
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

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Kar-Zarar Raporu</h1>
            <p class="text-muted">Seçilen dönem için gelir, gider ve kar/zarar analizini görüntüleyin.</p>
        </div>

        <!-- Tarih Seçici -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="form-inline">
                            <div class="form-group mr-3">
                                <label for="baslangic" class="mr-2">Başlangıç Tarihi:</label>
                                <input type="date" class="form-control" id="baslangic" name="baslangic" value="<?php echo $baslangic_tarihi; ?>">
                            </div>
                            <div class="form-group mr-3">
                                <label for="bitis" class="mr-2">Bitiş Tarihi:</label>
                                <input type="date" class="form-control" id="bitis" name="bitis" value="<?php echo $bitis_tarihi; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Raporu Güncelle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="summary-card gelir-card">
                    <i class="fas fa-coins icon"></i>
                    <div class="value text-success"><?php echo number_format($toplam_gelir, 2, ',', '.'); ?> ₺</div>
                    <div class="label">Toplam Gelir</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="summary-card gider-card">
                    <i class="fas fa-money-bill-wave icon"></i>
                    <div class="value text-danger"><?php echo number_format($toplam_gider, 2, ',', '.'); ?> ₺</div>
                    <div class="label">Dönem Gideri</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="summary-card <?php echo $kar_zarar >= 0 ? 'kar-card' : 'zarar-card'; ?>">
                    <i class="fas fa-<?php echo $kar_zarar >= 0 ? 'plus-circle' : 'minus-circle'; ?> icon"></i>
                    <div class="value <?php echo $kar_zarar >= 0 ? 'text-primary' : 'text-warning'; ?>"><?php echo number_format(abs($kar_zarar), 2, ',', '.'); ?> ₺</div>
                    <div class="label"><?php echo $durum; ?></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="summary-card">
                    <i class="fas fa-calendar-alt icon" style="color: var(--primary);"></i>
                    <div class="value" style="color: var(--primary); font-size: 1.5rem;"><?php echo date('d.m.Y', strtotime($baslangic_tarihi)) . ' - ' . date('d.m.Y', strtotime($bitis_tarihi)); ?></div>
                    <div class="label">Rapor Dönemi</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Gelir Ürünleri Tablosu -->
            <div class="col-md-6">
                <div class="details-card">
                    <h4><i class="fas fa-coins"></i> Ürün Bazlı Gelir Dağılımı</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Ürün Adı</th>
                                    <th>Toplam Adet</th>
                                    <th>Gelir</th>
                                    <th>Gelir Oranı</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($gelir_kategorileri, 0, 10) as $gelir): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($gelir['urun_ismi']); ?></td>
                                        <td><?php echo number_format($gelir['toplam_adet'], 0, ',', '.'); ?></td>
                                        <td><?php echo number_format($gelir['toplam_gelir'], 2, ',', '.'); ?> ₺</td>
                                        <td><?php echo $toplam_gelir > 0 ? number_format(($gelir['toplam_gelir'] / $toplam_gelir) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gider Kategorileri Tablosu -->
            <div class="col-md-6">
                <div class="details-card">
                    <h4><i class="fas fa-money-bill-wave"></i> Gider Kategorileri Dağılımı</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Toplam Tutar</th>
                                    <th>Gider Oranı</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gider_kategorileri as $kategori): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($kategori['kategori']); ?></td>
                                        <td><?php echo number_format($kategori['toplam'], 2, ',', '.'); ?> ₺</td>
                                        <td><?php echo $toplam_gider > 0 ? number_format(($kategori['toplam'] / $toplam_gider) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-md-12">
                <div class="details-card">
                    <h4><i class="fas fa-info-circle"></i> Hesaplama Detayları</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Gelir Hesaplaması</h6>
                            <p>Tamamlanan siparişlerin satış fiyatları ile ürün adetlerinin çarpılması sonucu elde edilen toplam gelir.</p>
                            <small class="text-muted">Formül: Σ(satış_fiyat × adet)</small>
                        </div>
                        <div class="col-md-4">
                            <h6>Gider Hesaplaması</h6>
                            <p>Seçilen tarih aralığındaki tüm giderlerin toplamı.</p>
                            <small class="text-muted">Formül: Σ(gider_tutar) WHERE tarih BETWEEN başlangıç AND bitiş</small>
                        </div>
                        <div class="col-md-4">
                            <h6>Kar/Zarar Hesaplaması</h6>
                            <p>Gelir ile gider arasındaki fark.</p>
                            <small class="text-muted">Formül: Gelir - Gider</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
