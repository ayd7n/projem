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
if (!yetkisi_var('page:view:musteri_siparisleri')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get all customers for the dropdown
$customers_query = "SELECT musteri_id, musteri_adi FROM musteriler WHERE giris_yetkisi = 1 ORDER BY musteri_adi";
$customers_result = $connection->query($customers_query);

// Get all products for the search/dropdown
$products_query = "SELECT urun_kodu, urun_ismi, stok_miktari, birim, satis_fiyati FROM urunler WHERE stok_miktari > 0 ORDER BY urun_ismi";
$products_result = $connection->query($products_query);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Yeni Müşteri Siparişi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    
    <style>
        body {
            background-color: #fcfcfc; /* Very light gray, almost white */
            color: #444;
        }
        
        /* Minimalist Card */
        .card {
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border-radius: 12px;
            background: #fff;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f5f5f5;
            padding: 1.25rem 1.5rem;
        }
        .card-header h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-body {
            padding: 1.5rem;
        }

        /* Minimalist Form Elements */
        .form-group label {
            font-weight: 500;
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.4rem;
        }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            background-color: #fff;
            box-shadow: none;
            height: auto;
        }
        .form-control:focus {
            border-color: #bbb;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.03);
        }

        /* Minimalist Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            box-shadow: none !important;
        }
        .btn-primary {
            background-color: #333;
            border-color: #333;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #000;
            border-color: #000;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-secondary {
            border-color: #ddd;
            color: #666;
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #333;
            border-color: #ccc;
        }

        /* Minimalist Table */
        .table {
            color: #555;
        }
        .table th {
            border-top: none;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #888;
            padding: 1rem;
        }
        .table td {
            border-top: 1px solid #f9f9f9;
            padding: 1rem;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #fafafa;
        }

        /* Select2 Minimalist Override */
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 8px) !important; /* Match form-control height */
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem + 6px);
            padding-left: 1rem;
            color: #444;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            line-height: calc(1.5em + 0.75rem + 6px);
            color: #999;
        }

        /* Total Card */
        .total-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: right;
        }
        .total-label {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 0.2rem;
        }
        .total-amount {
            font-size: 2rem;
            font-weight: 300;
            color: #333;
            line-height: 1;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 300;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .page-header p {
            color: #888;
            font-weight: 300;
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
                        <a class="nav-link" href="musteri_siparisleri.php">Siparişler</a>
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

    <div class="main-content">
        <div class="container-fluid mt-4">
            <div class="page-header">
                <h1>Yeni Müşteri Siparişi</h1>
                <p>Müşteri adına sipariş oluşturma ekranı</p>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <!-- Customer Selection -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Müşteri</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="customerSelect">Müşteri Seçimi</label>
                                <select class="form-control select2" id="customerSelect" style="width: 100%;">
                                    <option value="">Müşteri Ara...</option>
                                    <?php while($customer = $customers_result->fetch_assoc()): ?>
                                        <option value="<?php echo $customer['musteri_id']; ?>">
                                            <?php echo htmlspecialchars($customer['musteri_adi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label for="orderDescription">Notlar</label>
                                <textarea class="form-control" id="orderDescription" rows="3" placeholder="Sipariş notu (opsiyonel)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Add Product -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Ürün Ekle</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="productSelect">Ürün Seçimi</label>
                                <select class="form-control select2" id="productSelect" style="width: 100%;">
                                    <option value="">Ürün Ara...</option>
                                    <?php foreach($products as $product): ?>
                                        <option value="<?php echo $product['urun_kodu']; ?>" 
                                                data-price="<?php echo $product['satis_fiyati']; ?>"
                                                data-unit="<?php echo htmlspecialchars($product['birim']); ?>"
                                                data-stock="<?php echo $product['stok_miktari']; ?>">
                                            <?php echo htmlspecialchars($product['urun_ismi']); ?> 
                                            (Stok: <?php echo $product['stok_miktari']; ?> <?php echo htmlspecialchars($product['birim']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Birim Fiyat</label>
                                        <input type="text" class="form-control" id="productPrice" readonly style="background-color: #fcfcfc;">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="productQuantity">Adet</label>
                                        <input type="number" class="form-control" id="productQuantity" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-block mt-2" id="addProductBtn">
                                Ekle
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Order Summary -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h2>Sipariş Detayları</h2>
                            <span class="text-muted small" id="itemCountText">0 kalem ürün</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="orderTable">
                                    <thead>
                                        <tr>
                                            <th class="pl-4">Ürün</th>
                                            <th class="text-center">Birim</th>
                                            <th class="text-right">Fiyat</th>
                                            <th class="text-center">Adet</th>
                                            <th class="text-right">Tutar</th>
                                            <th class="text-right pr-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="emptyRow">
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <small>Henüz ürün eklenmedi.</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row align-items-center mt-4" id="orderFooter" style="display: none;">
                        <div class="col-md-6">
                            <div class="total-card">
                                <div class="total-label">Genel Toplam</div>
                                <div class="total-amount" id="grandTotal">0.00 ₺</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-success btn-lg px-5" id="createOrderBtn">
                                Siparişi Tamamla
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                language: "tr",
                width: '100%',
                placeholder: "Seçiniz..."
            });

            // Update price input when product changes
            $('#productSelect').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const price = selectedOption.data('price');
                if (price) {
                    $('#productPrice').val(parseFloat(price).toFixed(2) + ' ₺');
                } else {
                    $('#productPrice').val('');
                }
            });

            let orderItems = [];

            // Add product to list
            $('#addProductBtn').click(function() {
                const productId = $('#productSelect').val();
                const quantity = parseInt($('#productQuantity').val());
                
                if (!productId) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Ürün Seçiniz',
                        text: 'Lütfen listeye eklemek için bir ürün seçin.',
                        confirmButtonColor: '#333'
                    });
                    return;
                }
                
                if (quantity <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Adet Giriniz',
                        text: 'Lütfen geçerli bir adet girin.',
                        confirmButtonColor: '#333'
                    });
                    return;
                }

                const selectedOption = $('#productSelect').find(':selected');
                const productName = selectedOption.text().split('(')[0].trim();
                const unit = selectedOption.data('unit');
                const stock = parseInt(selectedOption.data('stock'));
                const price = parseFloat(selectedOption.data('price'));

                if (quantity > stock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stok Yetersiz',
                        text: `Mevcut stok: ${stock} ${unit}`,
                        confirmButtonColor: '#333'
                    });
                    return;
                }

                // Check if product already exists in list
                const existingItemIndex = orderItems.findIndex(item => item.id === productId);
                
                if (existingItemIndex > -1) {
                    // Update quantity
                    const newQuantity = orderItems[existingItemIndex].quantity + quantity;
                    if (newQuantity > stock) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Stok Sınırı',
                            text: `Toplam miktar stoktan fazla olamaz. Mevcut stok: ${stock}`,
                            confirmButtonColor: '#333'
                        });
                        return;
                    }
                    orderItems[existingItemIndex].quantity = newQuantity;
                    orderItems[existingItemIndex].total = newQuantity * price;
                } else {
                    // Add new item
                    orderItems.push({
                        id: productId,
                        name: productName,
                        unit: unit,
                        quantity: quantity,
                        price: price,
                        total: quantity * price
                    });
                }

                renderOrderTable();
                
                // Reset product selection
                $('#productSelect').val('').trigger('change');
                $('#productQuantity').val(1);
                $('#productPrice').val('');
                
                // Minimal toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: false,
                    background: '#333',
                    color: '#fff'
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Eklendi',
                    iconColor: '#fff'
                });
            });

            // Remove item from list
            $(document).on('click', '.remove-item', function() {
                const index = $(this).data('index');
                orderItems.splice(index, 1);
                renderOrderTable();
            });

            function renderOrderTable() {
                const tbody = $('#orderTable tbody');
                tbody.empty();

                if (orderItems.length === 0) {
                    tbody.html(`
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-5">
                                <small>Henüz ürün eklenmedi.</small>
                            </td>
                        </tr>
                    `);
                    $('#createOrderBtn').prop('disabled', true);
                    $('#orderFooter').hide();
                    $('#itemCountText').text('0 kalem ürün');
                } else {
                    let grandTotal = 0;
                    
                    orderItems.forEach((item, index) => {
                        grandTotal += item.total;
                        tbody.append(`
                            <tr>
                                <td class="pl-4 font-weight-bold text-dark">${item.name}</td>
                                <td class="text-center text-muted small">${item.unit}</td>
                                <td class="text-right">${item.price.toFixed(2)} ₺</td>
                                <td class="text-center">
                                    ${item.quantity}
                                </td>
                                <td class="text-right font-weight-bold">${item.total.toFixed(2)} ₺</td>
                                <td class="text-right pr-4">
                                    <button class="btn btn-sm btn-link text-muted remove-item" data-index="${index}" title="Sil" style="padding: 0;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    $('#grandTotal').text(grandTotal.toFixed(2) + ' ₺');
                    $('#orderFooter').fadeIn();
                    $('#createOrderBtn').prop('disabled', false);
                    $('#itemCountText').text(orderItems.length + ' kalem ürün');
                }
            }

            // Create Order
            $('#createOrderBtn').click(function() {
                const customerId = $('#customerSelect').val();
                const description = $('#orderDescription').val();

                if (!customerId) {
                    Swal.fire('Uyarı', 'Lütfen bir müşteri seçin.', 'warning');
                    return;
                }

                if (orderItems.length === 0) {
                    Swal.fire('Uyarı', 'Sepet boş.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Onaylıyor musunuz?',
                    text: "Sipariş oluşturulacak.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#333',
                    cancelButtonColor: '#999',
                    confirmButtonText: 'Evet, Oluştur',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send to backend
                        $.ajax({
                            url: 'api_islemleri/admin_order_operations.php',
                            type: 'POST',
                            data: {
                                action: 'create_order',
                                musteri_id: customerId,
                                aciklama: description,
                                items: orderItems
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire(
                                        'Başarılı',
                                        'Sipariş oluşturuldu.',
                                        'success'
                                    ).then(() => {
                                        window.location.href = 'musteri_siparisleri.php';
                                    });
                                } else {
                                    Swal.fire(
                                        'Hata',
                                        response.message || 'Bir hata oluştu.',
                                        'error'
                                    );
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Hata',
                                    'Sunucu hatası.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
