<?php
require_once('config.php');

// Hash the new password
$new_password = '12345';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "New hashed password for '12345': " . $hashed_password . "\n";

// Update the admin user's password
$query = "UPDATE sistem_kullanicilari SET sifre = ? WHERE kullanici_adi = 'admin'";

$stmt = $connection->prepare($query);
$stmt->bind_param('s', $hashed_password);

if ($stmt->execute()) {
    echo "Admin password successfully updated to '12345'!\n";
} else {
    echo "Error updating admin password: " . $connection->error . "\n";
}

$stmt->close();
$connection->close();
?>