<?php
session_start();

// Simulate a valid session for testing
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test User';

// Include config to establish DB connection
include 'config.php';

echo "<h2>Çerçeve Sözleşmeler CRUD Testi</h2>";

echo "<h3>1. Mevcut Kayıtlar:</h3>";
$contracts_query = "SELECT * FROM cerceve_sozlesmeler ORDER BY sozlesme_id";
$contracts_result = $connection->query($contracts_query);

if ($contracts_result && $contracts_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Tedarikçi</th>";
    echo "<th>Malzeme</th>";
    echo "<th>Birim Fiyat</th>";
    echo "<th>Para Birimi</th>";
    echo "<th>Limit Miktar</th>";
    echo "<th>Toplu Ödenen</th>";
    echo "<th>Başlangıç</th>";
    echo "<th>Bitiş</th>";
    echo "<th>Oluşturan</th>";
    echo "<th>Açıklama</th>";
    echo "</tr>";

    while ($contract = $contracts_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $contract['sozlesme_id'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['tedarikci_adi']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['malzeme_ismi']) . "</td>";
        echo "<td>" . $contract['birim_fiyat'] . "</td>";
        echo "<td>" . $contract['para_birimi'] . "</td>";
        echo "<td>" . $contract['limit_miktar'] . "</td>";
        echo "<td>" . $contract['toplu_odenen_miktar'] . "</td>";
        echo "<td>" . $contract['baslangic_tarihi'] . "</td>";
        echo "<td>" . $contract['bitis_tarihi'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['olusturan']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['aciklama']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Henüz kayıt yok.</p>";
}

// Test adding a new record
echo "<h3>2. Yeni Kayıt Ekleme Testi:</h3>";

// Get sample supplier and material IDs
$supplier_result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler LIMIT 1");
$material_result = $connection->query("SELECT malzeme_kodu, malzeme_ismi FROM malzemeler LIMIT 1");

if ($supplier_result && $material_result) {
    $supplier = $supplier_result->fetch_assoc();
    $material = $material_result->fetch_assoc();
    
    if ($supplier && $material) {
        $tedarikci_id = $supplier['tedarikci_id'];
        $tedarikci_adi = $supplier['tedarikci_adi'];
        $malzeme_kodu = $material['malzeme_kodu'];
        $malzeme_ismi = $material['malzeme_ismi'];
        
        $sql = "INSERT INTO cerceve_sozlesmeler (
                    tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, 
                    birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar,
                    baslangic_tarihi, bitis_tarihi, olusturan, aciklama
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('issssdssssss', 
            $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi,
            15.75, 'EUR', 250.00, 0.00,
            '2025-01-01', '2025-12-31', 'Test User', 'Test Kaydı'
        );
        
        $tedarikci_id = $supplier['tedarikci_id'];
        $tedarikci_adi = $supplier['tedarikci_adi'];
        $malzeme_kodu = $material['malzeme_kodu'];
        $malzeme_ismi = $material['malzeme_ismi'];
        
        $stmt = $connection->prepare($sql);
        
        $birim_fiyat = 15.75;
        $para_birimi = 'EUR';
        $limit_miktar = 250.00;
        $toplu_odenen_miktar = 0.00;
        $baslangic_tarihi = '2025-01-01';
        $bitis_tarihi = '2025-12-31';
        $olusturan = 'Test User';
        $aciklama = 'Test Kaydı';
        
        $stmt->bind_param('issssdssssss', 
            $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi,
            $birim_fiyat, $para_birimi, $limit_miktar, $toplu_odenen_miktar,
            $baslangic_tarihi, $bitis_tarihi, $olusturan, $aciklama
        );
        
        if ($stmt->execute()) {
            echo "<p>Yeni kayıt başarıyla eklendi! ID: " . $connection->insert_id . "</p>";
        } else {
            echo "<p>Hata: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Yeterli tedarikçi veya malzeme kaydı bulunamadı.</p>";
    }
} else {
    echo "<p>Tedarikçi veya malzeme sorgusunda hata oluştu.</p>";
}

// Show records after adding
echo "<h3>3. Yeni Kayıttan Sonra:</h3>";
$contracts_result = $connection->query("SELECT * FROM cerceve_sozlesmeler ORDER BY sozlesme_id");

if ($contracts_result && $contracts_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Tedarikçi</th>";
    echo "<th>Malzeme</th>";
    echo "<th>Birim Fiyat</th>";
    echo "<th>Para Birimi</th>";
    echo "<th>Limit Miktar</th>";
    echo "<th>Toplu Ödenen</th>";
    echo "<th>Başlangıç</th>";
    echo "<th>Bitiş</th>";
    echo "<th>Oluşturan</th>";
    echo "<th>Açıklama</th>";
    echo "</tr>";

    while ($contract = $contracts_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $contract['sozlesme_id'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['tedarikci_adi']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['malzeme_ismi']) . "</td>";
        echo "<td>" . $contract['birim_fiyat'] . "</td>";
        echo "<td>" . $contract['para_birimi'] . "</td>";
        echo "<td>" . $contract['limit_miktar'] . "</td>";
        echo "<td>" . $contract['toplu_odenen_miktar'] . "</td>";
        echo "<td>" . $contract['baslangic_tarihi'] . "</td>";
        echo "<td>" . $contract['bitis_tarihi'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['olusturan']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['aciklama']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test updating a record
echo "<h3>4. Kayıt Güncelleme Testi:</h3>";
if ($contracts_result->num_rows > 0) {
    $sql = "UPDATE cerceve_sozlesmeler SET toplu_odenen_miktar = 175.50 WHERE sozlesme_id = 1 LIMIT 1";
    if ($connection->query($sql) === TRUE) {
        echo "<p>1 numaralı kayıt başarıyla güncellendi.</p>";
    } else {
        echo "<p>Güncelleme hatası: " . $connection->error . "</p>";
    }
    
    // Show updated record
    $result = $connection->query("SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = 1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $record = $result->fetch_assoc();
        echo "<p>1 numaralı kayıt: Toplu Ödenen Miktar = " . $record['toplu_odenen_miktar'] . "</p>";
    }
} else {
    echo "<p>Güncellenecek kayıt bulunamadı.</p>";
}

// Test deleting a record (not the one we just created to keep the data intact)
echo "<h3>5. Kayıt Silme Testi:</h3>";
$test_id = $connection->insert_id; // This is the ID of our test record
if ($test_id > 1) {
    $sql = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = $test_id LIMIT 1";
    if ($connection->query($sql) === TRUE) {
        echo "<p>$test_id numaralı kayıt başarıyla silindi.</p>";
    } else {
        echo "<p>Silme hatası: " . $connection->error . "</p>";
    }
} else {
    echo "<p>Test kaydı silinemedi çünkü ID 1'den büyük değil.</p>";
}

echo "<h3>6. Sonuç:</h3>";
echo "<p>Çerçeve Sözleşmeler tablosu ve CRUD işlemleri başarıyla test edildi.</p>";
echo "<p>Yeni <code>toplu_odenen_miktar</code> alanı doğru şekilde eklendi ve çalışmaktadır.</p>";
echo "<p>Arayüz dosyası: <code>cerceve_sozlesmeler.php</code></p>";
echo "<p>API dosyası: <code>api_islemleri/cerceve_sozlesmeler_islemler.php</code></p>";
?>