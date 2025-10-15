<?php
include '../config.php';

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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_locations':
        $locations = [];
        $query = "SELECT DISTINCT depo_ismi, raf FROM lokasyonlar ORDER BY depo_ismi, raf";
        
        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $locations[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $locations]);
        break;
    
    case 'get_current_location':
        $stock_type = $_GET['stock_type'] ?? '';
        $item_code = $_GET['item_code'] ?? '';

        // Escape user inputs to prevent SQL injection
        $stock_type = $connection->real_escape_string($stock_type);
        $item_code = $connection->real_escape_string($item_code);

        if (!$stock_type || !$item_code) {
            echo json_encode(['status' => 'error', 'message' => 'Stok türü ve ürün kodu belirtilmedi.']);
            break;
        }

        $location_data = null;

        // Get the location directly from the main item table based on stock type
        switch ($stock_type) {
            case 'malzeme':
                $query = "SELECT malzeme_kodu as kod, malzeme_ismi as isim, stok_miktari, depo, raf FROM malzemeler WHERE malzeme_kodu = '$item_code'";
                $result = $connection->query($query);
                
                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    $location_data = [
                        'depo' => $item['depo'],
                        'raf' => $item['raf'],
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            case 'esans':
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari FROM esanslar WHERE esans_kodu = '$item_code'";
                $result = $connection->query($query);
                
                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    // For essences, we don't have direct tank location in the main table
                    // We could look for the most recent tank in stock movements, but per your request
                    // we'll just return the essence info without location for now
                    $location_data = [
                        'konum' => null, // Direct tank location not available in main table
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            case 'urun':
                $query = "SELECT urun_kodu as kod, urun_ismi as isim, stok_miktari, depo, raf FROM urunler WHERE urun_kodu = '$item_code'";
                $result = $connection->query($query);
                
                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    $location_data = [
                        'depo' => $item['depo'],
                        'raf' => $item['raf'],
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                break;
        }

        if ($location_data) {
            echo json_encode(['status' => 'success', 'data' => $location_data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ürün bulunamadı.']);
        }
        break;
    
    case 'get_stock_items':
        $type = $_GET['type'] ?? '';

        if (!$type) {
            echo json_encode(['status' => 'error', 'message' => 'Stok türü belirtilmedi.']);
            break;
        }

        $items = [];
        switch ($type) {
            case 'malzeme':
                $query = "SELECT malzeme_kodu as kod, malzeme_ismi as isim, stok_miktari as stok FROM malzemeler ORDER BY malzeme_ismi";
                break;
            case 'esans':
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari as stok FROM esanslar ORDER BY esans_ismi";
                break;
            case 'urun':
                $query = "SELECT urun_kodu as kod, urun_ismi as isim, stok_miktari as stok FROM urunler ORDER BY urun_ismi";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $items]);
        break;

    case 'get_movement':
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Hareket ID belirtilmedi.']);
            break;
        }

        $query = "SELECT * FROM stok_hareket_kayitlari WHERE hareket_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $movement = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $movement]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi bulunamadı.']);
        }
        $stmt->close();
        break;

    case 'add_movement':
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $yon = $_POST['yon'] ?? '';
        $hareket_turu = $_POST['hareket_turu'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';

        // Validation
        if (!$stok_turu || !$kod || !$miktar || !$yon || !$hareket_turu || !$aciklama) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get item name and unit based on stock type
        $item_name = '';
        $item_unit = '';

        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('s', $kod);
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'] ?? $item['esans_ismi'] ?? $item['urun_ismi'] ?? '';
            $item_unit = $item['birim'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz kod.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        // Insert stock movement
        $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $movement_stmt = $connection->prepare($movement_query);
        $movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $aciklama, $_SESSION['user_id'], $_SESSION['kullanici_adi']);

        if ($movement_stmt->execute()) {
            $hareket_id = $movement_stmt->insert_id;

            // Update stock based on movement
            $direction = ($yon === 'giris') ? $miktar : -$miktar;

            switch ($stok_turu) {
                case 'malzeme':
                    $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + ? WHERE malzeme_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('di', $direction, $kod);
                    break;
                case 'esans':
                    $stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari + ? WHERE esans_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('ds', $direction, $kod);
                    break;
                case 'urun':
                    $stock_query = "UPDATE urunler SET stok_miktari = stok_miktari + ? WHERE urun_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('ii', $direction, $kod);
                    break;
            }

            if (isset($stock_stmt) && $stock_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla kaydedildi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Stok hareketi kaydedildi ama stok güncellenirken hata oluştu.']);
            }

            if (isset($stock_stmt)) {
                $stock_stmt->close();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi kaydedilirken hata oluştu: ' . $connection->error]);
        }
        $movement_stmt->close();
        break;

    case 'update_movement':
        $hareket_id = $_POST['hareket_id'] ?? 0;
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $yon = $_POST['yon'] ?? '';
        $hareket_turu = $_POST['hareket_turu'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';

        // Validation
        if (!$hareket_id || !$stok_turu || !$kod || !$miktar || !$yon || !$hareket_turu || !$aciklama) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get item name and unit based on stock type
        $item_name = '';
        $item_unit = '';

        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('s', $kod);
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'] ?? $item['esans_ismi'] ?? $item['urun_ismi'] ?? '';
            $item_unit = $item['birim'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz kod.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        // Update stock movement
        $movement_query = "UPDATE stok_hareket_kayitlari SET stok_turu = ?, kod = ?, isim = ?, birim = ?, miktar = ?, yon = ?, hareket_turu = ?, depo = ?, raf = ?, tank_kodu = ?, ilgili_belge_no = ?, aciklama = ? WHERE hareket_id = ?";
        $movement_stmt = $connection->prepare($movement_query);
        $movement_stmt->bind_param('ssssdsssssssi', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $aciklama, $hareket_id);

        if ($movement_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi güncellenirken hata oluştu: ' . $connection->error]);
        }
        $movement_stmt->close();
        break;

    case 'delete_movement':
        $hareket_id = $_POST['hareket_id'] ?? 0;

        if (!$hareket_id) {
            echo json_encode(['status' => 'error', 'message' => 'Hareket ID belirtilmedi.']);
            break;
        }

        // Get movement details before deletion for stock adjustment
        $get_query = "SELECT * FROM stok_hareket_kayitlari WHERE hareket_id = ?";
        $get_stmt = $connection->prepare($get_query);
        $get_stmt->bind_param('i', $hareket_id);
        $get_stmt->execute();
        $get_result = $get_stmt->get_result();

        if ($get_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi bulunamadı.']);
            $get_stmt->close();
            break;
        }

        $movement = $get_result->fetch_assoc();
        $get_stmt->close();

        // Delete the movement
        $delete_query = "DELETE FROM stok_hareket_kayitlari WHERE hareket_id = ?";
        $delete_stmt = $connection->prepare($delete_query);
        $delete_stmt->bind_param('i', $hareket_id);

        if ($delete_stmt->execute()) {
            // Reverse stock adjustment
            $direction = ($movement['yon'] === 'giris') ? -$movement['miktar'] : $movement['miktar'];

            switch ($movement['stok_turu']) {
                case 'malzeme':
                    $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + ? WHERE malzeme_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('di', $direction, $movement['kod']);
                    break;
                case 'esans':
                    $stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari + ? WHERE esans_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('ds', $direction, $movement['kod']);
                    break;
                case 'urun':
                    $stock_query = "UPDATE urunler SET stok_miktari = stok_miktari + ? WHERE urun_kodu = ?";
                    $stock_stmt = $connection->prepare($stock_query);
                    $stock_stmt->bind_param('ii', $direction, $movement['kod']);
                    break;
            }

            if (isset($stock_stmt) && $stock_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla silindi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Stok hareketi silindi ama stok geri yüklenirken hata oluştu.']);
            }

            if (isset($stock_stmt)) {
                $stock_stmt->close();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi silinirken hata oluştu: ' . $connection->error]);
        }
        $delete_stmt->close();
        break;

    case 'transfer_stock':
        // Get transfer parameters
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = floatval($_POST['miktar'] ?? 0);
        $aciklama = $_POST['aciklama'] ?? 'Stok transferi';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';

        // Validate that stock type is not essence (only materials and products allowed)
        if ($stok_turu === 'esans') {
            echo json_encode(['status' => 'error', 'message' => 'Essence transferi desteklenmemektedir.']);
            break;
        }

        // Get source and destination locations for materials and products
        $kaynak_depo = $_POST['kaynak_depo'] ?? '';
        $kaynak_raf = $_POST['kaynak_raf'] ?? '';
        $hedef_depo = $_POST['hedef_depo'] ?? '';
        $hedef_raf = $_POST['hedef_raf'] ?? '';

        if (!$kaynak_depo || !$kaynak_raf || !$hedef_depo || !$hedef_raf) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen hem kaynak hem de hedef konumu belirtin.']);
            break;
        }

        // Validation
        if (!$stok_turu || !$kod || $miktar <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Check if source and destination are the same
        if ($kaynak_depo === $hedef_depo && $kaynak_raf === $hedef_raf) {
            echo json_encode(['status' => 'error', 'message' => 'Kaynak ve hedef konum aynı olamaz.']);
            break;
        }

        // Check current stock at source location
        $item_name = '';
        $item_unit = '';
        
        // Escape user inputs to prevent SQL injection
        $stok_turu = $connection->real_escape_string($stok_turu);
        $kod = $connection->real_escape_string($kod);
        $kaynak_depo = $connection->real_escape_string($kaynak_depo);
        $kaynak_raf = $connection->real_escape_string($kaynak_raf);
        
        // First, get item details
        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = '$kod'";
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = '$kod'";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü. Sadece malzeme ve ürün transferi desteklenmektedir.']);
                exit;
        }

        $item_result = $connection->query($item_query);
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'] ?? $item['urun_ismi'] ?? '';
            $item_unit = $item['birim'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün kodu.']);
            break;
        }

        // Check current stock at source location for materials and products
        $available_stock = 0;
        
        // For materials and products, check stock at the specific depot/shelf
        $stock_check_query = "SELECT SUM(CASE WHEN yon = 'giris' THEN miktar ELSE -miktar END) as current_stock
                              FROM stok_hareket_kayitlari 
                              WHERE kod = '$kod' AND stok_turu = '$stok_turu' AND depo = '$kaynak_depo' AND raf = '$kaynak_raf'
                              GROUP BY depo, raf";

        $stock_result = $connection->query($stock_check_query);
        if ($stock_result && $stock_result->num_rows > 0) {
            $stock_row = $stock_result->fetch_assoc();
            $available_stock = floatval($stock_row['current_stock']) ?? 0;
        } else {
            // If no specific location records exist, use the overall stock from the main table
            switch ($stok_turu) {
                case 'malzeme':
                    $overall_stock_query = "SELECT stok_miktari FROM malzemeler WHERE malzeme_kodu = '$kod'";
                    break;
                case 'urun':
                    $overall_stock_query = "SELECT stok_miktari FROM urunler WHERE urun_kodu = '$kod'";
                    break;
            }
            
            $overall_result = $connection->query($overall_stock_query);
            if ($overall_result && $overall_result->num_rows > 0) {
                $overall_row = $overall_result->fetch_assoc();
                $available_stock = floatval($overall_row['stok_miktari']) ?? 0;
            }
        }

        // Check if there's enough stock for transfer
        if ($available_stock < $miktar) {
            echo json_encode(['status' => 'error', 'message' => 'Yetersiz stok. Kaynak konumda mevcut stok: ' . $available_stock]);
            break;
        }

        // Start transaction for consistency
        $connection->autocommit(FALSE);

        try {
            // Define variables for bind_param to avoid reference errors
            $null1 = NULL;
            $yon_cikis = 'cikis';
            $yon_giris = 'giris';
            $hareket_turu_transfer = 'transfer';
            
            // Create description strings for movements
            $source_description = $aciklama . ' - Kaynak: ' . $kaynak_depo . '/' . $kaynak_raf . ' -> Hedef: ' . $hedef_depo . '/' . $hedef_raf;
            $dest_description = $aciklama . ' - Kaynak: ' . $kaynak_depo . '/' . $kaynak_raf . ' -> Hedef: ' . $hedef_depo . '/' . $hedef_raf;

            // Step 1: Create the source (outgoing) stock movement
            $source_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $source_movement_stmt = $connection->prepare($source_movement_query);

            // Set location values for source - only for materials and products
            $source_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_cikis, $hareket_turu_transfer, $kaynak_depo, $kaynak_raf, $null1, $ilgili_belge_no, $source_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);

            if (!$source_movement_stmt->execute()) {
                throw new Exception('Kaynak hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $source_movement_id = $source_movement_stmt->insert_id;
            $source_movement_stmt->close();

            // Step 2: Create the destination (incoming) stock movement
            $dest_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $dest_movement_stmt = $connection->prepare($dest_movement_query);

            // Set location values for destination - only for materials and products
            $dest_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_giris, $hareket_turu_transfer, $hedef_depo, $hedef_raf, $null1, $ilgili_belge_no, $dest_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);

            if (!$dest_movement_stmt->execute()) {
                throw new Exception('Hedef hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $dest_movement_id = $dest_movement_stmt->insert_id;
            $dest_movement_stmt->close();

            // Commit transaction
            $connection->commit();

            // Update the location in the main item table (urunler or malzemeler) to the destination location
            switch ($stok_turu) {
                case 'malzeme':
                    $update_query = "UPDATE malzemeler SET depo = ?, raf = ? WHERE malzeme_kodu = ?";
                    $update_stmt = $connection->prepare($update_query);
                    $update_stmt->bind_param('ssi', $hedef_depo, $hedef_raf, $kod);
                    $update_stmt->execute();
                    $update_stmt->close();
                    break;
                case 'urun':
                    $update_query = "UPDATE urunler SET depo = ?, raf = ? WHERE urun_kodu = ?";
                    $update_stmt = $connection->prepare($update_query);
                    $update_stmt->bind_param('ssi', $hedef_depo, $hedef_raf, $kod);
                    $update_stmt->execute();
                    $update_stmt->close();
                    break;
            }

            echo json_encode(['status' => 'success', 'message' => 'Stok başarıyla transfer edildi.']);

        } catch (Exception $e) {
            // Rollback transaction on error
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Transfer işlemi sırasında hata oluştu: ' . $e->getMessage()]);
        }

        $connection->autocommit(TRUE); // Reset autocommit
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>
