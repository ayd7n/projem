<?php
include 'config.php';

echo "--- Siparisler Dump ---\n";
$query = "SELECT siparis_id, durum, odeme_durumu, odenen_tutar, musteri_adi FROM siparisler ORDER BY siparis_id DESC LIMIT 10";
$result = $connection->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);

        // Check items total for this order
        $s_id = $row['siparis_id'];
        $q2 = "SELECT SUM(fiyat * adet) as total FROM siparis_kalemleri WHERE siparis_id = $s_id";
        $r2 = $connection->query($q2);
        $row2 = $r2->fetch_assoc();
        echo "Calculated Total: " . $row2['total'] . "\n";
        echo "----------------\n";
    }
} else {
    echo "Error: " . $connection->error;
}
?>