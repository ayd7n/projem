<?php
// Direct API testing script without web interface
include 'config.php';

echo "Testing API functions directly...\n";

// Function to simulate POST data and test API functionality
function test_api_function($action, $data = []) {
    // Set up a mock session for testing
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Set up a test session (this would normally come from login)
    $_SESSION['user_id'] = 1;
    $_SESSION['taraf'] = 'personel';
    $_SESSION['kullanici_adi'] = 'test_user';
    
    // Simulate $_POST data
    $_POST = $data;
    $_GET = ['action' => $action] + $data;
    
    // Capture output
    ob_start();
    
    // Set action variable 
    $action_var = $action;
    
    // Execute API logic directly for testing
    switch ($action_var) {
        case 'get_all_contracts':
            $query = "SELECT * FROM cerceve_sozlesmeler ORDER BY olusturulma_tarihi DESC";
            $result = $connection->query($query);

            if ($result) {
                $contracts = [];
                while ($row = $result->fetch_assoc()) {
                    $contracts[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $contracts]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veri alınırken hata oluştu: ' . $connection->error]);
            }
            break;
            
        case 'get_contract':
            $id = $data['id'] ?? 0;

            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
                break;
            }

            $query = "SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $contract = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $contract]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
            }
            $stmt->close();
            break;
            
        case 'add_contract':
            // Note: This is simplified version for testing - same as the actual API
            $tedarikci_id = $data['tedarikci_id'] ?? '';
            $malzeme_kodu = $data['malzeme_kodu'] ?? '';
            $birim_fiyat = $data['birim_fiyat'] ?? 0;
            $para_birimi = $data['para_birimi'] ?? '';
            $limit_miktar = isset($data['limit_miktar']) ? $data['limit_miktar'] : '';
            $toplu_odenen_miktar = $data['toplu_odenen_miktar'] ?? 0;
            $baslangic_tarihi = $data['baslangic_tarihi'] ?? '';
            $bitis_tarihi = $data['bitis_tarihi'] ?? '';
            $aciklama = $data['aciklama'] ?? '';

            // Validation (fixed version)
            if (!$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
                echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
                break;
            }

            // Get tedarikci name
            $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
            $tedarikci_stmt = $connection->prepare($tedarikci_query);
            $tedarikci_stmt->bind_param('i', $tedarikci_id);
            $tedarikci_stmt->execute();
            $tedarikci_result = $tedarikci_stmt->get_result();
            if ($tedarikci_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
                $tedarikci_stmt->close();
                break;
            }
            $tedarikci = $tedarikci_result->fetch_assoc();
            $tedarikci_adi = $tedarikci['tedarikci_adi'];
            $tedarikci_stmt->close();

            // Get malzeme name
            $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
            $malzeme_stmt = $connection->prepare($malzeme_query);
            $malzeme_stmt->bind_param('i', $malzeme_kodu);
            $malzeme_stmt->execute();
            $malzeme_result = $malzeme_stmt->get_result();
            if ($malzeme_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
                $malzeme_stmt->close();
                break;
            }
            $malzeme = $malzeme_result->fetch_assoc();
            $malzeme_ismi = $malzeme['malzeme_ismi'];
            $malzeme_stmt->close();

            // Insert contract
            $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, aciklama, olusturan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('isssddssssss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $limit_miktar, $toplu_odenen_miktar, $baslangic_tarihi, $bitis_tarihi, $aciklama, $_SESSION['kullanici_adi']);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla oluşturuldu.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme oluşturulurken hata oluştu: ' . $connection->error]);
            }
            $stmt->close();
            break;
            
        case 'update_contract':
            // Note: This is simplified version for testing - same as the actual API
            $sozlesme_id = $data['sozlesme_id'] ?? 0;
            $tedarikci_id = $data['tedarikci_id'] ?? '';
            $malzeme_kodu = $data['malzeme_kodu'] ?? '';
            $birim_fiyat = $data['birim_fiyat'] ?? 0;
            $para_birimi = $data['para_birimi'] ?? '';
            $limit_miktar = isset($data['limit_miktar']) ? $data['limit_miktar'] : '';
            $toplu_odenen_miktar = $data['toplu_odenen_miktar'] ?? 0;
            $baslangic_tarihi = $data['baslangic_tarihi'] ?? '';
            $bitis_tarihi = $data['bitis_tarihi'] ?? '';
            $aciklama = $data['aciklama'] ?? '';

            // Validation (fixed version)
            if (!$sozlesme_id || !$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
                echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
                break;
            }

            // Get tedarikci name
            $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
            $tedarikci_stmt = $connection->prepare($tedarikci_query);
            $tedarikci_stmt->bind_param('i', $tedarikci_id);
            $tedarikci_stmt->execute();
            $tedarikci_result = $tedarikci_stmt->get_result();
            if ($tedarikci_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
                $tedarikci_stmt->close();
                break;
            }
            $tedarikci = $tedarikci_result->fetch_assoc();
            $tedarikci_adi = $tedarikci['tedarikci_adi'];
            $tedarikci_stmt->close();

            // Get malzeme name
            $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
            $malzeme_stmt = $connection->prepare($malzeme_query);
            $malzeme_stmt->bind_param('i', $malzeme_kodu);
            $malzeme_stmt->execute();
            $malzeme_result = $malzeme_stmt->get_result();
            if ($malzeme_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
                $malzeme_stmt->close();
                break;
            }
            $malzeme = $malzeme_result->fetch_assoc();
            $malzeme_ismi = $malzeme['malzeme_ismi'];
            $malzeme_stmt->close();

            // Update contract
            $query = "UPDATE cerceve_sozlesmeler SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim_fiyat = ?, para_birimi = ?, limit_miktar = ?, toplu_odenen_miktar = ?, baslangic_tarihi = ?, bitis_tarihi = ?, aciklama = ? WHERE sozlesme_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('isssddsssssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $limit_miktar, $toplu_odenen_miktar, $baslangic_tarihi, $bitis_tarihi, $aciklama, $sozlesme_id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla güncellendi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme güncellenirken hata oluştu: ' . $connection->error]);
            }
            $stmt->close();
            break;
            
        case 'delete_contract':
            $sozlesme_id = $data['sozlesme_id'] ?? 0;

            if (!$sozlesme_id) {
                echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
                break;
            }

            $query = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $sozlesme_id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla silindi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme silinirken hata oluştu: ' . $connection->error]);
            }
            $stmt->close();
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
            break;
    }
    
    $output = ob_get_contents();
    ob_end_clean();
    
    return json_decode($output, true) ?: ['status' => 'error', 'message' => 'Invalid JSON output', 'output' => $output];
}

// Test 1: Get all contracts
echo "\n1. Testing get_all_contracts...\n";
$result = test_api_function('get_all_contracts');
echo "Result: " . json_encode($result) . "\n";

// Test 2: Get specific contract (try to get one if available)
echo "\n2. Testing get_contract...\n";
// Get the first contract ID if available
$contracts_result = $connection->query("SELECT sozlesme_id FROM cerceve_sozlesmeler LIMIT 1");
if ($contracts_result && $row = $contracts_result->fetch_assoc()) {
    $test_id = $row['sozlesme_id'];
    $result = test_api_function('get_contract', ['id' => $test_id]);
    echo "Result for contract ID $test_id: " . json_encode($result) . "\n";
} else {
    echo "No contracts available to test get_contract\n";
}

// Test 3: Try to add a test contract (using valid data)
echo "\n3. Testing add_contract...\n";
// We need to get valid tedarikci_id and malzeme_kodu from the existing data
$tedarikci_result = $connection->query("SELECT tedarikci_id FROM tedarikciler LIMIT 1");
$malzeme_result = $connection->query("SELECT malzeme_kodu FROM malzemeler LIMIT 1");

if ($tedarikci_result && $ted_row = $tedarikci_result->fetch_assoc() && 
    $malzeme_result && $mal_row = $malzeme_result->fetch_assoc()) {
    
    $result = test_api_function('add_contract', [
        'tedarikci_id' => $ted_row['tedarikci_id'],
        'malzeme_kodu' => $mal_row['malzeme_kodu'],
        'birim_fiyat' => 50.00,
        'para_birimi' => 'USD',
        'limit_miktar' => 100,
        'toplu_odenen_miktar' => 0,
        'baslangic_tarihi' => date('Y-m-d'),
        'bitis_tarihi' => date('Y-m-d', strtotime('+30 days')),
        'aciklama' => 'Test contract'
    ]);
    echo "Add contract result: " . json_encode($result) . "\n";
} else {
    echo "Cannot test add_contract because no tedarikci or malzeme records exist\n";
}

// Clean up: Remove the test contract if the add was successful
if (isset($result) && $result['status'] === 'success') {
    echo "\n4. Testing delete_contract (cleaning up test record)...\n";
    
    // Get the last inserted ID to delete it
    $last_result = $connection->query("SELECT sozlesme_id FROM cerceve_sozlesmeler ORDER BY sozlesme_id DESC LIMIT 1");
    if ($last_result && $last_row = $last_result->fetch_assoc()) {
        $last_id = $last_row['sozlesme_id'];
        $delete_result = test_api_function('delete_contract', ['sozlesme_id' => $last_id]);
        echo "Delete test contract (ID: $last_id) result: " . json_encode($delete_result) . "\n";
    }
}

echo "\nDirect API testing completed.\n";
?>