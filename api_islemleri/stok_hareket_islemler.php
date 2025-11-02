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
        $query = "SELECT DISTINCT depo_ismi FROM lokasyonlar ORDER BY depo_ismi";

        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // For each unique depot, get all shelves
                $shelves_query = "SELECT DISTINCT raf FROM lokasyonlar WHERE depo_ismi = ? ORDER BY raf";
                $shelves_stmt = $connection->prepare($shelves_query);
                $shelves_stmt->bind_param('s', $row['depo_ismi']);
                $shelves_stmt->execute();
                $shelves_result = $shelves_stmt->get_result();
                
                $shelves = [];
                while ($shelf = $shelves_result->fetch_assoc()) {
                    $shelves[] = $shelf['raf'];
                }
                
                $locations[] = [
                    'depo_ismi' => $row['depo_ismi'],
                    'raflar' => $shelves
                ];
                
                $shelves_stmt->close();
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
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari, tank_kodu, tank_ismi FROM esanslar WHERE esans_kodu = '$item_code'";
                $result = $connection->query($query);

                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    // For essences, return the tank information from the esanslar table
                    $location_data = [
                        'tank_kodu' => $item['tank_kodu'] ?? null,
                        'tank_ismi' => $item['tank_ismi'] ?? null,
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
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari as stok, tank_kodu, tank_ismi FROM esanslar ORDER BY esans_ismi";
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

    case 'get_suppliers_for_material':
        $material_code = $_GET['material_code'] ?? '';

        if (!$material_code) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu belirtilmedi.']);
            break;
        }

        // Get distinct suppliers for the material from cerceve_sozlesmeler table
        $query = "SELECT DISTINCT t.tedarikci_id, t.tedarikci_adi AS tedarikci_ismi 
                  FROM cerceve_sozlesmeler cs
                  JOIN tedarikciler t ON cs.tedarikci_adi = t.tedarikci_adi
                  WHERE cs.malzeme_kodu = ? 
                  ORDER BY t.tedarikci_adi";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('s', $material_code);
        $stmt->execute();
        $result = $stmt->get_result();

        $suppliers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $suppliers]);
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

    case 'get_all_movements':
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 25;

        if ($per_page < 1) {
            $per_page = 10;
        }
        $per_page = min($per_page, 200);

        if ($page < 1) {
            $page = 1;
        }

        $total = 0;
        $count_query = "SELECT COUNT(*) AS total FROM stok_hareket_kayitlari";
        $count_result = $connection->query($count_query);
        if ($count_result) {
            $count_row = $count_result->fetch_assoc();
            $total = isset($count_row['total']) ? (int) $count_row['total'] : 0;
        }

        $max_page = $total > 0 ? (int) ceil($total / $per_page) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }

        $offset = ($page - 1) * $per_page;

        $query = "SELECT * FROM stok_hareket_kayitlari ORDER BY tarih DESC LIMIT ? OFFSET ?";
        $stmt = $connection->prepare($query);

        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Hareketler al��namad��: ' . $connection->error]);
            break;
        }

        $stmt->bind_param('ii', $per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $movements = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $movements[] = $row;
            }
        }
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'data' => $movements,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ]);
        break;

    case 'get_total_movements':
        $query = "SELECT COUNT(*) as total FROM stok_hareket_kayitlari";
        $result = $connection->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => intval($row['total'])]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Toplam hareket sayısı alınamadı.']);
        }
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
        $cerceve_sozlesme_id = $_POST['cerceve_sozlesme_id'] ?? '';
        $fatura_no = $_POST['fatura_no'] ?? '';
        $tedarikci = $_POST['tedarikci'] ?? '';

        // Validation
        if (!$stok_turu || !$kod || !$miktar || !$yon || !$hareket_turu || !$aciklama) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // For 'mal_kabul' action, ensure we have a supplier
        if ($hareket_turu === 'mal_kabul' && empty($tedarikci)) {
            echo json_encode(['status' => 'error', 'message' => 'Mal kabul işlemi için tedarikçi seçilmelidir.']);
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
        $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $movement_stmt = $connection->prepare($movement_query);
        $movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $aciklama, $_SESSION['user_id'], $_SESSION['kullanici_adi'], $tedarikci);

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
        $tedarikci = $_POST['tedarikci'] ?? '';

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
        $movement_query = "UPDATE stok_hareket_kayitlari SET stok_turu = ?, kod = ?, isim = ?, birim = ?, miktar = ?, yon = ?, hareket_turu = ?, depo = ?, raf = ?, tank_kodu = ?, ilgili_belge_no = ?, aciklama = ?, tedarikci_ismi = ? WHERE hareket_id = ?";
        $movement_stmt = $connection->prepare($movement_query);
        $movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $aciklama, $tedarikci, $hareket_id);

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
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $hedef_tank_kodu = $_POST['hedef_tank_kodu'] ?? '';

        // Get source and destination locations for materials and products
        // For essences, we will use depot and raf as well but the primary storage will be tanks
        $kaynak_depo = $_POST['kaynak_depo'] ?? '';
        $kaynak_raf = $_POST['kaynak_raf'] ?? '';
        $hedef_depo = $_POST['hedef_depo'] ?? '';
        $hedef_raf = $_POST['hedef_raf'] ?? '';

        // For essence transfers, we require hedef_tank_kodu
        if ($stok_turu === 'esans' && (!$hedef_tank_kodu || !$tank_kodu)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen hem kaynak hem de hedef tankı belirtin.']);
            break;
        }

        // For materials and products, we require depot and raf
        if ($stok_turu !== 'esans' && (!$kaynak_depo || !$kaynak_raf || !$hedef_depo || !$hedef_raf)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen hem kaynak hem de hedef konumu belirtin.']);
            break;
        }

        // Validation
        if (!$stok_turu || !$kod || $miktar <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Check if source and destination are the same
        if (($stok_turu !== 'esans' && $kaynak_depo === $hedef_depo && $kaynak_raf === $hedef_raf) ||
            ($stok_turu === 'esans' && $tank_kodu === $hedef_tank_kodu)) {
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
        $hedef_depo = $connection->real_escape_string($hedef_depo);
        $hedef_raf = $connection->real_escape_string($hedef_raf);
        $tank_kodu = $connection->real_escape_string($tank_kodu);
        $hedef_tank_kodu = $connection->real_escape_string($hedef_tank_kodu);

        // First, get item details
        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = '$kod'";
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = '$kod'";
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = '$kod'";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü. Sadece malzeme, ürün ve essence transferi desteklenmektedir.']);
                exit;
        }

        $item_result = $connection->query($item_query);
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            if ($stok_turu === 'malzeme') {
                $item_name = $item['malzeme_ismi'] ?? '';
                $item_unit = $item['birim'];
            } else if ($stok_turu === 'urun') {
                $item_name = $item['urun_ismi'] ?? '';
                $item_unit = $item['birim'];
            } else if ($stok_turu === 'esans') {
                $item_name = $item['esans_ismi'] ?? '';
                $item_unit = $item['birim'];
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün kodu.']);
            break;
        }

        // Check current stock for essence or materials/products
        $available_stock = 0;

        if ($stok_turu === 'esans') {
            // For essences, check the main table as they are stored by tank
            $overall_stock_query = "SELECT stok_miktari FROM esanslar WHERE esans_kodu = '$kod'";
            $overall_result = $connection->query($overall_stock_query);
            if ($overall_result && $overall_result->num_rows > 0) {
                $overall_row = $overall_result->fetch_assoc();
                $available_stock = floatval($overall_row['stok_miktari']) ?? 0;
            }
        } else {
            // For materials and products, check the overall stock from the main table
            // since each item is stored in a single location
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

            // For essence transfers, we use tank_kodu in the movements, for materials/products we use depot/raf
            if ($stok_turu === 'esans') {
                $source_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_cikis, $hareket_turu_transfer, $kaynak_depo, $kaynak_raf, $tank_kodu, $ilgili_belge_no, $source_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);
            } else {
                $source_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_cikis, $hareket_turu_transfer, $kaynak_depo, $kaynak_raf, $null1, $ilgili_belge_no, $source_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);
            }

            if (!$source_movement_stmt->execute()) {
                throw new Exception('Kaynak hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $source_movement_id = $source_movement_stmt->insert_id;
            $source_movement_stmt->close();

            // Step 2: Create the destination (incoming) stock movement
            $dest_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $dest_movement_stmt = $connection->prepare($dest_movement_query);

            // For essence transfers, we use tank_kodu in the movements, for materials/products we use depot/raf
            if ($stok_turu === 'esans') {
                $dest_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_giris, $hareket_turu_transfer, $hedef_depo, $hedef_raf, $tank_kodu, $ilgili_belge_no, $dest_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);
            } else {
                $dest_movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon_giris, $hareket_turu_transfer, $hedef_depo, $hedef_raf, $null1, $ilgili_belge_no, $dest_description, $_SESSION['user_id'], $_SESSION['kullanici_adi']);
            }

            if (!$dest_movement_stmt->execute()) {
                throw new Exception('Hedef hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $dest_movement_id = $dest_movement_stmt->insert_id;
            $dest_movement_stmt->close();

            // Commit transaction
            $connection->commit();

            // Update the location in the main item table (urunler, malzemeler or esanslar) to the destination location
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
                case 'esans':
                    // For essences, we update the tank information and stock amounts
                    // First, get the target tank name
                    $tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_kodu = ?";
                    $tank_stmt = $connection->prepare($tank_query);
                    $tank_stmt->bind_param('s', $hedef_tank_kodu);
                    $tank_stmt->execute();
                    $tank_result = $tank_stmt->get_result();
                    
                    if ($tank_result->num_rows === 0) {
                        throw new Exception('Hedef tank bulunamadı.');
                    }
                    
                    $tank_info = $tank_result->fetch_assoc();
                    $hedef_tank_ismi = $tank_info['tank_ismi'];
                    $tank_stmt->close();
                    
                    // First, reduce stock from source
                    $reduce_query = "UPDATE esanslar SET stok_miktari = stok_miktari - ? WHERE esans_kodu = ?";
                    $reduce_stmt = $connection->prepare($reduce_query);
                    $reduce_stmt->bind_param('ds', $miktar, $kod);
                    $reduce_stmt->execute();
                    $reduce_stmt->close();

                    // Then increase stock at destination with new tank assignment
                    $increase_query = "UPDATE esanslar SET stok_miktari = stok_miktari + ?, tank_kodu = ?, tank_ismi = ? WHERE esans_kodu = ?";
                    $increase_stmt = $connection->prepare($increase_query);
                    $increase_stmt->bind_param('dsss', $miktar, $hedef_tank_kodu, $hedef_tank_ismi, $kod);
                    $increase_stmt->execute();
                    $increase_stmt->close();
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

    case 'get_contracts_by_priority':
        $material_kodu = $_POST['material_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        
        if (!$material_kodu || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu ve tedarikci ID gerekli.']);
            break;
        }
        
        $contract_query = "SELECT c.sozlesme_id, c.limit_miktar, 
                          COALESCE(kullanilan.toplam_mal_kabul, 0) as toplam_mal_kabul_miktari,
                          (c.limit_miktar - COALESCE(kullanilan.toplam_mal_kabul, 0)) as kalan_miktar,
                          c.oncelik
                          FROM cerceve_sozlesmeler c
                          LEFT JOIN (
                              SELECT 
                                  tedarikci_id,
                                  kod as malzeme_kodu,
                                  SUM(miktar) as toplam_mal_kabul
                              FROM stok_hareket_kayitlari
                              WHERE hareket_turu = 'mal_kabul'
                              GROUP BY tedarikci_id, kod
                          ) kullanilan ON c.tedarikci_id = kullanilan.tedarikci_id 
                          AND c.malzeme_kodu = kullanilan.malzeme_kodu
                          WHERE c.tedarikci_id = ? 
                          AND c.malzeme_kodu = ?
                          AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                          AND COALESCE(kullanilan.toplam_mal_kabul, 0) < c.limit_miktar
                          ORDER BY c.oncelik ASC, kalan_miktar DESC";
        
        $contract_stmt = $connection->prepare($contract_query);
        $contract_stmt->bind_param('is', $tedarikci_id, $material_kodu);
        $contract_stmt->execute();
        $contract_result = $contract_stmt->get_result();
        
        $contracts = [];
        while ($row = $contract_result->fetch_assoc()) {
            $contracts[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'contracts' => $contracts]);
        $contract_stmt->close();
        break;

    case 'check_framework_contract':
        $material_kodu = $_POST['material_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        
        if (!$material_kodu || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu ve tedarikci ID gerekli.']);
            break;
        }
        
        // Get material name
        $material_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $material_stmt = $connection->prepare($material_query);
        $material_stmt->bind_param('s', $material_kodu);
        $material_stmt->execute();
        $material_result = $material_stmt->get_result();
        $material_name = '';
        if ($material_result->num_rows > 0) {
            $material = $material_result->fetch_assoc();
            $material_name = $material['malzeme_ismi'];
        }
        $material_stmt->close();
        
        // Check if there is a valid framework contract for this supplier and material
        $contract_check_query = "SELECT c.sozlesme_id, c.limit_miktar, 
                                COALESCE(kullanilan.toplam_mal_kabul, 0) as toplam_mal_kabul_miktari,
                                (c.limit_miktar - COALESCE(kullanilan.toplam_mal_kabul, 0)) as kalan_miktar
                                FROM cerceve_sozlesmeler c
                                LEFT JOIN (
                                    SELECT 
                                        tedarikci_id,
                                        kod as malzeme_kodu,
                                        SUM(miktar) as toplam_mal_kabul
                                    FROM stok_hareket_kayitlari
                                    WHERE hareket_turu = 'mal_kabul'
                                    GROUP BY tedarikci_id, kod
                                ) kullanilan ON c.tedarikci_id = kullanilan.tedarikci_id 
                                AND c.malzeme_kodu = kullanilan.malzeme_kodu
                                WHERE c.tedarikci_id = ? 
                                AND c.malzeme_kodu = ?
                                AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                                AND COALESCE(kullanilan.toplam_mal_kabul, 0) < c.limit_miktar
                                ORDER BY c.oncelik ASC, kalan_miktar DESC";
        $contract_check_stmt = $connection->prepare($contract_check_query);
        $contract_check_stmt->bind_param('is', $tedarikci_id, $material_kodu);
        $contract_check_stmt->execute();
        $contract_result = $contract_check_stmt->get_result();
        
        if ($contract_result->num_rows > 0) {
            $contract = $contract_result->fetch_assoc();
            echo json_encode([
                'status' => 'success', 
                'contract_info' => [
                    'has_valid_contract' => true,
                    'material_name' => $material_name,
                    'remaining_amount' => $contract['kalan_miktar']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'success', 
                'contract_info' => [
                    'has_valid_contract' => false,
                    'material_name' => $material_name,
                    'remaining_amount' => 0
                ]
            ]);
        }
        $contract_check_stmt->close();
        break;

    case 'add_mal_kabul':
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';
        $tedarikci_id = $_POST['tedarikci'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';

        // Validation
        if (!$stok_turu || !$kod || !$miktar || !$aciklama || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Lutfen tum zorunlu alanlari doldurun (stok_turu, kod, miktar, aciklama ve tedarikci).']);
            break;
        }

        // Ensure we're dealing with materials only
        if ($stok_turu !== 'malzeme') {
            echo json_encode(['status' => 'error', 'message' => 'Mal kabul sadece malzeme turu icin yapilabilir.']);
            break;
        }

        // Get item name, unit, and current location based on stock type
        $item_name = '';
        $item_unit = '';
        $current_depo = '';
        $current_raf = '';

        $item_query = "SELECT malzeme_ismi, birim, depo, raf FROM malzemeler WHERE malzeme_kodu = ?";
        $item_stmt = $connection->prepare($item_query);
        $item_stmt->bind_param('s', $kod);
        
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'];
            $item_unit = $item['birim'];
            $current_depo = $item['depo'] ?? '';
            $current_raf = $item['raf'] ?? '';
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz malzeme kodu.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        // Use current location if not provided in the form
        if (!$depo) {
            $depo = $current_depo;
        }
        if (!$raf) {
            $raf = $current_raf;
        }

        // Get supplier name from ID
        $tedarikci_ismi = '';
        $supplier_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $supplier_stmt = $connection->prepare($supplier_query);
        $supplier_stmt->bind_param('i', $tedarikci_id);
        $supplier_stmt->execute();
        $supplier_result = $supplier_stmt->get_result();
        if ($supplier_result->num_rows > 0) {
            $supplier = $supplier_result->fetch_assoc();
            $tedarikci_ismi = $supplier['tedarikci_adi'];
        }
        $supplier_stmt->close();

        // Check available framework contracts and split the amount if necessary
        $contract_query = "SELECT c.sozlesme_id, c.limit_miktar, 
                          COALESCE(shs.toplam_kullanilan, 0) as toplam_mal_kabul_miktari,
                          (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) as kalan_miktar,
                          c.oncelik
                          FROM cerceve_sozlesmeler c
                          LEFT JOIN (
                              SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
                              FROM stok_hareketleri_sozlesmeler shs
                              WHERE EXISTS (
                                  SELECT 1 FROM stok_hareket_kayitlari
                                  WHERE stok_hareket_kayitlari.hareket_id = shs.hareket_id
                                  AND stok_hareket_kayitlari.hareket_turu = 'mal_kabul'
                              )
                              GROUP BY sozlesme_id
                          ) shs ON c.sozlesme_id = shs.sozlesme_id
                          WHERE c.tedarikci_id = ? 
                          AND c.malzeme_kodu = ?
                          AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                          AND COALESCE(shs.toplam_kullanilan, 0) < c.limit_miktar
                          ORDER BY c.oncelik ASC, kalan_miktar DESC";
        
        $contract_stmt = $connection->prepare($contract_query);
        $contract_stmt->bind_param('is', $tedarikci_id, $kod);
        $contract_stmt->execute();
        $contract_result = $contract_stmt->get_result();
        
        $contracts = [];
        while ($row = $contract_result->fetch_assoc()) {
            $contracts[] = $row;
        }
        $contract_stmt->close();
        
        // Check if we have enough contracts to cover the requested amount
        $total_available = array_sum(array_column($contracts, 'kalan_miktar'));
        $requested_amount = $miktar;
        
        if ($total_available < $requested_amount) {
            echo json_encode(['status' => 'error', 'message' => 'Tum gecerli sozlesme limitleri bu islem icin yeterli degildir.']);
            break;
        }

        // Process the amount using contracts in priority order
        $remaining_amount = $requested_amount;
        $total_updated_stock = 0;
        
        foreach ($contracts as $contract) {
            if ($remaining_amount <= 0) {
                break; // All requested amount has been allocated
            }
            
            $contract_amount = min($contract['kalan_miktar'], $remaining_amount);
            
            // Insert stock movement for this contract portion
            $yon = 'giris'; // Mal kabul is always incoming
            $hareket_turu = 'mal_kabul'; // Specific for mal kabul
            $tank_kodu = $_POST['tank_kodu'] ?? ''; // Although not typically used for materials

            $connection->autocommit(FALSE); // Start transaction
            
            $contract_specific_aciklama = $aciklama . ' [Sozlesme ID: ' . $contract['sozlesme_id'] . ']';
            $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi, tedarikci_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $movement_stmt = $connection->prepare($movement_query);
            $movement_stmt->bind_param('ssssdssssssisssi', $stok_turu, $kod, $item_name, $item_unit, $contract_amount, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $contract_specific_aciklama, $_SESSION['user_id'], $_SESSION['kullanici_adi'], $tedarikci_ismi, $tedarikci_id);

            if (!$movement_stmt->execute()) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Mal kabul islemi kaydedilirken hata olustu: ' . $connection->error]);
                $movement_stmt->close();
                break;
            }
            
            $hareket_id = $connection->insert_id;
            $movement_stmt->close();
            
            $contract_id = $contract['sozlesme_id'];
            // Insert the relationship between stock movement and contract
            $contract_link_query = "INSERT INTO stok_hareketleri_sozlesmeler (hareket_id, sozlesme_id, kullanilan_miktar) VALUES (?, ?, ?)";
            $contract_link_stmt = $connection->prepare($contract_link_query);
            $contract_link_stmt->bind_param('iid', $hareket_id, $contract_id, $contract_amount);
            
            if (!$contract_link_stmt->execute()) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Sozlesme baglantisi kaydedilirken hata olustu: ' . $connection->error]);
                $contract_link_stmt->close();
                break;
            }
            
            $contract_link_stmt->close();
            
            $remaining_amount -= $contract_amount;
            $total_updated_stock += $contract_amount;
        }
        
        // Commit the transaction if everything was successful
        if ($remaining_amount <= 0) {
            $connection->commit();
            $connection->autocommit(TRUE);
        } else {
            $connection->rollback();
            $connection->autocommit(TRUE);
            echo json_encode(['status' => 'error', 'message' => 'Miktar dagitimi sirasinda hata olustu.']);
            break;
        }

        // Update the material stock in the main materials table
        if ($total_updated_stock > 0) {
            $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + ? WHERE malzeme_kodu = ?";
            $stock_stmt = $connection->prepare($stock_query);
            $stock_stmt->bind_param('ds', $total_updated_stock, $kod);

            if ($stock_stmt->execute()) {
                // Also update the location information if provided
                if ($depo && $raf) {
                    $location_query = "UPDATE malzemeler SET depo = ?, raf = ? WHERE malzeme_kodu = ?";
                    $location_stmt = $connection->prepare($location_query);
                    $location_stmt->bind_param('sss', $depo, $raf, $kod);
                    $location_stmt->execute();
                    $location_stmt->close();
                }
                
                echo json_encode(['status' => 'success', 'message' => 'Mal kabul islemi basariyla kaydedildi ve stok guncellendi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Mal kabul islemi kaydedildi ama stok guncellenirken hata olustu: ' . $connection->error]);
            }

            $stock_stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Miktar dagitimi sirasinda hata olustu.']);
        }
        
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz islem.']);
        break;
}
?>
