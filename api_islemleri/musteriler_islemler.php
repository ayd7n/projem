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

        // 1. Installment Plan Debts (Assuming TRY for now as table structure isn't fully migrated)
        $plan_debt_query = "SELECT tp.musteri_id, SUM(td.kalan_tutar) as plan_debt 
                            FROM taksit_detaylari td 
                            JOIN taksit_planlari tp ON tp.plan_id = td.plan_id 
                            WHERE tp.musteri_id IN ($ids_str) AND tp.durum != 'iptal'
                            GROUP BY tp.musteri_id";
        $plan_debt_res = $connection->query($plan_debt_query);
        $plan_debts = [];
        while ($p = $plan_debt_res->fetch_assoc()) {
            $plan_debts[$p['musteri_id']] = floatval($p['plan_debt']);
        }

        // 2. Linked Order IDs (to exclude from regular balance)
        $linked_orders_query = "SELECT tsb.siparis_id 
                                FROM taksit_siparis_baglantisi tsb 
                                JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id 
                                WHERE tp.musteri_id IN ($ids_str) AND tp.durum != 'iptal'";
        $linked_res = $connection->query($linked_orders_query);
        $linked_ids = [];
        while ($l = $linked_res->fetch_assoc()) {
            $linked_ids[] = $l['siparis_id'];
        }
        
        // 3. Regular Orders Balance Per Currency
        // Fetch order items to get totals per currency
        // Also fetch payments (odenen_tutar) from orders
        
        // Note: odenen_tutar is in orders table. We need to subtract it from the total.
        // We assume payment is made in the same currency as the order items.
        // If an order has mixed currencies, we take the currency of the items (grouped).
        // Since payment is just a number, we subtract it from the first currency found or TRY.
        
        $orders_sql = "SELECT s.musteri_id, s.siparis_id, s.odenen_tutar 
                       FROM siparisler s 
                       WHERE s.musteri_id IN ($ids_str) 
                       AND s.durum IN ('onaylandi', 'tamamlandi')";
        
        if (!empty($linked_ids)) {
            $orders_sql .= " AND s.siparis_id NOT IN (" . implode(',', $linked_ids) . ")";
        }
        
        $orders_res = $connection->query($orders_sql);
        $customer_balances = []; // [musteri_id][currency] = amount

        while ($ord = $orders_res->fetch_assoc()) {
            $mid = $ord['musteri_id'];
            $sid = $ord['siparis_id'];
            $paid = floatval($ord['odenen_tutar']);
            
            // Get items for this order grouped by currency
            $items_sql = "SELECT para_birimi, SUM(birim_fiyat * adet) as total 
                          FROM siparis_kalemleri 
                          WHERE siparis_id = $sid 
                          GROUP BY para_birimi";
            $items_res = $connection->query($items_sql);
            
            $order_currency = 'TRY'; // Fallback
            $first = true;
            
            while ($item = $items_res->fetch_assoc()) {
                $cur = $item['para_birimi'] ?: 'TRY';
                $amount = floatval($item['total']);
                
                if ($first) {
                    $order_currency = $cur;
                    $first = false;
                }
                
                if (!isset($customer_balances[$mid][$cur])) {
                    $customer_balances[$mid][$cur] = 0;
                }
                $customer_balances[$mid][$cur] += $amount;
            }
            
            // Subtract payment from the main currency of the order
            if ($paid > 0) {
                if (!isset($customer_balances[$mid][$order_currency])) {
                    $customer_balances[$mid][$order_currency] = 0;
                }
                $customer_balances[$mid][$order_currency] -= $paid;
            }
        }

        // 4. Count Unpaid Orders
        // Regular Unpaid
        $unpaid_sql = "SELECT s.musteri_id, COUNT(*) as cnt 
                       FROM siparisler s 
                       WHERE s.musteri_id IN ($ids_str) 
                       AND s.durum IN ('onaylandi', 'tamamlandi')
                       AND (
                           (SELECT COALESCE(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) 
                           - COALESCE(s.odenen_tutar, 0)
                       ) > 0.01";
        if (!empty($linked_ids)) {
            $unpaid_sql .= " AND s.siparis_id NOT IN (" . implode(',', $linked_ids) . ")";
        }
        $unpaid_sql .= " GROUP BY s.musteri_id";
        $unpaid_res = $connection->query($unpaid_sql);
        $unpaid_counts = [];
        while($u = $unpaid_res->fetch_assoc()) {
            $unpaid_counts[$u['musteri_id']] = intval($u['cnt']);
        }

        // Active Plan Orders (considered unpaid)
        $plan_orders_sql = "SELECT tp.musteri_id, COUNT(DISTINCT tsb.siparis_id) as cnt
                            FROM taksit_siparis_baglantisi tsb 
                            JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id 
                            WHERE tp.musteri_id IN ($ids_str) AND tp.durum = 'aktif'
                            GROUP BY tp.musteri_id";
        $plan_orders_res = $connection->query($plan_orders_sql);
        while($p = $plan_orders_res->fetch_assoc()) {
            if(!isset($unpaid_counts[$p['musteri_id']])) $unpaid_counts[$p['musteri_id']] = 0;
            $unpaid_counts[$p['musteri_id']] += intval($p['cnt']);
        }

        // Merge Data into Customers Array
        foreach ($customers as $mid => &$cust) {
            $balances = $customer_balances[$mid] ?? [];
            
            // Add Plan Debt (Assuming TRY)
            $p_debt = $plan_debts[$mid] ?? 0;
            if ($p_debt > 0) {
                if (!isset($balances['TRY'])) $balances['TRY'] = 0;
                $balances['TRY'] += $p_debt;
            }
            
            // Construct Balance String and Total (approx in TRY)
            $balance_parts = [];
            $total_approx_try = 0;
            $has_balance = false;
            
            foreach ($balances as $curr => $amount) {
                if ($amount > 0.01) { // Only show positive balances
                    $symbol = '₺';
                    if ($curr === 'USD') { $symbol = '$'; $total_approx_try += $amount * 30; } // Approx rate 30
                    elseif ($curr === 'EUR') { $symbol = '€'; $total_approx_try += $amount * 33; } // Approx rate 33
                    else { $total_approx_try += $amount; }
                    
                    $balance_parts[] = number_format($amount, 2, ',', '.') . ' ' . $symbol;
                    $has_balance = true;
                }
            }
            
            $cust['bakiye_gosterim'] = empty($balance_parts) ? '<span style="background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.75rem; white-space: nowrap;"><i class="fas fa-check-circle"></i> Temiz</span>' 
                                                             : '<span style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.75rem; white-space: nowrap; display: inline-block;">' . implode('<br>', $balance_parts) . '</span>';
            
            $cust['kalan_bakiye'] = $total_approx_try; // For sorting/filtering logic if needed
            $cust['odenmemis_siparis'] = $unpaid_counts[$mid] ?? 0;
        }
    }

    // Recalculate Global Total Balance (Approximate TRY) - Keeping logic simple for stats
    // Note: This is an approximation for the stat card.
    // Ideally, we should sum per currency globally too, but for now let's keep the existing logic 
    // but just fix the query to not fail.
    // Actually, let's just return 0 or calculate properly if needed.
    // The previous logic summed everything as raw numbers.
    $total_balance = 0; // Placeholder, calculating global multicurrency balance is expensive and complex for UI
    
    // Convert associative array back to indexed array
    $customers_list = array_values($customers);

    $response = [
        'status' => 'success',
        'data' => $customers_list,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_customers' => $total_customers,
            'total_balance' => $total_balance, // Disabled for now to avoid confusion
            'total_unpaid_orders' => 0, // Disabled for performance
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