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
if (!yetkisi_var('action:ayarlar:maintenance_mode')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honeypot Kullanıcı Bilgileri - Parfüm ERP</title>
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
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        .info-card h3 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .credential-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 1rem;
        }
        .credential-item {
            margin-bottom: 15px;
        }
        .credential-item:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }
        .credential-value {
            background: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: var(--text-primary);
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .copy-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: var(--transition);
        }
        .copy-btn:hover {
            background: var(--secondary);
        }
        .alert-warning {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
            border-radius: 8px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .back-btn:hover {
            color: var(--secondary);
            text-decoration: none;
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
        <div class="mb-3">
            <a href="ayarlar.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Ayarlara Dön
            </a>
        </div>

        <div class="page-header">
            <h1><i class="fas fa-user-secret"></i> Honeypot Kullanıcı Bilgileri</h1>
            <p class="text-muted">Güvenlik amaçlı fake kullanıcı bilgileri</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="info-card">
                    <h3><i class="fas fa-key"></i> Giriş Bilgileri</h3>
                    
                    <div class="credential-box">
                        <div class="credential-item">
                            <span class="credential-label">Kullanıcı Adı</span>
                            <div class="credential-value">
                                <span id="username-value">giris@sistem.com</span>
                                <button class="copy-btn" onclick="copyToClipboard('username-value', this)">
                                    <i class="fas fa-copy"></i> Kopyala
                                </button>
                            </div>
                        </div>
                        
                        <div class="credential-item">
                            <span class="credential-label">Şifre</span>
                            <div class="credential-value">
                                <span id="password-value">758236</span>
                                <button class="copy-btn" onclick="copyToClipboard('password-value', this)">
                                    <i class="fas fa-copy"></i> Kopyala
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Açıklama</h3>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Önemli Bilgi</h5>
                        <p class="mb-2">
                            Bu bilgilerle giriş yapıldığında kullanıcı <strong>dashboard.php</strong> sayfasına yönlendirilir 
                            ve sanki sistem bozukmuş gibi hatalı bir sayfa gösterilir.
                        </p>
                        <p class="mb-0">
                            <strong>Amaç:</strong> Yetkisiz erişim denemelerinde sistemi bozuk göstererek 
                            saldırganları yanıltmak ve gerçek sistem yapısını gizlemek.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function copyToClipboard(elementId, button) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(function() {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Kopyalandı!';
            button.style.background = '#28a745';
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.style.background = '';
            }, 2000);
        }).catch(function(err) {
            alert('Kopyalama başarısız: ' + err);
        });
    }
    </script>
</body>
</html>
