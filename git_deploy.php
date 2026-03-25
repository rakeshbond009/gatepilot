<?php
/**
 * GATEPILOT - DEPLOYMENT DIAGNOSTICS & MANUAL TRIGGER
 * Upload this file to your Hostinger public_html to debug and manually trigger git pulls.
 * 
 * Access: yourdomain.com/git_deploy.php
 */

header('Content-Type: text/plain; charset=utf-8');
echo "--- GATEPILOT DEPLOYMENT LOG ---\n";
echo "Date/Time (IST): " . date('Y-m-d H:i:s') . "\n";
echo "Server User: " . exec('whoami') . "\n";
echo "Current Path: " . __DIR__ . "\n";
echo "---------------------------------\n\n";

// 1. Check if Git is installed
echo "[1] DETECTING GIT SYSTEM...\n";
$git_version = shell_exec("git --version 2>&1");
echo "System Git: " . ($git_version ?: "NOT FOUND") . "\n\n";

// 2. Check Repository Remote URL
echo "[2] CHECKING GIT CONFIGURATION...\n";
if (is_dir('.git')) {
    $remote_url = shell_exec("git remote -v 2>&1");
    echo "Remote Config:\n" . ($remote_url ?: "NO REMOTES FOUND") . "\n";
} else {
    echo "ERROR: .git folder not found in this directory.\n";
}
echo "\n";

// 3. Attempt Git Pull
echo "[3] ATTEMPTING MANUAL PULL (origin main)...\n";
// Add 2>&1 to capture all errors
$pull_output = shell_exec("git pull origin main 2>&1");
echo "Pull Result:\n" . ($pull_output ?: "No output received.") . "\n\n";

// 4. Check file ownership (simplified for shared hosting)
echo "[4] PERMISSION CHECK...\n";
$files = ['index.php', 'config.php', 'includes/init.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "File: $file | Perms: $perms | Writable: " . (is_writable($file) ? 'YES' : 'NO') . "\n";
    }
}

echo "\n--- DIAGNOSTICS COMPLETE ---";
?>
