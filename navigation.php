<?php
include 'config.php';

// --- Maintenance Mode Check ---
// This must be after config.php is included and before any other output.
$maintenance_mode = get_setting($connection, 'maintenance_mode');
// Use the email from session for a reliable admin check
$is_admin = isset($_SESSION['email']) && ($_SESSION['email'] === 'admin@parfum.com' || $_SESSION['email'] === 'admin2@parfum.com');
$current_page = basename($_SERVER['PHP_SELF']);

if ($maintenance_mode === 'on' && !$is_admin && $current_page !== 'maintenance.php' && $current_page !== 'login.php') {
    header('Location: maintenance.php');
    exit;
}
// --- End Maintenance Mode Check ---

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
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #390b4d; /* Royal Plum - Deep, Premium from personeller.php */
            --secondary: #7c2a99; /* Amethyst - Vivid from personeller.php */
            --accent: #d4af37; /* Classic Gold - From personeller.php */
            --bg-color: #fdfbfd; /* Porcelain White - Warmth for plum */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #2d1b36; /* Deep Plum Black */
            --text-secondary: #6a4c7d; /* Soft Plum */
            --shadow-sm: 0 4px 12px rgba(57, 11, 77, 0.05);
            --shadow: 0 10px 30px rgba(57, 11, 77, 0.08);
            --shadow-hover: 0 20px 40px rgba(57, 11, 77, 0.12);
            --transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            --gradient-primary: linear-gradient(135deg, #390b4d, #7c2a99); /* Royal Plum to Amethyst */
        }

        body {
            font-family: 'Fira Sans', sans-serif;
            font-weight: 400;
            background-color: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 10% 20%, rgba(124, 42, 153, 0.03) 0%, rgba(57, 11, 77, 0.03) 90%);
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem; /* Reduced from 2rem */
            box-sizing: border-box;
        }

        .top-bar-wrapper {
            padding: 0.25rem 1rem; /* Ultra-thin navbar */
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 20px rgba(74, 14, 99, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .logo h1 {
            font-family: 'Fira Sans', sans-serif;
            font-weight: 700;
            font-style: normal;
            font-size: 1.4rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo .fa-spa {
            color: var(--accent);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .user-controls a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            transition: var(--transition);
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-controls a:hover {
            opacity: 1;
            transform: translateY(-2px);
            color: var(--accent);
        }

        .user-controls .link-text {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .hamburger-menu {
            display: none;
            font-size: 1.5rem;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            z-index: 1001;
            transition: var(--transition);
        }

        .hamburger-menu:hover {
            transform: scale(1.1);
        }

        main {
            padding: 1rem 0; /* Reduced from 2rem */
            flex-grow: 1;
        }

        .page-header {
            text-align: center;
            margin-bottom: 1rem; /* Reduced from 2rem */
            position: relative;
        }

        .page-header h1 {
            font-family: 'Fira Sans', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            font-style: normal;
            margin-bottom: 0.5rem;
            color: var(--primary);
            letter-spacing: 0;
            background: none; /* Removed gradient text for cleaner sharp look */
            -webkit-text-fill-color: initial;
            -webkit-background-clip: border-box;
        }

        .page-header p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .module-category {
            margin-bottom: 1rem; /* Reduced from 2rem */
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
        }

        .module-category:nth-child(1) {
            animation-delay: 0.1s;
        }

        .module-category:nth-child(2) {
            animation-delay: 0.2s;
        }

        .module-category:nth-child(3) {
            animation-delay: 0.3s;
        }

        .module-category:nth-child(4) {
            animation-delay: 0.4s;
        }

        .module-category:nth-child(5) {
            animation-delay: 0.5s;
        }

        .module-category:nth-child(6) {
            animation-delay: 0.6s;
        }

        .module-category:nth-child(7) {
            animation-delay: 0.7s;
        }

        .module-category h3 {
            font-family: 'Fira Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            padding-left: 0;
            border-left: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--secondary); /* Vivid Purple for Headers */
            background: none;
            padding: 0.5rem 0;
            border-radius: 0;
            border-bottom: 2px solid rgba(124, 42, 153, 0.15); /* Amethyst divider */
        }
        
        /* Gold dash/line next to category */
        .module-category h3::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background: linear-gradient(to right, var(--accent), transparent); 
            opacity: 0.5;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Slightly narrower for gallery feel */
            gap: 1rem;
        }

        .module-card {
            background: rgba(255, 255, 255, 0.8); /* Glassmorphism base */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            padding: 1.5rem 1rem; /* More vertical padding for gallery look */
            text-decoration: none;
            color: var(--text-primary);
            text-align: center; /* Center alignment */
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column; /* Stack vertically */
            align-items: center; /* Center horizontally */
            justify-content: center;
            gap: 0.8rem;
            position: relative;
            overflow: visible;
            z-index: 1;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(88, 15, 117, 0.02), rgba(26, 5, 37, 0.02));
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.15); /* Classic Gold Glow */
            border-color: var(--accent);
            background: #ffffff;
        }

        .module-card:hover::before {
            opacity: 1;
        }

        .module-card .icon {
            color: var(--primary); /* Solid Deep Indigo */
            background: transparent;
             /* Removed gradient text fill for professionalism */
            width: auto;
            height: auto;
            margin-bottom: 0.5rem; /* Spacing below icon */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transition);
            box-shadow: none; 
        }

        .module-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
            color: var(--accent); /* Change color on hover instead of shadow */
            box-shadow: none;
        }

        .module-card .card-content {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .module-card .card-content .title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            color: var(--primary);
            display: block;
            font-family: 'Fira Sans', sans-serif;
            letter-spacing: normal;
        }

        .module-card .card-content .description {
            font-size: 0.85rem; /* Ensure description is readable */
            color: var(--text-secondary);
            line-height: 1.4;
            margin: 0;
            font-weight: 400; /* Clearly regular weight */
        }

        footer {
            text-align: center;
            padding: 2rem;
            background-color: white;
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 992px) {
            .user-controls {
                display: none;
                position: absolute;
                top: 70px;
                right: 0.5rem;
                background: white;
                color: var(--text-primary);
                flex-direction: column;
                min-width: 250px;
                border-radius: 12px;
                box-shadow: var(--shadow);
                gap: 0;
                border: 1px solid var(--border-color);
                padding: 0.25rem;
            }

            .hamburger-menu {
                display: block;
            }

            .user-controls.menu-open {
                display: flex;
                animation: fadeIn 0.2s ease-out;
            }

            .user-controls .user-info {
                padding: 0.75rem 0.5rem;
                border-bottom: 1px solid var(--border-color);
                color: var(--text-primary);
                font-weight: 700;
                text-align: center;
                width: 100%;
                box-sizing: border-box;
            }

            .user-controls a {
                color: var(--text-secondary);
                font-size: 0.95rem;
                padding: 0.75rem 0.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                border-radius: 8px;
                width: 100%;
                box-sizing: border-box;
            }

            .user-controls a:hover {
                background-color: rgba(74, 14, 99, 0.05);
                color: var(--primary);
                transform: none;
                opacity: 1;
            }

            .user-controls a .link-text {
                display: inline;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.75rem;
            }

            .top-bar-wrapper {
                padding: 0.25rem 0.5rem; /* Ultra thin mobile */
            }

            .module-card {
                padding: 0.8rem;
            }

            .page-header h1 {
                font-size: 1.7rem;
            }

            .page-header {
                margin-bottom: 1rem;
            }

            .page-header p {
                font-size: 0.95rem;
            }

            main {
                padding: 1rem 0;
            }
        }

        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 0.6rem 0;
            z-index: 1000;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.08);
            justify-content: space-around;
            align-items: center;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-secondary);
            transition: var(--transition);
            padding: 0.2rem 0.5rem;
            border-radius: 8px;
            width: 19%;
            /* Adjusted to accommodate 5 items */
            min-width: 55px;
            text-align: center;
        }

        .nav-item i {
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
        }

        .nav-item .nav-text {
            font-size: 0.7rem;
            font-weight: 500;
        }

        .nav-item:hover,
        .nav-item.active {
            color: var(--secondary);
            background: rgba(124, 42, 153, 0.05);
        }

        .module-category {
            scroll-margin-top: 70px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .module-category.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .module-card {
            opacity: 0;
            transform: translateY(15px);
            transition: opacity 0.4s ease-out, transform 0.4s ease-out, var(--transition);
        }

        .module-card.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 992px) {
            .user-controls {
                display: none;
                position: absolute;
                top: 70px;
                right: 0.5rem;
                background: white;
                color: var(--text-primary);
                flex-direction: column;
                min-width: 250px;
                border-radius: 12px;
                box-shadow: var(--shadow);
                gap: 0;
                border: 1px solid var(--border-color);
                padding: 0.25rem;
            }

            .hamburger-menu {
                display: block;
            }

            .user-controls.menu-open {
                display: flex;
                animation: fadeIn 0.2s ease-out;
            }

            .user-controls .user-info {
                padding: 0.75rem 0.5rem;
                border-bottom: 1px solid var(--border-color);
                color: var(--text-primary);
                font-weight: 700;
                text-align: center;
                width: 100%;
                box-sizing: border-box;
            }

            .user-controls a {
                color: var(--text-secondary);
                font-size: 0.95rem;
                padding: 0.75rem 0.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                border-radius: 8px;
                width: 100%;
                box-sizing: border-box;
            }

            .user-controls a:hover {
                background-color: rgba(74, 14, 99, 0.05);
                color: var(--primary);
                transform: none;
                opacity: 1;
            }

            .user-controls a .link-text {
                display: inline;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.75rem;
            }

            .top-bar-wrapper {
                padding: 0.6rem 0.75rem;
            }

            .module-card {
                padding: 0.8rem;
            }

            .page-header h1 {
                font-size: 1.7rem;
            }

            .page-header {
                margin-bottom: 1rem;
            }

            .page-header p {
                font-size: 0.95rem;
            }

            main {
                padding: 1rem 0;
                padding-bottom: 70px;
                /* Space for bottom nav */
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 0.4rem;
            }

            .logo h1 {
                font-size: 1.1rem;
                gap: 0.4rem;
            }

            .module-grid {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }

            .module-card {
                flex-direction: row; /* Keep row on VERY small settings if needed, or column? Let's stick to column for consistency unless typically row is better. User asked for centered icons. */
                flex-direction: column;
                text-align: center;
                align-items: center;
                gap: 0.8rem;
                padding: 1rem;
                min-height: auto;
                justify-content: center;
                border-radius: 12px;
            }

            .module-card .icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                margin-bottom: 0;
                border-radius: 10px;
                flex-shrink: 0;
            }

            .module-card .card-content {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .module-card .card-content .title {
                font-size: 0.9rem;
                margin-bottom: 0.2rem;
                line-height: 1.3;
                font-weight: 600;
                width: auto;
            }

            .module-card .card-content .description {
                display: block;
                font-size: 0.75rem;
            }

            .module-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
                transition: all 0.3s ease;
                border: 1px solid var(--border-color);
                overflow: hidden;
            }

            .module-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
            }

            .module-card .icon {
                background: var(--gradient-primary);
                box-shadow: 0 4px 10px rgba(74, 14, 99, 0.2);
            }

            .module-card .card-content .title {
                color: var(--primary);
            }

            .module-card .card-content .description {
                color: var(--text-secondary);
                opacity: 0.8;
            }

            .module-category {
                margin-bottom: 1.2rem;
                background: var(--card-bg);
                border-radius: 12px;
                padding: 0.8rem;
                box-shadow: var(--shadow-sm);
            }

            .module-category h3 {
                justify-content: flex-start;
                border-left: none;
                background: none;
                margin: 0 0 0.8rem 0;
                padding: 0;
                font-size: 0.8rem;
                letter-spacing: 1.5px;
            }
            
            .module-category h3::after {
                display: none; /* Hide line on mobile for cleaner look */
            }

            .page-header {
                margin-bottom: 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
                margin-bottom: 0.4rem;
            }

            .page-header p {
                font-size: 0.9rem;
                line-height: 1.4;
            }

            main {
                padding: 0.8rem 0;
                padding-bottom: 70px;
                /* Space for bottom nav */
            }

            .top-bar-wrapper {
                padding: 0.5rem;
            }

            footer {
                padding: 1rem;
                font-size: 0.8rem;
            }

            /* Show mobile bottom nav on small screens */
            .mobile-bottom-nav {
                display: flex;
            }

            .hamburger-menu {
                position: relative;
                z-index: 1001;
            }

            /* Additional mobile optimizations for various screen sizes */
            .module-card .icon {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }
        }

        /* Extra small devices (phones, 320px and up) */
        @media (max-width: 320px) {
            .module-grid {
                grid-template-columns: 1fr;
            }

            .module-card .icon {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .module-card {
                padding: 0.6rem;
                gap: 0.6rem;
            }

            .module-card .card-content .title {
                font-size: 0.85rem;
            }

            .module-card .card-content .description {
                font-size: 0.7rem;
            }
        }

        /* Large mobile devices (landscape) */
        @media (min-width: 577px) and (max-width: 768px) {
            .module-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <header class="top-bar-wrapper">
        <div class="top-bar">
            <div class="logo">
                <h1><i class="fas fa-spa"></i>IDO KOZMETIK</h1>
            </div>
            <button class="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="user-controls">
                <span class="user-info">Hoş geldin, <strong><?php echo $kullanici_adi; ?></strong></span>
                <a href="change_password.php" title="Şifre Değiştir"><i class="fas fa-key fa-fw"></i><span
                        class="link-text">Şifre Değiştir</span></a>
                <a href="logout.php" title="Çıkış Yap"><i class="fas fa-sign-out-alt fa-fw"></i><span
                        class="link-text">Çıkış Yap</span></a>
            </nav>
        </div>
    </header>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="#relations" class="nav-item">
            <i class="fas fa-users-cog"></i>
            <span class="nav-text">İlişkiler</span>
        </a>
        <a href="#products" class="nav-item">
            <i class="fas fa-boxes-stacked"></i>
            <span class="nav-text">Ürünler</span>
        </a>
        <a href="#operations" class="nav-item">
            <i class="fas fa-cogs"></i>
            <span class="nav-text">İşlemler</span>
        </a>
        <a href="#reports" class="nav-item">
            <i class="fas fa-chart-pie"></i>
            <span class="nav-text">Raporlar</span>
        </a>
        <a href="logout.php" class="nav-item" title="Çıkış Yap">
            <i class="fas fa-sign-out-alt"></i>
            <span class="nav-text">Çıkış</span>
        </a>
    </nav>

    <div class="container">
        <main>


            <div class="module-grid-container">
                <div class="module-category" id="relations">
                    <h3><i class="fas fa-users-cog"></i> İlişkiler</h3>
                    <div class="module-grid">
                        <a href="musteriler.php" class="module-card">
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <div class="card-content">
                                <span class="title">Müşteriler</span>
                                <p class="description">Müşteri kayıtlarını ve bilgilerini yönetin.</p>
                            </div>
                        </a>

                        <a href="personeller.php" class="module-card">
                            <div class="icon"><i class="fas fa-id-card"></i></div>
                            <div class="card-content">
                                <span class="title">Personeller</span>
                                <p class="description">Şirket personellerini görüntüleyin ve yönetin.</p>
                            </div>
                        </a>
                        <a href="tedarikciler.php" class="module-card">
                            <div class="icon"><i class="fas fa-truck"></i></div>
                            <div class="card-content">
                                <span class="title">Tedarikçiler</span>
                                <p class="description">Tedarikçi firmaları ve işlemlerini yönetin.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="module-category" id="products">
                    <h3><i class="fas fa-boxes-stacked"></i> Ürün Yönetimi</h3>
                    <div class="module-grid">
                        <a href="urunler.php" class="module-card">
                            <div class="icon"><i class="fas fa-box"></i></div>
                            <div class="card-content">
                                <span class="title">Ürünler</span>
                                <p class="description">Ürün kataloğunu ve stok durumunu yönetin.</p>
                            </div>
                        </a>
                        <a href="esanslar.php" class="module-card">
                            <div class="icon"><i class="fas fa-vial"></i></div>
                            <div class="card-content">
                                <span class="title">Esanslar</span>
                                <p class="description">Esans reçetelerini ve üretimini yönetin.</p>
                            </div>
                        </a>
                        <a href="malzemeler.php" class="module-card">
                            <div class="icon"><i class="fas fa-cubes"></i></div>
                            <div class="card-content">
                                <span class="title">Malzemeler</span>
                                <p class="description">Üretim ve diğer malzemeleri takip edin.</p>
                            </div>
                        </a>
                        <a href="urun_agaclari.php" class="module-card">
                            <div class="icon"><i class="fas fa-sitemap"></i></div>
                            <div class="card-content">
                                <span class="title">Ürün Ağaçları</span>
                                <p class="description">Ürün reçetelerini ve bileşenlerini oluşturun.</p>
                            </div>
                        </a>
                        <a href="kokpit.php" class="module-card">
                            <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
                            <div class="card-content">
                                <span class="title">Kokpit</span>
                                <p class="description">Ürün bileşen durumlarını kontrol edin.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="module-category" id="operations">
                    <h3><i class="fas fa-cogs"></i> Operasyonlar</h3>
                    <div class="module-grid">
                        <a href="musteri_siparisleri.php" class="module-card">
                            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                            <div class="card-content">
                                <span class="title">Müşteri Siparişleri</span>
                                <p class="description">Yeni siparişleri görüntüleyin ve yönetin.</p>
                            </div>
                        </a>
                        <a href="esans_is_emirleri.php" class="module-card">
                            <div class="icon"><i class="fas fa-flask"></i></div>
                            <div class="card-content">
                                <span class="title">Esans İş Emirleri</span>
                                <p class="description">Üretimdeki esans iş emirlerini takip edin.</p>
                            </div>
                        </a>
                        <a href="montaj_is_emirleri.php" class="module-card">
                            <div class="icon"><i class="fas fa-industry"></i></div>
                            <div class="card-content">
                                <span class="title">Montaj İş Emirleri</span>
                                <p class="description">Montaj ve dolum iş emirlerini yönetin.</p>
                            </div>
                        </a>
                        <a href="manuel_stok_hareket.php" class="module-card">
                            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                            <div class="card-content">
                                <span class="title">Stok Hareketleri</span>
                                <p class="description">Manuel stok giriş/çıkış işlemleri yapın.</p>
                            </div>
                        </a>
                        <a href="satinalma_siparisler.php" class="module-card">
                            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                            <div class="card-content">
                                <span class="title">Satınalma Siparişleri</span>
                                <p class="description">Tedarikçilere verilen satınalma siparişlerini yönetin.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="module-category" id="infrastructure">
                    <h3><i class="fas fa-building"></i> Altyapı</h3>
                    <div class="module-grid">
                        <a href="lokasyonlar.php" class="module-card">
                            <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="card-content">
                                <span class="title">Lokasyonlar</span>
                                <p class="description">Depo ve üretim lokasyonlarını tanımlayın.</p>
                            </div>
                        </a>
                        <a href="tanklar.php" class="module-card">
                            <div class="icon"><i class="fas fa-database"></i></div>
                            <div class="card-content">
                                <span class="title">Tanklar</span>
                                <p class="description">Üretim tanklarını ve kapasitelerini yönetin.</p>
                            </div>
                        </a>
                        <a href="is_merkezleri.php" class="module-card">
                            <div class="icon"><i class="fas fa-warehouse"></i></div>
                            <div class="card-content">
                                <span class="title">İş Merkezleri</span>
                                <p class="description">Üretim hatlarını ve iş istasyonlarını yönetin.</p>
                            </div>
                        </a>
                    </div>
                </div>



                <div class="module-category" id="finance">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Finans</h3>
                    <div class="module-grid">
                        <a href="gelir_yonetimi.php" class="module-card">
                            <div class="icon"><i class="fas fa-coins"></i></div>
                            <div class="card-content">
                                <span class="title">Gelir Yönetimi</span>
                                <p class="description">Şirket gelirlerini ve tahsilatları takip edin.</p>
                            </div>
                        </a>
                        <a href="gider_yonetimi.php" class="module-card">
                            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                            <div class="card-content">
                                <span class="title">Gider Yönetimi</span>
                                <p class="description">Şirket giderlerini takip edin ve raporlayın.</p>
                            </div>
                        </a>
                        <?php if (yetkisi_var('page:view:gider_yonetimi')): ?>
                            <a href="kasa_yonetimi.php" class="module-card">
                                <div class="icon"><i class="fas fa-wallet"></i></div>
                                <div class="card-content">
                                    <span class="title">Kasa Yönetimi</span>
                                    <p class="description">Çoklu para birimi, çek kasası ve tüm finansal hareketleri yönetin.</p>
                                </div>
                            </a>
                        <?php endif; ?>
                        <a href="cerceve_sozlesmeler.php" class="module-card">
                            <div class="icon"><i class="fas fa-file-contract"></i></div>
                            <div class="card-content">
                                <span class="title">Sözleşmeler</span>
                                <p class="description">Müşteri ve tedarikçi sözleşmelerini yönetin.</p>
                            </div>
                        </a>
                        <?php if (yetkisi_var('page:view:personel_bordro')): ?>
                            <a href="personel_bordro.php" class="module-card">
                                <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                                <div class="card-content">
                                    <span class="title">Personel Bordro</span>
                                    <p class="description">Maaş ödemelerini ve avansları yönetin.</p>
                                </div>
                            </a>
                        <?php endif; ?>
                        <?php if (yetkisi_var('page:view:tekrarli_odemeler')): ?>
                            <a href="tekrarli_odemeler.php" class="module-card">
                                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="card-content">
                                    <span class="title">Tekrarlı Ödemeler</span>
                                    <p class="description">Kira, fatura ve aylık ödemeleri takip edin.</p>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>


            </div>

                <div class="module-category" id="system-reports">
                    <h3><i class="fas fa-cogs"></i> Sistem & Raporlama</h3>
                    <div class="module-grid">
                        <a href="raporlar.php" class="module-card">
                            <div class="icon"><i class="fas fa-chart-pie"></i></div>
                            <div class="card-content">
                                <span class="title">Raporlar</span>
                                <p class="description">Satış, stok ve maliyet raporlarını görüntüleyin.</p>
                            </div>
                        </a>
                        <a href="ayarlar.php" class="module-card">
                            <div class="icon"><i class="fas fa-cog"></i></div>
                            <div class="card-content">
                                <span class="title">Ayarlar</span>
                                <p class="description">Sistem genel ayarlarını ve yapılandırmasını yönetin.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer>
        IDO KOZMETIK © <?php echo date('Y'); ?> - Yönetim Paneli
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hamburger = document.querySelector('.hamburger-menu');
            const userControls = document.querySelector('.user-controls');
            const navItems = document.querySelectorAll('.nav-item');
            const moduleCategories = document.querySelectorAll('.module-category');

            // Hamburger menu functionality
            if (hamburger && userControls) {
                hamburger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userControls.classList.toggle('menu-open');
                });
            }

            document.addEventListener('click', (e) => {
                if (userControls && userControls.classList.contains('menu-open') && !userControls.contains(e.target) && !hamburger.contains(e.target)) {
                    userControls.classList.remove('menu-open');
                }
            });

            // Mobile bottom navigation functionality
            navItems.forEach(item => {
                // Check if the item is the logout button
                const isLogoutButton = item.getAttribute('href') === 'logout.php';

                item.addEventListener('click', function (e) {
                    if (isLogoutButton) {
                        // For logout button, allow the default behavior
                        return; // Allow default navigation to logout.php
                    }

                    e.preventDefault();

                    // Remove active class from all items
                    navItems.forEach(navItem => navItem.classList.remove('active'));

                    // Add active class to clicked item
                    this.classList.add('active');

                    // Get the target category ID
                    const targetId = this.getAttribute('href').substring(1);

                    // Scroll to the corresponding module category
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add animation to module cards on scroll
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');

                        // Animate child module cards with a delay
                        const moduleCards = entry.target.querySelectorAll('.module-card');
                        moduleCards.forEach((card, index) => {
                            setTimeout(() => {
                                card.classList.add('animate-in');
                            }, 100 * index);
                        });

                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe module categories
            moduleCategories.forEach(category => {
                observer.observe(category);
            });

            // Add swipe detection for mobile
            let touchStartX = 0;
            let touchEndX = 0;

            document.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            });

            document.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });

            function handleSwipe() {
                const swipeThreshold = 50;

                if (touchStartX - touchEndX > swipeThreshold) {
                    // Swipe left - next category
                    const activeIndex = Array.from(navItems).findIndex(item => item.classList.contains('active'));
                    if (activeIndex < navItems.length - 1) {
                        navItems[activeIndex + 1].click();
                    }
                } else if (touchEndX - touchStartX > swipeThreshold) {
                    // Swipe right - previous category
                    const activeIndex = Array.from(navItems).findIndex(item => item.classList.contains('active'));
                    if (activeIndex > 0) {
                        navItems[activeIndex - 1].click();
                    }
                }
            }
        });
    </script>
</body>

</html>