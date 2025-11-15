<?php
include '../config.php'; // Provides $connection (mysqli object)

// This will throw a fatal error if the file doesn't exist, which is what we want.
require_once '../libs/SimpleXLSXGen.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    }
    exit;
}

function get_all_tables_and_views($conn) {
    $result = $conn->query("SHOW FULL TABLES");
    if (!$result) {
        throw new Exception("Veritabanı sorgulanamadı: " . $conn->error);
    }
    $tables = [];
    while ($row = $result->fetch_assoc()) {
        $tableName = reset($row); // Gets the first value from the associative array
        $tables[] = [
            'name' => $tableName,
            'type' => $row['Table_type'] == 'VIEW' ? 'View' : 'Table'
        ];
    }
    return $tables;
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'list_tables') {
        header('Content-Type: application/json');
        try {
            $tables = get_all_tables_and_views($connection);
            echo json_encode(['status' => 'success', 'tables' => $tables]);
        } catch (Exception $e) {
            // The 'require_once' might fail before this, but this is good practice.
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı tabloları listelenemedi: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action == 'export_table' && isset($_GET['name'])) {
        $table_name = $_GET['name'];

        try {
            // Security Check: Validate that the requested table/view actually exists
            $allowed_tables = array_column(get_all_tables_and_views($connection), 'name');
            if (!in_array($table_name, $allowed_tables)) {
                die('Geçersiz tablo veya görünüm adı.');
            }

            $result = $connection->query("SELECT * FROM `{$table_name}`");
            if (!$result) {
                throw new Exception("Veri alınamadı: " . $connection->error);
            }

            $data = $result->fetch_all(MYSQLI_ASSOC);

            if (empty($data)) {
                echo "<script>alert('Bu tabloda/görünümde dışa aktarılacak veri yok.'); window.history.back();</script>";
                exit;
            }

            $filename = "export_{$table_name}_" . date('Y-m-d') . ".xlsx";
            
            // Prepare data for the library: header row + data rows
            $header = array_keys($data[0]);
            $xlsx_data = array_merge([$header], $data);

            // Use the SimpleXLSXGen library
            \Shuchkin\SimpleXLSXGen::fromArray($xlsx_data)
                ->setAuthor('Parfum ERP')
                ->setTitle($table_name)
                ->downloadAs($filename);

        } catch (Exception $e) {
            die('Dışa aktarma sırasında hata: ' . $e->getMessage());
        }
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
?>