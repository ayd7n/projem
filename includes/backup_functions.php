<?php
require_once __DIR__ . '/telegram_functions.php';

function find_latest_backup()
{
    $backup_dir = __DIR__ . '/../yedekler';
    $backups = glob($backup_dir . '/backup_*.sql');

    if (empty($backups)) {
        return null;
    }

    usort($backups, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return $backups[0];
}

function restore_database($connection, $backup_path, &$error_message = null)
{
    $error_message = null;

    if (!file_exists($backup_path)) {
        $error_message = 'Yedek dosyasi bulunamadi.';
        return false;
    }

    $sql = file_get_contents($backup_path);
    if ($sql === false) {
        $error_message = 'Yedek dosyasi okunamadi.';
        error_log('Restore database: SQL dosyasi okunamadi - ' . $backup_path);
        return false;
    }

    $normalized_sql = normalize_backup_sql($sql);
    if (!backup_has_minimum_structure($normalized_sql, $error_message)) {
        return false;
    }

    if (!validate_backup_in_temporary_database($normalized_sql, $error_message)) {
        return false;
    }

    if (!drop_all_database_objects($connection, $error_message)) {
        return false;
    }

    if (!execute_sql_batch($connection, $normalized_sql, $error_message)) {
        return false;
    }

    return true;
}

function perform_automatic_backup($connection)
{
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        return ['status' => 'error', 'message' => 'Veritabani bilgileri tanimli degil.'];
    }

    $backup_dir = __DIR__ . '/../yedekler';
    if (!is_dir($backup_dir) && !mkdir($backup_dir, 0777, true)) {
        return ['status' => 'error', 'message' => "Yedekleme dizini olusturulamadi: {$backup_dir}"];
    }

    if (!is_writable($backup_dir)) {
        @chmod($backup_dir, 0777);
    }

    if (!is_writable($backup_dir)) {
        $real_path = realpath($backup_dir) ?: $backup_dir;
        return ['status' => 'error', 'message' => "Yedekleme dizini yazilabilir degil: {$real_path}"];
    }

    $backup_file_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_file_path = $backup_dir . '/' . $backup_file_name;

    $mysqldump_success = false;
    $mysqldump_error = '';

    if (function_exists('exec')) {
        $mysqldump_path = resolve_mysqldump_path();
        if ($mysqldump_path !== null) {
            $command_parts = [
                escapeshellarg($mysqldump_path),
                '--host=' . escapeshellarg(DB_HOST),
                '--user=' . escapeshellarg(DB_USER),
                '--default-character-set=utf8mb4',
                '--skip-comments',
                '--skip-triggers',
                '--skip-add-locks',
                '--skip-lock-tables',
                '--no-tablespaces',
                '--result-file=' . escapeshellarg($backup_file_path),
                escapeshellarg(DB_NAME),
            ];

            if (DB_PASS !== '') {
                $command_parts[] = '--password=' . escapeshellarg(DB_PASS);
            }

            $output = [];
            $return_var = 0;
            exec(implode(' ', $command_parts) . ' 2>&1', $output, $return_var);

            if ($return_var === 0 && file_exists($backup_file_path) && filesize($backup_file_path) > 0) {
                $validation_error = null;
                if (validate_generated_backup_file($backup_file_path, $validation_error)) {
                    $mysqldump_success = true;
                } else {
                    $mysqldump_error = $validation_error ?: 'mysqldump gecerli bir dosya uretmedi.';
                    @unlink($backup_file_path);
                }
            } else {
                $mysqldump_error = 'mysqldump basarisiz oldu (Kod: ' . $return_var . '). ' . implode("\n", $output);
                if (file_exists($backup_file_path)) {
                    @unlink($backup_file_path);
                }
            }
        } else {
            $mysqldump_error = 'mysqldump bulunamadi.';
        }
    } else {
        $mysqldump_error = 'exec fonksiyonu devre disi.';
    }

    if (!$mysqldump_success) {
        $php_backup_result = php_native_backup($connection, $backup_file_path);
        if ($php_backup_result['status'] !== 'success') {
            return [
                'status' => 'error',
                'message' => 'Yedekleme basarisiz. mysqldump: ' . $mysqldump_error . ' PHP yedekleme: ' . $php_backup_result['message'],
            ];
        }

        $validation_error = null;
        if (!validate_generated_backup_file($backup_file_path, $validation_error)) {
            @unlink($backup_file_path);
            return [
                'status' => 'error',
                'message' => 'Yedek dosyasi eksik veya bozuk olustu. ' . $validation_error,
            ];
        }
    }

    if (function_exists('update_setting')) {
        update_setting($connection, 'son_otomatik_yedek_tarihi', date('Y-m-d H:i:s'));
    }

    $telegram_settings = get_telegram_settings($connection);
    if (!empty($telegram_settings['bot_token']) && !empty($telegram_settings['chat_id'])) {
        $caption = 'Veritabani yedegi olusturuldu: ' . $backup_file_name;
        sendTelegramFile($backup_file_path, $caption, $telegram_settings['bot_token'], $telegram_settings['chat_id']);
    }

    return [
        'status' => 'success',
        'message' => 'Veritabani basariyla yedeklendi: ' . basename($backup_file_path),
    ];
}

function php_native_backup($connection, $filepath)
{
    try {
        $tables = fetch_database_objects($connection, 'BASE TABLE');
        $views = fetch_database_objects($connection, 'VIEW');

        if (empty($tables) && empty($views)) {
            return ['status' => 'error', 'message' => 'Yedeklenecek tablo veya view bulunamadi.'];
        }

        $content = "-- PHP Native Backup\n";
        $content .= '-- Generated: ' . date('Y-m-d H:i:s') . "\n\n";
        $content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $content .= "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $create_result = $connection->query('SHOW CREATE TABLE ' . quote_identifier($table));
            if (!$create_result) {
                return ['status' => 'error', 'message' => $connection->error];
            }

            $create_row = $create_result->fetch_assoc();
            $create_sql = $create_row['Create Table'] ?? array_values($create_row)[1];

            $content .= 'DROP TABLE IF EXISTS ' . quote_identifier($table) . ";\n";
            $content .= $create_sql . ";\n\n";

            $result = $connection->query('SELECT * FROM ' . quote_identifier($table));
            if (!$result) {
                return ['status' => 'error', 'message' => $connection->error];
            }

            $fields = $result->fetch_fields();
            $columns = array_map(function ($field) {
                return quote_identifier($field->name);
            }, $fields);

            while ($row = $result->fetch_row()) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $connection->real_escape_string((string) $value) . "'";
                    }
                }

                $content .= 'INSERT INTO ' . quote_identifier($table) . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
            }

            $content .= "\n";
        }

        foreach ($views as $view) {
            $create_result = $connection->query('SHOW CREATE VIEW ' . quote_identifier($view));
            if (!$create_result) {
                return ['status' => 'error', 'message' => $connection->error];
            }

            $create_row = $create_result->fetch_assoc();
            $create_sql = $create_row['Create View'] ?? array_values($create_row)[1];
            $create_sql = rtrim(normalize_backup_sql($create_sql), ";\r\n\t ");

            $content .= 'DROP VIEW IF EXISTS ' . quote_identifier($view) . ";\n";
            $content .= $create_sql . ";\n\n";
        }

        if (file_put_contents($filepath, $content) === false) {
            return ['status' => 'error', 'message' => 'Yedek dosyasi yazilamadi.'];
        }

        return ['status' => 'success'];
    } catch (Throwable $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function resolve_mysqldump_path()
{
    $candidates = [];

    if (PHP_OS_FAMILY === 'Windows') {
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $output = [];
        $return_var = 1;
        @exec('where mysqldump', $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            return trim($output[0]);
        }
    } else {
        $output = [];
        $return_var = 1;
        @exec('which mysqldump', $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        $candidates = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/mysql/bin/mysqldump',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }
    }

    return null;
}

function normalize_backup_sql($sql)
{
    $normalized = preg_replace('/^\xEF\xBB\xBF/', '', (string) $sql);
    $normalized = str_replace(["\r\n", "\r"], "\n", $normalized);
    $normalized = preg_replace('/^\s*USE\s+`?[^`;\n]+`?\s*;\s*$/im', '', $normalized);
    $normalized = preg_replace('/\s+DEFINER=`[^`]+`@`[^`]+`/i', '', $normalized);

    if (defined('DB_NAME') && DB_NAME !== '') {
        $normalized = preg_replace('/`' . preg_quote(DB_NAME, '/') . '`\./i', '', $normalized);
    }

    return trim($normalized) . "\n";
}

function validate_generated_backup_file($filepath, &$error_message = null)
{
    $error_message = null;

    if (!file_exists($filepath)) {
        $error_message = 'Olusan yedek dosyasi bulunamadi.';
        return false;
    }

    $sql = file_get_contents($filepath);
    if ($sql === false) {
        $error_message = 'Olusan yedek dosyasi okunamadi.';
        return false;
    }

    $normalized_sql = normalize_backup_sql($sql);
    if (!backup_has_minimum_structure($normalized_sql, $error_message)) {
        return false;
    }

    return validate_backup_in_temporary_database($normalized_sql, $error_message);
}

function backup_has_minimum_structure($sql, &$error_message = null, $minimum_table_count = 10)
{
    $error_message = null;
    $table_count = 0;

    if (preg_match_all('/^\s*CREATE\s+TABLE\b/im', $sql, $matches)) {
        $table_count = count($matches[0]);
    }

    if ($table_count < $minimum_table_count) {
        $error_message = 'Yedek dosyasi yalnizca ' . $table_count . ' tablo iceriyor. Bu dosya eksik veya bozuk gorunuyor.';
        return false;
    }

    return true;
}

function validate_backup_in_temporary_database($sql, &$error_message = null)
{
    $error_message = null;
    $server_connection = null;
    $temp_connection = null;
    $temp_db_name = null;

    try {
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            $error_message = 'Gecici dogrulama icin veritabani bilgileri eksik.';
            return false;
        }

        $temp_db_name = DB_NAME . '_restore_validation_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
        $server_connection = new mysqli(DB_HOST, DB_USER, DB_PASS);
        configure_connection_charset($server_connection);

        $create_db_sql = 'CREATE DATABASE ' . quote_identifier($temp_db_name) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        $server_connection->query($create_db_sql);

        $temp_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, $temp_db_name);
        configure_connection_charset($temp_connection);

        $temp_sql = str_replace('`' . DB_NAME . '`.', '`' . $temp_db_name . '`.', $sql);
        if (!execute_sql_batch($temp_connection, $temp_sql, $error_message)) {
            $error_message = 'Yedek dosyasi dogrulanamadi: ' . $error_message;
            return false;
        }

        return true;
    } catch (Throwable $e) {
        $error_message = 'Gecici dogrulama basarisiz oldu: ' . $e->getMessage();
        error_log('Backup validation failed: ' . $e->getMessage());
        return false;
    } finally {
        if ($temp_connection instanceof mysqli) {
            $temp_connection->close();
        }

        if ($server_connection instanceof mysqli && $temp_db_name !== null) {
            try {
                $server_connection->query('DROP DATABASE IF EXISTS ' . quote_identifier($temp_db_name));
            } catch (Throwable $ignored) {
            }
            $server_connection->close();
        }
    }
}

function drop_all_database_objects($connection, &$error_message = null)
{
    $error_message = null;

    try {
        $connection->query('SET foreign_key_checks = 0');

        foreach (fetch_database_objects($connection, 'VIEW') as $view) {
            $connection->query('DROP VIEW IF EXISTS ' . quote_identifier($view));
        }

        foreach (fetch_database_objects($connection, 'BASE TABLE') as $table) {
            $connection->query('DROP TABLE IF EXISTS ' . quote_identifier($table));
        }

        $connection->query('SET foreign_key_checks = 1');
        return true;
    } catch (Throwable $e) {
        $error_message = 'Mevcut veritabani nesneleri silinemedi: ' . $e->getMessage();
        error_log('Restore database drop failed: ' . $e->getMessage());

        try {
            $connection->query('SET foreign_key_checks = 1');
        } catch (Throwable $ignored) {
        }

        return false;
    }
}

function execute_sql_batch($connection, $sql, &$error_message = null)
{
    $error_message = null;

    try {
        $connection->query('SET foreign_key_checks = 0');

        if (!$connection->multi_query($sql)) {
            $error_message = $connection->error ?: 'SQL batch baslatilamadi.';
            return false;
        }

        do {
            $result = $connection->store_result();
            if ($result instanceof mysqli_result) {
                $result->free();
            }

            if (!$connection->more_results()) {
                break;
            }

            if (!$connection->next_result()) {
                $error_message = $connection->error ?: 'SQL batch sirasinda bir ifade basarisiz oldu.';
                return false;
            }
        } while (true);

        $connection->query('SET foreign_key_checks = 1');
        return true;
    } catch (Throwable $e) {
        $error_message = $e->getMessage();
        error_log('SQL batch execution failed: ' . $e->getMessage());
        return false;
    } finally {
        try {
            $connection->query('SET foreign_key_checks = 1');
        } catch (Throwable $ignored) {
        }
    }
}

function fetch_database_objects($connection, $type = null)
{
    $objects = [];
    $result = $connection->query('SHOW FULL TABLES');

    if (!$result) {
        return $objects;
    }

    while ($row = $result->fetch_row()) {
        if ($type === null || strcasecmp($row[1], $type) === 0) {
            $objects[] = $row[0];
        }
    }

    return $objects;
}

function configure_connection_charset($connection)
{
    $connection->set_charset('utf8mb4');
    $connection->query("SET NAMES 'utf8mb4'");
    $connection->query('SET CHARACTER SET utf8mb4');
    $connection->query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
}

function quote_identifier($name)
{
    return '`' . str_replace('`', '``', (string) $name) . '`';
}
