<?php
require_once 'includes/init.php';

echo "<h1>Login Persistence Debug</h1>";

// 1. Check if user_sessions table exists
$res = mysqli_query($conn, "SHOW TABLES LIKE 'user_sessions'");
if (mysqli_num_rows($res) > 0) {
    echo "<p style='color:green;'>✅ Table 'user_sessions' exists.</p>";
    
    // Check column structure
    $res = mysqli_query($conn, "DESCRIBE user_sessions");
    echo "<h3>Table Structure:</h3><ul>";
    while($row = mysqli_fetch_assoc($res)) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ Table 'user_sessions' DOES NOT EXIST.</p>";
}

// 2. Check for the persistent cookie
if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
    $token = $_COOKIE['GATEPILOT_REMEMBER'];
    echo "<p style='color:green;'>✅ Cookie 'GATEPILOT_REMEMBER' is SET.</p>";
    echo "<p>Token Length: " . strlen($token) . "</p>";
    
    // Check if token exists in DB
    $token_esc = mysqli_real_escape_string($conn, $token);
    $res = mysqli_query($conn, "SELECT * FROM user_sessions WHERE token = '$token_esc'");
    if (mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        echo "<p style='color:green;'>✅ Token found in Database for User ID: " . $data['user_id'] . "</p>";
    } else {
        echo "<p style='color:red;'>❌ Token NOT FOUND in Database.</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠️ Cookie 'GATEPILOT_REMEMBER' is NOT SET.</p>";
}

// 3. Check Session Status
echo "<p>Session Status: " . (isLoggedIn() ? "Logged In as " . $_SESSION['username'] : "Not Logged In") . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>HTTPS Setting: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : "N/A") . "</p>";
echo "<p>X-Forwarded-Proto: " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : "N/A") . "</p>";
?>
