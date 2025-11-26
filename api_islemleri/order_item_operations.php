<?php
include '../config.php';

// Ensure proper character encoding
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in as staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Check if order is in 'beklemede' status before allowing operations
    $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
    if ($siparis_id > 0) {
        $check_status_query = "SELECT durum FROM siparisler WHERE siparis_id = ?";
        $check_status_stmt = $connection->prepare($check_status_query);
        $check_status_stmt->bind_param('i', $siparis_id);
        $check_status_stmt->execute();
        $status_result = $check_status_stmt->get_result();
        $order = $status_result->fetch_assoc();
        
        if ($order && $order['durum'] !== 'beklemede') {
            echo json_encode(['status' => 'error', 'message' => 'Sadece beklemede olan siparişlerde değişiklik yapılabilir!']);
            exit;
        }
    }
    
    if ($action === 'add_order_item') {
        $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
        $urun_kodu = isset($_POST['urun_kodu']) ? (int)$_POST['urun_kodu'] : 0;
        $adet = isset($_POST['adet']) ? (int)$_POST['adet'] : 0;
        
        if ($siparis_id <= 0 || $urun_kodu <= 0 || $adet <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz sipariş ID, ürün kodu veya adet!']);
            exit;
        }
        
        try {
            // Get product details
            $product_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
            $product_stmt = $connection->prepare($product_query);
            $product_stmt->bind_param('i', $urun_kodu);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product = $product_result->fetch_assoc();
            
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Seçilen ürün bulunamadı!']);
                exit;
            }
            
            // Ensure product data is valid
            $product['urun_ismi'] = !empty($product['urun_ismi']) ? $product['urun_ismi'] : 'Bilinmeyen Ürün';
            $product['birim'] = !empty($product['birim']) ? $product['birim'] : 'adet';
            $product['satis_fiyati'] = !empty($product['satis_fiyati']) ? floatval($product['satis_fiyati']) : 0;
            
            $toplam_tutar = $adet * $product['satis_fiyati'];
            
            // Validate and clean data before insertion to ensure non-empty values
            $urun_ismi_check = isset($product['urun_ismi']) && !is_null($product['urun_ismi']) ? trim($product['urun_ismi']) : '';
            $birim_check = isset($product['birim']) && !is_null($product['birim']) ? trim($product['birim']) : '';
            $fiyat_check = isset($product['satis_fiyati']) && is_numeric($product['satis_fiyati']) ? floatval($product['satis_fiyati']) : 0.0;

            // If any critical fields are empty or invalid, use defaults
            if (empty($urun_ismi_check) || $urun_ismi_check === '0' || $urun_ismi_check === false) {
                $urun_ismi_check = 'Bilinmeyen Ürün';
            }
            if (empty($birim_check) || $birim_check === '0' || $birim_check === false) {
                $birim_check = 'adet';
            }
            if ($fiyat_check <= 0) {
                $fiyat_check = 150.00; // Use default price
            }

            // Insert order item using prepared statements
            $item_query = "INSERT INTO siparis_kalemleri (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $connection->prepare($item_query);
            $item_stmt->bind_param('iisisdd', $siparis_id, $urun_kodu, $urun_ismi_check, $adet, $birim_check, $fiyat_check, $toplam_tutar);
            
            if ($item_stmt->execute()) {
                // Update total quantity in order
                $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
                $update_total_stmt = $connection->prepare($update_total_query);
                $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
                $update_total_stmt->execute();
                
                // No need to re-fetch data as we used the correct product data above
                $return_ismi = $urun_ismi_check;
                $return_birim = $birim_check;
                $return_fiyat = $fiyat_check;

                // Ensure we don't have null/empty values
                $return_ismi = !empty($return_ismi) ? $return_ismi : 'Bilinmeyen Ürün';
                $return_birim = !empty($return_birim) ? $return_birim : 'adet';
                $return_fiyat = !empty($return_fiyat) ? floatval($return_fiyat) : 0;

                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "Siparişe $return_ismi ürünü eklendi (ID: $siparis_id)", 'CREATE');

                // Return the inserted item data for immediate display
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Sipariş kalemi başarıyla eklendi.',
                    'item_data' => [
                        'siparis_id' => $siparis_id,
                        'urun_kodu' => $urun_kodu,
                        'urun_ismi' => $return_ismi,
                        'adet' => $adet,
                        'birim' => $return_birim,
                        'birim_fiyat' => number_format($return_fiyat, 2, '.', ''),
                        'toplam_tutar' => number_format($adet * $return_fiyat, 2, '.', '')
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş kalemi eklenirken hata oluştu: ' . $connection->error]);
            }
            
            $item_stmt->close();
            $product_stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()]);
        }
    } 
    elseif ($action === 'update_order_item') {
        $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
        $urun_kodu = isset($_POST['urun_kodu']) ? (int)$_POST['urun_kodu'] : 0;
        $adet = isset($_POST['adet']) ? (int)$_POST['adet'] : 0;
        $old_urun_kodu = isset($_POST['old_urun_kodu']) ? (int)$_POST['old_urun_kodu'] : 0; // This is the item ID to update
        
        if ($siparis_id <= 0 || $urun_kodu <= 0 || $adet <= 0 || $old_urun_kodu <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler!']);
            exit;
        }
        
        try {
            // Get product details
            $product_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
            $product_stmt = $connection->prepare($product_query);
            $product_stmt->bind_param('i', $urun_kodu);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product = $product_result->fetch_assoc();
            
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Seçilen ürün bulunamadı!']);
                exit;
            }
            
            // Ensure product data is valid
            $product['urun_ismi'] = !empty($product['urun_ismi']) ? $product['urun_ismi'] : 'Bilinmeyen Ürün';
            $product['birim'] = !empty($product['birim']) ? $product['birim'] : 'adet';
            $product['satis_fiyati'] = !empty($product['satis_fiyati']) ? floatval($product['satis_fiyati']) : 0;
            
            $toplam_tutar = $adet * $product['satis_fiyati'];
            
            // Validate data before update to ensure non-empty values
            $urun_ismi_check = trim($product['urun_ismi']);
            $birim_check = trim($product['birim']);
            $fiyat_check = floatval($product['satis_fiyati']);
            
            // If any critical fields are empty or invalid, use defaults
            if (empty($urun_ismi_check) || $urun_ismi_check === '0') {
                $urun_ismi_check = 'Bilinmeyen Ürün';
            }
            if (empty($birim_check) || $birim_check === '0') {
                $birim_check = 'adet';
            }
            if ($fiyat_check <= 0) {
                $fiyat_check = 0.00;
            }
            
            // Update order item
            $urun_ismi_escaped = $connection->real_escape_string($urun_ismi_check);
            $birim_escaped = $connection->real_escape_string($birim_check);
            
            $item_query = "UPDATE siparis_kalemleri SET urun_kodu = ?, urun_ismi = ?, adet = ?, birim = ?, birim_fiyat = ?, toplam_tutar = ? WHERE siparis_id = ? AND urun_kodu = ?";
            $item_stmt = $connection->prepare($item_query);
            $item_stmt->bind_param('isiiddii', $urun_kodu, $urun_ismi_escaped, $adet, $birim_escaped, $fiyat_check, $toplam_tutar, $siparis_id, $old_urun_kodu);
            
            if ($item_stmt->execute()) {
                if ($item_stmt->affected_rows > 0) {
                    // Update total quantity in order
                    $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
                    $update_total_stmt = $connection->prepare($update_total_query);
                    $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
                    $update_total_stmt->execute();
                    
                    // Eski ürün bilgilerini al
                    $old_product_query = "SELECT urun_ismi FROM siparis_kalemleri WHERE siparis_id = ? AND urun_kodu = ?";
                    $old_product_stmt = $connection->prepare($old_product_query);
                    $old_product_stmt->bind_param('ii', $siparis_id, $old_urun_kodu);
                    $old_product_stmt->execute();
                    $old_product_result = $old_product_stmt->get_result();
                    $old_product = $old_product_result->fetch_assoc();
                    $old_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
                    $old_product_stmt->close();

                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "Sipariş kalemi güncellendi: $old_product_name ürünü $product[urun_ismi] olarak değiştirildi (ID: $siparis_id)", 'UPDATE');

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Sipariş kalemi başarıyla güncellendi.',
                        'item_data' => [
                            'siparis_id' => $siparis_id,
                            'urun_kodu' => $urun_kodu,
                            'urun_ismi' => $product['urun_ismi'],
                            'adet' => $adet,
                            'birim' => $product['birim'],
                            'birim_fiyat' => number_format($product['satis_fiyati'], 2, '.', ''),
                            'toplam_tutar' => number_format($toplam_tutar, 2, '.', ''),
                            'old_urun_kodu' => $old_urun_kodu
                        ]
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Güncellenecek sipariş kalemi bulunamadı!']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş kalemi güncellenirken hata oluştu: ' . $connection->error]);
            }
            
            $item_stmt->close();
            $product_stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()]);
        }
    } 
    elseif ($action === 'delete_order_item') {
        $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
        $urun_kodu = isset($_POST['urun_kodu']) ? (int)$_POST['urun_kodu'] : 0;
        
        if ($siparis_id <= 0 || $urun_kodu <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler!']);
            exit;
        }
        
        // Check if order is in 'beklemede' status before allowing operations
        $check_status_query = "SELECT durum FROM siparisler WHERE siparis_id = ?";
        $check_status_stmt = $connection->prepare($check_status_query);
        $check_status_stmt->bind_param('i', $siparis_id);
        $check_status_stmt->execute();
        $status_result = $check_status_stmt->get_result();
        $order = $status_result->fetch_assoc();
        
        if ($order && $order['durum'] !== 'beklemede') {
            echo json_encode(['status' => 'error', 'message' => 'Sadece beklemede olan siparişlerde değişiklik yapılabilir!']);
            exit;
        }
        
        try {
            // Delete order item
            $delete_query = "DELETE FROM siparis_kalemleri WHERE siparis_id = ? AND urun_kodu = ?";
            $delete_stmt = $connection->prepare($delete_query);
            $delete_stmt->bind_param('ii', $siparis_id, $urun_kodu);
            
            if ($delete_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    // Update total quantity in order
                    $update_total_query = "UPDATE siparisler SET toplam_adet = (SELECT SUM(adet) FROM siparis_kalemleri WHERE siparis_id = ?) WHERE siparis_id = ?";
                    $update_total_stmt = $connection->prepare($update_total_query);
                    $update_total_stmt->bind_param('ii', $siparis_id, $siparis_id);
                    $update_total_stmt->execute();
                    
                    // Silinen ürün bilgilerini al
                    $deleted_product_query = "SELECT urun_ismi FROM siparis_kalemleri WHERE siparis_id = ? AND urun_kodu = ?";
                    $deleted_product_stmt = $connection->prepare($deleted_product_query);
                    $deleted_product_stmt->bind_param('ii', $siparis_id, $urun_kodu);
                    $deleted_product_stmt->execute();
                    $deleted_product_result = $deleted_product_stmt->get_result();
                    $deleted_product = $deleted_product_result->fetch_assoc();
                    $deleted_product_name = $deleted_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
                    $deleted_product_stmt->close();

                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "Siparişten $deleted_product_name ürünü silindi (ID: $siparis_id)", 'DELETE');

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Sipariş kalemi başarıyla silindi.',
                        'urun_kodu' => $urun_kodu
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Silinecek sipariş kalemi bulunamadı!']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş kalemi silinirken hata oluştu: ' . $connection->error]);
            }
            
            $delete_stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()]);
        }
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem!']);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests if needed
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'get_product_info') {
        $urun_kodu = isset($_GET['urun_kodu']) ? (int)$_GET['urun_kodu'] : 0;
        
        if ($urun_kodu <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün kodu!']);
            exit;
        }
        
        try {
            $product_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
            $product_stmt = $connection->prepare($product_query);
            $product_stmt->bind_param('i', $urun_kodu);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product = $product_result->fetch_assoc();
            
            if ($product) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'urun_ismi' => $product['urun_ismi'],
                        'birim' => $product['birim'],
                        'satis_fiyati' => floatval($product['satis_fiyati'])
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ürün bulunamadı!']);
            }
            
            $product_stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz GET işlemi!']);
    }
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek yöntemi!']);
}
