<?php
/**
 * Cabinet360 - Configuration File
 * Database connection and global settings
 */

// Load environment variables from .env file in development
if (!getenv('APP_ENV')) {
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
            }
        }
    }
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'cabinet360_saas');

// Application Settings
define('APP_NAME', getenv('APP_NAME') ?: 'Cabinet360');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/Cabinet360');
define('UPLOAD_DIR', getenv('UPLOAD_PATH') ? __DIR__ . '/..' . getenv('UPLOAD_PATH') : __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', getenv('UPLOAD_MAX_SIZE') ? (int)(trim(getenv('UPLOAD_MAX_SIZE'), 'M') * 1024 * 1024) : 5242880);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_start();

// Database Connection
try {
    // Build PDO options and include optional SSL CA for PlanetScale or other TLS-enabled hosts
    $pdo_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // If a DB SSL CA is provided (PEM file path), add it to PDO options so TLS connections work
    if (getenv('DB_SSL_CA')) {
        // PDO::MYSQL_ATTR_SSL_CA is available when the PDO MySQL driver is present
        $pdo_options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
    }

    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $pdo_options
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Security Functions
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

// Check if lawyer is logged in (multi-tenant)
function is_logged_in() {
    return isset($_SESSION['lawyer_id']) && isset($_SESSION['lawyer_email']);
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Get current lawyer ID
function get_lawyer_id() {
    return $_SESSION['lawyer_id'] ?? null;
}

// Get lawyer info
function get_lawyer_info($field = null) {
    if ($field) {
        return $_SESSION[$field] ?? null;
    }
    return [
        'id' => $_SESSION['lawyer_id'] ?? null,
        'cabinet_name' => $_SESSION['cabinet_name'] ?? null,
        'lawyer_name' => $_SESSION['lawyer_name'] ?? null,
        'email' => $_SESSION['lawyer_email'] ?? null,
        'subscription_plan' => $_SESSION['subscription_plan'] ?? 'free'
    ];
}

// Session timeout (30 minutes of inactivity)
define('SESSION_TIMEOUT', 1800);

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    redirect('login.php');
}
$_SESSION['LAST_ACTIVITY'] = time();

?>

