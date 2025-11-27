<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_metrics':
        getMetrics();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}

function getMetrics()
{
    global $connection;

    try {
        // 1. Calculate Total Operating Expenses (Last 1 Month)
        // Excluding 'Malzeme Gideri'
        $expenses_query = "SELECT IFNULL(SUM(tutar), 0) as total_expenses
                           FROM gider_yonetimi
                           WHERE kategori != 'Malzeme Gideri'
                           AND tarih >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

        $expenses_result = $connection->query($expenses_query);
        if (!$expenses_result) {
            throw new Exception("Gider sorgusu hatası: " . $connection->error);
        }
        $total_expenses = floatval($expenses_result->fetch_assoc()['total_expenses']);

        // 2. Calculate Total Produced Quantity (Last 1 Month)
        $production_query = "SELECT IFNULL(SUM(tamamlanan_miktar), 0) as total_produced
                             FROM montaj_is_emirleri
                             WHERE gerceklesen_bitis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

        $production_result = $connection->query($production_query);
        if (!$production_result) {
            throw new Exception("Üretim sorgusu hatası: " . $connection->error);
        }
        $total_produced = floatval($production_result->fetch_assoc()['total_produced']);

        // 3. Calculate Unit Operating Cost
        $unit_operating_cost = 0;
        if ($total_produced > 0) {
            $unit_operating_cost = $total_expenses / $total_produced;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_expenses' => $total_expenses,
                'total_produced' => $total_produced,
                'unit_operating_cost' => $unit_operating_cost,
                'period' => 'Son 1 Ay'
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>