<?php
// Quick diagnostic - access as /Truckmovement/tmp/debug_session.php
require_once dirname(__DIR__) . '/config.php';
session_start();

header('Content-Type: text/plain');

echo "=== SESSION DEBUG ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Save Path Writable: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "\n";
echo "Session Data: " . json_encode($_SESSION) . "\n";
echo "\n";
echo "=== COOKIE DEBUG ===\n";
echo "All Cookies: " . json_encode($_COOKIE) . "\n";
echo "GATEPILOT_REMEMBER: " . ($_COOKIE['GATEPILOT_REMEMBER'] ?? '(not set)') . "\n";
echo "PHPSESSID: " . ($_COOKIE[session_name()] ?? '(not set)') . "\n";
echo "\n";
echo "=== CONFIG DEBUG ===\n";
global $_cookie_domain, $_is_https;
echo "_cookie_domain: " . ($_cookie_domain ?? '(not defined)') . "\n";
echo "_is_https: " . ($_is_https ? 'true' : 'false') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? '(not set)') . "\n";
echo "ENVIRONMENT: " . ENVIRONMENT . "\n";
echo "\n";
echo "=== PHP SESSION CONFIG ===\n";
echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "\n";
// Test DB connection & token table
$conn = getDatabaseConnection();
echo "=== DB TOKEN CHECK ===\n";
if (!empty($_COOKIE['GATEPILOT_REMEMBER'])) {
    $token = mysqli_real_escape_string($conn, $_COOKIE['GATEPILOT_REMEMBER']);
    $r = mysqli_query($conn, "SELECT s.id, s.user_id, s.last_used_at, u.username, u.is_active 
                               FROM user_sessions s JOIN user_master u ON s.user_id=u.id 
                               WHERE s.token='$token' LIMIT 1");
    if ($r && $row = mysqli_fetch_assoc($r)) {
        echo "Token FOUND: user=" . $row['username'] . ", is_active=" . $row['is_active'] . ", last_used=" . $row['last_used_at'] . "\n";
    } else {
        echo "Token NOT FOUND in DB (or DB error: " . mysqli_error($conn) . ")\n";
    }
    // Count total sessions
    $cnt = mysqli_query($conn, "SELECT COUNT(*) AS c FROM user_sessions");
    echo "Total sessions in DB: " . (mysqli_fetch_assoc($cnt)['c'] ?? 'N/A') . "\n";
} else {
    echo "No GATEPILOT_REMEMBER cookie present.\n";
}
