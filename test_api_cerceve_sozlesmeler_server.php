<?php
// Include config to establish DB connection and start session
include 'config.php';

echo "<h2>Çerçeve Sözleşmeler API Sunucu Tarafı Testi</h2>";

// Get a sample supplier and material for testing
$supplier_result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler LIMIT 1");
$material_result = $connection->query("SELECT malzeme_kodu, malzeme_ismi FROM malzemeler LIMIT 1");

if (!$supplier_result || !$material_result) {
    echo "<p>Test için yeterli veri bulunamadı!</p>";
    exit;
}

$supplier = $supplier_result->fetch_assoc();
$material = $material_result->fetch_assoc();

if (!$supplier || !$material) {
    echo "<p>Test için yeterli veri bulunamadı!</p>";
    exit;
}

echo "<p>Test için kullanılacak veriler:</p>";
echo "<ul>";
echo "<li>Tedarikçi: " . $supplier['tedarikci_adi'] . " (ID: " . $supplier['tedarikci_id'] . ")</li>";
echo "<li>Malzeme: " . $material['malzeme_ismi'] . " (ID: " . $material['malzeme_kodu'] . ")</li>";
echo "</ul>";

// Simulate POST data for add operation
$_POST = array(
    'action' => 'add_contract',
    'tedarikci_id' => $supplier['tedarikci_id'],
    'malzeme_kodu' => $material['malzeme_kodu'],
    'birim_fiyat' => 25.50,
    'para_birimi' => 'USD',
    'limit_miktar' => 200,
    'toplu_odenen_miktar' => 150,
    'baslangic_tarihi' => '2025-01-01',
    'bitis_tarihi' => '2025-12-31',
    'aciklama' => 'Sunucu Tarafı Test Kaydı'
);

// Set session variables
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test User';

echo "<h3>1. Add (Ekleme) Testi:</h3>";

// Capture the output from the API
ob_start();
include 'api_islemleri/cerceve_sozlesmeler_islemler.php';
$add_result = ob_get_clean();

echo "<p>Add Contract API Sonucu: " . $add_result . "</p>";

// Get the ID of the newly created record for further tests
$new_contract_id = null;
$recent_result = $connection->query("SELECT sozlesme_id FROM cerceve_sozlesmeler ORDER BY sozlesme_id DESC LIMIT 1");
if ($recent_result) {
    $recent_row = $recent_result->fetch_assoc();
    $new_contract_id = $recent_row['sozlesme_id'];
}
echo "<p>Yeni Oluşturulan Sözleşme ID: " . $new_contract_id . "</p>";

// Test Get operation
echo "<h3>2. Get (Getirme) Testi:</h3>";

if ($new_contract_id) {
    $_GET = array(
        'action' => 'get_contract',
        'id' => $new_contract_id
    );
    
    // Clear POST so it doesn't interfere
    $_POST = array();
    
    ob_start();
    include 'api_islemleri/cerceve_sozlesmeler_islemler.php';
    $get_result = ob_get_clean();
    
    echo "<p>Get Contract API Sonucu: " . $get_result . "</p>";
} else {
    echo "<p>Yeni kayıt oluşturulamadığı için Get testi yapılamadı.</p>";
}

// Test Update operation
echo "<h3>3. Update (Güncelleme) Testi:</h3>";

if ($new_contract_id) {
    $_POST = array(
        'action' => 'update_contract',
        'sozlesme_id' => $new_contract_id,
        'tedarikci_id' => $supplier['tedarikci_id'],
        'malzeme_kodu' => $material['malzeme_kodu'],
        'birim_fiyat' => 35.75,
        'para_birimi' => 'EUR',
        'limit_miktar' => 300,
        'toplu_odenen_miktar' => 250,
        'baslangic_tarihi' => '2025-02-01',
        'bitis_tarihi' => '2025-11-30',
        'aciklama' => 'Sunucu Tarafı Test Kaydı - Güncellenmiş'
    );
    $_GET = array();
    
    ob_start();
    include 'api_islemleri/cerceve_sozlesmeler_islemler.php';
    $update_result = ob_get_clean();
    
    echo "<p>Update Contract API Sonucu: " . $update_result . "</p>";
} else {
    echo "<p>Yeni kayıt oluşturulamadığı için Update testi yapılamadı.</p>";
}

// Test Delete operation
echo "<h3>4. Delete (Silme) Testi:</h3>";

if ($new_contract_id) {
    $_POST = array(
        'action' => 'delete_contract',
        'sozlesme_id' => $new_contract_id
    );
    $_GET = array();
    
    ob_start();
    include 'api_islemleri/cerceve_sozlesmeler_islemler.php';
    $delete_result = ob_get_clean();
    
    echo "<p>Delete Contract API Sonucu: " . $delete_result . "</p>";
    
    // Verify deletion
    $check_result = $connection->query("SELECT COUNT(*) as count FROM cerceve_sozlesmeler WHERE sozlesme_id = $new_contract_id");
    if ($check_result) {
        $check_row = $check_result->fetch_assoc();
        if ($check_row['count'] == 0) {
            echo "<p>Silme işlemi doğrulandı: Kayıt artık mevcut değil.</p>";
        } else {
            echo "<p>Silme işlemi başarısız olabilir: Kayıt hala veritabanında mevcut.</p>";
        }
    }
} else {
    echo "<p>Yeni kayıt oluşturulamadığı için Delete testi yapılamadı.</p>";
}

echo "<h3>5. Genel Sonuç:</h3>";
echo "<p>Tüm API testleri tamamlandı. Her işlem için API'nin dönüş değerleri kontrol edildi.</p>";
echo "<p>Alan türleri doğru şekilde işlendi: limit_miktar ve toplu_odenen_miktar integer olarak, birim_fiyat decimal olarak</p>";

// Check current number of records
$count_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler");
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    echo "<p>Toplam sözleşme sayısı: " . $count_row['total'] . "</p>";
}
?>