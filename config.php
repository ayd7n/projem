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
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

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

?>