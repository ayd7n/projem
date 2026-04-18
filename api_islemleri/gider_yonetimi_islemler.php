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
    case 'get_expenses':
        getExpenses();
        break;
    case 'get_expense':
        getExpense();
        break;
    case 'get_total_expenses':
        getTotalExpenses();
        break;
    case 'add_expense':
        addExpense();
        break;
    case 'update_expense':
        updateExpense();
        break;
    case 'delete_expense':
        deleteExpense();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getExpenses() {
    global $connection;

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
    $search = trim($_GET['search'] ?? '');

    if ($page < 1) {
        $page = 1;
    }

    if ($per_page < 1) {
        $per_page = 10;
    }
    $per_page = min($per_page, 200);

    $offset = ($page - 1) * $per_page;

    $searchLike = '';
    $whereClause = '';
    if ($search !== '') {
        $searchLike = "'" . $connection->real_escape_string('%' . $search . '%') . "'";
        $whereClause = " WHERE (
            kategori LIKE {$searchLike}
            OR aciklama LIKE {$searchLike}
            OR odeme_tipi LIKE {$searchLike}
            OR fatura_no LIKE {$searchLike}
            OR kaydeden_personel_ismi LIKE {$searchLike}
            OR CAST(kaydeden_personel_id AS CHAR) LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%d.%m.%Y') LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%Y-%m-%d') LIKE {$searchLike}
            OR CAST(tutar AS CHAR) LIKE {$searchLike}
            OR odeme_yapilan_firma LIKE {$searchLike}
        )";
    }

    $countQuery = "SELECT COUNT(*) AS total FROM gider_yonetimi" . $whereClause;
    $countResult = $connection->query($countQuery);
    if (!$countResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam kayıt sayısı alınamadı: ' . $connection->error]);
        return;
    }

    $totalRow = $countResult->fetch_assoc();
    $total = isset($totalRow['total']) ? (int) $totalRow['total'] : 0;
    $countResult->free();

    $sumQuery = "SELECT IFNULL(SUM(tutar), 0) AS total_sum FROM gider_yonetimi" . $whereClause;
    $sumResult = $connection->query($sumQuery);
    if (!$sumResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam tutar alınamadı: ' . $connection->error]);
        return;
    }

    $sumRow = $sumResult->fetch_assoc();
    $filteredSum = isset($sumRow['total_sum']) ? (float) $sumRow['total_sum'] : 0.0;
    $sumResult->free();

    $maxPage = $total > 0 ? (int) ceil($total / $per_page) : 1;
    if ($total > 0 && $page > $maxPage) {
        $page = $maxPage;
        $offset = ($page - 1) * $per_page;
    }

    if ($total === 0) {
        $page = 1;
        $offset = 0;
    }

    $perPageSql = (int) $per_page;
    $offsetSql = (int) $offset;

    $dataQuery = "SELECT * FROM gider_yonetimi" . $whereClause . " ORDER BY gider_id DESC LIMIT {$perPageSql} OFFSET {$offsetSql}";
    $dataResult = $connection->query($dataQuery);
    if (!$dataResult) {
        echo json_encode(['status' => 'error', 'message' => 'Giderler alınamadı: ' . $connection->error]);
        return;
    }

    $expenses = [];
    while ($row = $dataResult->fetch_assoc()) {
        $expenses[] = $row;
    }
    $dataResult->free();

    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $overallSum = 0.0;
    $overallResult = $connection->query("SELECT IFNULL(SUM(tutar), 0) AS overall_sum FROM gider_yonetimi WHERE DATE(tarih) BETWEEN '$current_month_start' AND '$current_month_end'");
    if ($overallResult && $overallRow = $overallResult->fetch_assoc()) {
        $overallSum = (float) $overallRow['overall_sum'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $expenses,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_sum' => $filteredSum,
        'overall_sum' => $overallSum
    ]);
}

function getExpense() {
    global $connection;

    $id = (int)($_GET['id'] ?? 0);
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM gider_yonetimi WHERE gider_id = $id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $expense = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $expense]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider bulunamadı.']);
    }
}

function getTotalExpenses() {
    global $connection;

    $query = "SELECT IFNULL(SUM(tutar), 0) AS total FROM gider_yonetimi";
    $result = $connection->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => (float)$row['total']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam giderler alınırken hata oluştu.']);
    }
}

function getFinanceRates()
{
    global $connection;
    $rates = ['TL' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $q = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $result = $connection->query($q);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) $row['ayar_deger']);
            }
            if ($row['ayar_anahtar'] === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) $row['ayar_deger']);
            }
        }
    }
    return $rates;
}

function normalizeFinanceCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if (!in_array($currency, ['TL', 'USD', 'EUR'], true)) {
        return 'TL';
    }
    return $currency;
}

function convertTlToCashCurrency($amountTl, $currency, $rates)
{
    $currency = normalizeFinanceCurrency($currency);
    $amountTl = (float) $amountTl;
    if ($currency === 'TL') {
        return $amountTl;
    }
    $rate = (float) ($rates[$currency] ?? 0);
    if ($rate <= 0) {
        throw new Exception($currency . ' kuru tanimli degil veya 0.');
    }
    return round($amountTl / $rate, 2);
}

function convertCurrencyToTl($amount, $currency, $rates)
{
    $currency = normalizeFinanceCurrency($currency);
    $amount = (float) $amount;
    if ($currency === 'TL') {
        return round($amount, 2);
    }
    $rate = (float) ($rates[$currency] ?? 0);
    if ($rate <= 0) {
        throw new Exception($currency . ' kuru tanimli degil veya 0.');
    }
    return round($amount * $rate, 2);
}

function ensureCompanyCashAccount($currency)
{
    global $connection;
    $currency = normalizeFinanceCurrency($currency);
    if (!in_array($currency, ['TL', 'USD', 'EUR'], true)) {
        return;
    }
    $exists = $connection->query("SELECT id FROM sirket_kasasi WHERE para_birimi = '$currency' LIMIT 1");
    if ($exists && $exists->num_rows > 0) {
        return;
    }
    if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$currency', 0)")) {
        throw new Exception('Sirket kasasi hesabi olusturulamadi: ' . $connection->error);
    }
}

function adjustCompanyCashBalance($currency, $delta)
{
    global $connection;
    $currency = normalizeFinanceCurrency($currency);
    ensureCompanyCashAccount($currency);
    $delta = (float) $delta;
    if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + ($delta) WHERE para_birimi = '$currency'")) {
        throw new Exception('Sirket kasasi bakiyesi guncellenemedi: ' . $connection->error);
    }
}

function getExpenseRecord($gider_id)
{
    global $connection;
    $gider_id = (int) $gider_id;
    $q = "SELECT gider_id, tarih, tutar, kategori, aciklama, fatura_no, odeme_tipi, odeme_yapilan_firma, kasa_secimi, cek_secimi FROM gider_yonetimi WHERE gider_id = $gider_id FOR UPDATE";
    $r = $connection->query($q);
    if (!$r || $r->num_rows === 0) {
        return null;
    }
    return $r->fetch_assoc();
}

function getLinkedExpenseMovement($gider_id)
{
    global $connection;
    $gider_id = (int) $gider_id;
    $q = "SELECT * FROM kasa_hareketleri WHERE kaynak_tablo = 'gider_yonetimi' AND kaynak_id = $gider_id ORDER BY hareket_id DESC LIMIT 1";
    $r = $connection->query($q);
    if ($r && $r->num_rows > 0) {
        return $r->fetch_assoc();
    }
    return null;
}

function deleteLinkedExpenseMovements($gider_id)
{
    global $connection;
    $gider_id = (int) $gider_id;
    if (!$connection->query("DELETE FROM kasa_hareketleri WHERE kaynak_tablo = 'gider_yonetimi' AND kaynak_id = $gider_id")) {
        throw new Exception('Eski kasa hareketleri silinemedi: ' . $connection->error);
    }
}

function isCheckUsedByOtherExpenses($cek_id, $exclude_gider_id)
{
    global $connection;
    $cek_id = (int) $cek_id;
    $exclude_gider_id = (int) $exclude_gider_id;
    if ($cek_id <= 0) {
        return false;
    }
    $q = "SELECT COUNT(*) as cnt FROM gider_yonetimi WHERE cek_secimi = $cek_id AND gider_id <> $exclude_gider_id";
    $r = $connection->query($q);
    if (!$r) {
        throw new Exception('Cek kullanim kontrolu yapilamadi: ' . $connection->error);
    }
    $row = $r->fetch_assoc();
    return ((int) ($row['cnt'] ?? 0)) > 0;
}

function reserveCheckForExpense($cek_id, $current_gider_id)
{
    global $connection;
    $cek_id = (int) $cek_id;
    $current_gider_id = (int) $current_gider_id;
    if ($cek_id <= 0) {
        throw new Exception('Gecerli bir cek secimi yapilamadi.');
    }

    $q = "SELECT cek_id, cek_tutari, cek_para_birimi, cek_durumu FROM cek_kasasi WHERE cek_id = $cek_id FOR UPDATE";
    $r = $connection->query($q);
    if (!$r || $r->num_rows === 0) {
        throw new Exception('Secilen cek bulunamadi.');
    }
    $cek = $r->fetch_assoc();
    $cek_durumu = $cek['cek_durumu'] ?? '';
    if ($cek_durumu !== 'alindi') {
        throw new Exception('Secilen cek kullanima uygun degil. Durum: ' . $cek_durumu);
    }
    if (isCheckUsedByOtherExpenses($cek_id, $current_gider_id)) {
        throw new Exception('Secilen cek baska bir giderde kullaniliyor.');
    }
    $set_link = "UPDATE cek_kasasi
                 SET cek_durumu = 'kullanildi',
                     cek_kullanim_tarihi = NOW(),
                     ilgili_tablo = 'gider_yonetimi',
                     ilgili_id = $current_gider_id
                 WHERE cek_id = $cek_id";
    if (!$connection->query($set_link)) {
        throw new Exception('Cek durumu guncellenemedi: ' . $connection->error);
    }

    return [
        'tutar' => (float) ($cek['cek_tutari'] ?? 0),
        'para_birimi' => normalizeFinanceCurrency($cek['cek_para_birimi'] ?? 'TL')
    ];
}

function releaseCheckFromExpense($cek_id, $current_gider_id)
{
    global $connection;
    $cek_id = (int) $cek_id;
    $current_gider_id = (int) $current_gider_id;
    if ($cek_id <= 0) {
        return;
    }
    if (isCheckUsedByOtherExpenses($cek_id, $current_gider_id)) {
        return;
    }
    $clear_link = "UPDATE cek_kasasi
                   SET cek_durumu = 'alindi',
                       cek_kullanim_tarihi = NULL,
                       ilgili_tablo = NULL,
                       ilgili_id = NULL
                   WHERE cek_id = $cek_id";
    if (!$connection->query($clear_link)) {
        throw new Exception('Cek durumu eski haline dondurulemedi: ' . $connection->error);
    }
}

function reverseOldExpenseEffects($old_expense, $gider_id)
{
    $gider_id = (int) $gider_id;
    $old_kasa = $old_expense['kasa_secimi'] ?? 'TL';
    $old_kasa = $old_kasa === 'cek_kasasi' ? 'cek_kasasi' : normalizeFinanceCurrency($old_kasa);
    $old_cek = (int) ($old_expense['cek_secimi'] ?? 0);

    if ($old_kasa === 'cek_kasasi' && $old_cek > 0) {
        releaseCheckFromExpense($old_cek, $gider_id);
    } elseif (in_array($old_kasa, ['TL', 'USD', 'EUR'], true)) {
        $movement = getLinkedExpenseMovement($gider_id);
        if ($movement && ($movement['kasa_adi'] ?? '') !== 'cek_kasasi') {
            $currency = normalizeFinanceCurrency($movement['para_birimi'] ?? $old_kasa);
            $iade_miktari = (float) ($movement['tutar'] ?? 0);
            ensureCompanyCashAccount($currency);
            adjustCompanyCashBalance($currency, $iade_miktari);
        } else {
            $rates = getFinanceRates();
            $old_tutar_tl = (float) ($old_expense['tutar'] ?? 0);
            $iade_miktari = convertTlToCashCurrency($old_tutar_tl, $old_kasa, $rates);
            ensureCompanyCashAccount($old_kasa);
            adjustCompanyCashBalance($old_kasa, $iade_miktari);
        }
    }

    deleteLinkedExpenseMovements($gider_id);
}

function insertExpenseCashMovement($tarih, $kasa_adi, $cek_secimi, $tutar, $para_birimi, $tl_karsiligi, $gider_id, $aciklama, $personel_adi, $odeme_yapilan_firma, $odeme_tipi)
{
    global $connection;
    $cek_id_col = $cek_secimi ? (int) $cek_secimi : "NULL";
    $gider_id = (int) $gider_id;
    $tutar = (float) $tutar;
    $tl_karsiligi = (float) $tl_karsiligi;
    $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
        VALUES ('$tarih', 'gider_cikisi', '$kasa_adi', $cek_id_col, $tutar, '$para_birimi', $tl_karsiligi, 'gider_yonetimi', $gider_id, '$aciklama', '$personel_adi', '$odeme_yapilan_firma', '$odeme_tipi')";
    if (!$connection->query($hareket_sql)) {
        throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);
    }
}

function applyNewExpenseEffects($tutar_tl, $kasa_secimi, $cek_secimi, $gider_id, $tarih, $aciklama, $personel_adi, $odeme_yapilan_firma, &$odeme_tipi)
{
    $rates = getFinanceRates();
    $kasa_adi = ($kasa_secimi === 'cek_kasasi') ? 'cek_kasasi' : $kasa_secimi;
    $hareket_tutar = (float) $tutar_tl;
    $hareket_para_birimi = 'TL';
    $tl_karsiligi = (float) $tutar_tl;

    if ($kasa_secimi === 'cek_kasasi') {
        if (!$cek_secimi) {
            throw new Exception('Cek kasasindan odeme icin cek secmelisiniz.');
        }
        $cek_info = reserveCheckForExpense((int) $cek_secimi, (int) $gider_id);
        $odeme_tipi = 'Cek';
        $hareket_tutar = (float) ($cek_info['tutar'] ?? 0);
        if ($hareket_tutar <= 0) {
            $hareket_tutar = (float) $tutar_tl;
        }
        $hareket_para_birimi = normalizeFinanceCurrency($cek_info['para_birimi'] ?? 'TL');
        $tl_karsiligi = convertCurrencyToTl($hareket_tutar, $hareket_para_birimi, $rates);
    } else {
        $dusulecek_miktar = convertTlToCashCurrency($tutar_tl, $kasa_secimi, $rates);
        ensureCompanyCashAccount($kasa_secimi);
        adjustCompanyCashBalance($kasa_secimi, -$dusulecek_miktar);
        $hareket_tutar = $dusulecek_miktar;
        $hareket_para_birimi = $kasa_secimi;
        $tl_karsiligi = convertCurrencyToTl($hareket_tutar, $hareket_para_birimi, $rates);
    }

    insertExpenseCashMovement(
        $tarih,
        $kasa_adi,
        $cek_secimi,
        $hareket_tutar,
        $hareket_para_birimi,
        $tl_karsiligi,
        $gider_id,
        $aciklama,
        $personel_adi,
        $odeme_yapilan_firma,
        $odeme_tipi
    );
}

function addExpense()
{
    global $connection;

    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $fatura_no = $connection->real_escape_string($_POST['fatura_no'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $personel_id = (int) $_SESSION['user_id'];
    $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
    $odeme_yapilan_firma = $connection->real_escape_string($_POST['odeme_yapilan_firma'] ?? '');
    $kasa_input = trim((string) ($_POST['kasa_secimi'] ?? 'TL'));
    $kasa_secimi = ($kasa_input === 'cek_kasasi') ? 'cek_kasasi' : normalizeFinanceCurrency($kasa_input);
    $cek_secimi = !empty($_POST['cek_secimi']) ? (int) $_POST['cek_secimi'] : null;

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, aciklama ve odeme tipi alanlari zorunludur.']);
        return;
    }
    if ($kasa_secimi === 'cek_kasasi' && !$cek_secimi) {
        echo json_encode(['status' => 'error', 'message' => 'Cek kasasi secildiginde cek secimi zorunludur.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $cek_col = $cek_secimi ? ", cek_secimi" : "";
        $cek_val = $cek_secimi ? ", $cek_secimi" : "";
        $insert = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi, odeme_yapilan_firma, kasa_secimi $cek_col)
                   VALUES ('$tarih', $tutar, '$kategori', '$aciklama', $personel_id, '$personel_adi', '$fatura_no', '$odeme_tipi', '$odeme_yapilan_firma', '$kasa_secimi' $cek_val)";
        if (!$connection->query($insert)) {
            throw new Exception('Gider eklenemedi: ' . $connection->error);
        }
        $gider_id = (int) $connection->insert_id;

        applyNewExpenseEffects($tutar, $kasa_secimi, $cek_secimi, $gider_id, $tarih, $aciklama, $personel_adi, $odeme_yapilan_firma, $odeme_tipi);

        if (!$connection->query("UPDATE gider_yonetimi SET odeme_tipi = '$odeme_tipi' WHERE gider_id = $gider_id")) {
            throw new Exception('Gider odeme tipi guncellenemedi: ' . $connection->error);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$kategori kategorisinde $tutar TL tutarinda gider eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Gider basariyla eklendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateExpense()
{
    global $connection;

    $gider_id = (int) ($_POST['gider_id'] ?? 0);
    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $fatura_no = $connection->real_escape_string($_POST['fatura_no'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $odeme_yapilan_firma = $connection->real_escape_string($_POST['odeme_yapilan_firma'] ?? '');
    $kasa_input = trim((string) ($_POST['kasa_secimi'] ?? 'TL'));
    $kasa_secimi = ($kasa_input === 'cek_kasasi') ? 'cek_kasasi' : normalizeFinanceCurrency($kasa_input);
    $cek_secimi = !empty($_POST['cek_secimi']) ? (int) $_POST['cek_secimi'] : null;

    if (empty($gider_id) || empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tum alanlar zorunludur.']);
        return;
    }
    if ($kasa_secimi === 'cek_kasasi' && !$cek_secimi) {
        echo json_encode(['status' => 'error', 'message' => 'Cek kasasi secildiginde cek secimi zorunludur.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $old_expense = getExpenseRecord($gider_id);
        if (!$old_expense) {
            throw new Exception('Gider bulunamadi.');
        }
        $old_category = $old_expense['kategori'] ?? 'Bilinmeyen Kategori';
        $old_amount = (float) ($old_expense['tutar'] ?? 0);

        reverseOldExpenseEffects($old_expense, $gider_id);

        $cek_sql = $cek_secimi ? (string) $cek_secimi : 'NULL';
        $update = "UPDATE gider_yonetimi
                   SET tarih = '$tarih',
                       tutar = $tutar,
                       kategori = '$kategori',
                       aciklama = '$aciklama',
                       fatura_no = '$fatura_no',
                       odeme_tipi = '$odeme_tipi',
                       odeme_yapilan_firma = '$odeme_yapilan_firma',
                       kasa_secimi = '$kasa_secimi',
                       cek_secimi = $cek_sql
                   WHERE gider_id = $gider_id";
        if (!$connection->query($update)) {
            throw new Exception('Gider guncellenemedi: ' . $connection->error);
        }

        $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
        applyNewExpenseEffects($tutar, $kasa_secimi, $cek_secimi, $gider_id, $tarih, $aciklama, $personel_adi, $odeme_yapilan_firma, $odeme_tipi);
        $odeme_tipi = $connection->real_escape_string($odeme_tipi);
        if (!$connection->query("UPDATE gider_yonetimi SET odeme_tipi = '$odeme_tipi' WHERE gider_id = $gider_id")) {
            throw new Exception('Gider odeme tipi guncellenemedi: ' . $connection->error);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_category kategorisindeki $old_amount TL tutarli gider guncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Gider basariyla guncellendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteExpense()
{
    global $connection;
    $gider_id = (int) ($_POST['gider_id'] ?? 0);
    if (empty($gider_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $old_expense = getExpenseRecord($gider_id);
        if (!$old_expense) {
            throw new Exception('Gider bulunamadi.');
        }
        $deleted_category = $old_expense['kategori'] ?? 'Bilinmeyen Kategori';
        $deleted_amount = (float) ($old_expense['tutar'] ?? 0);

        reverseOldExpenseEffects($old_expense, $gider_id);

        if (!$connection->query("DELETE FROM gider_yonetimi WHERE gider_id = $gider_id")) {
            throw new Exception('Gider silinemedi: ' . $connection->error);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_category kategorisindeki $deleted_amount TL tutarli gider silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Gider basariyla silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
