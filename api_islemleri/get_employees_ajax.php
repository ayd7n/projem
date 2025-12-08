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
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10; // Default 10 items per page, max 100
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare query with search functionality (excluding admin users)
$where_clause = "WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')";
if (!empty($search)) {
    $search_escaped = $connection->real_escape_string($search);
    $search_param = '%' . $search_escaped . '%';
    $where_clause .= " AND (ad_soyad LIKE '$search_param' OR e_posta LIKE '$search_param' OR departman LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param')";
}

// Get total count for current filter
$count_query = "SELECT COUNT(*) as total FROM personeller " . $where_clause;
$result = $connection->query($count_query);
$total_employees = $result->fetch_assoc()['total'];

// Get overall total count (excluding admins, no search)
$overall_count_query = "SELECT COUNT(*) as total FROM personeller WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')";
$overall_result = $connection->query($overall_count_query);
$overall_total_employees = $overall_result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = $limit > 0 ? ceil($total_employees / $limit) : 0;

// Get employees for current page (excluding password for security)
$query = "SELECT personel_id, ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, telefon_2, adres, notlar, bordrolu_calisan_mi, aylik_brut_ucret FROM personeller " . $where_clause . " ORDER BY ad_soyad LIMIT $limit OFFSET $offset";
$result = $connection->query($query);

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

$response = [
    'status' => 'success',
    'data' => $employees,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_employees' => $total_employees,
        'overall_total_employees' => $overall_total_employees,
        'limit' => $limit
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>