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
        // Create new work center
        $isim = $_POST['isim'];
        $aciklama = $_POST['aciklama'];
        
        $query = "INSERT INTO is_merkezleri (isim, aciklama) VALUES (?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ss', $isim, $aciklama);
        
        if ($stmt->execute()) {
            $message = "İş merkezi başarıyla oluşturuldu.";
        } else {
            $error = "İş merkezi oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update work center
        $is_merkezi_id = $_POST['is_merkezi_id'];
        $isim = $_POST['isim'];
        $aciklama = $_POST['aciklama'];
        
        $query = "UPDATE is_merkezleri SET isim = ?, aciklama = ? WHERE is_merkezi_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssi', $isim, $aciklama, $is_merkezi_id);
        
        if ($stmt->execute()) {
            $message = "İş merkezi başarıyla güncellendi.";
        } else {
            $error = "İş merkezi güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete work center
        $is_merkezi_id = $_POST['is_merkezi_id'];
        
        $query = "DELETE FROM is_merkezleri WHERE is_merkezi_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $is_merkezi_id);
        
        if ($stmt->execute()) {
            $message = "İş merkezi başarıyla silindi.";
        } else {
            $error = "İş merkezi silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all work centers
$work_centers_query = "SELECT * FROM is_merkezleri ORDER BY isim";
$work_centers_result = $connection->query($work_centers_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Merkezleri - Parfüm ERP Sistemi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .form-section, .list-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 300px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-update {
            background-color: #28a745;
        }
        
        .btn-update:hover {
            background-color: #218838;
        }
        
        .btn-delete {
            background-color: #dc3545;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .logout {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 20px;
        }
        
        .logout:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>İş Merkezleri Yönetimi</h1>
        <p>İş merkezlerini tanımlayın ve yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'İş Merkezi Güncelle' : 'Yeni İş Merkezi Ekle'; ?></h2>
            
            <?php
            $is_merkezi_id = '';
            $isim = '';
            $aciklama = '';
            
            if (isset($_GET['edit'])) {
                $is_merkezi_id = $_GET['edit'];
                $edit_query = "SELECT * FROM is_merkezleri WHERE is_merkezi_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $is_merkezi_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $work_center = $result->fetch_assoc();
                
                if ($work_center) {
                    $isim = $work_center['isim'];
                    $aciklama = $work_center['aciklama'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="is_merkezi_id" value="<?php echo $is_merkezi_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="isim">İş Merkezi Adı:</label>
                    <input type="text" id="isim" name="isim" value="<?php echo htmlspecialchars($isim); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama"><?php echo htmlspecialchars($aciklama); ?></textarea>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="is_merkezleri.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>İş Merkezi Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>İsim</th>
                        <th>Açıklama</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($work_center = $work_centers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $work_center['is_merkezi_id']; ?></td>
                            <td><?php echo htmlspecialchars($work_center['isim']); ?></td>
                            <td><?php echo htmlspecialchars($work_center['aciklama']); ?></td>
                            <td class="actions">
                                <a href="is_merkezleri.php?edit=<?php echo $work_center['is_merkezi_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu iş merkezini silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="is_merkezi_id" value="<?php echo $work_center['is_merkezi_id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-delete">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <a href="navigation.php" class="logout">Ana Sayfaya Dön</a>
</body>
</html>