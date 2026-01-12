<?php
include 'config.php';

// Admin kullanıcı oturumu oluşturmak için kullanıcı adını ve ID'sini belirle
// Veritabanından admin kullanıcıyı bulalım
$username = 'admin';

// Kullanıcı bilgilerini al
$kullanici_sorgu = $connection->prepare("SELECT id, taraf FROM sistem_kullanicilari WHERE kullanici_adi = ?");
$kullanici_sorgu->bind_param("s", $username);
$kullanici_sorgu->execute();
$kullanici_sonuc = $kullanici_sorgu->get_result();

if ($kullanici_sonuc->num_rows > 0) {
    $kullanici = $kullanici_sonuc->fetch_assoc();
    
    // Oturum başlat
    session_start();
    $_SESSION['user_id'] = $kullanici['id'];
    $_SESSION['taraf'] = $kullanici['taraf'];
    
    // Kullanıcı adı bilgisi
    $_SESSION['kullanici_adi'] = $username;
    
    echo "Oturum başarıyla oluşturuldu. Kullanıcı ID: " . $kullanici['id'] . ", Taraf: " . $kullanici['taraf'] . "<br>";
    
    // Kullanılacak kategoriler ve ödeme türleri
    $kategoriler = [
        'Personel Gideri',
        'Malzeme Gideri', 
        'İşletme Gideri',
        'Kira',
        'Enerji',
        'Taşıt Gideri',
        'Diğer'
    ];

    $odeme_turleri = [
        'Nakit',
        'Kredi Kartı',
        'Havale',
        'Diğer'
    ];

    // Açıklamalar için örnek metinler
    $aciklamalar = [
        'Aylık elektrik faturası',
        'Ofis malzeme alışverişi',
        'Personel maaşı',
        'Taşıt yakıt gideri',
        'Kira ödemesi',
        'İnternet hizmeti',
        'Telefon faturası',
        'Temizlik malzemeleri',
        'Pazarlama harcaması',
        'Bakım onarım gideri',
        'Sigorta primi',
        'Kira gideri',
        'Yazılım lisans ücreti',
        'Taşıt bakım gideri',
        'Personel eğitim gideri'
    ];

    // 2025 yılı için aylık tarihler oluştur
    $baslangic_yili = 2025;
    $bitis_yili = 2025;

    $aylar = [];
    for ($yil = $baslangic_yili; $yil <= $bitis_yili; $yil++) {
        for ($ay = 1; $ay <= 12; $ay++) {
            $aylar[] = sprintf('%04d-%02d-01', $yil, $ay);
            $aylar[] = sprintf('%04d-%02d-15', $yil, $ay);
        }
    }

    // 100 adet rastgele gider kaydı oluştur
    $eklenen_kayit_sayisi = 0;
    while ($eklenen_kayit_sayisi < 100) {
        // Rastgele bir tarih seç
        $tarih = $aylar[array_rand($aylar)];
        // Ayın sonuna doğru tarihler için ay sonu tarihi de ekle
        $gun = rand(1, 28);
        $tarih = date('Y-m-d', mktime(0, 0, 0, substr($tarih, 5, 2), $gun, substr($tarih, 0, 4)));
        
        // Rastgele veriler oluştur
        $kategori = $kategoriler[array_rand($kategoriler)];
        $tutar = rand(100, 5000) + (rand(0, 99) / 100); // 100.00 - 5000.99 TL arası
        $odeme_tipi = $odeme_turleri[array_rand($odeme_turleri)];
        $aciklama = $aciklamalar[array_rand($aciklamalar)];
        $fatura_no = rand(1000, 9999); // Rastgele fatura no
        
        // Veritabanına ekle
        $stmt = $connection->prepare("INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, fatura_no, aciklama, kaydeden_personel_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $tarih, $kategori, $tutar, $odeme_tipi, $fatura_no, $aciklama, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            echo "Başarıyla eklendi: Tarih: $tarih, Kategori: $kategori, Tutar: $tutar TL<br>";
            $eklenen_kayit_sayisi++;
        } else {
            echo "Hata oluştu: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    }
    
    echo "Toplam 100 gider kaydı eklendi.";
    
} else {
    echo "Kullanıcı bulunamadı.";
}
?>