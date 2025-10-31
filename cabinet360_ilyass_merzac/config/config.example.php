<?php
/**
 * Cabinet360 - Configuration Example for InfinityFree
 * Copy this file to config.php and update with your settings
 */

// ============================================
// DATABASE CONFIGURATION - InfinityFree
// ============================================
// Get these from your InfinityFree control panel
define('DB_HOST', 'sql###.infinityfree.com');     // Your database host
define('DB_USER', 'epiz_xxxxx_username');         // Your database username
define('DB_PASS', 'your_database_password');      // Your database password
define('DB_NAME', 'epiz_xxxxx_cabinet360');       // Your database name

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'Cabinet360');

// IMPORTANT: Update this with your actual domain
// Examples:
// - For InfinityFree subdomain: 'https://yoursite.infinityfreeapp.com'
// - For custom domain: 'https://yourdomain.com'
// - For subdirectory: 'https://yourdomain.com/cabinet360'
define('APP_URL', 'https://yoursite.infinityfreeapp.com');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// ============================================
// SESSION CONFIGURATION
// ============================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Keep at 1 for HTTPS (InfinityFree supports HTTPS)
session_start();

// ============================================
// DATABASE CONNECTION
// ============================================
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ============================================
// SECURITY FUNCTIONS
// ============================================
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($page) {
    header("Location: " . APP_URL . "/" . $page);
    exit();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// ============================================
// SESSION TIMEOUT
// ============================================
define('SESSION_TIMEOUT', 1800); // 30 minutes of inactivity

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    redirect('login.php');
}
$_SESSION['LAST_ACTIVITY'] = time();

?>

