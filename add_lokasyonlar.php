<?php
require_once('config.php');

// Array of different raf names using ASCII characters only
$raf_names = [
    'R001',
    'R002', 
    'R003',
    'R004',
    'R005',
    'R006',
    'R007',
    'R008',
    'R009',
    'R010'
];

// Same depo name for all
$depo_ismi = 'Main Warehouse';

$added_count = 0;

foreach ($raf_names as $raf) {
    $query = "INSERT INTO lokasyonlar (depo_ismi, raf) VALUES (?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ss', $depo_ismi, $raf);
    
    if ($stmt->execute()) {
        echo "Successfully added: $depo_ismi - $raf<br>";
        $added_count++;
    } else {
        echo "Error adding $depo_ismi - $raf: " . $connection->error . "<br>";
    }
    $stmt->close();
}

echo "<br>$added_count adet lokasyon başarıyla eklendi.";
$connection->close();
?>