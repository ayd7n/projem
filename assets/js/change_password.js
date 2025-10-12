
$(document).ready(function() {

    function showAlert(message, type) {
        const alertPlaceholder = document.getElementById('alert-placeholder');
        alertPlaceholder.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;
    }

    // Password strength checker
    $('#new_password').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        const strengthFill = $('#strengthFill');
        const strengthText = $('#strengthText');

        strengthFill.removeClass('strength-weak strength-medium strength-strong');

        if (strength <= 2) {
            strengthFill.addClass('strength-weak');
            strengthText.text('Şifre gücü: Zayıf');
        } else if (strength <= 4) {
            strengthFill.addClass('strength-medium');
            strengthText.text('Şifre gücü: Orta');
        } else {
            strengthFill.addClass('strength-strong');
            strengthText.text('Şifre gücü: Güçlü');
        }
    });

    // Form submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();

        const currentPassword = $('#current_password').val();
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        const userType = $('#user_type').val();
        const submitButton = $(this).find('button[type="submit"]');

        if (newPassword !== confirmPassword) {
            showAlert('Yeni şifreler uyuşmuyor.', 'danger');
            return;
        }

        if (newPassword.length < 8) {
            showAlert('Yeni şifre en az 8 karakter uzunluğunda olmalıdır.', 'danger');
            return;
        }

        submitButton.prop('disabled', true).text('Güncelleniyor...');

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
                submitButton.prop('disabled', false).text('Şifreyi Güncelle');
            }
        });
    });
});
