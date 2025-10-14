<?php
include '../config.php';

// --- Search & Pagination Logic ---
$search_term = $_GET['search'] ?? '';
$items_per_page = 20;

$sql_where_clause = "WHERE stok_miktari > 0";
$params = [];
$types = '';

if (!empty($search_term)) {
    $sql_where_clause .= " AND urun_ismi LIKE ?";
    $search_param = "%{$search_term}%";
    $params[] = $search_param;
    $types .= 's';
}

// Get total number of products for pagination
$total_products_query = "SELECT COUNT(*) as total FROM urunler " . $sql_where_clause;
$total_stmt = $connection->prepare($total_products_query);
if (!empty($search_term)) {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_products = $total_stmt->get_result()->fetch_assoc()['total'];
$total_stmt->close();

$total_pages = ceil($total_products / $items_per_page);
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
} elseif ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $items_per_page;

// Get available products for the current page
$products_query = "SELECT urun_kodu, urun_ismi FROM urunler " . $sql_where_clause . " ORDER BY urun_ismi LIMIT ? OFFSET ?";
$products_stmt = $connection->prepare($products_query);

if (!empty($search_term)) {
    $products_stmt->bind_param('sii', $search_param, $items_per_page, $offset);
} else {
    $products_stmt->bind_param('ii', $items_per_page, $offset);
}

$products_stmt->execute();
$products_result = $products_stmt->get_result();

// --- HTML Output ---

echo '<div class="card-body">';
if ($products_result->num_rows > 0) {
    while($product = $products_result->fetch_assoc()) {
        echo '<div class="product-item">';
        echo '    <div class="product-name">' . htmlspecialchars($product['urun_ismi']) . '</div>';
        echo '    <form method="POST" class="add-to-cart-form">';
        echo '        <input type="hidden" name="urun_kodu" value="' . $product['urun_kodu'] . '">';
        echo '        <input type="number" class="quantity-input" name="adet" min="1" value="1" required>';
        echo '        <button type="submit" class="btn btn-primary add-btn" name="add_to_cart" title="Sepete Ekle">';
        echo '            <i class="fas fa-plus"></i>';
        echo '        </button>';
        echo '    </form>';
        echo '</div>';
    }
} else {
    echo '<div class="text-center py-5">';
    echo '    <i class="fas fa-search fa-3x text-muted mb-3"></i>';
    echo '    <h4>Aramanızla eşleşen ürün bulunamadı.</h4>';
    echo '</div>';
}
echo '</div>';


// --- Pagination HTML Output ---
if ($total_pages > 1) {
    $search_query_string = !empty($search_term) ? '&search=' . urlencode($search_term) : '';
    echo '<div class="card-footer bg-white">';
    echo '    <nav aria-label="Ürün sayfaları" class="mt-0">';
    echo '        <ul class="pagination justify-content-center mb-0">';
    
    $prev_class = ($current_page <= 1) ? 'disabled' : '';
    echo "<li class='page-item {$prev_class}'><a class='page-link' href='?page=".($current_page - 1)."{$search_query_string}'>Önceki</a></li>";

    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo "<li class='page-item {$active_class}'><a class='page-link' href='?page={$i}{$search_query_string}'>{$i}</a></li>";
    }

    $next_class = ($current_page >= $total_pages) ? 'disabled' : '';
    echo "<li class='page-item {$next_class}'><a class='page-link' href='?page=".($current_page + 1)."{$search_query_string}'>Sonraki</a></li>";

    echo '        </ul>';
    echo '    </nav>';
    echo '</div>';
}
