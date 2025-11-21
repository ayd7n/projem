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
    <title>Excel'e Aktar - Parfüm ERP</title>
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
        .main-container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 15px;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 700;
        }
        .table-list .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-list .badge {
            font-size: 0.8rem;
        }
        #searchInput {
            margin-bottom: 1rem;
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 576px) {
            .table-list .list-group-item {
                flex-direction: column;
                align-items: stretch;
            }
            .table-list .list-group-item > div:first-child {
                word-wrap: break-word;
                margin-bottom: 0.5rem;
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
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="ayarlar.php">Ayarlar</a></li>
                    <li class="nav-item"><a class="nav-link" href="change_password.php">Parolamı Değiştir</a></li>
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
            <h1><i class="fas fa-file-excel"></i> Excel'e Aktar</h1>
            <p class="text-muted">Veritabanı tablolarını veya görünümlerini CSV formatında dışa aktarın.</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tablolar ve Görünümler</h5>
            </div>
            <div class="card-body">
                <input type="text" id="searchInput" class="form-control" placeholder="Tablo veya görünüm ara...">
                <ul id="tableList" class="list-group list-group-flush table-list">
                    <li class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        function showAlert(message, type) {
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button></div>`;
            $('#alert-placeholder').html(alertHtml);
        }

        function loadTables() {
            const tableList = $('#tableList');
            tableList.html('<li class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</li>');

            $.ajax({
                url: 'api_islemleri/excel_islemler.php?action=list_tables',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    tableList.empty();
                    if (response.status === 'success' && response.tables.length > 0) {
                        response.tables.forEach(function(item) {
                            const badgeClass = item.type === 'View' ? 'badge-info' : 'badge-secondary';
                            const icon = item.type === 'View' ? 'fa-eye' : 'fa-table';
                            const listItem = `
                                <li class="list-group-item" data-name="${item.name.toLowerCase()}">
                                    <div>
                                        <i class="fas ${icon} text-muted mr-2"></i>
                                        <strong>${item.name}</strong>
                                        <span class="badge ${badgeClass} ml-2">${item.type}</span>
                                    </div>
                                    <a href="api_islemleri/excel_islemler.php?action=export_table&name=${item.name}" class="btn btn-sm btn-success">
                                        <i class="fas fa-download"></i> Aktar
                                    </a>
                                </li>`;
                            tableList.append(listItem);
                        });
                    } else if (response.status === 'success') {
                        tableList.html('<li class="list-group-item text-center">Hiç tablo veya görünüm bulunamadı.</li>');
                    } else {
                        showAlert(response.message || 'Tablolar listelenemedi.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Tablo listesi alınırken bir sunucu hatası oluştu.', 'danger');
                }
            });
        }

        $('#searchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $("#tableList li").filter(function() {
                $(this).toggle($(this).data('name').indexOf(value) > -1)
            });
        });

        loadTables();
    });
    </script>
</body>
</html>
