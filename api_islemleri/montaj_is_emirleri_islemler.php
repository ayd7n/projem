<?php
require_once '../config.php';
date_default_timezone_set('Europe/Istanbul');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

// Helper function to validate date format
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

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
    case 'get_products':
        getProducts();
        break;
    case 'get_materials':
        getMaterials();
        break;
    case 'get_montaj_alanlari':
        getMontajAlanlari();
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
        
        // Count total records for the last 1000 by work order number
        $count_query = "SELECT COUNT(*) as total FROM (
            SELECT is_emri_numarasi FROM montaj_is_emirleri
            ORDER BY is_emri_numarasi DESC
            LIMIT 1000
        ) AS subquery";
        $count_result = $connection->query($count_query);
        $total_records = $count_result->fetch_assoc()['total'];

        // Fetch last 1000 records by work order number with pagination
        // Join with is_merkezleri to get the work center name
        $query = "SELECT m.*,
                         COALESCE(im.isim, '') as montaj_alani_ismi,
                         COALESCE(im.isim, '') as montaj_alani_kodu
                  FROM (
                      SELECT * FROM montaj_is_emirleri
                      ORDER BY is_emri_numarasi DESC
                      LIMIT 1000
                  ) AS m
                  LEFT JOIN is_merkezleri im ON m.is_merkezi_id = im.is_merkezi_id
                  ORDER BY m.is_emri_numarasi DESC
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
        
        $query = "SELECT m.*, 
                         COALESCE(im.isim, '') as montaj_alani_ismi, 
                         COALESCE(im.isim, '') as montaj_alani_kodu
                  FROM montaj_is_emirleri m
                  LEFT JOIN is_merkezleri im ON m.is_merkezi_id = im.is_merkezi_id
                  WHERE m.is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
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

function getProducts() {
    global $connection;
    
    try {
        // Sadece urun_agaci tablosunda agac_turu = "urun" olarak tanımlı ürünleri getir
        $query = "SELECT DISTINCT u.urun_kodu, u.urun_ismi, u.birim 
                  FROM urunler u 
                  INNER JOIN urun_agaci ua ON u.urun_kodu = ua.urun_kodu 
                  WHERE ua.agac_turu = 'urun' 
                  ORDER BY u.urun_ismi";
        $result = $connection->query($query);
        
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $products]);
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

function getMontajAlanlari() {
    global $connection;
    
    try {
        $query = "SELECT DISTINCT is_merkezi_id as montaj_alani_kodu, isim as montaj_alani_ismi FROM is_merkezleri ORDER BY isim";
        $result = $connection->query($query);
        
        $montajAlanlari = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $montajAlanlari[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $montajAlanlari]);
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
        
        // Validate required fields
        if (empty($work_order['urun_kodu'])) {
            throw new Exception('Ürün kodu boş olamaz.');
        }
        
        if (empty($work_order['montaj_alani_kodu']) && empty($work_order['is_merkezi_id'])) {
            throw new Exception('Montaj alanı kodu boş olamaz.');
        }
        
        if (!is_numeric($work_order['planlanan_miktar']) || $work_order['planlanan_miktar'] <= 0) {
            throw new Exception('Planlanan miktar pozitif bir sayı olmalıdır.');
        }
        
        // Validate dates
        if (!validateDate($work_order['olusturulma_tarihi'])) {
            throw new Exception('Oluşturulma tarihi geçerli bir tarih olmalıdır (YYYY-MM-DD formatında).');
        }
        
        if (!validateDate($work_order['planlanan_baslangic_tarihi'])) {
            throw new Exception('Planlanan başlangıç tarihi geçerli bir tarih olmalıdır (YYYY-MM-DD formatında).');
        }
        
        // Set default values for optional fields
        $work_order['durum'] = $work_order['durum'] ?? 'olusturuldu';
        $work_order['tamamlanan_miktar'] = $work_order['tamamlanan_miktar'] ?? 0;
        $work_order['eksik_miktar_toplami'] = $work_order['eksik_miktar_toplami'] ?? 0;
        $work_order['aciklama'] = $work_order['aciklama'] ?? '';
        $work_order['birim'] = $work_order['birim'] ?? 'adet';
        
        // Validate status
        $valid_statuses = ['olusturuldu', 'uretimde', 'tamamlandi', 'iptal'];
        if (!in_array($work_order['durum'], $valid_statuses)) {
            throw new Exception('Geçersiz durum değeri. Geçerli durumlar: ' . implode(', ', $valid_statuses));
        }
        
        // Set planlanan_bitis_tarihi same as planlanan_baslangic_tarihi for assembly orders
        $work_order['planlanan_bitis_tarihi'] = $work_order['planlanan_baslangic_tarihi'];

        // Calculate eksik_miktar_toplami as planned amount when creating
        $work_order['eksik_miktar_toplami'] = $work_order['planlanan_miktar'];

        // Begin transaction
        $connection->begin_transaction();

        // Insert work order
        $query = "INSERT INTO montaj_is_emirleri (olusturulma_tarihi, olusturan, urun_kodu, urun_ismi,
                  planlanan_miktar, birim, planlanan_baslangic_tarihi, planlanan_bitis_tarihi,
                  aciklama, durum, tamamlanan_miktar, eksik_miktar_toplami, is_merkezi_id)
                  VALUES ('" . $connection->real_escape_string($work_order['olusturulma_tarihi']) . "',
                  '" . $connection->real_escape_string($user) . "',
                  '" . $connection->real_escape_string($work_order['urun_kodu']) . "',
                  '" . $connection->real_escape_string($work_order['urun_ismi']) . "',
                  '" . $connection->real_escape_string($work_order['planlanan_miktar']) . "',
                  '" . $connection->real_escape_string($work_order['birim']) . "',
                  '" . $connection->real_escape_string($work_order['planlanan_baslangic_tarihi']) . "',
                  '" . $connection->real_escape_string($work_order['planlanan_bitis_tarihi']) . "',
                  '" . $connection->real_escape_string($work_order['aciklama']) . "',
                  '" . $connection->real_escape_string($work_order['durum']) . "',
                  '" . $connection->real_escape_string($work_order['tamamlanan_miktar']) . "',
                  '" . $connection->real_escape_string($work_order['eksik_miktar_toplami']) . "',
                  '" . $connection->real_escape_string($work_order['is_merkezi_id'] ?? $work_order['montaj_alani_kodu']) . "')";
        
        if ($connection->query($query)) {
            $work_order_id = $connection->insert_id;
            
            // Insert components
            foreach ($components as $component) {
                $comp_query = "INSERT INTO montaj_is_emri_malzeme_listesi (is_emri_numarasi, malzeme_kodu, malzeme_ismi, 
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
            
            echo json_encode(['status' => 'success', 'message' => 'Montaj iş emri başarıyla oluşturuldu']);
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

        // Update work order - calculate eksik_miktar_toplami based on current status
        // If status is 'olusturuldu' or 'uretimde', eksik_miktar_toplami should be the planned amount minus completed amount
        // If status is 'tamamlandi', it should have been set during completion
        $query = "UPDATE montaj_is_emirleri
                  SET olusturan = '" . $connection->real_escape_string($user) . "',
                      urun_kodu = '" . $connection->real_escape_string($work_order['urun_kodu']) . "',
                      urun_ismi = '" . $connection->real_escape_string($work_order['urun_ismi']) . "',
                      planlanan_miktar = '" . $connection->real_escape_string($work_order['planlanan_miktar']) . "',
                      birim = '" . $connection->real_escape_string($work_order['birim']) . "',
                      planlanan_baslangic_tarihi = '" . $connection->real_escape_string($work_order['planlanan_baslangic_tarihi']) . "',
                      planlanan_bitis_tarihi = '" . $connection->real_escape_string($work_order['planlanan_bitis_tarihi']) . "',
                      aciklama = '" . $connection->real_escape_string($work_order['aciklama'] ?? '') . "',
                      durum = '" . $connection->real_escape_string($work_order['durum']) . "',
                      eksik_miktar_toplami = CASE
                          WHEN durum = 'tamamlandi' THEN '" . $connection->real_escape_string($work_order['eksik_miktar_toplami']) . "'
                          ELSE planlanan_miktar - tamamlanan_miktar
                      END,
                      is_merkezi_id = '" . $connection->real_escape_string($work_order['is_merkezi_id'] ?? $work_order['montaj_alani_kodu']) . "'
                  WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order['is_emri_numarasi']) . "'";
        
        if ($connection->query($query)) {
            // Delete existing components for this work order
            $delete_query = "DELETE FROM montaj_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order['is_emri_numarasi']) . "'";
            $connection->query($delete_query);
            
            // Insert new components
            foreach ($components as $component) {
                $comp_query = "INSERT INTO montaj_is_emri_malzeme_listesi (is_emri_numarasi, malzeme_kodu, malzeme_ismi, 
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
            
            echo json_encode(['status' => 'success', 'message' => 'Montaj iş emri başarıyla güncellendi']);
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
        $delete_components_query = "DELETE FROM montaj_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        $connection->query($delete_components_query);
        
        // Then delete work order
        $delete_work_order_query = "DELETE FROM montaj_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
        
        if ($connection->query($delete_work_order_query)) {
            // Commit transaction
            $connection->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Montaj iş emri başarıyla silindi']);
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
        
        $product_code = $input['product_code'] ?? null;
        $quantity = $input['quantity'] ?? null;
        
        if (!$product_code) {
            throw new Exception('Product code is required');
        }
        
        if ($quantity === null || $quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }
        
        // Get product components from urun_agaci table where agac_turu = 'montaj'
        // Using urun_kodu directly instead of urun_id
        $query = "SELECT bilesen_kodu, bilesen_ismi, bilesenin_malzeme_turu, bilesen_miktari 
                  FROM urun_agaci 
                  WHERE urun_kodu = '" . $connection->real_escape_string($product_code) . "' 
                  AND agac_turu = 'urun'";
        
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
        
        $query = "SELECT * FROM montaj_is_emri_malzeme_listesi 
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
        
        // Get the product code from the work order to find its components
        $work_order_query = "SELECT urun_kodu, planlanan_miktar FROM montaj_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($work_order_id) . "'";
        $work_order_result = $connection->query($work_order_query);
        
        if (!$work_order_result || $work_order_result->num_rows == 0) {
            throw new Exception('Work order not found');
        }
        
        $work_order = $work_order_result->fetch_assoc();
        $product_code = $work_order['urun_kodu'];
        $quantity = $work_order['planlanan_miktar'];
        
        // Get product components from urun_agaci table where urun_kodu matches the product code
        $query = "SELECT bilesen_kodu as malzeme_kodu, bilesen_ismi as malzeme_ismi, 
                         bilesenin_malzeme_turu as malzeme_turu, bilesen_miktari
                  FROM urun_agaci 
                  WHERE urun_kodu = '" . $connection->real_escape_string($product_code) . "' 
                  AND agac_turu = 'urun'";
        
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
        $update_wo_query = "UPDATE montaj_is_emirleri
                            SET durum = 'uretimde',
                                gerceklesen_baslangic_tarihi = '$today',
                                eksik_miktar_toplami = planlanan_miktar  -- When starting, all planned amount is missing until completion
                            WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'
                            AND durum = 'olusturuldu'";
        
        if (!$connection->query($update_wo_query)) {
            throw new Exception("İş emri durumu güncellenirken bir hata oluştu: " . $connection->error);
        }

        if ($connection->affected_rows == 0) {
            throw new Exception('İş emri başlatılamadı. Durumu "Oluşturuldu" olmalı veya zaten başlatılmış olabilir.');
        }

        // 2. Get components for the work order
        $components_query = "SELECT * FROM montaj_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
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
                                VALUES ('" . $connection->real_escape_string($component['malzeme_turu']) . "', 
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
        $update_wo_query = "UPDATE montaj_is_emirleri
                            SET durum = 'olusturuldu',
                                gerceklesen_baslangic_tarihi = NULL,
                                eksik_miktar_toplami = planlanan_miktar  -- When reverted back to 'olusturuldu', all planned amount is missing again
                            WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'
                            AND durum = 'uretimde'";
        
        if (!$connection->query($update_wo_query)) {
            throw new Exception("İş emri durumu güncellenirken bir hata oluştu: " . $connection->error);
        }

        if ($connection->affected_rows == 0) {
            throw new Exception('İş emri geri alınamadı. Durumu "Üretimde" olmalı.');
        }

        // 2. Get components for the work order
        $components_query = "SELECT * FROM montaj_is_emri_malzeme_listesi WHERE is_emri_numarasi = '" . $connection->real_escape_string($id) . "'";
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
                               VALUES ('" . $connection->real_escape_string($component['malzeme_turu']) . "', 
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
    $eksik_miktar_toplami = $input['eksik_miktar_toplami'] ?? 0;
    $aciklama = $input['aciklama'] ?? '';

    if (!$is_emri_numarasi || $tamamlanan_miktar < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri. İş emri numarası ve tamamlanan miktar gereklidir.']);
        return;
    }

    try {
        // Begin transaction
        $connection->begin_transaction();

        // 1. Get work order details
        $work_order_query = "SELECT * FROM montaj_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "' AND durum = 'uretimde' FOR UPDATE";
        $work_order_result = $connection->query($work_order_query);
        if (!$work_order_result || $work_order_result->num_rows == 0) {
            throw new Exception('İş emri bulunamadı veya durumu "Üretimde" değil.');
        }
        $work_order = $work_order_result->fetch_assoc();

        // 2. Update work order status - calculate eksik_miktar_toplami if not provided
        $today = date('Y-m-d');
        if ($eksik_miktar_toplami === null || $eksik_miktar_toplami < 0) {
            $eksik_miktar_toplami = max(0, floatval($work_order['planlanan_miktar']) - floatval($tamamlanan_miktar));
        }
        $update_wo_query = "UPDATE montaj_is_emirleri SET
                            durum = 'tamamlandi',
                            tamamlanan_miktar = '" . $connection->real_escape_string($tamamlanan_miktar) . "',
                            eksik_miktar_toplami = '" . $connection->real_escape_string($eksik_miktar_toplami) . "',
                            gerceklesen_bitis_tarihi = '$today',
                            aciklama = CONCAT(aciklama, ' " . $connection->real_escape_string($aciklama) . "')
                          WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "'";
        if (!$connection->query($update_wo_query)) {
            throw new Exception('İş emri güncellenirken hata oluştu: ' . $connection->error);
        }

        // 3. Increase stock for the produced product (LOG)
        $kaydeden_personel_id = $_SESSION['user_id'] ?? null;
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? 'Sistem';

        $stock_in_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                           VALUES ('Ürün', 
                                   '" . $connection->real_escape_string($work_order['urun_kodu']) . "', 
                                   '" . $connection->real_escape_string($work_order['urun_ismi']) . "', 
                                   '" . $connection->real_escape_string($work_order['birim']) . "', 
                                   '" . $connection->real_escape_string($tamamlanan_miktar) . "', 
                                   'Giriş', 
                                   'Üretimden Giriş', 
                                   '" . $connection->real_escape_string($is_emri_numarasi) . "', 
                                   'İş emri tamamlama', 
                                   '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                   '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
        if (!$connection->query($stock_in_query)) {
            throw new Exception('Ürün stok girişi yapılamadı: ' . $connection->error);
        }

        // 4. UPDATE product stock quantity
        $update_product_stock_query = "UPDATE urunler SET stok_miktari = stok_miktari + '" . $connection->real_escape_string($tamamlanan_miktar) . "' WHERE urun_kodu = '" . $connection->real_escape_string($work_order['urun_kodu']) . "'";
        if (!$connection->query($update_product_stock_query)) {
            throw new Exception('Ürün stok miktarı güncellenemedi: ' . $connection->error);
        }

        // Commit transaction
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri başarıyla tamamlandı ve üretilen ürün stoğa eklendi.']);

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
        $work_order_query = "SELECT * FROM montaj_is_emirleri WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "' AND durum = 'tamamlandi' FOR UPDATE";
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

        // 2. Decrease stock for the produced product
        $update_product_stock_query = "UPDATE urunler SET stok_miktari = stok_miktari - '" . $connection->real_escape_string($tamamlanan_miktar) . "' WHERE urun_kodu = '" . $connection->real_escape_string($work_order['urun_kodu']) . "'";
        if (!$connection->query($update_product_stock_query)) {
            throw new Exception('Ürün stok miktarı güncellenirken hata oluştu: ' . $connection->error);
        }

        // 3. Log the stock reversal for the product
        $stock_out_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, is_emri_numarasi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                            VALUES ('Ürün', 
                                    '" . $connection->real_escape_string($work_order['urun_kodu']) . "', 
                                    '" . $connection->real_escape_string($work_order['urun_ismi']) . "', 
                                    '" . $connection->real_escape_string($work_order['birim']) . "', 
                                    '" . $connection->real_escape_string($tamamlanan_miktar) . "', 
                                    'Çıkış', 
                                    'Üretim İptal', 
                                    '" . $connection->real_escape_string($is_emri_numarasi) . "', 
                                    'Tamamlanan iş emri geri alındı', 
                                    '" . $connection->real_escape_string($kaydeden_personel_id) . "', 
                                    '" . $connection->real_escape_string($kaydeden_personel_adi) . "')";
        if (!$connection->query($stock_out_query)) {
            throw new Exception('Ürün stok iade (çıkış) kaydı oluşturulamadı: ' . $connection->error);
        }

        // 4. Update work order status
        $update_wo_query = "UPDATE montaj_is_emirleri SET
                            durum = 'uretimde',
                            tamamlanan_miktar = 0,
                            eksik_miktar_toplami = '" . $connection->real_escape_string($work_order['planlanan_miktar']) . "',
                            gerceklesen_bitis_tarihi = NULL
                          WHERE is_emri_numarasi = '" . $connection->real_escape_string($is_emri_numarasi) . "'";
        if (!$connection->query($update_wo_query)) {
            throw new Exception('İş emri durumu geri alınırken hata oluştu: ' . $connection->error);
        }

        // Commit transaction
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İş emri tamamlama durumu başarıyla geri alındı. Üretilen ürün stoktan düşüldü.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

?>