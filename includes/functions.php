<?php
/**
 * Global functions for Gatepilot
 */

// Format duration in hours and minutes
function formatDuration($hours)
{
    $total_minutes = round($hours * 60);
    $display_hours = floor($total_minutes / 60);
    $display_minutes = $total_minutes % 60;

    if ($display_hours > 0) {
        return $display_hours . ' hr' . ($display_hours > 1 ? 's' : '') . ' ' . $display_minutes . ' min (' . number_format($hours, 1) . ' hrs)';
    }
    else {
        return $display_minutes . ' min (' . number_format($hours, 1) . ' hrs)';
    }
}

// Get setting value
function getSetting($conn, $key, $default = null)
{
    $key = mysqli_real_escape_string($conn, $key);
    $result = mysqli_query($conn, "SELECT setting_value FROM app_settings WHERE setting_key = '$key'");
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['setting_value'];
    }
    return $default;
}

// Set setting value
function setSetting($conn, $key, $value)
{
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);
    $result = mysqli_query($conn, "INSERT INTO app_settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value = '$value'");
    return $result;
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

// Check User Permission
function hasPermission($key)
{
    global $conn;
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1) {
        return true;
    }

    // Always fetch fresh permissions to ensure updates apply immediately
    if (true) {
        $uid = $_SESSION['user_id'];
        $res = mysqli_query($conn, "SELECT permissions FROM user_master WHERE id = $uid");
        if ($res && $r = mysqli_fetch_assoc($res)) {
            $_SESSION['permissions'] = $r['permissions'];
        }
        else {
            $_SESSION['permissions'] = '{}';
        }
    }

    $perms = json_decode($_SESSION['permissions'] ?? '{}', true);

    // Retry decoding if failed (maybe double escaped)
    if (json_last_error() !== JSON_ERROR_NONE) {
        $raw = stripslashes($_SESSION['permissions'] ?? '{}');
        $perms = json_decode($raw, true);
    }

    if (!is_array($perms))
        $perms = [];

    // Flatten keys for easier access (e.g. 'pages.dashboard')
    $parts = explode('.', $key);
    $category = $parts[0];
    $action = $parts[1] ?? null;

    if ($category && $action) {
        return isset($perms[$category][$action]) ? $perms[$category][$action] : false;
    }
    return false;
}

// Custom Function: Show App Modal
function showAppModal($title, $message, $type = 'success')
{
    $color = ($type == 'success') ? '#10b981' : '#ef4444';
    $icon = ($type == 'success') ? '✅' : '❌';
    echo "
<div id='app_modal_overlay' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999; animation: fadeIn 0.3s;'>
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
<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
";
}
