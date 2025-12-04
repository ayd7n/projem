<?php
// Test file to debug API
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test';

$_GET['action'] = 'get_product_card';
$_GET['urun_kodu'] = 1;

include 'api_islemleri/urun_karti_islemler.php';
