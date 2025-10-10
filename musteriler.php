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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Create new customer
        $musteri_adi = $_POST['musteri_adi'];
        $vergi_no_tc = $_POST['vergi_no_tc'];
        $adres = $_POST['adres'];
        $telefon = $_POST['telefon'];
        $e_posta = $_POST['e_posta'];
        $sistem_sifresi = $_POST['sifre'];
        $aciklama_notlar = $_POST['aciklama_notlar'];
        $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;
        
        // Hash the password - use default if not provided during create
        $password_to_use = !empty($sistem_sifresi) ? $sistem_sifresi : '12345';
        $hashed_password = password_hash($password_to_use, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO musteriler (musteri_adi, vergi_no_tc, adres, telefon, e_posta, sistem_sifresi, aciklama_notlar, giris_yetkisi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $hashed_password, $aciklama_notlar, $giris_yetkisi);
        
        if ($stmt->execute()) {
            $message = "Müşteri başarıyla oluşturuldu.";
        } else {
            $error = "Müşteri oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update customer
        $musteri_id = $_POST['musteri_id'];
        $musteri_adi = $_POST['musteri_adi'];
        $vergi_no_tc = $_POST['vergi_no_tc'];
        $adres = $_POST['adres'];
        $telefon = $_POST['telefon'];
        $e_posta = $_POST['e_posta'];
        $aciklama_notlar = $_POST['aciklama_notlar'];
        $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;
        
        // Update password if provided
        if (!empty($_POST['sifre'])) {
            $hashed_password = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
            $query = "UPDATE musteriler SET musteri_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, sistem_sifresi = ?, aciklama_notlar = ?, giris_yetkisi = ? WHERE musteri_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $hashed_password, $aciklama_notlar, $giris_yetkisi, $musteri_id);
        } else {
            $query = "UPDATE musteriler SET musteri_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, aciklama_notlar = ?, giris_yetkisi = ? WHERE musteri_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $aciklama_notlar, $giris_yetkisi, $musteri_id);
        }
        
        if ($stmt->execute()) {
            $message = "Müşteri başarıyla güncellendi.";
        } else {
            $error = "Müşteri güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete customer
        $musteri_id = $_POST['musteri_id'];
        
        $query = "DELETE FROM musteriler WHERE musteri_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $musteri_id);
        
        if ($stmt->execute()) {
            $message = "Müşteri başarıyla silindi.";
        } else {
            $error = "Müşteri silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all customers
$customers_query = "SELECT * FROM musteriler ORDER BY musteri_adi";
$customers_result = $connection->query($customers_query);

// Calculate total customers
$total_result = $connection->query("SELECT COUNT(*) as total FROM musteriler");
$total_customers = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Yönetimi - Parfüm ERP</title>
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
                <h1>Müşteri Yönetimi</h1>
                <p>Yeni müşteriler ekleyin ve mevcut müşterileri yönetin.</p>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="stat-card" style="margin-bottom: 30px;">
                <div class="stat-icon" style="background: var(--primary)">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_customers; ?></h3>
                    <p>Toplam Müşteri</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo isset($_GET['edit']) ? 'Müşteri Bilgilerini Düzenle' : 'Yeni Müşteri Ekle'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php
                        $musteri_id = '';
                        $musteri_adi = '';
                        $vergi_no_tc = '';
                        $adres = '';
                        $telefon = '';
                        $e_posta = '';
                        $aciklama_notlar = '';
                        $giris_yetkisi = 0;
                        
                        if (isset($_GET['edit'])) {
                            $musteri_id = $_GET['edit'];
                            $edit_query = "SELECT * FROM musteriler WHERE musteri_id = ?";
                            $stmt = $connection->prepare($edit_query);
                            $stmt->bind_param('i', $musteri_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $customer = $result->fetch_assoc();
                            
                            if ($customer) {
                                $musteri_adi = $customer['musteri_adi'];
                                $vergi_no_tc = $customer['vergi_no_tc'];
                                $adres = $customer['adres'];
                                $telefon = $customer['telefon'];
                                $e_posta = $customer['e_posta'];
                                $aciklama_notlar = $customer['aciklama_notlar'];
                                $giris_yetkisi = $customer['giris_yetkisi'];
                            }
                            $stmt->close();
                        }
                        ?>
                        
                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="musteri_id" value="<?php echo $musteri_id; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="musteri_adi">Müşteri Adı *</label>
                                <input type="text" id="musteri_adi" name="musteri_adi" value="<?php echo htmlspecialchars($musteri_adi); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="vergi_no_tc">Vergi No / TC</label>
                                <input type="text" id="vergi_no_tc" name="vergi_no_tc" value="<?php echo htmlspecialchars($vergi_no_tc); ?>">
                            </div>
                            <div class="form-group">
                                <label for="telefon">Telefon</label>
                                <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($telefon); ?>">
                            </div>
                            <div class="form-group">
                                <label for="e_posta">E-posta</label>
                                <input type="email" id="e_posta" name="e_posta" value="<?php echo htmlspecialchars($e_posta); ?>">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="adres">Adres</label>
                                <textarea id="adres" name="adres"><?php echo htmlspecialchars($adres); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="sistem_sifresi"><?php echo isset($_GET['edit']) ? 'Yeni Şifre (şifreyi değiştirmeyecekseniz boş bırakın)' : 'Sistem Şifresi *'; ?></label>
                                <input type="<?php echo isset($_GET['edit']) ? 'password' : 'text'; ?>" id="sistem_sifresi" name="sistem_sifresi" <?php echo !isset($_GET['edit']) ? 'required' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="giris_yetkisi" style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="giris_yetkisi" name="giris_yetkisi" value="1" <?php echo (isset($customer) && $customer['giris_yetkisi'] == 1) ? 'checked' : ''; ?>> 
                                    <span>Sisteme Giriş Yetkisi</span>
                                </label>
                                <small style="color: #666; margin-top: 5px; display: block;">Müşterinin sistemde oturum açmasına izin ver</small>
                            </div>
                            <div class="form-group">
                                <label for="sifre"><?php echo isset($_GET['edit']) ? 'Yeni Şifre (değiştirmek istemiyorsanız boş bırakın)' : 'Şifre *'; ?></label>
                                <input type="<?php echo isset($_GET['edit']) ? 'password' : 'text'; ?>" id="sifre" name="sifre" <?php echo !isset($_GET['edit']) ? 'required' : ''; ?>>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama_notlar">Açıklama / Notlar</label>
                                <textarea id="aciklama_notlar" name="aciklama_notlar"><?php echo htmlspecialchars($aciklama_notlar); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <?php if (isset($_GET['edit'])): ?>
                                    <button type="submit" name="update" class="btn btn-success"><i class="fas fa-check"></i> Güncelle</button>
                                    <a href="musteriler.php" class="btn btn-secondary"><i class="fas fa-times"></i> İptal</a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary"><i class="fas fa-plus"></i> Müşteri Ekle</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Müşteri Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>İşlemler</th>
                                    <th>Giriş Yetkisi</th>
                                    <th>Müşteri Adı</th>
                                    <th>Vergi/TC No</th>
                                    <th>Telefon</th>
                                    <th>E-posta</th>
                                    <th>Adres</th>
                                    <th>Açıklama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($customers_result && $customers_result->num_rows > 0): ?>
                                    <?php while ($customer = $customers_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="actions">
                                                <a href="musteriler.php?edit=<?php echo $customer['musteri_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <form method="POST" onsubmit="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?');">
                                                    <input type="hidden" name="musteri_id" value="<?php echo $customer['musteri_id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                            <td>
                                                <?php if ($customer['giris_yetkisi'] == 1): ?>
                                                    <span style="color: green; font-weight: bold;">✓</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">✗</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($customer['musteri_adi']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($customer['vergi_no_tc']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['telefon']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['e_posta']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($customer['adres'], 0, 30)) . (strlen($customer['adres']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo htmlspecialchars(substr($customer['aciklama_notlar'], 0, 20)) . (strlen($customer['aciklama_notlar']) > 20 ? '...' : ''); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 20px;">Henüz kayıtlı müşteri bulunmuyor.</td>
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