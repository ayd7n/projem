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

    if ($action == 'get_essence' && isset($_GET['id'])) {
        $esans_id = (int)$_GET['id'];
        $query = "SELECT * FROM esanslar WHERE esans_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $esans_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $essence = $result->fetch_assoc();
        $stmt->close();

        if ($essence) {
            $response = ['status' => 'success', 'data' => $essence];
        } else {
            $response = ['status' => 'error', 'message' => 'Esans bulunamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $esans_kodu = $_POST['esans_kodu'] ?? null;
    $esans_ismi = $_POST['esans_ismi'] ?? null;
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (float)$_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'lt';
    $demlenme_suresi_gun = isset($_POST['demlenme_suresi_gun']) ? (int)$_POST['demlenme_suresi_gun'] : 0;

    if ($action == 'add_essence') {
        if (empty($esans_kodu) || empty($esans_ismi)) {
            $response = ['status' => 'error', 'message' => 'Esans kodu ve ismi boş olamaz.'];
        } else {
            $query = "INSERT INTO esanslar (esans_kodu, esans_ismi, not_bilgisi, stok_miktari, birim, demlenme_suresi_gun) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsd', $esans_kodu, $esans_ismi, $not_bilgisi, $stok_miktari, $birim, $demlenme_suresi_gun);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Esans başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_essence' && isset($_POST['esans_id'])) {
        $esans_id = (int)$_POST['esans_id'];
        
        if (empty($esans_kodu) || empty($esans_ismi)) {
            $response = ['status' => 'error', 'message' => 'Esans kodu ve ismi boş olamaz.'];
        } else {
            $query = "UPDATE esanslar SET esans_kodu = ?, esans_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, demlenme_suresi_gun = ? WHERE esans_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsdi', $esans_kodu, $esans_ismi, $not_bilgisi, $stok_miktari, $birim, $demlenme_suresi_gun, $esans_id);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Esans başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_essence' && isset($_POST['esans_id'])) {
        $esans_id = (int)$_POST['esans_id'];
        
        $query = "DELETE FROM esanslar WHERE esans_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $esans_id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Esans başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>