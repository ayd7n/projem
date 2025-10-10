<?php
include 'config.php';

echo "<h2>Test Verileri Oluşturma</h2>";

// Create sample materials
$materials = [
    ['Ham Madde A', 'ham_madde', 'hm_a.jpg', 'Temel ham madde', 100, 'adet', 5, 'Depo 1', 'Raf A1', 10],
    ['Ham Madde B', 'ham_madde', 'hm_b.jpg', 'İkinci ham madde', 200, 'adet', 3, 'Depo 1', 'Raf A2', 20],
    ['Ambalaj Malzemesi 1', 'ambalaj', 'am1.jpg', 'Şişe', 500, 'adet', 2, 'Depo 2', 'Raf B1', 50],
    ['Ambalaj Malzemesi 2', 'ambalaj', 'am2.jpg', 'Etiket', 1000, 'adet', 1, 'Depo 2', 'Raf B2', 100]
];

foreach ($materials as $mat) {
    $query = "INSERT IGNORE INTO malzemeler (malzeme_ismi, malzeme_turu, malzeme_foto_ismi, not_bilgisi, stok_miktari, birim, termin_suresi, depo, raf, kritik_stok_seviyesi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssdisii', ...$mat);
    $stmt->execute();
    $stmt->close();
}

echo "Malzemeler oluşturuldu.<br>";

// Create sample products
$products = [
    ['Parfüm A', 'urun_a.jpg', 'Lavanta aromalı parfüm', 50, 'adet', 150.00, 10, 'Depo 3', 'Raf C1'],
    ['Parfüm B', 'urun_b.jpg', 'Gül aromalı parfüm', 30, 'adet', 180.00, 5, 'Depo 3', 'Raf C2'],
    ['Parfüm C', 'urun_c.jpg', 'Vanilya aromalı parfüm', 25, 'adet', 200.00, 3, 'Depo 3', 'Raf C3']
];

foreach ($products as $prod) {
    $query = "INSERT IGNORE INTO urunler (urun_ismi, urun_foto_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, kritik_stok_seviyesi, depo, raf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssisdisi', ...$prod);
    $stmt->execute();
    $stmt->close();
}

echo "Ürünler oluşturuldu.<br>";

// Create sample orders
$orders = [
    [1, 'Müşteri A', 'Acil sipariş', 'musteri_a'],
    [2, 'Müşteri B', 'Normal sipariş', 'musteri_b']
];

foreach ($orders as $order) {
    $query = "INSERT IGNORE INTO siparisler (musteri_id, musteri_adi, aciklama, olusturan_musteri) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('isss', ...$order);
    $stmt->execute();
    $stmt->close();
}

echo "Siparişler oluşturuldu.<br>";

// Create sample suppliers
$suppliers = [
    ['Tedarikçi A', '1234567890', 'İstanbul', '02121234567', 'a@tedarikci.com', 'Ahmet Yılmaz', 'Temel tedarikçi'],
    ['Tedarikçi B', '0987654321', 'Ankara', '03121234567', 'b@tedarikci.com', 'Mehmet Kaya', 'İkinci tedarikçi']
];

foreach ($suppliers as $supp) {
    $query = "INSERT IGNORE INTO tedarikciler (tedarikci_adi, vergi_no_tc, adres, telefon, e_posta, yetkili_kisi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssss', ...$supp);
    $stmt->execute();
    $stmt->close();
}

echo "Tedarikçiler oluşturuldu.<br>";

// Create sample employees
$employees = [
    ['Ali Demir', '11111111111', '1985-05-15', '2020-01-01', 'Üretim Müdürü', 'Üretim', 'ali@firma.com', '05321111111', 'İstanbul', 'Deneyimli personel'],
    ['Ayşe Yılmaz', '22222222222', '1990-08-20', '2021-03-15', 'Kalite Kontrol Sorumlusu', 'Kalite', 'ayse@firma.com', '05322222222', 'Ankara', 'Uzman personel']
];

foreach ($employees as $emp) {
    $query = "INSERT IGNORE INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssssssssss', ...$emp);
    $stmt->execute();
    $stmt->close();
}

echo "Personeller oluşturuldu.<br>";

// Create sample customers
$customers = [
    ['Müşteri A', '1234567890', 'İstanbul', '02123334455', 'a@musteri.com', '$2y$10$xyz', 'VIP müşteri'],
    ['Müşteri B', '0987654321', 'Ankara', '03124445566', 'b@musteri.com', '$2y$10$xyz', 'Standart müşteri']
];

foreach ($customers as $cust) {
    $query = "INSERT IGNORE INTO musteriler (musteri_adi, vergi_no_tc, adres, telefon, e_posta, sistem_sifresi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssss', ...$cust);
    $stmt->execute();
    $stmt->close();
}

echo "Müşteriler oluşturuldu.<br>";

// Create sample tanks
$tanks = [
    ['TANK001', 'Esans Tankı 1', 'Ana üretim tankı', 1000.00],
    ['TANK002', 'Esans Tankı 2', 'Rezerv tank', 500.00]
];

foreach ($tanks as $tank) {
    $query = "INSERT IGNORE INTO tanklar (tank_kodu, tank_ismi, not_bilgisi, kapasite) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssd', ...$tank);
    $stmt->execute();
    $stmt->close();
}

echo "Tanklar oluşturuldu.<br>";

// Create sample essences
$essences = [
    ['ESANS001', 'Lavanta Esansı', 'Yüksek kaliteli lavanta esansı', 200.00, 'lt', 30],
    ['ESANS002', 'Gül Esansı', 'Doğal gül esansı', 150.00, 'lt', 45]
];

foreach ($essences as $ess) {
    $query = "INSERT IGNORE INTO esanslar (esans_kodu, esans_ismi, not_bilgisi, stok_miktari, birim, demlenme_suresi_gun) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssdss', ...$ess);
    $stmt->execute();
    $stmt->close();
}

echo "Esanslar oluşturuldu.<br>";

echo "<br><strong>Tüm test verileri başarıyla oluşturuldu!</strong>";
?>