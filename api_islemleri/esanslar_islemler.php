<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'error_code' => 'ES001', 'message' => 'Yetkisiz erişim']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'error_code' => 'ES002', 'message' => 'Yetkisiz erişim']);
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_all_essences':
            if (!yetkisi_var('page:view:esanslar')) {
                echo json_encode(['status' => 'error', 'message' => 'Esansları görüntüleme yetkiniz yok.']);
                exit;
            }
            $essences_query = "SELECT * FROM esanslar ORDER BY esans_ismi";
            $essences_result = $connection->query($essences_query);
            
            if ($essences_result) {
                $essences = [];
                while ($row = $essences_result->fetch_assoc()) {
                    $essences[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $essences]);
            } else {
                echo json_encode(['status' => 'error', 'error_code' => 'ES003', 'message' => 'Esanslar alınırken bir hata oluştu']);
            }
            break;
            
        case 'get_essence':
            if (!yetkisi_var('page:view:esanslar')) {
                echo json_encode(['status' => 'error', 'message' => 'Esans görüntüleme yetkiniz yok.']);
                exit;
            }
            $esans_id = $_GET['id'] ?? null;
            if ($esans_id) {
                $stmt = $connection->prepare("SELECT * FROM esanslar WHERE esans_id = ?");
                $stmt->bind_param("i", $esans_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $essence = $result->fetch_assoc();
                    echo json_encode(['status' => 'success', 'data' => $essence]);
                } else {
                    echo json_encode(['status' => 'error', 'error_code' => 'ES004', 'message' => 'Esans bulunamadı']);
                }
            } else {
                echo json_encode(['status' => 'error', 'error_code' => 'ES005', 'message' => 'Geçersiz esans ID']);
            }
            break;
            
        default:
            echo json_encode(['status' => 'error', 'error_code' => 'ES006', 'message' => 'Geçersiz işlem']);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle both form data and JSON data
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        // JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
    } else {
        // Form data
        $action = $_POST['action'] ?? '';
        $input = $_POST;
    }
    
    switch ($action) {
        case 'add_essence':
            if (!yetkisi_var('action:esanslar:create')) {
                echo json_encode(['status' => 'error', 'message' => 'Yeni esans ekleme yetkiniz yok.']);
                exit;
            }
            $esans_kodu = $input['esans_kodu'] ?? '';
            $esans_ismi = $input['esans_ismi'] ?? '';
            $stok_miktari = floatval($input['stok_miktari'] ?? 0);
            $birim = $input['birim'] ?? 'ml';
            $demlenme_suresi_gun = intval($input['demlenme_suresi_gun'] ?? 0);
            $not_bilgisi = $input['not_bilgisi'] ?? '';
            $tank_kodu = $input['tank_kodu'] ?? null;
            $tank_ismi = $input['tank_ismi'] ?? null;
            
            // Check if esans_kodu already exists
            $check_query = "SELECT esans_id FROM esanslar WHERE esans_kodu = ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param("s", $esans_kodu);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                echo json_encode(['status' => 'error', 'error_code' => 'ES007', 'message' => 'Bu esans kodu zaten mevcut']);
                exit;
            }
            
            $stmt = $connection->prepare("INSERT INTO esanslar (esans_kodu, esans_ismi, stok_miktari, birim, demlenme_suresi_gun, not_bilgisi, tank_kodu, tank_ismi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsssss", $esans_kodu, $esans_ismi, $stok_miktari, $birim, $demlenme_suresi_gun, $not_bilgisi, $tank_kodu, $tank_ismi);
            
            if ($stmt->execute()) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$esans_ismi esansı sisteme eklendi", 'CREATE');
                echo json_encode(['status' => 'success', 'message' => 'Esans başarıyla eklendi']);
            } else {
                echo json_encode(['status' => 'error', 'error_code' => 'ES008', 'message' => 'Esans eklenirken bir hata oluştu']);
            }
            break;
            
        case 'update_essence':
            if (!yetkisi_var('action:esanslar:edit')) {
                echo json_encode(['status' => 'error', 'message' => 'Esans düzenleme yetkiniz yok.']);
                exit;
            }
            $esans_id = $input['esans_id'] ?? null;
            $esans_kodu = $input['esans_kodu'] ?? '';
            $esans_ismi = $input['esans_ismi'] ?? '';
            $stok_miktari = floatval($input['stok_miktari'] ?? 0);
            $birim = $input['birim'] ?? 'ml';
            $demlenme_suresi_gun = intval($input['demlenme_suresi_gun'] ?? 0);
            $not_bilgisi = $input['not_bilgisi'] ?? '';
            $tank_kodu = $input['tank_kodu'] ?? null;
            $tank_ismi = $input['tank_ismi'] ?? null;
            
            if ($esans_id) {
                // Check if another esans with the same code exists (excluding current esans)
                $check_query = "SELECT esans_id FROM esanslar WHERE esans_kodu = ? AND esans_id != ?";
                $check_stmt = $connection->prepare($check_query);
                $check_stmt->bind_param("si", $esans_kodu, $esans_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    echo json_encode(['status' => 'error', 'error_code' => 'ES009', 'message' => 'Bu esans kodu başka bir esans tarafından kullanılıyor']);
                    exit;
                }
                
                $stmt = $connection->prepare("UPDATE esanslar SET esans_kodu = ?, esans_ismi = ?, stok_miktari = ?, birim = ?, demlenme_suresi_gun = ?, not_bilgisi = ?, tank_kodu = ?, tank_ismi = ? WHERE esans_id = ?");
                $stmt->bind_param("ssdsssssi", $esans_kodu, $esans_ismi, $stok_miktari, $birim, $demlenme_suresi_gun, $not_bilgisi, $tank_kodu, $tank_ismi, $esans_id);
                
                if ($stmt->execute()) {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$esans_ismi esansı güncellendi", 'UPDATE');
                    echo json_encode(['status' => 'success', 'message' => 'Esans başarıyla güncellendi']);
                } else {
                    echo json_encode(['status' => 'error', 'error_code' => 'ES010', 'message' => 'Esans güncellenirken bir hata oluştu']);
                }
            } else {
                echo json_encode(['status' => 'error', 'error_code' => 'ES011', 'message' => 'Geçersiz esans ID']);
            }
            break;
            
        case 'delete_essence':
            if (!yetkisi_var('action:esanslar:delete')) {
                echo json_encode(['status' => 'error', 'message' => 'Esans silme yetkiniz yok.']);
                exit;
            }
            $esans_id = $input['esans_id'] ?? null;
            
            if ($esans_id) {
                $stmt = $connection->prepare("DELETE FROM esanslar WHERE esans_id = ?");
                $stmt->bind_param("i", $esans_id);
                
                // Silinen esans bilgilerini al
                $get_esans_query = "SELECT esans_ismi FROM esanslar WHERE esans_id = ?";
                $get_stmt = $connection->prepare($get_esans_query);
                $get_stmt->bind_param("i", $esans_id);
                $get_stmt->execute();
                $get_result = $get_stmt->get_result();
                $esans_ismi = "Bilinmeyen Esans";
                if ($get_result && $row = $get_result->fetch_assoc()) {
                    $esans_ismi = $row['esans_ismi'];
                }

                if ($stmt->execute()) {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$esans_ismi esansı sistemden silindi", 'DELETE');
                    echo json_encode(['status' => 'success', 'message' => 'Esans başarıyla silindi']);
                } else {
                    echo json_encode(['status' => 'error', 'error_code' => 'ES012', 'message' => 'Esans silinirken bir hata oluştu']);
                }
            } else {
                echo json_encode(['status' => 'error', 'error_code' => 'ES013', 'message' => 'Geçersiz esans ID']);
            }
            break;
            
        default:
            echo json_encode(['status' => 'error', 'error_code' => 'ES014', 'message' => 'Geçersiz işlem']);
    }
}
?>