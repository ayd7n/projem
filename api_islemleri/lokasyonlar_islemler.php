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

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_locations':
        getLocations();
        break;
    case 'get_location':
        getLocation();
        break;
    case 'add_location':
        addLocation();
        break;
    case 'update_location':
        updateLocation();
        break;
    case 'delete_location':
        deleteLocation();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getLocations() {
    global $connection;

    $query = "SELECT * FROM lokasyonlar ORDER BY depo_ismi, raf";
    $result = $connection->query($query);

    $locations = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $locations]);
}

function getLocation() {
    global $connection;

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM lokasyonlar WHERE lokasyon_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $location = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $location]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon bulunamadı.']);
    }

    $stmt->close();
}

function addLocation() {
    global $connection;

    $depo_ismi = $_POST['depo_ismi'] ?? '';
    $raf = $_POST['raf'] ?? '';

    if (empty($depo_ismi) || empty($raf)) {
        echo json_encode(['status' => 'error', 'message' => 'Depo ismi ve raf alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO lokasyonlar (depo_ismi, raf) VALUES (?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ss', $depo_ismi, $raf);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Lokasyon başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateLocation() {
    global $connection;

    $lokasyon_id = $_POST['lokasyon_id'] ?? '';
    $depo_ismi = $_POST['depo_ismi'] ?? '';
    $raf = $_POST['raf'] ?? '';

    if (empty($lokasyon_id) || empty($depo_ismi) || empty($raf)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    $query = "UPDATE lokasyonlar SET depo_ismi = ?, raf = ? WHERE lokasyon_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssi', $depo_ismi, $raf, $lokasyon_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Lokasyon başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteLocation() {
    global $connection;

    $lokasyon_id = $_POST['lokasyon_id'] ?? '';
    if (empty($lokasyon_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon ID gerekli.']);
        return;
    }

    $query = "DELETE FROM lokasyonlar WHERE lokasyon_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $lokasyon_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Lokasyon başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>
