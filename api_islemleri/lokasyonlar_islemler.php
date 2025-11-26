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

    // Get parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10; // Default 10 items per page, max 100
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Prepare query with search functionality
    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE depo_ismi LIKE '$search_param' OR raf LIKE '$search_param'";
    }

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM lokasyonlar " . $where_clause;
    $result = $connection->query($count_query);
    $total_locations = $result->fetch_assoc()['total'];

    // Calculate total pages
    $total_pages = $limit > 0 ? ceil($total_locations / $limit) : 0;

    // Get locations for current page
    $query = "SELECT * FROM lokasyonlar " . $where_clause . " ORDER BY depo_ismi, raf LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);

    $locations = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
    }

    $response = [
        'status' => 'success',
        'data' => $locations,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_locations' => $total_locations,
            'limit' => $limit
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
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
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$depo_ismi deposuna $raf rafı eklendi", 'CREATE');
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

    // Eski lokasyon bilgilerini al
    $old_location_query = "SELECT depo_ismi, raf FROM lokasyonlar WHERE lokasyon_id = ?";
    $old_stmt = $connection->prepare($old_location_query);
    $old_stmt->bind_param('i', $lokasyon_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_location = $old_result->fetch_assoc();
    $old_depo = $old_location['depo_ismi'] ?? 'Bilinmeyen Depo';
    $old_raf = $old_location['raf'] ?? 'Bilinmeyen Raf';
    $old_stmt->close();

    $query = "UPDATE lokasyonlar SET depo_ismi = ?, raf = ? WHERE lokasyon_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssi', $depo_ismi, $raf, $lokasyon_id);

    if ($stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_depo deposundaki $old_raf rafı $depo_ismi deposuna ve $raf olarak güncellendi", 'UPDATE');
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

    // Silinen lokasyon bilgilerini al
    $old_location_query = "SELECT depo_ismi, raf FROM lokasyonlar WHERE lokasyon_id = ?";
    $old_stmt = $connection->prepare($old_location_query);
    $old_stmt->bind_param('i', $lokasyon_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_location = $old_result->fetch_assoc();
    $deleted_depo = $old_location['depo_ismi'] ?? 'Bilinmeyen Depo';
    $deleted_raf = $old_location['raf'] ?? 'Bilinmeyen Raf';
    $old_stmt->close();

    $query = "DELETE FROM lokasyonlar WHERE lokasyon_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $lokasyon_id);

    if ($stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_depo deposundaki $deleted_raf rafı silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Lokasyon başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lokasyon silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>
