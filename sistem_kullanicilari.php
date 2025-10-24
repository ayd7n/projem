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

// Only admin can manage system users
if ($_SESSION['rol'] !== 'admin') {
    header('Location: navigation.php');
    exit;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Create new system user
        $taraf = $_POST['taraf'];
        $id = $_POST['id'];
        $kullanici_adi = $_POST['kullanici_adi'];
        $telefon = $_POST['telefon'];
        $sifre = $_POST['sifre'];
        $rol = $_POST['rol'];
        $aktif = isset($_POST['aktif']) ? 1 : 0;
        
        // Hash the password
        $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO sistem_kullanicilari (taraf, id, kullanici_adi, telefon, sifre, rol, aktif) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sissssi', $taraf, $id, $kullanici_adi, $telefon, $hashed_password, $rol, $aktif);
        
        if ($stmt->execute()) {
            $message = "Sistem kullanıcısı başarıyla oluşturuldu.";
        } else {
            $error = "Sistem kullanıcısı oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update system user
        $kullanici_id = $_POST['kullanici_id'];
        $taraf = $_POST['taraf'];
        $id = $_POST['id'];
        $kullanici_adi = $_POST['kullanici_adi'];
        $telefon = $_POST['telefon'];
        $rol = $_POST['rol'];
        $aktif = isset($_POST['aktif']) ? 1 : 0;
        
        // Update password if provided
        if (!empty($_POST['sifre'])) {
            $sifre = $_POST['sifre'];
            $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
            $query = "UPDATE sistem_kullanicilari SET taraf = ?, id = ?, kullanici_adi = ?, telefon = ?, sifre = ?, rol = ?, aktif = ? WHERE kullanici_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sisssssii', $taraf, $id, $kullanici_adi, $telefon, $hashed_password, $rol, $aktif, $kullanici_id);
        } else {
            $query = "UPDATE sistem_kullanicilari SET taraf = ?, id = ?, kullanici_adi = ?, telefon = ?, rol = ?, aktif = ? WHERE kullanici_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sissssi', $taraf, $id, $kullanici_adi, $telefon, $rol, $aktif, $kullanici_id);
        }
        
        if ($stmt->execute()) {
            $message = "Sistem kullanıcısı başarıyla güncellendi.";
        } else {
            $error = "Sistem kullanıcısı güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete system user
        $kullanici_id = $_POST['kullanici_id'];
        
        // Don't allow deletion of own account
        if ($kullanici_id == $_SESSION['user_id']) {
            $error = "Kendi hesabınızı silemezsiniz.";
        } else {
            $query = "DELETE FROM sistem_kullanicilari WHERE kullanici_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $kullanici_id);
            
            if ($stmt->execute()) {
                $message = "Sistem kullanıcısı başarıyla silindi.";
            } else {
                $error = "Sistem kullanıcısı silinirken hata oluştu: " . $connection->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all system users
$users_query = "SELECT * FROM sistem_kullanicilari ORDER BY kullanici_adi";
$users_result = $connection->query($users_query);

// Fetch all employees and customers for dropdowns
$employees_query = "SELECT personel_id, ad_soyad FROM personeller ORDER BY ad_soyad";
$employees_result = $connection->query($employees_query);

$customers_query = "SELECT musteri_id, musteri_adi FROM musteriler ORDER BY musteri_adi";
$customers_result = $connection->query($customers_query);

// Calculate total users
$total_result = $connection->query("SELECT COUNT(*) as total FROM sistem_kullanicilari");
$total_users = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Sistem Kullanıcıları - Parfüm ERP</title>
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
        
        @media (max-width: 768px) {
            .main-content { padding: 0; }
        }
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
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group input:disabled, .form-group select:disabled { background-color: #f1f3f5; cursor: not-allowed; }
        .form-group select { width: 100%; }
        .form-group.checkbox-group { flex-direction: row; align-items: center; gap: 10px; }
        .form-group.checkbox-group input[type="checkbox"] { width: auto; }
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
                <h1>Sistem Kullanıcıları Yönetimi</h1>
                <p>Kullanıcı hesaplarını ve yetkilerini yönetin</p>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="stat-card" style="margin-bottom: 30px;">
                <div class="stat-icon" style="background: var(--primary)">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Toplam Kullanıcı</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo isset($_GET['edit']) ? 'Kullanıcı Bilgilerini Düzenle' : 'Yeni Kullanıcı Ekle'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php
                        $kullanici_id = '';
                        $taraf = '';
                        $id = '';
                        $kullanici_adi = '';
                        $telefon = '';
                        $rol = '';
                        $aktif = 1;
                        
                        if (isset($_GET['edit'])) {
                            $kullanici_id = $_GET['edit'];
                            $edit_query = "SELECT * FROM sistem_kullanicilari WHERE kullanici_id = ?";
                            $stmt = $connection->prepare($edit_query);
                            $stmt->bind_param('i', $kullanici_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user = $result->fetch_assoc();
                            
                            if ($user) {
                                $taraf = $user['taraf'];
                                $id = $user['id'];
                                $kullanici_adi = $user['kullanici_adi'];
                                $telefon = $user['telefon'];
                                $rol = $user['rol'];
                                $aktif = $user['aktif'];
                            }
                            $stmt->close();
                        }
                        ?>
                        
                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="kullanici_id" value="<?php echo $kullanici_id; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="taraf">Kullanıcı Tipi *</label>
                                <select id="taraf" name="taraf" required>
                                    <option value="personel" <?php echo $taraf === 'personel' ? 'selected' : ''; ?>>Personel</option>
                                    <option value="musteri" <?php echo $taraf === 'musteri' ? 'selected' : ''; ?>>Müşteri</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id">Personel/Müşteri *</label>
                                <select id="id" name="id" required>
                                    <option value="">Seçin</option>
                                    <?php if ($taraf == 'personel' || !isset($_GET['edit'])): ?>
                                        <?php $employees_result->data_seek(0); ?>
                                        <?php while($employee = $employees_result->fetch_assoc()): ?>
                                            <option value="<?php echo $employee['personel_id']; ?>" 
                                                <?php echo $id == $employee['personel_id'] ? 'selected' : ''; ?>>
                                                <?php echo $employee['personel_id']; ?> - <?php echo htmlspecialchars($employee['ad_soyad']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <?php $customers_result->data_seek(0); ?>
                                        <?php while($customer = $customers_result->fetch_assoc()): ?>
                                            <option value="<?php echo $customer['musteri_id']; ?>" 
                                                <?php echo $id == $customer['musteri_id'] ? 'selected' : ''; ?>>
                                                <?php echo $customer['musteri_id']; ?> - <?php echo htmlspecialchars($customer['musteri_adi']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="kullanici_adi">Kullanıcı Adı *</label>
                                <input type="text" id="kullanici_adi" name="kullanici_adi" value="<?php echo htmlspecialchars($kullanici_adi); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefon">Telefon *</label>
                                <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($telefon); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="sifre"><?php echo isset($_GET['edit']) ? 'Yeni Şifre (değiştirmek istemiyorsanız boş bırakın)' : 'Şifre *'; ?></label>
                                <input type="<?php echo isset($_GET['edit']) ? 'password' : 'text'; ?>" id="sifre" name="sifre" <?php echo !isset($_GET['edit']) ? 'required' : ''; ?>>
                            </div>
                            
                            <div class="form-group">
                                <label for="rol">Rol *</label>
                                <select id="rol" name="rol" required>
                                    <option value="admin" <?php echo $rol === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="personel" <?php echo $rol === 'personel' ? 'selected' : ''; ?>>Personel</option>
                                </select>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="aktif" name="aktif" <?php echo $aktif ? 'checked' : ''; ?>>
                                <label for="aktif">Hesap Aktif</label>
                            </div>
                            
                            <div class="form-actions">
                                <?php if (isset($_GET['edit'])): ?>
                                    <button type="submit" name="update" class="btn btn-success"><i class="fas fa-check"></i> Güncelle</button>
                                    <a href="sistem_kullanicilari.php" class="btn btn-secondary"><i class="fas fa-times"></i> İptal</a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary"><i class="fas fa-plus"></i> Kullanıcı Ekle</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Kullanıcı Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>İşlemler</th>
                                    <th>Taraf</th>
                                    <th>Referans ID</th>
                                    <th>Kullanıcı Adı</th>
                                    <th>Telefon</th>
                                    <th>Rol</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users_result && $users_result->num_rows > 0): ?>
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="actions">
                                                <a href="sistem_kullanicilari.php?edit=<?php echo $user['kullanici_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <?php if ($user['kullanici_id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" id="deleteForm<?php echo $user['kullanici_id']; ?>" onsubmit="return false;">
                                                        <input type="hidden" name="kullanici_id" value="<?php echo $user['kullanici_id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-danger" onclick="confirmDelete('<?php echo $user['kullanici_id']; ?>', event)"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['taraf'] == 'personel'): ?>
                                                    <span style="background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">Personel</span>
                                                <?php else: ?>
                                                    <span style="background: #e8f5e9; color: #388e3c; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">Müşteri</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($user['kullanici_adi']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['telefon']); ?></td>
                                            <td>
                                                <?php if ($user['rol'] == 'admin'): ?>
                                                    <span style="background: #fff3e0; color: #f57c00; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">Yönetici</span>
                                                <?php else: ?>
                                                    <span style="background: #f3e5f5; color: #7b1fa2; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">Personel</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['aktif']): ?>
                                                    <span style="color: green; font-weight: bold;">✓ Aktif</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">✗ Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px;">Henüz kayıtlı kullanıcı bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function confirmDelete(kullanici_id, event) {
            event.preventDefault();
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu kullanıcıyı silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm' + kullanici_id).submit();
                }
            });
        }

        // Update the ID dropdown based on the selected taraf
        document.getElementById('taraf').addEventListener('change', function() {
            const taraf = this.value;
            const idSelect = document.getElementById('id');
            idSelect.innerHTML = '<option value="">Seçin</option>';

            <?php
            // Fetch and store employee and customer data for JavaScript
            $employees_result->data_seek(0);
            $employees = [];
            while($employee = $employees_result->fetch_assoc()) {
                $employees[] = array('id' => $employee['personel_id'], 'name' => $employee['ad_soyad']);
            }

            $customers_result->data_seek(0);
            $customers = [];
            while($customer = $customers_result->fetch_assoc()) {
                $customers[] = array('id' => $customer['musteri_id'], 'name' => $customer['musteri_adi']);
            }
            ?>

            if (taraf === 'personel') {
                <?php foreach ($employees as $employee): ?>
                    const option = document.createElement('option');
                    option.value = <?php echo $employee['id']; ?>;
                    option.textContent = '<?php echo $employee['id']; ?> - <?php echo addslashes($employee['name']); ?>';
                    <?php if ($id == $employee['id'] && $taraf == 'personel'): ?>
                        option.selected = true;
                    <?php endif; ?>
                    idSelect.appendChild(option);
                <?php endforeach; ?>
            } else if (taraf === 'musteri') {
                <?php foreach ($customers as $customer): ?>
                    const option = document.createElement('option');
                    option.value = <?php echo $customer['id']; ?> - <?php echo addslashes($customer['name']); ?>;
                    <?php if ($id == $customer['id'] && $taraf == 'musteri'): ?>
                        option.selected = true;
                    <?php endif; ?>
                    idSelect.appendChild(option);
                <?php endforeach; ?>
            }
        });
    </script>

</body>
</html>
