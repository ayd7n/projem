<?php
// Basit test - sadece veritabanı bağlantısı
include 'config.php';
header('Content-Type: application/json');

try {
    $result = $connection->query("SELECT COUNT(*) as total FROM urunler");
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'total_products' => $row['total']]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
