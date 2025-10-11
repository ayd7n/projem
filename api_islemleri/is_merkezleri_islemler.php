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
    case 'get_work_centers':
        getWorkCenters();
        break;
    case 'get_work_center':
        getWorkCenter();
        break;
    case 'add_work_center':
        addWorkCenter();
        break;
    case 'update_work_center':
        updateWorkCenter();
        break;
    case 'delete_work_center':
        deleteWorkCenter();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getWorkCenters() {
    global $connection;

    $query = "SELECT * FROM is_merkezleri ORDER BY isim";
    $result = $connection->query($query);

    $work_centers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $work_centers[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $work_centers]);
}

function getWorkCenter() {
    global $connection;

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM is_merkezleri WHERE is_merkezi_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $work_center = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $work_center]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi bulunamadı.']);
    }

    $stmt->close();
}

function addWorkCenter() {
    global $connection;

    $isim = $_POST['isim'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';

    if (empty($isim)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi adı zorunludur.']);
        return;
    }

    $query = "INSERT INTO is_merkezleri (isim, aciklama) VALUES (?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ss', $isim, $aciklama);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateWorkCenter() {
    global $connection;

    $is_merkezi_id = $_POST['is_merkezi_id'] ?? '';
    $isim = $_POST['isim'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';

    if (empty($is_merkezi_id) || empty($isim)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID ve isim alanları zorunludur.']);
        return;
    }

    $query = "UPDATE is_merkezleri SET isim = ?, aciklama = ? WHERE is_merkezi_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssi', $isim, $aciklama, $is_merkezi_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteWorkCenter() {
    global $connection;

    $is_merkezi_id = $_POST['is_merkezi_id'] ?? '';
    if (empty($is_merkezi_id)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID gerekli.']);
        return;
    }

    $query = "DELETE FROM is_merkezleri WHERE is_merkezi_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $is_merkezi_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>
