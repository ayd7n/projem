<?php
// Hata raporlamayı aç ama ekrana basma (JSON'ı bozmasın)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include '../config.php';

header('Content-Type: application/json');

// Giriş ve yetki kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetki yok veya oturum kapalı.']);
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
        default: throw new Exception('Geçersiz action: ' . $action);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getIncomes() {
    global $connection;
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
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

    $inc = $connection->query("SELECT para_birimi, SUM(tutar) as total FROM gelir_yonetimi WHERE tarih BETWEEN '$start' AND '$end' GROUP BY para_birimi");
    $incData = []; while($r = $inc->fetch_assoc()) $incData[$r['para_birimi']] = $r['total'];

    $exp = $connection->query("SELECT SUM(tutar) as total FROM gider_yonetimi WHERE tarih BETWEEN '$start' AND '$end'")->fetch_assoc()['total'] ?? 0;

    $kasalar = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $kasaData = []; while($r = $kasalar->fetch_assoc()) $kasaData[$r['para_birimi']] = $r['bakiye'];

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
    $tip = $connection->real_escape_string($_POST['kasa_islem_tipi'] ?? '');
    $tutar = (float)($_POST['kasa_tutar'] ?? 0);
    $pb = $connection->real_escape_string($_POST['kasa_para_birimi'] ?? 'TL');
    $aciklama = $connection->real_escape_string($_POST['kasa_aciklama'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';

    if ($tutar <= 0) throw new Exception('Geçersiz tutar (Girilen: '.$tutar.').');

    $connection->begin_transaction();
    try {
        // Kasa var mı?
        $check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$pb'");
        if ($check->num_rows == 0) {
            $connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$pb', 0)");
            $bakiye = 0;
        } else {
            $bakiye = $check->fetch_assoc()['bakiye'];
        }

        if (strpos($tip, 'ekle') !== false || strpos($tip, 'alinan') !== false) {
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$pb'";
        } else {
            if ($bakiye < $tutar) throw new Exception('Kasada yeterli bakiye yok! (Mevcut: '.$bakiye.')');
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$pb'";
        }

        if (!$connection->query($sql)) throw new Exception('Kasa güncellenemedi: ' . $connection->error);
        
        $logSql = "INSERT INTO kasa_islemleri (tarih, islem_tipi, tutar, para_birimi, aciklama, kaydeden_personel) 
                   VALUES ('$tarih', '$tip', $tutar, '$pb', '$aciklama', '$personel')";
        
        if (!$connection->query($logSql)) throw new Exception('İşlem kaydedilemedi: ' . $connection->error);

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla kaydedildi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

function deleteCashTransaction() {
    global $connection;
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('Geçersiz ID.');

    $connection->begin_transaction();
    try {
        $res = $connection->query("SELECT * FROM kasa_islemleri WHERE id = $id");
        if ($res->num_rows == 0) throw new Exception('İşlem bulunamadı.');
        
        $row = $res->fetch_assoc();
        $tutar = (float)$row['tutar'];
        $pb = $row['para_birimi'];
        $tip = $row['islem_tipi'];

        // Ters işlemle kasayı düzelt
        if (strpos($tip, 'ekle') !== false || strpos($tip, 'alinan') !== false) {
            // Ekleme yapılmıştı, şimdi çıkarıyoruz
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$pb'";
        } else {
            // Çıkarma yapılmıştı, şimdi ekliyoruz
            $sql = "UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$pb'";
        }

        if (!$connection->query($sql)) throw new Exception('Kasa bakiyesi geri alınamadı: ' . $connection->error);
        
        if (!$connection->query("DELETE FROM kasa_islemleri WHERE id = $id")) throw new Exception('Kayıt silinemedi.');

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İşlem geri alındı ve silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}
?>