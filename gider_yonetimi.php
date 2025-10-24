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

// Fetch all expenses
$expenses_query = "SELECT * FROM gider_yonetimi ORDER BY tarih DESC, gider_id DESC";
$expenses_result = $connection->query($expenses_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Gider Yönetimi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5; /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-secondary: #6b7280; /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        html {
            font-size: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }
        .main-content {
            padding: 20px;
        }
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.825rem;
        }
        .btn:hover {
             transform: translateY(-2px);
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--secondary);
            box-shadow: 0 10px 20px rgba(74, 14, 99, 0.2);
        }
        .add-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
        }
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 5px solid;
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-color: #f56565;
        }
        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            border-color: #48bb78;
        }
        .product-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-name {
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--primary);
        }
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .quantity-input {
            width: 70px;
            padding: 0.6rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .quantity-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        .cart-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-info h4 {
            margin-bottom: 5px;
        }
        .item-quantity {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        /* Cart panel that slides from right */
        #sepet {
            position: fixed;
            top: 0;
            right: 0;
            width: 320px;
            height: 100%;
            z-index: 1050;
            border-radius: 0;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        #sepet.show {
            transform: translateX(0);
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        .empty-cart {
            text-align: center;
            padding: 30px 0;
            color: var(--text-secondary);
        }
        .empty-cart i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: var(--primary);
            opacity: 0.3;
        }
        .cart-total {
            padding: 20px 20px;
            border-top: 2px solid var(--border-color);
            font-size: 1.3rem;
            font-weight: 700;
            text-align: right;
            display: none; /* Hide total since we're hiding pricing */
        }
        
        .order-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .order-filters .btn {
            padding: 8px 12px;
            font-size: 0.85rem;
            border-radius: 20px;
        }
        
        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .table th i {
            margin-right: 6px;
        }
        
        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
        }
        
        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .actions .btn {
            padding: 6px 10px;
            border-radius: 18px;
        }
        
        .no-orders-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }

        .mobile-menu-btn {
            display: none;
        }

        html {
            scroll-behavior: smooth;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
        }

        @media (max-width: 991.98px) {
            #sepet.collapse.show {
                position: fixed;
                top: 0;
                right: 0;
                width: 320px;
                height: 100%;
                z-index: 1050; /* Higher than navbar */
                border-radius: 0;
                box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            }
            #sepet .card-body {
                overflow-y: auto;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

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
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
        
        <div class="page-header">
            <div>
                <h1>Gider Yönetimi</h1>
                <p>İşletme giderlerini ekleyin, düzenleyin ve takip edin.</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addExpenseBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Gider Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;"><?php echo number_format($total_expenses, 2, ',', '.'); ?> TL</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Gider</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Gider Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-tag"></i> Kategori</th>
                                <th><i class="fas fa-money-bill"></i> Tutar</th>
                                <th><i class="fas fa-credit-card"></i> Ödeme Tipi</th>
                                <th><i class="fas fa-file-invoice"></i> Fatura No</th>
                                <th><i class="fas fa-sticky-note"></i> Açıklama</th>
                                <th><i class="fas fa-user"></i> Kaydeden</th>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($expenses_result && $expenses_result->num_rows > 0): ?>
                                <?php while ($expense = $expenses_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y', strtotime($expense['tarih'])); ?></td>
                                        <td><?php echo htmlspecialchars($expense['kategori']); ?></td>
                                        <td><strong><?php echo number_format($expense['tutar'], 2, ',', '.'); ?> TL</strong></td>
                                        <td><?php echo htmlspecialchars($expense['odeme_tipi']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['fatura_no'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($expense['aciklama']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['kaydeden_personel_ismi'] ?: $expense['kaydeden_personel_id']); ?></td>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $expense['gider_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $expense['gider_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center p-4">Henüz kayıtlı gider bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div class="modal fade" id="expenseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="expenseForm">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-edit"></i> Gider Formu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="gider_id" name="gider_id">
                        <input type="hidden" id="action" name="action">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tarih">Tarih *</label>
                                    <input type="date" class="form-control" id="tarih" name="tarih" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tutar">Tutar (TL) *</label>
                                    <input type="number" class="form-control" id="tutar" name="tutar" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
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
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="odeme_tipi">Ödeme Tipi *</label>
                                    <select class="form-control" id="odeme_tipi" name="odeme_tipi" required>
                                        <option value="Nakit">Nakit</option>
                                        <option value="Kredi Kartı">Kredi Kartı</option>
                                        <option value="Havale">Havale/EFT</option>
                                        <option value="Diğer">Diğer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="fatura_no">Fatura No</label>
                            <input type="text" class="form-control" id="fatura_no" name="fatura_no">
                        </div>
                        <div class="form-group mb-3">
                            <label for="aciklama">Açıklama *</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> İptal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save"></i> Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    $(document).ready(function() {
        
        function showAlert(message, type) {
            $('#alert-placeholder').html(
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`
            );
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
        $('.edit-btn').on('click', function() {
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

        // Handle expense deletion
        $('.delete-btn').on('click', function() {
            var expenseId = $(this).data('id');

            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu gideri silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
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
    });
    </script>
</body>
</html>
