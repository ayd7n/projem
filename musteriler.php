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

// Calculate total customers
$total_result = $connection->query("SELECT COUNT(*) as total FROM musteriler");
$total_customers = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Yönetimi - Parfüm ERP</title>
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

        .erp-container { min-height: 100vh; }

        /* Main Content */
        .main-content { padding: 30px; }
        
        @media (max-width: 768px) {
            .main-content { padding: 0; }
        }
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
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group input:disabled, .form-group select:disabled, .form-group textarea:disabled { background-color: #f1f3f5; cursor: not-allowed; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1; }

        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn:disabled { background-color: #bdc3c7; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn.disabled { background-color: #bdc3c7; cursor: not-allowed; pointer-events: none; }

        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-secondary { background-color: var(--text-secondary); color: white; }
        .btn-secondary:hover { background-color: var(--dark); }
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
        .actions a, .actions button { padding: 8px 12px; }

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
            <h1>Müşteri Yönetimi</h1>
            <p>Yeni müşteriler ekleyin ve mevcut müşterileri yönetin.</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addCustomerBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Müşteri Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_customers; ?></h3>
                        <p>Toplam Müşteri</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Müşteri Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Giriş Yetkisi</th>
                                <th>Müşteri Adı</th>
                                <th>Vergi/TC No</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Adres</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody id="customersTableBody">
                            <tr>
                                <td colspan="8" class="text-center p-4">Yükleniyor...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="customerForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Müşteri Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="musteri_id" name="musteri_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="musteri_adi">Müşteri Adı *</label>
                                <input type="text" class="form-control" id="musteri_adi" name="musteri_adi" required>
                            </div>
                            <div class="form-group">
                                <label for="vergi_no_tc">Vergi No / TC</label>
                                <input type="text" class="form-control" id="vergi_no_tc" name="vergi_no_tc">
                            </div>
                            <div class="form-group">
                                <label for="telefon">Telefon</label>
                                <input type="text" class="form-control" id="telefon" name="telefon">
                            </div>
                            <div class="form-group">
                                <label for="e_posta">E-posta</label>
                                <input type="email" class="form-control" id="e_posta" name="e_posta">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="adres">Adres</label>
                                <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="sifre"><?php echo isset($_POST['musteri_id']) ? 'Şifre (değiştirmek istemiyorsanız boş bırakın)' : 'Şifre *'; ?></label>
                                <input type="password" class="form-control" id="sifre" name="sifre" <?php echo !isset($_POST['musteri_id']) ? 'required' : ''; ?>>
                                <?php if (isset($_POST['musteri_id'])): ?>
                                    <small class="form-text text-muted">Şifreyi değiştirmek istemiyorsanız boş bırakın</small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="giris_yetkisi" name="giris_yetkisi" value="1">
                                    <label class="form-check-label" for="giris_yetkisi">
                                        Sisteme Giriş Yetkisi
                                    </label>
                                </div>
                                <small class="form-text text-muted">Müşterinin sistemde oturum açmasına izin ver</small>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama_notlar">Açıklama / Notlar</label>
                                <textarea class="form-control" id="aciklama_notlar" name="aciklama_notlar" rows="3"></textarea>
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

        // Load customers on page load
        loadCustomers();

        // Function to load customers
        function loadCustomers() {
            $.ajax({
                url: 'api_islemleri/musteriler_islemler.php?action=get_customers',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var tbody = $('#customersTableBody');
                        tbody.empty();

                        if (response.data.length > 0) {
                            $.each(response.data, function(index, customer) {
                                tbody.append(`
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="${customer.musteri_id}"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="${customer.musteri_id}"><i class="fas fa-trash"></i></button>
                                        </td>
                                        <td>
                                            ${customer.giris_yetkisi == 1 ?
                                                '<span style="color: green; font-weight: bold;">✓</span>' :
                                                '<span style="color: red; font-weight: bold;">✗</span>'
                                            }
                                        </td>
                                        <td><strong>${customer.musteri_adi}</strong></td>
                                        <td>${customer.vergi_no_tc || '-'}</td>
                                        <td>${customer.telefon || '-'}</td>
                                        <td>${customer.e_posta || '-'}</td>
                                        <td>${customer.adres ? (customer.adres.length > 30 ? customer.adres.substring(0, 30) + '...' : customer.adres) : '-'}</td>
                                        <td>${customer.aciklama_notlar ? (customer.aciklama_notlar.length > 20 ? customer.aciklama_notlar.substring(0, 20) + '...' : customer.aciklama_notlar) : '-'}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="8" class="text-center p-4">Henüz kayıtlı müşteri bulunmuyor.</td></tr>');
                        }
                    } else {
                        $('#customersTableBody').html('<tr><td colspan="8" class="text-center p-4 text-danger">Müşteriler yüklenirken hata oluştu.</td></tr>');
                    }
                },
                error: function() {
                    $('#customersTableBody').html('<tr><td colspan="8" class="text-center p-4 text-danger">Müşteriler yüklenirken bir hata oluştu.</td></tr>');
                }
            });
        }

        // Open modal for adding a new customer
        $('#addCustomerBtn').on('click', function() {
            $('#customerForm')[0].reset();
            $('#modalTitle').text('Yeni Müşteri Ekle');
            $('#action').val('add_customer');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#sifre').prop('required', true);
            $('#customerModal').modal('show');
        });

        // Open modal for editing a customer
        $(document).on('click', '.edit-btn', function() {
            var customerId = $(this).data('id');
            $.ajax({
                url: 'api_islemleri/musteriler_islemler.php?action=get_customer&id=' + customerId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var customer = response.data;
                        $('#customerForm')[0].reset();
                        $('#modalTitle').text('Müşteriyi Düzenle');
                        $('#action').val('update_customer');
                        $('#musteri_id').val(customer.musteri_id);
                        $('#musteri_adi').val(customer.musteri_adi);
                        $('#vergi_no_tc').val(customer.vergi_no_tc);
                        $('#telefon').val(customer.telefon);
                        $('#e_posta').val(customer.e_posta);
                        $('#adres').val(customer.adres);
                        $('#aciklama_notlar').val(customer.aciklama_notlar);
                        $('#giris_yetkisi').prop('checked', customer.giris_yetkisi == 1);
                        $('#sifre').prop('required', false);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#customerModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Müşteri bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#customerForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/musteriler_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#customerModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload customers to see changes
                        loadCustomers();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle customer deletion
        $(document).on('click', '.delete-btn', function() {
            var customerId = $(this).data('id');
            if (confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/musteriler_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_customer',
                        musteri_id: customerId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            loadCustomers();
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
