<?php
// Check if mysqli extension is loaded
if (extension_loaded('mysqli')) {
    echo "mysqli extension is loaded\n";
} else {
    echo "mysqli extension is NOT loaded\n";
}

// Check all loaded extensions
$extensions = get_loaded_extensions();
echo "\nAll loaded extensions:\n";
foreach ($extensions as $extension) {
    echo "- $extension\n";
}

// Check PHP configuration for MySQL
echo "\nMySQL related PHP settings:\n";
echo "mysql.connect_timeout = " . ini_get('mysql.connect_timeout') . "\n";
echo "mysql.default_host = " . ini_get('mysql.default_host') . "\n";
echo "mysql.default_port = " . ini_get('mysql.default_port') . "\n";
echo "mysql.default_socket = " . ini_get('mysql.default_socket') . "\n";

// Check MySQLi settings
echo "\nMySQLi related PHP settings:\n";
echo "mysqli.default_host = " . ini_get('mysqli.default_host') . "\n";
echo "mysqli.default_port = " . ini_get('mysqli.default_port') . "\n";
echo "mysqli.default_socket = " . ini_get('mysqli.default_socket') . "\n";
echo "mysqli.reconnect = " . ini_get('mysqli.reconnect') . "\n";
echo "mysqli.rollback_on_cached_plink = " . ini_get('mysqli.rollback_on_cached_plink') . "\n";

// Check connection info
echo "\nPHP Version: " . PHP_VERSION . "\n";
echo "MySQL Client Version: " . mysqli_get_client_info() . "\n";
?>