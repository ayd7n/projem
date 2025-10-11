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

// Fetch all employees
$employees_query = "SELECT * FROM personeller ORDER BY ad_soyad";
$employees_result = $connection->query($employees_query);

// Calculate total employees
$total_result = $connection->query("SELECT COUNT(*) as total FROM personeller");
$total_employees = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Personel Yönetimi - Parfüm ERP</title>
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
        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }
        .actions button { padding: 8px 12px; }
        .stat-card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); padding: 25px; display: flex; align-items: center; }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }
        .modal-body .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .modal-body .form-group { display: flex; flex-direction: column; }
        .modal-body .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .modal-body .form-group input, .modal-body .form-group select, .modal-body .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; }
        .disabled-row { opacity: 0.6; }
        .disabled-row .actions button { pointer-events: none; opacity: 0.5; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Personel Yönetimi</h1>
            <p>Personelleri ekleyin, düzenleyin ve yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addEmployeeBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Personel Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_employees; ?></h3>
                        <p>Toplam Personel</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Personel Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Ad Soyad</th>
                                <th>Pozisyon</th>
                                <th>Departman</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>TC Kimlik No</th>
                                <th>Doğum Tarihi</th>
                                <th>İşe Giriş</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($employees_result && $employees_result->num_rows > 0): ?>
                                <?php while ($employee = $employees_result->fetch_assoc()): ?>
                                    <tr class="<?php echo ($employee['ad_soyad'] === 'Admin User') ? 'disabled-row' : ''; ?>">
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn <?php echo ($employee['ad_soyad'] === 'Admin User') ? 'disabled' : ''; ?>" 
                                                    data-id="<?php echo $employee['personel_id']; ?>" 
                                                    title="<?php echo ($employee['ad_soyad'] === 'Admin User') ? 'Bu kullanıcı düzenlenemez' : ''; ?>"
                                                    <?php echo ($employee['ad_soyad'] === 'Admin User') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn <?php echo ($employee['ad_soyad'] === 'Admin User') ? 'disabled' : ''; ?>" 
                                                    data-id="<?php echo $employee['personel_id']; ?>" 
                                                    title="<?php echo ($employee['ad_soyad'] === 'Admin User') ? 'Bu kullanıcı silinemez' : ''; ?>"
                                                    <?php echo ($employee['ad_soyad'] === 'Admin User') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($employee['ad_soyad']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($employee['pozisyon']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['departman']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['telefon']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['e_posta']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['tc_kimlik_no']); ?></td>
                                        <td><?php echo $employee['dogum_tarihi'] ? date("d.m.Y", strtotime($employee['dogum_tarihi'])) : ''; ?></td>
                                        <td><?php echo date("d.m.Y", strtotime($employee['ise_giris_tarihi'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center p-4">Henüz kayıtlı personel bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="employeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Personel Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="personel_id" name="personel_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="ad_soyad">Ad Soyad *</label>
                                <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" required>
                            </div>
                            <div class="form-group">
                                <label for="pozisyon">Pozisyon</label>
                                <input type="text" class="form-control" id="pozisyon" name="pozisyon">
                            </div>
                            <div class="form-group">
                                <label for="departman">Departman</label>
                                <input type="text" class="form-control" id="departman" name="departman">
                            </div>
                            <div class="form-group">
                                <label for="ise_giris_tarihi">İşe Giriş Tarihi</label>
                                <input type="date" class="form-control" id="ise_giris_tarihi" name="ise_giris_tarihi">
                            </div>
                            <div class="form-group">
                                <label for="telefon">Telefon</label>
                                <input type="text" class="form-control" id="telefon" name="telefon">
                            </div>
                            <div class="form-group">
                                <label for="e_posta">E-posta</label>
                                <input type="email" class="form-control" id="e_posta" name="e_posta">
                            </div>
                            <div class="form-group">
                                <label for="tc_kimlik_no">TC Kimlik No</label>
                                <input type="text" class="form-control" id="tc_kimlik_no" name="tc_kimlik_no">
                            </div>
                            <div class="form-group">
                                <label for="dogum_tarihi">Doğum Tarihi</label>
                                <input type="date" class="form-control" id="dogum_tarihi" name="dogum_tarihi">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="adres">Adres</label>
                                <textarea class="form-control" id="adres" name="adres" rows="2"></textarea>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="notlar">Notlar</label>
                                <textarea class="form-control" id="notlar" name="notlar" rows="2"></textarea>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="sifre">Şifre</label>
                                <input type="password" class="form-control" id="sifre" name="sifre" placeholder="Yeni şifre (boş bırakırsanız değişmez)">
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

        // Open modal for adding a new employee
        $('#addEmployeeBtn').on('click', function() {
            $('#employeeForm')[0].reset();
            $('#modalTitle').text('Yeni Personel Ekle');
            $('#action').val('add_employee');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#employeeModal').modal('show');
        });

        // Open modal for editing an employee
        $('.edit-btn').on('click', function() {
            var employeeId = $(this).data('id');
            if($(this).hasClass('disabled')) {
                showAlert('Bu kullanıcı düzenlenemez.', 'warning');
                return;
            }
            
            $.ajax({
                url: 'api_islemleri/personeller_islemler.php?action=get_employee&id=' + employeeId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var employee = response.data;
                        $('#employeeForm')[0].reset();
                        $('#modalTitle').text('Personeli Düzenle');
                        $('#action').val('update_employee');
                        $('#personel_id').val(employee.personel_id);
                        $('#ad_soyad').val(employee.ad_soyad);
                        $('#tc_kimlik_no').val(employee.tc_kimlik_no);
                        $('#dogum_tarihi').val(employee.dogum_tarihi);
                        $('#ise_giris_tarihi').val(employee.ise_giris_tarihi);
                        $('#pozisyon').val(employee.pozisyon);
                        $('#departman').val(employee.departman);
                        $('#e_posta').val(employee.e_posta);
                        $('#telefon').val(employee.telefon);
                        $('#adres').val(employee.adres);
                        $('#notlar').val(employee.notlar);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#employeeModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Personel bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#employeeForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/personeller_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#employeeModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload page to see changes
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle employee deletion
        $('.delete-btn').on('click', function() {
            var employeeId = $(this).data('id');
            
            if($(this).hasClass('disabled')) {
                showAlert('Bu kullanıcı silinemez.', 'warning');
                return;
            }
            
            if (confirm('Bu personeli silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/personeller_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_employee',
                        personel_id: employeeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
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