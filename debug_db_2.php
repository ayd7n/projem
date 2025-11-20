<?php
include 'config.php';

echo "--- Esans Detay ---\n";
$result = $connection->query("SELECT * FROM esanslar WHERE esans_kodu = 'ES010'");
if ($row = $result->fetch_assoc()) {
    echo "Esans: '" . $row['esans_kodu'] . "' (Length: " . strlen($row['esans_kodu']) . ")\n";
}

echo "\n--- Ürün Ağacı Arama (LIKE %ES010%) ---\n";
$result = $connection->query("SELECT * FROM urun_agaci WHERE urun_kodu LIKE '%ES010%'");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Bulundu: ID: " . $row['urun_agaci_id'] . "\n";
        echo "  Urun Kodu: '" . $row['urun_kodu'] . "' (Length: " . strlen($row['urun_kodu']) . ")\n";
        echo "  Bilesen: '" . $row['bilesen_kodu'] . "'\n";
        echo "  Agac Turu: '" . $row['agac_turu'] . "'\n";
    }
} else {
    echo "Ürün ağacında ES010 ile eşleşen kayıt yok.\n";
}
?>
