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
        $movement_stmt->bind_param('ssssdsssssssis', $stok_turu, $kod, $item_name, $item_unit, $miktar, $yon, $hareket_turu, $depo, $raf, $tank_kodu, $ilgili_belge_no, $aciklama, $_SESSION['id'], $_SESSION['kullanici_adi']);

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

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>
