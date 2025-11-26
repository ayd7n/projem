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
    case 'get_work_centers_paginated':
        getWorkCentersPaginated();
        break;
    case 'get_work_center':
        getWorkCenter();
        break;
    case 'get_total_work_centers':
        getTotalWorkCenters();
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

    $id = (int)($_GET['id'] ?? 0);
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM is_merkezleri WHERE is_merkezi_id = $id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $work_center = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $work_center]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi bulunamadı.']);
    }
}

function getTotalWorkCenters() {
    global $connection;

    $query = "SELECT COUNT(*) AS total FROM is_merkezleri";
    $result = $connection->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => (int)$row['total']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam iş merkezi sayısı alınırken hata oluştu.']);
    }
}

function addWorkCenter() {
    global $connection;

    $isim = $connection->real_escape_string($_POST['isim'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');

    if (empty($isim)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi adı zorunludur.']);
        return;
    }

    $query = "INSERT INTO is_merkezleri (isim, aciklama) VALUES ('$isim', '$aciklama')";

    if ($connection->query($query)) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$isim iş merkezi eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi oluşturulurken hata oluştu: ' . $connection->error]);
    }
}

function updateWorkCenter() {
    global $connection;

    $is_merkezi_id = (int)($_POST['is_merkezi_id'] ?? 0);
    $isim = $connection->real_escape_string($_POST['isim'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');

    if (empty($is_merkezi_id) || empty($isim)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID ve isim alanları zorunludur.']);
        return;
    }

    // Eski iş merkezi bilgilerini al
    $old_center_query = "SELECT isim FROM is_merkezleri WHERE is_merkezi_id = $is_merkezi_id";
    $old_center_result = $connection->query($old_center_query);
    $old_center = $old_center_result->fetch_assoc();
    $old_name = $old_center['isim'] ?? 'Bilinmeyen İş Merkezi';

    $query = "UPDATE is_merkezleri SET isim = '$isim', aciklama = '$aciklama' WHERE is_merkezi_id = $is_merkezi_id";

    if ($connection->query($query)) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_name iş merkezi $isim olarak güncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi güncellenirken hata oluştu: ' . $connection->error]);
    }
}

function deleteWorkCenter() {
    global $connection;

    $is_merkezi_id = (int)($_POST['is_merkezi_id'] ?? 0);
    if (empty($is_merkezi_id)) {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi ID gerekli.']);
        return;
    }

    // Silinen iş merkezi bilgilerini al
    $old_center_query = "SELECT isim FROM is_merkezleri WHERE is_merkezi_id = $is_merkezi_id";
    $old_center_result = $connection->query($old_center_query);
    $old_center = $old_center_result->fetch_assoc();
    $deleted_name = $old_center['isim'] ?? 'Bilinmeyen İş Merkezi';

    $query = "DELETE FROM is_merkezleri WHERE is_merkezi_id = $is_merkezi_id";

    if ($connection->query($query)) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_name iş merkezi silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'İş merkezi başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İş merkezi silinirken hata oluştu: ' . $connection->error]);
    }
}

function getWorkCentersPaginated() {
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
        $whereClause = "WHERE isim LIKE '%$search%' OR aciklama LIKE '%$search%'";
    }
    
    // Get total count
    $totalQuery = "SELECT COUNT(*) AS total FROM is_merkezleri $whereClause";
    $totalResult = $connection->query($totalQuery);
    $total = 0;
    if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
        $total = (int)$totalRow['total'];
    }
    
    // Get paginated results
    $query = "SELECT * FROM is_merkezleri $whereClause ORDER BY isim LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);
    
    $work_centers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $work_centers[] = $row;
        }
    }
    
    $totalPages = ceil($total / $limit);
    
    echo json_encode([
        'status' => 'success', 
        'data' => $work_centers,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
}
?>
