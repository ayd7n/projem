<?php
// File: includes/backup_functions.php

/**
 * Fetches a specific setting's value from the 'ayarlar' table.
 *
 * @param mysqli $conn The database connection object.
 * @param string $setting_name The key of the setting to retrieve.
 * @return string|null The value of the setting or null if not found.
 */
function get_setting($conn, $setting_name) {
    $stmt = $conn->prepare("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = ?");
    $stmt->bind_param("s", $setting_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['ayar_deger'];
    }
    return null;
}

/**
 * Updates a specific setting in the 'ayarlar' table.
 *
 * @param mysqli $conn The database connection object.
 * @param string $setting_name The key of the setting to update.
 * @param string $setting_value The new value for the setting.
 * @return bool True on success, false on failure.
 */
function update_setting($conn, $setting_name, $setting_value) {
    $stmt = $conn->prepare("UPDATE ayarlar SET ayar_deger = ? WHERE ayar_anahtar = ?");
    $stmt->bind_param("ss", $setting_value, $setting_name);
    return $stmt->execute();
}

/**
 * Performs the database backup using mysqldump.
 *
 * @param mysqli $connection The database connection object (used for updating settings).
 * @return array An array with 'status' and 'message'.
 */
function perform_automatic_backup($connection) {
    $backup_dir = __DIR__ . '/../yedekler/';
    
    // Ensure the backup directory exists and is writable
    if (!is_dir($backup_dir)) {
        if (!mkdir($backup_dir, 0777, true)) {
            error_log("Yedekleme dizini oluşturulamadı: " . $backup_dir);
            return ['status' => 'error', 'message' => 'Yedekleme dizini oluşturulamadı.'];
        }
    }
    if (!is_writable($backup_dir)) {
        error_log("Yedekleme dizini yazılabilir değil: " . $backup_dir);
        return ['status' => 'error', 'message' => 'Yedekleme dizini yazılabilir değil.'];
    }

    // Database credentials from config.php (already defined constants)
    $db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $db_user = defined('DB_USER') ? DB_USER : 'root';
    $db_pass = defined('DB_PASS') ? DB_PASS : '';
    $db_name = defined('DB_NAME') ? DB_NAME : 'parfum_erp';
    
    $filename = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Use a more robust path for mysqldump
    $mysqldump_path = 'mysqldump'; // Assume it's in PATH

    $command = sprintf(
        '%s --host=%s --user=%s --password=%s %s > %s',
        escapeshellarg($mysqldump_path),
        escapeshellarg($db_host),
        escapeshellarg($db_user),
        escapeshellarg($db_pass),
        escapeshellarg($db_name),
        escapeshellarg($filename)
    );

    exec($command, $output, $return_var);

    if ($return_var === 0) {
        // Update the last automatic backup date in the ayarlar table
        update_setting($connection, 'son_otomatik_yedek_tarihi', date('Y-m-d H:i:s'));
        return ['status' => 'success', 'message' => 'Otomatik veritabanı yedeği başarıyla oluşturuldu.'];
    } else {
        error_log("Otomatik yedekleme hatası: " . implode("\n", $output));
        return ['status' => 'error', 'message' => 'Otomatik yedekleme oluşturulurken bir hata oluştu.', 'details' => $output];
    }
}
?>
