<?php
// force_sync.php - UPLOAD THIS TO HOSTINGER VIA FILE MANAGER OR PULL FROM GITHUB
header('Content-Type: text/plain; charset=utf-8');
echo "--- GATEPILOT FORCE SYNC SYSTEM ---\n";
echo "Starting Force Sync at: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Fetch all updates from GitHub
echo "[1] Fetching all updates from GitHub...\n";
$fetch = shell_exec("git fetch --all 2>&1");
echo "Fetch Result:\n" . ($fetch ?: "No output.") . "\n\n";

// 2. Force reset to match origin/main exactly
echo "[2] Hard resetting to origin/main...\n";
$reset = shell_exec("git reset --hard origin/main 2>&1");
echo "Reset Result:\n" . ($reset ?: "No output.") . "\n\n";

// 3. Status Check
echo "[3] Current Git Status:\n";
$status = shell_exec("git status 2>&1");
echo $status ?: "Unable to get status.";

echo "\n\n--- SYNC PROCESS COMPLETE ---";
if (function_exists('opcache_reset')) @opcache_reset();
?>
