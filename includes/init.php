<?php
if (!defined('APP_VERSION'))
    define('APP_VERSION', '26.04.09.2337');
/**
 * GATEPILOT - COMPLETE VERSION
 * Features: Inward/Outward, QR Scanning, Vehicle Fetch, Dashboard, Reports, Admin Panel
 */

ob_start(); // START OUTPUT BUFFERING TO PREVENT CORRUPTING JSON RESPONSES

// Load configuration (auto-detects environment)
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/DynamicRegisters.php';
require_once __DIR__ . '/functions.php';

session_start();
$page = $_GET['page'] ?? 'dashboard';

// ========== MULTI-TENANT GLOBAL HANDLERS (AJAX & STATE) ==========


// 1.5 Real-time username uniqueness check
if (isset($_GET['check_username_uniqueness'])) {
    while (ob_get_level())
        ob_end_clean();
    header('Content-Type: application/json');
    $uname = trim($_GET['username'] ?? '');
    if (strlen($uname) < 2) {
        echo json_encode(["unique" => true, "owner" => false]);
        exit;
    }
    $master_conn = @getMasterDatabaseConnection();
    if (!$master_conn) {
        echo json_encode(["unique" => true, "owner" => false, "error" => "Master connection failed"]);
        exit;
    }
    $esc_uname = mysqli_real_escape_string($master_conn, $uname);
    // Use @ to avoid PHP warnings if column is missing, handle error properly
    $check_q = @mysqli_query($master_conn, "SELECT slug FROM tenants WHERE admin_username = '$esc_uname' LIMIT 1");
    if ($check_q && $row = mysqli_fetch_assoc($check_q)) {
        echo json_encode(["unique" => false, "owner" => strtoupper($row['slug'])]);
    } else {
        // If query failed (e.g. column missing), return as unique but maybe log it
        echo json_encode(["unique" => true, "owner" => false, "debug" => mysqli_error($master_conn)]);
    }
    exit;
}

// 2. Real-time slug (company code) uniqueness check
if (isset($_GET['check_slug_uniqueness'])) {
    while (ob_get_level())
        ob_end_clean();
    header('Content-Type: application/json');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $_GET['slug'] ?? ''));
    if (empty($slug)) {
        echo json_encode(["unique" => true]);
        exit;
    }
    $master_conn = getMasterDatabaseConnection();
    $check_q = mysqli_query($master_conn, "SELECT customer_name FROM tenants WHERE slug = '" . mysqli_real_escape_string($master_conn, $slug) . "' LIMIT 1");
    if ($row = mysqli_fetch_assoc($check_q)) {
        echo json_encode(["unique" => false, "owner" => $row['customer_name']]);
    } else {
        echo json_encode(["unique" => true]);
    }
    exit;
}

// 3. Clear Tenant Form Data (State Managed - Clean Session Only, Allow page to continue with trigger)
if (isset($_GET['clear_form'])) {
    unset($_SESSION['tenant_form_data']);
    unset($_SESSION['tenant_error']);
}

// Database connection using auto-detected environment settings
$conn = getDatabaseConnection();
$registers_manager = new DynamicRegisters($conn);

// 3. Enforce Tenant Status (Active/Inactive)
if (isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] !== 'admin') {
    $current_slug = $_SESSION['tenant_slug'];
    $master_conn = getMasterDatabaseConnection();
    if ($master_conn) {
        $status_q = mysqli_query($master_conn, "SELECT is_active, deactivation_message FROM tenants WHERE slug = '" . mysqli_real_escape_string($master_conn, $current_slug) . "'");
        if ($status_row = mysqli_fetch_assoc($status_q)) {
            if (($status_row['is_active'] ?? 1) == 0) {
                // If the user is trying to logout, let them. Otherwise, show the suspension screen.
                if ($page !== 'logout') {
                    // Fetch custom message if any
                    $custom_msg = !empty($status_row['deactivation_message']) ? $status_row['deactivation_message'] : "Your system access has been temporarily suspended by the administrator. Please contact support for more information.";

                    // Render Professional Full-Page Suspension UI
                    ?>
                                        <!DOCTYPE html>
                                        <html lang="en">

                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>System Suspended | GatePilot</title>
                                            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
                                            <style>
                                                :root {
                                                    --primary: #6366f1;
                                                    --danger: #ef4444;
                                                    --bg: #0f172a;
                                                    --text: #f8fafc;
                                                    --glass: rgba(255, 255, 255, 0.03);
                                                    --border: rgba(255, 255, 255, 0.1);
                                                }

                                                * {
                                                    margin: 0;
                                                    padding: 0;
                                                    box-sizing: border-box;
                                                    font-family: 'Outfit', sans-serif;
                                                }

                                                body {
                                                    background: var(--bg);
                                                    color: var(--text);
                                                    height: 100vh;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    background-image:
                                                        radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                                                        radial-gradient(circle at 80% 70%, rgba(239, 68, 68, 0.1) 0%, transparent 40%);
                                                    overflow: hidden;
                                                }

                                                .card {
                                                    background: var(--glass);
                                                    backdrop-filter: blur(20px);
                                                    border: 1px solid var(--border);
                                                    padding: 3rem;
                                                    border-radius: 2rem;
                                                    width: 90%;
                                                    max-width: 600px;
                                                    text-align: center;
                                                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                                                    animation: slideIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                                                }

                                                @keyframes slideIn {
                                                    from {
                                                        opacity: 0;
                                                        transform: translateY(30px);
                                                    }

                                                    to {
                                                        opacity: 1;
                                                        transform: translateY(0);
                                                    }
                                                }

                                                .icon-box {
                                                    width: 80px;
                                                    height: 80px;
                                                    background: rgba(239, 68, 68, 0.1);
                                                    border-radius: 1.5rem;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    margin: 0 auto 2rem;
                                                    border: 1px solid rgba(239, 68, 68, 0.2);
                                                }

                                                .icon-box svg {
                                                    width: 40px;
                                                    height: 40px;
                                                    color: var(--danger);
                                                }

                                                h1 {
                                                    font-weight: 800;
                                                    font-size: 2.5rem;
                                                    margin-bottom: 1rem;
                                                    letter-spacing: -1px;
                                                }

                                                p.message {
                                                    color: #94a3b8;
                                                    font-size: 1.1rem;
                                                    line-height: 1.6;
                                                    margin-bottom: 2.5rem;
                                                }

                                                .btn-group {
                                                    display: flex;
                                                    gap: 1rem;
                                                    justify-content: center;
                                                }

                                                .btn {
                                                    padding: 0.8rem 2rem;
                                                    border-radius: 0.75rem;
                                                    font-weight: 600;
                                                    text-decoration: none;
                                                    transition: all 0.3s;
                                                    cursor: pointer;
                                                }

                                                .btn-primary {
                                                    background: var(--primary);
                                                    color: white;
                                                    border: none;
                                                }

                                                .btn-primary:hover {
                                                    transform: translateY(-2px);
                                                    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
                                                }

                                                .btn-outline {
                                                    background: transparent;
                                                    color: #94a3b8;
                                                    border: 1px solid var(--border);
                                                }

                                                .btn-outline:hover {
                                                    background: rgba(255, 255, 255, 0.05);
                                                    color: white;
                                                }

                                                .brand {
                                                    position: absolute;
                                                    bottom: 3rem;
                                                    opacity: 0.4;
                                                    font-weight: 600;
                                                    letter-spacing: 2px;
                                                }
                                            </style>
                                        </head>

                                        <body>
                                            <div class="card">
                                                <div class="icon-box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
                                                </div>
                                                <h1>System Locked</h1>
                                                <p class="message"><?php echo nl2br(htmlspecialchars($custom_msg)); ?></p>
                                                <div class="btn-group">
                                                    <a href="?page=logout" class="btn btn-primary">Logout from System</a>
                                                    <button onclick="location.reload()" class="btn btn-outline">Check Connection</button>
                                                </div>
                                            </div>
                                            <div class="brand">GATEPILOT CLOUD</div>
                                        </body>

                                        </html>
                                        <?php
                                        exit;
                }
            }
        }
    }
}
// ================================================================

// Set MySQL timezone to IST
if ($conn) {
    // Handle Template Downloads Global Handler
    if (isset($_GET['download_template'])) {
        $template = $_GET['download_template'];

        // Clear buffer to avoid output corruption
        while (ob_get_level())
            ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $template . '_template.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

        if ($template == 'materials') {
            fputcsv($output, array('Material Code', 'Material Description', 'Category'));
            fputcsv($output, array('MAT001', 'Cement', 'Raw Material'));
            fputcsv($output, array('MAT002', 'Steel Bars', 'Construction'));
        } elseif ($template == 'suppliers') {
            fputcsv($output, array('Supplier Name', 'Supplier Code'));
            fputcsv($output, array('Acme Corp', 'SUP001'));
            fputcsv($output, array('Global Logistics', 'SUP002'));
        }

        fclose($output);
        exit;
    }

    // Ensure vehicle_photo_url column exists
    $check_vehicle_col = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'vehicle_photo_url'");
    $check_old_col = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'driver_photo_url'");

    if (mysqli_num_rows($check_vehicle_col) == 0 && mysqli_num_rows($check_old_col) > 0) {
        // Rename old column to new one
        mysqli_query($conn, "ALTER TABLE truck_inward CHANGE driver_photo_url vehicle_photo_url VARCHAR(255)");
    } elseif (mysqli_num_rows($check_vehicle_col) == 0 && mysqli_num_rows($check_old_col) == 0) {
        // Create new column if neither exists
        mysqli_query($conn, "ALTER TABLE truck_inward ADD COLUMN vehicle_photo_url VARCHAR(255) AFTER photo_url");
    } elseif (mysqli_num_rows($check_vehicle_col) > 0 && mysqli_num_rows($check_old_col) > 0) {
        // Both exist - drop the old driver_photo_url column
        mysqli_query($conn, "ALTER TABLE truck_inward DROP COLUMN driver_photo_url");
    }

    // Add bill_photo_url column if it doesn't exist
    $check_bill_col = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'bill_photo_url'");
    if (mysqli_num_rows($check_bill_col) == 0) {
        mysqli_query($conn, "ALTER TABLE truck_inward ADD COLUMN bill_photo_url VARCHAR(255) AFTER vehicle_photo_url");
    }

    // Create loading/unloading tables if they don't exist
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Create patrol tables if they don't exist
    require_once dirname(__DIR__) . '/database/init_patrol_tables.php';
    initPatrolTables($conn);

    // Create manual registers table if it doesn't exist
    require_once dirname(__DIR__) . '/database/init_manual_registers_table.php';
    initManualRegistersTable($conn);

    // Create and seed manual register types table
    require_once dirname(__DIR__) . '/database/init_register_types_and_fields.php';
    initRegisterTypesTable($conn);

    // Create audit logs table
    require_once dirname(__DIR__) . '/database/init_audit_logs.php';
    initAuditLogsTable($conn);

    // Create issue tables if they don't exist
    require_once dirname(__DIR__) . '/database/init_issue_tables.php';
    initIssueTables($conn);

    // Ensure permissions column exists in user_master
    $check_perm_col = mysqli_query($conn, "SHOW COLUMNS FROM user_master LIKE 'permissions'");
    if (mysqli_num_rows($check_perm_col) == 0) {
        mysqli_query($conn, "ALTER TABLE user_master ADD COLUMN permissions TEXT DEFAULT NULL");
    }

    // Create persistent sessions table for "Login Forever"
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) UNIQUE NOT NULL,
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_used_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_token (token),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Custom Function: Show App Modal
    function showAppModal($title, $message, $type = 'success')
    {
        $color = ($type == 'success') ? '#10b981' : '#ef4444';
        $icon = ($type == 'success') ? '✅' : '❌';
        echo "
    <div id='app_modal_overlay' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50000; animation: fadeIn 0.3s;'>
        <div style='background: white; padding: 30px; border-radius: 16px; border-top: 6px solid $color; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 90%; max-width: 400px; text-align: center; transform: scale(0.9); animation: popIn 0.3s forwards;'>
            <div style='font-size: 50px; margin-bottom: 20px;'>$icon</div>
            <h2 style='margin: 0 0 10px; color: #1f2937; font-size: 24px;'>$title</h2>
            <p style='margin: 0 0 25px; color: #4b5563; font-size: 16px; line-height: 1.5;'>$message</p>
            <button onclick=\"document.getElementById('app_modal_overlay').remove()\" 
                    style='background: $color; color: white; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; transition: transform 0.1s;'>
                OK
            </button>
        </div>
    </div>
    <script>
        // Simple animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        `;
        document.head.appendChild(style);
    </script>
    ";
    }


    // Add Material Inward Specific Columns to manual_registers
    $mr_cols = mysqli_query($conn, "SHOW COLUMNS FROM manual_registers");
    $mr_existing = [];
    while ($c = mysqli_fetch_assoc($mr_cols)) {
        $mr_existing[] = $c['Field'];
    }

    if (!in_array('material_code', $mr_existing))
        mysqli_query($conn, "ALTER TABLE manual_registers ADD COLUMN material_code VARCHAR(50) AFTER material_desc");
    if (!in_array('supp_code', $mr_existing))
        mysqli_query($conn, "ALTER TABLE manual_registers ADD COLUMN supp_code VARCHAR(50) AFTER party_name");
    if (!in_array('category', $mr_existing))
        mysqli_query($conn, "ALTER TABLE manual_registers ADD COLUMN category VARCHAR(100) AFTER material_code");
    if (!in_array('pack_size', $mr_existing))
        mysqli_query($conn, "ALTER TABLE manual_registers ADD COLUMN pack_size VARCHAR(50) AFTER quantity");

}

// Check for exceeded POST size limit (common with multiple large photo uploads)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $max_post = ini_get('post_max_size');
    $content_length = round($_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 2);
    $error_msg = "❌ <strong>Upload Failed:</strong> The total size of your photos ($content_length MB) exceeds the server's allowed limit ($max_post). <br><br><strong>Why:</strong> High-resolution photos taken directly from phone cameras are often very large. <br><br><strong>Solution:</strong> Please upload only 1-2 photos at a time and edit the record later to add more, or ask your administrator to increase the PHP 'post_max_size' on your Hostinger/Server settings.";
}

// Check if logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ?page=login');
        exit;
    }
}

// Format duration in hours and minutes
function formatDuration($hours)
{
    $total_minutes = round($hours * 60);
    $display_hours = floor($total_minutes / 60);
    $display_minutes = $total_minutes % 60;

    if ($display_hours > 0) {
        return $display_hours . ' hr' . ($display_hours > 1 ? 's' : '') . ' ' . $display_minutes . ' min (' . number_format($hours, 1) . ' hrs)';
    } else {
        return $display_minutes . ' min (' . number_format($hours, 1) . ' hrs)';
    }
}

// Log Activity for Audit Trail
function logActivity($conn, $activity_type, $module, $details = "")
{
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'guest';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
    $activity_type = mysqli_real_escape_string($conn, $activity_type);
    $module = mysqli_real_escape_string($conn, $module);
    $details = mysqli_real_escape_string($conn, is_array($details) ? json_encode($details) : $details);

    $sql = "INSERT INTO audit_logs (user_id, username, activity_type, module, details, ip_address, user_agent) 
            VALUES (" . ($user_id ? $user_id : "NULL") . ", '$username', '$activity_type', '$module', '$details', '$ip_address', '$user_agent')";
    return mysqli_query($conn, $sql);
}

function getSetting($conn, $key, $default = null)
{
    $key = mysqli_real_escape_string($conn, $key);
    $res = mysqli_query($conn, "SELECT setting_value FROM app_settings WHERE setting_key = '$key'");
    if ($row = mysqli_fetch_assoc($res)) {
        return $row['setting_value'];
    }
    return $default;
}

/**
 * Returns the maximum number of users allowed for a specific tenant.
 * Fetched from the Master Database Index.
 */
function getUserQuota($tenant_slug)
{
    if ($tenant_slug === 'admin')
        return 999; // Super Admin has no limit

    $master_conn = getMasterDatabaseConnection();
    $esc_slug = mysqli_real_escape_string($master_conn, $tenant_slug);

    // Check for user_limit column first (Self-Healing)
    $res = mysqli_query($master_conn, "SELECT user_limit FROM tenants WHERE slug = '$esc_slug'");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        return (int) ($row['user_limit'] ?? 10);
    }

    return 10; // Default fallback
}

// Set setting value
function setSetting($conn, $key, $value)
{
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);
    $result = mysqli_query($conn, "INSERT INTO app_settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value = '$value'");
    return $result;
}

// Check User Permission
// Check User Permission
function hasPermission($key)
{
    global $conn;
    if (!isset($_SESSION['user_id']))
        return false;
    if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1)
        return true;
    if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin')
        return true;

    // Always fetch fresh permissions to ensure updates apply immediately
    // if (!isset($_SESSION['permissions'])) {
    if (true) {
        $uid = $_SESSION['user_id'];
        $res = mysqli_query($conn, "SELECT permissions FROM user_master WHERE id = $uid");
        if ($res && $r = mysqli_fetch_assoc($res)) {
            $_SESSION['permissions'] = $r['permissions'];
        } else {
            $_SESSION['permissions'] = '{}';
        }
    }

    $perms = json_decode($_SESSION['permissions'] ?? '{}', true);

    // Retry decoding if failed (maybe double escaped)
    if (json_last_error() !== JSON_ERROR_NONE) {
        $raw = stripslashes($_SESSION['permissions'] ?? '{}');
        $perms = json_decode($raw, true);
    }

    // Safety Net Removed - We rely on data now.
    // If decoding still failed, $perms is null. Set to empty array.
    if (!is_array($perms))
        $perms = [];

    // Flatten keys for easier access (e.g. 'pages.dashboard')
    $parts = explode('.', $key);
    $category = $parts[0];
    $action = $parts[1] ?? null;

    if ($category && $action) {
        $granted = isset($perms[$category][$action]) ? $perms[$category][$action] : false;

        // Backward compatibility for employee_scan move
        if (!$granted && $category === 'pages' && $action === 'employee_scan' && isset($perms['actions']['employee_scan'])) {
            $granted = $perms['actions']['employee_scan'];
        }

        return $granted;
    }
    return false;
}

// Persistent Login — runs on EVERY request when a remember-cookie exists.
// This makes auth DB-driven: even if PHP session GC kills the session file,
// the next request immediately restores the session from the cookie token.
// ========== PERSISTENT LOGIN (REMEMBER ME) ==========
if (!isLoggedIn() && isset($_COOKIE['GATEPILOT_REMEMBER'])) {
    $token = mysqli_real_escape_string($master_conn, $_COOKIE['GATEPILOT_REMEMBER']);

    // 1. Search GLOBAL sessions in Master DB
    $g_query = "SELECT * FROM global_sessions WHERE token = '$token' LIMIT 1";
    $g_res = mysqli_query($master_conn, $g_query);

    if ($g_res && $g_row = mysqli_fetch_assoc($g_res)) {
        $r_slug = $g_row['tenant_slug'];
        $r_uid = $g_row['user_id'];

        // 2. Fetch Tenant Config
        $t_res = mysqli_query($master_conn, "SELECT db_name FROM tenants WHERE slug = '$r_slug' LIMIT 1");
        if ($t_row = mysqli_fetch_assoc($t_res)) {
            $r_db = $t_row['db_name'];
            $r_conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, $r_db);

            if ($r_conn) {
                // 3. Restore User Session
                $u_res = mysqli_query($r_conn, "SELECT * FROM user_master WHERE id = $r_uid LIMIT 1");
                if ($user = mysqli_fetch_assoc($u_res)) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['tenant_slug'] = $r_slug;
                    $_SESSION['tenant_db'] = $r_db;
                    $_SESSION['super_admin'] = (int) ($user['super_admin'] ?? 0);
                    $_SESSION['permissions'] = $user['permissions'] ?? '';

                    // Refresh connection to its new tenant state
                    $conn = $r_conn;
                }
            }
        }
    } else {
        // Token was not found in Master store — clean it up
        setcookie('GATEPILOT_REMEMBER', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        unset($_COOKIE['GATEPILOT_REMEMBER']);
    }
}

// ========== LEGACY SESSION CLEANUP (PREVENT 500 ERRORS) ==========
// If PHPSESSID is active but missing our new tenant context, it's a "bad" session.
// Wipe it silently so the user can see the login page.
if (isLoggedIn() && !isset($_SESSION['tenant_slug'])) {
    $_SESSION = array();
    if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
        setcookie('GATEPILOT_REMEMBER', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        unset($_COOKIE['GATEPILOT_REMEMBER']);
    }
    @session_destroy();
    header("Location: ?page=login&session_reset=legacy");
    exit;
}

// Page routing
$page = isset($_GET['page']) ? $_GET['page'] : (isLoggedIn() ? 'dashboard' : 'login');

// CRITICAL SESSION VALIDATION: If logged in but tenant context is missing (legacy session), force re-login.
// This prevents "Dashboard direct access" issues with new multi-tenant changes.
if (isLoggedIn() && !isset($_SESSION['tenant_slug']) && $page !== 'logout') {
    // Only skip for logout to prevent loops
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    // Also clear the persistent login cookie to prevent infinite loop during restore
    if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
        setcookie('GATEPILOT_REMEMBER', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        unset($_COOKIE['GATEPILOT_REMEMBER']);
    }

    session_destroy();
    header('Location: ?page=login&session_reset=1');
    exit;
}

// Change Password Action
if ($page == 'change_password_action' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['user_id'];
    $old = mysqli_real_escape_string($conn, $_POST['old_password']);
    $new = mysqli_real_escape_string($conn, $_POST['new_password']);

    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM user_master WHERE id=$uid"));
    if ($u && password_verify($old, $u['password'])) {
        if (strlen($new) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
            exit;
        }
        if ($old === $new) {
            echo json_encode(['success' => false, 'message' => 'New password cannot be the same as current password']);
            exit;
        }
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        if (mysqli_query($conn, "UPDATE user_master SET password='$hashed' WHERE id=$uid")) {
            logActivity($conn, 'PASSWORD_CHANGE', 'Profile', "User changed their password");
            echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating database: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password']);
    }
    exit;
}

// Handle login
if ($page == 'login' && isset($_POST['login'])) {
    $tenant_slug = strtolower(trim(mysqli_real_escape_string($conn, $_POST['tenant_slug'] ?? '')));
    $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    // 1. Identify Tenant Database
    $master_conn = getMasterDatabaseConnection();
    if (!$master_conn) {
        die("❌ ERROR: Could not connect to the Master Management database. Check config.php credentials.");
    }

    // Master Admin Override: If slug is 'admin', use the master database directly
    if ($tenant_slug === 'admin') {
        $tenant = [
            'db_name' => DB_NAME,
            'customer_name' => 'Master Admin',
            'is_active' => 1
        ];
    } else {
        // Check if slug exists in tenants table
        $tenant_check = mysqli_query($master_conn, "SELECT db_name, customer_name, is_active FROM tenants WHERE slug = '$tenant_slug'");
        $tenant = mysqli_fetch_assoc($tenant_check);
    }

    if ($tenant) {
        if (!$tenant['is_active']) {
            $error = "🚫 This system is temporarily DISABLED. Please contact your administrator.";
        } else {
            $_SESSION['tenant_db'] = $tenant['db_name'];
            $_SESSION['tenant_slug'] = $tenant_slug;
            $_SESSION['customer_name'] = $tenant['customer_name'];

            // Re-initialize connection to the tenant database
            $conn = getDatabaseConnection();

            $query = "SELECT * FROM user_master WHERE username = '$username' AND is_active = 1";
            $result = mysqli_query($conn, $query);

            if ($row = mysqli_fetch_assoc($result)) {
                // Secure password check using password_verify
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['full_name'] = $row['full_name'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['super_admin'] = (int) $row['super_admin'];
                    $_SESSION['permissions'] = $row['permissions'];

                    // If the password was plain text, update it to hashed version now
                    if ($password === $row['password'] && !empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        mysqli_query($conn, "UPDATE user_master SET password = '$hashed_password' WHERE id = " . $row['id']);
                    }

                    // Audit Log
                    logActivity($conn, 'LOGIN_SUCCESS', 'Auth', "User '{$row['username']}' logged in to tenant '$tenant_slug'");

                    // Persistent Login Logic ("Login Forever") - WRITE TO MASTER DB
                    try {
                        $token = bin2hex(random_bytes(32));
                    } catch (Exception $e) {
                        $token = bin2hex(openssl_random_pseudo_bytes(32));
                    }

                    $user_agent = mysqli_real_escape_string($master_conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
                    $user_id = (int) $row['id'];
                    $slug_safe = mysqli_real_escape_string($master_conn, $tenant_slug);

                    // Ensure global table exists in Master DB
                    mysqli_query($master_conn, "CREATE TABLE IF NOT EXISTS global_sessions (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        token VARCHAR(64) UNIQUE NOT NULL,
                        tenant_slug VARCHAR(100) NOT NULL,
                        user_agent TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        last_used_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_token (token)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                    $token_sql = "INSERT INTO global_sessions (user_id, token, tenant_slug, user_agent) VALUES ($user_id, '$token', '$slug_safe', '$user_agent')";
                    if (mysqli_query($master_conn, $token_sql)) {
                        setcookie('GATEPILOT_REMEMBER', $token, [
                            'expires' => time() + (365 * 24 * 60 * 60),
                            'path' => '/',
                            'domain' => '',
                            'secure' => false,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    header('Location: ?page=dashboard');
                    exit;
                }
            }
        }
    }

    if (!isset($error)) {
        logActivity($conn, 'LOGIN_FAILED', 'Auth', "Failed login for '$username' on tenant '$tenant_slug'");
        $error = "Invalid Company Code or Credentials!";
    }
}

// Handle logout
if ($page == 'logout') {
    // Audit Log: Logout (must be before session_destroy)
    if (isLoggedIn()) {
        logActivity($conn, 'LOGOUT', 'Auth', "User '{$_SESSION['username']}' logged out.");
    }

    // Clear Persistent Login Token if exists
    if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
        $token = mysqli_real_escape_string($conn, $_COOKIE['GATEPILOT_REMEMBER']);
        mysqli_query($conn, "DELETE FROM user_sessions WHERE token = '$token'");
        $cookie_domain = $_cookie_domain ?? '';
        setcookie('GATEPILOT_REMEMBER', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $cookie_domain,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Clear session data
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: ?page=login');
    exit;
}



// Message handling from session
$session_success = null;
$session_error = null;
if (isset($_SESSION['success_msg'])) {
    $session_success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $session_error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Detection for AJAX requests to prevent HTML output in JSON responses
$is_ajax_call = (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') || strpos($page, 'get-') === 0);

// Display Modal if messages exist (Skip for AJAX calls to avoid JSON corruption)
if (!$is_ajax_call) {
    if ($session_success) {
        showAppModal('Success', $session_success, 'success');
    }
    if ($session_error) {
        showAppModal('Error', $session_error, 'error');
    }
}

// Global Permission Save Handler (Works for both the modal and the user-permissions page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    if (!isset($_SESSION['user_id']))
        exit;

    // Ensure the user has permission to manage rights (Role check instead of hasPermission to avoid circular logic for initial setup)
    if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager') {
        $_SESSION['error_msg'] = "❌ Access denied!";
        header("Location: ?page=" . ($page ?: 'dashboard'));
        exit;
    }

    $user_id = intval($_POST['user_id']);

    // Extra security: Only super admin can edit himself (or other super admins)
    $check_q = mysqli_query($conn, "SELECT super_admin FROM user_master WHERE id=$user_id");
    $target = mysqli_fetch_assoc($check_q);
    if ($target && $target['super_admin'] == 1 && $user_id != $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "❌ Only a super admin can edit their own permissions!";
    } else {
        // Construct Permissions Array
        $permissions = [
            'pages' => [
                'dashboard' => isset($_POST['perm_page_dashboard']),
                'inward' => isset($_POST['perm_page_inward']),
                'outward' => isset($_POST['perm_page_outward']),
                'reports' => isset($_POST['perm_page_reports']),
                'loading' => isset($_POST['perm_page_loading']),
                'unloading' => isset($_POST['perm_page_unloading']),
                'masters' => isset($_POST['perm_page_masters']),
                'management' => isset($_POST['perm_page_management']),
                'tickets' => isset($_POST['perm_page_tickets']),
                'permissions' => isset($_POST['perm_page_permissions']),
                'guard_patrol' => isset($_POST['perm_page_patrol']),
                'inside' => isset($_POST['perm_page_inside']),
                'history' => isset($_POST['perm_page_history']),
                'register' => isset($_POST['perm_page_register']),
                'register_types' => isset($_POST['perm_page_register_types']),
                'audit_logs' => isset($_POST['perm_page_audit_logs']),
                'employee_scan' => isset($_POST['perm_page_employee_scan']),
                'app_issues' => isset($_POST['perm_page_app_issues'])
            ],
            'masters' => [
                'transporters' => isset($_POST['perm_master_transporters']),
                'drivers' => isset($_POST['perm_master_drivers']),
                'vehicles' => isset($_POST['perm_master_vehicles']),
                'purposes' => isset($_POST['perm_master_purposes']),
                'employees' => isset($_POST['perm_master_employees']),
                'departments' => isset($_POST['perm_master_departments']),
                'patrol' => isset($_POST['perm_master_patrol']),
                'materials' => isset($_POST['perm_master_materials']),
                'suppliers' => isset($_POST['perm_master_suppliers']),
                'users' => isset($_POST['perm_master_users']),
            ],
            'actions' => [
                'edit_record' => isset($_POST['perm_action_edit']),
                'delete_record' => isset($_POST['perm_action_delete']),
                'view_buttons' => isset($_POST['perm_action_buttons'])
            ]
        ];

        // 1. Fetch OLD permissions for detailed logging
        $old_perm_q = mysqli_query($conn, "SELECT permissions, username FROM user_master WHERE id=$user_id");
        $old_row = mysqli_fetch_assoc($old_perm_q);
        $old_perms = json_decode($old_row['permissions'] ?? '{}', true);
        $t_username = $old_row['username'] ?? 'Unknown';

        // 2. Define Label Mapping for human-readable audit logs
        $label_map = [
            'pages.dashboard' => 'Dashboard',
            'pages.inward' => 'Inward Entry',
            'pages.outward' => 'Outward Exit',
            'pages.reports' => 'Reports',
            'pages.loading' => 'Loading Checklist',
            'pages.unloading' => 'Unloading Checklist',
            'pages.employee_scan' => 'Employee QR Scan',
            'pages.masters' => 'Master Data',
            'pages.management' => 'Management Dash',
            'pages.tickets' => 'Tickets',
            'pages.permissions' => 'User Permissions',
            'pages.guard_patrol' => 'Guard Patrol',
            'pages.inside' => 'Trucks Inside',
            'pages.history' => 'Vehicle History',
            'pages.register' => 'Manual Registers',
            'pages.register_types' => 'Manage Register Types',
            'pages.audit_logs' => 'Audit Logs',
            'pages.app_issues' => 'Report Issues',
            'masters.transporters' => 'Master Transporters',
            'masters.drivers' => 'Master Drivers',
            'masters.vehicles' => 'Master Vehicles',
            'masters.purposes' => 'Master Purposes',
            'masters.employees' => 'Master Employees',
            'masters.departments' => 'Master Departments',
            'masters.patrol' => 'Master Patrol QR',
            'masters.materials' => 'Master Materials',
            'masters.suppliers' => 'Master Suppliers',
            'masters.users' => 'Master Users',
            'masters.settings' => 'Master Settings',
            'actions.edit_record' => 'Action Edit',
            'actions.delete_record' => 'Action Delete',
            'actions.view_buttons' => 'Action View Buttons'
        ];

        // 3. Calculate Diff and construct single summary
        $changes = [];
        foreach ($permissions as $cat => $subs) {
            foreach ($subs as $k => $val) {
                $old_val = (isset($old_perms[$cat][$k]) && $old_perms[$cat][$k]) ? true : false;
                $new_val = (bool) $val;
                if ($old_val !== $new_val) {
                    $mapKey = "$cat.$k";
                    $label = $label_map[$mapKey] ?? ucwords(str_replace('_', ' ', $k));
                    $changes[] = "$label: [" . ($old_val ? 'ON' : 'OFF') . " ➔ " . ($new_val ? 'ON' : 'OFF') . "]";
                }
            }
        }

        $perm_json = mysqli_real_escape_string($conn, json_encode($permissions));

        if (mysqli_query($conn, "UPDATE user_master SET permissions = '$perm_json' WHERE id = $user_id")) {
            $_SESSION['success_msg'] = "✅ Permissions updated successfully!";

            if (!empty($changes)) {
                $change_summary = implode(", ", $changes);
                logActivity($conn, 'PERMISSION_UPDATE', 'Access Control', "Updated $t_username: $change_summary");
            }

            // Re-fetch for current session if editing self
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['permissions'] = json_encode($permissions);
            }
        } else {
            $_SESSION['error_msg'] = "❌ Error updating permissions: " . mysqli_error($conn);
        }
    }
    header("Location: ?page=" . ($page ?: 'dashboard') . (isset($_GET['id']) ? '&id=' . $_GET['id'] : ''));
    exit;
}


// ==================== AUTHENTICATION CHECKS (MUST BE BEFORE HTML OUTPUT) ====================
// Check authentication for all protected pages
$protected_pages = [
    'dashboard',
    'inward',
    'outward',
    'details',
    'inward-details',
    'outward-details',
    'entry',
    'loading-details',
    'unloading-details',
    'admin',
    'reports',
    'reports',
    'guard-patrol',
    'tickets',
    'get-employee-details',
    'get-employee-entry',
    'employee-entry-action',
    'management',
    'user-permissions',
    'inside',
    'vehicle-history',
    'register-entry',
    'view-registers',
    'manage-register-types',
    'edit-register-entry'
];

if (in_array($page, $protected_pages)) {
    requireLogin();

    // Permission Check for Pages
    $page_permission_map = [
        'dashboard' => 'pages.dashboard',
        'inward' => 'pages.inward',
        'outward' => 'pages.outward',
        'reports' => 'pages.reports',
        'loading-details' => 'pages.loading',
        'loading' => 'pages.loading',
        'unloading-details' => 'pages.unloading',
        'unloading' => 'pages.unloading',
        'admin' => 'pages.masters',
        'management' => 'pages.management',
        'guard-patrol' => 'pages.guard_patrol',
        'tickets' => 'pages.tickets',
        'user-permissions' => 'pages.permissions',
        'inside' => 'pages.inside',
        'vehicle-history' => 'pages.history',
        'register-entry' => 'pages.register',
        'view-registers' => 'pages.register',
        'manage-register-types' => 'pages.register_types',
        'edit-register-entry' => 'pages.register'
    ];

    if (isset($page_permission_map[$page])) {
        if (!hasPermission($page_permission_map[$page])) {
            // Access Denied
            if ($page != 'dashboard') {
                // Redirect to dashboard or show error
                // But wait, what if they don't have dashboard access?
                // Then show a simple "Access Denied" message
                if ($page == 'dashboard') {
                    echo '<div style="padding: 50px; text-align: center;"><h1>⛔ Access Denied</h1><p>You do not have permission to view the dashboard.</p><a href="?page=logout">Logout</a></div>';
                    exit;
                } else {
                    echo "<script>alert('⛔ You do not have permission to access this page.'); window.location.href='?page=dashboard';</script>";
                    exit;
                }
            } else {
                echo '<div style="padding: 50px; text-align: center;"><h1>⛔ Access Denied</h1><p>You do not have permission to view the dashboard.</p><a href="?page=logout">Logout</a></div>';
                exit;
            }
        }
    }
}

// Check admin/manager access for admin page


// Handle logo removal (must be before HTML output)
if ($page == 'admin' && isset($_GET['remove_logo']) && isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1) {
    requireLogin();
    $m_conn = getMasterDatabaseConnection();
    $current_logo = getSetting($m_conn, 'company_logo');
    if ($current_logo) {
        $logo_file = basename($current_logo);
        $logo_path = LOGO_UPLOAD_DIR . $logo_file;
        if (file_exists($logo_path)) {
            @unlink($logo_path);
        }
    }
    setSetting($m_conn, 'company_logo', '');
    logActivity($conn, 'LOGO_REMOVE', 'Settings', "Removed company logo from the system.");
    header('Location: ?page=admin&master=settings');
    exit;
}

// Handle logo upload (must be before HTML output)
if ($page == 'admin' && isset($_POST['upload_logo']) && isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1) {
    requireLogin();
    if (isset($_FILES['company_logo'])) {
        if ($_FILES['company_logo']['error'] === UPLOAD_ERR_OK && $_FILES['company_logo']['size'] > 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['company_logo']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                $filename = 'company_logo_' . time() . '.' . $file_ext;
                $upload_path = LOGO_UPLOAD_DIR . $filename;

                // Delete old logo if exists
                $m_conn = getMasterDatabaseConnection();
                $old_logo = getSetting($m_conn, 'company_logo');
                if ($old_logo) {
                    $old_logo_file = basename($old_logo);
                    $old_logo_path = LOGO_UPLOAD_DIR . $old_logo_file;
                    if (file_exists($old_logo_path)) {
                        @unlink($old_logo_path);
                    }
                }

                // Ensure directory exists and is writable
                if (!is_dir(LOGO_UPLOAD_DIR)) {
                    mkdir(LOGO_UPLOAD_DIR, 0755, true);
                }

                if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                    // Store relative path; we'll prepend BASE_URL when rendering
                    $logo_url = 'uploads/logo/' . $filename;
                    setSetting($m_conn, 'company_logo', $logo_url);
                    logActivity($conn, 'LOGO_UPLOAD', 'Settings', "Uploaded new company logo: '$filename'");
                    header('Location: ?page=admin&master=settings&uploaded=1');
                    exit;
                }
            }
        }
    }
    // If we get here, there was an error
    header('Location: ?page=admin&master=settings&error=1');
    exit;
}

// Handle management page access check (must be before HTML output)
if ($page == 'management') {

    if (!isset($_SESSION['role']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'manager')) {
        header('Location: ?page=dashboard');
        exit;
    }
}

// Handle Employee Master Save/Delete (must be before HTML output)
if ($page == 'admin' && isset($_GET['master']) && $_GET['master'] == 'employees') {
    requireLogin();
    // Handle Delete
    if (isset($_GET['delete_employee'])) {
        $id = (int) $_GET['delete_employee'];
        // Get employee name for log
        $e_res = mysqli_query($conn, "SELECT employee_name FROM employee_master WHERE id=$id");
        $e_row = mysqli_fetch_assoc($e_res);
        $e_name = $e_row['employee_name'] ?? 'Unknown';

        if (mysqli_query($conn, "DELETE FROM employee_master WHERE id=$id")) {
            logActivity($conn, 'EMPLOYEE_DELETE', 'Masters', "Deleted Employee: Name: [$e_name] (ID: $id)");
            $_SESSION['success_msg'] = "✅ Employee deleted successfully!";
            session_write_close();
            header("Location: ?page=admin&master=employees&t=" . time());
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error deleting employee: " . mysqli_error($conn);
        }
    }

    // Handle Import Employees
    if (isset($_POST['import_employees'])) {
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
            $file = $_FILES['import_file']['tmp_name'];
            $handle = fopen($file, "r");
            $success_count = 0;
            $error_count = 0;
            $first_row = true;

            $imported_names = [];
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($first_row) {
                    $first_row = false;
                    continue; // Skip header
                }

                $emp_id = mysqli_real_escape_string($conn, $data[0] ?? '');
                $name = mysqli_real_escape_string($conn, $data[1] ?? '');
                $mobile = mysqli_real_escape_string($conn, $data[2] ?? '');
                $email = mysqli_real_escape_string($conn, $data[3] ?? '');
                $dept = mysqli_real_escape_string($conn, $data[4] ?? '');
                $vehicle = strtoupper(mysqli_real_escape_string($conn, $data[5] ?? ''));
                $vehicle_type = mysqli_real_escape_string($conn, $data[6] ?? '');

                $formatDate = function ($d) {
                    $d = trim($d);
                    if (empty($d))
                        return "NULL";
                    if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $d, $matches)) {
                        return "'" . $matches[3] . '-' . $matches[2] . '-' . $matches[1] . "'";
                    }
                    $ts = strtotime($d);
                    return $ts ? "'" . date('Y-m-d', $ts) . "'" : "NULL";
                };

                $rc_expiry = $formatDate($data[7] ?? '');
                $license_expiry = $formatDate($data[8] ?? '');
                $pollution_expiry = $formatDate($data[9] ?? '');
                $fitness_expiry = $formatDate($data[10] ?? '');

                if (!empty($emp_id) && !empty($name)) {
                    // Check for duplicate
                    $dup_check = mysqli_query($conn, "SELECT id FROM employee_master WHERE employee_id='$emp_id'");
                    if (mysqli_num_rows($dup_check) > 0) {
                        $error_count++;
                        continue;
                    }

                    $qr_data = json_encode([
                        'type' => 'employee',
                        'id' => $emp_id,
                        'name' => $name,
                        'vehicle' => $vehicle,
                        'department' => $dept
                    ]);
                    $qr_data_esc = mysqli_real_escape_string($conn, $qr_data);

                    $sql = "INSERT INTO employee_master (
                                employee_id, employee_name, mobile, email, department, vehicle_number, 
                                vehicle_type, rc_expiry, license_expiry, pollution_expiry, fitness_expiry, 
                                qr_code_data
                            ) VALUES (
                                '$emp_id', '$name', '$mobile', '$email', '$dept', '$vehicle', 
                                '$vehicle_type', $rc_expiry, $license_expiry, $pollution_expiry, $fitness_expiry, 
                                '$qr_data_esc'
                            )";

                    try {
                        if (mysqli_query($conn, $sql)) {
                            $success_count++;
                            $imported_names[] = "$name ($emp_id)";
                        } else {
                            $error_count++;
                        }
                    } catch (Exception $e) {
                        $error_count++;
                    }
                }
            }
            fclose($handle);
            $log_details = "Imported $success_count employees from CSV.";
            if (!empty($imported_names)) {
                $log_details .= " Names: " . implode(", ", $imported_names);
            }
            logActivity($conn, 'EMPLOYEE_IMPORT', 'Masters', $log_details);
            $_SESSION['success_msg'] = "✅ Imported $success_count employees. Skipped $error_count duplicates/errors.";
            session_write_close();
            header("Location: ?page=admin&master=employees&t=" . time());
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error uploading file.";
        }
    }

    // Handle Save
    if (isset($_POST['save_employee'])) {
        $emp_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
        $name = mysqli_real_escape_string($conn, $_POST['employee_name']);
        $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
        $email = mysqli_real_escape_string($conn, $_POST['employee_email']);
        $dept = mysqli_real_escape_string($conn, $_POST['department']);
        $vehicle = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));

        // New Fields
        $vehicle_type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
        $rc_expiry = !empty($_POST['rc_expiry']) ? "'" . mysqli_real_escape_string($conn, $_POST['rc_expiry']) . "'" : "NULL";
        $license_expiry = !empty($_POST['license_expiry']) ? "'" . mysqli_real_escape_string($conn, $_POST['license_expiry']) . "'" : "NULL";
        $pollution_expiry = !empty($_POST['pollution_expiry']) ? "'" . mysqli_real_escape_string($conn, $_POST['pollution_expiry']) . "'" : "NULL";
        $fitness_expiry = !empty($_POST['fitness_expiry']) ? "'" . mysqli_real_escape_string($conn, $_POST['fitness_expiry']) . "'" : "NULL";

        // Photo Upload Handling
        $photo_path = '';
        if (isset($_FILES['employee_photo']) && $_FILES['employee_photo']['error'] == 0) {
            $ext = pathinfo($_FILES['employee_photo']['name'], PATHINFO_EXTENSION);
            $filename = "emp_" . $emp_id . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['employee_photo']['tmp_name'], EMPLOYEE_UPLOAD_DIR . $filename)) {
                $photo_path = $filename;
            }
        }

        $id = isset($_POST['e_id']) ? $_POST['e_id'] : null;

        try {
            // Generate QR data automatically with department
            $qr_data = json_encode([
                'type' => 'employee',
                'id' => $emp_id,
                'name' => $name,
                'vehicle' => $vehicle,
                'department' => $dept
            ]);

            if ($id && !empty($id)) {
                $current_res = mysqli_query($conn, "SELECT * FROM employee_master WHERE id=$id");
                $current = mysqli_fetch_assoc($current_res);
                $changes = [];
                if ($current) {
                    $changes_list = auditDiff($current, $_POST, ['e_id'], [
                        'employee_id' => 'EmpID',
                        'employee_name' => 'Name',
                        'mobile' => 'Mobile',
                        'email' => 'Email',
                        'department' => 'Dept',
                        'vehicle_number' => 'Vehicle',
                        'vehicle_type' => 'Type'
                    ]);
                    $changes = $changes_list ? explode("\n", $changes_list) : [];


                    // Compare Expiry Dates (Handling NULL vs empty/quoted string)
                    $old_rc = $current['rc_expiry'] ? date('Y-m-d', strtotime($current['rc_expiry'])) : 'NULL';
                    $new_rc = trim($rc_expiry, "'");
                    if ($old_rc != $new_rc)
                        $changes[] = "RC Exp: [$old_rc ➔ $new_rc]";

                    $old_lic = $current['license_expiry'] ? date('Y-m-d', strtotime($current['license_expiry'])) : 'NULL';
                    $new_lic = trim($license_expiry, "'");
                    if ($old_lic != $new_lic)
                        $changes[] = "Lic Exp: [$old_lic ➔ $new_lic]";

                    $old_poll = $current['pollution_expiry'] ? date('Y-m-d', strtotime($current['pollution_expiry'])) : 'NULL';
                    $new_poll = trim($pollution_expiry, "'");
                    if ($old_poll != $new_poll)
                        $changes[] = "Poll Exp: [$old_poll ➔ $new_poll]";

                    $old_fit = $current['fitness_expiry'] ? date('Y-m-d', strtotime($current['fitness_expiry'])) : 'NULL';
                    $new_fit = trim($fitness_expiry, "'");
                    if ($old_fit != $new_fit)
                        $changes[] = "Fit Exp: [$old_fit ➔ $new_fit]";
                }

                $sql = "UPDATE employee_master SET 
                        employee_id='$emp_id', 
                        employee_name='$name', 
                        mobile='$mobile', 
                        email='$email',
                        department='$dept', 
                        vehicle_number='$vehicle', 
                        vehicle_type='$vehicle_type',
                        rc_expiry=$rc_expiry,
                        license_expiry=$license_expiry,
                        pollution_expiry=$pollution_expiry,
                        fitness_expiry=$fitness_expiry,
                        qr_code_data='$qr_data'";
                if (!empty($photo_path)) {
                    $sql .= ", photo='$photo_path'";
                    $changes[] = "Photo: [Updated]";
                }
                $sql .= " WHERE id=$id";

                if (mysqli_query($conn, $sql)) {
                    $details = "Updated Employee Entry:\nName: [$name]\nID: [$emp_id]\nVehicle: [$v_num]\nDept/Location: [$dept]";
                    if (!empty($changes)) {
                        $details .= "\nChanges:\n" . implode("\n", $changes);
                    }
                    logActivity($conn, 'EMPLOYEE_UPDATE', 'Masters', $details);
                    $_SESSION['success_msg'] = "✅ Employee updated successfully!";
                    session_write_close();
                    header("Location: ?page=admin&master=employees&t=" . time());
                    exit;
                }
            } else {
                $sql = "INSERT INTO employee_master (
                            employee_id, employee_name, mobile, email, department, vehicle_number, 
                            vehicle_type, rc_expiry, license_expiry, pollution_expiry, fitness_expiry, 
                            qr_code_data, photo
                        ) VALUES (
                            '$emp_id', '$name', '$mobile', '$email', '$dept', '$vehicle', 
                            '$vehicle_type', $rc_expiry, $license_expiry, $pollution_expiry, $fitness_expiry, 
                            '$qr_data', '$photo_path'
                        )";

                if (mysqli_query($conn, $sql)) {
                    $details = "Created Employee:\n" . auditFromPost($_POST, [], ['employee_name' => 'Name', 'employee_id' => 'EmpID', 'mobile' => 'Mobile', 'department' => 'Dept', 'vehicle_number' => 'Vehicle']);
                    if ($photo_path) {
                        $details .= "\nPhoto: [Uploaded]";
                    }
                    logActivity($conn, 'EMPLOYEE_CREATE', 'Masters', $details);
                    $_SESSION['success_msg'] = "✅ Employee added successfully!";
                    session_write_close();
                    header("Location: ?page=admin&master=employees&t=" . time());
                    exit;
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry error code
                $error_msg = "❌ Error: Employee ID '$emp_id' or Vehicle Number '$vehicle' already exists!";
            } else {
                $error_msg = "❌ Database Error: " . $e->getMessage();
            }
        }
    }
}

// Handle patrol scan processing
if ($page == 'guard-patrol' && isset($_POST['patrol_scan'])) {
    requireLogin();
    $qr_data = mysqli_real_escape_string($conn, $_POST['qr_data']);
    $guard_id = $_SESSION['user_id'];
    $guard_name = $_SESSION['full_name'];
    $scan_datetime = date('Y-m-d H:i:s');

    // Find location by QR data or Location ID
    $loc_q = mysqli_query($conn, "SELECT id, location_name FROM patrol_locations WHERE qr_code_data = '$qr_data' OR location_id = '$qr_data' LIMIT 1");

    if ($loc_q && mysqli_num_rows($loc_q) > 0) {
        $loc = mysqli_fetch_assoc($loc_q);
        $location_id = $loc['id'];
        $location_name = $loc['location_name'];

        // Prevent duplicate scans within 5 minutes
        $check_dup = mysqli_query($conn, "SELECT id FROM patrol_logs WHERE location_id = $location_id AND scan_datetime >= DATE_SUB('$scan_datetime', INTERVAL 5 MINUTE) LIMIT 1");

        if (mysqli_num_rows($check_dup) > 0) {
            $error_msg = "⚠️ Duplicate scan! This location was already scanned in the last 5 minutes.";
        } else {
            $session_id = date('Ymd') . '-' . $guard_id; // Simple daily session ID
            $sql = "INSERT INTO patrol_logs (location_id, guard_id, guard_name, scan_datetime, session_id) 
                    VALUES ($location_id, $guard_id, '$guard_name', '$scan_datetime', '$session_id')";

            if (mysqli_query($conn, $sql)) {
                logActivity($conn, 'PATROL_SCAN', 'Patrol', "Patrol Log: Location: [$location_name], Guard: [{$_SESSION['full_name']}], Result: [OK]");
                $_SESSION['success_msg'] = "✅ Patrol logged successfully: <strong>$location_name</strong> at " . date('h:i A');
            } else {
                $_SESSION['error_msg'] = "❌ Error logging patrol: " . mysqli_error($conn);
            }
        }
    } else {
        $_SESSION['error_msg'] = "❌ Invalid QR code! Location not found in system.";
    }
    header("Location: ?page=guard-patrol");
    exit;
}

// Handle Patrol Issue Reporting
if ($page == 'guard-patrol' && isset($_POST['report_issue'])) {
    requireLogin();
    $guard_id = $_SESSION['user_id'];
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $photo_url = '';

    // Handle Photo Upload
    if (isset($_FILES['issue_photo']) && $_FILES['issue_photo']['error'] == 0) {
        $upload_dir = 'uploads/issues/';
        if (!file_exists($upload_dir))
            mkdir($upload_dir, 0777, true);

        $file_ext = pathinfo($_FILES['issue_photo']['name'], PATHINFO_EXTENSION);
        $file_name = 'issue_' . time() . '_' . $guard_id . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['issue_photo']['tmp_name'], $target_file)) {
            $photo_url = $target_file;
        }
    }

    if ($location_id > 0) {
        $sql = "INSERT INTO patrol_issues (location_id, reported_by, issue_description, photo_url) 
                VALUES ($location_id, $guard_id, '$description', '$photo_url')";

        if (mysqli_query($conn, $sql)) {
            $ticket_id = mysqli_insert_id($conn);
            $loc_res = mysqli_query($conn, "SELECT location_name FROM patrol_locations WHERE id = $location_id");
            $loc_name = ($l_row = mysqli_fetch_assoc($loc_res)) ? $l_row['location_name'] : 'Unknown';

            $details = "Issue Ticket Created:\n";
            $details .= "Ticket Id: $ticket_id\n";
            $details .= "Location: $loc_name\n";
            $details .= "Issue: $description";

            if ($photo_url) {
                $details .= "\nPhoto: [Uploaded]";
            }
            logActivity($conn, 'PATROL_ISSUE', 'Patrol', $details);
            $_SESSION['success_msg'] = "✅ Issue reported successfully. Ticket created.";
        } else {
            $_SESSION['error_msg'] = "❌ Error reporting issue: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_msg'] = "❌ Please select a location.";
    }
    header("Location: ?page=guard-patrol");
    exit;
}

// Handle Manual Register Entry (FULLY DYNAMIC)
if ($page == 'register-entry' && isset($_POST['save_register'])) {
    $result = $registers_manager->saveEntry($_POST['register_type'], $_POST, $_SESSION['user_id'] ?? 0);

    if ($result['status'] === 'success') {
        $id = $result['id'];
        $type = $registers_manager->getType($_POST['register_type']);
        $title = $type['title'] ?? $_POST['register_type'];

        $log_parts = [];
        if ($type && isset($type['fields'])) {
            foreach ($type['fields'] as $f) {
                $f_name = $f['name'];
                $f_label = $f['label'] ?? $f_name;
                $val = $_POST[$f_name] ?? '';
                // Map from dynamic_registers table schema if relevant
                if ($f_name == 'in_time')
                    $val = $_POST['in_time'] ?? '';
                if ($f_name == 'out_time')
                    $val = $_POST['out_time'] ?? '';
                if ($f_name == 'date')
                    $val = $_POST['date'] ?? '';

                if (!empty($val))
                    $log_parts[] = "$f_label: [$val]";
            }
        }

        $details = "Created Register Entry:";
        if (!empty($log_parts)) {
            $details .= "\n" . implode("\n", $log_parts);
        }

        logActivity($conn, 'REGISTER_CREATE', 'Registers', $details);
        $_SESSION['success_msg'] = "✅ Register entry logged successfully!";
        header("Location: ?page=register-entry");
        exit;
    } else {
        $error_msg = "❌ Error logging entry: " . $result['message'];
    }
}


// Handle Update Register (Edit Save)
if ($page == 'edit-register-entry' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_register'])) {
    $id = intval($_POST['id']);
    $reg_slug = $_POST['reg_type'] ?? '';

    // Fetch Current Data for comparison
    $curr_res = mysqli_query($conn, "SELECT * FROM manual_registers WHERE id = $id");
    $current = mysqli_fetch_assoc($curr_res);

    $type = $registers_manager->getType($reg_slug);
    $title = $type['title'] ?? $reg_slug;

    $cols_to_update = [];
    $changes = [];
    $dynamic_data = json_decode($current['dynamic_data'] ?? '{}', true) ?: [];
    $is_dynamic_changed = false;

    // Standard schema fields for direct mapping
    $standard_db_map = [
        'date' => 'entry_date',
        'vehicle_no' => 'vehicle_no',
        'challan_no' => 'challan_no',
        'gate_pass_no' => 'gate_pass_no',
        'in_time' => 'time_in',
        'out_time' => 'time_out',
        'party_name' => 'party_name',
        'supp_code' => 'supp_code',
        'material_desc' => 'material_desc',
        'material_code' => 'material_code',
        'category' => 'category',
        'quantity' => 'quantity',
        'pack_size' => 'pack_size',
        'transporter_name' => 'transporter_name',
        'security_sign' => 'security_sign',
        'remarks' => 'remarks',
        'department' => 'department',
        'recheck' => 'recheck_status',
        'received_by' => 'received_by',
        'handed_over_to' => 'handed_over_to',
        'reference_no' => 'reference_no',
        'received_quantity' => 'received_quantity',
        'out_time_date' => 'out_time_date'
    ];

    if ($type && isset($type['fields'])) {
        foreach ($type['fields'] as $f) {
            $f_name = $f['name'];
            $f_label = $f['label'] ?? $f_name;

            if (isset($_POST[$f_name])) {
                $new_val = $_POST[$f_name];

                // Determine where it's stored
                if (isset($standard_db_map[$f_name])) {
                    $db_col = $standard_db_map[$f_name];
                    $old_val = $current[$db_col] ?? '';
                } else {
                    $db_col = 'dynamic_data';
                    $old_val = $dynamic_data[$f_name] ?? '';
                }

                if (trim($old_val) != trim($new_val)) {
                    $changes[] = "$f_label: [$old_val ➔ $new_val]";
                    if ($db_col == 'dynamic_data') {
                        $dynamic_data[$f_name] = $new_val;
                        $is_dynamic_changed = true;
                    }
                }

                if ($db_col != 'dynamic_data') {
                    $val_esc = mysqli_real_escape_string($conn, $new_val);
                    $cols_to_update[] = "$db_col = '$val_esc'";
                }
            }
        }
    }

    if ($is_dynamic_changed) {
        $dyn_json = mysqli_real_escape_string($conn, json_encode($dynamic_data));
        $cols_to_update[] = "dynamic_data = '$dyn_json'";
    }

    if ($cols_to_update) {
        $sql = "UPDATE manual_registers SET " . implode(', ', $cols_to_update) . " WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $details = "Updated Register Entry: Title: [$title], ID: [$id]";
            if (!empty($changes)) {
                $details .= "\n" . implode("\n", $changes);
            }
            logActivity($conn, 'REGISTER_UPDATE', 'Registers', $details);
            $_SESSION['success_msg'] = "✅ Register Entry Updated Successfully!";
            header("Location: ?page=view-registers");
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Register
if ($page == 'edit-register-entry' && isset($_GET['delete_register_id'])) {
    $del_id = intval($_GET['delete_register_id']);

    // Fetch summary for log
    $curr_res = mysqli_query($conn, "SELECT * FROM manual_registers WHERE id = $del_id");
    $current = mysqli_fetch_assoc($curr_res);

    if (mysqli_query($conn, "DELETE FROM manual_registers WHERE id=$del_id")) {
        $summary = "ID: $del_id";
        if ($current) {
            $summary .= " | Details: ";
            $parts = [];
            if (!empty($current['vehicle_no']))
                $parts[] = "Vehicle: " . $current['vehicle_no'];
            if (!empty($current['party_name']))
                $parts[] = "Party: " . $current['party_name'];
            if (!empty($current['entry_date']))
                $parts[] = "Date: " . $current['entry_date'];
            $summary .= implode(", ", $parts);
        }

        logActivity($conn, 'REGISTER_DELETE', 'Registers', "Deleted Register Entry: $summary");
        $_SESSION['success_msg'] = "✅ Register Entry Deleted Successfully!";
        header("Location: ?page=view-registers");
        exit;
    }
}


// Handle inward entry
if ($page == 'inward' && isset($_POST['submit_inward'])) {

    $vehicle = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));
    $driver = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['driver_mobile']);
    $transporter = mysqli_real_escape_string($conn, $_POST['transporter_name']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose_name']);
    $bill_number = mysqli_real_escape_string($conn, $_POST['bill_number']);
    $from_loc = mysqli_real_escape_string($conn, $_POST['from_location']);
    $to_loc = mysqli_real_escape_string($conn, $_POST['to_location']);
    $comments = mysqli_real_escape_string($conn, $_POST['security_comments']);
    $qr_code_data = isset($_POST['qr_code_data']) ? $_POST['qr_code_data'] : null;
    $qr_raw_data = isset($_POST['qr_raw_data']) ? $_POST['qr_raw_data'] : null;

    // If raw data not provided, fallback to qr_code_data (might be same)
    if (empty($qr_raw_data))
        $qr_raw_data = $qr_code_data;

    $qr_code_data_escaped = $qr_code_data ? mysqli_real_escape_string($conn, $qr_code_data) : null;
    $qr_raw_data_escaped = $qr_raw_data ? mysqli_real_escape_string($conn, $qr_raw_data) : null;

    // Check if vehicle is already inside
    $check_inside = mysqli_query($conn, "SELECT id, entry_number, inward_datetime, driver_name FROM truck_inward WHERE vehicle_number = '$vehicle' AND status = 'inside' LIMIT 1");
    if (mysqli_num_rows($check_inside) > 0) {
        $existing_entry = mysqli_fetch_assoc($check_inside);
        $error_msg = "⚠️ Vehicle <strong>$vehicle</strong> is already inside! Entry #: <strong>{$existing_entry['entry_number']}</strong> (In: " . date('d/m/Y h:i A', strtotime($existing_entry['inward_datetime'])) . ", Driver: {$existing_entry['driver_name']}). Please complete the outward entry first.";
    } else {
        // Capture IDs if available
        $transporter_id = !empty($_POST['transporter_id']) ? intval($_POST['transporter_id']) : 'NULL';
        $purpose_id = !empty($_POST['purpose_id']) ? intval($_POST['purpose_id']) : 'NULL';

        // Generate entry number
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM truck_inward WHERE DATE(inward_date) = CURDATE()");
        $count = mysqli_fetch_assoc($count_result)['cnt'] + 1;
        $entry_num = 'IN' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);

        $datetime = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i:s');

        // Handle vehicle photo upload
        $vehicle_photo_url = null;
        if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['size'] > 0) {
            $upload_dir = 'uploads/inward/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $vehicle_photo_url = $upload_dir . 'vehicle_' . time() . '_' . basename($_FILES['vehicle_photo']['name']);
            move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $vehicle_photo_url);
        }

        // Handle bill photo upload
        $bill_photo_url = null;
        if (isset($_FILES['bill_photo']) && $_FILES['bill_photo']['size'] > 0) {
            $upload_dir = 'uploads/inward/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $bill_photo_url = $upload_dir . 'bill_' . time() . '_' . basename($_FILES['bill_photo']['name']);
            move_uploaded_file($_FILES['bill_photo']['tmp_name'], $bill_photo_url);
        }

        // Process items if any - must be valid JSON or empty array
        $items_json = '[]'; // Default to empty JSON array

        // First check if items were submitted directly
        if (!empty($_POST['items'])) {
            // Check if it's already a JSON string
            $items_post = $_POST['items'];
            if (is_string($items_post)) {
                $test_json = json_decode($items_post, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // It's already valid JSON
                    $items_json = $items_post;
                } else {
                    // Not JSON, wrap it
                    $items_json = json_encode($items_post);
                }
            } else {
                $items_json = json_encode($items_post);
            }
        }
        // If QR data exists, try to extract items from it
        elseif (!empty($qr_code_data)) {
            $qr_json = json_decode($qr_code_data, true);
            if ($qr_json && isset($qr_json['items']) && is_array($qr_json['items'])) {
                $items_json = json_encode($qr_json['items']);
            } elseif ($qr_json && isset($qr_json['decoded_items']) && is_array($qr_json['decoded_items'])) {
                $items_json = json_encode($qr_json['decoded_items']);
            } elseif ($qr_json && isset($qr_json['products']) && is_array($qr_json['products'])) {
                // Convert products to items format
                $items = array_map(function ($product) {
                    return [
                        'item_code' => $product['product_code'] ?? $product['sku'] ?? '',
                        'item_name' => $product['product_name'] ?? $product['name'] ?? '',
                        'quantity' => floatval($product['quantity'] ?? $product['qty'] ?? 0),
                        'unit' => $product['unit'] ?? 'PCS'
                    ];
                }, $qr_json['products']);
                $items_json = json_encode($items);
            } elseif ($qr_json && isset($qr_json['line_items']) && is_array($qr_json['line_items'])) {
                // Convert line_items to items format
                $items = array_map(function ($line) {
                    return [
                        'item_code' => $line['item_code'] ?? '',
                        'item_name' => $line['description'] ?? $line['item_name'] ?? '',
                        'quantity' => floatval($line['quantity'] ?? 0),
                        'unit' => $line['unit'] ?? 'PCS'
                    ];
                }, $qr_json['line_items']);
                $items_json = json_encode($items);
            }
        }

        // Escape for SQL
        $items_json_escaped = mysqli_real_escape_string($conn, $items_json);

        // Check if qr_code_data column exists, if not add it
        $check_qr_col = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'qr_code_data'");
        if (mysqli_num_rows($check_qr_col) == 0) {
            mysqli_query($conn, "ALTER TABLE truck_inward ADD COLUMN qr_code_data TEXT NULL AFTER items_json");
        }

        $sql = "INSERT INTO truck_inward (
            entry_number, vehicle_number, driver_name, driver_mobile, 
            transporter_id, transporter_name, purpose_id, purpose_name, 
            bill_number, from_location, to_location,
            security_comments, vehicle_photo_url, bill_photo_url, items_json, qr_code_data,
            inward_date, inward_time, inward_datetime, 
            inward_by, inward_by_name, status
        ) VALUES (
            '$entry_num', '$vehicle', '$driver', '$mobile', 
            $transporter_id, '$transporter', $purpose_id, '$purpose', 
            '$bill_number', '$from_loc', '$to_loc',
            '$comments', 
            " . ($vehicle_photo_url ? "'$vehicle_photo_url'" : "NULL") . ", 
            " . ($bill_photo_url ? "'$bill_photo_url'" : "NULL") . ", 
            '$items_json_escaped',
            " . ($qr_code_data_escaped ? "'$qr_code_data_escaped'" : "NULL") . ",
            '$date', '$time', '$datetime', 
            {$_SESSION['user_id']}, '{$_SESSION['full_name']}', 'inside'
        )";

        if (mysqli_query($conn, $sql)) {
            $inward_id = mysqli_insert_id($conn);
            $details = "Inward Gate Entry:\n" . auditFromPost($_POST, [], ['vehicle_number' => 'Vehicle', 'driver_name' => 'Driver', 'driver_mobile' => 'Mobile', 'transporter_name' => 'Transporter', 'purpose_name' => 'Purpose', 'from_location' => 'From', 'to_location' => 'To', 'bill_number' => 'Bill No', 'security_comments' => 'Comments']);
            $details .= "\nReported By: [" . ($_SESSION['full_name'] ?? 'System') . "]";
            
            // Add items to audit details
            if (!empty($items_json) && $items_json !== '[]') {
                $items_arr = json_decode($items_json, true);
                if (is_array($items_arr) && count($items_arr) > 0) {
                    $details .= "\nMaterial Items (" . count($items_arr) . "):";
                    foreach ($items_arr as $idx => $item) {
                        $name = $item['item_name'] ?? ($item['item_description'] ?? 'Item');
                        $qty = $item['quantity'] ?? 0;
                        $unit = $item['unit'] ?? '';
                        $details .= "\n  - " . $name . ": " . $qty . " " . $unit;
                    }
                }
            }

            if ($vehicle_photo_url)
                $details .= "\nVehicle Photo: [$vehicle_photo_url]";
            if ($bill_photo_url)
                $details .= "\nBill Photo: [$bill_photo_url]";
            logActivity($conn, 'INWARD_ENTRY', 'Logistics', $details);
            $success_msg = "✅ Inward entry created successfully! Entry #: $entry_num";

            // Simple QR Logging - Simplified to prevent any 500 errors
            if (!empty($qr_code_data)) {
                // Ensure table exists
                mysqli_query($conn, "CREATE TABLE IF NOT EXISTS qr_scan_logs (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            inward_id INT NOT NULL,
                            qr_raw_data TEXT,
                            qr_type VARCHAR(50) DEFAULT 'bill',
                    parsed_data JSON,
                            scan_status VARCHAR(50) DEFAULT 'success',
                            scanned_by INT,
                            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_inward_id (inward_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $q_raw = mysqli_real_escape_string($conn, $qr_raw_data);

                // FORCE VALID JSON for the parsed_data column to satisfy database constraints
                // This is crucial because your database has a JSON type constraint
                $json_check = json_decode($qr_code_data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Not valid JSON (likely plain text), wrap it in a JSON object
                    $q_parsed_final = json_encode(['raw_text' => $qr_code_data]);
                } else {
                    // Already valid JSON
                    $q_parsed_final = $qr_code_data;
                }

                $q_parsed = mysqli_real_escape_string($conn, $q_parsed_final);
                // Check for Employee Photo column                mysqli_query($conn, "ALTER TABLE employee_master ADD COLUMN IF NOT EXISTS photo VARCHAR(255) AFTER qr_code_data");
                $is_logged_in = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
                $u_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

                $log_sql = "INSERT INTO qr_scan_logs (inward_id, qr_raw_data, qr_type, parsed_data, scan_status, scanned_by) 
                           VALUES ($inward_id, '$q_raw', 'bill', '$q_parsed', 'success', $u_id)";

                // Use @ to suppress errors and prevent 500 status if insertion fails
                @mysqli_query($conn, $log_sql);
            }
            $_SESSION['success_msg'] = "✅ Inward entry created successfully! Entry #: $entry_num";
            header("Location: ?page=inward-details&id=$inward_id");
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error creating entry: " . mysqli_error($conn);
        }
    }
}

// Handle outward entry
if ($page == 'outward' && isset($_POST['submit_outward'])) {

    $inward_id = $_POST['inward_id'];
    $remarks = mysqli_real_escape_string($conn, $_POST['outward_remarks']);

    $datetime = date('Y-m-d H:i:s');
    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Get inward time to calculate duration
    $inward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT inward_datetime FROM truck_inward WHERE id = $inward_id"));
    $duration = (strtotime($datetime) - strtotime($inward['inward_datetime'])) / 3600;

    // Insert outward
    $sql = "INSERT INTO truck_outward (
        inward_id, outward_date, outward_time, outward_datetime, 
        outward_by, outward_by_name, outward_remarks, duration_hours
    ) VALUES (
        $inward_id, '$date', '$time', '$datetime',
        {$_SESSION['user_id']}, '{$_SESSION['full_name']}', '$remarks', $duration
    )";

    if (mysqli_query($conn, $sql)) {
        $outward_id = mysqli_insert_id($conn);
        // Get inward details for the audit log
        $v_res = mysqli_query($conn, "SELECT vehicle_number, driver_name, transporter_name FROM truck_inward WHERE id=$inward_id");
        $v_row = mysqli_fetch_assoc($v_res);
        $v_num = $v_row['vehicle_number'] ?? 'Unknown';
        $d_name = $v_row['driver_name'] ?? 'Unknown';
        $t_name = $v_row['transporter_name'] ?? 'Unknown';

        $log_details = "Outward Gate Exit:\n" .
            "Vehicle: [$v_num]\n" .
            "Driver: [$d_name]\n" .
            "Transporter: [$t_name]\n" .
            auditFromPost($_POST, [], [
                'outward_remarks' => 'Remarks',
                'outgoing_customer_name' => 'Customer',
                'outgoing_destination' => 'Destination',
                'number_of_seals' => 'Seals',
                'sealing_method' => 'Seal Method'
            ]);

        logActivity($conn, 'OUTWARD_EXIT', 'Logistics', $log_details);

        // ---------------- OUT-GOING CHECK (Moved from loading) ----------------
        $outgoing_reporting_datetime = mysqli_real_escape_string($conn, $_POST['outgoing_reporting_datetime'] ?? '');
        $outgoing_customer_id = !empty($_POST['outgoing_customer_id']) ? intval($_POST['outgoing_customer_id']) : 'NULL';
        $outgoing_customer_name = mysqli_real_escape_string($conn, $_POST['outgoing_customer_name'] ?? '');
        $outgoing_destination = mysqli_real_escape_string($conn, $_POST['outgoing_destination'] ?? '');

        $tarpaulin_condition_obs = mysqli_real_escape_string($conn, $_POST['tarpaulin_condition_obs'] ?? '');
        $tarpaulin_condition_action = mysqli_real_escape_string($conn, $_POST['tarpaulin_condition_action'] ?? '');
        $tarpaulin_condition_remarks = mysqli_real_escape_string($conn, $_POST['tarpaulin_condition_remarks'] ?? '');

        $wooden_blocks_used_obs = mysqli_real_escape_string($conn, $_POST['wooden_blocks_used_obs'] ?? '');
        $wooden_blocks_used_action = mysqli_real_escape_string($conn, $_POST['wooden_blocks_used_action'] ?? '');
        $wooden_blocks_used_remarks = mysqli_real_escape_string($conn, $_POST['wooden_blocks_used_remarks'] ?? '');

        $rope_tightening_obs = mysqli_real_escape_string($conn, $_POST['rope_tightening_obs'] ?? '');
        $rope_tightening_action = mysqli_real_escape_string($conn, $_POST['rope_tightening_action'] ?? '');
        $rope_tightening_remarks = mysqli_real_escape_string($conn, $_POST['rope_tightening_remarks'] ?? '');

        $number_of_seals = isset($_POST['number_of_seals']) && $_POST['number_of_seals'] !== '' ? intval($_POST['number_of_seals']) : 0;
        $sealing_method = mysqli_real_escape_string($conn, $_POST['sealing_method'] ?? '');
        $sealing_done_by = mysqli_real_escape_string($conn, $_POST['sealing_done_by'] ?? '');
        $sealing_obs = mysqli_real_escape_string($conn, $_POST['sealing_obs'] ?? '');
        $sealing_action = mysqli_real_escape_string($conn, $_POST['sealing_action'] ?? '');
        $sealing_remarks = mysqli_real_escape_string($conn, $_POST['sealing_remarks'] ?? '');

        $leaving_datetime = mysqli_real_escape_string($conn, $_POST['leaving_datetime'] ?? '');
        $naaviq_trip_started = mysqli_real_escape_string($conn, $_POST['naaviq_trip_started'] ?? '');
        $naaviq_trip_action = mysqli_real_escape_string($conn, $_POST['naaviq_trip_action'] ?? '');
        $naaviq_trip_remarks = mysqli_real_escape_string($conn, $_POST['naaviq_trip_remarks'] ?? '');

        $out_driver_signature = mysqli_real_escape_string($conn, $_POST['out_driver_signature'] ?? '');
        $out_transporter_signature = mysqli_real_escape_string($conn, $_POST['out_transporter_signature'] ?? '');
        $out_security_signature = mysqli_real_escape_string($conn, $_POST['out_security_signature'] ?? '');
        $out_logistic_signature = mysqli_real_escape_string($conn, $_POST['out_logistic_signature'] ?? '');

        // Outgoing documents check JSON
        $out_docs = [];
        $out_doc_types = [
            'ce_invoice' => 'CE Invoice',
            'way_bill' => 'Way Bill',
            'lr_copy' => 'LR Copy',
            'trem_card' => 'TREM Card'
        ];
        foreach ($out_doc_types as $key => $label) {
            if (isset($_POST["out_doc_{$key}_status"]) && $_POST["out_doc_{$key}_status"] !== '') {
                $out_docs[] = [
                    'type' => $key,
                    'label' => $label,
                    'status' => $_POST["out_doc_{$key}_status"],
                    'remarks' => mysqli_real_escape_string($conn, $_POST["out_doc_{$key}_remarks"] ?? '')
                ];
            }
        }
        $out_docs_json = json_encode($out_docs);

        // Find loading checklist ID if exists for this inward entry
        $loading_q = mysqli_query($conn, "SELECT id FROM vehicle_loading_checklist WHERE inward_id = $inward_id ORDER BY created_at DESC LIMIT 1");
        $loading_checklist_id = 'NULL';
        if ($loading_q && mysqli_num_rows($loading_q) > 0) {
            $loading_checklist_id = mysqli_fetch_assoc($loading_q)['id'];
        }

        $out_doc_date = date('Y-m-d');
        $out_sql = "INSERT INTO vehicle_outgoing_checklist (
            loading_checklist_id, inward_id, document_date, reporting_datetime,
            customer_id, customer_name, destination,
            tarpaulin_condition_obs, tarpaulin_condition_action, tarpaulin_condition_remarks,
            wooden_blocks_used_obs, wooden_blocks_used_action, wooden_blocks_used_remarks,
            rope_tightening_obs, rope_tightening_action, rope_tightening_remarks,
            number_of_seals, sealing_method, sealing_done_by,
            sealing_obs, sealing_action, sealing_remarks,
            documents_json, leaving_datetime,
            naaviq_trip_started, naaviq_trip_action, naaviq_trip_remarks,
            driver_signature, transporter_signature, security_signature, logistic_signature,
            status
        ) VALUES (
            $loading_checklist_id, $inward_id, '$out_doc_date', " . ($outgoing_reporting_datetime ? "'$outgoing_reporting_datetime'" : "NULL") . ",
            " . ($outgoing_customer_id != 'NULL' ? $outgoing_customer_id : 'NULL') . ", '$outgoing_customer_name', '$outgoing_destination',
            '$tarpaulin_condition_obs', '$tarpaulin_condition_action', '$tarpaulin_condition_remarks',
            '$wooden_blocks_used_obs', '$wooden_blocks_used_action', '$wooden_blocks_used_remarks',
            '$rope_tightening_obs', '$rope_tightening_action', '$rope_tightening_remarks',
            $number_of_seals, '$sealing_method', '$sealing_done_by',
            '$sealing_obs', '$sealing_action', '$sealing_remarks',
            '$out_docs_json', " . ($leaving_datetime ? "'$leaving_datetime'" : "NULL") . ",
            '$naaviq_trip_started', '$naaviq_trip_action', '$naaviq_trip_remarks',
            '$out_driver_signature', '$out_transporter_signature', '$out_security_signature', '$out_logistic_signature',
            'completed'
        )";
        mysqli_query($conn, $out_sql);

        // Update inward status
        mysqli_query($conn, "UPDATE truck_inward SET status = 'exited', is_exited = 1 WHERE id = $inward_id");

        $_SESSION['success_msg'] = "✅ Outward entry completed! Duration: " . formatDuration($duration);
        header("Location: ?page=outward-details&id=$inward_id");
        exit;
    } else {
        $_SESSION['error_msg'] = "❌ Error recording outward entry: " . mysqli_error($conn);
    }
}

// Handle edit inward entry (Admin only)
if ($page == 'edit-inward' && isset($_POST['update_inward'])) {

    // Check if user is admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        echo "<div class='container'><div class='alert alert-error'>Access denied! Admin privileges required.</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $id = intval($_POST['id']);
    $vehicle = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));
    $driver = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['driver_mobile']);
    $transporter = mysqli_real_escape_string($conn, $_POST['transporter_name']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose_name']);
    $bill_number = mysqli_real_escape_string($conn, $_POST['bill_number']);
    $from_loc = mysqli_real_escape_string($conn, $_POST['from_location']);
    $to_loc = mysqli_real_escape_string($conn, $_POST['to_location']);
    $comments = mysqli_real_escape_string($conn, $_POST['security_comments']);
    $inward_datetime = mysqli_real_escape_string($conn, $_POST['inward_datetime']);
    $qr_code_data = isset($_POST['qr_code_data']) ? mysqli_real_escape_string($conn, $_POST['qr_code_data']) : null;

    // Capture IDs if available
    $transporter_id = !empty($_POST['transporter_id']) ? intval($_POST['transporter_id']) : 'NULL';
    $purpose_id = !empty($_POST['purpose_id']) ? intval($_POST['purpose_id']) : 'NULL';

    // Parse datetime
    $datetime_parts = explode(' ', $inward_datetime);
    $date = $datetime_parts[0];
    $time = isset($datetime_parts[1]) ? $datetime_parts[1] : date('H:i:s');

    // Handle vehicle photo upload
    $vehicle_photo_update = '';
    if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['size'] > 0) {
        $upload_dir = 'uploads/inward/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $vehicle_photo_url = $upload_dir . 'vehicle_' . time() . '_' . basename($_FILES['vehicle_photo']['name']);
        move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $vehicle_photo_url);
        $vehicle_photo_update = ", vehicle_photo_url = '$vehicle_photo_url'";
    }

    // Handle bill photo upload
    $bill_photo_update = '';
    if (isset($_FILES['bill_photo']) && $_FILES['bill_photo']['size'] > 0) {
        $upload_dir = 'uploads/inward/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $bill_photo_url = $upload_dir . 'bill_' . time() . '_' . basename($_FILES['bill_photo']['name']);
        move_uploaded_file($_FILES['bill_photo']['tmp_name'], $bill_photo_url);
        $bill_photo_update = ", bill_photo_url = '$bill_photo_url'";
    }

    // Process items if any
    $items_json = '[]';
    if (!empty($_POST['items'])) {
        $items_post = $_POST['items'];
        if (is_string($items_post)) {
            $test_json = json_decode($items_post, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $items_json = $items_post;
            } else {
                $items_json = json_encode($items_post);
            }
        } else {
            $items_json = json_encode($items_post);
        }
    } elseif (!empty($qr_code_data)) {
        $qr_json = json_decode($qr_code_data, true);
        if ($qr_json && isset($qr_json['items']) && is_array($qr_json['items'])) {
            $items_json = json_encode($qr_json['items']);
        } elseif ($qr_json && isset($qr_json['decoded_items']) && is_array($qr_json['decoded_items'])) {
            $items_json = json_encode($qr_json['decoded_items']);
        }
    }
    $items_json_escaped = mysqli_real_escape_string($conn, $items_json);

    $sql = "UPDATE truck_inward SET 
        vehicle_number = '$vehicle',
        driver_name = '$driver',
        driver_mobile = '$mobile',
        transporter_id = $transporter_id,
        transporter_name = '$transporter',
        purpose_id = $purpose_id,
        purpose_name = '$purpose',
        bill_number = '$bill_number',
        from_location = '$from_loc',
        to_location = '$to_loc',
        security_comments = '$comments',
        inward_date = '$date',
        inward_time = '$time',
        inward_datetime = '$inward_datetime',
        items_json = '$items_json_escaped',
        qr_code_data = " . ($qr_code_data ? "'$qr_code_data'" : "qr_code_data") . "
        $vehicle_photo_update
        $bill_photo_update
        WHERE id = $id";

    // Fetch old record BEFORE update for diff logging
    $old_res = mysqli_query($conn, "SELECT * FROM truck_inward WHERE id=$id");
    $old = mysqli_fetch_assoc($old_res);

    if (mysqli_query($conn, $sql)) {
        $inward_log = "Edited Inward Entry: ID: [$id]\nVehicle: [$vehicle]\nDriver: [$driver]\nLocation: [$from_loc ➔ $to_loc]";
        $inward_log .= "\nUpdated By: [" . ($_SESSION['full_name'] ?? 'System') . "]";
        if ($old) {
            $diff = auditDiff($old, $_POST, [], ['vehicle_number' => 'Vehicle', 'driver_name' => 'Driver', 'driver_mobile' => 'Mobile', 'transporter_name' => 'Transporter', 'purpose_name' => 'Purpose', 'from_location' => 'From', 'to_location' => 'To', 'bill_number' => 'Bill No', 'security_comments' => 'Comments', 'inward_datetime' => 'Datetime']);
            if ($diff)
                $inward_log .= "\nChanges:\n" . $diff;

            // Add items summary to audit
            if (!empty($items_json) && $items_json !== '[]') {
                $items_arr = json_decode($items_json, true);
                if (is_array($items_arr) && count($items_arr) > 0) {
                    $inward_log .= "\nMaterial Items (" . count($items_arr) . "):";
                    foreach ($items_arr as $idx => $item) {
                        $name = $item['item_name'] ?? ($item['item_description'] ?? 'Item');
                        $qty = $item['quantity'] ?? 0;
                        $unit = $item['unit'] ?? '';
                        $inward_log .= "\n  - " . $name . ": " . $qty . " " . $unit;
                    }
                }
            }

            if ($vehicle_photo_update)
                $inward_log .= "\nVehicle Photo: [Updated]";
            if ($bill_photo_update)
                $inward_log .= "\nBill Photo: [Updated]";
        }
        logActivity($conn, 'INWARD_EDIT', 'Logistics', $inward_log);
        $_SESSION['success_msg'] = "✅ Inward entry updated successfully!";
        header("Location: ?page=inward-details&id=$id&success=1&t=" . time());
        exit;
    } else {
        $_SESSION['error_msg'] = "❌ Error updating entry: " . mysqli_error($conn);
        header("Location: ?page=edit-inward&id=$id");
        exit;
    }
}

// Handle edit outward entry (Admin only)
if ($page == 'edit-outward' && isset($_POST['update_outward'])) {

    // Check if user is admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        echo "<div class='container'><div class='alert alert-error'>Access denied! Admin privileges required.</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $id = intval($_POST['id']);
    $inward_id = intval($_POST['inward_id']);
    $remarks = mysqli_real_escape_string($conn, $_POST['outward_remarks']);
    $outward_datetime = mysqli_real_escape_string($conn, $_POST['outward_datetime']);

    // Parse datetime
    $datetime_parts = explode(' ', $outward_datetime);
    $date = $datetime_parts[0];
    $time = isset($datetime_parts[1]) ? $datetime_parts[1] : date('H:i:s');

    // Get inward time to calculate duration
    $inward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT inward_datetime FROM truck_inward WHERE id = $inward_id"));
    $duration = (strtotime($outward_datetime) - strtotime($inward['inward_datetime'])) / 3600;

    $sql = "UPDATE truck_outward SET 
        outward_date = '$date',
        outward_time = '$time',
        outward_datetime = '$outward_datetime',
        outward_remarks = '$remarks',
        duration_hours = $duration
        WHERE id = $id";

    // Fetch old record BEFORE update for diff logging
    $v_res = mysqli_query($conn, "SELECT vehicle_number FROM truck_inward WHERE id=$inward_id");
    $v_num = ($v_row = mysqli_fetch_assoc($v_res)) ? $v_row['vehicle_number'] : 'N/A';
    $old_out_res = mysqli_query($conn, "SELECT * FROM truck_outward WHERE id=$id");
    $old_out = mysqli_fetch_assoc($old_out_res);

    if (mysqli_query($conn, $sql)) {
        $out_log = "Edited Outward Entry: ID: [$id], Vehicle: [$v_num]";
        if ($old_out) {
            $diff = auditDiff($old_out, $_POST, [], ['outward_datetime' => 'Datetime', 'outward_remarks' => 'Remarks']);
            if ($diff)
                $out_log .= "\nChanges:\n" . $diff;
        }
        logActivity($conn, 'OUTWARD_EDIT', 'Logistics', $out_log);
        $_SESSION['success_msg'] = "✅ Outward entry updated successfully!";
        header("Location: ?page=outward-details&id=$inward_id&success=1&t=" . time());
        exit;
    } else {
        $_SESSION['error_msg'] = "❌ Error updating outward entry: " . mysqli_error($conn);
        header("Location: ?page=edit-outward&id=$id");
        exit;
    }
}

// Handle employee entry/exit
if ($page == 'employee-entry-action') {

    $is_ajax = isset($_POST['ajax']);
    $response = ['success' => false, 'message' => 'Invalid request'];

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $user_id = $_SESSION['user_id'];

        if ($action == 'inward') {
            $vehicle = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));
            $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
            $employee_name = mysqli_real_escape_string($conn, $_POST['employee_name']);
            $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

            // Check if employee is already inside
            $check_inside = mysqli_query($conn, "SELECT id FROM employee_entries WHERE (employee_id = '$employee_id' OR vehicle_number = '$vehicle') AND status = 'inside' LIMIT 1");
            if (mysqli_num_rows($check_inside) > 0) {
                $response['message'] = "⚠️ Employee or Vehicle is already inside!";
                $_SESSION['error_msg'] = $response['message'];
            } else {
                $now = date('Y-m-d H:i:s');
                $sql = "INSERT INTO employee_entries (employee_id, employee_name, vehicle_number, inward_datetime, inward_by, status, remarks) 
                        VALUES ('$employee_id', '$employee_name', '$vehicle', '$now', $user_id, 'inside', '$remarks')";
                if (mysqli_query($conn, $sql)) {
                    $response['success'] = true;
                    $response['message'] = "✅ Employee inward logged successfully!";
                    $_SESSION['success_msg'] = $response['message'];

                    $details = "Employee Inward Action:\nAction: [INWARD]\nName: [$employee_name]\nID: [$employee_id]\nVehicle: [$vehicle]";
                    if (!empty($remarks))
                        $details .= "\nRemarks: [$remarks]";
                    logActivity($conn, 'EMP_INWARD', 'Employee', $details);
                } else {
                    $response['message'] = "❌ Error logging employee entry: " . mysqli_error($conn);
                    $_SESSION['error_msg'] = $response['message'];
                }
            }
        } elseif ($action == 'exit') {
            $id = intval($_POST['entry_id']);
            $now = date('Y-m-d H:i:s');
            $sql = "UPDATE employee_entries SET outward_datetime = '$now', outward_by = $user_id, status = 'exited' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $response['success'] = true;
                $response['message'] = "✅ Employee exit logged successfully!";
                $_SESSION['success_msg'] = $response['message'];

                // Fetch details for better log
                $e_det = mysqli_query($conn, "SELECT employee_name, employee_id, vehicle_number FROM employee_entries WHERE id = $id");
                $e_row = mysqli_fetch_assoc($e_det);
                $e_name = $e_row['employee_name'] ?? 'Unknown';
                $e_id = $e_row['employee_id'] ?? 'N/A';
                $v_num = $e_row['vehicle_number'] ?? 'N/A';

                $details = "Employee Outward Action:\nAction: [OUTWARD]\nName: [$e_name]\nID: [$e_id]\nVehicle: [$v_num]";
                logActivity($conn, 'EMP_OUTWARD', 'Employee', $details);
            } else {
                $response['message'] = "❌ Error logging employee exit: " . mysqli_error($conn);
                $_SESSION['error_msg'] = $response['message'];
            }
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        while (ob_get_level())
            ob_end_clean(); // CLEAR ANY PREVIOUS OUTPUT/WHITESPACE
        echo json_encode($response);
        exit;
    }

    // Redirection removed as per user request (handled by AJAX or frontend)
    exit;
}

// Handle Password Change Action
if ($page == 'change_password_action') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    $res = mysqli_query($conn, "SELECT password FROM user_master WHERE id = $user_id");
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($current, $user['password'])) {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        if (mysqli_query($conn, "UPDATE user_master SET password = '$new_hash' WHERE id = $user_id")) {
            logActivity($conn, 'PASSWORD_CHANGE', 'User', "User changed their own password.");
            echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
    }
    exit;
}

// AJAX: Get employee details by vehicle number & check current status
if ($page == 'get-employee-details') {
    $vehicle = strtoupper(mysqli_real_escape_string($conn, $_GET['vehicle']));

    // 1. Get from master
    $query = "SELECT * FROM employee_master WHERE (vehicle_number = '$vehicle' OR employee_id = '$vehicle') AND is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        $emp_id = $data['employee_id'];

        // 2. Check if currently inside
        $status_q = mysqli_query($conn, "SELECT id FROM employee_entries WHERE (employee_id = '$emp_id' OR vehicle_number = '$vehicle') AND status = 'inside' ORDER BY inward_datetime DESC LIMIT 1");
        $status_row = mysqli_fetch_assoc($status_q);

        $data['is_inside'] = (bool) $status_row;
        $data['current_entry_id'] = $status_row['id'] ?? null;

        while (ob_get_level())
            ob_end_clean();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        while (ob_get_level())
            ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'No employee found in master for this ID/Vehicle.']);
    }
    exit;
}

// AJAX: Get complete employee entry data by ID
if ($page == 'get-employee-entry') {

    $id = intval($_GET['id']);
    $query = "SELECT e.*, em.department, u.username as inward_by_name, u2.username as outward_by_name
              FROM employee_entries e
              LEFT JOIN employee_master em ON e.employee_id = em.employee_id
              LEFT JOIN user_master u ON e.inward_by = u.id
              LEFT JOIN user_master u2 ON e.outward_by = u2.id
              WHERE e.id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        while (ob_get_level())
            ob_end_clean();
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        while (ob_get_level())
            ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Employee entry not found.']);
    }
    exit;
}

// AJAX: Get refreshed Inside list HTML
if ($page == 'get-emp-inside-list') {
    $inside_emps = mysqli_query($conn, "SELECT * FROM employee_entries WHERE status = 'inside' ORDER BY inward_datetime DESC");
    if (mysqli_num_rows($inside_emps) > 0) {
        while ($emp = mysqli_fetch_assoc($inside_emps)) {
            $e_name = htmlspecialchars($emp['employee_name']);
            $e_id = htmlspecialchars($emp['employee_id']);
            $v_no = htmlspecialchars($emp['vehicle_number']);
            $in_time = date('h:i A', strtotime($emp['inward_datetime']));
            $id = $emp['id'];

            echo "<tr class='emp-row' data-emp-id='$e_id' data-vehicle='$v_no' style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 10px;'>
                        <strong>$e_name</strong><br>
                        <span style='font-size: 11px; color: #64748b;'>$e_id</span>
                    </td>
                    <td style='padding: 10px;'>$v_no</td>
                    <td style='padding: 10px;'>$in_time</td>
                    <td style='padding: 10px;'>
                        <form action='?page=employee-entry-action' method='POST' style='margin: 0;' onsubmit=\"handleEmployeeExitForm(this, '" . addslashes($e_name) . "'); return false;\">
                            <input type='hidden' name='action' value='exit'>
                            <input type='hidden' name='entry_id' value='$id'>
                            <button type='submit' class='btn-emp-exit' style='background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;'>Exit</button>
                        </form>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #64748b;'>No employees inside</td></tr>";
    }
    exit;
}

// Handle QR scan processing
if ($page == 'qr-scanner' && isset($_POST['process_qr'])) {

    $qr_data = $_POST['qr_data'];

    // Try to parse QR data (JSON, delimited, URL)
    $parsed = null;
    $items = [];

    // Try JSON
    $json = json_decode($qr_data, true);
    if ($json) {
        $parsed = $json;
        if (isset($json['items'])) {
            $items = $json['items'];
        }
    } else {
        // Try delimited (pipe, comma)
        if (strpos($qr_data, '|') !== false) {
            $parts = explode('|', $qr_data);
            foreach ($parts as $part) {
                if (strpos($part, ':') !== false) {
                    list($key, $value) = explode(':', $part, 2);
                    $parsed[strtolower(trim($key))] = trim($value);
                }
            }
        }
    }

    $qr_result = [
        'raw' => $qr_data,
        'parsed' => $parsed,
        'items' => $items
    ];
}

// AJAX: Check for duplicate vehicle number (Direct UI validation)
if ($page == 'check-duplicate-vehicle') {
    $v = isset($_GET['vehicle']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_GET['vehicle']))) : '';
    $id = intval($_GET['id'] ?? 0);

    if (!empty($v)) {
        $res = mysqli_query($conn, "SELECT employee_name FROM employee_master WHERE UPPER(TRIM(vehicle_number)) = UPPER('$v') AND id != $id");
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            echo json_encode(['exists' => true, 'name' => $row['employee_name']]);
        } else {
            echo json_encode(['exists' => false]);
        }
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}
// AJAX: Check for duplicate employee ID
if ($page == 'check-duplicate-employee-id') {
    $emp_id = isset($_GET['emp_id']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_GET['emp_id']))) : '';
    $id = intval($_GET['id'] ?? 0);

    if (!empty($emp_id)) {
        $res = mysqli_query($conn, "SELECT employee_name FROM employee_master WHERE UPPER(TRIM(employee_id)) = UPPER('$emp_id') AND id != $id");
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            echo json_encode(['exists' => true, 'name' => $row['employee_name']]);
        } else {
            echo json_encode(['exists' => false]);
        }
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}
?>