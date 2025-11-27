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
        $stmt->execute();
        $stmt->close();
    }
}
?>
