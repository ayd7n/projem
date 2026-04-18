<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim!']);
    exit;
}

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

function parse_positive_int($value)
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

function normalize_cart_unit($value)
{
    $unit = strtolower(trim((string) $value));
    return $unit === 'koli' ? 'koli' : 'adet';
}

function validate_cart_unit_input($value)
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

function get_cart_product($connection, $urun_kodu)
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

function build_cart_item_payload($urun_kodu, $product, $siparis_miktari, $siparis_birimi)
{
    $koli_ici_adet = max(1, (int) ($product['koli_ici_adet'] ?? 1));
    $siparis_birimi = normalize_cart_unit($siparis_birimi);
    $siparis_miktari = (int) $siparis_miktari;

    $gercek_adet = $siparis_birimi === 'koli'
        ? $siparis_miktari * $koli_ici_adet
        : $siparis_miktari;

    return [
        'urun_kodu' => (int) $urun_kodu,
        'urun_ismi' => (string) ($product['urun_ismi'] ?? 'Bilinmeyen Urun'),
        'marka' => (string) ($product['marka'] ?? 'Belirtilmedi'),
        'koli_ici_adet' => $koli_ici_adet,
        'siparis_miktari' => $siparis_miktari,
        'siparis_birimi' => $siparis_birimi,
        'gercek_adet' => $gercek_adet
    ];
}

function normalize_existing_cart_item($urun_kodu, $entry, $product)
{
    $siparis_birimi = 'adet';
    $siparis_miktari = null;

    if (is_array($entry)) {
        $siparis_birimi = normalize_cart_unit($entry['siparis_birimi'] ?? 'adet');
        $siparis_miktari = parse_positive_int($entry['siparis_miktari'] ?? null);

        if ($siparis_miktari === null) {
            $legacy_adet = parse_positive_int($entry['adet'] ?? null);
            if ($legacy_adet !== null) {
                $siparis_miktari = $legacy_adet;
                $siparis_birimi = 'adet';
            }
        }

        if ($siparis_miktari === null) {
            $gercek_adet = parse_positive_int($entry['gercek_adet'] ?? null);
            if ($gercek_adet !== null) {
                $koli_ici_adet = max(1, (int) ($product['koli_ici_adet'] ?? 1));
                if ($siparis_birimi === 'koli') {
                    $siparis_miktari = max(1, (int) ceil($gercek_adet / $koli_ici_adet));
                } else {
                    $siparis_miktari = $gercek_adet;
                }
            }
        }
    } else {
        $siparis_miktari = parse_positive_int($entry);
        $siparis_birimi = 'adet';
    }

    if ($siparis_miktari === null) {
        return null;
    }

    return build_cart_item_payload($urun_kodu, $product, $siparis_miktari, $siparis_birimi);
}

ensure_product_brand_and_box_columns_for_customer($connection);

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add_to_cart') {
    $urun_kodu = (int) ($_POST['urun_kodu'] ?? 0);
    $siparis_miktari = parse_positive_int($_POST['siparis_miktari'] ?? null);
    $siparis_birimi = validate_cart_unit_input($_POST['siparis_birimi'] ?? null);

    if ($urun_kodu <= 0 || $siparis_miktari === null || $siparis_birimi === null) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Lutfen gecerli urun, pozitif miktar ve birim secin.'
        ]);
        exit;
    }

    $product = get_cart_product($connection, $urun_kodu);
    if (!$product) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Urun bulunamadi!'
        ]);
        exit;
    }

    $cart_item = build_cart_item_payload($urun_kodu, $product, $siparis_miktari, $siparis_birimi);
    $stok_miktari = (int) ($product['stok_miktari'] ?? 0);
    $gercek_adet = (int) ($cart_item['gercek_adet'] ?? 0);

    if ($stok_miktari <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu urun stokta kalmamis.'
        ]);
        exit;
    }

    if ($gercek_adet > $stok_miktari) {
        echo json_encode([
            'status' => 'error',
            'message' => "Istenen miktar mevcut stogu asiyor. En fazla {$stok_miktari} adet siparis verebilirsiniz."
        ]);
        exit;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ezme davranisi: ayni urun yeniden eklenirse onceki satir replace edilir
    $_SESSION['cart'][$urun_kodu] = $cart_item;

    echo json_encode([
        'status' => 'success',
        'message' => 'Urun sepete eklendi!',
        'total_different_products' => count($_SESSION['cart']),
        'cart_item' => $cart_item
    ]);
} elseif ($action === 'remove_from_cart') {
    $urun_kodu = (int) ($_POST['urun_kodu'] ?? 0);

    if (isset($_SESSION['cart'][$urun_kodu])) {
        unset($_SESSION['cart'][$urun_kodu]);
        echo json_encode(['status' => 'success', 'message' => 'Urun sepetten kaldirildi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Urun sepette bulunamadi!']);
    }
} elseif ($action === 'get_cart_contents') {
    $raw_cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $normalized_cart = [];
    $cart_items = [];

    foreach ($raw_cart as $urun_kodu => $entry) {
        $urun_kodu = (int) $urun_kodu;
        if ($urun_kodu <= 0) {
            continue;
        }

        $product = get_cart_product($connection, $urun_kodu);
        if (!$product) {
            continue;
        }

        $normalized_item = normalize_existing_cart_item($urun_kodu, $entry, $product);
        if ($normalized_item === null) {
            continue;
        }

        $normalized_cart[$urun_kodu] = $normalized_item;
        $cart_items[] = array_merge($normalized_item, [
            'adet' => (int) ($normalized_item['gercek_adet'] ?? 0)
        ]);
    }

    $_SESSION['cart'] = $normalized_cart;

    echo json_encode([
        'status' => 'success',
        'cart_items' => $cart_items,
        'total_items' => count($cart_items)
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gecersiz istek!']);
}
