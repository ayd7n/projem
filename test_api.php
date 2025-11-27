<?php
// Test API endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/projem/api_islemleri/cerceve_sozlesmeler_islemler.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "action=get_contract_movements&sozlesme_id=41");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

header('Content-Type: application/json');
echo json_encode([
    'raw_response' => $response,
    'error' => $error,
    'decoded' => json_decode($response, true)
]);
