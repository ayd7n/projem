<?php
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Oturumunuz sona erdi. Lutfen tekrar giris yapin.']);
    exit;
}

if ($_SESSION['taraf'] !== 'personel') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Bu islem icin yetkiniz yok.']);
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

$whereClause = '';
if ($search !== '') {
    $searchEscaped = $connection->real_escape_string($search);
    $like = "'%" . $searchEscaped . "%'";
    $whereClause = "WHERE kategori LIKE $like
                    OR aciklama LIKE $like
                    OR fatura_no LIKE $like
                    OR odeme_tipi LIKE $like
                    OR kaydeden_personel_ismi LIKE $like";
}

$countQuery = "SELECT COUNT(*) AS total FROM gider_yonetimi $whereClause";
$countResult = $connection->query($countQuery);
$totalRecords = 0;
if ($countResult) {
    $totalRecords = (int)($countResult->fetch_assoc()['total'] ?? 0);
}

$totalPages = $limit > 0 ? (int)ceil($totalRecords / $limit) : 0;

$dataQuery = "SELECT gider_id, tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi
              FROM gider_yonetimi
              $whereClause
              ORDER BY tarih DESC, gider_id DESC
              LIMIT $limit OFFSET $offset";

$dataResult = $connection->query($dataQuery);
$expenses = [];
if ($dataResult) {
    while ($row = $dataResult->fetch_assoc()) {
        $row['tutar'] = (float)$row['tutar'];
        $expenses[] = $row;
    }
}

$response = [
    'status' => 'success',
    'data' => $expenses,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords,
        'limit' => $limit
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>

