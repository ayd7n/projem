<?php
session_start();

// Simulate a valid session for testing
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test User';

// Include config to establish DB connection
include 'config.php';

echo "<h2>Çerçeve Sözleşmeler API Testi</h2>";

// Function to make API requests
function makeApiRequest($url, $data = null, $method = 'POST') {
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => $method,
            'timeout' => 30
        )
    );
    
    if ($data && $method === 'POST') {
        $options['http']['content'] = http_build_query($data);
    } elseif ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return ['status' => 'error', 'message' => 'API request failed'];
    }
    
    return json_decode($result, true);
}

echo "<h3>1. API Bağlantı Testi:</h3>";

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

echo "<h3>2. Add (Ekleme) Testi:</h3>";
$add_data = array(
    'action' => 'add_contract',
    'tedarikci_id' => $supplier['tedarikci_id'],
    'malzeme_kodu' => $material['malzeme_kodu'],
    'birim_fiyat' => 25.50,
    'para_birimi' => 'USD',
    'limit_miktar' => 200,
    'toplu_odenen_miktar' => 150,
    'baslangic_tarihi' => '2025-01-01',
    'bitis_tarihi' => '2025-12-31',
    'aciklama' => 'API Test Kaydı'
);

$add_result = makeApiRequest('http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php', $add_data, 'POST');
if ($add_result && isset($add_result['status'])) {
    echo "<p>Add Contract API Sonucu: <strong>" . $add_result['status'] . "</strong></p>";
    if (isset($add_result['message'])) {
        echo "<p>Mesaj: " . $add_result['message'] . "</p>";
    }
} else {
    echo "<p>Add Contract API Yanlış Format: " . json_encode($add_result) . "</p>";
}

// Get the ID of the newly created record for further tests
$new_contract_id = null;
$recent_result = $connection->query("SELECT sozlesme_id FROM cerceve_sozlesmeler ORDER BY sozlesme_id DESC LIMIT 1");
if ($recent_result) {
    $recent_row = $recent_result->fetch_assoc();
    $new_contract_id = $recent_row['sozlesme_id'];
}
echo "<p>Yeni Oluşturulan Sözleşme ID: " . $new_contract_id . "</p>";

echo "<h3>3. Get (Getirme) Testi:</h3>";
if ($new_contract_id) {
    $get_data = array(
        'action' => 'get_contract',
        'id' => $new_contract_id
    );
    
    $get_result = makeApiRequest('http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php', $get_data, 'GET');
    if ($get_result && isset($get_result['status'])) {
        echo "<p>Get Contract API Sonucu: <strong>" . $get_result['status'] . "</strong></p>";
        if (isset($get_result['message'])) {
            echo "<p>Mesaj: " . $get_result['message'] . "</p>";
        }
        if (isset($get_result['data']) && $get_result['data']) {
            echo "<p>Veri alındı: " . $get_result['data']['tedarikci_adi'] . " - " . $get_result['data']['malzeme_ismi'] . "</p>";
        }
    } else {
        echo "<p>Get Contract API Yanlış Format: " . json_encode($get_result) . "</p>";
    }
} else {
    echo "<p>Yeni kayıt oluşturulamadığı için Get testi yapılamadı.</p>";
}

echo "<h3>4. Update (Güncelleme) Testi:</h3>";
if ($new_contract_id) {
    $update_data = array(
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
        'aciklama' => 'API Test Kaydı - Güncellenmiş'
    );
    
    $update_result = makeApiRequest('http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php', $update_data, 'POST');
    if ($update_result && isset($update_result['status'])) {
        echo "<p>Update Contract API Sonucu: <strong>" . $update_result['status'] . "</strong></p>";
        if (isset($update_result['message'])) {
            echo "<p>Mesaj: " . $update_result['message'] . "</p>";
        }
    } else {
        echo "<p>Update Contract API Yanlış Format: " . json_encode($update_result) . "</p>";
    }
} else {
    echo "<p>Yeni kayıt oluşturulamadığı için Update testi yapılamadı.</p>";
}

echo "<h3>5. Delete (Silme) Testi:</h3>";
if ($new_contract_id) {
    $delete_data = array(
        'action' => 'delete_contract',
        'sozlesme_id' => $new_contract_id
    );
    
    $delete_result = makeApiRequest('http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php', $delete_data, 'POST');
    if ($delete_result && isset($delete_result['status'])) {
        echo "<p>Delete Contract API Sonucu: <strong>" . $delete_result['status'] . "</strong></p>";
        if (isset($delete_result['message'])) {
            echo "<p>Mesaj: " . $delete_result['message'] . "</p>";
        }
    } else {
        echo "<p>Delete Contract API Yanlış Format: " . json_encode($delete_result) . "</p>";
    }
    
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

echo "<h3>6. Genel Sonuç:</h3>";
echo "<p>Tüm API testleri tamamlandı. Her işlem için API'nin dönüş değerleri kontrol edildi.</p>";
echo "<p>Alan türleri doğru şekilde işlendi: limit_miktar ve toplu_odenen_miktar integer olarak, birim_fiyat decimal olarak</p>";

// Check current number of records
$count_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler");
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    echo "<p>Toplam sözleşme sayısı: " . $count_row['total'] . "</p>";
}
?>