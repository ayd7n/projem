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
    <title>Yedekleme - Parfüm ERP</title>
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
        .backup-actions {
            margin-bottom: 2rem;
        }
        .backup-list .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .backup-info {
            flex-grow: 1;
        }
        .backup-controls .btn {
            margin-left: 0.5rem;
        }
        .custom-file-label::after {
            content: "Gözat";
        }
        @media (max-width: 768px) {
            .backup-list .list-group-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .backup-controls {
                margin-top: 1rem;
                width: 100%;
                display: flex;
                justify-content: flex-end;
            }
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
            <h1><i class="fas fa-database"></i> Veritabanı Yedekleme</h1>
            <p class="text-muted">Veritabanı yedeklerini yönetin, oluşturun ve geri yükleyin.</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row backup-actions">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Yeni Yedek Oluştur</h5>
                        <p class="card-text">Tüm veritabanının anlık yedeğini oluştur.</p>
                        <button id="createBackupBtn" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Yeni Yedek Oluştur</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Yedekten Geri Yükle</h5>
                        <p class="card-text">Bilgisayarınızdan bir <code>.sql</code> dosyası seçerek geri yükleyin.</p>
                        <form id="uploadRestoreForm">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="backupFile" accept=".sql">
                                <label class="custom-file-label" for="backupFile" data-browse="Gözat">Dosya seç...</label>
                            </div>
                            <button id="uploadRestoreBtn" type="button" class="btn btn-warning mt-3" disabled><i class="fas fa-upload"></i> Yükle ve Geri Yükle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mevcut Yedekler</h5>
            </div>
            <div class="card-body">
                <ul id="backupList" class="list-group list-group-flush">
                    <li class="list-group-item text-center">Yedekler yükleniyor...</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="restoreWarningModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> DİKKAT! Yıkıcı İşlem</h5><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body"><p>Bu işlem mevcut veritabanını tamamen <strong>SİLECEK</strong> ve seçili yedekle değiştirecektir.</p><p><strong>Bu işlem geri alınamaz.</strong></p><p>Devam etmek istediğinizden emin misiniz?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button><button type="button" class="btn btn-danger" id="continueToFinalConfirm">Anladım, Devam Et</button></div>
        </div></div>
    </div>
    <div class="modal fade" id="restoreFinalConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Son Onay</h5><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body"><p>Geri yüklemeyi onaylamak için lütfen aşağıdaki alana veritabanı adını (<strong>parfum_erp</strong>) yazın.</p><div class="form-group"><input type="text" class="form-control" id="dbNameConfirmInput" placeholder="parfum_erp"></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button><button type="button" class="btn btn-danger" id="confirmRestoreBtn" disabled>Onayla ve Geri Yükle</button></div>
        </div></div>
    </div>
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Yedeği Sil</h5><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body"><p>Bu yedek dosyasını kalıcı olarak silmek istediğinizden emin misiniz?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button><button type="button" class="btn btn-danger" id="confirmDeleteBtn">Evet, Sil</button></div>
        </div></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        let fileToRestore = null;
        let fileToDelete = null;
        let restoreMode = 'file'; // 'file' or 'upload'

        function showAlert(message, type) {
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button></div>`;
            $('#alert-placeholder').html(alertHtml);
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function loadBackups() {
            const backupList = $('#backupList');
            backupList.html('<li class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i>Yedekler yükleniyor...</li>');

            $.ajax({
                url: 'api_islemleri/yedekleme_islemler.php?action=list_backups',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    backupList.empty();
                    if (response.status === 'success' && response.backups.length > 0) {
                        response.backups.forEach(function(backup) {
                            const listItem = `
                                <li class="list-group-item">
                                    <div class="backup-info">
                                        <strong>${backup.filename}</strong><br>
                                        <small class="text-muted">Tarih: ${backup.created_at} | Boyut: ${formatBytes(backup.size)}</small>
                                    </div>
                                    <div class="backup-controls">
                                        <button class="btn btn-sm btn-info telegram-btn" data-filename="${backup.filename}"><i class="fab fa-telegram-plane"></i> Gönder</button>
                                        <a href="yedekler/${backup.filename}" class="btn btn-sm btn-success" download><i class="fas fa-download"></i> İndir</a>
                                        <button class="btn btn-sm btn-warning restore-btn" data-filename="${backup.filename}"><i class="fas fa-undo"></i> Geri Yükle</button>
                                        <button class="btn btn-sm btn-danger delete-btn" data-filename="${backup.filename}"><i class="fas fa-trash"></i> Sil</button>
                                    </div>
                                </li>`;
                            backupList.append(listItem);
                        });
                    } else if (response.status === 'success') {
                        backupList.html('<li class="list-group-item text-center">Henüz hiç yedek oluşturulmamış.</li>');
                    } else {
                        showAlert(response.message || 'Yedekler listelenemedi.', 'danger');
                    }
                },
                error: function() { showAlert('Yedek listesi alınırken bir sunucu hatası oluştu.', 'danger'); }
            });
        }

        $('#createBackupBtn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Oluşturuluyor...');
            $.ajax({
                url: 'api_islemleri/yedekleme_islemler.php', type: 'POST', data: { action: 'create_backup' }, dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        loadBackups();
                    } else {
                        showAlert(response.message || 'Yedekleme başarısız.', 'danger');
                    }
                },
                error: function() { showAlert('Yedekleme sırasında bir sunucu hatası oluştu.', 'danger'); },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-plus-circle"></i> Yeni Yedek Oluştur'); }
            });
        });
        
        // Send to Telegram
        $(document).on('click', '.telegram-btn', function() {
            const btn = $(this);
            const filename = btn.data('filename');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'api_islemleri/yedekleme_islemler.php',
                type: 'POST',
                data: { action: 'send_telegram', filename: filename },
                dataType: 'json',
                success: function(response) {
                    showAlert(response.message, response.status);
                },
                error: function() {
                    showAlert('Telegram\'a gönderilirken bir sunucu hatası oluştu.', 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fab fa-telegram-plane"></i> Gönder');
                }
            });
        });

        // Restore Process
        $(document).on('click', '.restore-btn', function() {
            restoreMode = 'file';
            fileToRestore = $(this).data('filename');
            $('#restoreWarningModal').modal('show');
        });
        
        $('#uploadRestoreBtn').on('click', function() {
            restoreMode = 'upload';
            $('#restoreWarningModal').modal('show');
        });

        $('#continueToFinalConfirm').on('click', function() {
            $('#restoreWarningModal').modal('hide');
            $('#restoreFinalConfirmModal').modal('show');
        });

        $('#dbNameConfirmInput').on('input', function() {
            $('#confirmRestoreBtn').prop('disabled', $(this).val() !== 'parfum_erp');
        });

        $('#confirmRestoreBtn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Geri Yükleniyor...');
            $('#restoreFinalConfirmModal').modal('hide');

            let ajaxOptions = {
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message || 'Geri yükleme başarısız.', 'danger');
                    }
                },
                error: function() { showAlert('Geri yükleme sırasında bir sunucu hatası oluştu.', 'danger'); },
                complete: function() {
                    btn.prop('disabled', false).html('Onayla ve Geri Yükle');
                    $('#dbNameConfirmInput').val('');
                    $('#uploadRestoreBtn').prop('disabled', true);
                    $('#backupFile').val('');
                    $('.custom-file-label').text('Dosya seç...');
                }
            };

            if (restoreMode === 'upload') {
                const formData = new FormData();
                formData.append('action', 'upload_and_restore');
                formData.append('backupFile', $('#backupFile')[0].files[0]);
                ajaxOptions.url = 'api_islemleri/yedekleme_islemler.php';
                ajaxOptions.data = formData;
                ajaxOptions.processData = false;
                ajaxOptions.contentType = false;
            } else {
                ajaxOptions.url = 'api_islemleri/yedekleme_islemler.php';
                ajaxOptions.data = { action: 'restore_backup', filename: fileToRestore };
            }
            $.ajax(ajaxOptions);
        });

        // Delete Process
        $(document).on('click', '.delete-btn', function() {
            fileToDelete = $(this).data('filename');
            $('#deleteConfirmModal').modal('show');
        });

        $('#confirmDeleteBtn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true);
            $('#deleteConfirmModal').modal('hide');

            $.ajax({
                url: 'api_islemleri/yedekleme_islemler.php',
                type: 'POST',
                data: { action: 'delete_backup', filename: fileToDelete },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        loadBackups();
                    } else {
                        showAlert(response.message || 'Silme işlemi başarısız.', 'danger');
                    }
                },
                error: function() { showAlert('Silme sırasında bir sunucu hatası oluştu.', 'danger'); },
                complete: function() { btn.prop('disabled', false); }
            });
        });
        
        $('#backupFile').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
            $('#uploadRestoreBtn').prop('disabled', !fileName);
        });

        loadBackups();
    });
    </script>
</body>
</html>