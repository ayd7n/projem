<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_product' && isset($_GET['id'])) {
        $urun_kodu = (int)$_GET['id'];
        $query = "SELECT * FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $response = ['status' => 'success', 'data' => $product];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün bulunamadı.'];
        }
    }
    elseif ($action == 'get_depo_list') {
        $query = "SELECT DISTINCT depo_ismi FROM lokasyonlar ORDER BY depo_ismi";
        $result = $connection->query($query);
        $depolar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $depolar[] = $row;
            }
            $response = ['status' => 'success', 'data' => $depolar];
        } else {
            $response = ['status' => 'error', 'message' => 'Depo listesi alınamadı.'];
        }
    }
    elseif ($action == 'get_raf_list') {
        $depo = $_GET['depo'] ?? '';
        $query = "SELECT DISTINCT raf FROM lokasyonlar WHERE depo_ismi = ? ORDER BY raf";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('s', $depo);
        $stmt->execute();
        $result = $stmt->get_result();
        $raflar = [];
        while ($row = $result->fetch_assoc()) {
            $raflar[] = $row;
        }
        $stmt->close();
        $response = ['status' => 'success', 'data' => $raflar];
    }
    elseif ($action == 'get_all_products') {
        $query = "SELECT * FROM urunler ORDER BY urun_ismi";
        $result = $connection->query($query);
        
        if ($result) {
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $response = ['status' => 'success', 'data' => $products];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün listesi alınamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $urun_ismi = $_POST['urun_ismi'] ?? null;
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (int)$_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'adet';
    $satis_fiyati = isset($_POST['satis_fiyati']) ? (float)$_POST['satis_fiyati'] : 0.0;
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int)$_POST['kritik_stok_seviyesi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';

    if ($action == 'add_product') {
        if (empty($urun_ismi)) {
             $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            $query = "INSERT INTO urunler (urun_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, kritik_stok_seviyesi, depo, raf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ssisdiss', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $kritik_stok_seviyesi, $depo, $raf);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Ürün başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_product' && isset($_POST['urun_kodu'])) {
        $urun_kodu = (int)$_POST['urun_kodu'];
        if (empty($urun_ismi)) {
             $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            $query = "UPDATE urunler SET urun_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, satis_fiyati = ?, kritik_stok_seviyesi = ?, depo = ?, raf = ? WHERE urun_kodu = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ssisdissi', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $kritik_stok_seviyesi, $depo, $raf, $urun_kodu);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Ürün başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_product' && isset($_POST['urun_kodu'])) {
        $urun_kodu = (int)$_POST['urun_kodu'];
        $query = "DELETE FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Ürün başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>
