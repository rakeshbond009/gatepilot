                <!-- Generic Webcam Modal (Moved to top for max visibility) -->
<div id="webcamModal"
    style="display:none !important; position:fixed !important; z-index:2147483647 !important; left:0 !important; top:0 !important; width:100% !important; height:100% !important; background-color:rgba(0,0,0,0.95) !important; flex-direction:column !important; align-items:center !important; justify-content:center !important;">
    <div style="position:relative; width:90%; max-width:640px; z-index:2147483647 !important;">
        <!-- Camera Selector -->
        <div id="webcamSelectorContainer" style="margin-bottom: 15px; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
            <label style="display: block; color: white; font-size: 12px; margin-bottom: 5px; opacity: 0.8;">Select Camera:</label>
            <select id="webcamDeviceSelect" onchange="switchWebcamDevice(this.value)" 
                style="width: 100%; padding: 8px; background: #333; color: white; border: 1px solid #555; border-radius: 6px; cursor: pointer; outline: none;">
                <option value="">Detecting cameras...</option>
            </select>
        </div>

        <div style="width: 100%; height: 450px; overflow: hidden !important; position: relative !important; border-radius: 12px; border: 4px solid #fff; background: #000; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <video id="webcamVideo" autoplay playsinline
                style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; min-width: 100% !important; min-height: 100% !important; width: auto !important; height: auto !important; object-fit: cover !important; display: block !important; margin: 0 !important;"></video>
        </div>
        <canvas id="webcamCanvas" style="display:none;"></canvas>
        <div style="position:absolute; bottom:20px; left:0; width:100%; display:flex; justify-content:center; gap:20px;">
            <button onclick="takeWebcamSnapshot()"
                style="background:white; border:none; border-radius:50%; width:80px; height:80px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 15px rgba(0,0,0,0.5); transform:scale(1); transition:transform 0.2s;" onmousedown="this.style.transform='scale(0.9)'" onmouseup="this.style.transform='scale(1)'">
                <div style="width:60px; height:60px; background:#ef4444; border-radius:50%;"></div>
            </button>
            <button onclick="closeWebcamModal()"
                style="position:absolute; top:-120px; right:0; background:rgba(255,255,255,0.2); color:white; border:none; border-radius:50%; width:44px; height:44px; font-size:28px; cursor:pointer; backdrop-filter:blur(5px);">✕</button>
        </div>
    </div>
</div>
<?php if ($page != 'login'): ?>
                    <!-- Footer -->
                    <div class="app-footer">
                        <p style="margin: 0;">
                            &copy; <?php echo date('Y'); ?> GatePilot · A CodePilotx Architecture. All Rights Reserved.
                        </p>
                    </div>

                    <!-- Bottom Navigation -->
                    <div class="bottom-nav">
                        <a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                            <span class="icon">🏠</span>
                            Home
                        </a>
                        <?php if (hasPermission('pages.inward')): ?>
                            <a href="?page=inward" class="<?php echo $page == 'inward' ? 'active' : ''; ?>">
                                <span class="icon">➕</span>
                                Inward
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.outward')): ?>
                            <a href="?page=outward" class="<?php echo $page == 'outward' ? 'active' : ''; ?>">
                                <span class="icon">📤</span>
                                Outward Exit
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.guard_patrol')): ?>
                            <a href="?page=guard-patrol" class="<?php echo $page == 'guard-patrol' ? 'active' : ''; ?>">
                                <span class="icon">👮</span>
                                Patrol
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.reports')): ?>
                            <a href="?page=reports" class="<?php echo $page == 'reports' ? 'active' : ''; ?>">
                                <span class="icon">📊</span>
                                Reports
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.loading')): ?>
                            <a href="?page=loading" class="<?php echo $page == 'loading' ? 'active' : ''; ?>">
                                <span class="icon">📦</span>
                                Loading
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.unloading')): ?>
                            <a href="?page=unloading" class="<?php echo $page == 'unloading' ? 'active' : ''; ?>">
                                <span class="icon">📥</span>
                                Unloading
                            </a>
                        <?php
    endif; ?>


                        <?php if (hasPermission('pages.management')): ?>
                            <a href="?page=management" class="<?php echo $page == 'management' ? 'active' : ''; ?>">
                                <span class="icon">📈</span>
                                Management
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.tickets')): ?>
                            <a href="?page=tickets" class="<?php echo $page == 'tickets' ? 'active' : ''; ?>">
                                <span class="icon">🎫</span>
                                Tickets
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.masters')): ?>
                            <a href="?page=admin" class="<?php echo $page == 'admin' ? 'active' : ''; ?>">
                                <span class="icon">⚙️</span>
                                Masters
                            </a>
                        <?php
    endif; ?>

                        <?php if (hasPermission('pages.register')): ?>
                            <a href="?page=register-entry"
                                class="<?php echo($page == 'register-entry' || $page == 'view-registers') ? 'active' : ''; ?>">
                                <span class="icon">📝</span>
                                Register
                            </a>
                        <?php
    endif; ?>
                        <a href="?page=logout" class="<?php echo $page == 'logout' ? 'active' : ''; ?>">
                            <span class="icon">🚪</span>
                            Logout
                        </a>
                    </div>

                    <!-- Custom App Confirmation Modal -->
                    <div id="appConfirmModal"
                        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 11000; align-items: center; justify-content: center;">
                        <div
                            style="background: white; border-radius: 16px; padding: 25px; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 15px;">❓</div>
                            <h3 id="appConfirmTitle" style="margin: 0 0 10px 0; font-size: 20px; color: #1e2937;">
                                Confirm
                                Action
                            </h3>
                            <p id="appConfirmMessage" style="margin: 0 0 25px 0; color: #64748b; line-height: 1.5;"></p>
                            <div style="display: flex; gap: 12px;">
                                <button id="appConfirmCancel" class="btn btn-secondary"
                                    style="flex: 1; padding: 12px; font-weight: 600;">Cancel</button>
                                <button id="appConfirmOk" class="btn btn-primary"
                                    style="flex: 1; padding: 12px; font-weight: 600; background: #ef4444; border-color: #ef4444;">Confirm</button>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Modal -->
                    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; overflow-y: auto; align-items: center; justify-content: center;">
                        <div style="position: relative; width: 100%; max-width: 480px; margin: 50px auto; background: white; border-radius: 16px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                            <div style="text-align: center; margin-bottom: 25px;">
                                <div style="background: #eef2ff; color: #4f46e5; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 30px;">🔑</div>
                                <h3 style="margin: 0; font-size: 20px; color: #1e2937;">Change Password</h3>
                            </div>
                            <form action="?page=change_password_action" method="POST" onsubmit="handlePasswordChange(this); return false;">
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Current Password</label>
                                    <input type="password" name="old_password" required style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">New Password</label>
                                    <input type="password" name="new_password" required style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 25px;">
                                    <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Confirm New Password</label>
                                    <input type="password" name="confirm_password" required style="width: 100%; padding: 10px; border: 1.5px solid #d1d5db; border-radius: 8px;">
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <button type="button" onclick="closeChangePasswordModal()" style="flex: 1; padding: 12px; background: #f1f5f9; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                                    <button type="submit" style="flex: 2; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Employee Entry Modal -->
                    <div id="employeeModal"
                        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10002; overflow-y: auto;">
                        <div
                            style="position: relative; max-width: 600px; margin: 20px auto; background: white; border-radius: 16px; padding: 25px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h2 style="margin: 0; font-size: 24px; color: #1e2937;">👤 Employee Entry/Exit</h2>
                                <button type="button" onclick="closeEmployeeModal()"
                                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">&times;</button>
                            </div>

                            <!-- Inward Form -->
                            <div id="empInwardCard" class="card"
                                style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3 id="empModalModeTitle" style="margin: 0; font-size: 18px;">Welcome</h3>
                                </div>
                                <form action="?page=employee-entry-action" method="POST" onsubmit="handleEmployeeEntryForm(this); return false;">
                                    <input type="hidden" name="action" id="action" value="inward">
                                    <input type="hidden" name="entry_id" id="entry_id" value="">
                                    <input type="hidden" name="redirect_to" value="?page=<?php echo $page; ?>">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                        <div class="form-group">
                                            <label style="font-weight: 600; font-size: 13px;">Vehicle Number *</label>
                                            <input type="text" name="vehicle_number" id="emp_vehicle_number"
                                                placeholder="MH12AB1234" required
                                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"
                                                onblur="fetchEmployeeByVehicle(this.value)">
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: 600; font-size: 13px;">Employee ID</label>
                                            <input type="text" name="employee_id" id="emp_id" readonly
                                                style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f1f5f9;">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-top: 10px;">
                                        <label style="font-weight: 600; font-size: 13px;">Employee Name</label>
                                        <input type="text" name="employee_name" id="emp_name" readonly
                                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f1f5f9;">
                                    </div>
                                    <div class="form-group" style="margin-top: 10px;">
                                        <label style="font-weight: 600; font-size: 13px;">Department</label>
                                        <input type="text" name="department" id="emp_department" readonly
                                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f1f5f9;">
                                    </div>
                                    <div class="form-group" style="margin-top: 10px;">
                                        <label style="font-weight: 600; font-size: 13px;">Remarks</label>
                                        <textarea name="remarks" id="emp_remarks" rows="2"
                                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                                    </div>
                                    <button type="submit" id="empSubmitBtn" class="btn btn-primary"
                                        style="width: 100%; margin-top: 15px; padding: 12px; font-weight: 600;">Log
                                        Entry</button>
                                </form>
                            </div>

                            <!-- List of Employees Inside -->
                            <div id="empOutwardCard" class="card">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3 style="margin: 0; font-size: 15px;">Employees Currently Inside</h3>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="text" id="empSearch" placeholder="Search..."
                                            onkeyup="filterEmployeeList()"
                                            style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; width: 120px;">
                                    </div>
                                </div>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #f1f5f9; text-align: left;">
                                                <th style="padding: 10px; border-bottom: 2px solid #e2e8f0;">Employee
                                                </th>
                                                <th style="padding: 10px; border-bottom: 2px solid #e2e8f0;">Vehicle
                                                </th>
                                                <th style="padding: 10px; border-bottom: 2px solid #e2e8f0;">In Time
                                                </th>
                                                <th style="padding: 10px; border-bottom: 2px solid #e2e8f0;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="empInsideList">
                                            <?php
    $inside_emps = mysqli_query($conn, "SELECT * FROM employee_entries WHERE status = 'inside' ORDER BY inward_datetime DESC");
    if (mysqli_num_rows($inside_emps) > 0):
        while ($emp = mysqli_fetch_assoc($inside_emps)):
?>
                                                    <tr class="emp-row"
                                                        data-emp-id="<?php echo htmlspecialchars($emp['employee_id']); ?>"
                                                        style="border-bottom: 1px solid #e2e8f0;">
                                                        <td style="padding: 10px;">
                                                            <strong>
                                                                <?php echo htmlspecialchars($emp['employee_name']); ?>
                                                            </strong><br>
                                                            <span style="font-size: 11px; color: #64748b;">
                                                                <?php echo htmlspecialchars($emp['employee_id']); ?>
                                                            </span>
                                                        </td>
                                                        <td style="padding: 10px;">
                                                            <?php echo htmlspecialchars($emp['vehicle_number']); ?>
                                                        </td>
                                                        <td style="padding: 10px;">
                                                            <?php echo date('h:i A', strtotime($emp['inward_datetime'])); ?>
                                                        </td>
                                                        <td style="padding: 10px;">
                                                            <form action="?page=employee-entry-action" method="POST"
                                                                style="margin: 0;"
                                                                onsubmit="handleEmployeeExitForm(this, '<?php echo addslashes($emp['employee_name']); ?>'); return false;">
                                                                <input type="hidden" name="action" value="exit">
                                                                <input type="hidden" name="entry_id"
                                                                    value="<?php echo $emp['id']; ?>">
                                                                <input type="hidden" name="redirect_to"
                                                                    value="?page=<?php echo $page; ?>">
                                                                <button type="submit" class="btn-emp-exit"
                                                                    style="background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">Exit</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php
        endwhile;
    else: ?>
                                                <tr>
                                                    <td colspan="4" style="padding: 20px; text-align: center; color: #64748b;">
                                                        No
                                                        employees
                                                        inside</td>
                                                </tr>
                                            <?php
    endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Scanner Modal -->
                    <div id="qrScannerModal"
                        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10100; overflow-y: auto;">
                        <div
                            style="position: relative; max-width: 500px; margin: 20px auto; background: white; border-radius: 12px; padding: 20px; margin-top: 20px; margin-bottom: 20px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="margin: 0; font-size: 20px;">📷 Scan QR Code</h3>
                                <button type="button" onclick="closeQRScannerModal()"
                                    style="background: #ef4444; color: white; border: none; border-radius: 6px; padding: 8px 15px; cursor: pointer; font-size: 14px;">
                                    ✕ Close
                                </button>
                            </div>
                            <!-- Scanner Area -->
                            <div id="qr-wrapper-modal">
                                <div id="qr-reader-modal"
                                    style="width: 100%; min-height: 300px; overflow: hidden; position: relative;"></div>
                            </div>

                            <!-- Controls -->
                            <div class="mt-3 text-center">
                                <div style="margin-bottom: 10px; display: flex; gap: 10px; justify-content: center;">
                                    <button type="button" id="startScanBtnModal" onclick="startQRScannerModal()"
                                        class="btn btn-primary" style="background: #4f46e5; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600;">📷 Start Camera</button>
                                    <button type="button" id="stopScanBtnModal" onclick="stopQRScannerModal()"
                                        class="btn btn-secondary" style="display: none; background: #64748b; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600;">⏹️ Stop Scanner</button>
                                </div>
                                <!-- Camera Device Selection -->
                                <div id="cameraDeviceList" style="margin-bottom: 15px; display: none; padding: 10px; background: #f8fafc; border-radius: 8px;">
                                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #475569;">📷 Select Camera Device</label>
                                    <select id="cameraSelectModal" onchange="restartScannerWithNewDevice(this.value)" 
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white;">
                                        <option value="">Detecting cameras...</option>
                                    </select>
                                </div>
                                <div id="zoomContainerModal"
                                    style="margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 8px; position: relative; z-index: 10;">
                                    <label
                                        style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #475569;">🔍
                                        Manual Zoom (Try this for dense codes)</label>
                                    <input type="range" id="zoomInputModal" min="1" max="5" step="0.5" value="1"
                                        oninput="applyManualZoom(this.value)"
                                        style="width: 100%; height: 8px; border-radius: 4px; background: #e2e8f0; cursor: pointer;">
                                </div>
                                <div id="qrStatusModal"
                                    style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none; position: relative;">
                                    <button type="button"
                                        onclick="document.getElementById('qrStatusModal').style.display='none';"
                                        style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.1); border: none; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 16px; line-height: 1; color: #64748b;">✕</button>
                                </div>
                                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
                                    <p style="margin-bottom: 15px; color: #666; text-align: center; font-size: 14px;">
                                        <strong>OR</strong>
                                        Enter QR data manually:
                                    </p>
                                    <textarea id="qr_manual_input" rows="4" placeholder="Paste QR code data here..."
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; margin-bottom: 10px;"></textarea>
                                    <button type="button" onclick="processManualQR()"
                                        class="btn btn-primary btn-full">Process
                                        QR
                                        Data</button>
                                </div>
                            </div>
                        </div>

                        <!-- QR Data Preview Modal -->
                        <div id="qrPreviewModal"
                            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10101; overflow-y: auto;">
                            <div
                                style="position: relative; max-width: 450px; margin: 50px auto; background: white; border-radius: 16px; padding: 25px;">
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <div
                                        style="background: #ecfdf5; color: #10b981; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 30px;">
                                        ✅</div>
                                    <h3 style="margin: 0; font-size: 20px; color: #1e2937;">QR Data Preview</h3>
                                </div>
                                <div id="qrPreviewContent" style="margin-bottom: 25px;"></div>
                                <div style="display: flex; gap: 12px;">
                                    <button type="button" onclick="closeQRPreviewModal()"
                                        style="flex: 1; padding: 12px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                                    <button type="button" onclick="confirmQRAutoFill()"
                                        style="flex: 2; padding: 12px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">OK
                                        - Auto Fill Form</button>
                                </div>
                            </div>
                        </div>

                        <script>
                            let currentWebcamStream = null;
                            let currentTargetInputId = null;
                            let currentPreviewCallback = null;

                            function detectMobileForWebcam() {
                                return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                            }

                            function safeClick(id) {
                                const el = document.getElementById(id);
                                if (!el) return;
                                try {
                                    el.click();
                                } catch (e) {
                                    const event = new MouseEvent('click', { bubbles: true, cancelable: true, view: window });
                                    el.dispatchEvent(event);
                                }
                            }

                            function openWebcamCapture(inputId, previewFunc) {
                                // Force modal on Desktop
                                if (detectMobileForWebcam()) {
                                    const inputIdCamera = inputId.replace('_file', '_camera');
                                    safeClick(inputIdCamera);
                                    return;
                                }

                                currentTargetInputId = inputId;
                                currentPreviewCallback = previewFunc;
                                const modal = document.getElementById('webcamModal');

                                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                    alert('Webcam not supported on this browser.');
                                    return;
                                }

                                // Forced Visibility
                                modal.style.setProperty('display', 'flex', 'important');
                                modal.style.setProperty('z-index', '2147483647', 'important');

                                // Populate cameras first
                                enumerateWebcamDevices();
                                
                                // Start with default
                                startWebcamStream(null);
                            }

                            function enumerateWebcamDevices() {
                                const select = document.getElementById('webcamDeviceSelect');
                                if (!select) return;

                                navigator.mediaDevices.enumerateDevices()
                                    .then(devices => {
                                        const videoDevices = devices.filter(d => d.kind === 'videoinput');
                                        select.innerHTML = videoDevices.length > 0 ? '' : '<option value="">No cameras found</option>';
                                        
                                        const preferredId = localStorage.getItem('preferredWebcamId');
                                        videoDevices.forEach((device, index) => {
                                            const option = document.createElement('option');
                                            option.value = device.deviceId;
                                            option.text = device.label || `Camera ${index + 1}`;
                                            if (device.deviceId === preferredId) option.selected = true;
                                            select.appendChild(option);
                                        });
                                    })
                                    .catch(err => {
                                        console.error("Device enumeration error:", err);
                                        select.innerHTML = '<option value="">Permission denied</option>';
                                    });
                            }

                            function switchWebcamDevice(deviceId) {
                                if (!deviceId) return;
                                localStorage.setItem('preferredWebcamId', deviceId); // SAVE PREFERENCE
                                if (currentWebcamStream) {
                                    currentWebcamStream.getTracks().forEach(track => track.stop());
                                }
                                startWebcamStream(deviceId);
                            }

                            function startWebcamStream(deviceId) {
                                const video = document.getElementById('webcamVideo');
                                const modal = document.getElementById('webcamModal');

                                // Check preferred
                                const effectiveId = deviceId || localStorage.getItem('preferredWebcamId');
                                const constraints = effectiveId ? 
                                    { video: { deviceId: { exact: effectiveId } } } : 
                                    { video: true };

                                navigator.mediaDevices.getUserMedia(constraints)
                                    .then(stream => {
                                        currentWebcamStream = stream;
                                        video.srcObject = stream;
                                        video.onloadedmetadata = () => {
                                            video.play().catch(e => console.error("Video play failed:", e));
                                        };
                                        // Refresh device list to get labels if they were missing initially
                                        if (!deviceId) enumerateWebcamDevices();
                                    })
                                    .catch(err => {
                                        console.error("Streaming error:", err);
                                        alert('Unable to access camera: ' + err.message);
                                        // On permission error, we keep modal open so they can try gallery or another camera
                                    });
                            }

                            function closeWebcamModal() {
                                const modal = document.getElementById('webcamModal');
                                const video = document.getElementById('webcamVideo');
                                if (currentWebcamStream) {
                                    currentWebcamStream.getTracks().forEach(track => track.stop());
                                    currentWebcamStream = null;
                                }
                                video.srcObject = null;
                                modal.style.setProperty('display', 'none', 'important');
                            }

                            function takeWebcamSnapshot() {
                                if (!currentWebcamStream) return;

                                const video = document.getElementById('webcamVideo');
                                const canvas = document.getElementById('webcamCanvas');
                                const context = canvas.getContext('2d');

                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                // Mirroring not applied here to keep text readable
                                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                                canvas.toBlob(blob => {
                                    const file = new File([blob], "webcam_snap_" + Date.now() + ".jpg", { type: "image/jpeg" });
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(file);

                                    const targetInput = document.getElementById(currentTargetInputId);
                                    if (targetInput) {
                                        targetInput.files = dataTransfer.files;
                                        // Trigger preview
                                        if (typeof window[currentPreviewCallback] === 'function') {
                                            window[currentPreviewCallback](targetInput);
                                        }
                                    }

                                    closeWebcamModal();
                                }, 'image/jpeg', 0.85);
                            }

                            function closeWebcamModal() {
                                const modal = document.getElementById('webcamModal');
                                modal.style.display = 'none';
                                if (currentWebcamStream) {
                                    currentWebcamStream.getTracks().forEach(track => track.stop());
                                    currentWebcamStream = null;
                                }
                            }
                        </script>
                    <?php
endif; ?>
</body>

</html>
