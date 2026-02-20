<?php
date_default_timezone_set('Europe/Istanbul');
// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'parfum_erp');
}

// Create connection
// Only create connection if it doesn't exist to avoid multiple connections
if (!isset($connection)) {
    try {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception $e) {
        // Eğer hata "Unknown database" ise (Hata kodu: 1049)
        if ($e->getCode() === 1049) {
            // Veritabanı olmadan bağlan
            $temp_conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            if ($temp_conn->connect_error) {
                die("Kritik Bağlantı Hatası: " . $temp_conn->connect_error);
            }

            // Veritabanını oluştur
            $create_db_query = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if ($temp_conn->query($create_db_query)) {
                // Yeni oluşturulan veritabanına bağlan
                $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                
                // Yedekleme fonksiyonlarını yükle
                require_once __DIR__ . '/includes/backup_functions.php';
                
                // En son yedeği bul
                $latest_backup = find_latest_backup();
                if ($latest_backup) {
                    // Veritabanını geri yükle
                    restore_database($connection, $latest_backup);
                }
            } else {
                die("Veritabanı oluşturulamadı: " . $temp_conn->error);
            }
            $temp_conn->close();
        } else {
            // Başka bir hata ise fırlat
            throw $e;
        }
    }

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Set charset to UTF-8
    $connection->set_charset("utf8mb4");
    $connection->query("SET NAMES 'utf8mb4'");
    $connection->query("SET CHARACTER SET utf8mb4");
    $connection->query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
}


// Session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Automatic Daily Backup Logic (Safe Implementation) ---
// This block should be after the database connection is established.

// Include the decoupled backup functions
require_once __DIR__ . '/includes/backup_functions.php';
// Include the new authentication helper functions
require_once __DIR__ . '/includes/auth_functions.php';

// Use a try-catch block to prevent backup errors from crashing the entire site
try {
    // Get the last automatic backup date from settings
    $last_backup_date_str = get_setting($connection, 'son_otomatik_yedek_tarihi');

    // If the setting doesn't exist, we don't proceed.
    // The setting should be added to the database via ayarlar_tablosu.sql
    if ($last_backup_date_str) {
        $last_backup_timestamp = strtotime($last_backup_date_str);

        // Check if a backup has been made today.
        // We compare dates ('Y-m-d') to ensure one backup per day.
        if (date('Y-m-d', $last_backup_timestamp) < date('Y-m-d')) {
            // If the last backup was on a previous day, perform a new backup.
            $backup_result = perform_automatic_backup($connection);

            if ($backup_result['status'] === 'error') {
                // Log the error, but don't stop page execution.
                error_log("Otomatik yedekleme hatası: " . $backup_result['message']);
            }
        }
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions during the backup check/process
    error_log("Otomatik yedekleme sürecinde kritik hata: " . $e->getMessage());
}
// --- End Automatic Daily Backup Logic ---

// Loglama fonksiyonu
if (!function_exists('log_islem')) {
    function log_islem($connection, $kullanici_adi, $log_metni, $islem_turu = 'OTHER')
    {
        $stmt = $connection->prepare("INSERT INTO log_tablosu (kullanici_adi, log_metni, islem_turu) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $kullanici_adi, $log_metni, $islem_turu);
            $stmt->execute();
            $stmt->close();
        }

        // Telegram'a mesaj gönder
        telegram_gonder($log_metni);
    }
}

// Telegram mesaj gönderme fonksiyonu
if (!function_exists('telegram_gonder')) {
    function telegram_gonder($log_message)
    {
        global $connection;

        // Ayarlardan Telegram bot token ve chat id'leri al
        $result = $connection->query("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'telegram_bot_token'");
        $bot_token = $result->fetch_assoc()['ayar_deger'] ?? '';

        $result = $connection->query("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'telegram_chat_id'");
        $chat_ids_raw = $result->fetch_assoc()['ayar_deger'] ?? '';

        // Eğer bot token yoksa işlem yapma
        if (empty($bot_token)) {
            return;
        }

        // Chat ID'leri satır satır böl ve boş olanları temizle
        $chat_ids = array_filter(array_map('trim', explode("\n", $chat_ids_raw)), function ($id) {
            return !empty($id);
        });

        // Eğer herhangi bir chat ID yoksa işlem yapma
        if (empty($chat_ids)) {
            return;
        }

        // Her bir chat ID'ye mesaj gönder
        foreach ($chat_ids as $chat_id) {
            // Telegram API'ye mesaj gönder
            $telegram_url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
            $data = array(
                'chat_id' => $chat_id,
                'text' => $log_message
            );

            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/json\r\n",
                    'method' => "POST",
                    'content' => json_encode($data),
                ),
            );

            $context = stream_context_create($options);
            $result = file_get_contents($telegram_url, false, $context);
        }

        // Hata durumunda sadece pas geç
    }
}
