<?php
include 'config.php';

echo "--- ES010 (Bergamot Essence) Maliyet Detayı ---\n\n";

// Esans bilgisi
$esans = $connection->query("SELECT * FROM esanslar WHERE esans_kodu = 'ES010' OR esans_id = 24")->fetch_assoc();
echo "Esans: " . $esans['esans_kodu'] . " - " . $esans['esans_ismi'] . "\n";
echo "Esans ID: " . $esans['esans_id'] . "\n\n";

// Ürün ağacı
echo "Bileşenler:\n";
$result = $connection->query("
    SELECT ua.*, m.alis_fiyati, m.para_birimi 
    FROM urun_agaci ua
    LEFT JOIN malzemeler m ON TRIM(ua.bilesen_kodu) = TRIM(m.malzeme_kodu)
    WHERE (ua.urun_kodu = 'ES010' OR ua.urun_kodu = '24') AND ua.agac_turu = 'esans'
");

$toplam = 0;
while($row = $result->fetch_assoc()) {
    echo "  - " . $row['bilesen_kodu'] . " (" . $row['bilesen_ismi'] . ")\n";
    echo "    Miktar: " . $row['bilesen_miktari'] . "\n";
    echo "    Birim Fiyat: " . ($row['alis_fiyati'] ?? 'NULL') . " " . ($row['para_birimi'] ?? 'N/A') . "\n";
    
    if ($row['alis_fiyati']) {
        $maliyet = $row['bilesen_miktari'] * $row['alis_fiyati'];
        echo "    Maliyet: " . $maliyet . " " . $row['para_birimi'] . "\n";
        $toplam += $maliyet;
    }
    echo "\n";
}

echo "Toplam (Para birimi karışık): " . $toplam . "\n";

// View'dan gelen sonuç
echo "\n--- View Sonucu ---\n";
$view_result = $connection->query("SELECT * FROM v_esans_maliyetleri WHERE esans_kodu = 'ES010'");
if ($view_result && $view_result->num_rows > 0) {
    $view_row = $view_result->fetch_assoc();
    echo "View Maliyeti: " . $view_row['toplam_maliyet'] . " TRY\n";
} else {
    echo "View'da sonuç yok\n";
}
?>
