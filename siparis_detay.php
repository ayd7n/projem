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

$siparis_id = isset($_GET['siparis_id']) ? (int)$_GET['siparis_id'] : 0;

if ($siparis_id <= 0) {
    header('Location: siparisler.php');
    exit;
}

$message = '';
$error = '';

// Fetch order details
$order_query = "SELECT * FROM siparisler WHERE siparis_id = ?";
$stmt = $connection->prepare($order_query);
$stmt->bind_param('i', $siparis_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: siparisler.php');
    exit;
}

// Fetch order items
$items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param('i', $siparis_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Handle form submissions for adding/updating items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $urun_kodu = $_POST['urun_kodu'];
        $adet = $_POST['adet'];
        
        // Get product details
        $product_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
        $product_stmt = $connection->prepare($product_query);
        $product_stmt->bind_param('i', $urun_kodu);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        $product = $product_result->fetch_assoc();
        
        if ($product) {
            $toplam_tutar = $adet * $product['satis_fiyati'];
            
            // Insert order item
            $item_query = "INSERT INTO siparis_kalemleri (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $connection->prepare($item_query);
            $item_stmt->bind_param('iiisidd', $siparis_id, $urun_kodu, $product['urun_ismi'], $adet, $product['birim'], $product['satis_fiyati'], $toplam_tutar);
            
            if ($item_stmt->execute()) {
                // Update total quantity in order
                $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
                $update_total_stmt = $connection->prepare($update_total_query);
                $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
                $update_total_stmt->execute();
                
                $message = "Sipariş kalemi başarıyla eklendi.";
            } else {
                $error = "Sipariş kalemi eklenirken hata oluştu: " . $connection->error;
            }
            $item_stmt->close();
        } else {
            $error = "Seçilen ürün bulunamadı.";
        }
        $product_stmt->close();
    } 
    elseif (isset($_POST['update_item'])) {
        $item_id = $_POST['item_id'];
        $urun_kodu = $_POST['urun_kodu'];
        $adet = $_POST['adet'];
        
        // Get product details
        $product_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
        $product_stmt = $connection->prepare($product_query);
        $product_stmt->bind_param('i', $urun_kodu);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        $product = $product_result->fetch_assoc();
        
        if ($product) {
            $toplam_tutar = $adet * $product['satis_fiyati'];
            
            // Update order item
            $item_query = "UPDATE siparis_kalemleri SET urun_kodu = ?, urun_ismi = ?, adet = ?, birim = ?, birim_fiyat = ?, toplam_tutar = ? WHERE siparis_id = ? AND urun_kodu = ?";
            $item_stmt = $connection->prepare($item_query);
            $item_stmt->bind_param('isiiddii', $urun_kodu, $product['urun_ismi'], $adet, $product['birim'], $product['satis_fiyati'], $toplam_tutar, $siparis_id, $item_id);
            
            if ($item_stmt->execute()) {
                // Update total quantity in order
                $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
                $update_total_stmt = $connection->prepare($update_total_query);
                $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
                $update_total_stmt->execute();
                
                $message = "Sipariş kalemi başarıyla güncellendi.";
            } else {
                $error = "Sipariş kalemi güncellenirken hata oluştu: " . $connection->error;
            }
            $item_stmt->close();
        } else {
            $error = "Seçilen ürün bulunamadı.";
        }
        $product_stmt->close();
    } 
    elseif (isset($_POST['delete_item'])) {
        $item_id = $_POST['item_id'];
        
        // Delete order item
        $delete_query = "DELETE FROM siparis_kalemleri WHERE siparis_id = ? AND urun_kodu = ?";
        $delete_stmt = $connection->prepare($delete_query);
        $delete_stmt->bind_param('ii', $siparis_id, $item_id);
        
        if ($delete_stmt->execute()) {
            // Update total quantity in order
            $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
            $update_total_stmt = $connection->prepare($update_total_query);
            $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
            $update_total_stmt->execute();
            
            $message = "Sipariş kalemi başarıyla silindi.";
        } else {
            $error = "Sipariş kalemi silinirken hata oluştu: " . $connection->error;
        }
        $delete_stmt->close();
    }
}

// Fetch all products for dropdown
$products_query = "SELECT * FROM urunler WHERE stok_miktari > 0 ORDER BY urun_ismi";
$products_result = $connection->query($products_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Detayı - Parfüm ERP Sistemi</title>
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
        
        .info-section, .items-section, .add-item-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select, .form-group textarea {
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
        
        .order-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            flex: 1;
            min-width: 200px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info-value {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sipariş Detayı #<?php echo $siparis_id; ?></h1>
        <p>Sipariş bilgileri ve kalemleri</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="info-section">
        <h2>Sipariş Bilgileri</h2>
        <div class="order-info">
            <div class="info-item">
                <div class="info-label">Sipariş ID:</div>
                <div class="info-value"><?php echo $order['siparis_id']; ?></div>
                
                <div class="info-label">Müşteri:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['musteri_adi']); ?></div>
                
                <div class="info-label">Durum:</div>
                <div class="info-value">
                    <?php 
                    switch($order['durum']) {
                        case 'beklemede': echo 'Beklemede'; break;
                        case 'onaylandi': echo 'Onaylandı'; break;
                        case 'iptal_edildi': echo 'İptal Edildi'; break;
                        case 'tamamlandi': echo 'Tamamlandı'; break;
                        default: echo $order['durum']; break;
                    }
                    ?>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Tarih:</div>
                <div class="info-value"><?php echo $order['tarih']; ?></div>
                
                <div class="info-label">Toplam Adet:</div>
                <div class="info-value"><?php echo $order['toplam_adet']; ?></div>
                
                <div class="info-label">Oluşturan:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['olusturan_musteri']); ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Onaylayan Personel:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['onaylayan_personel_adi']); ?></div>
                
                <div class="info-label">Onay Tarihi:</div>
                <div class="info-value"><?php echo $order['onay_tarihi']; ?></div>
                
                <div class="info-label">Açıklama:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['aciklama']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="items-section">
        <h2>Sipariş Kalemleri</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Ürün Kodu</th>
                    <th>Ürün İsmi</th>
                    <th>Adet</th>
                    <th>Birim</th>
                    <th>Birim Fiyat</th>
                    <th>Toplam Tutar</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['urun_kodu']; ?></td>
                        <td><?php echo htmlspecialchars($item['urun_ismi']); ?></td>
                        <td><?php echo $item['adet']; ?></td>
                        <td><?php echo htmlspecialchars($item['birim']); ?></td>
                        <td><?php echo number_format($item['birim_fiyat'], 2); ?> TL</td>
                        <td><?php echo number_format($item['toplam_tutar'], 2); ?> TL</td>
                        <td class="actions">
                            <a href="#update-form-<?php echo $item['urun_kodu']; ?>" class="btn">Düzenle</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kalemi silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="item_id" value="<?php echo $item['urun_kodu']; ?>">
                                <button type="submit" name="delete_item" class="btn btn-delete">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <tr id="update-form-<?php echo $item['urun_kodu']; ?>" style="display:none;">
                        <td colspan="7">
                            <form method="POST" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px;">
                                <input type="hidden" name="item_id" value="<?php echo $item['urun_kodu']; ?>">
                                <div class="form-group">
                                    <label for="urun_kodu_<?php echo $item['urun_kodu']; ?>">Ürün:</label>
                                    <select id="urun_kodu_<?php echo $item['urun_kodu']; ?>" name="urun_kodu" required>
                                        <option value="">Ürün Seçin</option>
                                        <?php 
                                        $products_result->data_seek(0);
                                        while($product = $products_result->fetch_assoc()): ?>
                                            <option value="<?php echo $product['urun_kodu']; ?>" 
                                                <?php echo $product['urun_kodu'] == $item['urun_kodu'] ? 'selected' : ''; ?>>
                                                <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?> (Stok: <?php echo $product['stok_miktari']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="adet_<?php echo $item['urun_kodu']; ?>">Adet:</label>
                                    <input type="number" id="adet_<?php echo $item['urun_kodu']; ?>" name="adet" value="<?php echo $item['adet']; ?>" min="1" required>
                                </div>
                                <button type="submit" name="update_item" class="btn btn-update">Güncelle</button>
                                <button type="button" class="btn" onclick="hideUpdateForm(<?php echo $item['urun_kodu']; ?>)">İptal</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="add-item-section">
        <h2>Yeni Sipariş Kalemi Ekle</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="urun_kodu">Ürün:</label>
                <select id="urun_kodu" name="urun_kodu" required>
                    <option value="">Ürün Seçin</option>
                    <?php 
                    $products_result->data_seek(0);
                    while($product = $products_result->fetch_assoc()): ?>
                        <option value="<?php echo $product['urun_kodu']; ?>">
                            <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?> (Stok: <?php echo $product['stok_miktari']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="adet">Adet:</label>
                <input type="number" id="adet" name="adet" min="1" required>
            </div>
            
            <button type="submit" name="add_item" class="btn">Kalem Ekle</button>
        </form>
    </div>
    
    <a href="siparisler.php" class="logout">Sipariş Listesine Dön</a>
    
    <script>
        function showUpdateForm(itemId) {
            // Hide all other update forms
            document.querySelectorAll('[id^="update-form-"]').forEach(function(form) {
                form.style.display = 'none';
            });
            
            // Show the selected update form
            document.getElementById('update-form-' + itemId).style.display = 'table-row';
        }
        
        function hideUpdateForm(itemId) {
            document.getElementById('update-form-' + itemId).style.display = 'none';
        }
        
        // Add click event to all edit buttons
        document.querySelectorAll('a[href^="#update-form-"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                showUpdateForm(targetId.split('-')[2]);
            });
        });
    </script>
</body>
</html>