<?php
include '../config.php';

if (!function_exists('normalize_stock_direction')) {
    function normalize_stock_direction($yon)
    {
        $value = mb_strtolower(trim((string)$yon), 'UTF-8');
        if (in_array($value, ['giris', 'giriş'], true)) {
            return 'giris';
        }
        if (in_array($value, ['cikis', 'çıkış'], true)) {
            return 'cikis';
        }
        return null;
    }
}

if (!function_exists('normalize_stock_type')) {
    function normalize_stock_type($stok_turu)
    {
        $value = mb_strtolower(trim((string)$stok_turu), 'UTF-8');
        if (in_array($value, ['bileşen', 'bilesen'], true)) {
            return 'malzeme';
        }
        if (in_array($value, ['ürün', 'urun'], true)) {
            return 'urun';
        }
        if (in_array($value, ['malzeme', 'esans'], true)) {
            return $value;
        }
        return null;
    }
}

if (!function_exists('get_stock_table_info')) {
    function get_stock_table_info($stok_turu)
    {
        $normalized = normalize_stock_type($stok_turu);
        switch ($normalized) {
            case 'malzeme':
                return ['table' => 'malzemeler', 'code_column' => 'malzeme_kodu'];
            case 'esans':
                return ['table' => 'esanslar', 'code_column' => 'esans_kodu'];
            case 'urun':
                return ['table' => 'urunler', 'code_column' => 'urun_kodu'];
            default:
                return null;
        }
    }
}

if (!function_exists('stock_table_has_column')) {
    function stock_table_has_column($connection, $table_name, $column_name)
    {
        static $cache = [];

        $table_name = (string) $table_name;
        $column_name = (string) $column_name;
        $cache_key = $table_name . '.' . $column_name;

        if (array_key_exists($cache_key, $cache)) {
            return $cache[$cache_key];
        }

        $stmt = $connection->prepare(
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $cache[$cache_key] = false;
            return false;
        }

        $stmt->bind_param('ss', $table_name, $column_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $cache[$cache_key] = (bool) ($result && $result->num_rows > 0);
        $stmt->close();

        return $cache[$cache_key];
    }
}

if (!function_exists('normalize_stock_currency_code')) {
    function normalize_stock_currency_code($currency)
    {
        $currency = strtoupper(trim((string) $currency));
        if ($currency === 'TL' || $currency === '') {
            return 'TRY';
        }

        if (in_array($currency, ['TRY', 'USD', 'EUR'], true)) {
            return $currency;
        }

        return 'TRY';
    }
}

if (!function_exists('get_stock_exchange_rates')) {
    function get_stock_exchange_rates($connection)
    {
        static $cached_rates = null;
        if ($cached_rates !== null) {
            return $cached_rates;
        }

        $cached_rates = ['TRY' => 1.0, 'USD' => 1.0, 'EUR' => 1.0];
        $query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $value = (float) $row['ayar_deger'];
                if ($value <= 0) {
                    continue;
                }

                if ($row['ayar_anahtar'] === 'dolar_kuru') {
                    $cached_rates['USD'] = $value;
                } elseif ($row['ayar_anahtar'] === 'euro_kuru') {
                    $cached_rates['EUR'] = $value;
                }
            }
        }

        return $cached_rates;
    }
}

if (!function_exists('convert_stock_currency_amount')) {
    function convert_stock_currency_amount($amount, $from_currency, $to_currency, $rates)
    {
        $amount = (float) $amount;
        $from_currency = normalize_stock_currency_code($from_currency);
        $to_currency = normalize_stock_currency_code($to_currency);

        if ($from_currency === $to_currency) {
            return $amount;
        }

        $from_rate = isset($rates[$from_currency]) ? (float) $rates[$from_currency] : 1.0;
        $to_rate = isset($rates[$to_currency]) ? (float) $rates[$to_currency] : 1.0;

        if ($from_rate <= 0 || $to_rate <= 0) {
            return $amount;
        }

        $amount_try = $from_currency === 'TRY' ? $amount : ($amount * $from_rate);
        if ($to_currency === 'TRY') {
            return $amount_try;
        }

        return $amount_try / $to_rate;
    }
}

if (!function_exists('calculate_signed_stock_delta')) {
    function calculate_signed_stock_delta($miktar, $yon)
    {
        $amount = abs((float) $miktar);
        return normalize_stock_direction($yon) === 'giris' ? $amount : -$amount;
    }
}

if (!function_exists('apply_stock_quantity_delta')) {
    function apply_stock_quantity_delta($connection, $stok_turu, $kod, $signed_delta)
    {
        $stok_turu = normalize_stock_type($stok_turu);
        $table_info = get_stock_table_info($stok_turu);
        if (!$table_info) {
            throw new RuntimeException('Stok turu icin tablo bilgisi bulunamadi.');
        }

        $signed_delta = (float) $signed_delta;
        if ($signed_delta == 0.0) {
            return;
        }

        $escaped_kod = $connection->real_escape_string((string) $kod);
        if ($signed_delta < 0) {
            $required_stock = abs($signed_delta);
            $query = "UPDATE {$table_info['table']}
                      SET stok_miktari = stok_miktari - {$required_stock}
                      WHERE {$table_info['code_column']} = '{$escaped_kod}'
                        AND stok_miktari >= {$required_stock}";
            if (!$connection->query($query)) {
                throw new RuntimeException('Stok dusurulemedi: ' . $connection->error);
            }
            if ($connection->affected_rows !== 1) {
                throw new RuntimeException('Yetersiz stok.');
            }
            return;
        }

        $query = "UPDATE {$table_info['table']}
                  SET stok_miktari = stok_miktari + {$signed_delta}
                  WHERE {$table_info['code_column']} = '{$escaped_kod}'";
        if (!$connection->query($query)) {
            throw new RuntimeException('Stok artirilamadi: ' . $connection->error);
        }
        if ($connection->affected_rows !== 1) {
            throw new RuntimeException('Stok kaydi bulunamadi.');
        }
    }
}

if (!function_exists('is_expense_tracked_stock_movement')) {
    function is_expense_tracked_stock_movement($hareket_turu, $yon)
    {
        $normalized_direction = normalize_stock_direction($yon);
        return in_array($hareket_turu, ['fire', 'sayim_eksigi'], true) && $normalized_direction === 'cikis';
    }
}

if (!function_exists('get_theoretical_stock_expense_unit_cost')) {
    function get_theoretical_stock_expense_unit_cost($connection, $stok_turu, $kod)
    {
        $stok_turu = normalize_stock_type($stok_turu);
        $escaped_kod = $connection->real_escape_string((string) $kod);

        switch ($stok_turu) {
            case 'malzeme':
                $query = "SELECT alis_fiyati FROM malzemeler WHERE malzeme_kodu = '{$escaped_kod}'";
                break;
            case 'urun':
                $query = "SELECT teorik_maliyet FROM v_urun_maliyetleri WHERE urun_kodu = '{$escaped_kod}'";
                break;
            case 'esans':
                $query = "SELECT toplam_maliyet FROM v_esans_maliyetleri WHERE esans_kodu = '{$escaped_kod}'";
                break;
            default:
                return 0.0;
        }

        $result = @$connection->query($query);
        if (!$result) {
            return 0.0;
        }

        $row = $result->fetch_assoc();
        if (!$row) {
            return 0.0;
        }

        $value = reset($row);
        return (float) $value;
    }
}

if (!function_exists('delete_stock_expense_record')) {
    function delete_stock_expense_record($connection, $hareket_id)
    {
        $hareket_id = (int) $hareket_id;
        $connection->query("DELETE FROM gider_yonetimi WHERE fatura_no = 'Fire_Kaydi_{$hareket_id}'");
    }
}

if (!function_exists('upsert_stock_expense_record')) {
    function upsert_stock_expense_record($connection, $hareket_id, $stok_turu, $kod, $item_name, $item_unit, $miktar, $hareket_turu)
    {
        if (!is_expense_tracked_stock_movement($hareket_turu, 'cikis')) {
            delete_stock_expense_record($connection, $hareket_id);
            return;
        }

        $unit_cost = get_theoretical_stock_expense_unit_cost($connection, $stok_turu, $kod);
        $total_cost = abs((float) $miktar) * $unit_cost;
        if ($total_cost <= 0) {
            delete_stock_expense_record($connection, $hareket_id);
            return;
        }

        $expense_date = date('Y-m-d');
        $personel_id = (int) $_SESSION['user_id'];
        $personel_adi = $connection->real_escape_string((string) $_SESSION['kullanici_adi']);
        $item_name = $connection->real_escape_string((string) $item_name);
        $item_unit = $connection->real_escape_string((string) $item_unit);
        $kod = $connection->real_escape_string((string) $kod);
        $category = $hareket_turu === 'fire' ? 'Fire Gideri' : 'Stok Gideri';
        $category = $connection->real_escape_string($category);
        $description = $connection->real_escape_string("Stok gideri - {$item_name} - {$miktar} {$item_unit} ({$kod})");

        $check_query = "SELECT gider_id FROM gider_yonetimi WHERE fatura_no = 'Fire_Kaydi_{$hareket_id}'";
        $check_result = $connection->query($check_query);

        if ($check_result && $check_result->num_rows > 0) {
            $expense_query = "UPDATE gider_yonetimi
                              SET tarih = '{$expense_date}',
                                  tutar = {$total_cost},
                                  kategori = '{$category}',
                                  aciklama = '{$description}',
                                  kaydeden_personel_id = {$personel_id},
                                  kaydeden_personel_ismi = '{$personel_adi}'
                              WHERE fatura_no = 'Fire_Kaydi_{$hareket_id}'";
        } else {
            $expense_query = "INSERT INTO gider_yonetimi
                              (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi, odeme_yapilan_firma)
                              VALUES ('{$expense_date}', {$total_cost}, '{$category}', '{$description}', {$personel_id}, '{$personel_adi}', 'Fire_Kaydi_{$hareket_id}', 'Diger', 'Ic Gider')";
        }

        if (!$connection->query($expense_query)) {
            throw new RuntimeException('Gider kaydi guncellenemedi: ' . $connection->error);
        }
    }
}

if (!function_exists('find_purchase_order_id_for_movement')) {
    function find_purchase_order_id_for_movement($connection, array $movement)
    {
        $stored_id = isset($movement['satinalma_siparis_id']) ? (int) $movement['satinalma_siparis_id'] : 0;
        if ($stored_id > 0) {
            return $stored_id;
        }

        $description = (string) ($movement['aciklama'] ?? '');
        if (!preg_match('/\[Sip(?:aris|ariş):\s*([^\]]+)\]/u', $description, $matches)) {
            return 0;
        }

        $siparis_no = trim($matches[1]);
        if ($siparis_no === '') {
            return 0;
        }

        $stmt = $connection->prepare("SELECT siparis_id FROM satinalma_siparisler WHERE siparis_no = ? LIMIT 1");
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('s', $siparis_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ? (int) $row['siparis_id'] : 0;
    }
}

if (!function_exists('sync_purchase_order_delivery_status')) {
    function sync_purchase_order_delivery_status($connection, $siparis_id)
    {
        $siparis_id = (int) $siparis_id;
        if ($siparis_id <= 0) {
            return;
        }

        $stmt = $connection->prepare(
            "SELECT ss.durum,
                    COUNT(*) AS total_items,
                    SUM(CASE WHEN ssk.teslim_edilen_miktar >= ssk.miktar THEN 1 ELSE 0 END) AS completed_items,
                    COALESCE(SUM(ssk.teslim_edilen_miktar), 0) AS total_delivered
             FROM satinalma_siparisler ss
             JOIN satinalma_siparis_kalemleri ssk ON ss.siparis_id = ssk.siparis_id
             WHERE ss.siparis_id = ?
             GROUP BY ss.siparis_id, ss.durum"
        );
        if (!$stmt) {
            throw new RuntimeException('Siparis durum kontrolu hazirlanamadi: ' . $connection->error);
        }

        $stmt->bind_param('i', $siparis_id);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Siparis durum kontrolu calistirilamadi: ' . $error);
        }

        $result = $stmt->get_result();
        $status_row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!$status_row) {
            return;
        }

        $current_status = (string) $status_row['durum'];
        if (in_array($current_status, ['iptal', 'kapatildi'], true)) {
            return;
        }

        $new_status = $current_status;
        if ((int) $status_row['total_items'] > 0 && (int) $status_row['completed_items'] === (int) $status_row['total_items']) {
            $new_status = 'tamamlandi';
        } elseif ((float) $status_row['total_delivered'] > 0) {
            $new_status = 'kismen_teslim';
        } elseif (in_array($current_status, ['kismen_teslim', 'tamamlandi'], true)) {
            $new_status = 'gonderildi';
        }

        if ($new_status === $current_status) {
            return;
        }

        $update_stmt = $connection->prepare("UPDATE satinalma_siparisler SET durum = ? WHERE siparis_id = ?");
        if (!$update_stmt) {
            throw new RuntimeException('Siparis durum guncelleme sorgusu hazirlanamadi: ' . $connection->error);
        }

        $update_stmt->bind_param('si', $new_status, $siparis_id);
        if (!$update_stmt->execute()) {
            $error = $update_stmt->error;
            $update_stmt->close();
            throw new RuntimeException('Siparis durumu guncellenemedi: ' . $error);
        }
        $update_stmt->close();
    }
}

if (!function_exists('update_purchase_order_delivery_quantity')) {
    function update_purchase_order_delivery_quantity($connection, $siparis_id, $malzeme_kodu, $delta)
    {
        $siparis_id = (int) $siparis_id;
        $malzeme_kodu = (int) $malzeme_kodu;
        $delta = (float) $delta;

        if ($siparis_id <= 0 || $malzeme_kodu <= 0 || $delta == 0.0) {
            return;
        }

        if ($delta > 0) {
            $stmt = $connection->prepare(
                "UPDATE satinalma_siparis_kalemleri
                 SET teslim_edilen_miktar = teslim_edilen_miktar + ?
                 WHERE siparis_id = ? AND malzeme_kodu = ?
                   AND (miktar - teslim_edilen_miktar) >= ?"
            );
            if (!$stmt) {
                throw new RuntimeException('Siparis kalemi guncelleme sorgusu hazirlanamadi: ' . $connection->error);
            }
            $stmt->bind_param('diid', $delta, $siparis_id, $malzeme_kodu, $delta);
        } else {
            $abs_delta = abs($delta);
            $stmt = $connection->prepare(
                "UPDATE satinalma_siparis_kalemleri
                 SET teslim_edilen_miktar = teslim_edilen_miktar - ?
                 WHERE siparis_id = ? AND malzeme_kodu = ? AND teslim_edilen_miktar >= ?"
            );
            if (!$stmt) {
                throw new RuntimeException('Siparis kalemi geri sarma sorgusu hazirlanamadi: ' . $connection->error);
            }
            $stmt->bind_param('diid', $abs_delta, $siparis_id, $malzeme_kodu, $abs_delta);
        }

        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Bagli siparis kalemi guncellenemedi veya siparis miktari asildi: ' . $error);
        }
        $stmt->close();

        sync_purchase_order_delivery_status($connection, $siparis_id);
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_locations':
        $locations = [];
        $query = "SELECT DISTINCT depo_ismi FROM lokasyonlar ORDER BY depo_ismi";

        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // For each unique depot, get all shelves
                $shelves_query = "SELECT DISTINCT raf FROM lokasyonlar WHERE depo_ismi = ? ORDER BY raf";
                $shelves_stmt = $connection->prepare($shelves_query);
                $shelves_stmt->bind_param('s', $row['depo_ismi']);
                $shelves_stmt->execute();
                $shelves_result = $shelves_stmt->get_result();
                
                $shelves = [];
                while ($shelf = $shelves_result->fetch_assoc()) {
                    $shelves[] = $shelf['raf'];
                }
                
                $locations[] = [
                    'depo_ismi' => $row['depo_ismi'],
                    'raflar' => $shelves
                ];
                
                $shelves_stmt->close();
            }
        }

        echo json_encode(['status' => 'success', 'data' => $locations]);
        break;

    case 'get_current_location':
        $stock_type = $_GET['stock_type'] ?? '';
        $item_code = $_GET['item_code'] ?? '';

        // Escape user inputs to prevent SQL injection
        $stock_type = $connection->real_escape_string($stock_type);
        $item_code = $connection->real_escape_string($item_code);

        if (!$stock_type || !$item_code) {
            echo json_encode(['status' => 'error', 'message' => 'Stok türü ve ürün kodu belirtilmedi.']);
            break;
        }

        $location_data = null;

        // Get the location directly from the main item table based on stock type
        switch ($stock_type) {
            case 'malzeme':
                $query = "SELECT malzeme_kodu as kod, malzeme_ismi as isim, stok_miktari, depo, raf FROM malzemeler WHERE malzeme_kodu = '$item_code'";
                $result = $connection->query($query);

                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    $location_data = [
                        'depo' => $item['depo'],
                        'raf' => $item['raf'],
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            case 'esans':
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari, tank_kodu, tank_ismi FROM esanslar WHERE esans_kodu = '$item_code'";
                $result = $connection->query($query);

                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    // For essences, return the tank information from the esanslar table
                    $location_data = [
                        'tank_kodu' => $item['tank_kodu'] ?? null,
                        'tank_ismi' => $item['tank_ismi'] ?? null,
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            case 'urun':
                $query = "SELECT urun_kodu as kod, urun_ismi as isim, stok_miktari, depo, raf FROM urunler WHERE urun_kodu = '$item_code'";
                $result = $connection->query($query);

                if ($result && $result->num_rows > 0) {
                    $item = $result->fetch_assoc();
                    $location_data = [
                        'depo' => $item['depo'],
                        'raf' => $item['raf'],
                        'stok_miktari' => floatval($item['stok_miktari'])
                    ];
                }
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                break;
        }

        if ($location_data) {
            echo json_encode(['status' => 'success', 'data' => $location_data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ürün bulunamadı.']);
        }
        break;

    case 'get_stock_items':
        $type = $_GET['type'] ?? '';

        if (!$type) {
            echo json_encode(['status' => 'error', 'message' => 'Stok türü belirtilmedi.']);
            break;
        }

        $items = [];
        switch ($type) {
            case 'malzeme':
                $query = "SELECT malzeme_kodu as kod, malzeme_ismi as isim, stok_miktari as stok FROM malzemeler ORDER BY malzeme_ismi";
                break;
            case 'esans':
                $query = "SELECT esans_kodu as kod, esans_ismi as isim, stok_miktari as stok, tank_kodu, tank_ismi FROM esanslar ORDER BY esans_ismi";
                break;
            case 'urun':
                $query = "SELECT urun_kodu as kod, urun_ismi as isim, stok_miktari as stok FROM urunler ORDER BY urun_ismi";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $result = $connection->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $items]);
        break;

    case 'get_suppliers_for_material':
        $material_code = $_GET['material_code'] ?? '';

        if (!$material_code) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu belirtilmedi.']);
            break;
        }

        // Get distinct suppliers for the material from cerceve_sozlesmeler table
        $query = "SELECT DISTINCT t.tedarikci_id, t.tedarikci_adi AS tedarikci_ismi 
                  FROM cerceve_sozlesmeler cs
                  JOIN tedarikciler t ON cs.tedarikci_adi = t.tedarikci_adi
                  WHERE cs.malzeme_kodu = ? 
                  ORDER BY t.tedarikci_adi";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('s', $material_code);
        $stmt->execute();
        $result = $stmt->get_result();

        $suppliers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $suppliers]);
        break;

    case 'get_movement':
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Hareket ID belirtilmedi.']);
            break;
        }

        $query = "SELECT * FROM stok_hareket_kayitlari WHERE hareket_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $movement = $result->fetch_assoc();
            // Normalize the 'yon' field to ensure consistent values
            // Handle Turkish specific variations: 'Giriş' stays as 'Giriş', 'Çıkış' stays as 'Çıkış'
            $yon_value = $movement['yon'];
            // Convert to lowercase for comparison but preserve original case for display
            $yon_lower = mb_strtolower($yon_value, 'UTF-8');

            // If lowercase contains 'giris' or 'giriş', set as 'Giriş'
            if ($yon_lower === 'giris' || $yon_lower === 'giriş') {
                $movement['yon'] = 'Giriş';
            }
            // If lowercase contains 'cikis' or 'çıkış', set as 'Çıkış'
            elseif ($yon_lower === 'cikis' || $yon_lower === 'çıkış') {
                $movement['yon'] = 'Çıkış';
            }
            // Otherwise, keep original value
            else {
                $movement['yon'] = $yon_value;
            }

            echo json_encode(['status' => 'success', 'data' => $movement]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi bulunamadı.']);
        }
        $stmt->close();
        break;

    case 'get_all_movements':
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 25;

        if ($per_page < 1) {
            $per_page = 10;
        }
        $per_page = min($per_page, 200);

        if ($page < 1) {
            $page = 1;
        }

        $total = 0;
        $count_query = "SELECT COUNT(*) AS total FROM stok_hareket_kayitlari";
        $count_result = $connection->query($count_query);
        if ($count_result) {
            $count_row = $count_result->fetch_assoc();
            $total = isset($count_row['total']) ? (int) $count_row['total'] : 0;
        }

        $max_page = $total > 0 ? (int) ceil($total / $per_page) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }

        $offset = ($page - 1) * $per_page;

        $query = "SELECT * FROM stok_hareket_kayitlari ORDER BY tarih DESC LIMIT ? OFFSET ?";
        $stmt = $connection->prepare($query);

        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Hareketler al��namad��: ' . $connection->error]);
            break;
        }

        $stmt->bind_param('ii', $per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $movements = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Normalize the 'yon' field to ensure consistent values
                // Handle Turkish specific variations: 'Giriş' stays as 'Giriş', 'Çıkış' stays as 'Çıkış'
                $yon_value = $row['yon'];
                // Convert to lowercase for comparison but preserve original case for display
                $yon_lower = mb_strtolower($yon_value, 'UTF-8');

                // If lowercase contains 'giris' or 'giriş', set as 'Giriş'
                if ($yon_lower === 'giris' || $yon_lower === 'giriş') {
                    $row['yon'] = 'Giriş';
                }
                // If lowercase contains 'cikis' or 'çıkış', set as 'Çıkış'
                elseif ($yon_lower === 'cikis' || $yon_lower === 'çıkış') {
                    $row['yon'] = 'Çıkış';
                }
                // Otherwise, keep original value
                else {
                    $row['yon'] = $yon_value;
                }

                $movements[] = $row;
            }
        }
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'data' => $movements,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ]);
        break;

    case 'get_total_movements':
        $query = "SELECT COUNT(*) as total FROM stok_hareket_kayitlari";
        $result = $connection->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => intval($row['total'])]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Toplam hareket sayısı alınamadı.']);
        }
        break;

    case 'add_movement':
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $yon = $_POST['yon'] ?? '';
        $hareket_turu = $_POST['hareket_turu'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';
        $cerceve_sozlesme_id = $_POST['cerceve_sozlesme_id'] ?? '';
        $fatura_no = $_POST['fatura_no'] ?? '';
        $tedarikci = $_POST['tedarikci'] ?? '';

        $stok_turu = normalize_stock_type($stok_turu);
        $yon = normalize_stock_direction($yon);
        if (!$stok_turu) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz stok turu.']);
            break;
        }
        if (!$yon) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz yon bilgisi.', 'error_code' => 'INVALID_DIRECTION']);
            break;
        }

        // Validation
        if (!$stok_turu || !$kod || !$miktar || !$yon || !$hareket_turu || !$aciklama) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // For 'mal_kabul' action, ensure we have a supplier
        if ($hareket_turu === 'mal_kabul' && empty($tedarikci)) {
            echo json_encode(['status' => 'error', 'message' => 'Mal kabul işlemi için tedarikçi seçilmelidir.']);
            break;
        }

        // Get item name and unit based on stock type
        $item_name = '';
        $item_unit = '';

        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('s', $kod);
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'] ?? $item['esans_ismi'] ?? $item['urun_ismi'] ?? '';
            $item_unit = $item['birim'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz kod.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        // Insert stock movement - converted from prepared statement to direct query
        $stok_turu = $connection->real_escape_string($stok_turu);
        $kod = $connection->real_escape_string($kod);
        $item_name = $connection->real_escape_string($item_name);
        $item_unit = $connection->real_escape_string($item_unit);
        $miktar = floatval($miktar);
        $yon = $connection->real_escape_string($yon);
        $hareket_turu = $connection->real_escape_string($hareket_turu);
        $depo = $connection->real_escape_string($depo);
        $raf = $connection->real_escape_string($raf);
        $tank_kodu = $connection->real_escape_string($tank_kodu);
        $ilgili_belge_no = $connection->real_escape_string($ilgili_belge_no);
        $aciklama = $connection->real_escape_string($aciklama);
        $kaydeden_personel_id = intval($_SESSION['user_id']);
        $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi']);
        $tedarikci = $connection->real_escape_string($tedarikci);

        $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi) VALUES ('$stok_turu', '$kod', '$item_name', '$item_unit', $miktar, '$yon', '$hareket_turu', '$depo', '$raf', '$tank_kodu', '$ilgili_belge_no', '$aciklama', $kaydeden_personel_id, '$kaydeden_personel_adi', '$tedarikci')";

        if ($connection->query($movement_query)) {
            $hareket_id = $connection->insert_id;

            // Update stock based on movement
            $direction = ($yon === 'giris') ? $miktar : -$miktar;
            $required_stock = abs(floatval($direction));

            // Update stock based on movement type
            $direction = floatval($direction);
            $escaped_kod = $connection->real_escape_string($kod);

            switch ($stok_turu) {
                case 'malzeme':
                    if ($direction < 0) {
                        $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari - $required_stock WHERE malzeme_kodu = '$escaped_kod' AND stok_miktari >= $required_stock";
                    } else {
                        $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + $direction WHERE malzeme_kodu = '$escaped_kod'";
                    }
                    $result = $connection->query($stock_query);
                    break;
                case 'esans':
                    if ($direction < 0) {
                        $stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari - $required_stock WHERE esans_kodu = '$escaped_kod' AND stok_miktari >= $required_stock";
                    } else {
                        $stock_query = "UPDATE esanslar SET stok_miktari = stok_miktari + $direction WHERE esans_kodu = '$escaped_kod'";
                    }
                    $result = $connection->query($stock_query);
                    break;
                case 'urun':
                    if ($direction < 0) {
                        $stock_query = "UPDATE urunler SET stok_miktari = stok_miktari - $required_stock WHERE urun_kodu = '$escaped_kod' AND stok_miktari >= $required_stock";
                    } else {
                        $stock_query = "UPDATE urunler SET stok_miktari = stok_miktari + $direction WHERE urun_kodu = '$escaped_kod'";
                    }
                    $result = $connection->query($stock_query);
                    break;
            }
            if ($result && $direction < 0 && $connection->affected_rows === 0) {
                $result = false;
            }

            if (isset($result) && $result) {
                // If this movement is a fire (waste) with exit direction, create an expense record
                if ($hareket_turu === 'fire' && $yon === 'cikis') {
                    // Get the theoretical cost for the item based on its stock type
                    $theoretical_cost = 0;
                    $item_description = $item_name;

                    switch ($stok_turu) {
                        case 'malzeme':
                            // For malzeme, we expect alis_fiyati to exist based on our earlier code
                            $cost_query = "SELECT alis_fiyati FROM malzemeler WHERE malzeme_kodu = '$kod'";
                            $cost_result = $connection->query($cost_query);
                            if ($cost_result && $cost_row = $cost_result->fetch_assoc()) {
                                $theoretical_cost = floatval($cost_row['alis_fiyati']);
                            }
                            break;
                        case 'urun':
                            // For urun, use v_urun_maliyetleri view
                            $cost_query = "SELECT teorik_maliyet FROM v_urun_maliyetleri WHERE urun_kodu = '$kod'";
                            $cost_result = $connection->query($cost_query);
                            if ($cost_result) {
                                if ($cost_row = $cost_result->fetch_assoc()) {
                                    $theoretical_cost = floatval($cost_row['teorik_maliyet']);
                                }
                            }
                            break;
                        case 'esans':
                            // For esans, use v_esans_maliyetleri view
                            $cost_query = "SELECT toplam_maliyet FROM v_esans_maliyetleri WHERE esans_kodu = '$kod'";
                            $cost_result = @$connection->query($cost_query);
                            if ($cost_result && $cost_row = $cost_result->fetch_assoc()) {
                                $theoretical_cost = floatval($cost_row['toplam_maliyet']);
                            }
                            break;
                    }

                    // Calculate the total cost (miktar * theoretical cost)
                    $total_cost = $miktar * $theoretical_cost;

                    if ($total_cost > 0) {
                        // Insert expense record
                        $expense_description = "Fire kaydı - " . $item_name . " - " . $miktar . " " . $item_unit . " (" . $kod . ")";
                        $expense_tarih = date('Y-m-d');
                        $personel_id = intval($_SESSION['user_id']);
                        $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi']);

                        $expense_query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi, odeme_yapilan_firma) VALUES ('$expense_tarih', $total_cost, 'Fire Gideri', '$expense_description', $personel_id, '$personel_adi', 'Fire_Kaydi_$hareket_id', 'Diğer', 'İç Gider')";

                        if ($connection->query($expense_query)) {
                            // Log ekleme
                            log_islem($connection, $_SESSION['kullanici_adi'], "$item_name için $miktar $item_unit stok hareketi ve ilgili gider kaydı eklendi", 'CREATE');
                            echo json_encode(['status' => 'success', 'message' => 'Stok hareketi ve ilgili gider kaydı başarıyla kaydedildi.']);
                        } else {
                            // Log ekleme
                            log_islem($connection, $_SESSION['kullanici_adi'], "$item_name için $miktar $item_unit stok hareketi eklendi", 'CREATE');
                            // If expense creation fails, still indicate success for the stock movement
                            echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla kaydedildi ancak gider kaydı oluşturulurken hata oluştu: ' . $connection->error]);
                        }
                    } else {
                        // Log ekleme
                        log_islem($connection, $_SESSION['kullanici_adi'], "$item_name için $miktar $item_unit stok hareketi eklendi", 'CREATE');
                        echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla kaydedildi. (Maliyet 0 hesaplandı)']);
                    }
                } else {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$item_name için $miktar $item_unit stok hareketi eklendi", 'CREATE');
                    echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla kaydedildi.']);
                }
            } else {
                $connection->query("DELETE FROM stok_hareket_kayitlari WHERE hareket_id = " . intval($hareket_id));
                $stock_error = $connection->error;
                $error_message = $stock_error !== ''
                    ? normalize_negative_stock_error_message($stock_error, 'Yetersiz stok: hareket kaydi iptal edildi.')
                    : 'Yetersiz stok: hareket kaydi iptal edildi.';
                echo json_encode([
                    'status' => 'error',
                    'message' => $error_message,
                    'error_code' => $stock_error !== '' && is_negative_stock_guard_error($stock_error)
                        ? 'NEGATIVE_STOCK_BLOCKED'
                        : 'INSUFFICIENT_STOCK'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok hareketi kaydedilirken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'update_movement':
        $hareket_id = $_POST['hareket_id'] ?? 0;
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $yon = $_POST['yon'] ?? '';
        $hareket_turu = $_POST['hareket_turu'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';
        $tedarikci = $_POST['tedarikci'] ?? '';

        $stok_turu = normalize_stock_type($stok_turu);
        $yon = normalize_stock_direction($yon);
        if (!$stok_turu) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz stok turu.']);
            break;
        }
        if (!$yon) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz yon bilgisi.', 'error_code' => 'INVALID_DIRECTION']);
            break;
        }

        // Validation
        if (!$hareket_id || !$stok_turu || !$kod || !$miktar || !$yon || !$hareket_turu || !$aciklama) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        if (in_array($hareket_turu, ['mal_kabul', 'transfer'], true)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Mal kabul ve transfer hareketleri duzenlenemez. Gerekirse ilgili islemi geri alip yeniden olusturun.'
            ]);
            break;
        }

        // Get item name and unit based on stock type
        $item_name = '';
        $item_unit = '';

        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('s', $kod);
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = ?";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->bind_param('i', $kod);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü.']);
                exit;
        }

        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'] ?? $item['esans_ismi'] ?? $item['urun_ismi'] ?? '';
            $item_unit = $item['birim'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz kod.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        // Update stock movement - converted from prepared statement to direct query
        $stok_turu = $connection->real_escape_string($stok_turu);
        $kod = $connection->real_escape_string($kod);
        $item_name = $connection->real_escape_string($item_name);
        $item_unit = $connection->real_escape_string($item_unit);
        $miktar = floatval($miktar);
        $yon = $connection->real_escape_string($yon);
        $hareket_turu = $connection->real_escape_string($hareket_turu);
        $depo = $connection->real_escape_string($depo);
        $raf = $connection->real_escape_string($raf);
        $tank_kodu = $connection->real_escape_string($tank_kodu);
        $ilgili_belge_no = $connection->real_escape_string($ilgili_belge_no);
        $aciklama = $connection->real_escape_string($aciklama);
        $tedarikci = $connection->real_escape_string($tedarikci);
        $hareket_id = intval($hareket_id);

        $connection->autocommit(FALSE);

        try {
            $existing_stmt = $connection->prepare("SELECT * FROM stok_hareket_kayitlari WHERE hareket_id = ? FOR UPDATE");
            if (!$existing_stmt) {
                throw new Exception('Mevcut hareket sorgusu hazirlanamadi: ' . $connection->error);
            }

            $existing_stmt->bind_param('i', $hareket_id);
            $existing_stmt->execute();
            $existing_result = $existing_stmt->get_result();
            $existing_movement = $existing_result ? $existing_result->fetch_assoc() : null;
            $existing_stmt->close();

            if (!$existing_movement) {
                throw new Exception('Guncellenecek stok hareketi bulunamadi.');
            }

            if (in_array($existing_movement['hareket_turu'], ['mal_kabul', 'transfer'], true)) {
                throw new Exception('Mal kabul ve transfer hareketleri duzenlenemez.');
            }

            $old_type = normalize_stock_type($existing_movement['stok_turu']);
            $old_direction = normalize_stock_direction($existing_movement['yon']);
            if (!$old_type || !$old_direction) {
                throw new Exception('Mevcut stok hareketi turu veya yonu gecersiz.');
            }

            $old_delta = calculate_signed_stock_delta($existing_movement['miktar'], $old_direction);
            apply_stock_quantity_delta($connection, $old_type, $existing_movement['kod'], -$old_delta);

            $movement_query = "UPDATE stok_hareket_kayitlari
                               SET stok_turu = '$stok_turu',
                                   kod = '$kod',
                                   isim = '$item_name',
                                   birim = '$item_unit',
                                   miktar = $miktar,
                                   yon = '$yon',
                                   hareket_turu = '$hareket_turu',
                                   depo = '$depo',
                                   raf = '$raf',
                                   tank_kodu = '$tank_kodu',
                                   ilgili_belge_no = '$ilgili_belge_no',
                                   aciklama = '$aciklama',
                                   tedarikci_ismi = '$tedarikci'
                               WHERE hareket_id = $hareket_id";

            if (!$connection->query($movement_query)) {
                throw new Exception('Stok hareketi guncellenirken hata olustu: ' . $connection->error);
            }

            $new_delta = calculate_signed_stock_delta($miktar, $yon);
            apply_stock_quantity_delta($connection, $stok_turu, $kod, $new_delta);

            if (is_expense_tracked_stock_movement($hareket_turu, $yon)) {
                upsert_stock_expense_record($connection, $hareket_id, $stok_turu, $kod, $item_name, $item_unit, $miktar, $hareket_turu);
                log_islem($connection, $_SESSION['kullanici_adi'], "Stok hareketi guncellendi: $item_name - $miktar $item_unit (gider kaydi ile)", 'UPDATE');
                $message = 'Stok hareketi ve ilgili gider kaydi basariyla guncellendi.';
            } else {
                delete_stock_expense_record($connection, $hareket_id);
                log_islem($connection, $_SESSION['kullanici_adi'], "Stok hareketi guncellendi: $item_name - $miktar $item_unit", 'UPDATE');
                $message = 'Stok hareketi basariyla guncellendi.';
            }

            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => $message]);
        } catch (Throwable $e) {
            $connection->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => normalize_negative_stock_error_message($e->getMessage(), 'Stok hareketi guncellenemedi.')
            ]);
        }

        $connection->autocommit(TRUE);
        break;

    case 'delete_movement':
        $hareket_id = $_POST['hareket_id'] ?? 0;

        if (!$hareket_id) {
            echo json_encode(['status' => 'error', 'message' => 'Hareket ID belirtilmedi.']);
            break;
        }

        $connection->autocommit(FALSE); // Start transaction

        try {
            $hareket_id = intval($hareket_id);

            // 1. Get all necessary data BEFORE making any changes
            $movement_query = "SELECT * FROM stok_hareket_kayitlari WHERE hareket_id = $hareket_id";
            $movement_result = $connection->query($movement_query);
            if (!$movement_result || $movement_result->num_rows === 0) {
                throw new Exception('Stok hareketi bulunamadı.');
            }
            $movement = $movement_result->fetch_assoc();

            $malzeme_kodu = $movement['kod'];
            $normalized_movement_type = normalize_stock_type($movement['stok_turu']);
            $normalized_movement_direction = normalize_stock_direction($movement['yon']);
            if (!$normalized_movement_type || !$normalized_movement_direction) {
                throw new Exception('Gecersiz stok hareketi turu/yonu.');
            }
            if ($movement['hareket_turu'] === 'transfer') {
                throw new Exception('Transfer hareketleri tek satir olarak silinemez. Dengelemek icin ters transfer olusturun.');
            }
            $is_mal_kabul = ($normalized_movement_type === 'malzeme' && $movement['hareket_turu'] === 'mal_kabul');

            $deleted_miktar = (float)$movement['miktar'];
            $deleted_birim_fiyat = 0;
            $deleted_para_birimi = 'TRY';
            $mevcut_stok = 0;
            $mevcut_alis_fiyati = 0;
            $mevcut_para_birimi = 'TRY';
            $maliyet_manuel_girildi = false;
            $linked_purchase_order_id = 0;

            if ($is_mal_kabul) {
                $contract_details_query = "SELECT birim_fiyat, para_birimi FROM stok_hareketleri_sozlesmeler WHERE hareket_id = $hareket_id";
                $contract_result = $connection->query($contract_details_query);
                if ($contract_result && $contract_result->num_rows > 0) {
                    $contract_details = $contract_result->fetch_assoc();
                    $deleted_birim_fiyat = (float)$contract_details['birim_fiyat'];
                    $deleted_para_birimi = normalize_stock_currency_code($contract_details['para_birimi'] ?? 'TRY');
                }

                $escaped_malzeme_kodu = $connection->real_escape_string($malzeme_kodu);
                $material_query = "SELECT stok_miktari, alis_fiyati, para_birimi, maliyet_manuel_girildi FROM malzemeler WHERE malzeme_kodu = '$escaped_malzeme_kodu'";
                $material_result = $connection->query($material_query);
                if (!$material_result || $material_result->num_rows === 0) {
                    throw new Exception('Mal kabul ile baglantili malzeme bulunamadi.');
                }
                $material_data = $material_result->fetch_assoc();
                $mevcut_stok = (float)$material_data['stok_miktari'];
                $mevcut_alis_fiyati = (float)$material_data['alis_fiyati'];
                $mevcut_para_birimi = normalize_stock_currency_code($material_data['para_birimi'] ?? 'TRY');
                $maliyet_manuel_girildi = (bool)($material_data['maliyet_manuel_girildi'] ?? false);
                $linked_purchase_order_id = find_purchase_order_id_for_movement($connection, $movement);
            }

            // 2. Perform all database writes
            // Delete from child table first
            $contract_delete_query = "DELETE FROM stok_hareketleri_sozlesmeler WHERE hareket_id = $hareket_id";
            if (!$connection->query($contract_delete_query)) {
                throw new Exception('Sözleşme bağlantısı silinirken hata oluştu: ' . $connection->error);
            }

            // Check if this is a fire or sayım eksigi movement to handle expense deletion
            $is_fire_or_shortage = (($movement['hareket_turu'] === 'fire' || $movement['hareket_turu'] === 'sayim_eksigi') && $normalized_movement_direction === 'cikis');

            // Delete from main movement table
            $delete_query = "DELETE FROM stok_hareket_kayitlari WHERE hareket_id = $hareket_id";
            if (!$connection->query($delete_query)) {
                throw new Exception('Stok hareketi silinirken hata oluştu: ' . $connection->error);
            }

            // If this was a fire or sayım eksigi movement, delete the associated expense record
            if ($is_fire_or_shortage) {
                delete_stock_expense_record($connection, $hareket_id);
            }

            // 3. Update material stock and cost
            if ($is_mal_kabul) {
                $yeni_stok = $mevcut_stok - $deleted_miktar;
                if ($yeni_stok < 0) {
                    throw new Exception('Yetersiz stok: mal kabul silme islemi mevcut stogu negatife dusurur.');
                }
                $rates = get_stock_exchange_rates($connection);
                $mevcut_birim_maliyet_try = convert_stock_currency_amount($mevcut_alis_fiyati, $mevcut_para_birimi, 'TRY', $rates);
                $deleted_birim_fiyat_try = convert_stock_currency_amount($deleted_birim_fiyat, $deleted_para_birimi, 'TRY', $rates);
                $yeni_toplam_maliyet = ($mevcut_stok * $mevcut_birim_maliyet_try) - ($deleted_miktar * $deleted_birim_fiyat_try);
                
                $yeni_agirlikli_ortalama = 0;
                if ($yeni_stok > 0 && $yeni_toplam_maliyet > 0) {
                    $yeni_agirlikli_ortalama = round($yeni_toplam_maliyet / $yeni_stok, 4); // Round to 4 decimal places
                }

                $maliyet_manuel_girildi_yeni = $maliyet_manuel_girildi;
                $escaped_malzeme_kodu = $connection->real_escape_string($malzeme_kodu);
                $update_cost_query = "UPDATE malzemeler
                                      SET stok_miktari = $yeni_stok,
                                          alis_fiyati = $yeni_agirlikli_ortalama,
                                          para_birimi = 'TRY',
                                          maliyet_manuel_girildi = " . ($maliyet_manuel_girildi_yeni ? 1 : 0) . "
                                      WHERE malzeme_kodu = '$escaped_malzeme_kodu'";
                if (!$connection->query($update_cost_query)) {
                    throw new Exception('Malzeme maliyeti güncellenirken hata oluştu: ' . $connection->error);
                }

                if ($linked_purchase_order_id > 0) {
                    update_purchase_order_delivery_quantity($connection, $linked_purchase_order_id, (int) $malzeme_kodu, -$deleted_miktar);
                }
            } else {
                // If not a mal_kabul, just reverse the stock quantity
                $direction = ($normalized_movement_direction === 'giris') ? -$deleted_miktar : $deleted_miktar;
                $table_info = get_stock_table_info($normalized_movement_type);
                if (!$table_info) {
                    throw new Exception('Stok turu icin tablo bilgisi bulunamadi.');
                }
                $escaped_kod = $connection->real_escape_string($malzeme_kodu);
                if ($direction < 0) {
                    $required_stock = abs(floatval($direction));
                    $stock_query = "UPDATE " . $table_info['table'] . " SET stok_miktari = stok_miktari - $required_stock WHERE " . $table_info['code_column'] . " = '$escaped_kod' AND stok_miktari >= $required_stock";
                } else {
                    $stock_query = "UPDATE " . $table_info['table'] . " SET stok_miktari = stok_miktari + $direction WHERE " . $table_info['code_column'] . " = '$escaped_kod'";
                }
                if (!$connection->query($stock_query)) {
                    throw new Exception('Stok geri yuklenirken hata olustu: ' . $connection->error);
                }
                if ($direction < 0 && $connection->affected_rows === 0) {
                    throw new Exception('Yetersiz stok: hareket silme islemi mevcut stogu negatife dusurur.');
                }
            }

            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$movement[isim] ürünü için stok hareketi silindi (ID: $hareket_id)", 'DELETE');

            // 4. If all successful, commit
            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'Stok hareketi başarıyla silindi.']);

        } catch (Exception $e) {
            // 5. If any step fails, roll back
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Silme sırasında bir hata oluştu: ' . $e->getMessage()]);
        }

        $connection->autocommit(TRUE); // Restore autocommit mode
        break;



    case 'transfer_stock':
        // Get transfer parameters
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = floatval($_POST['miktar'] ?? 0);
        $aciklama = $_POST['aciklama'] ?? 'Stok transferi';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';
        $tank_kodu = $_POST['tank_kodu'] ?? '';
        $hedef_tank_kodu = $_POST['hedef_tank_kodu'] ?? '';

        // Get source and destination locations for materials and products
        // For essences, we will use depot and raf as well but the primary storage will be tanks
        $kaynak_depo = $_POST['kaynak_depo'] ?? '';
        $kaynak_raf = $_POST['kaynak_raf'] ?? '';
        $hedef_depo = $_POST['hedef_depo'] ?? '';
        $hedef_raf = $_POST['hedef_raf'] ?? '';

        // For essence transfers, we require hedef_tank_kodu
        if ($stok_turu === 'esans' && (!$hedef_tank_kodu || !$tank_kodu)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen hem kaynak hem de hedef tankı belirtin.']);
            break;
        }

        // For materials and products, we require depot and raf
        if ($stok_turu !== 'esans' && (!$kaynak_depo || !$kaynak_raf || !$hedef_depo || !$hedef_raf)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen hem kaynak hem de hedef konumu belirtin.']);
            break;
        }

        // Validation
        if (!$stok_turu || !$kod || $miktar <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Check if source and destination are the same
        if (($stok_turu !== 'esans' && $kaynak_depo === $hedef_depo && $kaynak_raf === $hedef_raf) ||
            ($stok_turu === 'esans' && $tank_kodu === $hedef_tank_kodu)) {
            echo json_encode(['status' => 'error', 'message' => 'Kaynak ve hedef konum aynı olamaz.']);
            break;
        }

        // Check current stock at source location
        $item_name = '';
        $item_unit = '';

        // Escape user inputs to prevent SQL injection
        $stok_turu = $connection->real_escape_string($stok_turu);
        $kod = $connection->real_escape_string($kod);
        $kaynak_depo = $connection->real_escape_string($kaynak_depo);
        $kaynak_raf = $connection->real_escape_string($kaynak_raf);
        $hedef_depo = $connection->real_escape_string($hedef_depo);
        $hedef_raf = $connection->real_escape_string($hedef_raf);
        $tank_kodu = $connection->real_escape_string($tank_kodu);
        $hedef_tank_kodu = $connection->real_escape_string($hedef_tank_kodu);

        // First, get item details
        switch ($stok_turu) {
            case 'malzeme':
                $item_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = '$kod'";
                break;
            case 'urun':
                $item_query = "SELECT urun_ismi, birim FROM urunler WHERE urun_kodu = '$kod'";
                break;
            case 'esans':
                $item_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = '$kod'";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz stok türü. Sadece malzeme, ürün ve essence transferi desteklenmektedir.']);
                exit;
        }

        $item_result = $connection->query($item_query);
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            if ($stok_turu === 'malzeme') {
                $item_name = $item['malzeme_ismi'] ?? '';
                $item_unit = $item['birim'];
            } else if ($stok_turu === 'urun') {
                $item_name = $item['urun_ismi'] ?? '';
                $item_unit = $item['birim'];
            } else if ($stok_turu === 'esans') {
                $item_name = $item['esans_ismi'] ?? '';
                $item_unit = $item['birim'];
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün kodu.']);
            break;
        }

        // Check current stock for essence or materials/products
        $available_stock = 0;

        if ($stok_turu === 'esans') {
            // For essences, check the main table as they are stored by tank
            $overall_stock_query = "SELECT stok_miktari FROM esanslar WHERE esans_kodu = '$kod'";
            $overall_result = $connection->query($overall_stock_query);
            if ($overall_result && $overall_result->num_rows > 0) {
                $overall_row = $overall_result->fetch_assoc();
                $available_stock = floatval($overall_row['stok_miktari']) ?? 0;
            }
        } else {
            // For materials and products, check the overall stock from the main table
            // since each item is stored in a single location
            switch ($stok_turu) {
                case 'malzeme':
                    $overall_stock_query = "SELECT stok_miktari FROM malzemeler WHERE malzeme_kodu = '$kod'";
                    break;
                case 'urun':
                    $overall_stock_query = "SELECT stok_miktari FROM urunler WHERE urun_kodu = '$kod'";
                    break;
            }

            $overall_result = $connection->query($overall_stock_query);
            if ($overall_result && $overall_result->num_rows > 0) {
                $overall_row = $overall_result->fetch_assoc();
                $available_stock = floatval($overall_row['stok_miktari']) ?? 0;
            }
        }

        // Check if there's enough stock for transfer
        if ($available_stock < $miktar) {
            echo json_encode(['status' => 'error', 'message' => 'Yetersiz stok. Kaynak konumda mevcut stok: ' . $available_stock]);
            break;
        }

        // Start transaction for consistency
        $connection->autocommit(FALSE);

        try {
            // Define variables for bind_param to avoid reference errors
            $null1 = NULL;
            $yon_cikis = 'cikis';
            $yon_giris = 'giris';
            $hareket_turu_transfer = 'transfer';

            // Create description strings for movements
            $source_description = $aciklama . ' - Kaynak: ' . $kaynak_depo . '/' . $kaynak_raf . ' -> Hedef: ' . $hedef_depo . '/' . $hedef_raf;
            $dest_description = $aciklama . ' - Kaynak: ' . $kaynak_depo . '/' . $kaynak_raf . ' -> Hedef: ' . $hedef_depo . '/' . $hedef_raf;

            // Step 1: Create the source (outgoing) stock movement
            $stok_turu_escaped = $connection->real_escape_string($stok_turu);
            $kod_escaped = $connection->real_escape_string($kod);
            $item_name_escaped = $connection->real_escape_string($item_name);
            $item_unit_escaped = $connection->real_escape_string($item_unit);
            $miktar_val = floatval($miktar);
            $yon_cikis_escaped = $connection->real_escape_string($yon_cikis);
            $hareket_turu_transfer_escaped = $connection->real_escape_string($hareket_turu_transfer);
            $kaynak_depo_escaped = $connection->real_escape_string($kaynak_depo);
            $kaynak_raf_escaped = $connection->real_escape_string($kaynak_raf);
            $tank_kodu_escaped = $connection->real_escape_string($tank_kodu);
            $ilgili_belge_no_escaped = $connection->real_escape_string($ilgili_belge_no);
            $source_description_escaped = $connection->real_escape_string($source_description);
            $user_id_val = intval($_SESSION['user_id']);
            $kullanici_adi_escaped = $connection->real_escape_string($_SESSION['kullanici_adi']);

            // For essence transfers, we use tank_kodu in the movements, for materials/products we use depot/raf
            if ($stok_turu === 'esans') {
                $source_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES ('$stok_turu_escaped', '$kod_escaped', '$item_name_escaped', '$item_unit_escaped', $miktar_val, '$yon_cikis_escaped', '$hareket_turu_transfer_escaped', '$kaynak_depo_escaped', '$kaynak_raf_escaped', '$tank_kodu_escaped', '$ilgili_belge_no_escaped', '$source_description_escaped', $user_id_val, '$kullanici_adi_escaped')";
            } else {
                $null1_escaped = $connection->real_escape_string($null1);
                $source_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES ('$stok_turu_escaped', '$kod_escaped', '$item_name_escaped', '$item_unit_escaped', $miktar_val, '$yon_cikis_escaped', '$hareket_turu_transfer_escaped', '$kaynak_depo_escaped', '$kaynak_raf_escaped', '$null1_escaped', '$ilgili_belge_no_escaped', '$source_description_escaped', $user_id_val, '$kullanici_adi_escaped')";
            }

            if (!$connection->query($source_movement_query)) {
                throw new Exception('Kaynak hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $source_movement_id = $connection->insert_id;

            // Step 2: Create the destination (incoming) stock movement
            $hedef_depo_escaped = $connection->real_escape_string($hedef_depo);
            $hedef_raf_escaped = $connection->real_escape_string($hedef_raf);
            $dest_description_escaped = $connection->real_escape_string($dest_description);
            $yon_giris_escaped = $connection->real_escape_string($yon_giris);
            $hedef_tank_kodu_escaped = $connection->real_escape_string($hedef_tank_kodu);

            // For essence transfers, we use tank_kodu in the movements, for materials/products we use depot/raf
            if ($stok_turu === 'esans') {
                $dest_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES ('$stok_turu_escaped', '$kod_escaped', '$item_name_escaped', '$item_unit_escaped', $miktar_val, '$yon_giris_escaped', '$hareket_turu_transfer_escaped', '$hedef_depo_escaped', '$hedef_raf_escaped', '$hedef_tank_kodu_escaped', '$ilgili_belge_no_escaped', '$dest_description_escaped', $user_id_val, '$kullanici_adi_escaped')";
            } else {
                $null1_escaped = $connection->real_escape_string($null1);
                $dest_movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, tank_kodu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) VALUES ('$stok_turu_escaped', '$kod_escaped', '$item_name_escaped', '$item_unit_escaped', $miktar_val, '$yon_giris_escaped', '$hareket_turu_transfer_escaped', '$hedef_depo_escaped', '$hedef_raf_escaped', '$null1_escaped', '$ilgili_belge_no_escaped', '$dest_description_escaped', $user_id_val, '$kullanici_adi_escaped')";
            }

            if (!$connection->query($dest_movement_query)) {
                throw new Exception('Hedef hareket kaydı oluşturulamadı: ' . $connection->error);
            }
            $dest_movement_id = $connection->insert_id;

            // Update the location in the main item table (urunler, malzemeler or esanslar) to the destination location
            switch ($stok_turu) {
                case 'malzeme':
                    $update_query = "UPDATE malzemeler SET depo = ?, raf = ? WHERE malzeme_kodu = ?";
                    $update_stmt = $connection->prepare($update_query);
                    if (!$update_stmt) {
                        throw new Exception('Malzeme transfer guncellemesi hazirlanamadi: ' . $connection->error);
                    }
                    $update_stmt->bind_param('ssi', $hedef_depo, $hedef_raf, $kod);
                    if (!$update_stmt->execute() || $update_stmt->affected_rows !== 1) {
                        $update_error = $update_stmt->error ?: $connection->error;
                        $update_stmt->close();
                        throw new Exception('Malzeme transferi tamamlanamadi: ' . ($update_error ?: 'Kayit guncellenemedi.'));
                    }
                    $update_stmt->close();
                    break;
                case 'urun':
                    $update_query = "UPDATE urunler SET depo = ?, raf = ? WHERE urun_kodu = ?";
                    $update_stmt = $connection->prepare($update_query);
                    if (!$update_stmt) {
                        throw new Exception('Urun transfer guncellemesi hazirlanamadi: ' . $connection->error);
                    }
                    $update_stmt->bind_param('ssi', $hedef_depo, $hedef_raf, $kod);
                    if (!$update_stmt->execute() || $update_stmt->affected_rows !== 1) {
                        $update_error = $update_stmt->error ?: $connection->error;
                        $update_stmt->close();
                        throw new Exception('Urun transferi tamamlanamadi: ' . ($update_error ?: 'Kayit guncellenemedi.'));
                    }
                    $update_stmt->close();
                    break;
                case 'esans':
                    // Essence transfers only change tank assignment; total stock stays unchanged.
                    $tank_query = "SELECT tank_ismi FROM tanklar WHERE tank_kodu = ?";
                    $tank_stmt = $connection->prepare($tank_query);
                    if (!$tank_stmt) {
                        throw new Exception('Hedef tank sorgusu hazirlanamadi: ' . $connection->error);
                    }
                    $tank_stmt->bind_param('s', $hedef_tank_kodu);
                    $tank_stmt->execute();
                    $tank_result = $tank_stmt->get_result();
                    
                    if ($tank_result->num_rows === 0) {
                        throw new Exception('Hedef tank bulunamadı.');
                    }
                    
                    $tank_info = $tank_result->fetch_assoc();
                    $hedef_tank_ismi = $tank_info['tank_ismi'];
                    $tank_stmt->close();

                    $update_query = "UPDATE esanslar SET tank_kodu = ?, tank_ismi = ? WHERE esans_kodu = ?";
                    $update_stmt = $connection->prepare($update_query);
                    if (!$update_stmt) {
                        throw new Exception('Esans transfer guncellemesi hazirlanamadi: ' . $connection->error);
                    }
                    $update_stmt->bind_param('sss', $hedef_tank_kodu, $hedef_tank_ismi, $kod);
                    if (!$update_stmt->execute() || $update_stmt->affected_rows !== 1) {
                        $update_error = $update_stmt->error ?: $connection->error;
                        $update_stmt->close();
                        throw new Exception(normalize_negative_stock_error_message(
                            $update_error,
                            'Esans transferi tamamlanamadi: ' . ($update_error ?: 'Kayit guncellenemedi.')
                        ));
                    }
                    $update_stmt->close();
                    break;
            }

            $connection->commit();

            echo json_encode(['status' => 'success', 'message' => 'Stok başarıyla transfer edildi.']);

        } catch (Exception $e) {
            // Rollback transaction on error
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Transfer işlemi sırasında hata oluştu: ' . $e->getMessage()]);
        }

        $connection->autocommit(TRUE); // Reset autocommit
        break;

    case 'get_contracts_by_priority':
        $material_kodu = $_POST['material_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        
        if (!$material_kodu || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu ve tedarikci ID gerekli.']);
            break;
        }
        
        $contract_query = "SELECT c.sozlesme_id, c.limit_miktar, 
                          COALESCE(shs.toplam_kullanilan, 0) as toplam_mal_kabul_miktari,
                          (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) as kalan_miktar,
                          c.oncelik
                          FROM cerceve_sozlesmeler c
                          LEFT JOIN (
                              SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
                              FROM stok_hareketleri_sozlesmeler
                              GROUP BY sozlesme_id
                          ) shs ON c.sozlesme_id = shs.sozlesme_id
                          WHERE c.tedarikci_id = ? 
                          AND c.malzeme_kodu = ?
                          AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                          AND (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) > 0
                          ORDER BY c.oncelik ASC, kalan_miktar DESC";
        
        $contract_stmt = $connection->prepare($contract_query);
        $contract_stmt->bind_param('is', $tedarikci_id, $material_kodu);
        $contract_stmt->execute();
        $contract_result = $contract_stmt->get_result();
        
        $contracts = [];
        while ($row = $contract_result->fetch_assoc()) {
            $contracts[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'contracts' => $contracts]);
        $contract_stmt->close();
        break;

    case 'get_material_orders':
        $malzeme_kodu = $_GET['malzeme_kodu'] ?? '';
        $tedarikci_id = $_GET['tedarikci_id'] ?? '';

        if (!$malzeme_kodu || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Eksik parametreler.']);
            break;
        }

        // Get open orders for this supplier and material
        $query = "SELECT 
                    ss.siparis_id, 
                    ss.siparis_no, 
                    ss.siparis_tarihi,
                    ssk.miktar as siparis_miktari, 
                    ssk.teslim_edilen_miktar,
                    (ssk.miktar - ssk.teslim_edilen_miktar) as kalan_miktar
                  FROM satinalma_siparisler ss
                  JOIN satinalma_siparis_kalemleri ssk ON ss.siparis_id = ssk.siparis_id
                  WHERE ss.tedarikci_id = ? 
                  AND ssk.malzeme_kodu = ?
                  AND ss.durum NOT IN ('iptal', 'kapatildi', 'tamamlandi')
                  AND (ssk.miktar - ssk.teslim_edilen_miktar) > 0
                  ORDER BY ss.siparis_tarihi ASC";

        $stmt = $connection->prepare($query);
        $stmt->bind_param('is', $tedarikci_id, $malzeme_kodu);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $orders]);
        $stmt->close();
        break;

    case 'check_framework_contract':
        $material_kodu = $_POST['material_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        
        if (!$material_kodu || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu ve tedarikci ID gerekli.']);
            break;
        }
        
        // Get material name
        $material_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $material_stmt = $connection->prepare($material_query);
        $material_stmt->bind_param('s', $material_kodu);
        $material_stmt->execute();
        $material_result = $material_stmt->get_result();
        $material_name = '';
        if ($material_result->num_rows > 0) {
            $material = $material_result->fetch_assoc();
            $material_name = $material['malzeme_ismi'];
        }
        $material_stmt->close();
        
        // Check if there is a valid framework contract for this supplier and material
        $contract_check_query = "SELECT c.sozlesme_id, c.limit_miktar, 
                                COALESCE(shs.toplam_kullanilan, 0) as toplam_mal_kabul_miktari,
                                (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) as kalan_miktar
                                FROM cerceve_sozlesmeler c
                                LEFT JOIN (
                                    SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
                                    FROM stok_hareketleri_sozlesmeler
                                    GROUP BY sozlesme_id
                                ) shs ON c.sozlesme_id = shs.sozlesme_id
                                WHERE c.tedarikci_id = ? 
                                AND c.malzeme_kodu = ?
                                AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                                AND (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) > 0
                                ORDER BY c.oncelik ASC, kalan_miktar DESC";
        $contract_check_stmt = $connection->prepare($contract_check_query);
        $contract_check_stmt->bind_param('is', $tedarikci_id, $material_kodu);
        $contract_check_stmt->execute();
        $contract_result = $contract_check_stmt->get_result();
        
        if ($contract_result->num_rows > 0) {
            $contract = $contract_result->fetch_assoc();
            echo json_encode([
                'status' => 'success', 
                'contract_info' => [
                    'has_valid_contract' => true,
                    'material_name' => $material_name,
                    'remaining_amount' => $contract['kalan_miktar']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'success', 
                'contract_info' => [
                    'has_valid_contract' => false,
                    'material_name' => $material_name,
                    'remaining_amount' => 0
                ]
            ]);
        }
        $contract_check_stmt->close();
        break;

    case 'add_mal_kabul':
        $stok_turu = $_POST['stok_turu'] ?? '';
        $kod = $_POST['kod'] ?? '';
        $miktar = floatval($_POST['miktar'] ?? 0);
        $aciklama = $_POST['aciklama'] ?? '';
        $ilgili_belge_no = $_POST['ilgili_belge_no'] ?? '';
        $tedarikci_id = $_POST['tedarikci'] ?? '';
        $depo = $_POST['depo'] ?? '';
        $raf = $_POST['raf'] ?? '';
        $siparis_id = !empty($_POST['siparis_id']) ? intval($_POST['siparis_id']) : 0;

        // Validation
        if (!$stok_turu || !$kod || !$miktar || !$aciklama || !$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Lutfen tum zorunlu alanlari doldurun (stok_turu, kod, miktar, aciklama ve tedarikci).']);
            break;
        }

        if ($stok_turu !== 'malzeme') {
            echo json_encode(['status' => 'error', 'message' => 'Mal kabul sadece malzeme turu icin yapilabilir.']);
            break;
        }

        $item_name = '';
        $item_unit = '';
        $current_depo = '';
        $current_raf = '';
        $mevcut_stok = 0;
        $mevcut_alis_fiyati = 0;
        $mevcut_para_birimi = 'TRY';
        $maliyet_manuel_girildi = false;

        $item_query = "SELECT malzeme_ismi, birim, depo, raf, stok_miktari, alis_fiyati, para_birimi, maliyet_manuel_girildi FROM malzemeler WHERE malzeme_kodu = ?";
        $item_stmt = $connection->prepare($item_query);
        $item_stmt->bind_param('s', $kod);
        
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $item_name = $item['malzeme_ismi'];
            $item_unit = $item['birim'];
            $current_depo = $item['depo'] ?? '';
            $current_raf = $item['raf'] ?? '';
            $mevcut_stok = (float)$item['stok_miktari'];
            $mevcut_alis_fiyati = (float)$item['alis_fiyati'];
            $mevcut_para_birimi = normalize_stock_currency_code($item['para_birimi'] ?? 'TRY');
            $maliyet_manuel_girildi = (bool)$item['maliyet_manuel_girildi'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz malzeme kodu.']);
            $item_stmt->close();
            break;
        }
        $item_stmt->close();

        if (!$depo) $depo = $current_depo;
        if (!$raf) $raf = $current_raf;

        $tedarikci_ismi = '';
        $supplier_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $supplier_stmt = $connection->prepare($supplier_query);
        $supplier_stmt->bind_param('i', $tedarikci_id);
        $supplier_stmt->execute();
        $supplier_result = $supplier_stmt->get_result();
        if ($supplier_result->num_rows > 0) {
            $supplier = $supplier_result->fetch_assoc();
            $tedarikci_ismi = $supplier['tedarikci_adi'];
        }
        $supplier_stmt->close();

        $contract_query = "SELECT c.sozlesme_id, c.limit_miktar, c.birim_fiyat, c.para_birimi, c.baslangic_tarihi, c.bitis_tarihi,
                          COALESCE(shs.toplam_kullanilan, 0) as toplam_mal_kabul_miktari,
                          (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) as kalan_miktar,
                          c.oncelik
                          FROM cerceve_sozlesmeler c
                          LEFT JOIN (
                              SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
                              FROM stok_hareketleri_sozlesmeler
                              GROUP BY sozlesme_id
                          ) shs ON c.sozlesme_id = shs.sozlesme_id
                          WHERE c.tedarikci_id = ? 
                          AND c.malzeme_kodu = ?
                          AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                          AND (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) > 0
                          ORDER BY c.oncelik ASC, kalan_miktar DESC";
        
        $contract_stmt = $connection->prepare($contract_query);
        $contract_stmt->bind_param('is', $tedarikci_id, $kod);
        $contract_stmt->execute();
        $contract_result = $contract_stmt->get_result();
        
        $contracts = [];
        while ($row = $contract_result->fetch_assoc()) {
            $contracts[] = $row;
        }
        $contract_stmt->close();
        
        $total_available = array_sum(array_column($contracts, 'kalan_miktar'));
        $requested_amount = $miktar;
        
        if ($total_available < $requested_amount) {
            echo json_encode(['status' => 'error', 'message' => 'Tum gecerli sozlesme limitleri bu islem icin yeterli degildir. Kalan Miktar: ' . $total_available]);
            break;
        }

        $connection->autocommit(FALSE);
        
        $remaining_amount = $requested_amount;
        $total_updated_stock = 0;
        $error_occured = false;
        $has_purchase_order_column = stock_table_has_column($connection, 'stok_hareket_kayitlari', 'satinalma_siparis_id');
        $rates = get_stock_exchange_rates($connection);
        
        $yeni_gelen_toplam_maliyet = 0;
        $yeni_gelen_toplam_miktar = 0;

        foreach ($contracts as $contract) {
            if ($remaining_amount <= 0) break;
            
            $contract_amount = min($contract['kalan_miktar'], $remaining_amount);
            
            $yon = 'giris';
            $hareket_turu = 'mal_kabul';
            
            $contract_currency = normalize_stock_currency_code($contract['para_birimi'] ?? 'TRY');
            $contract_unit_price_try = convert_stock_currency_amount($contract['birim_fiyat'], $contract_currency, 'TRY', $rates);
            $yeni_gelen_toplam_maliyet += $contract_amount * $contract_unit_price_try;
            $yeni_gelen_toplam_miktar += $contract_amount;

            $contract_specific_aciklama = $aciklama . ' [Sozlesme ID: ' . $contract['sozlesme_id'] . ']';
            
            // If linked to an order, append order info to description
            if ($siparis_id > 0) {
                // Get order no
                $s_res = $connection->query("SELECT siparis_no FROM satinalma_siparisler WHERE siparis_id = $siparis_id");
                if ($s_res && $s_row = $s_res->fetch_assoc()) {
                    $contract_specific_aciklama .= ' [Siparis: ' . $s_row['siparis_no'] . ']';
                }
            }

            $stok_turu_escaped = $connection->real_escape_string($stok_turu);
            $kod_escaped = $connection->real_escape_string($kod);
            $item_name_escaped = $connection->real_escape_string($item_name);
            $item_unit_escaped = $connection->real_escape_string($item_unit);
            $depo_escaped = $connection->real_escape_string($depo);
            $raf_escaped = $connection->real_escape_string($raf);
            $ilgili_belge_no_escaped = $connection->real_escape_string($ilgili_belge_no);
            $contract_specific_aciklama_escaped = $connection->real_escape_string($contract_specific_aciklama);
            $tedarikci_ismi_escaped = $connection->real_escape_string($tedarikci_ismi);
            $user_id_val = intval($_SESSION['user_id']);
            $kullanici_adi_escaped = $connection->real_escape_string($_SESSION['kullanici_adi']);
            $tedarikci_id_val = intval($tedarikci_id);
            $siparis_id_val = $siparis_id > 0 ? $siparis_id : 'NULL';

            $movement_columns = "stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, ilgili_belge_no";
            $movement_values = "'$stok_turu_escaped', '$kod_escaped', '$item_name_escaped', '$item_unit_escaped', $contract_amount, '$yon', '$hareket_turu', '$depo_escaped', '$raf_escaped', '$ilgili_belge_no_escaped'";

            if ($has_purchase_order_column) {
                $movement_columns .= ", satinalma_siparis_id";
                $movement_values .= ", $siparis_id_val";
            }

            $movement_columns .= ", aciklama, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi, tedarikci_id";
            $movement_values .= ", '$contract_specific_aciklama_escaped', $user_id_val, '$kullanici_adi_escaped', '$tedarikci_ismi_escaped', $tedarikci_id_val";

            $movement_query = "INSERT INTO stok_hareket_kayitlari ($movement_columns) VALUES ($movement_values)";

            if (!$connection->query($movement_query)) {
                $error_occured = true;
                echo json_encode(['status' => 'error', 'message' => 'Mal kabul islemi kaydedilirken hata olustu: ' . $connection->error]);
                break;
            }

            $hareket_id = $connection->insert_id;
            
            $contract_id = $contract['sozlesme_id'];
            $birim_fiyat = $contract['birim_fiyat'];
            $para_birimi = $connection->real_escape_string($contract['para_birimi']);
            $tedarikci_ismi_escaped = $connection->real_escape_string($tedarikci_ismi);
            
            $baslangic_tarihi_val = (!empty($contract['baslangic_tarihi']) && $contract['baslangic_tarihi'] !== '0000-00-00') ? "'" . $contract['baslangic_tarihi'] . "'" : "NULL";
            $bitis_tarihi_val = (!empty($contract['bitis_tarihi']) && $contract['bitis_tarihi'] !== '0000-00-00') ? "'" . $contract['bitis_tarihi'] . "'" : "NULL";

            $contract_link_query = "INSERT INTO stok_hareketleri_sozlesmeler (hareket_id, sozlesme_id, malzeme_kodu, kullanilan_miktar, birim_fiyat, para_birimi, tedarikci_adi, tedarikci_id, baslangic_tarihi, bitis_tarihi) VALUES ($hareket_id, $contract_id, $kod, $contract_amount, $birim_fiyat, '$para_birimi', '$tedarikci_ismi_escaped', $tedarikci_id, $baslangic_tarihi_val, $bitis_tarihi_val)";
            
            if (!$connection->query($contract_link_query)) {
                $error_occured = true;
                echo json_encode(['status' => 'error', 'message' => 'Sozlesme baglantisi kaydedilirken hata olustu: ' . $connection->error]);
                break;
            }
            
            $remaining_amount -= $contract_amount;
            $total_updated_stock += $contract_amount;
        }
        
        if ($error_occured) {
            $connection->rollback();
        } else {
            try {
                if ($total_updated_stock <= 0) {
                    throw new Exception('Miktar dagitimi sirasinda hata olustu.');
                }

                // Recalculate weighted average and update stock in the same transaction.
                $mevcut_birim_maliyet_try = convert_stock_currency_amount($mevcut_alis_fiyati, $mevcut_para_birimi, 'TRY', $rates);
                $toplam_maliyet = ($mevcut_stok * $mevcut_birim_maliyet_try) + $yeni_gelen_toplam_maliyet;
                $toplam_stok = $mevcut_stok + $yeni_gelen_toplam_miktar;
                $yeni_agirlikli_ortalama = $toplam_stok > 0 ? round($toplam_maliyet / $toplam_stok, 6) : 0;
                $maliyet_manuel_girildi_yeni = $maliyet_manuel_girildi;

                $escaped_kod = $connection->real_escape_string($kod);
                $escaped_depo = $connection->real_escape_string($depo);
                $escaped_raf = $connection->real_escape_string($raf);

                $stock_query = "UPDATE malzemeler
                                SET stok_miktari = stok_miktari + $total_updated_stock,
                                    alis_fiyati = $yeni_agirlikli_ortalama,
                                    para_birimi = 'TRY',
                                    maliyet_manuel_girildi = " . ($maliyet_manuel_girildi_yeni ? 1 : 0) . ",
                                    depo = '$escaped_depo',
                                    raf = '$escaped_raf'
                                WHERE malzeme_kodu = '$escaped_kod'";

                if (!$connection->query($stock_query) || $connection->affected_rows !== 1) {
                    throw new Exception('Stok guncellenemedi: ' . $connection->error);
                }

                if ($siparis_id > 0) {
                    update_purchase_order_delivery_quantity($connection, $siparis_id, (int) $kod, $total_updated_stock);
                }

                $connection->commit();
                echo json_encode(['status' => 'success', 'message' => 'Mal kabul islemi basariyla kaydedildi ve stok guncellendi.']);
            } catch (Exception $e) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        $connection->autocommit(TRUE);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz islem.']);
        break;
}
?>
