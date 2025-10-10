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
        // Create new incoming quality control record
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $red_edilen_miktar = $_POST['red_edilen_miktar'];
        $red_nedeni = $_POST['red_nedeni'];
        $ilgili_belge_no = $_POST['ilgili_belge_no'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme details
        $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $birim = $malzeme['birim'];
        
        $query = "INSERT INTO giris_kalite_kontrolu (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim, reddedilen_miktar, red_nedeni, kontrol_eden_personel_id, kontrol_eden_personel_adi, ilgili_belge_no, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssdssiss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $_SESSION['id'], $_SESSION['kullanici_adi'], $ilgili_belge_no, $aciklama);
        
        if ($stmt->execute()) {
            $message = "Giriş kalite kontrolü kaydı başarıyla oluşturuldu.";
        } else {
            $error = "Giriş kalite kontrolü kaydı oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update incoming quality control record
        $kontrol_id = $_POST['kontrol_id'];
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $red_edilen_miktar = $_POST['red_edilen_miktar'];
        $red_nedeni = $_POST['red_nedeni'];
        $ilgili_belge_no = $_POST['ilgili_belge_no'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme details
        $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $birim = $malzeme['birim'];
        
        $query = "UPDATE giris_kalite_kontrolu SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim = ?, reddedilen_miktar = ?, red_nedeni = ?, ilgili_belge_no = ?, aciklama = ? WHERE kontrol_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssdsssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $ilgili_belge_no, $aciklama, $kontrol_id);
        
        if ($stmt->execute()) {
            $message = "Giriş kalite kontrolü kaydı başarıyla güncellendi.";
        } else {
            $error = "Giriş kalite kontrolü kaydı güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete incoming quality control record
        $kontrol_id = $_POST['kontrol_id'];
        
        $query = "DELETE FROM giris_kalite_kontrolu WHERE kontrol_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $kontrol_id);
        
        if ($stmt->execute()) {
            $message = "Giriş kalite kontrolü kaydı başarıyla silindi.";
        } else {
            $error = "Giriş kalite kontrolü kaydı silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all quality control records
$controls_query = "SELECT * FROM giris_kalite_kontrolu ORDER BY tarih DESC";
$controls_result = $connection->query($controls_query);

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
    <title>Giriş Kalite Kontrolü - Parfüm ERP Sistemi</title>
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
        
        .red-nedeni {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        
        .eksik { background-color: #ffc107; }
        .gedikli { background-color: #fd7e14; }
        .kirlenmis { background-color: #6f42c1; }
        .yanlis_malzeme { background-color: #e83e8c; }
        .diger { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Giriş Kalite Kontrolü</h1>
        <p>Tedarik edilen malzemelerin kalite kontrolünü yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Kalite Kontrolü Güncelle' : 'Yeni Kalite Kontrolü Ekle'; ?></h2>
            
            <?php
            $kontrol_id = '';
            $tedarikci_id = '';
            $malzeme_kodu = '';
            $red_edilen_miktar = 0;
            $red_nedeni = '';
            $ilgili_belge_no = '';
            $aciklama = '';
            
            if (isset($_GET['edit'])) {
                $kontrol_id = $_GET['edit'];
                $edit_query = "SELECT * FROM giris_kalite_kontrolu WHERE kontrol_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $kontrol_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $control = $result->fetch_assoc();
                
                if ($control) {
                    $tedarikci_id = $control['tedarikci_id'];
                    $malzeme_kodu = $control['malzeme_kodu'];
                    $red_edilen_miktar = $control['reddedilen_miktar'];
                    $red_nedeni = $control['red_nedeni'];
                    $ilgili_belge_no = $control['ilgili_belge_no'];
                    $aciklama = $control['aciklama'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="kontrol_id" value="<?php echo $kontrol_id; ?>">
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
                    <label for="red_edilen_miktar">Red Edilen Miktar:</label>
                    <input type="number" id="red_edilen_miktar" name="red_edilen_miktar" value="<?php echo $red_edilen_miktar; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="red_nedeni">Red Nedeni:</label>
                    <select id="red_nedeni" name="red_nedeni" required>
                        <option value="eksik" <?php echo $red_nedeni === 'eksik' ? 'selected' : ''; ?>>Eksik</option>
                        <option value="gedikli" <?php echo $red_nedeni === 'gedikli' ? 'selected' : ''; ?>>Gedikli/Bozuk</option>
                        <option value="kirlenmis" <?php echo $red_nedeni === 'kirlenmis' ? 'selected' : ''; ?>>Kirli/Kirlenmiş</option>
                        <option value="yanlis_malzeme" <?php echo $red_nedeni === 'yanlis_malzeme' ? 'selected' : ''; ?>>Yanlış Malzeme</option>
                        <option value="diger" <?php echo $red_nedeni === 'diger' ? 'selected' : ''; ?>>Diğer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ilgili_belge_no">İlgili Belge No:</label>
                    <input type="text" id="ilgili_belge_no" name="ilgili_belge_no" value="<?php echo htmlspecialchars($ilgili_belge_no); ?>">
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama"><?php echo htmlspecialchars($aciklama); ?></textarea>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="giris_kalite_kontrolu.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Kalite Kontrol Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarih</th>
                        <th>Tedarikçi</th>
                        <th>Malzeme</th>
                        <th>Birim</th>
                        <th>Red Miktarı</th>
                        <th>Red Nedeni</th>
                        <th>Belge No</th>
                        <th>Kontrol Eden</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($control = $controls_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $control['kontrol_id']; ?></td>
                            <td><?php echo $control['tarih']; ?></td>
                            <td><?php echo htmlspecialchars($control['tedarikci_adi']); ?></td>
                            <td><?php echo htmlspecialchars($control['malzeme_ismi']); ?></td>
                            <td><?php echo htmlspecialchars($control['birim']); ?></td>
                            <td><?php echo $control['reddedilen_miktar']; ?></td>
                            <td>
                                <span class="red-nedeni <?php echo str_replace('_', '-', $control['red_nedeni']); ?>">
                                    <?php 
                                    switch($control['red_nedeni']) {
                                        case 'eksik': echo 'Eksik'; break;
                                        case 'gedikli': echo 'Gedikli/Bozuk'; break;
                                        case 'kirlenmis': echo 'Kirli/Kirlenmiş'; break;
                                        case 'yanlis_malzeme': echo 'Yanlış Malzeme'; break;
                                        case 'diger': echo 'Diğer'; break;
                                        default: echo htmlspecialchars($control['red_nedeni']); break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($control['ilgili_belge_no']); ?></td>
                            <td><?php echo htmlspecialchars($control['kontrol_eden_personel_adi']); ?></td>
                            <td class="actions">
                                <a href="giris_kalite_kontrolu.php?edit=<?php echo $control['kontrol_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kalite kontrol kaydını silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="kontrol_id" value="<?php echo $control['kontrol_id']; ?>">
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