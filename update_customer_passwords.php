<?php
require_once('config.php');

// Hash the default password
$default_password = '12345';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update all customers with the default password
$query = "UPDATE musteriler SET sistem_sifresi = ? WHERE sistem_sifresi IS NULL OR sistem_sifresi = ''";

$stmt = $connection->prepare($query);
$stmt->bind_param('s', $hashed_password);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    echo "Tüm müşterilere varsayılan şifre ('12345') başarıyla atanmıştır. Etkilenen satır sayısı: " . $affected_rows . "\n";
    
    // Update all customers regardless of current value to ensure consistency
    $update_all_query = "UPDATE musteriler SET sistem_sifresi = ?";
    $update_all_stmt = $connection->prepare($update_all_query);
    $update_all_stmt->bind_param('s', $hashed_password);
    $update_all_stmt->execute();
    $update_all_stmt->close();
    
    echo "Tüm müşterilerin şifresi '12345' olarak güncellenmiştir.\n";
} else {
    echo "Hata oluştu: " . $connection->error . "\n";
}

$stmt->close();
$connection->close();
?>