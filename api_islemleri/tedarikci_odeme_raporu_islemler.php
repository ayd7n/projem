<?php
header('Content-Type: application/json; charset=utf-8');
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
    exit;
}

try {
    // Get exchange rates from settings
    $settings_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $settings_result = $connection->query($settings_query);
    
    if (!$settings_result) {
        echo json_encode(['status' => 'error', 'message' => 'Döviz kurları sorgulanamadı: ' . $connection->error]);
        exit;
    }
    
    // Default values
    $usd_rate = 0;
    $eur_rate = 0;
    
    // Parse settings
    while ($row = $settings_result->fetch_assoc()) {
        if ($row['ayar_anahtar'] === 'dolar_kuru') {
            $usd_rate = floatval($row['ayar_deger']);
        } elseif ($row['ayar_anahtar'] === 'euro_kuru') {
            $eur_rate = floatval($row['ayar_deger']);
        }
    }
    
    // Check if rates are set
    if ($usd_rate == 0 || $eur_rate == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Döviz kurları ayarlanmamış. Lütfen önce döviz kurlarını güncelleyin.']);
        exit;
    }
    
    // Get supplier payment data from framework contracts
    $query = "
        SELECT 
            t.tedarikci_id,
            t.tedarikci_adi,
            cs.para_birimi,
            cs.birim_fiyat,
            cs.toplu_odenen_miktar,
            cs.malzeme_kodu,
            m.malzeme_ismi,
            (cs.birim_fiyat * cs.toplu_odenen_miktar) as toplam_odeme
        FROM cerceve_sozlesmeler cs
        INNER JOIN tedarikciler t ON cs.tedarikci_id = t.tedarikci_id
        LEFT JOIN malzemeler m ON cs.malzeme_kodu = m.malzeme_kodu
        WHERE cs.toplu_odenen_miktar > 0
        ORDER BY t.tedarikci_adi, cs.sozlesme_id
    ";
    
    $result = $connection->query($query);
    
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Sorgu hatası: ' . $connection->error]);
        exit;
    }
    
    $payments = [];
    $supplier_totals = [];
    
    while ($row = $result->fetch_assoc()) {
        $tedarikci_id = $row['tedarikci_id'];
        $tedarikci_adi = $row['tedarikci_adi'];
        $para_birimi = $row['para_birimi'];
        $toplam_odeme = floatval($row['toplam_odeme']);
        
        // Convert to TL based on currency
        $odeme_tl = 0;
        if ($para_birimi === 'TL') {
            $odeme_tl = $toplam_odeme;
        } elseif ($para_birimi === 'USD') {
            $odeme_tl = $toplam_odeme * $usd_rate;
        } elseif ($para_birimi === 'EUR') {
            $odeme_tl = $toplam_odeme * $eur_rate;
        }
        
        // Add to payments array
        $payments[] = [
            'tedarikci_id' => $tedarikci_id,
            'tedarikci_adi' => $tedarikci_adi,
            'malzeme_kodu' => $row['malzeme_kodu'],
            'malzeme_ismi' => $row['malzeme_ismi'],
            'para_birimi' => $para_birimi,
            'birim_fiyat' => floatval($row['birim_fiyat']),
            'odenen_miktar' => floatval($row['toplu_odenen_miktar']),
            'toplam_odeme_orijinal' => $toplam_odeme,
            'toplam_odeme_tl' => $odeme_tl
        ];
        
        // Aggregate by supplier
        if (!isset($supplier_totals[$tedarikci_id])) {
            $supplier_totals[$tedarikci_id] = [
                'tedarikci_id' => $tedarikci_id,
                'tedarikci_adi' => $tedarikci_adi,
                'toplam_tl' => 0,
                'odeme_sayisi' => 0
            ];
        }
        
        $supplier_totals[$tedarikci_id]['toplam_tl'] += $odeme_tl;
        $supplier_totals[$tedarikci_id]['odeme_sayisi']++;
    }
    
    // Sort suppliers by total payment (descending)
    usort($supplier_totals, function($a, $b) {
        return $b['toplam_tl'] <=> $a['toplam_tl'];
    });
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'payments' => $payments,
            'supplier_totals' => array_values($supplier_totals),
            'exchange_rates' => [
                'usd' => $usd_rate,
                'eur' => $eur_rate
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
