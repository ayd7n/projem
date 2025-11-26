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

// Page-level permission check
if (!yetkisi_var('page:view:raporlar')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem İşlem Logları - Parfüm ERP</title>
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
        .main-content {
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 700;
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
        .table-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        .filter-section {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        .pagination-container {
            margin-top: 1.5rem;
        }
        .table th {
            background-color: var(--primary);
            color: white;
        }
        .table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table td:nth-child(4) { /* Log metni sütunu */
            max-width: 400px;
            font-size: 0.875em; /* Small boyut */
        }
        .table td:not(:nth-child(4)) { /* Diğer sütunlar */
            max-width: 150px;
        }
        .table th {
            white-space: nowrap;
        }
        .badge-success { background-color: #28a745; }
        .badge-primary { background-color: #007bff; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
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

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-list-alt"></i> Sistem İşlem Logları</h1>
            <p class="text-muted">Sistemde yapılan tüm işlemleri analiz edin.</p>
        </div>

        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <label for="startDate">Başlangıç Tarihi:</label>
                    <input type="date" class="form-control" id="startDate" name="startDate">
                </div>
                <div class="col-md-3">
                    <label for="endDate">Bitiş Tarihi:</label>
                    <input type="date" class="form-control" id="endDate" name="endDate">
                </div>
                <div class="col-md-3">
                    <label for="islemTuru">İşlem Türü:</label>
                    <select class="form-control" id="islemTuru" name="islemTuru">
                        <option value="">Tümü</option>
                        <option value="CREATE">Oluşturuldu</option>
                        <option value="UPDATE">Güncellendi</option>
                        <option value="DELETE">Silindi</option>
                        <option value="LOGIN">Giriş</option>
                        <option value="LOGOUT">Çıkış</option>
                        <option value="OTHER">Diğer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="searchInput">Arama:</label>
                    <input type="text" class="form-control" id="searchInput" name="searchInput" placeholder="Kullanıcı adı veya log metni...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-right">
                    <button class="btn btn-primary" id="applyFilters"><i class="fas fa-filter"></i> Filtreleri Uygula</button>
                    <button class="btn btn-secondary" id="resetFilters"><i class="fas fa-redo"></i> Sıfırla</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Tarih</th>
                            <th>Kullanıcı Adı</th>
                            <th>İşlem Türü</th>
                            <th>Log Metni</th>
                        </tr>
                    </thead>
                    <tbody id="logResults">
                        <tr>
                            <td colspan="4" class="text-center">Veriler yükleniyor...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Log pagination" class="pagination-container">
                <ul class="pagination justify-content-center" id="logPagination">
                    <!-- Pagination will be loaded here -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Load logs on page load
        loadLogs(1);

        // Event handlers for filters
        $('#applyFilters').on('click', function() {
            loadLogs(1); // Reset to first page when applying filters
        });

        $('#resetFilters').on('click', function() {
            $('#startDate').val('');
            $('#endDate').val('');
            $('#islemTuru').val('');
            $('#searchInput').val('');
            loadLogs(1); // Reload first page with no filters
        });

        // Also trigger load when Enter is pressed in search
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                loadLogs(1);
            }
        });

        // Handle pagination clicks
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                loadLogs(page);
            }
        });
    });

    function loadLogs(page) {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var islemTuru = $('#islemTuru').val();
        var search = $('#searchInput').val();

        $.ajax({
            url: 'api_islemleri/log_raporu_islemler.php',
            type: 'GET',
            data: {
                action: 'get_logs',
                page: page,
                limit: 10,
                startDate: startDate,
                endDate: endDate,
                islemTuru: islemTuru,
                search: search
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    displayLogs(response.data, response.pagination);
                } else {
                    $('#logResults').html('<tr><td colspan="4">Hata: ' + response.message + '</td></tr>');
                }
            },
            error: function() {
                $('#logResults').html('<tr><td colspan="4">Veriler yüklenirken bir hata oluştu.</td></tr>');
            }
        });
    }

    function displayLogs(logs, pagination) {
        var tbody = $('#logResults');
        tbody.empty();

        if (logs.length === 0) {
            tbody.html('<tr><td colspan="4">Herhangi bir log bulunamadı.</td></tr>');
            $('#logPagination').empty();
            return;
        }

        $.each(logs, function(index, log) {
            var row = '<tr>' +
                        '<td>' + formatDateTime(log.tarih) + '</td>' +
                        '<td>' + escapeHtml(log.kullanici_adi) + '</td>' +
                        '<td>' + formatIslemTuru(log.islem_turu) + '</td>' +
                        '<td>' + escapeHtml(log.log_metni) + '</td>' +
                      '</tr>';
            tbody.append(row);
        });

        // Generate pagination
        generatePagination(pagination);
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleString('tr-TR');
    }

    function formatIslemTuru(islemTuru) {
        var icon = '';
        var text = '';
        var badgeClass = 'badge-secondary'; // Default

        switch(islemTuru) {
            case 'CREATE':
                icon = '<i class="fas fa-plus-circle"></i> ';
                text = 'Oluşturuldu';
                badgeClass = 'badge-success';
                break;
            case 'UPDATE':
                icon = '<i class="fas fa-edit"></i> ';
                text = 'Güncellendi';
                badgeClass = 'badge-primary';
                break;
            case 'DELETE':
                icon = '<i class="fas fa-trash-alt"></i> ';
                text = 'Silindi';
                badgeClass = 'badge-danger';
                break;
            case 'LOGIN':
            case 'Giriş Yapıldı': // Handle both keys
                icon = '<i class="fas fa-sign-in-alt"></i> ';
                text = 'Giriş Yapıldı';
                badgeClass = 'badge-success';
                break;
            case 'LOGOUT':
            case 'Çıkış Yapıldı': // Handle both keys
                icon = '<i class="fas fa-sign-out-alt"></i> ';
                text = 'Çıkış Yapıldı';
                badgeClass = 'badge-danger';
                break;
            case 'OTHER':
            case '': // Handle empty string as OTHER
                icon = '<i class="fas fa-info-circle"></i> ';
                text = 'Diğer';
                badgeClass = 'badge-secondary';
                break;
            default:
                icon = '<i class="fas fa-info-circle"></i> ';
                text = escapeHtml(islemTuru);
                badgeClass = 'badge-secondary';
        }
        return '<span class="badge ' + badgeClass + '">' + icon + text + '</span>';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function generatePagination(pagination) {
        var paginationElement = $('#logPagination');
        paginationElement.empty();

        if (pagination.total_pages <= 1) {
            return;
        }

        var currentPage = pagination.current_page;
        var totalPages = pagination.total_pages;

        // Previous button
        var prevDisabled = currentPage <= 1 ? 'disabled' : '';
        var prevPage = currentPage > 1 ? currentPage - 1 : 1;
        var prevHtml = '<li class="page-item ' + prevDisabled + '">' +
                       '<a class="page-link" href="#" data-page="' + prevPage + '">Önceki</a></li>';
        paginationElement.append(prevHtml);

        // Page numbers
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            var firstPageHtml = '<li class="page-item">' +
                                '<a class="page-link" href="#" data-page="1">1</a></li>';
            paginationElement.append(firstPageHtml);
            
            if (startPage > 2) {
                paginationElement.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }

        for (var i = startPage; i <= endPage; i++) {
            var activeClass = i === currentPage ? 'active' : '';
            var pageHtml = '<li class="page-item ' + activeClass + '">' +
                           '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            paginationElement.append(pageHtml);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationElement.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
            
            var lastPageHtml = '<li class="page-item">' +
                               '<a class="page-link" href="#" data-page="' + totalPages + '">' + totalPages + '</a></li>';
            paginationElement.append(lastPageHtml);
        }

        // Next button
        var nextDisabled = currentPage >= totalPages ? 'disabled' : '';
        var nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        var nextHtml = '<li class="page-item ' + nextDisabled + '">' +
                       '<a class="page-link" href="#" data-page="' + nextPage + '">Sonraki</a></li>';
        paginationElement.append(nextHtml);
    }
    </script>
</body>
</html>