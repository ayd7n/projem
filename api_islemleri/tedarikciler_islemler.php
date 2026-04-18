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
    case 'get_suppliers':
        getSuppliers();
        break;
    case 'get_supplier':
        getSupplier();
        break;
    case 'add_supplier':
        addSupplier();
        break;
    case 'update_supplier':
        updateSupplier();
        break;
    case 'delete_supplier':
        deleteSupplier();
        break;
    case 'export_excel':
        exportExcel();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getSupplierExchangeRates($connection)
{
    $rates = ['USD' => 0.0, 'EUR' => 0.0];
    $kur_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $kur_result = $connection->query($kur_query);
    if ($kur_result) {
        while ($kur_row = $kur_result->fetch_assoc()) {
            if (($kur_row['ayar_anahtar'] ?? '') === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) ($kur_row['ayar_deger'] ?? 0));
            } elseif (($kur_row['ayar_anahtar'] ?? '') === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) ($kur_row['ayar_deger'] ?? 0));
            }
        }
    }

    return $rates;
}

function convertSupplierAmountToTl($amount, $currency, $rates)
{
    $amount = (float) $amount;
    $currency = strtoupper(trim((string) $currency));

    if ($currency === '' || $currency === 'TL' || $currency === 'TRY') {
        return $amount;
    }
    if ($currency === '$') {
        $currency = 'USD';
    } elseif ($currency === 'â‚¬') {
        $currency = 'EUR';
    }

    if ($currency === 'USD') {
        $usdRate = (float) ($rates['USD'] ?? 0);
        if ($usdRate <= 0) {
            throw new Exception('USD kuru tanimli degil veya 0.');
        }
        return $amount * $usdRate;
    }

    if ($currency === 'EUR') {
        $eurRate = (float) ($rates['EUR'] ?? 0);
        if ($eurRate <= 0) {
            throw new Exception('EUR kuru tanimli degil veya 0.');
        }
        return $amount * $eurRate;
    }

    return $amount;
}

function calculateSupplierTotals($row, $rates) {
    global $connection;
    
    // Toplam Alım Hesaplama (TL cinsinden)
    $purchase_query = "SELECT para_birimi, SUM(kullanilan_miktar * birim_fiyat) as total FROM stok_hareketleri_sozlesmeler WHERE tedarikci_id = " . (int)$row['tedarikci_id'] . " GROUP BY para_birimi";
    $p_result = $connection->query($purchase_query);
    $total_purchase_tl = 0;
    if ($p_result) {
        while($p_row = $p_result->fetch_assoc()) {
            $amount = floatval($p_row['total']);
            $currency = strtoupper($p_row['para_birimi']);
            $total_purchase_tl += convertSupplierAmountToTl($amount, $currency, $rates);
        }
    }

    // Toplam Ödeme Hesaplama (tedarikci_id bazlı, isim eşleşmesine bağlı değil)
    $payment_query = "SELECT para_birimi, SUM(toplu_odenen_miktar * birim_fiyat) as total
                      FROM cerceve_sozlesmeler
                      WHERE tedarikci_id = " . (int)$row['tedarikci_id'] . "
                      GROUP BY para_birimi";
    $pay_result = $connection->query($payment_query);
    $total_payment_tl = 0;
    if ($pay_result) {
        while ($pay_row = $pay_result->fetch_assoc()) {
            $amount = floatval($pay_row['total']);
            $currency = strtoupper(trim((string)($pay_row['para_birimi'] ?? 'TL')));
            $total_payment_tl += convertSupplierAmountToTl($amount, $currency, $rates);
        }
    }

    $row['total_purchase'] = $total_purchase_tl;
    $row['total_payment'] = $total_payment_tl;
    $row['balance'] = $total_purchase_tl - $total_payment_tl;
    
    return $row;
}

function getSuppliers() {
    global $connection;

    if (!yetkisi_var('page:view:tedarikciler')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçileri görüntüleme yetkiniz yok.']);
        return;
    }

    try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param' OR sektor LIKE '$search_param' OR vergi_no_tc LIKE '$search_param' OR adres LIKE '$search_param' OR aciklama_notlar LIKE '$search_param'";
    }

    $count_query = "SELECT COUNT(*) as total FROM tedarikciler " . $where_clause;
    $result = $connection->query($count_query);
    if (!$result) {
        throw new Exception('Tedarikci sayisi alinamadi: ' . $connection->error);
    }
    $total_suppliers = $result->fetch_assoc()['total'];

    $total_pages = $limit > 0 ? ceil($total_suppliers / $limit) : 0;

    $query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);
    if (!$result) {
        throw new Exception('Tedarikci listesi alinamadi: ' . $connection->error);
    }

    $suppliers = [];
    
    // Döviz kurlarını al
    $rates = getSupplierExchangeRates($connection);

    while ($row = $result->fetch_assoc()) {
        $suppliers[] = calculateSupplierTotals($row, $rates);
    }

    // GENEL TOPLAMLARI HESAPLA (Tüm sayfalar için)
    $all_suppliers_query = "SELECT * FROM tedarikciler " . $where_clause;
    $all_result = $connection->query($all_suppliers_query);
    if (!$all_result) {
        throw new Exception('Genel toplamlar alinamadi: ' . $connection->error);
    }
    $total_purchase_sum = 0;
    $total_payment_sum = 0;
    $total_balance_sum = 0;

    if ($all_result) {
        while ($row = $all_result->fetch_assoc()) {
            $row_totals = calculateSupplierTotals($row, $rates);
            $total_purchase_sum += $row_totals['total_purchase'];
            $total_payment_sum += $row_totals['total_payment'];
            $total_balance_sum += $row_totals['balance'];
        }
    }

    $response = [
        'status' => 'success',
        'data' => $suppliers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_suppliers' => $total_suppliers,
            'limit' => $limit
        ],
        'total_stats' => [
            'total_purchase' => $total_purchase_sum,
            'total_payment' => $total_payment_sum,
            'total_balance' => $total_balance_sum
        ]
    ];

    echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function exportExcel() {
    global $connection;
    include '../libs/SimpleXLSXGen.php';

    if (!yetkisi_var('page:view:tedarikciler')) {
        die('Yetkiniz yok.');
    }

    try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param' OR sektor LIKE '$search_param' OR vergi_no_tc LIKE '$search_param' OR adres LIKE '$search_param' OR aciklama_notlar LIKE '$search_param'";
    }

    $query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi";
    $result = $connection->query($query);
    if (!$result) {
        throw new Exception('Tedarikci listesi alinamadi: ' . $connection->error);
    }

    // Döviz kurlarını al
    $rates = getSupplierExchangeRates($connection);

    $excel_data = [
        ['Tedarikçi Adı', 'Sektör', 'Vergi/TC No', 'Telefon', 'Telefon 2', 'E-posta', 'Yetkili Kişi', 'Açıklama', 'Toplam Alım (TL)', 'Toplam Ödeme (TL)', 'Bakiye (TL)', 'Durum']
    ];

    while ($row = $result->fetch_assoc()) {
        $row = calculateSupplierTotals($row, $rates);
        
        $durum = 'Hesap Kapalı';
        if ($row['balance'] > 0) $durum = 'Borçlu';
        elseif ($row['balance'] < 0) $durum = 'Alacaklı';

        $excel_data[] = [
            $row['tedarikci_adi'],
            $row['sektor'] ?? '',
            $row['vergi_no_tc'],
            $row['telefon'],
            $row['telefon_2'],
            $row['e_posta'],
            $row['yetkili_kisi'],
            $row['aciklama_notlar'],
            number_format($row['total_purchase'], 2, ',', '.'),
            number_format($row['total_payment'], 2, ',', '.'),
            number_format(abs($row['balance']), 2, ',', '.'),
            $durum
        ];
    }

    $xlsx = Shuchkin\SimpleXLSXGen::fromArray($excel_data);
    $xlsx->downloadAs('tedarikciler_' . date('Y-m-d_H-i') . '.xlsx');
    exit;
    } catch (Exception $e) {
        die('Excel olusturulamadi: ' . $e->getMessage());
    }
}

function getSupplier() {
    global $connection;

    if (!yetkisi_var('page:view:tedarikciler')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi görüntüleme yetkiniz yok.']);
        return;
    }

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM tedarikciler WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $supplier = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $supplier]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi bulunamadı.']);
    }

    $stmt->close();
}

function addSupplier() {
    global $connection;

    if (!yetkisi_var('action:tedarikciler:create')) {
        echo json_encode(['status' => 'error', 'message' => 'Yeni tedarikçi ekleme yetkiniz yok.']);
        return;
    }

    $tedarikci_adi = $_POST['tedarikci_adi'] ?? '';
    $sektor = $_POST['sektor'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $telefon_2 = $_POST['telefon_2'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $yetkili_kisi = $_POST['yetkili_kisi'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';

    // Debug output to understand what's happening with telefon_2
    // error_log("Tedarikci API Add - telefon: '$telefon', telefon_2: '$telefon_2'");

    if (empty($tedarikci_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi adı zorunludur.']);
        return;
    }

    $query = "INSERT INTO tedarikciler (tedarikci_adi, sektor, vergi_no_tc, adres, telefon, telefon_2, e_posta, yetkili_kisi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssssss', $tedarikci_adi, $sektor, $vergi_no_tc, $adres, $telefon, $telefon_2, $e_posta, $yetkili_kisi, $aciklama_notlar);

    if ($stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_adi tedarikçisi sisteme eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateSupplier() {
    global $connection;

    if (!yetkisi_var('action:tedarikciler:edit')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi bilgilerini düzenleme yetkiniz yok.']);
        return;
    }

    $tedarikci_id = $_POST['tedarikci_id'] ?? '';
    $tedarikci_adi = $_POST['tedarikci_adi'] ?? '';
    $sektor = $_POST['sektor'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $telefon_2 = $_POST['telefon_2'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $yetkili_kisi = $_POST['yetkili_kisi'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';

    // Debug output to understand what's happening with telefon_2
    // error_log("Tedarikci API Update - telefon: '$telefon', telefon_2: '$telefon_2'");

    if (empty($tedarikci_id) || empty($tedarikci_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID ve tedarikçi adı alanları zorunludur.']);
        return;
    }

    // Eski tedarikçi adını almak için sorgu
    $old_supplier_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
    $old_stmt = $connection->prepare($old_supplier_query);
    $old_stmt->bind_param('i', $tedarikci_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_supplier = $old_result->fetch_assoc();
    $old_supplier_name = $old_supplier['tedarikci_adi'] ?? 'Bilinmeyen Tedarikçi';
    $old_stmt->close();

    $query = "UPDATE tedarikciler SET tedarikci_adi = ?, sektor = ?, vergi_no_tc = ?, adres = ?, telefon = ?, telefon_2 = ?, e_posta = ?, yetkili_kisi = ?, aciklama_notlar = ? WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssssssi', $tedarikci_adi, $sektor, $vergi_no_tc, $adres, $telefon, $telefon_2, $e_posta, $yetkili_kisi, $aciklama_notlar, $tedarikci_id);

    if ($stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_supplier_name tedarikçisi $tedarikci_adi olarak güncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteSupplier() {
    global $connection;

    if (!yetkisi_var('action:tedarikciler:delete')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi silme yetkiniz yok.']);
        return;
    }

    $tedarikci_id = $_POST['tedarikci_id'] ?? '';
    if (empty($tedarikci_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID gerekli.']);
        return;
    }

    // Silinen tedarikçi adını almak için sorgu
    $old_supplier_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
    $old_stmt = $connection->prepare($old_supplier_query);
    $old_stmt->bind_param('i', $tedarikci_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_supplier = $old_result->fetch_assoc();
    $deleted_supplier_name = $old_supplier['tedarikci_adi'] ?? 'Bilinmeyen Tedarikçi';
    $old_stmt->close();

    $query = "DELETE FROM tedarikciler WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $tedarikci_id);

    if ($stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_supplier_name tedarikçisi sistemden silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>





