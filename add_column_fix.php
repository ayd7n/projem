<?php
include 'config.php';

$sql = "ALTER TABLE siparis_kalemleri ADD COLUMN para_birimi VARCHAR(10) DEFAULT 'TRY' AFTER toplam_tutar";

if ($connection->query($sql) === TRUE) {
    echo "Column added successfully";
} else {
    echo "Error adding column: " . $connection->error;
}
?>