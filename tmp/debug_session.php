<?php
// STANDALONE debug - no app routing needed
// Access: https://yourdomain.com/Truckmovement/tmp/debug_session.php
define('APP_VERSION', 'debug');
require_once dirname(__DIR__) . '/config.php';
session_start();

// Output raw text - bypass any output buffering
while (ob_get_level()) ob_end_clean();
header('Content-Type: text/plain; charset=utf-8');

echo "=== SESSION ===\n";
echo "ID:           " . session_id() . "\n";
echo "Save Path:    " . session_save_path() . "\n";
echo "Writable:     " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "\n";
echo "Data:         " . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n\n";

echo "=== COOKIES ===\n";
echo "PHPSESSID:           " . ($_COOKIE[session_name()] ?? '(NOT SET)') . "\n";
echo "GATEPILOT_REMEMBER:  " . ($_COOKIE['GATEPILOT_REMEMBER'] ?? '(NOT SET)') . "\n\n";

echo "=== COOKIE CONFIG ===\n";
global $_cookie_domain, $_is_https;
echo "_cookie_domain:  '" . ($_cookie_domain ?? '(not defined)') . "'\n";
echo "_is_https:       " . (($_is_https ?? false) ? 'true' : 'false') . "\n";
echo "HTTP_HOST:       " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "HTTPS env var:   " . ($_SERVER['HTTPS'] ?? '(not set)') . "\n\n";

echo "=== PHP INI ===\n";
echo "session.gc_maxlifetime:  " . ini_get('session.gc_maxlifetime') . "s (" . round(ini_get('session.gc_maxlifetime')/86400, 1) . " days)\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "s\n";
echo "session.cookie_domain:   '" . ini_get('session.cookie_domain') . "'\n";
echo "session.cookie_secure:   " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n\n";

echo "=== DB TOKEN CHECK ===\n";
$conn = getDatabaseConnection();
if (!empty($_COOKIE['GATEPILOT_REMEMBER'])) {
    $tok = mysqli_real_escape_string($conn, $_COOKIE['GATEPILOT_REMEMBER']);
    $r = mysqli_query($conn, "SELECT s.id, s.created_at, s.last_used_at, u.username, u.is_active 
                               FROM user_sessions s JOIN user_master u ON s.user_id=u.id 
                               WHERE s.token='$tok' LIMIT 1");
    if ($r && $row = mysqli_fetch_assoc($r)) {
        echo "Token: FOUND - user={$row['username']}, active={$row['is_active']}, last_used={$row['last_used_at']}\n";
    } else {
        echo "Token: NOT FOUND in DB - error: " . mysqli_error($conn) . "\n";
    }
    $cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM user_sessions"));
    echo "Total sessions in DB: " . ($cnt['c'] ?? 'N/A') . "\n";
} else {
    echo "No GATEPILOT_REMEMBER cookie - persistent login won't work.\n";
}

echo "\n=== ENVIRONMENT: " . ENVIRONMENT . " ===\n";
