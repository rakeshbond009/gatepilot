<?php if (isLoggedIn()): ?>
        <script>
            // ========== Global App Control & Security Verification ==========
            document.addEventListener('DOMContentLoaded', function () {
                fetch('https://clientmanagement.codepilotx.com/api/app_control.php?project_id=16')
                    .then(response => response.json())
                    .then(data => {
                        if (data.app_allowed === false) {
                            // Block the app (Full screen modal styled per attached image)
                            document.body.innerHTML = `
                            <div style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);z-index:9999999;display:flex;align-items:center;justify-content:center;font-family:sans-serif;">
                                <div style="background:white;padding:50px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.2);text-align:center;max-width:400px;width:90%;">
                                    <div style="background:#e74c3c;color:white;width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:35px;margin:0 auto 20px;">
                                        ⚠️
                                    </div>
                                    <h1 style="color:#333;font-size:24px;margin-bottom:10px;font-weight:600;">App Blocked</h1>
                                    <p style="color:#666;font-size:15px;margin-bottom:25px;">${data.message || 'Account suspended.'}</p>
                                    <p style="color:#999;font-size:13px;">Please contact your administrator for more information.</p>
                                </div>
                            </div>
                        `;
                        } else if (data.message) {
                            // Show Warning globally (Banner at the top, per attached image)
                            const bannerHTML = `
                            <div id="amc-warning-banner" style="background:#ffb822;color:#111;text-align:center;padding:12px 40px;font-weight:600;font-size:14px;position:relative;z-index:999999;box-shadow:0 2px 10px rgba(0,0,0,0.1);width:100%;">
                                ⚠️ Warning: ⚠️ ${data.message}
                                <span onclick="this.parentElement.style.display='none'" style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px;font-weight:bold;">&times;</span>
                            </div>
                        `;
                            document.body.insertAdjacentHTML('afterbegin', bannerHTML);
                        }
                    }).catch(err => console.error('Security check failed', err));
            });
        </script>
    <?php
endif; ?>

    <script>
        // ========== Global Functions - Loaded Early ==========
        function showAppLoader(message = 'Saving data...') {
            const loader = document.createElement('div');
            loader.id = 'app_loader_overlay';
            loader.innerHTML = `
                <div class="spinner"></div>
                <div style="color: #4f46e5; font-weight: 600; font-size: 16px; font-family: sans-serif;">${message}</div>
            `;
            document.body.appendChild(loader);
        }

        function openChangePasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex';
        }

        function closeChangePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function handlePasswordChange(form) {
            const formData = new FormData(form);
            if (formData.get('new_password') !== formData.get('confirm_password')) {
                Swal.fire('Error', 'New passwords do not match!', 'error');
                return;
            }

            showAppLoader('Updating password...');
            fetch('?page=change_password_action', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('app_loader_overlay').remove();
                if (data.success) {
                    Swal.fire('Success', data.message, 'success');
                    closeChangePasswordModal();
                    form.reset();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(e => {
                document.getElementById('app_loader_overlay').remove();
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }

        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const filter = input.value.toUpperCase().trim();
            const table = document.getElementById(tableId);
            if (!table) return;
            const tr = table.getElementsByTagName("tr");

            // If filter is empty, show all rows and exit early
            if (filter === "") {
                for (let i = 1; i < tr.length; i++) {
                    tr[i].style.display = "";
                }
                return;
            }

            for (let i = 1; i < tr.length; i++) {
                let found = false;
                const td = tr[i].getElementsByTagName("td");
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }

        var confirmCallback = null;

        function showAppConfirm(message, callback, title) {
            var modal = document.getElementById('appConfirmModal');
            var msgEl = document.getElementById('appConfirmMessage');
            var titleEl = document.getElementById('appConfirmTitle');
            var okBtn = document.getElementById('appConfirmOk');
            var cancelBtn = document.getElementById('appConfirmCancel');

            if (modal && msgEl) {
                msgEl.textContent = message;
                if (title && titleEl) titleEl.textContent = title;
                modal.style.display = 'flex';
                confirmCallback = callback;

                okBtn.onclick = function () {
                    modal.style.display = 'none';
                    if (confirmCallback) confirmCallback(true);
                };

                cancelBtn.onclick = function () {
                    modal.style.display = 'none';
                    if (confirmCallback) confirmCallback(false);
                };
            } else {
                // Fallback to browser confirm if modal not found
                if (confirm(message)) callback(true);
                else callback(false);
            }
        }

        function showAppPreview(file, targetId, maxWidth = '120px') {
            const reader = new FileReader();
            reader.onload = function (e) {
                const previewStyle = `max-width: ${maxWidth}; max-height: 150px; border-radius: 8px; border: 2px solid #10b981; margin-top: 5px;`;
                document.getElementById(targetId).innerHTML = `<div style="font-size:10px; color:#10b981; font-weight:700;">NEW CAPTURE:</div><img src="${e.target.result}" style="${previewStyle}">`;
            };
            reader.readAsDataURL(file);
        }

        function showAppExistingPreview(url, targetId, label = 'EXISTING DOCUMENT:', maxWidth = '120px') {
            if (!url) return;
            const previewStyle = `max-width: ${maxWidth}; max-height: 150px; border-radius: 8px; border: 2px solid #e5e7eb; margin-top: 5px; cursor: pointer;`;
            document.getElementById(targetId).innerHTML = `<div style="font-size:10px; color:#10b981; font-weight:700; margin-bottom:2px;">${label}</div><img src="${url}" style="${previewStyle}" onclick="window.open('${url}', '_blank')">`;
        }

        var currentEmployeeModalMode = 'both';

        function openEmployeeModal(mode) {
            currentEmployeeModalMode = mode || 'both';
            var modal = document.getElementById('employeeModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';

                // Handle visibility based on mode
                var inwardCard = document.getElementById('empInwardCard');
                var outwardCard = document.getElementById('empOutwardCard');
                var modalTitle = document.querySelector('#employeeModal h2');

                if (currentEmployeeModalMode === 'exit') {
                    if (inwardCard) inwardCard.style.display = 'none';
                    if (outwardCard) outwardCard.style.display = 'block';
                    if (modalTitle) modalTitle.textContent = '👤 Employee Exit';
                } else if (currentEmployeeModalMode === 'entry') {
                    if (inwardCard) inwardCard.style.display = 'block';
                    if (outwardCard) outwardCard.style.display = 'none';
                    if (modalTitle) modalTitle.textContent = '👤 Employee Entry';
                } else {
                    // Universal Mode (Both shown)
                    if (inwardCard) inwardCard.style.display = 'block';
                    if (outwardCard) outwardCard.style.display = 'block';
                    if (modalTitle) modalTitle.textContent = '👤 Employee Entry/Exit';
                    
                    // ONLY auto-trigger scanner if explicitly requested for 'scan' mode
                    if (currentEmployeeModalMode === 'scan') {
                        setTimeout(function() {
                            openQRScannerModal();
                        }, 500);
                    }
                }
            } else {
                console.error('Employee modal not found');
            }
        }

        function handleEmployeeEntryForm(form) {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            // Add loading state to button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            fetch('?page=employee-entry-action', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message || 'Operation successful',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Update the list visually
                    refreshEmployeeList();

                    // Reset fields
                    form.reset();
                    document.getElementById('emp_id').value = '';
                    document.getElementById('emp_name').value = '';
                    document.getElementById('emp_department').value = '';
                    
                    // Re-trigger scanner for next entry
                    closeEmployeeModal();
                    setTimeout(function() {
                        openQRScannerModal();
                    }, 800);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Something went wrong!'
                    });
                    isProcessingScanModal = false; // RELEASE LOCK ON ERROR
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Server communication failed.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        function handleEmployeeExitForm(form, empName, skipConfirm = false) {
            const processExit = () => {
                const formData = new FormData(form);
                formData.append('ajax', '1');

                fetch('?page=employee-entry-action', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Exit Logged',
                            text: data.message || 'Employee exit logged successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Update the list visually
                        refreshEmployeeList();

                        // Re-trigger scanner for next entry
                        closeEmployeeModal();
                        setTimeout(function() {
                            openQRScannerModal();
                        }, 800);
                    } else {
                        Swal.fire('Error', data.message || 'Could not log exit', 'error');
                        isProcessingScanModal = false; // RELEASE LOCK IF ERROR
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Server communication failed.', 'error');
                    isProcessingScanModal = false; // RELEASE LOCK IF ERROR
                });
            };

            if (skipConfirm) {
                processExit();
            } else {
                showAppConfirm('Are you sure employee ' + empName + ' is exiting?', function (confirmed) {
                    if (confirmed) processExit();
                }, 'Confirm Exit');
            }
        }

        function refreshEmployeeList() {
            fetch('?page=get-emp-inside-list')
            .then(r => r.text())
            .then(html => {
                const target = document.getElementById('empInsideList');
                if (target) target.innerHTML = html;
            })
            .catch(e => console.error('Refresh error:', e));
        }

        function closeEmployeeModal() {
            var modal = document.getElementById('employeeModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';

                // Clean up dynamically added elements
                var exitInfo = document.getElementById('exitInfo');
                if (exitInfo) exitInfo.remove();

                var viewCloseBtn = document.getElementById('viewCloseBtn');
                if (viewCloseBtn) viewCloseBtn.remove();

                var loadingDiv = document.getElementById('empLoading');
                if (loadingDiv) loadingDiv.remove();

                // Reset modal title
                var modalTitle = document.querySelector('#employeeModal h2');
                if (modalTitle) {
                    modalTitle.textContent = '👤 Employee Entry/Exit';
                }

                // Reset form display
                var form = document.querySelector('#employeeModal form');
                if (form) {
                    form.style.display = 'block';
                }

                // Show entry sections
                var entryCards = document.querySelectorAll('#employeeModal .card');
                entryCards.forEach(function (card) {
                    if (card) card.style.display = 'block';
                });

                // Reset mode labels
                var modeTitle = document.getElementById('empModalModeTitle');
                if (modeTitle) modeTitle.textContent = 'New Inward Entry';
                
                var submitBtn = document.getElementById('empSubmitBtn');
                if (submitBtn) {
                    submitBtn.textContent = 'Log Entry';
                    submitBtn.style.background = '#3b82f6';
                    submitBtn.style.borderColor = '#3b82f6';
                }

                // Clear form values safely
                try {
                    var vehicleField = document.getElementById('emp_vehicle_number');
                    if (vehicleField) vehicleField.value = '';

                    var idField = document.getElementById('emp_id');
                    if (idField) idField.value = '';

                    var nameField = document.getElementById('emp_name');
                    if (nameField) nameField.value = '';

                    var deptField = document.getElementById('emp_department');
                    if (deptField) deptField.value = '';

                    var remarksField = document.getElementById('emp_remarks');
                    if (remarksField) remarksField.value = '';

                    var actionField = document.getElementById('action');
                    if (actionField) actionField.value = 'inward';

                    var entryIdField = document.getElementById('entry_id');
                    if (entryIdField) entryIdField.value = '';
                } catch (e) {
                    console.error('Error clearing form fields:', e);
                }
            }
        }

        // Auto-open employee modal if redirected from QR scan
        document.addEventListener('DOMContentLoaded', function () {
            var autoOpen = sessionStorage.getItem('auto_open_employee');
            if (autoOpen) {
                try {
                    var data = JSON.parse(autoOpen);
                    sessionStorage.removeItem('auto_open_employee');

                    openEmployeeModal();
                    setTimeout(function () {
                        var insideRow = document.querySelector('.emp-row[data-emp-id="' + data.id + '"]');
                        if (insideRow) {
                            var exitForm = insideRow.querySelector('form');
                            if (exitForm) {
                                showAppConfirm('Employee ' + data.name + ' is already inside. Log EXIT now?', function (confirmed) {
                                    if (confirmed) {
                                        handleEmployeeExitForm(exitForm, data.name, true);
                                    }
                                }, 'Employee Exit');
                                return;
                            }
                        }
                        var empVehicleInput = document.getElementById('emp_vehicle_number');
                        var empIdInput = document.getElementById('emp_id');
                        var empNameInput = document.getElementById('emp_name');
                        var empDeptInput = document.getElementById('emp_department');
                        if (empVehicleInput) empVehicleInput.value = data.vehicle;
                        if (empIdInput) empIdInput.value = data.id;
                        if (empNameInput) empNameInput.value = data.name;
                        if (empDeptInput) empDeptInput.value = data.department || '';
                    }, 100);
                } catch (e) {
                    console.error('Failed to parse auto-open data:', e);
                }
            }
        });

        function fetchEmployeeByVehicle(vehicle) {
            if (!vehicle) return;
            vehicle = vehicle.toUpperCase().trim();
            
            // Clean up previous mode state
            const actionField = document.getElementById('action');
            const entryIdField = document.getElementById('entry_id');
            const modeTitle = document.getElementById('empModalModeTitle');
            const submitBtn = document.getElementById('empSubmitBtn');
            
            fetch('?page=get-employee-details&vehicle=' + encodeURIComponent(vehicle))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('emp_id').value = data.data.employee_id;
                        document.getElementById('emp_name').value = data.data.employee_name;
                        document.getElementById('emp_department').value = data.data.department || '';
                        
                        // Handle Mode Shift if already inside
                        if (data.data.is_inside) {
                            if (actionField) actionField.value = 'exit';
                            if (entryIdField) entryIdField.value = data.data.current_entry_id;
                            if (modeTitle) modeTitle.textContent = 'Employee Already Inside (Outward Mode)';
                            if (submitBtn) {
                                submitBtn.textContent = 'Log Exit';
                                submitBtn.style.background = '#ef4444'; // Red for exit
                                submitBtn.style.borderColor = '#ef4444';
                            }
                        } else {
                            if (actionField) actionField.value = 'inward';
                            if (entryIdField) entryIdField.value = '';
                            if (modeTitle) modeTitle.textContent = 'New Inward Entry';
                            if (submitBtn) {
                                submitBtn.textContent = 'Log Entry';
                                submitBtn.style.background = '#3b82f6'; // Blue for entry
                                submitBtn.style.borderColor = '#3b82f6';
                            }
                        }
                    } else {
                        document.getElementById('emp_id').value = '';
                        document.getElementById('emp_name').value = 'Not found in master';
                        document.getElementById('emp_department').value = '';
                        // Reset to inward if not found
                        if (actionField) actionField.value = 'inward';
                        if (modeTitle) modeTitle.textContent = 'New Inward Entry (Unknown Employee)';
                        if (submitBtn) {
                            submitBtn.textContent = 'Log Entry';
                            submitBtn.style.background = '#3b82f6';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }



        function filterEmployeeList() {
            var input = document.getElementById("empSearch");
            if (!input) return;
            var filter = input.value.toUpperCase().trim();
            var rows = document.getElementById("empInsideList");
            if (!rows) return;
            var empRows = rows.getElementsByClassName("emp-row");
            for (var i = 0; i < empRows.length; i++) {
                if (filter === "") {
                    empRows[i].style.display = "";
                    continue;
                }
                var text = empRows[i].textContent || empRows[i].innerText;
                empRows[i].style.display = text.toUpperCase().indexOf(filter) > -1 ? "" : "none";
            }
        }

        function viewEmployeeEntry(employeeId, employeeName, status) {
            var modal = document.getElementById('employeeModal');
            if (!modal) {
                alert('Employee modal not available on this page.');
                return;
            }

            // Open modal immediately for instant feedback
            openEmployeeModal();

            var wrapper = modal.children[0];
            var header = wrapper ? wrapper.children[0] : null;

            // Hide the entry form sections immediately
            var entryCards = document.querySelectorAll('#employeeModal .card');
            entryCards.forEach(function (card) {
                if (card) card.style.display = 'none';
            });

            // Show loading indicator
            var loadingDiv = document.createElement('div');
            loadingDiv.id = 'empLoading';
            loadingDiv.style.cssText = 'padding: 40px; text-align: center; color: #64748b;';
            loadingDiv.innerHTML = '<div style="font-size: 24px; margin-bottom: 10px;">⏳</div>Loading employee details...';
            if (wrapper && header) {
                wrapper.insertBefore(loadingDiv, header.nextSibling);
            }

            // Fetch and display data
            fetch('?page=get-employee-entry&id=' + employeeId)
                .then(response => response.json())
                .then(employeeData => {
                    // Remove loading
                    if (loadingDiv) loadingDiv.remove();

                    if (!employeeData.success) {
                        alert('Error: ' + employeeData.message);
                        closeEmployeeModal();
                        return;
                    }

                    var data = employeeData.data;
                    var modalTitle = header ? header.querySelector('h2') : null;

                    // Clean up old content
                    var oldInfo = document.getElementById('exitInfo');
                    if (oldInfo) oldInfo.remove();
                    var oldBtn = document.getElementById('viewCloseBtn');
                    if (oldBtn) oldBtn.remove();

                    // Create info display
                    var infoDiv = document.createElement('div');
                    infoDiv.id = 'exitInfo';
                    infoDiv.style.cssText = 'background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 20px; border-radius: 8px;';

                    var statusIcon = data.status === 'inside' ? '🟢' : '✅';
                    var statusText = data.status === 'inside' ? 'Currently Inside' : 'Exit Completed';

                    infoDiv.innerHTML = '<div style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">' + statusIcon + ' ' + statusText + '</div>' +
                        '<div style="display: grid; gap: 10px; font-size: 14px;">' +
                        '<div><strong>Vehicle:</strong> ' + (data.vehicle_number || 'N/A') + '</div>' +
                        '<div><strong>Employee ID:</strong> ' + (data.employee_id || 'N/A') + '</div>' +
                        '<div><strong>Name:</strong> ' + (data.employee_name || 'N/A') + '</div>' +
                        '<div><strong>Department:</strong> ' + (data.department || 'N/A') + '</div>' +
                        '<div><strong>Entry Time:</strong> ' + new Date(data.inward_datetime).toLocaleString() + '</div>' +
                        (data.outward_datetime ? '<div><strong>Exit Time:</strong> ' + new Date(data.outward_datetime).toLocaleString() + '</div>' : '') +
                        (data.outward_datetime ? '<div><strong>Duration:</strong> ' + calculateDuration(data.inward_datetime, data.outward_datetime) + '</div>' : '') +
                        '<div><strong>Entry Logged by:</strong> ' + (data.inward_by_name || 'N/A') + '</div>' +
                        (data.outward_by_name ? '<div><strong>Exit Logged by:</strong> ' + data.outward_by_name + '</div>' : '') +
                        (data.remarks ? '<div><strong>Remarks:</strong> ' + data.remarks + '</div>' : '') +
                        '</div>';

                    // Update title
                    if (modalTitle) modalTitle.textContent = '👁️ ' + data.employee_name;

                    // Insert info
                    if (wrapper && header) {
                        wrapper.insertBefore(infoDiv, header.nextSibling);
                    }

                    // Add close button
                    var closeBtn = document.createElement('button');
                    closeBtn.id = 'viewCloseBtn';
                    closeBtn.type = 'button';
                    closeBtn.onclick = function () { closeEmployeeModal(); };
                    closeBtn.style.cssText = 'width: 100%; padding: 12px; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 20px;';
                    closeBtn.textContent = '✕ Close';
                    if (wrapper) wrapper.appendChild(closeBtn);
                })
                .catch(error => {
                    if (loadingDiv) loadingDiv.remove();
                    console.error('Error:', error);
                    alert('Error loading data. Please try again.');
                    closeEmployeeModal();
                });
        }

        function calculateDuration(startTime, endTime) {
            if (!startTime) return 'N/A';
            var start = new Date(startTime);
            var end = endTime ? new Date(endTime) : new Date();
            var diff = end - start;
            var hours = Math.floor(diff / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            return hours + 'h ' + minutes + 'm' + (endTime ? '' : ' (current)');
        }

        function openQRScannerModal() {
            var modal = document.getElementById('qrScannerModal');
            if (modal) {
                modal.style.display = 'block';

                // Auto-start camera after modal opens (all environments)
                setTimeout(function () {
                    startQRScannerModal();
                }, 300); // Small delay to let modal render
            }
        }

        function closeQRScannerModal() {
            stopQRScannerModal();
            var modal = document.getElementById('qrScannerModal');
            if (modal) modal.style.display = 'none';
            var input = document.getElementById('qr_manual_input');
            if (input) input.value = '';

            // Ensure start button is visible for next time
            var startBtn = document.getElementById("startScanBtnModal");
            if (startBtn) startBtn.style.display = "inline-block";

            // Reset scanner tracking
            isScanningModal = false;
            isProcessingScanModal = false; // RELEASE THE LOCK FOR SUBSEQUENT SCANS
            lastScannedTextModal = '';
            lastScannedTimeModal = 0;
        }

        function showQRStatusModal(message, type) {
            var statusDiv = document.getElementById('qrStatusModal');
            if (!statusDiv) return;

            statusDiv.style.display = 'block';
            statusDiv.innerHTML = message; // Use innerHTML to support HTML formatting

            // Style based on type
            if (type === 'success') {
                statusDiv.style.background = '#f0fdf4';
                statusDiv.style.color = '#065f46';
                statusDiv.style.borderLeft = '4px solid #10b981';
                // Removed auto-hide - stays visible until user closes it
            } else if (type === 'error') {
                statusDiv.style.background = '#fef2f2';
                statusDiv.style.color = '#991b1b';
                statusDiv.style.borderLeft = '4px solid #ef4444';
                statusDiv.style.fontSize = '13px';
                statusDiv.style.lineHeight = '1.6';
                // Don't auto-hide errors - user needs to read them
            } else { // info
                statusDiv.style.background = '#eff6ff';
                statusDiv.style.color = '#1e40af';
                statusDiv.style.borderLeft = '4px solid #3b82f6';
            }
        }

        // QR Scanner Modal Variables
        var html5QrCodeModal = null;
        var isScanningModal = false;
        var isProcessingScanModal = false;
        var lastScannedTextModal = "";
        var lastScannedTimeModal = 0;

        function startQRScannerModal() {
            // 1. Double-click prevention
            if (isScanningModal) return;
            isScanningModal = true;

            // 2. Reset UI
            var statusDiv = document.getElementById("qrStatusModal");
            if (statusDiv) { statusDiv.style.display = "block"; statusDiv.innerHTML = "⌛ Starting Camera..."; }
            
            // 3. Ensure Zoom Slider is Visible (Do NOT hide it)
            var zoomContainer = document.getElementById("zoomContainerModal");
            if (zoomContainer) zoomContainer.style.display = "block";

            var startBtn = document.getElementById("startScanBtnModal");
            if (startBtn) startBtn.style.display = "none";

            var stopBtn = document.getElementById("stopScanBtnModal");
            if (stopBtn) stopBtn.style.display = "none";

            // 4. Clean up any zombie instances
            if (html5QrCodeModal) {
                try {
                    html5QrCodeModal.clear().then(function () {
                        html5QrCodeModal = null;
                        initializeScanner();
                    }).catch(function (err) {
                        html5QrCodeModal = null;
                        initializeScanner();
                    });
                } catch (e) {
                    html5QrCodeModal = null;
                    initializeScanner();
                }
            } else {
                initializeScanner();
            }
        }

        function stopQRScannerModal() {
            if (!html5QrCodeModal) {
                isScanningModal = false;
                return;
            }

            try {
                html5QrCodeModal.stop().then(function () {
                    html5QrCodeModal.clear();
                    isScanningModal = false;
                }).catch(function (err) {
                    console.log("Stop error:", err);
                    try { html5QrCodeModal.clear(); } catch (e) { }
                    isScanningModal = false;
                });
            } catch (e) {
                isScanningModal = false;
            }

            var stopBtn = document.getElementById("stopScanBtnModal");
            if (stopBtn) stopBtn.style.display = "none";

            var startBtn = document.getElementById("startScanBtnModal");
            if (startBtn) startBtn.style.display = "inline-block";
        }

        function updateCameraList() {
            Html5Qrcode.getCameras().then(devices => {
                const select = document.getElementById('cameraSelectModal');
                const listDiv = document.getElementById('cameraDeviceList');
                if (select && devices && devices.length > 0) {
                    var currentVal = select.value;
                    const preferredScannerId = localStorage.getItem('preferredScannerId');
                    
                    select.innerHTML = '';
                    devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.label || `Camera ${select.length + 1}`;
                        if (device.id === preferredScannerId && !currentVal) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                    if (currentVal) select.value = currentVal;
                    else if (preferredScannerId) select.value = preferredScannerId;
                    
                    if (listDiv) listDiv.style.display = 'block';
                }
            }).catch(err => { console.error("Camera detection error", err); });
        }

        function forceKillAllCameras() {
            try {
                if (window.localStreamManual) {
                    window.localStreamManual.getTracks().forEach(t => t.stop());
                    window.localStreamManual = null;
                }
                const container = document.getElementById("qr-reader-modal");
                if (container) {
                    const videoTags = container.getElementsByTagName("video");
                    for (let vt of videoTags) {
                        vt.pause();
                        if (vt.srcObject) {
                            vt.srcObject.getTracks().forEach(track => track.stop());
                            vt.srcObject = null;
                        }
                        vt.remove();
                    }
                }
            } catch(e) {}
        }

        var isSwitchingCameraGlobal = false;
        var currentScannerId = "qr-reader-modal";
        var isSystemInitializing = false;

        function restartScannerWithNewDevice(deviceId) {
            if (!deviceId || deviceId === "" || isSwitchingCameraGlobal) return;
            isSwitchingCameraGlobal = true;
            
            const cleanupAndStartNew = (targetId) => {
                if (html5QrCodeModal) {
                    try { html5QrCodeModal.clear(); } catch(e) {}
                    html5QrCodeModal = null;
                }
                setTimeout(() => {
                    isSwitchingCameraGlobal = false;
                    // Save as preferred whenever explicitly switched
                    localStorage.setItem('preferredScannerId', targetId);
                    initializeScanner(targetId);
                }, 800);
            };

            if (html5QrCodeModal) {
                var state = 1; 
                try { state = html5QrCodeModal.getState(); } catch(e) {}
                if (state === 3) {
                    html5QrCodeModal.stop().then(() => cleanupAndStartNew(deviceId))
                        .catch(() => cleanupAndStartNew(deviceId));
                } else {
                    cleanupAndStartNew(deviceId);
                }
            } else {
                cleanupAndStartNew(deviceId);
            }
        }

        function initializeScanner(deviceId = null) {
            if (isSystemInitializing) return;
            isSystemInitializing = true;
            
            updateCameraList();
            
            const startActual = (targetId) => {
                const uniqueId = "qr-reader-modal-" + Math.floor(Math.random() * 1000000);
                try {
                    const wrapper = document.getElementById("qr-wrapper-modal");
                    if (wrapper) {
                        wrapper.innerHTML = `<div id="${uniqueId}" style="width: 100%; min-height: 300px; overflow: hidden; position: relative; background:#000;"></div>`;
                    }
                    
                    currentScannerId = uniqueId;
                    html5QrCodeModal = new Html5Qrcode(uniqueId);

                    // GOLDEN CONFIG - Maintaining 4K/UHD constraints for dense scanning
                    const preferredScannerId = localStorage.getItem('preferredScannerId');
                    const effectiveTargetId = targetId || preferredScannerId;
                    
                    var cameraSelection = effectiveTargetId ? effectiveTargetId : { facingMode: "environment" };
                    var config = {
                        fps: 30,
                        // qrbox: REMOVED -> Scan Full Frame for maximum detail (Critical for Dense QRs)
                        showTorchButtonIfSupported: true,
                        useBarCodeDetectorIfSupported: false,
                        videoConstraints: {
                            deviceId: effectiveTargetId ? { exact: effectiveTargetId } : undefined,
                            facingMode: "environment",
                            focusMode: "continuous",
                            width: { min: 1280, ideal: 3840 }, 
                            height: { min: 720, ideal: 2160 }
                        }
                    };

                    html5QrCodeModal.start(cameraSelection, config, onQRCodeScannedModal, () => {})
                    .then(() => {
                        isScanningModal = true;
                        isSystemInitializing = false; 
                        document.getElementById("stopScanBtnModal").style.display = "inline-block";
                        document.getElementById("startScanBtnModal").style.display = "none";
                        showQRStatusModal("📷 READY: Scan active", "info");
                        
                        try {
                            const video = document.querySelector(`#${uniqueId} video`);
                            if (video && video.srcObject) window.localStreamManual = video.srcObject;
                        } catch(se) {}
                        
                        setTimeout(() => {
                            if (html5QrCodeModal && html5QrCodeModal.getState() === 3) applyManualZoom(1.0);
                        }, 1200);

                    }).catch(err => {
                        isSystemInitializing = false;
                        isScanningModal = false;
                        document.getElementById("startScanBtnModal").style.display = "inline-block";
                        console.error("Camera Error:", err);
                        showQRStatusModal("❌ Hardware Locked or Busy.", "error"); 
                    });
                } catch(e) { 
                    isSystemInitializing = false;
                    isScanningModal = false;
                }
            };

            if (html5QrCodeModal) {
                try {
                    if (html5QrCodeModal.getState() === 3) {
                        html5QrCodeModal.stop().then(() => {
                            html5QrCodeModal.clear();
                            forceKillAllCameras();
                            setTimeout(() => startActual(deviceId), 1500);
                        }).catch(() => {
                            forceKillAllCameras();
                            setTimeout(() => startActual(deviceId), 1500);
                        });
                        return;
                    }
                } catch(e) {}
            }
            
            forceKillAllCameras();
            setTimeout(() => startActual(deviceId), 1500);
        }

        // --- NEW MANUAL ZOOM FUNCTION (With CSS Fallback) ---
        function applyManualZoom(val) {
            val = parseFloat(val);
            if (!html5QrCodeModal) return;

            // 1. Try Hardware Zoom
            try {
                if (html5QrCodeModal.applyVideoConstraints) {
                        html5QrCodeModal.applyVideoConstraints({
                        advanced: [{ zoom: val }]
                    }).then(() => console.log("HW Zoom applied:", val))
                        .catch(e => {
                            console.log("HW Zoom failed, using CSS:", e);
                            applyCSSZoom(val);
                        });
                } else {
                    applyCSSZoom(val); 
                }
            } catch (e) { 
                console.error(e); 
                applyCSSZoom(val); // Fallback on crash
            }
        }

        function applyCSSZoom(val) {
            var v = document.querySelector(`#${currentScannerId} video`) || document.querySelector("video");
            if (v) {
                v.style.transform = "scale(" + val + ")";
                v.style.transformOrigin = "center center";
            }
        }


        function onQRCodeScannedModal(decodedText) {
            if (!decodedText || isProcessingScanModal) return;
            var text = String(decodedText).trim();
            if (text === "") return;

            var now = Date.now();
            if (now - lastScannedTimeModal < 1000) return;
            if (text === lastScannedTextModal && now - lastScannedTimeModal < 3000) return;

            isProcessingScanModal = true;
            lastScannedTextModal = text;
            lastScannedTimeModal = now;

            showQRStatusModal("⌛ Checking Database...", "info");

            try {
                var data = null;
                var originalText = text;

                // --- COMPREHENSIVE DECODING STRATEGY ---
                // 1. Try JSON parse directly
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // 2. Try base64 decode then JSON
                    try {
                        var decoded = atob(text);
                        data = JSON.parse(decoded);
                    } catch (e2) {
                        // 3. Try URL decode then JSON
                        try {
                            var urlDecoded = decodeURIComponent(text);
                            data = JSON.parse(urlDecoded);
                        } catch (e3) {
                            // 4. JWT / E-Invoice Handling (Header.Payload.Signature)
                            if (text.startsWith('ey') && text.indexOf('.') > -1) {
                                try {
                                    var parts = text.split('.');
                                    if (parts.length >= 2) {
                                        var payload = parts[1];
                                        // Fix Base64Url padding/chars
                                        var base64 = payload.replace(/-/g, '+').replace(/_/g, '/');
                                        // Pad with = if needed
                                        while (base64.length % 4) { base64 += '='; }

                                        var jsonPayload = decodeURIComponent(atob(base64).split('').map(function (c) {
                                            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                                        }).join(''));

                                        var jwtData = JSON.parse(jsonPayload);
                                        // GST E-Invoice spec: data is often a string inside the 'data' key
                                        if (jwtData.data && typeof jwtData.data === 'string') {
                                            data = JSON.parse(jwtData.data);
                                        } else {
                                            data = jwtData.data || jwtData;
                                        }
                                    }
                                } catch (e4) {
                                    console.log("JWT decode failed", e4);
                                }
                            }

                            // 5. Check if it's a GST invoice QR (pipe-separated format)
                            if (!data && text.indexOf('|') > -1) {
                                var parts = text.split('|');
                                data = {
                                    type: 'bill',
                                    bill_number: parts[0] || '',
                                    DocNo: parts[0] || '',
                                    raw: text
                                };
                            }
                        }
                    }
                }

                var isPageInward = window.location.search.indexOf("page=inward") !== -1;
                var empId = null;
                var empName = null;
                var isBill = false;

                if (data && (data.type === "employee" || data.employee_id)) {
                    empId = String(data.id || data.employee_id || "");
                    empName = String(data.name || data.employee_name || empId || "Employee");
                } else if (data && (data.DocNo || data.bill_number || data.type === 'bill')) {
                    isBill = true;
                } else if (text.length >= 4 && text.length <= 15) {
                    // Potential Vehicle or ID, but exclude likely bill numbers if on inward page
                    if (!isPageInward) {
                        empId = text;
                    } else {
                        // On inward page, treat unknown text as bill number if it looks like one
                        isBill = true;
                        if (!data) data = { bill_number: text };
                    }
                }

                // Check if this ID/Vehicle is already inside
                var insideRow = null;
                if (empId) {
                    insideRow = document.querySelector(".emp-row[data-emp-id=\"" + empId + "\"]");
                    if (!insideRow) {
                        // Check if it's a vehicle number match in the list (exact match)
                        insideRow = document.querySelector(".emp-row[data-vehicle=\"" + empId.toUpperCase() + "\"]");
                    }
                }

                // --- Handle Logic ---
                if (isBill) {
                    stopQRScannerModal();
                    var billCode = String(data.DocNo || data.bill_number || text);

                    // Construct the standardized data object expected by backend
                    var finalData = Object.assign({}, data); // Clone
                    if (finalData.raw) delete finalData.raw; // Remove raw JWT string if present to save space

                    // Generate Summary Item if needed (matching user's format)
                    var itemCount = parseInt(data.ItemCnt || data.item_count || 0);
                    // Define and populate itemsArray FIRST
                    var itemsArray = [];
                    if (data) {
                        if (Array.isArray(data.ItemList)) itemsArray = data.ItemList; // GST E-Invoice
                        else if (Array.isArray(data.items)) itemsArray = data.items;
                        else if (Array.isArray(data.products)) itemsArray = data.products;
                        else if (Array.isArray(data.line_items)) itemsArray = data.line_items;
                    }

                    // Generate Summary Item if needed (matching user's format)
                    var itemCount = parseInt(data.ItemCnt || data.item_count || 0);
                    if (itemCount === 0 && Array.isArray(itemsArray) && itemsArray.length > 0) {
                        itemCount = itemsArray.length;
                    }

                    var summaryItem = {
                        "item_code": "E-INV",
                        "item_name": itemCount + " Items (Details as per E-Invoice)",
                        "quantity": itemCount,
                        "unit": "Nos"
                    };

                    // Ensure 'items' key exists for backend parsing
                    if (!finalData.items || !Array.isArray(finalData.items) || finalData.items.length === 0) {
                        finalData.items = [summaryItem];
                    }

                    if (isPageInward) {
                        // Fill form fields
                        var bf = document.getElementById("bill_number");
                        if (bf) bf.value = billCode;

                        // Store DECODED JSON in hidden field (Robust Method)
                        var jsonString = JSON.stringify(finalData);
                        var foundField = false;

                        // 1. Try ID 'qr_raw_data'
                        var qrFieldId = document.getElementById("qr_raw_data");
                        if (qrFieldId) { qrFieldId.value = jsonString; foundField = true; }

                        // 2. Try Name 'qr_code_data' (Standard backend expectation)
                        var qrFieldsName = document.getElementsByName("qr_code_data");
                        if (qrFieldsName.length > 0) { qrFieldsName[0].value = jsonString; foundField = true; }

                        // 3. Try Name 'qr_raw_data'
                        var qrFieldsNameRaw = document.getElementsByName("qr_raw_data");
                        if (qrFieldsNameRaw.length > 0) { qrFieldsNameRaw[0].value = jsonString; foundField = true; }

                        // 4. FORCE INJECT if missing!
                        if (!foundField) {
                            var form = document.getElementById("bill_number").closest("form");
                            if (form) {
                                var input = document.createElement("input");
                                input.type = "hidden";
                                input.name = "qr_code_data"; // Default to this name
                                input.value = jsonString;
                                form.appendChild(input);
                                console.log("Auto-injected qr_code_data field");
                            }
                        }

                        // Also try to fill a hidden 'items' input if it exists (for direct submission)
                        var itemsField = document.getElementById("items_json_input"); // Hypothetical
                        if (itemsField) itemsField.value = JSON.stringify(finalData.items);

                        // Show preview of decoded QR data
                        var itemsPreview = "<br><div style='text-align:left; margin:10px 0; max-height:200px; overflow-y:auto; background:#f9fafb; padding:10px; border-radius:8px;'>";
                        itemsPreview += "<strong>📄 Scanned Data:</strong><pre style='margin:5px 0; font-size:11px; white-space:pre-wrap; word-wrap:break-word;'>";
                        itemsPreview += JSON.stringify(finalData, null, 2);
                        itemsPreview += "</pre></div>";

                        // Show success message
                        showQRStatusModal("✅ Bill #" + billCode + " scanned!" + itemsPreview, "success");
                        
                        // Populate manual field so "Process QR Data" button works
                        var manualField = document.getElementById("qr_manual_input");
                        if (manualField) manualField.value = originalText;

                        // Auto-close after 2.5s
                        setTimeout(function() {
                            closeQRScannerModal();
                        }, 2500);
                    } else {
                        sessionStorage.setItem("pending_qr_data", JSON.stringify(finalData));
                        window.location.href = "?page=qr-scanner";
                    }
                } else if (insideRow) {
                    // FOUND INSIDE -> Automatic Exit logic (using AJAX now)
                    var exitForm = insideRow.querySelector("form");
                    if (exitForm) {
                        showQRStatusModal("✅ Employee found. Registering EXIT...", "success");
                        stopQRScannerModal();
                        closeQRScannerModal();
                        
                        // Use the AJAX handler to log exit and reopen scanner
                        handleEmployeeExitForm(exitForm, empName, true);
                        return;
                    }
                } else if (empId) {
                    // NOT FOUND INSIDE -> Entry logic
                    if (typeof currentEmployeeModalMode !== "undefined" && currentEmployeeModalMode === "exit") {
                        showQRStatusModal("❌ Employee NOT marked as inside. Cannot exit.", "error");
                        isProcessingScanModal = false; return;
                    }

                    stopQRScannerModal(); 
                    closeQRScannerModal();
                    
                    // Force Entry form mode (this stops the auto-scanner loop)
                    openEmployeeModal("entry");

                    var vField = document.getElementById("emp_vehicle_number");
                    if (vField) {
                        vField.value = (data && data.vehicle) ? data.vehicle.toUpperCase() : empId.toUpperCase();
                        if (typeof fetchEmployeeByVehicle === "function") fetchEmployeeByVehicle(vField.value);
                    }
                    
                    // FIXED: Always fill name and ID from QR if available (don't skip if vehicle exists)
                    if (data) {
                        var idF = document.getElementById("emp_id"); if (idF) idF.value = String(data.id || data.employee_id || "");
                        var nmF = document.getElementById("emp_name"); if (nmF) nmF.value = String(data.name || data.employee_name || "");
                        var dtF = document.getElementById("emp_department"); if (dtF) dtF.value = String(data.department || "");
                    }
                } else {
                    // FINAL FALLBACK: If we are on inward page, just force it as a bill number
                    if (isPageInward) {
                        stopQRScannerModal();
                        var bf = document.getElementById("bill_number");
                        if (bf) bf.value = text;
                        var qrDataField = document.getElementById("qr_raw_data");
                        if (qrDataField) qrDataField.value = originalText;

                        closeQRScannerModal();
                        showQRStatusModal("✅ Scanned data filled (Format unknown).", "warning");
                    } else {
                        // Detailed debug info
                        var debugMsg = "⚠️ Unknown data format.<br><hr style='margin:10px 0'>";
                        debugMsg += "<strong>Raw Data Header:</strong><br>" + text.substring(0, 100);
                        debugMsg += "<br><br><small>Length: " + text.length + "</small>";
                        showQRStatusModal(debugMsg, "error");
                        isProcessingScanModal = false;
                    }
                }
            } catch (err) {
                console.error("Scan Error:", err);
                showQRStatusModal("❌ Error: " + String(err), "error");
                isProcessingScanModal = false;
            }
        }
        function processManualQR() {
            var input = document.getElementById('qr_manual_input');
            if (!input) return;
            var qrData = input.value.trim();
            if (!qrData) {
                showQRStatusModal('Enter QR data', 'error');
                return;
            }
            onQRCodeScannedModal(qrData);
            input.value = '';
        }
        function openChangePasswordModal() {
            const modal = document.getElementById('passwordModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById('passwordModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                modal.querySelector('form').reset();
            }
        }

        function handlePasswordChange(form) {
            const formData = new FormData(form);
            const currentPass = formData.get('old_password');
            const newPass = formData.get('new_password');
            const confirmPass = formData.get('confirm_password');

            // 1. Basic empty check
            if (!currentPass || !newPass || !confirmPass) {
                Swal.fire('Error', 'All fields are required', 'error');
                return;
            }

            // 2. Length check
            if (newPass.length < 6) {
                Swal.fire('Error', 'New password must be at least 6 characters long', 'error');
                return;
            }

            // 3. Current vs New check
            if (currentPass === newPass) {
                Swal.fire('Error', 'New password cannot be the same as current password', 'error');
                return;
            }

            // 4. Mismatch check
            if (newPass !== confirmPass) {
                Swal.fire({
                    icon: 'error',
                    title: 'Mismatch',
                    text: 'New passwords do not match!',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            fetch('?page=change_password_action', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        closeChangePasswordModal();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Communication failed.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
    </script>

