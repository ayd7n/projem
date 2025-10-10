<?php
include 'config.php';

echo "<h2>Veritabanı İstatistikleri</h2>";

// Count materials
$malzeme_query = "SELECT COUNT(*) as count FROM malzemeler";
$malzeme_result = $connection->query($malzeme_query);
$malzeme_count = $malzeme_result->fetch_assoc()['count'];
echo "Malzeme sayısı: " . $malzeme_count . "<br>";

// Count products
$urun_query = "SELECT COUNT(*) as count FROM urunler";
$urun_result = $connection->query($urun_query);
$urun_count = $urun_result->fetch_assoc()['count'];
echo "Ürün sayısı: " . $urun_count . "<br>";

// Count pending orders
$bekleyen_siparis_query = "SELECT COUNT(*) as count FROM siparisler WHERE durum = 'beklemede'";
$bekleyen_siparis_result = $connection->query($bekleyen_siparis_query);
$bekleyen_siparis_count = $bekleyen_siparis_result->fetch_assoc()['count'];
echo "Bekleyen sipariş sayısı: " . $bekleyen_siparis_count . "<br>";

// Count critical stock items
$kritik_stok_query = "SELECT COUNT(*) as count FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi";
$kritik_stok_result = $connection->query($kritik_stok_query);
$kritik_stok_count = $kritik_stok_result->fetch_assoc()['count'];
echo "Kritik stok sayısı: " . $kritik_stok_count . "<br>";

// List some sample products
echo "<h3>Örnek Ürünler</h3>";
$sample_products_query = "SELECT urun_ismi, stok_miktari, satis_fiyati FROM urunler LIMIT 5";
$sample_products_result = $connection->query($sample_products_query);
while($row = $sample_products_result->fetch_assoc()) {
    echo "- " . htmlspecialchars($row['urun_ismi']) . " (Stok: " . $row['stok_miktari'] . ", Fiyat: " . $row['satis_fiyati'] . " TL)<br>";
}

echo "<br><strong>Sistem başarıyla çalışıyor ve gerçek verilerle çalışıyor!</strong>";
?>