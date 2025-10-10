<?php
require_once('config.php');

// Ensure UTF8MB4 encoding
$connection->set_charset("utf8mb4");

// Insert 10 employee records with proper Turkish characters
$employees = [
    [
        'ad_soyad' => 'Ahmet Yılmaz',
        'tc_kimlik_no' => '12345678901',
        'dogum_tarihi' => '1985-06-15',
        'ise_giris_tarihi' => '2020-01-15',
        'pozisyon' => 'Satış Müdürü',
        'departman' => 'Satış',
        'e_posta' => 'ahmet.yilmaz@parfum.com',
        'telefon' => '05321234567',
        'adres' => 'İstanbul, Beşiktaş',
        'notlar' => 'Tecrübeli satış personeli'
    ],
    [
        'ad_soyad' => 'Ayşe Kaya',
        'tc_kimlik_no' => '23456789012',
        'dogum_tarihi' => '1990-03-22',
        'ise_giris_tarihi' => '2021-02-10',
        'pozisyon' => 'Pazarlama Uzmanı',
        'departman' => 'Pazarlama',
        'e_posta' => 'ayse.kaya@parfum.com',
        'telefon' => '05332345678',
        'adres' => 'İstanbul, Kadıköy',
        'notlar' => 'Yaratıcı pazarlama uzmanı'
    ],
    [
        'ad_soyad' => 'Mehmet Demir',
        'tc_kimlik_no' => '34567890123',
        'dogum_tarihi' => '1987-11-08',
        'ise_giris_tarihi' => '2019-05-20',
        'pozisyon' => 'Depo Sorumlusu',
        'departman' => 'Lojistik',
        'e_posta' => 'mehmet.demir@parfum.com',
        'telefon' => '05343456789',
        'adres' => 'İstanbul, Pendik',
        'notlar' => 'Depo süreçlerinde uzman'
    ],
    [
        'ad_soyad' => 'Fatma Öztürk',
        'tc_kimlik_no' => '45678901234',
        'dogum_tarihi' => '1992-07-30',
        'ise_giris_tarihi' => '2022-01-05',
        'pozisyon' => 'Muhasebe Uzmanı',
        'departman' => 'Muhasebe',
        'e_posta' => 'fatma.ozturk@parfum.com',
        'telefon' => '05354567890',
        'adres' => 'İstanbul, Üsküdar',
        'notlar' => 'Muhasebe sistemlerinde deneyimli'
    ],
    [
        'ad_soyad' => 'Ali Can',
        'tc_kimlik_no' => '56789012345',
        'dogum_tarihi' => '1988-12-10',
        'ise_giris_tarihi' => '2018-09-12',
        'pozisyon' => 'Üretim Müdürü',
        'departman' => 'Üretim',
        'e_posta' => 'ali.can@parfum.com',
        'telefon' => '05365678901',
        'adres' => 'İstanbul, Tuzla',
        'notlar' => 'Üretim süreçlerinde lider'
    ],
    [
        'ad_soyad' => 'Zeynep Şahin',
        'tc_kimlik_no' => '67890123456',
        'dogum_tarihi' => '1993-04-18',
        'ise_giris_tarihi' => '2021-11-03',
        'pozisyon' => 'İK Uzmanı',
        'departman' => 'İnsan Kaynakları',
        'e_posta' => 'zeynep.sahin@parfum.com',
        'telefon' => '05376789012',
        'adres' => 'İstanbul, Şişli',
        'notlar' => 'Çalışan memnuniyeti uzmanı'
    ],
    [
        'ad_soyad' => 'Caner Aktaş',
        'tc_kimlik_no' => '78901234567',
        'dogum_tarihi' => '1986-09-25',
        'ise_giris_tarihi' => '2020-03-18',
        'pozisyon' => 'Finans Uzmanı',
        'departman' => 'Finans',
        'e_posta' => 'caner.aktas@parfum.com',
        'telefon' => '05387890123',
        'adres' => 'İstanbul, Levent',
        'notlar' => 'Finansal analiz uzmanı'
    ],
    [
        'ad_soyad' => 'Ebru Güneş',
        'tc_kimlik_no' => '89012345678',
        'dogum_tarihi' => '1991-01-07',
        'ise_giris_tarihi' => '2022-02-28',
        'pozisyon' => 'Sistem Uzmanı',
        'departman' => 'Bilgi İşlem',
        'e_posta' => 'ebru.gunes@parfum.com',
        'telefon' => '05398901234',
        'adres' => 'İstanbul, Maslak',
        'notlar' => 'IT sistemleri uzmanı'
    ],
    [
        'ad_soyad' => 'Kemal Başbakan',
        'tc_kimlik_no' => '90123456789',
        'dogum_tarihi' => '1984-08-14',
        'ise_giris_tarihi' => '2017-06-01',
        'pozisyon' => 'Kalite Kontrol Müdürü',
        'departman' => 'Kalite',
        'e_posta' => 'kemal.baskan@parfum.com',
        'telefon' => '05409012345',
        'adres' => 'İstanbul, Ataşehir',
        'notlar' => 'ISO kalite sistemleri uzmanı'
    ],
    [
        'ad_soyad' => 'Gülşah Arslan',
        'tc_kimlik_no' => '01234567890',
        'dogum_tarihi' => '1994-10-30',
        'ise_giris_tarihi' => '2023-01-10',
        'pozisyon' => 'Arama Uzmanı',
        'departman' => 'Arama',
        'e_posta' => 'gulsa.arslan@parfum.com',
        'telefon' => '05410123456',
        'adres' => 'İstanbul, Bomonti',
        'notlar' => 'Yeni başlayan arama uzmanı'
    ]
];

// Prepare the insert statement
$stmt = $connection->prepare("INSERT INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    foreach ($employees as $emp) {
        $stmt->bind_param('ssssssssss', 
            $emp['ad_soyad'], 
            $emp['tc_kimlik_no'], 
            $emp['dogum_tarihi'], 
            $emp['ise_giris_tarihi'], 
            $emp['pozisyon'], 
            $emp['departman'], 
            $emp['e_posta'], 
            $emp['telefon'], 
            $emp['adres'], 
            $emp['notlar']
        );
        
        if ($stmt->execute()) {
            echo "Successfully added: " . $emp['ad_soyad'] . "<br>";
        } else {
            echo "Error adding " . $emp['ad_soyad'] . ": " . $stmt->error . "<br>";
        }
    }
    $stmt->close();
    echo "<br>All employee records have been successfully added with proper Turkish characters!";
} else {
    echo "Error preparing statement: " . $connection->error;
}

$connection->close();
?>