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
    <title>Tablo Temizleme - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
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
            margin: 2rem 0;
            padding: 0 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 700;
        }
        .table-list-card {
            border: none;
            box-shadow: var(--shadow);
            border-radius: 15px;
        }
        .table-list-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-list-body {
            padding: 1rem;
            max-height: 600px;
            overflow-y: auto;
        }
        .table-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }
        .table-item {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: #fff;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        .table-item:hover {
            background-color: #f1f3f5;
            border-color: var(--primary);
        }
        .custom-control-label {
            font-size: 0.9rem;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-count {
            font-size: 0.75rem;
        }
        .modal-fullscreen {
            width: 100%;
            max-width: none;
            height: 100%;
            margin: 0;
        }
        .modal-fullscreen .modal-content {
            height: 100%;
            border: 0;
            border-radius: 0;
        }
        .modal-fullscreen .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 120px) !important; /* Adjust for header/footer */
        }
        #dataTable th, #dataTable td {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!--Navbar -->
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
            <h1><i class="fas fa-eraser"></i> Tablo Temizleme</h1>
            <p class="text-muted">Seçilen veritabanı tablolarındaki tüm verileri kalıcı olarak silin.</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="card table-list-card">
            <div class="table-list-header">
                <h5 class="mb-0">Tablolar</h5>
                <div class="d-flex align-items-center">
                    <div class="custom-control custom-switch mr-3">
                        <input type="checkbox" class="custom-control-input" id="filterEmptyTables">
                        <label class="custom-control-label" for="filterEmptyTables" style="white-space: nowrap; width: auto; font-size: 0.85rem;">Sadece Dolu Tablolar</label>
                    </div>
                    <input type="text" id="tableSearch" class="form-control form-control-sm mr-3" placeholder="Tablo ara..." style="width: 200px;">
                    <button class="btn btn-sm btn-outline-primary mr-2" id="selectAllBtn">Tümünü Seç</button>
                    <button class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Seçimi Kaldır</button>
                </div>
            </div>
            <div class="table-list-body">
                <form id="tableClearForm">
                    <div id="tableListContainer" class="table-grid">
                        <div class="text-center w-100"><i class="fas fa-spinner fa-spin"></i> Tablolar yükleniyor...</div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-right">
                <button type="button" class="btn btn-danger btn-lg" id="clearBtn" disabled>
                    <i class="fas fa-trash-alt"></i> Seçili Tabloları Temizle
                </button>
            </div>
        </div>
    </div>

    <!-- View Data Modal -->
    <div class="modal fade" id="viewDataModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDataModalTitle">Tablo Verileri</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Yenile
                    </button>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
            </div>
            <div class="modal-body p-0" id="viewDataContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
            </div>
        </div></div>
    </div>

    <!-- Warning Modal -->
    <div class="modal fade" id="warningModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> DİKKAT! Veri Kaybı Riski</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Seçtiğiniz <strong id="selectedCount">0</strong> tablodaki <strong>TÜM VERİLER SİLİNECEK</strong>.</p>
                <p>Bu işlem geri alınamaz. Lütfen devam etmeden önce yedek aldığınızdan emin olun.</p>
                <div class="alert alert-warning">
                    <small>Silinecek tablolar: <span id="selectedTablesList"></span></small>
                </div>
                <div class="form-group">
                    <label>Onaylamak için lütfen <strong>ONAY</strong> yazın:</label>
                    <input type="text" class="form-control" id="confirmInput" placeholder="ONAY">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="confirmClearBtn" disabled>Evet, Temizle</button>
            </div>
        </div></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
        function showAlert(message, type) {
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button></div>`;
            $('#alert-placeholder').html(alertHtml);
            window.scrollTo(0, 0);
        }

        function loadTables() {
            $.ajax({
                url: 'api_islemleri/tablo_temizle_islemler.php?action=list_tables',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let html = '';
                        response.tables.forEach(function(table) {
                            const isCritical = table.name === 'sistem_kullanicilari' || table.name === 'ayarlar';
                            const badgeClass = isCritical ? 'badge-danger' : 'badge-secondary';
                            const badgeText = isCritical ? 'Kritik' : (table.rows);
                            
                            html += `
                                <div class="table-item" data-rows="${table.rows}" data-name="${table.name.toLowerCase()}">
                                    <div class="custom-control custom-checkbox w-100 d-flex align-items-center justify-content-between">
                                        <div>
                                            <input type="checkbox" class="custom-control-input table-checkbox" id="tbl_${table.name}" name="tables[]" value="${table.name}">
                                            <label class="custom-control-label" for="tbl_${table.name}">
                                                <span class="text-truncate" title="${table.name}">${table.name}</span>
                                            </label>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-secondary badge-count mr-2">${table.rows}</span>
                                            <button type="button" class="btn btn-sm btn-link text-info view-data-btn p-0" data-table="${table.name}" title="Verileri Gör">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        $('#tableListContainer').html(html);
                        applyFilters(); // Apply current filters to new data
                    } else {
                        $('#tableListContainer').html('<div class="alert alert-danger">Tablolar yüklenemedi.</div>');
                    }
                },
                error: function() {
                    $('#tableListContainer').html('<div class="alert alert-danger">Sunucu hatası.</div>');
                }
            });
        }

        loadTables();

        function updateButtonState() {
            const count = $('.table-checkbox:checked').length;
            $('#clearBtn').prop('disabled', count === 0);
        }

        function applyFilters() {
            const searchValue = $('#tableSearch').val().toLowerCase();
            const onlyFull = $('#filterEmptyTables').is(':checked');

            $('.table-item').each(function() {
                const tableName = $(this).data('name') || "";
                const rows = parseInt($(this).data('rows')) || 0;
                
                const matchesSearch = tableName.indexOf(searchValue) > -1;
                const matchesFilter = !onlyFull || rows > 0;

                $(this).toggle(matchesSearch && matchesFilter);
            });
            updateButtonState();
        }

        // Search and Filter Logic
        $('#tableSearch').on('keyup', applyFilters);
        $('#filterEmptyTables').on('change', applyFilters);

        // Selection Logic
        $('#selectAllBtn').click(function() {
            $('.table-checkbox:visible').prop('checked', true);
            updateButtonState();
        });

        $('#deselectAllBtn').click(function() {
            $('.table-checkbox').prop('checked', false);
            updateButtonState();
        });

        $(document).on('change', '.table-checkbox', function() {
            updateButtonState();
        });

        // Function to load table data
        function loadTableData(tableName) {
            $('#viewDataContent').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><br>Veriler yükleniyor...</div>');

            $.ajax({
                url: 'api_islemleri/tablo_temizle_islemler.php',
                type: 'GET',
                data: { action: 'get_table_data', table: tableName },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        if (response.data.length === 0) {
                            $('#viewDataContent').html('<div class="alert alert-info m-3">Bu tabloda hiç veri yok.</div>');
                            return;
                        }

                        let tableHtml = '<div class="table-responsive p-3"><table id="dataTable" class="table table-sm table-bordered table-striped table-hover mb-0" style="width:100%"><thead class="thead-light"><tr>';

                        // Headers
                        response.columns.forEach(function(col) {
                            tableHtml += `<th>${col}</th>`;
                        });
                        tableHtml += '</tr></thead><tbody>';

                        // Rows
                        response.data.forEach(function(row) {
                            tableHtml += '<tr>';
                            response.columns.forEach(function(col) {
                                let cellData = row[col];
                                if (cellData === null) cellData = '<em class="text-muted">NULL</em>';
                                else if (cellData.length > 100) cellData = cellData.substring(0, 100) + '...'; // Increased preview length
                                tableHtml += `<td>${cellData}</td>`;
                            });
                            tableHtml += '</tr>';
                        });
                        tableHtml += '</tbody></table></div>';

                        $('#viewDataContent').html(tableHtml);

                        // Initialize DataTables
                        $('#dataTable').DataTable({
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Turkish.json"
                            },
                            "pageLength": 10,
                            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tümü"]],
                            "responsive": true,
                            "order": [] // Disable initial sort
                        });
                    } else {
                        $('#viewDataContent').html(`<div class="alert alert-danger m-3">${response.message}</div>`);
                    }
                },
                error: function() {
                    $('#viewDataContent').html('<div class="alert alert-danger m-3">Veriler yüklenirken bir hata oluştu.</div>');
                }
            });
        }

        // View Data Logic
        $(document).on('click', '.view-data-btn', function(e) {
            e.preventDefault();
            const tableName = $(this).data('table');
            $('#viewDataModalTitle').text(tableName + ' - İlk 50 Kayıt');
            $('#viewDataModal').modal('show');
            loadTableData(tableName);
        });

        // Refresh Button
        $(document).on('click', '#refreshTableBtn', function(e) {
            e.preventDefault();
            const tableName = $('#viewDataModalTitle').text().split(' - ')[0];
            loadTableData(tableName);
        });

        // Modal Logic
        $('#clearBtn').click(function() {
            const selected = [];
            $('.table-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            
            $('#selectedCount').text(selected.length);
            $('#selectedTablesList').text(selected.join(', '));
            $('#confirmInput').val('');
            $('#confirmClearBtn').prop('disabled', true);
            $('#warningModal').modal('show');
        });

        $('#confirmInput').on('input', function() {
            $('#confirmClearBtn').prop('disabled', $(this).val() !== 'ONAY');
        });

        // Execution Logic
        $('#confirmClearBtn').click(function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Temizleniyor...');
            
            const selectedTables = [];
            $('.table-checkbox:checked').each(function() {
                selectedTables.push($(this).val());
            });

            $.ajax({
                url: 'api_islemleri/tablo_temizle_islemler.php',
                type: 'POST',
                data: { action: 'clear_tables', tables: selectedTables },
                dataType: 'json',
                success: function(response) {
                    $('#warningModal').modal('hide');
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        loadTables(); // Refresh counts
                        $('.table-checkbox').prop('checked', false);
                        updateButtonState();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    $('#warningModal').modal('hide');
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Evet, Temizle');
                }
            });
        });
    });
    </script>
</body>
</html>
