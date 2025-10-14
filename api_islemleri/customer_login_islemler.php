<?php
include '../config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Zaten giriş yapmışsınız!']);
    exit;
}

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
                echo json_encode(['status' => 'error', 'message' => 'Giriş yetkiniz yok!']);
            } else {
                $_SESSION['user_id'] = $customer['user_id'];
                $_SESSION['taraf'] = $customer['taraf'];
                $_SESSION['id'] = $customer['user_id'];
                $_SESSION['kullanici_adi'] = $customer['kullanici_adi'];
                $_SESSION['rol'] = 'musteri';
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Giriş başarılı! Yönlendiriliyorsunuz...', 
                    'redirect_url' => '../customer_panel.php'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hatalı şifre!']);
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
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Giriş başarılı! Yönlendiriliyorsunuz...', 
                    'redirect_url' => '../navigation.php'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Hatalı şifre!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı!']);
        }
        
        $staff_stmt->close();
    }
    
    $customer_stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek!']);
}