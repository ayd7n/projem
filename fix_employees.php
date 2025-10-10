<?php
header('Content-Type: text/html; charset=utf-8');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parfum_erp";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    
    // First, clear the previously inserted records with encoding issues
    $pdo->exec("DELETE FROM personeller WHERE personel_id > 1");
    
    // Prepare the insert statement
    $stmt = $pdo->prepare("INSERT INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Turkish employee data
    $employees = [
        ['Ahmet Yılmaz', '12345678901', '1985-06-15', '2020-01-15', 'Satış Müdürü', 'Satış', 'ahmet.yilmaz@parfum.com', '05321234567', 'İstanbul, Beşiktaş', 'Tecrübeli satış personeli'],
        ['Ayşe Kaya', '23456789012', '1990-03-22', '2021-02-10', 'Pazarlama Uzmanı', 'Pazarlama', 'ayse.kaya@parfum.com', '05332345678', 'İstanbul, Kadıköy', 'Yaratıcı pazarlama uzmanı'],
        ['Mehmet Demir', '34567890123', '1987-11-08', '2019-05-20', 'Depo Sorumlusu', 'Lojistik', 'mehmet.demir@parfum.com', '05343456789', 'İstanbul, Pendik', 'Depo süreçlerinde uzman'],
        ['Fatma Öztürk', '45678901234', '1992-07-30', '2022-01-05', 'Muhasebe Uzmanı', 'Muhasebe', 'fatma.ozturk@parfum.com', '05354567890', 'İstanbul, Üsküdar', 'Muhasebe sistemlerinde deneyimli'],
        ['Ali Can', '56789012345', '1988-12-10', '2018-09-12', 'Üretim Müdürü', 'Üretim', 'ali.can@parfum.com', '05365678901', 'İstanbul, Tuzla', 'Üretim süreçlerinde lider'],
        ['Zeynep Şahin', '67890123456', '1993-04-18', '2021-11-03', 'İK Uzmanı', 'İnsan Kaynakları', 'zeynep.sahin@parfum.com', '05376789012', 'İstanbul, Şişli', 'Çalışan memnuniyeti uzmanı'],
        ['Caner Aktaş', '78901234567', '1986-09-25', '2020-03-18', 'Finans Uzmanı', 'Finans', 'caner.aktas@parfum.com', '05387890123', 'İstanbul, Levent', 'Finansal analiz uzmanı'],
        ['Ebru Güneş', '89012345678', '1991-01-07', '2022-02-28', 'Sistem Uzmanı', 'Bilgi İşlem', 'ebru.gunes@parfum.com', '05398901234', 'İstanbul, Maslak', 'IT sistemleri uzmanı'],
        ['Kemal Başbakan', '90123456789', '1984-08-14', '2017-06-01', 'Kalite Kontrol Müdürü', 'Kalite', 'kemal.baskan@parfum.com', '05409012345', 'İstanbul, Ataşehir', 'ISO kalite sistemleri uzmanı'],
        ['Gülşah Arslan', '01234567890', '1994-10-30', '2023-01-10', 'Arama Uzmanı', 'Arama', 'gulsa.arslan@parfum.com', '05410123456', 'İstanbul, Bomonti', 'Yeni başlayan arama uzmanı']
    ];
    
    // Insert the employee records
    foreach ($employees as $emp) {
        $stmt->execute($emp);
    }
    
    echo "10 employee records have been successfully added with proper Turkish character encoding!";
    
    // Verify the data
    echo "<h3>Verification:</h3>";
    $verifyStmt = $pdo->query("SELECT personel_id, ad_soyad, pozisyon, departman FROM personeller WHERE personel_id > 1 ORDER BY personel_id");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Personel ID</th><th>Ad Soyad</th><th>Pozisyon</th><th>Departman</th></tr>";
    
    while ($row = $verifyStmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['personel_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ad_soyad']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pozisyon']) . "</td>";
        echo "<td>" . htmlspecialchars($row['departman']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Connection or query failed: " . $e->getMessage();
}
?>