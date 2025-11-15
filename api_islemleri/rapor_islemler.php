<?php
include '../config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Geçersiz işlem.'];

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_monthly_sales') {
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

        $sql = "SELECT 
                    MONTH(s.tarih) as ay,
                    SUM(sk.toplam_tutar) as toplam_satis
                FROM siparisler s
                JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
                WHERE YEAR(s.tarih) = ? AND s.durum = 'onaylandi'
                GROUP BY MONTH(s.tarih)
                ORDER BY ay ASC";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $sales_data = array_fill(1, 12, 0);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $sales_data[(int)$row['ay']] = (float)$row['toplam_satis'];
            }
            $response = [
                'status' => 'success',
                'labels' => ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                'data' => array_values($sales_data)
            ];
        } else {
            $response['message'] = 'Satış verileri alınırken bir hata oluştu: ' . $connection->error;
        }
        $stmt->close();
    }
}

echo json_encode($response);
?>
