<?php
include 'config.php';

echo "<h1>Müşteri Siparişleri</h1>";

// Query to get recent orders
$orders_query = "SELECT * FROM siparisler ORDER BY tarih DESC LIMIT 20";
$orders_result = $connection->query($orders_query);

if ($orders_result && $orders_result->num_rows > 0) {
    echo "<h2>En Yeni 20 Sipariş</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    $fields = $orders_result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    while ($row = $orders_result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hiç sipariş bulunamadı.</p>";
}

// Query to get order items for the most recent order
$recent_order_query = "SELECT siparis_id FROM siparisler ORDER BY tarih DESC LIMIT 1";
$recent_order_result = $connection->query($recent_order_query);

if ($recent_order_result && $recent_order_result->num_rows > 0) {
    $recent_order = $recent_order_result->fetch_assoc();
    $recent_order_id = $recent_order['siparis_id'];
    
    echo "<h2>En Yeni Siparişin Kalemleri (Sipariş ID: $recent_order_id)</h2>";
    $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = $recent_order_id";
    $items_result = $connection->query($items_query);
    
    if ($items_result && $items_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        $fields = $items_result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        
        while ($row = $items_result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Bu sipariş için hiç ürün kalemi bulunamadı.</p>";
    }
} else {
    echo "<p>Hiç sipariş bulunamadı.</p>";
}

$connection->close();
?>