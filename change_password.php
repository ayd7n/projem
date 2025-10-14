<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$user_type = $_SESSION['taraf']; // 'musteri' or 'personel'
$user_name = $_SESSION['kullanici_adi'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Parfüm ERP Sistemi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #fdf8f5; /* Soft cream background */
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .main-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background-color: #ffffff;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .form-container {
            flex: 1.2;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info-container {
            flex: 1;
            background-color: #4a0e63; /* Deep purple */
            color: #ffffff;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M12.5,100 C5.596,100 0,94.404 0,87.5 C0,83.547 1.57,79.991 4.209,77.519 L77.519,4.209 C79.991,1.57 83.547,0 87.5,0 C94.404,0 100,5.596 100,12.5 C100,16.453 98.43,20.009 95.791,22.481 L22.481,95.791 C20.009,98.43 16.453,100 12.5,100 Z M87.5,25 C80.596,25 75,19.404 75,12.5 C75,5.596 80.596,0 87.5,0 C94.404,0 100,5.596 100,12.5 C100,19.404 94.404,25 87.5,25 Z M12.5,100 C5.596,100 0,94.404 0,87.5 C0,80.596 5.596,75 12.5,75 C19.404,75 25,80.596 25,87.5 C25,94.404 19.404,100 12.5,100 Z" fill="%23fff" fill-opacity="0.05"/></svg>');
        }
        .info-container h2 {
            font-family: 'Ubuntu', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }
        .info-container p, .info-container li {
            font-size: 1rem;
            line-height: 1.7;
            opacity: 0.8;
        }
        .info-container ul {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        .info-container li {
            margin-bottom: 1rem;
        }
        .info-container li strong {
            color: #d4af37; /* Gold color */
            font-weight: 700;
        }
        h1 {
            font-family: 'Ubuntu', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-label {
            display: block;
            font-weight: 700;
            color: #4a0e63;
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem; /* Padding for icon */
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        .form-group .fa {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(calc(-50% + 10px));
            color: #9ca3af;
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, #4a0e63, #7c2a99);
            color: #ffffff;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 14, 99, 0.2);
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 5px solid;
        }
        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            border-color: #48bb78;
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-color: #f56565;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: #4a0e63;
            text-decoration: none;
            font-weight: 700;
        }
        .back-link:hover {
            color: #d4af37;
        }
        .password-strength {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .strength-meter {
            flex-grow: 1;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
            border-radius: 3px;
        }
        .strength-text {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 700;
        }
        .strength-weak { background: #f56565; width: 33.33%; }
        .strength-medium { background: #ed8936; width: 66.66%; }
        .strength-strong { background: #48bb78; width: 100%; }

        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
                width: 90%;
                margin: 2rem auto;
                box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            }
            .form-container, .info-container {
                padding: 2.5rem;
                flex: 1;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 0; /* Remove padding on smaller mobile devices */
            }
            .form-container, .info-container {
                padding: 1.5rem; /* Reduce padding inside containers on mobile */
            }
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="form-container">
            <h1>Şifrenizi Yenileyin</h1>
            <p class="subtitle">Merhaba <?php echo htmlspecialchars($user_name); ?>, hesabınızın güvenliği bizim için önemli.</p>

            <div id="alert-placeholder"></div>

            <form id="passwordForm">
                <input type="hidden" id="user_type" value="<?php echo $user_type; ?>">
                <div class="form-group">
                    <label for="current_password" class="form-label">Mevcut Şifre</label>
                    <i class="fa fa-lock form-icon"></i>
                    <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="new_password" class="form-label">Yeni Şifre</label>
                    <i class="fa fa-key form-icon"></i>
                    <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Yeni şifrenizi girin">
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                    <i class="fa fa-check-circle form-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Yeni şifrenizi doğrulayın">
                </div>
                <button type="submit" class="btn"><i class="fa fa-save"></i> Şifreyi Güncelle</button>
            </form>
            <a href="<?php echo $user_type === 'musteri' ? 'customer_panel.php' : 'navigation.php'; ?>" class="back-link">Panele Geri Dön</a>
        </div>
        <div class="info-container">
            <h2><i class="fa-solid fa-shield-halved"></i> Güvenliğin Zarafeti</h2>
            <p>Tıpkı en nadide parfümler gibi, dijital kimliğiniz de özenle korunmalıdır. Güçlü bir şifre, bu korumanın en temel notasıdır.</p>
            <ul>
                <li><strong>Eşsiz Formül:</strong> Her platform için farklı ve tahmin edilemez şifreler oluşturun.</li>
                <li><strong>Karmaşık Notalar:</strong> Büyük/küçük harf, rakam ve sembolleri bir arada kullanarak şifrenizin gücünü artırın.</li>
                <li><strong>Gizli Hazine:</strong> Şifreniz size özeldir. Kimseyle paylaşmayarak dijital hazinenizi koruyun.</li>
                <li><strong>Tazelenen Koku:</strong> Şifrenizi düzenli aralıklarla değiştirerek güvenliğinizi taze tutun.</li>
            </ul>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {

        function showAlert(message, type) {
            $('#alert-placeholder').html(
                `<div class="alert alert-${type}">${message}</div>`
            ).hide().fadeIn(300);
        }

        $('#new_password').on('input', function() {
            var password = $(this).val();
            var strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            var strengthFill = $('#strengthFill');
            var strengthText = $('#strengthText');

            strengthFill.removeClass('strength-weak strength-medium strength-strong');

            if (password.length === 0) {
                strengthText.text('');
                 strengthFill.css('width', '0%');
            } else if (strength <= 2) {
                strengthFill.addClass('strength-weak');
                strengthText.text('Zayıf');
            } else if (strength <= 4) {
                strengthFill.addClass('strength-medium');
                strengthText.text('Orta');
            } else {
                strengthFill.addClass('strength-strong');
                strengthText.text('Güçlü');
            }
        });

        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();

            var currentPassword = $('#current_password').val();
            var newPassword = $('#new_password').val();
            var confirmPassword = $('#confirm_password').val();
            var userType = $('#user_type').val();

            if (newPassword !== confirmPassword) {
                showAlert('Yeni şifreler uyuşmuyor.', 'danger');
                return;
            }

            if (newPassword.length < 8) {
                showAlert('Yeni şifre en az 8 karakter uzunluğunda olmalıdır.', 'danger');
                return;
            }

            var submitButton = $(this).find('.btn');
            submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Güncelleniyor...');

            $.ajax({
                url: 'api_islemleri/change_password_islemler.php',
                type: 'POST',
                data: {
                    current_password: currentPassword,
                    new_password: newPassword,
                    user_type: userType
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        $('#passwordForm')[0].reset();
                        $('#strengthFill').removeClass('strength-weak strength-medium strength-strong').css('width', '0');
                        $('#strengthText').text('');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Şifre değiştirme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html('<i class="fa fa-save"></i> Şifreyi Güncelle');
                }
            });
        });
    });
    </script>

</body>
</html>
