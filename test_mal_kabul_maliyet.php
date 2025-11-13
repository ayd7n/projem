<?php
include 'config.php';

echo "Test: Manuel Mal Kabul Kaydı ve Maliyet Hesaplama\n";
echo "===============================================\n";

// 1. Örnek bir malzeme ve tedarikçi seçelim
$malzeme_kodu = 37; // Ambalaj Malzemesi 2
$tedarikci_id = 3; // Tedarikçi Abdi
$miktar = 5;

echo "Test verileri:\n";
echo "- Malzeme Kodu: $malzeme_kodu\n";
echo "- Tedarikçi ID: $tedarikci_id\n";
echo "- Miktar: $miktar\n";

// 2. Geçerli sözleşme var mı kontrol et
$contract_check_query = "SELECT c.sozlesme_id, c.birim_fiyat, c.para_birimi
                        FROM cerceve_sozlesmeler c
                        WHERE c.tedarikci_id = ?
                        AND c.malzeme_kodu = ?
                        AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                        ORDER BY c.oncelik ASC
                        LIMIT 1";

$contract_check_stmt = $connection->prepare($contract_check_query);
$contract_check_stmt->bind_param('ii', $tedarikci_id, $malzeme_kodu);
$contract_check_stmt->execute();
$contract_result = $contract_check_stmt->get_result();

if($contract_row = $contract_result->fetch_assoc()) {
    echo "\nGeçerli sözleşme bulundu:\n";
    echo "- Sözleşme ID: {$contract_row['sozlesme_id']}\n";
    echo "- Birim Fiyat: {$contract_row['birim_fiyat']} {$contract_row['para_birimi']}\n";
    
    // 3. Stok hareketi oluştur (Manuel olarak)
    echo "\nStok hareketi oluşturuluyor...\n";
    
    // Malzeme bilgilerini al
    $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
    $malzeme_stmt = $connection->prepare($malzeme_query);
    $malzeme_stmt->bind_param('i', $malzeme_kodu);
    $malzeme_stmt->execute();
    $malzeme_result = $malzeme_stmt->get_result();
    $malzeme = $malzeme_result->fetch_assoc();
    
    // Tedarikçi bilgilerini al
    $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
    $tedarikci_stmt = $connection->prepare($tedarikci_query);
    $tedarikci_stmt->bind_param('i', $tedarikci_id);
    $tedarikci_stmt->execute();
    $tedarikci_result = $tedarikci_stmt->get_result();
    $tedarikci = $tedarikci_result->fetch_assoc();
    
    // Yeni stok hareketi ekle
    $hareket_turu = 'mal_kabul';
    $yon = 'giris';
    $depo = 'Ana Depo';
    $raf = 'A1';
    $aciklama = 'Test Mal Kabul';
    
    $insert_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, depo, raf, aciklama, kaydeden_personel_id, kaydeden_personel_adi, tedarikci_ismi, tedarikci_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $connection->prepare($insert_query);
    $insert_stmt->bind_param('ssssdsssssisss', $yon, $malzeme_kodu, $malzeme['malzeme_ismi'], $malzeme['birim'], $miktar, $yon, $hareket_turu, $depo, $raf, $aciklama, 1, 'Test Kullanıcı', $tedarikci['tedarikci_adi'], $tedarikci_id);
    
    if($insert_stmt->execute()) {
        $yeni_hareket_id = $connection->insert_id;
        echo "Stok hareketi başarıyla oluşturuldu. ID: $yeni_hareket_id\n";
        
        // 4. Stok hareketi ile sözleşme ilişkisini oluştur
        echo "Stok hareketi ile sözleşme ilişkisi kuruluyor...\n";
        
        // Sözleşme detaylarını al
        $contract_details_query = "SELECT birim_fiyat, para_birimi, tedarikci_adi, tedarikci_id, baslangic_tarihi, bitis_tarihi FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
        $contract_details_stmt = $connection->prepare($contract_details_query);
        $contract_details_stmt->bind_param('i', $contract_row['sozlesme_id']);
        $contract_details_stmt->execute();
        $contract_details_result = $contract_details_stmt->get_result();
        $contract_details = $contract_details_result->fetch_assoc();
        
        // İlişki kaydını oluştur
        $contract_link_query = "INSERT INTO stok_hareketleri_sozlesmeler (hareket_id, sozlesme_id, kullanilan_miktar, birim_fiyat, para_birimi, tedarikci_adi, tedarikci_id, baslangic_tarihi, bitis_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $contract_link_stmt = $connection->prepare($contract_link_query);
        $contract_link_stmt->bind_param('iidsssisss', $yeni_hareket_id, $contract_row['sozlesme_id'], $miktar, $contract_details['birim_fiyat'], $contract_details['para_birimi'], $contract_details['tedarikci_adi'], $contract_details['tedarikci_id'], $contract_details['baslangic_tarihi'], $contract_details['bitis_tarihi']);
        
        if($contract_link_stmt->execute()) {
            echo "Stok hareketi ile sözleşme ilişkisi başarıyla oluşturuldu.\n";
            
            // 5. Malzeme stok miktarını güncelle
            $stock_query = "UPDATE malzemeler SET stok_miktari = stok_miktari + ? WHERE malzeme_kodu = ?";
            $stock_stmt = $connection->prepare($stock_query);
            $stock_stmt->bind_param('di', $miktar, $malzeme_kodu);
            
            if($stock_stmt->execute()) {
                echo "Malzeme stok miktarı güncellendi.\n";
                
                // 6. Tekrar maliyet hesaplamayı dene
                echo "\nYeni maliyet hesaplaması yapılıyor:\n";
                
                // En son mal kabul edilen birim fiyatını al
                $fiyat_query = "SELECT shs.birim_fiyat, shs.para_birimi, shs.tedarikci_adi
                                FROM stok_hareket_kayitlari shk
                                JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                                WHERE shk.kod = ?
                                AND shk.hareket_turu = 'mal_kabul'
                                ORDER BY shk.tarih DESC
                                LIMIT 1";
                
                $fiyat_stmt = $connection->prepare($fiyat_query);
                $fiyat_stmt->bind_param('s', $malzeme_kodu);
                $fiyat_stmt->execute();
                $fiyat_result = $fiyat_stmt->get_result();
                
                if($fiyat_row = $fiyat_result->fetch_assoc()) {
                    echo "Birim Fiyat: {$fiyat_row['birim_fiyat']} {$fiyat_row['para_birimi']} (Tedarikçi: {$fiyat_row['tedarikci_adi']})\n";
                    echo "Maliyet hesaplaması başarıyla yapıldı!\n";
                } else {
                    echo "Fiyat bilgisi hala bulunamadı.\n";
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
} else {
    echo "Geçerli sözleşme bulunamadı.\n";
    
    // Yeni bir sözleşme oluştur
    echo "Yeni test sözleşmesi oluşturuluyor...\n";
    
    $insert_contract_query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, baslangic_tarihi, bitis_tarihi, olusturan, oncelik) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_contract_stmt = $connection->prepare($insert_contract_query);
    
    // Malzeme ismini al
    $malzeme_name_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
    $malzeme_name_stmt = $connection->prepare($malzeme_name_query);
    $malzeme_name_stmt->bind_param('i', $malzeme_kodu);
    $malzeme_name_stmt->execute();
    $malzeme_name_result = $malzeme_name_stmt->get_result();
    $malzeme_name = $malzeme_name_result->fetch_assoc()['malzeme_ismi'];
    
    // Tedarikçi ismini al
    $tedarikci_name_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
    $tedarikci_name_stmt = $connection->prepare($tedarikci_name_query);
    $tedarikci_name_stmt->bind_param('i', $tedarikci_id);
    $tedarikci_name_stmt->execute();
    $tedarikci_name_result = $tedarikci_name_stmt->get_result();
    $tedarikci_name = $tedarikci_name_result->fetch_assoc()['tedarikci_adi'];
    
    $baslangic = date('Y-m-d');
    $bitis = date('Y-m-d', strtotime('+1 year'));
    $limit = 100;
    $fiyat = 5.50;
    $birim = 'TL';
    $oncelik = 3;
    $olusturan = 'Test Kullanıcı';
    
    $insert_contract_stmt->bind_param('isssdsssssi', $tedarikci_id, $tedarikci_name, $malzeme_kodu, $malzeme_name, $fiyat, $birim, $limit, $baslangic, $bitis, $olusturan, $oncelik);
    
    if($insert_contract_stmt->execute()) {
        $yeni_sozlesme_id = $connection->insert_id;
        echo "Yeni test sözleşmesi oluşturuldu. ID: $yeni_sozlesme_id\n";
        echo "Yeni sözleşme ile tekrar deneyin.\n";
    } else {
        echo "HATA: Yeni sözleşme oluşturulamadı: " . $connection->error . "\n";
    }
}

echo "\nTest tamamlandı.\n";
?>