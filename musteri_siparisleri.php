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
    if (isset($_POST['update'])) {
        // Update order status
        $siparis_id = $_POST['siparis_id'];
        $durum = $_POST['durum'];
        
        if ($durum === 'onaylandi') {
            // When approving, set the approval details
            $personel_id = $_SESSION['id'];
            $personel_adi = $_SESSION['kullanici_adi'];
            
            $query = "UPDATE siparisler SET durum = ?, onaylayan_personel_id = ?, onaylayan_personel_adi = ?, onay_tarihi = NOW() WHERE siparis_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sisi', $durum, $personel_id, $personel_adi, $siparis_id);
        } else {
            $query = "UPDATE siparisler SET durum = ? WHERE siparis_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('si', $durum, $siparis_id);
        }
        
        if ($stmt->execute()) {
            $message = "Sipariş başarıyla güncellendi.";
            
            // If the order is approved, update stock
            if ($durum === 'onaylandi') {
                // Get order items
                $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
                $items_stmt = $connection->prepare($items_query);
                $items_stmt->bind_param('i', $siparis_id);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                // Update stock for each item
                while ($item = $items_result->fetch_assoc()) {
                    $update_stock_query = "UPDATE urunler SET stok_miktari = stok_miktari - ? WHERE urun_kodu = ?";
                    $update_stock_stmt = $connection->prepare($update_stock_query);
                    $update_stock_stmt->bind_param('ii', $item['adet'], $item['urun_kodu']);
                    $update_stock_stmt->execute();
                    $update_stock_stmt->close();
                    
                    // Add stock movement record
                    $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                                      VALUES ('urun', ?, ?, ?, ?, 'cikis', 'cikis', ?, 'Müşteri siparişi', ?, ?)";
                    $movement_stmt = $connection->prepare($movement_query);
                    $movement_stmt->bind_param('ssdissi', $item['urun_kodu'], $item['urun_ismi'], $item['birim'], $item['adet'], $siparis_id, $_SESSION['id'], $_SESSION['kullanici_adi']);
                    $movement_stmt->execute();
                    $movement_stmt->close();
                }
                
                $items_stmt->close();
            }
        } else {
            $error = "Sipariş güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all orders with filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'beklemede';
$where_clause = '';

switch ($filter) {
    case 'beklemede':
        $where_clause = "WHERE durum = 'beklemede'";
        break;
    case 'onaylandi':
        $where_clause = "WHERE durum = 'onaylandi'";
        break;
    case 'iptal_edildi':
        $where_clause = "WHERE durum = 'iptal_edildi'";
        break;
    case 'tamamlandi':
        $where_clause = "WHERE durum = 'tamamlandi'";
        break;
    case 'tum':
        $where_clause = '';
        break;
    default:
        $where_clause = "WHERE durum = 'beklemede'";
        $filter = 'beklemede';
}

$orders_query = "SELECT * FROM siparisler $where_clause ORDER BY tarih DESC";
$orders_result = $connection->query($orders_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Siparişleri - Parfüm ERP Sistemi</title>
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
        
        .list-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
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
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .filter-btn.active {
            background-color: #007bff;
        }
        
        .filter-btn:hover {
            background-color: #5a6268;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        
        .beklemede { background-color: #ffc107; }
        .onaylandi { background-color: #28a745; }
        .iptal_edildi { background-color: #dc3545; }
        .tamamlandi { background-color: #17a2b8; }
        
        .order-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Müşteri Siparişleri</h1>
        <p>Admin kullanıcıların müşteri siparişlerini yönetebileceği arayüz</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="filter-buttons">
        <a href="musteri_siparisleri.php?filter=beklemede" class="filter-btn <?php echo $filter === 'beklemede' ? 'active' : ''; ?>">Onay Bekleyen</a>
        <a href="musteri_siparisleri.php?filter=onaylandi" class="filter-btn <?php echo $filter === 'onaylandi' ? 'active' : ''; ?>">Onaylanan</a>
        <a href="musteri_siparisleri.php?filter=iptal_edildi" class="filter-btn <?php echo $filter === 'iptal_edildi' ? 'active' : ''; ?>">İptal Edilen</a>
        <a href="musteri_siparisleri.php?filter=tamamlandi" class="filter-btn <?php echo $filter === 'tamamlandi' ? 'active' : ''; ?>">Tamamlanan</a>
        <a href="musteri_siparisleri.php?filter=tum" class="filter-btn <?php echo $filter === 'tum' ? 'active' : ''; ?>">Tümü</a>
    </div>
    
    <div class="list-section">
        <h2>Sipariş Listesi</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Müşteri</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th>Toplam Adet</th>
                    <th>Oluşturan</th>
                    <th>Onaylayan</th>
                    <th>Açıklama</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['siparis_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['musteri_adi']); ?></td>
                        <td><?php echo $order['tarih']; ?></td>
                        <td>
                            <span class="status <?php echo $order['durum']; ?>">
                                <?php 
                                switch($order['durum']) {
                                    case 'beklemede': echo 'Beklemede'; break;
                                    case 'onaylandi': echo 'Onaylandı'; break;
                                    case 'iptal_edildi': echo 'İptal Edildi'; break;
                                    case 'tamamlandi': echo 'Tamamlandı'; break;
                                    default: echo $order['durum']; break;
                                }
                                ?>
                            </span>
                        </td>
                        <td><?php echo $order['toplam_adet']; ?></td>
                        <td><?php echo htmlspecialchars($order['olusturan_musteri']); ?></td>
                        <td><?php echo htmlspecialchars($order['onaylayan_personel_adi']); ?></td>
                        <td><?php echo htmlspecialchars($order['aciklama']); ?></td>
                        <td class="actions">
                            <a href="siparis_detay.php?siparis_id=<?php echo $order['siparis_id']; ?>" class="btn">Detay</a>
                            <?php if ($order['durum'] === 'beklemede'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu siparişi onaylamak istediğinizden emin misiniz?');">
                                    <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                    <input type="hidden" name="durum" value="onaylandi">
                                    <button type="submit" name="update" class="btn btn-update">Onayla</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu siparişi reddetmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                    <input type="hidden" name="durum" value="iptal_edildi">
                                    <button type="submit" name="update" class="btn btn-delete">Reddet</button>
                                </form>
                            <?php elseif ($order['durum'] === 'onaylandi'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu siparişi tamamlamak istediğinizden emin misiniz?');">
                                    <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                    <input type="hidden" name="durum" value="tamamlandi">
                                    <button type="submit" name="update" class="btn" style="background-color: #17a2b8;">Tamamla</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <a href="navigation.php" class="logout">Ana Sayfaya Dön</a>
</body>
</html>