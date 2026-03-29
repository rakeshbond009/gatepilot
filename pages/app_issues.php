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
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_app` (`app_name`),
      INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    @mysqli_query($support_conn, $sql);
}

// Get Support DB connection
$support_conn = getSupportDatabaseConnection();
if ($support_conn) {
    ensureAppIssuesTable($support_conn);
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
            $success_msg = "✅ Issue reported successfully! Our technical team will review it.";
            logActivity($conn, 'ISSUE_REPORTED', 'Support', "Reported a $type: $desc");
        } else {
            $error_msg = "❌ Error saving issue: " . mysqli_error($support_conn);
        }
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

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 25px;">
        <!-- Report Form -->
        <div class="card" style="padding: 30px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span style="background: #f1f5f9; padding: 8px; border-radius: 10px;">📝</span>
                New Issue Details
            </h3>

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

        <!-- History / Status -->
        <div class="card" style="padding: 30px; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span
                    style="background: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">📅</span>
                Previous Reports
            </h3>

            <div style="max-height: 600px; overflow-y: auto;">
                <?php if (empty($my_issues)): ?>
                    <div style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                        <div style="font-size: 50px; opacity: 0.3; margin-bottom: 15px;">🔍</div>
                        <p>No issues reported recently.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($my_issues as $iss): ?>
                        <div
                            style="background: white; border-radius: 15px; padding: 15px; margin-bottom: 15px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <span class="badge"
                                    style="background: <?php echo getStatusColor($iss['status']); ?>; color: white;">
                                    <?php echo $iss['status']; ?>
                                </span>
                                <small
                                    style="color: #94a3b8;"><?php echo date('d M, h:i A', strtotime($iss['reported_at'])); ?></small>
                            </div>
                            <div style="font-weight: 700; color: #1e293b; margin-bottom: 5px;">
                                <?php echo $iss['issue_type']; ?> - <?php echo $iss['priority']; ?>
                            </div>
                            <p
                                style="margin: 0; font-size: 14px; color: #475569; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                <?php echo htmlspecialchars($iss['description']); ?>
                            </p>
                            <?php if ($iss['admin_remarks']): ?>
                                <div
                                    style="margin-top: 10px; padding: 10px; background: #f1f5f9; border-radius: 8px; font-size: 12px; border-left: 3px solid #3b82f6;">
                                    <strong>Admin:</strong> <?php echo htmlspecialchars($iss['admin_remarks']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div
                style="margin-top: 20px; padding: 15px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="font-size: 20px;">📞</div>
                    <div>
                        <strong>Client Contact:</strong><br>
                        <?php echo $client_name; ?> (<?php echo $client_contact ?: 'No contact info'; ?>)
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
</script>

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