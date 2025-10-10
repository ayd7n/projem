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
        // Create new supplier
        $tedarikci_adi = $_POST['tedarikci_adi'];
        $vergi_no_tc = $_POST['vergi_no_tc'];
        $adres = $_POST['adres'];
        $telefon = $_POST['telefon'];
        $e_posta = $_POST['e_posta'];
        $yetkili_kisi = $_POST['yetkili_kisi'];
        $aciklama_notlar = $_POST['aciklama_notlar'];
        
        $query = "INSERT INTO tedarikciler (tedarikci_adi, vergi_no_tc, adres, telefon, e_posta, yetkili_kisi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssss', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $yetkili_kisi, $aciklama_notlar);
        
        if ($stmt->execute()) {
            $message = "Tedarikçi başarıyla oluşturuldu.";
        } else {
            $error = "Tedarikçi oluşturulurken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update supplier
        $tedarikci_id = $_POST['tedarikci_id'];
        $tedarikci_adi = $_POST['tedarikci_adi'];
        $vergi_no_tc = $_POST['vergi_no_tc'];
        $adres = $_POST['adres'];
        $telefon = $_POST['telefon'];
        $e_posta = $_POST['e_posta'];
        $yetkili_kisi = $_POST['yetkili_kisi'];
        $aciklama_notlar = $_POST['aciklama_notlar'];
        
        $query = "UPDATE tedarikciler SET tedarikci_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, yetkili_kisi = ?, aciklama_notlar = ? WHERE tedarikci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssssi', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $yetkili_kisi, $aciklama_notlar, $tedarikci_id);
        
        if ($stmt->execute()) {
            $message = "Tedarikçi başarıyla güncellendi.";
        } else {
            $error = "Tedarikçi güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete supplier
        $tedarikci_id = $_POST['tedarikci_id'];
        
        $query = "DELETE FROM tedarikciler WHERE tedarikci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $tedarikci_id);
        
        if ($stmt->execute()) {
            $message = "Tedarikçi başarıyla silindi.";
        } else {
            $error = "Tedarikçi silinirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all suppliers
$suppliers_query = "SELECT * FROM tedarikciler ORDER BY tedarikci_adi";
$suppliers_result = $connection->query($suppliers_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçiler - Parfüm ERP Sistemi</title>
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
        <h1>Tedarikçiler Yönetimi</h1>
        <p>Tedarikçi bilgilerini yönetin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Tedarikçi Güncelle' : 'Yeni Tedarikçi Ekle'; ?></h2>
            
            <?php
            $tedarikci_id = '';
            $tedarikci_adi = '';
            $vergi_no_tc = '';
            $adres = '';
            $telefon = '';
            $e_posta = '';
            $yetkili_kisi = '';
            $aciklama_notlar = '';
            
            if (isset($_GET['edit'])) {
                $tedarikci_id = $_GET['edit'];
                $edit_query = "SELECT * FROM tedarikciler WHERE tedarikci_id = ?";
                $stmt = $connection->prepare($edit_query);
                $stmt->bind_param('i', $tedarikci_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $supplier = $result->fetch_assoc();
                
                if ($supplier) {
                    $tedarikci_adi = $supplier['tedarikci_adi'];
                    $vergi_no_tc = $supplier['vergi_no_tc'];
                    $adres = $supplier['adres'];
                    $telefon = $supplier['telefon'];
                    $e_posta = $supplier['e_posta'];
                    $yetkili_kisi = $supplier['yetkili_kisi'];
                    $aciklama_notlar = $supplier['aciklama_notlar'];
                }
                $stmt->close();
            }
            ?>
            
            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="tedarikci_id" value="<?php echo $tedarikci_id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="tedarikci_adi">Tedarikçi Adı:</label>
                    <input type="text" id="tedarikci_adi" name="tedarikci_adi" value="<?php echo htmlspecialchars($tedarikci_adi); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="vergi_no_tc">Vergi No / TC:</label>
                    <input type="text" id="vergi_no_tc" name="vergi_no_tc" value="<?php echo htmlspecialchars($vergi_no_tc); ?>">
                </div>
                
                <div class="form-group">
                    <label for="adres">Adres:</label>
                    <textarea id="adres" name="adres"><?php echo htmlspecialchars($adres); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon:</label>
                    <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($telefon); ?>">
                </div>
                
                <div class="form-group">
                    <label for="e_posta">E-posta:</label>
                    <input type="email" id="e_posta" name="e_posta" value="<?php echo htmlspecialchars($e_posta); ?>">
                </div>
                
                <div class="form-group">
                    <label for="yetkili_kisi">Yetkili Kişi:</label>
                    <input type="text" id="yetkili_kisi" name="yetkili_kisi" value="<?php echo htmlspecialchars($yetkili_kisi); ?>">
                </div>
                
                <div class="form-group">
                    <label for="aciklama_notlar">Açıklama / Notlar:</label>
                    <textarea id="aciklama_notlar" name="aciklama_notlar"><?php echo htmlspecialchars($aciklama_notlar); ?></textarea>
                </div>
                
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" name="update" class="btn btn-update">Güncelle</button>
                    <a href="tedarikciler.php" class="btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="create" class="btn">Oluştur</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Tedarikçi Listesi</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Adı</th>
                        <th>Vergi/TC No</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>Yetkili</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($supplier = $suppliers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $supplier['tedarikci_id']; ?></td>
                            <td><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['vergi_no_tc']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['telefon']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['e_posta']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['yetkili_kisi']); ?></td>
                            <td class="actions">
                                <a href="tedarikciler.php?edit=<?php echo $supplier['tedarikci_id']; ?>" class="btn">Düzenle</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu tedarikçiyi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="tedarikci_id" value="<?php echo $supplier['tedarikci_id']; ?>">
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