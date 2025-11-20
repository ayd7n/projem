<?php
include 'config.php';

echo "--- İsim ile Arama ---\n";
$result = $connection->query("SELECT * FROM urun_agaci WHERE urun_ismi LIKE '%Bergamot%'");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Bulundu (İsimle): Kod: '" . $row['urun_kodu'] . "', İsim: '" . $row['urun_ismi'] . "'\n";
    }
} else {
    echo "İsimle de bulunamadı.\n";
}
?>
