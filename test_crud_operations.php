<?php
// Test script for API CRUD operations via command line
include 'config.php';

echo "Testing API CRUD operations...\n";

// Function to test API calls using cURL with session handling
function test_api_call($data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');  // Store cookies
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt'); // Use stored cookies
    
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    curl_close($ch);
    
    $result = json_decode($body, true);
    return $result ?: ['status' => 'error', 'message' => 'Invalid JSON response', 'raw' => $body];
}

// Test the database connection first
echo "1. Testing database connection...\n";
try {
    $test_query = "SELECT COUNT(*) as total FROM cerceve_sozlesmeler";
    $result = $connection->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Database connection OK. Current records: " . $row['total'] . "\n";
    } else {
        echo "Database connection failed.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Testing API without session (should fail with auth error)...\n";
$result = test_api_call(['action' => 'get_all_contracts']);
echo "Response: " . json_encode($result) . "\n";

echo "\n3. Testing requires logging in first to get session.\n";
echo "Please login through the web interface first to create a session, then run this test.\n";

?>