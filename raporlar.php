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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
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
        .settings-card, .settings-form-card {
            display: block;
            text-decoration: none;
            color: var(--text-primary);
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
        }
        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            color: var(--primary);
        }
        .settings-card .card-title, .settings-form-card .card-title {
            font-weight: 700;
        }
        .settings-card .card-text, .settings-form-card .card-text {
            color: var(--text-secondary);
        }
        .settings-card .icon, .settings-form-card .icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: var(--primary);
        }
        .custom-control-label::before,
        .custom-control-label::after {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
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
            <h1><i class="fas fa-chart-pie"></i> Raporlar</h1>
            <p class="text-muted">Sistem verilerini analiz edin ve raporları görüntüleyin.</p>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="gider_raporlari.php" class="settings-card">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-invoice-dollar icon"></i>
                        <div>
                            <h5 class="card-title mb-1">Gider Raporları</h5>
                            <p class="card-text mb-0">Gider yönetimi verilerini analiz edin.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="kritik_stok_raporlari.php" class="settings-card">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle icon"></i>
                        <div>
                            <h5 class="card-title mb-1">Kritik Stok Seviyeleri</h5>
                            <p class="card-text mb-0">Kritik seviye altındaki ürün ve malzeme stoğunu analiz edin.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="urun_agaci_analiz.php" class="settings-card">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-sitemap icon"></i>
                        <div>
                            <h5 class="card-title mb-1">Eksik Bileşen Raporu</h5>
                            <p class="card-text mb-0">Hem esans hem de diğer bileşen türlerinden eksik olan ürünleri analiz edin.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <a href="bileseni_eksik_esanslar.php" class="settings-card">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-wine-bottle icon"></i>
                        <div>
                            <h5 class="card-title mb-1">Ağaçları Olmayan Esanslar</h5>
                            <p class="card-text mb-0">Kendi üretim ağaçları olmayan esansları analiz edin.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <a href="stok_hareket_raporu.php" class="settings-card">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line icon"></i>
                        <div>
                            <h5 class="card-title mb-1">Stok Hareket Analizi</h5>
                            <p class="card-text mb-0">Stok hareketlerini grafiklerle görselleştirin.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>