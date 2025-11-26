<?php
include 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['taraf'] === 'musteri') {
        header('Location: customer_panel.php');
    } else {
        header('Location: navigation.php');
    }
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Check for fake/honeypot credentials (hardcoded, not in database)
    if ($username === 'giris@sistem.com' && $password === '758236') {
        // Redirect to fake error page to simulate broken system
        header('Location: dashboard.php');
        exit;
    }
    
    // First, check if it's a customer login
    $customer_query = "SELECT musteri_id as user_id, 'musteri' as taraf, musteri_adi as kullanici_adi, sistem_sifresi as sifre, giris_yetkisi 
                       FROM musteriler 
                       WHERE (e_posta = ? OR telefon = ?)";
    
    $customer_stmt = $connection->prepare($customer_query);
    $customer_stmt->bind_param('ss', $username, $username);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();
    
    if ($customer_result->num_rows > 0) {
        $customer = $customer_result->fetch_assoc();
        
        if (password_verify($password, $customer['sifre'])) {
            // Check if customer has login permission
            if ($customer['giris_yetkisi'] != 1) {
                $error_message = 'Giriş yetkiniz yok!';
            } else {
                $_SESSION['user_id'] = $customer['user_id'];
                $_SESSION['taraf'] = $customer['taraf'];
                $_SESSION['id'] = $customer['user_id'];
                $_SESSION['kullanici_adi'] = $customer['kullanici_adi'];
                $_SESSION['rol'] = 'musteri';
                
                header('Location: customer_panel.php');
                exit;
            }
        } else {
            $error_message = 'Hatalı şifre!';
        }
    } else {
        // Check if it's a staff login
        $staff_query = "SELECT personel_id as user_id, 'personel' as taraf, ad_soyad as kullanici_adi, sistem_sifresi as sifre 
                       FROM personeller 
                       WHERE (e_posta = ? OR telefon = ?)";
        
        $staff_stmt = $connection->prepare($staff_query);
        $staff_stmt->bind_param('ss', $username, $username);
        $staff_stmt->execute();
        $staff_result = $staff_stmt->get_result();
        
        if ($staff_result->num_rows > 0) {
            $staff = $staff_result->fetch_assoc();
            
            if (password_verify($password, $staff['sifre'])) {
                $_SESSION['user_id'] = $staff['user_id'];
                $_SESSION['taraf'] = $staff['taraf'];
                $_SESSION['id'] = $staff['user_id'];
                $_SESSION['kullanici_adi'] = $staff['kullanici_adi'];
                $_SESSION['email'] = $username; // Store email for admin check
                $_SESSION['rol'] = 'personel';
                $_SESSION['izinler'] = []; // Default to empty array

                // Set a flag for the admin user
                if ($username === 'admin@parfum.com') {
                    $_SESSION['is_admin'] = true;
                } else {
                    // For non-admin staff, load their specific permissions
                    $_SESSION['is_admin'] = false;
                    $izin_stmt = $connection->prepare("SELECT izin_anahtari FROM personel_izinleri WHERE personel_id = ?");
                    $izin_stmt->bind_param('i', $staff['user_id']);
                    $izin_stmt->execute();
                    $izin_result = $izin_stmt->get_result();
                    $izinler = [];
                    while ($row = $izin_result->fetch_assoc()) {
                        $izinler[] = $row['izin_anahtari'];
                    }
                    $_SESSION['izinler'] = $izinler;
                    $izin_stmt->close();
                }
                
                header('Location: navigation.php');
                exit;
            } else {
                $error_message = 'Hatalı şifre!';
            }
        } else {
            $error_message = 'Kullanıcı bulunamadı!';
        }
        
        $staff_stmt->close();
    }
    
    $customer_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDO KOZMETIK - ERP Giriş</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-image-side">
            <!-- The background image is set in CSS -->
        </div>
        <div class="login-form-side">
            <div class="login-header">
                <svg class="perfume-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 1.65.59 3.19 1.58 4.42L5 22h14l-1.58-8.58C18.41 12.19 19 10.65 19 9c0-3.87-3.13-7-7-7zm0 2c2.76 0 5 2.24 5 5s-2.24 5-5 5-5-2.24-5-5 2.24-5 5-5zM9 9c0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3-3 1.34-3 3z"/>
                </svg>
                <h2>IDO KOZMETIK</h2>
                <p>ERP Sistemine Giriş</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">E-posta veya Telefon</label>
                    <input type="text" id="username" name="username" required autocomplete="username" placeholder="ornek@mail.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="********">
                </div>
                
                <button type="submit" class="btn">Giriş Yap</button>
            </form>
        </div>
    </div>
    <!-- The old canvas and script are removed. If there was any other logic in login.js, it's gone.
         This new design doesn't require JS for its core functionality. -->
</body>
</html>
