<?php
// Final test - direct SQL insert
include 'config.php';

echo "=== FINAL TEST: DIRECT SQL vs PREPARED STATEMENT ===\n\n";

$urun_kodu = 21;
$urun_stmt = $connection->prepare("SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?");
$urun_stmt->bind_param('i', $urun_kodu);
$urun_stmt->execute();
$urun_result = $urun_stmt->get_result();
$urun = $urun_result->fetch_assoc();

if ($urun) {
    $siparis_id = 999;
    $urun_ismi = $urun['urun_ismi'];
    $urun_birimi = $urun['birim'];
    $adet = 2;
    $satis_fiyati = $urun['satis_fiyati'];
    $toplam_tutar = $adet * $satis_fiyati;

    echo "Test verileri:\n";
    echo "siparis_id: $siparis_id\n";
    echo "urun_kodu: $urun_kodu\n";
    echo "urun_ismi: '$urun_ismi'\n";
    echo "adet: $adet\n";
    echo "urun_birimi: '$urun_birimi'\n";
    echo "satis_fiyati: $satis_fiyati\n";
    echo "toplam_tutar: $toplam_tutar\n\n";

    // Try direct SQL insert
    $direct_sql = "INSERT INTO siparis_kalemleri
                   (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                   VALUES ($siparis_id, $urun_kodu, '$urun_ismi', $adet, '$urun_birimi', $satis_fiyati, $toplam_tutar)";

    echo "Direct SQL: $direct_sql\n";

    if ($connection->query($direct_sql)) {
        echo "Direct SQL insert başarılı!\n";

        // Check what was inserted
        $result = $connection->query("SELECT * FROM siparis_kalemleri WHERE siparis_id = 999");
        $row = $result->fetch_assoc();
        echo "Direct SQL inserted row: " . print_r($row, true);

        // Clean up
        $connection->query("DELETE FROM siparis_kalemleri WHERE siparis_id = 999");
    } else {
        echo "Direct SQL başarısız: " . $connection->error . "\n";
    }

    echo "\n=== NOW PREPARED STATEMENT TEST ===\n";

    // Prepared statement test
    $stmt = $connection->prepare("INSERT INTO siparis_kalemleri
                                  (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiisidd', $siparis_id, $urun_kodu, $urun_ismi, $adet, $urun_birimi, $satis_fiyati, $toplam_tutar);

    if ($stmt->execute()) {
        echo "Prepared statement insert başarılı!\n";

        // Check what was inserted
        $result = $connection->query("SELECT * FROM siparis_kalemleri WHERE siparis_id = 999");
        $row = $result->fetch_assoc();
        echo "Prepared statement inserted row: " . print_r($row, true);

        // Clean up
        $connection->query("DELETE FROM siparis_kalemleri WHERE siparis_id = 999");
    } else {
        echo "Prepared statement başarısız: " . $stmt->error . "\n";
    }

} else {
    echo "Ürün bulunamadı!\n";
}

$connection->close();
?>
