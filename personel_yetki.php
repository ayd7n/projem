<?php
include 'config.php';
include 'includes/permissions_list.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // A non-admin might have the permission to view this page, so we use the helper function.
    // For now, let's restrict it to super-admin only for simplicity.
    // In the future, we could use: if (!yetkisi_var('action:personeller:permissions')) { ... }
    header('Location: navigation.php');
    exit;
}

// Get personel ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Geçersiz personel ID.');
}
$personel_id = intval($_GET['id']);

// Fetch personel details
$stmt = $connection->prepare("SELECT ad_soyad FROM personeller WHERE personel_id = ?");
$stmt->bind_param('i', $personel_id);
$stmt->execute();
$result = $stmt->get_result();
$personel = $result->fetch_assoc();
$stmt->close();

if (!$personel) {
    die('Personel bulunamadı.');
}

// Fetch current permissions for this personel
$current_permissions_stmt = $connection->prepare("SELECT izin_anahtari FROM personel_izinleri WHERE personel_id = ?");
$current_permissions_stmt->bind_param('i', $personel_id);
$current_permissions_stmt->execute();
$current_permissions_result = $current_permissions_stmt->get_result();
$current_permissions = [];
while ($row = $current_permissions_result->fetch_assoc()) {
    $current_permissions[] = $row['izin_anahtari'];
}
$current_permissions_stmt->close();

// Get all possible permissions
$all_permissions = get_all_permissions();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo htmlspecialchars($personel['ad_soyad']); ?> - Yetki Yönetimi</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5; /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-secondary: #6b7280; /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        html {
            font-size: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }
        .main-content {
            padding: 20px;
        }
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.825rem;
        }
        .btn:hover {
             transform: translateY(-2px);
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--secondary);
            box-shadow: 0 10px 20px rgba(74, 14, 99, 0.2);
        }
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 5px solid;
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-color: #f56565;
        }
        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            border-color: #48bb78;
        }
        .custom-control-label { user-select: none; }
        .permission-group { margin-bottom: 25px; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
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
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Yetki Yönetimi</h1>
                <p>Personel: <strong><?php echo htmlspecialchars($personel['ad_soyad']); ?></strong></p>
            </div>
        </div>

        <div id="alert-container"></div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-shield-alt"></i> Yetki Ayarları</h2>
                <a href="personeller.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>
            <div class="card-body">
                <form id="permissions-form">
                    <input type="hidden" name="personel_id" value="<?php echo $personel_id; ?>">
                    <input type="hidden" name="action" value="update_permissions">

                    <?php foreach ($all_permissions as $group_name => $permissions): ?>
                        <div class="card permission-group">
                            <div class="card-header">
                                <h3 class="mb-0"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($group_name); ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($permissions as $key => $label): ?>
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="<?php echo $key; ?>" name="permissions[]" value="<?php echo $key; ?>" <?php echo in_array($key, $current_permissions) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="<?php echo $key; ?>">
                                                    <?php echo htmlspecialchars($label); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Yetkileri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#permissions-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var submitButton = $(this).find('button[type="submit"]');
            submitButton.prop('disabled', true).html('<span class="d-flex align-items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Kaydediliyor...</span>');

            $.ajax({
                url: 'api_islemleri/personeller_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    var alertType = response.status === 'success' ? 'alert-success' : 'alert-danger';
                    var alertHtml = '<div class="alert ' + alertType + ' alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('#alert-container').html(alertHtml);
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                },
                error: function() {
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                    'Yetkiler güncellenirken bir sunucu hatası oluştu.' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('#alert-container').html(alertHtml);
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html('<span class="d-flex align-items-center"><i class="fas fa-save mr-2"></i> Yetkileri Kaydet</span>');
                }
            });
        });
    });
    </script>
</body>
</html>