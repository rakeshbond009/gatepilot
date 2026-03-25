<?php if ($page != 'login' && isLoggedIn()): ?>
        <!-- Top Navigation -->
        <div class="navbar">
            <div class="logo">
                <?php
    $company_logo = getSetting($conn, 'company_logo');
    if ($company_logo):
        // Build full URL for logo (works for both relative and absolute)
        $logo_src = preg_match('#^https?://#', $company_logo)
            ? $company_logo
            : rtrim(BASE_URL, '/') . '/' . ltrim($company_logo, '/');

        // Check if file exists on disk for local render; fall back to icon if missing
        $logo_file_path = LOGO_UPLOAD_DIR . basename($company_logo);
        if (file_exists($logo_file_path)):
?>
                        <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Company Logo"
                            onerror="this.onerror=null; this.outerHTML='🚛';">
                    <?php
        else: ?>
                        🚛
                        <?php
        endif;
    else:
?>
                    🚛
                <?php
    endif; ?>
                <span>Gatepilot</span>
            </div>
            <div class="user-info" style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;">
                    <span style="font-weight: 600; font-size: 14px; color: #1e2937; display: block;">
                        <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest'; ?>
                    </span>
                    <small style="color: #64748b; font-size: 11px;">
                        (<?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : ''; ?>)
                    </small>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openChangePasswordModal()" title="Change Password"
                        style="background: #f1f5f9; border: none; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        🔑
                    </button>
                    <a href="?page=logout" title="Logout"
                        style="background: #fee2e2; border: none; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: all 0.2s;"
                        onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                        🚪
                    </a>
                </div>
            </div>
        </div>
    <?php
endif; ?>
