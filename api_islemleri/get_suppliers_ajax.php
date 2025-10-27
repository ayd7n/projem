<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Oturumunuz süresi dolmuş. Lütfen tekrar giriş yapın.']);
    exit;
}

// Only staff can access this
if ($_SESSION['taraf'] !== 'personel') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

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
    $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param'";
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM tedarikciler " . $where_clause;
$result = $connection->query($count_query);
$total_suppliers = $result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = $limit > 0 ? ceil($total_suppliers / $limit) : 0;

// Get suppliers for current page
$query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi LIMIT $limit OFFSET $offset";
$result = $connection->query($query);

$suppliers = [];
while ($row = $result->fetch_assoc()) {
    $suppliers[] = $row;
}

$response = [
    'status' => 'success',
    'data' => $suppliers,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_suppliers' => $total_suppliers,
        'limit' => $limit
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>