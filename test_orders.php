<?php
include 'config.php';

session_start(); // Session kontrolü için

echo "=== DEBUG SESSION KONTROLÜ ===\n";

if (isset($_SESSION['user_id'])) {
    echo "Session user_id: " . $_SESSION['user_id'] . "\n";
    echo "Session taraf: " . $_SESSION['taraf'] . "\n";
    echo "Session kullanici_adi: " . $_SESSION['kullanici_adi'] . "\n";
} else {
    echo "Session yok!\n";
}

if (isset($_SESSION['cart'])) {
    echo "Cart içeriği: " . print_r($_SESSION['cart'], true);
} else {
    echo "Cart boş!\n";
}

echo "\n=== SİPARİŞLER VE SİPARİŞ KALEMLERİ KONTROLÜ ===\n\n";

// Son 5 sipariş
echo "Son 5 Sipariş:\n";
$result = $connection->query("SELECT * FROM siparisler ORDER BY tarih DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['siparis_id']} - Müşteri: {$row['musteri_adi']} - Durum: {$row['durum']} - Toplam Adet: {$row['toplam_adet']} - Tarih: {$row['tarih']}\n";
    }
} else {
    echo "Sipariş bulunamadı\n";
}

echo "\n=== SİPARİŞ KALEMLERİ ===\n";

// Sipariş kalemleri kontrolü
$result = $connection->query("SELECT sk.*, u.urun_ismi as urun_gercek_ismi, u.birim as urun_gercek_birimi
                              FROM siparis_kalemleri sk
                              LEFT JOIN urunler u ON sk.urun_kodu = u.urun_kodu
                              ORDER BY sk.siparis_id DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "Sipariş Kalemleri:\n";
    while ($row = $result->fetch_assoc()) {
        echo "--- Sipariş: {$row['siparis_id']} ---\n";
        echo "Ürün Kodu: {$row['urun_kodu']}\n";
        echo "Veritabanında Kaydedilen İsim: '{$row['urun_ismi']}'\n";
        echo "Gerçek Ürün İsim (urunler tablosu): '{$row['urun_gercek_ismi']}'\n";
        echo "Kaydedilen Birim: '{$row['birim']}'\n";
        echo "Gerçek Birim (urunler tablosu): '{$row['urun_gercek_birimi']}'\n";
        echo "Adet: {$row['adet']}\n";
        echo "Fiyat: {$row['birim_fiyat']}\n";
        echo "Toplam Tutar: {$row['toplam_tutar']}\n";
        echo "\n";
    }
} else {
    echo "Sipariş kalemi bulunamadı\n";
}

echo "\n=== ÜRÜNLER TABLOSU ÖRNEKLERİ ===\n";

$result = $connection->query("SELECT urun_kodu, urun_ismi, birim, stok_miktari FROM urunler WHERE stok_miktari > 0 LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Ürün Kodu: {$row['urun_kodu']} - İsim: {$row['urun_ismi']} - Birim: {$row['birim']} - Stok: {$row['stok_miktari']}\n";
    }
} else {
    echo "Ürün bulunamadı\n";
}

$connection->close();
?>
