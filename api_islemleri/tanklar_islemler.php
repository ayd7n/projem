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
    case 'get_tanks':
        getTanks();
        break;
    case 'get_tank':
        getTank();
        break;
    case 'add_tank':
        addTank();
        break;
    case 'update_tank':
        updateTank();
        break;
    case 'delete_tank':
        deleteTank();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getTanks() {
    global $connection;

    $query = "SELECT * FROM tanklar ORDER BY tank_ismi";
    $result = $connection->query($query);

    $tanks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tanks[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $tanks]);
}

function getTank() {
    global $connection;

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tank ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM tanklar WHERE tank_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $tank = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $tank]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tank bulunamadı.']);
    }

    $stmt->close();
}

function addTank() {
    global $connection;

    $tank_kodu = $_POST['tank_kodu'] ?? '';
    $tank_ismi = $_POST['tank_ismi'] ?? '';
    $kapasite = $_POST['kapasite'] ?? '';
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';

    if (empty($tank_kodu) || empty($tank_ismi) || empty($kapasite)) {
        echo json_encode(['status' => 'error', 'message' => 'Tank kodu, tank ismi ve kapasite alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO tanklar (tank_kodu, tank_ismi, kapasite, not_bilgisi) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssds', $tank_kodu, $tank_ismi, $kapasite, $not_bilgisi);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tank oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateTank() {
    global $connection;

    $tank_id = $_POST['tank_id'] ?? '';
    $tank_kodu = $_POST['tank_kodu'] ?? '';
    $tank_ismi = $_POST['tank_ismi'] ?? '';
    $kapasite = $_POST['kapasite'] ?? '';
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';

    if (empty($tank_id) || empty($tank_kodu) || empty($tank_ismi) || empty($kapasite)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    $query = "UPDATE tanklar SET tank_kodu = ?, tank_ismi = ?, kapasite = ?, not_bilgisi = ? WHERE tank_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssdsi', $tank_kodu, $tank_ismi, $kapasite, $not_bilgisi, $tank_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tank güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteTank() {
    global $connection;

    $tank_id = $_POST['tank_id'] ?? '';
    if (empty($tank_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tank ID gerekli.']);
        return;
    }

    $query = "DELETE FROM tanklar WHERE tank_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $tank_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tank silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>
