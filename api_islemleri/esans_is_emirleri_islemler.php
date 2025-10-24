<?php
require_once '../config.php';
date_default_timezone_set('Europe/Istanbul');

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
    case 'start_work_order':
        startWorkOrder();
        break;
    case 'revert_work_order':
        revertWorkOrder();
        break;
    case 'revert_completion':
        revertCompletion();
        break;
    case 'complete_work_order':
        completeWorkOrder();
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
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;
        
        // Count total records for the last 1000 by creation date
        $count_query = "SELECT COUNT(*) as total FROM (
            SELECT is_emri_numarasi FROM esans_is_emirleri 
            ORDER BY olusturulma_tarihi DESC 
            LIMIT 1000
        ) AS subquery";
        $count_result = $connection->query($count_query);
        $total_records = $count_result->fetch_assoc()['total'];
        
        // Fetch last 1000 records by creation date with pagination
        $query = "SELECT * FROM (
            SELECT * FROM esans_is_emirleri 
            ORDER BY olusturulma_tarihi DESC 
            LIMIT 1000
        ) AS subquery 
        ORDER BY olusturulma_tarihi DESC 
        LIMIT $limit OFFSET $offset";
        
        $result = $connection->query($query);
        
        $workOrders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $workOrders[] = $row;
            }
        }
        
        echo json_encode([
            'status' => 'success', 
            'data' => $workOrders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total_records,
                'total_pages' => ceil($total_records / $limit)
            ]
        ]);
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
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    try {
        $id = $input['id'] ?? null;
        
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

function startWorkOrder() {
    global $connection;
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $id = $input['id'] ?? null;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'İş emri numarası gerekli.']);
        return;
    }

    try {
        $connection->begin_transaction();

        // 1. Update work order status
        $today = date('Y-m-d');
        $update_wo_query = "UPDATE esans_is_emirleri 
                            SET durum = 'uretimde', 
                                gerceklesen_baslangic_tarihi = '$today'
                            WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'
                            AND durum = 'olusturuldu'";
        
        if (!$connection->query($update_wo_query)) {
            throw new Exception("İş emri durumu güncellenirken bir hata oluştu: " . $connection->error);
        }

        if ($connection->affected_rows == 0) {
            throw new Exception('İş emri başlatılamadı. Durumu "Oluşturuldu" olmalı veya zaten başlatılmış olabilir.');
        }

        // 2. Get components for the work order
        $components_query = "SELECT * FROM esans_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        $components_result = $connection->query($components_query);
        
        if ($components_result->num_rows == 0) {
            // If there are no components, we can just commit and return success.
            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla başlatıldı. (Bileşen bulunmuyor)']);
            return;
        }

        $kaydeden_personel_id = $_SESSION['user_id'] ?? null;
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? 'Sistem';

        // 3. Deduct component stocks
        while ($component = $components_result->fetch_assoc()) {
            $malzeme_kodu = $component['malzeme_kodu'];
            $miktar = floatval($component['miktar']);

            // 3a. Update material stock
            $update_material_stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari - '" . $connection->real_escape_string($miktar) . "' WHERE malzeme_kodu = '" . $connection->real_escape_string($malzeme_kodu) . "'";
            if (!$connection->query($update_material_stock_query)) {
                throw new Exception("Malzeme stok miktarı güncellenemedi: " . $connection->error);
            }

            // 3b. Log stock movement
            $stock_out_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                                VALUES ('Bileşen', 
                                        '" . $connection->real_escape_string($component['malzeme_kodu']) . "', 
                                        '" . $connection->real_escape_string($component['malzeme_ismi']) . "', 
                                        '" . $connection->real_escape_string($component['birim']) . "', 
                                        '" . $connection->real_escape_string($miktar) . "', 
                                        'Çıkış', 
                                        'Üretime Çıkış', 
                                        '" . $connection->real_escape_string($id) . "', 
                                        'İş emri başlatıldı', 
                                        '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                        '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
            if (!$connection->query($stock_out_query)) {
                throw new Exception("Bileşen stok çıkışı yapılamadı: " . $connection->error);
            }
        }

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla başlatıldı ve bileşenler stoktan düşüldü.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function revertWorkOrder() {
    global $connection;
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $id = $input['id'] ?? null;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'İş emri numarası gerekli.']);
        return;
    }

    try {
        $connection->begin_transaction();

        // 1. Update work order status
        $update_wo_query = "UPDATE esans_is_emirleri 
                            SET durum = 'olusturuldu', 
                                gerceklesen_baslangic_tarihi = NULL
                            WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'
                            AND durum = 'uretimde'";
        
        if (!$connection->query($update_wo_query)) {
            throw new Exception("İş emri durumu güncellenirken bir hata oluştu: " . $connection->error);
        }

        if ($connection->affected_rows == 0) {
            throw new Exception('İş emri geri alınamadı. Durumu "Üretimde" olmalı.');
        }

        // 2. Get components for the work order
        $components_query = "SELECT * FROM esans_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        $components_result = $connection->query($components_query);
        
        if ($components_result->num_rows == 0) {
            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla "Oluşturuldu" durumuna geri alındı. (Bileşen bulunmuyor)']);
            return;
        }

        $kaydeden_personel_id = $_SESSION['user_id'] ?? null;
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? 'Sistem';

        // 3. Return component stocks
        while ($component = $components_result->fetch_assoc()) {
            $malzeme_kodu = $component['malzeme_kodu'];
            $miktar = floatval($component['miktar']);

            // 3a. Update material stock
            $update_material_stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + '" . $connection->real_escape_string($miktar) . "' WHERE malzeme_kodu = '" . $connection->real_escape_string($malzeme_kodu) . "'";
            if (!$connection->query($update_material_stock_query)) {
                throw new Exception("Malzeme stok miktarı iade edilemedi: " . $connection->error);
            }

            // 3b. Log stock movement
            $stock_in_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                               VALUES ('Bileşen', 
                                       '" . $connection->real_escape_string($component['malzeme_kodu']) . "', 
                                       '" . $connection->real_escape_string($component['malzeme_ismi']) . "', 
                                       '" . $connection->real_escape_string($component['birim']) . "', 
                                       '" . $connection->real_escape_string($miktar) . "', 
                                       'Giriş', 
                                       'Üretimden İade', 
                                       '" . $connection->real_escape_string($id) . "', 
                                       'Üretimdeki iş emri geri alındı', 
                                       '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                       '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
            if (!$connection->query($stock_in_query)) {
                throw new Exception("Bileşen stok iadesi yapılamadı: " . $connection->error);
            }
        }

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla "Oluşturuldu" durumuna geri alındı ve bileşenler stoğa iade edildi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function completeWorkOrder() {
    global $connection;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $is_emri_numarasi = $input['is_emri_numarasi'] ?? null;
    $tamamlanan_miktar = $input['tamamlanan_miktar'] ?? 0;
    $aciklama = $input['aciklama'] ?? '';
    
    if (!$is_emri_numarasi || $tamamlanan_miktar <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri. İş emri numarası ve tamamlanan miktar gereklidir.']);
        return;
    }

    try {
        // Begin transaction
        $connection->begin_transaction();

        // 1. Get work order details
        $work_order_query = "SELECT * FROM esans_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "' AND durum = 'uretimde' FOR UPDATE";
        $work_order_result = $connection->query($work_order_query);
        if (!$work_order_result || $work_order_result->num_rows == 0) {
            throw new Exception('İş emri bulunamadı veya durumu "Üretimde" değil.');
        }
        $work_order = $work_order_result->fetch_assoc();

        // 2. Update work order status
        $today = date('Y-m-d');
        $update_wo_query = "UPDATE esans_is_emirleri SET 
                            durum = 'tamamlandi', 
                            tamamlanan_miktar = '" . $connection->real_escape_string($tamamlanan_miktar) . "', 
                            gerceklesen_bitis_tarihi = '$today', 
                            aciklama = CONCAT(aciklama, ' " . $connection->real_escape_string($aciklama) . "')
                          WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "'";
        if (!$connection->query($update_wo_query)) {
            throw new Exception('İş emri güncellenirken hata oluştu: ' . $connection->error);
        }

        // 3. Increase stock for the produced essence (LOG)
        $kaydeden_personel_id = $_SESSION['user_id'] ?? null;
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? 'Sistem';

        $stock_in_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                           VALUES ('Esans', 
                                   '" . $connection->real_escape_string($work_order['esans_kodu']) . "', 
                                   '" . $connection->real_escape_string($work_order['esans_ismi']) . "', 
                                   '" . $connection->real_escape_string($work_order['birim']) . "', 
                                   '" . $connection->real_escape_string($tamamlanan_miktar) . "', 
                                   'Giriş', 
                                   'Üretimden Giriş', 
                                   '" . $connection->real_escape_string($is_emri_numarasi) . "', 
                                   'İş emri tamamlama', 
                                   '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                   '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
        if (!$connection->query($stock_in_query)) {
            throw new Exception('Esans stok girişi yapılamadı: ' . $connection->error);
        }

        // 4. UPDATE essence stock quantity
        $update_essence_stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari + '" . $connection->real_escape_string($tamamlanan_miktar) . "' WHERE esans_kodu = '" . $connection->real_escape_string($work_order['esans_kodu']) . "'";
        if (!$connection->query($update_essence_stock_query)) {
            throw new Exception('Esans stok miktarı güncellenemedi: ' . $connection->error);
        }

        // Commit transaction
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla tamamlandı ve üretilen esans stoğa eklendi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function revertCompletion() {
    global $connection;

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $is_emri_numarasi = $input['id'] ?? null;

    if (!$is_emri_numarasi) {
        echo json_encode(['status' => 'error', 'message' => 'İş emri numarası gerekli.']);
        return;
    }

    try {
        // Begin transaction
        $connection->begin_transaction();

        // 1. Get work order details, must be 'tamamlandi'
        $work_order_query = "SELECT * FROM esans_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "' AND durum = 'tamamlandi' FOR UPDATE";
        $work_order_result = $connection->query($work_order_query);
        if (!$work_order_result || $work_order_result->num_rows == 0) {
            throw new Exception('İş emri bulunamadı veya durumu "Tamamlandı" değil.');
        }
        $work_order = $work_order_result->fetch_assoc();
        $tamamlanan_miktar = floatval($work_order['tamamlanan_miktar']);

        if ($tamamlanan_miktar <= 0) {
            throw new Exception('Geri alınacak bir üretim miktarı bulunmuyor.');
        }

        $kaydeden_personel_id = $_SESSION['user_id'] ?? null;
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? 'Sistem';

        // 2. Decrease stock for the produced essence
        $update_essence_stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari - '" . $connection->real_escape_string($tamamlanan_miktar) . "' WHERE esans_kodu = '" . $connection->real_escape_string($work_order['esans_kodu']) . "'";
        if (!$connection->query($update_essence_stock_query)) {
            throw new Exception('Esans stok miktarı güncellenirken hata oluştu: ' . $connection->error);
        }

        // 3. Log the stock reversal for the essence
        $stock_out_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                            VALUES ('Esans', 
                                    '" . $connection->real_escape_string($work_order['esans_kodu']) . "', 
                                    '" . $connection->real_escape_string($work_order['esans_ismi']) . "', 
                                    '" . $connection->real_escape_string($work_order['birim']) . "', 
                                    '" . $connection->real_escape_string($tamamlanan_miktar) . "', 
                                    'Çıkış', 
                                    'Üretim İptal', 
                                    '" . $connection->real_escape_string($is_emri_numarasi) . "', 
                                    'Tamamlanan iş emri geri alındı', 
                                    '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                    '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
        if (!$connection->query($stock_out_query)) {
            throw new Exception('Esans stok iade (çıkış) kaydı oluşturulamadı: ' . $connection->error);
        }

        // 4. Update work order status
        $update_wo_query = "UPDATE esans_is_emirleri SET 
                            durum = 'uretimde', 
                            tamamlanan_miktar = 0,
                            gerceklesen_bitis_tarihi = NULL
                          WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "'";
        if (!$connection->query($update_wo_query)) {
            throw new Exception('İş emri durumu geri alınırken hata oluştu: ' . $connection->error);
        }

        // Commit transaction
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri tamamlama durumu başarıyla geri alındı. Üretilen esans stoktan düşüldü.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

?>