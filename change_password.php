<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate new password match
    if ($new_password !== $confirm_password) {
        $error = "Yeni şifreler uyuşmuyor.";
    } elseif (strlen($new_password) < 6) {
        $error = "Yeni şifre en az 6 karakter uzunluğunda olmalıdır.";
    } else {
        // Determine if user is customer or staff
        if ($_SESSION['taraf'] === 'musteri') {
            // Customer password check and update
            $query = "SELECT sistem_sifresi FROM musteriler WHERE musteri_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($current_password, $user['sistem_sifresi'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE musteriler SET sistem_sifresi = ? WHERE musteri_id = ?";
                $update_stmt = $connection->prepare($update_query);
                $update_stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    $message = "Şifreniz başarıyla değiştirildi.";
                } else {
                    $error = "Şifre değiştirme işlemi sırasında hata oluştu.";
                }
                $update_stmt->close();
            } else {
                $error = "Mevcut şifre hatalı.";
            }
            $stmt->close();
        } else {
            // Staff password check and update
            $query = "SELECT sistem_sifresi FROM personeller WHERE personel_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($current_password, $user['sistem_sifresi'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE personeller SET sistem_sifresi = ? WHERE personel_id = ?";
                $update_stmt = $connection->prepare($update_query);
                $update_stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    $message = "Şifreniz başarıyla değiştirildi.";
                } else {
                    $error = "Şifre değiştirme işlemi sırasında hata oluştu.";
                }
                $update_stmt->close();
            } else {
                $error = "Mevcut şifre hatalı.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Değiştir - Parfüm ERP Sistemi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .logout {
            display: block;
            text-align: center;
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .logout:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Şifremi Değiştir</h2>
            <p>Hesabınızın şifresini güncelleyin</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="current_password">Mevcut Şifre:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Yeni Şifre:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Yeni Şifre (Tekrar):</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Şifreyi Güncelle</button>
        </form>
        
        <a href="<?php echo $_SESSION['taraf'] === 'musteri' ? 'customer_panel.php' : 'navigation.php'; ?>" class="logout">Geri Dön</a>
    </div>
</body>
</html>