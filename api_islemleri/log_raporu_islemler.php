<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_logs':
        getLogs();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}

function getLogs() {
    global $connection;

    // Parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $startDate = $_GET['startDate'] ?? '';
    $endDate = $_GET['endDate'] ?? '';
    $islemTuru = $_GET['islemTuru'] ?? '';
    $search = $_GET['search'] ?? '';

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "SELECT * FROM log_tablosu WHERE 1=1";
    $params = [];
    $types = '';

    // Add date filters
    if (!empty($startDate)) {
        $query .= " AND tarih >= ?";
        $params[] = $startDate . ' 00:00:00';
        $types .= 's';
    }

    if (!empty($endDate)) {
        $query .= " AND tarih <= ?";
        $params[] = $endDate . ' 23:59:59';
        $types .= 's';
    }

    // Add operation type filter
    if (!empty($islemTuru)) {
        $query .= " AND islem_turu = ?";
        $params[] = $islemTuru;
        $types .= 's';
    }

    // Add search filter
    if (!empty($search)) {
        $query .= " AND (kullanici_adi LIKE ? OR log_metni LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    // Add ordering and pagination
    $query .= " ORDER BY tarih DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    // Prepare and execute query
    $stmt = $connection->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM log_tablosu WHERE 1=1";
    $countParams = [];
    $countTypes = '';

    // Add same filters for count query
    if (!empty($startDate)) {
        $countQuery .= " AND tarih >= ?";
        $countParams[] = $startDate . ' 00:00:00';
        $countTypes .= 's';
    }

    if (!empty($endDate)) {
        $countQuery .= " AND tarih <= ?";
        $countParams[] = $endDate . ' 23:59:59';
        $countTypes .= 's';
    }

    if (!empty($islemTuru)) {
        $countQuery .= " AND islem_turu = ?";
        $countParams[] = $islemTuru;
        $countTypes .= 's';
    }

    if (!empty($search)) {
        $countQuery .= " AND (kullanici_adi LIKE ? OR log_metni LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countTypes .= 'ss';
    }

    $countStmt = $connection->prepare($countQuery);
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];

    $totalPages = ceil($totalCount / $limit);

    echo json_encode([
        'status' => 'success',
        'data' => $logs,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalCount,
            'total_pages' => $totalPages
        ]
    ]);
}
?>