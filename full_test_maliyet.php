<?php
include 'config.php';

echo "Tam Test: Mal Kabul ve Maliyet Hesaplama\n";
echo "=======================================\n";

// 1. Test malzemesi için mal kabul işlemi yap
$malzeme_kodu = 37; // Ambalaj Malzemesi 2
$miktar = 10;
$tedarikci_id = 3;

echo "Test verileri:\n";
echo "- Malzeme Kodu: $malzeme_kodu\n";
echo "- Miktar: $miktar\n";
echo "- Tedarikçi ID: $tedarikci_id\n";

// 2. Geçerli bir sözleşme var mı kontrol et (yeni oluşturduğumuz 18 numaralı sözleşme)
$sozlesme_id = 18;

// 3. Malzeme bilgilerini al
$malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = $malzeme_kodu";
$malzeme_result = $connection->query($malzeme_query);
$malzeme = $malzeme_result->fetch_assoc();

// 4. Tedarikçi bilgilerini al
$tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id";
$tedarikci_result = $connection->query($tedarikci_query);
$tedarikci = $tedarikci_result->fetch_assoc();

// 5. Yeni stok hareketi oluştur (mal kabul)
$stok_turu = 'malzeme';
$yon = 'giris';
$hareket_turu = 'mal_kabul';
$depo = 'Test Depo';
$raf = 'T1';
$aciklama = 'Test Mal Kabul İşlemi';
$tarih = date('Y-m-d H:i:s');

$hareket_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, aciklama, tarih, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi, tedarikci_id) 
                  VALUES ('$stok_turu', '$malzeme_kodu', '{$malzeme['malzeme_ismi']}', '{$malzeme['birim']}', $miktar, '$yon', '$hareket_turu', '$depo', '$raf', '$aciklama', '$tarih', 1, 'Test Kullanıcı', '{$tedarikci['tedarikci_adi']}', $tedarikci_id)";

if($connection->query($hareket_query)) {
    $yeni_hareket_id = $connection->insert_id;
    echo "Stok hareketi oluşturuldu. ID: $yeni_hareket_id\n";
    
    // 6. Sözleşme detaylarını al
    $contract_details_query = "SELECT birim_fiyat, para_birimi, tedarikci_adi, tedarikci_id, baslangic_tarihi, bitis_tarihi FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
    $contract_details_result = $connection->query($contract_details_query);
    $contract_details = $contract_details_result->fetch_assoc();
    
    // 7. Stok hareketi ile sözleşme ilişkisini oluştur
    $contract_link_query = "INSERT INTO stok_hareketleri_sozlesmeler (hareket_id, sozlesme_id, kullanilan_miktar, birim_fiyat, para_birimi, tedarikci_adi, tedarikci_id, baslangic_tarihi, bitis_tarihi) 
                            VALUES ($yeni_hareket_id, $sozlesme_id, $miktar, {$contract_details['birim_fiyat']}, '{$contract_details['para_birimi']}', '{$contract_details['tedarikci_adi']}', {$contract_details['tedarikci_id']}, '{$contract_details['baslangic_tarihi']}', '{$contract_details['bitis_tarihi']}')";
    
    if($connection->query($contract_link_query)) {
        echo "Stok hareketi ile sözleşme ilişkisi oluşturuldu.\n";
        
        // 8. Malzeme stok miktarını güncelle
        $stok_guncelle_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + $miktar WHERE malzeme_kodu = $malzeme_kodu";
        
        if($connection->query($stok_guncelle_query)) {
            echo "Malzeme stok miktarı güncellendi.\n";
            
            // 9. Yeni oluşturulan kayıtların doğruluğunu kontrol et
            echo "\nYeni kaydın doğrulama kontrolü:\n";
            
            $kontrol_query = "SELECT shk.hareket_id, shk.kod, shk.isim, shk.miktar, shk.tarih, 
                                     shs.sozlesme_id, shs.birim_fiyat, shs.para_birimi, shs.tedarikci_adi
                              FROM stok_hareket_kayitlari shk
                              JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                              WHERE shk.hareket_id = $yeni_hareket_id
                              AND shk.hareket_turu = 'mal_kabul'";
            
            $kontrol_result = $connection->query($kontrol_query);
            
            if($kontrol_row = $kontrol_result->fetch_assoc()) {
                echo "Kayıt doğrulandı:\n";
                echo "- Hareket ID: {$kontrol_row['hareket_id']}\n";
                echo "- Malzeme: {$kontrol_row['isim']} (Kod: {$kontrol_row['kod']})\n";
                echo "- Miktar: {$kontrol_row['miktar']}\n";
                echo "- Sözleşme ID: {$kontrol_row['sozlesme_id']}\n";
                echo "- Birim Fiyat: {$kontrol_row['birim_fiyat']} {$kontrol_row['para_birimi']}\n";
                echo "- Tedarikçi: {$kontrol_row['tedarikci_adi']}\n";
                
                // 10. Artık maliyet hesaplaması yapabiliriz
                echo "\nMaliyet hesaplaması testi:\n";
                
                $fiyat_query = "SELECT shs.birim_fiyat, shs.para_birimi, shs.tedarikci_adi
                                FROM stok_hareket_kayitlari shk
                                JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                                WHERE shk.kod = '$malzeme_kodu'
                                AND shk.hareket_turu = 'mal_kabul'
                                ORDER BY shk.tarih DESC
                                LIMIT 1";
                
                $fiyat_result = $connection->query($fiyat_query);
                
                if($fiyat_row = $fiyat_result->fetch_assoc()) {
                    echo "Birim Fiyat: {$fiyat_row['birim_fiyat']} {$fiyat_row['para_birimi']} (Tedarikçi: {$fiyat_row['tedarikci_adi']})\n";
                    echo "Maliyet hesaplaması başarıyla yapıldı!\n";
                } else {
                    echo "Fiyat bilgisi hala bulunamadı.\n";
                }
            } else {
                echo "Kayıt doğrulaması başarısız.\n";
            }
        } else {
            echo "HATA: Malzeme stok miktarı güncellenemedi: " . $connection->error . "\n";
        }
    } else {
        echo "HATA: Stok hareketi ile sözleşme ilişkisi oluşturulamadı: " . $connection->error . "\n";
    }
} else {
    echo "HATA: Stok hareketi oluşturulamadı: " . $connection->error . "\n";
}

echo "\nTam test tamamlandı.\n";
?>