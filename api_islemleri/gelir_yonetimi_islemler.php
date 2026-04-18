<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum aÃ§manÄ±z gerekiyor.']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu iÅŸlem iÃ§in yetkiniz yok.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_incomes':
        getIncomes();
        break;
    case 'get_income':
        getIncome();
        break;
    case 'get_total_income':
        getTotalIncome();
        break;
    case 'add_income':
        addIncome();
        break;
    case 'update_income':
        updateIncome();
        break;
    case 'delete_income':
        deleteIncome();
        break;
    case 'get_pending_orders':
        getPendingOrders();
        break;
    case 'get_pending_stats':
        getPendingStats();
        break;
    // New Installment Actions
    case 'get_customers_with_debt':
        getCustomersWithDebt();
        break;
    case 'get_customer_orders_for_plan':
        getCustomerOrdersForPlan();
        break;
    case 'create_installment_plan':
        createInstallmentPlan();
        break;
    case 'get_installment_plans':
        getInstallmentPlans();
        break;
    case 'get_plan_details':
        getPlanDetails();
        break;
    case 'pay_installment':
        payInstallment();
        break;
    case 'cancel_installment_plan':
        cancelInstallmentPlan();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'GeÃ§ersiz iÅŸlem.']);
}

function getIncomes()
{
    global $connection;

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
    $search = trim($_GET['search'] ?? '');

    if ($page < 1)
        $page = 1;
    if ($per_page < 1)
        $per_page = 10;
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
            OR kaydeden_personel_ismi LIKE {$searchLike}
            OR musteri_adi LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%d.%m.%Y') LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%Y-%m-%d') LIKE {$searchLike}
            OR CAST(tutar AS CHAR) LIKE {$searchLike}
        )";
    }

    $countQuery = "SELECT COUNT(*) AS total FROM gelir_yonetimi" . $whereClause;
    $countResult = $connection->query($countQuery);
    if (!$countResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam kayÄ±t sayÄ±sÄ± alÄ±namadÄ±: ' . $connection->error]);
        return;
    }

    $totalRow = $countResult->fetch_assoc();
    $total = isset($totalRow['total']) ? (int) $totalRow['total'] : 0;
    $countResult->free();

    $rates = getIncomeRates();
    $sumQuery = "SELECT para_birimi, IFNULL(SUM(tutar), 0) AS total_sum FROM gelir_yonetimi" . $whereClause . " GROUP BY para_birimi";
    $sumResult = $connection->query($sumQuery);
    $filteredSum = 0.0;
    $filteredSumByCurrency = ['TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
    if ($sumResult) {
        while ($sumRow = $sumResult->fetch_assoc()) {
            $currency = incomeNormalizeCurrency($sumRow['para_birimi'] ?? 'TL');
            $amount = isset($sumRow['total_sum']) ? (float) $sumRow['total_sum'] : 0.0;
            $filteredSumByCurrency[$currency] += $amount;

            if ($currency === 'USD') {
                $usdRate = (float) ($rates['USD'] ?? 0);
                if ($usdRate <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'USD kuru tanimli degil veya 0.']);
                    return;
                }
                $filteredSum += $amount * $usdRate;
            } elseif ($currency === 'EUR') {
                $eurRate = (float) ($rates['EUR'] ?? 0);
                if ($eurRate <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'EUR kuru tanimli degil veya 0.']);
                    return;
                }
                $filteredSum += $amount * $eurRate;
            } else {
                $filteredSum += $amount;
            }
        }
        $sumResult->free();
    }

    $maxPage = $total > 0 ? (int) ceil($total / $per_page) : 1;
    if ($total > 0 && $page > $maxPage) {
        $page = $maxPage;
        $offset = ($page - 1) * $per_page;
    }

    if ($total === 0) {
        $page = 1;
        $offset = 0;
    }

    $dataQuery = "SELECT * FROM gelir_yonetimi" . $whereClause . " ORDER BY tarih DESC, gelir_id DESC LIMIT {$per_page} OFFSET {$offset}";
    $dataResult = $connection->query($dataQuery);

    $incomes = [];
    if ($dataResult) {
        while ($row = $dataResult->fetch_assoc()) {
            $incomes[] = $row;
        }
        $dataResult->free();
    }

    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $overallResult = $connection->query("SELECT IFNULL(SUM(tutar), 0) AS overall_sum, para_birimi FROM gelir_yonetimi WHERE DATE(tarih) BETWEEN '$current_month_start' AND '$current_month_end' GROUP BY para_birimi");
    $overallSums = [];
    if ($overallResult) {
        while ($row = $overallResult->fetch_assoc()) {
            $currency = incomeNormalizeCurrency($row['para_birimi'] ?? 'TL');
            if (!isset($overallSums[$currency])) {
                $overallSums[$currency] = 0.0;
            }
            $overallSums[$currency] += (float) $row['overall_sum'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $incomes,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_sum' => round($filteredSum, 2),
        'total_sum_by_currency' => array_map(function ($value) {
            return round((float) $value, 2);
        }, $filteredSumByCurrency),
        'overall_sum' => array_map(function ($value) {
            return round((float) $value, 2);
        }, $overallSums)
    ]);
}

function getIncome()
{
    global $connection;

    $id = (int) ($_GET['id'] ?? 0);
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM gelir_yonetimi WHERE gelir_id = $id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $income = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $income]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gelir bulunamadÄ±.']);
    }
}

function getTotalIncome()
{
    global $connection;

    $rates = getIncomeRates();
    $query = "SELECT para_birimi, IFNULL(SUM(tutar), 0) AS total FROM gelir_yonetimi GROUP BY para_birimi";
    $result = $connection->query($query);
    if ($result) {
        $byCurrency = ['TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
        $totalTl = 0.0;

        while ($row = $result->fetch_assoc()) {
            $currency = incomeNormalizeCurrency($row['para_birimi'] ?? 'TL');
            $amount = (float) ($row['total'] ?? 0);
            $byCurrency[$currency] += $amount;

            if ($currency === 'USD') {
                $usdRate = (float) ($rates['USD'] ?? 0);
                if ($usdRate <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'USD kuru tanimli degil veya 0.']);
                    return;
                }
                $totalTl += $amount * $usdRate;
            } elseif ($currency === 'EUR') {
                $eurRate = (float) ($rates['EUR'] ?? 0);
                if ($eurRate <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'EUR kuru tanimli degil veya 0.']);
                    return;
                }
                $totalTl += $amount * $eurRate;
            } else {
                $totalTl += $amount;
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => round($totalTl, 2),
            'by_currency' => array_map(function ($value) {
                return round((float) $value, 2);
            }, $byCurrency)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam gelir alÄ±nÄ±rken hata oluÅŸtu.']);
    }
}

function getIncomeRates()
{
    global $connection;
    $rates = ['TL' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $rate_query = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    if ($rate_query) {
        while ($row = $rate_query->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') {
                $rates['USD'] = max(0.0, floatval($row['ayar_deger']));
            }
            if ($row['ayar_anahtar'] === 'euro_kuru') {
                $rates['EUR'] = max(0.0, floatval($row['ayar_deger']));
            }
        }
    }
    return $rates;
}

function incomeNormalizeCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TRY') {
        $currency = 'TL';
    }
    return in_array($currency, ['TL', 'USD', 'EUR']) ? $currency : 'TL';
}

function incomeConvertCurrency($amount, $from, $to, $rates)
{
    $amount = floatval($amount);
    $from = incomeNormalizeCurrency($from);
    $to = incomeNormalizeCurrency($to);
    if ($from === $to) {
        return $amount;
    }
    $fromRate = floatval($rates[$from] ?? 0);
    $toRate = floatval($rates[$to] ?? 0);
    if ($fromRate <= 0 || $toRate <= 0) {
        throw new Exception("Kur bilgisi gecersiz.");
    }
    $tl = $amount * $fromRate;
    return $tl / $toRate;
}

function incomeGetOrderCurrency($siparis_id)
{
    global $connection;
    $siparis_id = (int) $siparis_id;
    if ($siparis_id <= 0) {
        return 'TL';
    }

    $result = $connection->query("SELECT para_birimi FROM siparisler WHERE siparis_id = $siparis_id");
    $row = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    return incomeNormalizeCurrency($row['para_birimi'] ?? 'TL');
}

function incomeGetOrderTotalInCurrency($siparis_id, $target_currency, $rates, $fallback_currency = 'TL')
{
    global $connection;
    $siparis_id = (int) $siparis_id;
    if ($siparis_id <= 0) {
        return 0.0;
    }

    $target_currency = incomeNormalizeCurrency($target_currency);
    $fallback_currency = incomeNormalizeCurrency($fallback_currency);
    $query = "SELECT para_birimi, IFNULL(SUM(COALESCE(toplam_tutar, birim_fiyat * adet)), 0) AS total
              FROM siparis_kalemleri
              WHERE siparis_id = $siparis_id
              GROUP BY para_birimi";
    $result = $connection->query($query);

    $order_total = 0.0;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $line_currency = incomeNormalizeCurrency($row['para_birimi'] ?: $fallback_currency);
            $line_total = (float) ($row['total'] ?? 0);
            if ($line_total == 0.0) {
                continue;
            }
            $order_total += incomeConvertCurrency($line_total, $line_currency, $target_currency, $rates);
        }
    }

    return $order_total;
}

function incomeGetPaidTotalInCurrency($siparis_id, $target_currency, $rates, $fallback_currency = 'TL')
{
    global $connection;
    $siparis_id = (int) $siparis_id;
    if ($siparis_id <= 0) {
        return 0.0;
    }

    $target_currency = incomeNormalizeCurrency($target_currency);
    $fallback_currency = incomeNormalizeCurrency($fallback_currency);
    $query = "SELECT para_birimi, IFNULL(SUM(tutar), 0) AS total
              FROM gelir_yonetimi
              WHERE siparis_id = $siparis_id
              GROUP BY para_birimi";
    $result = $connection->query($query);

    $total_paid = 0.0;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $line_currency = incomeNormalizeCurrency($row['para_birimi'] ?: $fallback_currency);
            $line_total = (float) ($row['total'] ?? 0);
            if ($line_total == 0.0) {
                continue;
            }
            $total_paid += incomeConvertCurrency($line_total, $line_currency, $target_currency, $rates);
        }
    }

    return $total_paid;
}

function incomeGetOrderFinancialSummary($siparis_id, $rates, $target_currency = null)
{
    $order_currency = incomeGetOrderCurrency($siparis_id);
    $display_currency = $target_currency !== null ? incomeNormalizeCurrency($target_currency) : $order_currency;

    $order_total = incomeGetOrderTotalInCurrency($siparis_id, $display_currency, $rates, $order_currency);
    $total_paid = incomeGetPaidTotalInCurrency($siparis_id, $display_currency, $rates, $order_currency);
    $remaining = $order_total - $total_paid;
    if ($remaining < 0) {
        $remaining = 0.0;
    }

    return [
        'currency' => $display_currency,
        'order_currency' => $order_currency,
        'order_total' => $order_total,
        'total_paid' => $total_paid,
        'remaining' => $remaining
    ];
}

function incomeEnsureCashRow($currency)
{
    global $connection;
    $currency = incomeNormalizeCurrency($currency);
    $currencyEsc = $connection->real_escape_string($currency);
    $check = $connection->query("SELECT para_birimi FROM sirket_kasasi WHERE para_birimi = '$currencyEsc'");
    if (!$check || $check->num_rows === 0) {
        if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$currencyEsc', 0)")) {
            throw new Exception("Kasa satiri olusturulamadi: " . $connection->error);
        }
    }
}

function incomeAdjustCash($currency, $delta)
{
    global $connection;
    $currency = incomeNormalizeCurrency($currency);
    incomeEnsureCashRow($currency);
    $currencyEsc = $connection->real_escape_string($currency);
    $delta = floatval($delta);
    if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + ($delta) WHERE para_birimi = '$currencyEsc'")) {
        throw new Exception("Kasa bakiyesi guncellenemedi: " . $connection->error);
    }
}

function incomeGetLinkedMovement($gelir_id)
{
    global $connection;
    $gelir_id = (int) $gelir_id;
    $q = "SELECT * FROM kasa_hareketleri WHERE kaynak_tablo = 'gelir_yonetimi' AND kaynak_id = $gelir_id ORDER BY hareket_id DESC LIMIT 1";
    $r = $connection->query($q);
    return ($r && $r->num_rows > 0) ? $r->fetch_assoc() : null;
}

function incomeDeleteLinkedMovements($gelir_id)
{
    global $connection;
    $gelir_id = (int) $gelir_id;
    if (!$connection->query("DELETE FROM kasa_hareketleri WHERE kaynak_tablo = 'gelir_yonetimi' AND kaynak_id = $gelir_id")) {
        throw new Exception("Eski kasa hareketleri temizlenemedi: " . $connection->error);
    }
}

function incomeCheckUsedInExpense($cek_id)
{
    global $connection;
    $cek_id = (int) $cek_id;
    if ($cek_id <= 0)
        return false;
    $res = $connection->query("SELECT COUNT(*) AS c FROM gider_yonetimi WHERE cek_secimi = $cek_id");
    $cnt = ($res && $res->num_rows > 0) ? (int) ($res->fetch_assoc()['c'] ?? 0) : 0;
    return $cnt > 0;
}

function addIncome()
{
    global $connection;

    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $para_birimi = incomeNormalizeCurrency($_POST['para_birimi'] ?? 'TL');
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');
    $kasa_raw = $_POST['kasa_secimi'] ?? 'TL';
    $kasa_secimi = ($kasa_raw === 'cek_kasasi') ? 'cek_kasasi' : incomeNormalizeCurrency($kasa_raw);

    $personel_id = $_SESSION['user_id'];
    $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    $siparis_id = !empty($_POST['siparis_id']) ? (int) $_POST['siparis_id'] : 'NULL';
    $musteri_id = !empty($_POST['musteri_id']) ? (int) $_POST['musteri_id'] : 'NULL';

    $cek_no = $connection->real_escape_string($_POST['cek_no'] ?? '');
    $cek_sahibi = $connection->real_escape_string($_POST['cek_sahibi'] ?? '');
    $cek_vade = $connection->real_escape_string($_POST['cek_vade'] ?? '');

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi) || !in_array($kasa_secimi, ['TL', 'USD', 'EUR', 'cek_kasasi'])) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, aciklama ve odeme tipi alanlari zorunludur.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $rates = getIncomeRates();
        $cek_secimi = 'NULL';
        $kasa_adi = $kasa_secimi;
        $hareket_tutar = $tutar;
        $hareket_para_birimi = $para_birimi;
        $tl_karsiligi = incomeConvertCurrency($tutar, $para_birimi, 'TL', $rates);

        if ($kasa_secimi === 'cek_kasasi') {
            if (empty($cek_no) || empty($cek_sahibi) || empty($cek_vade)) {
                throw new Exception('Cek numarasi, cek sahibi ve vade zorunludur.');
            }
            $cek_insert = "INSERT INTO cek_kasasi (cek_no, cek_tutari, cek_para_birimi, cek_sahibi, vade_tarihi, cek_tipi, cek_durumu, aciklama, kaydeden_personel)
                VALUES ('$cek_no', $tutar, '$para_birimi', '$cek_sahibi', '$cek_vade', 'alacak', 'alindi', '$aciklama', '$personel_adi')";
            if (!$connection->query($cek_insert))
                throw new Exception('Cek kaydedilemedi.');
            $cek_secimi = $connection->insert_id;
            $odeme_tipi = 'Cek';
        } else {
            $hareket_tutar = incomeConvertCurrency($tutar, $para_birimi, $kasa_secimi, $rates);
            $hareket_para_birimi = $kasa_secimi;
            incomeAdjustCash($kasa_secimi, $hareket_tutar);
        }

        $cek_col = ($cek_secimi !== 'NULL') ? ", cek_secimi" : "";
        $cek_val = ($cek_secimi !== 'NULL') ? ", $cek_secimi" : "";
        $query = "INSERT INTO gelir_yonetimi (tarih, tutar, para_birimi, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, siparis_id, odeme_tipi, musteri_id, musteri_adi, kasa_secimi $cek_col)
              VALUES ('$tarih', $tutar, '$para_birimi', '$kategori', '$aciklama', $personel_id, '$personel_adi', $siparis_id, '$odeme_tipi', $musteri_id, '$musteri_adi', '$kasa_secimi' $cek_val)";

        if (!$connection->query($query))
            throw new Exception('Gelir eklenemedi: ' . $connection->error);
        $gelir_id = $connection->insert_id;

        $cek_id_col = ($cek_secimi !== 'NULL') ? $cek_secimi : "NULL";
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_musteri, odeme_tipi)
            VALUES ('$tarih', 'gelir_girisi', '$kasa_adi', $cek_id_col, $hareket_tutar, '$hareket_para_birimi', $tl_karsiligi, 'gelir_yonetimi', $gelir_id, '$aciklama', '$personel_adi', '$musteri_adi', '$odeme_tipi')";
        if (!$connection->query($hareket_sql))
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);

        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$kategori kategorisinde $tutar $para_birimi tutarinda gelir eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir basariyla eklendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateIncome()
{
    global $connection;

    $gelir_id = (int) ($_POST['gelir_id'] ?? 0);
    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $para_birimi = incomeNormalizeCurrency($_POST['para_birimi'] ?? 'TL');
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');
    $kasa_raw = $_POST['kasa_secimi'] ?? 'TL';
    $kasa_secimi = ($kasa_raw === 'cek_kasasi') ? 'cek_kasasi' : incomeNormalizeCurrency($kasa_raw);
    $cek_no = $connection->real_escape_string($_POST['cek_no'] ?? '');
    $cek_sahibi = $connection->real_escape_string($_POST['cek_sahibi'] ?? '');
    $cek_vade = $connection->real_escape_string($_POST['cek_vade'] ?? '');

    if (empty($gelir_id) || empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi) || !in_array($kasa_secimi, ['TL', 'USD', 'EUR', 'cek_kasasi'])) {
        echo json_encode(['status' => 'error', 'message' => 'Tum alanlar zorunludur.']);
        return;
    }

    $old_res = $connection->query("SELECT * FROM gelir_yonetimi WHERE gelir_id = $gelir_id");
    if (!$old_res || $old_res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir kaydi bulunamadi.']);
        return;
    }

    $old_rec = $old_res->fetch_assoc();
    $old_cat = $old_rec['kategori'] ?? '';
    $old_amt = floatval($old_rec['tutar'] ?? 0);
    $old_currency = $old_rec['para_birimi'] ?? 'TL';
    $old_kasa = $old_rec['kasa_secimi'] ?? 'TL';
    $old_cek_id = !empty($old_rec['cek_secimi']) ? (int) $old_rec['cek_secimi'] : 0;
    $siparis_id = (int) ($old_rec['siparis_id'] ?? 0);

    $connection->begin_transaction();
    try {
        $rates = getIncomeRates();
        $old_movement = incomeGetLinkedMovement($gelir_id);

        if ($old_kasa !== 'cek_kasasi' && in_array($old_kasa, ['TL', 'USD', 'EUR'])) {
            if ($old_movement && ($old_movement['islem_tipi'] ?? '') === 'gelir_girisi' && in_array($old_movement['kasa_adi'] ?? '', ['TL', 'USD', 'EUR'])) {
                incomeAdjustCash($old_movement['kasa_adi'], -floatval($old_movement['tutar']));
            } else {
                $old_delta = incomeConvertCurrency(floatval($old_rec['tutar']), incomeNormalizeCurrency($old_rec['para_birimi'] ?? 'TL'), $old_kasa, $rates);
                incomeAdjustCash($old_kasa, -$old_delta);
            }
        }

        if ($old_kasa === 'cek_kasasi' && $old_cek_id > 0 && incomeCheckUsedInExpense($old_cek_id)) {
            throw new Exception('Bu gelir kaydina bagli cek giderde kullanildigi icin guncellenemez.');
        }

        $new_cek_id = 0;
        $kasa_adi = $kasa_secimi;
        $hareket_tutar = $tutar;
        $hareket_para_birimi = $para_birimi;
        $tl_karsiligi = incomeConvertCurrency($tutar, $para_birimi, 'TL', $rates);

        if ($kasa_secimi === 'cek_kasasi') {
            if ($old_kasa === 'cek_kasasi' && $old_cek_id > 0) {
                $new_cek_id = $old_cek_id;
                $cek_no_final = $cek_no !== '' ? $cek_no : ($connection->query("SELECT cek_no FROM cek_kasasi WHERE cek_id = $new_cek_id")->fetch_assoc()['cek_no'] ?? '');
                $cek_sahibi_final = $cek_sahibi !== '' ? $cek_sahibi : ($connection->query("SELECT cek_sahibi FROM cek_kasasi WHERE cek_id = $new_cek_id")->fetch_assoc()['cek_sahibi'] ?? '');
                $cek_vade_final = $cek_vade !== '' ? $cek_vade : ($connection->query("SELECT vade_tarihi FROM cek_kasasi WHERE cek_id = $new_cek_id")->fetch_assoc()['vade_tarihi'] ?? '');
                if ($cek_no_final === '' || $cek_sahibi_final === '' || $cek_vade_final === '') {
                    throw new Exception('Cek bilgileri eksik.');
                }
                $upd_cek = "UPDATE cek_kasasi
                            SET cek_no = '$cek_no_final', cek_tutari = $tutar, cek_para_birimi = '$para_birimi', cek_sahibi = '$cek_sahibi_final', vade_tarihi = '$cek_vade_final', aciklama = '$aciklama', cek_durumu = 'alindi', cek_kullanim_tarihi = NULL, cek_son_durum_tarihi = NOW()
                            WHERE cek_id = $new_cek_id";
                if (!$connection->query($upd_cek))
                    throw new Exception('Cek guncellenemedi: ' . $connection->error);
            } else {
                if (empty($cek_no) || empty($cek_sahibi) || empty($cek_vade)) {
                    throw new Exception('Cek numarasi, cek sahibi ve vade zorunludur.');
                }
                $ins_cek = "INSERT INTO cek_kasasi (cek_no, cek_tutari, cek_para_birimi, cek_sahibi, vade_tarihi, cek_tipi, cek_durumu, aciklama, kaydeden_personel)
                            VALUES ('$cek_no', $tutar, '$para_birimi', '$cek_sahibi', '$cek_vade', 'alacak', 'alindi', '$aciklama', '{$_SESSION['kullanici_adi']}')";
                if (!$connection->query($ins_cek))
                    throw new Exception('Cek kaydedilemedi: ' . $connection->error);
                $new_cek_id = (int) $connection->insert_id;
            }
            $odeme_tipi = 'Cek';
        } else {
            if ($old_kasa === 'cek_kasasi' && $old_cek_id > 0) {
                $connection->query("UPDATE cek_kasasi SET cek_durumu = 'iptal', cek_son_durum_tarihi = NOW() WHERE cek_id = $old_cek_id");
            }
            $hareket_tutar = incomeConvertCurrency($tutar, $para_birimi, $kasa_secimi, $rates);
            $hareket_para_birimi = $kasa_secimi;
            incomeAdjustCash($kasa_secimi, $hareket_tutar);
        }

        $cek_sql = $new_cek_id > 0 ? $new_cek_id : 'NULL';
        $upd = "UPDATE gelir_yonetimi
                SET tarih = '$tarih', tutar = $tutar, para_birimi = '$para_birimi', kategori = '$kategori', aciklama = '$aciklama', odeme_tipi = '$odeme_tipi', musteri_adi = '$musteri_adi', kasa_secimi = '$kasa_secimi', cek_secimi = $cek_sql
                WHERE gelir_id = $gelir_id";
        if (!$connection->query($upd))
            throw new Exception('Gelir guncellenemedi: ' . $connection->error);

        incomeDeleteLinkedMovements($gelir_id);
        $hareket_cek = $new_cek_id > 0 ? $new_cek_id : "NULL";
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_musteri, odeme_tipi)
                        VALUES ('$tarih', 'gelir_girisi', '$kasa_adi', $hareket_cek, $hareket_tutar, '$hareket_para_birimi', $tl_karsiligi, 'gelir_yonetimi', $gelir_id, '$aciklama', '{$_SESSION['kullanici_adi']}', '$musteri_adi', '$odeme_tipi')";
        if (!$connection->query($hareket_sql))
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);

        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_cat kategorisindeki $old_amt $old_currency tutarli gelir guncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir basariyla guncellendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteIncome()
{
    global $connection;

    $gelir_id = (int) ($_POST['gelir_id'] ?? 0);
    if (empty($gelir_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir ID gerekli.']);
        return;
    }

    $old_res = $connection->query("SELECT * FROM gelir_yonetimi WHERE gelir_id = $gelir_id");
    if (!$old_res || $old_res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir kaydi bulunamadi.']);
        return;
    }

    $old_rec = $old_res->fetch_assoc();
    $old_cat = $old_rec['kategori'] ?? '';
    $old_amt = floatval($old_rec['tutar'] ?? 0);
    $old_kasa = $old_rec['kasa_secimi'] ?? 'TL';
    $old_cek_id = !empty($old_rec['cek_secimi']) ? (int) $old_rec['cek_secimi'] : 0;
    $siparis_id = (int) ($old_rec['siparis_id'] ?? 0);
    $taksit_id = (int) ($old_rec['taksit_id'] ?? 0);

    $connection->begin_transaction();
    try {
        $rates = getIncomeRates();
        $old_movement = incomeGetLinkedMovement($gelir_id);

        if ($old_kasa !== 'cek_kasasi' && in_array($old_kasa, ['TL', 'USD', 'EUR'])) {
            if ($old_movement && ($old_movement['islem_tipi'] ?? '') === 'gelir_girisi' && in_array($old_movement['kasa_adi'] ?? '', ['TL', 'USD', 'EUR'])) {
                incomeAdjustCash($old_movement['kasa_adi'], -floatval($old_movement['tutar']));
            } else {
                $old_delta = incomeConvertCurrency(floatval($old_rec['tutar']), incomeNormalizeCurrency($old_rec['para_birimi'] ?? 'TL'), $old_kasa, $rates);
                incomeAdjustCash($old_kasa, -$old_delta);
            }
        }

        if ($old_kasa === 'cek_kasasi' && $old_cek_id > 0) {
            if (incomeCheckUsedInExpense($old_cek_id)) {
                throw new Exception('Bu gelir kaydina bagli cek giderde kullanildigi icin silinemez.');
            }
            $connection->query("UPDATE cek_kasasi SET cek_durumu = 'iptal', cek_son_durum_tarihi = NOW() WHERE cek_id = $old_cek_id");
        }

        incomeDeleteLinkedMovements($gelir_id);
        if (!$connection->query("DELETE FROM gelir_yonetimi WHERE gelir_id = $gelir_id")) {
            throw new Exception('Gelir silinirken hata olustu: ' . $connection->error);
        }

        $extra_msg = "";
        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
            $check_order = $connection->query("SELECT siparis_id, odeme_durumu FROM siparisler WHERE siparis_id = $siparis_id");
            if ($check_order->num_rows > 0) {
                $ord = $check_order->fetch_assoc();
                $extra_msg = " Bagli Siparis Durumu: " . $ord['odeme_durumu'] . " olarak guncellendi.";
            }
        }

        if ($taksit_id > 0) {
            $revert_inst = "UPDATE taksit_detaylari SET durum = 'bekliyor', odenen_tutar = 0, kalan_tutar = tutar, odeme_tarihi = NULL WHERE taksit_id = $taksit_id";
            $connection->query($revert_inst);
            $plan_q = $connection->query("SELECT plan_id FROM taksit_detaylari WHERE taksit_id = $taksit_id");
            $plan_id = $plan_q->fetch_assoc()['plan_id'] ?? 0;
            if ($plan_id > 0) {
                $plan_check = $connection->query("SELECT durum FROM taksit_planlari WHERE plan_id = $plan_id");
                $plan_status = $plan_check->fetch_assoc()['durum'] ?? '';
                if ($plan_status === 'tamamlandi') {
                    $connection->query("UPDATE taksit_planlari SET durum = 'aktif' WHERE plan_id = $plan_id");
                    $linked_orders = $connection->query("SELECT siparis_id FROM taksit_siparis_baglantisi WHERE plan_id = $plan_id");
                    while ($lo = $linked_orders->fetch_assoc()) {
                        $lsid = $lo['siparis_id'];
                        $connection->query("UPDATE siparisler SET odeme_durumu = 'kismi_odendi' WHERE siparis_id = $lsid");
                    }
                }
                $extra_msg .= " Taksit odenmemis olarak isaretlendi.";
            }
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_cat kategorisindeki $old_amt TL tutarli gelir silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir basariyla silindi.' . $extra_msg]);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getPendingOrders()
{
    global $connection;
    try {
        $rates = getIncomeRates();
        $query = "SELECT s.siparis_id, s.musteri_id, s.musteri_adi, s.tarih, s.odeme_durumu
                  FROM siparisler s
                  WHERE s.durum IN ('onaylandi', 'tamamlandi')
                  AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
                  AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
                  ORDER BY s.siparis_id DESC";

        $result = $connection->query($query);
        $orders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $summary = incomeGetOrderFinancialSummary((int) $row['siparis_id'], $rates);
                if ((float) $summary['remaining'] > 0.01) {
                    $row['para_birimi'] = $summary['currency'];
                    $row['toplam_tutar'] = round((float) $summary['order_total'], 2);
                    $row['odenen_tutar'] = round((float) $summary['total_paid'], 2);
                    $row['kalan_tutar'] = round((float) $summary['remaining'], 2);
                    $orders[] = $row;
                }
            }
        }

        echo json_encode(['status' => 'success', 'data' => $orders]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateOrderPaymentStatus($siparis_id)
{
    global $connection;

    if (empty($siparis_id))
        return;

    $siparis_id = (int) $siparis_id;
    $rates = getIncomeRates();
    $summary = incomeGetOrderFinancialSummary($siparis_id, $rates);
    $total_paid = (float) $summary['total_paid'];
    $order_total = (float) $summary['order_total'];
    $order_currency = incomeNormalizeCurrency($summary['currency'] ?? 'TL');

    $new_status = 'bekliyor';
    if ($total_paid >= $order_total - 0.01) {
        $new_status = 'odendi';
    } elseif ($total_paid > 0) {
        $new_status = 'kismi_odendi';
    }

    $status_esc = $connection->real_escape_string($new_status);
    $currency_esc = $connection->real_escape_string($order_currency);
    $update = "UPDATE siparisler SET odeme_durumu = '$status_esc', odenen_tutar = $total_paid, para_birimi = '$currency_esc' WHERE siparis_id = $siparis_id";
    $connection->query($update);
}

function getPendingStats()
{
    global $connection;
    try {
        $rates = getIncomeRates();

        // 1. Pending Orders (Standard) - Excludes installments
        $queryOrders = "SELECT s.siparis_id
                        FROM siparisler s
                        WHERE s.durum IN ('onaylandi', 'tamamlandi')
                        AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
                        AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')";
        $resultOrders = $connection->query($queryOrders);
        $totals = [];
        $orderCount = 0;

        if ($resultOrders) {
            while ($row = $resultOrders->fetch_assoc()) {
                $summary = incomeGetOrderFinancialSummary((int) $row['siparis_id'], $rates);
                $remaining = (float) $summary['remaining'];
                if ($remaining > 0.01) {
                    $orderCount++;
                    $currency = incomeNormalizeCurrency($summary['currency'] ?? 'TL');
                    if (!isset($totals[$currency])) {
                        $totals[$currency] = 0.0;
                    }
                    $totals[$currency] += $remaining;
                }
            }
        }

        // 2. Unpaid Installments (Active Plans) - Add to totals
        $queryInstallments = "SELECT 
                                SUM(td.kalan_tutar) as total_remaining,
                                tp.para_birimi
                              FROM taksit_detaylari td
                              JOIN taksit_planlari tp ON tp.plan_id = td.plan_id
                              WHERE tp.durum = 'aktif' AND td.durum != 'odendi'
                              GROUP BY tp.para_birimi";
        $resultInst = $connection->query($queryInstallments);
        if ($resultInst) {
            while ($row = $resultInst->fetch_assoc()) {
                $currency = incomeNormalizeCurrency($row['para_birimi'] ?? 'TL');
                if (!isset($totals[$currency])) {
                    $totals[$currency] = 0.0;
                }
                $totals[$currency] += (float) $row['total_remaining'];
            }
        }

        // 3. New Stats: Active Plans Count
        $activePlansCount = $connection->query("SELECT COUNT(*) as c FROM taksit_planlari WHERE durum = 'aktif'")->fetch_assoc()['c'];

        // 4. New Stats: Overdue Installments
        $overdueQuery = "SELECT COUNT(*) as count, SUM(td.kalan_tutar) as total, tp.para_birimi
                         FROM taksit_detaylari td
                         JOIN taksit_planlari tp ON tp.plan_id = td.plan_id
                         WHERE tp.durum = 'aktif' AND td.durum != 'odendi' AND td.vade_tarihi < CURDATE()
                         GROUP BY tp.para_birimi";
        $overdueStats = ['count' => 0, 'totals' => []];
        $resOverdue = $connection->query($overdueQuery);
        if ($resOverdue) {
            while ($row = $resOverdue->fetch_assoc()) {
                $overdueStats['count'] += (int) $row['count'];
                $currency = incomeNormalizeCurrency($row['para_birimi'] ?? 'TL');
                $overdueStats['totals'][$currency] = (float) $row['total'];
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'pending_orders_count' => $orderCount,
                'total_receivables' => $totals,
                'active_plans_count' => $activePlansCount,
                'overdue_installments' => $overdueStats
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getCustomersWithDebt() {
    global $connection;
    // Get customers who have approved orders not fully paid
    $query = "SELECT DISTINCT s.musteri_id, s.musteri_adi 
              FROM siparisler s
              WHERE s.durum IN ('onaylandi', 'tamamlandi') 
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
              ORDER BY s.musteri_adi ASC";
    
    $result = $connection->query($query);
    $customers = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $customers]);
}

function getCustomerOrdersForPlan() {
    global $connection;
    $musteri_id = (int)($_GET['musteri_id'] ?? 0);
    
    if(!$musteri_id) {
        echo json_encode(['status' => 'error', 'message' => 'MÃ¼ÅŸteri seÃ§ilmeli']);
        return;
    }

    try {
        $rates = getIncomeRates();
        $query = "SELECT s.siparis_id, s.tarih
                  FROM siparisler s
                  WHERE s.musteri_id = $musteri_id
                  AND s.durum IN ('onaylandi', 'tamamlandi')
                  AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
                  AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
                  ORDER BY s.tarih DESC";

        $result = $connection->query($query);
        $orders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $summary = incomeGetOrderFinancialSummary((int) $row['siparis_id'], $rates);
                if ((float) $summary['remaining'] > 0.01) {
                    $row['para_birimi'] = $summary['currency'];
                    $row['toplam_tutar'] = round((float) $summary['order_total'], 2);
                    $row['odenen_tutar'] = round((float) $summary['total_paid'], 2);
                    $row['kalan_tutar'] = round((float) $summary['remaining'], 2);
                    $orders[] = $row;
                }
            }
        }
        echo json_encode(['status' => 'success', 'data' => $orders]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function createInstallmentPlan() {
    global $connection;
    
    $musteri_id = (int)($_POST['musteri_id'] ?? 0);
    $siparis_ids = $_POST['siparis_ids'] ?? []; 
    $taksit_sayisi = (int)($_POST['taksit_sayisi'] ?? 1);
    $vade_farki_orani = floatval($_POST['vade_farki_orani'] ?? 0);
    $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? date('Y-m-d');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_id = $_SESSION['user_id'];
    
    if(!$musteri_id || empty($siparis_ids) || $taksit_sayisi < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Eksik bilgi.']);
        return;
    }

    $rates = getIncomeRates();
    $total_principal = 0;
    $order_contributions = [];
    $first_order_curr = '';
    
    foreach($siparis_ids as $sid) {
        $sid = (int)$sid;
        $summary = incomeGetOrderFinancialSummary($sid, $rates);
        $order_currency = incomeNormalizeCurrency($summary['currency'] ?? 'TL');
        
        if(!$first_order_curr) $first_order_curr = $order_currency;
        if($order_currency !== $first_order_curr) {
             echo json_encode(['status' => 'error', 'message' => 'SeÃ§ilen sipariÅŸlerin para birimleri aynÄ± olmalÄ±dÄ±r.']);
             return;
        }
        
        $rem = floatval($summary['remaining']);
        if($rem > 0) {
            $total_principal += $rem;
            $order_contributions[$sid] = $rem;
        }
    }
    if ($total_principal <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'SeÃ§ilen sipariÅŸlerde taksitlendirilecek kalan tutar bulunamadÄ±.']);
        return;
    }
    $currency = $first_order_curr;

    $interest_amount = $total_principal * ($vade_farki_orani / 100);
    $total_payable = $total_principal + $interest_amount;
    $monthly_amount = $total_payable / $taksit_sayisi;

    $musteri_adi_q = $connection->query("SELECT musteri_adi FROM musteriler WHERE musteri_id = $musteri_id");
    $musteri_adi = $musteri_adi_q->fetch_assoc()['musteri_adi'] ?? '';

    $ins_plan = "INSERT INTO taksit_planlari (musteri_id, musteri_adi, toplam_anapara, vade_farki_orani, vade_farki_tutari, toplam_odenecek, para_birimi, taksit_sayisi, baslangic_tarihi, aciklama, kaydeden_personel_id)
                 VALUES ($musteri_id, '$musteri_adi', $total_principal, $vade_farki_orani, $interest_amount, $total_payable, '$currency', $taksit_sayisi, '$baslangic_tarihi', '$aciklama', $kaydeden_id)";
    
    if($connection->query($ins_plan)) {
        $plan_id = $connection->insert_id;
        
        foreach($order_contributions as $sid => $amt) {
            $connection->query("INSERT INTO taksit_siparis_baglantisi (plan_id, siparis_id, tutar_katkisi) VALUES ($plan_id, $sid, $amt)");
        }

        $current_date = new DateTime($baslangic_tarihi);
        
        for($i = 1; $i <= $taksit_sayisi; $i++) {
            $vade = $current_date->format('Y-m-d');
            $this_amount = round($monthly_amount, 2);
            if ($i == $taksit_sayisi) {
                $so_far = round($monthly_amount, 2) * ($taksit_sayisi - 1);
                $this_amount = $total_payable - $so_far;
            }

            $connection->query("INSERT INTO taksit_detaylari (plan_id, sira_no, vade_tarihi, tutar, kalan_tutar) 
                              VALUES ($plan_id, $i, '$vade', $this_amount, $this_amount)");
            
            $current_date->modify('+1 month');
        }

        echo json_encode(['status' => 'success', 'message' => 'Taksit planÄ± baÅŸarÄ±yla oluÅŸturuldu.', 'plan_id' => $plan_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Plan oluÅŸturulamadÄ±: ' . $connection->error]);
    }
}

function getInstallmentPlans() {
    global $connection;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    $q = "SELECT * FROM taksit_planlari ORDER BY olusturma_tarihi DESC LIMIT $per_page OFFSET $offset";
    $res = $connection->query($q);
    $plans = [];
    while($row = $res->fetch_assoc()) {
        $pid = $row['plan_id'];
        $stats = $connection->query("SELECT COUNT(*) as total, SUM(CASE WHEN durum='odendi' THEN 1 ELSE 0 END) as paid_count FROM taksit_detaylari WHERE plan_id=$pid")->fetch_assoc();
        $row['odenen_taksit'] = $stats['paid_count'];
        $row['toplam_taksit_sayisi'] = $stats['total'];
        $plans[] = $row;
    }
    
    $total = $connection->query("SELECT COUNT(*) as t FROM taksit_planlari")->fetch_assoc()['t'];
    
    echo json_encode(['status' => 'success', 'data' => $plans, 'total' => $total, 'page' => $page]);
}

function getPlanDetails() {
    global $connection;
    $plan_id = (int)$_GET['plan_id'];
    
    $plan = $connection->query("SELECT * FROM taksit_planlari WHERE plan_id = $plan_id")->fetch_assoc();
    
    $inst = [];
    $res = $connection->query("SELECT * FROM taksit_detaylari WHERE plan_id = $plan_id ORDER BY sira_no ASC");
    while($r = $res->fetch_assoc()) $inst[] = $r;
    
    $orders = [];
    $res2 = $connection->query("SELECT tsb.tutar_katkisi, s.siparis_id, s.tarih 
                               FROM taksit_siparis_baglantisi tsb 
                               JOIN siparisler s ON s.siparis_id = tsb.siparis_id 
                               WHERE tsb.plan_id = $plan_id");
    while($r = $res2->fetch_assoc()) $orders[] = $r;

    echo json_encode(['status' => 'success', 'plan' => $plan, 'installments' => $inst, 'orders' => $orders]);
}

function payInstallment() {
    global $connection;
    $taksit_id = (int) ($_POST['taksit_id'] ?? 0);
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Nakit');

    if ($taksit_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz taksit ID.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $td = $connection->query("SELECT * FROM taksit_detaylari WHERE taksit_id = $taksit_id FOR UPDATE")->fetch_assoc();
        if (!$td || floatval($td['kalan_tutar']) <= 0) {
            throw new Exception('Taksit bulunamadi veya zaten odenmis.');
        }

        $plan = $connection->query("SELECT * FROM taksit_planlari WHERE plan_id = {$td['plan_id']} FOR UPDATE")->fetch_assoc();
        if (!$plan) {
            throw new Exception('Taksit plani bulunamadi.');
        }

        $amount_to_pay = floatval($td['kalan_tutar']);
        $plan_currency = incomeNormalizeCurrency($plan['para_birimi'] ?? 'TL');
        $kasa_secimi = incomeNormalizeCurrency($_POST['kasa_secimi'] ?? $plan_currency);
        if (!in_array($kasa_secimi, ['TL', 'USD', 'EUR'])) {
            $kasa_secimi = 'TL';
        }
        $tarih = date('Y-m-d');

        $upd = "UPDATE taksit_detaylari SET odenen_tutar = odenen_tutar + $amount_to_pay, kalan_tutar = 0, durum = 'odendi', odeme_tarihi = NOW() WHERE taksit_id = $taksit_id";
        if (!$connection->query($upd)) {
            throw new Exception('Taksit guncellenemedi: ' . $connection->error);
        }

        $desc = "Taksit Odemesi - Taksit {$td['sira_no']}/{$plan['taksit_sayisi']} - {$plan['musteri_adi']}";
        $personel_id = $_SESSION['user_id'];
        $personel_adi = $_SESSION['kullanici_adi'];
        
        $ins_inc = "INSERT INTO gelir_yonetimi (tarih, tutar, para_birimi, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_tipi, musteri_id, musteri_adi, taksit_id, kasa_secimi)
                    VALUES ('$tarih', $amount_to_pay, '$plan_currency', 'Siparis Odemesi', '$desc', $personel_id, '$personel_adi', '$odeme_tipi', {$plan['musteri_id']}, '{$plan['musteri_adi']}', $taksit_id, '$kasa_secimi')";
        if (!$connection->query($ins_inc)) {
            throw new Exception('Gelir kaydi olusturulamadi: ' . $connection->error);
        }
        $gelir_id = (int) $connection->insert_id;

        $rates = getIncomeRates();
        $hareket_tutar = incomeConvertCurrency($amount_to_pay, $plan_currency, $kasa_secimi, $rates);
        $tl_karsiligi = incomeConvertCurrency($amount_to_pay, $plan_currency, 'TL', $rates);
        incomeAdjustCash($kasa_secimi, $hareket_tutar);

        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_musteri, odeme_tipi)
                        VALUES ('$tarih', 'gelir_girisi', '$kasa_secimi', $hareket_tutar, '$kasa_secimi', $tl_karsiligi, 'gelir_yonetimi', $gelir_id, '$desc', '$personel_adi', '{$plan['musteri_adi']}', '$odeme_tipi')";
        if (!$connection->query($hareket_sql)) {
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);
        }
        
        $check = $connection->query("SELECT COUNT(*) as rem FROM taksit_detaylari WHERE plan_id = {$plan['plan_id']} AND durum != 'odendi'")->fetch_assoc();
        if ($check['rem'] == 0) {
            $connection->query("UPDATE taksit_planlari SET durum = 'tamamlandi' WHERE plan_id = {$plan['plan_id']}");
            
             $linked = $connection->query("SELECT siparis_id FROM taksit_siparis_baglantisi WHERE plan_id = {$plan['plan_id']}");
             while($l = $linked->fetch_assoc()) {
                 $sid = (int) $l['siparis_id'];
                 updateOrderPaymentStatus($sid);
             }
        }
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Taksit odendi, gelir ve kasa kayitlari olusturuldu.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function cancelInstallmentPlan() {
    global $connection;
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    
    if(!$plan_id) {
        echo json_encode(['status' => 'error', 'message' => 'Plan ID gerekli.']);
        return;
    }

    $upd = "UPDATE taksit_planlari SET durum = 'iptal' WHERE plan_id = $plan_id";
    if($connection->query($upd)) {
        log_islem($connection, $_SESSION['kullanici_adi'], "Taksit PlanÄ± #$plan_id iptal edildi.", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Plan iptal edildi. SipariÅŸler tekrar tahsilat listesine dÃ¼ÅŸecektir.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $connection->error]);
    }
}
