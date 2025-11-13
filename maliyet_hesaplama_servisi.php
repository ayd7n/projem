<?php
// WARNING: This file uses plain SQL queries with variable interpolation, which can be vulnerable to SQL injection.
// This was implemented at the specific request of the user after a security warning was provided and acknowledged.
// It is highly recommended to use prepared statements instead.

include 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['malzeme_kodu'])) {
    echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu belirtilmedi.']);
    exit;
}

$malzeme_kodu_raw = $_GET['malzeme_kodu'];
// Basic sanitization
if (!is_numeric($malzeme_kodu_raw)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme kodu.']);
    exit;
}
$malzeme_kodu = (int)$malzeme_kodu_raw;


// Get current stock quantity and material name
$stock_query = "SELECT stok_miktari, malzeme_ismi FROM malzemeler WHERE malzeme_kodu = $malzeme_kodu";
$stock_result = $connection->query($stock_query);

if (!$stock_result) {
    echo json_encode(['status' => 'error', 'message' => 'Stok sorgusu başarısız: ' . $connection->error]);
    exit;
}

if ($stock_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Malzeme bulunamadı.']);
    exit;
}

$material_data = $stock_result->fetch_assoc();
$current_stock = (float)$material_data['stok_miktari'];
$malzeme_ismi = $material_data['malzeme_ismi'];


if ($current_stock <= 0) {
    // If stock is zero or negative, cost is zero.
    $weighted_average_cost = 0;
    $malzeme_ismi_escaped = $connection->real_escape_string($malzeme_ismi);

    // Update or insert into malzeme_maliyetleri
    $upsert_query = "INSERT INTO malzeme_maliyetleri (malzeme_kodu, malzeme_ismi, agirlikli_ortalama_maliyet) VALUES ($malzeme_kodu, '$malzeme_ismi_escaped', $weighted_average_cost) ON DUPLICATE KEY UPDATE malzeme_ismi = VALUES(malzeme_ismi), agirlikli_ortalama_maliyet = VALUES(agirlikli_ortalama_maliyet)";
    
    if (!$connection->query($upsert_query)) {
        echo json_encode(['status' => 'error', 'message' => 'Maliyet (stok sıfır) güncellenirken hata: ' . $connection->error]);
        exit;
    }

    echo json_encode(['status' => 'success', 'message' => 'Stok sıfır veya negatif olduğu için maliyet 0 olarak güncellendi.', 'malzeme_kodu' => $malzeme_kodu, 'agirlikli_ortalama_maliyet' => $weighted_average_cost]);
    exit;
}

// Get goods receipts from stok_hareketleri_sozlesmeler
$receipts_query = "SELECT kullanilan_miktar, birim_fiyat FROM stok_hareketleri_sozlesmeler WHERE malzeme_kodu = $malzeme_kodu ORDER BY tarih DESC";
$receipts_result = $connection->query($receipts_query);

if (!$receipts_result) {
    echo json_encode(['status' => 'error', 'message' => 'Mal kabul hareketleri sorgusu başarısız: ' . $connection->error]);
    exit;
}

$accumulated_quantity = 0;
$accumulated_cost = 0;
$remaining_stock_to_cover = $current_stock;

while ($row = $receipts_result->fetch_assoc()) {
    $receipt_quantity = (float)$row['kullanilan_miktar'];
    $receipt_unit_price = (float)$row['birim_fiyat'];

    if ($remaining_stock_to_cover <= 0) {
        break; // Covered all current stock
    }

    $quantity_to_use = 0;
    if ($receipt_quantity >= $remaining_stock_to_cover) {
        // This receipt covers the rest of the stock
        $quantity_to_use = $remaining_stock_to_cover;
        $remaining_stock_to_cover = 0;
    } else {
        // This receipt is fully used
        $quantity_to_use = $receipt_quantity;
        $remaining_stock_to_cover -= $receipt_quantity;
    }
    
    $accumulated_cost += ($quantity_to_use * $receipt_unit_price);
    $accumulated_quantity += $quantity_to_use;
}

if ($accumulated_quantity > 0) {
    $weighted_average_cost = $accumulated_cost / $accumulated_quantity;
} else {
    // This can happen if there is stock but no corresponding goods receipt was found.
    // In this case, we cannot determine a cost. We can set it to 0 or leave it as is.
    $weighted_average_cost = 0; 
}

// Update or insert into malzeme_maliyetleri
$malzeme_ismi_escaped = $connection->real_escape_string($malzeme_ismi);
$upsert_query = "INSERT INTO malzeme_maliyetleri (malzeme_kodu, malzeme_ismi, agirlikli_ortalama_maliyet) VALUES ($malzeme_kodu, '$malzeme_ismi_escaped', $weighted_average_cost) ON DUPLICATE KEY UPDATE malzeme_ismi = VALUES(malzeme_ismi), agirlikli_ortalama_maliyet = VALUES(agirlikli_ortalama_maliyet)";

if (!$connection->query($upsert_query)) {
    echo json_encode(['status' => 'error', 'message' => 'Maliyet güncellenirken hata: ' . $connection->error]);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Maliyet başarıyla hesaplandı ve güncellendi.', 'malzeme_kodu' => $malzeme_kodu, 'agirlikli_ortalama_maliyet' => $weighted_average_cost]);

?>
