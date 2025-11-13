<?php
// Mal kabul testi için örnek veri
include 'config.php';

echo "Yeni alanlar ile sözleşme kontrol testi\n";
echo "========================================\n";

// Malzeme ve tedarikçi ID'lerini al
$test_query = "SELECT c.sozlesme_id, c.malzeme_kodu, c.tedarikci_id, c.birim_fiyat, c.para_birimi
               FROM cerceve_sozlesmeler c
               WHERE c.sozlesme_id = 17";

$result = $connection->query($test_query);
if($row = $result->fetch_assoc()) {
    echo "Test verisi bulundu:\n";
    echo "- Sözleşme ID: {$row['sozlesme_id']}\n";
    echo "- Malzeme Kodu: {$row['malzeme_kodu']}\n";
    echo "- Tedarikçi ID: {$row['tedarikci_id']}\n";
    echo "- Birim Fiyat: {$row['birim_fiyat']} {$row['para_birimi']}\n";
    
    // Artık check_framework_contract işlemi için gerekli parametreler var
    echo "\ncheck_framework_contract işlemi test ediliyor...\n";
    
    // Test için gerekli POST verilerini simüle edelim
    $_POST['material_kodu'] = $row['malzeme_kodu'];
    $_POST['tedarikci_id'] = $row['tedarikci_id'];
    
    // check_framework_contract sorgusunu manuel olarak çalıştır
    $contract_check_query = "SELECT c.sozlesme_id, c.limit_miktar, c.birim_fiyat, c.para_birimi,
                            COALESCE(kullanilan.toplam_mal_kabul, 0) as toplam_mal_kabul_miktari,
                            (c.limit_miktar - COALESCE(kullanilan.toplam_mal_kabul, 0)) as kalan_miktar
                            FROM cerceve_sozlesmeler c
                            LEFT JOIN (
                                SELECT
                                    tedarikci_id,
                                    kod as malzeme_kodu,
                                    SUM(miktar) as toplam_mal_kabul
                                FROM stok_hareket_kayitlari
                                WHERE hareket_turu = 'mal_kabul'
                                GROUP BY tedarikci_id, kod
                            ) kullanilan ON c.tedarikci_id = kullanilan.tedarikci_id
                            AND c.malzeme_kodu = kullanilan.malzeme_kodu
                            WHERE c.tedarikci_id = ?
                            AND c.malzeme_kodu = ?
                            AND (c.bitis_tarihi >= CURDATE() OR c.bitis_tarihi IS NULL)
                            AND COALESCE(kullanilan.toplam_mal_kabul, 0) < c.limit_miktar
                            ORDER BY c.oncelik ASC, kalan_miktar DESC";
    
    $contract_check_stmt = $connection->prepare($contract_check_query);
    $contract_check_stmt->bind_param('is', $row['tedarikci_id'], $row['malzeme_kodu']);
    $contract_check_stmt->execute();
    $contract_result = $contract_check_stmt->get_result();
    
    echo "Sorgu sonuçları:\n";
    while($contract_row = $contract_result->fetch_assoc()) {
        echo "- Sözleşme: ID={$contract_row['sozlesme_id']}, Fiyat={$contract_row['birim_fiyat']} {$contract_row['para_birimi']}, Kalan miktar={$contract_row['kalan_miktar']}\n";
    }
    
    $contract_check_stmt->close();
} else {
    echo "Test verisi bulunamadı\n";
}

echo "\nTest tamamlandı.\n";
?>