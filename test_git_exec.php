<?php
echo "CHECKING SHELL_EXEC:\n";
$output = shell_exec("git --version 2>&1");
echo "Git Version: " . ($output ?: "FAILED TO EXECUTE") . "\n";
?>
