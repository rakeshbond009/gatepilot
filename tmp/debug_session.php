<?php
/**
 * Session/Cookie Debugger - accessed via ?page=debug_session on main app
 * This runs through index.php so init.php is already loaded
 */
if (!isLoggedIn()) {
    echo "<div class='container'><div class='alert alert-error'>Please log in first.</div></div>";
    return;
}

$save_path = session_save_path();
?>
<div class="container" style="font-family: monospace; white-space: pre-wrap;">
<div class="card" style="background:#1e293b; color:#e2e8f0; padding:20px; border-radius:12px; margin:20px 0;">
<h3 style="color:#38bdf8; margin:0 0 15px;">🔍 Session / Cookie Diagnostics</h3>

<strong style="color:#fb923c;">── SESSION ──</strong>
Session ID:          <?= session_id() ?>

Session Save Path:   <?= $save_path ?>

Path Writable:       <?= is_writable($save_path) ? '✅ YES' : '❌ NO' ?>

Session Data:
<pre style="color:#86efac;"><?= json_encode($_SESSION, JSON_PRETTY_PRINT) ?></pre>

<strong style="color:#fb923c;">── COOKIES ──</strong>
PHPSESSID:           <?= htmlspecialchars($_COOKIE[session_name()] ?? '(NOT SET - session will die on reload!)') ?>

GATEPILOT_REMEMBER:  <?= !empty($_COOKIE['GATEPILOT_REMEMBER']) ? '✅ SET (' . substr(htmlspecialchars($_COOKIE['GATEPILOT_REMEMBER']),0,16) . '...)' : '❌ NOT SET' ?>


<strong style="color:#fb923c;">── COOKIE CONFIG ──</strong>
Domain set to:       "<?= $_cookie_domain ?? '(variable not in scope!)' ?>"
Secure flag:         <?= ($_is_https ?? false) ? 'true (HTTPS)' : 'false (HTTP)' ?>

HTTP_HOST:           <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') ?>

HTTPS server var:    <?= $_SERVER['HTTPS'] ?? '(not set)' ?>


<strong style="color:#fb923c;">── PHP SESSION INI ──</strong>
gc_maxlifetime:      <?= ini_get('session.gc_maxlifetime') ?>s (<?= round(ini_get('session.gc_maxlifetime')/86400, 1) ?> days)
cookie_lifetime:     <?= ini_get('session.cookie_lifetime') ?>s
cookie_domain:       "<?= ini_get('session.cookie_domain') ?>"
cookie_secure:       <?= ini_get('session.cookie_secure') ?>

cookie_samesite:     <?= ini_get('session.cookie_samesite') ?>


<strong style="color:#fb923c;">── TOKEN DB CHECK ──</strong>
<?php
if (!empty($_COOKIE['GATEPILOT_REMEMBER'])) {
    $tok = mysqli_real_escape_string($conn, $_COOKIE['GATEPILOT_REMEMBER']);
    $r = mysqli_query($conn, "SELECT s.id, s.user_id, s.last_used_at, u.username, u.is_active 
                               FROM user_sessions s JOIN user_master u ON s.user_id=u.id 
                               WHERE s.token='$tok' LIMIT 1");
    if ($r && $row = mysqli_fetch_assoc($r)) {
        echo "✅ Token FOUND: user={$row['username']}, is_active={$row['is_active']}, last_used={$row['last_used_at']}\n";
    } else {
        echo "❌ Token NOT FOUND in DB! Error: " . mysqli_error($conn) . "\n";
    }
    $cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM user_sessions"));
    echo "Total tokens in DB: " . ($cnt['c'] ?? 'N/A') . "\n";
} else {
    echo "No GATEPILOT_REMEMBER cookie present.\n";
}
?>

<strong style="color:#fb923c;">── ENVIRONMENT ──</strong>
Environment:     <?= ENVIRONMENT ?>

Sessions path:   <?= ini_get('session.save_path') ?>

</div>

<p style="color:#64748b; font-size:12px;">⚠️ Remove ?page=debug_session access once debugging is complete.</p>
</div>
