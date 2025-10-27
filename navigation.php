<?php
include 'config.php';

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
    <title>Yönetim Paneli - IDO KOZMETIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --bg-main: #fdf8f5; /* Soft Cream */
            --bg-card: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-muted: #6b7280; /* Medium Gray */
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 10px 25px rgba(74, 14, 99, 0.15);
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            margin: 0;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .logo .fa-spa {
            color: var(--primary);
            margin-right: 0.5rem;
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-controls a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .user-controls a:hover {
            color: var(--primary);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .module-grid-container {
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }

        .module-category h3 {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .module-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .module-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
            transition: color 0.3s ease;
        }
        
        .module-card:hover .icon {
            color: var(--accent);
        }

        .module-card .title {
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        .module-card .description {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            line-height: 1.4;
        }

        footer {
            text-align: center;
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

    </style>
</head>
<body>

    <div class="container">
        <header class="top-bar">
            <div class="logo">
                <h1><i class="fas fa-spa"></i>IDO KOZMETIK</h1>
            </div>
            <div class="user-controls">
                <span class="user-info">Hoş geldin, <strong><?php echo $kullanici_adi; ?></strong></span>
                <a href="change_password.php" title="Şifre Değiştir"><i class="fas fa-key"></i></a>
                <a href="logout.php" title="Çıkış Yap"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <main>
            <div class="page-header">
                <h1>Yönetim Paneli</h1>
                <p>Sistem modüllerine erişmek için aşağıdaki bilgilendirici kartları kullanabilirsiniz.</p>
            </div>

            <div class="module-grid-container">
                <div class="module-category">
                    <h3><i class="fas fa-users-cog"></i> İlişkiler</h3>
                    <div class="module-grid">
                        <a href="musteriler.php" class="module-card"><div class="icon"><i class="fas fa-users"></i></div><span class="title">Müşteriler</span><p class="description">Müşteri kayıtlarını ve bilgilerini yönetin.</p></a>
                        <a href="personeller.php" class="module-card"><div class="icon"><i class="fas fa-id-card"></i></div><span class="title">Personeller</span><p class="description">Şirket personellerini görüntüleyin ve yönetin.</p></a>
                        <a href="tedarikciler.php" class="module-card"><div class="icon"><i class="fas fa-truck"></i></div><span class="title">Tedarikçiler</span><p class="description">Tedarikçi firmaları ve işlemlerini yönetin.</p></a>
                    </div>
                </div>

                <div class="module-category">
                    <h3><i class="fas fa-boxes-stacked"></i> Ürün Yönetimi</h3>
                    <div class="module-grid">
                        <a href="urunler.php" class="module-card"><div class="icon"><i class="fas fa-box"></i></div><span class="title">Ürünler</span><p class="description">Ürün kataloğunu ve stok durumunu yönetin.</p></a>
                        <a href="esanslar.php" class="module-card"><div class="icon"><i class="fas fa-vial"></i></div><span class="title">Esanslar</span><p class="description">Esans reçetelerini ve üretimini yönetin.</p></a>
                        <a href="malzemeler.php" class="module-card"><div class="icon"><i class="fas fa-cubes"></i></div><span class="title">Malzemeler</span><p class="description">Üretim ve diğer malzemeleri takip edin.</p></a>
                        <a href="urun_agaclari.php" class="module-card"><div class="icon"><i class="fas fa-sitemap"></i></div><span class="title">Ürün Ağaçları</span><p class="description">Ürün reçetelerini ve bileşenlerini oluşturun.</p></a>
                    </div>
                </div>

                <div class="module-category">
                    <h3><i class="fas fa-cogs"></i> Operasyonlar</h3>
                    <div class="module-grid">
                        <a href="musteri_siparisleri.php" class="module-card"><div class="icon"><i class="fas fa-shopping-cart"></i></div><span class="title">Müşteri Siparişleri</span><p class="description">Yeni siparişleri görüntüleyin ve yönetin.</p></a>
                        <a href="esans_is_emirleri.php" class="module-card"><div class="icon"><i class="fas fa-flask"></i></div><span class="title">Esans İş Emirleri</span><p class="description">Üretimdeki esans iş emirlerini takip edin.</p></a>
                        <a href="montaj_is_emirleri.php" class="module-card"><div class="icon"><i class="fas fa-industry"></i></div><span class="title">Montaj İş Emirleri</span><p class="description">Montaj ve dolum iş emirlerini yönetin.</p></a>
                        <a href="manuel_stok_hareket.php" class="module-card"><div class="icon"><i class="fas fa-exchange-alt"></i></div><span class="title">Stok Hareketleri</span><p class="description">Manuel stok giriş/çıkış işlemleri yapın.</p></a>
                    </div>
                </div>
                 <div class="module-category">
                    <h3><i class="fas fa-building"></i> Altyapı & Finans</h3>
                    <div class="module-grid">
                        <a href="lokasyonlar.php" class="module-card"><div class="icon"><i class="fas fa-map-marker-alt"></i></div><span class="title">Lokasyonlar</span><p class="description">Depo ve üretim lokasyonlarını tanımlayın.</p></a>
                        <a href="tanklar.php" class="module-card"><div class="icon"><i class="fas fa-database"></i></div><span class="title">Tanklar</span><p class="description">Üretim tanklarını ve kapasitelerini yönetin.</p></a>
                        <a href="is_merkezleri.php" class="module-card"><div class="icon"><i class="fas fa-warehouse"></i></div><span class="title">İş Merkezleri</span><p class="description">Üretim hatlarını ve iş istasyonlarını yönetin.</p></a>
                        <a href="gider_yonetimi.php" class="module-card"><div class="icon"><i class="fas fa-money-bill-wave"></i></div><span class="title">Gider Yönetimi</span><p class="description">Şirket giderlerini takip edin ve raporlayın.</p></a>
                        <a href="cerceve_sozlesmeler.php" class="module-card"><div class="icon"><i class="fas fa-file-contract"></i></div><span class="title">Sözleşmeler</span><p class="description">Müşteri ve tedarikçi sözleşmelerini yönetin.</p></a>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            IDO KOZMETIK © <?php echo date('Y'); ?> - Yönetim Paneli
        </footer>
    </div>

</body>
</html>