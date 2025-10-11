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

    if ($action == 'get_material' && isset($_GET['id'])) {
        $malzeme_kodu = (int)$_GET['id'];
        $query = "SELECT * FROM malzemeler WHERE malzeme_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $malzeme_kodu);
        $stmt->execute();
        $result = $stmt->get_result();
        $material = $result->fetch_assoc();
        $stmt->close();

        if ($material) {
            $response = ['status' => 'success', 'data' => $material];
        } else {
            $response = ['status' => 'error', 'message' => 'Malzeme bulunamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $malzeme_ismi = $_POST['malzeme_ismi'] ?? null;
    $malzeme_turu = $_POST['malzeme_turu'] ?? '';
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (float)$_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'adet';
    $termin_suresi = isset($_POST['termin_suresi']) ? (int)$_POST['termin_suresi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int)$_POST['kritik_stok_seviyesi'] : 0;

    if ($action == 'add_material') {
        if (empty($malzeme_ismi)) {
            $response = ['status' => 'error', 'message' => 'Malzeme ismi boş olamaz.'];
        } else {
            $query = "INSERT INTO malzemeler (malzeme_ismi, malzeme_turu, not_bilgisi, stok_miktari, birim, termin_suresi, depo, raf, kritik_stok_seviyesi) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsissi', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Malzeme başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_material' && isset($_POST['malzeme_kodu'])) {
        $malzeme_kodu = (int)$_POST['malzeme_kodu'];
        
        if (empty($malzeme_ismi)) {
            $response = ['status' => 'error', 'message' => 'Malzeme ismi boş olamaz.'];
        } else {
            $query = "UPDATE malzemeler SET malzeme_ismi = ?, malzeme_turu = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, termin_suresi = ?, depo = ?, raf = ?, kritik_stok_seviyesi = ? WHERE malzeme_kodu = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsissii', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi, $malzeme_kodu);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Malzeme başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_material' && isset($_POST['malzeme_kodu'])) {
        $malzeme_kodu = (int)$_POST['malzeme_kodu'];
        
        $query = "DELETE FROM malzemeler WHERE malzeme_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $malzeme_kodu);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Malzeme başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>