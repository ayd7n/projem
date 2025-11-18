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

// Handle different actions based on the action parameter
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = 'get_analysis_data';
}

switch ($action) {
    case 'get_analysis_data':
        getAnalysisData();
        break;
    case 'count_esans_products':
        countEsansProducts();
        break;
    default:
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Geçersiz işlem']);
        break;
}

function getAnalysisData() {
    global $connection;
    
    // SQL query to find products from urunler table that are MISSING either 'esans' components OR other component types in urun_agaci
    // This identifies problematic products that don't have both required component types
    $query = "
        SELECT
            urun_kodu,
            urun_ismi,
            'Eksik' as bilesenin_malzeme_turu,
            'N/A' as bilesen_kodu,
            aciklama as bilesen_ismi,
            0.00 as bilesen_miktari,
            'urun' as agac_turu
        FROM (
            -- Products that exist in urunler table and have other components but are MISSING 'esans' components
            SELECT
                u.urun_kodu,
                u.urun_ismi,
                'Esans Bileşeni Yok' as aciklama,
                1 as siralama
            FROM urunler u
            WHERE u.urun_kodu IN (
                -- Products that have other components (non-esans) in urun_agaci
                SELECT DISTINCT urun_kodu
                FROM urun_agaci
                WHERE bilesenin_malzeme_turu != 'esans'
            )
            AND u.urun_kodu NOT IN (
                -- But exclude products that already have 'esans' components
                SELECT DISTINCT urun_kodu
                FROM urun_agaci
                WHERE bilesenin_malzeme_turu = 'esans'
            )

            UNION ALL

            -- Products that exist in urunler table and have 'esans' components but are MISSING other component types
            SELECT
                u.urun_kodu,
                u.urun_ismi,
                'Diğer Bileşen Türleri Yok' as aciklama,
                2 as siralama
            FROM urunler u
            WHERE u.urun_kodu IN (
                -- Products that have 'esans' components in urun_agaci
                SELECT DISTINCT urun_kodu
                FROM urun_agaci
                WHERE bilesenin_malzeme_turu = 'esans'
            )
            AND u.urun_kodu NOT IN (
                -- But exclude products that have other component types
                SELECT DISTINCT urun_kodu
                FROM urun_agaci
                WHERE bilesenin_malzeme_turu != 'esans'
            )

            UNION ALL

            -- Products that exist in urunler table but have NO components at all in urun_agaci
            SELECT
                u.urun_kodu,
                u.urun_ismi,
                'Bileşen Yok' as aciklama,
                3 as siralama
            FROM urunler u
            WHERE u.urun_kodu NOT IN (
                -- Exclude products that have any components in urun_agaci
                SELECT DISTINCT urun_kodu
                FROM urun_agaci
            )
        ) as eksik_urunler
        ORDER BY siralama, urun_ismi
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
        // Create a unique ID for each combination of product and component
        $row['urun_agaci_id'] = $row['urun_kodu'] . '_' . ($row['bilesen_kodu'] ?: '0');
        $data[] = $row;
    }

    // Make sure we're sending JSON content
    if (headers_sent()) {
        // If headers were already sent (due to error), we shouldn't send content type
        echo json_encode($data);
    } else {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

function countEsansProducts() {
    global $connection;

    $query = "SELECT COUNT(*) as total FROM esanslar";
    $result = $connection->query($query);

    if (!$result) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sorgu hatası: ' . $connection->error]);
        exit;
    }

    $row = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total' => (int)$row['total']]);
}
?>