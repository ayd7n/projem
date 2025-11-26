<?php
session_start();

// Capture session data before destroying the session
$logout_user = $_SESSION['kullanici_adi'] ?? 'unknown';
$user_type = $_SESSION['taraf'] ?? 'unknown';
$user_id = $_SESSION['user_id'] ?? 'unknown';

include 'config.php';

// Log the logout event
log_islem($connection, $logout_user, "$user_type oturumu kapattı (ID: $user_id)", 'Çıkış Yapıldı');

session_destroy();
header('Location: login.php');
exit;
?>