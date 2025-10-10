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
        // Create new essence
        $esans_kodu = $_POST['esans_kodu'];
        $esans_ismi = $_POST['esans_ismi'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $stok_miktari = $_POST['stok_miktari'];
        $birim = $_POST['birim'];
        $demlenme_suresi_gun = $_POST['demlenme_suresi_gun'];
        
        $query = "INSERT INTO esanslar (esans_kodu, esans_ismi, not_bilgisi, stok_miktari, birim, demlenme_suresi_gun) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssdsd', $esans_kodu, $esans_ismi, $not_bilgisi, $stok_miktari, $birim, $demlenme_suresi_gun);
        
        if ($stmt->execute()) {
            $message = "Esans başarıyla oluşturuldu.";
        } else {
            $error = "Esans oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update essence
        $esans_id = $_POST['esans_id'];
        $esans_kodu = $_POST['esans_kodu'];
        $esans_ismi = $_POST['esans_ismi'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $stok_miktari = $_POST['stok_miktari'];
        $birim = $_POST['birim'];
        $demlenme_suresi_gun = $_POST['demlenme_suresi_gun'];
        
        $query = "UPDATE esanslar SET esans_kodu = ?, esans_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, demlenme_suresi_gun = ? WHERE esans_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssdsdi', $esans_kodu, $esans_ismi, $not_bilgisi, $stok_miktari, $birim, $demlenme_suresi_gun, $esans_id);
        
        if ($stmt->execute()) {
            $message = "Esans başarıyla güncellendi.";
        } else {
            $error = "Esans güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete essence
        $esans_id = $_POST['esans_id'];
        
        $query = "DELETE FROM esanslar WHERE esans_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $esans_id);
        
        if ($stmt->execute()) {
            $message = "Esans başarıyla silindi.";
        } else {
            $error = "Esans silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all essences
$essences_query = "SELECT * FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esanslar - Parfüm ERP Sistemi</title>
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
        
        .form-group input, .form-group textarea, .form-group select {
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
        <h1>Esanslar Yönetimi</h1>
        <p>Esans bilgilerini yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Esans Güncelle' : 'Yeni Esans Ekle'; ?></h2>
            
            <?php
            $esans_id = '';
            $esans_kodu = '';
            $esans_ismi = '';
            $not_bilgisi = '';
            $stok_miktari = 0;
            $birim = 'lt';
            $demlenme_suresi_gun = 0;
            
            if (isset($_GET['edit'])) {
                $esans_id = $_GET['edit'];
                $edit_query = "SELECT * FROM esanslar WHERE esans_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $esans_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $essence = $result->fetch_assoc();
                
                if ($essence) {
                    $esans_kodu = $essence['esans_kodu'];
                    $esans_ismi = $essence['esans_ismi'];
                    $not_bilgisi = $essence['not_bilgisi'];
                    $stok_miktari = $essence['stok_miktari'];
                    $birim = $essence['birim'];
                    $demlenme_suresi_gun = $essence['demlenme_suresi_gun'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="esans_id" value="<?php echo $esans_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="esans_kodu">Esans Kodu:</label>
                    <input type="text" id="esans_kodu" name="esans_kodu" value="<?php echo htmlspecialchars($esans_kodu); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="esans_ismi">Esans İsmi:</label>
                    <input type="text" id="esans_ismi" name="esans_ismi" value="<?php echo htmlspecialchars($esans_ismi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="not_bilgisi">Not Bilgisi:</label>
                    <textarea id="not_bilgisi" name="not_bilgisi"><?php echo htmlspecialchars($not_bilgisi); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="stok_miktari">Stok Miktarı:</label>
                    <input type="number" id="stok_miktari" name="stok_miktari" value="<?php echo $stok_miktari; ?>" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="birim">Birim:</label>
                    <select id="birim" name="birim" required>
                        <option value="lt" <?php echo $birim === 'lt' ? 'selected' : ''; ?>>Litre</option>
                        <option value="ml" <?php echo $birim === 'ml' ? 'selected' : ''; ?>>Mililitre</option>
                        <option value="gr" <?php echo $birim === 'gr' ? 'selected' : ''; ?>>Gram</option>
                        <option value="kg" <?php echo $birim === 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                        <option value="adet" <?php echo $birim === 'adet' ? 'selected' : ''; ?>>Adet</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="demlenme_suresi_gun">Demlenme Süresi (Gün):</label>
                    <input type="number" id="demlenme_suresi_gun" name="demlenme_suresi_gun" value="<?php echo $demlenme_suresi_gun; ?>" min="0" step="1" required>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="esanslar.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Esans Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kodu</th>
                        <th>İsmi</th>
                        <th>Stok</th>
                        <th>Birim</th>
                        <th>Demlenme (Gün)</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($essence = $essences_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $essence['esans_id']; ?></td>
                            <td><?php echo htmlspecialchars($essence['esans_kodu']); ?></td>
                            <td><?php echo htmlspecialchars($essence['esans_ismi']); ?></td>
                            <td><?php echo $essence['stok_miktari']; ?></td>
                            <td><?php echo htmlspecialchars($essence['birim']); ?></td>
                            <td><?php echo $essence['demlenme_suresi_gun']; ?></td>
                            <td class="actions">
                                <a href="esanslar.php?edit=<?php echo $essence['esans_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu esansı silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="esans_id" value="<?php echo $essence['esans_id']; ?>">
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