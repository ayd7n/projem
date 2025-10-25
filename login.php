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
                $_SESSION['rol'] = 'personel';
                
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
    <title>Parfüm ERP Sistemi - Giriş</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color-start: #f4eef2;
            --bg-color-end: #dcd3e2;
            --form-bg-color: rgba(255, 255, 255, 0.85);
            --accent-color: #c0a0c3; /* Dusty lavender */
            --accent-gold: #d4af37;
            --text-color: #333;
            --shadow-color: rgba(100, 80, 120, 0.15);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, var(--bg-color-start) 0%, var(--bg-color-end) 100%);
            overflow: hidden;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: var(--form-bg-color);
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 15px 35px var(--shadow-color);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .perfume-icon {
            width: 45px;
            height: 45px;
            margin-bottom: 15px;
            opacity: 0.6;
            color: var(--accent-color);
        }

        .login-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-color);
            font-size: 2.8rem;
            font-weight: 700;
            margin: 0 0 10px;
        }

        .login-header p {
            font-family: 'Cormorant Garamond', serif;
            color: #555;
            font-size: 1.3rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #444;
            font-weight: 400;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.7);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(192, 160, 195, 0.4);
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(192, 160, 195, 0.5);
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #b18db4;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(192, 160, 195, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 30px 25px;
                margin: 0 15px;
                width: calc(100% - 30px);
            }

            .login-header h2 {
                font-size: 2.4rem;
                margin: 0 0 10px;
            }
            
            .login-header p {
                font-size: 1.2rem;
                margin-bottom: 25px;
            }
            
            .form-group input {
                padding: 12px 16px;
                font-size: 1rem;
            }
            
            .btn {
                padding: 14px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .login-container {
                padding: 25px 20px;
                margin: 0 10px;
            }

            .login-header h2 {
                font-size: 2rem;
            }
            
            .login-header p {
                font-size: 1.1rem;
                margin-bottom: 20px;
            }
            
            .perfume-icon {
                width: 40px;
                height: 40px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group input {
                padding: 12px 15px;
            }
            
            .btn {
                padding: 13px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 360px) {
            .login-container {
                padding: 20px 15px;
                margin: 0 8px;
            }

            .login-header h2 {
                font-size: 1.8rem;
            }
            
            .login-header p {
                font-size: 1rem;
            }
            
            .perfume-icon {
                width: 35px;
                height: 35px;
            }
            
            .form-group input {
                padding: 10px 12px;
            }
            
            .btn {
                padding: 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <svg class="perfume-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 1.65.59 3.19 1.58 4.42L5 22h14l-1.58-8.58C18.41 12.19 19 10.65 19 9c0-3.87-3.13-7-7-7zm0 2c2.76 0 5 2.24 5 5s-2.24 5-5 5-5-2.24-5-5 2.24-5 5-5zM9 9c0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3-3 1.34-3 3z"/></svg>
        <div class="login-header">
            <h2>IDO KOZMETIK</h2>
            <p>Giriş Yap</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">E-posta veya Telefon</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn">Giriş</button>
        </form>
    </div>
</body>
</html>
