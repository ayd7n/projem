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
        // Create new stock movement
        $stok_turu = $_POST['stok_turu'];
        $kod = $_POST['kod'];
        $miktar = $_POST['miktar'];
        $yon = $_POST['yon'];
        $hareket_turu = $_POST['hareket_turu'];
        $depo = isset($_POST['depo']) ? $_POST['depo'] : '';
        $raf = isset($_POST['raf']) ? $_POST['raf'] : '';
        $tank_kodu = isset($_POST['tank_kodu']) ? $_POST['tank_kodu'] : '';
        $aciklama = $_POST['aciklama'];
        $ilgili_belge_no = isset($_POST['ilgili_belge_no']) ? $_POST['ilgili_belge_no'] : '';
        $is_emri_numarasi = isset($_POST['is_emri_numarasi']) ? $_POST['is_emri_numarasi'] : null;
        $musteri_id = isset($_POST['musteri_id']) ? $_POST['musteri_id'] : null;
        
        // Get item name and unit based on stock type
        $item_name = '';
        $item_unit = '';
        
        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                $item_stmt->execute();
                $item_result = $item_stmt->get_result();
                $item = $item_result->fetch_assoc();
                $item_name = $item['malzeme_ismi'];
                $item_unit = $item['birim'];
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('s', $kod);
                $item_stmt->execute();
                $item_result = $item_stmt->get_result();
                $item = $item_result->fetch_assoc();
                $item_name = $item['esans_ismi'];
                $item_unit = $item['birim'];
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                $item_stmt->execute();
                $item_result = $item_stmt->get_result();
                $item = $item_result->fetch_assoc();
                $item_name = $item['urun_ismi'];
                $item_unit = $item['birim'];
                break;
        }
        
        // Insert stock movement
        $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, is_emri_numarasi, musteri_id, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $movement_stmt = $connection->prepare($movement_query);
        $movement_stmt->bind_param('ssssdsssssisiiis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $is_emri_numarasi, $musteri_id, $aciklama, $_SESSION['id'], $_SESSION['kullanici_adi']);
        
        if ($movement_stmt->execute()) {
            // Update stock based on movement
            if ($stok_turu === 'malzeme') {
                $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + ? WHERE malzeme_kodu = ?";
                $stock_stmt = $connection->prepare($stock_query);
                $direction = ($yon === 'giris') ? $miktar : -$miktar;
                $stock_stmt->bind_param('di', $direction, $kod);
            } elseif ($stok_turu === 'esans') {
                $stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari + ? WHERE esans_kodu = ?";
                $stock_stmt = $connection->prepare($stock_query);
                $direction = ($yon === 'giris') ? $miktar : -$miktar;
                $stock_stmt->bind_param('ds', $direction, $kod);
            } else { // urun
                $stock_query = "UPDATE urunler SET stok_miktari = stok_miktari + ? WHERE urun_kodu = ?";
                $stock_stmt = $connection->prepare($stock_query);
                $direction = ($yon === 'giris') ? $miktar : -$miktar;
                $stock_stmt->bind_param('ii', $direction, $kod);
            }
            
            if ($stock_stmt->execute()) {
                $message = "Stok hareketi başarıyla kaydedildi.";
            } else {
                $error = "Stok hareketi kaydedildi ama stok güncellenirken hata oluştu: " . $connection->error;
            }
            $stock_stmt->close();
        } else {
            $error = "Stok hareketi kaydedilirken hata oluştu: " . $connection->error;
        }
        $movement_stmt->close();
    }
}

// Fetch all stock movements
$movement_query = "SELECT * FROM stok_hareket_kayitlari ORDER BY tarih DESC LIMIT 100";
$movement_result = $connection->query($movement_query);

// Fetch all locations for dropdown
$locations_query = "SELECT * FROM lokasyonlar ORDER BY depo_ismi, raf";
$locations_result = $connection->query($locations_query);

// Fetch all tanks for dropdown
$tanks_query = "SELECT * FROM tanklar ORDER BY tank_ismi";
$tanks_result = $connection->query($tanks_query);

// Fetch all materials for dropdown
$materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

// Fetch all essences for dropdown
$essences_query = "SELECT * FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Fetch all products for dropdown
$products_query = "SELECT * FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuel Stok Hareket - Parfüm ERP Sistemi</title>
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
        
        .current-stock, .after-movement-stock {
            font-weight: bold;
            padding: 5px;
            border-radius: 4px;
        }
        
        .current-stock {
            background-color: #e7f3ff;
        }
        
        .after-movement-stock {
            background-color: #e8f5e9;
        }
        
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manuel Stok Hareket Kaydı</h1>
        <p>Manuel olarak stok hareketlerini kaydedin</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2>Yeni Stok Hareketi Ekle</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="stok_turu">Stok Türü:</label>
                    <select id="stok_turu" name="stok_turu" required>
                        <option value="malzeme">Malzeme</option>
                        <option value="esans">Esans</option>
                        <option value="urun">Ürün</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kod">Kod Seçin:</label>
                    <select id="kod" name="kod" required>
                        <option value="">Kod Seçin</option>
                        <!-- Options will be populated based on stock type -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="yon">Yön:</label>
                    <select id="yon" name="yon" required>
                        <option value="giris">Giriş</option>
                        <option value="cikis">Çıkış</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="hareket_turu">Hareket Türü:</label>
                    <select id="hareket_turu" name="hareket_turu" required>
                        <option value="stok_giris">Stok Girişi</option>
                        <option value="stok_cikis">Stok Çıkışı</option>
                        <option value="uretim">Üretim</option>
                        <option value="uretimde_kullanim">Üretimde Kullanım</option>
                        <option value="fire">Fire</option>
                        <option value="sayim_farki">Sayım Farkı</option>
                        <option value="stok_duzeltme">Stok Düzeltme</option>
                        <option value="iade_girisi">İade Girişi</option>
                        <option value="tedarikciye_iade">TedarikçİYE İade</option>
                        <option value="numune_cikisi">Numune Çıkışı</option>
                        <option value="montaj">Montaj</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="miktar">Miktar:</label>
                    <input type="number" id="miktar" name="miktar" min="0.01" step="0.01" required>
                </div>
                
                <div id="location-fields">
                    <!-- Location fields will be shown/hidden based on stock type -->
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="ilgili_belge_no">İlgili Belge No:</label>
                    <input type="text" id="ilgili_belge_no" name="ilgili_belge_no">
                </div>
                
                <button type="submit" name="create" class="btn">Kaydet</button>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Son Stok Hareketleri</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarih</th>
                        <th>Stok Türü</th>
                        <th>Kod</th>
                        <th>İsim</th>
                        <th>Miktar</th>
                        <th>Yön</th>
                        <th>Hareket Türü</th>
                        <th>Depo/Raf/Tank</th>
                        <th>Belge No</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($movement = $movement_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $movement['hareket_id']; ?></td>
                            <td><?php echo $movement['tarih']; ?></td>
                            <td><?php echo htmlspecialchars($movement['stok_turu']); ?></td>
                            <td><?php echo htmlspecialchars($movement['kod']); ?></td>
                            <td><?php echo htmlspecialchars($movement['isim']); ?></td>
                            <td><?php echo $movement['miktar']; ?></td>
                            <td><?php echo htmlspecialchars($movement['yon']); ?></td>
                            <td><?php echo htmlspecialchars($movement['hareket_turu']); ?></td>
                            <td>
                                <?php 
                                if (!empty($movement['depo'])) {
                                    echo htmlspecialchars($movement['depo']);
                                    if (!empty($movement['raf'])) {
                                        echo ' / ' . htmlspecialchars($movement['raf']);
                                    }
                                } elseif (!empty($movement['tank_kodu'])) {
                                    echo 'Tank: ' . htmlspecialchars($movement['tank_kodu']);
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($movement['ilgili_belge_no']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <a href="navigation.php" class="logout">Ana Sayfaya Dön</a>
    
    <script>
        // Update the kod dropdown based on stock type
        document.getElementById('stok_turu').addEventListener('change', function() {
            const stokTuru = this.value;
            const kodSelect = document.getElementById('kod');
            kodSelect.innerHTML = '<option value="">Kod Seçin</option>';
            
            // Show/hide location fields based on stock type
            const locationDiv = document.getElementById('location-fields');
            
            if (stokTuru === 'malzeme' || stokTuru === 'urun') {
                locationDiv.innerHTML = `
                    <div class="form-group">
                        <label for="depo">Depo:</label>
                        <select id="depo" name="depo" required>
                            <option value="">Depo Seçin</option>
                            <?php 
                            $locations_result->data_seek(0);
                            while($location = $locations_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($location['depo_ismi']); ?>">
                                    <?php echo htmlspecialchars($location['depo_ismi']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="raf">Raf:</label>
                        <input type="text" id="raf" name="raf" required>
                    </div>
                `;
            } else if (stokTuru === 'esans') {
                locationDiv.innerHTML = `
                    <div class="form-group">
                        <label for="tank_kodu">Tank Kodu:</label>
                        <select id="tank_kodu" name="tank_kodu" required>
                            <option value="">Tank Seçin</option>
                            <?php 
                            $tanks_result->data_seek(0);
                            while($tank = $tanks_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($tank['tank_kodu']); ?>">
                                    <?php echo htmlspecialchars($tank['tank_kodu']); ?> - <?php echo htmlspecialchars($tank['tank_ismi']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                `;
            } else {
                locationDiv.innerHTML = '';
            }
            
            // Populate the kod dropdown based on selected stock type
            <?php 
            $materials_result->data_seek(0);
            $materials = [];
            while($material = $materials_result->fetch_assoc()) {
                $materials[] = array('kod' => $material['malzeme_kodu'], 'isim' => $material['malzeme_ismi'], 'stok' => $material['stok_miktari']);
            }
            
            $essences_result->data_seek(0);
            $essences = [];
            while($essence = $essences_result->fetch_assoc()) {
                $essences[] = array('kod' => $essence['esans_kodu'], 'isim' => $essence['esans_ismi'], 'stok' => $essence['stok_miktari']);
            }
            
            $products_result->data_seek(0);
            $products = [];
            while($product = $products_result->fetch_assoc()) {
                $products[] = array('kod' => $product['urun_kodu'], 'isim' => $product['urun_ismi'], 'stok' => $product['stok_miktari']);
            }
            ?>
            
            if (stokTuru === 'malzeme') {
                <?php foreach ($materials as $material): ?>
                    const option = document.createElement('option');
                    option.value = <?php echo $material['kod']; ?>;
                    option.textContent = '<?php echo $material['kod']; ?> - <?php echo addslashes($material['isim']); ?> (Stok: <?php echo $material['stok']; ?>)';
                    kodSelect.appendChild(option);
                <?php endforeach; ?>
            } else if (stokTuru === 'esans') {
                <?php foreach ($essences as $essence): ?>
                    const option = document.createElement('option');
                    option.value = '<?php echo addslashes($essence['kod']); ?>';
                    option.textContent = '<?php echo addslashes($essence['kod']); ?> - <?php echo addslashes($essence['isim']); ?> (Stok: <?php echo $essence['stok']; ?>)';
                    kodSelect.appendChild(option);
                <?php endforeach; ?>
            } else if (stokTuru === 'urun') {
                <?php foreach ($products as $product): ?>
                    const option = document.createElement('option');
                    option.value = <?php echo $product['kod']; ?>;
                    option.textContent = '<?php echo $product['kod']; ?> - <?php echo addslashes($product['isim']); ?> (Stok: <?php echo $product['stok']; ?>)';
                    kodSelect.appendChild(option);
                <?php endforeach; ?>
            }
        });
        
        // Trigger change event to initialize the form
        document.getElementById('stok_turu').dispatchEvent(new Event('change'));
    </script>
</body>
</html>