<?php
include 'config.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['taraf'] === 'musteri') {
        header('Location: customer_panel.php');
    } else {
        header('Location: navigation.php');
    }
} else {
    header('Location: login.php');
}
exit;
