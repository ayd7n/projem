<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$user_type = $_POST['user_type'] ?? '';

if (empty($current_password) || empty($new_password) || empty($user_type)) {
    echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Yeni şifre en az 6 karakter uzunluğunda olmalıdır.']);
    exit;
}

// Determine table and field based on user type
if ($user_type === 'musteri') {
    $table = 'musteriler';
    $id_field = 'musteri_id';
} else {
    $table = 'personeller';
    $id_field = 'personel_id';
}

// Get current password from database
$query = "SELECT sistem_sifresi FROM $table WHERE $id_field = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
    exit;
}

$user = $result->fetch_assoc();

// Verify current password
if (!password_verify($current_password, $user['sistem_sifresi'])) {
    echo json_encode(['status' => 'error', 'message' => 'Mevcut şifre hatalı.']);
    exit;
}

// Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_query = "UPDATE $table SET sistem_sifresi = ? WHERE $id_field = ?";
$update_stmt = $connection->prepare($update_query);
$update_stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);

if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Şifreniz başarıyla değiştirildi.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Şifre değiştirme işlemi sırasında hata oluştu.']);
}

$update_stmt->close();
?>
