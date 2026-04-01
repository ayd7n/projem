<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

function normalize_currency_code($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TL' || $currency === 'TRY') {
        return 'TRY';
    }
    if ($currency === 'USD' || $currency === 'EUR') {
        return $currency;
    }
    return 'TRY';
}

function get_exchange_rates($connection)
{
    static $cached_rates = null;
    if ($cached_rates !== null) {
        return $cached_rates;
    }

    $rates = ['TRY' => 1.0, 'USD' => 1.0, 'EUR' => 1.0];
    $query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $value = (float) $row['ayar_deger'];
            if ($value <= 0) {
                continue;
            }
            if ($row['ayar_anahtar'] === 'dolar_kuru') {
                $rates['USD'] = $value;
            } elseif ($row['ayar_anahtar'] === 'euro_kuru') {
                $rates['EUR'] = $value;
            }
        }
    }

    $cached_rates = $rates;
    return $rates;
}

function convert_currency_amount($amount, $from_currency, $to_currency, $rates)
{
    $from_currency = normalize_currency_code($from_currency);
    $to_currency = normalize_currency_code($to_currency);
    $amount = (float) $amount;

    if ($from_currency === $to_currency) {
        return $amount;
    }

    $from_rate = isset($rates[$from_currency]) ? (float) $rates[$from_currency] : 1.0;
    $to_rate = isset($rates[$to_currency]) ? (float) $rates[$to_currency] : 1.0;
    if ($from_rate <= 0 || $to_rate <= 0) {
        return $amount;
    }

    $amount_try = ($from_currency === 'TRY') ? $amount : ($amount * $from_rate);
    if ($to_currency === 'TRY') {
        return $amount_try;
    }

    return $amount_try / $to_rate;
}

function enrich_cost_display_fields(&$row, $can_view_cost, $rates)
{
    $display_currency = normalize_currency_code($row['satis_fiyati_para_birimi'] ?? 'TRY');
    $row['cost_display_currency'] = $display_currency;

    $alis_fiyati = (float) ($row['alis_fiyati'] ?? 0);
    $alis_currency = normalize_currency_code($row['alis_fiyati_para_birimi'] ?? 'TRY');
    $row['alis_fiyati_display'] = round(convert_currency_amount($alis_fiyati, $alis_currency, $display_currency, $rates), 6);

    if ($can_view_cost) {
        if (($row['urun_tipi'] ?? '') === 'hazir_alinan') {
            $teorik_maliyet_try = convert_currency_amount($alis_fiyati, $alis_currency, 'TRY', $rates);
            $row['teorik_maliyet'] = $teorik_maliyet_try;
        } else {
            $teorik_maliyet_try = (float) ($row['teorik_maliyet'] ?? 0);
        }
        $row['teorik_maliyet_display'] = round(convert_currency_amount($teorik_maliyet_try, 'TRY', $display_currency, $rates), 6);
    }
}

function get_recent_contract_usage_for_material($connection, $material_code, $rates, $limit = 5)
{
    $material_code = trim((string) $material_code);
    if ($material_code === '') {
        return [];
    }

    $limit = max(1, min(10, (int) $limit));
    $query = "SELECT shs.sozlesme_id, shs.tarih, shs.kullanilan_miktar, shs.birim_fiyat, shs.para_birimi,
                     COALESCE(shs.tedarikci_adi, cs.tedarikci_adi) as tedarikci_adi,
                     cs.baslangic_tarihi, cs.bitis_tarihi
              FROM stok_hareketleri_sozlesmeler shs
              LEFT JOIN cerceve_sozlesmeler cs ON cs.sozlesme_id = shs.sozlesme_id
              WHERE TRIM(CAST(shs.malzeme_kodu AS CHAR)) = TRIM(?)
              ORDER BY shs.tarih DESC, shs.sozlesme_id DESC
              LIMIT " . $limit;
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('s', $material_code);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $contracts = [];
    while ($row = $result->fetch_assoc()) {
        $currency = normalize_currency_code($row['para_birimi'] ?? 'TRY');
        $base_price = (float) ($row['birim_fiyat'] ?? 0);
        $contracts[] = [
            'sozlesme_id' => (int) ($row['sozlesme_id'] ?? 0),
            'tedarikci_adi' => (string) ($row['tedarikci_adi'] ?? ''),
            'birim_fiyat' => round($base_price, 6),
            'para_birimi' => $currency,
            'birim_fiyat_try' => round(convert_currency_amount($base_price, $currency, 'TRY', $rates), 6),
            'kullanilan_miktar' => round((float) ($row['kullanilan_miktar'] ?? 0), 6),
            'tarih' => $row['tarih'] ?? null,
            'baslangic_tarihi' => $row['baslangic_tarihi'] ?? null,
            'bitis_tarihi' => $row['bitis_tarihi'] ?? null,
            'kaynak_turu' => 'stok_hareketleri_sozlesmeler'
        ];
    }
    $stmt->close();

    return $contracts;
}

function extract_unique_contracts_from_children($children, $limit = 5)
{
    $limit = max(1, min(10, (int) $limit));
    $contracts = [];
    $seen = [];

    foreach ($children as $child) {
        $child_contracts = isset($child['cost_calc']['contracts']) && is_array($child['cost_calc']['contracts'])
            ? $child['cost_calc']['contracts']
            : [];

        foreach ($child_contracts as $contract) {
            $key = (string) ($contract['sozlesme_id'] ?? 0) . '|' . (string) ($contract['tarih'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $contracts[] = $contract;
            if (count($contracts) >= $limit) {
                return $contracts;
            }
        }
    }

    return $contracts;
}

function build_cost_calc_payload($source, $unit_cost_try, $base_unit_price, $base_currency, $rates, $formula, $note, $contracts = [])
{
    $base_currency = normalize_currency_code($base_currency);
    $base_to_try_rate = 1.0;
    if ($base_currency !== 'TRY') {
        $base_to_try_rate = isset($rates[$base_currency]) ? (float) $rates[$base_currency] : 1.0;
        if ($base_to_try_rate <= 0) {
            $base_to_try_rate = 1.0;
        }
    }

    return [
        'source' => (string) $source,
        'formula' => (string) $formula,
        'note' => (string) $note,
        'base_unit_price' => round((float) $base_unit_price, 6),
        'base_currency' => $base_currency,
        'base_to_try_rate' => round($base_to_try_rate, 6),
        'unit_cost_try' => round((float) $unit_cost_try, 6),
        'contracts' => is_array($contracts) ? $contracts : []
    ];
}

function build_essence_cost_children($connection, $esans_kodu, $display_currency, $rates, &$warnings)
{
    $children = [];
    $esans_id = null;

    $esans_stmt = $connection->prepare("SELECT esans_id FROM esanslar WHERE esans_kodu = ? LIMIT 1");
    if ($esans_stmt) {
        $esans_stmt->bind_param('s', $esans_kodu);
        $esans_stmt->execute();
        $esans_row = $esans_stmt->get_result()->fetch_assoc();
        $esans_stmt->close();
        if ($esans_row) {
            $esans_id = (string) $esans_row['esans_id'];
        }
    }

    $query = "SELECT ua.urun_agaci_id, ua.bilesen_kodu, ua.bilesen_ismi, ua.bilesen_miktari,
                     m.malzeme_kodu as matched_malzeme_kodu, m.alis_fiyati as malzeme_alis_fiyati, m.para_birimi as malzeme_para_birimi
              FROM urun_agaci ua
              LEFT JOIN malzemeler m ON TRIM(ua.bilesen_kodu) = TRIM(CAST(m.malzeme_kodu AS CHAR))
              WHERE ua.agac_turu = 'esans' AND (ua.urun_kodu = ?" . ($esans_id !== null ? " OR ua.urun_kodu = ?" : "") . ")
              ORDER BY ua.urun_agaci_id";
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        $warnings[] = 'Esans alt maliyet sorgusu hazirlanamadi.';
        return $children;
    }

    if ($esans_id !== null) {
        $stmt->bind_param('ss', $esans_kodu, $esans_id);
    } else {
        $stmt->bind_param('s', $esans_kodu);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $child_warnings = [];
        $qty = (float) ($row['bilesen_miktari'] ?? 0);
        $unit_cost_try = 0.0;
        $source = 'none';
        $base_unit_price = 0.0;
        $base_currency = 'TRY';
        $formula = 'Maliyet kaynagi bulunamadi.';
        $note = 'Bu alt satir icin malzeme kaynagi bulunamadigi icin birim maliyet 0 kabul edildi.';
        $contracts = [];

        if ($row['matched_malzeme_kodu'] !== null) {
            $source = 'material_purchase_price';
            $base_unit_price = (float) ($row['malzeme_alis_fiyati'] ?? 0);
            $base_currency = normalize_currency_code($row['malzeme_para_birimi'] ?? 'TRY');
            $unit_cost_try = convert_currency_amount(
                $base_unit_price,
                $base_currency,
                'TRY',
                $rates
            );
            $contracts = get_recent_contract_usage_for_material($connection, $row['matched_malzeme_kodu'], $rates, 5);
            $formula = $base_currency === 'TRY'
                ? 'Birim Maliyet (TRY) = Alis Fiyati'
                : 'Birim Maliyet (TRY) = Alis Fiyati x Kur';
            $note = 'Kaynak: malzemeler tablosundaki alis fiyatidir. Sozlesme listesi son kullanim kayitlarindan gelir.';

            if ($unit_cost_try <= 0) {
                $child_warnings[] = 'Esans alt bileseninde maliyet 0 veya negatif gorunuyor.';
            }
        } else {
            $child_warnings[] = 'Esans alt bileseni icin malzeme kaynagi bulunamadi, 0 kabul edildi.';
        }

        if ($qty <= 0) {
            $child_warnings[] = 'Bilesen miktari 0 veya negatif.';
        }

        $line_cost_try = $qty * $unit_cost_try;
        $children[] = [
            'row_id' => (int) ($row['urun_agaci_id'] ?? 0),
            'bilesen_kodu' => (string) ($row['bilesen_kodu'] ?? ''),
            'bilesen_ismi' => (string) ($row['bilesen_ismi'] ?? ''),
            'bilesen_turu' => 'esans_alt_malzeme',
            'bilesen_miktari' => round($qty, 6),
            'unit_cost_try' => round($unit_cost_try, 6),
            'unit_cost_display' => round(convert_currency_amount($unit_cost_try, 'TRY', $display_currency, $rates), 6),
            'line_cost_try' => round($line_cost_try, 6),
            'line_cost_display' => round(convert_currency_amount($line_cost_try, 'TRY', $display_currency, $rates), 6),
            'cost_source' => $source,
            'warnings' => $child_warnings,
            'cost_calc' => build_cost_calc_payload($source, $unit_cost_try, $base_unit_price, $base_currency, $rates, $formula, $note, $contracts)
        ];
    }
    $stmt->close();

    return $children;
}

function get_product_cost_breakdown_data($connection, $urun_kodu, $rates)
{
    $product_query = "SELECT u.*, COALESCE(vum.teorik_maliyet, 0) as teorik_maliyet
                      FROM urunler u
                      LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu
                      WHERE u.urun_kodu = ? LIMIT 1";
    $product_stmt = $connection->prepare($product_query);
    if (!$product_stmt) {
        throw new Exception('Urun sorgusu hazirlanamadi.');
    }
    $product_stmt->bind_param('i', $urun_kodu);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $product = $product_result ? $product_result->fetch_assoc() : null;
    $product_stmt->close();

    if (!$product) {
        throw new Exception('Urun bulunamadi.');
    }

    enrich_cost_display_fields($product, true, $rates);
    $display_currency = normalize_currency_code($product['cost_display_currency'] ?? ($product['satis_fiyati_para_birimi'] ?? 'TRY'));
    $teorik_maliyet_try = (float) ($product['teorik_maliyet'] ?? 0);

    $items = [];
    $warnings = [];
    $toplam_bilesen_maliyeti_try = 0.0;
    $has_zero_or_negative_essence_cost = false;

    if (($product['urun_tipi'] ?? '') === 'hazir_alinan') {
        $alis_fiyati = (float) ($product['alis_fiyati'] ?? 0);
        $alis_currency = normalize_currency_code($product['alis_fiyati_para_birimi'] ?? 'TRY');
        $unit_cost_try = convert_currency_amount($alis_fiyati, $alis_currency, 'TRY', $rates);
        $line_cost_try = $unit_cost_try;
        $item_warnings = [];
        if ($unit_cost_try <= 0) {
            $item_warnings[] = 'Hazir alinan urunun alis maliyeti 0 veya negatif gorunuyor.';
        }

        $contracts = get_recent_contract_usage_for_material($connection, $product['urun_kodu'] ?? '', $rates, 5);
        $cost_calc = build_cost_calc_payload(
            'ready_product_purchase_price',
            $unit_cost_try,
            $alis_fiyati,
            $alis_currency,
            $rates,
            $alis_currency === 'TRY' ? 'Birim Maliyet (TRY) = Alis Fiyati' : 'Birim Maliyet (TRY) = Alis Fiyati x Kur',
            'Kaynak: urunler tablosundaki alis fiyati. Sozlesme listesi son kullanim kayitlarindan gelir.',
            $contracts
        );
        $toplam_bilesen_maliyeti_try += $line_cost_try;

        $items[] = [
            'row_id' => 0,
            'bilesen_kodu' => (string) ($product['urun_kodu'] ?? ''),
            'bilesen_ismi' => (string) ($product['urun_ismi'] ?? ''),
            'bilesen_turu' => 'hazir_alinan_urun',
            'bilesen_miktari' => 1.0,
            'unit_cost_try' => round($unit_cost_try, 6),
            'unit_cost_display' => round(convert_currency_amount($unit_cost_try, 'TRY', $display_currency, $rates), 6),
            'line_cost_try' => round($line_cost_try, 6),
            'line_cost_display' => round(convert_currency_amount($line_cost_try, 'TRY', $display_currency, $rates), 6),
            'cost_source' => 'ready_product_purchase_price',
            'warnings' => $item_warnings,
            'children' => [],
            'cost_calc' => $cost_calc
        ];
    } else {
        $bom_query = "SELECT ua.urun_agaci_id, ua.bilesen_kodu, ua.bilesen_ismi, ua.bilesen_miktari, ua.bilesenin_malzeme_turu as bilesen_turu,
                             m.malzeme_kodu as matched_malzeme_kodu, m.alis_fiyati as malzeme_alis_fiyati, m.para_birimi as malzeme_para_birimi,
                             e.esans_kodu as matched_esans_kodu, COALESCE(vem.toplam_maliyet, 0) as esans_toplam_maliyet,
                             pu.urun_kodu as matched_urun_kodu
                      FROM urun_agaci ua
                      LEFT JOIN malzemeler m ON TRIM(ua.bilesen_kodu) = TRIM(CAST(m.malzeme_kodu AS CHAR))
                      LEFT JOIN esanslar e ON TRIM(ua.bilesen_kodu) = TRIM(e.esans_kodu)
                      LEFT JOIN v_esans_maliyetleri vem ON TRIM(ua.bilesen_kodu) = TRIM(vem.esans_kodu)
                      LEFT JOIN urunler pu ON TRIM(ua.bilesen_kodu) = TRIM(CAST(pu.urun_kodu AS CHAR))
                      WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?
                      ORDER BY ua.urun_agaci_id";
        $bom_stmt = $connection->prepare($bom_query);
        if (!$bom_stmt) {
            throw new Exception('Urun agaci maliyet sorgusu hazirlanamadi.');
        }
        $bom_stmt->bind_param('i', $urun_kodu);
        $bom_stmt->execute();
        $bom_result = $bom_stmt->get_result();

        while ($row = $bom_result->fetch_assoc()) {
            $item_warnings = [];
            $children = [];
            $qty = (float) ($row['bilesen_miktari'] ?? 0);
            $unit_cost_try = 0.0;
            $source = 'none';
            $base_unit_price = 0.0;
            $base_currency = 'TRY';
            $formula = 'Maliyet kaynagi bulunamadi.';
            $note = 'Bu satir icin maliyet kaynagi bulunamadigi icin birim maliyet 0 kabul edildi.';
            $contracts = [];

            if ($row['matched_malzeme_kodu'] !== null) {
                $source = 'material_purchase_price';
                $base_unit_price = (float) ($row['malzeme_alis_fiyati'] ?? 0);
                $base_currency = normalize_currency_code($row['malzeme_para_birimi'] ?? 'TRY');
                $unit_cost_try = convert_currency_amount(
                    $base_unit_price,
                    $base_currency,
                    'TRY',
                    $rates
                );
                $contracts = get_recent_contract_usage_for_material($connection, $row['matched_malzeme_kodu'], $rates, 5);
                $formula = $base_currency === 'TRY'
                    ? 'Birim Maliyet (TRY) = Alis Fiyati'
                    : 'Birim Maliyet (TRY) = Alis Fiyati x Kur';
                $note = 'Kaynak: malzemeler tablosundaki alis fiyatidir. Sozlesme listesi son kullanim kayitlarindan gelir.';
                if ($unit_cost_try <= 0) {
                    $item_warnings[] = 'Bilesen birim maliyeti 0 veya negatif gorunuyor.';
                }
            } elseif (!empty($row['matched_esans_kodu'])) {
                $source = 'essence_view_cost';
                $unit_cost_try = (float) ($row['esans_toplam_maliyet'] ?? 0);
                $children = build_essence_cost_children($connection, $row['matched_esans_kodu'], $display_currency, $rates, $item_warnings);
                $base_unit_price = (float) ($row['esans_toplam_maliyet'] ?? 0);
                $base_currency = 'TRY';
                $formula = 'Birim Maliyet (TRY) = v_esans_maliyetleri.toplam_maliyet';
                $contracts = extract_unique_contracts_from_children($children, 5);
                $note = 'Kaynak: esans maliyet gorunumu (v_esans_maliyetleri). Alt satir maliyetleri ile desteklenir.';

                if (empty($children)) {
                    $item_warnings[] = 'Esans icin alt recete satiri bulunamadi.';
                }

                if ($unit_cost_try <= 0) {
                    $item_warnings[] = 'Esans maliyeti 0 veya negatif gorunuyor. Esans recetesi ve alt malzeme alis fiyatlarini kontrol edin.';
                    $has_zero_or_negative_essence_cost = true;
                }

                if (!empty($children)) {
                    $child_total_try = 0.0;
                    foreach ($children as $child_row) {
                        $child_total_try += (float) ($child_row['line_cost_try'] ?? 0);
                    }
                    if (abs($child_total_try - $unit_cost_try) > 0.01) {
                        $item_warnings[] = 'Esans alt kirilim toplami ile esans maliyet gorunumu arasinda fark var.';
                    }
                }
            } elseif ($row['matched_urun_kodu'] !== null) {
                $source = 'product_component_not_costed';
                $formula = 'Urun bileseni bu formulde 0 kabul edilir.';
                $note = 'Bu satir baska bir urunu temsil ettigi icin mevcut maliyet formulunde otomatik dahil edilmiyor.';
                $item_warnings[] = 'Urun bileseni maliyeti mevcut formulde otomatik dahil edilmiyor, 0 kabul edildi.';
            } else {
                $source = 'none';
                $formula = 'Kaynak olmadigi icin Birim Maliyet = 0';
                $note = 'Bilesen kodu malzeme/esans/urun kaydiyla eslesmedigi icin birim maliyet 0 kabul edildi.';
                $item_warnings[] = 'Bilesen maliyet kaynagi bulunamadi, 0 kabul edildi.';
            }

            if ($qty <= 0) {
                $item_warnings[] = 'Bilesen miktari 0 veya negatif.';
            }

            $line_cost_try = $qty * $unit_cost_try;
            $toplam_bilesen_maliyeti_try += $line_cost_try;

            $items[] = [
                'row_id' => (int) ($row['urun_agaci_id'] ?? 0),
                'bilesen_kodu' => (string) ($row['bilesen_kodu'] ?? ''),
                'bilesen_ismi' => (string) ($row['bilesen_ismi'] ?? ''),
                'bilesen_turu' => (string) ($row['bilesen_turu'] ?? ''),
                'bilesen_miktari' => round($qty, 6),
                'unit_cost_try' => round($unit_cost_try, 6),
                'unit_cost_display' => round(convert_currency_amount($unit_cost_try, 'TRY', $display_currency, $rates), 6),
                'line_cost_try' => round($line_cost_try, 6),
                'line_cost_display' => round(convert_currency_amount($line_cost_try, 'TRY', $display_currency, $rates), 6),
                'cost_source' => $source,
                'warnings' => $item_warnings,
                'children' => $children,
                'cost_calc' => build_cost_calc_payload($source, $unit_cost_try, $base_unit_price, $base_currency, $rates, $formula, $note, $contracts)
            ];
        }
        $bom_stmt->close();
    }

    $fark_try = $teorik_maliyet_try - $toplam_bilesen_maliyeti_try;
    if (abs($fark_try) > 0.01) {
        $warnings[] = 'Satir toplami ile teorik maliyet arasinda fark var. Guncel view formulune gore teorik maliyet esas alinir.';
    }

    if ($has_zero_or_negative_essence_cost) {
        $warnings[] = 'En az bir esans satirinda maliyet 0 veya negatif gorunuyor.';
    }

    $product_payload = $product;
    $product_payload['teorik_maliyet_try'] = round($teorik_maliyet_try, 6);

    return [
        'product' => $product_payload,
        'rates' => $rates,
        'summary' => [
            'toplam_bilesen_maliyeti_try' => round($toplam_bilesen_maliyeti_try, 6),
            'toplam_bilesen_maliyeti_display' => round(convert_currency_amount($toplam_bilesen_maliyeti_try, 'TRY', $display_currency, $rates), 6),
            'teorik_maliyet_try' => round($teorik_maliyet_try, 6),
            'teorik_maliyet_display' => round(convert_currency_amount($teorik_maliyet_try, 'TRY', $display_currency, $rates), 6),
            'fark_try' => round($fark_try, 6),
            'fark_display' => round(convert_currency_amount($fark_try, 'TRY', $display_currency, $rates), 6),
            'maliyet_kaynagi_notu' => 'Hesaplama TRY bazinda yapilir, gosterimde satis para birimine cevrilir.'
        ],
        'items' => $items,
        'warnings' => $warnings
    ];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_products') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürünleri görüntüleme yetkiniz yok.']);
            exit;
        }
        try {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
            $urun_tipi = isset($_GET['urun_tipi']) ? $_GET['urun_tipi'] : '';
            $depo_filter = isset($_GET['depo']) ? $_GET['depo'] : '';
            $raf_filter = isset($_GET['raf']) ? $_GET['raf'] : '';
            $offset = ($page - 1) * $limit;
            $search_term = '%' . $search . '%';

            // Build WHERE conditions
            $where_conditions = [];
            $params = [];
            $param_types = '';

            if (!empty($search)) {
                $where_conditions[] = "(u.urun_ismi LIKE ? OR u.urun_kodu LIKE ?)";
                $params[] = $search_term;
                $params[] = $search_term;
                $param_types .= 'ss';
            }

            if ($filter === 'critical') {
                $where_conditions[] = "u.stok_miktari <= u.kritik_stok_seviyesi AND u.kritik_stok_seviyesi > 0";
            }

            if (!empty($urun_tipi)) {
                $where_conditions[] = "u.urun_tipi = ?";
                $params[] = $urun_tipi;
                $param_types .= 's';
            }

            if (!empty($depo_filter)) {
                $where_conditions[] = "u.depo = ?";
                $params[] = $depo_filter;
                $param_types .= 's';
            }

            if (!empty($raf_filter)) {
                $where_conditions[] = "u.raf = ?";
                $params[] = $raf_filter;
                $param_types .= 's';
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $count_query = "SELECT COUNT(*) as total, SUM(u.stok_miktari) as total_stock FROM urunler u {$where_clause}";
            $stmt_count = $connection->prepare($count_query);
            if (!empty($params)) {
                $stmt_count->bind_param($param_types, ...$params);
            }
            $stmt_count->execute();
            $count_result = $stmt_count->get_result()->fetch_assoc();
            $total_products = $count_result['total'];
            $total_stock = $count_result['total_stock'] ?? 0;
            $total_pages = ceil($total_products / $limit);
            $stmt_count->close();

            $can_view_cost = yetkisi_var('action:urunler:view_cost');
            $rates = get_exchange_rates($connection);
            $cost_column = $can_view_cost ? ", vum.teorik_maliyet" : "";
            $query = "
                SELECT u.* {$cost_column}, COUNT(uf.fotograf_id) as foto_sayisi
                FROM urunler u
                " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                LEFT JOIN urun_fotograflari uf ON u.urun_kodu = uf.urun_kodu
                {$where_clause}
                GROUP BY u.urun_kodu
                ORDER BY u.urun_ismi
                LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;
            $param_types .= 'ii';

            $stmt_data = $connection->prepare($query);
            $stmt_data->bind_param($param_types, ...$params);
            $stmt_data->execute();
            $result = $stmt_data->get_result();

            $products = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Üretilebilir miktar hesaplama
                    $row['uretilebilir_miktar'] = 0;  // Kritik bileşenlere göre
                    $row['gercek_uretilebilir'] = 0;  // Tüm bileşenlere göre
                    $row['maliyet_eksik_uyari'] = false;

                    $kritik_bilesen_turleri = ['kutu', 'takim', 'esans'];
                    $uretilebilir_kritik = PHP_INT_MAX;
                    $uretilebilir_gercek = PHP_INT_MAX;
                    $bom_satir_sayisi = 0;

                    // Ürün ağacından bileşenleri al
                    $bom_query = "SELECT ua.bilesen_miktari, ua.bilesenin_malzeme_turu as bilesen_turu,
                                 CASE
                                    WHEN m.malzeme_kodu IS NOT NULL THEN m.stok_miktari
                                    WHEN u.urun_kodu IS NOT NULL THEN u.stok_miktari
                                    WHEN e.esans_kodu IS NOT NULL THEN e.stok_miktari
                                    ELSE 0
                                 END as bilesen_stok
                                 FROM urun_agaci ua
                                 LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                                 LEFT JOIN urunler u ON ua.bilesen_kodu = u.urun_kodu
                                 LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                                 WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
                    $bom_stmt = $connection->prepare($bom_query);
                    $bom_stmt->bind_param('i', $row['urun_kodu']);
                    $bom_stmt->execute();
                    $bom_result = $bom_stmt->get_result();

                    while ($bom_row = $bom_result->fetch_assoc()) {
                        $bom_satir_sayisi++;
                        $gerekli = floatval($bom_row['bilesen_miktari']);
                        $mevcut = floatval($bom_row['bilesen_stok']);

                        if ($gerekli > 0) {
                            $bu_bilesenden = max(0, floor($mevcut / $gerekli));

                            // Gerçek üretilebilir (tüm bileşenler)
                            $uretilebilir_gercek = min($uretilebilir_gercek, $bu_bilesenden);

                            // Kritik üretilebilir (sadece kritik bileşenler)
                            if (in_array(strtolower($bom_row['bilesen_turu']), $kritik_bilesen_turleri)) {
                                $uretilebilir_kritik = min($uretilebilir_kritik, $bu_bilesenden);
                            }
                        }
                    }
                    $bom_stmt->close();

                    $row['uretilebilir_miktar'] = ($uretilebilir_kritik === PHP_INT_MAX) ? 0 : $uretilebilir_kritik;
                    $row['gercek_uretilebilir'] = ($uretilebilir_gercek === PHP_INT_MAX) ? 0 : $uretilebilir_gercek;

                    enrich_cost_display_fields($row, $can_view_cost, $rates);
                    if ($can_view_cost) {
                        $teorik_maliyet_try = (float) ($row['teorik_maliyet'] ?? 0);
                        $row['maliyet_eksik_uyari'] = (
                            ($row['urun_tipi'] ?? '') === 'uretilen' &&
                            $bom_satir_sayisi > 0 &&
                            abs($teorik_maliyet_try) < 0.000001
                        );
                    } else {
                        unset($row['teorik_maliyet']);
                    }

                    $products[] = $row;
                }
            }
            $stmt_data->close();

            // Calculate filtered critical stock count (apply same filters except critical filter itself)
            $critical_where_conditions = $where_conditions;
            $critical_params = [];
            $critical_param_types = '';

            // Remove critical filter from conditions if it was added
            $critical_where_conditions = array_filter($critical_where_conditions, function ($cond) {
                return strpos($cond, 'kritik_stok_seviyesi') === false;
            });

            // Add critical stock condition
            $critical_where_conditions[] = "u.stok_miktari <= u.kritik_stok_seviyesi AND u.kritik_stok_seviyesi > 0";

            // Rebuild params without limit/offset and filter param
            if (!empty($search)) {
                $critical_params[] = $search_term;
                $critical_params[] = $search_term;
                $critical_param_types .= 'ss';
            }
            if (!empty($urun_tipi)) {
                $critical_params[] = $urun_tipi;
                $critical_param_types .= 's';
            }
            if (!empty($depo_filter)) {
                $critical_params[] = $depo_filter;
                $critical_param_types .= 's';
            }
            if (!empty($raf_filter)) {
                $critical_params[] = $raf_filter;
                $critical_param_types .= 's';
            }

            $critical_where_clause = !empty($critical_where_conditions) ? 'WHERE ' . implode(' AND ', $critical_where_conditions) : '';
            $critical_count_query = "SELECT COUNT(*) as total FROM urunler u {$critical_where_clause}";
            $stmt_critical = $connection->prepare($critical_count_query);
            if (!empty($critical_params)) {
                $stmt_critical->bind_param($critical_param_types, ...$critical_params);
            }
            $stmt_critical->execute();
            $filtered_critical = $stmt_critical->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt_critical->close();

            $response = [
                'status' => 'success',
                'data' => $products,
                'pagination' => [
                    'total_pages' => $total_pages,
                    'total_products' => $total_products,
                    'current_page' => $page
                ],
                'stats' => [
                    'total_products' => $total_products,
                    'total_stock' => $total_stock,
                    'critical_products' => $filtered_critical
                ]
            ];
        } catch (Throwable $t) {
            $response = ['status' => 'error', 'message' => 'Bir hata oluştu: ' . $t->getMessage()];
        }
    } elseif ($action == 'get_product' && isset($_GET['id'])) {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün görüntüleme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_GET['id'];
        $query = "SELECT * FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $response = ['status' => 'success', 'data' => $product];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün bulunamadı.'];
        }
    } elseif ($action == 'get_depo_list') {
        $query = "SELECT DISTINCT depo_ismi FROM lokasyonlar ORDER BY depo_ismi";
        $result = $connection->query($query);
        $depolar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $depolar[] = $row;
            }
            $response = ['status' => 'success', 'data' => $depolar];
        } else {
            $response = ['status' => 'error', 'message' => 'Depo listesi alınamadı.'];
        }
    } elseif ($action == 'get_raf_list') {
        $depo = $_GET['depo'] ?? '';
        $query = "SELECT DISTINCT raf FROM lokasyonlar WHERE depo_ismi = ? ORDER BY raf";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('s', $depo);
        $stmt->execute();
        $result = $stmt->get_result();
        $raflar = [];
        while ($row = $result->fetch_assoc()) {
            $raflar[] = $row;
        }
        $stmt->close();
        $response = ['status' => 'success', 'data' => $raflar];
    } elseif ($action == 'get_product_depolar') {
        // Sadece ürünlerde kullanılan depolar
        $query = "SELECT DISTINCT depo FROM urunler WHERE depo IS NOT NULL AND depo != '' ORDER BY depo";
        $result = $connection->query($query);
        $depolar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $depolar[] = ['depo_ismi' => $row['depo']];
            }
            $response = ['status' => 'success', 'data' => $depolar];
        } else {
            $response = ['status' => 'error', 'message' => 'Depo listesi alınamadı.'];
        }
    } elseif ($action == 'get_product_raflar') {
        // Sadece ürünlerde kullanılan raflar (depoya göre filtrelenebilir)
        $depo = $_GET['depo'] ?? '';
        if (!empty($depo)) {
            $query = "SELECT DISTINCT raf FROM urunler WHERE depo = ? AND raf IS NOT NULL AND raf != '' ORDER BY raf";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $depo);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT DISTINCT raf FROM urunler WHERE raf IS NOT NULL AND raf != '' ORDER BY raf";
            $result = $connection->query($query);
        }
        $raflar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $raflar[] = $row;
            }
            $response = ['status' => 'success', 'data' => $raflar];
        } else {
            $response = ['status' => 'error', 'message' => 'Raf listesi alınamadı.'];
        }
        if (isset($stmt))
            $stmt->close();
    } elseif ($action == 'get_cost_breakdown') {
        if (!yetkisi_var('action:urunler:view_cost')) {
            echo json_encode(['status' => 'error', 'message' => 'Maliyet detayini goruntuleme yetkiniz yok.']);
            exit;
        }

        $urun_kodu = isset($_GET['urun_kodu']) ? (int) $_GET['urun_kodu'] : 0;
        if ($urun_kodu <= 0) {
            $response = ['status' => 'error', 'message' => 'Gecerli bir urun kodu gerekli.'];
        } else {
            try {
                $rates = get_exchange_rates($connection);
                $breakdown = get_product_cost_breakdown_data($connection, $urun_kodu, $rates);
                $response = ['status' => 'success', 'data' => $breakdown];
            } catch (Throwable $t) {
                $response = ['status' => 'error', 'message' => 'Maliyet detayi alinamadi: ' . $t->getMessage()];
            }
        }
    } elseif ($action == 'get_all_products') {
        $can_view_cost = yetkisi_var('action:urunler:view_cost');
        $rates = get_exchange_rates($connection);
        $cost_column = $can_view_cost ? ", COALESCE(vum.teorik_maliyet, 0) as teorik_maliyet" : "";

        $query = "SELECT u.* {$cost_column} 
                  FROM urunler u 
                  " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                  ORDER BY u.urun_ismi";
        $result = $connection->query($query);

        if ($result) {
            $products = [];
            while ($row = $result->fetch_assoc()) {
                enrich_cost_display_fields($row, $can_view_cost, $rates);
                $row['maliyet_eksik_uyari'] = false;
                if (!$can_view_cost) {
                    unset($row['teorik_maliyet']);
                }
                $products[] = $row;
            }
            $response = ['status' => 'success', 'data' => $products];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün listesi alınamadı.'];
        }
    } elseif ($action == 'get_malzeme_turleri') {
        $query = "SELECT * FROM malzeme_turleri ORDER BY label";
        $result = $connection->query($query);
        $turler = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $turler[] = $row;
            }
            $response = ['status' => 'success', 'data' => $turler];
        } else {
            $response = ['status' => 'error', 'message' => 'Malzeme türleri listesi alınamadı.'];
        }
    } elseif ($action == 'get_available_tanks') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Tankları görüntüleme yetkiniz yok.']);
            exit;
        }

        $query = "SELECT t.tank_kodu, t.tank_ismi
                  FROM tanklar t
                  LEFT JOIN esanslar e ON t.tank_kodu = e.tank_kodu
                  WHERE e.tank_kodu IS NULL
                  ORDER BY t.tank_ismi";
        $result = $connection->query($query);
        $tanks = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tanks[] = $row;
            }
            $response = ['status' => 'success', 'data' => $tanks];
        } else {
            $response = ['status' => 'error', 'message' => 'Boş tank listesi alınamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    $urun_ismi = $_POST['urun_ismi'] ?? null;
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (int) $_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'adet';
    $satis_fiyati = isset($_POST['satis_fiyati']) && $_POST['satis_fiyati'] !== '' ? (float) $_POST['satis_fiyati'] : 0.0;
    $satis_fiyati_para_birimi = $_POST['satis_fiyati_para_birimi'] ?? 'TRY';
    $alis_fiyati = isset($_POST['alis_fiyati']) && $_POST['alis_fiyati'] !== '' ? (float) $_POST['alis_fiyati'] : 0.0;
    $alis_fiyati_para_birimi = $_POST['alis_fiyati_para_birimi'] ?? 'TRY';
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int) $_POST['kritik_stok_seviyesi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';
    $urun_tipi = $_POST['urun_tipi'] ?? 'uretilen';
    $urun_tipi = $_POST['urun_tipi'] ?? 'uretilen';
    $selected_material_types = isset($_POST['selected_material_types']) ? json_decode($_POST['selected_material_types'], true) : [];
    $create_essence = isset($_POST['create_essence']) && $_POST['create_essence'] === '1';
    $selected_tank_kodu = trim($_POST['selected_tank_kodu'] ?? '');

    if (in_array($action, ['add_product', 'update_product'], true)) {
        $stock_validation_error = validate_non_negative_stock_value($stok_miktari);
        if ($stock_validation_error !== null) {
            echo json_encode(['status' => 'error', 'message' => $stock_validation_error]);
            exit;
        }
    }

    if ($action == 'add_product') {
        if (!yetkisi_var('action:urunler:create')) {
            echo json_encode(['status' => 'error', 'message' => 'Yeni ürün ekleme yetkiniz yok.']);
            exit;
        }
        if (empty($urun_ismi)) {
            $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } elseif ($create_essence && empty($selected_tank_kodu)) {
            $response = ['status' => 'error', 'message' => 'Esans oluşturmak için boş bir tank seçmelisiniz.'];
        } else {
            $product_inserted = false;
            $new_product_id = null;

            try {
            $precheck_failed = false;
            if ($create_essence) {
                $precheck_query = "SELECT t.tank_kodu
                                   FROM tanklar t
                                   LEFT JOIN esanslar e ON t.tank_kodu = e.tank_kodu
                                   WHERE t.tank_kodu = ? AND e.tank_kodu IS NULL
                                   LIMIT 1";
                $precheck_stmt = $connection->prepare($precheck_query);
                $precheck_stmt->bind_param('s', $selected_tank_kodu);
                $precheck_stmt->execute();
                $precheck_result = $precheck_stmt->get_result();
                $precheck_row = $precheck_result ? $precheck_result->fetch_assoc() : null;
                $precheck_stmt->close();

                if (!$precheck_row) {
                    $precheck_failed = true;
                    $response = ['status' => 'error', 'message' => 'Seçilen tank artık kullanılamıyor. Lütfen boş bir tank seçip tekrar deneyin.'];
                }
            }

            if (!$precheck_failed) {
            // Check if product name already exists
            $check_query = "SELECT urun_kodu FROM urunler WHERE urun_ismi = ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param('s', $urun_ismi);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_stmt->close();

            if ($check_result->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Bu ürün ismi zaten mevcut. Lütfen farklı bir isim kullanın.'];
            } else {
                $depo = $depo ?: NULL;
                $raf = $raf ?: NULL;

                // Eğer ürün tipi üretilen ise alış fiyatını 0 yap (sadece hazır alınan ürünlerde alış fiyatı olur)
                if ($urun_tipi === 'uretilen') {
                    $alis_fiyati = 0.0;
                    $alis_fiyati_para_birimi = 'TRY';
                } elseif ($urun_tipi === 'hazir_alinan') {
                    $rates = get_exchange_rates($connection);
                    $source_currency = normalize_currency_code($alis_fiyati_para_birimi);
                    $target_currency_calc = normalize_currency_code($satis_fiyati_para_birimi);
                    $target_currency_store = strtoupper(trim((string) $satis_fiyati_para_birimi));
                    if (!in_array($target_currency_store, ['TL', 'TRY', 'USD', 'EUR'], true)) {
                        $target_currency_store = $target_currency_calc;
                    }
                    $alis_fiyati = convert_currency_amount($alis_fiyati, $source_currency, $target_currency_calc, $rates);
                    $alis_fiyati_para_birimi = $target_currency_store;
                }

                $query = "INSERT INTO urunler (urun_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, satis_fiyati_para_birimi, alis_fiyati, alis_fiyati_para_birimi, kritik_stok_seviyesi, depo, raf, urun_tipi) VALUES ('" . $connection->real_escape_string($urun_ismi) . "', '" . $connection->real_escape_string($not_bilgisi) . "', " . (int)$stok_miktari . ", '" . $connection->real_escape_string($birim) . "', " . (float)$satis_fiyati . ", '" . $connection->real_escape_string($satis_fiyati_para_birimi) . "', " . (float)$alis_fiyati . ", '" . $connection->real_escape_string($alis_fiyati_para_birimi) . "', " . (int)$kritik_stok_seviyesi . ", " . ($depo ? "'" . $connection->real_escape_string($depo) . "'" : "NULL") . ", " . ($raf ? "'" . $connection->real_escape_string($raf) . "'" : "NULL") . ", '" . $connection->real_escape_string($urun_tipi) . "')";

                if ($connection->query($query)) {
                    $product_inserted = true;
                    $new_product_id = (int) $connection->insert_id;
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürünü sisteme eklendi", 'CREATE');
                    
                    // Otomatik Malzeme Ekleme
                    $created_ham_esans = null;
                    if (!empty($selected_material_types)) {
                        foreach ($selected_material_types as $type) {
                            $malzeme_turu_value = $type['value'];
                            $malzeme_turu_label = $type['label'];
                            $malzeme_ismi = $urun_ismi . ", " . $malzeme_turu_label;

                            // Malzeme zaten var mı kontrol et (Unique constraint hatası almamak için)
                            $m_check = "SELECT malzeme_kodu FROM malzemeler WHERE malzeme_ismi = '" . $connection->real_escape_string($malzeme_ismi) . "'";
                            $m_check_res = $connection->query($m_check);

                            if ($m_check_res && $m_check_res->num_rows == 0) {
                                $m_query = "INSERT INTO malzemeler (malzeme_ismi, malzeme_turu, birim, para_birimi, depo, raf)
                                           VALUES ('" . $connection->real_escape_string($malzeme_ismi) . "',
                                                   '" . $connection->real_escape_string($malzeme_turu_value) . "',
                                                   'adet', 'TRY',
                                                   " . ($depo ? "'" . $connection->real_escape_string($depo) . "'" : "NULL") . ",
                                                   " . ($raf ? "'" . $connection->real_escape_string($raf) . "'" : "NULL") . ")";
                                if ($connection->query($m_query)) {
                                    $new_material_id = $connection->insert_id;

                                    // Ham esans malzemesini ürün ağacına bağlama (kullanıcı istemedi)
                                    if ($malzeme_turu_value !== 'ham_esans') {
                                        // Ürün Ağacına Bağla
                                        $ua_query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari, agac_turu)
                                                    VALUES (?, ?, ?, ?, ?, 1.00, 'urun')";
                                        $ua_stmt = $connection->prepare($ua_query);
                                        $ua_stmt->bind_param('issss', $new_product_id, $urun_ismi, $malzeme_turu_value, $new_material_id, $malzeme_ismi);
                                        $ua_stmt->execute();
                                        $ua_stmt->close();
                                    } else {
                                        $created_ham_esans = [
                                            'id' => $new_material_id,
                                            'name' => $malzeme_ismi,
                                            'type' => $malzeme_turu_value
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    $tank_kodu = null;
                    $tank_ismi = null;
                    $essence_error = null;

                    // Otomatik Esans Oluşturma
                    if ($create_essence) {
                        $tank_kodu = $selected_tank_kodu;

                        // Seçilen tankın varlığını ve boşta olduğunu doğrula
                        $tank_check_query = "SELECT t.tank_kodu, t.tank_ismi
                                            FROM tanklar t
                                            LEFT JOIN esanslar e ON t.tank_kodu = e.tank_kodu
                                            WHERE t.tank_kodu = ? AND e.tank_kodu IS NULL
                                            LIMIT 1";
                        $tank_check_stmt = $connection->prepare($tank_check_query);
                        $tank_check_stmt->bind_param('s', $tank_kodu);
                        $tank_check_stmt->execute();
                        $tank_check_result = $tank_check_stmt->get_result();
                        $tank_row = $tank_check_result ? $tank_check_result->fetch_assoc() : null;
                        $tank_check_stmt->close();

                        if (!$tank_row) {
                            $essence_error = 'Seçilen tank artık kullanılamıyor. Lütfen boş bir tank seçip tekrar deneyin.';
                        } else {
                            $tank_ismi = $tank_row['tank_ismi'];

                            // Esans Kodu Oluştur (ES-YYYYMMDD-RAND)
                            $esans_kodu = 'ES-' . date('ymd') . '-' . rand(100, 999);
                            $esans_ismi = $urun_ismi . ', Esans';

                            // Esans Ekle
                            $esans_query = "INSERT INTO esanslar (esans_kodu, esans_ismi, stok_miktari, birim, demlenme_suresi_gun, not_bilgisi, tank_kodu, tank_ismi) 
                                           VALUES (
                                            '" . $connection->real_escape_string($esans_kodu) . "', 
                                            '" . $connection->real_escape_string($esans_ismi) . "', 
                                            0, 
                                            'lt', 
                                            1, 
                                            '', 
                                            '" . $connection->real_escape_string($tank_kodu) . "', 
                                            '" . $connection->real_escape_string($tank_ismi) . "'
                                           )";

                            if ($connection->query($esans_query)) {
                                $new_essence_id = $connection->insert_id;

                                log_islem($connection, $_SESSION['kullanici_adi'], "Otomatik esans oluşturuldu: $esans_ismi (Tank: $tank_kodu)", 'CREATE');

                                // Oluşturulan Esansı Ürün Ağacına Bağla
                                $ua_query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari, agac_turu)
                                            VALUES (?, ?, ?, ?, ?, 1.00, 'urun')";
                                $ua_stmt = $connection->prepare($ua_query);
                                $esans_type = 'esans';
                                $ua_stmt->bind_param('issss', $new_product_id, $urun_ismi, $esans_type, $esans_kodu, $esans_ismi);
                                $ua_stmt->execute();
                                $ua_stmt->close();

                                // Eğer Ham Esans oluşturulduysa, onu da Esans Ağacına bağla
                                if ($created_ham_esans) {
                                    $et_query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari, agac_turu)
                                                VALUES (?, ?, ?, ?, ?, 1.00, 'esans')";
                                    $et_stmt = $connection->prepare($et_query);
                                    $et_stmt->bind_param('sssss', $esans_kodu, $esans_ismi, $created_ham_esans['type'], $created_ham_esans['id'], $created_ham_esans['name']);
                                    $et_stmt->execute();
                                    $et_stmt->close();

                                    log_islem($connection, $_SESSION['kullanici_adi'], "Otomatik ham esans esans ağacına eklendi: " . $created_ham_esans['name'], 'CREATE');
                                }
                            } else {
                                $essence_error = 'Esans oluşturulamadı: ' . $connection->error;
                            }
                        }
                    }

                    if ($essence_error !== null) {
                        $response = [
                            'status' => 'partial_success',
                            'message' => 'Urun eklendi ancak esans olusturma adimi tamamlanamadi. Detay: ' . $essence_error . ' (Urun Kodu: ' . $new_product_id . ')'
                        ];
                    } else {
                        $resp_message = 'Ürün başarıyla eklendi';
                        if (!empty($selected_material_types))
                            $resp_message .= ' ve malzemeler eklendi';
                        if ($create_essence)
                            $resp_message .= ' ve esans oluşturuldu (Tank: ' . $tank_kodu . ')';

                        $response = ['status' => 'success', 'message' => $resp_message];
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => normalize_negative_stock_error_message(
                            $connection->error,
                            'Veritabani hatasi: ' . $connection->error
                        )
                    ];
                }
            }
            }
            } catch (Throwable $t) {
                if ($product_inserted && $new_product_id !== null) {
                    $response = [
                        'status' => 'partial_success',
                        'message' => 'Urun eklendi ancak ek adimlarda hata olustu: ' . $t->getMessage() . ' (Urun Kodu: ' . $new_product_id . ')'
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Urun ekleme sirasinda hata olustu: ' . $t->getMessage()
                    ];
                }
            }
        }
    } elseif ($action == 'update_product' && isset($_POST['urun_kodu'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün düzenleme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_POST['urun_kodu'];
        if (empty($urun_ismi)) {
            $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            // Check if product name already exists (excluding current product)
            $check_query = "SELECT urun_kodu FROM urunler WHERE urun_ismi = ? AND urun_kodu != ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param('si', $urun_ismi, $urun_kodu);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_stmt->close();

            if ($check_result->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Bu ürün ismi zaten mevcut. Lütfen farklı bir isim kullanın.'];
            } else {
                // Eski ürün adını almak için sorgu
                $old_product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
                $old_stmt = $connection->prepare($old_product_query);
                $old_stmt->bind_param('i', $urun_kodu);
                $old_stmt->execute();
                $old_result = $old_stmt->get_result();
                $old_product = $old_result->fetch_assoc();
                $old_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
                $old_stmt->close();

                $depo = $depo ?: NULL;
                $raf = $raf ?: NULL;

                // Eğer ürün tipi üretilen ise alış fiyatını 0 yap (sadece hazır alınan ürünlerde alış fiyatı olur)
                if ($urun_tipi === 'uretilen') {
                    $alis_fiyati = 0.0;
                    $alis_fiyati_para_birimi = 'TRY';
                } elseif ($urun_tipi === 'hazir_alinan') {
                    $rates = get_exchange_rates($connection);
                    $source_currency = normalize_currency_code($alis_fiyati_para_birimi);
                    $target_currency_calc = normalize_currency_code($satis_fiyati_para_birimi);
                    $target_currency_store = strtoupper(trim((string) $satis_fiyati_para_birimi));
                    if (!in_array($target_currency_store, ['TL', 'TRY', 'USD', 'EUR'], true)) {
                        $target_currency_store = $target_currency_calc;
                    }
                    $alis_fiyati = convert_currency_amount($alis_fiyati, $source_currency, $target_currency_calc, $rates);
                    $alis_fiyati_para_birimi = $target_currency_store;
                }

                // Hazır alınan ürünler için alış fiyatı kontrolü
                if ($urun_tipi === 'hazir_alinan' && $alis_fiyati <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Hazır alınan ürünler için alış fiyatı 0\'dan büyük olmalıdır.']);
                    exit;
                }

                $query = "UPDATE urunler SET urun_ismi = '" . $connection->real_escape_string($urun_ismi) . "', not_bilgisi = '" . $connection->real_escape_string($not_bilgisi) . "', stok_miktari = " . (int)$stok_miktari . ", birim = '" . $connection->real_escape_string($birim) . "', satis_fiyati = " . (float)$satis_fiyati . ", satis_fiyati_para_birimi = '" . $connection->real_escape_string($satis_fiyati_para_birimi) . "', alis_fiyati = " . (float)$alis_fiyati . ", alis_fiyati_para_birimi = '" . $connection->real_escape_string($alis_fiyati_para_birimi) . "', kritik_stok_seviyesi = " . (int)$kritik_stok_seviyesi . ", depo = " . ($depo ? "'" . $connection->real_escape_string($depo) . "'" : "NULL") . ", raf = " . ($raf ? "'" . $connection->real_escape_string($raf) . "'" : "NULL") . ", urun_tipi = '" . $connection->real_escape_string($urun_tipi) . "' WHERE urun_kodu = " . (int)$urun_kodu;

                if ($connection->query($query)) {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$old_product_name ürünü $urun_ismi olarak güncellendi", 'UPDATE');
                    $response = ['status' => 'success', 'message' => 'Ürün başarıyla güncellendi.'];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => normalize_negative_stock_error_message(
                            $connection->error,
                            'Veritabani hatasi: ' . $connection->error
                        )
                    ];
                }
            }
        }
    } elseif ($action == 'delete_product' && isset($_POST['urun_kodu'])) {
        if (!yetkisi_var('action:urunler:delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün silme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_POST['urun_kodu'];
        // Silinen ürün adını almak için sorgu
        $old_product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
        $old_stmt = $connection->prepare($old_product_query);
        $old_stmt->bind_param('i', $urun_kodu);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_product = $old_result->fetch_assoc();
        $deleted_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
        $old_stmt->close();

        // Ürüne ait fotoğrafları al ve fiziksel dosyaları sil
        $photo_query = "SELECT dosya_yolu FROM urun_fotograflari WHERE urun_kodu = ?";
        $photo_stmt = $connection->prepare($photo_query);
        $photo_stmt->bind_param('i', $urun_kodu);
        $photo_stmt->execute();
        $photo_result = $photo_stmt->get_result();

        while ($photo = $photo_result->fetch_assoc()) {
            $file_path = '../' . $photo['dosya_yolu'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $photo_stmt->close();

        // Ürüne ait fotoğraf kayıtlarını veritabanından sil
        $delete_photos_query = "DELETE FROM urun_fotograflari WHERE urun_kodu = ?";
        $delete_photos_stmt = $connection->prepare($delete_photos_query);
        $delete_photos_stmt->bind_param('i', $urun_kodu);
        $delete_photos_stmt->execute();
        $delete_photos_stmt->close();

        // Ürüne ait ürün ağacı kayıtlarını sil
        // Ürüne ait esans bileşenlerini bul ve onların da esans ağaçlarını temizle
        $essence_components_query = "
            SELECT e.esans_id, e.esans_kodu
            FROM urun_agaci ua
            JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
            WHERE ua.urun_kodu = ? AND ua.agac_turu = 'urun' AND LOWER(ua.bilesenin_malzeme_turu) = 'esans'
        ";
        $essence_stmt = $connection->prepare($essence_components_query);
        $essence_stmt->bind_param('i', $urun_kodu);
        $essence_stmt->execute();
        $essence_result = $essence_stmt->get_result();
        
        while ($essence = $essence_result->fetch_assoc()) {
            $esans_id = $essence['esans_id'];
            $esans_kodu = $essence['esans_kodu'];
            
            $delete_essence_tree_query = "DELETE FROM urun_agaci WHERE (urun_kodu = ? OR urun_kodu = ?) AND agac_turu = 'esans'";
            $del_ess_stmt = $connection->prepare($delete_essence_tree_query);
            $del_ess_stmt->bind_param('ss', $esans_id, $esans_kodu);
            $del_ess_stmt->execute();
            $del_ess_stmt->close();
        }
        $essence_stmt->close();

        $delete_tree_query = "DELETE FROM urun_agaci WHERE urun_kodu = ? AND agac_turu = 'urun'";
        $delete_tree_stmt = $connection->prepare($delete_tree_query);
        $delete_tree_stmt->bind_param('i', $urun_kodu);
        $delete_tree_stmt->execute();
        $delete_tree_stmt->close();

        $query = "DELETE FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        if ($stmt->execute()) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_product_name ürünü sistemden silindi", 'DELETE');
            $response = ['status' => 'success', 'message' => 'Ürün başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    } elseif ($action == 'quick_purchase') {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Yetkiniz yok.']);
            exit;
        }

        $tedarikci_id = (int) $_POST['tedarikci_id'];
        $urun_kodu = (int) $_POST['urun_kodu'];
        $miktar = (float) $_POST['miktar'];
        $birim_fiyat = (float) $_POST['birim_fiyat'];
        $para_birimi_raw = $_POST['para_birimi'] ?? 'TRY';
        $para_birimi = $connection->real_escape_string($para_birimi_raw);
        $para_birimi_norm = normalize_currency_code($para_birimi_raw);
        $tarih = $connection->real_escape_string($_POST['tarih']);
        $aciklama = $connection->real_escape_string($_POST['aciklama']);

        if ($miktar <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Miktar 0\'dan büyük olmalıdır.']);
            exit;
        }

        $connection->begin_transaction();
        try {
            // 1. Ürün Bilgilerini Al (Mevcut stok ve fiyat dahil)
            $u_query = "SELECT urun_ismi, urun_tipi, stok_miktari, alis_fiyati, alis_fiyati_para_birimi, satis_fiyati_para_birimi FROM urunler WHERE urun_kodu = $urun_kodu";
            $u_res = $connection->query($u_query);
            $urun = $u_res->fetch_assoc();
            if (!$urun) {
                throw new Exception('Ürün bulunamadı.');
            }
            $urun_ismi = $urun['urun_ismi'];
            $rates = get_exchange_rates($connection);
            
            // Ağırlıklı Ortalama Maliyet Hesabı
            $yeni_alis_fiyati = (float) $urun['alis_fiyati'];
            $mevcut_stok = (float)$urun['stok_miktari'];
            $mevcut_fiyat = (float)$urun['alis_fiyati'];
            
            if ($urun['urun_tipi'] === 'hazir_alinan') {
                $mevcut_fiyat_tl = convert_currency_amount($mevcut_fiyat, $urun['alis_fiyati_para_birimi'] ?? 'TRY', 'TRY', $rates);
                $yeni_birim_tl = convert_currency_amount($birim_fiyat, $para_birimi_norm, 'TRY', $rates);
                $toplam_deger = ($mevcut_stok * $mevcut_fiyat_tl) + ($miktar * $yeni_birim_tl);
                $toplam_miktar = $mevcut_stok + $miktar;
                
                if ($toplam_miktar > 0) {
                    $yeni_alis_fiyati_tl = $toplam_deger / $toplam_miktar;
                } else {
                    $yeni_alis_fiyati_tl = $yeni_birim_tl;
                }

                $satis_currency_raw = strtoupper(trim((string) ($urun['satis_fiyati_para_birimi'] ?? 'TRY')));
                if (!in_array($satis_currency_raw, ['TL', 'TRY', 'USD', 'EUR'], true)) {
                    $satis_currency_raw = 'TRY';
                }
                $hedef_currency_calc = normalize_currency_code($satis_currency_raw);
                $yeni_alis_fiyati = convert_currency_amount($yeni_alis_fiyati_tl, 'TRY', $hedef_currency_calc, $rates);
            }

            // 2. Tedarikçi Adını Al
            $t_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id";
            $t_res = $connection->query($t_query);
            $tedarikci_adi = $t_res->fetch_assoc()['tedarikci_adi'];

            // 3. Stok ve Fiyat Güncelle
            $fiyat_sql = ($urun['urun_tipi'] === 'hazir_alinan') 
                ? ", alis_fiyati = $yeni_alis_fiyati, alis_fiyati_para_birimi = '" . $connection->real_escape_string($satis_currency_raw) . "'" 
                : "";
            
            $connection->query("UPDATE urunler SET stok_miktari = stok_miktari + $miktar $fiyat_sql WHERE urun_kodu = $urun_kodu");

            // 4. Çerçeve Sözleşme Oluştur (Spot Alım İçin)
            $sozlesme_aciklama = "Spot Alım - " . $aciklama;
            $s_stmt = $connection->prepare("INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, olusturan, aciklama, oncelik, odeme_kasasi) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 1, 'TL')");
            $kullanici = $_SESSION['kullanici_adi'];
            $s_stmt->bind_param("isssdsdssss", $tedarikci_id, $tedarikci_adi, $urun_kodu, $urun_ismi, $birim_fiyat, $para_birimi, $miktar, $tarih, $tarih, $kullanici, $sozlesme_aciklama);
            $s_stmt->execute();
            $sozlesme_id = $connection->insert_id;
            $s_stmt->close();

            // 5. Genel Stok Hareketi Kaydı (ÖNCE BU YAPILMALI - FK İÇİN)
            $log_sql = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, miktar, yon, hareket_turu, aciklama, kaydeden_personel_id, tedarikci_id, tedarikci_ismi) VALUES (?, ?, ?, ?, 'giris', 'mal_kabul', ?, ?, ?, ?)";
            $log_stmt = $connection->prepare($log_sql);
            $log_aciklama = "Hızlı Satın Alma (Spot) [Sözleşme ID: $sozlesme_id] - " . $aciklama;
            $user_id = $_SESSION['user_id'];
            $stok_turu = 'urun';
            // Düzeltildi: sssdsiss (8 parametre)
            $log_stmt->bind_param("sssdsiss", $stok_turu, $urun_kodu, $urun_ismi, $miktar, $log_aciklama, $user_id, $tedarikci_id, $tedarikci_adi);
            $log_stmt->execute();
            $hareket_id = $connection->insert_id; // FK için ID alındı
            $log_stmt->close();

            // 6. Sözleşme Hareket Kaydı (Kasa Yönetimi ve FK için)
            $sh_stmt = $connection->prepare("INSERT INTO stok_hareketleri_sozlesmeler (hareket_id, sozlesme_id, malzeme_kodu, tarih, kullanilan_miktar, birim_fiyat, para_birimi, tedarikci_id, tedarikci_adi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sh_stmt->bind_param("iiisddsis", $hareket_id, $sozlesme_id, $urun_kodu, $tarih, $miktar, $birim_fiyat, $para_birimi, $tedarikci_id, $tedarikci_adi);
            $sh_stmt->execute();
            $sh_stmt->close();

            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'Satın alma işlemi başarıyla gerçekleşti. Stok güncellendi ve borç kaydı oluşturuldu.']);

        } catch (Exception $e) {
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
        }
        exit;

    } elseif ($action == 'count_products') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürünleri görüntüleme yetkiniz yok.']);
            exit;
        }

        $query = "SELECT COUNT(*) as total FROM urunler";
        $result = $connection->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'total' => (int) $row['total']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error]);
        }
        exit; // Exit here to prevent the final response
    }
}

$connection->close();
echo json_encode($response);
?>
