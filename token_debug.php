<?php
require_once 'config.php';
session_start();

$conn = getDatabaseConnection();

echo "<h2>Token Debug</h2>";
echo "<p>Cookie: " . (isset($_COOKIE['GATEPILOT_REMEMBER']) ? htmlspecialchars($_COOKIE['GATEPILOT_REMEMBER']) : 'NOT SET') . "</p>";

if (isset($_COOKIE['GATEPILOT_REMEMBER'])) {
    $token = mysqli_real_escape_string($conn, $_COOKIE['GATEPILOT_REMEMBER']);
    
    // Check in user_sessions
    $r1 = mysqli_query($conn, "SELECT * FROM user_sessions WHERE token = '$token'");
    echo "<p>Sessions rows for this token: " . mysqli_num_rows($r1) . "</p>";
    if ($row = mysqli_fetch_assoc($r1)) {
        echo "<pre>" . print_r($row, true) . "</pre>";
        
        // Check user_master
        $uid = $row['user_id'];
        $r2 = mysqli_query($conn, "SELECT id, username, is_active FROM user_master WHERE id = $uid");
        if ($u = mysqli_fetch_assoc($r2)) {
            echo "<p>User: " . $u['username'] . " | Active: " . $u['is_active'] . "</p>";
        }
    }
    
    // Full join query (same as auto-login)
    $r3 = mysqli_query($conn, "SELECT u.id, u.username FROM user_sessions s JOIN user_master u ON s.user_id = u.id WHERE s.token = '$token' AND u.is_active = 1 LIMIT 1");
    echo "<p>Full Join Result: " . mysqli_num_rows($r3) . " row(s)</p>";
    if ($row3 = mysqli_fetch_assoc($r3)) {
        echo "<p>Would auto-login as: " . $row3['username'] . " (ID: " . $row3['id'] . ")</p>";
    }
}

echo "<p>DB Error (if any): " . mysqli_error($conn) . "</p>";
?>
