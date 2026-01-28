<?php
include 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['taraf'] === 'musteri') {
        header('Location: customer_panel.php');
    } else {
        header('Location: navigation.php');
    }
    exit;
}

$error_message = '';
$success_message = '';

// Check for restore status from URL
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'restored') {
        $success_message = 'Sistem en son yedekten başarıyla geri yüklendi.';
    } elseif ($_GET['status'] === 'restore_failed') {
        $error_message = 'Geri yükleme başarısız oldu. Yedek dosyası bulunamadı veya bozuk.';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check for fake/honeypot credentials (hardcoded, not in database)
    if ($username === 'giris@sistem.com' && $password === '758236') {
        // Redirect to fake error page to simulate broken system
        header('Location: dashboard.php');
        exit;
    }
    // EMERGENCY RESTORE: Check for emergency restore credentials
    else if ($username === 'restore@sistem.com' && $password === '_!ERp*R3sT0rE_99!') {
        $latest_backup = find_latest_backup();
        if ($latest_backup && restore_database($connection, $latest_backup)) {
            // Log this critical action
            log_islem($connection, 'SISTEM', 'Acil durum kullanıcısı ile en son yedekten geri yükleme yapıldı.', 'Kritik Eylem');
            header('Location: login.php?status=restored');
        } else {
            log_islem($connection, 'SISTEM', 'Acil durum kullanıcısı ile geri yükleme denendi ancak BAŞARISIZ oldu.', 'HATA');
            header('Location: login.php?status=restore_failed');
        }
        exit;
    }

    // First, check if it's a customer login
    $customer_query = "SELECT musteri_id as user_id, 'musteri' as taraf, musteri_adi as kullanici_adi, sistem_sifresi as sifre, giris_yetkisi 
                       FROM musteriler 
                       WHERE (e_posta = ? OR telefon = ?)";

    $customer_stmt = $connection->prepare($customer_query);
    $customer_stmt->bind_param('ss', $username, $username);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();

    if ($customer_result->num_rows > 0) {
        $customer = $customer_result->fetch_assoc();

        if (password_verify($password, $customer['sifre'])) {
            // Check if customer has login permission
            if ($customer['giris_yetkisi'] != 1) {
                $error_message = 'Giriş yetkiniz yok!';
            } else {
                $_SESSION['user_id'] = $customer['user_id'];
                $_SESSION['taraf'] = $customer['taraf'];
                $_SESSION['id'] = $customer['user_id'];
                $_SESSION['kullanici_adi'] = $customer['kullanici_adi'];
                $_SESSION['rol'] = 'musteri';

                // Log customer login
                log_islem($connection, $customer['kullanici_adi'], "Müşteri giriş yaptı (E-posta/Telefon: $username)", 'Giriş Yapıldı');

                header('Location: customer_panel.php');
                exit;
            }
        } else {
            $error_message = 'Hatalı şifre!';
        }
    } else {
        // Check if it's a staff login
        $staff_query = "SELECT personel_id as user_id, 'personel' as taraf, ad_soyad as kullanici_adi, sistem_sifresi as sifre 
                       FROM personeller 
                       WHERE (e_posta = ? OR telefon = ?)";

        $staff_stmt = $connection->prepare($staff_query);
        $staff_stmt->bind_param('ss', $username, $username);
        $staff_stmt->execute();
        $staff_result = $staff_stmt->get_result();

        if ($staff_result->num_rows > 0) {
            $staff = $staff_result->fetch_assoc();

            if (password_verify($password, $staff['sifre'])) {
                $_SESSION['user_id'] = $staff['user_id'];
                $_SESSION['taraf'] = $staff['taraf'];
                $_SESSION['id'] = $staff['user_id'];
                $_SESSION['kullanici_adi'] = $staff['kullanici_adi'];
                $_SESSION['email'] = $username; // Store email for admin check
                $_SESSION['rol'] = 'personel';
                $_SESSION['izinler'] = []; // Default to empty array

                // Set a flag for the admin user
                if ($username === 'admin@parfum.com' || $username === 'admin2@parfum.com') {
                    $_SESSION['is_admin'] = true;
                } else {
                    // For non-admin staff, load their specific permissions
                    $_SESSION['is_admin'] = false;
                    $izin_stmt = $connection->prepare("SELECT izin_anahtari FROM personel_izinleri WHERE personel_id = ?");
                    $izin_stmt->bind_param('i', $staff['user_id']);
                    $izin_stmt->execute();
                    $izin_result = $izin_stmt->get_result();
                    $izinler = [];
                    while ($row = $izin_result->fetch_assoc()) {
                        $izinler[] = $row['izin_anahtari'];
                    }
                    $_SESSION['izinler'] = $izinler;
                    $izin_stmt->close();
                }

                // Log staff login
                log_islem($connection, $staff['kullanici_adi'], "Personel giriş yaptı (E-posta/Telefon: $username)", 'Giriş Yapıldı');

                header('Location: navigation.php');
                exit;
            } else {
                $error_message = 'Hatalı şifre!';
            }
        } else {
            $error_message = 'Kullanıcı bulunamadı!';
        }

        $staff_stmt->close();
    }

    $customer_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0a0a0f">
    <title>IDO KOZMETIK - Parfümeri Yönetim Sistemi</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <!-- Animated Particles Background -->
    <div class="particles-container">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Floating Perfume Bottles Background -->
    <div class="floating-bottles">
        <div class="bottle bottle-1">
            <svg viewBox="0 0 60 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="bottle1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#c9a962;stop-opacity:0.3"/>
                        <stop offset="100%" style="stop-color:#8b7355;stop-opacity:0.15"/>
                    </linearGradient>
                </defs>
                <rect x="23" y="2" width="14" height="8" rx="2" fill="url(#bottle1)"/>
                <rect x="25" y="10" width="10" height="6" fill="url(#bottle1)"/>
                <path d="M20 16 L40 16 L44 28 L44 85 Q44 95 30 97 Q16 95 16 85 L16 28 Z" fill="url(#bottle1)" stroke="rgba(201,169,98,0.2)" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="bottle bottle-2">
            <svg viewBox="0 0 50 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="bottle2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#d4c4c8;stop-opacity:0.25"/>
                        <stop offset="100%" style="stop-color:#8b7b8b;stop-opacity:0.1"/>
                    </linearGradient>
                </defs>
                <circle cx="25" cy="6" r="5" fill="url(#bottle2)"/>
                <rect x="22" y="11" width="6" height="8" fill="url(#bottle2)"/>
                <ellipse cx="25" cy="55" rx="18" ry="30" fill="url(#bottle2)" stroke="rgba(212,196,200,0.15)" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="bottle bottle-3">
            <svg viewBox="0 0 55 95" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="bottle3" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#c9a962;stop-opacity:0.2"/>
                        <stop offset="100%" style="stop-color:#5a4a3a;stop-opacity:0.15"/>
                    </linearGradient>
                </defs>
                <rect x="22" y="0" width="11" height="10" rx="2" fill="url(#bottle3)"/>
                <path d="M20 10 L35 10 L35 20 L42 30 L42 80 L13 80 L13 30 L20 20 Z" fill="url(#bottle3)" stroke="rgba(201,169,98,0.15)" stroke-width="0.5"/>
            </svg>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="login-form-side">
            <div class="login-header">
                <!-- Ultra Premium Perfume Bottle Icon -->
                <div class="logo-container">
                    <svg class="perfume-icon" viewBox="0 0 80 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <!-- Diamond Gold Gradient -->
                            <linearGradient id="luxeGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#fff8e7"/>
                                <stop offset="30%" style="stop-color:#f0d890"/>
                                <stop offset="50%" style="stop-color:#d4af37"/>
                                <stop offset="70%" style="stop-color:#b8962b"/>
                                <stop offset="100%" style="stop-color:#8b7355"/>
                            </linearGradient>
                            
                            <!-- Crystal Facet Gradient -->
                            <linearGradient id="crystalFacet" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#4a3a55;stop-opacity:0.95"/>
                                <stop offset="40%" style="stop-color:#5d4a65;stop-opacity:0.8"/>
                                <stop offset="100%" style="stop-color:#2a2035;stop-opacity:0.98"/>
                            </linearGradient>
                            
                            <!-- Diamond Shine -->
                            <linearGradient id="diamondShine" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0"/>
                                <stop offset="50%" style="stop-color:#ffffff;stop-opacity:0.4"/>
                                <stop offset="100%" style="stop-color:#ffffff;stop-opacity:0"/>
                            </linearGradient>
                            
                            <!-- Liquid Gradient -->
                            <linearGradient id="perfumeLiquid" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#f5e6d3;stop-opacity:0.35"/>
                                <stop offset="100%" style="stop-color:#c9a962;stop-opacity:0.5"/>
                            </linearGradient>
                            
                            <!-- Glow Filter -->
                            <filter id="goldGlow" x="-50%" y="-50%" width="200%" height="200%">
                                <feGaussianBlur stdDeviation="1.5" result="glow"/>
                                <feMerge>
                                    <feMergeNode in="glow"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        
                        <!-- ===== ORNATE CAP ===== -->
                        <!-- Cap Crown -->
                        <polygon points="40,0 44,6 36,6" fill="url(#luxeGold)" filter="url(#goldGlow)"/>
                        <rect x="34" y="6" width="12" height="5" rx="1" fill="url(#luxeGold)"/>
                        
                        <!-- Cap Body -->
                        <rect x="32" y="11" width="16" height="7" rx="1.5" fill="url(#luxeGold)"/>
                        <line x1="34" y1="14.5" x2="46" y2="14.5" stroke="#fff8e7" stroke-width="0.4" opacity="0.6"/>
                        
                        <!-- ===== SPRAY MECHANISM ===== -->
                        <rect x="36" y="18" width="8" height="5" fill="url(#luxeGold)"/>
                        
                        <!-- ===== DECORATIVE COLLAR ===== -->
                        <path d="M28 23 L52 23 L55 28 L25 28 Z" fill="url(#luxeGold)" filter="url(#goldGlow)"/>
                        <line x1="30" y1="25.5" x2="50" y2="25.5" stroke="#fff8e7" stroke-width="0.3" opacity="0.5"/>
                        
                        <!-- ===== DIAMOND CUT NECK ===== -->
                        <path d="M30 28 L30 38 L27 42 L53 42 L50 38 L50 28" fill="url(#crystalFacet)" stroke="url(#luxeGold)" stroke-width="0.6"/>
                        <!-- Neck Facet Lines -->
                        <line x1="35" y1="28" x2="33" y2="42" stroke="rgba(255,255,255,0.08)" stroke-width="0.5"/>
                        <line x1="45" y1="28" x2="47" y2="42" stroke="rgba(255,255,255,0.08)" stroke-width="0.5"/>
                        
                        <!-- ===== MAIN DIAMOND BODY ===== -->
                        <!-- Faceted Diamond Shape -->
                        <path d="M25 42 
                                 L15 55 
                                 L12 80 
                                 L15 105 
                                 L25 118 
                                 L40 122 
                                 L55 118 
                                 L65 105 
                                 L68 80 
                                 L65 55 
                                 L55 42 Z" 
                              fill="url(#crystalFacet)" 
                              stroke="url(#luxeGold)" 
                              stroke-width="0.8"/>
                        
                        <!-- Diamond Facet Lines - Left -->
                        <path d="M25 42 L20 65 L15 55" stroke="rgba(255,255,255,0.1)" stroke-width="0.5" fill="none"/>
                        <path d="M20 65 L15 90 L12 80" stroke="rgba(255,255,255,0.08)" stroke-width="0.5" fill="none"/>
                        <path d="M15 90 L18 110 L15 105" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" fill="none"/>
                        <path d="M18 110 L28 120 L25 118" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" fill="none"/>
                        
                        <!-- Diamond Facet Lines - Right -->
                        <path d="M55 42 L60 65 L65 55" stroke="rgba(255,255,255,0.1)" stroke-width="0.5" fill="none"/>
                        <path d="M60 65 L65 90 L68 80" stroke="rgba(255,255,255,0.08)" stroke-width="0.5" fill="none"/>
                        <path d="M65 90 L62 110 L65 105" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" fill="none"/>
                        <path d="M62 110 L52 120 L55 118" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" fill="none"/>
                        
                        <!-- Center Facet Lines -->
                        <path d="M40 42 L40 122" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/>
                        <path d="M25 42 L40 80 L55 42" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" fill="none"/>
                        <path d="M25 118 L40 80 L55 118" stroke="rgba(255,255,255,0.04)" stroke-width="0.5" fill="none"/>
                        
                        <!-- Horizontal Facet Bands -->
                        <path d="M15 55 L65 55" stroke="rgba(255,255,255,0.06)" stroke-width="0.5"/>
                        <path d="M12 80 L68 80" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/>
                        <path d="M15 105 L65 105" stroke="rgba(255,255,255,0.04)" stroke-width="0.5"/>
                        
                        <!-- ===== PERFUME LIQUID ===== -->
                        <path d="M18 70 
                                 L15 90 
                                 L18 108 
                                 L28 117 
                                 L40 120 
                                 L52 117 
                                 L62 108 
                                 L65 90 
                                 L62 70 Z" 
                              fill="url(#perfumeLiquid)"/>
                        
                        <!-- ===== CRYSTAL REFLECTIONS ===== -->
                        <!-- Main Left Shine -->
                        <ellipse cx="22" cy="75" rx="3" ry="18" fill="rgba(255,255,255,0.15)"/>
                        <ellipse cx="24" cy="58" rx="2" ry="8" fill="rgba(255,255,255,0.12)"/>
                        
                        <!-- Right Edge Shine -->
                        <path d="M60 55 Q62 80 58 105" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" fill="none"/>
                        
                        <!-- Diamond Sparkles -->
                        <circle cx="20" cy="50" r="1.2" fill="#fff8e7" opacity="0.8"/>
                        <circle cx="60" cy="65" r="0.8" fill="#fff8e7" opacity="0.5"/>
                        <circle cx="40" cy="95" r="0.6" fill="#fff8e7" opacity="0.4"/>
                        <circle cx="25" cy="110" r="0.5" fill="#fff8e7" opacity="0.3"/>
                        <circle cx="55" cy="50" r="0.7" fill="#fff8e7" opacity="0.6"/>
                        
                        <!-- Top Shine Bar -->
                        <rect x="30" y="45" width="20" height="2" rx="1" fill="url(#diamondShine)"/>
                        
                        <!-- ===== GOLD RING ===== -->
                        <ellipse cx="40" cy="42" rx="16" ry="2.5" fill="none" stroke="url(#luxeGold)" stroke-width="1.2"/>
                        
                        <!-- ===== BOTTOM ACCENT ===== -->
                        <ellipse cx="40" cy="121" rx="10" ry="1.5" fill="url(#luxeGold)" opacity="0.4"/>
                    </svg>
                    <div class="logo-glow"></div>
                </div>
                
                <h1>IDO</h1>
                <h2>KOZMETIK</h2>
                <p class="tagline">Parfümeri Yönetim Sistemi</p>
            </div>

            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">E-posta veya Telefon</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                        placeholder="ornek@parfum.com">
                </div>

                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="btn">
                    <span>Giriş Yap</span>
                </button>
            </form>
            
            <div class="footer-accent">
                <div class="accent-line"></div>
            </div>
        </div>
    </div>

    <!-- VANTA.js Animated Background -->
    <div id="vanta-bg"></div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.fog.min.js"></script>
    <script>
        VANTA.FOG({
            el: "#vanta-bg",
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            highlightColor: 0xc9a962,
            midtoneColor: 0x2d1d35,
            lowlightColor: 0x14111c,
            baseColor: 0x0a0a0f,
            blurFactor: 0.5,
            speed: 1.2,
            zoom: 0.6
        });
    </script>
</body>

</html>