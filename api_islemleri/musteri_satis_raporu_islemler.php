<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access
if ($_SESSION['taraf'] !== 'personel') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_report_data') {
    try {
        // Main query to get sales data by customer and product
        $query = "
            SELECT 
                s.musteri_id,
                s.musteri_adi,
                sk.urun_kodu,
                sk.urun_ismi,
                COUNT(DISTINCT s.siparis_id) as siparis_sayisi,
                SUM(sk.adet) as toplam_adet,
                SUM(sk.toplam_tutar) as toplam_satis,
                COALESCE(vum.teorik_maliyet, 0) as teorik_maliyet
            FROM siparisler s
            JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
            LEFT JOIN urunler u ON sk.urun_kodu = u.urun_kodu
            LEFT JOIN v_urun_maliyetleri vum ON sk.urun_kodu = vum.urun_kodu
            WHERE s.durum = 'tamamlandi'
            GROUP BY s.musteri_id, sk.urun_kodu
            ORDER BY s.musteri_adi, sk.urun_ismi
        ";

        $result = $connection->query($query);
        
        $data = [];
        $customer_totals = [];
        $grand_total_profit = 0;

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $musteri_id = $row['musteri_id'];
                $musteri_adi = $row['musteri_adi'];
                
                // Calculate profit
                // Profit = Total Sales - (Total Quantity * Unit Cost)
                // Note: This uses current theoretical cost. If historical cost is needed, it should be stored in order items.
                // Assuming current cost for now as per available data.
                $cost = floatval($row['teorik_maliyet']);
                $quantity = floatval($row['toplam_adet']);
                $sales = floatval($row['toplam_satis']);
                
                $total_cost = $cost * $quantity;
                $profit = $sales - $total_cost;

                // Prepare row data
                $item = [
                    'musteri_id' => $musteri_id,
                    'musteri_adi' => $musteri_adi,
                    'urun_kodu' => $row['urun_kodu'],
                    'urun_ismi' => $row['urun_ismi'],
                    'siparis_sayisi' => intval($row['siparis_sayisi']),
                    'toplam_adet' => $quantity,
                    'toplam_satis' => $sales,
                    'birim_maliyet' => $cost,
                    'toplam_maliyet' => $total_cost,
                    'kar' => $profit
                ];
                
                $data[] = $item;

                // Aggregate totals per customer
                if (!isset($customer_totals[$musteri_id])) {
                    $customer_totals[$musteri_id] = [
                        'musteri_adi' => $musteri_adi,
                        'toplam_siparis_sayisi' => 0, // This needs to be calculated carefully as sum of product orders != total customer orders
                        'toplam_urun_adedi' => 0,
                        'toplam_satis' => 0,
                        'toplam_kar' => 0,
                        'urun_cesidi' => 0
                    ];
                }
                
                $customer_totals[$musteri_id]['toplam_urun_adedi'] += $quantity;
                $customer_totals[$musteri_id]['toplam_satis'] += $sales;
                $customer_totals[$musteri_id]['toplam_kar'] += $profit;
                $customer_totals[$musteri_id]['urun_cesidi'] += 1;
                
                $grand_total_profit += $profit;
            }
            
            // Fix total orders per customer (since we grouped by product, we can't just sum siparis_sayisi)
            // We need a separate query for total completed orders per customer
            $orders_query = "
                SELECT musteri_id, COUNT(DISTINCT siparis_id) as total_orders 
                FROM siparisler 
                WHERE durum = 'tamamlandi' 
                GROUP BY musteri_id
            ";
            $orders_result = $connection->query($orders_query);
            while($o_row = $orders_result->fetch_assoc()) {
                if(isset($customer_totals[$o_row['musteri_id']])) {
                    $customer_totals[$o_row['musteri_id']]['toplam_siparis_sayisi'] = intval($o_row['total_orders']);
                }
            }

        }

        echo json_encode([
            'status' => 'success', 
            'data' => $data, 
            'customer_totals' => array_values($customer_totals),
            'grand_total_profit' => $grand_total_profit
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}
?>
