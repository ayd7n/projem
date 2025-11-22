<?php
include 'config.php';

function getTableColumns($tableName) {
    global $connection;
    $result = $connection->query("SHOW COLUMNS FROM $tableName");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

echo "Columns in montaj_is_emirleri:\n";
print_r(getTableColumns('montaj_is_emirleri'));

echo "\nColumns in montaj_is_emri_malzeme_listesi:\n";
print_r(getTableColumns('montaj_is_emri_malzeme_listesi'));

echo "\nColumns in montaj_is_emirleri_eksik_miktar_kayitlari:\n";
print_r(getTableColumns('montaj_is_emirleri_eksik_miktar_kayitlari'));
?>
