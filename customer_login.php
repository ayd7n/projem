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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Girişi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #1abc9c;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --bg-color: #f5f7fb;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #2c3e50;
            --text-secondary: #8492a6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            max-width: 500px;
            width: 100%;
            padding: 30px;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            padding: 30px;
            text-align: center;
        }
        .login-header {
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
        }
        .login-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary);
            font-size: 1.5rem;
        }
        .login-footer {
            margin-top: 20px;
            text-align: center;
        }
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="icon-container">
                <i class="fas fa-user"></i>
            </div>
            
            <div class="login-header">
                <h1>Müşteri Girişi</h1>
                <p>Lütfen giriş bilgilerinizi girin</p>
            </div>

            <div id="alert-placeholder"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="username">E-posta veya Telefon</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>
            
            <div class="login-footer">
                <a href="javascript:history.back()"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        
        function showAlert(message, type) {
            $('#alert-placeholder').html(
                `<div class="alert alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    ${message}
                </div>`
            );
            
            // Auto-hide success messages after 5 seconds
            if(type === 'success') {
                setTimeout(function() {
                    $('#alert-placeholder').html('');
                }, 5000);
            }
        }

        // Handle form submission via AJAX
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var loginBtn = $('#loginBtn');
            
            // Disable button and show loading state
            loginBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Giriş Yapılıyor...');
            
            $.ajax({
                url: 'api_islemleri/customer_login_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        // Redirect after a short delay
                        setTimeout(function() {
                            window.location.href = response.redirect_url;
                        }, 1500);
                    } else {
                        showAlert(response.message, 'danger');
                        // Re-enable button
                        loginBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Giriş Yap');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                    // Re-enable button
                    loginBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Giriş Yap');
                }
            });
        });
    });
    </script>
</body>
</html>