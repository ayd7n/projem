<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$kullanici_adi = isset($_SESSION['kullanici_adi']) ? htmlspecialchars($_SESSION['kullanici_adi']) : 'Kullanıcı';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDO KOZMETIK - Ana Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #fdf8f5; /* Soft cream background */
            margin: 0;
            color: #5a5a5a;
        }
        .header {
            background: linear-gradient(45deg, #4a0e63, #7c2a99);
            color: #ffffff;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title h1 {
            font-family: 'Ubuntu', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            color: #ffffff;
        }
        .header-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-user span {
            font-weight: 700;
        }
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            padding: 0.25rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .page-header {
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .page-header h2 {
            font-family: 'Ubuntu', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: #4a0e63;
        }
        .page-header p {
            font-size: 0.9rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0.25rem auto 0;
        }
        .category-divider {
            margin: 0.25rem 0 0.25rem 0;
            text-align: center;
        }
        .category-divider h3 {
            font-family: 'Ubuntu', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: #4a0e63;
            display: inline-block;
            position: relative;
        }
        .category-divider h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background-color: #d4af37; /* Gold */
        }
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.1rem;
        }
        .nav-card {
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: #4a0e63;
            padding: 0.25rem 0.2rem;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #f0e9e4;
        }
        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .nav-card .icon-wrapper {
            font-size: 1.3rem;
            margin-bottom: 0.1rem;
            color: #d4af37; /* Gold */
        }
        .nav-card .card-title {
            font-weight: 700;
            font-size: 0.8rem;
        }
        footer {
            text-align: center;
            padding: 0.25rem;
            margin-top: 0.25rem;
            color: #9ca3af;
        }
        footer strong {
            color: #4a0e63;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 0.25rem;
            }
            .main-content {
                padding: 0.5rem;
            }
            .page-header h2 {
                font-size: 1.5rem;
            }
            .nav-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 0.1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-title">
            <h1><i class="fas fa-spa"></i> IDO KOZMETIK</h1>
        </div>
        <div class="header-user">
            <span><i class="fas fa-user-circle"></i> <?php echo $kullanici_adi; ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h2>Yönetim Paneli</h2>
            <p>Sistem modüllerine hızlı erişim için aşağıdaki kartları kullanın.</p>
        </div>

        <div class="category-divider">
            <h3>İnsan Kaynakları ve İlişkiler</h3>
        </div>
        <div class="nav-grid">
            <a href="musteriler.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                <p class="card-title">Müşteriler</p>
            </a>
            <a href="personeller.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-id-card"></i></div>
                <p class="card-title">Personeller</p>
            </a>
            <a href="tedarikciler.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-truck"></i></div>
                <p class="card-title">Tedarikçiler</p>
            </a>
        </div>

        <div class="category-divider">
            <h3>Ürün ve Malzeme Yönetimi</h3>
        </div>
        <div class="nav-grid">
            <a href="urunler.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-box"></i></div>
                <p class="card-title">Ürünler</p>
            </a>
            <a href="esanslar.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-vial"></i></div>
                <p class="card-title">Esanslar</p>
            </a>
            <a href="malzemeler.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-cubes"></i></div>
                <p class="card-title">Malzemeler</p>
            </a>
            <a href="urun_agaclari.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-sitemap"></i></div>
                <p class="card-title">Ürün Ağaçları</p>
            </a>
        </div>

        <div class="category-divider">
            <h3>Operasyonel İşlemler</h3>
        </div>
        <div class="nav-grid">
            <a href="musteri_siparisleri.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-shopping-cart"></i></div>
                <p class="card-title">Müşteri Siparişleri</p>
            </a>
            <a href="esans_is_emirleri.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-clipboard-list"></i></div>
                <p class="card-title">Esans İş Emirleri</p>
            </a>
            <a href="montaj_is_emirleri.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-clipboard-list"></i></div>
                <p class="card-title">Montaj İş Emirleri</p>
            </a>

            <a href="manuel_stok_hareket.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-exchange-alt"></i></div>
                <p class="card-title">Stok Hareketleri</p>
            </a>
        </div>

        <div class="category-divider">
            <h3>Sistem Altyapısı</h3>
        </div>
        <div class="nav-grid">
            <a href="lokasyonlar.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-map-marker-alt"></i></div>
                <p class="card-title">Lokasyonlar</p>
            </a>
            <a href="tanklar.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-database"></i></div>
                <p class="card-title">Tanklar</p>
            </a>
            <a href="is_merkezleri.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-industry"></i></div>
                <p class="card-title">İş Merkezleri</p>
            </a>
        </div>

        <div class="category-divider">
            <h3>Finans ve Raporlama</h3>
        </div>
        <div class="nav-grid">
            <a href="gider_yonetimi.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-money-bill-wave"></i></div>
                <p class="card-title">Gider Yönetimi</p>
            </a>
            <a href="cerceve_sozlesmeler.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-file-contract"></i></div>
                <p class="card-title">Sözleşmeler</p>
            </a>
        </div>

        <div class="category-divider">
            <h3>Sistem Ayarları</h3>
        </div>
        <div class="nav-grid">
            <a href="change_password.php" class="nav-card">
                <div class="icon-wrapper"><i class="fas fa-key"></i></div>
                <p class="card-title">Şifre Değiştir</p>
            </a>
        </div>

    </main>

    <footer>
        <p><strong style="font-family: 'Ubuntu', sans-serif;">IDO KOZMETIK</strong> © <?php echo date('Y'); ?></p>
    </footer>
</body>
</html>
