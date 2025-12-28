<?php
include 'config.php';

echo "Updating order currencies...<br>";

// Query to update siparisler based on first siparis_kalemleri item
$sql = "UPDATE siparisler s 
        SET s.para_birimi = (
            SELECT sk.para_birimi 
            FROM siparis_kalemleri sk 
            WHERE sk.siparis_id = s.siparis_id 
            ORDER BY sk.siparis_kalem_id ASC 
            LIMIT 1
        )
        WHERE s.para_birimi IS NULL OR s.para_birimi = '' OR s.para_birimi = 'TL'";

// Since 'TL' is also a valid currency, we might overwrite valid 'TL' with 'USD' if items are USD, which is what we want. 
// But if items are 'TL', it will just set it to 'TL' again, which is harmless.

if ($connection->query($sql) === TRUE) {
    echo "Orders updated successfully based on items.<br>";
    echo "Affected rows: " . $connection->affected_rows;
} else {
    echo "Error updating orders: " . $connection->error;
}
?>