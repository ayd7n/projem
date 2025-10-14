<?php
require_once('config.php');

// Ensure UTF8MB4 encoding
$connection->set_charset("utf8mb4");

// Insert 10 employee records with proper Turkish characters
$employees = [
    [
        'ad_soyad' => 'Ahmet Yilmaz',
        'tc_kimlik_no' => '12345678901',
        'dogum_tarihi' => '1985-06-15',
        'ise_giris_tarihi' => '2020-01-15',
        'pozisyon' => 'Satis Muduru',
        'departman' => 'Satis',
        'e_posta' => 'ahmet.yilmaz@parfum.com',
        'telefon' => '05321234567',
        'adres' => 'Istanbul, Besiktas',
        'notlar' => 'Tecrubeli satis personeli'
    ],
    [
        'ad_soyad' => 'Ayse Kaya',
        'tc_kimlik_no' => '23456789012',
        'dogum_tarihi' => '1990-03-22',
        'ise_giris_tarihi' => '2021-02-10',
        'pozisyon' => 'Pazarlama Uzmani',
        'departman' => 'Pazarlama',
        'e_posta' => 'ayse.kaya@parfum.com',
        'telefon' => '05332345678',
        'adres' => 'Istanbul, Kadikoy',
        'notlar' => 'Yaratici pazarlama uzmani'
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
        'adres' => 'Istanbul, Pendik',
        'notlar' => 'Depo sureclerinde uzman'
    ],
    [
        'ad_soyad' => 'Fatma Ozturk',
        'tc_kimlik_no' => '45678901234',
        'dogum_tarihi' => '1992-07-30',
        'ise_giris_tarihi' => '2022-01-05',
        'pozisyon' => 'Muhasebe Uzmani',
        'departman' => 'Muhasebe',
        'e_posta' => 'fatma.ozturk@parfum.com',
        'telefon' => '05354567890',
        'adres' => 'Istanbul, Uskudar',
        'notlar' => 'Muhasebe sistemlerinde deneyimli'
    ],
    [
        'ad_soyad' => 'Ali Can',
        'tc_kimlik_no' => '56789012345',
        'dogum_tarihi' => '1988-12-10',
        'ise_giris_tarihi' => '2018-09-12',
        'pozisyon' => 'Uretim Muduru',
        'departman' => 'Uretim',
        'e_posta' => 'ali.can@parfum.com',
        'telefon' => '05365678901',
        'adres' => 'Istanbul, Tuzla',
        'notlar' => 'Uretim sureclerinde lider'
    ],
    [
        'ad_soyad' => 'Zeynep Sahin',
        'tc_kimlik_no' => '67890123456',
        'dogum_tarihi' => '1993-04-18',
        'ise_giris_tarihi' => '2021-11-03',
        'pozisyon' => 'IK Uzmani',
        'departman' => 'Insan Kaynaklari',
        'e_posta' => 'zeynep.sahin@parfum.com',
        'telefon' => '05376789012',
        'adres' => 'Istanbul, Sisli',
        'notlar' => 'Calisan memnuniyeti uzmani'
    ],
    [
        'ad_soyad' => 'Caner Aktas',
        'tc_kimlik_no' => '78901234567',
        'dogum_tarihi' => '1986-09-25',
        'ise_giris_tarihi' => '2020-03-18',
        'pozisyon' => 'Finans Uzmani',
        'departman' => 'Finans',
        'e_posta' => 'caner.aktas@parfum.com',
        'telefon' => '05387890123',
        'adres' => 'Istanbul, Levent',
        'notlar' => 'Finansal analiz uzmani'
    ],
    [
        'ad_soyad' => 'Ebru Gunes',
        'tc_kimlik_no' => '89012345678',
        'dogum_tarihi' => '1991-01-07',
        'ise_giris_tarihi' => '2022-02-28',
        'pozisyon' => 'Sistem Uzmani',
        'departman' => 'Bilgi Islem',
        'e_posta' => 'ebru.gunes@parfum.com',
        'telefon' => '05398901234',
        'adres' => 'Istanbul, Maslak',
        'notlar' => 'IT sistemleri uzmani'
    ],
    [
        'ad_soyad' => 'Kemal Basbakan',
        'tc_kimlik_no' => '90123456789',
        'dogum_tarihi' => '1984-08-14',
        'ise_giris_tarihi' => '2017-06-01',
        'pozisyon' => 'Kalite Kontrol Muduru',
        'departman' => 'Kalite',
        'e_posta' => 'kemal.baskan@parfum.com',
        'telefon' => '05409012345',
        'adres' => 'Istanbul, Atasehir',
        'notlar' => 'ISO kalite sistemleri uzmani'
    ],
    [
        'ad_soyad' => 'Gulsah Arslan',
        'tc_kimlik_no' => '01234567890',
        'dogum_tarihi' => '1994-10-30',
        'ise_giris_tarihi' => '2023-01-10',
        'pozisyon' => 'Arama Uzmani',
        'departman' => 'Arama',
        'e_posta' => 'gulsa.arslan@parfum.com',
        'telefon' => '05410123456',
        'adres' => 'Istanbul, Bomonti',
        'notlar' => 'Yeni baslayan arama uzmani'
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