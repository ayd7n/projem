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
    <title>Scent ERP - Ana Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color-start: #fdfbfb;
            --bg-color-end: #ebedee;
            --card-bg-color: rgba(255, 255, 255, 0.85);
            --accent-color: #2c5d63; /* Deep Teal */
            --accent-dark: #21464a;
            --text-color: #2c3e50; /* Dark Slate Blue */
            --text-light: #7f8c8d; /* Greyish */
            --shadow-color: rgba(44, 93, 99, 0.1);
            --shadow-light: rgba(44, 93, 99, 0.05);
            --border-color: rgba(44, 93, 99, 0.2);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, var(--bg-color-start) 0%, var(--bg-color-end) 100%);
            color: var(--text-color);
            min-height: 100vh;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: var(--card-bg-color);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow-color);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            margin-bottom: 40px;
        }

        .header-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin: 0;
            color: var(--accent-color);
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-user span {
            font-weight: 500;
            font-size: 1.1rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 2px solid var(--accent-dark);
        }

        .logout-btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-color);
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 22px;
        }

        .nav-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            border-radius: 15px;
            box-shadow: 0 4px 15px var(--shadow-light), 0 1px 4px var(--shadow-light);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.35s cubic-bezier(.25,.8,.25,1);
            backdrop-filter: blur(5px);
        }

        .nav-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 10px 25px var(--shadow-color), 0 4px 10px var(--shadow-light);
            border-color: var(--accent-color);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            text-decoration: none;
            color: var(--text-color);
        }

        .nav-link i {
            font-size: 1.6rem;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border-radius: 50%;
            color: white;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-dark));
            flex-shrink: 0;
            transition: all 0.35s cubic-bezier(.25,.8,.25,1);
        }

        .nav-card:hover .nav-link i {
            transform: scale(1.15);
            box-shadow: 0 0 20px rgba(44, 93, 99, 0.4);
        }

        .nav-link h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
            .header-title h1 {
                font-size: 2rem;
            }
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="page-header">
            <div class="header-title">
                <h1>Scent ERP</h1>
            </div>
            <div class="header-user">
                <span>Hoş geldiniz, <strong><?php echo $kullanici_adi; ?></strong></span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                </a>
            </div>
        </header>

        <h2 class="section-title">Yönetim Paneli</h2>

        <div class="nav-grid">
            <div class="nav-card"><a href="musteriler.php" class="nav-link"><i class="fas fa-users"></i><h3>Müşteriler</h3></a></div>
            <div class="nav-card"><a href="personeller.php" class="nav-link"><i class="fas fa-id-card"></i><h3>Personeller</h3></a></div>
            <div class="nav-card"><a href="tedarikciler.php" class="nav-link"><i class="fas fa-truck"></i><h3>Tedarikçiler</h3></a></div>
            <div class="nav-card"><a href="urunler.php" class="nav-link"><i class="fas fa-box"></i><h3>Ürünler</h3></a></div>
            <div class="nav-card"><a href="esanslar.php" class="nav-link"><i class="fas fa-vial"></i><h3>Esanslar</h3></a></div>
            <div class="nav-card"><a href="malzemeler.php" class="nav-link"><i class="fas fa-cubes"></i><h3>Malzemeler</h3></a></div>
            <div class="nav-card"><a href="urun_agaclari.php" class="nav-link"><i class="fas fa-sitemap"></i><h3>Ürün Ağaçları</h3></a></div>
            <div class="nav-card"><a href="musteri_siparisleri.php" class="nav-link"><i class="fas fa-shopping-cart"></i><h3>Müşteri Siparişleri</h3></a></div>
            <div class="nav-card"><a href="esans_is_emirleri.php" class="nav-link"><i class="fas fa-clipboard-list"></i><h3>Esans İş Emirleri</h3></a></div>
            <div class="nav-card"><a href="giris_kalite_kontrolu.php" class="nav-link"><i class="fas fa-clipboard-check"></i><h3>Giriş Kalite Kontrolü</h3></a></div>
            <div class="nav-card"><a href="manuel_stok_hareket.php" class="nav-link"><i class="fas fa-exchange-alt"></i><h3>Stok Hareketleri</h3></a></div>
            <div class="nav-card"><a href="gider_yonetimi.php" class="nav-link"><i class="fas fa-money-bill-wave"></i><h3>Gider Yönetimi</h3></a></div>
            <div class="nav-card"><a href="lokasyonlar.php" class="nav-link"><i class="fas fa-map-marker-alt"></i><h3>Lokasyonlar</h3></a></div>
            <div class="nav-card"><a href="tanklar.php" class="nav-link"><i class="fas fa-database"></i><h3>Tanklar</h3></a></div>
            <div class="nav-card"><a href="is_merkezleri.php" class="nav-link"><i class="fas fa-industry"></i><h3>İş Merkezleri</h3></a></div>
            <div class="nav-card"><a href="cerceve_sozlesmeler.php" class="nav-link"><i class="fas fa-file-contract"></i><h3>Sözleşmeler</h3></a></div>
            <div class="nav-card"><a href="musteri_geri_bildirimleri.php" class="nav-link"><i class="fas fa-comments"></i><h3>Geri Bildirimler</h3></a></div>
            <div class="nav-card"><a href="check_stats.php" class="nav-link"><i class="fas fa-chart-line"></i><h3>İstatistikler</h3></a></div>
            <div class="nav-card"><a href="change_password.php" class="nav-link"><i class="fas fa-key"></i><h3>Şifre Değiştir</h3></a></div>
        </div>
    </div>
</body>
</html>
