<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'list_tables') {
    $tables = [];
    $excludedTables = ['ayarlar', 'sistem_kullanicilari', 'malzeme_turleri'];
    $result = $connection->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            
            // Skip excluded tables
            if (in_array($tableName, $excludedTables)) {
                continue;
            }

            // Get row count - exclude admin users for personeller table
            if ($tableName === 'personeller') {
                $countResult = $connection->query("SELECT COUNT(*) as count FROM `$tableName` WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')");
            } else {
                $countResult = $connection->query("SELECT COUNT(*) as count FROM `$tableName`");
            }
            $count = 0;
            if ($countResult) {
                $countRow = $countResult->fetch_assoc();
                $count = $countRow['count'];
            }
            $tables[] = ['name' => $tableName, 'rows' => $count];
        }
        echo json_encode(['status' => 'success', 'tables' => $tables]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tablolar listelenemedi.']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] == 'clear_tables') {
    if (!isset($_POST['tables']) || !is_array($_POST['tables']) || empty($_POST['tables'])) {
        echo json_encode(['status' => 'error', 'message' => 'Hiçbir tablo seçilmedi.']);
        exit;
    }

    $tablesToClear = $_POST['tables'];
    $successCount = 0;
    $errors = [];

    // Disable foreign key checks to allow truncation
    $connection->query("SET FOREIGN_KEY_CHECKS = 0");

    foreach ($tablesToClear as $table) {
        // Sanitize table name (basic protection, though input comes from trusted staff session)
        $table = $connection->real_escape_string($table);

        // Special handling for personeller table - don't delete admin users
        if ($table === 'personeller') {
            // Delete all personeller except admin users
            if ($connection->query("DELETE FROM `$table` WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')")) {
                $successCount++;
            } else {
                $errors[] = "$table: " . $connection->error;
            }
        } else {
            // Use TRUNCATE for faster and cleaner removal (resets auto_increment)
            // If TRUNCATE fails (e.g. view), try DELETE
            if ($connection->query("TRUNCATE TABLE `$table`")) {
                $successCount++;
            } else {
                // Fallback to DELETE if TRUNCATE fails (e.g. due to view or locking)
                if ($connection->query("DELETE FROM `$table`")) {
                    $successCount++;
                } else {
                    $errors[] = "$table: " . $connection->error;
                }
            }
        }
    }

    // Log ekleme
    $tables_list = implode(", ", $tablesToClear);
    log_islem($connection, $_SESSION['kullanici_adi'], "$tables_list tabloları temizlendi", 'DELETE');

    // Re-enable foreign key checks
    $connection->query("SET FOREIGN_KEY_CHECKS = 1");

    if (empty($errors)) {
        echo json_encode(['status' => 'success', 'message' => "$successCount tablo başarıyla temizlendi."]);
    } else {
        echo json_encode(['status' => 'warning', 'message' => "$successCount tablo temizlendi, ancak bazı hatalar oluştu.", 'errors' => $errors]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'get_table_data') {
    if (!isset($_GET['table'])) {
        echo json_encode(['status' => 'error', 'message' => 'Tablo adı belirtilmedi.']);
        exit;
    }

    $table = $connection->real_escape_string($_GET['table']);

    // Fetch columns
    $columns = [];
    $colResult = $connection->query("SHOW COLUMNS FROM `$table`");
    if ($colResult) {
        while ($row = $colResult->fetch_assoc()) {
            // For personeller table, exclude sistem_sifresi column for security
            if ($table === 'personeller' && $row['Field'] === 'sistem_sifresi') {
                continue;
            }
            $columns[] = $row['Field'];
        }
    }

    // Fetch data (limit 1000)
    $data = [];
    if ($table === 'personeller') {
        // For personeller table, exclude admin users and sistem_sifresi column
        $result = $connection->query("SELECT personel_id, ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, telefon_2, adres, notlar, bordrolu_calisan_mi, aylik_brut_ucret FROM `$table` WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com') LIMIT 1000");
    } else {
        $result = $connection->query("SELECT * FROM `$table` LIMIT 1000");
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'columns' => $columns, 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Veriler alınamadı: ' . $connection->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
?>