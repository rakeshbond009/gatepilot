<?php
require_once 'includes/init.php';

// Force create a token and set cookie for user ID 1 (Assuming admin is 1)
$token = bin2hex(random_bytes(32));
$user_agent = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
$user_id = 1; // Assuming admin exists at ID 1

$sql = "INSERT INTO user_sessions (user_id, token, user_agent) VALUES ($user_id, '$token', '$user_agent')";
if (mysqli_query($conn, $sql)) {
    // Set cookie using the EXACT SAME code as in init.php
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $cookie_params = [
        'expires' => time() + (365 * 24 * 60 * 60),
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    setcookie('GATEPILOT_REMEMBER', $token, $cookie_params);
    echo "<h1>Token Forced!</h1><p>Token: $token</p>";
    echo "<p>Please visit verify_persistence.php now.</p>";
} else {
    echo "ERROR: " . mysqli_error($conn);
}
?>
