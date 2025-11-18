<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bu işlem için yetkiniz yok']);
    exit;
}

// Page-level permission check
if (!yetkisi_var('page:view:raporlar')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bu sayfayı görüntüleme yetkiniz yok']);
    exit;
}

// Get essences from esanslar table that don't have their own product tree
$query = "
    SELECT
        e.esans_kodu,
        e.esans_ismi,
        'Ağacı Yok' as bilesen_ismi
    FROM esanslar e
    WHERE NOT EXISTS (
        -- Check if essence exists as a product tree (appears as urun_ismi in urun_agaci)
        SELECT 1
        FROM urun_agaci u
        WHERE u.urun_ismi = e.esans_ismi
    )
";

$result = $connection->query($query);

if (!$result) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Sorgu hatası: ' . $connection->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Map esans_kodu to urun_kodu for consistency
    $data[] = [
        'urun_agaci_id' => $row['esans_kodu'] . '_missing',
        'urun_kodu' => $row['esans_kodu'],
        'urun_ismi' => $row['esans_ismi'],
        'bilesen_ismi' => $row['bilesen_ismi']
    ];
}

// Make sure we're sending JSON content
if (headers_sent()) {
    // If headers were already sent (due to error), we shouldn't send content type
    echo json_encode($data);
} else {
    header('Content-Type: application/json');
    echo json_encode($data);
}
?>