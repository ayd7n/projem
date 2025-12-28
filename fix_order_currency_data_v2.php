<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

echo "Starting Currency Fix...<br>";

// 1. Fix siparis_kalemleri from urunler (Assume current product currency applies if item currency is TRY/NULL)
// Use 'TRY' literal since that was the default I added.
$sql_items = "UPDATE siparis_kalemleri sk 
              JOIN urunler u ON sk.urun_kodu = u.urun_kodu 
              SET sk.para_birimi = u.satis_fiyati_para_birimi 
              WHERE (sk.para_birimi = 'TRY' OR sk.para_birimi IS NULL OR sk.para_birimi = '')
              AND u.satis_fiyati_para_birimi IS NOT NULL 
              AND u.satis_fiyati_para_birimi != 'TRY'";

if ($connection->query($sql_items) === TRUE) {
    echo "1. Updated siparis_kalemleri from urunler. Affected rows: " . $connection->affected_rows . "<br>";
} else {
    echo "Error updating items: " . $connection->error . "<br>";
}

// 2. Fix siparisler from siparis_kalemleri
$sql_orders = "UPDATE siparisler s 
               SET s.para_birimi = (
                   SELECT sk.para_birimi 
                   FROM siparis_kalemleri sk 
                   WHERE sk.siparis_id = s.siparis_id 
                   AND sk.para_birimi IS NOT NULL 
                   ORDER BY sk.siparis_kalem_id ASC 
                   LIMIT 1
               )
               WHERE s.para_birimi IS NULL OR s.para_birimi = '' OR s.para_birimi = 'TL'";

if ($connection->query($sql_orders) === TRUE) {
    echo "2. Updated siparisler from items. Affected rows: " . $connection->affected_rows . "<br>";
} else {
    echo "Error updating orders: " . $connection->error . "<br>";
}

echo "Done.";
?>