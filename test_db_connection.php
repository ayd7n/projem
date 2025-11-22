<?php
// Test script to test database connection
echo "Testing database connection...\n";

// Test mysqli connection
$mysqli = new mysqli("localhost", "root", "", "information_schema");

if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error . "\n";
} else {
    echo "mysqli connection successful!\n";
    $result = $mysqli->query("SELECT VERSION()");
    if ($result) {
        $row = $result->fetch_row();
        echo "MySQL Version: " . $row[0] . "\n";
    }
    $mysqli->close();
}

// Test PDO connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=information_schema", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "PDO connection successful!\n";
    $result = $pdo->query("SELECT VERSION()");
    $row = $result->fetch();
    echo "MySQL Version (PDO): " . $row[0] . "\n";
} catch(PDOException $e) {
    echo "PDO Connection failed: " . $e->getMessage() . "\n";
}
?>