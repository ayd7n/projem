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

// Calculate total expenses
$total_result = $connection->query("SELECT SUM(tutar) as total FROM gider_yonetimi");
$total_expenses = $total_result->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gider Yönetimi - Parfüm ERP</title>
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

        .main-content {
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .page-header p { color: var(--text-secondary); font-size: 1rem; }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h2 { font-size: 1.2rem; font-weight: 600; }
        .card-body { padding: 20px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 0.95rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }

        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-secondary { background-color: var(--text-secondary); color: white; }
        .btn-secondary:hover { background-color: var(--dark); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { background-color: #16a085; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-danger:hover { background-color: #c0392b; }

        .table-wrapper { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }
        .actions a, .actions button { padding: 8px 12px; }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            padding: 25px;
            display: flex;
            align-items: center;
        }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }



    </style>
</head>
<body>
    <div class="main-content">
            <div class="page-header">
                <h1>Gider Yönetimi</h1>
                <p>İşletme giderlerini ekleyin, düzenleyin ve takip edin.</p>
            </div>

            <div id="alert-placeholder"></div>

            <div class="row">
                <div class="col-md-8">
                    <button id="addExpenseBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Gider Ekle</button>
                </div>
                <div class="col-md-4">
                    <div class="stat-card mb-3">
                        <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-wallet"></i></div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_expenses, 2, ',', '.'); ?> TL</h3>
                            <p>Toplam Kayıtlı Gider</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Gider Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Kategori</th>
                                    <th>Tutar</th>
                                    <th>Ödeme Tipi</th>
                                    <th>Fatura No</th>
                                    <th>Açıklama</th>
                                    <th>Kaydeden</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="expensesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center p-4">Yükleniyor...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div class="modal fade" id="expenseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="expenseForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Gider Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="gider_id" name="gider_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tarih">Tarih *</label>
                                <input type="date" class="form-control" id="tarih" name="tarih" required>
                            </div>
                            <div class="form-group">
                                <label for="tutar">Tutar (TL) *</label>
                                <input type="number" class="form-control" id="tutar" name="tutar" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="kategori">Kategori *</label>
                                <select class="form-control" id="kategori" name="kategori" required>
                                    <option value="Personel Gideri">Personel Gideri</option>
                                    <option value="Malzeme Gideri">Malzeme Gideri</option>
                                    <option value="İşletme Gideri">İşletme Gideri</option>
                                    <option value="Kira">Kira</option>
                                    <option value="Enerji">Enerji</option>
                                    <option value="Taşıt Gideri">Taşıt Gideri</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="odeme_tipi">Ödeme Tipi *</label>
                                <select class="form-control" id="odeme_tipi" name="odeme_tipi" required>
                                    <option value="Nakit">Nakit</option>
                                    <option value="Kredi Kartı">Kredi Kartı</option>
                                    <option value="Havale">Havale/EFT</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fatura_no">Fatura No</label>
                                <input type="text" class="form-control" id="fatura_no" name="fatura_no">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama">Açıklama *</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3" required></textarea>
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

        // Load expenses on page load
        loadExpenses();

        // Function to load expenses
        function loadExpenses() {
            $.ajax({
                url: 'api_islemleri/gider_yonetimi_islemler.php?action=get_expenses',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var tbody = $('#expensesTableBody');
                        tbody.empty();

                        if (response.data.length > 0) {
                            $.each(response.data, function(index, expense) {
                                tbody.append(`
                                    <tr>
                                        <td>${new Date(expense.tarih).toLocaleDateString('tr-TR')}</td>
                                        <td>${expense.kategori}</td>
                                        <td><strong>${parseFloat(expense.tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} TL</strong></td>
                                        <td>${expense.odeme_tipi}</td>
                                        <td>${expense.fatura_no || '-'}</td>
                                        <td>${expense.aciklama}</td>
                                        <td>${expense.kaydeden_personel_ismi || expense.kaydeden_personel_id}</td>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="${expense.gider_id}"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="${expense.gider_id}"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="8" class="text-center p-4">Henüz kayıtlı gider bulunmuyor.</td></tr>');
                        }
                    } else {
                        $('#expensesTableBody').html('<tr><td colspan="8" class="text-center p-4 text-danger">Giderler yüklenirken hata oluştu.</td></tr>');
                    }
                },
                error: function() {
                    $('#expensesTableBody').html('<tr><td colspan="8" class="text-center p-4 text-danger">Giderler yüklenirken bir hata oluştu.</td></tr>');
                }
            });
        }

        // Open modal for adding a new expense
        $('#addExpenseBtn').on('click', function() {
            $('#expenseForm')[0].reset();
            $('#modalTitle').text('Yeni Gider Ekle');
            $('#action').val('add_expense');
            $('#tarih').val(new Date().toISOString().split('T')[0]);
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#expenseModal').modal('show');
        });

        // Open modal for editing an expense
        $(document).on('click', '.edit-btn', function() {
            var expenseId = $(this).data('id');
            $.ajax({
                url: 'api_islemleri/gider_yonetimi_islemler.php?action=get_expense&id=' + expenseId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var expense = response.data;
                        $('#expenseForm')[0].reset();
                        $('#modalTitle').text('Gideri Düzenle');
                        $('#action').val('update_expense');
                        $('#gider_id').val(expense.gider_id);
                        $('#tarih').val(expense.tarih);
                        $('#tutar').val(expense.tutar);
                        $('#kategori').val(expense.kategori);
                        $('#odeme_tipi').val(expense.odeme_tipi);
                        $('#aciklama').val(expense.aciklama);
                        $('#fatura_no').val(expense.fatura_no);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#expenseModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Gider bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#expenseForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/gider_yonetimi_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#expenseModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload expenses to see changes
                        loadExpenses();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle expense deletion
        $(document).on('click', '.delete-btn', function() {
            var expenseId = $(this).data('id');
            if (confirm('Bu gideri silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/gider_yonetimi_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_expense',
                        gider_id: expenseId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            loadExpenses();
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
