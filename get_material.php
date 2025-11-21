<?php
include 'config.php';
$result = $connection->query("SELECT malzeme_kodu, alis_fiyati FROM malzemeler LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "Code: " . $row['malzeme_kodu'] . ", Cost: " . $row['alis_fiyati'] . "\n";
}
?>
