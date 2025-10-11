<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

// Mevcut iş merkezlerini kontrol et
$query = "SELECT COUNT(*) as total FROM is_merkezleri";
$result = $connection->query($query);
$total = $result->fetch_assoc()['total'];

echo "<h2>Mevcut İş Merkezleri Sayısı: $total</h2>";

// Eğer 0 ise, örnek verileri ekle
if ($total == 0) {
    echo "<h3>İş merkezleri tablosu boş. Örnek veriler ekleniyor...</h3>";

    $work_centers = [
        ['isim' => 'Ana Uretim Alani', 'aciklama' => 'Ana uretim bolumu'],
        ['isim' => 'Kimyasal Hazirlama', 'aciklama' => 'Kimyasal karisimlarin hazirlandigi alan'],
        ['isim' => 'Paketleme Bolumu', 'aciklama' => 'Urunlerin paketlendigi bolum'],
        ['isim' => 'Kalite Kontrol', 'aciklama' => 'Urun kalitesinin kontrol edildigi alan'],
        ['isim' => 'Hammadde Deposu', 'aciklama' => 'Hammadde stoklarinin tutuldugu depo'],
        ['isim' => 'Mamul Deposu', 'aciklama' => 'Hazir urunlerin tutuldugu depo'],
        ['isim' => 'Karistirma Odasi', 'aciklama' => 'Esans karisimlarinin yapildigi oda'],
        ['isim' => 'Test Laboratuari', 'aciklama' => 'Urun testlerinin yapildigi laboratuar'],
        ['isim' => 'Ambalajlama Alani', 'aciklama' => 'Urun ambalajlama islemlerinin yapildigi alan'],
        ['isim' => 'Sevkiyat Bolumu', 'aciklama' => 'Hazirlanan siparislerin sevkiyata hazirlandigi bolum']
    ];

    foreach ($work_centers as $work_center) {
        $isim = $work_center['isim'];
        $aciklama = $work_center['aciklama'];

        $insert_query = "INSERT INTO is_merkezleri (isim, aciklama) VALUES (?, ?)";
        $stmt = $connection->prepare($insert_query);
        $stmt->bind_param('ss', $isim, $aciklama);

        if ($stmt->execute()) {
            echo "✓ İş merkezi '$isim' başarıyla eklendi.<br>";
        } else {
            echo "✗ İş merkezi '$isim' eklenirken hata oluştu: " . $connection->error . "<br>";
        }
        $stmt->close();
    }

    echo "<h3>Tüm iş merkezleri başarıyla eklendi!</h3>";
} else {
    echo "<h3>İş merkezleri tablosunda zaten $total kayıt var.</h3>";

    // Mevcut kayıtları göster
    $list_query = "SELECT * FROM is_merkezleri ORDER BY isim";
    $list_result = $connection->query($list_query);

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>İş Merkezi Adı</th><th>Açıklama</th></tr>";

    while ($row = $list_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['is_merkezi_id']}</td>";
        echo "<td>{$row['isim']}</td>";
        echo "<td>{$row['aciklama']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><a href='is_merkezleri.php'>İş Merkezleri Sayfasına Dön</a>";
?>
