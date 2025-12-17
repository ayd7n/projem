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
    case 'get_tanks_paginated':
        getTanksPaginated();
        break;
    case 'get_total_tanks':
        getTotalTanks();
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

    // Get tanks with their assignment status to essences
    $query = "SELECT t.tank_id, t.tank_kodu, t.tank_ismi, t.kapasite, t.not_bilgisi, 
              e.esans_id, e.esans_ismi 
              FROM tanklar t 
              LEFT JOIN esanslar e ON t.tank_kodu = e.tank_kodu 
              ORDER BY t.tank_ismi";
    $result = $connection->query($query);

    $tanks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tanks[] = [
                'tank_id' => $row['tank_id'],
                'tank_kodu' => $row['tank_kodu'],
                'tank_ismi' => $row['tank_ismi'],
                'kapasite' => $row['kapasite'],
                'not_bilgisi' => $row['not_bilgisi'],
                'in_use' => !empty($row['esans_id']),
                'used_by_essence_id' => $row['esans_id'],
                'used_by_essence_name' => $row['esans_ismi']
            ];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $tanks]);
}

function getTotalTanks() {
    global $connection;

    $query = "SELECT COUNT(*) AS total FROM tanklar";
    $result = $connection->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => (int)$row['total']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam tank sayısı alınırken hata oluştu.']);
    }
}

function addTank() {
    global $connection;

    $tank_kodu = $connection->real_escape_string($_POST['tank_kodu'] ?? '');
    $tank_ismi = $connection->real_escape_string($_POST['tank_ismi'] ?? '');
    $kapasite = floatval($_POST['kapasite'] ?? 0);
    $not_bilgisi = $connection->real_escape_string($_POST['not_bilgisi'] ?? '');

    if (empty($tank_kodu) || empty($tank_ismi) || $kapasite < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Tank kodu, tank ismi ve kapasite alanları zorunludur.']);
        return;
    }

    try {
        $query = "INSERT INTO tanklar (tank_kodu, tank_ismi, kapasite, not_bilgisi) VALUES ('$tank_kodu', '$tank_ismi', $kapasite, '$not_bilgisi')";
        $result = $connection->query($query);

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$tank_ismi adlı tank sisteme eklendi", 'CREATE');
            echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla oluşturuldu.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tank oluşturulurken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function updateTank() {
    global $connection;

    $tank_id = (int)($_POST['tank_id'] ?? 0);
    $tank_kodu = $connection->real_escape_string($_POST['tank_kodu'] ?? '');
    $tank_ismi = $connection->real_escape_string($_POST['tank_ismi'] ?? '');
    $kapasite = floatval($_POST['kapasite'] ?? 0);
    $not_bilgisi = $connection->real_escape_string($_POST['not_bilgisi'] ?? '');

    if (empty($tank_id) || empty($tank_kodu) || empty($tank_ismi) || $kapasite < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Tank ID, tank kodu, tank ismi ve kapasite alanları zorunludur.']);
        return;
    }

    try {
        $query = "UPDATE tanklar SET tank_kodu = '$tank_kodu', tank_ismi = '$tank_ismi', kapasite = $kapasite, not_bilgisi = '$not_bilgisi' WHERE tank_id = $tank_id";
        $result = $connection->query($query);

        // Eski tank bilgilerini al
        $old_tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_id = $tank_id";
        $old_tank_result = $connection->query($old_tank_query);
        $old_tank = $old_tank_result->fetch_assoc();
        $old_name = $old_tank['tank_ismi'] ?? 'Bilinmeyen Tank';

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$old_name adlı tank $tank_ismi olarak güncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tank güncellenirken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function deleteTank() {
    global $connection;

    $tank_id = (int)($_POST['tank_id'] ?? 0);
    if (empty($tank_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tank ID gerekli.']);
        return;
    }

    try {
        $query = "DELETE FROM tanklar WHERE tank_id = $tank_id";
        $result = $connection->query($query);

        // Silinen tank bilgilerini al
        $deleted_tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_id = $tank_id";
        $deleted_tank_result = $connection->query($deleted_tank_query);
        $deleted_tank = $deleted_tank_result->fetch_assoc();
        $deleted_name = $deleted_tank['tank_ismi'] ?? 'Bilinmeyen Tank';

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_name adlı tank silindi", 'DELETE');
            echo json_encode(['status' => 'success', 'message' => 'Tank başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tank silinirken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getTanksPaginated() {
    global $connection;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = $_GET['search'] ?? '';
    
    // Validate inputs
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 100) $limit = 100;
    
    $offset = ($page - 1) * $limit;
    
    $whereClause = '';
    $search = $connection->real_escape_string($search);
    if (!empty($search)) {
        $whereClause = "WHERE tank_kodu LIKE '%$search%' OR tank_ismi LIKE '%$search%' OR not_bilgisi LIKE '%$search%'";
    }
    
    // Get total count
    $totalQuery = "SELECT COUNT(*) AS total FROM tanklar $whereClause";
    $totalResult = $connection->query($totalQuery);
    $total = 0;
    if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
        $total = (int)$totalRow['total'];
    }
    
    // Get paginated results
    $query = "SELECT tank_id, tank_kodu, tank_ismi, kapasite, not_bilgisi FROM tanklar $whereClause ORDER BY tank_ismi LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);
    
    $tanks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tanks[] = $row;
        }
    }
    
    $totalPages = ceil($total / $limit);
    
    echo json_encode([
        'status' => 'success', 
        'data' => $tanks,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
}
?>