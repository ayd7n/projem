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

function calculateSupplierTotals($row, $dolar_kuru, $euro_kuru) {
    global $connection;
    
    // Toplam Alım Hesaplama (TL cinsinden)
    $purchase_query = "SELECT para_birimi, SUM(kullanilan_miktar * birim_fiyat) as total FROM stok_hareketleri_sozlesmeler WHERE tedarikci_id = " . (int)$row['tedarikci_id'] . " GROUP BY para_birimi";
    $p_result = $connection->query($purchase_query);
    $total_purchase_tl = 0;
    if ($p_result) {
        while($p_row = $p_result->fetch_assoc()) {
            $amount = floatval($p_row['total']);
            $currency = strtoupper($p_row['para_birimi']);
            if($currency == 'USD' || $currency == '$') $total_purchase_tl += $amount * $dolar_kuru;
            elseif($currency == 'EUR' || $currency == '€') $total_purchase_tl += $amount * $euro_kuru;
            else $total_purchase_tl += $amount;
        }
    }

    // Toplam Ödeme Hesaplama
    $tedarikci_adi_safe = $connection->real_escape_string($row['tedarikci_adi']);
    $payment_query = "SELECT SUM(tutar) as total FROM gider_yonetimi WHERE odeme_yapilan_firma = '$tedarikci_adi_safe'";
    $pay_result = $connection->query($payment_query);
    $total_payment_tl = 0;
    if ($pay_result) {
        $pay_row = $pay_result->fetch_assoc();
        $total_payment_tl = floatval($pay_row['total'] ?? 0);
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

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param'";
    }

    $count_query = "SELECT COUNT(*) as total FROM tedarikciler " . $where_clause;
    $result = $connection->query($count_query);
    $total_suppliers = $result->fetch_assoc()['total'];

    $total_pages = $limit > 0 ? ceil($total_suppliers / $limit) : 0;

    $query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);

    $suppliers = [];
    
    // Döviz kurlarını al
    $dolar_kuru = 1;
    $euro_kuru = 1;
    $kur_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $kur_result = $connection->query($kur_query);
    while ($kur_row = $kur_result->fetch_assoc()) {
        if ($kur_row['ayar_anahtar'] == 'dolar_kuru') $dolar_kuru = floatval($kur_row['ayar_deger']);
        elseif ($kur_row['ayar_anahtar'] == 'euro_kuru') $euro_kuru = floatval($kur_row['ayar_deger']);
    }

    while ($row = $result->fetch_assoc()) {
        $suppliers[] = calculateSupplierTotals($row, $dolar_kuru, $euro_kuru);
    }

    // GENEL TOPLAMLARI HESAPLA (Tüm sayfalar için)
    $all_suppliers_query = "SELECT * FROM tedarikciler " . $where_clause;
    $all_result = $connection->query($all_suppliers_query);
    $total_purchase_sum = 0;
    $total_payment_sum = 0;
    $total_balance_sum = 0;

    if ($all_result) {
        while ($row = $all_result->fetch_assoc()) {
            $row_totals = calculateSupplierTotals($row, $dolar_kuru, $euro_kuru);
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
}

function exportExcel() {
    global $connection;
    include '../libs/SimpleXLSXGen.php';

    if (!yetkisi_var('page:view:tedarikciler')) {
        die('Yetkiniz yok.');
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param' OR telefon LIKE '$search_param' OR telefon_2 LIKE '$search_param'";
    }

    $query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi";
    $result = $connection->query($query);

    // Döviz kurlarını al
    $dolar_kuru = 1;
    $euro_kuru = 1;
    $kur_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $kur_result = $connection->query($kur_query);
    while ($kur_row = $kur_result->fetch_assoc()) {
        if ($kur_row['ayar_anahtar'] == 'dolar_kuru') $dolar_kuru = floatval($kur_row['ayar_deger']);
        elseif ($kur_row['ayar_anahtar'] == 'euro_kuru') $euro_kuru = floatval($kur_row['ayar_deger']);
    }

    $excel_data = [
        ['Tedarikçi Adı', 'Vergi/TC No', 'Telefon', 'Telefon 2', 'E-posta', 'Yetkili Kişi', 'Açıklama', 'Toplam Alım (TL)', 'Toplam Ödeme (TL)', 'Bakiye (TL)', 'Durum']
    ];

    while ($row = $result->fetch_assoc()) {
        $row = calculateSupplierTotals($row, $dolar_kuru, $euro_kuru);
        
        $durum = 'Hesap Kapalı';
        if ($row['balance'] > 0) $durum = 'Borçlu';
        elseif ($row['balance'] < 0) $durum = 'Alacaklı';

        $excel_data[] = [
            $row['tedarikci_adi'],
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

    $query = "INSERT INTO tedarikciler (tedarikci_adi, vergi_no_tc, adres, telefon, telefon_2, e_posta, yetkili_kisi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssssssss', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $telefon_2, $e_posta, $yetkili_kisi, $aciklama_notlar);

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

    $query = "UPDATE tedarikciler SET tedarikci_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, telefon_2 = ?, e_posta = ?, yetkili_kisi = ?, aciklama_notlar = ? WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssssssssi', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $telefon_2, $e_posta, $yetkili_kisi, $aciklama_notlar, $tedarikci_id);

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
