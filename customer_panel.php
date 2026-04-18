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

// Check if customer still has login access and stock visibility permission
$musteri_id = $_SESSION['user_id'];
$access_check_query = "SELECT giris_yetkisi, stok_goruntuleme_yetkisi FROM musteriler WHERE musteri_id = ?";
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
    // Store stock visibility permission in session
    $_SESSION['stok_goruntuleme_yetkisi'] = $customer['stok_goruntuleme_yetkisi'];
} else {
    // Customer record doesn't exist
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get customer info
$musteri_id = $_SESSION['user_id'];
$musteri_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = ?";
$musteri_stmt = $connection->prepare($musteri_query);
$musteri_stmt->bind_param('i', $musteri_id);
$musteri_stmt->execute();
$musteri_result = $musteri_stmt->get_result();
$musteri = $musteri_result->fetch_assoc();
$musteri_adi = $musteri ? $musteri['musteri_adi'] : 'Müşteri';

function ensure_product_brand_and_box_columns_for_customer($connection)
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    try {
        $column_result = $connection->query("SHOW COLUMNS FROM urunler");
        if (!$column_result) {
            return;
        }

        $columns = [];
        while ($column = $column_result->fetch_assoc()) {
            $columns[$column['Field']] = true;
        }

        if (!isset($columns['marka'])) {
            $connection->query("ALTER TABLE urunler ADD COLUMN marka VARCHAR(255) NOT NULL DEFAULT 'Belirtilmedi' AFTER urun_ismi");
        }

        if (!isset($columns['koli_ici_adet'])) {
            $connection->query("ALTER TABLE urunler ADD COLUMN koli_ici_adet INT UNSIGNED NOT NULL DEFAULT 1 AFTER birim");
        }

        $connection->query("UPDATE urunler SET marka = 'Belirtilmedi' WHERE marka IS NULL OR TRIM(marka) = ''");
        $connection->query("UPDATE urunler SET koli_ici_adet = 1 WHERE koli_ici_adet IS NULL OR koli_ici_adet < 1");
    } catch (Throwable $e) {
        // no-op
    }
}

function customer_parse_positive_int($value)
{
    if (is_string($value)) {
        $value = trim($value);
    }

    if ($value === null || $value === '') {
        return null;
    }

    $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($parsed === false) {
        return null;
    }

    return (int) $parsed;
}

function customer_normalize_cart_unit($value)
{
    $unit = strtolower(trim((string) $value));
    return $unit === 'koli' ? 'koli' : 'adet';
}

function customer_validate_cart_unit_input($value)
{
    if (!is_string($value)) {
        return null;
    }

    $unit = strtolower(trim($value));
    if ($unit === 'adet' || $unit === 'koli') {
        return $unit;
    }

    return null;
}

function customer_get_product_snapshot($connection, $urun_kodu)
{
    $query = "SELECT urun_kodu, urun_ismi, stok_miktari,
                     COALESCE(NULLIF(TRIM(marka), ''), 'Belirtilmedi') AS marka,
                     COALESCE(koli_ici_adet, 1) AS koli_ici_adet
              FROM urunler
              WHERE urun_kodu = ?";
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $urun_kodu);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $product ?: null;
}

function customer_build_cart_item_from_entry($urun_kodu, $entry, $product)
{
    $urun_kodu = (int) $urun_kodu;
    if ($urun_kodu <= 0 || !$product) {
        return null;
    }

    $koli_ici_adet = max(1, (int) ($product['koli_ici_adet'] ?? 1));
    $siparis_birimi = 'adet';
    $siparis_miktari = null;

    if (is_array($entry)) {
        $siparis_birimi = customer_normalize_cart_unit($entry['siparis_birimi'] ?? 'adet');
        $siparis_miktari = customer_parse_positive_int($entry['siparis_miktari'] ?? null);

        if ($siparis_miktari === null) {
            $legacy_adet = customer_parse_positive_int($entry['adet'] ?? null);
            if ($legacy_adet !== null) {
                $siparis_miktari = $legacy_adet;
                $siparis_birimi = 'adet';
            }
        }

        if ($siparis_miktari === null) {
            $existing_gercek_adet = customer_parse_positive_int($entry['gercek_adet'] ?? null);
            if ($existing_gercek_adet !== null) {
                if ($siparis_birimi === 'koli') {
                    $siparis_miktari = max(1, (int) ceil($existing_gercek_adet / $koli_ici_adet));
                } else {
                    $siparis_miktari = $existing_gercek_adet;
                }
            }
        }
    } else {
        $siparis_miktari = customer_parse_positive_int($entry);
        $siparis_birimi = 'adet';
    }

    if ($siparis_miktari === null) {
        return null;
    }

    $gercek_adet = $siparis_birimi === 'koli'
        ? $siparis_miktari * $koli_ici_adet
        : $siparis_miktari;

    return [
        'urun_kodu' => $urun_kodu,
        'urun_ismi' => (string) ($product['urun_ismi'] ?? 'Bilinmeyen Urun'),
        'marka' => (string) ($product['marka'] ?? 'Belirtilmedi'),
        'koli_ici_adet' => $koli_ici_adet,
        'siparis_miktari' => $siparis_miktari,
        'siparis_birimi' => $siparis_birimi,
        'gercek_adet' => $gercek_adet
    ];
}

function customer_cart_item_quantity_label($item)
{
    $siparis_miktari = (int) ($item['siparis_miktari'] ?? 0);
    $gercek_adet = (int) ($item['gercek_adet'] ?? 0);
    $siparis_birimi = customer_normalize_cart_unit($item['siparis_birimi'] ?? 'adet');

    if ($siparis_birimi === 'koli') {
        return $siparis_miktari . ' koli (' . $gercek_adet . ' adet)';
    }

    return $siparis_miktari . ' adet';
}

function customer_normalize_cart($connection, $raw_cart)
{
    $normalized_cart = [];
    if (!is_array($raw_cart)) {
        return $normalized_cart;
    }

    foreach ($raw_cart as $urun_kodu => $entry) {
        $urun_kodu = (int) $urun_kodu;
        if ($urun_kodu <= 0) {
            continue;
        }

        $product = customer_get_product_snapshot($connection, $urun_kodu);
        if (!$product) {
            continue;
        }

        $normalized_item = customer_build_cart_item_from_entry($urun_kodu, $entry, $product);
        if ($normalized_item === null) {
            continue;
        }

        $normalized_cart[$urun_kodu] = $normalized_item;
    }

    return $normalized_cart;
}

ensure_product_brand_and_box_columns_for_customer($connection);

// Get all available products (stock > 0) with their primary photo
// Include stock quantity if customer has permission to see it
$stok_goruntuleme_yetkisi = $_SESSION['stok_goruntuleme_yetkisi'] ?? 0;
if ($stok_goruntuleme_yetkisi == 1) {
    // Customer has permission to see stock quantities
    $products_query = "
        SELECT u.urun_kodu, u.urun_ismi, u.marka, u.koli_ici_adet, u.stok_miktari,
               uf.fotograf_id, uf.dosya_yolu, uf.dosya_adi,
               uf.ana_fotograf
        FROM urunler u
        LEFT JOIN urun_fotograflari uf ON u.urun_kodu = uf.urun_kodu AND uf.ana_fotograf = 1
        WHERE u.stok_miktari > 0
        ORDER BY u.urun_ismi";
} else {
    // Customer does not have permission to see stock quantities
    $products_query = "
        SELECT u.urun_kodu, u.urun_ismi, u.marka, u.koli_ici_adet,
               uf.fotograf_id, uf.dosya_yolu, uf.dosya_adi,
               uf.ana_fotograf
        FROM urunler u
        LEFT JOIN urun_fotograflari uf ON u.urun_kodu = uf.urun_kodu AND uf.ana_fotograf = 1
        WHERE u.stok_miktari > 0
        ORDER BY u.urun_ismi";
}
$products_result = $connection->query($products_query);

// Get total product count
$products_count_query = "SELECT COUNT(*) as count FROM urunler WHERE stok_miktari > 0";
$products_count_result = $connection->query($products_count_query);
$products_count = $products_count_result->fetch_assoc()['count'];

// Normalize cart for backward compatibility (old: urun_kodu => adet)
$raw_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart = customer_normalize_cart($connection, $raw_cart);
$_SESSION['cart'] = $cart;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $urun_kodu = (int) ($_POST['urun_kodu'] ?? 0);
    $siparis_miktari = customer_parse_positive_int($_POST['siparis_miktari'] ?? null);
    $siparis_birimi = customer_validate_cart_unit_input($_POST['siparis_birimi'] ?? null);

    if ($urun_kodu <= 0 || $siparis_miktari === null || $siparis_birimi === null) {
        $error = "Lutfen gecerli bir urun, pozitif miktar ve birim secin.";
    } else {
        $product_row = customer_get_product_snapshot($connection, $urun_kodu);

        if ($product_row) {
            $new_cart_item = customer_build_cart_item_from_entry($urun_kodu, [
                'siparis_miktari' => $siparis_miktari,
                'siparis_birimi' => $siparis_birimi
            ], $product_row);

            $stok_miktari = (int) ($product_row['stok_miktari'] ?? 0);
            $gercek_adet = (int) ($new_cart_item['gercek_adet'] ?? 0);

            if ($stok_miktari <= 0) {
                $error = "Bu urun stokta kalmamis.";
            } elseif ($gercek_adet > $stok_miktari) {
                $error = "Istenen miktar mevcut stogu asiyor.";
            } else {
                // Ezme davranisi: ayni urun yeniden eklenirse onceki satir replace edilir
                $cart[$urun_kodu] = $new_cart_item;
                $_SESSION['cart'] = $cart;
                $message = "Urun sepete eklendi!";
            }
        } else {
            $error = "Urun bulunamadi!";
        }
    }
}

// Order submission is now handled via AJAX only (order_operations.php)
// Direct PHP handling removed to prevent conflicts
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Paneli - Parfüm ERP</title>
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
            padding: 15px;
        }

        .navbar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
        }

        .navbar-brand.brand-title {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--accent, #d4af37) !important;
            font-weight: 700;
            letter-spacing: 0.25px;
            font-size: 0.96rem;
            line-height: 1.1;
            padding-top: 0.1rem !important;
            padding-bottom: 0.1rem !important;
        }

        .navbar-brand.brand-title i {
            font-size: 0.92rem;
        }

        .brand-subtitle {
            font-size: 0.62rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.78);
            border-left: 1px solid rgba(255, 255, 255, 0.35);
            padding-left: 6px;
            margin-left: 2px;
            letter-spacing: 0.12px;
        }

        .navbar-nav .nav-link {
            font-size: 0.79rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9) !important;
            border-radius: 7px;
            padding: 0.34rem 0.56rem !important;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .navbar-nav .nav-item.active .nav-link,
        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.14);
        }

        .user-nav-name {
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .page-header {
            margin-bottom: 12px;
            background: linear-gradient(135deg, rgba(74, 14, 99, 0.05) 0%, rgba(212, 175, 55, 0.05) 100%);
            padding: 12px 14px;
            border-radius: 12px;
            border-left: 3px solid var(--primary);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
            animation: fadeInDown 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .page-header h1 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 3px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        .page-header-kicker {
            font-size: 0.62rem;
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: 0.38px;
            text-transform: uppercase;
            margin-bottom: 2px;
            position: relative;
            z-index: 1;
        }

        .page-header h1 i {
            color: var(--accent);
            -webkit-text-fill-color: var(--accent);
            margin-right: 6px;
            font-size: 1.06rem;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            position: relative;
            z-index: 1;
            line-height: 1.35;
            margin: 0;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        @keyframes fxSuccessPulse {
            0% {
                transform: scale(1);
                filter: brightness(1);
            }

            35% {
                transform: scale(1.06);
                filter: brightness(1.08);
            }

            100% {
                transform: scale(1);
                filter: brightness(1);
            }
        }

        @keyframes fxCardGlow {
            0% {
                box-shadow: 0 0 0 rgba(74, 14, 99, 0);
            }

            35% {
                box-shadow: 0 0 0 4px rgba(74, 14, 99, 0.14), 0 8px 22px rgba(74, 14, 99, 0.16);
            }

            100% {
                box-shadow: 0 0 0 rgba(74, 14, 99, 0);
            }
        }

        @keyframes fxOrderGlow {
            0% {
                box-shadow: 0 0 0 rgba(212, 175, 55, 0);
            }

            30% {
                box-shadow: 0 0 0 6px rgba(212, 175, 55, 0.16), 0 12px 28px rgba(74, 14, 99, 0.2);
            }

            100% {
                box-shadow: 0 0 0 rgba(212, 175, 55, 0);
            }
        }

        .fx-success-pulse {
            animation: fxSuccessPulse 540ms ease-out;
        }

        .fx-card-glow {
            animation: fxCardGlow 820ms ease-out;
        }

        .fx-order-glow {
            animation: fxOrderGlow 1100ms ease-out;
        }

        .fx-celebration-flash {
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            z-index: 3000;
            transition: opacity 180ms ease-out;
            background: radial-gradient(circle at 50% 40%, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 65%);
        }

        .fx-celebration-flash.is-active {
            opacity: 1;
        }

        .fx-celebration-flash.order {
            background: radial-gradient(circle at 50% 35%, rgba(212, 175, 55, 0.18) 0%, rgba(124, 42, 153, 0.08) 35%, rgba(255, 255, 255, 0) 70%);
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeIn 0.6s ease-out;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            padding: 9px 12px;
            background: linear-gradient(135deg, rgba(74, 14, 99, 0.02) 0%, rgba(212, 175, 55, 0.02) 100%);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h2 {
            font-size: 0.94rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-title-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .section-title-row {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .section-subtitle {
            font-size: 0.64rem;
            color: var(--text-secondary);
            font-weight: 500;
            letter-spacing: 0.08px;
            line-height: 1.2;
        }

        .product-count-badge {
            font-size: 0.66rem !important;
            font-weight: 700;
            letter-spacing: 0.06px;
            padding: 3px 8px !important;
        }

        .card-header h2 i {
            color: var(--accent);
            font-size: 0.9rem;
        }

        .card-header .badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 3px 8px;
            font-size: 0.75rem;
            border-radius: 15px;
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
            width: 38px;
            height: 38px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 3px 10px rgba(74, 14, 99, 0.25);
            position: relative;
            overflow: hidden;
        }

        .add-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .add-btn:hover::before {
            width: 80px;
            height: 80px;
        }

        .add-btn i {
            position: relative;
            z-index: 1;
            font-size: 0.9rem;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        #sepet .btn-success {
            background: linear-gradient(45deg, var(--success), #2ecc71);
            border: none;
            width: 100%;
            padding: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        #sepet .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
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
            background: #fff;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 100%;
        }

        .product-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(74, 14, 99, 0.14);
            border-color: var(--primary);
        }

        .product-item:hover::before {
            transform: scaleX(1);
        }

        .product-card-header {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 10px;
            background: rgba(74, 14, 99, 0.02);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .product-name-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
        }

        .product-name {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin: 0;
            transition: color 0.3s ease;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-name-brand,
        .product-name-model {
            min-width: 0;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-name-brand::after {
            content: ' /';
            font-weight: 500;
            color: var(--text-secondary);
        }

        .product-stock-badge {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--primary);
            background: rgba(74, 14, 99, 0.1);
            border-radius: 999px;
            padding: 3px 8px;
            white-space: nowrap;
        }

        .product-box-info {
            font-size: 0.76rem;
            color: var(--text-secondary);
            background: rgba(74, 14, 99, 0.06);
            border-radius: 999px;
            padding: 2px 8px;
            line-height: 1.3;
            width: fit-content;
        }

        .product-photo-area {
            min-height: 180px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            background: linear-gradient(180deg, #faf8ff 0%, #f2edf9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .product-photo-area.has-photo {
            cursor: pointer;
        }

        .product-photo-image {
            width: 100%;
            height: 100%;
            min-height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-photo-area.has-photo:hover .product-photo-image {
            transform: scale(1.04);
        }

        .product-photo-placeholder {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-align: center;
            padding: 0 12px;
        }

        .product-item:hover .product-name {
            color: var(--primary);
        }

        .add-to-cart-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .product-order-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: auto;
        }

        .add-to-cart-form.product-order-row {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 7px 8px;
            background: #fff;
        }

        .order-row-label {
            min-width: 38px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .quantity-input {
            width: 72px;
            padding: 0.45rem 0.55rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--bg-color);
        }

        .quantity-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(74, 14, 99, 0.1);
            background: white;
        }

        .add-to-cart-form.product-order-row .quantity-input {
            flex: 1 1 auto;
            width: auto;
            min-width: 0;
        }

        .add-to-cart-form.product-order-row .add-btn {
            margin-left: 0;
            flex: 0 0 34px;
            width: 34px;
            min-width: 34px;
            max-width: 34px;
            height: 34px;
            min-height: 34px;
            max-height: 34px;
            border-radius: 50%;
        }

        .cart-item {
            padding: 15px 18px;
            /* Daha fazla padding */
            margin-bottom: 10px;
            /* Öğeler arasına boşluk */
            border-bottom: none;
            /* Alt çizgi yerine kutu stili */
            border-radius: 8px;
            /* Yuvarlak köşeler */
            background-color: var(--card-bg);
            /* Arka plan rengi */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            /* Hafif gölge */
            display: flex;
            align-items: center;
            /* Öğeleri dikeyde ortala */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cart-item:last-child {
            margin-bottom: 10px;
            /* Son öğe için de boşluk */
        }

        .cart-item:hover {
            transform: translateY(-3px);
            /* Hover efekti */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .cart-item-content {
            flex-grow: 1;
            padding-right: 15px;
            /* Sağdan boşluk */
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .cart-item-content h4 {
            /* ürün adı için h4 */
            margin-bottom: 5px;
            font-size: 1.05rem;
            /* Biraz daha büyük font */
            color: var(--text-primary);
            /* Daha koyu metin */
            font-weight: 600;
            /* Daha kalın */
        }

        .item-quantity {
            color: var(--primary);
            /* Renk */
            font-size: 0.85rem;
            /* Biraz daha büyük font */
            font-weight: 700;
            background-color: rgba(74, 14, 99, 0.1);
            /* Hafif mor arka plan */
            padding: 4px 10px;
            /* Daha fazla padding */
            border-radius: 20px;
            /* Daha yuvarlak badge */
            display: inline-block;
            align-self: flex-start;
            /* Başlangıca hizala */
        }

        .item-brand {
            color: var(--text-secondary);
            font-size: 0.75rem;
            margin-bottom: 4px;
        }

        .remove-from-cart-btn {
            min-width: 36px;
            /* Buton boyutu */
            height: 36px;
            /* Buton boyutu */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50% !important;
            transition: all 0.3s ease;
            padding: 0 !important;
            background-color: var(--danger);
            /* Kırmızı arka plan */
            color: white;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.2);
        }

        .remove-from-cart-btn:hover {
            background-color: #c82333 !important;
            /* Koyu kırmızı */
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
            transform: scale(1.1);
            /* Hafif büyüt */
        }

        .remove-from-cart-btn i {
            font-size: 0.9rem;
            /* İkon boyutu */
        }

        .empty-cart {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            /* Daha fazla padding */
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-cart i {
            font-size: 4.5rem;
            /* Daha büyük ikon */
            margin-bottom: 20px;
            color: var(--primary);
            opacity: 0.5;
            /* Biraz daha belirgin */
            animation: bounceIn 0.8s ease-out;
            /* Giriş animasyonu */
        }

        .empty-cart h4 {
            color: var(--text-primary);
            /* Daha koyu başlık */
            font-weight: 700;
            /* Daha kalın */
            margin-bottom: 10px;
        }

        .empty-cart p {
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
            }
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(74, 14, 99, 0.25);
        }

        /* Cart panel that slides from right */
        #sepet {
            position: fixed;
            top: 0;
            right: 0;
            width: 420px;
            max-width: 90%;
            height: 100%;
            z-index: 1050;
            border-radius: 0;
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        @media (max-width: 576px) {
            #sepet {
                width: 320px;
                max-width: 95%;
            }
        }

        #sepet.show {
            transform: translateX(0);
        }

        #sepet .card-header {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.8rem 1rem !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        #sepet .card-header h2 {
            color: var(--accent);
        }

        .cart-summary {
            background-color: var(--bg-color);
            border-radius: 8px;
            padding: 12px;
            margin: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            border: 1px solid var(--border-color);
        }

        .cart-summary span {
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
            padding: 8px;
            background-color: #fff;
            border-radius: 6px;
        }

        .cart-summary span strong {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            margin-bottom: 2px;
        }

        #sepet .card-header .close {
            color: white;
            opacity: 0.8;
            text-shadow: none;
        }

        #sepet .card-header .close:hover {
            opacity: 1;
        }

        #sepet .card-body {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 110px);
            /* Header yüksekliği çıkarılıyor */
        }

        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 !important;
        }

        .cart-items-container-inner {
            padding: 10px 12px !important;
        }

        .cart-order-section {
            padding: 12px 15px !important;
            border-top: 1px solid var(--border-color);
            background-color: white;
            margin-top: auto;
        }

        .empty-cart-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
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
            display: none;
            /* Hide total since we're hiding pricing */
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
            /* Metinlerin tek satırda kalmasını sağlar */
        }

        .table th i {
            margin-right: 6px;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
            white-space: nowrap;
            /* Metinlerin tek satırda kalmasını sağlar */
        }

        .table-responsive.past-orders-table {
            /* Geçmiş siparişler tablosu için özel sınıf */
            max-height: 400px;
            /* Maksimum yükseklik */
            overflow-y: auto;
            /* İçerik sığmazsa kaydırma çubuğu */
            border: 1px solid var(--border-color);
            /* Kenarlık ekleyelim, daha derli toplu durur */
            border-radius: 8px;
            /* Köşeleri yuvarlayalım */
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

        .pagination-container {
            margin-top: 20px;
            display: none;
            /* Pagination removed to show all products */
        }

        .pagination {
            justify-content: center;
        }

        .product-item {
            display: none;
        }

        .product-item.visible {
            display: block;
        }

        .products-container {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 8px;
        }

        @media (min-width: 768px) {
            .products-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .products-container {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .page-header>div {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: nowrap;
            }

            .page-header-kicker {
                margin-bottom: 0;
                white-space: nowrap;
                border-right: 1px solid rgba(74, 14, 99, 0.2);
                padding-right: 8px;
            }

            .page-header h1 {
                margin-bottom: 0;
                white-space: nowrap;
                font-size: 1.08rem;
            }

            .page-header p {
                margin-left: auto;
                text-align: right;
                max-width: 56ch;
                font-size: 0.74rem;
                line-height: 1.25;
            }

            .section-title-group {
                flex-direction: row;
                align-items: baseline;
                gap: 8px;
                min-width: 0;
            }

            .section-subtitle {
                white-space: nowrap;
                margin: 0;
            }

            .product-order-actions {
                gap: 4px;
            }

            .add-to-cart-form.product-order-row {
                padding: 4px 5px;
                gap: 4px;
            }

            .order-row-label {
                min-width: 30px;
                font-size: 0.64rem;
                letter-spacing: 0.1px;
            }

            .add-to-cart-form.product-order-row .quantity-input {
                padding: 0.26rem 0.32rem;
                font-size: 0.78rem;
            }

            .add-to-cart-form.product-order-row .add-btn {
                flex: 0 0 26px;
                width: 26px;
                min-width: 26px;
                max-width: 26px;
                height: 26px;
                min-height: 26px;
                max-height: 26px;
            }

            .add-to-cart-form.product-order-row .add-btn i {
                font-size: 0.66rem;
            }

            .main-content {
                padding-left: 6px;
                padding-right: 6px;
            }

            .navbar .container-fluid {
                padding-left: 8px;
                padding-right: 8px;
            }

            #product-list-container .card-body {
                padding-left: 8px;
                padding-right: 8px;
            }

            .product-photo-area {
                aspect-ratio: 1 / 1;
                min-height: 0;
            }

            .product-photo-image {
                min-height: 0;
                height: 100%;
            }
        }

        html {
            scroll-behavior: smooth;
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
            /* Aligns items to the top */
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
            /* Consistent height */
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
            .navbar-brand.brand-title {
                font-size: 0.92rem;
                gap: 6px;
            }

            .brand-subtitle {
                display: none !important;
            }

            .navbar-nav .nav-link {
                font-size: 0.8rem;
                padding: 0.38rem 0.55rem !important;
            }

            .main-content {
                padding: 6px 4px;
                padding-bottom: 80px;
                /* Space for bottom nav */
            }

            .navbar .container-fluid {
                padding-left: 6px;
                padding-right: 6px;
            }

            #product-list-container .card {
                margin-bottom: 10px;
            }

            #product-list-container .card-body {
                padding: 8px 6px;
            }

            .products-container {
                gap: 8px;
                padding: 2px;
            }

            .page-header {
                padding: 10px 12px;
                margin-bottom: 12px;
            }

            .page-header h1 {
                font-size: 1.15rem;
            }

            .page-header-kicker {
                font-size: 0.62rem;
                margin-bottom: 3px;
                letter-spacing: 0.4px;
            }

            .page-header p {
                font-size: 0.78rem;
                line-height: 1.35;
            }

            .card-header {
                padding: 10px;
            }

            .card-header h2 {
                font-size: 0.9rem;
                line-height: 1.2;
            }

            .section-subtitle {
                font-size: 0.66rem;
                line-height: 1.2;
            }

            .product-count-badge {
                font-size: 0.68rem !important;
                padding: 4px 8px !important;
            }

            .search-input {
                font-size: 0.86rem !important;
                padding-top: 10px !important;
                padding-bottom: 10px !important;
            }

            .product-item {
                padding: 8px;
                gap: 8px;
            }

            .product-card-header {
                padding: 6px 7px;
                gap: 4px;
            }

            .product-name-line {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .product-name {
                font-size: 0.74rem;
                line-height: 1.25;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 1px;
                white-space: normal;
                overflow: visible;
                text-overflow: clip;
            }

            .product-name-brand::after {
                content: '';
            }

            .product-name-brand,
            .product-name-model {
                white-space: normal;
                overflow: visible;
                text-overflow: clip;
                line-height: 1.15;
            }

            .product-name-brand {
                font-size: 0.64rem;
                font-weight: 700;
                color: var(--text-secondary);
            }

            .product-name-model {
                font-size: 0.74rem;
                font-weight: 700;
            }

            .product-stock-badge {
                font-size: 0.62rem;
                padding: 2px 6px;
            }

            .product-box-info {
                font-size: 0.65rem;
                padding: 2px 6px;
            }

            .product-photo-area {
                min-height: 120px;
            }

            .product-photo-placeholder {
                font-size: 0.72rem;
            }

            .add-to-cart-form.product-order-row {
                display: flex;
                align-items: center;
                flex-wrap: nowrap;
                padding: 4px 5px;
                gap: 4px;
            }

            .order-row-label {
                min-width: 28px;
                font-size: 0.58rem;
                letter-spacing: 0.1px;
            }

            .add-to-cart-form.product-order-row .quantity-input {
                flex: 1 1 auto;
                width: auto;
                min-width: 0;
                font-size: 0.72rem;
                padding: 0.2rem 0.2rem;
                border-width: 1.5px;
            }

            .add-to-cart-form.product-order-row .add-btn {
                margin-left: 0;
                flex: 0 0 24px;
                width: 24px;
                min-width: 24px;
                max-width: 24px;
                height: 24px;
                min-height: 24px;
                max-height: 24px;
                border-radius: 50%;
            }

            .add-to-cart-form.product-order-row .add-btn i {
                font-size: 0.62rem;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            .mobile-bottom-nav .nav-item .cart-badge {
                font-size: 0.8rem;
                /* Daha büyük font */
                padding: 0.25em 0.5em;
                /* Dolgunluk */
                position: relative;
                top: -1px;
                left: 2px;
                transform: scale(0.9);
                /* Sadece rozetin boyutunu küçültmek için, daha az yer kaplasın */
                transform-origin: left center;
                white-space: nowrap;
                /* Sayı tek satırda kalsın */
            }
        }

        @media (max-width: 991.98px) {
            #sepet.collapse.show {
                position: fixed;
                top: 0;
                right: 0;
                width: 420px;
                max-width: 90%;
                height: 100%;
                z-index: 1050;
                /* Higher than navbar */
                border-radius: 0;
                box-shadow: -5px 0 20px rgba(0, 0, 0, 0.15);
            }

            @media (max-width: 480px) {
                #sepet.collapse.show {
                    width: 320px;
                }
            }
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
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand brand-title" href="customer_panel.php">
                <i class="fas fa-spa"></i>
                <span>IDO KOZMETIK</span>
                <span class="brand-subtitle d-none d-xl-inline">Musteri Siparis Platformu</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item active">
                        <a class="nav-link" href="customer_panel.php">Sipariş Merkezi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customer_orders.php">Sipariş Geçmişi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Hesap Güvenliği</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <span
                                class="user-nav-name"><?php echo htmlspecialchars($musteri_adi); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                    <li class="nav-item d-none d-lg-flex">
                        <button class="btn btn-light cart-toggle-btn" type="button" id="openCartBtn">
                            <i class="fas fa-shopping-cart text-primary"></i> Sepet
                            <span class="badge badge-danger ml-1 cart-badge"><?php echo count($cart); ?></span>
                        </button>
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
                <div class="page-header-kicker">Müşteri Alanı</div>
                <h1><i class="fas fa-store"></i> Sipariş ve Stok Ekranı</h1>
                <p>Stoktaki ürünleri hızlıca görüntüleyebilir, sepetinizi düzenleyerek siparişinizi güvenli şekilde
                    oluşturabilirsiniz.</p>
            </div>
        </div>

        <!-- Mobile Cart Button - Below Page Header -->
        <div class="d-flex justify-content-end mb-3 d-lg-none">
            <button class="btn btn-primary cart-toggle-btn" type="button" id="mobile-cart-btn">
                <i class="fas fa-shopping-cart"></i> Sepet <span
                    class="badge badge-light ml-1 cart-badge"><?php echo count($cart); ?></span>
            </button>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div id="product-list-container">
            <div class="card">
                <div class="card-header">
                    <div class="section-title-group">
                        <div class="section-title-row">
                            <h2><i class="fas fa-box-open"></i> Stoktaki Ürünler</h2>
                            <span class="badge badge-primary product-count-badge"><?php echo $products_count; ?> ürün</span>
                        </div>
                        <div class="section-subtitle">Satışa açık güncel ürün listesi</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3" style="position: relative;">
                        <div
                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: var(--text-secondary);">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" class="form-control search-input" name="search"
                            style="padding-left: 45px; border-radius: 10px; border: 2px solid var(--border-color); font-size: 1rem; padding: 12px 12px 12px 45px; transition: all 0.3s ease;"
                            placeholder="Ürün adıyla ara..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(74, 14, 99, 0.1)';"
                            onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                    </div>
                    <div class="products-container" id="product-items-wrapper">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-item"
                                    data-name="<?php echo strtolower(htmlspecialchars(($product['urun_ismi'] ?? '') . ' ' . ($product['marka'] ?? ''))); ?>">
                                    <div class="product-card-header">
                                        <div class="product-name-line">
                                            <div class="product-name">
                                                <span class="product-name-brand"><?php echo htmlspecialchars($product['marka'] ?? 'Belirtilmedi'); ?></span>
                                                <span class="product-name-model"><?php echo htmlspecialchars($product['urun_ismi'] ?? 'Bilinmeyen Urun'); ?></span>
                                            </div>
                                            <?php if (isset($_SESSION['stok_goruntuleme_yetkisi']) && $_SESSION['stok_goruntuleme_yetkisi'] == 1): ?>
                                                <span class="product-stock-badge"><?php echo (int) ($product['stok_miktari'] ?? 0); ?> adet</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="product-box-info">1 koli = <?php echo max(1, (int) ($product['koli_ici_adet'] ?? 1)); ?> adet</span>
                                    </div>

                                    <?php if (!empty($product['fotograf_id']) && !empty($product['dosya_yolu'])): ?>
                                        <div class="product-photo-area has-photo"
                                            onclick="openProductGallery(<?php echo $product['urun_kodu']; ?>)">
                                            <img class="product-photo-image"
                                                src="<?php echo htmlspecialchars($product['dosya_yolu']); ?>"
                                                alt="<?php echo htmlspecialchars($product['urun_ismi'] ?? 'Urun'); ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="product-photo-area">
                                            <div class="product-photo-placeholder">Fotograf yok</div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="product-order-actions">
                                        <form method="POST" class="add-to-cart-form product-order-row">
                                            <span class="order-row-label">Koli</span>
                                            <input type="hidden" name="urun_kodu" value="<?php echo $product['urun_kodu']; ?>">
                                            <input type="hidden" name="siparis_birimi" value="koli">
                                            <input type="number" class="quantity-input" name="siparis_miktari" min="1" step="1" value="1" required>
                                            <button type="submit" class="btn btn-primary add-btn" name="add_to_cart"
                                                title="Koli Ekle">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>

                                        <form method="POST" class="add-to-cart-form product-order-row">
                                            <span class="order-row-label">Adet</span>
                                            <input type="hidden" name="urun_kodu" value="<?php echo $product['urun_kodu']; ?>">
                                            <input type="hidden" name="siparis_birimi" value="adet">
                                            <input type="number" class="quantity-input" name="siparis_miktari" min="1" step="1" value="1" required>
                                            <button type="submit" class="btn btn-primary add-btn" name="add_to_cart"
                                                title="Adet Ekle">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 no-products-message">
                                <i class="fas fa-box-open"
                                    style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                                <h4>Şu anda stokta ürün bulunmamaktadır.</h4>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center py-5 no-results-message" style="display: none;">
                        <i class="fas fa-search"
                            style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                        <h4>Aramanızla eşleşen ürün bulunamadı.</h4>
                        <p class="text-muted">Farklı bir anahtar kelime ile tekrar deneyin.</p>
                    </div>
                </div>
            </div>
        </div>



        <!-- Overlay for cart -->
        <div class="cart-overlay" id="cartOverlay"></div>

        <!-- Shopping Cart - Slides from right -->
        <div class="card" id="sepet">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-shopping-cart"></i> Sepet ve Sipariş</h2>
                <button type="button" class="close" id="closeCartBtn" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($cart)):
                    // Calculate total different products and total quantity
                    $total_different_products = count($cart);
                    $total_quantity = 0;
                    foreach ($cart as $cart_item_summary) {
                        $total_quantity += (int) ($cart_item_summary['gercek_adet'] ?? 0);
                    }
                    ?>
                    <div class="cart-items-container">
                        <div class="cart-summary">
                            <span><strong><?php echo $total_different_products; ?></strong> farklı ürün</span>
                            <span><strong><?php echo $total_quantity; ?></strong> adet</span>
                        </div>
                        <div class="cart-items-container-inner">
                            <?php
                            foreach ($cart as $urun_kodu => $cart_item) {
                                if (!is_array($cart_item)) {
                                    continue;
                                }
                                    ?>
                                    <div class="cart-item">
                                        <div class="cart-item-content">
                                            <h4 class="mb-1"><?php echo htmlspecialchars($cart_item['urun_ismi'] ?? 'Bilinmeyen Urun'); ?></h4>
                                            <div class="item-brand">Marka: <?php echo htmlspecialchars($cart_item['marka'] ?? 'Belirtilmedi'); ?></div>
                                            <div class="item-quantity"><?php echo htmlspecialchars(customer_cart_item_quantity_label($cart_item)); ?></div>
                                        </div>
                                        <a href="#" class="btn btn-outline-danger remove-from-cart-btn"
                                            data-urun-kodu="<?php echo $urun_kodu; ?>" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                    <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="cart-order-section">
                        <form method="POST" name="submit_order" class="mb-0">
                            <div class="form-group mb-3">
                                <label for="order_description">Sipariş Açıklaması (Opsiyonel)</label>
                                <textarea class="form-control" id="order_description" name="order_description"
                                    placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..."
                                    rows="2"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100" name="submit_order">
                                <i class="fas fa-paper-plane"></i> Siparişi Oluştur
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-cart-section">
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart text-muted"></i>
                            <h4>Sepetiniz Boş</h4>
                            <p class="text-muted">Sepetinize ürün eklemek için ürünler kısmından seçim yapabilirsiniz.</p>
                        </div>
                    <?php endif; ?>
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
                                placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..."
                                readonly></textarea>
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

        <!-- Product Photo Lightbox -->
        <div id="productPhotoLightbox"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;">
            <button onclick="closeProductGallery()"
                style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; background: none; border: none; cursor: pointer; z-index: 10000;">
                <i class="fas fa-times"></i>
            </button>

            <button id="prevPhotoBtn" onclick="previousProductPhoto()"
                style="position: absolute; left: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: none; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div style="max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center;">
                <img id="lightboxImage" src="" alt=""
                    style="max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 8px;">
                <div style="color: white; margin-top: 15px; text-align: center;">
                    <p id="lightboxProductName" style="margin: 5px 0; font-size: 18px; font-weight: bold;"></p>
                    <small id="lightboxPhotoCounter" style="opacity: 0.7;"></small>
                </div>
            </div>

            <button id="nextPhotoBtn" onclick="nextProductPhoto()"
                style="position: absolute; right: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: none; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>


        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-bottom-nav">
            <a href="customer_panel.php" class="nav-item active">
                <i class="fas fa-store"></i>
                <span class="nav-text">Mağaza</span>
            </a>
            <a href="#" id="mobile-nav-cart" class="nav-item cart-toggle-btn">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-text">Sepet <span
                        class="badge badge-danger ml-1 cart-badge"><?php echo count($cart); ?></span></span>
            </a>
            <a href="customer_orders.php" id="mobile-nav-orders" class="nav-item">
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
        <!-- Canvas Confetti -->
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
        <script>
            // Product pagination variables - declared globally so they can be accessed from window.load event
            // Pagination has been removed to show all products at once
            var currentPaginationPage = 1;
            var itemsPerPage = 9999; // Set to high number to show all products
            var allProducts = []; // Will store all product elements

            $(document).ready(function () {
                // Initialize product pagination
                initializePagination();

                // Mobile menu toggle
                const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
                const sidebar = document.querySelector('.sidebar');

                if (mobileMenuBtn) {
                    mobileMenuBtn.addEventListener('click', function () {
                        sidebar.classList.toggle('active');
                    });
                }

                // Highlight active nav link
                const currentNavPage = window.location.pathname.split('/').pop();
                const navLinks = document.querySelectorAll('.nav-links a');

                navLinks.forEach(link => {
                    const linkPage = link.getAttribute('href').split('/').pop();
                    if (currentNavPage === linkPage || (currentNavPage === '' && linkPage === 'index.php') || (currentNavPage === 'customer_panel.php' && linkPage === '#')) {
                        link.classList.add('active');
                    }
                });

                function escapeHtml(value) {
                    return $('<div>').text(value || '').html();
                }

                function formatCartItemQuantity(item) {
                    const siparisBirimi = ((item && item.siparis_birimi) ? item.siparis_birimi : 'adet').toString().toLowerCase() === 'koli' ? 'koli' : 'adet';
                    const siparisMiktari = parseInt(item && item.siparis_miktari, 10);
                    const gercekAdet = parseInt(item && (item.gercek_adet ?? item.adet), 10);

                    const safeSiparisMiktari = Number.isInteger(siparisMiktari) && siparisMiktari > 0 ? siparisMiktari : 0;
                    const safeGercekAdet = Number.isInteger(gercekAdet) && gercekAdet > 0 ? gercekAdet : 0;

                    if (siparisBirimi === 'koli') {
                        return `${safeSiparisMiktari} koli (${safeGercekAdet} adet)`;
                    }

                    return `${safeSiparisMiktari || safeGercekAdet} adet`;
                }

                const celebrationFx = (() => {
                    const addToCartCooldownMs = 450;
                    const orderSuccessCooldownMs = 1200;
                    let lastAddToCartFxAt = 0;
                    let lastOrderFxAt = 0;

                    function isMobileProfile() {
                        if (window.matchMedia) {
                            return window.matchMedia('(max-width: 991.98px)').matches;
                        }
                        return window.innerWidth < 992;
                    }

                    function prefersReducedMotion() {
                        if (window.matchMedia) {
                            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                        }
                        return false;
                    }

                    function pulseElement(element, className, durationMs) {
                        if (!(element instanceof HTMLElement) || !className) {
                            return;
                        }
                        element.classList.remove(className);
                        void element.offsetWidth;
                        element.classList.add(className);
                        setTimeout(() => {
                            element.classList.remove(className);
                        }, durationMs || 800);
                    }

                    function flashOverlay(type) {
                        const flash = document.createElement('div');
                        flash.className = 'fx-celebration-flash' + (type === 'order' ? ' order' : '');
                        document.body.appendChild(flash);

                        requestAnimationFrame(() => {
                            flash.classList.add('is-active');
                        });

                        setTimeout(() => {
                            flash.classList.remove('is-active');
                            setTimeout(() => {
                                if (flash.parentNode) {
                                    flash.parentNode.removeChild(flash);
                                }
                            }, 250);
                        }, 220);
                    }

                    function getOriginFromElement(element, fallbackY) {
                        if (element && typeof element.getBoundingClientRect === 'function') {
                            const rect = element.getBoundingClientRect();
                            if (rect.width > 0 && rect.height > 0) {
                                return {
                                    x: Math.min(0.98, Math.max(0.02, (rect.left + rect.width / 2) / window.innerWidth)),
                                    y: Math.min(0.96, Math.max(0.04, (rect.top + rect.height / 2) / window.innerHeight))
                                };
                            }
                        }
                        return { x: 0.5, y: fallbackY };
                    }

                    function runCartConfetti(anchorElement) {
                        if (typeof window.confetti !== 'function') {
                            return;
                        }

                        const mobile = isMobileProfile();
                        const origin = getOriginFromElement(anchorElement, mobile ? 0.76 : 0.64);
                        const base = {
                            particleCount: mobile ? 34 : 58,
                            spread: mobile ? 54 : 72,
                            startVelocity: mobile ? 24 : 32,
                            ticks: mobile ? 110 : 145,
                            scalar: mobile ? 0.82 : 0.95,
                            gravity: 1.06,
                            zIndex: 4000
                        };

                        window.confetti({
                            ...base,
                            angle: 90,
                            origin
                        });

                        setTimeout(() => {
                            window.confetti({
                                ...base,
                                particleCount: mobile ? 22 : 38,
                                spread: mobile ? 66 : 90,
                                startVelocity: mobile ? 20 : 27,
                                decay: 0.92,
                                origin: {
                                    x: Math.min(0.96, origin.x + 0.02),
                                    y: Math.max(0.04, origin.y - 0.02)
                                }
                            });
                        }, 100);
                    }

                    function runOrderConfetti(anchorElement) {
                        if (typeof window.confetti !== 'function') {
                            return;
                        }

                        const mobile = isMobileProfile();
                        const origin = getOriginFromElement(anchorElement, mobile ? 0.72 : 0.6);
                        const base = {
                            spread: mobile ? 80 : 102,
                            startVelocity: mobile ? 30 : 40,
                            ticks: mobile ? 135 : 180,
                            scalar: mobile ? 0.9 : 1,
                            gravity: 1.04,
                            zIndex: 4000
                        };

                        window.confetti({
                            ...base,
                            particleCount: mobile ? 52 : 94,
                            angle: 90,
                            origin
                        });

                        setTimeout(() => {
                            window.confetti({
                                ...base,
                                particleCount: mobile ? 26 : 48,
                                angle: 60,
                                origin: { x: 0.18, y: mobile ? 0.22 : 0.16 }
                            });
                        }, 90);

                        setTimeout(() => {
                            window.confetti({
                                ...base,
                                particleCount: mobile ? 26 : 48,
                                angle: 120,
                                origin: { x: 0.82, y: mobile ? 0.22 : 0.16 }
                            });
                        }, 170);

                        setTimeout(() => {
                            window.confetti({
                                ...base,
                                particleCount: mobile ? 30 : 54,
                                spread: mobile ? 74 : 94,
                                startVelocity: mobile ? 26 : 34,
                                origin: { x: 0.5, y: mobile ? 0.28 : 0.24 }
                            });
                        }, 280);
                    }

                    function addToCartSuccess(anchorElement) {
                        const now = Date.now();
                        if (now - lastAddToCartFxAt < addToCartCooldownMs) {
                            return;
                        }
                        lastAddToCartFxAt = now;

                        const anchor = anchorElement instanceof HTMLElement ? anchorElement : null;
                        const card = anchor ? anchor.closest('.product-item') : null;

                        pulseElement(anchor, 'fx-success-pulse', 620);
                        pulseElement(card, 'fx-card-glow', 880);

                        if (prefersReducedMotion()) {
                            return;
                        }

                        flashOverlay('cart');
                        runCartConfetti(anchor);
                    }

                    function orderSuccess(anchorElement) {
                        const now = Date.now();
                        if (now - lastOrderFxAt < orderSuccessCooldownMs) {
                            return;
                        }
                        lastOrderFxAt = now;

                        const anchor = anchorElement instanceof HTMLElement ? anchorElement : null;
                        const cartPanel = document.getElementById('sepet');

                        pulseElement(anchor, 'fx-success-pulse', 700);
                        pulseElement(cartPanel, 'fx-order-glow', 1180);

                        if (prefersReducedMotion()) {
                            return;
                        }

                        flashOverlay('order');
                        runOrderConfetti(anchor);
                    }

                    return {
                        addToCartSuccess,
                        orderSuccess
                    };
                })();

                // AJAX for adding to cart
                $(document).on('submit', 'form.add-to-cart-form', function (e) {
                    e.preventDefault();

                    var form = $(this);
                    var formData = form.serialize();
                    var button = form.find('button[name="add_to_cart"]');
                    var originalText = button.html();

                    // Show loading state
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ekleniyor...');

                    $.ajax({
                        url: 'api_islemleri/cart_operations.php',
                        type: 'POST',
                        data: formData + '&action=add_to_cart',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                // Count number of different products in cart
                                var differentProductsCount = response.total_different_products || 0;
                                if (differentProductsCount === 0) {
                                    // If response doesn't contain the count, calculate from the cart UI
                                    differentProductsCount = $('.cart-item').length;
                                    // If we're adding to an empty cart, it would be 1
                                    if (differentProductsCount === 0) {
                                        differentProductsCount = 1;
                                    }
                                }

                                // Show SweetAlert with the number of different products
                                celebrationFx.addToCartSuccess(button.get(0));
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ürün Sepete Eklendi!',
                                    text: `Sepette toplam ${differentProductsCount} farklı ürün bulunmaktadır.`,
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Update the cart UI without page reload (includes badge update)
                                updateCartUI();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                            // Re-enable button
                            button.prop('disabled', false).html(originalText);
                        },
                        error: function () {
                            showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                            // Re-enable button
                            button.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // AJAX for removing from cart
                $(document).on('click', '.remove-from-cart-btn', function (e) {
                    e.preventDefault();

                    var urun_kodu = $(this).data('urun-kodu');
                    var button = $(this);

                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: 'api_islemleri/cart_operations.php',
                        type: 'POST',
                        data: {
                            action: 'remove_from_cart',
                            urun_kodu: urun_kodu
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                showAlert(response.message, 'success');
                                // Update cart UI instead of reloading page
                                updateCartUI();
                            } else {
                                showAlert(response.message, 'danger');
                                button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                            }
                        },
                        error: function () {
                            showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                            button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                        }
                    });
                });

                // AJAX for submitting order - using event delegation for dynamic content
                $(document).on('submit', 'form[name="submit_order"]', function (e) {
                    e.preventDefault();

                    var form = $(this);
                    var formData = form.serialize() + '&action=submit_order';
                    var button = form.find('button[name="submit_order"]');
                    var originalText = button.html();

                    // Debug: Check what data is being sent
                    console.log('Form data being sent:', formData);

                    // Show loading state
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sipariş İşleniyor...');

                    $.ajax({
                        url: 'api_islemleri/order_operations.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            console.log('Order submission response:', response);
                            if (response.status === 'success') {
                                // Show a success message with SweetAlert
                                celebrationFx.orderSuccess(button.get(0));
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Siparişiniz Alındı!',
                                    text: 'Siparişiniz başarıyla oluşturulmuştur. En kısa sürede ilgilenilerek işleme alınacaktır. Teşekkür ederiz!',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Tamam',
                                    confirmButtonColor: '#4a0e63'
                                }).then((result) => {
                                    // Close the cart and update the UI after user acknowledges
                                    closeCart();
                                    updateCartUI(); // This will refresh the cart UI and badge
                                    // Refresh the page to see direct PHP processing results if any
                                    setTimeout(function () {
                                        location.reload();
                                    }, 500);
                                });
                            } else {
                                showAlert(response.message, 'danger');
                            }
                            // Re-enable button
                            button.prop('disabled', false).html(originalText);
                        },
                        error: function (xhr, status, error) {
                            console.log('AJAX Error:', xhr.responseText, status, error);
                            showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                            // Re-enable button
                            button.prop('disabled', false).html(originalText);
                        }
                    });

                    return false; // Prevent any form submission
                });



                // Disable form submission for search (use AJAX instead)
                $('form[method="GET"][action="customer_panel.php"]').on('submit', function (e) {
                    e.preventDefault();
                });

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

                // Function to update cart UI with current cart contents
                function updateCartUI() {
                    $.ajax({
                        url: 'api_islemleri/cart_operations.php',
                        type: 'POST',
                        data: { action: 'get_cart_contents' },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success' && response.cart_items) {
                                // Update cart count badge
                                var totalItems = response.total_items || 0;
                                $('.cart-toggle-btn .badge').text(totalItems);

                                // Update cart content if the cart is visible
                                var cartHtml = '<div class="card-body">';

                                if (response.cart_items.length > 0) {
                                    // Calculate total different products and total quantity
                                    var totalDifferentProducts = response.cart_items.length;
                                    var totalQuantity = 0;
                                    $.each(response.cart_items, function (index, item) {
                                        totalQuantity += parseInt(item.gercek_adet || item.adet || 0, 10) || 0;
                                    });

                                    cartHtml += `<div class="cart-items-container">
                                    <div class="cart-summary">
                                        <span><strong>${totalDifferentProducts}</strong> farklı ürün</span>
                                        <span><strong>${totalQuantity}</strong> adet</span>
                                    </div>
                                    <div class="cart-items-container-inner">`;

                                    $.each(response.cart_items, function (index, item) {
                                        cartHtml += `
                                    <div class="cart-item">
                                        <div class="cart-item-content">
                                            <h4 class="mb-1">${escapeHtml(item.urun_ismi)}</h4>
                                            <div class="item-brand">Marka: ${escapeHtml(item.marka || 'Belirtilmedi')}</div>
                                            <div class="item-quantity">${formatCartItemQuantity(item)}</div>
                                        </div>
                                        <a href="#" class="btn btn-outline-danger remove-from-cart-btn" data-urun-kodu="${item.urun_kodu}" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                `;
                                    });

                                    cartHtml += '</div></div>'; // closing cart-items-container-inner and cart-items-container

                                    // Add order form in separate section at the bottom
                                    cartHtml += `
                                <div class="cart-order-section">
                                    <form method="POST" name="submit_order" class="mb-0">
                                        <div class="form-group mb-3">
                                            <label for="order_description">Sipariş Açıklaması (Opsiyonel)</label>
                                            <textarea class="form-control" id="order_description" name="order_description" placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..." rows="2"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success submit-order-btn w-100" name="submit_order">
                                            <i class="fas fa-paper-plane"></i> Siparişi Oluştur
                                        </button>
                                    </form>
                                </div>
                            `;
                                } else {
                                    cartHtml += `
                                <div class="empty-cart-section">
                                    <div class="empty-cart">
                                        <i class="fas fa-shopping-cart text-muted"></i>
                                        <h4>Sepetiniz Boş</h4>
                                        <p class="text-muted">Sepetinize ürün eklemek için ürünler kısmından seçim yapabilirsiniz.</p>
                                    </div>
                                </div>
                            `;
                                }

                                cartHtml += '</div>';

                                // Update the cart content
                                $('#sepet .card-body').replaceWith(cartHtml);

                                // Function to set up event handlers using event delegation
                                function setupCartEventHandlers() {
                                    // Use event delegation to handle both initial and dynamically loaded forms
                                    $(document).off('submit', 'form.add-to-cart-form').on('submit', 'form.add-to-cart-form', function (e) {
                                        e.preventDefault();

                                        var form = $(this);
                                        var formData = form.serialize();
                                        var button = form.find('button[name="add_to_cart"]');
                                        var originalText = button.html();

                                        // Prevent multiple clicks during processing
                                        if (button.prop('disabled')) {
                                            return false;
                                        }

                                        // Show loading state
                                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ekleniyor...');

                                        $.ajax({
                                            url: 'api_islemleri/cart_operations.php',
                                            type: 'POST',
                                            data: formData + '&action=add_to_cart',
                                            dataType: 'json',
                                            success: function (response) {
                                                if (response.status === 'success') {
                                                    // Count number of different products in cart
                                                    var differentProductsCount = response.total_different_products || 0;
                                                    if (differentProductsCount === 0) {
                                                        // If response doesn't contain the count, calculate from the cart UI
                                                        differentProductsCount = $('.cart-item').length;
                                                        // If we're adding to an empty cart, it would be 1
                                                        if (differentProductsCount === 0) {
                                                            differentProductsCount = 1;
                                                        }
                                                    }

                                                    // Show SweetAlert with the number of different products
                                                    celebrationFx.addToCartSuccess(button.get(0));
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'Ürün Sepete Eklendi!',
                                                        text: `Sepette toplam ${differentProductsCount} farklı ürün bulunmaktadır.`,
                                                        showConfirmButton: false,
                                                        timer: 2000
                                                    });

                                                    // Update the cart UI without page reload
                                                    updateCartUI();
                                                } else {
                                                    showAlert(response.message, 'danger');
                                                }
                                                // Re-enable button
                                                button.prop('disabled', false).html(originalText);
                                            },
                                            error: function () {
                                                showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                                                // Re-enable button
                                                button.prop('disabled', false).html(originalText);
                                            }
                                        });
                                    });

                                    // Use event delegation for remove from cart functionality
                                    $(document).off('click', '.remove-from-cart-btn').on('click', '.remove-from-cart-btn', function (e) {
                                        e.preventDefault();

                                        var urun_kodu = $(this).data('urun-kodu');
                                        var button = $(this);

                                        $.ajax({
                                            url: 'api_islemleri/cart_operations.php',
                                            type: 'POST',
                                            data: {
                                                action: 'remove_from_cart',
                                                urun_kodu: urun_kodu
                                            },
                                            dataType: 'json',
                                            success: function (response) {
                                                if (response.status === 'success') {
                                                    showAlert(response.message, 'success');
                                                    // Update cart UI instead of reloading page
                                                    updateCartUI();
                                                } else {
                                                    showAlert(response.message, 'danger');
                                                }
                                            },
                                            error: function () {
                                                showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                                            }
                                        });
                                    });
                                }

                                // Re-attach event handlers for the newly added elements
                                setupCartEventHandlers();

                                // Check if cart is currently open and update accordingly
                                if ($('#sepet').hasClass('show')) {
                                    // Cart is open, ensure the content is visible
                                }
                            }
                        },
                        error: function () {
                            console.error('Error fetching cart contents');
                            // At least update the badge count by calculating from existing UI
                            var cartCount = parseInt($('.cart-toggle-btn .badge').text()) || 0;
                            // Keep the existing count as is since we couldn't fetch updated data
                        }
                    });
                }

                // Function to open the cart panel
                function openCart() {
                    $('#sepet').addClass('show');
                    $('#cartOverlay').addClass('show');
                    $('body').css('overflow', 'hidden'); // Prevent background scrolling
                }

                // Function to close the cart panel
                function closeCart() {
                    $('#sepet').removeClass('show');
                    $('#cartOverlay').removeClass('show');
                    $('body').css('overflow', 'auto'); // Re-enable scrolling
                }

                // Attach cart toggle event handlers when the document is ready
                $(document).ready(function () {
                    // Click handlers for cart open buttons
                    $(document).on('click', '.cart-toggle-btn', function (e) {
                        e.preventDefault();
                        openCart();
                    });

                    // Click handler for closing cart
                    $(document).on('click', '#closeCartBtn', function (e) {
                        e.preventDefault();
                        closeCart();
                    });

                    // Click handler for overlay to close cart
                    $(document).on('click', '#cartOverlay', function (e) {
                        if (e.target === this) {
                            closeCart();
                        }
                    });

                    // Keyboard handler for closing cart (ESC key)
                    $(document).keydown(function (e) {
                        if (e.key === 'Escape' && $('#sepet').hasClass('show')) {
                            closeCart();
                        }
                    });
                });

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
                                    e.preventDefault(); // Prevent any default behavior
                                    e.stopPropagation(); // Stop event bubbling

                                    var $button = $(this);
                                    var orderId = $button.data('id');

                                    if ($button.prop('disabled')) return; // If already processing, return

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

                                // Show/hide buttons based on status
                                if (status === 'beklemede') {
                                    $('#cancelOrderBtn').show();
                                    $('#updateOrderBtn').show();
                                } else {
                                    $('#cancelOrderBtn').hide();
                                    $('#updateOrderBtn').hide();
                                }

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

                // Function to perform search and then update pagination
                function performSearchAndPagination() {
                    currentPaginationPage = 1;
                    if (typeof updatePagination === 'function') {
                        updatePagination();
                    }
                }

                // Initialize product pagination
                function initializePagination() {
                    allProducts = $('.product-item');

                    // Helper function to get currently filtered products based on search
                    function getProductsToPaginate() {
                        const searchTerm = $('.form-control[name="search"]').val().toLowerCase().trim();
                        if (!searchTerm) return allProducts;
                        return allProducts.filter(function () {
                            const productName = $(this).data('name') || '';
                            return productName.includes(searchTerm);
                        });
                    }

                    // Search functionality
                    $('.search-input').on('input', function () {
                        currentPaginationPage = 1;
                        if (typeof updatePagination === 'function') {
                            updatePagination(getProductsToPaginate());
                        }
                    });

                    // Items per page functionality
                    $('#itemsPerPageSelect').on('change', function () {
                        itemsPerPage = parseInt($(this).val()) || 5;
                        currentPaginationPage = 1;
                        if (typeof updatePagination === 'function') {
                            updatePagination(getProductsToPaginate());
                        }
                    });

                    // Pagination click handlers
                    $(document).on('click', '.pagination-btn', function (e) {
                        e.preventDefault();
                        const page = parseInt($(this).data('page'));
                        if (page && page !== currentPaginationPage) {
                            currentPaginationPage = page;
                            if (typeof updatePagination === 'function') {
                                updatePagination(getProductsToPaginate());
                            }
                            // Scroll to top of product list
                            $('html, body').animate({
                                scrollTop: $('#product-list-container').offset().top - 100
                            }, 300);
                        }
                    });

                    // Initial display
                    itemsPerPage = parseInt($('#itemsPerPageSelect').val()) || 5;
                    if (typeof updatePagination === 'function') {
                        updatePagination();
                    }
                }

                // --- Mobile Bottom Nav ---
                const bottomNavItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');

                bottomNavItems.forEach(item => {
                    item.addEventListener('click', function (e) {
                        const href = this.getAttribute('href');

                        // Allow default behavior for external links, cart, logout, and customer_orders
                        if (href === 'change_password.php' || href === 'logout.php' || href === 'customer_orders.php' || this.classList.contains('cart-toggle-btn')) {
                            // For cart, ensure active state is set
                            if (this.classList.contains('cart-toggle-btn')) {
                                bottomNavItems.forEach(i => i.classList.remove('active'));
                                this.classList.add('active');
                            }
                            return;
                        }
                        e.preventDefault();

                        // Set active state
                        bottomNavItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');

                        // Handle scrolling for internal links
                        if (href && href.startsWith('#')) {
                            const targetElement = document.querySelector(href);
                            if (targetElement) {
                                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        } else if (href === 'customer_panel.php') {
                            // Scroll to top for "Mağaza"
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    });
                });

                // When cart is closed, revert active state to the main page icon
                $(document).on('click', '#closeCartBtn, #cartOverlay', function () {
                    setTimeout(function () {
                        if (!$('#sepet').hasClass('show')) {
                            bottomNavItems.forEach(i => i.classList.remove('active'));
                            // Activate the "Mağaza" button
                            $('.mobile-bottom-nav .nav-item[href="customer_panel.php"]').addClass('active');
                        }
                    }, 350); // wait for animation
                });
                // --- End Mobile Bottom Nav ---
            });

            // Function to update pagination display - defined outside document ready to be accessible globally
            // Pagination has been removed to show all products at once
            function updatePagination(productsToPaginate) {
                // Check if DOM is ready and elements exist
                if (typeof $ === 'undefined' || !$('#product-list-container').length) {
                    // Wait a bit and try again if DOM isn't ready yet
                    setTimeout(function () {
                        if (typeof updatePagination === 'function') {
                            updatePagination(productsToPaginate);
                        }
                    }, 100);
                    return;
                }

                // If no products are provided (e.g., on initial load), use all products.
                if (productsToPaginate === undefined) {
                    // Make sure allProducts is available, if not try to get them
                    if (typeof allProducts === 'undefined' || allProducts.length === 0) {
                        allProducts = $('.product-item');
                    }
                    productsToPaginate = allProducts;
                }

                // Handle "no results" message for search
                const $noResultsMessage = $('.no-results-message');
                const searchTerm = $('.search-input').val().toLowerCase().trim();
                if (productsToPaginate.length === 0 && searchTerm !== '') {
                    $noResultsMessage.show();
                } else {
                    $noResultsMessage.hide();
                }

                // Since pagination is removed, show all products
                allProducts.removeClass('visible');

                if (productsToPaginate.length > 0) {
                    productsToPaginate.addClass('visible');
                }

                // Hide pagination container since pagination is removed
                $('#pagination-container').hide();
            }

            // Function to generate pagination buttons - defined outside document ready to be accessible globally
            // Pagination has been removed to show all products at once
            /*
            function generatePaginationButtons(totalPages, activePage) {
                const $pagination = $('#product-pagination');
                let paginationHtml = '';
    
                // Previous button
                const prevDisabled = activePage <= 1 ? 'disabled' : '';
                paginationHtml += `<li class="page-item ${prevDisabled}">
                <a class="page-link pagination-btn" href="#" data-page="${activePage - 1}" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
    
                // Page numbers
                const maxVisiblePages = 5;
                let startPage = Math.max(1, activePage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
                // Adjust start page if we're near the end
                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }
    
                // First page + ellipsis if needed
                if (startPage > 1) {
                    paginationHtml += `<li class="page-item">
                    <a class="page-link pagination-btn" href="#" data-page="1">1</a>
                </li>`;
                    if (startPage > 2) {
                        paginationHtml += `<li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>`;
                    }
                }
    
                // Page numbers
                for (let i = startPage; i <= endPage; i++) {
                    const activeClass = i === activePage ? 'active' : '';
                    paginationHtml += `<li class="page-item ${activeClass}">
                    <a class="page-link pagination-btn" href="#" data-page="${i}">${i}</a>
                </li>`;
                }
    
                // Last page + ellipsis if needed
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHtml += `<li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>`;
                    }
                    paginationHtml += `<li class="page-item">
                    <a class="page-link pagination-btn" href="#" data-page="${totalPages}">${totalPages}</a>
                </li>`;
                }
    
                // Next button
                const nextDisabled = activePage >= totalPages ? 'disabled' : '';
                paginationHtml += `<li class="page-item ${nextDisabled}">
                <a class="page-link pagination-btn" href="#" data-page="${activePage + 1}" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
    
                $pagination.html(paginationHtml);
            }
            */

            // Ensure initial products are displayed when page is fully loaded
            $(window).on('load', function () {
                if (typeof allProducts !== 'undefined' && allProducts.length > 0) {
                    // Update pagination to show first page
                    updatePagination();
                }
            });

            // Product photo gallery variables
            let currentProductPhotos = [];
            let currentPhotoIndex = 0;
            let currentProductName = '';

            // Open product photo gallery
            window.openProductGallery = function (urunKodu) {
                console.log('Opening gallery for product:', urunKodu);

                // Fetch all photos for this product
                $.ajax({
                    url: 'api_islemleri/urun_fotograflari_islemler.php',
                    type: 'GET',
                    data: {
                        action: 'get_photos',
                        urun_kodu: urunKodu
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Photo API response:', response);

                        if (response.status === 'success' && response.data.length > 0) {
                            currentProductPhotos = response.data;
                            currentPhotoIndex = 0;

                            // Get product name from the list
                            const productItem = $('.product-item').filter(function () {
                                return $(this).find('input[name="urun_kodu"]').val() == urunKodu;
                            });
                            currentProductName = productItem.find('.product-name').text().trim();

                            console.log('Total photos:', currentProductPhotos.length);
                            console.log('Product name:', currentProductName);

                            showProductPhoto();
                            $('#productPhotoLightbox').css('display', 'flex');
                            $('body').css('overflow', 'hidden');

                            // Show/hide navigation buttons
                            if (currentProductPhotos.length > 1) {
                                $('#prevPhotoBtn, #nextPhotoBtn').css('display', 'flex');
                            } else {
                                $('#prevPhotoBtn, #nextPhotoBtn').hide();
                            }

                            // Add keyboard support
                            $(document).on('keydown.lightbox', handleLightboxKeyboard);
                        } else {
                            console.error('No photos found or API error');
                            alert('Bu ürün için fotoğraf bulunamadı.');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText, status, error);
                        alert('Fotoğraflar yüklenirken bir hata oluştu.');
                    }
                });
            };

            // Close product gallery
            window.closeProductGallery = function () {
                $('#productPhotoLightbox').hide();
                $('body').css('overflow', '');
                $(document).off('keydown.lightbox');
            };

            // Show current photo
            function showProductPhoto() {
                const photo = currentProductPhotos[currentPhotoIndex];
                $('#lightboxImage').attr('src', photo.dosya_yolu).attr('alt', photo.dosya_adi);
                $('#lightboxProductName').text(currentProductName);
                $('#lightboxPhotoCounter').text(`${currentPhotoIndex + 1} / ${currentProductPhotos.length}`);
            }

            // Navigate to next photo
            window.nextProductPhoto = function () {
                currentPhotoIndex = (currentPhotoIndex + 1) % currentProductPhotos.length;
                showProductPhoto();
            };

            // Navigate to previous photo
            window.previousProductPhoto = function () {
                currentPhotoIndex = (currentPhotoIndex - 1 + currentProductPhotos.length) % currentProductPhotos.length;
                showProductPhoto();
            };

            // Handle keyboard navigation
            function handleLightboxKeyboard(e) {
                if (e.key === 'Escape') {
                    closeProductGallery();
                } else if (e.key === 'ArrowRight') {
                    nextProductPhoto();
                } else if (e.key === 'ArrowLeft') {
                    previousProductPhoto();
                }
            }
        </script>
</body>

</html>

