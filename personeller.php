<?php
include 'config.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Define the protected user
define('PROTECTED_USER_NAME', 'Admin User');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = $_POST['personel_id'] ?? null;
    $is_protected = false;

    if ($target_id) {
        $check_stmt = $connection->prepare("SELECT ad_soyad FROM personeller WHERE personel_id = ?");
        $check_stmt->bind_param('i', $target_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $user_to_check = $result->fetch_assoc();
            if ($user_to_check['ad_soyad'] === PROTECTED_USER_NAME) {
                $is_protected = true;
            }
        }
        $check_stmt->close();
    }

    // CREATE
    if (isset($_POST['create'])) {
        // Hash the default password
        $default_password = password_hash('12345', PASSWORD_DEFAULT);
        $query = "INSERT INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar, sistem_sifresi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssssssss', $_POST['ad_soyad'], $_POST['tc_kimlik_no'], $_POST['dogum_tarihi'], $_POST['ise_giris_tarihi'], $_POST['pozisyon'], $_POST['departman'], $_POST['e_posta'], $_POST['telefon'], $_POST['adres'], $_POST['notlar'], $default_password);
        if ($stmt->execute()) {
            $message = "Personel başarıyla oluşturuldu.";
        } else {
            $error = "Hata: " . $stmt->error;
        }
        $stmt->close();
    } 
    // UPDATE
    elseif (isset($_POST['update'])) {
        if ($is_protected) {
            $error = PROTECTED_USER_NAME . " kaydı güncellenemez.";
        } else {
            // Update password if provided
            if (!empty($_POST['sifre'])) {
                $hashed_password = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
                $query = "UPDATE personeller SET ad_soyad = ?, tc_kimlik_no = ?, dogum_tarihi = ?, ise_giris_tarihi = ?, pozisyon = ?, departman = ?, e_posta = ?, telefon = ?, adres = ?, notlar = ?, sistem_sifresi = ? WHERE personel_id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('sssssssssssi', $_POST['ad_soyad'], $_POST['tc_kimlik_no'], $_POST['dogum_tarihi'], $_POST['ise_giris_tarihi'], $_POST['pozisyon'], $_POST['departman'], $_POST['e_posta'], $_POST['telefon'], $_POST['adres'], $_POST['notlar'], $hashed_password, $target_id);
            } else {
                $query = "UPDATE personeller SET ad_soyad = ?, tc_kimlik_no = ?, dogum_tarihi = ?, ise_giris_tarihi = ?, pozisyon = ?, departman = ?, e_posta = ?, telefon = ?, adres = ?, notlar = ? WHERE personel_id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssssssssssi', $_POST['ad_soyad'], $_POST['tc_kimlik_no'], $_POST['dogum_tarihi'], $_POST['ise_giris_tarihi'], $_POST['pozisyon'], $_POST['departman'], $_POST['e_posta'], $_POST['telefon'], $_POST['adres'], $_POST['notlar'], $target_id);
            }
            if ($stmt->execute()) {
                $message = "Personel bilgileri başarıyla güncellendi.";
            } else {
                $error = "Hata: " . $stmt->error;
            }
            $stmt->close();
        }
    } 
    // DELETE
    elseif (isset($_POST['delete'])) {
        if ($is_protected) {
            $error = PROTECTED_USER_NAME . " kaydı silinemez.";
        } else {
            $query = "DELETE FROM personeller WHERE personel_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $target_id);
            if ($stmt->execute()) {
                $message = "Personel başarıyla silindi.";
            } else {
                $error = "Hata: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch data for editing
$edit_employee = null;
$is_editing_protected = false;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM personeller WHERE personel_id = ?";
    $stmt = $connection->prepare($edit_query);
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_employee = $result->fetch_assoc();
    if ($edit_employee && $edit_employee['ad_soyad'] === PROTECTED_USER_NAME) {
        $is_editing_protected = true;
    }
    $stmt->close();
}

// Fetch all employees
$employees_result = $connection->query("SELECT * FROM personeller ORDER BY ad_soyad ASC");

// Calculate total employees
$total_result = $connection->query("SELECT COUNT(*) as total FROM personeller");
$total_employees = $total_result->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Personel Yönetimi - Parfüm ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #1abc9c;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --bg-color: #f5f7fb;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #2c3e50;
            --text-secondary: #8492a6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .erp-container { min-height: 100vh; }

        /* Main Content */
        .main-content { padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .page-header p { color: var(--text-secondary); font-size: 1rem; }

        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); margin-bottom: 30px; overflow: hidden; }
        .card-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-header h2 { font-size: 1.2rem; font-weight: 600; }
        .card-body { padding: 20px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group input:disabled, .form-group select:disabled, .form-group textarea:disabled { background-color: #f1f3f5; cursor: not-allowed; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1; }

        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn:disabled { background-color: #bdc3c7; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn.disabled { background-color: #bdc3c7; cursor: not-allowed; pointer-events: none; }

        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-secondary { background-color: var(--text-secondary); color: white; }
        .btn-secondary:hover { background-color: var(--dark); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { background-color: #16a085; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-danger:hover { background-color: #c0392b; }

        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }
        .actions a, .actions button { padding: 8px 12px; }

        .stat-card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); padding: 25px; display: flex; align-items: center; }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }



    </style>
</head>
<body>
    <div class="erp-container">
        <!-- Main Content -->
        <div class="main-content">

            <div class="page-header">
                <h1>Personel Yönetimi</h1>
                <p>Yeni personel ekleyin ve mevcut personelleri yönetin.</p>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="stat-card" style="margin-bottom: 30px;">
                <div class="stat-icon" style="background: var(--primary)">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_employees; ?></h3>
                    <p>Toplam Personel</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo $edit_employee ? 'Personel Bilgilerini Düzenle' : 'Yeni Personel Ekle'; ?></h2>
                </div>
                <div class="card-body">
                    <?php if ($is_editing_protected): ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong><?php echo PROTECTED_USER_NAME; ?></strong> kaydı sistem tarafından korunmaktadır ve düzenlenemez.</div>
                    <?php endif; ?>
                    <form method="POST" action="personeller.php">
                        <?php if ($edit_employee): ?>
                            <input type="hidden" name="personel_id" value="<?php echo $edit_employee['personel_id']; ?>">
                        <?php endif; ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="ad_soyad">Ad Soyad</label>
                                <input type="text" id="ad_soyad" name="ad_soyad" value="<?php echo htmlspecialchars($edit_employee['ad_soyad'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?> required>
                            </div>
                            <div class="form-group">
                                <label for="pozisyon">Pozisyon</label>
                                <input type="text" id="pozisyon" name="pozisyon" value="<?php echo htmlspecialchars($edit_employee['pozisyon'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="departman">Departman</label>
                                <input type="text" id="departman" name="departman" value="<?php echo htmlspecialchars($edit_employee['departman'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="ise_giris_tarihi">İşe Giriş Tarihi</label>
                                <input type="date" id="ise_giris_tarihi" name="ise_giris_tarihi" value="<?php echo $edit_employee['ise_giris_tarihi'] ?? date('Y-m-d'); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="telefon">Telefon</label>
                                <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($edit_employee['telefon'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="e_posta">E-posta</label>
                                <input type="email" id="e_posta" name="e_posta" value="<?php echo htmlspecialchars($edit_employee['e_posta'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="adres">Adres</label>
                                <textarea id="adres" name="adres" <?php if($is_editing_protected) echo 'disabled'; ?>><?php echo htmlspecialchars($edit_employee['adres'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="tc_kimlik_no">TC Kimlik No</label>
                                <input type="text" id="tc_kimlik_no" name="tc_kimlik_no" value="<?php echo htmlspecialchars($edit_employee['tc_kimlik_no'] ?? ''); ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="dogum_tarihi">Doğum Tarihi</label>
                                <input type="date" id="dogum_tarihi" name="dogum_tarihi" value="<?php echo $edit_employee['dogum_tarihi'] ?? ''; ?>" <?php if($is_editing_protected) echo 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label for="sifre">Şifre</label>
                                <input type="password" id="sifre" name="sifre" value="" placeholder="Yeni şifre (boş bırakırsanız değişmez)">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="notlar">Notlar</label>
                                <textarea id="notlar" name="notlar" <?php if($is_editing_protected) echo 'disabled'; ?>><?php echo htmlspecialchars($edit_employee['notlar'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <?php if ($edit_employee): ?>
                                    <button type="submit" name="update" class="btn btn-success" <?php if($is_editing_protected) echo 'disabled'; ?>><i class="fas fa-check"></i> Bilgileri Güncelle</button>
                                    <a href="personeller.php" class="btn btn-secondary">İptal</a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary"><i class="fas fa-plus"></i> Personel Ekle</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Personel Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>İşlemler</th>
                                    <th>Ad Soyad</th>
                                    <th>Pozisyon</th>
                                    <th>Departman</th>
                                    <th>Telefon</th>
                                    <th>E-posta</th>
                                    <th>TC Kimlik No</th>
                                    <th>Doğum Tarihi</th>
                                    <th>İşe Giriş</th>
                                    <th>Adres</th>
                                    <th>Notlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($employees_result->num_rows > 0): ?>
                                    <?php while ($employee = $employees_result->fetch_assoc()): ?>
                                        <?php $is_protected_row = ($employee['ad_soyad'] === PROTECTED_USER_NAME); ?>
                                        <tr>
                                            <td class="actions">
                                                <a href="personeller.php?edit=<?php echo $employee['personel_id']; ?>" class="btn btn-primary <?php if($is_protected_row) echo 'disabled'; ?>" title="<?php if($is_protected_row) echo 'Bu kullanıcı düzenlenemez'; ?>"><i class="fas fa-edit"></i></a>
                                                <form method="POST" onsubmit="if(<?php echo $is_protected_row ? 'true' : 'false'; ?>) { alert('<?php echo PROTECTED_USER_NAME; ?> kaydı silinemez.'); return false; } return confirm('Bu personeli silmek istediğinizden emin misiniz?');">
                                                    <input type="hidden" name="personel_id" value="<?php echo $employee['personel_id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger" <?php if($is_protected_row) echo 'disabled'; ?>" title="<?php if($is_protected_row) echo 'Bu kullanıcı silinemez'; ?>"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($employee['ad_soyad']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($employee['pozisyon']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['departman']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['telefon']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['e_posta']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['tc_kimlik_no']); ?></td>
                                            <td><?php echo $employee['dogum_tarihi'] ? date("d.m.Y", strtotime($employee['dogum_tarihi'])) : ''; ?></td>
                                            <td><?php echo date("d.m.Y", strtotime($employee['ise_giris_tarihi'])); ?></td>
                                            <td><?php echo htmlspecialchars(substr($employee['adres'], 0, 30)) . (strlen($employee['adres']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo htmlspecialchars(substr($employee['notlar'], 0, 20)) . (strlen($employee['notlar']) > 20 ? '...' : ''); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" style="text-align: center; padding: 20px;">Henüz kayıtlı personel bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>