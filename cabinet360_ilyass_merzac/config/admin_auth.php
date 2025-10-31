<?php
/**
 * Cabinet360 SaaS - Admin Authentication Control
 * Protects admin pages from unauthorized access
 */

session_start();
require_once __DIR__ . '/config.php';

// Check if admin is authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Session timeout for admin (30 minutes)
if (isset($_SESSION['ADMIN_LAST_ACTIVITY']) && (time() - $_SESSION['ADMIN_LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['ADMIN_LAST_ACTIVITY'] = time();

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_name = $_SESSION['admin_name'];
$is_super_admin = $_SESSION['is_super_admin'] ?? false;

?>



