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
?>
