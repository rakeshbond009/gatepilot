<?php
/**
 * AUTOMATIC ENVIRONMENT DETECTION & DATABASE CONFIGURATION
 * This file automatically detects whether the application is running on:
 * - Local environment (XAMPP/WAMP)
 * - Hosted environment (Hostinger or other web hosting)
 */

// Function to detect if running on local environment
function isLocalEnvironment()
{
    // Check if running from CLI
    if (php_sapi_name() === 'cli') {
        return true;
    }
    // Check if running on localhost or local IP
    $local_hosts = ['localhost', '127.0.0.1', '::1', 'localhost:8000'];
    $server_name = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';

    // Check if server name matches local hosts
    foreach ($local_hosts as $local_host) {
        if (stripos($server_name, $local_host) !== false) {
            return true;
        }
    }

    // Check if running on local IP range (192.168.x.x or 10.x.x.x)
    if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.)/i', $server_name)) {
        return true;
    }

    // Check if XAMPP/WAMP directory structure exists
    if (
        stripos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false ||
        stripos($_SERVER['DOCUMENT_ROOT'] ?? '', 'wamp') !== false
    ) {
        return true;
    }

    return false;
}

// Detect environment
$is_local = isLocalEnvironment();

// Set database credentials based on environment
if ($is_local) {
    // ========== LOCAL ENVIRONMENT (XAMPP/WAMP) ==========
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'gp_admin');
    define('ENVIRONMENT', 'LOCAL');
} else {
    // ========== HOSTED ENVIRONMENT (HOSTINGER) ==========
    // Update these credentials with your Hostinger database details
    define('DB_HOST', 'localhost'); // Usually 'localhost' on Hostinger
    define('DB_USER', 'u875321134_gatepilot'); // Your Hostinger MySQL username
    define('DB_PASS', 'Gatepilot@123'); // Your Hostinger MySQL password
    define('DB_NAME', 'u875321134_gp_admin'); // Shortened for grouping AND length compliance
    define('ENVIRONMENT', 'PRODUCTION');
}

// ========== CENTRALIZED SUPPORT DATABASE (For Issue/Bug Reporting) ==========
define('SUPPORT_DB_HOST', 'localhost');
define('SUPPORT_DB_NAME', 'u875321134_mywebsite');
define('SUPPORT_DB_USER', 'u875321134_rakeshwebsite');
define('SUPPORT_DB_PASS', 'Mywebsite@2025');
define('CLIENT_APP_NAME', 'GatePilot - Truck Movement'); // Identifies which app reported the issue

// ========== UNIVERSAL SESSION CONFIGURATION (FOR "LOGIN FOREVER") ==========
$session_lifetime = 365 * 24 * 60 * 60; // 1 year

// Detect HTTPS including behind reverse proxies (Hostinger / Cloudflare)
$_is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false)
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

$_cookie_domain = '';

// Custom session directory — only applied if writable (gitignored so may not exist on server)
// If it doesn't exist/isn't writable, PHP uses system default — that's OK, we use DB token as fallback
$_session_dir = __DIR__ . '/sessions';
if (!is_dir($_session_dir)) {
    @mkdir($_session_dir, 0755, true);
}
if (is_dir($_session_dir) && is_writable($_session_dir)) {
    ini_set('session.save_path', $_session_dir);
}

// Long-lived session: cookie persists for 1 year in browser
ini_set('session.gc_maxlifetime', 86400 * 30); // 30 days server-side file lifetime
ini_set('session.cookie_lifetime', $session_lifetime);

session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',      // empty: browser infers host — most compatible
    'secure' => false,   // works on both HTTP and HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);



// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// Error reporting (disable in production)
if ($is_local) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
}

// ========== MASTER DATABASE (Global Control) ==========
define('MASTER_DB_NAME', DB_NAME); 
define('MASTER_DB_USER', DB_USER);
define('MASTER_DB_PASS', DB_PASS);

// Get Master DB connection
function getMasterDatabaseConnection()
{
    // Since we're using the main DB as the control center, just return the standard connection
    return mysqli_connect(DB_HOST, MASTER_DB_USER, MASTER_DB_PASS, DB_NAME);
}

// Database connection function
function getDatabaseConnection()
{
    // If we're logged in/specifying a tenant, we use that database
    $db_name = $_SESSION['tenant_db'] ?? DB_NAME;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_name);

    if (!$conn) {
        // Try to connect without database and create it
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
        if ($conn) {
            $db_created = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS " . $db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            if ($db_created) {
                mysqli_select_db($conn, $db_name);
            } else {
                die("Error creating database: " . mysqli_error($conn));
            }
        } else {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }

    // Set MySQL timezone to IST
    mysqli_query($conn, "SET time_zone = '+05:30'");

    return $conn;
}

// Get base URL
function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    return $protocol . "://" . $host . $script;
}

define('BASE_URL', getBaseUrl());

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('DRIVER_UPLOAD_DIR', UPLOAD_DIR . 'drivers/');
define('LICENSE_UPLOAD_DIR', UPLOAD_DIR . 'licenses/');
define('VEHICLE_UPLOAD_DIR', UPLOAD_DIR . 'vehicles/');
define('LOGO_UPLOAD_DIR', UPLOAD_DIR . 'logo/');
define('EMPLOYEE_UPLOAD_DIR', UPLOAD_DIR . 'employees/');

// Create upload directories if they don't exist
if (!is_dir(UPLOAD_DIR))
    mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(DRIVER_UPLOAD_DIR))
    mkdir(DRIVER_UPLOAD_DIR, 0755, true);
if (!is_dir(LICENSE_UPLOAD_DIR))
    mkdir(LICENSE_UPLOAD_DIR, 0755, true);
if (!is_dir(VEHICLE_UPLOAD_DIR))
    mkdir(VEHICLE_UPLOAD_DIR, 0755, true);
if (!is_dir(LOGO_UPLOAD_DIR))
    mkdir(LOGO_UPLOAD_DIR, 0755, true);
if (!is_dir(EMPLOYEE_UPLOAD_DIR))
    mkdir(EMPLOYEE_UPLOAD_DIR, 0755, true);

// Display environment info (only for debugging - remove in production)
if ($is_local && isset($_GET['debug_env'])) {
    echo "<div style='background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid #333;'>";
    echo "<h3>Environment Debug Info</h3>";
    echo "<p><strong>Environment:</strong> " . ENVIRONMENT . "</p>";
    echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Database User:</strong> " . DB_USER . "</p>";
    echo "<p><strong>Base URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</p>";
    echo "</div>";
}
