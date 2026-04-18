<?php
include 'config.php';

function urun_maliyet_hesapla($urun_kodu) {
    global $connection;

    echo "Ürün maliyeti hesaplanıyor: $urun_kodu\n";
    echo "=====================================\n";

    // Guncel doviz kurlari (TRY bazli maliyet icin)
    $dolar_kuru = 0.0;
    $euro_kuru = 0.0;
    $rates_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
    $rates_result = $connection->query($rates_query);
    if ($rates_result) {
        while ($rate_row = $rates_result->fetch_assoc()) {
            if ($rate_row['ayar_anahtar'] === 'dolar_kuru') {
                $dolar_kuru = max(0.0, (float) $rate_row['ayar_deger']);
            } elseif ($rate_row['ayar_anahtar'] === 'euro_kuru') {
                $euro_kuru = max(0.0, (float) $rate_row['ayar_deger']);
            }
        }
    }

    // Urunun BOM (Bill of Materials) bilgilerini al
    $bom_query = "SELECT urun_ismi, bilesen_kodu, bilesen_ismi, bilesen_miktari, bilesenin_malzeme_turu
                  FROM urun_agaci
                  WHERE urun_kodu = ?";
    $bom_stmt = $connection->prepare($bom_query);
    $bom_stmt->bind_param('s', $urun_kodu);
    $bom_stmt->execute();
    $bom_result = $bom_stmt->get_result();

    $toplam_maliyet = 0;
    $bilesenler = [];

    while ($bom_row = $bom_result->fetch_assoc()) {
        $bilesen_kodu = $bom_row['bilesen_kodu'];
        $bilesen_miktari = (float) $bom_row['bilesen_miktari'];
        $bilesen_ismi = $bom_row['bilesen_ismi'];

        echo "\nBileşen: $bilesen_ismi (Kod: $bilesen_kodu)\n";
        echo "Miktar: $bilesen_miktari\n";

        // En son mal kabul edilen birim fiyatini al
        $fiyat_query = "SELECT shs.birim_fiyat, shs.para_birimi, shs.tedarikci_adi
                        FROM stok_hareket_kayitlari shk
                        JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                        WHERE shk.kod = ?
                        AND shk.hareket_turu = 'mal_kabul'
                        ORDER BY shk.tarih DESC
                        LIMIT 1";

        $fiyat_stmt = $connection->prepare($fiyat_query);
        $fiyat_stmt->bind_param('s', $bilesen_kodu);
        $fiyat_stmt->execute();
        $fiyat_result = $fiyat_stmt->get_result();

        if ($fiyat_row = $fiyat_result->fetch_assoc()) {
            $birim_fiyat = (float) $fiyat_row['birim_fiyat'];
            $para_birimi = strtoupper(trim((string) $fiyat_row['para_birimi']));
            $tedarikci_adi = $fiyat_row['tedarikci_adi'];

            echo "Birim Fiyat: $birim_fiyat $para_birimi (Tedarikçi: $tedarikci_adi)\n";

            $bilesen_maliyeti_orijinal = $bilesen_miktari * $birim_fiyat;
            $bilesen_maliyeti_try = $bilesen_maliyeti_orijinal;

            if ($para_birimi === 'USD' || $para_birimi === '$') {
                if ($dolar_kuru <= 0) {
                    echo "UYARI: USD kuru tanimli degil veya 0. Bu bilesenin TRY maliyeti 0 kabul edildi.\n";
                    $bilesen_maliyeti_try = 0;
                } else {
                    $bilesen_maliyeti_try = $bilesen_maliyeti_orijinal * $dolar_kuru;
                }
            } elseif ($para_birimi === 'EUR' || $para_birimi === '€') {
                if ($euro_kuru <= 0) {
                    echo "UYARI: EUR kuru tanimli degil veya 0. Bu bilesenin TRY maliyeti 0 kabul edildi.\n";
                    $bilesen_maliyeti_try = 0;
                } else {
                    $bilesen_maliyeti_try = $bilesen_maliyeti_orijinal * $euro_kuru;
                }
            }

            echo "Bileşen Maliyeti: $bilesen_miktari x $birim_fiyat = $bilesen_maliyeti_orijinal $para_birimi (" . number_format($bilesen_maliyeti_try, 4, '.', '') . " TL)\n";

            $bilesenler[] = [
                'bilesen_kodu' => $bilesen_kodu,
                'bilesen_ismi' => $bilesen_ismi,
                'miktar' => $bilesen_miktari,
                'birim_fiyat' => $birim_fiyat,
                'para_birimi' => $para_birimi,
                'tedarikci_adi' => $tedarikci_adi,
                'maliyet_orijinal' => $bilesen_maliyeti_orijinal,
                'maliyet_try' => $bilesen_maliyeti_try
            ];

            $toplam_maliyet += $bilesen_maliyeti_try;
        } else {
            echo "UYARI: Bu bileşen için fiyat bilgisi bulunamadı!\n";
            $bilesenler[] = [
                'bilesen_kodu' => $bilesen_kodu,
                'bilesen_ismi' => $bilesen_ismi,
                'miktar' => $bilesen_miktari,
                'birim_fiyat' => 0,
                'para_birimi' => 'Bilinmiyor',
                'tedarikci_adi' => 'Bilinmiyor',
                'maliyet_orijinal' => 0,
                'maliyet_try' => 0
            ];
        }

        $fiyat_stmt->close();
    }

    echo "\nToplam Ürün Maliyeti: $toplam_maliyet TL\n";

    // Urun tablosunu guncelle
    $urun_guncelle_query = "UPDATE urunler SET son_maliyet = ? WHERE urun_kodu = ?";
    $urun_guncelle_stmt = $connection->prepare($urun_guncelle_query);
    $urun_guncelle_stmt->bind_param('ds', $toplam_maliyet, $urun_kodu);

    if ($urun_guncelle_stmt->execute()) {
        echo "Ürün maliyeti veritabanına başarıyla kaydedildi.\n";
    } else {
        echo "HATA: Ürün maliyeti veritabanına kaydedilemedi: " . $connection->error . "\n";
    }

    $urun_guncelle_stmt->close();
    $bom_stmt->close();

    return [
        'urun_kodu' => $urun_kodu,
        'toplam_maliyet' => $toplam_maliyet,
        'bilesenler' => $bilesenler
    ];
}

// Test için mevcut ürünleri al
echo "Mevcut Ürünler:\n";
$urun_query = "SELECT urun_kodu, urun_ismi FROM urunler LIMIT 10";
$urun_result = $connection->query($urun_query);
$urunler = [];
while ($row = $urun_result->fetch_assoc()) {
    $urunler[] = $row;
    echo "- {$row['urun_kodu']}: {$row['urun_ismi']}\n";
}

if (count($urunler) > 0) {
    // Ilk urune maliyet hesapla
    $test_urun = $urunler[0];
    echo "\nTest edilen ürün: {$test_urun['urun_kodu']} - {$test_urun['urun_ismi']}\n";

    $maliyet_sonuc = urun_maliyet_hesapla($test_urun['urun_kodu']);

    echo "\nDetaylı Sonuç:\n";
    echo "Ürün Kodu: {$maliyet_sonuc['urun_kodu']}\n";
    echo "Toplam Maliyet: {$maliyet_sonuc['toplam_maliyet']} TL\n";
    echo "Bileşenler:\n";
    foreach ($maliyet_sonuc['bilesenler'] as $bilesen) {
        echo "  - {$bilesen['bilesen_ismi']} ({$bilesen['miktar']} x {$bilesen['birim_fiyat']} {$bilesen['para_birimi']} = {$bilesen['maliyet_orijinal']} {$bilesen['para_birimi']}) [TRY: {$bilesen['maliyet_try']}] [Tedarikçi: {$bilesen['tedarikci_adi']}]\n";
    }
} else {
    echo "Veritabanında ürün bulunamadı.\n";
}

?>


