<?php
// Hata raporlamayÄ± aÃ§ ama ekrana basma (JSON'Ä± bozmasÄ±n)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include '../config.php';

header('Content-Type: application/json');

// GiriÅŸ ve yetki kontrolÃ¼
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetki yok veya oturum kapalÄ±.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action eksik.']);
    exit;
}

try {
    switch ($action) {
        case 'get_incomes': getIncomes(); break;
        case 'get_expenses': getExpenses(); break;
        case 'get_cash_transactions': getCashTransactions(); break;
        case 'get_statistics': getStatistics(); break;
        case 'add_cash_transaction': addCashTransaction(); break;
        case 'delete_cash_transaction': deleteCashTransaction(); break;
        default: throw new Exception('GeÃ§ersiz action: ' . $action);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function normalizeCompanyCashCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TRY' || $currency === '') {
        $currency = 'TL';
    }
    return in_array($currency, ['TL', 'USD', 'EUR'], true) ? $currency : 'TL';
}

function getIncomes() {
    global $connection;
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
    if ($page < 1) {
        $page = 1;
    }
    if ($perPage < 1) {
        $perPage = 10;
    }
    if ($perPage > 200) {
        $perPage = 200;
    }
    $offset = ($page - 1) * $perPage;
    $search = $connection->real_escape_string($_GET['search'] ?? '');
    
    $where = $search ? "WHERE musteri_adi LIKE '%$search%' OR aciklama LIKE '%$search%'" : "";
    
    $total = $connection->query("SELECT COUNT(*) as total FROM gelir_yonetimi $where")->fetch_assoc()['total'];
    $res = $connection->query("SELECT * FROM gelir_yonetimi $where ORDER BY tarih DESC LIMIT $perPage OFFSET $offset");
    
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    
    echo json_encode(['status' => 'success', 'data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
}

function getExpenses() {
    global $connection;
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
    if ($page < 1) {
        $page = 1;
    }
    if ($perPage < 1) {
        $perPage = 10;
    }
    if ($perPage > 200) {
        $perPage = 200;
    }
    $offset = ($page - 1) * $perPage;
    $search = $connection->real_escape_string($_GET['search'] ?? '');
    
    $where = $search ? "WHERE odeme_yapilan_firma LIKE '%$search%' OR aciklama LIKE '%$search%'" : "";
    
    $total = $connection->query("SELECT COUNT(*) as total FROM gider_yonetimi $where")->fetch_assoc()['total'];
    $res = $connection->query("SELECT * FROM gider_yonetimi $where ORDER BY tarih DESC LIMIT $perPage OFFSET $offset");
    
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    
    echo json_encode(['status' => 'success', 'data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
}

function getCashTransactions() {
    global $connection;
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
    if ($page < 1) {
        $page = 1;
    }
    if ($perPage < 1) {
        $perPage = 10;
    }
    if ($perPage > 200) {
        $perPage = 200;
    }
    $offset = ($page - 1) * $perPage;
    $search = $connection->real_escape_string($_GET['search'] ?? '');
    
    $where = $search ? "WHERE aciklama LIKE '%$search%' OR kaydeden_personel LIKE '%$search%'" : "";
    
    $total = $connection->query("SELECT COUNT(*) as total FROM kasa_islemleri $where")->fetch_assoc()['total'];
    $res = $connection->query("SELECT * FROM kasa_islemleri $where ORDER BY tarih DESC, id DESC LIMIT $perPage OFFSET $offset");
    
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    
    echo json_encode(['status' => 'success', 'data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
}

function getStatistics() {
    global $connection;
    $start = date('Y-m-01');
    $end = date('Y-m-t');

    $inc = $connection->query("SELECT para_birimi, SUM(tutar) as total FROM gelir_yonetimi WHERE DATE(tarih) BETWEEN '$start' AND '$end' GROUP BY para_birimi");
    $incData = ['TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
    if ($inc) {
        while($r = $inc->fetch_assoc()) {
            $pb = normalizeCompanyCashCurrency($r['para_birimi'] ?? 'TL');
            $incData[$pb] += (float) ($r['total'] ?? 0);
        }
    }

    $exp = $connection->query("SELECT SUM(tutar) as total FROM gider_yonetimi WHERE DATE(tarih) BETWEEN '$start' AND '$end'")->fetch_assoc()['total'] ?? 0;

    $kasalar = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $kasaData = ['TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
    if ($kasalar) {
        while($r = $kasalar->fetch_assoc()) {
            $pb = normalizeCompanyCashCurrency($r['para_birimi'] ?? 'TL');
            $kasaData[$pb] += (float) ($r['bakiye'] ?? 0);
        }
    }

    echo json_encode([
        'status' => 'success',
        'monthly_income_by_currency' => $incData,
        'monthly_expenses' => $exp,
        'kasa_bakiyeleri' => $kasaData
    ]);
}

function addCashTransaction() {
    global $connection;
    // MySQL expects 'YYYY-MM-DD HH:MM:SS', but HTML datetime-local gives 'YYYY-MM-DDTHH:MM'
    $raw_tarih = $_POST['kasa_tarih'] ?? date('Y-m-d H:i:s');
    $tarih = str_replace('T', ' ', $raw_tarih);
    if(strlen($tarih) == 16) $tarih .= ':00'; // Add seconds if missing
    
    $tarih = $connection->real_escape_string($tarih);
    $tip_raw = trim((string) ($_POST['kasa_islem_tipi'] ?? ''));
    $tip_normalized = strtolower($tip_raw);
    $tip = $connection->real_escape_string($tip_raw);
    $tutar = (float)($_POST['kasa_tutar'] ?? 0);
    $pbRaw = strtoupper(trim((string) ($_POST['kasa_para_birimi'] ?? 'TL')));
    if ($pbRaw === 'TRY' || $pbRaw === '') {
        $pbRaw = 'TL';
    }
    if (!in_array($pbRaw, ['TL', 'USD', 'EUR'], true)) {
        throw new Exception('Gecersiz para birimi.');
    }
    $pb = $connection->real_escape_string($pbRaw);
    $aciklama = $connection->real_escape_string($_POST['kasa_aciklama'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';

    if ($tutar <= 0) throw new Exception('GeÃ§ersiz tutar (Girilen: '.$tutar.').');

    $connection->begin_transaction();
    try {
        // Kasa var mÄ±?
        $check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$pb' LIMIT 1");
        if (!$check) {
            throw new Exception('Kasa sorgusu basarisiz: ' . $connection->error);
        }
        if ($check->num_rows == 0) {
            if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$pb', 0)")) {
                throw new Exception('Kasa satiri olusturulamadi: ' . $connection->error);
            }
            $bakiye = 0;
        } else {
            $bakiye = (float) $check->fetch_assoc()['bakiye'];
        }

        $isIncoming = (strpos($tip_normalized, 'ekle') !== false || strpos($tip_normalized, 'alinan') !== false);
        $isOutgoing = (strpos($tip_normalized, 'cikar') !== false || strpos($tip_normalized, 'odenen') !== false || strpos($tip_normalized, 'verilen') !== false || strpos($tip_normalized, 'gider') !== false);
        if (!$isIncoming && !$isOutgoing) {
            throw new Exception('Gecersiz islem tipi.');
        }

        if ($isIncoming) {
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$pb'";
        } else {
            if ($bakiye + 0.00001 < $tutar) throw new Exception('Kasada yeterli bakiye yok! (Mevcut: '.$bakiye.')');
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$pb'";
        }

        if (!$connection->query($sql)) throw new Exception('Kasa gÃ¼ncellenemedi: ' . $connection->error);
        
        $logSql = "INSERT INTO kasa_islemleri (tarih, islem_tipi, tutar, para_birimi, aciklama, kaydeden_personel) 
                   VALUES ('$tarih', '$tip', $tutar, '$pb', '$aciklama', '$personel')";
        
        if (!$connection->query($logSql)) throw new Exception('Ä°ÅŸlem kaydedilemedi: ' . $connection->error);

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ä°ÅŸlem baÅŸarÄ±yla kaydedildi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

function deleteCashTransaction() {
    global $connection;
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('GeÃ§ersiz ID.');

    $connection->begin_transaction();
    try {
        $res = $connection->query("SELECT * FROM kasa_islemleri WHERE id = $id LIMIT 1");
        if (!$res) throw new Exception('Islem bilgisi alinamadi: ' . $connection->error);
        if ($res->num_rows == 0) throw new Exception('Ä°ÅŸlem bulunamadÄ±.');
        
        $row = $res->fetch_assoc();
        $tutar = (float)$row['tutar'];
        $pbRaw = strtoupper(trim((string) ($row['para_birimi'] ?? 'TL')));
        if ($pbRaw === 'TRY' || $pbRaw === '') {
            $pbRaw = 'TL';
        }
        if (!in_array($pbRaw, ['TL', 'USD', 'EUR'], true)) {
            throw new Exception('Kayitli para birimi gecersiz.');
        }
        $pb = $pbRaw;
        $tip = $row['islem_tipi'];
        $tip_normalized = strtolower((string) $tip);

        $kasaRes = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$pb' LIMIT 1");
        if (!$kasaRes || $kasaRes->num_rows === 0) {
            throw new Exception('Kasa bulunamadi.');
        }
        $mevcutBakiye = (float) ($kasaRes->fetch_assoc()['bakiye'] ?? 0);

        // Ters iÅŸlemle kasayÄ± dÃ¼zelt
        $isIncoming = (strpos($tip_normalized, 'ekle') !== false || strpos($tip_normalized, 'alinan') !== false);
        $isOutgoing = (strpos($tip_normalized, 'cikar') !== false || strpos($tip_normalized, 'odenen') !== false || strpos($tip_normalized, 'verilen') !== false || strpos($tip_normalized, 'gider') !== false);
        if (!$isIncoming && !$isOutgoing) {
            throw new Exception('Kayitli islem tipi gecersiz.');
        }

        if ($isIncoming) {
            // Ekleme yapÄ±lmÄ±ÅŸtÄ±, ÅŸimdi Ã§Ä±karÄ±yoruz
            if ($mevcutBakiye + 0.00001 < $tutar) {
                throw new Exception('Islem geri alininca kasa eksiye dusecek.');
            }
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$pb'";
        } else {
            // Ã‡Ä±karma yapÄ±lmÄ±ÅŸtÄ±, ÅŸimdi ekliyoruz
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$pb'";
        }

        if (!$connection->query($sql)) throw new Exception('Kasa bakiyesi geri alÄ±namadÄ±: ' . $connection->error);
        
        if (!$connection->query("DELETE FROM kasa_islemleri WHERE id = $id")) throw new Exception('KayÄ±t silinemedi.');

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ä°ÅŸlem geri alÄ±ndÄ± ve silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}
?>
