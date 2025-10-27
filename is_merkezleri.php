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

// Calculate total work centers
$total_result = $connection->query("SELECT COUNT(*) as total FROM is_merkezleri");
$total_work_centers = $total_result->fetch_assoc()['total'] ?? 0;

// Fetch all work centers
$work_centers_query = "SELECT * FROM is_merkezleri ORDER BY isim";
$work_centers_result = $connection->query($work_centers_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>İş Merkezleri - Parfüm ERP</title>
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
                <h1>İş Merkezleri Yönetimi</h1>
                <p>İş merkezlerini tanımlayın ve yönetin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addWorkCenterBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni İş Merkezi Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;"><?php echo $total_work_centers; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam İş Merkezi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> İş Merkezi Listesi</h2>
                <div class="search-container">
                    <div class="input-group" style="width: 300px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="searchInput" placeholder="İş merkezi ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-tag"></i> İş Merkezi Adı</th>
                                <th><i class="fas fa-sticky-note"></i> Açıklama</th>
                            </tr>
                        </thead>
                        <tbody id="workCentersTableBody">
                            <!-- Data will be loaded via AJAX -->
                            <tr>
                                <td colspan="3" class="text-center p-4">Yükleniyor...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination controls -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select id="recordsPerPage" class="form-control d-inline-block" style="width: auto; margin-left: 8px;">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="pagination-info mr-3">
                            <small class="text-muted" id="paginationInfo">Yükleniyor...</small>
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="paginationList">
                                <!-- Pagination links will be loaded via AJAX -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Center Modal -->
    <div class="modal fade" id="workCenterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="workCenterForm">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-edit"></i> İş Merkezi Formu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="is_merkezi_id" name="is_merkezi_id">
                        <input type="hidden" id="action" name="action">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="isim">İş Merkezi Adı *</label>
                                    <input type="text" class="form-control" id="isim" name="isim" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="aciklama">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="2"></textarea>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    $(document).ready(function() {
        
        let currentPage = 1;
        let totalRecords = 0;
        let totalPages = 0;
        let currentSearch = '';
        let currentLimit = 10;
        
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
        
        function loadWorkCenters(page = 1) {
            currentPage = page;
            const url = `api_islemleri/is_merkezleri_islemler.php?action=get_work_centers_paginated&page=${page}&limit=${currentLimit}&search=${encodeURIComponent(currentSearch)}`;
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Populate table with data
                        const tbody = $('#workCentersTableBody');
                        tbody.empty();
                        
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, workCenter) {
                                const row = `
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="${workCenter.is_merkezi_id}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="${workCenter.is_merkezi_id}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><strong>${workCenter.isim}</strong></td>
                                        <td>${workCenter.aciklama || ''}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                            
                            // Update pagination info
                            totalRecords = response.pagination.total;
                            totalPages = response.pagination.total_pages;
                            const startRecord = (page - 1) * currentLimit + 1;
                            const endRecord = Math.min(page * currentLimit, totalRecords);
                            
                            $('#paginationInfo').text(`${startRecord}-${endRecord} arası gösteriliyor, toplam ${totalRecords} kayıttan`);
                        } else {
                            tbody.append('<tr><td colspan="3" class="text-center p-4">Kayıt bulunamadı.</td></tr>');
                            totalRecords = 0;
                            totalPages = 0;
                            $('#paginationInfo').text('Gösterilecek kayıt yok');
                        }
                        
                        // Update pagination controls
                        updatePaginationControls();
                        
                        // Rebind event handlers for dynamically added elements
                        $('.edit-btn').off('click').on('click', function() {
                            var workCenterId = $(this).data('id');
                            $.ajax({
                                url: 'api_islemleri/is_merkezleri_islemler.php?action=get_work_center&id=' + workCenterId,
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status === 'success') {
                                        var workCenter = response.data;
                                        $('#workCenterForm')[0].reset();
                                        $('#modalTitle').text('İş Merkezini Düzenle');
                                        $('#action').val('update_work_center');
                                        $('#is_merkezi_id').val(workCenter.is_merkezi_id);
                                        $('#isim').val(workCenter.isim);
                                        $('#aciklama').val(workCenter.aciklama);
                                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                                        $('#workCenterModal').modal('show');
                                    } else {
                                        showAlert(response.message, 'danger');
                                    }
                                },
                                error: function() {
                                    showAlert('İş merkezi bilgileri alınırken bir hata oluştu.', 'danger');
                                }
                            });
                        });
                        
                        $('.delete-btn').off('click').on('click', function() {
                            var workCenterId = $(this).data('id');
                            Swal.fire({
                                title: 'Emin misiniz?',
                                text: 'Bu iş merkezini silmek istediğinizden emin misiniz?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Evet',
                                cancelButtonText: 'İptal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: 'api_islemleri/is_merkezleri_islemler.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete_work_center',
                                            is_merkezi_id: workCenterId
                                        },
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                showAlert(response.message, 'success');
                                                // Refresh current page after delete
                                                loadWorkCenters(currentPage);
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
                    } else {
                        showAlert(response.message || 'İş merkezleri yüklenirken bir hata oluştu.', 'danger');
                    }
                },
                error: function() {
                    showAlert('İş merkezleri yüklenirken bir hata oluştu.', 'danger');
                }
            });
        }
        
        function updatePaginationControls() {
            const paginationList = $('#paginationList');
            paginationList.empty();
            
            if (totalPages <= 1) {
                paginationList.append('<li class="page-item disabled"><a class="page-link" href="#">Sayfa yok</a></li>');
                return;
            }
            
            // Previous button
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            const prevLink = currentPage === 1 ? '#' : 'javascript:loadWorkCenters(' + (currentPage - 1) + ')';
            paginationList.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="${prevLink}" onclick="if(${!prevDisabled})loadWorkCenters(${currentPage - 1})"><i class="fas fa-chevron-left"></i> Önceki</a></li>`);
            
            // First page and ellipsis if needed
            if (currentPage > 3) {
                paginationList.append(`<li class="page-item"><a class="page-link" href="javascript:loadWorkCenters(1)">1</a></li>`);
                if (currentPage > 4) {
                    paginationList.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }
            
            // Page numbers around current page
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                const activeClass = i === currentPage ? 'active' : '';
                paginationList.append(`<li class="page-item ${activeClass}"><a class="page-link" href="javascript:loadWorkCenters(${i})">${i}</a></li>`);
            }
            
            // Last page and ellipsis if needed
            if (currentPage < totalPages - 2) {
                if (currentPage < totalPages - 3) {
                    paginationList.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
                paginationList.append(`<li class="page-item"><a class="page-link" href="javascript:loadWorkCenters(${totalPages})">${totalPages}</a></li>`);
            }
            
            // Next button
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            paginationList.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="javascript:loadWorkCenters(${currentPage + 1})">Sonraki <i class="fas fa-chevron-right"></i></a></li>`);
        }
        
        // Bind event for records per page change
        $('#recordsPerPage').on('change', function() {
            currentLimit = parseInt($(this).val());
            loadWorkCenters(1);  // Load first page with new limit
        });
        
        // Bind event for search input
        $('#searchInput').on('input', function() {
            currentSearch = $(this).val();
            loadWorkCenters(1);  // Load first page with new search
        });
        
        // Open modal for adding a new work center
        $('#addWorkCenterBtn').on('click', function() {
            $('#workCenterForm')[0].reset();
            $('#modalTitle').text('Yeni İş Merkezi Ekle');
            $('#action').val('add_work_center');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#workCenterModal').modal('show');
        });

        // Handle form submission
        $('#workCenterForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/is_merkezleri_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#workCenterModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Refresh current page after save
                        loadWorkCenters(currentPage);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });
        
        // Initial load
        loadWorkCenters(1);
    });
    </script>
</body>
</html>
