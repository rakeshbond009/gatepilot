<?php
/**
 * Logout Page
 * Completely clears all session and persistent cookie data.
 */

// 1. Clear session variables
$_SESSION = array();

// 2. Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Clear persistent login cookie
if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
    setcookie('GATEPILOT_REMEMBER', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    unset($_COOKIE['GATEPILOT_REMEMBER']);
}

// 4. Destroy the session
@session_destroy();

// 5. Redirect to login
header("Location: ?page=login&logout=1");
exit;
?>
