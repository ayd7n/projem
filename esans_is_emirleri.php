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
        // Create new essence work order
        $esans_kodu = $_POST['esans_kodu'];
        $tank_kodu = $_POST['tank_kodu'];
        $planlanan_miktar = $_POST['planlanan_miktar'];
        $planlanan_baslangic_tarihi = $_POST['planlanan_baslangic_tarihi'];
        $aciklama = $_POST['aciklama'];
        
        // Get esans details
        $esans_query = "SELECT esans_ismi, birim, demlenme_suresi_gun FROM esanslar WHERE esans_kodu = ?";
        $esans_stmt = $connection->prepare($esans_query);
        $esans_stmt->bind_param('s', $esans_kodu);
        $esans_stmt->execute();
        $esans_result = $esans_stmt->get_result();
        $esans = $esans_result->fetch_assoc();
        $esans_ismi = $esans['esans_ismi'];
        $birim = $esans['birim'];
        $demlenme_suresi_gun = $esans['demlenme_suresi_gun'];
        
        // Get tank details
        $tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_kodu = ?";
        $tank_stmt = $connection->prepare($tank_query);
        $tank_stmt->bind_param('s', $tank_kodu);
        $tank_stmt->execute();
        $tank_result = $tank_stmt->get_result();
        $tank = $tank_result->fetch_assoc();
        $tank_ismi = $tank['tank_ismi'];
        
        // Calculate end date
        $planlanan_bitis_tarihi = date('Y-m-d', strtotime($planlanan_baslangic_tarihi . ' + ' . $demlenme_suresi_gun . ' days'));
        
        $query = "INSERT INTO esans_is_emirleri (olusturan, esans_kodu, esans_ismi, tank_kodu, tank_ismi, planlanan_miktar, birim, planlanan_baslangic_tarihi, demlenme_suresi_gun, planlanan_bitis_tarihi, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssdsssss', $_SESSION['kullanici_adi'], $esans_kodu, $esans_ismi, $tank_kodu, $tank_ismi, $planlanan_miktar, $birim, $planlanan_baslangic_tarihi, $demlenme_suresi_gun, $planlanan_bitis_tarihi, $aciklama);
        
        if ($stmt->execute()) {
            $message = "Esans iş emri başarıyla oluşturuldu.";
        } else {
            $error = "Esans iş emri oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update essence work order
        $is_emri_numarasi = $_POST['is_emri_numarasi'];
        $esans_kodu = $_POST['esans_kodu'];
        $tank_kodu = $_POST['tank_kodu'];
        $planlanan_miktar = $_POST['planlanan_miktar'];
        $planlanan_baslangic_tarihi = $_POST['planlanan_baslangic_tarihi'];
        $aciklama = $_POST['aciklama'];
        $durum = $_POST['durum'];
        $tamamlanan_miktar = $_POST['tamamlanan_miktar'];
        
        // Get esans details
        $esans_query = "SELECT esans_ismi, birim, demlenme_suresi_gun FROM esanslar WHERE esans_kodu = ?";
        $esans_stmt = $connection->prepare($esans_query);
        $esans_stmt->bind_param('s', $esans_kodu);
        $esans_stmt->execute();
        $esans_result = $esans_stmt->get_result();
        $esans = $esans_result->fetch_assoc();
        $esans_ismi = $esans['esans_ismi'];
        $birim = $esans['birim'];
        $demlenme_suresi_gun = $esans['demlenme_suresi_gun'];
        
        // Get tank details
        $tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_kodu = ?";
        $tank_stmt = $connection->prepare($tank_query);
        $tank_stmt->bind_param('s', $tank_kodu);
        $tank_stmt->execute();
        $tank_result = $tank_stmt->get_result();
        $tank = $tank_result->fetch_assoc();
        $tank_ismi = $tank['tank_ismi'];
        
        // Calculate end date
        $planlanan_bitis_tarihi = date('Y-m-d', strtotime($planlanan_baslangic_tarihi . ' + ' . $demlenme_suresi_gun . ' days'));
        
        $query = "UPDATE esans_is_emirleri SET esans_kodu = ?, esans_ismi = ?, tank_kodu = ?, tank_ismi = ?, planlanan_miktar = ?, birim = ?, planlanan_baslangic_tarihi = ?, planlanan_bitis_tarihi = ?, aciklama = ?, durum = ?, tamamlanan_miktar = ? WHERE is_emri_numarasi = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssssdssssssi', $esans_kodu, $esans_ismi, $tank_kodu, $tank_ismi, $planlanan_miktar, $birim, $planlanan_baslangic_tarihi, $planlanan_bitis_tarihi, $aciklama, $durum, $tamamlanan_miktar, $is_emri_numarasi);
        
        if ($stmt->execute()) {
            $message = "Esans iş emri başarıyla güncellendi.";
        } else {
            $error = "Esans iş emri güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete essence work order
        $is_emri_numarasi = $_POST['is_emri_numarasi'];
        
        $query = "DELETE FROM esans_is_emirleri WHERE is_emri_numarasi = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $is_emri_numarasi);
        
        if ($stmt->execute()) {
            $message = "Esans iş emri başarıyla silindi.";
        } else {
            $error = "Esans iş emri silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all essence work orders
$orders_query = "SELECT * FROM esans_is_emirleri ORDER BY olusturulma_tarihi DESC";
$orders_result = $connection->query($orders_query);

// Fetch all essences for dropdown
$essences_query = "SELECT * FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Fetch all tanks for dropdown
$tanks_query = "SELECT * FROM tanklar ORDER BY tank_ismi";
$tanks_result = $connection->query($tanks_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esans İş Emirleri - Parfüm ERP Sistemi</title>
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
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        
        .olusturuldu { background-color: #ffc107; }
        .basladi { background-color: #17a2b8; }
        .tamamlandi { background-color: #28a745; }
        .iptal_edildi { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Esans İş Emirleri</h1>
        <p>Esans üretimi için iş emirlerini yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'İş Emri Güncelle' : 'Yeni İş Emri Oluştur'; ?></h2>
            
            <?php
            $is_emri_numarasi = '';
            $esans_kodu = '';
            $tank_kodu = '';
            $planlanan_miktar = 0;
            $planlanan_baslangic_tarihi = date('Y-m-d');
            $aciklama = '';
            $durum = 'olusturuldu';
            $tamamlanan_miktar = 0;
            
            if (isset($_GET['edit'])) {
                $is_emri_numarasi = $_GET['edit'];
                $edit_query = "SELECT * FROM esans_is_emirleri WHERE is_emri_numarasi = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $is_emri_numarasi);
                $stmt->execute();
                $result = $stmt->get_result();
                $order = $result->fetch_assoc();
                
                if ($order) {
                    $esans_kodu = $order['esans_kodu'];
                    $tank_kodu = $order['tank_kodu'];
                    $planlanan_miktar = $order['planlanan_miktar'];
                    $planlanan_baslangic_tarihi = $order['planlanan_baslangic_tarihi'];
                    $aciklama = $order['aciklama'];
                    $durum = $order['durum'];
                    $tamamlanan_miktar = $order['tamamlanan_miktar'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="is_emri_numarasi" value="<?php echo $is_emri_numarasi; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="esans_kodu">Esans:</label>
                    <select id="esans_kodu" name="esans_kodu" required>
                        <option value="">Esans Seçin</option>
                        <?php while($esans = $essences_result->fetch_assoc()): ?>
                            <option value="<?php echo $esans['esans_kodu']; ?>" 
                                <?php echo $esans_kodu == $esans['esans_kodu'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($esans['esans_kodu']); ?> - <?php echo htmlspecialchars($esans['esans_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tank_kodu">Tank:</label>
                    <select id="tank_kodu" name="tank_kodu" required>
                        <option value="">Tank Seçin</option>
                        <?php while($tank = $tanks_result->fetch_assoc()): ?>
                            <option value="<?php echo $tank['tank_kodu']; ?>" 
                                <?php echo $tank_kodu == $tank['tank_kodu'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tank['tank_kodu']); ?> - <?php echo htmlspecialchars($tank['tank_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="planlanan_miktar">Planlanan Miktar:</label>
                    <input type="number" id="planlanan_miktar" name="planlanan_miktar" value="<?php echo $planlanan_miktar; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="planlanan_baslangic_tarihi">Başlangıç Tarihi:</label>
                    <input type="date" id="planlanan_baslangic_tarihi" name="planlanan_baslangic_tarihi" value="<?php echo $planlanan_baslangic_tarihi; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama"><?php echo htmlspecialchars($aciklama); ?></textarea>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <div class="form-group">
                        <label for="durum">Durum:</label>
                        <select id="durum" name="durum" required>
                            <option value="olusturuldu" <?php echo $durum === 'olusturuldu' ? 'selected' : ''; ?>>Oluşturuldu</option>
                            <option value="basladi" <?php echo $durum === 'basladi' ? 'selected' : ''; ?>>Başladı</option>
                            <option value="tamamlandi" <?php echo $durum === 'tamamlandi' ? 'selected' : ''; ?>>Tamamlandı</option>
                            <option value="iptal_edildi" <?php echo $durum === 'iptal_edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tamamlanan_miktar">Tamamlanan Miktar:</label>
                        <input type="number" id="tamamlanan_miktar" name="tamamlanan_miktar" value="<?php echo $tamamlanan_miktar; ?>" step="0.01" min="0">
                    </div>
                    
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="esans_is_emirleri.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>İş Emri Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Oluşturulma</th>
                        <th>Oluşturan</th>
                        <th>Esans</th>
                        <th>Tank</th>
                        <th>Planlanan Miktar</th>
                        <th>Birim</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['is_emri_numarasi']; ?></td>
                            <td><?php echo $order['olusturulma_tarihi']; ?></td>
                            <td><?php echo htmlspecialchars($order['olusturan']); ?></td>
                            <td><?php echo htmlspecialchars($order['esans_ismi']); ?></td>
                            <td><?php echo htmlspecialchars($order['tank_ismi']); ?></td>
                            <td><?php echo $order['planlanan_miktar']; ?></td>
                            <td><?php echo htmlspecialchars($order['birim']); ?></td>
                            <td><?php echo $order['planlanan_baslangic_tarihi']; ?></td>
                            <td><?php echo $order['planlanan_bitis_tarihi']; ?></td>
                            <td>
                                <span class="status <?php echo $order['durum']; ?>">
                                    <?php 
                                    switch($order['durum']) {
                                        case 'olusturuldu': echo 'Oluşturuldu'; break;
                                        case 'basladi': echo 'Başladı'; break;
                                        case 'tamamlandi': echo 'Tamamlandı'; break;
                                        case 'iptal_edildi': echo 'İptal Edildi'; break;
                                        default: echo htmlspecialchars($order['durum']); break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="esans_is_emirleri.php?edit=<?php echo $order['is_emri_numarasi']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu iş emrini silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="is_emri_numarasi" value="<?php echo $order['is_emri_numarasi']; ?>">
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