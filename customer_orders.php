<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only customers can access this page
if ($_SESSION['taraf'] !== 'musteri') {
    header('Location: login.php');
    exit;
}

// Check if customer still has login access
$musteri_id = $_SESSION['user_id'];
$access_check_query = "SELECT giris_yetkisi FROM musteriler WHERE musteri_id = ?";
$access_check_stmt = $connection->prepare($access_check_query);
$access_check_stmt->bind_param('i', $musteri_id);
$access_check_stmt->execute();
$access_result = $access_check_stmt->get_result();

if ($access_result->num_rows > 0) {
    $customer = $access_result->fetch_assoc();
    if ($customer['giris_yetkisi'] != 1) {
        // Customer's access has been revoked, log them out
        session_destroy();
        header('Location: login.php?error=no_access');
        exit;
    }
} else {
    // Customer record doesn't exist
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get customer info
$musteri_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = ?";
$musteri_stmt = $connection->prepare($musteri_query);
$musteri_stmt->bind_param('i', $musteri_id);
$musteri_stmt->execute();
$musteri_result = $musteri_stmt->get_result();
$musteri = $musteri_result->fetch_assoc();
$musteri_adi = $musteri ? $musteri['musteri_adi'] : 'Müşteri';

// Get cart count for badge
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Geçmiş Siparişlerim - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            /* Deep Purple */
            --secondary: #7c2a99;
            /* Lighter Purple */
            --accent: #d4af37;
            /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5;
            /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827;
            /* Dark Gray/Black */
            --text-secondary: #6b7280;
            /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        html {
            font-size: 15px;
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .main-content {
            padding: 20px;
            padding-bottom: 80px;
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
            white-space: nowrap;
        }

        .table th i {
            margin-right: 6px;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        .table-responsive.past-orders-table {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .actions .btn {
            padding: 6px 10px;
            border-radius: 18px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
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

        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 0.6rem 0.2rem;
            z-index: 1000;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.08);
            justify-content: space-around;
            align-items: flex-start;
        }

        .mobile-bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-secondary);
            transition: all 0.2s ease-in-out;
            padding: 0.2rem 0.4rem;
            border-radius: 8px;
            width: 19%;
            min-width: 55px;
            text-align: center;
        }

        .mobile-bottom-nav .nav-item i {
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
            height: 1.2rem;
        }

        .mobile-bottom-nav .nav-item .nav-text {
            font-size: 0.7rem;
            font-weight: 500;
            line-height: 1.2;
        }

        .mobile-bottom-nav .nav-item:hover,
        .mobile-bottom-nav .nav-item.active {
            color: var(--secondary);
            background: rgba(124, 42, 153, 0.05);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                padding-bottom: 80px;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            .mobile-bottom-nav .nav-item .cart-badge {
                font-size: 0.8rem;
                padding: 0.25em 0.5em;
                position: relative;
                top: -1px;
                left: 2px;
                transform: scale(0.9);
                transform-origin: left center;
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;"
                href="customer_panel.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="customer_panel.php">Sipariş Paneli</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="customer_orders.php">Geçmiş Siparişlerim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($musteri_adi); ?>
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
        <div class="page-header">
            <div>
                <h1>Geçmiş Siparişlerim</h1>
                <p>Tüm siparişlerinizi görüntüleyebilir, detaylarını inceleyebilir ve beklemedeki siparişlerinizi iptal edebilirsiniz.</p>
            </div>
        </div>

        <!-- Past Orders Section -->
        <div class="card" id="gecmis-siparisler">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-history"></i> Siparişlerim</h2>
                <div class="order-filters">
                    <button
                        class="btn <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'btn-primary' : 'btn-outline-primary'; ?>"
                        onclick="filterOrders('all')">Tümü</button>
                    <button
                        class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'beklemede') ? 'btn-warning' : 'btn-outline-warning'; ?>"
                        onclick="filterOrders('beklemede')">Beklemede</button>
                    <button
                        class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'onaylandi') ? 'btn-success' : 'btn-outline-success'; ?>"
                        onclick="filterOrders('onaylandi')">Onaylandı</button>
                    <button
                        class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'iptal_edildi') ? 'btn-danger' : 'btn-outline-danger'; ?>"
                        onclick="filterOrders('iptal_edildi')">İptal Edildi</button>
                    <button
                        class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'tamamlandi') ? 'btn-info' : 'btn-outline-info'; ?>"
                        onclick="filterOrders('tamamlandi')">Tamamlandı</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive past-orders-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-tag"></i> Durum</th>
                                <th><i class="fas fa-boxes"></i> Toplam Adet</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Orders will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4" id="noOrdersMessage" style="display: none;">
                    <i class="fas fa-inbox fa-3x mb-3" style="color: var(--text-secondary);"></i>
                    <h4>Herhangi bir siparişiniz bulunmuyor.</h4>
                    <p class="text-muted">Dilerseniz yeni bir sipariş oluşturabilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="orderTitleText">Sipariş Detayı</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="orderDescription"><i class="fas fa-comment"></i> Sipariş Açıklaması</label>
                        <textarea class="form-control" id="orderDescription" name="orderDescription" rows="4"
                            placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..." readonly></textarea>
                        <input type="hidden" id="orderId" name="orderId">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Sipariş Kalemleri</label>
                        <div id="orderItemsList" class="border rounded p-3 bg-light">
                            <!-- Order items will be loaded via AJAX -->
                            <div class="text-center p-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Yükleniyor...</span>
                                </div>
                                <p class="mt-2">Sipariş kalemleri yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="fas fa-times"></i> Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="customer_panel.php" class="nav-item">
            <i class="fas fa-store"></i>
            <span class="nav-text">Mağaza</span>
        </a>
        <a href="customer_panel.php#sepet" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span class="nav-text">Sepet <span class="badge badge-danger ml-1 cart-badge"><?php echo count($cart); ?></span></span>
        </a>
        <a href="customer_orders.php" class="nav-item active">
            <i class="fas fa-history"></i>
            <span class="nav-text">Siparişlerim</span>
        </a>
        <a href="change_password.php" class="nav-item">
            <i class="fas fa-key"></i>
            <span class="nav-text">Parola</span>
        </a>
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span class="nav-text">Çıkış</span>
        </a>
    </nav>

    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function () {
            // Determine initial status from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const initialStatus = urlParams.get('status') || 'all';

            // Load orders on page load with initial status
            loadOrders(initialStatus);

            // Update filter button active states
            function updateFilterButtons(status) {
                $('.order-filters .btn').removeClass('btn-primary btn-outline-primary btn-warning btn-outline-warning btn-success btn-outline-success btn-danger btn-outline-danger btn-info btn-outline-info');

                switch (status) {
                    case 'all':
                        $('.order-filters .btn[onclick*="filterOrders(\'all\')"]').addClass('btn-primary');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'all\')"])').addClass('btn-outline-primary');
                        break;
                    case 'beklemede':
                        $('.order-filters .btn[onclick*="filterOrders(\'beklemede\')"]').addClass('btn-warning').removeClass('btn-outline-warning');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'beklemede\')"])').addClass('btn-outline-warning').removeClass('btn-warning');
                        break;
                    case 'onaylandi':
                        $('.order-filters .btn[onclick*="filterOrders(\'onaylandi\')"]').addClass('btn-success').removeClass('btn-outline-success');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'onaylandi\')"])').addClass('btn-outline-success').removeClass('btn-success');
                        break;
                    case 'iptal_edildi':
                        $('.order-filters .btn[onclick*="filterOrders(\'iptal_edildi\')"]').addClass('btn-danger').removeClass('btn-outline-danger');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'iptal_edildi\')"])').addClass('btn-outline-danger').removeClass('btn-danger');
                        break;
                    case 'tamamlandi':
                        $('.order-filters .btn[onclick*="filterOrders(\'tamamlandi\')"]').addClass('btn-info').removeClass('btn-outline-info');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'tamamlandi\')"])').addClass('btn-outline-info').removeClass('btn-info');
                        break;
                    default:
                        $('.order-filters .btn[onclick*="filterOrders(\'all\')"]').addClass('btn-primary');
                        $('.order-filters .btn:not([onclick*="filterOrders(\'all\')"])').addClass('btn-outline-primary');
                }
            }

            // Initialize filter buttons based on initial status
            updateFilterButtons(initialStatus);

            // Function to show alerts
            function showAlert(message, type) {
                // Remove existing alerts
                $('.alert').remove();

                var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                var alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <i class="fas ${icon} fa-2x mr-3"></i>
                    <div>
                        ${message}
                    </div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

                // Insert alert after page header
                $('.page-header').after(alertHtml);

                // Auto-hide messages after 3 seconds
                setTimeout(function () {
                    $('.alert').fadeOut(function () {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Load orders by status
            function loadOrders(status) {
                // Show loading indicator
                $('#ordersTableBody').html(`
                <tr>
                    <td colspan="6" class="text-center p-4">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Yükleniyor...</span>
                            </div>
                            <span class="ml-2">Siparişler yükleniyor...</span>
                        </div>
                    </td>
                </tr>
            `);

                $.ajax({
                    url: 'api_islemleri/musteri_siparis_islemler.php?action=get_orders&status=' + status,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var ordersHtml = '';
                            var orders = response.data;

                            if (orders.length > 0) {
                                $.each(orders, function (index, order) {
                                    // Set status badge style based on status
                                    var statusClass = '';
                                    var statusText = '';

                                    switch (order.durum) {
                                        case 'beklemede':
                                            statusClass = 'badge-warning text-dark';
                                            statusText = 'Beklemede';
                                            break;
                                        case 'onaylandi':
                                            statusClass = 'badge-success';
                                            statusText = 'Onaylandı';
                                            break;
                                        case 'iptal_edildi':
                                            statusClass = 'badge-danger';
                                            statusText = 'İptal Edildi';
                                            break;
                                        case 'tamamlandi':
                                            statusClass = 'badge-info';
                                            statusText = 'Tamamlandı';
                                            break;
                                        default:
                                            statusClass = 'badge-secondary';
                                            statusText = order.durum;
                                    }

                                    ordersHtml += `
                                    <tr>
                                        <td>#${order.siparis_id}</td>
                                        <td>${new Date(order.tarih).toLocaleString('tr-TR')}</td>
                                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                                        <td>${order.toplam_adet || 0}</td>
                                        <td>${order.aciklama ? order.aciklama.substring(0, 30) + (order.aciklama.length > 30 ? '...' : '') : '-'}</td>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm view-order-btn"
                                                    data-id="${order.siparis_id}"
                                                    data-status="${order.durum}">
                                                <i class="fas fa-eye"></i>  Detay
                                            </button>
                                            ${order.durum === 'beklemede' ?
                                            `<button class="btn btn-danger btn-sm cancel-order-btn"
                                                        data-id="${order.siparis_id}">
                                                    <i class="fas fa-times"></i> İptal
                                                </button>` : ''}
                                        </td>
                                    </tr>
                                `;
                                });
                            } else {
                                $('#ordersTableBody').html('');
                                $('#noOrdersMessage').show();
                                return;
                            }

                            $('#ordersTableBody').html(ordersHtml);
                            $('#noOrdersMessage').hide();

                            // Add event listeners for view order buttons
                            $('.view-order-btn').on('click', function () {
                                var orderId = $(this).data('id');
                                var status = $(this).data('status');
                                openOrderModal(orderId, status);
                            });

                            // Add event listeners for cancel order buttons
                            $(document).on('click', '.cancel-order-btn', function (e) {
                                e.preventDefault();
                                e.stopPropagation();

                                var $button = $(this);
                                var orderId = $button.data('id');

                                if ($button.prop('disabled')) return;

                                Swal.fire({
                                    title: 'Emin misiniz?',
                                    text: 'Siparişi iptal etmek istediğinize emin misiniz?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Evet',
                                    cancelButtonText: 'İptal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                                        $.ajax({
                                            url: 'api_islemleri/musteri_siparis_islemler.php',
                                            type: 'POST',
                                            data: {
                                                action: 'cancel_order',
                                                siparis_id: orderId
                                            },
                                            dataType: 'json',
                                            success: function (response) {
                                                if (response.status === 'success') {
                                                    showAlert(response.message, 'success');
                                                    // Reload orders to reflect the change
                                                    loadOrders('all');
                                                } else {
                                                    showAlert(response.message, 'danger');
                                                    $button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                                                }
                                            },
                                            error: function () {
                                                showAlert('Sipariş iptal edilirken bir hata oluştu.', 'danger');
                                                $button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                                            }
                                        });
                                    }
                                });
                            });
                        } else {
                            $('#ordersTableBody').html(`
                            <tr>
                                <td colspan="6" class="text-center p-4 text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> ${response.message}
                                </td>
                            </tr>
                        `);
                            $('#noOrdersMessage').hide();
                        }
                    },
                    error: function () {
                        $('#ordersTableBody').html(`
                        <tr>
                            <td colspan="6" class="text-center p-4 text-danger">
                                <i class="fas fa-exclamation-circle"></i> Siparişler yüklenirken bir hata oluştu.
                            </td>
                        </tr>
                    `);
                        $('#noOrdersMessage').hide();
                    }
                });
            }

            // Filter orders by status
            window.filterOrders = function (status) {
                loadOrders(status);
                updateFilterButtons(status);
            };

            // Open order modal for viewing/editing
            function openOrderModal(orderId, status) {
                // Load order details
                $.ajax({
                    url: 'api_islemleri/musteri_siparis_islemler.php?action=get_order&siparis_id=' + orderId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var order = response.data;
                            $('#orderId').val(order.siparis_id);
                            $('#orderDescription').val(order.aciklama || '');

                            // Set appropriate title based on status
                            var statusText = '';
                            switch (status) {
                                case 'beklemede':
                                    statusText = 'Beklemede';
                                    break;
                                case 'onaylandi':
                                    statusText = 'Onaylandı';
                                    break;
                                case 'iptal_edildi':
                                    statusText = 'İptal Edildi';
                                    break;
                                case 'tamamlandi':
                                    statusText = 'Tamamlandı';
                                    break;
                                default:
                                    statusText = status;
                            }

                            $('#orderTitleText').html(`Sipariş #${order.siparis_id} <small class="text-light">(${statusText})</small>`);

                            // Load order items
                            loadOrderItems(orderId);

                            $('#orderModal').modal('show');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showAlert('Sipariş detayı yüklenirken bir hata oluştu.', 'danger');
                    }
                });
            }

            // Load order items
            function loadOrderItems(orderId) {
                $.ajax({
                    url: 'api_islemleri/musteri_siparis_islemler.php?action=get_order_items&siparis_id=' + orderId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var itemsHtml = '';
                            var items = response.data;

                            if (items.length > 0) {
                                itemsHtml += '<div class="table-responsive"><table class="table table-borderless mb-0"><thead class="bg-light"><tr><th>Ürün</th><th class="text-center">Adet</th><th class="text-center">Birim</th></tr></thead><tbody>';

                                $.each(items, function (index, item) {
                                    itemsHtml += `
                                    <tr class="border-bottom">
                                        <td>${item.urun_ismi}</td>
                                        <td class="text-center"><span class="badge badge-primary">${item.adet}</span></td>
                                        <td class="text-center">${item.birim}</td>
                                    </tr>
                                `;
                                });

                                itemsHtml += '</tbody></table></div>';
                            } else {
                                itemsHtml = '<div class="text-center py-3"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">Sipariş kalemi bulunmuyor.</p></div>';
                            }

                            $('#orderItemsList').html(itemsHtml);
                        } else {
                            $('#orderItemsList').html('<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">Sipariş kalemleri yüklenirken hata oluştu.</p></div>');
                        }
                    },
                    error: function () {
                        $('#orderItemsList').html('<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">Sipariş kalemleri yüklenirken hata oluştu.</p></div>');
                    }
                });
            }

            // --- Mobile Bottom Nav ---
            const bottomNavItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');

            bottomNavItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');

                    // Allow default behavior for external links
                    if (href === 'change_password.php' || href === 'logout.php' || href === 'customer_panel.php' || href.includes('customer_panel.php#')) {
                        return;
                    }

                    e.preventDefault();

                    // Set active state
                    bottomNavItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            // --- End Mobile Bottom Nav ---
        });
    </script>
</body>

</html>
