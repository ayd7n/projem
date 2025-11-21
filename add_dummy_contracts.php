<?php
include 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Dummy veri ekleme işlemi başlıyor...\n";

// 1. Get Suppliers
$suppliers = [];
$result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

if (empty($suppliers)) {
    die("Hata: Tedarikçi bulunamadı.\n");
}

// 2. Get Materials
$materials = [];
$result = $connection->query("SELECT malzeme_kodu, malzeme_ismi FROM malzemeler");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
}

if (empty($materials)) {
    die("Hata: Malzeme bulunamadı.\n");
}

// 3. Add 10 Contracts
$count = 0;
for ($i = 0; $i < 10; $i++) {
    $supplier = $suppliers[array_rand($suppliers)];
    $material = $materials[array_rand($materials)];
    
    $tedarikci_id = $supplier['tedarikci_id'];
    $tedarikci_adi = $connection->real_escape_string($supplier['tedarikci_adi']);
    $malzeme_kodu = $material['malzeme_kodu'];
    $malzeme_ismi = $connection->real_escape_string($material['malzeme_ismi']);
    
    $birim_fiyat = rand(100, 1000) + (rand(0, 99) / 100);
    $para_birimleri = ['TL', 'USD', 'EUR'];
    $para_birimi = $para_birimleri[array_rand($para_birimleri)];
    
    $limit_miktar = rand(1000, 10000);
    
    // 50% chance of pre-payment
    $toplu_odenen_miktar = (rand(0, 1) == 1) ? rand(100, 500) : 0;
    
    // Ensure contract is valid (started in 2025, ends in 2026)
    $baslangic_tarihi = date('Y-m-d', strtotime("+" . rand(0, 300) . " days", strtotime("2025-01-01")));
    $bitis_tarihi = date('Y-m-d', strtotime("+" . rand(30, 365) . " days", strtotime("2026-01-01")));
    
    $oncelik = rand(1, 5);
    $aciklama = "Otomatik oluşturulan test sözleşmesi #" . ($i + 1);
    $olusturan = "Sistem"; // Or a specific user if needed
    
    $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, oncelik, aciklama, olusturan) VALUES ($tedarikci_id, '$tedarikci_adi', $malzeme_kodu, '$malzeme_ismi', $birim_fiyat, '$para_birimi', $limit_miktar, $toplu_odenen_miktar, '$baslangic_tarihi', '$bitis_tarihi', $oncelik, '$aciklama', '$olusturan')";
    
    if ($connection->query($query)) {
        echo "Sözleşme eklendi: $tedarikci_adi - $malzeme_ismi\n";
        $count++;
        
        // Expense Logic
        if ($toplu_odenen_miktar > 0) {
            $gider_tutar = $toplu_odenen_miktar * $birim_fiyat;
            $gider_tarih = date('Y-m-d');
            // Updated description to match the new system logic
            $gider_aciklama = "$tedarikci_adi - $malzeme_ismi için $toplu_odenen_miktar adet ön ödeme (Oto-Test)";
            // Use a dummy user ID or 1 if session not available in CLI
            $user_id = 1; 
            
            $gider_query = "INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, aciklama, kaydeden_personel_id) VALUES ('$gider_tarih', 'Malzeme Gideri', $gider_tutar, 'Diğer', '$gider_aciklama', $user_id)";
            
            if ($connection->query($gider_query)) {
                echo "  -> Gider eklendi: $gider_tutar $para_birimi (Not: DBde TL olarak saklanıyor olabilir)\n";
            } else {
                echo "  -> Gider eklenemedi: " . $connection->error . "\n";
            }
        }
    } else {
        echo "Hata: " . $connection->error . "\n";
    }
}

echo "İşlem tamamlandı. Toplam $count sözleşme eklendi.\n";
?>
