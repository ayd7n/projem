<?php
// File: includes/auth_functions.php

/**
 * Checks if the currently logged-in user has a specific permission.
 * Permissions are loaded into $_SESSION['izinler'] upon login.
 *
 * @param string $permission_key The permission key to check (e.g., 'page:view:musteriler').
 * @return bool True if the user has the permission, false otherwise.
 */
function yetkisi_var($permission_key) {
    // The admin user (identified by a specific session variable) has all permissions.
    // This acts as a super-admin override.
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }

    // If the 'izinler' session variable doesn't exist, the user has no permissions.
    if (!isset($_SESSION['izinler']) || !is_array($_SESSION['izinler'])) {
        return false;
    }

    // Check if the specific permission key exists in the user's permission array.
    return in_array($permission_key, $_SESSION['izinler']);
}

function request_expects_json() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($request_uri, PHP_URL_PATH) ?? '';
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $is_php_ajax = pathinfo($path, PATHINFO_EXTENSION) === 'php'
        && (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($content_type, 'application/json') !== false);

    return strpos($accept, 'application/json') !== false
        || strpos($request_uri, '/api_islemleri/') !== false
        || $is_php_ajax;
}

function deny_request($message = 'Yetkisiz erisim.', $status_code = 403, $json = null) {
    http_response_code($status_code);
    if ($json === null) {
        $json = request_expects_json();
    }

    if ($json) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        echo $message;
    }
    exit;
}

function require_login($json = null) {
    if (!isset($_SESSION['user_id'])) {
        if ($json === null) {
            $json = request_expects_json();
        }
        if ($json) {
            deny_request('Oturum acmaniz gerekiyor.', 401, true);
        }

        header('Location: login.php');
        exit;
    }
}

function require_staff($json = null) {
    require_login($json);
    if (($_SESSION['taraf'] ?? '') !== 'personel') {
        deny_request('Bu islem icin yetkiniz yok.', 403, $json);
    }
}

function require_permission($permission_key, $json = null) {
    if (!yetkisi_var($permission_key)) {
        deny_request('Bu islem icin yetkiniz yok.', 403, $json);
    }
}

function request_origin_matches_host($url) {
    if (!$url) {
        return null;
    }

    $parts = parse_url($url);
    if (empty($parts['host'])) {
        return null;
    }

    $request_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $origin_host = strtolower($parts['host']);
    $origin_port = isset($parts['port']) ? ':' . $parts['port'] : '';

    return $request_host === $origin_host . $origin_port || $request_host === $origin_host;
}

function enforce_same_origin_unsafe_request() {
    if (PHP_SAPI === 'cli') {
        return;
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    $origin_ok = request_origin_matches_host($origin);
    if ($origin_ok === false) {
        deny_request('Gecersiz istek kaynagi.', 403, null);
    }

    $referer_ok = request_origin_matches_host($referer);
    if ($origin_ok === null && $referer_ok === false) {
        deny_request('Gecersiz istek kaynagi.', 403, null);
    }
}

/**
 * Reloads permissions for a given user ID from the database and updates the session.
 *
 * @param int $user_id The ID of the user whose permissions to reload.
 * @param mysqli $connection The database connection object.
 */
function reload_permissions($user_id, $connection) {
    $izinler = [];
    $stmt = $connection->prepare("SELECT izin_anahtari FROM personel_izinleri WHERE personel_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $izinler[] = $row['izin_anahtari'];
    }
    $stmt->close();
    $_SESSION['izinler'] = $izinler;
}

/**
 * Retrieves a specific setting value from the 'ayarlar' table.
 *
 * @param mysqli $connection The database connection object.
 * @param string $key The key of the setting to retrieve.
 * @return string|null The value of the setting, or null if not found.
 */
function get_setting($connection, $key) {
    $stmt = $connection->prepare("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = ?");
    if (!$stmt) return null;
    
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['ayar_deger'] : null;
}

/**
 * Creates or updates a specific setting in the 'ayarlar' table.
 *
 * @param mysqli $connection The database connection object.
 * @param string $key The key of the setting.
 * @param string $value The value to set for the key.
 */
function update_setting($connection, $key, $value) {
    $stmt = $connection->prepare("INSERT INTO ayarlar (ayar_anahtar, ayar_deger) VALUES (?, ?) ON DUPLICATE KEY UPDATE ayar_deger = VALUES(ayar_deger)");
    if ($stmt) {
        $stmt->bind_param('ss', $key, $value);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    return false;
}
?>
