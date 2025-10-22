<?php
include 'config.php';

// Check if admin user exists in staff table
$result = $connection->query("SELECT personel_id, ad_soyad, e_posta FROM personeller LIMIT 5;");
echo "Staff members:\n";
while($row = $result->fetch_assoc()) {
    print_r($row);
}

// Check specifically for admin@parfum.com
echo "\nSearching for admin@parfum.com:\n";
$result2 = $connection->query("SELECT personel_id, ad_soyad, e_posta FROM personeller WHERE e_posta = 'admin@parfum.com';");
if($row2 = $result2->fetch_assoc()) {
    print_r($row2);
} else {
    echo "No staff found with email admin@parfum.com\n";
}

$connection->close();
?>