<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['status' => 'error', 'message' => 'Gecersiz istek.'];

function fetch_try_rate($from_currency, &$error_message = null)
{
    $from_currency = strtoupper(trim((string) $from_currency));
    $url = "https://api.frankfurter.app/latest?from={$from_currency}&to=TRY";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 8
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        $error_message = $from_currency . ' kuru servisten alinamadi.';
        return null;
    }

    $json = json_decode($raw, true);
    if (!is_array($json) || !isset($json['rates']['TRY'])) {
        $error_message = $from_currency . ' kuru gecersiz formatta dondu.';
        return null;
    }

    $rate = (float) $json['rates']['TRY'];
    if ($rate <= 0) {
        $error_message = $from_currency . ' kuru gecersiz deger dondu.';
        return null;
    }

    return round($rate, 4);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['status' => 'error', 'message' => 'Sadece POST istegi kabul edilir.'];
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['user_id']) || ($_SESSION['taraf'] ?? '') !== 'personel') {
    $response = ['status' => 'error', 'message' => 'Yetkisiz erisim.'];
    echo json_encode($response);
    exit;
}

$is_admin = !empty($_SESSION['is_admin']) ||
    (isset($_SESSION['email']) && in_array($_SESSION['email'], ['admin@parfum.com', 'admin2@parfum.com'], true));

if (!$is_admin) {
    $response = ['status' => 'error', 'message' => 'Bu islem sadece admin icin yetkilidir.'];
    echo json_encode($response);
    exit;
}

try {
    $usd_error = null;
    $eur_error = null;

    $usd_try = fetch_try_rate('USD', $usd_error);
    $eur_try = fetch_try_rate('EUR', $eur_error);

    if ($usd_try === null || $eur_try === null) {
        $details = trim(($usd_error ?? '') . ' ' . ($eur_error ?? ''));
        $response = [
            'status' => 'error',
            'message' => 'Doviz kurlari otomatik guncellenemedi. ' . $details
        ];
        echo json_encode($response);
        exit;
    }

    $connection->begin_transaction();

    $upsert_sql = "INSERT INTO ayarlar (ayar_anahtar, ayar_deger) VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE ayar_deger = VALUES(ayar_deger)";
    $upsert_stmt = $connection->prepare($upsert_sql);
    if (!$upsert_stmt) {
        throw new Exception('Ayar sorgusu hazirlanamadi: ' . $connection->error);
    }

    $dolar_value = number_format($usd_try, 4, '.', '');
    $dolar_key = 'dolar_kuru';
    $upsert_stmt->bind_param('ss', $dolar_key, $dolar_value);
    if (!$upsert_stmt->execute()) {
        throw new Exception('Dolar kuru guncellenemedi: ' . $upsert_stmt->error);
    }

    $euro_value = number_format($eur_try, 4, '.', '');
    $euro_key = 'euro_kuru';
    $upsert_stmt->bind_param('ss', $euro_key, $euro_value);
    if (!$upsert_stmt->execute()) {
        throw new Exception('Euro kuru guncellenemedi: ' . $upsert_stmt->error);
    }

    $upsert_stmt->close();

    // Intentionally avoid log_islem(): that helper sends Telegram notifications.
    $log_stmt = $connection->prepare(
        "INSERT INTO log_tablosu (kullanici_adi, log_metni, islem_turu) VALUES (?, ?, ?)"
    );
    if ($log_stmt) {
        $log_user = $_SESSION['kullanici_adi'] ?? 'SISTEM';
        $log_text = 'Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: ' . $dolar_value . ', EUR: ' . $euro_value;
        $log_type = 'AUTO_UPDATE';
        $log_stmt->bind_param('sss', $log_user, $log_text, $log_type);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $connection->commit();

    $response = [
        'status' => 'success',
        'message' => 'Doviz kurlari otomatik olarak guncellendi.',
        'data' => [
            'dolar_kuru' => $dolar_value,
            'euro_kuru' => $euro_value
        ]
    ];
} catch (Throwable $t) {
    if (method_exists($connection, 'rollback')) {
        $connection->rollback();
    }

    $response = [
        'status' => 'error',
        'message' => 'Otomatik doviz guncelleme hatasi: ' . $t->getMessage()
    ];
}

echo json_encode($response);

