<?php
/**
 * Cabinet360 SaaS - Logout (Lawyer)
 */

session_start();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_lawyer'])) {
    setcookie('remember_lawyer', '', time() - 3600, '/');
}

session_unset();
session_destroy();

header("Location: login_lawyer.php");
exit();
?>

