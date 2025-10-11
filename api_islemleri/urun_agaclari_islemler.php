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

    if ($action == 'get_product_tree' && isset($_GET['id'])) {
        $urun_agaci_id = (int)$_GET['id'];
        $query = "SELECT * FROM urun_agaci WHERE urun_agaci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_agaci_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_tree = $result->fetch_assoc();
        $stmt->close();

        if ($product_tree) {
            $response = ['status' => 'success', 'data' => $product_tree];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün ağacı bulunamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $urun_kodu = $_POST['urun_kodu'] ?? null;
    $urun_ismi = $_POST['urun_ismi'] ?? '';
    $bilesenin_malzeme_turu = $_POST['bilesenin_malzeme_turu'] ?? '';
    $bilesen_kodu = $_POST['bilesen_kodu'] ?? '';
    $bilesen_ismi = $_POST['bilesen_ismi'] ?? '';
    $bilesen_miktari = isset($_POST['bilesen_miktari']) ? (float)$_POST['bilesen_miktari'] : 0;

    if ($action == 'add_product_tree') {
        if (empty($urun_kodu) || empty($bilesen_kodu)) {
            $response = ['status' => 'error', 'message' => 'Ürün ve bileşen boş olamaz.'];
        } else {
            $query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('issssd', $urun_kodu, $urun_ismi, $bilesenin_malzeme_turu, $bilesen_kodu, $bilesen_ismi, $bilesen_miktari);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_product_tree' && isset($_POST['urun_agaci_id'])) {
        $urun_agaci_id = (int)$_POST['urun_agaci_id'];
        
        if (empty($urun_kodu) || empty($bilesen_kodu)) {
            $response = ['status' => 'error', 'message' => 'Ürün ve bileşen boş olamaz.'];
        } else {
            $query = "UPDATE urun_agaci SET urun_kodu = ?, urun_ismi = ?, bilesenin_malzeme_turu = ?, bilesen_kodu = ?, bilesen_ismi = ?, bilesen_miktari = ? WHERE urun_agaci_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('issssdi', $urun_kodu, $urun_ismi, $bilesenin_malzeme_turu, $bilesen_kodu, $bilesen_ismi, $bilesen_miktari, $urun_agaci_id);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_product_tree' && isset($_POST['urun_agaci_id'])) {
        $urun_agaci_id = (int)$_POST['urun_agaci_id'];
        
        $query = "DELETE FROM urun_agaci WHERE urun_agaci_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_agaci_id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>