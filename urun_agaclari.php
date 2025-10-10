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
        // Create new product tree
        $urun_kodu = $_POST['urun_kodu'];
        $urun_ismi = $_POST['urun_ismi'];
        $bilesenin_malzeme_turu = $_POST['bilesenin_malzeme_turu'];
        $bilesen_kodu = $_POST['bilesen_kodu'];
        $bilesen_ismi = $_POST['bilesen_ismi'];
        $bilesen_miktari = $_POST['bilesen_miktari'];
        
        $query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssd', $urun_kodu, $urun_ismi, $bilesenin_malzeme_turu, $bilesen_kodu, $bilesen_ismi, $bilesen_miktari);
        
        if ($stmt->execute()) {
            $message = "Ürün ağacı başarıyla oluşturuldu.";
        } else {
            $error = "Ürün ağacı oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update product tree
        $urun_agaci_id = $_POST['urun_agaci_id'];
        $urun_kodu = $_POST['urun_kodu'];
        $urun_ismi = $_POST['urun_ismi'];
        $bilesenin_malzeme_turu = $_POST['bilesenin_malzeme_turu'];
        $bilesen_kodu = $_POST['bilesen_kodu'];
        $bilesen_ismi = $_POST['bilesen_ismi'];
        $bilesen_miktari = $_POST['bilesen_miktari'];
        
        $query = "UPDATE urun_agaci SET urun_kodu = ?, urun_ismi = ?, bilesenin_malzeme_turu = ?, bilesen_kodu = ?, bilesen_ismi = ?, bilesen_miktari = ? WHERE urun_agaci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssd', $urun_kodu, $urun_ismi, $bilesenin_malzeme_turu, $bilesen_kodu, $bilesen_ismi, $bilesen_miktari, $urun_agaci_id);
        
        if ($stmt->execute()) {
            $message = "Ürün ağacı başarıyla güncellendi.";
        } else {
            $error = "Ürün ağacı güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete product tree
        $urun_agaci_id = $_POST['urun_agaci_id'];
        
        $query = "DELETE FROM urun_agaci WHERE urun_agaci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_agaci_id);
        
        if ($stmt->execute()) {
            $message = "Ürün ağacı başarıyla silindi.";
        } else {
            $error = "Ürün ağacı silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all product trees
$product_trees_query = "SELECT * FROM urun_agaci ORDER BY urun_ismi, bilesen_ismi";
$product_trees_result = $connection->query($product_trees_query);

// Fetch all products and materials for dropdowns
$products_query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

$materials_query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

$essences_query = "SELECT esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ağaçları - Parfüm ERP Sistemi</title>
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
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        <h1>Ürün Ağaçları Yönetimi</h1>
        <p>Ürünlerin bileşenlerini tanımlayın</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Ürün Ağacı Güncelle' : 'Yeni Ürün Ağacı Ekle'; ?></h2>
            
            <?php
            $urun_agaci_id = '';
            $urun_kodu = '';
            $urun_ismi = '';
            $bilesenin_malzeme_turu = '';
            $bilesen_kodu = '';
            $bilesen_ismi = '';
            $bilesen_miktari = 0;
            
            if (isset($_GET['edit'])) {
                $urun_agaci_id = $_GET['edit'];
                $edit_query = "SELECT * FROM urun_agaci WHERE urun_agaci_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $urun_agaci_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product_tree = $result->fetch_assoc();
                
                if ($product_tree) {
                    $urun_kodu = $product_tree['urun_kodu'];
                    $urun_ismi = $product_tree['urun_ismi'];
                    $bilesenin_malzeme_turu = $product_tree['bilesenin_malzeme_turu'];
                    $bilesen_kodu = $product_tree['bilesen_kodu'];
                    $bilesen_ismi = $product_tree['bilesen_ismi'];
                    $bilesen_miktari = $product_tree['bilesen_miktari'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="urun_agaci_id" value="<?php echo $urun_agaci_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="urun_kodu">Ürün Kodu:</label>
                    <select id="urun_kodu" name="urun_kodu" required>
                        <option value="">Ürün Seçin</option>
                        <?php while($product = $products_result->fetch_assoc()): ?>
                            <option value="<?php echo $product['urun_kodu']; ?>" 
                                <?php echo $urun_kodu == $product['urun_kodu'] ? 'selected' : ''; ?>>
                                <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="urun_ismi">Ürün İsmi:</label>
                    <input type="text" id="urun_ismi" name="urun_ismi" value="<?php echo htmlspecialchars($urun_ismi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bilesenin_malzeme_turu">Bileşen Türü:</label>
                    <select id="bilesenin_malzeme_turu" name="bilesenin_malzeme_turu" required>
                        <option value="esans" <?php echo $bilesenin_malzeme_turu === 'esans' ? 'selected' : ''; ?>>Esans</option>
                        <option value="sise" <?php echo $bilesenin_malzeme_turu === 'sise' ? 'selected' : ''; ?>>Şişe</option>
                        <option value="kutu" <?php echo $bilesenin_malzeme_turu === 'kutu' ? 'selected' : ''; ?>>Kutu</option>
                        <option value="etiket" <?php echo $bilesenin_malzeme_turu === 'etiket' ? 'selected' : ''; ?>>Etiket</option>
                        <option value="pompa" <?php echo $bilesenin_malzeme_turu === 'pompa' ? 'selected' : ''; ?>>Pompa</option>
                        <option value="ic_ambalaj" <?php echo $bilesenin_malzeme_turu === 'ic_ambalaj' ? 'selected' : ''; ?>>İç Ambalaj</option>
                        <option value="numune_sisesi" <?php echo $bilesenin_malzeme_turu === 'numune_sisesi' ? 'selected' : ''; ?>>Numune Şişesi</option>
                        <option value="kapak" <?php echo $bilesenin_malzeme_turu === 'kapak' ? 'selected' : ''; ?>>Kapak</option>
                        <option value="karton_ara_bolme" <?php echo $bilesenin_malzeme_turu === 'karton_ara_bolme' ? 'selected' : ''; ?>>Karton Ara Bölme</option>
                        <option value="diger" <?php echo $bilesenin_malzeme_turu === 'diger' ? 'selected' : ''; ?>>Diğer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bilesen_kodu">Bileşen Kodu:</label>
                    <select id="bilesen_kodu" name="bilesen_kodu" required>
                        <option value="">Bileşen Seçin</option>
                        <?php 
                        // Reset result pointers to reuse the queries
                        $materials_result->data_seek(0);
                        while($material = $materials_result->fetch_assoc()): ?>
                            <option value="<?php echo $material['malzeme_kodu']; ?>" 
                                <?php echo $bilesen_kodu == $material['malzeme_kodu'] ? 'selected' : ''; ?>
                                data-type="<?php echo $material['malzeme_turu']; ?>"
                                data-name="<?php echo htmlspecialchars($material['malzeme_ismi']); ?>">
                                <?php echo $material['malzeme_kodu']; ?> - <?php echo htmlspecialchars($material['malzeme_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                        
                        <?php 
                        $essences_result->data_seek(0);
                        while($essence = $essences_result->fetch_assoc()): ?>
                            <option value="<?php echo $essence['esans_kodu']; ?>" 
                                <?php echo $bilesen_kodu == $essence['esans_kodu'] ? 'selected' : ''; ?>
                                data-type="esans"
                                data-name="<?php echo htmlspecialchars($essence['esans_ismi']); ?>">
                                <?php echo htmlspecialchars($essence['esans_kodu']); ?> - <?php echo htmlspecialchars($essence['esans_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bilesen_ismi">Bileşen İsmi:</label>
                    <input type="text" id="bilesen_ismi" name="bilesen_ismi" value="<?php echo htmlspecialchars($bilesen_ismi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bilesen_miktari">Bileşen Miktarı:</label>
                    <input type="number" id="bilesen_miktari" name="bilesen_miktari" value="<?php echo $bilesen_miktari; ?>" min="0" step="0.01" required>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="urun_agaclari.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Ürün Ağacı Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ürün Kodu</th>
                        <th>Ürün İsmi</th>
                        <th>Bileşen Türü</th>
                        <th>Bileşen Kodu</th>
                        <th>Bileşen İsmi</th>
                        <th>Bileşen Miktarı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Re-fetch product trees for the list
                    $product_trees_result = $connection->query("SELECT * FROM urun_agaci ORDER BY urun_ismi, bilesen_ismi");
                    while ($product_tree = $product_trees_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product_tree['urun_agaci_id']; ?></td>
                            <td><?php echo $product_tree['urun_kodu']; ?></td>
                            <td><?php echo htmlspecialchars($product_tree['urun_ismi']); ?></td>
                            <td><?php echo htmlspecialchars($product_tree['bilesenin_malzeme_turu']); ?></td>
                            <td><?php echo htmlspecialchars($product_tree['bilesen_kodu']); ?></td>
                            <td><?php echo htmlspecialchars($product_tree['bilesen_ismi']); ?></td>
                            <td><?php echo $product_tree['bilesen_miktari']; ?></td>
                            <td class="actions">
                                <a href="urun_agaclari.php?edit=<?php echo $product_tree['urun_agaci_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu ürün ağacını silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="urun_agaci_id" value="<?php echo $product_tree['urun_agaci_id']; ?>">
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
    
    <script>
        // Update component name and type when component code is selected
        document.getElementById('bilesen_kodu').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('bilesen_ismi').value = selectedOption.getAttribute('data-name');
                document.getElementById('bilesenin_malzeme_turu').value = selectedOption.getAttribute('data-type');
            }
        });
        
        // Update product name when product code is selected
        document.getElementById('urun_kodu').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // In a real application, you would fetch the product name from the server
                // For this example, we would need to pass the product data to JavaScript
            }
        });
    </script>
</body>
</html>