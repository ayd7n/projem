<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Döviz Kurları - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
        }
        .navbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            box-shadow: var(--shadow);
        }
        .navbar-brand {
            color: var(--accent, #d4af37) !important;
            font-weight: 700;
        }
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: white;
        }
        .dropdown-menu {
            border-radius: 0.5rem;
            border: none;
            box-shadow: var(--shadow);
        }
        .dropdown-item {
            color: var(--text-primary);
        }
        .dropdown-item:hover {
            background-color: var(--bg-color);
            color: var(--primary);
        }
        .main-container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 15px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 700;
            font-size: 2.25rem;
        }
        .page-header .text-muted {
            font-size: 1.1rem;
        }
        .card-header .btn {
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            .page-header .text-muted {
                font-size: 1rem;
            }
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .card-header h5 {
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ayarlar.php">Ayarlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1><i class="fas fa-dollar-sign"></i> Döviz Kurları</h1>
            <p class="text-muted">Maliyet hesaplamalarında kullanılacak döviz kurlarını güncelleyin.</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Bilgilendirme</h4>
            <p class="mb-0">Burada belirleyeceğiniz Dolar (USD) ve Euro (EUR) kurları, sistemdeki tüm ürün ve esansların teorik maliyet hesaplamalarında kullanılacaktır. Malzeme alış fiyatları farklı döviz birimlerinde girildiğinde, bu kurlar üzerinden Türk Lirası (₺) karşılığı hesaplanarak maliyetlere yansıtılır. Kurları düzenli olarak güncelleyerek maliyet verilerinizin doğruluğunu sağlayabilirsiniz.</p>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kurları Güncelle</h5>
                <button type="button" id="fetchRatesBtn" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Kurları Otomatik Çek</button>
            </div>
            <div class="card-body">
                <form id="dovizForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="dolar_kuru">Dolar Kuru (USD)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.0001" class="form-control" id="dolar_kuru" placeholder="Örn: 30.50">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="euro_kuru">Euro Kuru (EUR)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">€</span>
                                </div>
                                <input type="number" step="0.0001" class="form-control" id="euro_kuru" placeholder="Örn: 32.75">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kurları Kaydet</button>
                    <a href="ayarlar.php" class="btn btn-secondary ml-2"><i class="fas fa-arrow-left"></i> Ayarlara Geri Dön</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        // Function to display alerts
        function showAlert(message, type) {
            $('#alert-placeholder').html(
                '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '</div>'
            );
        }

        // Load settings on page load
        function loadSettings() {
            $.ajax({
                url: 'api_islemleri/ayarlar_islemler.php?action=get_settings',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#dolar_kuru').val(response.data.dolar_kuru);
                        $('#euro_kuru').val(response.data.euro_kuru);
                    } else {
                        showAlert(response.message || 'Ayarlar yüklenemedi.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Ayarlar yüklenirken bir sunucu hatası oluştu.', 'danger');
                }
            });
        }

        // Handle form submission to save rates
        $('#dovizForm').on('submit', function(e) {
            e.preventDefault();
            var submitButton = $(this).find('button[type="submit"]');
            submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...');

            $.ajax({
                url: 'api_islemleri/ayarlar_islemler.php',
                type: 'POST',
                data: {
                    action: 'update_settings',
                    dolar_kuru: $('#dolar_kuru').val(),
                    euro_kuru: $('#euro_kuru').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message || 'Ayarlar güncellenemedi.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Ayarlar güncellenirken bir sunucu hatası oluştu.', 'danger');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Kurları Kaydet');
                }
            });
        });

        // Handle automatic rate fetching
        $('#fetchRatesBtn').on('click', function() {
            var fetchButton = $(this);
            fetchButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Çekiliyor...');

            // Fetch Dolar rate
            var usdPromise = $.ajax({
                url: 'https://api.frankfurter.app/latest?from=USD&to=TRY',
                type: 'GET',
                dataType: 'json'
            });

            // Fetch Euro rate
            var eurPromise = $.ajax({
                url: 'https://api.frankfurter.app/latest?from=EUR&to=TRY',
                type: 'GET',
                dataType: 'json'
            });

            $.when(usdPromise, eurPromise).done(function(usdResponse, eurResponse) {
                var dolarKuru = usdResponse[0].rates.TRY;
                var euroKuru = eurResponse[0].rates.TRY;

                if (dolarKuru && euroKuru) {
                    $('#dolar_kuru').val(dolarKuru.toFixed(4));
                    $('#euro_kuru').val(euroKuru.toFixed(4));
                    showAlert('Kurlar başarıyla çekildi. Kaydetmeyi unutmayın.', 'success');
                } else {
                    showAlert('Kurlar çekilirken bir hata oluştu.', 'danger');
                }
            }).fail(function() {
                showAlert('Kur API\'sine bağlanırken bir hata oluştu.', 'danger');
            }).always(function() {
                fetchButton.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Kurları Otomatik Çek');
            });
        });

        // Load settings when the document is ready
        loadSettings();
    });
    </script>
</body>
</html>