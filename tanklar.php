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

// Calculate total tanks
$total_result = $connection->query("SELECT COUNT(*) as total FROM tanklar");
$total_tanks = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanklar - Parfüm ERP Sistemi</title>
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
        }

        .main-content { padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .page-header p { color: var(--text-secondary); font-size: 1rem; }

        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); margin-bottom: 30px; overflow: hidden; }
        .card-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-header h2 { font-size: 1.2rem; font-weight: 600; }
        .card-body { padding: 20px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group textarea { height: 100px; resize: vertical; }

        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { background-color: #16a085; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-danger:hover { background-color: #c0392b; }

        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }

        .stat-card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); padding: 25px; display: flex; align-items: center; }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Tanklar Yönetimi</h1>
            <p>Tank bilgilerini yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addTankBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Tank Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-database"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_tanks; ?></h3>
                        <p>Toplam Tank</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Tank Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Tank Kodu</th>
                                <th>Tank İsmi</th>
                                <th>Kapasite (Litre)</th>
                                <th>Not Bilgisi</th>
                            </tr>
                        </thead>
                        <tbody id="tanksTableBody">
                            <tr>
                                <td colspan="5" class="text-center p-4">Yükleniyor...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tank Modal -->
    <div class="modal fade" id="tankModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="tankForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tank Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="tank_id" name="tank_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tank_kodu">Tank Kodu *</label>
                                <input type="text" class="form-control" id="tank_kodu" name="tank_kodu" required>
                            </div>

                            <div class="form-group">
                                <label for="tank_ismi">Tank İsmi *</label>
                                <input type="text" class="form-control" id="tank_ismi" name="tank_ismi" required>
                            </div>

                            <div class="form-group">
                                <label for="kapasite">Kapasite (Litre) *</label>
                                <input type="number" class="form-control" id="kapasite" name="kapasite" min="0" step="0.01" required>
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="not_bilgisi">Not Bilgisi</label>
                                <textarea class="form-control" id="not_bilgisi" name="not_bilgisi" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Kaydet</button>
                    </div>
                </form>
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
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`
            );
        }

        // Load tanks on page load
        loadTanks();

        // Function to load tanks
        function loadTanks() {
            $.ajax({
                url: 'api_islemleri/tanklar_islemler.php?action=get_tanks',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var tbody = $('#tanksTableBody');
                        tbody.empty();

                        if (response.data.length > 0) {
                            $.each(response.data, function(index, tank) {
                                tbody.append(`
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="${tank.tank_id}"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="${tank.tank_id}"><i class="fas fa-trash"></i></button>
                                        </td>
                                        <td><strong>${tank.tank_kodu}</strong></td>
                                        <td>${tank.tank_ismi}</td>
                                        <td>${tank.kapasite} L</td>
                                        <td>${tank.not_bilgisi || '-'}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5" class="text-center p-4">Henüz kayıtlı tank bulunmuyor.</td></tr>');
                        }
                    } else {
                        $('#tanksTableBody').html('<tr><td colspan="5" class="text-center p-4 text-danger">Tanklar yüklenirken hata oluştu.</td></tr>');
                    }
                },
                error: function() {
                    $('#tanksTableBody').html('<tr><td colspan="5" class="text-center p-4 text-danger">Tanklar yüklenirken bir hata oluştu.</td></tr>');
                }
            });
        }

        // Open modal for adding a new tank
        $('#addTankBtn').on('click', function() {
            $('#tankForm')[0].reset();
            $('#modalTitle').text('Yeni Tank Ekle');
            $('#action').val('add_tank');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#tankModal').modal('show');
        });

        // Open modal for editing a tank
        $(document).on('click', '.edit-btn', function() {
            var tankId = $(this).data('id');
            $.ajax({
                url: 'api_islemleri/tanklar_islemler.php?action=get_tank&id=' + tankId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var tank = response.data;
                        $('#tankForm')[0].reset();
                        $('#modalTitle').text('Tankı Düzenle');
                        $('#action').val('update_tank');
                        $('#tank_id').val(tank.tank_id);
                        $('#tank_kodu').val(tank.tank_kodu);
                        $('#tank_ismi').val(tank.tank_ismi);
                        $('#kapasite').val(tank.kapasite);
                        $('#not_bilgisi').val(tank.not_bilgisi);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#tankModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Tank bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#tankForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/tanklar_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#tankModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload tanks to see changes
                        loadTanks();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle tank deletion
        $(document).on('click', '.delete-btn', function() {
            var tankId = $(this).data('id');
            if (confirm('Bu tankı silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/tanklar_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_tank',
                        tank_id: tankId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            loadTanks();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
