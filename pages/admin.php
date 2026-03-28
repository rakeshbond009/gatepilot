<!-- Permission & Modal Global Styles -->
<style>
    .perm-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.7);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease-out;
    }

    /* Ensure SweetAlert2 is always above our modals */
    .swal2-container {
        z-index: 20000 !important;
    }

    .perm-modal-content {
        background: #ffffff;
        width: 95%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95);
        animation: zoomIn 0.2s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes zoomIn {
        from {
            transform: scale(0.95);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .perm-section-title {
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }

    /* Custom Checkbox Card */
    .perm-card {
        position: relative;
        cursor: pointer;
    }

    .perm-card input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .perm-card-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
        transition: all 0.2s;
        font-size: 14px;
        color: #374151;
        font-weight: 500;
    }

    .perm-card:hover .perm-card-content {
        border-color: #d1d5db;
        background: #f3f4f6;
    }

    .perm-card input:checked~.perm-card-content {
        border-color: #4f46e5;
        background: #eef2ff;
        color: #4338ca;
        box-shadow: 0 1px 2px 0 rgba(79, 70, 229, 0.1);
    }

    .perm-card input:checked~.perm-card-content::after {
        content: '✓';
        margin-left: auto;
        font-weight: bold;
        font-size: 14px;
    }

    /* Action Row Styles */
    .action-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }

    .action-row:last-child {
        border-bottom: none;
    }

    .action-info h4 {
        margin: 0;
        font-size: 15px;
        color: #1f2937;
    }

    .action-info p {
        margin: 2px 0 0 0;
        font-size: 12px;
        color: #6b7280;
    }

    /* Switch Toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #4f46e5;
    }

    input:checked+.slider:before {
        transform: translateX(20px);
    }

    .perm-change-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 6px;
        font-size: 13px;
        border: 1px solid #f1f5f9;
    }

    .perm-key {
        font-weight: 600;
        color: #475569;
        font-size: 12px;
    }

    .perm-val {
        font-family: monospace;
        text-align: right;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .table-wrapper table {
        min-width: 600px;
    }

    @media screen and (max-width: 768px) {

        .patrol-grid,
        .master-form-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .master-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
</style>

<script>
    function openPermissionModal(user) {
        document.getElementById("permUserId").value = user.id;
        document.getElementById("permUser").textContent = user.full_name;
        document.querySelectorAll("#permModal input[type=\"checkbox\"]").forEach(cb => cb.checked = false);
        let perms = {};
        try { if (user.permissions) perms = JSON.parse(user.permissions); } catch (e) { }

        if (perms.pages) {
            let p = perms.pages;
            if (p.dashboard) document.getElementById("p_dashboard").checked = true;
            if (p.inward) document.getElementById("p_inward").checked = true;
            if (p.outward) document.getElementById("p_outward").checked = true;
            if (p.reports) document.getElementById("p_reports").checked = true;
            if (p.history) document.getElementById("p_history").checked = true;
            if (p.inside) document.getElementById("p_inside").checked = true;
            if (p.guard_patrol || p.patrol) document.getElementById("p_patrol").checked = true;
            if (p.permissions) document.getElementById("p_permissions").checked = true;
            if (p.audit_logs) document.getElementById("p_audit_logs").checked = true;
            if (p.register) document.getElementById("p_register").checked = true;
            if (p.register_types) document.getElementById("p_register_types").checked = true;
            if (p.loading) document.getElementById("p_loading").checked = true;
            if (p.unloading) document.getElementById("p_unloading").checked = true;
            if (p.management) document.getElementById("p_management").checked = true;
            if (p.tickets) document.getElementById("p_tickets").checked = true;
            if (p.masters) document.getElementById("p_masters").checked = true;
            if (p.employee_scan) document.getElementById("p_employee_scan").checked = true;
            if (p.app_issues) document.getElementById("p_app_issues").checked = true;
        }

        if (perms.masters) {
            let m = perms.masters;
            if (m.transporters) document.getElementById("m_transporters").checked = true;
            if (m.drivers) document.getElementById("m_drivers").checked = true;
            if (m.vehicles) document.getElementById("m_vehicles").checked = true;
            if (m.purposes) document.getElementById("m_purposes").checked = true;
            if (m.employees) document.getElementById("m_employees").checked = true;
            if (m.departments) document.getElementById("m_departments").checked = true;
            if (m.patrol) document.getElementById("m_patrol").checked = true;
            if (m.materials) document.getElementById("m_materials").checked = true;
            if (m.suppliers) document.getElementById("m_suppliers").checked = true;
            if (m.users) document.getElementById("m_users").checked = true;
            if (m.settings) document.getElementById("m_settings").checked = true;
        }

        if (perms.actions) {
            let a = perms.actions;
            if (a.edit_record) document.getElementById("a_edit").checked = true;
            if (a.delete_record && document.getElementById("a_delete")) document.getElementById("a_delete").checked = true;
            if (a.view_buttons) document.getElementById("a_buttons").checked = true;
        }
        document.getElementById("permModal").style.display = "flex";
    }

    function showAuditDetail(log) {
        document.getElementById("auditUser").textContent = log.username;
        document.getElementById("auditModule").textContent = log.module;
        document.getElementById("auditIP").textContent = log.ip_address;
        document.getElementById("auditTime").textContent = new Date(log.created_at).toLocaleString("en-IN", {
            day: "numeric", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit", second: "2-digit"
        });

        const badge = document.getElementById("auditTypeBadge");
        badge.textContent = log.activity_type.replace(/_/g, " ");
        badge.className = "badge"; // Reset class

        if (log.activity_type.includes("UPDATE")) {
            badge.style.background = "#fff7ed"; badge.style.color = "#9a3412"; badge.style.border = "1px solid #ffedd5";
        } else if (log.activity_type.includes("SUCCESS") || log.activity_type.includes("CREATED") || log.activity_type.includes("LOGIN")) {
            badge.style.background = "#f0fdf4"; badge.style.color = "#166534"; badge.style.border = "1px solid #dcfce7";
        } else if (log.activity_type.includes("DELETE") || log.activity_type.includes("FAILED")) {
            badge.style.background = "#fef2f2"; badge.style.color = "#991b1b"; badge.style.border = "1px solid #fee2e2";
        } else {
            badge.style.background = "#f8fafc"; badge.style.color = "#475569"; badge.style.border = "1px solid #e2e8f0";
        }

        const detailsDiv = document.getElementById("auditDetails");
        detailsDiv.innerHTML = "";
        const addRow = (key, val) => {
            const row = document.createElement("div");
            row.className = "perm-change-row";
            row.style.cssText = "display:flex; justify-content:space-between; align-items:center; padding:12px 18px; background:#ffffff; border-radius:12px; margin-bottom:10px; border:1px solid #eef2ff; font-size:13px; box-shadow:0 1px 3px rgba(0,0,0,0.03);";

            const left = document.createElement("div");
            left.className = "perm-key";
            left.style.cssText = "font-weight:700; color:#475569; font-size:12px; display:flex; align-items:center; gap:8px;";

            // Add subtle icons based on common keys
            let icon = "🔹";
            const k = key.toLowerCase();
            if (k.includes("vehicle")) icon = "🚛";
            else if (k.includes("driver") || k.includes("user")) icon = "👤";
            else if (k.includes("mobile") || k.includes("phone")) icon = "📱";
            else if (k.includes("location") || k.includes("from") || k.includes("to")) icon = "📍";
            else if (k.includes("bill")) icon = "🧾";
            else if (k.includes("photo") || k.includes("upload")) icon = "📸";
            else if (k.includes("status")) icon = "🚦";
            else if (k.includes("date") || k.includes("time")) icon = "📅";

            left.innerHTML = `<span>${icon}</span> ${key}`;

            const right = document.createElement("div");
            right.className = "perm-val";
            right.style.cssText = "color:#1e293b; font-weight:500; text-align:right;";

            let displayVal = val;
            const valStr = String(val).trim();

            // Robust Photo/URL Detection
            if (valStr.toLowerCase().includes('uploads/') || valStr.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                const url = valStr.replace(/[\[\]']/g, "").trim();
                displayVal = `<a href="${url}" target="_blank" style="color:#6366f1; text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:6px; background:#eef2ff; padding:5px 12px; border-radius:6px; border:1px solid #c7d2fe; transition:all 0.2s;"><span>🖼️</span> View Upload</a>`;
            }
            // Before ➔ After logic
            else if (valStr.includes(' ➔ ') || valStr.includes(' -> ')) {
                const arrow = valStr.includes(' ➔ ') ? ' ➔ ' : ' -> ';
                const cleanVal = valStr.replace(/[\[\]]/g, "");
                const parts = cleanVal.split(arrow);
                const from = parts[0].trim();
                const to = parts[1].trim();

                const formatVal = (v) => {
                    const uv = String(v).toUpperCase();
                    if (uv === 'ON' || uv === 'TRUE' || uv === 'YES')
                        return '<span style="color:#10b981; font-weight:700; background:#dcfce7; padding:3px 10px; border-radius:6px; font-size:11px; border:1px solid #bbf7d0;">● ON</span>';
                    if (uv === 'OFF' || uv === 'FALSE' || uv === 'NO')
                        return '<span style="color:#ef4444; font-weight:700; background:#fef2f2; padding:3px 10px; border-radius:6px; font-size:11px; border:1px solid #fee2e2;">○ OFF</span>';
                    return `<span style="color:#334155; font-weight:600;">${v}</span>`;
                };

                displayVal = `<div style="display:flex; align-items:center; gap:10px; justify-content:flex-end;">
                            <span style="color:#94a3b8; font-size:11px; opacity:0.8;">${formatVal(from)}</span> 
                            <span style="color:#cbd5e1; font-weight:bold;">→</span> 
                            <span style="color:#1e293b;">${formatVal(to)}</span>
                          </div>`;
            }

            right.innerHTML = displayVal;
            row.appendChild(left);
            row.appendChild(right);
            detailsDiv.appendChild(row);
        };

        try {
            const details = log.details || "";

            // 1. Title/Header Section
            let mainContent = details;
            const mainHeaderMatch = details.match(/^([^:\n]+):\s*([\s\S]*)$/);
            if (mainHeaderMatch && !mainHeaderMatch[1].includes("[")) {
                const header = document.createElement("div");
                header.style.cssText = "font-weight:700; margin-bottom:18px; color:#1e293b; font-size:16px; border-bottom:2.5px solid #6366f1; padding-bottom:10px; display:inline-block; letter-spacing:-0.2px;";
                header.innerHTML = `<span>📝</span> ${mainHeaderMatch[1]}`;
                detailsDiv.appendChild(header);
                mainContent = mainHeaderMatch[2];
            }

            // 2. Split into segments
            const segments = mainContent.split(/\n| \| |(?<=\]),\s*/).map(s => s.trim()).filter(s => s.length > 0);

            let summarySectionHit = false;
            let changeSectionHit = false;

            segments.forEach(seg => {
                const isChange = seg.toLowerCase().startsWith("changes:") || seg.includes("➔") || seg.includes("->");

                // Section Divider Logic
                if (isChange && !changeSectionHit) {
                    const divider = document.createElement("div");
                    divider.style.cssText = "margin:22px 0 15px; padding:10px 18px; background:#fff7ed; border-radius:10px; border-left:5px solid #f59e0b; color:#9a3412; font-weight:800; font-size:12px; text-transform:uppercase; letter-spacing:0.8px; display:flex; align-items:center; gap:10px; box-shadow:0 2px 4px rgba(245,158,11,0.1);";
                    divider.innerHTML = "<span>⚡</span> Detailed Changes Detected";
                    detailsDiv.appendChild(divider);
                    changeSectionHit = true;
                } else if (!isChange && !summarySectionHit && !changeSectionHit) {
                    const divider = document.createElement("div");
                    divider.style.cssText = "margin:5px 0 15px; padding:10px 18px; background:#f0f9ff; border-radius:10px; border-left:5px solid #0ea5e9; color:#0369a1; font-weight:800; font-size:12px; text-transform:uppercase; letter-spacing:0.8px; display:flex; align-items:center; gap:10px; box-shadow:0 2px 4px rgba(14,165,233,0.1);";
                    divider.innerHTML = "<span>ℹ️</span> Initial Record Details";
                    detailsDiv.appendChild(divider);
                    summarySectionHit = true;
                }

                if (seg.toLowerCase().startsWith("changes:")) return;

                // Key-Value Parsing
                const pairMatch = seg.match(/^([^:]+):\s*(.*)$/);
                if (pairMatch) {
                    let label = pairMatch[1].trim();
                    let value = pairMatch[2].trim().replace(/^\[|\]$/g, "").trim();

                    // Skip identical values in change logs
                    if (value.includes("➔")) {
                        const vparts = value.split("➔");
                        if (vparts.length === 2 && vparts[0].trim() === vparts[1].trim()) return;
                    }

                    if (label && value) {
                        addRow(label, value);
                    }
                } else {
                    const item = document.createElement("div");
                    item.style.cssText = "padding:12px 18px; background:#f8fafc; border-radius:10px; color:#475569; margin-bottom:10px; font-size:13px; border:1px solid #e2e8f0; border-left:4px solid #94a3b8; line-height:1.5;";
                    item.textContent = seg.replace(/[\[\]]/g, "");
                    detailsDiv.appendChild(item);
                }
            });

            if (segments.length === 0) {
                detailsDiv.innerHTML = `<div style="padding:30px; background:#f8fafc; border-radius:15px; color:#64748b; line-height:1.7; white-space:pre-wrap; font-size:14px; border:2px dashed #e2e8f0; text-align:center;">${details}</div>`;
            }
        } catch (e) {
            console.error("Audit Parse Error:", e);
            detailsDiv.innerHTML = `<div style="padding:15px; background:#f8fafc; border-radius:10px; color:#334155; line-height:1.6; white-space:pre-wrap;">${log.details}</div>`;
        }

        const modal = document.getElementById("auditDetailModal");
        modal.style.display = "flex";
    }
</script>

<?php if ($page == 'admin'):
    // Handle Create Tenant (Multi-Tenancy)
    if (isset($_POST['create_tenant']) && isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] == 'admin') {
        if (!empty($_POST['edit_tenant_id'])) {
            // MUST update existing instead of creating
            $tenant_id = (int) $_POST['edit_tenant_id'];
            $master_conn = getMasterDatabaseConnection();

            $old_res = mysqli_query($master_conn, "SELECT contact_person, mobile, email, gst_no, address FROM tenants WHERE id = $tenant_id");
            $old_row = mysqli_fetch_assoc($old_res) ?: [];

            $stmt = $master_conn->prepare("UPDATE tenants SET contact_person=?, mobile=?, email=?, gst_no=?, address=? WHERE id=?");
            $stmt->bind_param("sssssi", $_POST['contact_person'], $_POST['mobile'], $_POST['email'], $_POST['gst_no'], $_POST['address'], $tenant_id);

            if ($stmt->execute()) {
                $audit_str = auditDiff($old_row, $_POST, ['edit_tenant_id', 'create_tenant'], ['customer_name' => 'Customer Name', 'slug' => 'URL Slug']);
                if (!empty($audit_str)) {
                    logActivity($conn, 'TENANT_UPDATE', 'Multi-Tenancy', "Updated Tenant Metadata:\n" . $audit_str);
                }
                $_SESSION['admin_msg'] = ["type" => "success", "title" => "Updated", "msg" => "Tenant metadata updated successfully."];
                unset($_SESSION['tenant_form_data']);
                header("Location: index.php?page=admin&master=multi-tenancy");
                exit;
            } else {
                $_SESSION['tenant_error'] = "Update failed: " . $master_conn->error;
                $_SESSION['tenant_form_data'] = $_POST;
                header("Location: index.php?page=admin&master=multi-tenancy");
                exit;
            }
        } else {
            // Create New
            $res = createTenant(
                $_POST['customer_name'],
                $_POST['slug'],
                $_POST['admin_user'],
                $_POST['admin_pass'],
                $_POST['contact_person'] ?? '',
                $_POST['mobile'] ?? '',
                $_POST['email'] ?? '',
                $_POST['address'] ?? '',
                $_POST['gst_no'] ?? ''
            );

            if ($res['success']) {
                $audit_str = auditFromPost($_POST, ['create_tenant', 'admin_pass'], ['slug' => 'URL Slug', 'admin_user' => 'Admin User']);
                logActivity($conn, 'TENANT_CREATE', 'Multi-Tenancy', "Provisioned New Tenant Platform:\n" . $audit_str);
                
                $_SESSION['admin_msg'] = ["type" => "success", "title" => "Success", "msg" => $res['message']];
                unset($_SESSION['tenant_form_data']); // Clear success data
            } else {
                // Store error specifically for the modal
                $_SESSION['tenant_error'] = $res['message'];
                $_SESSION['tenant_form_data'] = $_POST; // Save form data for retry
            }

            header("Location: index.php?page=admin&master=multi-tenancy");
            exit;
        }
    }

    // Handle Toggle Tenant Status
    if (isset($_GET['toggle_tenant']) && isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] == 'admin' && ($_SESSION['super_admin'] ?? 0) == 1) {
        $id = (int) $_GET['toggle_tenant'];
        $status = (int) $_GET['status'];
        $master_conn = getMasterDatabaseConnection();
        
        // Ensure column exists first
        $col_check = mysqli_query($master_conn, "SHOW COLUMNS FROM tenants LIKE 'is_active'");
        if (mysqli_num_rows($col_check) == 0) {
            mysqli_query($master_conn, "ALTER TABLE tenants ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER gst_no");
        }
        
        $success = mysqli_query($master_conn, "UPDATE tenants SET is_active = $status WHERE id = $id");
        
        // Get slug for log
        $slug_q = mysqli_query($master_conn, "SELECT slug FROM tenants WHERE id = $id");
        $slug_row = mysqli_fetch_assoc($slug_q);
        $slug = $slug_row['slug'] ?? 'Unknown';
        
        logActivity($conn, 'TENANT_TOGGLE', 'Multi-Tenancy', "Updated Tenant Status. Slug: $slug, ID: $id, NewStatus: $status");

        if (isset($_GET['ajax'])) {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(["success" => $success]);
            exit;
        }
        
        header("Location: index.php?page=admin&master=multi-tenancy");
        exit;
    }

    // Handle Delete Tenant (Permanent Purge)
    if (isset($_GET['delete_tenant']) && isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] == 'admin' && ($_SESSION['super_admin'] ?? 0) == 1) {
        $id = (int) $_GET['delete_tenant'];
        $master_conn = getMasterDatabaseConnection();

        // 1. Get database name to drop
        $res = mysqli_query($master_conn, "SELECT db_name, customer_name FROM tenants WHERE id = $id");
        if ($t = mysqli_fetch_assoc($res)) {
            $db_to_drop = $t['db_name'];
            $cust_name = $t['customer_name'];

            // 2. DROP DATABASE safely (only if it starts with gp_ and is not master)
            $master_db = DB_NAME;
            if (!empty($db_to_drop) && strpos($db_to_drop, 'gp_') === 0 && $db_to_drop !== $master_db) {
                // Connect without a specific DB to drop it
                $conn_drop = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
                if ($conn_drop) {
                    mysqli_query($conn_drop, "DROP DATABASE IF EXISTS `$db_to_drop` ");
                    mysqli_close($conn_drop);
                }
            }

            // 3. Remove entry from Master Index
            mysqli_query($master_conn, "DELETE FROM tenants WHERE id = $id");

            logActivity($conn, 'TENANT_DELETE', 'Multi-Tenancy', "Permanently Purged Tenant Data:\nCustomer: [$cust_name]\nDatabase: [$db_to_drop]");

            $_SESSION['admin_msg'] = [
                "type" => "success",
                "title" => "System Purged",
                "msg" => "Tenant '$cust_name' and its database '$db_to_drop' have been permanently deleted from the server."
            ];
        }

        header("Location: index.php?page=admin&master=multi-tenancy");
        exit;
    }

    // Show persistent session messages (like the one above)
    if (isset($_SESSION['admin_msg'])) {
        showAppModal($_SESSION['admin_msg']['title'], $_SESSION['admin_msg']['msg'], $_SESSION['admin_msg']['type']);
        unset($_SESSION['admin_msg']);
    }

    $master_page = $_GET['master'] ?? 'dashboard';

    // CLOUD DEPLOYMENT HANDLER
    if (isset($_POST['git_sync']) && ($_SESSION['super_admin'] ?? 0) == 1) {
        // 1. Update Version in init.php
        $init_path = dirname(__DIR__) . '/includes/init.php';
        $init_content = file_get_contents($init_path);
        // Version Format: YY.MM.DD.HHII (E.g. 26.03.26.1245)
        $new_v = date('y.m.d.Hi');
        $init_content = preg_replace("/define\('APP_VERSION', '.*?'\);/", "define('APP_VERSION', '$new_v');", $init_content);
        file_put_contents($init_path, $init_content);

        $remarks = isset($_POST['commit_remarks']) ? trim($_POST['commit_remarks']) : '';
        $remarks_text = !empty($remarks) ? " - " . str_replace('"', '\"', $remarks) : '';
        $commit_msg = "Auto Update v$new_v: " . date('Y-m-d H:i:s') . $remarks_text;
        $commands = [
            'git add .',
            'git commit -m "' . $commit_msg . '"',
            'git push origin main'
        ];

        $output = [];
        $all_success = true;
        foreach ($commands as $cmd) {
            $res = shell_exec($cmd . " 2>&1");
            $output[] = "<strong>$ " . $cmd . "</strong>\n" . htmlspecialchars($res ?: "(No output)");
            if (strpos($cmd, 'push') !== false && (strpos($res, 'error') !== false || strpos($res, 'fatal') !== false)) {
                $all_success = false;
            }
        }

        if ($all_success) {
            $m_conn = getMasterDatabaseConnection();
            $webhook = getSetting($m_conn, 'hostinger_webhook');
            if ($webhook) {
                // Hostinger webhooks expect a GitHub-style POST request with push event headers
                $payload = json_encode(['ref' => 'refs/heads/main', 'repository' => ['full_name' => 'rakeshbond009/Truckmovement']]);
                $ch = curl_init($webhook);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-GitHub-Event: push',
                    'X-GitHub-Delivery: ' . uniqid(),
                    'User-Agent: GitHub-Hookshot/GatePilot'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $webhook_response = curl_exec($ch);
                $webhook_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $webhook_error = curl_error($ch);
                curl_close($ch);

                if ($webhook_error) {
                    $output[] = "<strong style='color:#ef4444;'># Hostinger Webhook FAILED: $webhook_error</strong>";
                    $all_success = false;
                } elseif ($webhook_http_code >= 200 && $webhook_http_code < 300) {
                    $output[] = "<strong style='color:#10b981;'># Hostinger Webhook Triggered: HTTP $webhook_http_code ✅ — Auto-deploy started!</strong>";
                } else {
                    $output[] = "<strong style='color:#f59e0b;'># Hostinger Webhook Response: HTTP $webhook_http_code ⚠️</strong>\n" . htmlspecialchars($webhook_response ?: '(empty response)');
                }

            } else {
                $output[] = "<strong style='color:#f59e0b;'># No Hostinger Webhook URL set. Save one in settings to enable auto-deploy.</strong>";
            }
            if (function_exists('opcache_reset'))
                @opcache_reset();
            $output[] = "<strong># Deployed Version: $new_v</strong>";
        }


        $full_output = implode("\n\n", $output);
        $success_msg = $all_success ? "🚀 Cloud Sync Complete!" : "⚠️ Sync Failed. Check Console.";
        logActivity($conn, 'GIT_SYNC', 'System', "Sync Success: " . ($all_success ? 'YES' : 'NO'));

        // Save result in session and redirect to avoid re-submission on refresh
        $_SESSION['git_sync_success'] = $all_success;
        $_SESSION['git_sync_output'] = $full_output;
        echo "<script>window.location.href='?page=admin&master=settings';</script>";
        exit;
    }

    // Display deployment results if present in session
    if (isset($_SESSION['git_sync_output'])) {
        $all_success = $_SESSION['git_sync_success'];
        $full_output = $_SESSION['git_sync_output'];
        unset($_SESSION['git_sync_success']);
        unset($_SESSION['git_sync_output']);

        // Show result in a modal
        echo "
        <div id='gitResultModal' class='perm-modal-overlay' style='display:flex;'>
            <div class='perm-modal-content' style='max-width:800px;'>
                <div style='padding:20px; background:" . ($all_success ? '#1e293b' : '#ef4444') . "; color:white; border-radius:16px 16px 0 0; display:flex; justify-content:space-between;'>
                    <h3 style='margin:0;'>☁️ Deployment Console Output</h3>
                    <button onclick=\"document.getElementById('gitResultModal').remove()\" style='background:none; border:none; color:white; cursor:pointer; font-size:20px;'>&times;</button>
                </div>
                <div style='padding:24px; background:#0f172a; color:#38bdf8; font-family:monospace; font-size:13px; max-height:450px; overflow-y:auto; line-height:1.6;'>
                    " . nl2br($full_output) . "
                </div>
                <div style='padding:15px; background:#f8fafc; border-top:1px solid #e2e8f0; text-align:right; border-radius:0 0 16px 16px;'>
                    <button onclick=\"document.getElementById('gitResultModal').remove()\" class='btn btn-secondary'>✕ Close Console</button>
                </div>
            </div>
        </div>";
    }

    // Handle Webhook Save (Independent of Git Sync)
    if (isset($_POST['save_webhook']) && ($_SESSION['super_admin'] ?? 0) == 1) {
        $url = trim($_POST['webhook_url']);
        $m_conn = getMasterDatabaseConnection();
        setSetting($m_conn, 'hostinger_webhook', $url);
        $success_msg = "✅ Hostinger Webhook URL saved successfully!";
        logActivity($conn, 'SETTINGS_UPDATE', 'System', "Super Admin updated Webhook URL.");
    }
    $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_master"));
    $total_transporters = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transporter_master"));
    $total_drivers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM driver_master"));
    $total_purposes = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM purpose_master"));
    $total_vehicles = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_master"));

    $total_employees = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM employee_master"));

    // Check if table exists before counting to avoid error on first run
    $check_dept_table = mysqli_query($conn, "SHOW TABLES LIKE 'department_master'");
    $total_departments = (mysqli_num_rows($check_dept_table) > 0) ? mysqli_num_rows(mysqli_query($conn, "SELECT * FROM department_master")) : 0;

    $check_material_table = mysqli_query($conn, "SHOW TABLES LIKE 'material_master'");
    $total_materials = (mysqli_num_rows($check_material_table) > 0) ? mysqli_num_rows(mysqli_query($conn, "SELECT * FROM material_master")) : 0;

    $check_supplier_table = mysqli_query($conn, "SHOW TABLES LIKE 'supplier_master'");
    $total_suppliers = (mysqli_num_rows($check_supplier_table) > 0) ? mysqli_num_rows(mysqli_query($conn, "SELECT * FROM supplier_master")) : 0;
    ?>

        <div class="container">
            <?php if (isset($success_msg)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_msg; ?>
                    </div>
                    <?php
            endif; ?>
            <?php if (isset($error_msg)): ?>
                    <div class="alert alert-error">
                        <?php echo $error_msg; ?>
                    </div>
                    <?php
            endif; ?>
            <?php if (isset($session_success)): ?>
                    <div class="alert alert-success">
                        <?php echo $session_success; ?>
                    </div>
                    <?php
            endif; ?>
            <?php if (isset($session_error)): ?>
                    <div class="alert alert-error">
                        <?php echo $session_error; ?>
                    </div>
                    <?php
            endif; ?>

            <a href="?page=dashboard" class="btn btn-secondary btn-full"
                style="margin-bottom: 15px; display: block; position: relative; z-index: 10;">
                ← Back
            </a>
            <!-- Master Menu -->
            <div class="card">
                <h2>⚙️ Master Data Management</h2>

                <div class="actions-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                    <a href="?page=admin&master=dashboard#master-content"
                        class="action-card <?php echo $master_page == 'dashboard' ? 'active' : ''; ?>"
                        style="<?php echo $master_page == 'dashboard' ? 'background: #EEF2FF;' : ''; ?>">
                        <div class="icon">📊</div>
                        <strong>Overview</strong>
                    </a>

                    <?php if (hasPermission('masters.settings') && isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1): ?>
                            <a href="?page=admin&master=settings#master-content"
                                class="action-card <?php echo $master_page == 'settings' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'settings' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">⚙️</div>
                                <strong>Settings</strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.users')): ?>
                            <a href="?page=admin&master=users#master-content" class="action-card">
                                <div class="icon">👥</div>
                                <strong>Users (
                                    <?php echo $total_users; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('pages.permissions')): ?>
                            <a href="?page=user-permissions" class="action-card">
                                <div class="icon">🔐</div>
                                <strong>Permissions</strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.transporters')): ?>
                            <a href="?page=admin&master=transporters#master-content" class="action-card">
                                <div class="icon">🚚</div>
                                <strong>Transporters (
                                    <?php echo $total_transporters; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.drivers')): ?>
                            <a href="?page=admin&master=drivers#master-content" class="action-card">
                                <div class="icon">👨‍✈️</div>
                                <strong>Drivers (
                                    <?php echo $total_drivers; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.vehicles')): ?>
                            <a href="?page=admin&master=vehicles#master-content" class="action-card">
                                <div class="icon">🚛</div>
                                <strong>Vehicles (
                                    <?php echo $total_vehicles; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.purposes')): ?>
                            <a href="?page=admin&master=purposes#master-content" class="action-card">
                                <div class="icon">🎯</div>
                                <strong>Purposes (
                                    <?php echo $total_purposes; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.employees')): ?>
                            <a href="?page=admin&master=employees#master-content"
                                class="action-card <?php echo $master_page == 'employees' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'employees' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">👤</div>
                                <strong>Employees (
                                    <?php echo $total_employees; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.departments')): ?>
                            <a href="?page=admin&master=departments#master-content"
                                class="action-card <?php echo $master_page == 'departments' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'departments' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">🏢</div>
                                <strong>Departments (
                                    <?php echo $total_departments; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.patrol')): ?>
                            <a href="?page=admin&master=patrol-locations#master-content"
                                class="action-card <?php echo $master_page == 'patrol-locations' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'patrol-locations' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">📍</div>
                                <strong>Patrol QR
                                    (
                                    <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT id FROM patrol_locations")); ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.materials')): ?>
                            <a href="?page=admin&master=materials#master-content"
                                class="action-card <?php echo $master_page == 'materials' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'materials' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">🧱</div>
                                <strong>Materials (
                                    <?php echo $total_materials; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('masters.suppliers')): ?>
                            <a href="?page=admin&master=suppliers#master-content"
                                class="action-card <?php echo $master_page == 'suppliers' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'suppliers' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">🏭</div>
                                <strong>Suppliers (
                                    <?php echo $total_suppliers; ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>
                    <?php if (hasPermission('pages.register_types') || (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1)): ?>
                            <a href="?page=manage-register-types" class="action-card">
                                <div class="icon">📝</div>
                                <strong>Register Types</strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (hasPermission('pages.audit_logs')): ?>
                            <a href="?page=admin&master=audit_logs#master-content"
                                class="action-card <?php echo $master_page == 'audit_logs' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'audit_logs' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">📜</div>
                                <strong>Audit Logs</strong>
                            </a>
                            <?php
                    endif; ?>

                    <?php if (isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] == 'admin' && ($_SESSION['super_admin'] ?? 0) == 1): ?>
                            <a href="?page=admin&master=multi-tenancy#master-content"
                                class="action-card <?php echo $master_page == 'multi-tenancy' ? 'active' : ''; ?>"
                                style="<?php echo $master_page == 'multi-tenancy' ? 'background: #EEF2FF;' : ''; ?>">
                                <div class="icon">🏢</div>
                                <strong>Multi-Tenancy (
                                    <?php echo mysqli_num_rows(mysqli_query(getMasterDatabaseConnection(), "SELECT id FROM tenants")); ?>)
                                </strong>
                            </a>
                            <?php
                    endif; ?>
                </div>
            </div>

            <div id="master-content" style="scroll-margin-top: 20px;"></div>

            <?php
            // ========== MASTER DASHBOARD ==========
            if ($master_page == 'dashboard'):
                ?>
                    <div id="overviewSection">
                        <div class="card">
                            <h3>📋 Quick Info</h3>
                            <p style="color: #666; line-height: 1.6;">
                                Manage all master data from this section. Click on any category above to view and manage
                                records.
                                <br><br>
                                <strong>Available Actions:</strong><br>
                                • View all records<br>
                                • Add new entries<br>
                                • Edit existing data<br>
                                • Activate/Deactivate records
                            </p>
                        </div>
                    </div>

                    <?php
                // ========== SETTINGS ==========
            elseif ($master_page == 'settings' && isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1):
                $m_conn = getMasterDatabaseConnection();
                $current_logo = getSetting($m_conn, 'company_logo');
                ?>
                    <div id="settingsSection">
                        <?php if (isset($_GET['uploaded']) && $_GET['uploaded'] == '1'): ?>
                                <div class="alert alert-success">✅ Company logo uploaded successfully!</div>
                                <?php
                        endif; ?>
                        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                                <div class="alert alert-error">❌ Error uploading logo. Please try again with a valid image file
                                    (JPG,
                                    PNG,
                                    or GIF).</div>
                                <?php
                        endif; ?>
                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>
                        <?php if (isset($error_msg)): ?>
                                <div class="alert alert-error">
                                    <?php echo $error_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="card">
                            <h3>🎨 Company Logo & Webhook</h3>
                            <p style="color: #666; margin-bottom: 20px;">
                                Upload your company logo to display it in the navigation bar next to the app name.
                            </p>

                            <?php if ($current_logo):
                                // Build full URL for logo (works for both relative and absolute)
                                $logo_src = preg_match('#^https?://#', $current_logo)
                                    ? $current_logo
                                    : rtrim(BASE_URL, '/') . '/' . ltrim($current_logo, '/');
                                // Check if file exists on disk for local render; fall back to message if missing
                                $logo_file_path = LOGO_UPLOAD_DIR . basename($current_logo);
                                ?>
                                    <div style="margin-bottom: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                                        <p style="margin-bottom: 10px;"><strong>Current Logo:</strong></p>
                                        <?php if (file_exists($logo_file_path)): ?>
                                                <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Company Logo"
                                                    style="max-height: 80px; max-width: 200px; object-fit: contain; background: white; padding: 10px; border-radius: 6px; border: 1px solid #ddd;"
                                                    onerror="this.onerror=null; this.outerHTML='<p style=\'color: #ef4444; padding: 10px;\'>⚠️ Logo file not found</p>';">
                                                <?php
                                        else: ?>
                                                <p style="color: #ef4444; padding: 10px;">⚠️ Logo file not found:
                                                    <?php echo htmlspecialchars($current_logo); ?>
                                                </p>
                                                <?php
                                        endif; ?>
                                        <div style="margin-top: 15px;">
                                            <a href="?page=admin&master=settings&remove_logo=1" class="btn btn-secondary"
                                                onclick="return confirm('Are you sure you want to remove the logo?');">
                                                Remove Logo
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                            endif; ?>

                            <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Upload Company
                                        Logo:</label>
                                    <input type="file" name="company_logo" accept="image/jpeg,image/jpg,image/png,image/gif" required
                                        style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; width: 100%; max-width: 400px;">
                                    <small style="display: block; margin-top: 5px; color: #666;">
                                        Supported formats: JPG, PNG, GIF. Recommended size: 200x80px or similar aspect
                                        ratio.
                                    </small>
                                </div>
                                <button type="submit" name="upload_logo" class="btn btn-primary">
                                    Upload Logo
                                </button>
                            </form>
                        </div>

                        <?php
                        $is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
                        if ($is_local):
                            ?>
                                <div class="card" style="margin-top:20px; border-top: 4px solid #6366f1;">
                                    <h3 style="display: flex; align-items: center; gap: 10px;">🚀 Cloud Deployment <span
                                            style="background:#6366f1; color:white; font-size:10px; padding:2px 8px; border-radius:10px; text-transform:uppercase;">Super
                                            Admin Only</span></h3>
                                    <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                                        Use this tool to save your latest changes to the cloud. This will automatically prepare your code for
                                        deployment to Hostinger.
                                    </p>

                                    <div
                                        style="background: #eef2ff; border: 1px solid #c7d2fe; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                                        <ul style="margin: 0; padding-left: 20px; color: #4338ca; font-size: 13px; line-height: 1.6;">
                                            <li><strong>Step 1:</strong> Prepares all modified files on your laptop.</li>
                                            <li><strong>Step 2:</strong> Creates a secure version snapshot.</li>
                                            <li><strong>Step 3:</strong> Securely uploads to the GitHub repository.</li>
                                        </ul>
                                    </div>

                                    <form method="POST" id="mainGitSyncForm">
                                        <div style="margin-bottom: 20px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px;">Hostinger
                                                Webhook URL (Optional):</label>
                                            <div style="display: flex; gap: 10px;">
                                                <input type="text" name="webhook_url"
                                                    placeholder="https://hpanel.hostinger.com/api/git/deploy/..."
                                                    value="<?php echo htmlspecialchars(getSetting(getMasterDatabaseConnection(), 'hostinger_webhook')); ?>"
                                                    style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px;">
                                                <button type="submit" name="save_webhook" class="btn btn-secondary"
                                                    style="padding: 10px 20px; font-size: 13px;">Save URL</button>
                                            </div>
                                            <small style="display: block; margin-top: 5px; color: #666;">
                                                (Stored in database table <code>system_settings</code> under key <code>hostinger_webhook</code>)
                                            </small>
                                        </div>

                                        <div style="margin-bottom: 20px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px;">Commit Remarks
                                                / Version Details (Optional):</label>
                                            <input type="text" name="commit_remarks" id="commitRemarksField"
                                                placeholder="E.g., Fixed login bug, added new report section..."
                                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px;">
                                            <small style="display: block; margin-top: 5px; color: #666;">
                                                (This will be attached to your code's history log on GitHub.)
                                            </small>
                                        </div>

                                        <input type="hidden" name="git_sync" value="1" id="gitSyncTriggerField" disabled>

                                        <button type="button" onclick="confirmGitSync()" class="btn"
                                            style="background: #6366f1; color: white; padding: 14px 30px; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); width: 100%; max-width: 350px; font-size: 16px;">
                                            ☁️ Push to Cloud (Sync GitHub)
                                        </button>

                                        <script>
                                            function confirmGitSync() {
                                                Swal.fire({
                                                    title: 'Push to Cloud?',
                                                    text: 'This will save all current laptop changes and push them to GitHub. It may take up to 2 minutes depending on your internet speed.',
                                                    icon: 'question',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#6366f1',
                                                    cancelButtonColor: '#6b7280',
                                                    confirmButtonText: 'Yes, Start Syncing!',
                                                    cancelButtonText: 'Cancel'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        if (typeof showAppLoader === 'function') {
                                                            showAppLoader('☁️ Syncing with GitHub... Please wait.');
                                                        }
                                                        document.getElementById('gitSyncTriggerField').disabled = false;
                                                        document.getElementById('mainGitSyncForm').submit();
                                                    }
                                                });
                                            }
                                        </script>
                                        <p style="margin-top: 15px; font-size: 11px; color: #94a3b8; font-style: italic;">
                                            *Note: If Webhook is set, deployment is automated. Otherwise, manual deploy on Hostinger is needed.
                                        </p>
                                    </form>
                                </div>
                                <?php
                        endif; ?>
                    </div>

                    <?php
                // ========== MULTI-TENANCY MANAGEMENT ==========
            elseif ($master_page == 'multi-tenancy' && isset($_SESSION['tenant_slug']) && $_SESSION['tenant_slug'] == 'admin' && ($_SESSION['super_admin'] ?? 0) == 1):
                ?>
                    <div id="tenantsSection">
                        <div class="card" style="border-top: 4px solid #10b981;">
                            <h3 style="display: flex; align-items: center; gap: 10px;">🏢 Multi-Tenant Management <span
                                    style="background:#10b981; color:white; font-size:10px; padding:2px 8px; border-radius:10px; text-transform:uppercase;">System
                                    Admin ONLY</span></h3>
                            <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                                You are the platform owner. From here, you can provision and manage isolated instances for all your
                                customers.
                            </p>

                            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                <a href="index.php?page=admin&master=multi-tenancy&open_setup=1" class="btn btn-primary"
                                    style="background:#10b981; border:none; padding: 12px 25px; cursor: pointer; text-decoration: none; color: white; display: inline-block; border-radius: 8px; font-weight: 700;">
                                    ➕ Create New Tenant Instance
                                </a>
                                <span style="color: #64748b; font-size: 12px;">
                                    Master Database: <strong><?php echo DB_NAME; ?></strong>
                                </span>
                            </div>

                            <!-- Create/Edit Tenant Modal -->
                            <?php
                            if (isset($_GET['clear_form'])) {
                                unset($_SESSION['tenant_form_data']);
                                unset($_SESSION['tenant_error']);
                                $tenant_data = [];
                                $tenant_error = null;
                            }

                            if (isset($_GET['open_setup'])) {
                                unset($_SESSION['tenant_form_data']);
                                unset($_SESSION['tenant_error']);
                                $_SESSION['tenant_form_data'] = ['is_new' => true];
                                $tenant_data = $_SESSION['tenant_form_data'];
                                $tenant_error = null;
                            }

                            $showModal = !empty($tenant_data);
                            $isEditMode = !empty($tenant_data['edit_tenant_id']);
                            ?>
                            <div id="tenantModal" class="perm-modal-overlay"
                                style="display: <?php echo $showModal ? 'flex' : 'none'; ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
                                <div class="perm-modal-content"
                                    style="width: 90%; max-width: 800px; max-height: 90vh; padding: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; background: white;">
                                    <div
                                        style="padding: 24px 30px; background: linear-gradient(135deg, <?php echo $isEditMode ? '#3b82f6 0%, #2563eb' : '#10b981 0%, #059669'; ?> 100%); color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                                        <div>
                                            <h3 style="margin:0; font-size: 20px; font-weight: 700;" id="tenantModalTitle">🏢
                                                <?php echo $isEditMode ? 'Edit Tenant' : 'New Tenant Setup'; ?></h3>
                                            <p style="margin: 4px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);"
                                                id="tenantModalDesc">
                                                <?php echo $isEditMode ? 'Update customer metadata' : 'Provision a new isolated database instance'; ?>
                                            </p>
                                        </div>
                                        <button type="button"
                                            onclick="window.location.href='index.php?page=admin&master=multi-tenancy&clear_form=1'"
                                            style="background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 50%; color: white; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                                    </div>
                                    <div style="flex-grow: 1; overflow-y: auto; padding: 30px;">
                                        <form id="provisionForm" method="POST">
                                            <input type="hidden" name="edit_tenant_id" id="edit_tenant_id"
                                                value="<?php echo htmlspecialchars($tenant_data['edit_tenant_id'] ?? ''); ?>">
                                            <?php if ($tenant_error): ?>
                                                    <div
                                                        style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #991b1b; font-size: 14px; display: flex; align-items: center; gap: 10px;">
                                                        <span style="font-size: 20px;">⚠️</span>
                                                        <div>
                                                            <strong style="display:block;">Validation Failed</strong>
                                                            <?php echo $tenant_error; ?>
                                                        </div>
                                                    </div>
                                                    <?php unset($_SESSION['tenant_error']); ?>
                                            <?php endif; ?>

                                            <div id="passWarning"
                                                style="display:none; background: #fff7ed; border-left: 4px solid #f97316; padding: 12px; border-radius: 8px; margin-bottom: 20px; color: #9a3412; font-size: 13px;">
                                                <strong>⚠️ Password Conflict</strong>
                                                <div id="passWarningText"></div>
                                            </div>

                                            <div class="master-form-grid"
                                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                                <!-- Core Config -->
                                                <div
                                                    style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                    <h4
                                                        style="margin: 0 0 15px 0; font-size: 13px; color: #10b981; text-transform: uppercase;">
                                                        🔑 System Config</h4>
                                                    <div style="margin-bottom: 15px;">
                                                        <label
                                                            style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Customer
                                                            Name *</label>
                                                        <input type="text" name="customer_name" id="t_customer_name"
                                                            value="<?php echo htmlspecialchars($tenant_data['customer_name'] ?? ''); ?>"
                                                            placeholder="e.g. Tata Motors" required <?php echo $isEditMode ? 'readonly style="background:#f1f5f9; width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;"' : 'style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;"'; ?>>
                                                    </div>
                                                    <div style="margin-bottom: 15px;">
                                                        <label
                                                            style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Company
                                                            Code (URL Slug) *</label>
                                                        <input type="text" name="slug" id="t_slug"
                                                            value="<?php echo htmlspecialchars($tenant_data['slug'] ?? ''); ?>"
                                                            placeholder="e.g. tata" required <?php echo $isEditMode ? 'readonly style="background:#f1f5f9; width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;"' : 'style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;"'; ?>>
                                                    </div>
                                                    <div id="credentialsWrapper"
                                                        style="display: <?php echo $isEditMode ? 'none' : 'grid'; ?>; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                        <div>
                                                            <label
                                                                style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Admin
                                                                User</label>
                                                            <input type="text" name="admin_user" id="t_admin_user"
                                                                value="<?php echo htmlspecialchars($tenant_data['admin_user'] ?? ''); ?>"
                                                                placeholder="e.g. admin"
                                                                style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                        </div>
                                                        <div>
                                                            <label
                                                                style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Password</label>
                                                            <input type="text" name="admin_pass" id="tenantAdminPass"
                                                                value="<?php echo htmlspecialchars($tenant_data['admin_pass'] ?? ''); ?>"
                                                                placeholder="Enter password"
                                                                style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Metadata -->
                                                <div
                                                    style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                    <h4
                                                        style="margin: 0 0 15px 0; font-size: 13px; color: #10b981; text-transform: uppercase;">
                                                        📝 Customer Metadata</h4>
                                                    <div style="margin-bottom: 15px;">
                                                        <label
                                                            style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Contact
                                                            Person</label>
                                                        <input type="text" name="contact_person" id="t_contact_person"
                                                            value="<?php echo htmlspecialchars($tenant_data['contact_person'] ?? ''); ?>"
                                                            placeholder="Name of Manager"
                                                            style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                    </div>
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                        <div>
                                                            <label
                                                                style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mobile</label>
                                                            <input type="text" name="mobile" id="t_mobile"
                                                                value="<?php echo htmlspecialchars($tenant_data['mobile'] ?? ''); ?>"
                                                                placeholder="Contact number"
                                                                style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                        </div>
                                                        <div>
                                                            <label
                                                                style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">GST
                                                                No</label>
                                                            <input type="text" name="gst_no" id="t_gst_no"
                                                                value="<?php echo htmlspecialchars($tenant_data['gst_no'] ?? ''); ?>"
                                                                placeholder="GST registration"
                                                                style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                        </div>
                                                    </div>
                                                    <div style="margin-top: 15px;">
                                                        <label
                                                            style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Email</label>
                                                        <input type="email" name="email" id="t_email"
                                                            value="<?php echo htmlspecialchars($tenant_data['email'] ?? ''); ?>"
                                                            placeholder="Email for notifications"
                                                            style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                    </div>
                                                </div>

                                                <!-- Address (Full Width) -->
                                                <div
                                                    style="grid-column: span 2; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                    <label
                                                        style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Physical
                                                        Address</label>
                                                    <textarea name="address" id="t_address" rows="2"
                                                        placeholder="Full company address..."
                                                        style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit;"><?php echo htmlspecialchars($tenant_data['address'] ?? ''); ?></textarea>
                                                </div>

                                                <div
                                                    style="grid-column: span 2; display: flex; gap: 12px; margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                                                    <button type="button"
                                                        onclick="window.location.href='index.php?page=admin&master=multi-tenancy&clear_form=1'"
                                                        style="flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; color: #64748b; font-weight: 600; cursor: pointer;">Cancel</button>
                                                    <button type="submit" id="provisionBtn" name="create_tenant"
                                                        style="flex: 2; padding: 12px; border: none; border-radius: 8px; background: <?php echo $isEditMode ? '#3b82f6' : '#10b981'; ?>; color: white; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                                        <?php echo $isEditMode ? '💾 Save Changes' : '🚀 Provision System'; ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.getElementById('tenantAdminPass').addEventListener('input', function () {
                                    const pass = this.value.trim();
                                    const warning = document.getElementById('passWarning');
                                    const btn = document.getElementById('provisionBtn');

                                    if (pass.length < 2) {
                                        warning.style.display = 'none';
                                        btn.style.opacity = '1';
                                        btn.disabled = false;
                                        return;
                                    }

                                    fetch('index.php?page=admin&check_pass_uniqueness=1&pass=' + encodeURIComponent(pass))
                                        .then(res => res.json())
                                        .then(data => {
                                            if (!data.unique) {
                                                document.getElementById('passWarningText').innerText = 'Conflict: This password is already in use by ' + data.owner + '. Usage of non-unique passwords is blocked.';
                                                warning.style.display = 'block';
                                                btn.style.backgroundColor = '#94a3b8';
                                                btn.style.opacity = '0.6';
                                                btn.style.cursor = 'not-allowed';
                                                btn.disabled = true;
                                            } else {
                                                warning.style.display = 'none';
                                                btn.style.backgroundColor = '#10b981';
                                                btn.style.opacity = '1';
                                                btn.style.cursor = 'pointer';
                                                btn.disabled = false;
                                            }
                                        })
                                        .catch(err => {
                                            console.error('Validation Error:', err);
                                        });
                                });
                            </script>
                            </script>

                            <div class="table-wrapper">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead>
                                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                            <th style="padding: 12px; text-align: left;">Customer Name</th>
                                            <th style="padding: 12px; text-align: left;">Company Code</th>
                                            <th style="padding: 12px; text-align: left;">Contact Details</th>
                                            <th style="padding: 12px; text-align: left;">Database</th>
                                            <th style="padding: 12px; text-align: left;">Status</th>
                                            <th style="padding: 12px; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $master_conn = getMasterDatabaseConnection();
                                        $tenants_q = mysqli_query($master_conn, "SELECT * FROM tenants ORDER BY created_at DESC");
                                        while ($t = mysqli_fetch_assoc($tenants_q)):
                                            ?>
                                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;"
                                                    onmouseover="this.style.background='#f8fafc'"
                                                    onmouseout="this.style.background='transparent'">
                                                    <td style="padding: 12px;">
                                                        <div style="font-weight: 600; color: #1e293b;">
                                                            <?php echo htmlspecialchars($t['customer_name']); ?></div>
                                                        <div style="font-size: 11px; color: #94a3b8;">
                                                            <?php echo date('d M Y', strtotime($t['created_at'])); ?></div>
                                                    </td>
                                                    <td style="padding: 12px;"><code
                                                            style="background: #f1f5f9; color: #4338ca; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 12px; border: 1px solid #e2e8f0;"><?php echo htmlspecialchars($t['slug']); ?></code>
                                                    </td>
                                                    <td style="padding: 12px;">
                                                        <div style="font-size: 13px; font-weight: 500;">
                                                            <?php echo htmlspecialchars($t['contact_person'] ?: '-'); ?></div>
                                                        <div style="font-size: 11px; color: #64748b;">
                                                            <?php echo htmlspecialchars($t['mobile'] ?: $t['email'] ?: '-'); ?></div>
                                                    </td>
                                                    <td style="padding: 12px; color: #64748b; font-family: monospace; font-size: 12px;">
                                                        <?php echo htmlspecialchars($t['db_name']); ?></td>
                                                    <td style="padding: 12px;">
                                                        <span
                                                            style="background: <?php echo $t['is_active'] ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $t['is_active'] ? '#166534' : '#991b1b'; ?>; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid <?php echo $t['is_active'] ? '#bbf7d0' : '#fecaca'; ?>;">
                                                            <?php echo $t['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 12px; text-align: center;">
                                                        <?php
                                                        // Prepare json object for js
                                                        $json_data = htmlspecialchars(json_encode([
                                                            'id' => $t['id'],
                                                            'name' => $t['customer_name'],
                                                            'slug' => $t['slug'],
                                                            'contact' => $t['contact_person'],
                                                            'mobile' => $t['mobile'],
                                                            'email' => $t['email'],
                                                            'gst' => $t['gst_no'],
                                                            'address' => $t['address']
                                                        ]), ENT_QUOTES, 'UTF-8');
                                                        ?>
                                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                                            <button onclick="editTenant(<?php echo $json_data; ?>)" class="btn btn-sm"
                                                                style="background: #3b82f6; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;"
                                                                title="Edit Metadata">
                                                                ✏️ Edit
                                                            </button>

                                                            <?php if ($t['is_active']): ?>
                                                                    <button onclick="toggleTenantStatus(this, <?php echo $t['id']; ?>, 0)"
                                                                        class="btn btn-sm"
                                                                        style="background: #f59e0b; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: none;"
                                                                        title="Disable System">
                                                                        🚫 Disable
                                                                    </button>
                                                            <?php else: ?>
                                                                    <button onclick="toggleTenantStatus(this, <?php echo $t['id']; ?>, 1)"
                                                                        class="btn btn-sm"
                                                                        style="background: #10b981; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: none;"
                                                                        title="Enable System">
                                                                        ✅ Enable
                                                                    </button>
                                                            <?php endif; ?>

                                                            <button
                                                                onclick="confirmDeleteTenant(<?php echo $t['id']; ?>, '<?php echo addslashes($t['customer_name']); ?>', '<?php echo $t['db_name']; ?>')"
                                                                class="btn btn-sm"
                                                                style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;"
                                                                title="Delete System & DB">
                                                                🗑️ Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <script>
                                function editTenant(data) {
                                    // Populate form
                                    document.getElementById('edit_tenant_id').value = data.id;
                                    document.getElementById('t_customer_name').value = data.name;
                                    document.getElementById('t_customer_name').readOnly = true;
                                    document.getElementById('t_customer_name').style.background = '#f1f5f9';

                                    document.getElementById('t_slug').value = data.slug;
                                    document.getElementById('t_slug').readOnly = true;
                                    document.getElementById('t_slug').style.background = '#f1f5f9';

                                    document.getElementById('t_contact_person').value = data.contact || '';
                                    document.getElementById('t_mobile').value = data.mobile || '';
                                    document.getElementById('t_email').value = data.email || '';
                                    document.getElementById('t_gst_no').value = data.gst || '';
                                    document.getElementById('t_address').value = data.address || '';

                                    // Hide credentials logic for edit mode
                                    document.getElementById('credentialsWrapper').style.display = 'none';
                                    document.getElementById('t_admin_user').removeAttribute('required');
                                    document.getElementById('tenantAdminPass').removeAttribute('required');

                                    // Update Modal UI for edit
                                    document.getElementById('tenantModalTitle').innerHTML = '🏢 Edit Tenant';
                                    document.getElementById('tenantModalDesc').innerText = 'Update customer metadata';
                                    document.getElementById('provisionBtn').innerHTML = '💾 Save Changes';
                                    document.getElementById('provisionBtn').style.background = '#3b82f6';
                                    document.getElementById('tenantModal').querySelector('.perm-modal-content > div').style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';

                                    // Show modal
                                    document.getElementById('tenantModal').style.display = 'flex';
                                }

                                function toggleTenantStatus(btn, id, newStatus) {
                                    const originalContent = btn.innerHTML;
                                    btn.innerHTML = '⌛...';
                                    btn.disabled = true;

                                    fetch('index.php?page=admin&master=multi-tenancy&toggle_tenant=' + id + '&status=' + newStatus + '&ajax=1')
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Keep scroll position on reload
                                                const scrollPos = window.scrollY;
                                                localStorage.setItem('admin_tenant_scroll', scrollPos);
                                                window.location.reload();
                                            } else {
                                                Swal.fire('Error', 'Failed to update status.', 'error');
                                                btn.innerHTML = originalContent;
                                                btn.disabled = false;
                                            }
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            btn.innerHTML = originalContent;
                                            btn.disabled = false;
                                        });
                                }

                                function confirmDeleteTenant(id, name, db) {
                                    Swal.fire({
                                        title: '🚨 CRITICAL ACTION 🚨',
                                        html: 'Are you sure you want to delete <b>' + name + '</b>?<br><br>This will PERMANENTLY DROP the database <b>' + db + '</b> and all its data. This action cannot be undone.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#6b7280',
                                        confirmButtonText: 'Yes, DELETE EVERYTHING!',
                                        cancelButtonText: 'Cancel'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'index.php?page=admin&master=multi-tenancy&delete_tenant=' + id;
                                        }
                                    });
                                }

                                // Restore scroll position after reload
                                document.addEventListener('DOMContentLoaded', () => {
                                    const scrollPos = localStorage.getItem('admin_tenant_scroll');
                                    if (scrollPos) {
                                        window.scrollTo(0, parseInt(scrollPos));
                                        localStorage.removeItem('admin_tenant_scroll');
                                    }
                                });
                            </script>
                        </div>
                    </div>

                    <?php
                // ========== USERS MASTER ==========
            elseif ($master_page == 'users' && (hasPermission('masters.users') || (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1))):

                // Handle Delete User
                if (isset($_GET['delete_user'])) {
                    $id = (int) $_GET['delete_user'];

                    // Check if user is super admin
                    $check_super = mysqli_fetch_assoc(mysqli_query($conn, "SELECT super_admin FROM user_master WHERE id=$id"));
                    $is_target_super = ($check_super && $check_super['super_admin'] == 1);

                    // Don't allow deleting yourself or a super admin
                    if ($id != $_SESSION['user_id'] && !$is_target_super) {
                        // Get username for log
                        $uname_q = mysqli_query($conn, "SELECT username FROM user_master WHERE id=$id");
                        $uname_row = mysqli_fetch_assoc($uname_q);
                        $uname = $uname_row['username'] ?? 'Unknown';

                        if (mysqli_query($conn, "DELETE FROM user_master WHERE id=$id")) {
                            logActivity($conn, 'USER_DELETE', 'Users', "Deleted User Account: Username: $uname, ID: $id");
                            $_SESSION['success_msg'] = "✅ User deleted successfully!";
                            header("Location: ?page=admin&master=users");
                            exit;
                        } else {
                            $error_msg = "❌ Error deleting user: " . mysqli_error($conn);
                        }
                    } elseif ($id == $_SESSION['user_id']) {
                        $error_msg = "❌ You cannot delete your own account!";
                    } else {
                        $error_msg = "❌ Cannot delete a super admin account!";
                    }
                }

                // Add photo column if it doesn't exist
                $check_user_photo = mysqli_query($conn, "SHOW COLUMNS FROM user_master LIKE 'photo'");
                if (mysqli_num_rows($check_user_photo) == 0) {
                    mysqli_query($conn, "ALTER TABLE user_master ADD COLUMN photo VARCHAR(255) AFTER mobile");
                }

                // Handle Add/Edit User
                if (isset($_POST['save_user'])) {
                    $username = mysqli_real_escape_string($conn, $_POST['username']);
                    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
                    $email = mysqli_real_escape_string($conn, $_POST['email']);
                    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
                    $role = mysqli_real_escape_string($conn, $_POST['role']);
                    $password = $_POST['password'];

                    // Handle photo upload
                    $photo_path = '';
                    if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == 0) {
                        $upload_dir = 'uploads/users/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        $ext = pathinfo($_FILES['user_photo']['name'], PATHINFO_EXTENSION);
                        $filename = 'user_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['user_photo']['tmp_name'], $upload_dir . $filename)) {
                            $photo_path = $upload_dir . $filename;
                        }
                    }

                    try {
                        if ($_POST['user_id']) {
                            $id = (int) $_POST['user_id'];

                            // Fetch existing user data to check for changes
                            $current_res = mysqli_query($conn, "SELECT * FROM user_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);

                            if (!$current) {
                                $error_msg = "❌ User not found!";
                                showAppModal('Error', $error_msg, 'error');
                            } else {
                                $is_target_super = ($current['super_admin'] == 1);
                                if ($is_target_super && $id != $_SESSION['user_id']) {
                                    $error_msg = "❌ Only a super admin can edit their own profile!";
                                    showAppModal('Error', $error_msg, 'error');
                                } else {
                                    // Check for changes
                                    $changes_list = auditDiff($current, $_POST, ['password'], [
                                        'full_name' => 'Name',
                                        'email' => 'Email',
                                        'mobile' => 'Mobile',
                                        'role' => 'Role'
                                    ]);
                                    $changes = $changes_list ? explode("\n", $changes_list) : [];

                                    if ($photo_path)
                                        $changes[] = "Photo: [Updated]";

                                    $password_changed = false;
                                    if (!empty($password)) {
                                        $password_changed = true;
                                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                        $changes[] = "Password: [Changed]";
                                    }

                                    if (empty($changes)) {
                                        $session_success = "ℹ️ No changes were detected. Profile remains same.";
                                    } else {
                                        // Construct dynamic SQL
                                        $updates = [
                                            "full_name='$full_name'",
                                            "email='$email'",
                                            "mobile='$mobile'",
                                            "role='$role'"
                                        ];
                                        if ($photo_path)
                                            $updates[] = "photo='$photo_path'";
                                        if ($password_changed)
                                            $updates[] = "password='$hashed_password'";

                                        $update_sql = "UPDATE user_master SET " . implode(", ", $updates) . " WHERE id=$id";

                                        if (mysqli_query($conn, $update_sql)) {
                                            $log_details = "Updated User: Username: [$username] (ID: $id)";
                                            if (!empty($changes))
                                                $log_details .= "\nChanges:\n" . implode("\n", $changes);
                                            logActivity($conn, 'USER_UPDATE', 'Users', $log_details);
                                            $_SESSION['success_msg'] = "✅ User updated successfully!";
                                            session_write_close();
                                            header("Location: ?page=admin&master=users&t=" . time());
                                            exit;
                                        } else {
                                            $error_msg = "❌ Error updating user: " . mysqli_error($conn);
                                            showAppModal('Error', $error_msg, 'error');
                                        }
                                    }
                                }
                            }
                        } else {
                            // Create New User
                            if (empty($password)) {
                                $error_msg = "❌ Password is required for new users!";
                                showAppModal('Error', $error_msg, 'error');
                            } else {
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql = "INSERT INTO user_master (username, password, full_name, email, mobile, role, photo) 
                                VALUES ('$username', '$hashed_password', '$full_name', '$email', '$mobile', '$role', " . ($photo_path ? "'$photo_path'" : "NULL") . ")";

                                if (mysqli_query($conn, $sql)) {
                                    $details = "Created User: Name: [$full_name]\nUsername: [$username]";
                                    $post_details = auditFromPost($_POST, ['password', 'username', 'full_name', 'user_id', 'role']);
                                    if (!empty($post_details)) {
                                        $details .= "\n" . $post_details;
                                    }
                                    $details .= "\nPassword: [Set]";
                                    if ($photo_path) {
                                        $details .= "\nPhoto: [Uploaded]";
                                    }
                                    logActivity($conn, 'USER_CREATE', 'Users', $details);
                                    $success_msg = "✅ User created successfully!";
                                    $_SESSION['success_msg'] = $success_msg;
                                    session_write_close();
                                    header("Location: ?page=admin&master=users&t=" . time());
                                    exit;
                                } else {
                                    $error_msg = "❌ Error creating user: " . mysqli_error($conn);
                                    showAppModal('Error', $error_msg, 'error');
                                }
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: Username '$username' already exists!";
                            showAppModal('Error', $error_msg, 'error');
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                            showAppModal('Error', $error_msg, 'error');
                        }
                    }
                }

                $users = mysqli_query($conn, "SELECT * FROM user_master WHERE is_active=1 ORDER BY created_at DESC");
                ?>
                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">👥</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Users Management</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage system
                                    users
                                    and
                                    their access roles</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button onclick="newUser()" class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            ➕ Add New User
                        </button>

                        <!-- Add/Edit Form Modal -->
                        <div id="userForm" class="perm-modal-overlay">
                            <div class="perm-modal-content"
                                style="max-width: 800px; padding: 0; border-radius: 16px; overflow: hidden;">
                                <div
                                    style="padding: 24px 30px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h3 id="userFormTitle" style="margin:0; font-size: 20px; font-weight: 700;">👤 User Profile
                                            Manager</h3>
                                        <p style="margin: 4px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);">Create or update
                                            system user accounts</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('userForm').style.display='none'"
                                        style="background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 50%; color: white; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                                </div>
                                <form method="POST" enctype="multipart/form-data" style="padding: 30px; background: white;">
                                    <input type="hidden" name="user_id" id="user_id">

                                    <div class="master-form-grid">
                                        <!-- Account Info -->
                                        <div
                                            style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                            <h4
                                                style="margin: 0 0 15px 0; color: #1e293b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #3b82f6; display: inline-block; padding-bottom: 2px;">
                                                🔐 Account Settings</h4>
                                            <div class="form-group" style="margin-bottom: 15px;">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Username *</label>
                                                <input type="text" name="username" id="username"
                                                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                                    required
                                                    style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group" style="margin-bottom: 15px;">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Full Name *</label>
                                                <input type="text" name="full_name" id="full_name"
                                                    value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                                    required
                                                    style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group" style="margin-bottom: 15px;">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Role *</label>
                                                <select name="role" id="role" required
                                                    style="width: 100%; padding: 11px; border: 1.5px solid #d1d5db; border-radius: 8px; background: white;">
                                                    <option value="admin">Admin</option>
                                                    <option value="manager">Manager</option>
                                                    <option value="security">Security</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Password <span
                                                        id="pwd_req" style="display:none;">*</span></label>
                                                <input type="password" name="password" id="user_password"
                                                    placeholder="Leave blank to keep current"
                                                    style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                        </div>

                                        <!-- Contact & Media -->
                                        <div
                                            style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                            <h4
                                                style="margin: 0 0 15px 0; color: #1e293b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #10b981; display: inline-block; padding-bottom: 2px;">
                                                📞 Contact & Photo</h4>
                                            <div class="form-group" style="margin-bottom: 15px;">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Email</label>
                                                <input type="email" name="email" id="email"
                                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                    style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group" style="margin-bottom: 15px;">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Mobile</label>
                                                <input type="tel" name="mobile" id="p_mobile" inputmode="numeric" pattern="[0-9]{10}"
                                                    maxlength="10" minlength="10" title="Please enter a valid 10-digit mobile number"
                                                    value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>"
                                                    style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Profile
                                                    Photo</label>
                                                <input type="file" name="user_photo" id="user_photo" accept="image/*"
                                                    style="width: 100%; padding: 8px; border: 1.5px solid #d1d5db; border-radius: 8px; background: white;">
                                                <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">JPG or
                                                    PNG, max 2MB</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        style="display: flex; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; justify-content: flex-end;">
                                        <button type="button" onclick="document.getElementById('userForm').style.display='none'"
                                            class="btn btn-secondary"
                                            style="padding: 12px 25px; border-radius: 10px; font-weight: 600; min-width: 120px;">Cancel</button>
                                        <button type="submit" name="save_user" class="btn btn-primary"
                                            style="padding: 12px 30px; border-radius: 10px; font-weight: 600; background: #3b82f6; border: none; min-width: 150px; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);">💾
                                            Save Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="userSearch" placeholder="🔍 Search Users (Name, Username, Role, Email...)"
                                oninput="filterTable('userSearch', 'usersTable')" onsearch="filterTable('userSearch', 'usersTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper" id="usersTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                            <tr style="cursor: pointer;"
                                                onclick="window.location.href='?page=user-detail&id=<?php echo $user['id']; ?>'"
                                                title="Click to view details">
                                                <td>
                                                    <?php echo $user['username']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $user['full_name']; ?>
                                                </td>
                                                <td><span class="badge badge-info">
                                                        <?php echo strtoupper($user['role']); ?>
                                                    </span></td>
                                                <td>
                                                    <?php echo $user['email'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $user['mobile'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td onclick="event.stopPropagation();">
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php
                                                            // Only allow editing if user is NOT super admin, OR if super admin is editing themselves
                                                            $is_super = (isset($user['super_admin']) && $user['super_admin'] == 1);
                                                            // Allow editing if user is NOT super admin, OR if super admin is editing themselves, OR if logged in user is a super admin
                                                            if (hasPermission('actions.edit_record') && (!$is_super || $user['id'] == $_SESSION['user_id'] || (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1))):
                                                                ?>
                                                                    <button
                                                                        onclick='editUser(<?php echo $user["id"]; ?>, <?php echo json_encode($user["username"]); ?>, <?php echo json_encode($user["full_name"]); ?>, <?php echo json_encode($user["role"]); ?>, <?php echo json_encode($user["email"]); ?>, <?php echo json_encode($user["mobile"]); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php if (hasPermission('pages.permissions')): ?>
                                                                            <button class="btn btn-sm"
                                                                                style="background: #4f46e5; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;"
                                                                                onclick='openPermissionModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES); ?>)'>
                                                                                🔐 Rights
                                                                            </button>
                                                                            <?php
                                                                    endif; ?>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                            // Only hide delete for EXACT username 'admin' or yourself
                                                            if (hasPermission('actions.delete_record') && $user['id'] != $_SESSION['user_id'] && $user['username'] != 'admin'):
                                                                ?>
                                                                    <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            function newUser() {
                                document.getElementById('user_id').value = '';
                                document.getElementById('username').value = '';
                                document.getElementById('username').readOnly = false;
                                document.getElementById('full_name').value = '';
                                document.getElementById('role').value = 'guard';
                                document.getElementById('email').value = '';
                                document.getElementById('p_mobile').value = '';
                                document.getElementById('user_password').required = true;
                                document.getElementById('pwd_req').style.display = 'inline';
                                document.getElementById('userFormTitle').textContent = '👤 Add New User';
                                document.getElementById('userForm').style.display = 'flex';
                            }
                            function editUser(id, username, fullName, role, email, mobile) {
                                document.getElementById('user_id').value = id;
                                document.getElementById('username').value = username;
                                document.getElementById('username').readOnly = true; // Can't change username
                                document.getElementById('full_name').value = fullName;
                                if (role) {
                                    document.getElementById('role').value = role.toLowerCase().trim();
                                }
                                document.getElementById('email').value = email || '';
                                document.getElementById('p_mobile').value = mobile || '';
                                document.getElementById('user_password').required = false;
                                document.getElementById('pwd_req').style.display = 'none';
                                document.getElementById('userFormTitle').textContent = '👤 Edit User: ' + fullName;
                                document.getElementById('userForm').style.display = 'flex';
                            }
                            <?php if (isset($_POST['save_user']) && isset($error_msg)): ?>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        document.getElementById('userForm').style.display = 'flex';
                                        // If editing an existing user, we need to set the readonly state and title correctly
                                        <?php if (!empty($_POST['user_id'])): ?>
                                                document.getElementById('username').readOnly = true;
                                                document.getElementById('userFormTitle').textContent = '👤 Edit User: ' + <?php echo json_encode($_POST['full_name']); ?>;
                                                document.getElementById('user_password').required = false;
                                                document.getElementById('pwd_req').style.display = 'none';
                                                <?php
                                        else: ?>
                                                document.getElementById('user_password').required = true;
                                                document.getElementById('pwd_req').style.display = 'inline';
                                                <?php
                                        endif; ?>
                                    });
                                    <?php
                            endif; ?>

                            function deleteUser(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this user?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=users&t=' + Date.now() + '&delete_user=' + id;
                                    }
                                });
                            }
                        </script>
                    </div>

                    <?php
                // ========== TRANSPORTERS MASTER ==========
            elseif ($master_page == 'transporters'):

                // Handle Delete
                if (isset($_GET['delete_trans'])) {
                    $id = (int) $_GET['delete_trans'];

                    // Update drivers to remove transporter reference (set to NULL)
                    mysqli_query($conn, "UPDATE driver_master SET transporter_id=NULL WHERE transporter_id=$id");

                    // Get transporter name for log
                    $t_res = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$id");
                    $t_row = mysqli_fetch_assoc($t_res);
                    $t_name = $t_row['transporter_name'] ?? 'Unknown';

                    // Delete the transporter
                    if (mysqli_query($conn, "DELETE FROM transporter_master WHERE id=$id")) {
                        logActivity($conn, 'TRANSPORTER_DELETE', 'Transporters', "Deleted transporter: ['$t_name'] (ID: [$id])");
                        $_SESSION['success_msg'] = "✅ Transporter deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=transporters&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting transporter: " . mysqli_error($conn);
                    }
                }

                // Handle Add/Edit
                if (isset($_POST['save_transporter'])) {
                    $name = mysqli_real_escape_string($conn, $_POST['transporter_name']);
                    $person = mysqli_real_escape_string($conn, $_POST['contact_person']);
                    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
                    $email = mysqli_real_escape_string($conn, $_POST['email']);
                    $address = mysqli_real_escape_string($conn, $_POST['address']);
                    $gst = mysqli_real_escape_string($conn, $_POST['gst_number']);

                    try {
                        if ($_POST['trans_id']) {
                            $id = (int) $_POST['trans_id'];
                            $current_res = mysqli_query($conn, "SELECT * FROM transporter_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);

                            if ($current) {
                                $changes_list = auditDiff($current, $_POST, [], [
                                    'transporter_name' => 'Name',
                                    'contact_person' => 'Contact',
                                    'mobile' => 'Mobile',
                                    'email' => 'Email',
                                    'gst_number' => 'GST',
                                ]);
                                $changes = $changes_list ? explode("\n", $changes_list) : [];
                                if ($current['address'] != $_POST['address'])
                                    $changes[] = "Address: [Updated]";

                                $sql = "UPDATE transporter_master SET transporter_name='$name', contact_person='$person', 
                                mobile='$mobile', email='$email', address='$address', gst_number='$gst' WHERE id=$id";

                                if (mysqli_query($conn, $sql)) {
                                    $details = "Updated Transporter: Name: [$name] (ID: [$id])";
                                    if (!empty($changes)) {
                                        $details .= "\nChanges:\n" . implode("\n", $changes);
                                    }
                                    logActivity($conn, 'TRANSPORTER_UPDATE', 'Transporters', $details);
                                    $success_msg = "✅ Transporter updated successfully!";
                                    $_SESSION['success_msg'] = $success_msg;
                                    session_write_close();
                                    header("Location: ?page=admin&master=transporters&t=" . time());
                                    exit;
                                } else {
                                    $error_msg = "❌ Error updating transporter: " . mysqli_error($conn);
                                }
                            } else {
                                $error_msg = "❌ Transporter not found!";
                            }
                        } else {
                            $sql = "INSERT INTO transporter_master (transporter_name, contact_person, mobile, email, address, gst_number) 
                            VALUES ('$name', '$person', '$mobile', '$email', '$address', '$gst')";

                            if (mysqli_query($conn, $sql)) {
                                logActivity($conn, 'TRANSPORTER_CREATE', 'Transporters', "Created Transporter:\n" . auditFromPost($_POST, [], ['transporter_name' => 'Name', 'contact_person' => 'Contact', 'mobile' => 'Mobile', 'gst_number' => 'GST']));
                                $success_msg = "✅ Transporter added successfully!";
                                $_SESSION['success_msg'] = $success_msg;
                                session_write_close();
                                header("Location: ?page=admin&master=transporters&t=" . time());
                                exit;
                            } else {
                                $error_msg = "❌ Error adding transporter: " . mysqli_error($conn);
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: A transporter with similar details (Name or GST) already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                        }
                    }
                }

                $transporters = mysqli_query($conn, "SELECT * FROM transporter_master WHERE is_active=1 ORDER BY transporter_name");
                ?>
                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🚚</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Transporters Management</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage
                                    transporter
                                    and
                                    logistics company details</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button
                            onclick="document.getElementById('transForm').style.display='block'; document.getElementById('transForm').scrollIntoView({ behavior: 'smooth' });"
                            class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            ➕ Add New Transporter
                        </button>

                        <div id="transForm" style="display: none; margin-bottom: 20px;">
                            <form method="POST">
                                <input type="hidden" name="trans_id" id="trans_id">

                                <!-- Section: Basic Information -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #3b82f6; background: linear-gradient(to right, #eff6ff 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #3b82f6; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            1</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📋 Basic
                                            Information</h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Transporter Name *</label>
                                            <input type="text" name="transporter_name" id="transporter_name" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Contact Person *</label>
                                            <input type="text" name="contact_person" id="contact_person" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section: Contact Details -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #10b981; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            2</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📞 Contact
                                            Details
                                        </h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Mobile *</label>
                                            <input type="tel" name="mobile" id="trans_mobile" pattern="[0-9]{10}" minlength="10"
                                                maxlength="10" title="Please enter exactly 10 digits" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Email *</label>
                                            <input type="email" name="email" id="trans_email" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section: Business Details -->
                                <div class="card"
                                    style="margin-bottom: 25px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #f59e0b; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            3</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">🏢
                                            Business
                                            Details</h3>
                                    </div>
                                    <div class="form-group">
                                        <label style="font-weight: 600; color: #374151;">GST Number *</label>
                                        <input type="text" name="gst_number" id="gst_number" maxlength="15" required
                                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 15px;"
                                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                    </div>
                                    <div class="form-group">
                                        <label style="font-weight: 600; color: #374151;">Address *</label>
                                        <textarea name="address" id="trans_address" rows="3" required
                                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; resize: vertical; font-family: inherit;"
                                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"></textarea>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 10px; margin-top: 10px;">
                                    <button type="submit" name="save_transporter" class="btn btn-success"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                        Save Transporter</button>
                                    <button type="button" onclick="closeTransForm()" class="btn btn-secondary"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>
                        <?php if (isset($error_msg)): ?>
                                <div class="alert alert-error">
                                    <?php echo $error_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="transSearch" placeholder="🔍 Search Transporters (Name, Person, Mobile, GST...)"
                                oninput="filterTable('transSearch', 'transportersTable')"
                                onsearch="filterTable('transSearch', 'transportersTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper" id="transportersTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Transporter Name</th>
                                        <th>Contact Person</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>GST Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($trans = mysqli_fetch_assoc($transporters)): ?>
                                            <tr style="cursor: pointer;"
                                                onclick="window.location.href='?page=transporter-detail&id=<?php echo $trans['id']; ?>'"
                                                title="Click to view details">
                                                <td><strong>
                                                        <?php echo $trans['transporter_name']; ?>
                                                    </strong></td>
                                                <td>
                                                    <?php echo $trans['contact_person'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $trans['mobile'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $trans['email'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $trans['gst_number'] ?: '-'; ?>
                                                </td>
                                                <td onclick="event.stopPropagation();">
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button class="btn-edit-transporter btn btn-sm"
                                                                        data-id="<?php echo htmlspecialchars($trans['id'], ENT_QUOTES); ?>"
                                                                        data-name="<?php echo htmlspecialchars($trans['transporter_name'] ?? '', ENT_QUOTES); ?>"
                                                                        data-person="<?php echo htmlspecialchars($trans['contact_person'] ?? '', ENT_QUOTES); ?>"
                                                                        data-mobile="<?php echo htmlspecialchars($trans['mobile'] ?? '', ENT_QUOTES); ?>"
                                                                        data-email="<?php echo htmlspecialchars($trans['email'] ?? '', ENT_QUOTES); ?>"
                                                                        data-gst="<?php echo htmlspecialchars($trans['gst_number'] ?? '', ENT_QUOTES); ?>"
                                                                        data-address="<?php echo htmlspecialchars($trans['address'] ?? '', ENT_QUOTES); ?>"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deleteTransporter(<?php echo $trans['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            // Use event delegation for edit buttons
                            document.addEventListener('DOMContentLoaded', function () {
                                document.querySelectorAll('.btn-edit-transporter').forEach(function (btn) {
                                    btn.addEventListener('click', function (e) {
                                        e.stopPropagation();
                                        const id = this.getAttribute('data-id');
                                        const name = this.getAttribute('data-name') || '';
                                        const person = this.getAttribute('data-person') || '';
                                        const mobile = this.getAttribute('data-mobile') || '';
                                        const email = this.getAttribute('data-email') || '';
                                        const gst = this.getAttribute('data-gst') || '';
                                        const address = this.getAttribute('data-address') || '';

                                        document.getElementById('trans_id').value = id;
                                        document.getElementById('transporter_name').value = name;
                                        document.getElementById('contact_person').value = person;
                                        document.getElementById('trans_mobile').value = mobile;
                                        document.getElementById('trans_email').value = email;
                                        document.getElementById('gst_number').value = gst;
                                        document.getElementById('trans_address').value = address;
                                        document.getElementById('transForm').style.display = 'block';
                                        document.getElementById('transForm').scrollIntoView({ behavior: 'smooth' });
                                    });
                                });
                            });

                            function editTransporter(id, name, person, mobile, email, gst, address) {
                                document.getElementById('trans_id').value = id;
                                document.getElementById('transporter_name').value = name;
                                document.getElementById('contact_person').value = person || '';
                                document.getElementById('trans_mobile').value = mobile || '';
                                document.getElementById('trans_email').value = email || '';
                                document.getElementById('gst_number').value = gst || '';
                                document.getElementById('trans_address').value = address || '';
                                document.getElementById('transForm').style.display = 'block';
                                document.getElementById('transForm').scrollIntoView({ behavior: 'smooth' });
                            }
                            function closeTransForm() {
                                document.getElementById('transForm').style.display = 'none';
                                document.getElementById('trans_id').value = '';
                                document.querySelector('#transForm form').reset();
                            }
                            function deleteTransporter(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this transporter?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=transporters&delete_trans=' + id;
                                    }
                                });
                            }
                        </script>
                    </div>

                    <?php
                // ========== DRIVERS MASTER ==========
            elseif ($master_page == 'drivers'):

                // Add photo column if it doesn't exist
                $check_col = mysqli_query($conn, "SHOW COLUMNS FROM driver_master LIKE 'photo'");
                if (mysqli_num_rows($check_col) == 0) {
                    mysqli_query($conn, "ALTER TABLE driver_master ADD COLUMN photo VARCHAR(255) NULL AFTER mobile");
                }

                // Add license_photo column if it doesn't exist
                $check_col = mysqli_query($conn, "SHOW COLUMNS FROM driver_master LIKE 'license_photo'");
                if (mysqli_num_rows($check_col) == 0) {
                    mysqli_query($conn, "ALTER TABLE driver_master ADD COLUMN license_photo VARCHAR(255) NULL AFTER license_expiry");
                }

                // Handle Delete
                if (isset($_GET['delete_driver'])) {
                    $id = (int) $_GET['delete_driver'];

                    // First, delete from vehicle_drivers if exists
                    mysqli_query($conn, "DELETE FROM vehicle_drivers WHERE driver_id=$id");

                    // Get driver name for log
                    $d_res = mysqli_query($conn, "SELECT driver_name FROM driver_master WHERE id=$id");
                    $d_row = mysqli_fetch_assoc($d_res);
                    $d_name = $d_row['driver_name'] ?? 'Unknown';

                    // Then delete the driver record
                    if (mysqli_query($conn, "DELETE FROM driver_master WHERE id=$id")) {
                        logActivity($conn, 'DRIVER_DELETE', 'Drivers', "Deleted Driver: Name: [$d_name] (ID: $id)");
                        $_SESSION['success_msg'] = "✅ Driver deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=drivers&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting driver: " . mysqli_error($conn);
                    }
                }

                if (isset($_POST['save_driver'])) {
                    $name = mysqli_real_escape_string($conn, $_POST['driver_name']);
                    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
                    $license = mysqli_real_escape_string($conn, $_POST['license_number']);
                    $expiry = $_POST['license_expiry'];
                    $trans_id = !empty($_POST['transporter_id']) ? intval($_POST['transporter_id']) : 'NULL';
                    $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

                    // Handle driver photo upload (Check both gallery and camera inputs)
                    $photo_path = '';
                    $d_file = null;
                    if (isset($_FILES['driver_photo']) && $_FILES['driver_photo']['size'] > 0) {
                        $d_file = $_FILES['driver_photo'];
                    } elseif (isset($_FILES['driver_photo_camera']) && $_FILES['driver_photo_camera']['size'] > 0) {
                        $d_file = $_FILES['driver_photo_camera'];
                    }

                    if ($d_file && $d_file['error'] == 0) {
                        // Use absolute path for file system, relative path for database
                        $upload_dir_abs = dirname(__DIR__) . '/uploads/drivers/';
                        $upload_dir_rel = 'uploads/drivers/';
                        if (!is_dir($upload_dir_abs)) {
                            mkdir($upload_dir_abs, 0777, true);
                        }
                        $ext = pathinfo($d_file['name'], PATHINFO_EXTENSION);
                        $filename = 'driver_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($d_file['tmp_name'], $upload_dir_abs . $filename)) {
                            $photo_path = $upload_dir_rel . $filename;
                        }
                    }

                    // Handle license photo upload (Check both gallery and camera inputs)
                    $license_photo_path = '';
                    $l_file = null;
                    if (isset($_FILES['license_photo']) && $_FILES['license_photo']['size'] > 0) {
                        $l_file = $_FILES['license_photo'];
                    } elseif (isset($_FILES['license_photo_camera']) && $_FILES['license_photo_camera']['size'] > 0) {
                        $l_file = $_FILES['license_photo_camera'];
                    }

                    if ($l_file && $l_file['error'] == 0) {
                        // Use absolute path for file system, relative path for database
                        $upload_dir_abs = dirname(__DIR__) . '/uploads/licenses/';
                        $upload_dir_rel = 'uploads/licenses/';
                        if (!is_dir($upload_dir_abs)) {
                            mkdir($upload_dir_abs, 0777, true);
                        }
                        $ext = pathinfo($l_file['name'], PATHINFO_EXTENSION);
                        $filename = 'license_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($l_file['tmp_name'], $upload_dir_abs . $filename)) {
                            $license_photo_path = $upload_dir_rel . $filename;
                        }
                    }

                    try {
                        if ($_POST['driver_id']) {
                            $id = intval($_POST['driver_id']);
                            $current_res = mysqli_query($conn, "SELECT * FROM driver_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);

                            if ($current) {
                                $changes_list = auditDiff($current, $_POST, [], [
                                    'driver_name' => 'Name',
                                    'mobile' => 'Mobile',
                                    'license_number' => 'License',
                                    'license_expiry' => 'Expiry'
                                ]);
                                $changes = $changes_list ? explode("\n", $changes_list) : [];

                                if ($current['transporter_id'] != $trans_id) {
                                    $old_t_id = (int) $current['transporter_id'];
                                    $new_t_val = ($trans_id !== 'NULL') ? (int) $trans_id : 0;

                                    $old_t_res = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$old_t_id");
                                    $old_t_name = ($old_t_row = mysqli_fetch_assoc($old_t_res)) ? $old_t_row['transporter_name'] : 'None';

                                    $new_t_res = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$new_t_val");
                                    $new_t_name = ($new_t_row = mysqli_fetch_assoc($new_t_res)) ? $new_t_row['transporter_name'] : 'None';

                                    $changes[] = "Transporter: [$old_t_name ➔ $new_t_name]";
                                }
                                if ($current['is_active'] != $is_active)
                                    $changes[] = "Status: [" . ($current['is_active'] ? 'Active' : 'Inactive') . " ➔ " . ($is_active ? 'Active' : 'Inactive') . "]";

                                $sql = "UPDATE driver_master SET driver_name='$name', mobile='$mobile', 
                                license_number='$license', license_expiry='$expiry', transporter_id=$trans_id, is_active=$is_active";
                                if ($photo_path) {
                                    $sql .= ", photo='$photo_path'";
                                    $changes[] = "Photo: [Updated]";
                                }
                                if ($license_photo_path) {
                                    $sql .= ", license_photo='$license_photo_path'";
                                    $changes[] = "License Photo: [Updated]";
                                }
                                $sql .= " WHERE id=$id";

                                if (mysqli_query($conn, $sql)) {
                                    $details = "Updated Driver: Name: [$name] (ID: [$id])";
                                    if (!empty($changes)) {
                                        $details .= "\nChanges:\n" . implode("\n", $changes);
                                    }
                                    logActivity($conn, 'DRIVER_UPDATE', 'Drivers', $details);
                                    $success_msg = "✅ Driver updated successfully!";
                                    $_SESSION['success_msg'] = $success_msg;
                                    session_write_close();
                                    header("Location: ?page=admin&master=drivers&t=" . time());
                                    exit;
                                } else {
                                    $error_msg = "❌ Error updating driver: " . mysqli_error($conn);
                                }
                            } else {
                                $error_msg = "❌ Driver not found!";
                            }
                        } else {
                            $sql = "INSERT INTO driver_master (driver_name, mobile, license_number, license_expiry, transporter_id, photo, license_photo, is_active) 
                            VALUES ('$name', '$mobile', '$license', '$expiry', $trans_id, " . ($photo_path ? "'$photo_path'" : "NULL") . ", " . ($license_photo_path ? "'$license_photo_path'" : "NULL") . ", $is_active)";

                            if (mysqli_query($conn, $sql)) {
                                // Get Transporter Name for better logging
                                $tname = 'None';
                                if (!empty($trans_id)) {
                                    $tq = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$trans_id");
                                    if ($t_row = mysqli_fetch_assoc($tq)) {
                                        $tname = $t_row['transporter_name'];
                                    }
                                }

                                $details = "Created Driver:\n" . auditFromPost($_POST, ['transporter_id'], ['driver_name' => 'Name', 'mobile' => 'Mobile', 'license_number' => 'License', 'license_expiry' => 'Expiry']);
                                $details .= "\nTransporter: [$tname]";
                                if ($photo_path) {
                                    $details .= "\nDriver Photo: [Uploaded]";
                                }
                                if ($license_photo_path) {
                                    $details .= "\nLicense Document: [Uploaded]";
                                }
                                logActivity($conn, 'DRIVER_CREATE', 'Drivers', $details);
                                $success_msg = "✅ Driver added successfully!";
                                $_SESSION['success_msg'] = $success_msg;
                                session_write_close();
                                header("Location: ?page=admin&master=drivers&t=" . time());
                                exit;
                            } else {
                                $error_msg = "❌ Error adding driver: " . mysqli_error($conn);
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: A driver with mobile number '$mobile' already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                        }
                    }
                }

                $drivers = mysqli_query($conn, "SELECT d.*, t.transporter_name FROM driver_master d 
                                           LEFT JOIN transporter_master t ON d.transporter_id = t.id 
                                           WHERE d.is_active=1 ORDER BY d.driver_name");
                $trans_list = mysqli_query($conn, "SELECT id, transporter_name FROM transporter_master WHERE is_active=1");
                ?>
                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">👨‍✈️</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Drivers Management</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage driver
                                    information
                                    and licenses</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button onclick="openDriverForm()" class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            ➕ Add New Driver
                        </button>

                        <div id="driverForm" style="display: none; margin-bottom: 20px;">
                            <form method="POST" enctype="multipart/form-data" onsubmit="showAppLoader('Saving Driver Data...')">
                                <input type="hidden" name="driver_id" id="driver_id">

                                <!-- Section 1: Basic Information -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #10b981; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            1</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📋 Basic
                                            Information
                                        </h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Driver Name *</label>
                                            <input type="text" name="driver_name" id="driver_name" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Mobile *</label>
                                            <input type="tel" name="mobile" id="driver_mobile" maxlength="10" inputmode="numeric"
                                                pattern="[0-9]{10}" minlength="10" required title="Please enter exactly 10 digits"
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Transporter *</label>
                                            <select name="transporter_id" id="driver_transporter_id" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <option value="">-- Select Transporter --</option>
                                                <?php
                                                mysqli_data_seek($trans_list, 0);
                                                while ($t = mysqli_fetch_assoc($trans_list)): ?>
                                                        <option value="<?php echo $t['id']; ?>">
                                                            <?php echo $t['transporter_name']; ?>
                                                        </option>
                                                        <?php
                                                endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Status *</label>
                                            <select name="is_active" id="driver_is_active" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: License Information -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #f59e0b; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            2</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">🪪 License
                                            Information
                                        </h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">License Number *</label>
                                            <input type="text" name="license_number" id="license_number" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">License Expiry *</label>
                                            <input type="date" name="license_expiry" id="license_expiry" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-top: 15px;">
                                        <label style="font-weight: 600; color: #374151;">License Photo *</label>
                                        <div
                                            style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                            <input type="file" name="license_photo" id="license_photo_file" accept="image/*">
                                            <input type="file" name="license_photo_camera" id="license_photo_camera" accept="image/*"
                                                capture="environment">
                                        </div>
                                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                                            <label for="license_photo_camera" id="license_camera_label" class="btn"
                                                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 10px 16px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3); cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                Camera</label>
                                            <label for="license_photo_file" class="btn"
                                                style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: white; padding: 10px 16px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3); cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                Upload</label>
                                        </div>
                                        <div id="license_photo_preview" style="margin-top: 10px;"></div>
                                    </div>
                                </div>

                                <!-- Section 3: Photos -->
                                <div class="card"
                                    style="margin-bottom: 25px; border-left: 4px solid #8b5cf6; background: linear-gradient(to right, #faf5ff 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #8b5cf6; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            3</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📸 Driver
                                            Photo
                                        </h3>
                                    </div>
                                    <div class="form-group">
                                        <label style="font-weight: 600; color: #374151;">Driver Photo *</label>
                                        <div
                                            style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                            <input type="file" name="driver_photo" id="driver_photo_file" accept="image/*">
                                            <input type="file" name="driver_photo_camera" id="driver_photo_camera" accept="image/*"
                                                capture="user">
                                        </div>
                                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                                            <label for="driver_photo_camera" id="driver_camera_label" class="btn"
                                                style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 10px 16px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3); cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                Camera</label>
                                            <label for="driver_photo_file" class="btn"
                                                style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); color: white; padding: 10px 16px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(124, 58, 237, 0.3); cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                Upload</label>
                                        </div>
                                        <div id="driver_photo_preview" style="margin-top: 10px;"></div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 10px; margin-top: 10px;">
                                    <button type="submit" name="save_driver" class="btn btn-success"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                        Save Driver</button>
                                    <button type="button" onclick="closeDriverForm()" class="btn btn-secondary"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>
                        <?php if (isset($error_msg)): ?>
                                <div class="alert alert-error">
                                    <?php echo $error_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="driverSearch" placeholder="🔍 Search Drivers (Name, Mobile, License...)"
                                oninput="filterTable('driverSearch', 'driversTable')"
                                onsearch="filterTable('driverSearch', 'driversTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper" id="driversTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Driver Name</th>
                                        <th>Mobile</th>
                                        <th>License Number</th>
                                        <th>License Expiry</th>
                                        <th>Transporter</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($driver = mysqli_fetch_assoc($drivers)): ?>
                                            <tr style="cursor: pointer;"
                                                onclick="window.location.href='?page=driver-detail&id=<?php echo $driver['id']; ?>'"
                                                title="Click to view details">
                                                <td><strong>
                                                        <?php echo $driver['driver_name']; ?>
                                                    </strong></td>
                                                <td>
                                                    <?php echo $driver['mobile']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $driver['license_number'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $driver['license_expiry'] ? strtoupper(date('d-M-y', strtotime($driver['license_expiry']))) : '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $driver['transporter_name'] ?: '-'; ?>
                                                </td>
                                                <td onclick="event.stopPropagation();">
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button
                                                                        onclick='editDriver(<?php echo $driver["id"]; ?>, <?php echo json_encode($driver["driver_name"]); ?>, <?php echo json_encode($driver["mobile"]); ?>, <?php echo json_encode($driver["license_number"]); ?>, <?php echo json_encode($driver["license_expiry"]); ?>, <?php echo $driver["transporter_id"] ?: "null"; ?>, <?php echo isset($driver["is_active"]) ? $driver["is_active"] : 1; ?>, <?php echo json_encode($driver["photo"]); ?>, <?php echo json_encode($driver["license_photo"]); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deleteDriver(<?php echo $driver['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <script>
                            function editDriver(id, name, mobile, license, expiry, transId, isActive, photoUrl, licensePhoto) {
                                document.getElementById('driver_id').value = id;
                                document.getElementById('driver_name').value = name;
                                document.getElementById('driver_mobile').value = mobile;
                                document.getElementById('license_number').value = license || '';
                                document.getElementById('license_expiry').value = expiry || '';
                                document.getElementById('driver_transporter_id').value = transId || '';
                                document.getElementById('driver_is_active').value = isActive || '1';
                                document.getElementById('driver_photo_file').removeAttribute('required');
                                document.getElementById('license_photo_file').removeAttribute('required');

                                // Show existing photo previews
                                showAppExistingPreview(photoUrl, 'driver_photo_preview', 'EXISTING PHOTO:', '150px');
                                showAppExistingPreview(licensePhoto, 'license_photo_preview', 'EXISTING LICENSE:', '150px');

                                document.getElementById('driverForm').style.display = 'block';
                                document.getElementById('driverForm').scrollIntoView({ behavior: 'smooth' });
                            }
                            function closeDriverForm() {
                                document.getElementById('driverForm').style.display = 'none';
                                document.getElementById('driver_id').value = '';
                                document.getElementById('driver_photo_file').setAttribute('required', 'required');
                                document.getElementById('license_photo_file').setAttribute('required', 'required');
                                document.getElementById('driver_photo_preview').innerHTML = '';
                                document.getElementById('license_photo_preview').innerHTML = '';
                                document.querySelector('#driverForm form').reset();
                            }

                            function openDriverForm() {
                                closeDriverForm();
                                document.getElementById('driverForm').style.display = 'block';
                                document.getElementById('driverForm').scrollIntoView({ behavior: 'smooth' });
                            }

                            // Handle driver photo uploads (Simplified RELIABLE WEBVIEW FIX)
                            document.getElementById('driver_photo_camera')?.addEventListener('change', function (e) {
                                const file = e.target.files[0];
                                if (file) {
                                    // Swap names and remove required
                                    const mainInput = document.getElementById('driver_photo_file');
                                    e.target.name = "driver_photo";
                                    mainInput.name = "";
                                    mainInput.removeAttribute('required');

                                    // Preview
                                    showAppPreview(file, 'driver_photo_preview', '150px');

                                    // Label feedback
                                    document.getElementById('driver_camera_label').style.background = '#10b981';
                                    document.getElementById('driver_camera_label').innerHTML = '✅ Photo Captured';
                                }
                            });
                            document.getElementById('driver_photo_file')?.addEventListener('change', function (e) {
                                const file = e.target.files[0];
                                if (file) {
                                    // Swap back
                                    e.target.name = "driver_photo";
                                    const cameraInput = document.getElementById('driver_photo_camera');
                                    if (cameraInput) cameraInput.name = "";

                                    // Preview
                                    showAppPreview(file, 'driver_photo_preview', '150px');
                                }
                            });


                            // Handle license photo uploads
                            document.getElementById('license_photo_camera')?.addEventListener('change', function (e) {
                                const file = e.target.files[0];
                                if (file) {
                                    // Swap names and remove required
                                    const mainInput = document.getElementById('license_photo_file');
                                    e.target.name = "license_photo";
                                    mainInput.name = "";
                                    mainInput.removeAttribute('required');

                                    // Preview
                                    showAppPreview(file, 'license_photo_preview', '200px');

                                    // Label feedback
                                    document.getElementById('license_camera_label').style.background = '#10b981';
                                    document.getElementById('license_camera_label').innerHTML = '✅ Photo Captured';
                                }
                            });
                            document.getElementById('license_photo_file')?.addEventListener('change', function (e) {
                                const file = e.target.files[0];
                                if (file) {
                                    // Swap back
                                    e.target.name = "license_photo";
                                    const cameraInput = document.getElementById('license_photo_camera');
                                    if (cameraInput) cameraInput.name = "";

                                    // Preview
                                    showAppPreview(file, 'license_photo_preview', '200px');
                                }
                            });

                            // Intercept camera label clicks on desktop to show webcam modal
                            document.addEventListener('DOMContentLoaded', function () {
                                const driverCameraLabel = document.getElementById('driver_camera_label');
                                const licenseCameraLabel = document.getElementById('license_camera_label');

                                if (driverCameraLabel) {
                                    driverCameraLabel.addEventListener('click', function (e) {
                                        if (!detectMobileForWebcam()) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (typeof openWebcamCapture === 'function') {
                                                openWebcamCapture('driver_photo_file', 'updateDriverPhotoWebcam');
                                            }
                                        }
                                    });
                                }

                                if (licenseCameraLabel) {
                                    licenseCameraLabel.addEventListener('click', function (e) {
                                        if (!detectMobileForWebcam()) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (typeof openWebcamCapture === 'function') {
                                                openWebcamCapture('license_photo_file', 'updateLicensePhotoWebcam');
                                            }
                                        }
                                    });
                                }
                            });

                            // Preview wrappers for webcam modal
                            function updateDriverPhotoWebcam(input) {
                                if (input.files && input.files[0]) {
                                    // Ensure name is correct
                                    input.name = "driver_photo";
                                    const cameraInput = document.getElementById('driver_photo_camera');
                                    if (cameraInput) cameraInput.name = "";

                                    showAppPreview(input.files[0], 'driver_photo_preview', '150px');
                                }
                            }
                            function updateLicensePhotoWebcam(input) {
                                if (input.files && input.files[0]) {
                                    // Ensure name is correct
                                    input.name = "license_photo";
                                    const cameraInput = document.getElementById('license_photo_camera');
                                    if (cameraInput) cameraInput.name = "";

                                    showAppPreview(input.files[0], 'license_photo_preview', '200px');
                                }
                            }
                            function deleteDriver(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this driver?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=drivers&delete_driver=' + id;
                                    }
                                });
                            }
                        </script>
                    </div>

                    <?php
                // ========== VEHICLES MASTER ==========
            elseif ($master_page == 'vehicles'):

                // Add driver_id column if it doesn't exist
                $check_col = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'driver_id'");
                if (mysqli_num_rows($check_col) == 0) {
                    mysqli_query($conn, "ALTER TABLE vehicle_master ADD COLUMN driver_id INT NULL AFTER rc_owner_name, ADD FOREIGN KEY (driver_id) REFERENCES driver_master(id) ON DELETE SET NULL");
                }

                // Create vehicle_drivers junction table for multiple drivers per vehicle
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_drivers'");
                if (mysqli_num_rows($check_table) == 0) {
                    mysqli_query($conn, "
                    CREATE TABLE vehicle_drivers (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        vehicle_id INT NOT NULL,
                        driver_id INT NOT NULL,
                        is_primary TINYINT(1) DEFAULT 0,
                        assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (vehicle_id) REFERENCES vehicle_master(id) ON DELETE CASCADE,
                        FOREIGN KEY (driver_id) REFERENCES driver_master(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_vehicle_driver (vehicle_id, driver_id),
                        INDEX idx_vehicle_id (vehicle_id),
                        INDEX idx_driver_id (driver_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");

                    // Migrate existing single driver assignments
                    mysqli_query($conn, "
                    INSERT INTO vehicle_drivers (vehicle_id, driver_id, is_primary)
                    SELECT vm.id, vm.driver_id, 1
                    FROM vehicle_master vm
                    WHERE vm.driver_id IS NOT NULL
                ");
                }

                // Rename registration_date to registration_validity if it exists
                $check_reg_date = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'registration_date'");
                $check_reg_validity = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'registration_validity'");
                if (mysqli_num_rows($check_reg_date) > 0 && mysqli_num_rows($check_reg_validity) == 0) {
                    mysqli_query($conn, "ALTER TABLE vehicle_master CHANGE COLUMN registration_date registration_validity DATE");
                }

                // Add permit_validity column if it doesn't exist
                $check_permit_validity = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'permit_validity'");
                if (mysqli_num_rows($check_permit_validity) == 0) {
                    mysqli_query($conn, "ALTER TABLE vehicle_master ADD COLUMN permit_validity DATE AFTER insurance_validity");
                }

                // Add permit_number column if it doesn't exist
                $check_permit_number = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'permit_number'");
                if (mysqli_num_rows($check_permit_number) == 0) {
                    mysqli_query($conn, "ALTER TABLE vehicle_master ADD COLUMN permit_number VARCHAR(50) AFTER permit_validity");
                }

                // Add photo columns for vehicle documents if they don't exist
                $doc_columns = [
                    'rc_photo' => "ALTER TABLE vehicle_master ADD COLUMN rc_photo VARCHAR(255)",
                    'insurance_photo' => "ALTER TABLE vehicle_master ADD COLUMN insurance_photo VARCHAR(255)",
                    'pollution_photo' => "ALTER TABLE vehicle_master ADD COLUMN pollution_photo VARCHAR(255)",
                    'fitness_photo' => "ALTER TABLE vehicle_master ADD COLUMN fitness_photo VARCHAR(255)",
                    'permit_photo' => "ALTER TABLE vehicle_master ADD COLUMN permit_photo VARCHAR(255)"
                ];

                foreach ($doc_columns as $column => $sql) {
                    $check = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE '$column'");
                    if (mysqli_num_rows($check) == 0) {
                        mysqli_query($conn, $sql);
                    }
                }

                // Handle Delete
                if (isset($_GET['delete_vehicle'])) {
                    $id = (int) $_GET['delete_vehicle'];

                    // First, delete from vehicle_drivers if exists
                    mysqli_query($conn, "DELETE FROM vehicle_drivers WHERE vehicle_id=$id");

                    // Get vehicle number for log
                    $v_res = mysqli_query($conn, "SELECT vehicle_number FROM vehicle_master WHERE id=$id");
                    $v_row = mysqli_fetch_assoc($v_res);
                    $v_num = $v_row['vehicle_number'] ?? 'Unknown';

                    // Then delete the vehicle
                    if (mysqli_query($conn, "DELETE FROM vehicle_master WHERE id=$id")) {
                        logActivity($conn, 'VEHICLE_DELETE', 'Vehicles', "Deleted Vehicle: Number: [$v_num] (ID: $id)");
                        $_SESSION['success_msg'] = "✅ Vehicle deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=vehicles&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting vehicle: " . mysqli_error($conn);
                    }
                }

                if (isset($_POST['save_vehicle'])) {
                    $veh_no = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));
                    $maker = mysqli_real_escape_string($conn, $_POST['maker']);
                    $model = mysqli_real_escape_string($conn, $_POST['model']);
                    $fuel = mysqli_real_escape_string($conn, $_POST['fuel_type']);
                    $reg_validity = $_POST['registration_validity'];
                    $fitness = $_POST['fitness_validity'];
                    $pollution = $_POST['pollution_validity'];
                    $insurance = $_POST['insurance_validity'];
                    $permit_validity = isset($_POST['permit_validity']) && !empty($_POST['permit_validity']) ? $_POST['permit_validity'] : '';
                    $owner = mysqli_real_escape_string($conn, $_POST['rc_owner_name']);

                    // Get selected drivers and primary driver
                    $driver_ids = isset($_POST['driver_ids']) ? $_POST['driver_ids'] : [];
                    $primary_driver_id = isset($_POST['primary_driver']) ? intval($_POST['primary_driver']) : null;

                    // For backward compatibility, set driver_id to primary driver
                    $driver_id = $primary_driver_id ? $primary_driver_id : 'NULL';
                    $trans_id = !empty($_POST['transporter_id']) ? intval($_POST['transporter_id']) : 0;

                    // Handle document photo uploads
                    // Use absolute path for file system, relative path for database
                    $upload_dir_abs = dirname(__DIR__) . '/uploads/vehicle_docs/';
                    $upload_dir_rel = 'uploads/vehicle_docs/';
                    if (!is_dir($upload_dir_abs)) {
                        mkdir($upload_dir_abs, 0777, true);
                    }

                    $doc_photos = [];
                    $doc_types = ['rc_photo', 'insurance_photo', 'pollution_photo', 'fitness_photo', 'permit_photo'];

                    foreach ($doc_types as $doc_type) {
                        $doc_file = null;
                        if (isset($_FILES[$doc_type]) && $_FILES[$doc_type]['size'] > 0) {
                            $doc_file = $_FILES[$doc_type];
                        } elseif (isset($_FILES[$doc_type . '_camera']) && $_FILES[$doc_type . '_camera']['size'] > 0) {
                            $doc_file = $_FILES[$doc_type . '_camera'];
                        }

                        if ($doc_file && $doc_file['error'] == 0) {
                            $ext = pathinfo($doc_file['name'], PATHINFO_EXTENSION);
                            $filename = $doc_type . '_' . time() . '_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($doc_file['tmp_name'], $upload_dir_abs . $filename)) {
                                $doc_photos[$doc_type] = $upload_dir_rel . $filename;
                            }
                        }
                    }

                    try {
                        if ($_POST['vehicle_id']) {
                            // Update existing vehicle
                            $id = (int) $_POST['vehicle_id'];
                            $current_res = mysqli_query($conn, "SELECT * FROM vehicle_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);

                            if ($current) {
                                $changes_list = auditDiff($current, $_POST, [], [
                                    'maker' => 'Maker',
                                    'model' => 'Model',
                                    'fuel_type' => 'Fuel',
                                    'registration_validity' => 'Reg',
                                    'fitness_validity' => 'Fit',
                                    'pollution_validity' => 'Poll',
                                    'insurance_validity' => 'Ins',
                                    'permit_validity' => 'Permit',
                                    'rc_owner_name' => 'Owner'
                                ]);
                                $changes = $changes_list ? explode("\n", $changes_list) : [];

                                if ($current['driver_id'] != $driver_id)
                                    $changes[] = "Primary Driver: [Updated]";

                                if ($current['transporter_id'] != ($trans_id > 0 ? $trans_id : null)) {
                                    $old_tname = 'None';
                                    if ($current['transporter_id']) {
                                        $otq = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=" . $current['transporter_id']);
                                        if ($otr = mysqli_fetch_assoc($otq))
                                            $old_tname = $otr['transporter_name'];
                                    }
                                    $new_tname = 'None';
                                    if ($trans_id > 0) {
                                        $ntq = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$trans_id");
                                        if ($ntr = mysqli_fetch_assoc($ntq))
                                            $new_tname = $ntr['transporter_name'];
                                    }
                                    $changes[] = "Transporter: [$old_tname ➔ $new_tname]";
                                }

                                $sql = "UPDATE vehicle_master SET maker='$maker', model='$model', fuel_type='$fuel', 
                                registration_validity='$reg_validity', fitness_validity='$fitness', pollution_validity='$pollution',
                                insurance_validity='$insurance', permit_validity=" . ($permit_validity ? "'$permit_validity'" : 'NULL') . ",
                                rc_owner_name='$owner', transporter_id=" . ($trans_id > 0 ? $trans_id : 'NULL') . ", driver_id=$driver_id";

                                // Add photo updates if uploaded
                                foreach ($doc_photos as $col => $path) {
                                    $sql .= ", $col='$path'";
                                    $changes[] = ucfirst(str_replace('_', ' ', $col)) . ": [Updated]";
                                }

                                $sql .= " WHERE id=$id";

                                if (mysqli_query($conn, $sql)) {
                                    $details = "Updated Vehicle: Number: [$veh_no] (ID: [$id])";
                                    if (!empty($changes)) {
                                        $details .= "\nChanges:\n" . implode("\n", $changes);
                                    }
                                    logActivity($conn, 'VEHICLE_UPDATE', 'Vehicles', $details);

                                    // Update vehicle_drivers table
                                    mysqli_query($conn, "DELETE FROM vehicle_drivers WHERE vehicle_id=$id");
                                    foreach ($driver_ids as $did) {
                                        $did = intval($did);
                                        $is_primary = ($did == $primary_driver_id) ? 1 : 0;
                                        mysqli_query($conn, "INSERT INTO vehicle_drivers (vehicle_id, driver_id, is_primary) VALUES ($id, $did, $is_primary)");
                                    }

                                    $success_msg = "✅ Vehicle updated successfully!";
                                    $_SESSION['success_msg'] = $success_msg;
                                    session_write_close();
                                    header("Location: ?page=admin&master=vehicles&t=" . time());
                                    exit;
                                } else {
                                    $error_msg = "❌ Error updating vehicle: " . mysqli_error($conn);
                                }
                            } else {
                                $error_msg = "❌ Vehicle not found!";
                            }
                        } else {
                            // Check if vehicle number already exists
                            $check = mysqli_query($conn, "SELECT id FROM vehicle_master WHERE vehicle_number='$veh_no'");
                            if (mysqli_num_rows($check) > 0) {
                                $error_msg = "❌ Vehicle number <strong>$veh_no</strong> already exists! Please use edit to update it.";
                                $keep_vehicle_form_open = true; // Flag to keep form open with data
                            } else {
                                // Insert new vehicle
                                $cols = "vehicle_number, maker, model, fuel_type, registration_validity, fitness_validity, pollution_validity, insurance_validity, permit_validity, rc_owner_name, transporter_id, driver_id, fetch_source";
                                $vals = "'$veh_no', '$maker', '$model', '$fuel', '$reg_validity', '$fitness', '$pollution', '$insurance', " .
                                    ($permit_validity ? "'$permit_validity'" : 'NULL') . ", '$owner', " . ($trans_id > 0 ? $trans_id : 'NULL') . ", $driver_id, 'manual'";

                                // Add photo columns if photos were uploaded
                                foreach ($doc_photos as $col => $path) {
                                    $cols .= ", $col";
                                    $vals .= ", '$path'";
                                }

                                $sql = "INSERT INTO vehicle_master ($cols) VALUES ($vals)";

                                if (mysqli_query($conn, $sql)) {
                                    $new_vehicle_id = mysqli_insert_id($conn);

                                    // Get Driver Name for better logging
                                    $dname = 'None';
                                    if ($driver_id > 0) {
                                        $dq = mysqli_query($conn, "SELECT driver_name FROM driver_master WHERE id=$driver_id");
                                        $dr = mysqli_fetch_assoc($dq);
                                        $dname = $dr['driver_name'] ?? "ID: $driver_id";
                                    }

                                    // Get Transporter Name for better logging
                                    $tname = 'None';
                                    if ($trans_id > 0) {
                                        $tq = mysqli_query($conn, "SELECT transporter_name FROM transporter_master WHERE id=$trans_id");
                                        if ($t_row = mysqli_fetch_assoc($tq)) {
                                            $tname = $t_row['transporter_name'];
                                        }
                                    }

                                    $details = "Created Vehicle:\n" . auditFromPost($_POST, ['driver_id', 'driver_ids', 'primary_driver_id', 'transporter_id'], ['vehicle_number' => 'Number', 'maker' => 'Maker', 'model' => 'Model', 'fuel_type' => 'Fuel']);
                                    $details .= "\nTransporter: [$tname]";
                                    $details .= "\nPrimary Driver: [$dname]";

                                    // Log uploaded documents
                                    $docs_uploaded = array_keys($doc_photos);
                                    if (!empty($docs_uploaded)) {
                                        $details .= "\nDocuments: [" . implode(", ", array_map(function ($d) {
                                            return str_replace('_photo', '', strtoupper($d));
                                        }, $docs_uploaded)) . "] Uploaded";
                                    }

                                    logActivity($conn, 'VEHICLE_CREATE', 'Vehicles', $details);

                                    // Insert driver assignments into vehicle_drivers table
                                    foreach ($driver_ids as $did) {
                                        $did = intval($did);
                                        $is_primary = ($did == $primary_driver_id) ? 1 : 0;
                                        mysqli_query($conn, "INSERT INTO vehicle_drivers (vehicle_id, driver_id, is_primary) VALUES ($new_vehicle_id, $did, $is_primary)");
                                    }

                                    $success_msg = "✅ Vehicle added successfully!";

                                    $_SESSION['success_msg'] = $success_msg;
                                    session_write_close();
                                    header("Location: ?page=admin&master=vehicles&t=" . time());
                                    exit;
                                } else {
                                    $error_msg = "❌ Error adding vehicle: " . mysqli_error($conn);
                                    $keep_vehicle_form_open = true; // Keep form open on error
                                }
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: Vehicle number '$veh_no' already exists in the system!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                        }
                        $keep_vehicle_form_open = true;
                    }
                }

                $vehicles = mysqli_query($conn, "SELECT v.*, d.driver_name, d.mobile as driver_mobile, t.transporter_name FROM vehicle_master v 
                                             LEFT JOIN driver_master d ON v.driver_id = d.id 
                                             LEFT JOIN transporter_master t ON v.transporter_id = t.id 
                                             ORDER BY v.created_at DESC");
                $drivers_list = mysqli_query($conn, "SELECT id, driver_name, mobile FROM driver_master WHERE is_active=1 ORDER BY driver_name");
                $trans_list = mysqli_query($conn, "SELECT id, transporter_name FROM transporter_master WHERE is_active=1 ORDER BY transporter_name");
                ?>
                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(139, 92, 246, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🚛</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Vehicles Master</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage vehicle
                                    details,
                                    documents, and assigned drivers</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button onclick="openVehicleForm()" class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            ➕ Add New Vehicle
                        </button>

                        <div id="vehicleForm" style="display: none; margin-bottom: 20px;">
                            <form method="POST" enctype="multipart/form-data" onsubmit="showAppLoader('Saving Vehicle Data...')">
                                <input type="hidden" name="vehicle_id" id="vehicle_id">

                                <!-- Section 1: Vehicle Basic Information -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4:px solid #8b5cf6; background: linear-gradient(to right, #f3e8ff 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #8b5cf6; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            1</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">🚛 Vehicle
                                            Basic
                                            Information</h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Vehicle Number *</label>
                                            <input type="text" name="vehicle_number" id="vehicle_number"
                                                style="text-transform: uppercase; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';" required>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Transporter *</label>
                                            <select name="transporter_id" id="veh_transporter_id" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <option value="">-- Tag Transporter --</option>
                                                <?php
                                                mysqli_data_seek($trans_list, 0);
                                                while ($t = mysqli_fetch_assoc($trans_list)): ?>
                                                        <option value="<?php echo $t['id']; ?>">
                                                            <?php echo $t['transporter_name']; ?>
                                                        </option>
                                                        <?php
                                                endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Maker *</label>
                                            <input type="text" name="maker" id="veh_maker" placeholder="e.g., TATA, Ashok Leyland"
                                                required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Model *</label>
                                            <input type="text" name="model" id="veh_model" placeholder="e.g., LPT 1613" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Fuel Type *</label>
                                            <select name="fuel_type" id="fuel_type" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <option value="">-- Select Fuel Type --</option>
                                                <option>DIESEL</option>
                                                <option>PETROL</option>
                                                <option>CNG</option>
                                                <option>ELECTRIC</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">RC Owner Name *</label>
                                            <input type="text" name="rc_owner_name" id="rc_owner_name" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Assigned Drivers -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #10b981; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            2</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">👨‍✈️
                                            Assigned
                                            Drivers
                                        </h3>
                                    </div>
                                    <div class="form-group">
                                        <label style="font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">Select
                                            Drivers</label>
                                        <div id="drivers_container"
                                            style="border: 1px solid #d1d5db; border-radius: 8px; padding: 15px; background: white; min-height: 100px; max-height: 200px; overflow-y: auto;">
                                            <?php
                                            $drivers_list_array = [];
                                            while ($drv = mysqli_fetch_assoc($drivers_list)) {
                                                $drivers_list_array[] = $drv;
                                            }
                                            foreach ($drivers_list_array as $drv):
                                                ?>
                                                    <label
                                                        style="display: flex; align-items: center; padding: 8px; margin: 5px 0; background: #f9fafb; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                                                        onmouseover="this.style.background='#e5e7eb'"
                                                        onmouseout="this.style.background='#f9fafb'">
                                                        <input type="checkbox" name="driver_ids[]" value="<?php echo $drv['id']; ?>"
                                                            style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                                                        <span style="flex: 1;">
                                                            <strong>
                                                                <?php echo $drv['driver_name']; ?>
                                                            </strong>
                                                            <small style="color: #666; margin-left: 10px;">(
                                                                <?php echo $drv['mobile']; ?>)
                                                            </small>
                                                        </span>
                                                        <label
                                                            style="margin-left: 10px; font-size: 12px; color: #666; display: flex; align-items: center; margin-bottom: 0;">
                                                            <input type="radio" name="primary_driver" value="<?php echo $drv['id']; ?>"
                                                                style="margin-right: 5px; width: 14px; height: 14px;" disabled>
                                                            Primary
                                                        </label>
                                                    </label>
                                                    <?php
                                            endforeach; ?>
                                        </div>
                                        <small style="color: #666; margin-top: 5px; display: block;">✓ Select multiple
                                            drivers,
                                            mark
                                            one
                                            as primary</small>
                                    </div>
                                </div>

                                <!-- Section 3: Document Validity -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #f59e0b; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            3</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📅
                                            Document
                                            Validity
                                        </h3>
                                    </div>
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Registration Validity *</label>
                                            <input type="date" name="registration_validity" id="registration_validity" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Fitness Validity *</label>
                                            <input type="date" name="fitness_validity" id="fitness_validity" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Pollution Validity *</label>
                                            <input type="date" name="pollution_validity" id="pollution_validity" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Insurance Validity *</label>
                                            <input type="date" name="insurance_validity" id="insurance_validity" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Permit Validity</label>
                                            <input type="date" name="permit_validity" id="permit_validity"
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 4: Document Photos -->
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #ec4899; background: linear-gradient(to right, #fdf2f8 0%, white 10%);">
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                        <div
                                            style="background: #ec4899; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                                            4</div>
                                        <h3 style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 700;">📄 Upload
                                            Document
                                            Photos</h3>
                                    </div>
                                    <div class="master-form-grid">

                                        <!-- RC Photo -->
                                        <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: white;">
                                            <label style="font-weight: 500; display: block; margin-bottom: 8px;">🆔 RC
                                                Certificate</label>
                                            <div
                                                style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                                <input type="file" name="rc_photo" id="rc_photo_file" accept="image/*">
                                                <input type="file" name="rc_photo_camera" id="rc_photo_camera" accept="image/*"
                                                    capture="environment">
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <label for="rc_photo_camera" id="rc_camera_label" class="btn"
                                                    style="background: #3b82f6; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                    Camera</label>
                                                <label for="rc_photo_file" class="btn"
                                                    style="background: #6366f1; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                    Gallery</label>
                                            </div>
                                            <span id="rc_photo_name"
                                                style="font-size: 11px; color: #10b981; display: block; margin-top: 5px; font-weight: 600;"></span>
                                        </div>

                                        <!-- Insurance Photo -->
                                        <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: white;">
                                            <label style="font-weight: 500; display: block; margin-bottom: 8px;">🛡️
                                                Insurance</label>
                                            <div
                                                style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                                <input type="file" name="insurance_photo" id="insurance_photo_file" accept="image/*">
                                                <input type="file" name="insurance_photo_camera" id="insurance_photo_camera"
                                                    accept="image/*" capture="environment">
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <label for="insurance_photo_camera" id="insurance_camera_label" class="btn"
                                                    style="background: #10b981; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                    Camera</label>
                                                <label for="insurance_photo_file" class="btn"
                                                    style="background: #059669; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                    Gallery</label>
                                            </div>
                                            <span id="insurance_photo_name"
                                                style="font-size: 11px; color: #10b981; display: block; margin-top: 5px; font-weight: 600;"></span>
                                        </div>

                                        <!-- Pollution Photo -->
                                        <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: white;">
                                            <label style="font-weight: 500; display: block; margin-bottom: 8px;">🌿
                                                Pollution
                                                Certificate</label>
                                            <div
                                                style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                                <input type="file" name="pollution_photo" id="pollution_photo_file" accept="image/*">
                                                <input type="file" name="pollution_photo_camera" id="pollution_photo_camera"
                                                    accept="image/*" capture="environment">
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <label for="pollution_photo_camera" id="pollution_camera_label" class="btn"
                                                    style="background: #f59e0b; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                    Camera</label>
                                                <label for="pollution_photo_file" class="btn"
                                                    style="background: #d97706; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                    Gallery</label>
                                            </div>
                                            <span id="pollution_photo_name"
                                                style="font-size: 11px; color: #10b981; display: block; margin-top: 5px; font-weight: 600;"></span>
                                        </div>

                                        <!-- Fitness Photo -->
                                        <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: white;">
                                            <label style="font-weight: 500; display: block; margin-bottom: 8px;">✅ Fitness
                                                Certificate</label>
                                            <div
                                                style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                                <input type="file" name="fitness_photo" id="fitness_photo_file" accept="image/*">
                                                <input type="file" name="fitness_photo_camera" id="fitness_photo_camera"
                                                    accept="image/*" capture="environment">
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <label for="fitness_photo_camera" id="fitness_camera_label" class="btn"
                                                    style="background: #8b5cf6; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                    Camera</label>
                                                <label for="fitness_photo_file" class="btn"
                                                    style="background: #7c3aed; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                    Gallery</label>
                                            </div>
                                            <span id="fitness_photo_name"
                                                style="font-size: 11px; color: #10b981; display: block; margin-top: 5px; font-weight: 600;"></span>
                                        </div>

                                        <!-- Permit Photo -->
                                        <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: white;">
                                            <label style="font-weight: 500; display: block; margin-bottom: 8px;">📋 Permit
                                                Certificate</label>
                                            <div
                                                style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                                                <input type="file" name="permit_photo" id="permit_photo_file" accept="image/*">
                                                <input type="file" name="permit_photo_camera" id="permit_photo_camera" accept="image/*"
                                                    capture="environment">
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <label for="permit_photo_camera" id="permit_camera_label" class="btn"
                                                    style="background: #ec4899; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📷
                                                    Camera</label>
                                                <label for="permit_photo_file" class="btn"
                                                    style="background: #db2777; color: white; padding: 8px 12px; font-size: 12px; flex: 1; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">📁
                                                    Gallery</label>
                                            </div>
                                            <span id="permit_photo_name"
                                                style="font-size: 11px; color: #10b981; display: block; margin-top: 5px; font-weight: 600;"></span>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" name="save_vehicle" class="btn btn-success">💾 Save</button>
                                    <button type="button" onclick="closeVehicleForm()" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>

                            <script>
                                // Handle driver checkbox and primary driver selection
                                document.addEventListener('DOMContentLoaded', function () {
                                    const driverCheckboxes = document.querySelectorAll('input[name="driver_ids[]"]');
                                    const primaryRadios = document.querySelectorAll('input[name="primary_driver"]');

                                    driverCheckboxes.forEach(function (checkbox) {
                                        checkbox.addEventListener('change', function () {
                                            const driverId = this.value;
                                            const primaryRadio = document.querySelector('input[name="primary_driver"][value="' + driverId + '"]');

                                            if (this.checked) {
                                                // Enable primary radio when driver is selected
                                                primaryRadio.disabled = false;
                                                // Auto-select as primary if it's the first driver checked
                                                const checkedDrivers = document.querySelectorAll('input[name="driver_ids[]"]:checked');
                                                if (checkedDrivers.length === 1) {
                                                    primaryRadio.checked = true;
                                                }
                                            } else {
                                                // Disable and uncheck primary radio when driver is unselected
                                                primaryRadio.disabled = true;
                                                primaryRadio.checked = false;
                                                // If this was primary, select another one as primary
                                                const checkedDrivers = document.querySelectorAll('input[name="driver_ids[]"]:checked');
                                                if (checkedDrivers.length > 0 && !document.querySelector('input[name="primary_driver"]:checked')) {
                                                    const firstChecked = checkedDrivers[0];
                                                    document.querySelector('input[name="primary_driver"][value="' + firstChecked.value + '"]').checked = true;
                                                }
                                            }
                                        });
                                    });
                                });
                            </script>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>
                        <?php if (isset($error_msg)): ?>
                                <div class="alert alert-error">
                                    <?php echo $error_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <?php if (isset($keep_vehicle_form_open) && $keep_vehicle_form_open): ?>
                                <script>
                                    // Keep form open and populate with submitted data
                                    document.addEventListener('DOMContentLoaded', function () {
                                        document.getElementById('vehicleForm').style.display = 'block';
                                        document.getElementById('vehicle_number').value = '<?php echo addslashes($_POST['vehicle_number']); ?>';
                                        document.getElementById('vehicle_driver_id').value = '<?php echo $_POST['driver_id']; ?>';
                                        document.getElementById('veh_maker').value = '<?php echo addslashes($_POST['maker']); ?>';
                                        document.getElementById('veh_model').value = '<?php echo addslashes($_POST['model']); ?>';
                                        document.getElementById('fuel_type').value = '<?php echo $_POST['fuel_type']; ?>';
                                        document.getElementById('registration_validity').value = '<?php echo $_POST['registration_validity']; ?>';
                                        document.getElementById('fitness_validity').value = '<?php echo $_POST['fitness_validity']; ?>';
                                        document.getElementById('pollution_validity').value = '<?php echo $_POST['pollution_validity']; ?>';
                                        document.getElementById('insurance_validity').value = '<?php echo $_POST['insurance_validity']; ?>';
                                        document.getElementById('rc_owner_name').value = '<?php echo addslashes($_POST['rc_owner_name']); ?>';
                                        // Scroll to form
                                        document.getElementById('vehicleForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    });
                                </script>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="vehicleSearch" placeholder="🔍 Search Vehicles (Number, Type, Transporter...)"
                                oninput="filterTable('vehicleSearch', 'vehiclesTable')"
                                onsearch="filterTable('vehicleSearch', 'vehiclesTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper" id="vehiclesTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Vehicle Number</th>
                                        <th>Transporter</th>
                                        <th>Primary Driver</th>
                                        <th>Maker/Model</th>
                                        <th>Fuel Type</th>
                                        <th>Fitness</th>
                                        <th>Pollution</th>
                                        <th>Insurance</th>
                                        <th>Permit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($veh = mysqli_fetch_assoc($vehicles)): ?>
                                            <tr style="cursor: pointer;"
                                                onclick="window.location.href='?page=vehicle-detail&id=<?php echo $veh['id']; ?>'"
                                                title="Click to view details">
                                                <td><strong>
                                                        <?php echo $veh['vehicle_number']; ?>
                                                    </strong></td>
                                                <td>
                                                    <span style="font-weight: 600; color: #4f46e5;">
                                                        <?php echo $veh['transporter_name'] ?: '<span style="color:#94a3b8">Untagged</span>'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $veh['driver_name'] ? $veh['driver_name'] . '<br><small>' . $veh['driver_mobile'] . '</small>' : '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $veh['maker'] ? $veh['maker'] . ' ' . $veh['model'] : '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $veh['fuel_type'] ?: '-'; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($veh['fitness_validity']) {
                                                        $is_expired = strtotime($veh['fitness_validity']) < time();
                                                        echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                                                        echo date('d/m/Y', strtotime($veh['fitness_validity']));
                                                        echo '</span>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($veh['pollution_validity']) {
                                                        $is_expired = strtotime($veh['pollution_validity']) < time();
                                                        echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                                                        echo date('d/m/Y', strtotime($veh['pollution_validity']));
                                                        echo '</span>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($veh['insurance_validity']) {
                                                        $is_expired = strtotime($veh['insurance_validity']) < time();
                                                        echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                                                        echo date('d/m/Y', strtotime($veh['insurance_validity']));
                                                        echo '</span>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (isset($veh['permit_validity']) && $veh['permit_validity']) {
                                                        $is_expired = strtotime($veh['permit_validity']) < time();
                                                        echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                                                        echo date('d/m/Y', strtotime($veh['permit_validity']));
                                                        echo '</span>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td onclick="event.stopPropagation();">
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button
                                                                        onclick='editVehicle(<?php echo $veh["id"]; ?>, <?php echo json_encode($veh["vehicle_number"]); ?>, <?php echo json_encode($veh["maker"]); ?>, <?php echo json_encode($veh["model"]); ?>, <?php echo json_encode($veh["fuel_type"]); ?>, <?php echo json_encode($veh["registration_validity"]); ?>, <?php echo json_encode($veh["fitness_validity"]); ?>, <?php echo json_encode($veh["pollution_validity"]); ?>, <?php echo json_encode($veh["insurance_validity"]); ?>, <?php echo json_encode(isset($veh["permit_validity"]) ? $veh["permit_validity"] : ""); ?>, <?php echo json_encode($veh["rc_owner_name"]); ?>, <?php echo json_encode($veh["rc_photo"]); ?>, <?php echo json_encode($veh["insurance_photo"]); ?>, <?php echo json_encode($veh["pollution_photo"]); ?>, <?php echo json_encode($veh["fitness_photo"]); ?>, <?php echo json_encode($veh["permit_photo"]); ?>, <?php echo $veh["transporter_id"] ?: 0; ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deleteVehicle(<?php echo $veh['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            function editVehicle(id, vehNo, maker, model, fuel, regDate, fitness, pollution, insurance, permitValidity, owner, rcPhoto, insurancePhoto, pollutionPhoto, fitnessPhoto, permitPhoto, transId) {
                                document.getElementById('vehicle_id').value = id;
                                document.getElementById('vehicle_number').value = vehNo;
                                document.getElementById('vehicle_number').readOnly = true;
                                document.getElementById('veh_transporter_id').value = transId || '';
                                document.getElementById('veh_maker').value = maker || '';
                                document.getElementById('veh_model').value = model || '';
                                document.getElementById('fuel_type').value = fuel || '';
                                document.getElementById('registration_validity').value = regDate || '';
                                document.getElementById('fitness_validity').value = fitness || '';
                                document.getElementById('pollution_validity').value = pollution || '';
                                document.getElementById('insurance_validity').value = insurance || '';
                                document.getElementById('permit_validity').value = permitValidity || '';
                                document.getElementById('rc_owner_name').value = owner || '';

                                // Show attached photo previews
                                showAppExistingPreview(rcPhoto, 'rc_photo_name', 'EXISTING RC:');
                                showAppExistingPreview(insurancePhoto, 'insurance_photo_name', 'EXISTING INSURANCE:');
                                showAppExistingPreview(pollutionPhoto, 'pollution_photo_name', 'EXISTING POLLUTION:');
                                showAppExistingPreview(fitnessPhoto, 'fitness_photo_name', 'EXISTING FITNESS:');
                                showAppExistingPreview(permitPhoto, 'permit_photo_name', 'EXISTING PERMIT:');

                                // Reset all driver selections
                                document.querySelectorAll('input[name="driver_ids[]"]').forEach(cb => cb.checked = false);
                                document.querySelectorAll('input[name="primary_driver"]').forEach(radio => {
                                    radio.checked = false;
                                    radio.disabled = true;
                                });

                                // Load assigned drivers via AJAX
                                fetch('get_vehicle_drivers.php?vehicle_id=' + id)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.drivers) {
                                            data.drivers.forEach(driver => {
                                                const checkbox = document.querySelector('input[name="driver_ids[]"][value="' + driver.driver_id + '"]');
                                                const radio = document.querySelector('input[name="primary_driver"][value="' + driver.driver_id + '"]');
                                                if (checkbox) {
                                                    checkbox.checked = true;
                                                    radio.disabled = false;
                                                    if (driver.is_primary == 1) {
                                                        radio.checked = true;
                                                    }
                                                }
                                            });
                                        }
                                    });

                                document.getElementById('vehicleForm').style.display = 'block';
                                document.getElementById('vehicleForm').scrollIntoView({ behavior: 'smooth' });
                            }
                            function closeVehicleForm() {
                                document.getElementById('vehicleForm').style.display = 'none';
                                document.getElementById('vehicle_id').value = '';
                                document.getElementById('vehicle_number').readOnly = false;
                                document.querySelector('#vehicleForm form').reset();
                                // Reset photo indications
                                ['rc_photo_name', 'insurance_photo_name', 'pollution_photo_name', 'fitness_photo_name', 'permit_photo_name'].forEach(id => {
                                    document.getElementById(id).innerHTML = '';
                                });
                                // Reset driver selections
                                document.querySelectorAll('input[name="driver_ids[]"]').forEach(cb => cb.checked = false);
                                document.querySelectorAll('input[name="primary_driver"]').forEach(radio => {
                                    radio.checked = false;
                                    radio.disabled = true;
                                });
                            }
                            function deleteVehicle(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this vehicle?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=vehicles&delete_vehicle=' + id;
                                    }
                                });
                            }

                            // Check if mobile device
                            function isMobileDevice() {
                                return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                            }

                            // Preview wrapper for document webcam modal
                            function updateDocPreview(input) {
                                const docType = input.id.replace('_file', '');
                                if (input.files && input.files[0]) {
                                    // Try targeted preview container first, then fallback to name span
                                    let targetId = docType + '_preview';
                                    if (!document.getElementById(targetId)) targetId = docType + '_name';
                                    showAppPreview(input.files[0], targetId);
                                }
                            }

                            function showAppPreview(file, targetId) {
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    const previewStyle = 'max-width: 120px; max-height: 120px; border-radius: 8px; border: 2px solid #10b981; margin-top: 5px;';
                                    document.getElementById(targetId).innerHTML = '<div style="font-size:10px; color:#10b981; font-weight:700;">NEW CAPTURE:</div><img src="' + e.target.result + '" style="' + previewStyle + '">';
                                };
                                reader.readAsDataURL(file);
                            }

                            // Intercept camera label clicks on desktop to show webcam modal
                            document.addEventListener('DOMContentLoaded', function () {
                                const docTypes = ['rc_photo', 'insurance_photo', 'pollution_photo', 'fitness_photo', 'permit_photo'];
                                docTypes.forEach(function (docType) {
                                    const cameraLabel = document.getElementById(docType.replace('_photo', '_camera_label'));
                                    if (cameraLabel) {
                                        cameraLabel.addEventListener('click', function (e) {
                                            if (!detectMobileForWebcam()) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                if (typeof openWebcamCapture === 'function') {
                                                    openWebcamCapture(docType + '_file', 'updateDocPreview');
                                                }
                                            }
                                        });
                                    }
                                });
                            });

                            // Handle document photo uploads from camera
                            ['rc_photo', 'insurance_photo', 'pollution_photo', 'fitness_photo', 'permit_photo'].forEach(function (docType) {
                                // Camera capture
                                document.getElementById(docType + '_camera')?.addEventListener('change', function (e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        // RELIABLE WEBVIEW FIX: Swap names
                                        const mainInput = document.getElementById(docType + '_file');
                                        e.target.name = docType;
                                        mainInput.name = "";

                                        document.getElementById(docType + '_name').textContent = '✓ Photo captured';
                                        showAppPreview(file, docType + '_name');
                                    }
                                });

                                // File upload
                                document.getElementById(docType + '_file')?.addEventListener('change', function (e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        // RELIABLE WEBVIEW FIX: Swap names back
                                        e.target.name = docType;
                                        const cameraInput = document.getElementById(docType + '_camera');
                                        if (cameraInput) cameraInput.name = "";

                                        document.getElementById(docType + '_name').style.color = '#10b981';
                                        showAppPreview(file, docType + '_name');
                                    }
                                });
                            });

                            function openVehicleForm() {
                                closeVehicleForm();
                                document.getElementById('vehicleForm').style.display = 'block';
                                document.getElementById('vehicleForm').scrollIntoView({ behavior: 'smooth' });
                            }
                        </script>
                    </div>

                    <?php
                // ========== EMPLOYEES MASTER ==========
            elseif ($master_page == 'employees'):
                // Ensure new columns exist
                $new_cols = [
                    'rc_expiry' => "DATE NULL",
                    'license_expiry' => "DATE NULL",
                    'pollution_expiry' => "DATE NULL",
                    'fitness_expiry' => "DATE NULL",
                    'vehicle_type' => "VARCHAR(50) NULL",
                    'email' => "VARCHAR(100) NULL"
                ];
                foreach ($new_cols as $col => $def) {
                    $check = mysqli_query($conn, "SHOW COLUMNS FROM employee_master LIKE '$col'");
                    if (mysqli_num_rows($check) == 0) {
                        mysqli_query($conn, "ALTER TABLE employee_master ADD COLUMN $col $def");
                    }
                }
                $employees = mysqli_query($conn, "SELECT * FROM employee_master ORDER BY employee_name");
                // Fetch departments for dropdown
                $dept_list_result = mysqli_query($conn, "SELECT department_name FROM department_master ORDER BY department_name");
                $dept_options = [];
                if ($dept_list_result) {
                    while ($d = mysqli_fetch_assoc($dept_list_result)) {
                        $dept_options[] = $d['department_name'];
                    }
                }
                ?>
                    <!-- Header -->
                    <div
                        style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">👤</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Employees Management</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage employee
                                    details
                                    and
                                    their associated vehicle numbers</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div style="display: flex; gap: 10px;">
                                <button onclick="document.getElementById('empMasterModal').style.display='flex'; resetEmpForm();"
                                    class="btn btn-primary" style="padding: 12px 24px;">
                                    ➕ Add New Employee
                                </button>
                                <a href="?page=print-employee-qrs" target="_blank" class="btn btn-secondary"
                                    style="padding: 12px 24px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                                    🖨️ Bulk Print QRs
                                </a>
                            </div>

                            <form method="POST" enctype="multipart/form-data"
                                style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 10px; border-radius: 8px;">
                                <span style="font-weight: 600; font-size: 14px; color: #475569;">Import CSV:</span>
                                <input type="file" name="import_file" accept=".csv" required style="font-size: 13px;">
                                <button type="submit" name="import_employees" class="btn btn-sm"
                                    style="background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer;">
                                    📂 Upload
                                </button>
                                <a href="download_sample_csv.php" download="sample_employees.csv"
                                    style="color: #6366f1; font-size: 12px; text-decoration: underline; white-space: nowrap;">
                                    ⬇ Sample CSV
                                </a>
                            </form>
                        </div>

                        <div id="empMasterModal" class="perm-modal-overlay" style="display: none;">
                            <div class="perm-modal-content" style="max-width: 800px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #4f46e5; color: white;">
                                    <h3 id="formTitle" style="margin: 0;">Add New Employee</h3>
                                    <button onclick="document.getElementById('empMasterModal').style.display='none'; resetEmpForm();"
                                        style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
                                </div>
                                <div style="padding: 25px;">
                                    <form method="POST" enctype="multipart/form-data"
                                        onsubmit="showAppLoader('Saving Employee Data...')">
                                        <input type="hidden" name="e_id" id="e_id">
                                        <div class="master-form-grid">
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Employee ID *</label>
                                                <input type="text" name="employee_id" id="master_emp_id" required
                                                    onblur="checkDuplicateEmployeeID(this.value)"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Employee Name *</label>
                                                <input type="text" name="employee_name" id="master_emp_name" required
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Mobile *</label>
                                                <input type="tel" name="mobile" id="master_emp_mobile" pattern="[0-9]{10}"
                                                    minlength="10" maxlength="10" title="Please enter exactly 10 digits" required
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Email *</label>
                                                <input type="email" name="employee_email" id="master_emp_email" required
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Department *</label>
                                                <select name="department" id="master_emp_dept" required
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                                    <option value="">-- Select Department --</option>
                                                    <?php foreach ($dept_options as $dept_name): ?>
                                                            <option value="<?php echo htmlspecialchars($dept_name); ?>">
                                                                <?php echo htmlspecialchars($dept_name); ?>
                                                            </option>
                                                            <?php
                                                    endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Vehicle Number</label>
                                                <input type="text" name="vehicle_number" id="master_emp_vehicle"
                                                    oninput="toggleVehicleDates(this.value)" onblur="checkDuplicateVehicle(this.value)"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; text-transform: uppercase;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Vehicle Type</label>
                                                <select name="vehicle_type" id="master_emp_vehicle_type"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                                    <option value="">-- Select Type --</option>
                                                    <option value="Car">Car</option>
                                                    <option value="Two Wheeler">Two Wheeler</option>
                                                    <option value="Truck">Truck</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Employee Photo</label>
                                                <input type="file" name="employee_photo" accept="image/*"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">RC Expiry Date <span class="v-req"
                                                        style="display:none; color:red;">*</span></label>
                                                <input type="date" name="rc_expiry" id="master_emp_rc"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">License Expiry Date <span class="v-req"
                                                        style="display:none; color:red;">*</span></label>
                                                <input type="date" name="license_expiry" id="master_emp_license"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Pollution Expiry Date <span class="v-req"
                                                        style="display:none; color:red;">*</span></label>
                                                <input type="date" name="pollution_expiry" id="master_emp_pollution"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-weight: 600;">Fitness Expiry (Optional)</label>
                                                <input type="date" name="fitness_expiry" id="master_emp_fitness"
                                                    style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                                            <button type="submit" name="save_employee" class="btn btn-primary"
                                                style="flex: 2; padding: 12px;">Save Employee</button>
                                            <button type="button"
                                                onclick="document.getElementById('empMasterModal').style.display='none'; resetEmpForm();"
                                                class="btn btn-secondary" style="flex: 1; padding: 12px;">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Details View Modal -->
                        <div id="empDetailsModal" class="perm-modal-overlay" style="display: none;">
                            <div class="perm-modal-content" style="max-width: 600px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #1e293b; color: white;">
                                    <h3 style="margin: 0;">📋 Employee Profile Details</h3>
                                    <button onclick="document.getElementById('empDetailsModal').style.display='none'"
                                        style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
                                </div>
                                <div id="empDetailsContent" style="padding: 25px; background: white;">
                                    <!-- Dynamic Content -->
                                </div>
                                <div style="padding: 15px 25px; background: #f8fafc; text-align: right; border-top: 1px solid #e2e8f0;">
                                    <button onclick="document.getElementById('empDetailsModal').style.display='none'"
                                        class="btn btn-secondary" style="padding: 10px 20px;">Close View</button>
                                </div>
                            </div>
                        </div>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="empMasterSearch" placeholder="🔍 Search Employees (Name, ID, Department, Vehicle...)"
                                oninput="filterTable('empMasterSearch', 'employeesTable')"
                                onsearch="filterTable('empMasterSearch', 'employeesTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper" id="employeesTable">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead style="background: #f1f5f9;">
                                    <tr style="text-align: left;">
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Photo</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Emp ID</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Name</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Vehicle</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Department</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">QR Code</th>
                                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                                            <tr style="border-bottom: 1px solid #e2e8f0; cursor: pointer;" title="Click to view details"
                                                onclick='viewEmployeeDetails(<?php echo json_encode($emp); ?>)'>
                                                <td style="padding: 12px; text-align: center;">
                                                    <?php if (!empty($emp['photo'])): ?>
                                                            <img src="uploads/employees/<?php echo $emp['photo']; ?>"
                                                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; cursor: pointer;"
                                                                onclick="showPhotoModal('uploads/employees/<?php echo $emp['photo']; ?>', '<?php echo addslashes($emp['employee_name']); ?>')"
                                                                onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($emp['employee_name']); ?>&background=random'">
                                                            <?php
                                                    else: ?>
                                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($emp['employee_name']); ?>&background=random"
                                                                style="width: 40px; height: 40px; border-radius: 50%; opacity: 0.7;">
                                                            <?php
                                                    endif; ?>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <?php echo htmlspecialchars($emp['employee_id']); ?>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <strong>
                                                        <?php echo htmlspecialchars($emp['employee_name']); ?>
                                                    </strong><br>
                                                    <small style="color: #64748b;">
                                                        <?php echo htmlspecialchars($emp['mobile']); ?>
                                                    </small>
                                                    <br>
                                                    <small style="color: #64748b; font-size: 11px;">
                                                        <?php echo htmlspecialchars($emp['email'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <strong>
                                                        <?php echo htmlspecialchars($emp['vehicle_number']); ?>
                                                    </strong>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <?php echo htmlspecialchars($emp['department']); ?>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <?php
                                                    $emp_qr_data = json_encode([
                                                        'type' => 'employee',
                                                        'id' => $emp['employee_id'],
                                                        'name' => $emp['employee_name'],
                                                        'vehicle' => $emp['vehicle_number']
                                                    ]);
                                                    ?>
                                                    <button
                                                        onclick='event.stopPropagation(); showEmployeeQR(<?php echo json_encode($emp["employee_name"]); ?>, <?php echo json_encode($emp["department"]); ?>, <?php echo json_encode($emp["vehicle_number"]); ?>, <?php echo json_encode($emp_qr_data); ?>)'
                                                        class="btn btn-sm"
                                                        style="background: #8b5cf6; color: white; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; border: none;">🖼️
                                                        QR Code</button>
                                                </td>
                                                <td style="padding: 12px;">
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button
                                                                        onclick='event.stopPropagation(); editEmployeeMaster(<?php echo $emp["id"]; ?>, <?php echo json_encode($emp["employee_id"]); ?>, <?php echo json_encode($emp["employee_name"]); ?>, <?php echo json_encode($emp["mobile"]); ?>, <?php echo json_encode($emp["email"] ?? ""); ?>, <?php echo json_encode($emp["department"]); ?>, <?php echo json_encode($emp["vehicle_number"]); ?>, <?php echo json_encode($emp["vehicle_type"] ?? ""); ?>, <?php echo json_encode($emp["rc_expiry"] ?? ""); ?>, <?php echo json_encode($emp["license_expiry"] ?? ""); ?>, <?php echo json_encode($emp["pollution_expiry"] ?? ""); ?>, <?php echo json_encode($emp["fitness_expiry"] ?? ""); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; border: none; font-weight: 600;">Edit
                                                                        Profile</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="event.stopPropagation(); deleteEmployee(<?php echo $emp['id']; ?>)"
                                                                        class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; border: none; margin-left: 5px;">Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            function editEmployeeMaster(id, emp_id, name, mobile, email, dept, vehicle, type, rc, license, pollution, fitness) {
                                document.getElementById('e_id').value = id;
                                document.getElementById('master_emp_id').value = emp_id;
                                document.getElementById('master_emp_name').value = name;
                                document.getElementById('master_emp_mobile').value = mobile;
                                document.getElementById('master_emp_email').value = email || '';
                                document.getElementById('master_emp_dept').value = dept;
                                document.getElementById('master_emp_vehicle').value = vehicle;

                                document.getElementById('master_emp_vehicle_type').value = type || '';
                                document.getElementById('master_emp_rc').value = rc || '';
                                document.getElementById('master_emp_license').value = license || '';
                                document.getElementById('master_emp_pollution').value = pollution || '';
                                document.getElementById('master_emp_fitness').value = fitness || '';

                                toggleVehicleDates(vehicle || ""); // Set mandatory requirements based on vehicle

                                document.getElementById('formTitle').textContent = 'Edit Employee';
                                document.getElementById('empMasterModal').style.display = 'flex';
                            }

                            function viewEmployeeDetails(emp) {
                                const photo = emp.photo ? 'uploads/employees/' + emp.photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.employee_name);
                                const content = `
                                    <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                        <img src="${photo}" style="width: 100px; height: 100px; border-radius: 12px; object-fit: cover; border: 3px solid #e2e8f0;">
                                        <div>
                                            <h2 style="margin: 0; color: #1e293b;">${emp.employee_name}</h2>
                                            <p style="margin: 5px 0; color: #64748b; font-weight: 600;">ID: ${emp.employee_id}</p>
                                            <span style="background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">${emp.department}</span>
                                        </div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label style="display: block; font-size: 12px; color: #94a3b8; text-transform: uppercase;">Contact info</label>
                                            <p style="margin: 5px 0; font-weight: 600; color: #334155;">📞 ${emp.mobile}</p>
                                            <p style="margin: 5px 0; font-weight: 600; color: #334155;">✉️ ${emp.email || 'N/A'}</p>
                                            <p style="margin: 5px 0; font-weight: 600; color: #334155;">🏢 Dept: ${emp.department}</p>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 12px; color: #94a3b8; text-transform: uppercase;">Vehicle</label>
                                            <p style="margin: 5px 0; font-weight: 600; color: #334155;">🚚 ${emp.vehicle_number}</p>
                                            <p style="margin: 5px 0; color: #64748b; font-size: 13px;">Type: ${emp.vehicle_type || 'N/A'}</p>
                                        </div>
                                            <div>
                                                <small style="color: #64748b;">RC Expiry:</small><br>
                                                <strong>${formatDateJS(emp.rc_expiry)}</strong>
                                            </div>
                                            <div>
                                                <small style="color: #64748b;">License Expiry:</small><br>
                                                <strong>${formatDateJS(emp.license_expiry)}</strong>
                                            </div>
                                            <div>
                                                <small style="color: #64748b;">Pollution Expiry:</small><br>
                                                <strong>${formatDateJS(emp.pollution_expiry)}</strong>
                                            </div>
                                            <div>
                                                <small style="color: #64748b;">Fitness Expiry:</small><br>
                                                <strong>${formatDateJS(emp.fitness_expiry)}</strong>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                document.getElementById('empDetailsContent').innerHTML = content;
                                document.getElementById('empDetailsModal').style.display = 'flex';
                            }

                            function formatDateJS(dateStr) {
                                if (!dateStr || dateStr === '0000-00-00' || dateStr === 'NULL')
                                    return 'N/A';
                                const d = new Date(dateStr);
                                if (isNaN(d.getTime()))
                                    return dateStr;
                                const day = String(d.getDate()).padStart(2, '0');
                                const monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
                                const month = monthNames[d.getMonth()];
                                const year = String(d.getFullYear()).slice(-2);
                                return `${day}-${month}-${year}`;
                            }

                            function toggleVehicleDates(vehicleValue) {
                                const isMandatory = vehicleValue.trim() !== "";
                                const dateFields = ['master_emp_rc', 'master_emp_license', 'master_emp_pollution'];
                                const starIcons = document.querySelectorAll('.v-req');

                                dateFields.forEach(id => {
                                    const el = document.getElementById(id);
                                    if (el) {
                                        el.required = isMandatory;
                                    }
                                });

                                starIcons.forEach(icon => {
                                    icon.style.display = isMandatory ? 'inline' : 'none';
                                });
                            }

                            function showPhotoModal(src, name) {
                                event.stopPropagation();
                                Swal.fire({
                                    title: name,
                                    imageUrl: src,
                                    imageAlt: name,
                                    imageWidth: 400,
                                    showCloseButton: true,
                                    showConfirmButton: false,
                                    background: '#f8fafc'
                                });
                            }

                            function checkDuplicateVehicle(v) {
                                if (!v) return;
                                const idField = document.getElementById('e_id');
                                const currentId = idField ? idField.value : 0;
                                fetch(`?page=check-duplicate-vehicle&vehicle=${encodeURIComponent(v)}&id=${currentId}`)
                                    .then(r => r.json()).then(data => {
                                        if (data.exists) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Duplicate Vehicle',
                                                text: 'Vehicle number "' + v + '" is already assigned to ' + data.name + '.'
                                            }).then(() => {
                                                const vInput = document.getElementById('master_emp_vehicle');
                                                if (vInput) {
                                                    vInput.value = '';
                                                    vInput.focus();
                                                }
                                            });
                                        }
                                    });
                            }

                            function checkDuplicateEmployeeID(empId) {
                                if (!empId) return;
                                const currentId = (document.getElementById('e_id') || { value: 0 }).value;
                                fetch(`?page=check-duplicate-employee-id&emp_id=${encodeURIComponent(empId)}&id=${currentId}`)
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.exists) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Duplicate ID Found',
                                                text: 'Employee ID "' + empId + '" is already assigned to ' + data.name + '.',
                                                confirmButtonColor: '#4f46e5'
                                            }).then(() => {
                                                const el = document.getElementById('master_emp_id');
                                                if (el) { el.value = ''; el.focus(); }
                                            });
                                        }
                                    });
                            }

                            function resetEmpForm() {
                                document.getElementById('e_id').value = '';
                                document.getElementById('master_emp_id').value = '';
                                document.getElementById('master_emp_name').value = '';
                                document.getElementById('master_emp_mobile').value = '';
                                document.getElementById('master_emp_email').value = '';
                                document.getElementById('master_emp_dept').value = '';
                                document.getElementById('master_emp_vehicle').value = '';

                                document.getElementById('master_emp_vehicle_type').value = '';
                                document.getElementById('master_emp_rc').value = '';
                                document.getElementById('master_emp_license').value = '';
                                document.getElementById('master_emp_pollution').value = '';
                                document.getElementById('master_emp_fitness').value = '';

                                toggleVehicleDates(""); // Reset mandatory requirements

                                document.getElementById('formTitle').textContent = 'Add New Employee';
                            }

                            function deleteEmployee(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this employee?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=employees&delete_employee=' + id;
                                    }
                                });
                            }

                            function showEmployeeQR(name, dept, vehicle, data) {
                                document.getElementById('qrEmpName').textContent = name;
                                document.getElementById('qrEmpDept').textContent = dept ? dept : 'No Department';
                                document.getElementById('qrEmpVehicle').textContent = vehicle ? vehicle : 'No Vehicle';

                                document.getElementById('qrcode').innerHTML = '';
                                new QRCode(document.getElementById("qrcode"), {
                                    text: data,
                                    width: 200,
                                    height: 200,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                                document.getElementById('empQRModal').style.display = 'block';
                            }
                        </script>

                        <!-- Employee QR Modal -->
                        <div id="empQRModal"
                            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10003; overflow-y: auto;">
                            <div
                                style="position: relative; max-width: 400px; margin: 50px auto; background: white; border-radius: 16px; padding: 30px; text-align: center;">
                                <h3 id="qrEmpName" style="margin-top: 0; margin-bottom: 5px; font-size: 22px;">Employee Name
                                </h3>
                                <p style="margin: 0 0 5px 0; color: #64748b; font-size: 14px;"><strong>Dept:</strong> <span
                                        id="qrEmpDept"></span></p>
                                <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px;"><strong>Vehicle:</strong>
                                    <span id="qrEmpVehicle"></span>
                                </p>
                                <div id="qrcode" style="display: flex; justify-content: center; margin-bottom: 25px;"></div>
                                <p style="color: #64748b; font-size: 13px; margin-bottom: 25px;">Scan this code at the gate
                                    for
                                    quick
                                    entry/exit</p>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="window.print()" class="btn btn-primary" style="flex: 1;">🖨️
                                        Print</button>
                                    <button onclick="document.getElementById('empQRModal').style.display='none'"
                                        class="btn btn-secondary" style="flex: 1;">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                // ========== PATROL LOCATIONS MASTER ==========
            elseif ($master_page == 'patrol-locations'):

                // Handle Delete
                if (isset($_GET['delete_patrol_location'])) {
                    $id = (int) $_GET['delete_patrol_location'];
                    $loc_q = mysqli_query($conn, "SELECT location_name FROM patrol_locations WHERE id=$id");
                    $loc_row = mysqli_fetch_assoc($loc_q);
                    $loc_name = $loc_row['location_name'] ?? 'Unknown';

                    if (mysqli_query($conn, "DELETE FROM patrol_locations WHERE id=$id")) {
                        logActivity($conn, 'PATROL_DELETE', 'Patrol', "Deleted patrol location: ['$loc_name'] (ID: [$id])");
                        $_SESSION['success_msg'] = "✅ Patrol location deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=patrol-locations&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting location: " . mysqli_error($conn);
                    }
                }

                // Handle Save
                if (isset($_POST['save_patrol_location'])) {
                    $loc_id = mysqli_real_escape_string($conn, $_POST['location_id']);
                    $name = mysqli_real_escape_string($conn, $_POST['location_name']);
                    $area = mysqli_real_escape_string($conn, $_POST['area_site_building']);
                    $qr_data = mysqli_real_escape_string($conn, $_POST['qr_code_data'] ?: $loc_id);

                    try {
                        if ($_POST['p_id']) {
                            $id = (int) $_POST['p_id'];
                            $current_res = mysqli_query($conn, "SELECT * FROM patrol_locations WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);
                            $changes = [];
                            if ($current) {
                                if (trim($current['location_id']) != trim($loc_id))
                                    $changes[] = "LocID: [{$current['location_id']} -> $loc_id]";
                                if (trim($current['location_name']) != trim($name))
                                    $changes[] = "Name: [{$current['location_name']} -> $name]";
                                if (trim($current['area_site_building']) != trim($area))
                                    $changes[] = "Area: [{$current['area_site_building']} -> $area]";
                            }

                            $sql = "UPDATE patrol_locations SET location_id='$loc_id', location_name='$name', area_site_building='$area', qr_code_data='$qr_data' WHERE id=$id";
                            if (mysqli_query($conn, $sql)) {
                                $diff = auditDiff($current, $_POST, ['p_id'], ['location_id' => 'LocID', 'location_name' => 'Name', 'area_site_building' => 'Area', 'qr_code_data' => 'QR Data']);
                                $details = "Updated Patrol Location: Name: [$name] (ID: [$loc_id])";
                                if (!empty($diff)) {
                                    $details .= "\nChanges:\n" . $diff;
                                }
                                logActivity($conn, 'PATROL_UPDATE', 'Patrol', $details);
                                $_SESSION['success_msg'] = "✅ Patrol location updated successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=patrol-locations&t=" . time());
                                exit;
                            }
                        } else {
                            $sql = "INSERT INTO patrol_locations (location_id, location_name, area_site_building, qr_code_data) VALUES ('$loc_id', '$name', '$area', '$qr_data')";
                            if (mysqli_query($conn, $sql)) {
                                logActivity($conn, 'PATROL_CREATE', 'Patrol', "Created Patrol Location:\n" . auditFromPost($_POST, [], ['location_id' => 'LocID', 'location_name' => 'Name', 'area_site_building' => 'Area']));
                                $_SESSION['success_msg'] = "✅ Patrol location added successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=patrol-locations&t=" . time());
                                exit;
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: Location ID '$loc_id' already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                        }
                    }
                }

                $locations = mysqli_query($conn, "SELECT * FROM patrol_locations ORDER BY location_name");
                ?>
                    <!-- Header -->
                    <div
                        style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📍</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Patrol QR Management</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Pre-create and
                                    manage
                                    QR
                                    locations for guard patrols</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button
                            onclick="document.getElementById('patrolLocForm').style.display='block'; document.getElementById('patrolLocForm').scrollIntoView({ behavior: 'smooth' });"
                            class="btn btn-primary" style="margin-bottom: 20px; padding: 12px 24px;">
                            ➕ Add New Location
                        </button>

                        <div id="patrolLocForm" style="display: none; margin-bottom: 20px;">
                            <form method="POST">
                                <input type="hidden" name="p_id" id="p_id">
                                <div class="card" style="border-left: 4px solid #4f46e5; background: #f8fafc; margin-bottom: 20px;">
                                    <div class="master-form-grid">
                                        <div class="form-group"
                                            style="grid-column: span 2; margin-bottom: 5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                            <h4 id="form_title" style="margin:0; color: #4f46e5;">➕ Add New Patrol Location</h4>
                                        </div>
                                        <div class="form-group" id="loc_id_group" style="display:none;">
                                            <label style="font-weight: 600;">Location ID (Auto-generated)</label>
                                            <?php
                                            $new_auto_id = "PAT-" . date('His') . rand(10, 99);
                                            ?>
                                            <input type="text" name="location_id" id="p_location_id" value="<?php echo $new_auto_id; ?>"
                                                style="padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; background: #f1f5f9; width: 100%;"
                                                readonly>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600;">Location Name *</label>
                                            <input type="text" name="location_name" id="p_location_name" required
                                                style="padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; width: 100%;">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600;">Area / Site / Building</label>
                                            <input type="text" name="area_site_building" id="p_area"
                                                placeholder="e.g. Warehouse A, Main Gate"
                                                style="padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; width: 100%;">
                                        </div>
                                        <input type="hidden" name="qr_code_data" id="p_qr_data">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" name="save_patrol_location" class="btn btn-success">💾 Save
                                        Location</button>
                                    <button type="button" onclick="closePatrolLocForm()" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="patrolSearch" placeholder="🔍 Search Patrol Locations (Name, ID, Area...)"
                                oninput="filterTable('patrolSearch', 'patrolLocTable')"
                                onsearch="filterTable('patrolSearch', 'patrolLocTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper">
                            <table id="patrolLocTable">
                                <thead>
                                    <tr>
                                        <th>Location ID</th>
                                        <th>Name</th>
                                        <th>Area/Building</th>
                                        <th>QR Preview</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($loc = mysqli_fetch_assoc($locations)): ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($loc['location_id']); ?></code></td>
                                                <td><strong>
                                                        <?php echo htmlspecialchars($loc['location_name']); ?>
                                                    </strong></td>
                                                <td>
                                                    <?php echo htmlspecialchars($loc['area_site_building']); ?>
                                                </td>
                                                <td>
                                                    <div id="qr_<?php echo $loc['id']; ?>" class="patrol-qr-container"
                                                        style="background: white; padding: 5px; border-radius: 4px; display: inline-block;">
                                                    </div>
                                                    <script>
                                                        new QRCode(document.getElementById("qr_<?php echo $loc['id']; ?>"), {
                                                            text: "<?php echo addslashes($loc['qr_code_data']); ?>",
                                                            width: 80,
                                                            height: 80
                                                        });
                                                    </script>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 5px; min-width: 180px;">
                                                        <button
                                                            onclick="printPatrolQR(<?php echo $loc['id']; ?>, '<?php echo addslashes($loc['location_name']); ?>', '<?php echo addslashes($loc['area_site_building']); ?>', '<?php echo addslashes($loc['qr_code_data']); ?>')"
                                                            class="btn btn-sm" style="background: #10b981; color: white;">🖨️
                                                            Print</button>
                                                        <?php if (hasPermission('actions.view_buttons')): ?>
                                                                <?php if (hasPermission('actions.edit_record')): ?>
                                                                        <button
                                                                            onclick='editPatrolLoc(<?php echo $loc["id"]; ?>, <?php echo json_encode($loc["location_id"]); ?>, <?php echo json_encode($loc["location_name"]); ?>, <?php echo json_encode($loc["area_site_building"]); ?>, <?php echo json_encode($loc["qr_code_data"]); ?>)'
                                                                            class="btn btn-sm" style="background: #4f46e5; color: white;">✏️
                                                                            Edit</button>
                                                                        <?php
                                                                endif; ?>
                                                                <?php if (hasPermission('actions.delete_record')): ?>
                                                                        <button onclick="deletePatrolLoc(<?php echo $loc['id']; ?>)" class="btn btn-sm"
                                                                            style="background: #ef4444; color: white;">🗑️
                                                                            Delete</button>
                                                                        <?php
                                                                endif; ?>
                                                                <?php
                                                        endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <script>
                        function printPatrolQR(id, name, area, qrData) {
                            const printWindow = window.open('', '_blank');
                            printWindow.document.write(`
                                        < html >
                                                                <head>
                                                                    <title>Print Patrol QR - ${name}</title>
                                                                    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js\"><\/script>
                                                                    <style>
                                                                        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                                                                        .qr-card { border: 2px solid #000; padding: 40px; border-radius: 20px; text-align: center; max-width: 400px; }
                                                                        .qr-code { margin: 20px auto; }
                                                                        .loc-name { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
                                                                        .loc-area { font-size: 18px; color: #666; margin-bottom: 20px; }
                                                                        .footer { font-size: 12px; color: #999; margin-top: 20px; }
                                                                        @media print {
                                                                            .no-print { display: none; }
                                                                            body { height: auto; padding: 20px; }
                                                                        }
                                                                    </style>
                                                                </head>
                                                                <body>
                                                                    <div class="qr-card">
                                                                        <div class="loc-name">${name}</div>
                                                                        <div class="loc-area">${area || ''}</div>
                                                                        <div id="print_qr" class="qr-code"></div>
                                                                        <div class="footer">Patrol Location ID: ${qrData}</div>
                                                                        <div class="no-print" style="margin-top: 30px;">
                                                                            <button onclick="window.print()" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">🖨️ PRINT QR CODE</button>
                                                                        </div>
                                                                    </div>
                                                                    <script>
                                                                        new QRCode(document.getElementById("print_qr"), {
                                                                            text: "${qrData}",
                                                                            width: 250,
                                                                            height: 250
                                                                        });
                                                                    <\/script>
                                                                </body>
                                                            </html >
                                                        `);
                            printWindow.document.close();
                        }

                        function editPatrolLoc(id, loc_id, name, area, qr) {
                            document.getElementById('p_id').value = id;
                            document.getElementById('p_location_id').value = loc_id;
                            document.getElementById('p_location_name').value = name;
                            document.getElementById('p_area').value = area;
                            document.getElementById('p_qr_data').value = qr;
                            document.getElementById('loc_id_group').style.display = 'block';
                            document.getElementById('form_title').innerHTML = '✏️ Edit Patrol Location';
                            document.getElementById('patrolLocForm').style.display = 'block';
                            document.getElementById('patrolLocForm').scrollIntoView({ behavior: 'smooth' });
                        }
                        function closePatrolLocForm() {
                            document.getElementById('patrolLocForm').style.display = 'none';
                            document.getElementById('p_id').value = '';
                            document.getElementById('loc_id_group').style.display = 'none';
                            document.getElementById('form_title').innerHTML = '➕ Add New Patrol Location';
                            document.querySelector('#patrolLocForm form').reset();
                        }
                        function deletePatrolLoc(id) {
                            Swal.fire({
                                title: 'Are you sure?',
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, delete it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '?page=admin&master=patrol-locations&delete_patrol_location=' + id + '&t=' + Date.now();
                                }
                            });
                        }
                    </script>

                    <?php
                // ========== PURPOSES MASTER ==========
            elseif ($master_page == 'purposes'):

                // Handle Delete
                if (isset($_GET['delete_purpose'])) {
                    $id = (int) $_GET['delete_purpose'];

                    // Fetch info for log before deletion
                    $p_res = mysqli_query($conn, "SELECT purpose_name, purpose_type FROM purpose_master WHERE id=$id");
                    $p_row = mysqli_fetch_assoc($p_res);
                    $p_name = $p_row['purpose_name'] ?? 'Unknown';
                    $p_type = $p_row['purpose_type'] ?? 'Unknown';

                    // Delete the purpose
                    if (mysqli_query($conn, "DELETE FROM purpose_master WHERE id=$id")) {
                        logActivity($conn, 'PURPOSE_DELETE', 'Purposes', "Deleted Purpose: Name: [$p_name] (ID: [$id])");
                        $_SESSION['success_msg'] = "✅ Purpose deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=purposes&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting purpose: " . mysqli_error($conn);
                    }
                }

                if (isset($_POST['purpose_name'])) {
                    $name = mysqli_real_escape_string($conn, trim($_POST['purpose_name']));
                    $type = mysqli_real_escape_string($conn, $_POST['purpose_type']);
                    $id = isset($_POST['purpose_id']) ? (int) $_POST['purpose_id'] : 0;

                    // Check for duplicate
                    $dup_check = mysqli_query($conn, "SELECT id FROM purpose_master WHERE purpose_name='$name' AND purpose_type='$type' AND id != $id AND is_active=1");
                    if (mysqli_num_rows($dup_check) > 0) {
                        $error_msg = "❌ Error: A purpose with name '$name' and type '$type' already exists!";
                        showAppModal('Error', $error_msg, 'error');
                    } else {
                        if ($id) {
                            $current_res = mysqli_query($conn, "SELECT * FROM purpose_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);

                            $sql = "UPDATE purpose_master SET purpose_name='$name', purpose_type='$type' WHERE id=$id";
                            if (mysqli_query($conn, $sql)) {
                                $diff = auditDiff($current, $_POST, [], ['purpose_name' => 'Name', 'purpose_type' => 'Type']);
                                $details = "Updated Purpose: Name: [$name] (ID: [$id])";
                                if (!empty($diff)) {
                                    $details .= "\nChanges:\n" . $diff;
                                }
                                logActivity($conn, 'PURPOSE_UPDATE', 'Purposes', $details);
                                $_SESSION['success_msg'] = "✅ Purpose saved successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=purposes&t=" . time());
                                exit;
                            }
                        } else {
                            $sql = "INSERT INTO purpose_master (purpose_name, purpose_type) VALUES ('$name', '$type')";
                            if (mysqli_query($conn, $sql)) {
                                logActivity($conn, 'PURPOSE_CREATE', 'Purposes', "Created Purpose:\n" . auditFromPost($_POST, [], ['purpose_name' => 'Name', 'purpose_type' => 'Type']));
                                $_SESSION['success_msg'] = "✅ Purpose saved successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=purposes&t=" . time());
                                exit;
                            }
                        }
                    }
                }

                $purposes = mysqli_query($conn, "SELECT * FROM purpose_master WHERE is_active=1 ORDER BY purpose_name");
                ?>
                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🎯</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Purpose Types</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage purpose
                                    categories
                                    for truck movements</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button onclick="openNewPurposeForm()" class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                            ➕ Add New Purpose
                        </button>

                        <div id="purposeForm" style="display: none; margin-bottom: 20px;">
                            <form method="POST"
                                onsubmit="const btn=this.querySelector('button[type=submit]'); btn.innerHTML='⏳ Saving...'; setTimeout(() => btn.disabled=true, 10);">
                                <input type="hidden" name="purpose_id" id="purpose_id">
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Purpose Name *</label>
                                            <input type="text" name="purpose_name" id="purpose_name" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Purpose Type *</label>
                                            <select name="purpose_type" id="purpose_type" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <option value="">-- Select --</option>
                                                <option value="delivery">Delivery</option>
                                                <option value="pickup">Pickup</option>
                                                <option value="service">Service</option>
                                                <option value="visit">Visit</option>
                                                <option value="return">Return</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" name="save_purpose" class="btn btn-success"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                        Save Purpose</button>
                                    <button type="button" onclick="closePurposeForm()" class="btn btn-secondary"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="purposeSearch" placeholder="🔍 Search Purposes (Name, Type...)"
                                oninput="filterTable('purposeSearch', 'purposesTable')"
                                onsearch="filterTable('purposeSearch', 'purposesTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper">
                            <table id="purposesTable">
                                <thead>
                                    <tr>
                                        <th>Purpose Name</th>
                                        <th>Type</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($purpose = mysqli_fetch_assoc($purposes)): ?>
                                            <tr>
                                                <td><strong>
                                                        <?php echo $purpose['purpose_name']; ?>
                                                    </strong></td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?php echo strtoupper($purpose['purpose_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo strtoupper(date('d-M-y', strtotime($purpose['created_at']))); ?>
                                                </td>
                                                <td>
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button
                                                                        onclick='editPurpose(<?php echo $purpose["id"]; ?>, <?php echo json_encode($purpose["purpose_name"]); ?>, <?php echo json_encode($purpose["purpose_type"]); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deletePurpose(<?php echo $purpose['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>

                            <script>
                                function openNewPurposeForm() {
                                    document.getElementById('purpose_id').value = '';
                                    document.querySelector('#purposeForm form').reset();
                                    document.getElementById('purposeForm').style.display = 'block';
                                    document.getElementById('purposeForm').scrollIntoView({ behavior: 'smooth' });
                                }
                                function editPurpose(id, name, type) {
                                    document.getElementById('purpose_id').value = id;
                                    document.getElementById('purpose_name').value = name;
                                    document.getElementById('purpose_type').value = type;
                                    document.getElementById('purposeForm').style.display = 'block';
                                    document.getElementById('purposeForm').scrollIntoView({ behavior: 'smooth' });
                                }
                                function closePurposeForm() {
                                    document.getElementById('purposeForm').style.display = 'none';
                                    document.getElementById('purpose_id').value = '';
                                    document.querySelector('#purposeForm form').reset();
                                }
                                function deletePurpose(id) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You want to delete this purpose?",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#6b7280',
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = '?page=admin&master=purposes&delete_purpose=' + id;
                                        }
                                    });
                                }
                            </script>
                        </div>

                        <?php
                // ========== DEPARTMENTS MASTER ==========
            elseif ($master_page == 'departments'):

                // Create department_master table if it doesn't exist
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'department_master'");
                if (mysqli_num_rows($check_table) == 0) {
                    mysqli_query($conn, "
                    CREATE TABLE department_master (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        department_name VARCHAR(100) NOT NULL UNIQUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                }

                // Handle Delete
                if (isset($_GET['delete_department'])) {
                    $id = (int) $_GET['delete_department'];

                    $d_res = mysqli_query($conn, "SELECT department_name FROM department_master WHERE id=$id");
                    $d_row = mysqli_fetch_assoc($d_res);
                    $d_name = $d_row['department_name'] ?? 'Unknown';

                    if (mysqli_query($conn, "DELETE FROM department_master WHERE id=$id")) {
                        logActivity($conn, 'DEPT_DELETE', 'Departments', "Deleted department: '$d_name' (ID: $id)");
                        $_SESSION['success_msg'] = "✅ Department deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=departments&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting department: " . mysqli_error($conn);
                    }
                }

                // Handle Save
                if (isset($_POST['save_department'])) {
                    $name = mysqli_real_escape_string($conn, $_POST['department_name']);
                    $current = null;

                    try {
                        if ($_POST['department_id']) {
                            $id = (int) $_POST['department_id'];
                            $current_res = mysqli_query($conn, "SELECT department_name FROM department_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);
                            $sql = "UPDATE department_master SET department_name='$name' WHERE id=$id";
                        } else {
                            $sql = "INSERT INTO department_master (department_name) VALUES ('$name')";
                        }

                        if (mysqli_query($conn, $sql)) {
                            $audit_details = "";
                            if ($_POST['department_id']) {
                                $diff = auditDiff($current, $_POST, ['department_id'], ['department_name' => 'Name']);
                                $audit_details = "Updated Department: Name: [$name] (ID: $id)";
                                if (!empty($diff))
                                    $audit_details .= "\nChanges:\n" . $diff;
                            } else {
                                $audit_details = "Created Department:\n" . auditFromPost($_POST, [], ['department_name' => 'Name']);
                            }
                            logActivity($conn, ($_POST['department_id'] ? 'DEPT_UPDATE' : 'DEPT_CREATE'), 'Departments', $audit_details);
                            $_SESSION['success_msg'] = "✅ Department saved successfully!";
                            session_write_close();
                            header("Location: ?page=admin&master=departments&t=" . time());
                            exit;
                        } else {
                            $error_msg = "❌ Error saving department: " . mysqli_error($conn);
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: Department '$name' already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                        }
                    }
                }

                $departments = mysqli_query($conn, "SELECT * FROM department_master ORDER BY department_name");
                ?>
                        <!-- Form Header with Gradient -->
                        <div
                            style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25);">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🏢</div>
                                <div>
                                    <h2
                                        style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                        Departments Management</h2>
                                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage
                                        department
                                        list
                                        for
                                        the organization</p>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin-bottom: 20px;">
                            <button
                                onclick="document.getElementById('deptForm').style.display='block'; document.getElementById('deptForm').scrollIntoView({ behavior: 'smooth' });"
                                class="btn btn-primary"
                                style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(14, 165, 233, 0.3);">
                                ➕ Add New Department
                            </button>

                            <div id="deptForm" style="display: none; margin-bottom: 20px;">
                                <form method="POST">
                                    <input type="hidden" name="department_id" id="department_id">
                                    <div class="card"
                                        style="margin-bottom: 20px; border-left: 4px solid #0ea5e9; background: linear-gradient(to right, #f0f9ff 0%, white 10%);">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Department Name *</label>
                                            <input type="text" name="department_name" id="department_name" required
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="submit" name="save_department" class="btn btn-success"
                                            style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                            Save Department</button>
                                        <button type="button" onclick="closeDeptForm()" class="btn btn-secondary"
                                            style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <?php if (isset($success_msg)): ?>
                                    <div class="alert alert-success">
                                        <?php echo $success_msg; ?>
                                    </div>
                                    <?php
                            endif; ?>
                            <?php if (isset($error_msg)): ?>
                                    <div class="alert alert-error">
                                        <?php echo $error_msg; ?>
                                    </div>
                                    <?php
                            endif; ?>

                            <div class="search-container" style="margin-bottom: 20px;">
                                <input type="text" id="deptSearch" placeholder="🔍 Search Departments (Name...)"
                                    oninput="filterTable('deptSearch', 'departmentsTable')"
                                    onsearch="filterTable('deptSearch', 'departmentsTable')"
                                    style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                    onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                            </div>

                            <table id="departmentsTable">
                                <thead>
                                    <tr>
                                        <th>Department Name</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($dept = mysqli_fetch_assoc($departments)): ?>
                                            <tr>
                                                <td><strong>
                                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                                    </strong></td>
                                                <td>
                                                    <?php echo strtoupper(date('d-M-y', strtotime($dept['created_at']))); ?>
                                                </td>
                                                <td>
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button
                                                                        onclick='editDepartment(<?php echo $dept["id"]; ?>, <?php echo json_encode($dept["department_name"]); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deleteDepartment(<?php echo $dept['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            function editDepartment(id, name) {
                                document.getElementById('department_id').value = id;
                                document.getElementById('department_name').value = name;
                                document.getElementById('deptForm').style.display = 'block';
                                document.getElementById('deptForm').scrollIntoView({ behavior: 'smooth' });
                            }
                            function closeDeptForm() {
                                document.getElementById('deptForm').style.display = 'none';
                                document.getElementById('department_id').value = '';
                                document.querySelector('#deptForm form').reset();
                            }
                            function deleteDepartment(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "You want to delete this department?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '?page=admin&master=departments&delete_department=' + id;
                                    }
                                });
                            }
                        </script>
                    </div>

                    <?php
                // ========== MATERIALS MASTER ==========
            elseif ($master_page == 'materials'):

                // Create material_master table if it doesn't exist (Modified for new requirements)
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'material_master'");
                if (mysqli_num_rows($check_table) == 0) {
                    mysqli_query($conn, "
                    CREATE TABLE material_master (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        material_code VARCHAR(50) NOT NULL,
                        material_description TEXT,
                        material_category VARCHAR(100),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        is_active TINYINT(1) DEFAULT 1,
                        UNIQUE KEY unique_material_code (material_code)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                } else {
                    // Ensure correct schema for Material-only Data
                    // 1. Drop existing indexes to ensure unique material_code
                    try {
                        // Check if old composite index exists
                        $idx_check = mysqli_query($conn, "SHOW INDEX FROM material_master WHERE Key_name = 'unique_material_supplier'");
                        if (mysqli_num_rows($idx_check) > 0) {
                            mysqli_query($conn, "ALTER TABLE material_master DROP INDEX unique_material_supplier");
                        }
                        // Check if strict material_code index exists, if not create it
                        $code_idx_check = mysqli_query($conn, "SHOW INDEX FROM material_master WHERE Key_name = 'unique_material_code'");
                        if (mysqli_num_rows($code_idx_check) == 0) {
                            // Note: This might fail if there are duplicate codes from previous data. We assume user wants this enforced.
                            mysqli_query($conn, "ALTER TABLE material_master ADD UNIQUE INDEX unique_material_code (material_code)");
                        }
                    } catch (Exception $e) {
                        // Ignore
                    }
                }



                // Handle Delete
                if (isset($_GET['delete_material'])) {
                    $id = (int) $_GET['delete_material'];
                    $m_res = mysqli_query($conn, "SELECT material_description FROM material_master WHERE id=$id");
                    $m_row = mysqli_fetch_assoc($m_res);
                    $m_name = $m_row['material_description'] ?? 'Unknown';

                    if (mysqli_query($conn, "DELETE FROM material_master WHERE id=$id")) {
                        logActivity($conn, 'MATERIAL_DELETE', 'Materials', "Deleted material: '$m_name' (ID: $id)");
                        $_SESSION['success_msg'] = "✅ Material deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=materials&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting material: " . mysqli_error($conn);
                    }
                }

                // Handle CSV Import
                if (isset($_POST['import_materials'])) {
                    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                        $file = $_FILES['import_file']['tmp_name'];
                        $handle = fopen($file, "r");
                        $success_count = 0;
                        $error_count = 0;
                        $first_row = true;

                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if ($first_row) {
                                $first_row = false;
                                continue;
                            } // Skip header
        
                            // Expected Format: Code, Description, Category
                            $code = mysqli_real_escape_string($conn, $data[0] ?? '');
                            $desc = mysqli_real_escape_string($conn, $data[1] ?? '');
                            $cat = mysqli_real_escape_string($conn, $data[2] ?? '');

                            if (!empty($code)) {
                                try {
                                    $sql = "INSERT INTO material_master (material_code, material_description, material_category) 
                                            VALUES ('$code', '$desc', '$cat')";
                                    if (mysqli_query($conn, $sql)) {
                                        $success_count++;
                                    } else {
                                        $error_count++;
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    $error_count++;
                                }
                            }
                        }
                        fclose($handle);
                        logActivity($conn, 'MATERIAL_IMPORT', 'Materials', "Material CSV Import Summary: Successfully imported [$success_count] items. (Skipped/Errors: $error_count)");
                        $success_msg = "✅ Imported $success_count materials. Skipped $error_count duplicates/errors.";
                        $sweet_alert_success = $success_msg;
                    } else {
                        $error_msg = "❌ Error uploading file.";
                        $sweet_alert_error = $error_msg;
                    }
                }

                // Handle Save
                if (isset($_POST['save_material'])) {
                    $code = mysqli_real_escape_string($conn, $_POST['material_code']);
                    $desc = mysqli_real_escape_string($conn, $_POST['material_description']);
                    $cat = mysqli_real_escape_string($conn, $_POST['material_category']);

                    try {
                        if ($_POST['material_id']) {
                            $id = (int) $_POST['material_id'];
                            $current_res = mysqli_query($conn, "SELECT * FROM material_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);
                            $changes = [];
                            if ($current) {
                                if (trim($current['material_description'] ?? '') != trim($desc ?? ''))
                                    $changes[] = "Desc: [{$current['material_description']} -> $desc]";
                                if (trim($current['material_category'] ?? '') != trim($cat ?? ''))
                                    $changes[] = "Cat: [{$current['material_category']} -> $cat]";
                            }

                            $sql = "UPDATE material_master SET material_description='$desc', material_category='$cat' WHERE id=$id";
                            if (mysqli_query($conn, $sql)) {
                                $diff = auditDiff($current, $_POST, ['material_id'], ['material_description' => 'Desc', 'material_category' => 'Cat']);
                                $details = "Updated Material: Desc: [$desc] (Code: $code)";
                                if (!empty($diff)) {
                                    $details .= "\nChanges:\n" . $diff;
                                }
                                logActivity($conn, 'MATERIAL_UPDATE', 'Materials', $details);
                                $_SESSION['success_msg'] = "✅ Material updated successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=materials&t=" . time());
                                exit;
                            }
                        } else {
                            $sql = "INSERT INTO material_master (material_code, material_description, material_category) 
                                VALUES ('$code', '$desc', '$cat')";
                            if (mysqli_query($conn, $sql)) {
                                logActivity($conn, 'MATERIAL_CREATE', 'Materials', "Created Material:\n" . auditFromPost($_POST, [], ['material_code' => 'Code', 'material_description' => 'Desc', 'material_category' => 'Cat']));
                                $_SESSION['success_msg'] = "✅ Material added successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=materials&t=" . time());
                                exit;
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: This Material Code already exists!";
                            $sweet_alert_error = "This Material Code already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                            $sweet_alert_error = "Database Error: " . $e->getMessage();
                        }
                    }
                }

                $materials = mysqli_query($conn, "SELECT * FROM material_master WHERE is_active=1 ORDER BY material_code");

                // Initialize form values
                $form_code = isset($_POST['material_code']) ? htmlspecialchars($_POST['material_code']) : '';
                $form_desc = isset($_POST['material_description']) ? htmlspecialchars($_POST['material_description']) : '';
                $form_cat = isset($_POST['material_category']) ? htmlspecialchars($_POST['material_category']) : '';
                $form_id = isset($_POST['material_id']) ? htmlspecialchars($_POST['material_id']) : '';

                // Keep form open if there is an error
                $show_form = isset($error_msg) ? 'block' : 'none';
                ?>

                    <!-- Form Header with Gradient -->
                    <div
                        style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(6, 182, 212, 0.25);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🧱</div>
                            <div>
                                <h2
                                    style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    Materials Master</h2>
                                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage raw
                                    materials
                                    and
                                    product inventory</p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 20px;">
                        <button
                            onclick="resetMaterialForm(); document.getElementById('materialForm').style.display='block'; document.getElementById('materialForm').scrollIntoView({ behavior: 'smooth' });"
                            class="btn btn-primary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);">
                            ➕ Add New Material
                        </button>
                        <form method="post" enctype="multipart/form-data" style="display:inline;">
                            <input type="file" name="import_file" id="import_file_material" style="display:none;"
                                onchange="this.form.submit()" accept=".csv">
                            <button type="button" onclick="document.getElementById('import_file_material').click()"
                                class="btn btn-secondary"
                                style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; margin-left: 10px;">
                                📥 Import CSV
                            </button>
                            <input type="hidden" name="import_materials" value="1">
                        </form>
                        <a href="?page=admin&master=materials&download_template=materials" class="btn btn-secondary"
                            style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; margin-left: 10px; text-decoration: none; display: inline-block;">
                            📄 Template
                        </a>

                        <div id="materialForm" style="display: <?php echo $show_form; ?>; margin-bottom: 20px;">
                            <form method="POST">
                                <input type="hidden" name="material_id" id="material_id" value="<?php echo $form_id; ?>">
                                <div class="card"
                                    style="margin-bottom: 20px; border-left: 4px solid #06b6d4; background: linear-gradient(to right, #ecfeff 0%, white 10%);">
                                    <div class="master-form-grid">
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Material Code *</label>
                                            <input type="text" name="material_code" id="material_code" required
                                                value="<?php echo $form_code; ?>"
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Material Description</label>
                                            <input type="text" name="material_description" id="material_description"
                                                value="<?php echo $form_desc; ?>"
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; color: #374151;">Category</label>
                                            <input type="text" name="material_category" id="material_category"
                                                value="<?php echo $form_cat; ?>" placeholder="e.g. Raw Material, Packaging"
                                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                        </div>
                                        <!-- Removed Supplier Fields -->
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" name="save_material" class="btn btn-success"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                        Save Material</button>
                                    <button type="button" onclick="closeMaterialForm()" class="btn btn-secondary"
                                        style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_msg; ?>
                                </div>
                                <?php
                        endif; ?>
                        <?php if (isset($error_msg)): ?>
                                <div class="alert alert-error">
                                    <?php echo $error_msg; ?>
                                </div>
                                <?php
                        endif; ?>

                        <div class="search-container" style="margin-bottom: 20px;">
                            <input type="text" id="materialSearch" placeholder="🔍 Search Materials (Code, Description, Category...)"
                                oninput="filterTable('materialSearch', 'materialsTable')"
                                onsearch="filterTable('materialSearch', 'materialsTable')"
                                style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                        </div>

                        <div class="table-wrapper">
                            <table id="materialsTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = mysqli_fetch_assoc($materials)): ?>
                                            <tr>
                                                <td><strong>
                                                        <?php echo htmlspecialchars($item['material_code']); ?>
                                                    </strong></td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['material_description']); ?>
                                                </td>
                                                <td><span class="badge badge-info">
                                                        <?php echo htmlspecialchars($item['material_category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (hasPermission('actions.view_buttons')): ?>
                                                            <?php if (hasPermission('actions.edit_record')): ?>
                                                                    <button onclick='editMaterial(<?php echo $item["id"]; ?>, 
                                                    <?php echo json_encode($item["material_code"]); ?>, 
                                                    <?php echo json_encode($item["material_description"]); ?>, 
                                                    <?php echo json_encode($item["material_category"]); ?>)'
                                                                        class="btn btn-sm"
                                                                        style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                        Edit</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php if (hasPermission('actions.delete_record')): ?>
                                                                    <button onclick="deleteMaterial(<?php echo $item['id']; ?>)" class="btn btn-sm"
                                                                        style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                        Delete</button>
                                                                    <?php
                                                            endif; ?>
                                                            <?php
                                                    endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                    endwhile; ?>
                                </tbody>
                            </table>

                            <script>
                                function resetMaterialForm() {
                                    document.getElementById('materialForm').style.display = 'block';
                                    document.getElementById('material_id').value = '';
                                    document.getElementById('material_code').readOnly = false;
                                    document.getElementById('material_code').value = '';
                                    document.getElementById('material_description').value = '';
                                    document.getElementById('material_category').value = '';
                                }
                                function editMaterial(id, code, desc, cat) {
                                    document.getElementById('material_id').value = id;
                                    document.getElementById('material_code').value = code;
                                    // Use the reset function or manually clear valid states first?
                                    // Just set values.
                                    // document.getElementById('material_code').readOnly = true; // User can edit code if creating new supplier entry from existing, but logic is Update vs Insert.
                                    // To keep it simple: if ID exists -> UPDATE. So changing Code on Update changes the code for that ID.
                                    // Duplicate check handles conflicts.
                                    document.getElementById('material_code').readOnly = true;
                                    document.getElementById('material_description').value = desc || '';
                                    document.getElementById('material_category').value = cat || '';

                                    document.getElementById('materialForm').style.display = 'block';
                                    document.getElementById('materialForm').scrollIntoView({ behavior: 'smooth' });
                                }
                                function closeMaterialForm() {
                                    document.getElementById('materialForm').style.display = 'none';
                                    document.getElementById('material_id').value = '';
                                    document.getElementById('material_code').readOnly = false;
                                    document.querySelector('#materialForm form').reset();
                                }
                                function calculateTotal(fieldIdx, qtyValue) {
                                    // Start from next field
                                }
                                function deleteMaterial(id) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You want to delete this material?",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#6b7280',
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = '?page=admin&master=materials&delete_material=' + id;
                                        }
                                    });
                                }
                            </script>
                            <?php
            elseif ($master_page == 'suppliers'):
                // ========== SUPPLIERS MASTER ==========
        
                // Create supplier_master table if it doesn't exist
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'supplier_master'");
                if (mysqli_num_rows($check_table) == 0) {
                    mysqli_query($conn, "
                    CREATE TABLE supplier_master (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        supplier VARCHAR(100) NOT NULL,
                        supp_code VARCHAR(50) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        is_active TINYINT(1) DEFAULT 1,
                        UNIQUE KEY unique_supp_code (supp_code)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                }



                // Handle CSV Import
                if (isset($_POST['import_suppliers'])) {
                    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                        $file = $_FILES['import_file']['tmp_name'];
                        $handle = fopen($file, "r");
                        $success_count = 0;
                        $error_count = 0;
                        $first_row = true;

                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if ($first_row) {
                                $first_row = false;
                                continue;
                            } // Skip header
        
                            // Expected Format: Name, Code
                            $name = mysqli_real_escape_string($conn, $data[0] ?? '');
                            $code = mysqli_real_escape_string($conn, $data[1] ?? '');

                            if (!empty($code) && !empty($name)) {
                                try {
                                    $sql = "INSERT INTO supplier_master (supplier, supp_code) 
                                            VALUES ('$name', '$code')";
                                    if (mysqli_query($conn, $sql)) {
                                        $success_count++;
                                    } else {
                                        $error_count++;
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    $error_count++;
                                }
                            }
                        }
                        fclose($handle);
                        logActivity($conn, 'SUPPLIER_IMPORT', 'Suppliers', "Supplier CSV Import Summary: Successfully imported [$success_count] vendors. (Skipped/Errors: $error_count)");
                        $success_msg = "✅ Imported $success_count suppliers. Skipped $error_count duplicates/errors.";
                        $sweet_alert_success = $success_msg;
                    } else {
                        $error_msg = "❌ Error uploading file.";
                        $sweet_alert_error = $error_msg;
                    }
                }

                // Handle Delete
                if (isset($_GET['delete_supplier'])) {
                    $id = (int) $_GET['delete_supplier'];

                    // Get supplier name for log
                    $s_res = mysqli_query($conn, "SELECT supplier FROM supplier_master WHERE id=$id");
                    $s_row = mysqli_fetch_assoc($s_res);
                    $s_name = $s_row['supplier'] ?? 'Unknown';

                    if (mysqli_query($conn, "DELETE FROM supplier_master WHERE id=$id")) {
                        logActivity($conn, 'SUPPLIER_DELETE', 'Suppliers', "Deleted Supplier: Name: [$s_name] (ID: $id)");
                        $_SESSION['success_msg'] = "✅ Supplier deleted successfully!";
                        session_write_close();
                        header("Location: ?page=admin&master=suppliers&t=" . time());
                        exit;
                    } else {
                        $error_msg = "❌ Error deleting supplier: " . mysqli_error($conn);
                    }
                }

                // Handle Save
                if (isset($_POST['save_supplier'])) {
                    $supp = mysqli_real_escape_string($conn, $_POST['supplier']);
                    $supp_code = mysqli_real_escape_string($conn, $_POST['supp_code']);

                    try {
                        if ($_POST['supplier_id']) {
                            $id = (int) $_POST['supplier_id'];
                            $current_res = mysqli_query($conn, "SELECT * FROM supplier_master WHERE id=$id");
                            $current = mysqli_fetch_assoc($current_res);
                            $changes = [];
                            if ($current) {
                                if (trim($current['supplier'] ?? '') != trim($supp ?? ''))
                                    $changes[] = "Name: [{$current['supplier']} -> $supp]";
                                if (trim($current['supp_code'] ?? '') != trim($supp_code ?? ''))
                                    $changes[] = "Code: [{$current['supp_code']} -> $supp_code]";
                            }

                            $sql = "UPDATE supplier_master SET supplier='$supp', supp_code='$supp_code' WHERE id=$id";
                            if (mysqli_query($conn, $sql)) {
                                $details = "Updated Supplier: Name: [$supp] (Code: $supp_code)";
                                if (!empty($changes)) {
                                    $details .= "\nChanges:\n" . implode("\n", $changes);
                                }
                                logActivity($conn, 'SUPPLIER_UPDATE', 'Suppliers', $details);
                                $_SESSION['success_msg'] = "✅ Supplier updated successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=suppliers&t=" . time());
                                exit;
                            }
                        } else {
                            $sql = "INSERT INTO supplier_master (supplier, supp_code) 
                                VALUES ('$supp', '$supp_code')";
                            if (mysqli_query($conn, $sql)) {
                                logActivity($conn, 'SUPPLIER_CREATE', 'Suppliers', "Created Supplier:\n" . auditFromPost($_POST, [], ['supplier' => 'Name', 'supp_code' => 'Code']));
                                $_SESSION['success_msg'] = "✅ Supplier added successfully!";
                                session_write_close();
                                header("Location: ?page=admin&master=suppliers&t=" . time());
                                exit;
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1062) {
                            $error_msg = "❌ Error: This Supplier Code already exists!";
                            $sweet_alert_error = "This Supplier Code already exists!";
                        } else {
                            $error_msg = "❌ Database Error: " . $e->getMessage();
                            $sweet_alert_error = "Database Error: " . $e->getMessage();
                        }
                    }
                }

                $suppliers = mysqli_query($conn, "SELECT * FROM supplier_master WHERE is_active=1 ORDER BY supplier");

                // Initialize form values
                $form_supp = isset($_POST['supplier']) ? htmlspecialchars($_POST['supplier']) : '';
                $form_supp_code = isset($_POST['supp_code']) ? htmlspecialchars($_POST['supp_code']) : '';
                $form_id = isset($_POST['supplier_id']) ? htmlspecialchars($_POST['supplier_id']) : '';

                // Keep form open if there is an error
                $show_form = isset($error_msg) ? 'block' : 'none';
                ?>

                            <!-- Form Header with Gradient -->
                            <div
                                style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(6, 182, 212, 0.25);">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🏭</div>
                                    <div>
                                        <h2
                                            style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            Suppliers Master</h2>
                                        <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage
                                            supplier
                                            records
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card" style="margin-bottom: 20px;">
                                <button
                                    onclick="resetSupplierForm(); document.getElementById('supplierForm').style.display='block'; document.getElementById('supplierForm').scrollIntoView({ behavior: 'smooth' });"
                                    class="btn btn-primary"
                                    style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);">
                                    ➕ Add New Supplier
                                </button>
                                <form method="post" enctype="multipart/form-data" style="display:inline;">
                                    <input type="file" name="import_file" id="import_file_supplier" style="display:none;"
                                        onchange="this.form.submit()" accept=".csv">
                                    <button type="button" onclick="document.getElementById('import_file_supplier').click()"
                                        class="btn btn-secondary"
                                        style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; margin-left: 10px;">
                                        📥 Import CSV
                                    </button>
                                    <input type="hidden" name="import_suppliers" value="1">
                                </form>
                                <a href="?page=admin&master=suppliers&download_template=suppliers" class="btn btn-secondary"
                                    style="margin-bottom: 20px; padding: 12px 24px; font-size: 15px; font-weight: 600; margin-left: 10px; text-decoration: none; display: inline-block;">
                                    📄 Template
                                </a>


                                <div id="supplierForm" style="display: <?php echo $show_form; ?>; margin-bottom: 20px;">
                                    <form method="POST">
                                        <input type="hidden" name="supplier_id" id="supplier_id" value="<?php echo $form_id; ?>">
                                        <div class="card"
                                            style="margin-bottom: 20px; border-left: 4px solid #06b6d4; background: linear-gradient(to right, #ecfeff 0%, white 10%);">
                                            <div class="master-form-grid">
                                                <div class="form-group">
                                                    <label style="font-weight: 600; color: #374151;">Supplier Name *</label>
                                                    <input type="text" name="supplier" id="supplier" required
                                                        value="<?php echo $form_supp; ?>"
                                                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                        onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                </div>
                                                <div class="form-group">
                                                    <label style="font-weight: 600; color: #374151;">Supplier Code *</label>
                                                    <input type="text" name="supp_code" id="supp_code" required
                                                        value="<?php echo $form_supp_code; ?>"
                                                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                                        onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                    <small style="color: #666; display: block; margin-top: 5px;">Must be
                                                        unique.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 10px;">
                                            <button type="submit" name="save_supplier" class="btn btn-success"
                                                style="padding: 12px 24px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">💾
                                                Save Supplier</button>
                                            <button type="button" onclick="closeSupplierForm()" class="btn btn-secondary"
                                                style="padding: 12px 24px; font-size: 15px; font-weight: 600;">Cancel</button>
                                        </div>
                                    </form>
                                </div>

                                <?php if (isset($success_msg)): ?>
                                        <div class="alert alert-success">
                                            <?php echo $success_msg; ?>
                                        </div>
                                        <?php
                                endif; ?>
                                <?php if (isset($error_msg)): ?>
                                        <div class="alert alert-error">
                                            <?php echo $error_msg; ?>
                                        </div>
                                        <?php
                                endif; ?>

                                <div class="search-container" style="margin-bottom: 20px;">
                                    <input type="text" id="supplierSearch" placeholder="🔍 Search Suppliers (Name, Code...)"
                                        oninput="filterTable('supplierSearch', 'suppliersTable')"
                                        onsearch="filterTable('supplierSearch', 'suppliersTable')"
                                        style="width: 100%; padding: 12px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s; outline: none;"
                                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                                </div>

                                <div class="table-wrapper">
                                    <table id="suppliersTable">
                                        <thead>
                                            <tr>
                                                <th>Supplier Name</th>
                                                <th>Supplier Code</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($item = mysqli_fetch_assoc($suppliers)): ?>
                                                    <tr>
                                                        <td><strong>
                                                                <?php echo htmlspecialchars($item['supplier']); ?>
                                                            </strong></td>
                                                        <td><code><?php echo htmlspecialchars($item['supp_code']); ?></code></td>
                                                        <td>
                                                            <?php if (hasPermission('actions.view_buttons')): ?>
                                                                    <?php if (hasPermission('actions.edit_record')): ?>
                                                                            <button onclick='editSupplier(<?php echo $item["id"]; ?>, 
                                                    <?php echo json_encode($item["supplier"]); ?>, 
                                                    <?php echo json_encode($item["supp_code"]); ?>)' class="btn btn-sm"
                                                                                style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px;">✏️
                                                                                Edit</button>
                                                                            <?php
                                                                    endif; ?>
                                                                    <?php if (hasPermission('actions.delete_record')): ?>
                                                                            <button onclick="deleteSupplier(<?php echo $item['id']; ?>)" class="btn btn-sm"
                                                                                style="background: #ef4444; color: white; padding: 5px 10px; font-size: 12px;">🗑️
                                                                                Delete</button>
                                                                            <?php
                                                                    endif; ?>
                                                                    <?php
                                                            endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                            endwhile; ?>
                                        </tbody>
                                    </table>

                                    <script>
                                        function resetSupplierForm() {
                                            document.getElementById('supplierForm').style.display = 'block';
                                            document.getElementById('supplier_id').value = '';
                                            document.getElementById('supp_code').readOnly = false;
                                            document.getElementById('supplier').value = '';
                                            document.getElementById('supp_code').value = '';
                                        }
                                        function editSupplier(id, supp, code) {
                                            document.getElementById('supplier_id').value = id;
                                            document.getElementById('supplier').value = supp;
                                            document.getElementById('supp_code').value = code;
                                            // On edit, do we allow code change? Usually yes, but with check.
                                            // For consistency with material, maybe just uniqueness check on save.
                                            // Let's keep it editable but we rely on DB unique constraint.

                                            document.getElementById('supplierForm').style.display = 'block';
                                            document.getElementById('supplierForm').scrollIntoView({ behavior: 'smooth' });
                                        }
                                        function closeSupplierForm() {
                                            document.getElementById('supplierForm').style.display = 'none';
                                            document.getElementById('supplier_id').value = '';
                                            document.querySelector('#supplierForm form').reset();
                                        }
                                        function deleteSupplier(id) {
                                            Swal.fire({
                                                title: 'Are you sure?',
                                                text: "You want to delete this supplier?",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#6b7280',
                                                confirmButtonText: 'Yes, delete it!',
                                                cancelButtonText: 'Cancel'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = '?page=admin&master=suppliers&delete_supplier=' + id;
                                                }
                                            });
                                        }
                                    </script>

                                    <?php
                // ========== AUDIT LOGS ==========
            elseif ($master_page == 'audit_logs' || $master_page == 'logs'):
                // Filtering parameters
                // Filtering parameters (GET based as per user request to avoid persistent session sticky)
                $search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
                $f_module = isset($_GET['f_module']) ? mysqli_real_escape_string($conn, $_GET['f_module']) : '';
                $f_type = isset($_GET['f_type']) ? mysqli_real_escape_string($conn, $_GET['f_type']) : '';
                $date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
                $date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';

                $where = "WHERE 1=1";
                if ($search)
                    $where .= " AND (username LIKE '%$search%' OR details LIKE '%$search%')";
                if ($f_module)
                    $where .= " AND module = '$f_module'";
                if ($f_type)
                    $where .= " AND activity_type = '$f_type'";
                if ($date_from)
                    $where .= " AND DATE(created_at) >= '$date_from'";
                if ($date_to)
                    $where .= " AND DATE(created_at) <= '$date_to'";

                // Fetch Audit Logs - showing latest 1000 entries
                $audit_query = "SELECT * FROM audit_logs $where ORDER BY created_at DESC LIMIT 1000";
                $audit_logs = mysqli_query($conn, $audit_query);
                ?>
                                    <div
                                        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                                        <div
                                            style="display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 15px;">
                                                <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📜</div>
                                                <div>
                                                    <h2
                                                        style="margin: 0; color: white; font-size: 24px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                        System Audit Logs</h2>
                                                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.7); font-size: 13px;">
                                                        Overview of all past activities and administrative changes</p>
                                                </div>
                                            </div>
                                            <button onclick="document.getElementById('auditFilterModal').style.display='flex'"
                                                class="btn"
                                                style="background: #4b5563; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                                🔍 Search & Filter
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Active Filter Indicator -->
                                    <?php if ($search || $f_module || $f_type || $date_from || $date_to): ?>
                                            <div
                                                style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                    <span
                                                        style="font-size: 13px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 5px;">
                                                        <span style="font-size: 16px;">🔍</span> Active Filters:
                                                    </span>
                                                    <?php if ($search): ?>
                                                            <span
                                                                style="background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 12px; border-radius: 20px; font-size: 11px; color: #1e293b; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                                Search: "<?php echo htmlspecialchars($search); ?>"
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if ($f_module): ?>
                                                            <span
                                                                style="background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 12px; border-radius: 20px; font-size: 11px; color: #1e293b; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                                Module: <?php echo htmlspecialchars($f_module); ?>
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if ($f_type): ?>
                                                            <span
                                                                style="background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 12px; border-radius: 20px; font-size: 11px; color: #1e293b; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                                Activity: <?php echo htmlspecialchars($f_type); ?>
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if ($date_from || $date_to): ?>
                                                            <span
                                                                style="background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 12px; border-radius: 20px; font-size: 11px; color: #1e293b; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                                Date: <?php echo $date_from ?: '...'; ?> to <?php echo $date_to ?: 'now'; ?>
                                                            </span>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="?page=admin&master=audit_logs"
                                                    style="text-decoration: none; font-size: 11px; font-weight: 700; color: #ef4444; background: #fee2e2; padding: 6px 15px; border-radius: 8px; border: 1px solid #fecaca; transition: all 0.2s; white-space: nowrap;">
                                                    ✕ Clear All
                                                </a>
                                            </div>
                                    <?php endif; ?>

                                    <div class="card" style="margin-bottom: 20px;">
                                        <div class="table-wrapper" id="auditLogsTable">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <thead style="background: #f1f5f9;">
                                                    <tr style="text-align: left;">
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            TIME</th>
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            USER</th>
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            ACTIVITY</th>
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            PAGE NAME</th>
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            IP ADDRESS</th>
                                                        <th
                                                            style="padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569;">
                                                            DETAILS</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($log = mysqli_fetch_assoc($audit_logs)): ?>
                                                            <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.1s;"
                                                                onmouseover="this.style.background='#f8fafc'"
                                                                onmouseout="this.style.background='white'"
                                                                onclick='showAuditDetail(<?php echo htmlspecialchars(json_encode($log), ENT_QUOTES); ?>)'>
                                                                <td
                                                                    style="padding: 12px; font-size: 13px; color: #64748b; white-space: nowrap;">
                                                                    <?php echo strtoupper(date('d-M-y H:i:s', strtotime($log['created_at']))); ?>
                                                                </td>
                                                                <td style="padding: 12px; font-size: 14px;">
                                                                    <strong><?php echo htmlspecialchars($log['username']); ?></strong></td>
                                                                <td style="padding: 12px;">
                                                                    <?php
                                                                    $badge_class = 'badge-info';
                                                                    if (stripos($log['activity_type'], 'LOGIN') !== false)
                                                                        $badge_class = 'badge-success';
                                                                    if (stripos($log['activity_type'], 'FAILED') !== false)
                                                                        $badge_class = 'badge-danger';
                                                                    if (stripos($log['activity_type'], 'DELETE') !== false)
                                                                        $badge_class = 'badge-danger';
                                                                    if (stripos($log['activity_type'], 'UPDATE') !== false)
                                                                        $badge_class = 'badge-warning';
                                                                    if (stripos($log['activity_type'], 'CREATED') !== false)
                                                                        $badge_class = 'badge-success';
                                                                    ?>
                                                                    <span class="badge <?php echo $badge_class; ?>"
                                                                        style="font-size: 10px;"><?php echo htmlspecialchars($log['activity_type']); ?></span>
                                                                </td>
                                                                <td style="padding: 12px; font-size: 13px; color: #4b5563;">
                                                                    <?php echo htmlspecialchars($log['module']); ?>
                                                                </td>
                                                                <td
                                                                    style="padding: 12px; font-size: 12px; color: #94a3b8; font-family: monospace;">
                                                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                                                </td>
                                                                <td style="padding: 12px; font-size: 13px; color: #1e293b;">
                                                                    <?php
                                                                    $details = $log['details'];
                                                                    echo htmlspecialchars(strlen($details) > 60 ? substr($details, 0, 60) . '...' : $details);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                    endwhile; ?>
                                                    <?php if (mysqli_num_rows($audit_logs) == 0): ?>
                                                            <tr>
                                                                <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">No
                                                                    audit records found yet</td>
                                                            </tr>
                                                            <?php
                                                    endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <?php
            endif; // End master_page conditions
            ?>

                            <script>
                                // Auto-scroll to master table on page load based on master parameter
                                document.addEventListener('DOMContentLoaded', function () {
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const masterPage = urlParams.get('master');

                                    if (masterPage) {
                                        const scrollMap = {
                                            'dashboard': 'overviewSection',
                                            'settings': 'settingsSection',
                                            'users': 'usersTable',
                                            'transporters': 'transportersTable',
                                            'drivers': 'driversTable',
                                            'vehicles': 'vehiclesTable',
                                            'purposes': 'purposesTable',
                                            'materials': 'materialsTable',
                                            'suppliers': 'suppliersTable',
                                            'audit_logs': 'auditLogsTable'
                                        };

                                        const elementId = scrollMap[masterPage];
                                        if (elementId) {
                                            // Wait a bit for content to render, then scroll
                                            setTimeout(function () {
                                                const targetElement = document.getElementById(elementId);
                                                if (targetElement) {
                                                    // Scroll with offset to account for any fixed headers
                                                    const offset = 80;
                                                    const elementPosition = targetElement.getBoundingClientRect().top;
                                                    const offsetPosition = elementPosition + window.pageYOffset - offset;

                                                    window.scrollTo({
                                                        top: offsetPosition,
                                                        behavior: 'smooth'
                                                    });
                                                }
                                            }, 500);
                                        }
                                    }
                                });
                            </script>

                        </div>
                        <?php
endif; ?>
                <?php if ($page == 'user-permissions'): ?>

                        <?php // Ensure Admin Access (Allow if role is admin/manager OR user is super_admin)
                            $is_super_admin = (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1);
                            $user_role = strtolower($_SESSION['role'] ?? '');
                            if (!$is_super_admin && ($user_role != 'admin' && $user_role != 'manager') && !hasPermission('masters.users')) {
                                echo "
            <script>window.location.href = '?page=dashboard';</script>";
                                exit;
                            }

                            // Fetch Users
                            $users_res = mysqli_query($conn, "SELECT id, username, full_name, role, permissions, super_admin FROM user_master ORDER BY
            full_name");
                            ?>
                        <div class="container">
                            <?php if (isset($success_msg)): ?>
                                    <div class="alert alert-success">
                                        <?php echo $success_msg; ?>
                                    </div>
                                    <?php
                            endif; ?>
                            <?php if (isset($error_msg)): ?>
                                    <div class="alert alert-error">
                                        <?php echo $error_msg; ?>
                                    </div>
                                    <?php
                            endif; ?>

                            <a href="?page=admin" class="btn btn-secondary btn-full" style="margin-bottom: 20px;">← Back to
                                Admin</a>

                            <div
                                style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 40px;">🔐</div>
                                    <div>
                                        <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">User
                                            Permissions
                                        </h1>
                                        <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9);">Manage access rights for
                                            system
                                            users</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="table-wrapper">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Permissions Configured</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($u = mysqli_fetch_assoc($users_res)):
                                                $perms = json_decode($u['permissions'] ?? '{}', true);
                                                $has_perms = !empty($perms);
                                                ?>
                                                    <tr>
                                                        <td><strong>
                                                                <?php echo htmlspecialchars($u['full_name']); ?>
                                                            </strong></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($u['username']); ?>
                                                        </td>
                                                        <td><span class="badge">
                                                                <?php echo ucfirst($u['role']); ?>
                                                            </span></td>
                                                        <td>
                                                            <?php if ($has_perms): ?>
                                                                    <span class="badge badge-success">Yes</span>
                                                                    <?php
                                                            else: ?>
                                                                    <span class="badge badge-secondary">Default (Role Based)</span>
                                                                    <?php
                                                            endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($_SESSION['super_admin'] == 1 || $u['super_admin'] != 1 || $u['id'] == $_SESSION['user_id']): ?>
                                                                    <button class="btn btn-primary btn-sm"
                                                                        onclick='openPermissionModal(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>)'>
                                                                        ⚙️ Manage Rights
                                                                    </button>
                                                                    <?php
                                                            endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                            endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                endif; ?>

        <!-- GLOBAL MODALS (Available to all admin/permission pages) -->

        <!-- 1. Audit Log Detail Modal -->
        <div id="auditDetailModal" class="perm-modal-overlay">
            <div class="perm-modal-content"
                style="max-width: 650px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; border: none;">
                <div
                    style="padding: 24px 30px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin: 0; font-size: 20px; font-weight: 700;">📜 Activity Details</h2>
                        <p id="auditTime" style="margin: 4px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);"></p>
                    </div>
                    <button onclick="document.getElementById('auditDetailModal').style.display='none'"
                        style="background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 50%; color: white; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>
                <div style="padding: 30px; background: white;">
                    <div
                        style="display: flex; gap: 30px; margin-bottom: 25px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <div style="flex: 1;"><label
                                style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">User</label>
                            <div id="auditUser"
                                style="font-weight: 700; color: #1e293b; font-size: 17px; margin-top: 4px;"></div>
                        </div>
                        <div style="flex: 1;"><label
                                style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Activity</label>
                            <div id="auditTypeBadge"
                                style="display: inline-block; margin-top: 5px; padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                            </div>
                        </div>
                    </div>
                    <label
                        style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Update
                        Summary / Details</label>
                    <div id="auditDetailsContainer"
                        style="background: #ffffff; border: 1.5px solid #eef2ff; border-radius: 12px; padding: 20px; margin-bottom: 20px; min-height: 100px; max-height: 350px; overflow-y: auto; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                        <div id="auditDetails" style="font-size: 14px; line-height: 1.6; color: #334155;"></div>
                    </div>
                    <div
                        style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                        <div><strong>Page Name:</strong> <span id="auditModule" style="color: #475569;"></span></div>
                        <div style="text-align: right;"><strong>IP ADDRESS:</strong> <span id="auditIP"
                                style="color: #475569; font-family: monospace;"></span></div>
                    </div>
                </div>
                <div style="padding: 20px 30px; background: #f8fafc; border-top: 1px solid #f1f5f9; text-align: right;">
                    <button onclick="document.getElementById('auditDetailModal').style.display='none'"
                        class="btn btn-secondary"
                        style="background: #475569; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 600;">Close
                        View</button>
                </div>
            </div>
        </div>

        <!-- 2. Global Permission Modal -->
        <div id="permModal" class="perm-modal-overlay">
            <div class="perm-modal-content">
                <div
                    style="padding: 24px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
                    <div>
                        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #111827;">Access Control</h2>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;">Manage permissions for <strong
                                id="permUser" style="color: #4f46e5;"></strong></p>
                    </div>
                    <button onclick="document.getElementById('permModal').style.display='none'"
                        style="background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 50%; color: #4b5563; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <form method="POST" style="padding: 24px;">
                    <input type="hidden" name="user_id" id="permUserId">

                    <div style="margin-bottom: 30px;">
                        <div class="perm-section-title"><span>👁️</span> Page & Menu Visibility</div>
                        <div class="perm-grid">
                            <label class="perm-card"><input type="checkbox" name="perm_page_dashboard" id="p_dashboard">
                                <div class="perm-card-content">Dashboard</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_inward" id="p_inward">
                                <div class="perm-card-content">Inward Entry</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_outward" id="p_outward">
                                <div class="perm-card-content">Outward Exit</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_inside" id="p_inside">
                                <div class="perm-card-content">Trucks Inside</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_history" id="p_history">
                                <div class="perm-card-content">Vehicle History</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_patrol" id="p_patrol">
                                <div class="perm-card-content">Guard Patrol</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_reports" id="p_reports">
                                <div class="perm-card-content">Reports</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_register" id="p_register">
                                <div class="perm-card-content">Manual Registers</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_register_types"
                                    id="p_register_types">
                                <div class="perm-card-content">Manage Register Types</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_loading" id="p_loading">
                                <div class="perm-card-content">Loading Checklist</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_unloading" id="p_unloading">
                                <div class="perm-card-content">Unloading Checklist</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_management"
                                    id="p_management">
                                <div class="perm-card-content">Management Dash</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_tickets" id="p_tickets">
                                <div class="perm-card-content">Tickets</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_masters" id="p_masters">
                                <div class="perm-card-content">Master Data</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_audit_logs"
                                    id="p_audit_logs">
                                <div class="perm-card-content">Audit Logs</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_employee_scan"
                                    id="p_employee_scan">
                                <div class="perm-card-content">Employee QR Scan</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_app_issues"
                                    id="p_app_issues">
                                <div class="perm-card-content">Report Issues</div>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <div class="perm-section-title"><span>⚙️</span> Master Data Access</div>
                        <div class="perm-grid">
                            <label class="perm-card"><input type="checkbox" name="perm_master_transporters"
                                    id="m_transporters">
                                <div class="perm-card-content">Transporters</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_drivers" id="m_drivers">
                                <div class="perm-card-content">Drivers</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_vehicles" id="m_vehicles">
                                <div class="perm-card-content">Vehicles</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_purposes" id="m_purposes">
                                <div class="perm-card-content">Purposes</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_employees"
                                    id="m_employees">
                                <div class="perm-card-content">Employees</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_departments"
                                    id="m_departments">
                                <div class="perm-card-content">Departments</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_patrol" id="m_patrol">
                                <div class="perm-card-content">Patrol QR</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_materials"
                                    id="m_materials">
                                <div class="perm-card-content">Materials</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_suppliers"
                                    id="m_suppliers">
                                <div class="perm-card-content">Suppliers</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_users" id="m_users">
                                <div class="perm-card-content">Users</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_master_settings" id="m_settings">
                                <div class="perm-card-content">Settings</div>
                            </label>
                            <label class="perm-card"><input type="checkbox" name="perm_page_permissions"
                                    id="p_permissions">
                                <div class="perm-card-content">User Permissions</div>
                            </label>
                        </div>
                    </div>

                    <div style="background: #f9fafb; border-radius: 12px; padding: 16px; border: 1px solid #e5e7eb;">
                        <div class="perm-section-title" style="margin-bottom: 10px;"><span>⚡</span> Operational Actions
                        </div>
                        <div class="action-row">
                            <div class="action-info">
                                <h4>View Action Buttons</h4>
                                <p>Show/Hide Edit & Delete buttons</p>
                            </div><label class="switch"><input type="checkbox" name="perm_action_buttons"
                                    id="a_buttons"><span class="slider"></span></label>
                        </div>
                        <div class="action-row">
                            <div class="action-info">
                                <h4>Edit Records</h4>
                                <p>Allow entry modifications</p>
                            </div><label class="switch"><input type="checkbox" name="perm_action_edit" id="a_edit"><span
                                    class="slider"></span></label>
                        </div>
                        <?php if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1): ?>
                                <div class="action-row">
                                    <div class="action-info">
                                        <h4>Delete Records</h4>
                                        <p style="color:#ef4444;">Permanent removal</p>
                                    </div><label class="switch"><input type="checkbox" name="perm_action_delete"
                                            id="a_delete"><span class="slider"></span></label>
                                </div>
                                <?php
                        endif; ?>
                    </div>

                    <div
                        style="display: flex; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                        <button type="button" onclick="document.getElementById('permModal').style.display='none'"
                            class="btn btn-secondary" style="flex: 1; padding: 12px; font-weight: 600;">Cancel</button>
                        <button type="submit" name="save_permissions" class="btn btn-primary"
                            style="flex: 2; padding: 12px; font-weight: 600; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none;">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Audit Filter Modal -->
        <div id="auditFilterModal" class="perm-modal-overlay">
            <div class="perm-modal-content" style="max-width: 500px;">
                <div
                    style="padding: 20px 25px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin:0;">🔍 Filter Logs</h3>
                    <button type="button" onclick="document.getElementById('auditFilterModal').style.display='none'"
                        style="background: transparent; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
                </div>
                <form method="GET" style="padding: 25px;">
                    <input type="hidden" name="page" value="admin">
                    <input type="hidden" name="master" value="audit_logs">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600;">Search Keyword</label>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            placeholder="Username, Details, IP..."
                            style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 10px;">📅 Date Range</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="date" name="date_from"
                                value="<?php echo htmlspecialchars($date_from ?? ''); ?>"
                                style="flex: 1; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                            <span style="color: #6b7280;">to</span>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to ?? ''); ?>"
                                style="flex: 1; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600;">Module</label>
                        <select name="f_module"
                            style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px; background: white;">
                            <option value="">All Modules</option>
                            <?php
                            $m_res = mysqli_query($conn, "SELECT DISTINCT module FROM audit_logs ORDER BY module");
                            if ($m_res) {
                                while ($m = mysqli_fetch_assoc($m_res)):
                                    ?>
                                            <option value="<?php echo htmlspecialchars($m['module']); ?>" <?php echo (isset($f_module) && $f_module == $m['module']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($m['module']); ?>
                                            </option>
                                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600;">Activity Type</label>
                        <select name="f_type"
                            style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px; background: white;">
                            <option value="">All Activities</option>
                            <?php
                            $t_res = mysqli_query($conn, "SELECT DISTINCT activity_type FROM audit_logs ORDER BY activity_type");
                            if ($t_res) {
                                while ($t = mysqli_fetch_assoc($t_res)):
                                    ?>
                                            <option value="<?php echo htmlspecialchars($t['activity_type']); ?>" <?php echo (isset($f_type) && $f_type == $t['activity_type']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($t['activity_type']); ?>
                                            </option>
                                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn btn-primary"
                            style="flex: 2; padding: 12px; background: #4f46e5; border: none;">Apply Filters</button>
                        <a href="?page=admin&master=audit_logs" class="btn btn-secondary"
                            style="flex: 1; padding: 12px; text-align: center; text-decoration: none;">Reset</a>
                    </div>
                </form>
            </div>
        </div>