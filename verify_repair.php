<?php
// Verification script to test if the MySQL issue is resolved
echo "Checking MySQL and mysqli functionality:\n\n";

// Check if mysqli extension is loaded
if (extension_loaded('mysqli')) {
    echo "[OK] mysqli extension is loaded\n";
} else {
    echo "[ERROR] mysqli extension is NOT loaded\n";
    exit(1);
}

// Try to connect to MySQL
echo "\nAttempting to connect to MySQL...\n";

if ($link = @mysqli_connect('localhost', 'root', '')) {
    echo "[OK] Successfully connected to MySQL!\n";
    
    // Test a simple query
    $result = mysqli_query($link, "SELECT VERSION() as version");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "[OK] MySQL Version: " . $row['version'] . "\n";
    }
    
    // Check if our project database exists
    $db_result = mysqli_query($link, "SHOW DATABASES LIKE 'parfum_erp'");
    if (mysqli_num_rows($db_result) > 0) {
        echo "[OK] 'parfum_erp' database exists\n";
    } else {
        echo "[INFO] 'parfum_erp' database does not exist yet\n";
    }
    
    mysqli_close($link);
} else {
    echo "[ERROR] Could not connect to MySQL: " . mysqli_connect_error() . "\n";
    echo "This means MySQL service is still not running properly.\n";
    exit(1);
}

echo "\n[SUCCESS] All tests passed! Your MySQL and mysqli setup is working.\n";
?>