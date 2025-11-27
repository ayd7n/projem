<?php
include 'config.php';

// 1. Calculate Total Operating Expenses (Last 1 Month)
$expenses_query = "SELECT IFNULL(SUM(tutar), 0) as total_expenses
                   FROM gider_yonetimi
                   WHERE kategori != 'Malzeme Gideri'
                   AND tarih >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

$expenses_result = $connection->query($expenses_query);
$total_expenses = floatval($expenses_result->fetch_assoc()['total_expenses']);

// 2. Calculate Total Produced Quantity (Last 1 Month)
$production_query = "SELECT IFNULL(SUM(tamamlanan_miktar), 0) as total_produced
                     FROM montaj_is_emirleri
                     WHERE gerceklesen_bitis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

$production_result = $connection->query($production_query);
$total_produced = floatval($production_result->fetch_assoc()['total_produced']);

echo "Total Expenses (Last 1 Month): " . number_format($total_expenses, 2) . " TL\n";
echo "Total Produced (Last 1 Month): " . $total_produced . " Adet\n";

if ($total_produced > 0) {
    echo "Unit Operating Cost: " . number_format($total_expenses / $total_produced, 2) . " TL/Adet\n";
} else {
    echo "Unit Operating Cost: Undefined (0 production)\n";
}
?>