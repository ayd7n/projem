<?php
require_once('config.php');

// Hash the default password
$default_password = '12345';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update all employees with the default password
$query = "UPDATE personeller SET sistem_sifresi = ? WHERE sistem_sifresi IS NULL";

$stmt = $connection->prepare($query);
$stmt->bind_param('s', $hashed_password);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    echo "Tüm personellere varsayılan şifre ('12345') başarıyla atanmıştır. Etkilenen satır sayısı: " . $affected_rows . "\n";
    
    // Also update the admin user if exists (personel_id = 1)
    $admin_query = "UPDATE personeller SET sistem_sifresi = ? WHERE personel_id = 1";
    $admin_stmt = $connection->prepare($admin_query);
    $admin_stmt->bind_param('s', $hashed_password);
    $admin_stmt->execute();
    $admin_stmt->close();
    
    echo "Admin kullanıcıya da varsayılan şifre atanmıştır.\n";
} else {
    echo "Hata oluştu: " . $connection->error . "\n";
}

$stmt->close();
$connection->close();
?>