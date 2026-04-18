<?php
include '../config.php';

header('Content-Type: application/json');

function getReportRates($connection)
{
    $rates = ['USD' => 0.0, 'EUR' => 0.0];
    $rateQuery = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    if ($rateQuery) {
        while ($row = $rateQuery->fetch_assoc()) {
            if (($row['ayar_anahtar'] ?? '') === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            } elseif (($row['ayar_anahtar'] ?? '') === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            }
        }
    }
    return $rates;
}

$response = ['status' => 'error', 'message' => 'Gecersiz islem.'];

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_monthly_sales') {
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $rates = getReportRates($connection);
        $usdRate = (float) $rates['USD'];
        $eurRate = (float) $rates['EUR'];
        if ($usdRate <= 0 || $eurRate <= 0) {
            $response['message'] = 'Doviz kurlari tanimli degil veya gecersiz.';
            echo json_encode($response);
            exit;
        }

        $sql = "SELECT
                    MONTH(s.tarih) as ay,
                    SUM(
                        COALESCE(sk.toplam_tutar, sk.adet * sk.birim_fiyat) *
                        CASE UPPER(COALESCE(NULLIF(sk.para_birimi, ''), NULLIF(s.para_birimi, ''), 'TL'))
                            WHEN 'USD' THEN ?
                            WHEN 'EUR' THEN ?
                            WHEN 'TRY' THEN 1
                            ELSE 1
                        END
                    ) as toplam_satis
                FROM siparisler s
                JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
                WHERE YEAR(s.tarih) = ?
                  AND s.durum IN ('onaylandi', 'tamamlandi')
                GROUP BY MONTH(s.tarih)
                ORDER BY ay ASC";

        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            $response['message'] = 'Satis sorgusu hazirlanamadi: ' . $connection->error;
        } else {
            $stmt->bind_param('ddi', $usdRate, $eurRate, $year);
            $stmt->execute();
            $result = $stmt->get_result();

            $salesData = array_fill(1, 12, 0.0);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $month = (int) ($row['ay'] ?? 0);
                    if ($month >= 1 && $month <= 12) {
                        $salesData[$month] = (float) ($row['toplam_satis'] ?? 0);
                    }
                }
                $response = [
                    'status' => 'success',
                    'labels' => ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'],
                    'data' => array_values($salesData),
                ];
            } else {
                $response['message'] = 'Satis verileri alinirken bir hata olustu: ' . $connection->error;
            }
            $stmt->close();
        }
    }
}

echo json_encode($response);
?>
