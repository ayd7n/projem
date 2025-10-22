<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get the action from the request (handle both form data and JSON)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
if (!$action) {
    // If action not in GET/POST, check in JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['action'])) {
        $action = $input['action'];
    }
}

// Handle different actions
switch ($action) {
    case 'get_work_orders':
        getWorkOrders();
        break;
    case 'get_work_order':
        getWorkOrder();
        break;
    case 'get_essences':
        getEssences();
        break;
    case 'get_materials':
        getMaterials();
        break;
    case 'get_tanks':
        getTanks();
        break;
    case 'create_work_order':
        createWorkOrder();
        break;
    case 'update_work_order':
        updateWorkOrder();
        break;
    case 'delete_work_order':
        deleteWorkOrder();
        break;
    case 'calculate_components':
        calculateComponents();
        break;
    case 'get_components':
        getComponents();
        break;
    case 'get_work_order_components':
        getWorkOrderComponents();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function getWorkOrders() {
    global $connection;
    
    try {
        $query = "SELECT * FROM esans_is_emirleri ORDER BY olusturulma_tarihi DESC";
        $result = $connection->query($query);
        
        $workOrders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $workOrders[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $workOrders]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getWorkOrder() {
    global $connection;
    
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Work order ID is required');
        }
        
        $query = "SELECT * FROM esans_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        $result = $connection->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            throw new Exception('Work order not found');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getEssences() {
    global $connection;
    
    try {
        $query = "SELECT esans_kodu, esans_ismi, birim, demlenme_suresi_gun FROM esanslar ORDER BY esans_ismi";
        $result = $connection->query($query);
        
        $essences = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $essences[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $essences]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getMaterials() {
    global $connection;
    
    try {
        $query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu FROM malzemeler ORDER BY malzeme_ismi";
        $result = $connection->query($query);
        
        $materials = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $materials]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getTanks() {
    global $connection;
    
    try {
        $query = "SELECT DISTINCT tank_kodu, tank_ismi FROM tanklar ORDER BY tank_ismi";
        $result = $connection->query($query);
        
        $tanks = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tanks[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $tanks]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function createWorkOrder() {
    global $connection;
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    try {
        $work_order = $input['work_order'] ?? [];
        $components = $input['components'] ?? [];
        $user = $_SESSION['kullanici_adi'] ?? 'Sistem';
        
        // Begin transaction
        $connection->begin_transaction();
        
        // Insert work order
        $query = "INSERT INTO esans_is_emirleri (olusturulma_tarihi, olusturan, esans_kodu, esans_ismi, tank_kodu, tank_ismi, 
                  planlanan_miktar, birim, planlanan_baslangic_tarihi, demlenme_suresi_gun, planlanan_bitis_tarihi, 
                  aciklama, durum, tamamlanan_miktar, eksik_miktar_toplami) 
                  VALUES ('" . $connection->real_escape_string($work_order['olusturulma_tarihi']) . "', 
                  '" . $connection->real_escape_string($user) . "', 
                  '" . $connection->real_escape_string($work_order['esans_kodu']) . "', 
                  '" . $connection->real_escape_string($work_order['esans_ismi']) . "', 
                  '" . $connection->real_escape_string($work_order['tank_kodu']) . "', 
                  '" . $connection->real_escape_string($work_order['tank_ismi']) . "', 
                  '" . $connection->real_escape_string($work_order['planlanan_miktar']) . "', 
                  '" . $connection->real_escape_string($work_order['birim']) . "', 
                  '" . $connection->real_escape_string($work_order['planlanan_baslangic_tarihi']) . "', 
                  '" . $connection->real_escape_string($work_order['demlenme_suresi_gun']) . "', 
                  '" . $connection->real_escape_string($work_order['planlanan_bitis_tarihi']) . "', 
                  '" . $connection->real_escape_string($work_order['aciklama'] ?? '') . "', 
                  '" . $connection->real_escape_string($work_order['durum']) . "', 
                  '" . $connection->real_escape_string($work_order['tamamlanan_miktar'] ?? 0) . "', 
                  '" . $connection->real_escape_string($work_order['eksik_miktar_toplami'] ?? 0) . "')";
        
        if ($connection->query($query)) {
            $work_order_id = $connection->insert_id;
            
            // Insert components
            foreach ($components as $component) {
                $comp_query = "INSERT INTO esans_is_emri_malzeme_listesi (is_emri_numarasi, malzeme_kodu, malzeme_ismi, 
                         malzeme_turu, miktar, birim) 
                         VALUES ('" . $connection->real_escape_string($work_order_id) . "', 
                         '" . $connection->real_escape_string($component['malzeme_kodu']) . "', 
                         '" . $connection->real_escape_string($component['malzeme_ismi']) . "', 
                         '" . $connection->real_escape_string($component['malzeme_turu']) . "', 
                         '" . $connection->real_escape_string($component['miktar']) . "', 
                         '" . $connection->real_escape_string($component['birim']) . "')";
                
                if (!$connection->query($comp_query)) {
                    throw new Exception("Component insertion failed: " . $connection->error);
                }
            }
            
            // Commit transaction
            $connection->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Esans iş emri başarıyla oluşturuldu']);
        } else {
            throw new Exception("Work order insertion failed: " . $connection->error);
        }
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateWorkOrder() {
    global $connection;
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    try {
        $work_order = $input['work_order'] ?? [];
        $components = $input['components'] ?? [];
        $user = $_SESSION['kullanici_adi'] ?? 'Sistem';
        
        // Begin transaction
        $connection->begin_transaction();
        
        // Update work order
        $query = "UPDATE esans_is_emirleri 
                  SET olusturan = '" . $connection->real_escape_string($user) . "', 
                      esans_kodu = '" . $connection->real_escape_string($work_order['esans_kodu']) . "', 
                      esans_ismi = '" . $connection->real_escape_string($work_order['esans_ismi']) . "', 
                      tank_kodu = '" . $connection->real_escape_string($work_order['tank_kodu']) . "', 
                      tank_ismi = '" . $connection->real_escape_string($work_order['tank_ismi']) . "', 
                      planlanan_miktar = '" . $connection->real_escape_string($work_order['planlanan_miktar']) . "', 
                      birim = '" . $connection->real_escape_string($work_order['birim']) . "', 
                      planlanan_baslangic_tarihi = '" . $connection->real_escape_string($work_order['planlanan_baslangic_tarihi']) . "', 
                      demlenme_suresi_gun = '" . $connection->real_escape_string($work_order['demlenme_suresi_gun']) . "', 
                      planlanan_bitis_tarihi = '" . $connection->real_escape_string($work_order['planlanan_bitis_tarihi']) . "', 
                      aciklama = '" . $connection->real_escape_string($work_order['aciklama'] ?? '') . "', 
                      durum = '" . $connection->real_escape_string($work_order['durum']) . "' 
                  WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order['is_emri_numarasi']) . "'";
        
        if ($connection->query($query)) {
            // Delete existing components for this work order
            $delete_query = "DELETE FROM esans_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order['is_emri_numarasi']) . "'";
            $connection->query($delete_query);
            
            // Insert new components
            foreach ($components as $component) {
                $comp_query = "INSERT INTO esans_is_emri_malzeme_listesi (is_emri_numarasi, malzeme_kodu, malzeme_ismi, 
                         malzeme_turu, miktar, birim) 
                         VALUES ('" . $connection->real_escape_string($work_order['is_emri_numarasi']) . "', 
                         '" . $connection->real_escape_string($component['malzeme_kodu']) . "', 
                         '" . $connection->real_escape_string($component['malzeme_ismi']) . "', 
                         '" . $connection->real_escape_string($component['malzeme_turu']) . "', 
                         '" . $connection->real_escape_string($component['miktar']) . "', 
                         '" . $connection->real_escape_string($component['birim']) . "')";
                
                if (!$connection->query($comp_query)) {
                    throw new Exception("Component update failed: " . $connection->error);
                }
            }
            
            // Commit transaction
            $connection->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Esans iş emri başarıyla güncellendi']);
        } else {
            throw new Exception("Work order update failed: " . $connection->error);
        }
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteWorkOrder() {
    global $connection;
    
    try {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Work order ID is required');
        }
        
        // Begin transaction
        $connection->begin_transaction();
        
        // Delete components first
        $delete_components_query = "DELETE FROM esans_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        $connection->query($delete_components_query);
        
        // Then delete work order
        $delete_work_order_query = "DELETE FROM esans_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        
        if ($connection->query($delete_work_order_query)) {
            // Commit transaction
            $connection->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Esans iş emri başarıyla silindi']);
        } else {
            throw new Exception("Work order deletion failed: " . $connection->error);
        }
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function calculateComponents() {
    global $connection;
    
    try {
        // Try to get data from JSON input first
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !is_array($input)) {
            // If JSON fails, try form data (POST/GET)
            $input = array_merge($_GET, $_POST);
        }
        
        $essence_code = $input['essence_code'] ?? null;
        $quantity = $input['quantity'] ?? null;
        
        if (!$essence_code) {
            throw new Exception('Essence code is required');
        }
        
        if ($quantity === null || $quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }
        
        // First, find the essence ID from the essence code
        $essence_query = "SELECT esans_id FROM esanslar WHERE esans_kodu = '" . $connection->real_escape_string($essence_code) . "'";
        $essence_result = $connection->query($essence_query);
        
        if (!$essence_result || $essence_result->num_rows == 0) {
            throw new Exception('Essence not found');
        }
        
        $essence_row = $essence_result->fetch_assoc();
        $essence_id = $essence_row['esans_id'];
        
        // Get essence components from urun_agaci table where agac_turu = 'esans'
        $query = "SELECT bilesen_kodu, bilesen_ismi, bilesenin_malzeme_turu, bilesen_miktari 
                  FROM urun_agaci 
                  WHERE urun_kodu = '" . $connection->real_escape_string($essence_id) . "' 
                  AND agac_turu = 'esans'";
        
        $result = $connection->query($query);
        
        $components = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $components[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $components]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getComponents() {
    global $connection;
    
    try {
        $work_order_id = $_GET['work_order_id'] ?? null;
        
        if (!$work_order_id) {
            throw new Exception('Work order ID is required');
        }
        
        $query = "SELECT * FROM esans_is_emri_malzeme_listesi 
                  WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order_id) . "'";
        
        $result = $connection->query($query);
        
        $components = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $components[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $components]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getWorkOrderComponents() {
    global $connection;
    
    try {
        $work_order_id = $_GET['id'] ?? null;
        
        if (!$work_order_id) {
            throw new Exception('Work order ID is required');
        }
        
        // Get the essence code from the work order to find its components
        $work_order_query = "SELECT esans_kodu, planlanan_miktar FROM esans_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order_id) . "'";
        $work_order_result = $connection->query($work_order_query);
        
        if (!$work_order_result || $work_order_result->num_rows == 0) {
            throw new Exception('Work order not found');
        }
        
        $work_order = $work_order_result->fetch_assoc();
        $essence_code = $work_order['esans_kodu'];
        $quantity = $work_order['planlanan_miktar'];
        
        // Get the essence ID to find its components in urun_agaci
        $essence_query = "SELECT esans_id FROM esanslar WHERE esans_kodu = '" . $connection->real_escape_string($essence_code) . "'";
        $essence_result = $connection->query($essence_query);
        
        if (!$essence_result || $essence_result->num_rows == 0) {
            throw new Exception('Essence not found');
        }
        
        $essence_row = $essence_result->fetch_assoc();
        $essence_id = $essence_row['esans_id'];
        
        // Get essence components from urun_agaci table where agac_turu = 'esans'
        $query = "SELECT bilesen_kodu as malzeme_kodu, bilesen_ismi as malzeme_ismi, 
                         bilesenin_malzeme_turu as malzeme_turu, bilesen_miktari
                  FROM urun_agaci 
                  WHERE urun_kodu = '" . $connection->real_escape_string($essence_id) . "' 
                  AND agac_turu = 'esans'";
        
        $result = $connection->query($query);
        
        $components = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Calculate the required amount: original component amount * actual quantity for this work order
                $calculated_amount = floatval($row['bilesen_miktari']) * floatval($quantity);
                
                $components[] = [
                    'malzeme_kodu' => $row['malzeme_kodu'],
                    'malzeme_ismi' => $row['malzeme_ismi'],
                    'malzeme_turu' => $row['malzeme_turu'],
                    'miktar' => $calculated_amount,
                    'birim' => $row['bilesen_miktari']  // Original per unit amount
                ];
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $components]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>