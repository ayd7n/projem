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
        $tarih = $_POST['tarih'];
        $tutar = $_POST['tutar'];
        $kategori = $_POST['kategori'];
        $aciklama = $_POST['aciklama'];
        $fatura_no = $_POST['fatura_no'] ?? '';
        $odeme_tipi = $_POST['odeme_tipi'];
        $personel_id = $_SESSION['id'];
        $personel_adi = $_SESSION['kullanici_adi'];
        
        $query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sdssssis', $tarih, $tutar, $kategori, $aciklama, $personel_id, $personel_adi, $fatura_no, $odeme_tipi);
        
        if ($stmt->execute()) {
            $message = "Gider başarıyla eklendi.";
        } else {
            $error = "Hata: " . $stmt->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        $gider_id = $_POST['gider_id'];
        $tarih = $_POST['tarih'];
        $tutar = $_POST['tutar'];
        $kategori = $_POST['kategori'];
        $aciklama = $_POST['aciklama'];
        $fatura_no = $_POST['fatura_no'] ?? '';
        $odeme_tipi = $_POST['odeme_tipi'];
        
        $query = "UPDATE gider_yonetimi SET tarih = ?, tutar = ?, kategori = ?, aciklama = ?, fatura_no = ?, odeme_tipi = ? WHERE gider_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sdssssi', $tarih, $tutar, $kategori, $aciklama, $fatura_no, $odeme_tipi, $gider_id);
        
        if ($stmt->execute()) {
            $message = "Gider başarıyla güncellendi.";
        } else {
            $error = "Hata: " . $stmt->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        $gider_id = $_POST['gider_id'];
        
        $query = "DELETE FROM gider_yonetimi WHERE gider_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $gider_id);
        
        if ($stmt->execute()) {
            $message = "Gider başarıyla silindi.";
        } else {
            $error = "Hata: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch data for editing
$edit_expense = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM gider_yonetimi WHERE gider_id = ?";
    $stmt = $connection->prepare($edit_query);
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_expense = $result->fetch_assoc();
    $stmt->close();
}

// Fetch all expenses
$expenses_result = $connection->query("SELECT * FROM gider_yonetimi ORDER BY tarih DESC, gider_id DESC");

// Calculate total expenses
$total_result = $connection->query("SELECT SUM(tutar) as total FROM gider_yonetimi");
$total_expenses = $total_result->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gider Yönetimi - Parfüm ERP</title>
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

        .erp-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-align: center;
        }

        .sidebar-header h2 { font-size: 1.5rem; margin-bottom: 5px; }
        .sidebar-header p { font-size: 0.9rem; opacity: 0.9; }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .user-info h3 { font-size: 1.1rem; }
        .user-info p { font-size: 0.85rem; opacity: 0.8; }

        .nav-menu { padding: 20px 0; }
        .nav-category { padding: 15px 20px 10px; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }
        .nav-links { list-style: none; }
        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-primary);
            transition: var(--transition);
            border-left: 4px solid transparent;
        }
        .nav-links a:hover, .nav-links a.active {
            background: rgba(67, 97, 238, 0.08);
            border-left-color: var(--primary);
            color: var(--primary);
        }
        .nav-links a i { width: 25px; font-size: 1.1rem; margin-right: 12px; }

        .logout-btn {
            display: block; width: calc(100% - 40px); margin: 30px 20px; padding: 12px; background: var(--danger); color: white; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 500; transition: var(--transition); border: none; cursor: pointer; }
        .logout-btn:hover { background: #c0392b; }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
            transition: var(--transition);
        }

        .page-header {
            margin-bottom: 30px;
        }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .page-header p { color: var(--text-secondary); font-size: 1rem; }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h2 { font-size: 1.2rem; font-weight: 600; }
        .card-body { padding: 20px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }

        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-secondary { background-color: var(--text-secondary); color: white; }
        .btn-secondary:hover { background-color: var(--dark); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { background-color: #16a085; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-danger:hover { background-color: #c0392b; }

        .table-wrapper { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }
        .actions a, .actions button { padding: 8px 12px; }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            padding: 25px;
            display: flex;
            align-items: center;
        }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: 260px; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
        }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--primary); position: absolute; top: 25px; left: 20px; z-index: 101; }

    </style>
</head>
<body>
    <div class="erp-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Parfüm ERP</h2>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></h3>
                    <p><?php echo htmlspecialchars($_SESSION['rol']); ?></p>
                </div>
            </div>
            <div class="nav-menu">
                <div class="nav-category">Finans</div>
                <ul class="nav-links">
                    <li><a href="gider_yonetimi.php" class="active"><i class="fas fa-money-bill-wave"></i> <span>Gider Yönetimi</span></a></li>
                    <li><a href="siparisler.php"><i class="fas fa-shopping-cart"></i> <span>Siparişler</span></a></li>
                </ul>
                <div class="nav-category">Diğer</div>
                 <ul class="nav-links">
                    <li><a href="navigation_enhanced.php"><i class="fas fa-home"></i> <span>Ana Sayfa</span></a></li>
                </ul>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="menu-toggle"><i class="fas fa-bars"></i></button>

            <div class="page-header">
                <h1>Gider Yönetimi</h1>
                <p>İşletme giderlerini ekleyin, düzenleyin ve takip edin.</p>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="stat-card" style="margin-bottom: 30px;">
                <div class="stat-icon" style="background: var(--primary)">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_expenses, 2, ',', '.'); ?> TL</h3>
                    <p>Toplam Kayıtlı Gider</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo $edit_expense ? 'Gideri Düzenle' : 'Yeni Gider Ekle'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="gider_yonetimi.php">
                        <?php if ($edit_expense): ?>
                            <input type="hidden" name="gider_id" value="<?php echo $edit_expense['gider_id']; ?>">
                        <?php endif; ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tarih">Tarih</label>
                                <input type="date" id="tarih" name="tarih" value="<?php echo $edit_expense['tarih'] ?? date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="tutar">Tutar (TL)</label>
                                <input type="number" id="tutar" name="tutar" value="<?php echo $edit_expense['tutar'] ?? ''; ?>" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="kategori">Kategori</label>
                                <select id="kategori" name="kategori" required>
                                    <?php $kategori = $edit_expense['kategori'] ?? ''; ?>
                                    <option value="Personel Gideri" <?php echo $kategori === 'Personel Gideri' ? 'selected' : ''; ?>>Personel Gideri</option>
                                    <option value="Malzeme Gideri" <?php echo $kategori === 'Malzeme Gideri' ? 'selected' : ''; ?>>Malzeme Gideri</option>
                                    <option value="İşletme Gideri" <?php echo $kategori === 'İşletme Gideri' ? 'selected' : ''; ?>>İşletme Gideri</option>
                                    <option value="Kira" <?php echo $kategori === 'Kira' ? 'selected' : ''; ?>>Kira</option>
                                    <option value="Enerji" <?php echo $kategori === 'Enerji' ? 'selected' : ''; ?>>Enerji</option>
                                    <option value="Taşıt Gideri" <?php echo $kategori === 'Taşıt Gideri' ? 'selected' : ''; ?>>Taşıt Gideri</option>
                                    <option value="Diğer" <?php echo $kategori === 'Diğer' ? 'selected' : ''; ?>>Diğer</option>
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="odeme_tipi">Ödeme Tipi</label>
                                <select id="odeme_tipi" name="odeme_tipi" required>
                                     <?php $odeme_tipi = $edit_expense['odeme_tipi'] ?? ''; ?>
                                    <option value="Nakit" <?php echo $odeme_tipi === 'Nakit' ? 'selected' : ''; ?>>Nakit</option>
                                    <option value="Kredi Kartı" <?php echo $odeme_tipi === 'Kredi Kartı' ? 'selected' : ''; ?>>Kredi Kartı</option>
                                    <option value="Havale" <?php echo $odeme_tipi === 'Havale' ? 'selected' : ''; ?>>Havale/EFT</option>
                                    <option value="Diğer" <?php echo $odeme_tipi === 'Diğer' ? 'selected' : ''; ?>>Diğer</option>
                                </select>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama">Açıklama</label>
                                <textarea id="aciklama" name="aciklama"><?php echo htmlspecialchars($edit_expense['aciklama'] ?? ''); ?></textarea>
                            </div>
                             <div class="form-group">
                                <label for="fatura_no">Fatura No (varsa)</label>
                                <input type="text" id="fatura_no" name="fatura_no" value="<?php echo htmlspecialchars($edit_expense['fatura_no'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-actions">
                            <?php if ($edit_expense): ?>
                                <button type="submit" name="update" class="btn btn-success"><i class="fas fa-check"></i> Güncelle</button>
                                <a href="gider_yonetimi.php" class="btn btn-secondary">İptal</a>
                            <?php else: ?>
                                <button type="submit" name="create" class="btn btn-primary"><i class="fas fa-plus"></i> Gider Ekle</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Gider Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Kategori</th>
                                    <th>Tutar</th>
                                    <th>Ödeme Tipi</th>
                                    <th>Açıklama</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($expenses_result->num_rows > 0): ?>
                                    <?php while ($expense = $expenses_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date("d.m.Y", strtotime($expense['tarih'])); ?></td>
                                            <td><?php echo htmlspecialchars($expense['kategori']); ?></td>
                                            <td><?php echo number_format($expense['tutar'], 2, ',', '.'); ?> TL</td>
                                            <td><?php echo htmlspecialchars($expense['odeme_tipi']); ?></td>
                                            <td><?php echo htmlspecialchars($expense['aciklama']); ?></td>
                                            <td class="actions">
                                                <a href="gider_yonetimi.php?edit=<?php echo $expense['gider_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <form method="POST" onsubmit="return confirm('Bu gideri silmek istediğinizden emin misiniz?');">
                                                    <input type="hidden" name="gider_id" value="<?php echo $expense['gider_id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 20px;">Henüz kayıtlı gider bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.menu-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
