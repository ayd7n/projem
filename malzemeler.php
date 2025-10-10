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
        // Create new material
        $malzeme_ismi = $_POST['malzeme_ismi'];
        $malzeme_turu = $_POST['malzeme_turu'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $stok_miktari = $_POST['stok_miktari'];
        $birim = $_POST['birim'];
        $termin_suresi = $_POST['termin_suresi'];
        $depo = $_POST['depo'];
        $raf = $_POST['raf'];
        $kritik_stok_seviyesi = $_POST['kritik_stok_seviyesi'];
        
        $query = "INSERT INTO malzemeler (malzeme_ismi, malzeme_turu, not_bilgisi, stok_miktari, birim, termin_suresi, depo, raf, kritik_stok_seviyesi) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssisii', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi);
        
        if ($stmt->execute()) {
            $message = "Malzeme başarıyla oluşturuldu.";
        } else {
            $error = "Malzeme oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update material
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $malzeme_ismi = $_POST['malzeme_ismi'];
        $malzeme_turu = $_POST['malzeme_turu'];
        $not_bilgisi = $_POST['not_bilgisi'];
        $stok_miktari = $_POST['stok_miktari'];
        $birim = $_POST['birim'];
        $termin_suresi = $_POST['termin_suresi'];
        $depo = $_POST['depo'];
        $raf = $_POST['raf'];
        $kritik_stok_seviyesi = $_POST['kritik_stok_seviyesi'];
        
        $query = "UPDATE malzemeler SET malzeme_ismi = ?, malzeme_turu = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, termin_suresi = ?, depo = ?, raf = ?, kritik_stok_seviyesi = ? WHERE malzeme_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssisiii', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi, $malzeme_kodu);
        
        if ($stmt->execute()) {
            $message = "Malzeme başarıyla güncellendi.";
        } else {
            $error = "Malzeme güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete material
        $malzeme_kodu = $_POST['malzeme_kodu'];
        
        $query = "DELETE FROM malzemeler WHERE malzeme_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $malzeme_kodu);
        
        if ($stmt->execute()) {
            $message = "Malzeme başarıyla silindi.";
        } else {
            $error = "Malzeme silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all materials
$materials_query = "SELECT * FROM vw_malzemeler_detayli ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

// Fetch all locations for dropdown
$locations_query = "SELECT * FROM lokasyonlar ORDER BY depo_ismi, raf";
$locations_result = $connection->query($locations_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malzemeler - Parfüm ERP Sistemi</title>
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
        
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Malzemeler Yönetimi</h1>
        <p>Malzeme bilgilerini ve detaylı analizleri yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Malzeme Güncelle' : 'Yeni Malzeme Ekle'; ?></h2>
            
            <?php
            $malzeme_kodu = '';
            $malzeme_ismi = '';
            $malzeme_turu = '';
            $not_bilgisi = '';
            $stok_miktari = 0;
            $birim = 'adet';
            $termin_suresi = 0;
            $depo = '';
            $raf = '';
            $kritik_stok_seviyesi = 0;
            
            if (isset($_GET['edit'])) {
                $malzeme_kodu = $_GET['edit'];
                $edit_query = "SELECT * FROM malzemeler WHERE malzeme_kodu = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $malzeme_kodu);
                $stmt->execute();
                $result = $stmt->get_result();
                $material = $result->fetch_assoc();
                
                if ($material) {
                    $malzeme_ismi = $material['malzeme_ismi'];
                    $malzeme_turu = $material['malzeme_turu'];
                    $not_bilgisi = $material['not_bilgisi'];
                    $stok_miktari = $material['stok_miktari'];
                    $birim = $material['birim'];
                    $termin_suresi = $material['termin_suresi'];
                    $depo = $material['depo'];
                    $raf = $material['raf'];
                    $kritik_stok_seviyesi = $material['kritik_stok_seviyesi'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="malzeme_kodu" value="<?php echo $malzeme_kodu; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="malzeme_ismi">Malzeme İsmi:</label>
                    <input type="text" id="malzeme_ismi" name="malzeme_ismi" value="<?php echo htmlspecialchars($malzeme_ismi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="malzeme_turu">Malzeme Türü:</label>
                    <select id="malzeme_turu" name="malzeme_turu" required>
                        <option value="sise" <?php echo $malzeme_turu === 'sise' ? 'selected' : ''; ?>>Şişe</option>
                        <option value="kutu" <?php echo $malzeme_turu === 'kutu' ? 'selected' : ''; ?>>Kutu</option>
                        <option value="etiket" <?php echo $malzeme_turu === 'etiket' ? 'selected' : ''; ?>>Etiket</option>
                        <option value="pompa" <?php echo $malzeme_turu === 'pompa' ? 'selected' : ''; ?>>Pompa</option>
                        <option value="ic_ambalaj" <?php echo $malzeme_turu === 'ic_ambalaj' ? 'selected' : ''; ?>>İç Ambalaj</option>
                        <option value="numune_sisesi" <?php echo $malzeme_turu === 'numune_sisesi' ? 'selected' : ''; ?>>Numune Şişesi</option>
                        <option value="kapak" <?php echo $malzeme_turu === 'kapak' ? 'selected' : ''; ?>>Kapak</option>
                        <option value="karton_ara_bolme" <?php echo $malzeme_turu === 'karton_ara_bolme' ? 'selected' : ''; ?>>Karton Ara Bölme</option>
                        <option value="diger" <?php echo $malzeme_turu === 'diger' ? 'selected' : ''; ?>>Diğer</option>
                    </select>
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
                        <option value="adet" <?php echo $birim === 'adet' ? 'selected' : ''; ?>>Adet</option>
                        <option value="kg" <?php echo $birim === 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                        <option value="gr" <?php echo $birim === 'gr' ? 'selected' : ''; ?>>Gram</option>
                        <option value="lt" <?php echo $birim === 'lt' ? 'selected' : ''; ?>>Litre</option>
                        <option value="ml" <?php echo $birim === 'ml' ? 'selected' : ''; ?>>Mililitre</option>
                        <option value="m" <?php echo $birim === 'm' ? 'selected' : ''; ?>>Metre</option>
                        <option value="cm" <?php echo $birim === 'cm' ? 'selected' : ''; ?>>Santimetre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="termin_suresi">Termin Süresi (Gün):</label>
                    <input type="number" id="termin_suresi" name="termin_suresi" value="<?php echo $termin_suresi; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="depo">Depo:</label>
                    <select id="depo" name="depo" required>
                        <option value="">Depo Seçin</option>
                        <?php while($location = $locations_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($location['depo_ismi']); ?>" 
                                <?php echo $depo === $location['depo_ismi'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location['depo_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="raf">Raf:</label>
                    <input type="text" id="raf" name="raf" value="<?php echo htmlspecialchars($raf); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kritik_stok_seviyesi">Kritik Stok Seviyesi:</label>
                    <input type="number" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" value="<?php echo $kritik_stok_seviyesi; ?>" min="0" required>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="malzemeler.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Malzeme Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>İsim</th>
                        <th>Tür</th>
                        <th>Stok</th>
                        <th>Birim</th>
                        <th>Depo</th>
                        <th>Raf</th>
                        <th>Kritik Seviye</th>
                        <th>Ort. Tüketim</th>
                        <th>Son Tedarikçi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Re-fetch locations for the list
                    $materials_result = $connection->query("SELECT * FROM vw_malzemeler_detayli ORDER BY malzeme_ismi");
                    while ($material = $materials_result->fetch_assoc()): 
                        $stok_class = $material['stok_miktari'] <= $material['kritik_stok_seviyesi'] ? 'stock-warning' : '';
                    ?>
                        <tr>
                            <td><?php echo $material['malzeme_kodu']; ?></td>
                            <td><?php echo htmlspecialchars($material['malzeme_ismi']); ?></td>
                            <td><?php echo htmlspecialchars($material['malzeme_turu']); ?></td>
                            <td class="<?php echo $stok_class; ?>"><?php echo $material['stok_miktari']; ?></td>
                            <td><?php echo htmlspecialchars($material['birim']); ?></td>
                            <td><?php echo htmlspecialchars($material['depo']); ?></td>
                            <td><?php echo htmlspecialchars($material['raf']); ?></td>
                            <td><?php echo $material['kritik_stok_seviyesi']; ?></td>
                            <td><?php echo $material['ortalama_aylik_tuketim']; ?></td>
                            <td><?php echo htmlspecialchars($material['son_tedarikci_adi']); ?></td>
                            <td class="actions">
                                <a href="malzemeler.php?edit=<?php echo $material['malzeme_kodu']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu malzemeyi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="malzeme_kodu" value="<?php echo $material['malzeme_kodu']; ?>">
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