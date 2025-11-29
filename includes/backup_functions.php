<?php
// includes/backup_functions.php
require_once __DIR__ . '/telegram_functions.php'; // For sending notifications

/**
 * 'yedekler' klasöründeki en son oluşturulmuş .sql yedek dosyasını bulur.
 * Dosya adlarındaki tarih ve saate göre sıralama yapar.
 *
 * @return string|null En son yedek dosyasının tam yolu veya yedek bulunamazsa null.
 */
function find_latest_backup()
{
    $backup_dir = __DIR__ . '/../yedekler';
    $backups = glob($backup_dir . '/backup_*.sql');

    if (empty($backups)) {
        return null;
    }

    // Dosyaları değiştirilme zamanına göre sırala (en yeni en üstte)
    usort($backups, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return $backups[0];
}

/**
 * Veritabanını sağlanan .sql dosyasından geri yükler.
 * DİKKAT: Bu işlem, geri yüklemeden önce veritabanındaki tüm tabloları SİLER.
 *
 * @param mysqli $connection Aktif veritabanı bağlantısı.
 * @param string $backup_path Geri yüklenecek .sql dosyasının tam yolu.
 * @return bool Başarılı olursa true, başarısız olursa false döner.
 */
function restore_database($connection, $backup_path)
{
    if (!file_exists($backup_path)) {
        return false;
    }

    // Yabancı anahtar kontrolünü geçici olarak devre dışı bırak
    $connection->query('SET foreign_key_checks = 0');

    // Veritabanındaki tüm tabloları sil
    // DİKKAT: Bu, bağlantının mevcut veritabanını temel alır.
    $result = $connection->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $connection->query('DROP TABLE IF EXISTS `' . $row[0] . '`');
        }
    } else {
        // Hata durumunda yabancı anahtar kontrolünü tekrar aktif et
        $connection->query('SET foreign_key_checks = 1');
        error_log("Restore database: SHOW TABLES hatası - " . $connection->error);
        return false;
    }


    // SQL dosyasını oku
    $sql = file_get_contents($backup_path);
    if ($sql === false) {
        $connection->query('SET foreign_key_checks = 1');
        error_log("Restore database: SQL dosyası okunamadı - " . $backup_path);
        return false;
    }

    // SQL komutlarını çalıştır
    if ($connection->multi_query($sql)) {
        // multi_query'den sonra kalan sonuçları temizle
        while ($connection->next_result()) {
            if ($result = $connection->store_result()) {
                $result->free();
            }
        }
    } else {
        // Hata durumunda yabancı anahtar kontrolünü tekrar aktif et
        $connection->query('SET foreign_key_checks = 1');
        error_log("Restore database: multi_query hatası - " . $connection->error);
        return false;
    }

    // Yabancı anahtar kontrolünü tekrar aktif et
    $connection->query('SET foreign_key_checks = 1');

    return true;
}

/**
 * Performs an automatic database backup using mysqldump and sends it to Telegram.
 * DİKKAT: sunucuda 'mysqldump' komutunun çalıştırılabilir olması ve
 * PHP'nin 'shell_exec' fonksiyonunu kullanma izni olması gerekir.
 *
 * @param mysqli $connection The database connection object.
 * @return array Status and message of the backup operation.
 */
function perform_automatic_backup($connection)
{
    // Ensure the required constants are defined before using them.
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        return ['status' => 'error', 'message' => 'Database credentials are not defined.'];
    }

    $backup_dir = __DIR__ . '/../yedekler';
    if (!is_dir($backup_dir)) {
        // 0777 permission for wider compatibility
        if (!mkdir($backup_dir, 0777, true)) {
            return ['status' => 'error', 'message' => "Yedekleme dizini oluşturulamadı: {$backup_dir}"];
        }
    }

    if (!is_writable($backup_dir)) {
        return ['status' => 'error', 'message' => "Yedekleme dizini yazılabilir değil: {$backup_dir}"];
    }

    $backup_file_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_file_path = $backup_dir . '/' . $backup_file_name;

    // shell_exec/exec'in kullanılabilir olup olmadığını kontrol et
    if (!function_exists('exec')) {
        return ['status' => 'error', 'message' => 'exec fonksiyonu bu sunucuda devre dışı bırakılmış.'];
    }

    // Determine mysqldump path
    $mysqldump_cmd = 'mysqldump'; // Default
    if (PHP_OS_FAMILY === 'Linux') {
        // Try to find using 'which' command first
        $which_output = [];
        $which_return = 0;
        exec('which mysqldump', $which_output, $which_return);

        if ($which_return === 0 && !empty($which_output[0])) {
            $mysqldump_cmd = $which_output[0];
        } else {
            // Fallback to common paths
            $possible_paths = [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/usr/mysql/bin/mysqldump'
            ];

            foreach ($possible_paths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $mysqldump_cmd = $path;
                    break;
                }
            }
        }
    }

    // mysqldump komutunu oluştur
    $password_arg = DB_PASS ? sprintf('-p%s', escapeshellarg(DB_PASS)) : '';

    // Construct command
    $command = sprintf(
        '%s -h %s -u %s %s %s > %s 2>&1',
        $mysqldump_cmd,
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_USER),
        $password_arg,
        escapeshellarg(DB_NAME),
        escapeshellarg($backup_file_path)
    );

    // Komutu çalıştır ve çıktıyı yakala
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);

    // Yedekleme işleminin başarılı olup olmadığını kontrol et
    if ($return_var === 0 && file_exists($backup_file_path) && filesize($backup_file_path) > 0) {
        // Yedekleme başarılı, veritabanındaki son yedekleme tarihini güncelle
        if (function_exists('update_setting')) {
            update_setting($connection, 'son_otomatik_yedek_tarihi', date('Y-m-d H:i:s'));
        }

        // Send backup to Telegram
        $telegram_settings = get_telegram_settings($connection);
        if (!empty($telegram_settings['bot_token']) && !empty($telegram_settings['chat_id'])) {
            $caption = "Veritabanı yedeği oluşturuldu: " . $backup_file_name;
            sendTelegramFile($backup_file_path, $caption, $telegram_settings['bot_token'], $telegram_settings['chat_id']);
        }

        return ['status' => 'success', 'message' => "Veritabanı başarıyla yedeklendi ve Telegram'a gönderildi: " . basename($backup_file_path)];
    } else {
        // Yedekleme başarısız oldu
        // Mask password in command for display
        $masked_command = str_replace($password_arg, '-p*****', $command);

        $error_message = "Yedekleme başarısız (Kod: $return_var).";
        $error_message .= " Komut: $masked_command";

        if (!empty($output)) {
            $error_message .= " Çıktı: " . implode("\n", $output);
        } else {
            $error_message .= " (Çıktı boş)";
        }

        // Başarısız yedek dosyasını sil
        if (file_exists($backup_file_path)) {
            unlink($backup_file_path);
        }
        return ['status' => 'error', 'message' => $error_message];
    }
}