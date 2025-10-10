<?php
require_once('config.php');

// Ensure UTF8MB4 encoding
$connection->set_charset("utf8mb4");

// Hash the common password
$hashed_password = password_hash('12345', PASSWORD_DEFAULT);

// Insert 10 customer records with ASCII-only characters
$customers = [
    [
        'musteri_adi' => 'Ahmet Yilmaz',
        'vergi_no_tc' => '12345678901',
        'adres' => 'Istanbul, Besiktas',
        'telefon' => '05321234567',
        'e_posta' => 'ahmet.yilmaz@parfum.com',
        'aciklama_notlar' => 'Regular customer'
    ],
    [
        'musteri_adi' => 'Ayse Kaya',
        'vergi_no_tc' => '23456789012',
        'adres' => 'Istanbul, Kadikoy',
        'telefon' => '05332345678',
        'e_posta' => 'ayse.kaya@parfum.com',
        'aciklama_notlar' => 'VIP customer'
    ],
    [
        'musteri_adi' => 'Mehmet Demir',
        'vergi_no_tc' => '34567890123',
        'adres' => 'Istanbul, Pendik',
        'telefon' => '05343456789',
        'e_posta' => 'mehmet.demir@parfum.com',
        'aciklama_notlar' => 'New customer'
    ],
    [
        'musteri_adi' => 'Fatma Ozturk',
        'vergi_no_tc' => '45678901234',
        'adres' => 'Istanbul, Uskudar',
        'telefon' => '05354567890',
        'e_posta' => 'fatma.ozturk@parfum.com',
        'aciklama_notlar' => 'Frequent buyer'
    ],
    [
        'musteri_adi' => 'Ali Can',
        'vergi_no_tc' => '56789012345',
        'adres' => 'Istanbul, Tuzla',
        'telefon' => '05365678901',
        'e_posta' => 'ali.can@parfum.com',
        'aciklama_notlar' => 'Corporate client'
    ],
    [
        'musteri_adi' => 'Zeynep Sahin',
        'vergi_no_tc' => '67890123456',
        'adres' => 'Istanbul, Sisli',
        'telefon' => '05376789012',
        'e_posta' => 'zeynep.sahin@parfum.com',
        'aciklama_notlar' => 'Premium member'
    ],
    [
        'musteri_adi' => 'Caner Aktas',
        'vergi_no_tc' => '78901234567',
        'adres' => 'Istanbul, Levent',
        'telefon' => '05387890123',
        'e_posta' => 'caner.aktas@parfum.com',
        'aciklama_notlar' => 'Loyal customer'
    ],
    [
        'musteri_adi' => 'Ebru Gunes',
        'vergi_no_tc' => '89012345678',
        'adres' => 'Istanbul, Maslak',
        'telefon' => '05398901234',
        'e_posta' => 'ebru.gunes@parfum.com',
        'aciklama_notlar' => 'Online customer'
    ],
    [
        'musteri_adi' => 'Kemal Baskan',
        'vergi_no_tc' => '90123456789',
        'adres' => 'Istanbul, Atasehir',
        'telefon' => '05409012345',
        'e_posta' => 'kemal.baskan@parfum.com',
        'aciklama_notlar' => 'High value customer'
    ],
    [
        'musteri_adi' => 'Gulsah Arslan',
        'vergi_no_tc' => '01234567890',
        'adres' => 'Istanbul, Bomonti',
        'telefon' => '05410123456',
        'e_posta' => 'gulsah.arslan@parfum.com',
        'aciklama_notlar' => 'New prospect'
    ]
];

// Prepare the insert statement
$stmt = $connection->prepare("INSERT INTO musteriler (musteri_adi, vergi_no_tc, adres, telefon, e_posta, sistem_sifresi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    foreach ($customers as $customer) {
        $stmt->bind_param('sssssss', 
            $customer['musteri_adi'], 
            $customer['vergi_no_tc'], 
            $customer['adres'], 
            $customer['telefon'], 
            $customer['e_posta'], 
            $hashed_password, 
            $customer['aciklama_notlar']
        );
        
        if ($stmt->execute()) {
            echo "Successfully added: " . $customer['musteri_adi'] . "<br>";
        } else {
            echo "Error adding " . $customer['musteri_adi'] . ": " . $stmt->error . "<br>";
        }
    }
    $stmt->close();
    echo "<br>All customer records have been successfully added with password '12345'!";
} else {
    echo "Error preparing statement: " . $connection->error;
}

$connection->close();
?>