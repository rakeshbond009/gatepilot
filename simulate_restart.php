<?php
// Simulate what happens when a fresh browser visits root URL with GATEPILOT_REMEMBER cookie
// This destroys the current PHP session to mimic a browser restart

session_start();
session_destroy(); // Kill current session
session_write_close();
setcookie(session_name(), '', time() - 3600, '/'); // Remove PHPSESSID

// Now redirect to root URL - PHP will start fresh with only the GATEPILOT_REMEMBER cookie
header('Location: https://gatemanagement.codepilotx.com/');
exit;
?>
