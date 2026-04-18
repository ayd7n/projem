<?php
include '../config.php';

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

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_customers':
        getCustomers();
        break;
    case 'get_customer':
        getCustomer();
        break;
    case 'add_customer':
        addCustomer();
        break;
    case 'update_customer':
        updateCustomer();
        break;
    case 'delete_customer':
        deleteCustomer();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function normalizeCustomerCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TL' || $currency === '') {
        return 'TRY';
    }
    return in_array($currency, ['TRY', 'USD', 'EUR']) ? $currency : 'TRY';
}

function getCustomerRates()
{
    global $connection;
    $rates = ['TRY' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
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

function convertCustomerCurrency($amount, $from, $to, $rates)
{
    $amount = (float) $amount;
    $from = normalizeCustomerCurrency($from);
    $to = normalizeCustomerCurrency($to);
    if ($from === $to) {
        return $amount;
    }

    $fromRate = (float) ($rates[$from] ?? 0);
    $toRate = (float) ($rates[$to] ?? 0);
    if ($fromRate <= 0 || $toRate <= 0) {
        return $amount;
    }

    $tryAmount = $amount * $fromRate;
    return $tryAmount / $toRate;
}

function getCustomerCurrencySymbol($currency)
{
    $currency = normalizeCustomerCurrency($currency);
    if ($currency === 'USD') {
        return '$';
    }
    if ($currency === 'EUR') {
        return '€';
    }
    return '₺';
}
function getCustomers()
{
    global $connection;

    if (!yetkisi_var('page:view:musteriler')) {
        echo json_encode(['status' => 'error', 'message' => 'Müşterileri görüntüleme yetkiniz yok.']);
        return;
    }

    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE musteri_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param'";
    }

    $count_query = "SELECT COUNT(*) as total FROM musteriler " . $where_clause;
    $result = $connection->query($count_query);
    $total_customers = $result->fetch_assoc()['total'];

    $total_pages = $limit > 0 ? ceil($total_customers / $limit) : 0;

    $query = "SELECT * FROM musteriler " . $where_clause . " ORDER BY musteri_adi LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);

    $customers = [];
    $customer_ids = [];
    while ($row = $result->fetch_assoc()) {
        $customers[$row['musteri_id']] = $row;
        $customer_ids[] = $row['musteri_id'];
    }

    if (!empty($customer_ids)) {
        $ids_str = implode(',', $customer_ids);
        $rates = getCustomerRates();

        // 1. Installment plan debts (per plan currency)
        $plan_debt_query = "SELECT tp.musteri_id, tp.para_birimi, SUM(td.kalan_tutar) as plan_debt
                            FROM taksit_detaylari td
                            JOIN taksit_planlari tp ON tp.plan_id = td.plan_id
                            WHERE tp.musteri_id IN ($ids_str) AND tp.durum != 'iptal' AND td.durum != 'odendi'
                            GROUP BY tp.musteri_id, tp.para_birimi";
        $plan_debt_res = $connection->query($plan_debt_query);
        $plan_debts = [];
        if ($plan_debt_res) {
            while ($p = $plan_debt_res->fetch_assoc()) {
                $mid = (int) $p['musteri_id'];
                $curr = normalizeCustomerCurrency($p['para_birimi'] ?? 'TRY');
                if (!isset($plan_debts[$mid])) {
                    $plan_debts[$mid] = [];
                }
                if (!isset($plan_debts[$mid][$curr])) {
                    $plan_debts[$mid][$curr] = 0.0;
                }
                $plan_debts[$mid][$curr] += (float) $p['plan_debt'];
            }
        }

        // 2. Linked order IDs (exclude from regular balance)
        $linked_orders_query = "SELECT tsb.siparis_id
                                FROM taksit_siparis_baglantisi tsb
                                JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id
                                WHERE tp.musteri_id IN ($ids_str) AND tp.durum != 'iptal'";
        $linked_res = $connection->query($linked_orders_query);
        $linked_ids = [];
        while ($l = $linked_res->fetch_assoc()) {
            $linked_ids[] = (int) $l['siparis_id'];
        }

        // 3. Regular orders balance per currency
        $orders_sql = "SELECT s.musteri_id, s.siparis_id, s.odenen_tutar, s.para_birimi
                       FROM siparisler s
                       WHERE s.musteri_id IN ($ids_str)
                       AND s.durum IN ('onaylandi', 'tamamlandi')";

        if (!empty($linked_ids)) {
            $orders_sql .= " AND s.siparis_id NOT IN (" . implode(',', $linked_ids) . ")";
        }

        $orders_res = $connection->query($orders_sql);
        $customer_balances = [];
        $unpaid_counts = [];

        while ($ord = $orders_res->fetch_assoc()) {
            $mid = (int) $ord['musteri_id'];
            $sid = (int) $ord['siparis_id'];
            $order_currency = normalizeCustomerCurrency($ord['para_birimi'] ?? 'TRY');
            $paid = max(0.0, (float) $ord['odenen_tutar']);

            $items_sql = "SELECT para_birimi, SUM(COALESCE(toplam_tutar, birim_fiyat * adet)) as total
                          FROM siparis_kalemleri
                          WHERE siparis_id = $sid
                          GROUP BY para_birimi";
            $items_res = $connection->query($items_sql);

            $order_total = 0.0;
            if ($items_res) {
                while ($item = $items_res->fetch_assoc()) {
                    $item_currency = normalizeCustomerCurrency($item['para_birimi'] ?? $order_currency);
                    $item_total = (float) ($item['total'] ?? 0);
                    $order_total += convertCustomerCurrency($item_total, $item_currency, $order_currency, $rates);
                }
            }

            $remaining = max(0.0, $order_total - $paid);
            if ($remaining > 0.01) {
                if (!isset($customer_balances[$mid][$order_currency])) {
                    $customer_balances[$mid][$order_currency] = 0.0;
                }
                $customer_balances[$mid][$order_currency] += $remaining;

                if (!isset($unpaid_counts[$mid])) {
                    $unpaid_counts[$mid] = 0;
                }
                $unpaid_counts[$mid]++;
            }
        }

        // Active plan orders (considered unpaid)
        $plan_orders_sql = "SELECT tp.musteri_id, COUNT(DISTINCT tsb.siparis_id) as cnt
                            FROM taksit_siparis_baglantisi tsb
                            JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id
                            WHERE tp.musteri_id IN ($ids_str) AND tp.durum = 'aktif'
                            GROUP BY tp.musteri_id";
        $plan_orders_res = $connection->query($plan_orders_sql);
        while ($p = $plan_orders_res->fetch_assoc()) {
            $mid = (int) $p['musteri_id'];
            if (!isset($unpaid_counts[$mid])) {
                $unpaid_counts[$mid] = 0;
            }
            $unpaid_counts[$mid] += (int) $p['cnt'];
        }

        // Merge data into customers array
        foreach ($customers as $mid => &$cust) {
            $balances = $customer_balances[$mid] ?? [];

            if (!empty($plan_debts[$mid])) {
                foreach ($plan_debts[$mid] as $curr => $amount) {
                    if (!isset($balances[$curr])) {
                        $balances[$curr] = 0.0;
                    }
                    $balances[$curr] += (float) $amount;
                }
            }

            $balance_parts = [];
            $total_approx_try = 0.0;
            foreach ($balances as $curr => $amount) {
                $amount = (float) $amount;
                if ($amount > 0.01) {
                    $symbol = getCustomerCurrencySymbol($curr);
                    $balance_parts[] = number_format($amount, 2, ',', '.') . ' ' . $symbol;
                    $total_approx_try += convertCustomerCurrency($amount, $curr, 'TRY', $rates);
                }
            }

            $cust['bakiye_gosterim'] = empty($balance_parts)
                ? '<span style="background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.75rem; white-space: nowrap;"><i class="fas fa-check-circle"></i> Temiz</span>'
                : '<span style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.75rem; white-space: nowrap; display: inline-block;">' . implode('<br>', $balance_parts) . '</span>';

            $cust['kalan_bakiye'] = $total_approx_try;
            $cust['odenmemis_siparis'] = $unpaid_counts[$mid] ?? 0;
        }
        unset($cust);
    }
    // Convert associative array back to indexed array
    $customers_list = array_values($customers);

    // Top cards should reflect the currently listed customers.
    $total_balance = 0;
    $total_unpaid_orders = 0;
    foreach ($customers_list as $customer_row) {
        $total_balance += floatval($customer_row['kalan_bakiye'] ?? 0);
        $total_unpaid_orders += intval($customer_row['odenmemis_siparis'] ?? 0);
    }

    $response = [
        'status' => 'success',
        'data' => $customers_list,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_customers' => $total_customers,
            'total_balance' => round($total_balance, 2),
            'total_unpaid_orders' => $total_unpaid_orders,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
}

function getCustomer()
{
    global $connection;

    if (!yetkisi_var('page:view:musteriler')) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri görüntüleme yetkiniz yok.']);
        return;
    }

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID gerekli.']);
        return;
    }

    $escaped_id = $connection->real_escape_string($id);
    $query = "SELECT * FROM musteriler WHERE musteri_id = $escaped_id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $customer]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri bulunamadı.']);
    }
}

function addCustomer()
{
    global $connection;

    if (!yetkisi_var('action:musteriler:create')) {
        echo json_encode(['status' => 'error', 'message' => 'Yeni müşteri ekleme yetkiniz yok.']);
        return;
    }

    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');
    $vergi_no_tc = $connection->real_escape_string($_POST['vergi_no_tc'] ?? '');
    $adres = $connection->real_escape_string($_POST['adres'] ?? '');
    $telefon = $connection->real_escape_string($_POST['telefon'] ?? '');
    $telefon_2 = $connection->real_escape_string($_POST['telefon_2'] ?? '');
    $e_posta = $connection->real_escape_string($_POST['e_posta'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $aciklama_notlar = $connection->real_escape_string($_POST['aciklama_notlar'] ?? '');
    $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;
    $stok_goruntuleme_yetkisi = isset($_POST['stok_goruntuleme_yetkisi']) ? (($_POST['stok_goruntuleme_yetkisi'] === 'true' || $_POST['stok_goruntuleme_yetkisi'] === true || $_POST['stok_goruntuleme_yetkisi'] === '1' || $_POST['stok_goruntuleme_yetkisi'] == 1) ? 1 : 0) : 0;

    if (empty($musteri_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri adı alanı zorunludur.']);
        return;
    }

    // If giris_yetkisi is enabled, password is required
    if ($giris_yetkisi == 1 && empty($sifre)) {
        echo json_encode(['status' => 'error', 'message' => 'Sisteme giriş yetkisi verildiğinde şifre zorunludur.']);
        return;
    }

    try {
        // Hash the password if provided, otherwise store as empty string
        if (!empty($sifre)) {
            $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
            $escaped_hashed_password = $connection->real_escape_string($hashed_password);
        } else {
            $escaped_hashed_password = '';
        }

        $query = "INSERT INTO musteriler (musteri_adi, vergi_no_tc, adres, telefon, telefon_2, e_posta, sistem_sifresi, aciklama_notlar, giris_yetkisi, stok_goruntuleme_yetkisi) VALUES ('$musteri_adi', '$vergi_no_tc', '$adres', '$telefon', '$telefon_2', '$e_posta', '$escaped_hashed_password', '$aciklama_notlar', $giris_yetkisi, $stok_goruntuleme_yetkisi)";
        $result = $connection->query($query);

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$musteri_adi müşterisi sisteme eklendi", 'CREATE');
            echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla oluşturuldu.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Müşteri oluşturulurken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function updateCustomer()
{
    global $connection;

    if (!yetkisi_var('action:musteriler:edit')) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri bilgilerini düzenleme yetkiniz yok.']);
        return;
    }

    $musteri_id = (int) ($_POST['musteri_id'] ?? '');
    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');
    $vergi_no_tc = $connection->real_escape_string($_POST['vergi_no_tc'] ?? '');
    $adres = $connection->real_escape_string($_POST['adres'] ?? '');
    $telefon = $connection->real_escape_string($_POST['telefon'] ?? '');
    $telefon_2 = $connection->real_escape_string($_POST['telefon_2'] ?? '');
    $e_posta = $connection->real_escape_string($_POST['e_posta'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $aciklama_notlar = $connection->real_escape_string($_POST['aciklama_notlar'] ?? '');
    $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;
    $stok_goruntuleme_yetkisi = isset($_POST['stok_goruntuleme_yetkisi']) ? (($_POST['stok_goruntuleme_yetkisi'] === 'true' || $_POST['stok_goruntuleme_yetkisi'] === true || $_POST['stok_goruntuleme_yetkisi'] === '1' || $_POST['stok_goruntuleme_yetkisi'] == 1) ? 1 : 0) : 0;

    if (empty($musteri_id) || empty($musteri_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID ve müşteri adı alanları zorunludur.']);
        return;
    }

    try {
        // Update password if provided and giris_yetkisi is enabled, otherwise don't update password field
        if (!empty($sifre) && $giris_yetkisi == 1) {
            $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
            $escaped_hashed_password = $connection->real_escape_string($hashed_password);
            $query = "UPDATE musteriler SET musteri_adi = '$musteri_adi', vergi_no_tc = '$vergi_no_tc', adres = '$adres', telefon = '$telefon', telefon_2 = '$telefon_2', e_posta = '$e_posta', sistem_sifresi = '$escaped_hashed_password', aciklama_notlar = '$aciklama_notlar', giris_yetkisi = $giris_yetkisi, stok_goruntuleme_yetkisi = $stok_goruntuleme_yetkisi WHERE musteri_id = $musteri_id";
        } else {
            $query = "UPDATE musteriler SET musteri_adi = '$musteri_adi', vergi_no_tc = '$vergi_no_tc', adres = '$adres', telefon = '$telefon', telefon_2 = '$telefon_2', e_posta = '$e_posta', aciklama_notlar = '$aciklama_notlar', giris_yetkisi = $giris_yetkisi, stok_goruntuleme_yetkisi = $stok_goruntuleme_yetkisi WHERE musteri_id = $musteri_id";
        }

        // Eski müşteri adını almak için sorgu
        $old_customer_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = $musteri_id";
        $old_customer_result = $connection->query($old_customer_query);
        $old_customer = $old_customer_result->fetch_assoc();
        $old_customer_name = $old_customer['musteri_adi'] ?? 'Bilinmeyen Müşteri';

        $result = $connection->query($query);

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$old_customer_name müşterisi $musteri_adi olarak güncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Müşteri güncellenirken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function deleteCustomer()
{
    global $connection;

    if (!yetkisi_var('action:musteriler:delete')) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri silme yetkiniz yok.']);
        return;
    }

    $musteri_id = (int) ($_POST['musteri_id'] ?? '');
    if (empty($musteri_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID gerekli.']);
        return;
    }

    try {
        // Silinen müşteri adını almak için sorgu
        $old_customer_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = $musteri_id";
        $old_customer_result = $connection->query($old_customer_query);
        $old_customer = $old_customer_result->fetch_assoc();
        $deleted_customer_name = $old_customer['musteri_adi'] ?? 'Bilinmeyen Müşteri';

        $query = "DELETE FROM musteriler WHERE musteri_id = $musteri_id";
        $result = $connection->query($query);

        if ($result) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_customer_name müşterisi sistemden silindi", 'DELETE');
            echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Müşteri silinirken hata oluştu: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}
?>

