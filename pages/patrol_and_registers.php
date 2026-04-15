<?php if ($page == 'guard-patrol'):

    // Get recent patrol logs for this guard today
    $guard_id = $_SESSION['user_id'];
    $recent_patrols = mysqli_query($conn, "SELECT pl.*, loc.location_name, loc.area_site_building 
                                          FROM patrol_logs pl 
                                          JOIN patrol_locations loc ON pl.location_id = loc.id 
                                          WHERE pl.guard_id = $guard_id AND DATE(pl.scan_datetime) = CURDATE() 
                                          ORDER BY pl.scan_datetime DESC LIMIT 10");
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

        <button type="button" onclick="goBack();" class="btn btn-secondary btn-full"
            style="margin-bottom: 15px; display: block; position: relative; z-index: 10; width: 100%; text-align: left;">
            ← Back
        </button>

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">👮</div>
                <div>
                    <h1
                        style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Guard Patrol</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Scan location QR
                        codes
                        during your patrol</p>
                </div>
            </div>
        </div>

        <!-- Scanner Section -->
        <div class="card" style="text-align: center; padding: 40px 20px; border-top: 5px solid #4f46e5;">
            <div style="font-size: 64px; margin-bottom: 20px;">📸</div>
            <h2 style="margin-bottom: 10px;">Ready to Scan?</h2>
            <p style="color: #666; margin-bottom: 30px;">Point your camera at the location's QR code to log your
                visit.
            </p>

            <button type="button" onclick="openPatrolScanner()" class="btn"
                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 18px 40px; font-size: 18px; border-radius: 15px; font-weight: 700; width: 100%; max-width: 300px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);">
                📷 START SCANNER
            </button>

            <form method="POST" id="patrolForm" style="display: none;">
                <input type="hidden" name="qr_data" id="patrol_qr_data">
                <input type="hidden" name="patrol_scan" value="1">
            </form>
            <div style="margin-top: 20px;">
                <button onclick="document.getElementById('reportIssueModal').style.display='flex'" class="btn"
                    style="background: #f59e0b; color: white; padding: 12px 30px; border-radius: 12px; font-weight: 600; border: none; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);">
                    ⚠️ Report Issue
                </button>
            </div>
        </div>

        <!-- Report Issue Modal -->
        <div id="reportIssueModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
            <div
                style="background: white; width: 90%; max-width: 500px; padding: 25px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 20px; color: #1f2937;">Report an Issue</h3>
                    <button onclick="document.getElementById('reportIssueModal').style.display='none'"
                        style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="report_issue" value="1">

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label
                            style="display: block; font-weight: 600; margin-bottom: 5px; color: #374151;">Location</label>
                        <select name="location_id" required
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                            <option value="">-- Select Location --</option>
                            <?php
                            $report_locs = mysqli_query($conn, "SELECT id, location_name FROM patrol_locations WHERE is_active=1 ORDER BY location_name");
                            while ($l = mysqli_fetch_assoc($report_locs)):
                                ?>
                                <option value="<?php echo $l['id']; ?>">
                                    <?php echo htmlspecialchars($l['location_name']); ?>
                                </option>
                                <?php
                            endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label
                            style="display: block; font-weight: 600; margin-bottom: 5px; color: #374151;">Description</label>
                        <textarea name="description" required rows="3" placeholder="Describe the issue..."
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #374151;">Photo
                            Evidence</label>

                        <!-- Hidden inputs for Patrol (Robust Hiding - slightly exposed for WebView) -->
                        <div
                            style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                            <input type="file" name="issue_photo" id="issue_photo_file" accept="image/*"
                                onchange="updateIssuePhotoPreview(this)">
                            <input type="file" id="issue_photo_camera" accept="image/*"
                                onchange="transferIssueCameraFile(this)">
                        </div>

                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <label for="issue_photo_camera" id="issue_camera_label" class="btn"
                                style="background: #3b82f6; color: white; padding: 10px; flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-bottom: 0;">
                                📷 Camera
                            </label>
                            <label for="issue_photo_file" class="btn"
                                style="background: #6366f1; color: white; padding: 10px; flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-bottom: 0;">
                                📁 Gallery
                            </label>
                        </div>
                        <div id="issue_photo_preview" style="margin-top: 10px;"></div>

                        <script>
                            function updateIssuePhotoPreview(input) {
                                if (input.files && input.files[0]) {
                                    // RELIABLE WEBVIEW FIX: If this is the file input, ensure it has the name
                                    if (input.id === 'issue_photo_file') {
                                        input.name = "issue_photo";
                                        const cameraInput = document.getElementById('issue_photo_camera');
                                        if (cameraInput) cameraInput.name = "";
                                    }

                                    const reader = new FileReader();
                                    reader.onload = function (e) {
                                        document.getElementById('issue_photo_preview').innerHTML =
                                            '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">' +
                                            '<div style="font-size:12px; color:#10b981; margin-top:5px;">✅ Photo Selected</div>';
                                    }
                                    reader.readAsDataURL(input.files[0]);
                                }
                            }

                            function transferIssueCameraFile(cameraInput) {
                                if (cameraInput.files && cameraInput.files[0]) {
                                    // RELIABLE WEBVIEW FIX: Give this input the name and remove from the other
                                    const mainInput = document.getElementById('issue_photo_file');
                                    cameraInput.name = "issue_photo";
                                    mainInput.name = "";

                                    // Update preview
                                    updateIssuePhotoPreview(cameraInput);

                                    // Visual feedback
                                    document.getElementById('issue_camera_label').style.background = '#10b981';
                                    document.getElementById('issue_camera_label').innerHTML = '✅ Photo Captured';
                                }
                            }
                            // Intercept camera label click on desktop to show webcam modal
                            document.addEventListener('DOMContentLoaded', function () {
                                const cameraLabel = document.getElementById('issue_camera_label');
                                if (cameraLabel) {
                                    cameraLabel.addEventListener('click', function (e) {
                                        if (!detectMobileForWebcam()) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (typeof openWebcamCapture === 'function') {
                                                openWebcamCapture('issue_photo_file', 'updateIssuePhotoPreview');
                                            }
                                        }
                                    });
                                }
                            });
                        </script>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; padding: 12px; font-weight: bold;">Submit Report</button>
                </form>
            </div>
        </div>

        <!-- Recent Patrols -->
        <div class="card" style="margin-top: 25px;">
            <h3 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>🕒</span> Your Scans Today
            </h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Area/Building</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_patrols) == 0): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 20px; color: #999;">No locations
                                    scanned
                                    yet today</td>
                            </tr>
                            <?php
                        else: ?>
                            <?php while ($row = mysqli_fetch_assoc($recent_patrols)): ?>
                                <tr>
                                    <td><strong>
                                            <?php echo htmlspecialchars($row['location_name']); ?>
                                        </strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['area_site_building']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('h:i A', strtotime($row['scan_datetime'])); ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile; ?>
                            <?php
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Patrol Scanner Modal (Reuse existing scanner logic) -->
    <div id="patrolScannerModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; overflow-y: auto;">
        <div
            style="position: relative; max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
            <div
                style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #1e293b; color: white;">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📷</span> Scan Patrol QR
                </h3>
                <button type="button" onclick="closePatrolScanner()"
                    style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; padding: 8px 15px; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.1)'">✕ Close</button>
            </div>
            <!-- Camera Selection for Patrol -->
            <div style="padding: 10px 20px; background: #334155; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <select id="patrolDeviceSelect" onchange="switchPatrolCamera(this.value)"
                    style="width: 100%; padding: 8px; background: #1e293b; color: white; border: 1px solid #475569; border-radius: 6px; cursor: pointer; outline: none; font-size: 13px;">
                    <option value="">Detecting cameras...</option>
                </select>
            </div>



            <div id="patrol-qr-wrapper"
                style="position: relative; width: 100%; height: 450px; background: #000; overflow: hidden !important; border-radius: 8px;">
                <style>
                    #patrol-qr-reader {
                        width: 100% !important;
                        height: 100% !important;
                        border: none !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    #patrol-qr-reader video {
                        position: absolute !important;
                        top: 50% !important;
                        left: 50% !important;
                        transform: translate(-50%, -50%) !important;
                        min-width: 100% !important;
                        min-height: 100% !important;
                        width: auto !important;
                        height: auto !important;
                        object-fit: cover !important;
                        display: block !important;
                    }

                    /* Hide library clutter */
                    #patrol-qr-reader img[alt="Camera based scan"] {
                        display: none !important;
                    }

                    #patrol-qr-reader div[style*="text-align: center"] {
                        display: none !important;
                    }

                    #patrol-qr-reader__dashboard {
                        display: none !important;
                    }
                </style>
                <div id="patrol-qr-reader"></div>
            </div>
            <div id="zoomContainerPatrol"
                style="display:none; margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 8px;">
                <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #475569;">🔍
                    Camera Zoom Control</label>
                <input type="range" id="zoomInputPatrol"
                    style="width: 100%; height: 8px; border-radius: 4px; background: #e2e8f0; cursor: pointer;">
            </div>
            <div id="patrolScannerStatus" class="alert alert-info" style="margin-top: 15px; display: none;"></div>
        </div>
    </div>

    <script>
        let patrolScanner = null;
        let isScanningPatrol = false;

        function openPatrolScanner() {
            document.getElementById('patrolScannerModal').style.display = 'block';
            // Always start scanner after a short delay for smooth DOM mounting
            setTimeout(() => {
                startPatrolScanner();
            }, 400);
        }

        function closePatrolScanner() {
            stopPatrolScanner();
            document.getElementById('patrolScannerModal').style.display = 'none';
        }

        function showPatrolStatus(message, type = 'info') {
            const statusDiv = document.getElementById('patrolScannerStatus');
            statusDiv.style.display = 'block';
            statusDiv.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'error' ? 'error' : 'info');
            statusDiv.textContent = message;
        }

        function startPatrolScanner() {
            if (isScanningPatrol) return;

            // Native Flutter Bridge detection
            if (window.FlutterScanner || window.FlutterScannerChannel) {
                if (!window.FlutterScanner && window.FlutterScannerChannel) {
                    window.FlutterScanner = {
                        postMessage: function (msg) { window.FlutterScannerChannel.postMessage(msg); }
                    };
                }
                window.onNativeScanSuccess = function (decodedText) {
                    onPatrolCodeScanned(decodedText);
                };
                window.FlutterScanner.postMessage('startScan');
                return;
            }

            const stopBtn = document.getElementById('stopScanBtnPatrol');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showPatrolStatus('❌ Camera access not supported.', 'error');
                return;
            }

            const readerDiv = document.getElementById("patrol-qr-reader");
            if (!readerDiv) return;
            readerDiv.innerHTML = "";

            patrolScanner = new Html5Qrcode("patrol-qr-reader");

            // Get preferred camera from storage
            const preferredId = localStorage.getItem('patrolCameraId');
            const facingMode = preferredId ? preferredId : (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
                ? { facingMode: "environment" }
                : { facingMode: "user" });

            const config = {
                fps: 20,
                qrbox: (w, h) => { return { width: Math.min(w, h) * 0.8, height: Math.min(w, h) * 0.8 }; }
            };

            patrolScanner.start(
                facingMode,
                config,
                (decodedText) => onPatrolCodeScanned(decodedText),
                (err) => { }
            ).then(() => {
                isScanningPatrol = true;
                if (stopBtn) stopBtn.style.display = 'inline-block';
                showPatrolStatus('📷 Camera started. Point at QR code...', 'info');
                refreshPatrolCameraList();
            }).catch(err => {
                console.error("Scanner Error:", err);
                showPatrolStatus('❌ Error: ' + err, 'error');
                isScanningPatrol = false;
            });
        }

        function refreshPatrolCameraList() {
            Html5Qrcode.getCameras().then(devices => {
                const select = document.getElementById('patrolDeviceSelect');
                const preferredId = localStorage.getItem('patrolCameraId');

                if (devices && devices.length > 0) {
                    select.innerHTML = '<option value="">-- Switch Camera --</option>';
                    devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.label || `Camera ${select.length}`;
                        if (device.id === preferredId) option.selected = true;
                        select.appendChild(option);
                    });
                } else {
                    select.innerHTML = '<option value="">No cameras found</option>';
                }
            }).catch(err => {
                console.error("Camera List Error:", err);
            });
        }

        async function switchPatrolCamera(deviceId) {
            if (!deviceId || !patrolScanner) return;

            showPatrolStatus('🔄 Switching camera...', 'info');

            try {
                // 1. Only stop if it's currently running to avoid "Cannot stop" errors
                if (isScanningPatrol) {
                    await patrolScanner.stop();
                    isScanningPatrol = false;
                }

                // 2. Clear status to visually indicate a clean slate
                showPatrolStatus('⏳ Release camera hardware... Please wait.', 'info');

                // 3. Mandatory hardware release delay (500ms)
                setTimeout(async () => {
                    const config = {
                        fps: 20,
                        qrbox: (w, h) => { return { width: Math.min(w, h) * 0.8, height: Math.min(w, h) * 0.8 }; }
                    };

                    try {
                        await patrolScanner.start(
                            deviceId,
                            config,
                            (decodedText) => onPatrolCodeScanned(decodedText),
                            (err) => { }
                        );
                        isScanningPatrol = true;
                        localStorage.setItem('patrolCameraId', deviceId); // SAVE PREFERENCE
                        showPatrolStatus('📷 Camera switched successfully.', 'success');
                    } catch (err) {
                        console.error("Start Error:", err);
                        const msg = err.name === 'NotReadableError' ? '❌ Camera Busy. Please close other apps using it.' : '❌ Switch Error: ' + err;
                        showPatrolStatus(msg, 'error');
                    }
                }, 600);
            } catch (err) {
                console.error("Cleanup Error:", err);
                showPatrolStatus('❌ Cleanup Error: ' + err, 'error');
            }
        }

        async function stopPatrolScanner() {
            if (!patrolScanner || !isScanningPatrol) return;

            isScanningPatrol = false; // Prevent double-trigger immediately
            try {
                await patrolScanner.stop();
                patrolScanner.clear();
                const stopBtn = document.getElementById('stopScanBtnPatrol');
                if (stopBtn) stopBtn.style.display = 'none';
            } catch (err) {
                console.error('Failed to stop scanner:', err);
            }
        }

        function onPatrolCodeScanned(decodedText) {
            if (!decodedText || decodedText.trim() === '') return;

            showPatrolStatus('✅ Scanned! Processing...', 'success');

            // Handle stop based on mode
            if (window.FlutterScanner) {
                // In native app, just submit
                submitPatrol(decodedText);
            } else {
                // In web, stop scanner then submit
                patrolScanner.stop().then(() => {
                    isScanningPatrol = false;
                    submitPatrol(decodedText);
                }).catch(() => {
                    submitPatrol(decodedText);
                });
            }
        }

        function submitPatrol(qrData) {
            document.getElementById('patrol_qr_data').value = qrData;
            document.getElementById('patrolForm').submit();
        }
    </script>
    <?php
    // ==================== TICKETS / ISSUE MANAGEMENT ====================

elseif ($page == 'tickets'):
    requireLogin();
    // Permission Check (Managers/Admins)
    if (!hasPermission('pages.management')) {
        echo "<div class='container'><div class='alert alert-error'>Access Denied</div></div>";
        exit;
    }

    // Ensure assigned_at column exists (migration for older DBs)
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM patrol_issues LIKE 'assigned_at'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE patrol_issues ADD COLUMN assigned_at DATETIME DEFAULT NULL AFTER assigned_to");
    }

    // Handle Status Updates/Assignment
    if (isset($_POST['update_ticket'])) {
        $ticket_id = intval($_POST['ticket_id']);
        $action = $_POST['action'];
        $remarks = isset($_POST['remarks']) ? mysqli_real_escape_string($conn, $_POST['remarks']) : '';

        // Fetch ticket, location, reporter and currently assigned employee name for audit log
        $t_info_res = mysqli_query($conn, "
            SELECT t.issue_description, t.status, pl.location_name, em.employee_name as assigned_to_name,
                   um.full_name as reported_by_name
            FROM patrol_issues t 
            LEFT JOIN patrol_locations pl ON t.location_id = pl.id 
            LEFT JOIN employee_master em ON t.assigned_to = em.id
            LEFT JOIN user_master um ON t.reported_by = um.id
            WHERE t.id = $ticket_id
        ");
        $t_info = mysqli_fetch_assoc($t_info_res);
        $issue_desc = $t_info['issue_description'] ?? 'N/A';
        $location = $t_info['location_name'] ?? 'N/A';
        $old_status = $t_info['status'] ?? 'N/A';
        $existing_assigned_name = $t_info['assigned_to_name'] ?? 'None';
        $reported_by_name = $t_info['reported_by_name'] ?? 'Unknown';

        if ($action == 'assign') {
            $assign_to = intval($_POST['employee_id']);
            $sql = "UPDATE patrol_issues SET status='Assigned', assigned_to=$assign_to, assigned_at=NOW(), updated_at=NOW() WHERE id=$ticket_id";

            // Get employee name for log
            $e_info_res = mysqli_query($conn, "SELECT employee_name FROM employee_master WHERE id = $assign_to");
            $employee_name = ($e_row = mysqli_fetch_assoc($e_info_res)) ? $e_row['employee_name'] : 'Unknown';
            $new_status = 'Assigned';
        } elseif ($action == 'resolve') {
            $sql = "UPDATE patrol_issues SET status='Resolved', resolution_remarks='$remarks', resolved_at=NOW(), updated_at=NOW() WHERE id=$ticket_id";
            $new_status = 'Resolved';
        } elseif ($action == 'close') {
            $sql = "UPDATE patrol_issues SET status='Closed', closing_remarks='$remarks', updated_at=NOW() WHERE id=$ticket_id";
            $new_status = 'Closed';
        }

        if (isset($sql) && mysqli_query($conn, $sql)) {
            $_SESSION['success_msg'] = "✅ Ticket updated successfully.";

            // Enhanced Audit Log: Show transitions
            $details = "Ticket ID: [$ticket_id]\n";
            $details .= "Reported By: [$reported_by_name]\n";
            $details .= "Location: [$location]\n";
            $details .= "Issue: [$issue_desc]\n";
            $details .= "Status Change: [$old_status ➔ $new_status]\n";

            if ($action == 'assign') {
                $details .= "Assigned To: [$existing_assigned_name ➔ $employee_name]\n";
            } else {
                $details .= "Assigned To: [$existing_assigned_name]\n";
            }

            if (!empty($remarks)) {
                $details .= ($action == 'resolve' ? "Resolution: " : "Closing Remarks: ") . "[$remarks]";
            }

            logActivity($conn, 'TICKET_UPDATE', 'Patrol', trim($details));
            header("Location: ?page=tickets&tab=" . (in_array($action, ['resolve', 'close']) ? 'resolved' : 'open') . "&t=" . time());
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error updating ticket: " . mysqli_error($conn);
            header("Location: ?page=tickets&tab=" . $tab);
            exit;
        }
    }

    // Fetch Data
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'open';
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $loc_filter = isset($_GET['loc_filter']) ? intval($_GET['loc_filter']) : 0;
    $emp_filter = isset($_GET['emp_filter']) ? intval($_GET['emp_filter']) : 0;
    $status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';

    $where = "WHERE 1";
    if ($status_filter) {
        $where .= " AND t.status = '$status_filter'";
    } else {
        if ($tab == 'open')
            $where .= " AND t.status IN ('Open', 'Assigned')";
        if ($tab == 'resolved')
            $where .= " AND t.status IN ('Resolved', 'Closed')";
    }

    if ($search) {
        $where .= " AND (t.issue_description LIKE '%$search%' OR pl.location_name LIKE '%$search%' OR t.id = '$search')";
    }
    if ($loc_filter > 0) {
        $where .= " AND t.location_id = $loc_filter";
    }
    if ($emp_filter > 0) {
        $where .= " AND t.assigned_to = $emp_filter";
    }

    // Filter by Aging
    if (isset($_GET['aging'])) {
        $aging = $_GET['aging'];
        if ($aging == 'less_24h') {
            $where .= " AND TIMESTAMPDIFF(HOUR, t.created_at, NOW()) < 24";
        } elseif ($aging == '24_48h') {
            $where .= " AND TIMESTAMPDIFF(HOUR, t.created_at, NOW()) BETWEEN 24 AND 48";
        } elseif ($aging == 'more_48h') {
            $where .= " AND TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 48";
        }
    }

    // Filter by Specific Ticket ID
    if (isset($_GET['ticket_id'])) {
        $ticket_id = intval($_GET['ticket_id']);
        $where .= " AND t.id = $ticket_id";
    }

    $tickets = mysqli_query($conn, "
            SELECT t.*, pl.location_name, um.full_name as reported_by_name, em.employee_name as assigned_to_name 
            FROM patrol_issues t 
            LEFT JOIN patrol_locations pl ON t.location_id = pl.id 
            LEFT JOIN user_master um ON t.reported_by = um.id 
            LEFT JOIN employee_master em ON t.assigned_to = em.id 
            $where 
            ORDER BY t.created_at DESC
        ");

    $employees = mysqli_query($conn, "SELECT id, employee_name FROM employee_master WHERE is_active=1 ORDER BY employee_name");
    $emp_options = [];
    while ($e = mysqli_fetch_assoc($employees)) {
        $emp_options[] = $e;
    }

    $all_locations = mysqli_query($conn, "SELECT id, location_name FROM patrol_locations ORDER BY location_name");
    ?>
    <div class="container">
        <button type="button" onclick="goBack();"
            class="btn btn-secondary btn-full" style="margin-bottom: 20px; text-align: left;">
            ← Back
        </button>
        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🎫</div>
                <div>
                    <h2 style="margin: 0; color: white; font-size: 24px; font-weight: 700;">Issue Tracking (Tickets)
                    </h2>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 13px;">Manage and assign
                        reported patrol issues</p>
                </div>
            </div>
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

        <?php if (isset($_GET['ticket_id'])): ?>
            <div
                style="margin-bottom: 20px; background: #eff6ff; padding: 10px 15px; border-radius: 8px; border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: space-between;">
                <span style="color: #1e40af; font-size: 14px;">
                    <strong>🔍 Active Filter:</strong> Single Ticket View (ID:
                    <?php echo intval($_GET['ticket_id']); ?>)
                </span>
                <a href="?page=tickets"
                    style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #2563eb; padding: 4px 10px; border-radius: 4px;">✕
                    Clear</a>
            </div>
            <?php
        endif; ?>

        <!-- Filter Indicator -->
        <?php if (isset($_GET['aging'])): ?>
            <div
                style="margin-bottom: 20px; background: #eff6ff; padding: 10px 15px; border-radius: 8px; border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: space-between;">
                <span style="color: #1e40af; font-size: 14px;">
                    <strong>🔍 Active Filter:</strong>
                    <?php
                    if ($_GET['aging'] == 'less_24h')
                        echo 'Fresh Tickets (< 24 Hours)';
                    elseif ($_GET['aging'] == '24_48h')
                        echo 'Warning Tickets (24 - 48 Hours)';
                    elseif ($_GET['aging'] == 'more_48h')
                        echo 'Critical Tickets (> 48 Hours)';
                    ?>
                </span>
                <a href="?page=tickets"
                    style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #2563eb; padding: 4px 10px; border-radius: 4px;">✕
                    Clear</a>
            </div>
            <?php
        endif; ?>

        <!-- Tabs -->
        <div
            style="margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
            <div>
                <a href="?page=tickets&tab=open" class="tab-btn <?php echo $tab == 'open' ? 'active' : ''; ?>"
                    style="padding: 10px 20px; text-decoration: none; font-weight: bold; display: inline-block; border-bottom: 3px solid <?php echo $tab == 'open' ? '#f59e0b' : 'transparent'; ?>; color: <?php echo $tab == 'open' ? '#d97706' : '#6b7280'; ?>;">
                    Open / Assigned
                </a>
                <a href="?page=tickets&tab=resolved" class="tab-btn <?php echo $tab == 'resolved' ? 'active' : ''; ?>"
                    style="padding: 10px 20px; text-decoration: none; font-weight: bold; display: inline-block; border-bottom: 3px solid <?php echo $tab == 'resolved' ? '#10b981' : 'transparent'; ?>; color: <?php echo $tab == 'resolved' ? '#059669' : '#6b7280'; ?>;">
                    Resolved / Closed
                </a>
            </div>

            <!-- Quick Filter Form -->
            <form action="" method="GET"
                style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 10px;">
                <input type="hidden" name="page" value="tickets">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">

                <div style="position: relative;">
                    <input type="text" name="search" placeholder="Search issues..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        style="padding: 8px 30px 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 180px;">
                    <span
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8;">🔍</span>
                </div>

                <select name="status_filter"
                    style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                    <option value="">All Status</option>
                    <?php
                    $tab_statuses = ($tab == 'open') ? ['Open', 'Assigned'] : ['Resolved', 'Closed'];
                    foreach ($tab_statuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $status_filter == $st ? 'selected' : ''; ?>>
                            <?php echo $st; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="loc_filter"
                    style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                    <option value="">All Locations</option>
                    <?php mysqli_data_seek($all_locations, 0);
                    while ($l = mysqli_fetch_assoc($all_locations)): ?>
                        <option value="<?php echo $l['id']; ?>" <?php echo $loc_filter == $l['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($l['location_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="emp_filter"
                    style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                    <option value="">All Assigned</option>
                    <?php foreach ($emp_options as $eo): ?>
                        <option value="<?php echo $eo['id']; ?>" <?php echo $emp_filter == $eo['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($eo['employee_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary" style="padding: 8px 15px; font-size: 13px;">Filter</button>
                <?php if ($search || $loc_filter || $emp_filter || $status_filter): ?>
                    <a href="?page=tickets&tab=<?php echo $tab; ?>" class="btn btn-secondary"
                        style="padding: 8px 15px; font-size: 13px; text-decoration: none;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="border-left: 8px solid #f59e0b; padding: 0; overflow: hidden;">
            <div
                style="padding: 20px 25px; background: #fffcf5; border-bottom: 1px solid #fef3c7; display: flex; align-items: center; gap: 12px;">
                <div
                    style="background: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                    1</div>
                <h3 style="margin: 0; color: #92400e; font-size: 18px; font-weight: 700;">Active Tickets List</h3>
            </div>

            <?php if (mysqli_num_rows($tickets) == 0): ?>
                <div style="text-align: center; padding: 60px; color: #94a3b8;">
                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                    No tickets found matching your filters.
                </div>
                <?php
            else: ?>
                <div class="table-wrapper" style="padding: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reported</th>
                                <th>Location</th>
                                <th>Issue</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($t = mysqli_fetch_assoc($tickets)): ?>
                                <tr style="cursor: pointer;" onclick="openTicketTimeline(<?php echo htmlspecialchars(json_encode([
                                    'id' => $t['id'],
                                    'status' => $t['status'],
                                    'issue' => $t['issue_description'],
                                    'location' => $t['location_name'],
                                    'photo_url' => $t['photo_url'],
                                    'reported_by' => $t['reported_by_name'],
                                    'reported_at' => $t['reported_at'],
                                    'assigned_to' => $t['assigned_to_name'],
                                    'assigned_at' => $t['assigned_at'] ?? '',
                                    'resolved_at' => $t['resolved_at'],
                                    'resolution' => $t['resolution_remarks'],
                                    'closing_remarks' => $t['closing_remarks'] ?? '',
                                    'updated_at' => $t['updated_at'],
                                ]), ENT_QUOTES); ?>)" title="Click to view full history">
                                    <td><?php echo $t['id']; ?></td>
                                    <td>
                                        <?php echo date('d/m/Y h:i A', strtotime($t['reported_at'])); ?><br>
                                        <small style="color:#6b7280">by
                                            <?php echo htmlspecialchars($t['reported_by_name'] ?? ''); ?></small>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($t['location_name'] ?? ''); ?></strong></td>
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?php echo htmlspecialchars($t['issue_description'] ?? ''); ?>
                                        <?php if ($t['photo_url']): ?>
                                            <br><a href="<?php echo htmlspecialchars($t['photo_url'] ?? ''); ?>" target="_blank"
                                                onclick="event.stopPropagation()" style="color:#3b82f6;font-size:12px;">📷 Photo</a>
                                            <?php
                                        endif; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo ($t['status'] == 'Open' ? 'error' : ($t['status'] == 'Assigned' ? 'warning' : 'success')); ?>">
                                            <?php echo $t['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $t['assigned_to_name'] ?: '<span style="color:#9ca3af">— Unassigned —</span>'; ?>
                                    </td>
                                    <td onclick="event.stopPropagation();" style="white-space: nowrap;">
                                        <?php if ($t['status'] == 'Open' || $t['status'] == 'Assigned'): ?>
                                            <button onclick="openAssignModal(<?php echo $t['id']; ?>)" class="btn btn-sm"
                                                style="background:#3b82f6;color:white;">👤 Assign</button>
                                            <button onclick="openResolveModal(<?php echo $t['id']; ?>)" class="btn btn-sm"
                                                style="background:#10b981;color:white;">✅ Resolve</button>
                                            <button onclick="openCloseModal(<?php echo $t['id']; ?>)" class="btn btn-sm"
                                                style="background:#6b7280;color:white;">🔒 Close</button>
                                            <?php
                                        endif; ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            endif; ?>
        </div>
    </div>

    <!-- Ticket Timeline Modal -->
    <div id="ticketTimelineModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; justify-content:center; align-items:center; padding:20px; box-sizing:border-box;">
        <div
            style="background:white; border-radius:16px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
            <!-- Modal Header -->
            <div
                style="background:linear-gradient(135deg,#f59e0b,#d97706); padding:20px 25px; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0;color:white;font-size:18px;">🎫 Ticket Details &amp; History</h3>
                    <p id="tlTicketId" style="margin:4px 0 0 0;color:rgba(255,255,255,0.8);font-size:13px;"></p>
                </div>
                <button onclick="document.getElementById('ticketTimelineModal').style.display='none'"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;font-size:22px;cursor:pointer;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>

            <!-- Ticket Info -->
            <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9;">
                <div style="margin-bottom:10px;">
                    <strong style="color:#374151;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">Issue
                        Description</strong>
                    <p id="tlIssue" style="margin:6px 0 0 0;color:#1f2937;font-size:15px;line-height:1.5;"></p>
                </div>
                <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:12px;">
                    <div><small style="color:#6b7280;">📍 Location</small><br><strong id="tlLocation"
                            style="color:#1f2937;"></strong></div>
                    <div><small style="color:#6b7280;">📊 Status</small><br><span id="tlStatus"></span></div>
                </div>
                <div id="tlPhotoWrap" style="margin-top:12px;display:none;">
                    <a id="tlPhoto" href="#" target="_blank" style="color:#3b82f6;font-size:13px;">📷 View Attached
                        Photo</a>
                </div>
            </div>

            <!-- Timeline -->
            <div style="padding:20px 25px;">
                <strong style="color:#374151;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">📅 History
                    Timeline</strong>
                <div id="tlTimeline" style="margin-top:15px; position:relative; padding-left:20px;">
                    <!-- Injected by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="assignModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3>Assign Ticket</h3>
            <form method="POST">
                <input type="hidden" name="update_ticket" value="1">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="ticket_id" id="assign_ticket_id">

                <div class="form-group">
                    <label>Assign To Employee</label>
                    <select name="employee_id" required
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="">-- Select Employee --</option>
                        <?php foreach ($emp_options as $eo): ?>
                            <option value="<?php echo $eo['id']; ?>">
                                <?php echo htmlspecialchars($eo['employee_name']); ?>
                            </option>
                            <?php
                        endforeach; ?>
                    </select>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Assign</button>
                    <button type="button" onclick="document.getElementById('assignModal').style.display='none'"
                        class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resolveModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3>Resolve Ticket</h3>
            <form method="POST">
                <input type="hidden" name="update_ticket" value="1">
                <input type="hidden" name="action" value="resolve">
                <input type="hidden" name="ticket_id" id="resolve_ticket_id">

                <div class="form-group">
                    <label>Resolution Remarks</label>
                    <textarea name="remarks" required rows="3"
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">Mark Resolved</button>
                    <button type="button" onclick="document.getElementById('resolveModal').style.display='none'"
                        class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="closeModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3>Close Ticket</h3>
            <form method="POST">
                <input type="hidden" name="update_ticket" value="1">
                <input type="hidden" name="action" value="close">
                <input type="hidden" name="ticket_id" id="close_ticket_id">

                <div class="form-group">
                    <label>Closing Remarks</label>
                    <textarea name="remarks" required rows="3" placeholder="Why is this ticket being closed?"
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-secondary"
                        style="flex: 1; background: #4b5563; color: white;">Confirm Close</button>
                    <button type="button" onclick="document.getElementById('closeModal').style.display='none'"
                        class="btn btn-secondary"
                        style="background: #e5e7eb; color: #374151; border: 1px solid #d1d5db;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(id) {
            document.getElementById('assign_ticket_id').value = id;
            document.getElementById('assignModal').style.display = 'flex';
        }
        function openResolveModal(id) {
            document.getElementById('resolve_ticket_id').value = id;
            document.getElementById('resolveModal').style.display = 'flex';
        }
        function openCloseModal(id) {
            document.getElementById('close_ticket_id').value = id;
            document.getElementById('closeModal').style.display = 'flex';
        }

        function openTicketTimeline(data) {
            const statusColors = {
                'Open': '#ef4444',
                'Assigned': '#f59e0b',
                'Resolved': '#10b981',
                'Closed': '#6b7280'
            };

            // Header
            document.getElementById('tlTicketId').textContent = 'Ticket #' + data.id;
            document.getElementById('tlIssue').textContent = data.issue;
            document.getElementById('tlLocation').textContent = data.location || '—';

            // Status badge
            const color = statusColors[data.status] || '#6b7280';
            document.getElementById('tlStatus').innerHTML =
                '<span style="background:' + color + ';color:white;padding:2px 10px;border-radius:10px;font-size:13px;font-weight:600;">' + data.status + '</span>';

            // Photo
            if (data.photo_url) {
                document.getElementById('tlPhoto').href = data.photo_url;
                document.getElementById('tlPhotoWrap').style.display = 'block';
            } else {
                document.getElementById('tlPhotoWrap').style.display = 'none';
            }

            // Build timeline
            const steps = [];
            const dot = (bg) => '<span style="position:absolute;left:-27px;top:3px;width:16px;height:16px;background:' + bg + ';border-radius:50%;border:3px solid white;box-shadow:0 0 0 2px ' + bg + ';"></span>';

            // Created
            steps.push({
                color: '#3b82f6',
                label: 'Created',
                time: data.reported_at,
                detail: 'Reported by <strong>' + escHtml(data.reported_by) + '</strong>'
            });

            // Assigned
            if (data.assigned_to && data.assigned_at) {
                steps.push({
                    color: '#f59e0b',
                    label: 'Assigned',
                    time: data.assigned_at,
                    detail: 'Assigned to <strong>' + escHtml(data.assigned_to) + '</strong>'
                });
            }

            // Resolved
            if (data.resolved_at) {
                steps.push({
                    color: '#10b981',
                    label: 'Resolved',
                    time: data.resolved_at,
                    detail: data.resolution
                        ? 'Resolution: <em style="color:#065f46">' + escHtml(data.resolution) + '</em>'
                        : 'Marked as resolved'
                });
            }

            // Closed
            if (data.status === 'Closed') {
                steps.push({
                    color: '#6b7280',
                    label: 'Closed',
                    time: data.updated_at,
                    detail: data.closing_remarks
                        ? 'Closing remarks: <em style="color:#4b5563">' + escHtml(data.closing_remarks) + '</em>'
                        : 'Ticket closed'
                });
            }

            let html = '<div style="border-left:2px solid #e5e7eb; margin-left:7px; padding-left:0;">';
            steps.forEach((s, i) => {
                const isLast = i === steps.length - 1;
                html += '<div style="position:relative; padding:0 0 20px 25px; ' + (isLast ? '' : '') + '">'
                    + dot(s.color)
                    + '<div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:12px 15px;">'
                    + '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">'
                    + '<span style="background:' + s.color + ';color:white;font-size:11px;font-weight:700;padding:2px 8px;border-radius:8px;">' + s.label + '</span>'
                    + '<small style="color:#9ca3af;">' + fmtDate(s.time) + '</small>'
                    + '</div>'
                    + '<p style="margin:0;font-size:13px;color:#374151;">' + s.detail + '</p>'
                    + '</div></div>';
            });
            html += '</div>';

            document.getElementById('tlTimeline').innerHTML = html;
            document.getElementById('ticketTimelineModal').style.display = 'flex';
        }

        function escHtml(str) {
            if (!str) return '—';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function fmtDate(str) {
            if (!str) return '—';
            const d = new Date(str.replace(' ', 'T'));
            if (isNaN(d)) return str;
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                + ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }

        // Close modals on outside click
        window.onclick = function (event) {
            if (event.target == document.getElementById('assignModal')) {
                document.getElementById('assignModal').style.display = 'none';
            }
            if (event.target == document.getElementById('resolveModal')) {
                document.getElementById('resolveModal').style.display = 'none';
            }
            if (event.target == document.getElementById('closeModal')) {
                document.getElementById('closeModal').style.display = 'none';
            }
            if (event.target == document.getElementById('ticketTimelineModal')) {
                document.getElementById('ticketTimelineModal').style.display = 'none';
            }
        }
    </script>
    <?php
    // ==================== TRUCK INWARD ====================
// ==================== EDIT MATERIAL INWARD ====================
elseif ($page == 'edit-material-inward'):
    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $edit_row = [];

    // Handle Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_material_inward'])) {
        $id = intval($_POST['id']);
        $date = mysqli_real_escape_string($conn, $_POST['entry_date']);
        $vehicle_no = mysqli_real_escape_string($conn, $_POST['vehicle_number']);
        $mat_code = mysqli_real_escape_string($conn, $_POST['material_code']);
        $mat_desc = mysqli_real_escape_string($conn, $_POST['material_description']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $supp_code = mysqli_real_escape_string($conn, $_POST['supp_code']);
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $pack_size = mysqli_real_escape_string($conn, $_POST['pack_size']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

        $sql = "UPDATE material_inward SET 
                    entry_date='$date', 
                    vehicle_number='$vehicle_no', 
                    material_code='$mat_code', 
                    material_description='$mat_desc', 
                    quantity='$quantity', 
                    supp_code='$supp_code', 
                    supplier='$supplier', 
                    category='$category', 
                    pack_size='$pack_size', 
                    remarks='$remarks' 
                    WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_msg'] = "✅ Entry Updated Successfully!";
            header("Location: ?page=view-material-inward&t=" . time());
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error: " . mysqli_error($conn);
            header("Location: ?page=edit-material-inward&id=" . $id);
            exit;
        }
    }

    // Check permission
    if (!hasPermission('actions.edit_record')) {
        echo "<div class='container'><div class='alert alert-error'>🚫 Access Denied: You do not have permission to edit records.</div><a href='?page=view-material-inward' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    // Fetch Data
    if ($edit_id) {
        $res = mysqli_query($conn, "SELECT * FROM material_inward WHERE id=$edit_id");
        $edit_row = mysqli_fetch_assoc($res);
    }

    if (!$edit_row) {
        echo "<div class='container'><div class='alert alert-error'>Entry not found or invalid ID.</div></div>";
    } else {
        // Fetch Masters (same as inward)
        $materials = [];
        $mat_res = mysqli_query($conn, "SELECT material_code, material_description, material_category FROM material_master WHERE is_active=1");
        while ($row = mysqli_fetch_assoc($mat_res)) {
            $materials[] = $row;
        }

        $suppliers = [];
        $sup_res = mysqli_query($conn, "SELECT supplier, supp_code FROM supplier_master WHERE is_active=1");
        while ($row = mysqli_fetch_assoc($sup_res)) {
            $suppliers[] = $row;
        }
        ?>
        <div class="container">
            <button type="button" onclick="goBack();" class="btn btn-secondary btn-full" style="margin-bottom: 20px; text-align: left;">← Back</button>
            <div class="card">
                <h2 style="margin-bottom: 20px;">✏️ Edit Material Inward Entry</h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="entry_date" required
                                value="<?php echo htmlspecialchars($edit_row['entry_date'] ?? ''); ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Vehicle Number</label>
                            <input type="text" name="vehicle_number"
                                value="<?php echo htmlspecialchars($edit_row['vehicle_number'] ?? ''); ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Pack Size</label>
                            <input type="text" name="pack_size"
                                value="<?php echo htmlspecialchars($edit_row['pack_size'] ?? ''); ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" name="quantity"
                                value="<?php echo htmlspecialchars($edit_row['quantity'] ?? ''); ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Material Code</label>
                            <input type="text" name="material_code" id="e_material_code"
                                value="<?php echo htmlspecialchars($edit_row['material_code'] ?? ''); ?>" list="mat_codes"
                                onchange="fillMaterialDetails(this.value, 'code')"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Material Description</label>
                            <input type="text" name="material_description" id="e_material_description"
                                value="<?php echo htmlspecialchars($edit_row['material_description'] ?? ''); ?>"
                                list="mat_descs" onchange="fillMaterialDetails(this.value, 'desc')"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category" id="e_category"
                                value="<?php echo htmlspecialchars($edit_row['category'] ?? ''); ?>" list="mat_cats"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Supplier Code</label>
                            <input type="text" name="supp_code" id="e_supp_code"
                                value="<?php echo htmlspecialchars($edit_row['supp_code'] ?? ''); ?>" list="sup_codes"
                                onchange="fillSupplierDetails(this.value, 'code')"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Supplier Name</label>
                            <input type="text" name="supplier" id="e_supplier"
                                value="<?php echo htmlspecialchars($edit_row['supplier'] ?? ''); ?>" list="sup_names"
                                onchange="fillSupplierDetails(this.value, 'name')"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Remarks</label>
                            <textarea name="remarks" rows="2"
                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"><?php echo htmlspecialchars($edit_row['remarks'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <button type="submit" name="update_material_inward" class="btn btn-primary"
                        style="margin-top: 20px; width: 100%; padding: 12px; font-weight: bold;">Update</button>
                </form>

                <!-- Datalists and Scripts (Reusing logic) -->
                <datalist id="mat_codes">
                    <?php foreach ($materials as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['material_code']); ?>">
                            <?php echo htmlspecialchars($m['material_description']); ?>
                        </option>
                        <?php
                    endforeach; ?>
                </datalist>
                <datalist id="mat_descs">
                    <?php foreach ($materials as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['material_description']); ?>">
                            <?php
                    endforeach; ?>
                </datalist>
                <datalist id="mat_cats">
                    <?php foreach ($materials as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['material_category']); ?>">
                            <?php
                    endforeach; ?>
                </datalist>
                <datalist id="sup_codes">
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo htmlspecialchars($s['supp_code']); ?>">
                            <?php echo htmlspecialchars($s['supplier']); ?>
                        </option>
                        <?php
                    endforeach; ?>
                </datalist>
                <datalist id="sup_names">
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo htmlspecialchars($s['supplier']); ?>">
                            <?php
                    endforeach; ?>
                </datalist>
                <script>
                    const materials = <?php echo json_encode($materials); ?>;
                    const suppliers = <?php echo json_encode($suppliers); ?>;
                    function fillMaterialDetails(value, type) {
                        if (!value) return;
                        let match = null;
                        if (type === 'code') {
                            match = materials.find(m => m.material_code === value);
                            if (!match) match = materials.find(m => m.material_description === value);
                        } else if (type === 'desc') {
                            match = materials.find(m => m.material_description === value);
                        }
                        if (match) {
                            if (type === 'code' || !document.getElementById('e_material_code').value) document.getElementById('e_material_code').value = match.material_code;
                            if (type === 'desc' || !document.getElementById('e_material_description').value) document.getElementById('e_material_description').value = match.material_description;
                            document.getElementById('e_category').value = match.material_category;
                        }
                    }
                    function fillSupplierDetails(value, type) {
                        if (!value) return;
                        let match = null;
                        if (type === 'code') {
                            match = suppliers.find(s => s.supp_code === value);
                            if (!match) match = suppliers.find(s => s.supplier === value);
                        } else if (type === 'name') {
                            match = suppliers.find(s => s.supplier === value);
                            if (!match) match = suppliers.find(s => s.supp_code === value);
                        }
                        if (match) {
                            document.getElementById('e_supp_code').value = match.supp_code;
                            document.getElementById('e_supplier').value = match.supplier;
                        }
                    }
                </script>
            </div>
        </div>
        <?php
    }

    // ==================== MATERIAL INWARD ====================
elseif ($page == 'material-inward'):

    // Create table if not exists
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS material_inward (
            id INT AUTO_INCREMENT PRIMARY KEY,
            entry_date DATE NOT NULL,
            vehicle_number VARCHAR(50),
            material_code VARCHAR(50),
            material_description TEXT,
            quantity VARCHAR(50),
            supp_code VARCHAR(50),
            supplier VARCHAR(100),
            category VARCHAR(100),
            pack_size VARCHAR(50),
            remarks TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

    // Migrations: Add new columns if they don't exist
    $check_cols = mysqli_query($conn, "SHOW COLUMNS FROM material_inward");
    $existing_cols = [];
    while ($c = mysqli_fetch_assoc($check_cols)) {
        $existing_cols[] = $c['Field'];
    }

    if (!in_array('vehicle_number', $existing_cols)) {
        mysqli_query($conn, "ALTER TABLE material_inward ADD COLUMN vehicle_number VARCHAR(50) AFTER entry_date");
    }
    if (!in_array('quantity', $existing_cols)) {
        mysqli_query($conn, "ALTER TABLE material_inward ADD COLUMN quantity VARCHAR(50) AFTER material_description");
    }
    if (!in_array('remarks', $existing_cols)) {
        mysqli_query($conn, "ALTER TABLE material_inward ADD COLUMN remarks TEXT AFTER pack_size");
    }

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_material_inward'])) {
        $date = mysqli_real_escape_string($conn, $_POST['entry_date']);
        $vehicle_no = mysqli_real_escape_string($conn, $_POST['vehicle_number']);
        $mat_code = mysqli_real_escape_string($conn, $_POST['material_code']);
        $mat_desc = mysqli_real_escape_string($conn, $_POST['material_description']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $supp_code = mysqli_real_escape_string($conn, $_POST['supp_code']);
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $pack_size = mysqli_real_escape_string($conn, $_POST['pack_size']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

        $sql = "INSERT INTO material_inward (entry_date, vehicle_number, material_code, material_description, quantity, supp_code, supplier, category, pack_size, remarks) 
                    VALUES ('$date', '$vehicle_no', '$mat_code', '$mat_desc', '$quantity', '$supp_code', '$supplier', '$category', '$pack_size', '$remarks')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_msg'] = "✅ Material Inward Entry Saved Successfully!";
            header("Location: ?page=view-material-inward&t=" . time());
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error: " . mysqli_error($conn);
            header("Location: ?page=add-material-inward");
            exit;
        }
    }

    // Fetch Masters for Auto-fill
    $materials = [];
    $mat_res = mysqli_query($conn, "SELECT material_code, material_description, material_category FROM material_master WHERE is_active=1");
    while ($row = mysqli_fetch_assoc($mat_res)) {
        $materials[] = $row;
    }

    $suppliers = [];
    $sup_res = mysqli_query($conn, "SELECT supplier, supp_code FROM supplier_master WHERE is_active=1");
    while ($row = mysqli_fetch_assoc($sup_res)) {
        $suppliers[] = $row;
    }
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

        <button type="button" onclick="goBack();" class="btn btn-secondary btn-full" style="margin-bottom: 20px; text-align: left;">← Back</button>

        <div
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 40px;">📦</div>
                <div>
                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">Material Inward</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9);">Record material arrival details</p>
                </div>
            </div>
            <!-- Report Button -->
            <a href="?page=view-material-inward" class="btn"
                style="background: white; color: #059669; font-weight: 700; border: none; padding: 12px 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-decoration: none;">
                📋 View Reports
            </a>
        </div>

        <div class="card">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                    <!-- Date -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Date</label>
                        <input type="date" name="entry_date" required value="<?php echo date('Y-m-d'); ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>

                    <!-- Vehicle Number -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Vehicle Number</label>
                        <input type="text" name="vehicle_number" placeholder="MH12AB1234"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>

                    <!-- Pack Size -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Pack Size</label>
                        <input type="text" name="pack_size" placeholder="e.g. 50kg Bag, 200L Drum"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>

                    <!-- Quantity -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Quantity</label>
                        <input type="text" name="quantity" placeholder="e.g. 100"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>

                    <!-- Material Code Search -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Material Code *</label>
                        <input type="text" name="material_code" id="material_code" list="mat_codes" required
                            placeholder="Search Code" onchange="fillMaterialDetails(this.value, 'code')"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <datalist id="mat_codes">
                            <?php foreach ($materials as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['material_code']); ?>">
                                    <?php echo htmlspecialchars($m['material_description']); ?>
                                </option>
                                <?php
                            endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Material Description Search -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Material
                            Description</label>
                        <input type="text" name="material_description" id="material_description" list="mat_descs" required
                            placeholder="Search Description" onchange="fillMaterialDetails(this.value, 'desc')"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <datalist id="mat_descs">
                            <?php foreach ($materials as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['material_description']); ?>">
                                    <?php
                            endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Category (Auto-filled but also searchable) -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Category</label>
                        <input type="text" name="category" id="category" list="mat_cats" placeholder="Search Category"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb;">
                        <datalist id="mat_cats">
                            <?php foreach ($materials as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['material_category']); ?>">
                                    <?php
                            endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Supplier Code Search -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Supplier Code</label>
                        <input type="text" name="supp_code" id="supp_code" list="sup_codes"
                            placeholder="Search Supplier Code" onchange="fillSupplierDetails(this.value, 'code')"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <datalist id="sup_codes">
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['supp_code']); ?>">
                                    <?php echo htmlspecialchars($s['supplier']); ?>
                                </option>
                                <?php
                            endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Supplier Name Search -->
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Supplier Name</label>
                        <input type="text" name="supplier" id="supplier" list="sup_names" placeholder="Search Supplier Name"
                            onchange="fillSupplierDetails(this.value, 'name')"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <datalist id="sup_names">
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['supplier']); ?>">
                                    <?php
                            endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Remarks -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Remarks</label>
                        <textarea name="remarks" rows="2"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                    </div>

                </div>
                <button type="submit" name="save_material_inward" class="btn btn-primary"
                    style="margin-top: 20px; width: 100%; padding: 12px; font-weight: bold;">Submit Material
                    Inward</button>
            </form>
        </div>
    </div>

    <script>
        // Master Data passed from PHP
        const materials = <?php echo json_encode($materials); ?>;
        const suppliers = <?php echo json_encode($suppliers); ?>;

        function fillMaterialDetails(value, type) {
            if (!value) return;
            let match = null;

            if (type === 'code') {
                // Try exact code match
                match = materials.find(m => m.material_code === value);
                // Fallback: Try description match if user typed description in code field
                if (!match) {
                    match = materials.find(m => m.material_description === value || m.material_description.toLowerCase() === value.toLowerCase());
                }
            } else if (type === 'desc') {
                match = materials.find(m => m.material_description === value);
            }

            if (match) {
                if (type === 'code' || !document.getElementById('material_code').value) {
                    document.getElementById('material_code').value = match.material_code;
                }
                if (type === 'desc' || !document.getElementById('material_description').value) {
                    document.getElementById('material_description').value = match.material_description;
                }
                document.getElementById('category').value = match.material_category;
            }
        }

        function fillSupplierDetails(value, type) {
            if (!value) return;
            let match = null;

            if (type === 'code') {
                // Try exact code match
                match = suppliers.find(s => s.supp_code === value);
                // Fallback: Try name match if user typed name in code field
                if (!match) {
                    match = suppliers.find(s => s.supplier === value || s.supplier.toLowerCase() === value.toLowerCase());
                }
            } else if (type === 'name') {
                match = suppliers.find(s => s.supplier === value);
                // Fallback: Try code match if user typed code in name field
                if (!match) {
                    match = suppliers.find(s => s.supp_code === value);
                }
            }

            if (match) {
                // Update both fields to ensure consistency
                document.getElementById('supp_code').value = match.supp_code;
                document.getElementById('supplier').value = match.supplier;
            }
        }
    </script>

    <?php
    // ==================== EDIT REGISTER ENTRY ====================
elseif ($page == 'edit-register-entry'):
    // Check permission
    if (!hasPermission('actions.edit_record')) {
        echo "<div class='container'><div class='alert alert-error'>🚫 Access Denied: You do not have permission to edit records.</div><a href='?page=register' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (isset($_GET['delete_register_id'])) {
        // handled at top of file
    }


    // Fetch Data for Edit
    $edit_row = null;
    if ($edit_id) {
        $res = mysqli_query($conn, "SELECT * FROM manual_registers WHERE id=$edit_id");
        $edit_row = mysqli_fetch_assoc($res);
    }

    // Fetch Masters for Edit Auto-fill
    $materials_js_edit = [];
    $mat_res = mysqli_query($conn, "SELECT material_code, material_description, material_category FROM material_master WHERE is_active=1");
    while ($row = mysqli_fetch_assoc($mat_res)) {
        $materials_js_edit[] = $row;
    }

    $suppliers_js_edit = [];
    $sup_res = mysqli_query($conn, "SELECT supplier, supp_code FROM supplier_master WHERE is_active=1");
    while ($row = mysqli_fetch_assoc($sup_res)) {
        $suppliers_js_edit[] = $row;
    }


    if (!$edit_row) {
        echo "<div class='container'><div class='alert alert-error'>Entry not found.</div></div>";
    } else {
        $reg_type = $edit_row['register_type'];

        // Get Dynamic Configuration instead of hardcoded list
        $all_configs = $registers_manager->getTypesMap();
        $fields_to_show = isset($all_configs[$reg_type]) ? $all_configs[$reg_type]['columns'] : [];
        $current_config = isset($all_configs[$reg_type]) ? $all_configs[$reg_type] : null;


        ?>
        <div class="container">
            <button type="button" onclick="goBack();" class="btn btn-secondary btn-full" style="margin-bottom: 20px; text-align: left;">← Back</button>
            <div class="card">
                <h2 style="margin-bottom: 20px;">✏️ Edit Register Entry
                    (
                    <?php echo htmlspecialchars($current_config ? $current_config['title'] : $reg_type); ?>)
                </h2>

                <script>
                    const masterMaterialsEdit = <?php echo json_encode($materials_js_edit); ?>;
                    const masterSuppliersEdit = <?php echo json_encode($suppliers_js_edit); ?>;

                    function fillEditMaterial(type) {
                        let value, match = null;
                        const codeInput = document.getElementsByName('material_code')[0];
                        const descInput = document.getElementsByName('material_desc')[0];
                        const categoryInput = document.getElementsByName('category')[0];

                        if (type === 'code' && codeInput) {
                            value = codeInput.value;
                            match = masterMaterialsEdit.find(m => m.material_code === value);
                            if (!match) match = masterMaterialsEdit.find(m => m.material_description === value);
                        } else if (type === 'desc' && descInput) {
                            value = descInput.value;
                            match = masterMaterialsEdit.find(m => m.material_description === value);
                        }

                        if (match) {
                            if (codeInput && (type === 'desc' || !codeInput.value)) codeInput.value = match.material_code;
                            if (descInput && (type === 'code' || !descInput.value)) descInput.value = match.material_description;
                            if (categoryInput) categoryInput.value = match.material_category;
                        }
                    }

                    function fillEditSupplier(type) {
                        let value, match = null;
                        const codeInput = document.getElementsByName('supp_code')[0];
                        const nameInput = document.getElementsByName('party_name')[0];

                        if (type === 'code' && codeInput) {
                            value = codeInput.value;
                            match = masterSuppliersEdit.find(s => s.supp_code === value);
                            if (!match) match = masterSuppliersEdit.find(s => s.supplier === value);
                        } else if (type === 'name' && nameInput) {
                            value = nameInput.value;
                            match = masterSuppliersEdit.find(s => s.supplier === value);
                            if (!match) match = masterSuppliersEdit.find(s => s.supp_code === value);
                        }

                        if (match) {
                            if (codeInput) codeInput.value = match.supp_code;
                            if (nameInput) nameInput.value = match.supplier;
                        }
                    }
                </script>

                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <input type="hidden" name="update_register" value="1">

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <?php
                        if (empty($fields_to_show)) {
                            echo '<p>Unknown register type. Showing all fields.</p>';
                            $fields_to_show = array_keys($edit_row);
                        }

                        foreach ($fields_to_show as $label => $key) {
                            if (is_int($label)) {
                                $label = ucwords(str_replace('_', ' ', $key));
                            }

                            // Skip metadata fields
                            if (in_array($key, ['id', 'register_type', 'created_at']))
                                continue;

                            $val = isset($edit_row[$key]) ? $edit_row[$key] : '';

                            $type = (strpos($key, 'date') !== false) ? 'date' : ((strpos($key, 'time') !== false && strlen($val) <= 8) ? 'time' : 'text');
                            if ($key == 'remarks')
                                $type = 'textarea';

                            // Special Handling for Material Inward Fields
                            $listAttr = '';
                            $changeAttr = '';
                            $placeholder = '';

                            if ($reg_type == 'material_inward_reg') {
                                if ($key == 'material_code') {
                                    $listAttr = 'list="mat_codes_edit"';
                                    $changeAttr = 'onchange="fillEditMaterial(\'code\')"';
                                    $placeholder = 'Search Code';
                                    $type = 'text';
                                } elseif ($key == 'material_desc') {
                                    $listAttr = 'list="mat_descs_edit"';
                                    $changeAttr = 'onchange="fillEditMaterial(\'desc\')"';
                                    $placeholder = 'Search Description';
                                    $type = 'text';
                                } elseif ($key == 'supp_code') {
                                    $listAttr = 'list="sup_codes_edit"';
                                    $changeAttr = 'onchange="fillEditSupplier(\'code\')"';
                                    $placeholder = 'Search Supplier Code';
                                    $type = 'text';
                                } elseif ($key == 'party_name') { // Supplier Name
                                    $listAttr = 'list="sup_names_edit"';
                                    $changeAttr = 'onchange="fillEditSupplier(\'name\')"';
                                    $placeholder = 'Search Supplier Name';
                                    $type = 'text';
                                }
                            }

                            echo '<div class="form-group">';
                            echo '<label style="font-weight:600; font-size:13px; margin-bottom:5px; display:block;">' . htmlspecialchars($label) . '</label>';

                            if ($type == 'textarea') {
                                echo '<textarea name="' . $key . '" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">' . htmlspecialchars($val) . '</textarea>';
                            } else {
                                echo '<input type="' . $type . '" name="' . $key . '" value="' . htmlspecialchars($val) . '" ' . $listAttr . ' ' . $changeAttr . ' placeholder="' . $placeholder . '" autocomplete="off" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <button type="submit" class="btn btn-primary"
                        style="margin-top: 20px; width: 100%; padding: 12px; font-weight: bold;">Update Entry</button>
                </form>

                <!-- Datalists for Edit Form -->
                <?php if ($reg_type == 'material_inward_reg'): ?>
                    <datalist id="mat_codes_edit">
                        <?php foreach ($materials_js_edit as $m)
                            echo "<option value='{$m['material_code']}'>{$m['material_description']}</option>"; ?>
                    </datalist>
                    <datalist id="mat_descs_edit">
                        <?php foreach ($materials_js_edit as $m)
                            echo "<option value='{$m['material_description']}'>"; ?>
                    </datalist>
                    <datalist id="sup_codes_edit">
                        <?php foreach ($suppliers_js_edit as $s)
                            echo "<option value='{$s['supp_code']}'>{$s['supplier']}</option>"; ?>
                    </datalist>
                    <datalist id="sup_names_edit">
                        <?php foreach ($suppliers_js_edit as $s)
                            echo "<option value='{$s['supplier']}'>"; ?>
                    </datalist>
                    <?php
                endif; ?>
            </div>
        </div>
        <?php
    }

    // ==================== MANAGE REGISTER TYPES (ADMIN) ====================
elseif ($page == 'manage-register-types'):
    require_once 'manage_register_types.php';

    // ==================== REGISTER ENTRY (MANUAL) ====================
elseif ($page == 'register-entry'):
    ?>
    <div class="container">
        <?php
        // Fetch Masters for JS Auto-fill
        $materials_js = [];
        $mat_res = mysqli_query($conn, "SELECT material_code, material_description, material_category FROM material_master WHERE is_active=1");
        while ($row = mysqli_fetch_assoc($mat_res)) {
            $materials_js[] = $row;
        }

        $suppliers_js = [];
        $sup_res = mysqli_query($conn, "SELECT supplier, supp_code FROM supplier_master WHERE is_active=1");
        while ($row = mysqli_fetch_assoc($sup_res)) {
            $suppliers_js[] = $row;
        }
        ?>
        <script>
            // Store master data globally for the register form to access
            const masterMaterials = <?php echo json_encode($materials_js); ?>;
            const masterSuppliers = <?php echo json_encode($suppliers_js); ?>;
        </script>

        <button type="button" onclick="goBack();" class="btn btn-secondary btn-full" style="margin-bottom: 20px; text-align: left;">← Back</button>

        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));">📝</div>
                <div>
                    <h1
                        style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Manual Registers</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Select a register type to
                        log entry</p>
                </div>
            </div>
            <a href="?page=view-registers" class="btn"
                style="background: white; color: #4338ca; font-weight: 700; border: none; padding: 12px 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-decoration: none; transition: transform 0.2s;">
                📋 View Reports
            </a>
        </div>

        <div class="card" style="overflow: visible;">
            <div class="form-group" style="position: relative;">
                <label style="font-size: 18px; color: #1f2937; margin-bottom: 10px; display: block;">Select Register
                    Type</label>
                <div class="custom-select-container">
                    <input type="text" id="register_search_input" placeholder="🔍 Search & Select Register..." readonly
                        onclick="toggleRegisterDropdown()"
                        style="font-size: 16px; padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; width: 100%; background-color: #f9fafb; cursor: pointer;">
                    <input type="hidden" id="register_selector">
                    <div id="register_dropdown_options" class="dropdown-options"
                        style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); z-index: 2000; max-height: 400px; overflow-y: auto; margin-top: 5px;">
                        <input type="text" id="register_filter_box" placeholder="Type to filter..."
                            oninput="filterRegisterOptions()" onsearch="filterRegisterOptions()"
                            onclick="event.stopPropagation()"
                            style="width: 100%; padding: 12px; border: none; border-bottom: 1px solid #e5e7eb; outline: none; background: #f8fafc; font-size: 14px; position: sticky; top: 0;">
                        <div id="register_list_container">
                            <!-- Options injected by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="dynamic_form_container" style="display: none; animation: fadeIn 0.5s ease-in-out;">
            <!-- Form content will be injected here -->
        </div>
    </div>

    <script>
        const registerConfigs = <?php echo json_encode($registers_manager->getAllTypes()); ?>;
        /* OLD HARDCODED CONFIG REMOVED */



        function renderRegisterForm() {
            const type = document.getElementById('register_selector').value;
            const container = document.getElementById('dynamic_form_container');

            if (!type || !registerConfigs[type]) {
                container.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            const config = registerConfigs[type];
            let html = `
                    <div class="card" style="border-top: 5px solid ${config.color}; animation: slideDown 0.4s ease-out;">
                        <h2 style="color: ${config.color}; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f3f4f6; padding-bottom: 15px; margin-bottom: 20px;">
                            <span style="font-size: 32px;">${config.icon}</span> 
                            ${config.title}
                        </h2>
                        <form method="POST" id="manual_register_form">
                           <input type="hidden" name="save_register" value="1">
                           <input type="hidden" name="register_type" value="${type}">
                           <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                `;

            config.fields.forEach(field => {
                const required = field.required ? 'required' : '';
                const asterisk = field.required ? '<span style="color: #ef4444">*</span>' : '';
                const uppercase = field.uppercase ? 'text-transform: uppercase;' : '';
                const valueAttr = field.value ? `value="${field.value}"` : '';

                html += `
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">${field.label} ${asterisk}</label>
                    `;

                if (field.type === 'textarea') {
                    html += `<textarea name="${field.name}" ${required} style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; resize: vertical;" placeholder="${field.placeholder || ''}"></textarea>`;
                } else {
                    const listAttr = field.list ? `list="${field.list}"` : '';
                    const changeAttr = field.onchange ? `onchange="${field.onchange}"` : '';
                    const idAttr = field.name ? `id="reg_${field.name}"` : '';
                    html += `<input type="${field.type}" name="${field.name}" ${idAttr} ${valueAttr} ${required} ${listAttr} ${changeAttr} style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; ${uppercase}" placeholder="${field.placeholder || ''}" autocomplete="off">`;
                }

                html += `</div>`;
            });

            html += `
                           </div>
                           <div style="margin-top: 30px;">
                                <button type="submit" class="btn btn-primary btn-full" style="background: ${config.color}; border: none; padding: 15px; font-size: 18px; font-weight: 700;">
                                    💾 SAVE ENTRY
                                </button>
                           </div>
                        </form>
                        
                        <!-- Datalists for Material Inward -->
                        <datalist id="mat_codes"></datalist>
                        <datalist id="mat_descs"></datalist>
                        <datalist id="sup_codes"></datalist>
                        <datalist id="sup_names"></datalist>
                    </div>
                `;

            container.innerHTML = html;

            // Populate Datalists if they exist
            // Populate Datalists with Chunking for Performance
            const populateDatalistChunked = (data, listId1, listId2, mapFn) => {
                const list1 = document.getElementById(listId1);
                const list2 = document.getElementById(listId2);
                if (!list1 || !data) return;

                list1.innerHTML = '';
                if (list2) list2.innerHTML = '';

                const CHUNK_SIZE = 500;
                let index = 0;

                const processChunk = () => {
                    const chunk = data.slice(index, index + CHUNK_SIZE);
                    if (chunk.length === 0) return;

                    const frag1 = document.createDocumentFragment();
                    const frag2 = list2 ? document.createDocumentFragment() : null;

                    chunk.forEach(item => {
                        const mapped = mapFn(item);

                        const opt1 = document.createElement('option');
                        opt1.value = mapped.val1;
                        opt1.textContent = mapped.text1;
                        frag1.appendChild(opt1);

                        if (frag2 && mapped.val2) {
                            const opt2 = document.createElement('option');
                            opt2.value = mapped.val2;
                            frag2.appendChild(opt2);
                        }
                    });

                    list1.appendChild(frag1);
                    if (list2 && frag2) list2.appendChild(frag2);

                    index += CHUNK_SIZE;
                    if (index < data.length) {
                        requestAnimationFrame(processChunk);
                    }
                };

                requestAnimationFrame(processChunk);
            };

            if (typeof masterMaterials !== 'undefined') {
                populateDatalistChunked(masterMaterials, 'mat_codes', 'mat_descs', m => ({
                    val1: m.material_code,
                    text1: m.material_description,
                    val2: m.material_description
                }));
            }

            if (typeof masterSuppliers !== 'undefined') {
                populateDatalistChunked(masterSuppliers, 'sup_codes', 'sup_names', s => ({
                    val1: s.supp_code,
                    text1: s.supplier,
                    val2: s.supplier
                }));
            }

            container.style.display = 'block';

            // Add simple animation styles if not present
            if (!document.getElementById('animStyles')) {
                const style = document.createElement('style');
                style.id = 'animStyles';
                style.textContent = `
                        @keyframes slideDown {
                            from { opacity: 0; transform: translateY(-20px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                    `;
                document.head.appendChild(style);
            }
        }

        // ==================== CUSTOM SEARCH SELECT LOGIC ====================
        function initRegisterDropdown() {
            const container = document.getElementById('register_list_container');
            if (!container) return;

            container.innerHTML = '';
            Object.keys(registerConfigs).forEach(key => {
                const config = registerConfigs[key];
                const div = document.createElement('div');
                div.className = 'register-option-item';
                div.style.padding = '10px 15px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #f1f5f9';
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.gap = '10px';
                div.style.transition = 'background 0.2s';

                // Icon + Title
                div.innerHTML = `<span style="font-size: 20px;">${config.icon}</span> <span style="font-weight: 500; color: #374151;">${config.title}</span>`;

                div.onmouseover = () => div.style.background = '#f8fafc';
                div.onmouseout = () => div.style.background = 'white';
                div.onclick = () => selectRegister(key, config.title);

                // Store text for filtering
                div.setAttribute('data-text', config.title.toLowerCase());

                container.appendChild(div);
            });

            // Close dropdown on outside click
            document.addEventListener('click', function (e) {
                const container = document.querySelector('.custom-select-container');
                if (container && !container.contains(e.target)) {
                    document.getElementById('register_dropdown_options').style.display = 'none';
                }
            });
        }

        // ==================== AUTO-FILL HELPERS FOR REGISTERS ====================
        function fillRegisterMaterial(type) {
            if (typeof masterMaterials === 'undefined') return;

            let value, match = null;
            const codeInput = document.getElementById('reg_material_code');
            const descInput = document.getElementById('reg_material_desc');
            const categoryInput = document.getElementById('reg_category');

            if (type === 'code') {
                value = codeInput.value;
                match = masterMaterials.find(m => m.material_code === value);
                if (!match) match = masterMaterials.find(m => m.material_description === value); // Fallback
            } else if (type === 'desc') {
                value = descInput.value;
                match = masterMaterials.find(m => m.material_description === value);
            }

            if (match) {
                if (codeInput && (type === 'desc' || !codeInput.value)) codeInput.value = match.material_code;
                if (descInput && (type === 'code' || !descInput.value)) descInput.value = match.material_description;
                if (categoryInput) categoryInput.value = match.material_category;
            }
        }

        function fillRegisterSupplier(type) {
            if (typeof masterSuppliers === 'undefined') return;

            let value, match = null;
            const codeInput = document.getElementById('reg_supp_code');
            const nameInput = document.getElementById('reg_party_name');

            if (type === 'code' && codeInput) {
                value = codeInput.value;
                match = masterSuppliers.find(s => s.supp_code === value);
                if (!match) match = masterSuppliers.find(s => s.supplier === value); // Fallback
            } else if (type === 'name' && nameInput) {
                value = nameInput.value;
                match = masterSuppliers.find(s => s.supplier === value);
                if (!match) match = masterSuppliers.find(s => s.supp_code === value); // Fallback
            }

            if (match) {
                if (codeInput) codeInput.value = match.supp_code;
                if (nameInput) nameInput.value = match.supplier;
            }
        }

        function toggleRegisterDropdown() {
            const dd = document.getElementById('register_dropdown_options');
            const isHidden = dd.style.display === 'none';
            dd.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                document.getElementById('register_filter_box').value = '';
                filterRegisterOptions(); // Reset filter
                document.getElementById('register_filter_box').focus();
            }
        }

        function filterRegisterOptions() {
            const filter = document.getElementById('register_filter_box').value.toLowerCase().trim();
            const items = document.querySelectorAll('.register-option-item');
            items.forEach(item => {
                const text = item.getAttribute('data-text');
                if (text.includes(filter)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectRegister(key, title) {
            const config = registerConfigs[key];
            if (config && config.redirect) {
                window.location.href = config.redirect;
                return;
            }

            document.getElementById('register_selector').value = key; // Hidden input
            document.getElementById('register_search_input').value = title; // Display input
            document.getElementById('register_dropdown_options').style.display = 'none';
            renderRegisterForm(); // Trigger form render
        }

        // Initialize dropdown
        document.addEventListener('DOMContentLoaded', () => {
            initRegisterDropdown();
        });
    </script>

    <?php
endif;
// ==================== TRUCK INWARD ====================
