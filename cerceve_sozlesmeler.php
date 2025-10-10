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
        // Create new framework contract
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $birim_fiyat = $_POST['birim_fiyat'];
        $para_birimi = $_POST['para_birimi'];
        $sozlesme_turu = $_POST['sozlesme_turu'];
        $toplam_anlasilan_miktar = $_POST['toplam_anlasilan_miktar'];
        $baslangic_tarihi = $_POST['baslangic_tarihi'];
        $bitis_tarihi = $_POST['bitis_tarihi'];
        $pesin_odeme_yapildi_mi = isset($_POST['pesin_odeme_yapildi_mi']) ? 1 : 0;
        $toplam_pesin_odeme_tutari = $pesin_odeme_yapildi_mi ? $_POST['toplam_pesin_odeme_tutari'] : 0;
        $durum = $_POST['durum'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        
        $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, sozlesme_turu, toplam_anlasilan_miktar, kalan_anlasilan_miktar, baslangic_tarihi, bitis_tarihi, pesin_odeme_yapildi_mi, toplam_pesin_odeme_tutari, durum, olusturan, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssddssssddisss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $sozlesme_turu, $toplam_anlasilan_miktar, $toplam_anlasilan_miktar, $baslangic_tarihi, $bitis_tarihi, $pesin_odeme_yapildi_mi, $toplam_pesin_odeme_tutari, $durum, $_SESSION['kullanici_adi'], $aciklama);
        
        if ($stmt->execute()) {
            $message = "Çerçeve sözleşme başarıyla oluşturuldu.";
        } else {
            $error = "Çerçeve sözleşme oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update framework contract
        $sozlesme_id = $_POST['sozlesme_id'];
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $birim_fiyat = $_POST['birim_fiyat'];
        $para_birimi = $_POST['para_birimi'];
        $sozlesme_turu = $_POST['sozlesme_turu'];
        $toplam_anlasilan_miktar = $_POST['toplam_anlasilan_miktar'];
        $baslangic_tarihi = $_POST['baslangic_tarihi'];
        $bitis_tarihi = $_POST['bitis_tarihi'];
        $pesin_odeme_yapildi_mi = isset($_POST['pesin_odeme_yapildi_mi']) ? 1 : 0;
        $toplam_pesin_odeme_tutari = $pesin_odeme_yapildi_mi ? $_POST['toplam_pesin_odeme_tutari'] : 0;
        $durum = $_POST['durum'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        
        $query = "UPDATE cerceve_sozlesmeler SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim_fiyat = ?, para_birimi = ?, sozlesme_turu = ?, toplam_anlasilan_miktar = ?, baslangic_tarihi = ?, bitis_tarihi = ?, pesin_odeme_yapildi_mi = ?, toplam_pesin_odeme_tutari = ?, durum = ?, aciklama = ? WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssddssssddssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $sozlesme_turu, $toplam_anlasilan_miktar, $baslangic_tarihi, $bitis_tarihi, $pesin_odeme_yapildi_mi, $toplam_pesin_odeme_tutari, $durum, $aciklama, $sozlesme_id);
        
        if ($stmt->execute()) {
            $message = "Çerçeve sözleşme başarıyla güncellendi.";
        } else {
            $error = "Çerçeve sözleşme güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete framework contract
        $sozlesme_id = $_POST['sozlesme_id'];
        
        $query = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $sozlesme_id);
        
        if ($stmt->execute()) {
            $message = "Çerçeve sözleşme başarıyla silindi.";
        } else {
            $error = "Çerçeve sözleşme silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all framework contracts
$contracts_query = "SELECT * FROM cerceve_sozlesmeler ORDER BY olusturulma_tarihi DESC";
$contracts_result = $connection->query($contracts_query);

// Fetch all suppliers for dropdown
$suppliers_query = "SELECT * FROM tedarikciler ORDER BY tedarikci_adi";
$suppliers_result = $connection->query($suppliers_query);

// Fetch all materials for dropdown
$materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çerçeve Sözleşmeler - Parfüm ERP Sistemi</title>
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
        
        .form-group.checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .aktif { background-color: #28a745; }
        .tamamlandi { background-color: #17a2b8; }
        .iptal_edildi { background-color: #dc3545; }
        
        .remaining-amount {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Çerçeve Sözleşmeler Yönetimi</h1>
        <p>Tedarikçilerle yapılan çerçeve sözleşmeleri yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Sözleşme Güncelle' : 'Yeni Sözleşme Oluştur'; ?></h2>
            
            <?php
            $sozlesme_id = '';
            $tedarikci_id = '';
            $malzeme_kodu = '';
            $birim_fiyat = 0;
            $para_birimi = 'TL';
            $sozlesme_turu = 'sureli_birim_fiyatli';
            $toplam_anlasilan_miktar = 0;
            $baslangic_tarihi = date('Y-m-d');
            $bitis_tarihi = date('Y-m-d', strtotime('+1 year'));
            $pesin_odeme_yapildi_mi = 0;
            $toplam_pesin_odeme_tutari = 0;
            $durum = 'aktif';
            $aciklama = '';
            
            if (isset($_GET['edit'])) {
                $sozlesme_id = $_GET['edit'];
                $edit_query = "SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $sozlesme_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $contract = $result->fetch_assoc();
                
                if ($contract) {
                    $tedarikci_id = $contract['tedarikci_id'];
                    $malzeme_kodu = $contract['malzeme_kodu'];
                    $birim_fiyat = $contract['birim_fiyat'];
                    $para_birimi = $contract['para_birimi'];
                    $sozlesme_turu = $contract['sozlesme_turu'];
                    $toplam_anlasilan_miktar = $contract['toplam_anlasilan_miktar'];
                    $baslangic_tarihi = $contract['baslangic_tarihi'];
                    $bitis_tarihi = $contract['bitis_tarihi'];
                    $pesin_odeme_yapildi_mi = $contract['pesin_odeme_yapildi_mi'];
                    $toplam_pesin_odeme_tutari = $contract['toplam_pesin_odeme_tutari'];
                    $durum = $contract['durum'];
                    $aciklama = $contract['aciklama'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="sozlesme_id" value="<?php echo $sozlesme_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="tedarikci_id">Tedarikçi:</label>
                    <select id="tedarikci_id" name="tedarikci_id" required>
                        <option value="">Tedarikçi Seçin</option>
                        <?php while($supplier = $suppliers_result->fetch_assoc()): ?>
                            <option value="<?php echo $supplier['tedarikci_id']; ?>" 
                                <?php echo $tedarikci_id == $supplier['tedarikci_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="malzeme_kodu">Malzeme:</label>
                    <select id="malzeme_kodu" name="malzeme_kodu" required>
                        <option value="">Malzeme Seçin</option>
                        <?php while($material = $materials_result->fetch_assoc()): ?>
                            <option value="<?php echo $material['malzeme_kodu']; ?>" 
                                <?php echo $malzeme_kodu == $material['malzeme_kodu'] ? 'selected' : ''; ?>>
                                <?php echo $material['malzeme_kodu']; ?> - <?php echo htmlspecialchars($material['malzeme_ismi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="birim_fiyat">Birim Fiyat:</label>
                    <input type="number" id="birim_fiyat" name="birim_fiyat" value="<?php echo $birim_fiyat; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="para_birimi">Para Birimi:</label>
                    <select id="para_birimi" name="para_birimi" required>
                        <option value="TL" <?php echo $para_birimi === 'TL' ? 'selected' : ''; ?>>TL</option>
                        <option value="USD" <?php echo $para_birimi === 'USD' ? 'selected' : ''; ?>>USD</option>
                        <option value="EUR" <?php echo $para_birimi === 'EUR' ? 'selected' : ''; ?>>EUR</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sozlesme_turu">Sözleşme Türü:</label>
                    <select id="sozlesme_turu" name="sozlesme_turu" required>
                        <option value="pesin_odemeli_adet_anlasmasi" <?php echo $sozlesme_turu === 'pesin_odemeli_adet_anlasmasi' ? 'selected' : ''; ?>>Peşin Ödemeli Adet Anlaşması</option>
                        <option value="sureli_birim_fiyatli" <?php echo $sozlesme_turu === 'sureli_birim_fiyatli' ? 'selected' : ''; ?>>Süreli Birim Fiyatlı</option>
                        <option value="suresiz_birim_fiyatli" <?php echo $sozlesme_turu === 'suresiz_birim_fiyatli' ? 'selected' : ''; ?>>Süresiz Birim Fiyatlı</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="toplam_anlasilan_miktar">Toplam Anlaşilan Miktar:</label>
                    <input type="number" id="toplam_anlasilan_miktar" name="toplam_anlasilan_miktar" value="<?php echo $toplam_anlasilan_miktar; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="baslangic_tarihi">Başlangıç Tarihi:</label>
                    <input type="date" id="baslangic_tarihi" name="baslangic_tarihi" value="<?php echo $baslangic_tarihi; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bitis_tarihi">Bitiş Tarihi:</label>
                    <input type="date" id="bitis_tarihi" name="bitis_tarihi" value="<?php echo $bitis_tarihi; ?>" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="pesin_odeme_yapildi_mi" name="pesin_odeme_yapildi_mi" <?php echo $pesin_odeme_yapildi_mi ? 'checked' : ''; ?>>
                    <label for="pesin_odeme_yapildi_mi">Peşin Ödeme Yapıldı mı?</label>
                </div>
                
                <div class="form-group">
                    <label for="toplam_pesin_odeme_tutari">Toplam Peşin Ödeme Tutarı:</label>
                    <input type="number" id="toplam_pesin_odeme_tutari" name="toplam_pesin_odeme_tutari" value="<?php echo $toplam_pesin_odeme_tutari; ?>" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="durum">Durum:</label>
                    <select id="durum" name="durum" required>
                        <option value="aktif" <?php echo $durum === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="tamamlandi" <?php echo $durum === 'tamamlandi' ? 'selected' : ''; ?>>Tamamlandı</option>
                        <option value="iptal_edildi" <?php echo $durum === 'iptal_edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama"><?php echo htmlspecialchars($aciklama); ?></textarea>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="cerceve_sozlesmeler.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Sözleşme Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tedarikçi</th>
                        <th>Malzeme</th>
                        <th>Birim Fiyat</th>
                        <th>Para Birimi</th>
                        <th>Tür</th>
                        <th>Toplam Miktar</th>
                        <th>Kalan Miktar</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($contract = $contracts_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $contract['sozlesme_id']; ?></td>
                            <td><?php echo htmlspecialchars($contract['tedarikci_adi']); ?></td>
                            <td><?php echo htmlspecialchars($contract['malzeme_ismi']); ?></td>
                            <td><?php echo number_format($contract['birim_fiyat'], 2); ?></td>
                            <td><?php echo htmlspecialchars($contract['para_birimi']); ?></td>
                            <td>
                                <?php 
                                switch($contract['sozlesme_turu']) {
                                    case 'pesin_odemeli_adet_anlasmasi': echo 'Peşin Ödemeli Adet Anlaşması'; break;
                                    case 'sureli_birim_fiyatli': echo 'Süreli Birim Fiyatlı'; break;
                                    case 'suresiz_birim_fiyatli': echo 'Süresiz Birim Fiyatlı'; break;
                                    default: echo htmlspecialchars($contract['sozlesme_turu']); break;
                                }
                                ?>
                            </td>
                            <td><?php echo $contract['toplam_anlasilan_miktar']; ?></td>
                            <td class="remaining-amount"><?php echo $contract['kalan_anlasilan_miktar']; ?></td>
                            <td><?php echo $contract['baslangic_tarihi']; ?></td>
                            <td><?php echo $contract['bitis_tarihi']; ?></td>
                            <td>
                                <span class="status <?php echo $contract['durum']; ?>">
                                    <?php 
                                    switch($contract['durum']) {
                                        case 'aktif': echo 'Aktif'; break;
                                        case 'tamamlandi': echo 'Tamamlandı'; break;
                                        case 'iptal_edildi': echo 'İptal Edildi'; break;
                                        default: echo htmlspecialchars($contract['durum']); break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="cerceve_sozlesmeler.php?edit=<?php echo $contract['sozlesme_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu sözleşmeyi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="sozlesme_id" value="<?php echo $contract['sozlesme_id']; ?>">
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