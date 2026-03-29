<?php
/**
 * Report App Issue / Bug - Centralized Logging
 */

// Function to initialize centralized table if not exists
function ensureAppIssuesTable($support_conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS `app_issues` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `app_name` VARCHAR(100) NOT NULL,
      `client_name` VARCHAR(100),
      `client_contact` VARCHAR(100),
      `reported_by` VARCHAR(50),
      `issue_type` ENUM('Bug', 'Feature Request', 'UI Issue', 'Access Issue', 'Other') DEFAULT 'Bug',
      `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
      `description` TEXT NOT NULL,
      `photo_url` VARCHAR(255),
      `status` ENUM('Pending', 'In Progress', 'Resolved', 'Closed', 'Invalid') DEFAULT 'Pending',
      `admin_remarks` TEXT,
      `status_history` TEXT,
      `reported_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_app` (`app_name`),
      INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    @mysqli_query($support_conn, $sql);

    // Migration: Add status_history if it doesn't exist
    $check = mysqli_query($support_conn, "SHOW COLUMNS FROM app_issues LIKE 'status_history'");
    if (mysqli_num_rows($check) == 0) {
        @mysqli_query($support_conn, "ALTER TABLE app_issues ADD COLUMN status_history TEXT AFTER admin_remarks");
    }
}

// Get Support DB connection
$support_conn = getSupportDatabaseConnection();
if ($support_conn) {
    ensureAppIssuesTable($support_conn);
}

// Check for session-based messages (from Post-Redirect-Get)
if (isset($_SESSION['issue_reported_success'])) {
    $success_msg = $_SESSION['issue_reported_success'];
    unset($_SESSION['issue_reported_success']);
}
if (isset($_SESSION['issue_reported_error'])) {
    $error_msg = $_SESSION['issue_reported_error'];
    unset($_SESSION['issue_reported_error']);
}

// Fetch Client Details from Master Database (Official registration info)
$client_name = $_SESSION['customer_name'] ?? 'Unnamed Client';
$client_contact = 'No contact info';

$master_conn = getMasterDatabaseConnection();
if ($master_conn && isset($_SESSION['tenant_slug'])) {
    $t_slug = mysqli_real_escape_string($master_conn, $_SESSION['tenant_slug']);
    $t_res = mysqli_query($master_conn, "SELECT customer_name, email, mobile FROM tenants WHERE slug = '$t_slug' LIMIT 1");
    if ($t_row = mysqli_fetch_assoc($t_res)) {
        $client_name = $t_row['customer_name'];
        $c_email = trim($t_row['email'] ?? '');
        $c_mobile = trim($t_row['mobile'] ?? '');
        $client_contact = trim("$c_email $c_mobile") ?: 'No contact info';
    }
}
// Fallback to local settings if Master DB fetch failed
if ($client_name === 'Unnamed Client') {
    $client_name = getSetting($conn, 'company_name', 'Unnamed Client');
}

$success_msg = null;
$error_msg = null;

// Handle Form Submission
if (isset($_POST['submit_issue'])) {
    if (!$support_conn) {
        $error_msg = "❌ Could not connect to the centralized support server. Please try again later.";
    } else {
        $type = mysqli_real_escape_string($support_conn, $_POST['issue_type']);
        $priority = mysqli_real_escape_string($support_conn, $_POST['priority']);
        $desc = mysqli_real_escape_string($support_conn, $_POST['description']);
        $reported_by = mysqli_real_escape_string($support_conn, $_SESSION['username'] ?? 'Anonymous');
        $app_name = mysqli_real_escape_string($support_conn, CLIENT_APP_NAME);

        // Crucial: Escape client details for the support database connection
        $client_name_save = mysqli_real_escape_string($support_conn, $client_name);
        $client_contact_save = mysqli_real_escape_string($support_conn, $client_contact);

        // Handle Photo Upload (Local server first, then save path)
        $photo_path = '';
        if (isset($_FILES['issue_photo']) && $_FILES['issue_photo']['error'] == 0) {
            $upload_dir = 'uploads/issues/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);
            $ext = pathinfo($_FILES['issue_photo']['name'], PATHINFO_EXTENSION);
            $filename = 'issue_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['issue_photo']['tmp_name'], $upload_dir . $filename)) {
                $photo_path = mysqli_real_escape_string($support_conn, $upload_dir . $filename);
            }
        }

        $sql = "INSERT INTO app_issues (app_name, client_name, client_contact, reported_by, issue_type, priority, description, photo_url) 
                VALUES ('$app_name', '$client_name_save', '$client_contact_save', '$reported_by', '$type', '$priority', '$desc', '$photo_path')";

        if (mysqli_query($support_conn, $sql)) {
            $ticket_id = mysqli_insert_id($support_conn);
            $_SESSION['issue_reported_success'] = "✅ Issue reported successfully! Our technical team will review it.";
            
            // Log with structured details for better visibility in Audit Logs
            $log_details = "Issue Reported: Ticket #$ticket_id\nCategory: $type\nPriority: $priority\nDescription: $desc";
            if (!empty($photo_path)) {
                $log_details .= "\nPhoto Upload: $photo_path";
            }
            logActivity($conn, 'ISSUE_REPORTED', 'Support', $log_details);

            // Redirect to the same page to prevent duplicate submissions on refresh
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error_msg = "❌ Error saving issue: " . mysqli_error($support_conn);
        }
    }
}

// Handle Admin Update (Status & Remarks)
if (isset($_POST['update_issue_status']) && $support_conn) {
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'developer' || $_SESSION['role'] == 'manager') {
        $issue_id = intval($_POST['issue_id']);
        $new_status = mysqli_real_escape_string($support_conn, $_POST['new_status']);
        $new_remarks = mysqli_real_escape_string($support_conn, $_POST['admin_remarks']);
        $admin_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';

        // Fetch current context for the audit log and history append
        $issue_q = mysqli_query($support_conn, "SELECT * FROM app_issues WHERE id = $issue_id");
        $issue_details = mysqli_fetch_assoc($issue_q);
        
        $type = $issue_details['issue_type'] ?? 'Issue';
        $desc = $issue_details['description'] ?? '';
        $priority = $issue_details['priority'] ?? 'Medium';
        $photo_url = $issue_details['photo_url'] ?? '';
        $old_status = $issue_details['status'] ?? 'Open';
        $old_remarks = !empty($issue_details['admin_remarks']) ? $issue_details['admin_remarks'] : 'None';
        $old_history = $issue_details['status_history'] ?? '';

        // Prepare the history log entry
        $timestamp = date('d M, h:i A');
        $log_entry = "[$timestamp] $new_status: $new_remarks (by $admin_name)";
        $updated_history = $log_entry . ($old_history ? "\n" . $old_history : "");
        $updated_history_esc = mysqli_real_escape_string($support_conn, $updated_history);

        $sql = "UPDATE app_issues SET 
                status = '$new_status', 
                admin_remarks = '$new_remarks', 
                status_history = '$updated_history_esc' 
                WHERE id = $issue_id";

        if (mysqli_query($support_conn, $sql)) {
            $_SESSION['issue_reported_success'] = "✅ Issue updated successfully!";
            
            // Log the status update in System Audit Logs with the "Boxed UI" format
            $log_details = "Support Update: Ticket #$issue_id Status Changed\n";
            $log_details .= "Initial Record Details:\n";
            $log_details .= "Issue: [$issue_id]\n";
            $log_details .= "Category: [$type]\n";
            $log_details .= "Priority: [$priority]\n";
            $log_details .= "Description: [$desc]\n";
            if ($photo_url) {
                $log_details .= "Photo: [$photo_url]\n";
            }

            // Changes Section (Highlighting transitions)
            $log_details .= "Changes:\n";
            $log_details .= "Status: [$old_status ➔ $new_status]\n";
            $log_details .= "Remarks: [$old_remarks ➔ $new_remarks]";

            logActivity($conn, 'ISSUE_UPDATE', 'Support', $log_details);




            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error_msg = "❌ Error updating issue: " . mysqli_error($support_conn);
        }
    } else {
        $error_msg = "❌ Unauthorized action.";
    }
}

// If admin/manager, fetch all issues from this client
$my_issues = [];
if ($support_conn) {
    $app_name_esc = mysqli_real_escape_string($support_conn, CLIENT_APP_NAME);
    $client_name_esc = mysqli_real_escape_string($support_conn, $client_name);
    // Show issues for this specific client and app
    $query = "SELECT * FROM app_issues WHERE app_name = '$app_name_esc' AND client_name = '$client_name_esc' ORDER BY reported_at DESC";
    $res = mysqli_query($support_conn, $query);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res))
            $my_issues[] = $row;
    }
}
?>

<div class="container" style="padding-bottom: 120px;">
    <div
        style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-radius: 20px; padding: 30px; margin-bottom: 25px; color: white; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="font-size: 50px;">🔧</div>
            <div>
                <h1 style="margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">Report an Issue</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 15px;">Found a bug or need a feature? Let our
                    developers know.</p>
            </div>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div
            style="background: #ecfdf5; color: #065f46; padding: 20px; border-radius: 12px; border-left: 6px solid #10b981; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; animation: slideIn 0.3s ease-out;">
            <div style="font-size: 24px;">✅</div>
            <div><?php echo $success_msg; ?></div>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div
            style="background: #fef2f2; color: #991b1b; padding: 20px; border-radius: 12px; border-left: 6px solid #ef4444; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 24px;">❌</div>
            <div><?php echo $error_msg; ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr; gap: 25px;">
        <!-- Report Form -->
        <div class="card"
            style="padding: 30px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #f1f5f9; padding: 8px; border-radius: 10px;">📝</span>
                    New Issue Details
                </h3>
                <button type="button" onclick="openReportsModal()"
                    style="background: #f1f5f9; color: #4f46e5; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                    📂 View Previous Reports (<?php echo count($my_issues); ?>)
                </button>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Issue
                            Category *</label>
                        <select name="issue_type" required
                            style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #fff;">
                            <option value="Bug">🪲 App Bug / Error</option>
                            <option value="UI Issue">🎨 Display/Design Problem</option>
                            <option value="Access Issue">🔐 Login/Permission Issue</option>
                            <option value="Feature Request">💡 Feature Request</option>
                            <option value="Other">❓ Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Priority
                            Level *</label>
                        <select name="priority" required
                            style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #fff;">
                            <option value="Low">Low - Improvement</option>
                            <option value="Medium" selected>Medium - Minor Issue</option>
                            <option value="High">High - Hindering Work</option>
                            <option value="Critical">Critical - System Down</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Description
                        *</label>
                    <textarea name="description" required rows="5"
                        placeholder="Please describe the issue in detail. What happened? How to reproduce it?"
                        style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 15px; resize: vertical;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Attachment
                        (Optional)</label>
                    <div style="border: 2px dashed #e2e8f0; padding: 20px; border-radius: 12px; text-align: center; background: #f8fafc; cursor: pointer; position: relative;"
                        onclick="document.getElementById('issue_photo').click()">
                        <div style="font-size: 30px; margin-bottom: 10px;">📸</div>
                        <div style="font-size: 14px; color: #64748b;">Click to take a photo or upload screenshot</div>
                        <input type="file" name="issue_photo" id="issue_photo" accept="image/*" style="display: none;"
                            onchange="previewImage(this)">
                        <div id="photo_preview" style="display: none; margin-top: 15px;">
                            <img id="preview_img" src=""
                                style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit_issue" class="btn btn-primary"
                    style="width: 100%; padding: 15px; font-size: 16px; font-weight: 700; border-radius: 12px; background: #4f46e5; border: none; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);">
                    🚀 Submit Report
                </button>
            </form>
        </div>

    </div>
</div>
</div>

<!-- Previous Reports Modal -->
<div id="reportsModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div
        style="background: #f8fafc; border-radius: 24px; width: 98%; max-width: 1400px; height: 95vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); animation: zoomIn 0.3s ease-out;">
        <!-- Modal Header with Filters -->
        <div style="padding: 25px 40px; background: white; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0; color: #1e293b; display: flex; align-items: center; gap: 12px;">
                        <span style="background: #eef2ff; padding: 10px; border-radius: 14px;">📅</span>
                        Issue History & Reports
                    </h2>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #64748b;">Manage and track your reported app
                        issues.</p>
                </div>
                <button onclick="closeReportsModal()"
                    style="background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 15px; align-items: center;">
                <div style="position: relative;">
                    <span
                        style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.5;">🔍</span>
                    <input type="text" id="reportSearch" placeholder="Search by description or type..."
                        style="width: 100%; padding: 12px 12px 12px 45px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 15px;">
                </div>
                <div>
                    <input type="date" id="dateFrom" class="form-control"
                        style="border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 11px;">
                </div>
                <div>
                    <input type="date" id="dateTo" class="form-control"
                        style="border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 11px;">
                </div>
                <select id="statusFilter"
                    style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff; font-size: 14px; font-weight: 600; color: #475569;">
                    <option value="all">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
                </select>
                <div
                    style="background: #4f46e5; color: white; padding: 10px 15px; border-radius: 12px; font-weight: 700; font-size: 12px; white-space: nowrap;">
                    Total: <span id="visibleCount"><?php echo count($my_issues); ?></span>
                </div>
            </div>
        </div>

        <div id="modalReportsList" style="padding: 30px 40px; overflow-y: auto; flex: 1; background: #eaeff5;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(500px, 1fr)); gap: 30px;">
                <?php if (empty($my_issues)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #94a3b8;">
                        <div style="font-size: 70px; opacity: 0.3; margin-bottom: 20px;">✨</div>
                        <p>No issues have been reported yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($my_issues as $iss): ?>
                        <div class="report-card" data-status="<?php echo $iss['status']; ?>"
                            data-content="<?php echo strtolower($iss['description'] . ' ' . $iss['issue_type']); ?>"
                            data-date="<?php echo date('Y-m-d', strtotime($iss['reported_at'])); ?>"
                            style="background: white; border-radius: 24px; padding: 30px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.07); display: flex; flex-direction: column; position: relative;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <span class="badge"
                                    style="background: <?php echo getStatusColor($iss['status']); ?>; color: white; padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                    <?php echo $iss['status']; ?>
                                </span>
                                <div style="text-align: right;">
                                    <div style="color: #64748b; font-size: 11px; font-weight: 600;">
                                        <?php echo date('d M Y', strtotime($iss['reported_at'])); ?></div>
                                    <div style="color: #94a3b8; font-size: 10px;">
                                        <?php echo date('h:i A', strtotime($iss['reported_at'])); ?></div>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <div style="font-weight: 800; font-size: 16px; color: #1e293b; margin-bottom: 4px;">
                                    <?php echo $iss['issue_type']; ?></div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span
                                        style="font-size: 11px; font-weight: 700; color: #4f46e5; text-transform: uppercase;">Priority:
                                        <?php echo $iss['priority']; ?></span>
                                    <span style="color: #cbd5e1;">&bull;</span>
                                    <span style="font-size: 11px; color: #64748b;">ID: #<?php echo $iss['id']; ?></span>
                                </div>
                            </div>

                            <p
                                style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6; max-height: 80px; overflow-y: auto; padding: 12px; background: #f8fafc; border-radius: 12px; margin-bottom: 15px;">
                                <?php echo htmlspecialchars($iss['description']); ?>
                            </p>

                            <!-- Timeline-style Audit History Section -->
                            <?php if (!empty($iss['status_history']) || !empty($iss['admin_remarks'])): ?>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                                    <div
                                        style="font-size: 11px; font-weight: 800; color: #4f46e5; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">
                                        Activity Timeline</div>
                                    <div
                                        style="position: relative; padding-left: 20px; border-left: 2px solid #eef2ff; margin-left: 5px; max-height: 250px; overflow-y: auto;">
                                        <?php if (!empty($iss['status_history'])): ?>
                                            <?php
                                            $logs = explode("\n", $iss['status_history']);
                                            foreach ($logs as $log):
                                                if (trim($log) == '')
                                                    continue;
                                                // Extract timestamp and content if possible
                                                $display_log = htmlspecialchars($log);
                                                $bullet_color = (strpos(strtolower($log), 'resolved') !== false || strpos(strtolower($log), 'closed') !== false) ? '#10b981' : '#3b82f6';
                                                ?>
                                                <div style="position: relative; margin-bottom: 15px;">
                                                    <div
                                                        style="position: absolute; left: -26px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: white; border: 2.5px solid <?php echo $bullet_color; ?>;">
                                                    </div>
                                                    <div style="font-size: 13px; color: #334155; line-height: 1.5;">
                                                        <?php echo $display_log; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div style="position: relative; margin-bottom: 10px;">
                                                <div
                                                    style="position: absolute; left: -26px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: white; border: 2.5px solid #10b981;">
                                                </div>
                                                <div style="font-size: 13px; color: #334155;">
                                                    <strong><?php echo $iss['status']; ?>:</strong>
                                                    <?php echo htmlspecialchars($iss['admin_remarks']); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Admin Quick Action -->
                            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'developer' || $_SESSION['role'] == 'manager'): ?>
                                <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
                                    <button
                                        onclick='event.stopPropagation(); openUpdateModal(<?php echo $iss["id"]; ?>, "<?php echo $iss["status"]; ?>", <?php echo json_encode($iss["admin_remarks"] ?? ""); ?>)'
                                        style="background: #4f46e5; border: none; padding: 8px 16px; border-radius: 10px; font-size: 11px; font-weight: 700; cursor: pointer; color: white;">
                                        ⚙️ Update Status
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview_img').src = e.target.result;
                document.getElementById('photo_preview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openReportsModal() {
        document.getElementById('reportsModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeReportsModal() {
        document.getElementById('reportsModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function openUpdateModal(id, status, remarks) {
        document.getElementById('update_issue_id').value = id;
        document.getElementById('update_status_select').value = status;
        document.getElementById('update_remarks_text').value = remarks || '';
        document.getElementById('updateIssueModal').style.display = 'flex';
    }

    function closeUpdateModal() {
        document.getElementById('updateIssueModal').style.display = 'none';
    }

    // Dynamic Filter Logic
    function filterReports() {
        const searchText = document.getElementById('reportSearch').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const fromDate = document.getElementById('dateFrom').value;
        const toDate = document.getElementById('dateTo').value;

        const cards = document.querySelectorAll('.report-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const content = card.getAttribute('data-content');
            const status = card.getAttribute('data-status');
            const dateStr = card.getAttribute('data-date');

            const matchesSearch = content.includes(searchText);
            const matchesStatus = (statusFilter === 'all' || status === statusFilter);

            let matchesDate = true;
            if (fromDate && dateStr < fromDate) matchesDate = false;
            if (toDate && dateStr > toDate) matchesDate = false;

            if (matchesSearch && matchesStatus && matchesDate) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visibleCount;
    }

    document.getElementById('reportSearch').addEventListener('input', filterReports);
    document.getElementById('statusFilter').addEventListener('change', filterReports);
    document.getElementById('dateFrom').addEventListener('change', filterReports);
    document.getElementById('dateTo').addEventListener('change', filterReports);

    // Close modal on outside click
    window.onclick = function (event) {
        let updateModal = document.getElementById('updateIssueModal');
        let reportsModal = document.getElementById('reportsModal');
        if (event.target == updateModal) closeUpdateModal();
        if (event.target == reportsModal) closeReportsModal();
    }
</script>

<!-- Admin Update Modal -->
<div id="updateIssueModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div
        style="background: white; border-radius: 20px; width: 90%; max-width: 450px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <h2 style="margin-top: 0; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; color: #1e293b;">
            <span style="background: #f1f5f9; padding: 10px; border-radius: 12px;">⚙️</span>
            Update Issue Status
        </h2>

        <form method="POST">
            <input type="hidden" name="issue_id" id="update_issue_id">

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #64748b;">Current
                    Status</label>
                <select name="new_status" id="update_status_select" required
                    style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff; font-size: 15px; color: #1e293b;">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
                    <option value="Invalid">Invalid</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label
                    style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #64748b;">Administrator
                    Remarks</label>
                <textarea name="admin_remarks" id="update_remarks_text" required rows="4"
                    placeholder="Add your feedback or update here..."
                    style="width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff; font-size: 15px; resize: none;"></textarea>
                <p style="margin-top: 8px; font-size: 12px; color: #94a3b8;">* These remarks will be appended to the
                    issue history.</p>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="closeUpdateModal()"
                    style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" name="update_issue_status"
                    style="flex: 2; padding: 12px; border-radius: 12px; border: none; background: #4f46e5; color: white; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">Save
                    Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes popIn {
        from {
            transform: scale(0.95);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes zoomIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .report-card:hover {
        transform: translateY(-3px);
        border-color: #4f46e5;
    }

    .report-card {
        transition: all 0.2s ease;
    }
</style>

<?php
function getStatusColor($status)
{
    switch ($status) {
        case 'Resolved':
            return '#10b981';
        case 'In Progress':
            return '#3b82f6';
        case 'Closed':
            return '#64748b';
        case 'Invalid':
            return '#ef4444';
        default:
            return '#f59e0b';
    }
}
?>