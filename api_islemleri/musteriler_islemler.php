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
    while ($row = $result->fetch_assoc()) {
        // Calculate balance for each customer
        $musteri_id = $row['musteri_id'];
        
        // Get total order amount (only approved and completed orders)
        $balance_query = "SELECT 
            COALESCE(SUM(
                (SELECT COALESCE(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id)
            ), 0) as toplam_tutar,
            COALESCE(SUM(s.odenen_tutar), 0) as odenen_tutar
            FROM siparisler s 
            WHERE s.musteri_id = $musteri_id 
            AND s.durum IN ('onaylandi', 'tamamlandi')";
        
        $balance_result = $connection->query($balance_query);
        $balance_data = $balance_result->fetch_assoc();
        
        $toplam_tutar = floatval($balance_data['toplam_tutar'] ?? 0);
        $odenen_tutar = floatval($balance_data['odenen_tutar'] ?? 0);
        $kalan_bakiye = $toplam_tutar - $odenen_tutar;
        
        // Count unpaid orders
        $unpaid_query = "SELECT COUNT(*) as unpaid_count FROM siparisler s 
            WHERE s.musteri_id = $musteri_id 
            AND s.durum IN ('onaylandi', 'tamamlandi')
            AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
            AND (
                (SELECT COALESCE(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) 
                - COALESCE(s.odenen_tutar, 0)
            ) > 0.01";
        $unpaid_result = $connection->query($unpaid_query);
        $unpaid_data = $unpaid_result->fetch_assoc();
        $odenmemis_siparis = intval($unpaid_data['unpaid_count'] ?? 0);
        
        $row['toplam_tutar'] = $toplam_tutar;
        $row['odenen_tutar'] = $odenen_tutar;
        $row['kalan_bakiye'] = $kalan_bakiye;
        $row['odenmemis_siparis'] = $odenmemis_siparis;
        
        $customers[] = $row;
    }

    // Calculate total balance for ALL customers (not just current page)
    $total_balance_query = "SELECT 
        COALESCE(SUM(
            (SELECT COALESCE(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id)
            - COALESCE(s.odenen_tutar, 0)
        ), 0) as total_balance
        FROM siparisler s 
        WHERE s.durum IN ('onaylandi', 'tamamlandi')";
    $total_balance_result = $connection->query($total_balance_query);
    $total_balance_data = $total_balance_result->fetch_assoc();
    $total_balance = max(0, floatval($total_balance_data['total_balance'] ?? 0));

    // Calculate total unpaid orders count
    $total_unpaid_query = "SELECT COUNT(*) as unpaid_count FROM siparisler s 
        WHERE s.durum IN ('onaylandi', 'tamamlandi')
        AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
        AND (
            (SELECT COALESCE(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) 
            - COALESCE(s.odenen_tutar, 0)
        ) > 0.01";
    $total_unpaid_result = $connection->query($total_unpaid_query);
    $total_unpaid_data = $total_unpaid_result->fetch_assoc();
    $total_unpaid_orders = intval($total_unpaid_data['unpaid_count'] ?? 0);

    $response = [
        'status' => 'success',
        'data' => $customers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_customers' => $total_customers,
            'total_balance' => $total_balance,
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