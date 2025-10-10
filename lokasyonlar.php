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
        // Create new location
        $depo_ismi = $_POST['depo_ismi'];
        $raf = $_POST['raf'];
        
        $query = "INSERT INTO lokasyonlar (depo_ismi, raf) VALUES (?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ss', $depo_ismi, $raf);
        
        if ($stmt->execute()) {
            $message = "Lokasyon başarıyla oluşturuldu.";
        } else {
            $error = "Lokasyon oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update location
        $lokasyon_id = $_POST['lokasyon_id'];
        $depo_ismi = $_POST['depo_ismi'];
        $raf = $_POST['raf'];
        
        $query = "UPDATE lokasyonlar SET depo_ismi = ?, raf = ? WHERE lokasyon_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssi', $depo_ismi, $raf, $lokasyon_id);
        
        if ($stmt->execute()) {
            $message = "Lokasyon başarıyla güncellendi.";
        } else {
            $error = "Lokasyon güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete location
        $lokasyon_id = $_POST['lokasyon_id'];
        
        $query = "DELETE FROM lokasyonlar WHERE lokasyon_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $lokasyon_id);
        
        if ($stmt->execute()) {
            $message = "Lokasyon başarıyla silindi.";
        } else {
            $error = "Lokasyon silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all locations
$locations_query = "SELECT * FROM lokasyonlar ORDER BY depo_ismi, raf";
$locations_result = $connection->query($locations_query);

// Calculate total locations
$total_result = $connection->query("SELECT COUNT(*) as total FROM lokasyonlar");
$total_locations = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Lokasyonlar - Parfüm ERP</title>
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
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group input:disabled { background-color: #f1f3f5; cursor: not-allowed; }
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
                <h1>Lokasyonlar Yönetimi</h1>
                <p>Depo ve raf tanımlamaları</p>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="stat-card" style="margin-bottom: 30px;">
                <div class="stat-icon" style="background: var(--primary)">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_locations; ?></h3>
                    <p>Toplam Lokasyon</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo isset($_GET['edit']) ? 'Lokasyon Güncelle' : 'Yeni Lokasyon Ekle'; ?></h2>
                </div>
                <div class="card-body">
                    <?php
                    $depo_ismi = '';
                    $raf = '';
                    $lokasyon_id = '';
                    
                    if (isset($_GET['edit'])) {
                        $lokasyon_id = $_GET['edit'];
                        $edit_query = "SELECT * FROM lokasyonlar WHERE lokasyon_id = ?";
                        $stmt = $connection->prepare($edit_query);
                        $stmt->bind_param('i', $lokasyon_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $location = $result->fetch_assoc();
                        
                        if ($location) {
                            $depo_ismi = $location['depo_ismi'];
                            $raf = $location['raf'];
                        }
                        $stmt->close();
                    }
                    ?>
                    
                    <form method="POST">
                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="lokasyon_id" value="<?php echo $lokasyon_id; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="depo_ismi">Depo İsmi *</label>
                                <input type="text" id="depo_ismi" name="depo_ismi" value="<?php echo htmlspecialchars($depo_ismi); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="raf">Raf *</label>
                                <input type="text" id="raf" name="raf" value="<?php echo htmlspecialchars($raf); ?>" required>
                            </div>
                            
                            <div class="form-actions">
                                <?php if (isset($_GET['edit'])): ?>
                                    <button type="submit" name="update" class="btn btn-success"><i class="fas fa-check"></i> Güncelle</button>
                                    <a href="lokasyonlar.php" class="btn btn-secondary"><i class="fas fa-times"></i> İptal</a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary"><i class="fas fa-plus"></i> Lokasyon Ekle</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Lokasyon Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>İşlemler</th>
                                    <th>Depo İsmi</th>
                                    <th>Raf</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($locations_result && $locations_result->num_rows > 0): ?>
                                    <?php while ($location = $locations_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="actions">
                                                <a href="lokasyonlar.php?edit=<?php echo $location['lokasyon_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <form method="POST" onsubmit="return confirm('Bu lokasyonu silmek istediğinizden emin misiniz?');">
                                                    <input type="hidden" name="lokasyon_id" value="<?php echo $location['lokasyon_id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($location['depo_ismi']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($location['raf']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 20px;">Henüz kayıtlı lokasyon bulunmuyor.</td>
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