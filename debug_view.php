<?php
include 'config.php';

echo "--- View Tanımını Kontrol Et ---\n";
$result = $connection->query("SHOW CREATE VIEW v_esans_maliyetleri");
if ($result) {
    $row = $result->fetch_assoc();
    echo "View Create Statement:\n" . $row['Create View'] . "\n";
} else {
    echo "View bulunamadı veya okunamadı: " . $connection->error . "\n";
}

echo "\n--- View İçeriğini Kontrol Et (LIMIT 5) ---\n";
$result = $connection->query("SELECT * FROM v_esans_maliyetleri LIMIT 5");
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "View boş sonuç döndürdü.\n";
    }
} else {
    echo "Sorgu hatası: " . $connection->error . "\n";
}
?>
