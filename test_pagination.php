<?php
// Test script to check for syntax errors in pagination
echo "Testing musteri_siparisleri.php pagination...\n";

// Test URL
$url = "http://localhost/projem/musteri_siparisleri.php?filter=tum&page=2";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_error($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Status Code: " . $http_code . "\n";
    
    if ($http_code == 200) {
        // Look for JavaScript syntax errors in the response
        if (strpos($response, 'Unexpected token \'&lt;\'') !== false || 
            strpos($response, 'Unexpected token \'<!DOCTYPE') !== false || 
            strpos($response, 'Unexpected token \'<?php') !== false) {
            echo "Potential JavaScript errors found in response\n";
        } else {
            echo "Page loaded successfully without obvious syntax errors\n";
        }
        
        // Count occurrences of problematic patterns
        $script_count = substr_count($response, '<script');
        $a_href_count = substr_count($response, '<a href');
        echo "Found $script_count script tags and $a_href_count links\n";
    }
}

// Close cURL
curl_close($ch);

echo "Test completed.\n";
?>