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
    $result = $connection->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            // Get row count
            $countResult = $connection->query("SELECT COUNT(*) as count FROM `$tableName`");
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
            $columns[] = $row['Field'];
        }
    }

    // Fetch data (limit 1000)
    $data = [];
    $result = $connection->query("SELECT * FROM `$table` LIMIT 1000");
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
