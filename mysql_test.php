<?php
include 'config.php';

echo "Veritabanı Bağlantısı Testi:\n";
echo "Host: " . DB_HOST . "\n";
echo "Kullanıcı: " . DB_USER . "\n";
echo "Veritabanı: " . DB_NAME . "\n";
echo "Bağlantı Durumu: " . ($connection->connect_error ? 'BAŞARISIZ' : 'BAŞARILI') . "\n\n";

echo "Veritabanındaki Tablolar:\n";
$result = $connection->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "Hata: " . $connection->error . "\n";
}

echo "\nSistem Kullanıcıları Tablosundan Örnek Veri (varsa):\n";
$user_tables = ['sistem_kullanicilari', 'personeller', 'musteriler'];
foreach ($user_tables as $table) {
    $result = $connection->query("SELECT * FROM {$table} LIMIT 3");
    if ($result && $result->num_rows > 0) {
        echo "Tablo: {$table}\n";
        $fields = $result->fetch_fields();
        $field_names = array_map(function($f) { return $f->name; }, $fields);
        echo "Kolonlar: " . implode(', ', $field_names) . "\n";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
            echo "\n";
        }
        break;
    }
}

echo "\nDiğer Tablolardan Örnek Veriler:\n";
echo "Personeller Tablosu:\n";
$result = $connection->query("SELECT * FROM personeller LIMIT 2");
if ($result && $result->num_rows > 0) {
    $fields = $result->fetch_fields();
    $field_names = array_map(function($f) { return $f->name; }, $fields);
    echo "Kolonlar: " . implode(', ', $field_names) . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
}

echo "\nMüşteriler Tablosu:\n";
$result = $connection->query("SELECT * FROM musteriler LIMIT 2");
if ($result && $result->num_rows > 0) {
    $fields = $result->fetch_fields();
    $field_names = array_map(function($f) { return $f->name; }, $fields);
    echo "Kolonlar: " . implode(', ', $field_names) . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
}

echo "\nÜrünler Tablosu:\n";
$result = $connection->query("SELECT * FROM urunler LIMIT 2");
if ($result && $result->num_rows > 0) {
    $fields = $result->fetch_fields();
    $field_names = array_map(function($f) { return $f->name; }, $fields);
    echo "Kolonlar: " . implode(', ', $field_names) . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
}

$connection->close();
?>