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
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getTanks() {
    global $connection;

    $query = "SELECT tank_id, tank_kodu, tank_ismi, kapasite, not_bilgisi FROM tanklar ORDER BY tank_ismi";
    $result = $connection->query($query);

    $tanks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tanks[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $tanks]);
}
?>