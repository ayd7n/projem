<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'parfum_erp');

// Create connection
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set charset to UTF-8
$connection->set_charset("utf8mb4");
$connection->query("SET NAMES 'utf8mb4'");
$connection->query("SET CHARACTER SET utf8mb4");
$connection->query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");

// Session start
session_start();
?>