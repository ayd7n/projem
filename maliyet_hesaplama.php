<?php
include 'config.php';

function urun_maliyet_hesapla($urun_kodu) {
    global $connection;
    
    echo "Ürün maliyeti hesaplanıyor: $urun_kodu\n";
    echo "=====================================\n";
    
    // Ürünün BOM (Bill of Materials) bilgilerini al
    $bom_query = "SELECT urun_ismi, bilesen_kodu, bilesen_ismi, bilesen_miktari, bilesenin_malzeme_turu
                  FROM urun_agaci
                  WHERE urun_kodu = ?";
    $bom_stmt = $connection->prepare($bom_query);
    $bom_stmt->bind_param('s', $urun_kodu);
    $bom_stmt->execute();
    $bom_result = $bom_stmt->get_result();
    
    $toplam_maliyet = 0;
    $bileşenler = [];
    
    while($bom_row = $bom_result->fetch_assoc()) {
        $bilesen_kodu = $bom_row['bilesen_kodu'];
        $bilesen_miktari = $bom_row['bilesen_miktari'];
        $bilesen_turu = $bom_row['bilesenin_malzeme_turu'];
        $bilesen_ismi = $bom_row['bilesen_ismi'];
        
        echo "\nBileşen: $bilesen_ismi (Kod: $bilesen_kodu)\n";
        echo "Miktar: $bilesen_miktari\n";
        
        // En son mal kabul edilen birim fiyatını al
        // Bu sorgu hem malzeme hem essans türleri için çalışmalıdır
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
        
        if($fiyat_row = $fiyat_result->fetch_assoc()) {
            $birim_fiyat = $fiyat_row['birim_fiyat'];
            $para_birimi = $fiyat_row['para_birimi'];
            $tedarikci_adi = $fiyat_row['tedarikci_adi'];
            
            echo "Birim Fiyat: $birim_fiyat $para_birimi (Tedarikçi: $tedarikci_adi)\n";
            
            $bilesen_maliyeti = $bilesen_miktari * $birim_fiyat;
            echo "Bileşen Maliyeti: $bilesen_miktari x $birim_fiyat = $bilesen_maliyeti $para_birimi\n";
            
            $bileşenler[] = [
                'bilesen_kodu' => $bilesen_kodu,
                'bilesen_ismi' => $bilesen_ismi,
                'miktar' => $bilesen_miktari,
                'birim_fiyat' => $birim_fiyat,
                'para_birimi' => $para_birimi,
                'tedarikci_adi' => $tedarikci_adi,
                'maliyet' => $bilesen_maliyeti
            ];
            
            $toplam_maliyet += $bilesen_maliyeti;
        } else {
            echo "UYARI: Bu bileşen için fiyat bilgisi bulunamadı!\n";
            $bileşenler[] = [
                'bilesen_kodu' => $bilesen_kodu,
                'bilesen_ismi' => $bilesen_ismi,
                'miktar' => $bilesen_miktari,
                'birim_fiyat' => 0,
                'para_birimi' => 'Bilinmiyor',
                'tedarikci_adi' => 'Bilinmiyor',
                'maliyet' => 0
            ];
        }
        
        $fiyat_stmt->close();
    }
    
    echo "\nToplam Ürün Maliyeti: $toplam_maliyet TL\n";
    
    // Ürün tablosunu güncelle
    $urun_guncelle_query = "UPDATE urunler SET son_maliyet = ? WHERE urun_kodu = ?";
    $urun_guncelle_stmt = $connection->prepare($urun_guncelle_query);
    $urun_guncelle_stmt->bind_param('ds', $toplam_maliyet, $urun_kodu);
    
    if($urun_guncelle_stmt->execute()) {
        echo "Ürün maliyeti veritabanına başarıyla kaydedildi.\n";
    } else {
        echo "HATA: Ürün maliyeti veritabanına kaydedilemedi: " . $connection->error . "\n";
    }
    
    $urun_guncelle_stmt->close();
    $bom_stmt->close();
    
    return [
        'urun_kodu' => $urun_kodu,
        'toplam_maliyet' => $toplam_maliyet,
        'bileşenler' => $bileşenler
    ];
}

// Test için mevcut ürünleri al
echo "Mevcut Ürünler:\n";
$urun_query = "SELECT urun_kodu, urun_ismi FROM urunler LIMIT 10";
$urun_result = $connection->query($urun_query);
$urunler = [];
while($row = $urun_result->fetch_assoc()) {
    $urunler[] = $row;
    echo "- {$row['urun_kodu']}: {$row['urun_ismi']}\n";
}

if(count($urunler) > 0) {
    // İlk ürüne maliyet hesapla
    $test_urun = $urunler[0];
    echo "\nTest edilen ürün: {$test_urun['urun_kodu']} - {$test_urun['urun_ismi']}\n";
    
    $maliyet_sonuc = urun_maliyet_hesapla($test_urun['urun_kodu']);
    
    echo "\nDetaylı Sonuç:\n";
    echo "Ürün Kodu: {$maliyet_sonuc['urun_kodu']}\n";
    echo "Toplam Maliyet: {$maliyet_sonuc['toplam_maliyet']} TL\n";
    echo "Bileşenler:\n";
    foreach($maliyet_sonuc['bileşenler'] as $bilesen) {
        echo "  - {$bilesen['bilesen_ismi']} ({$bilesen['miktar']} x {$bilesen['birim_fiyat']} {$bilesen['para_birimi']} = {$bilesen['maliyet']} {$bilesen['para_birimi']}) [Tedarikçi: {$bilesen['tedarikci_adi']}]\n";
    }
} else {
    echo "Veritabanında ürün bulunamadı.\n";
}

?>