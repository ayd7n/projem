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
        // Create new tank
        $tank_kodu = $_POST['tank_kodu'];
        $tank_ismi = $_POST['tank_ismi'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $kapasite = $_POST['kapasite'];
        
        $query = "INSERT INTO tanklar (tank_kodu, tank_ismi, not_bilgisi, kapasite) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssd', $tank_kodu, $tank_ismi, $not_bilgisi, $kapasite);
        
        if ($stmt->execute()) {
            $message = "Tank başarıyla oluşturuldu.";
        } else {
            $error = "Tank oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update tank
        $tank_id = $_POST['tank_id'];
        $tank_kodu = $_POST['tank_kodu'];
        $tank_ismi = $_POST['tank_ismi'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $kapasite = $_POST['kapasite'];
        
        $query = "UPDATE tanklar SET tank_kodu = ?, tank_ismi = ?, not_bilgisi = ?, kapasite = ? WHERE tank_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssdsi', $tank_kodu, $tank_ismi, $not_bilgisi, $kapasite, $tank_id);
        
        if ($stmt->execute()) {
            $message = "Tank başarıyla güncellendi.";
        } else {
            $error = "Tank güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete tank
        $tank_id = $_POST['tank_id'];
        
        $query = "DELETE FROM tanklar WHERE tank_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $tank_id);
        
        if ($stmt->execute()) {
            $message = "Tank başarıyla silindi.";
        } else {
            $error = "Tank silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all tanks
$tanks_query = "SELECT * FROM tanklar ORDER BY tank_ismi";
$tanks_result = $connection->query($tanks_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanklar - Parfüm ERP Sistemi</title>
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
        <h1>Tanklar Yönetimi</h1>
        <p>Tank bilgilerini yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Tank Güncelle' : 'Yeni Tank Ekle'; ?></h2>
            
            <?php
            $tank_id = '';
            $tank_kodu = '';
            $tank_ismi = '';
            $not_bilgisi = '';
            $kapasite = 0;
            
            if (isset($_GET['edit'])) {
                $tank_id = $_GET['edit'];
                $edit_query = "SELECT * FROM tanklar WHERE tank_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $tank_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $tank = $result->fetch_assoc();
                
                if ($tank) {
                    $tank_kodu = $tank['tank_kodu'];
                    $tank_ismi = $tank['tank_ismi'];
                    $not_bilgisi = $tank['not_bilgisi'];
                    $kapasite = $tank['kapasite'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="tank_id" value="<?php echo $tank_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="tank_kodu">Tank Kodu:</label>
                    <input type="text" id="tank_kodu" name="tank_kodu" value="<?php echo htmlspecialchars($tank_kodu); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tank_ismi">Tank İsmi:</label>
                    <input type="text" id="tank_ismi" name="tank_ismi" value="<?php echo htmlspecialchars($tank_ismi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="not_bilgisi">Not Bilgisi:</label>
                    <textarea id="not_bilgisi" name="not_bilgisi"><?php echo htmlspecialchars($not_bilgisi); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="kapasite">Kapasite (Litre):</label>
                    <input type="number" id="kapasite" name="kapasite" value="<?php echo $kapasite; ?>" min="0" step="0.01" required>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="tanklar.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Tank Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tank Kodu</th>
                        <th>Tank İsmi</th>
                        <th>Not</th>
                        <th>Kapasite (Litre)</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tank = $tanks_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $tank['tank_id']; ?></td>
                            <td><?php echo htmlspecialchars($tank['tank_kodu']); ?></td>
                            <td><?php echo htmlspecialchars($tank['tank_ismi']); ?></td>
                            <td><?php echo htmlspecialchars($tank['not_bilgisi']); ?></td>
                            <td><?php echo $tank['kapasite']; ?></td>
                            <td class="actions">
                                <a href="tanklar.php?edit=<?php echo $tank['tank_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu tankı silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="tank_id" value="<?php echo $tank['tank_id']; ?>">
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