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
        if (!mkdir($backup_dir, 0777, true)) {
            return ['status' => 'error', 'message' => "Yedekleme dizini oluşturulamadı: {$backup_dir}"];
        }
    } else {
        // If it exists, try to ensure it is writable
        if (!is_writable($backup_dir)) {
            @chmod($backup_dir, 0777);
        }
    }

    if (!is_writable($backup_dir)) {
        $real_path = realpath($backup_dir) ?: $backup_dir;
        return ['status' => 'error', 'message' => "Yedekleme dizini yazılabilir değil. Lütfen sunucunuzda şu komutu çalıştırın: chmod 777 {$real_path}"];
    }

    $backup_file_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_file_path = $backup_dir . '/' . $backup_file_name;

    $mysqldump_success = false;
    $mysqldump_error = "";

    // 1. Try mysqldump if exec is available
    if (function_exists('exec')) {
        $mysqldump_cmd = 'mysqldump';
        if (PHP_OS_FAMILY === 'Linux') {
            $which_output = [];
            $which_return = 0;
            exec('which mysqldump', $which_output, $which_return);
            if ($which_return === 0 && !empty($which_output[0])) {
                $mysqldump_cmd = $which_output[0];
            } else {
                $possible_paths = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/usr/mysql/bin/mysqldump'];
                foreach ($possible_paths as $path) {
                    if (file_exists($path) && is_executable($path)) {
                        $mysqldump_cmd = $path;
                        break;
                    }
                }
            }
        }

        $password_arg = DB_PASS ? sprintf('-p%s', escapeshellarg(DB_PASS)) : '';
        $command = sprintf(
            '%s -h %s -u %s %s %s > %s 2>&1',
            $mysqldump_cmd,
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            $password_arg,
            escapeshellarg(DB_NAME),
            escapeshellarg($backup_file_path)
        );

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($backup_file_path) && filesize($backup_file_path) > 0) {
            $mysqldump_success = true;
        } else {
            $mysqldump_error = "mysqldump failed (Code: $return_var). Output: " . implode("\n", $output);
            // Clean up empty or partial file
            if (file_exists($backup_file_path)) {
                @unlink($backup_file_path);
            }
        }
    } else {
        $mysqldump_error = "exec function is disabled.";
    }

    // 2. Fallback to PHP Native Backup if mysqldump failed
    if (!$mysqldump_success) {
        // Try PHP native backup
        $php_backup_result = php_native_backup($connection, $backup_file_path);
        if ($php_backup_result['status'] === 'success') {
            // Success with PHP fallback
        } else {
            // Both failed
            return [
                'status' => 'error',
                'message' => "Yedekleme başarısız. mysqldump hatası: $mysqldump_error. PHP yedekleme hatası: " . $php_backup_result['message']
            ];
        }
    }

    // Success (either mysqldump or PHP native)
    if (function_exists('update_setting')) {
        update_setting($connection, 'son_otomatik_yedek_tarihi', date('Y-m-d H:i:s'));
    }

    // Send backup to Telegram
    $telegram_settings = get_telegram_settings($connection);
    if (!empty($telegram_settings['bot_token']) && !empty($telegram_settings['chat_id'])) {
        $caption = "Veritabanı yedeği oluşturuldu: " . $backup_file_name;
        sendTelegramFile($backup_file_path, $caption, $telegram_settings['bot_token'], $telegram_settings['chat_id']);
    }

    return ['status' => 'success', 'message' => "Veritabanı başarıyla yedeklendi (" . ($mysqldump_success ? "Sistem" : "PHP") . ") ve Telegram'a gönderildi: " . basename($backup_file_path)];
}

/**
 * Generates a database backup using native PHP code.
 * Useful as a fallback when mysqldump is not available.
 */
function php_native_backup($connection, $filepath)
{
    try {
        $tables = [];
        $result = $connection->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        $content = "-- PHP Native Backup\n";
        $content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $content .= "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            // Get create table syntax
            $row2 = $connection->query("SHOW CREATE TABLE `$table`")->fetch_row();
            $content .= "\n\n" . $row2[1] . ";\n\n";

            // Get data
            $result3 = $connection->query("SELECT * FROM `$table`");
            $num_fields = $result3->field_count;

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $result3->fetch_row()) {
                    $content .= "INSERT INTO `$table` VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $content .= '"' . $row[$j] . '"';
                        } else {
                            $content .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ");\n";
                }
            }
            $content .= "\n\n\n";
        }

        if (file_put_contents($filepath, $content) !== false) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'error', 'message' => 'Dosya yazılamadı.'];
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}