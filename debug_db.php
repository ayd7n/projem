<?php
include 'config.php';

echo "--- Esans Kontrolü ---\n";
$result = $connection->query("SELECT * FROM esanslar WHERE esans_ismi LIKE '%Bergamot%' OR esans_kodu = 'ES010'");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Esans Bulundu: ID: " . $row['esans_id'] . ", Kod: '" . $row['esans_kodu'] . "', İsim: " . $row['esans_ismi'] . "\n";
        
        echo "--- Ürün Ağacı Kontrolü (Kod ile) ---\n";
        $agac_result = $connection->query("SELECT * FROM urun_agaci WHERE urun_kodu = '" . $connection->real_escape_string($row['esans_kodu']) . "'");
        if ($agac_result->num_rows > 0) {
            while($agac = $agac_result->fetch_assoc()) {
                echo "  - Bileşen: " . $agac['bilesen_kodu'] . " (" . $agac['bilesen_ismi'] . "), Miktar: " . $agac['bilesen_miktari'] . ", Tür: " . $agac['agac_turu'] . "\n";
                
                // Malzeme fiyat kontrolü
                $malzeme_result = $connection->query("SELECT * FROM malzemeler WHERE malzeme_kodu = '" . $connection->real_escape_string($agac['bilesen_kodu']) . "'");
                if ($malzeme_result->num_rows > 0) {
                    $malzeme = $malzeme_result->fetch_assoc();
                    echo "    -> Malzeme Fiyatı: " . $malzeme['alis_fiyati'] . " " . $malzeme['para_birimi'] . "\n";
                } else {
                    echo "    -> Malzeme Bulunamadı!\n";
                }
            }
        } else {
            echo "  Bu esans için ürün ağacı kaydı bulunamadı.\n";
        }
    }
} else {
    echo "Esans bulunamadı.\n";
}

echo "\n--- Tüm Ürün Ağacı (İlk 10) ---\n";
$all_agac = $connection->query("SELECT * FROM urun_agaci LIMIT 10");
while($row = $all_agac->fetch_assoc()) {
    echo "Kod: " . $row['urun_kodu'] . ", Bileşen: " . $row['bilesen_kodu'] . "\n";
}
?>
