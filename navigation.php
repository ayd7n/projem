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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color-start: #0f0c29;
            --bg-color-mid: #302b63;
            --bg-color-end: #24243e;
            --glass-bg: rgba(20, 15, 40, 0.65);
            --accent-color: #a27cf2;
            --glow-color: rgba(162, 124, 242, 0.5);
            --text-color: #e0d9f5;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --border-color: rgba(162, 124, 242, 0.2);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-color);
            background: linear-gradient(to bottom, var(--bg-color-start), var(--bg-color-mid), var(--bg-color-end));
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
            overflow-x: hidden;
        }

        #sky-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4rem;
            padding: 1rem 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--text-color);
        }
        .logo .fa-spa {
            color: var(--accent-color);
            filter: drop-shadow(0 0 10px var(--glow-color));
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .user-info {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.9;
        }

        .user-controls a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 1.4rem; /* Larger icons for desktop */
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .user-controls a .link-text {
            display: none; /* Hide text on desktop */
        }

        .user-controls a:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        .hamburger-menu {
            display: none;
            font-size: 1.8rem;
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            z-index: 101;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }

        .page-header p {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto;
        }

        .module-category h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2.5rem;
        }

        .module-card {
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            text-decoration: none;
            color: var(--text-color);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 30px var(--glow-color);
            border-color: var(--accent-color);
        }

        .module-card .icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--accent-color);
            filter: drop-shadow(0 0 10px var(--glow-color));
            transition: transform 0.3s ease;
        }
        
        .module-card:hover .icon {
            transform: scale(1.1);
        }

        .module-card .title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .module-card .description {
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.5;
        }

        footer {
            text-align: center;
            margin-top: 6rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            opacity: 0.7;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .top-bar { flex-wrap: wrap; }
            .user-controls {
                display: none;
                flex-direction: column;
                gap: 0;
                width: 100%;
                text-align: left;
                margin-top: 1rem;
                background: rgba(15, 12, 41, 0.9);
                padding: 1rem 0;
                border-radius: 10px;
            }
            .user-controls.menu-open {
                display: flex;
            }
            .user-controls .user-info {
                padding: 0.75rem 1.5rem;
                font-size: 1.1rem;
                border-bottom: 1px solid var(--border-color);
                margin-bottom: 0.5rem;
            }
            .user-controls a {
                display: flex;
                align-items: center;
                gap: 1rem;
                font-size: 1.1rem;
                padding: 0.75rem 1.5rem;
                width: 100%;
                box-sizing: border-box;
            }
            .user-controls a .link-text {
                display: inline;
            }
            .hamburger-menu { display: block; }
            .page-header h1 { font-size: 2.8rem; }
            .page-header p { font-size: 1.3rem; }
            .module-category h3 { font-size: 1.5rem; }
        }

    </style>
</head>
<body>
    <canvas id="sky-canvas"></canvas>

    <div class="container">
        <header class="top-bar">
            <div class="logo">
                <h1><i class="fas fa-spa"></i>IDO KOZMETIK</h1>
            </div>
            <button class="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-controls">
                <span class="user-info">Hoş geldin, <strong><?php echo $kullanici_adi; ?></strong></span>
                <a href="change_password.php" title="Şifre Değiştir"><i class="fas fa-key"></i><span class="link-text">Şifre Değiştir</span></a>
                <a href="logout.php" title="Çıkış Yap"><i class="fas fa-sign-out-alt"></i><span class="link-text">Çıkış Yap</span></a>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('sky-canvas');
        if (!canvas) return; // Do nothing if canvas is not found
        const ctx = canvas.getContext('2d');

        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            initStars(); // Re-initialize stars on resize
        });

        // --- Starry Sky Animation ---
        let stars = [];
        let shootingStars = [];
        const numStars = 200;

        function initStars() {
            stars = [];
            for (let i = 0; i < numStars; i++) {
                stars.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    radius: Math.random() * 1.2 + 0.3,
                    alpha: Math.random() * 0.7 + 0.3,
                    twinkleSpeed: Math.random() * 0.01 + 0.005
                });
            }
        }

        function createShootingStar() {
            shootingStars.push({
                x: Math.random() * width,
                y: Math.random() * height * 0.2,
                len: Math.random() * 80 + 10,
                speed: Math.random() * 8 + 5,
                alpha: 1,
                angle: Math.PI / 4
            });
        }

        function draw() {
            if (!ctx) return;
            ctx.clearRect(0, 0, width, height);
            
            stars.forEach(star => {
                ctx.save();
                star.alpha += star.twinkleSpeed;
                if (star.alpha > 1) {
                    star.alpha = 1;
                    star.twinkleSpeed *= -1;
                } else if (star.alpha < 0.3) {
                    star.alpha = 0.3;
                    star.twinkleSpeed *= -1;
                }
                ctx.globalAlpha = star.alpha;
                ctx.fillStyle = 'white';
                ctx.beginPath();
                ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });

            shootingStars.forEach((ss, index) => {
                ctx.save();
                ctx.globalAlpha = ss.alpha;
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(ss.x, ss.y);
                ctx.lineTo(ss.x + ss.len * Math.cos(ss.angle), ss.y + ss.len * Math.sin(ss.angle));
                ctx.stroke();
                ctx.restore();

                ss.x += ss.speed * Math.cos(ss.angle);
                ss.y += ss.speed * Math.sin(ss.angle);
                ss.alpha -= 0.02;

                if (ss.alpha <= 0) {
                    shootingStars.splice(index, 1);
                }
            });
        }

        function animate() {
            draw();
            requestAnimationFrame(animate);
        }

        setInterval(createShootingStar, 3000);

        initStars();
        animate();
    });

    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger-menu');
    const userControls = document.querySelector('.user-controls');
    hamburger.addEventListener('click', () => {
        userControls.classList.toggle('menu-open');
    });
</script>
</body>
</html>
