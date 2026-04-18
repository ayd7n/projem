<?php
require_once __DIR__ . '/../config.php';

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

function normalizeSalesCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === '' || $currency === 'TL') {
        return 'TRY';
    }

    return in_array($currency, ['TRY', 'USD', 'EUR']) ? $currency : 'TRY';
}

function getSalesRates($connection)
{
    $rates = ['TRY' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $rateQuery = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $result = $connection->query($rateQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) $row['ayar_deger']);
            } elseif ($row['ayar_anahtar'] === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) $row['ayar_deger']);
            }
        }
    }

    return $rates;
}

function convertSalesAmount($amount, $from, $to, $rates)
{
    $amount = (float) $amount;
    $from = normalizeSalesCurrency($from);
    $to = normalizeSalesCurrency($to);

    if ($from === $to) {
        return $amount;
    }

    $fromRate = (float) ($rates[$from] ?? 0);
    $toRate = (float) ($rates[$to] ?? 0);
    if ($fromRate <= 0 || $toRate <= 0) {
        throw new Exception('Kur bilgisi eksik veya gecersiz.');
    }

    $tryAmount = $amount * $fromRate;
    return $tryAmount / $toRate;
}

function tableHasColumn($connection, $table, $column)
{
    $tableEsc = $connection->real_escape_string($table);
    $columnEsc = $connection->real_escape_string($column);
    $result = $connection->query("SHOW COLUMNS FROM `$tableEsc` LIKE '$columnEsc'");
    return $result && $result->num_rows > 0;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_report_data') {
    try {
        $rates = getSalesRates($connection);
        $hasHistoricalCost = tableHasColumn($connection, 'siparis_kalemleri', 'birim_maliyet_try');

        $costMap = [];
        $costResult = $connection->query("SELECT urun_kodu, COALESCE(teorik_maliyet, 0) as teorik_maliyet FROM v_urun_maliyetleri");
        if ($costResult) {
            while ($costRow = $costResult->fetch_assoc()) {
                $costMap[(string) $costRow['urun_kodu']] = (float) ($costRow['teorik_maliyet'] ?? 0);
            }
        }

        $historicalCostSelect = $hasHistoricalCost ? ", sk.birim_maliyet_try" : "";
        $query = "
            SELECT 
                s.musteri_id,
                s.musteri_adi,
                s.siparis_id,
                s.para_birimi AS siparis_para_birimi,
                sk.urun_kodu,
                sk.urun_ismi,
                sk.adet,
                COALESCE(sk.toplam_tutar, sk.birim_fiyat * sk.adet) AS satir_satis_tutari,
                sk.para_birimi AS satir_para_birimi
                $historicalCostSelect
            FROM siparisler s
            JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
            WHERE s.durum = 'tamamlandi'
            ORDER BY s.musteri_id, sk.urun_kodu, s.siparis_id
        ";

        $result = $connection->query($query);
        if (!$result) {
            throw new Exception('Rapor verisi okunamadı: ' . $connection->error);
        }

        $dataMap = [];
        $customerTotals = [];
        $customerOrderSets = [];
        $grandTotalProfit = 0.0;

        while ($row = $result->fetch_assoc()) {
            $musteriId = (int) ($row['musteri_id'] ?? 0);
            $musteriAdi = (string) ($row['musteri_adi'] ?? '');
            $siparisId = (int) ($row['siparis_id'] ?? 0);
            $urunKodu = (string) ($row['urun_kodu'] ?? '');
            $urunIsmi = (string) ($row['urun_ismi'] ?? '');
            $adet = (float) ($row['adet'] ?? 0);

            $lineCurrency = normalizeSalesCurrency($row['satir_para_birimi'] ?? ($row['siparis_para_birimi'] ?? 'TRY'));
            $lineSalesRaw = (float) ($row['satir_satis_tutari'] ?? 0);
            $lineSalesTry = convertSalesAmount($lineSalesRaw, $lineCurrency, 'TRY', $rates);

            $historicalUnitCostTry = $hasHistoricalCost ? (float) ($row['birim_maliyet_try'] ?? 0) : 0.0;
            $fallbackUnitCostTry = (float) ($costMap[$urunKodu] ?? 0.0);
            $unitCostTry = $historicalUnitCostTry > 0 ? $historicalUnitCostTry : $fallbackUnitCostTry;
            $lineCostTry = $unitCostTry * $adet;

            $mapKey = $musteriId . '|' . $urunKodu;
            if (!isset($dataMap[$mapKey])) {
                $dataMap[$mapKey] = [
                    'musteri_id' => $musteriId,
                    'musteri_adi' => $musteriAdi,
                    'urun_kodu' => $urunKodu,
                    'urun_ismi' => $urunIsmi,
                    'toplam_adet' => 0.0,
                    'toplam_satis' => 0.0,
                    'toplam_maliyet' => 0.0,
                    '_order_ids' => []
                ];
            }

            $dataMap[$mapKey]['toplam_adet'] += $adet;
            $dataMap[$mapKey]['toplam_satis'] += $lineSalesTry;
            $dataMap[$mapKey]['toplam_maliyet'] += $lineCostTry;
            if ($siparisId > 0) {
                $dataMap[$mapKey]['_order_ids'][$siparisId] = true;
            }

            if (!isset($customerTotals[$musteriId])) {
                $customerTotals[$musteriId] = [
                    'musteri_adi' => $musteriAdi,
                    'toplam_siparis_sayisi' => 0,
                    'toplam_urun_adedi' => 0.0,
                    'toplam_satis' => 0.0,
                    'toplam_kar' => 0.0,
                    'urun_cesidi' => 0
                ];
            }
            if (!isset($customerOrderSets[$musteriId])) {
                $customerOrderSets[$musteriId] = [];
            }
            if ($siparisId > 0) {
                $customerOrderSets[$musteriId][$siparisId] = true;
            }
        }

        $data = [];
        foreach ($dataMap as $item) {
            $quantity = (float) $item['toplam_adet'];
            $sales = (float) $item['toplam_satis'];
            $totalCost = (float) $item['toplam_maliyet'];
            $profit = $sales - $totalCost;
            $orderCount = count($item['_order_ids']);
            $avgUnitCost = $quantity > 0 ? ($totalCost / $quantity) : 0.0;

            $data[] = [
                'musteri_id' => $item['musteri_id'],
                'musteri_adi' => $item['musteri_adi'],
                'urun_kodu' => $item['urun_kodu'],
                'urun_ismi' => $item['urun_ismi'],
                'siparis_sayisi' => $orderCount,
                'toplam_adet' => $quantity,
                'toplam_satis' => $sales,
                'birim_maliyet' => $avgUnitCost,
                'toplam_maliyet' => $totalCost,
                'kar' => $profit
            ];

            $musteriId = (int) $item['musteri_id'];
            $customerTotals[$musteriId]['toplam_urun_adedi'] += $quantity;
            $customerTotals[$musteriId]['toplam_satis'] += $sales;
            $customerTotals[$musteriId]['toplam_kar'] += $profit;
            $customerTotals[$musteriId]['urun_cesidi'] += 1;

            $grandTotalProfit += $profit;
        }

        foreach ($customerTotals as $musteriId => &$totals) {
            $totals['toplam_siparis_sayisi'] = isset($customerOrderSets[$musteriId])
                ? count($customerOrderSets[$musteriId])
                : 0;
        }
        unset($totals);

        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'customer_totals' => array_values($customerTotals),
            'grand_total_profit' => $grandTotalProfit,
            'cost_basis' => $hasHistoricalCost ? 'order_item_snapshot_then_fallback_current' : 'current_theoretical'
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}
?>
