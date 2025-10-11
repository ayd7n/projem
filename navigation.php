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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .hero.is-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .main-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -3rem;
            position: relative;
            z-index: 10;
        }
        
        .page-title {
            color: #363636;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .page-subtitle {
            color: #7a7a7a;
            margin-bottom: 2rem;
        }
        
        .nav-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #f5f5f5;
        }
        
        .nav-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
            border-color: transparent;
        }
        
        .nav-card a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #363636;
            padding: 1.5rem 1rem;
            min-height: 140px;
        }
        
        .nav-card .icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .nav-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }
        
        .nav-card .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            line-height: 1.3;
            color: #363636;
        }
        
        .nav-card:hover .card-title {
            color: #667eea;
        }
        
        /* Kategori renkleri */
        .category-people .icon-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .category-product .icon-wrapper {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .category-operation .icon-wrapper {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .category-system .icon-wrapper {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .category-finance .icon-wrapper {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .category-special .icon-wrapper {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        }
        
        .nav-card .fa {
            color: white;
            font-size: 1.8rem;
        }
        
        .category-divider {
            margin: 2rem 0 1.5rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #f5f5f5;
        }
        
        .category-divider .title {
            color: #667eea;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .category-divider .subtitle {
            color: #7a7a7a;
            font-size: 0.9rem;
        }
        
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            margin-top: 2rem;
        }
        
        .info-box strong {
            color: white;
        }
        
        .welcome-badge {
            background-color: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .logout-btn {
            background-color: rgba(255,255,255,0.2);
            border-color: white;
            color: white;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: white;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        @media screen and (max-width: 768px) {
            .main-container {
                margin-top: -2rem;
                padding: 1.5rem 1rem;
                border-radius: 10px;
            }
            
            .nav-card a {
                padding: 1.25rem 0.75rem;
                min-height: 120px;
            }
            
            .nav-card .icon-wrapper {
                width: 60px;
                height: 60px;
            }
            
            .nav-card .fa {
                font-size: 1.5rem;
            }
            
            .nav-card .card-title {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <div>
                                <h1 class="title has-text-white mb-1">
                                    <i class="fas fa-spa mr-2"></i>Scent ERP
                                </h1>
                                <p class="subtitle is-6 has-text-white-ter">Entegre Yönetim Sistemi</p>
                            </div>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="welcome-badge">
                                <span class="icon-text">
                                    <span class="icon has-text-white">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <span class="has-text-white"><strong><?php echo $kullanici_adi; ?></strong></span>
                                </span>
                            </div>
                        </div>
                        <div class="level-item">
                            <a href="logout.php" class="button logout-btn">
                                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                                <span>Çıkış Yap</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 4rem;">
        <div class="container">
            <div class="main-container">
                <h2 class="title is-2 has-text-centered page-title">
                    <i class="fas fa-th-large has-text-primary mr-2"></i>Yönetim Paneli
                </h2>
                <p class="subtitle is-5 has-text-centered page-subtitle">
                    Sistem modüllerine hızlı erişim için aşağıdaki kartları kullanın. Her modül özel olarak optimize edilmiş yönetim araçları sunar.
                </p>

                <!-- İnsan Kaynakları ve İlişkiler -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-users-cog mr-2"></i>İnsan Kaynakları ve İlişkiler
                    </h3>
                    <p class="subtitle is-6">Müşteriler, personel ve tedarikçi yönetimi</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-people">
                            <a href="musteriler.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <p class="card-title">Müşteriler</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-people">
                            <a href="personeller.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-id-card fa-2x"></i>
                                </div>
                                <p class="card-title">Personeller</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-people">
                            <a href="tedarikciler.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-truck fa-2x"></i>
                                </div>
                                <p class="card-title">Tedarikçiler</p>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Ürün ve Malzeme Yönetimi -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-boxes mr-2"></i>Ürün ve Malzeme Yönetimi
                    </h3>
                    <p class="subtitle is-6">Ürünler, esanslar, malzemeler ve ürün ağaçları</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-product">
                            <a href="urunler.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                                <p class="card-title">Ürünler</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-product">
                            <a href="esanslar.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-vial fa-2x"></i>
                                </div>
                                <p class="card-title">Esanslar</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-product">
                            <a href="malzemeler.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-cubes fa-2x"></i>
                                </div>
                                <p class="card-title">Malzemeler</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-product">
                            <a href="urun_agaclari.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-sitemap fa-2x"></i>
                                </div>
                                <p class="card-title">Ürün Ağaçları</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Operasyonel İşlemler -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-cogs mr-2"></i>Operasyonel İşlemler
                    </h3>
                    <p class="subtitle is-6">Siparişler, üretim ve kalite kontrol süreçleri</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-operation">
                            <a href="musteri_siparisleri.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                                <p class="card-title">Müşteri Siparişleri</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-operation">
                            <a href="esans_is_emirleri.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                                <p class="card-title">Esans İş Emirleri</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-operation">
                            <a href="giris_kalite_kontrolu.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-clipboard-check fa-2x"></i>
                                </div>
                                <p class="card-title">Kalite Kontrolü</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-operation">
                            <a href="manuel_stok_hareket.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-exchange-alt fa-2x"></i>
                                </div>
                                <p class="card-title">Stok Hareketleri</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sistem Altyapısı -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-network-wired mr-2"></i>Sistem Altyapısı
                    </h3>
                    <p class="subtitle is-6">Lokasyonlar, tanklar ve iş merkezleri yönetimi</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-system">
                            <a href="lokasyonlar.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                                <p class="card-title">Lokasyonlar</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-system">
                            <a href="tanklar.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-database fa-2x"></i>
                                </div>
                                <p class="card-title">Tanklar</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-system">
                            <a href="is_merkezleri.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-industry fa-2x"></i>
                                </div>
                                <p class="card-title">İş Merkezleri</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Finans ve Raporlama -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-chart-bar mr-2"></i>Finans ve Raporlama
                    </h3>
                    <p class="subtitle is-6">Mali işlemler ve sistem analizleri</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-finance">
                            <a href="gider_yonetimi.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                                <p class="card-title">Gider Yönetimi</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-finance">
                            <a href="cerceve_sozlesmeler.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-file-contract fa-2x"></i>
                                </div>
                                <p class="card-title">Sözleşmeler</p>
                            </a>
                        </div>
                    </div>
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-finance">
                            <a href="check_stats.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                                <p class="card-title">İstatistikler</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sistem Ayarları -->
                <div class="category-divider">
                    <h3 class="title is-4">
                        <i class="fas fa-cog mr-2"></i>Sistem Ayarları
                    </h3>
                    <p class="subtitle is-6">Kullanıcı tercihleri ve güvenlik ayarları</p>
                </div>
                
                <div class="columns is-multiline is-mobile">
                    <div class="column is-half-mobile is-one-third-tablet is-one-quarter-desktop">
                        <div class="nav-card category-special">
                            <a href="change_password.php">
                                <div class="icon-wrapper">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                                <p class="card-title">Şifre Değiştir</p>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="info-box has-text-centered">
                    <p class="is-size-5 mb-2">
                        <i class="fas fa-info-circle mr-2"></i><strong>Bilgilendirme</strong>
                    </p>
                    <p>
                        Bu panel üzerinden sistemin tüm temel yönetim işlemlerine erişebilirsiniz. 
                        Her modül kategorize edilmiş ve renk kodlamasıyla kolayca ayırt edilebilir şekilde düzenlenmiştir. 
                        İhtiyacınıza göre ilgili modülü seçerek işlemlerinize başlayabilirsiniz.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer has-text-centered" style="background: transparent; padding: 2rem 1.5rem;">
        <div class="content has-text-white">
            <p>
                <strong class="has-text-white">Scent ERP</strong> © <?php echo date('Y'); ?>
            </p>
        </div>
    </footer>
</body>
</html>
