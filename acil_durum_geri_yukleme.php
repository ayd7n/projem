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
    <title>Acil Durum Geri Yükleme - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --danger: #dc3545;
            --danger-bg: #f8d7da;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-primary: #212529;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
        }
        .navbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
        }
        .navbar-brand {
            color: #d4af37 !important;
            font-weight: 700;
        }
        .main-content {
            padding: 2rem;
            max-width: 960px;
            margin: 2rem auto;
        }
        .page-header h1 {
            font-weight: 700;
            color: var(--danger);
        }
        .info-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--danger);
        }
        .info-card h3 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .credential-box {
            background: var(--danger-bg);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--danger);
            margin-top: 1.5rem;
        }
        .credential-label {
            font-weight: 600;
            color: #58151c;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            background: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            color: var(--danger);
            border: 1px solid var(--danger-bg);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.5rem;
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
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <a href="ayarlar.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Ayarlara Dön
        </a>

        <div class="page-header">
            <h1><i class="fas fa-bomb"></i> Acil Durum Geri Yükleme</h1>
            <p class="text-muted">Sistemi en son yedekten geri yükleyen özel kullanıcı bilgileri.</p>
        </div>

        <div class="info-card">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> ÇOK ÖNEMLİ UYARI</h3>
            <p>
                Aşağıdaki kimlik bilgileri, <strong>herhangi bir onay istemeden</strong> sistemi bilinen en son yedeğe geri yüklemek için kullanılır.
            </p>
            <p>
                Bu bilgilerle giriş yapıldığında, son yedekten bu yana yapılmış olan <strong>TÜM DEĞİŞİKLİKLER (yeni siparişler, müşteri güncellemeleri, ürün eklemeleri vb.) KALICI OLARAK SİLİNECEKTİR.</strong>
            </p>
            <p class="font-weight-bold">
                Bu özellik sadece ve sadece sistemin kullanılamaz hale geldiği ve başka bir çözümün kalmadığı kritik acil durumlarda kullanılmalıdır.
            </p>
            
            <div class="credential-box">
                <p class="credential-label">Kullanıcı Adı:</p>
                <p class="credential-value">restore@sistem.com</p>
                <hr>
                <p class="credential-label">Şifre:</p>
                <p class="credential-value">_!ERp*R3sT0rE_99!</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
