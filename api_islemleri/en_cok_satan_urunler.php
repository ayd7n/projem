<?php
header('Content-Type: application/json; charset=utf-8');
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access this API
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

try {
    // Query to get best-selling products from completed orders
    $query = "
        SELECT 
            sk.urun_kodu,
            sk.urun_ismi,
            sk.birim,
            SUM(sk.adet) as toplam_satis,
            COUNT(DISTINCT s.siparis_id) as siparis_sayisi
        FROM siparis_kalemleri sk
        INNER JOIN siparisler s ON sk.siparis_id = s.siparis_id
        WHERE s.durum = 'tamamlandi'
        GROUP BY sk.urun_kodu, sk.urun_ismi, sk.birim
        ORDER BY toplam_satis DESC
    ";
    
    $result = $connection->query($query);
    
    if (!$result) {
        throw new Exception('Veritabanı sorgusu başarısız: ' . $connection->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'urun_kodu' => $row['urun_kodu'],
            'urun_ismi' => $row['urun_ismi'],
            'birim' => $row['birim'],
            'toplam_satis' => floatval($row['toplam_satis']),
            'siparis_sayisi' => intval($row['siparis_sayisi'])
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $products,
        'total_count' => count($products)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Bir hata oluştu: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
