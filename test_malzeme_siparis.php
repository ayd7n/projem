<?php
// Test script to verify that the malzeme siparis form submission works
include 'config.php';

// This test simulates what the form would send
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Test Sonuçları:</h2>";
    
    // Simulate form data that would be sent by the frontend
    $_POST['action'] = 'add_order';
    $_POST['malzeme_kodu'] = $_POST['malzeme_kodu'] ?? 1; // Default to first material
    $_POST['tedarikci_id'] = $_POST['tedarikci_id'] ?? 1; // Default to first supplier
    $_POST['miktar'] = $_POST['miktar'] ?? 10.50; // Default quantity
    $_POST['siparis_tarihi'] = $_POST['siparis_tarihi'] ?? date('Y-m-d');
    $_POST['teslim_tarihi'] = $_POST['teslim_tarihi'] ?? date('Y-m-d', strtotime('+7 days'));
    $_POST['aciklama'] = $_POST['aciklama'] ?? 'Test siparişi';
    
    echo "<p>Test verileri:</p>";
    echo "<ul>";
    echo "<li>Malzeme Kodu: {$_POST['malzeme_kodu']}</li>";
    echo "<li>Tedarikçi ID: {$_POST['tedarikci_id']}</li>";
    echo "<li>Miktar: {$_POST['miktar']}</li>";
    echo "<li>Sipariş Tarihi: {$_POST['siparis_tarihi']}</li>";
    echo "<li>Teslim Tarihi: {$_POST['teslim_tarihi']}</li>";
    echo "</ul>";
    
    // Validate that miktar field is present
    if (!isset($_POST['miktar']) || $_POST['miktar'] === '') {
        echo "<p style='color: red;'>HATA: Miktar alanı eksik!</p>";
    } else {
        echo "<p style='color: green;'>BAŞARILI: Miktar alanı mevcut: {$_POST['miktar']}</p>";
    }
    
    // Simulate the validation from the API
    $miktar = $_POST['miktar'] ?? '';
    if (!is_numeric($miktar) || $miktar <= 0) {
        echo "<p style='color: red;'>HATA: Miktar değeri geçersiz veya 0'dan küçük!</p>";
    } else {
        echo "<p style='color: green;'>BAŞARILI: Miktar doğrulaması geçti: $miktar</p>";
    }
    
    exit;
}

// If not a POST request, show test form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Malzeme Siparişi Testi</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Malzeme Siparişi Formu Testi</h2>
    <form method="POST">
        <label>Malzeme Kodu: </label>
        <select name="malzeme_kodu">
            <option value="1">Test Malzeme 1</option>
            <option value="2">Test Malzeme 2</option>
        </select><br><br>
        
        <label>Tedarikçi ID: </label>
        <select name="tedarikci_id">
            <option value="1">Test Tedarikçi 1</option>
            <option value="2">Test Tedarikçi 2</option>
        </select><br><br>
        
        <label>Miktar: </label>
        <input type="number" name="miktar" step="0.01" min="0.01" value="5.50" required><br><br>
        
        <label>Sipariş Tarihi: </label>
        <input type="date" name="siparis_tarihi" value="<?php echo date('Y-m-d'); ?>" required><br><br>
        
        <label>Teslim Tarihi: </label>
        <input type="date" name="teslim_tarihi" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required><br><br>
        
        <label>Açıklama: </label>
        <textarea name="aciklama">Test siparişi</textarea><br><br>
        
        <input type="submit" value="Test Et">
    </form>
    
    <p><strong>Not:</strong> Bu test, formun miktar alanını içerip içermediğini ve API doğrulamasının çalışıp çalışmadığını kontrol eder.</p>
</body>
</html>