<?php
/**
 * Cabinet360 SaaS - Lawyer Authentication Control
 * Protects pages from unauthorized access (Multi-tenant)
 */

require_once __DIR__ . '/config.php';

// Check if lawyer is authenticated
if (!is_logged_in()) {
    redirect('login_lawyer.php');
}

// Get lawyer info
$lawyer_id = $_SESSION['lawyer_id'];
$lawyer_name = $_SESSION['lawyer_name'];
$cabinet_name = $_SESSION['cabinet_name'];
$lawyer_email = $_SESSION['lawyer_email'];
$subscription_plan = $_SESSION['subscription_plan'] ?? 'free';

?>

