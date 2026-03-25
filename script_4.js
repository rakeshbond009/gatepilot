
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
                    // Default: show both
                    if (inwardCard) inwardCard.style.display = 'block';
                    if (outwardCard) outwardCard.style.display = 'block';
                    if (modalTitle) modalTitle.textContent = '👤 Employee Entry/Exit';
                }
            } else {
                console.error('Employee modal not found');
            }
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
                                        exitForm.onsubmit = null;
                                        exitForm.submit();
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
            fetch('?page=get-employee-details&vehicle=' + encodeURIComponent(vehicle))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('emp_id').value = data.data.employee_id;
                        document.getElementById('emp_name').value = data.data.employee_name;
                        document.getElementById('emp_department').value = data.data.department || '';
                    } else {
                        document.getElementById('emp_id').value = '';
                        document.getElementById('emp_name').value = 'Not found in master';
                        document.getElementById('emp_department').value = '';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function handleEmployeeExitForm(form, empName) {
            showAppConfirm('Are you sure employee ' + empName + ' is exiting?', function (confirmed) {
                if (confirmed) {
                    form.onsubmit = null;
                    form.submit();
                }
            }, 'Confirm Exit');
        }

        function filterEmployeeList() {
            var input = document.getElementById("empSearch");
            if (!input) return;
            var filter = input.value.toUpperCase();
            var rows = document.getElementById("empInsideList");
            if (!rows) return;
            var empRows = rows.getElementsByClassName("emp-row");
            for (var i = 0; i < empRows.length; i++) {
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

        var html5QrCodeModal = null;
        var isScanningModal = false;

        function openQRScannerModal() {
            var modal = document.getElementById('qrScannerModal');
            if (modal) {
                modal.style.display = 'block';

                // Auto-start camera on mobile devices after modal opens
                var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                if (isMobile) {
                    setTimeout(function () {
                        startQRScannerModal();
                    }, 300); // Small delay to let modal render
                }
            }
        }

        function closeQRScannerModal() {
            stopQRScannerModal();
            var modal = document.getElementById('qrScannerModal');
            if (modal) modal.style.display = 'none';
            var input = document.getElementById('qr_manual_input');
            if (input) input.value = '';

            // Reset scanner tracking
            isScanningModal = false;
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


        var qrParamsLock = false;


        // Debug Logger
        function logQR(msg) {
            console.log("[QR_DEBUG] " + msg);
            var d = document.getElementById("qrDebugLog");
            if (!d) {
                d = document.createElement("div");
                d.id = "qrDebugLog";
                d.style.cssText = "position:fixed;bottom:0;left:0;width:100%;height:150px;background:rgba(0,0,0,0.8);color:#0f0;font-family:monospace;font-size:10px;overflow:auto;z-index:99999;padding:5px;pointer-events:none;display:none;";
                document.body.appendChild(d);
            }
            // Uncomment to show debug on screen
            // d.style.display = "block"; 
            d.innerHTML += "<div>" + msg + "</div>";
            d.scrollTop = d.scrollHeight;
        }

        var qrLock = false;


        var qrLock = false;


        var qrLock = false;


        var qrLock = false;



        // QR Scanner Modal Variables
        var html5QrCodeModal = null;
        var isScanningModal = false;
        var isProcessingScanModal = false;
        var lastScannedTextModal = "";
        var lastScannedTimeModal = 0;

        function startQRScannerModal() {
            // Prevent double-click
            if (isScanningModal) {
                console.log("Scanner already running");
                return;
            }

            isScanningModal = true;
            isProcessingScanModal = false;
            lastScannedTextModal = "";

            var statusDiv = document.getElementById("qrStatusModal");
            if (statusDiv) { statusDiv.style.display = "block"; statusDiv.innerHTML = "⌛ Starting Camera..."; }
            var zoomContainer = document.getElementById("zoomContainerModal");
            if (zoomContainer) zoomContainer.style.display = "block";
            var stopBtn = document.getElementById("stopScanBtnModal");
            if (stopBtn) stopBtn.style.display = "none";

            // Flutter Native Check
            if (window.FlutterScanner || window.FlutterScannerChannel) {
                if (!window.FlutterScanner && window.FlutterScannerChannel) {
                    window.FlutterScanner = { postMessage: function (msg) { window.FlutterScannerChannel.postMessage(msg); } };
                }
                window.onNativeScanSuccess = function (decodedText) {
                    lastScannedTextModal = "";
                    onQRCodeScannedModal(decodedText);
                };
                window.FlutterScanner.postMessage("startScan");
                return;
            }

            // Clean up any existing instance
            if (html5QrCodeModal) {
                try {
                    html5QrCodeModal.stop().then(function () {
                        html5QrCodeModal.clear();
                        html5QrCodeModal = null;
                        initializeScanner();
                    }).catch(function (err) {
                        console.log("Stop error (ignoring):", err);
                        try { html5QrCodeModal.clear(); } catch (e) { }
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
                applyCSSZoom(val);
            }
        }

        function applyCSSZoom(val) {
            var v = document.querySelector("#qr-reader-modal video");
            if (v) {
                v.style.transform = "scale(" + val + ")";
                v.style.transformOrigin = "center center";
            }
        }

        function initializeScanner() {
            setTimeout(function () {
                try {
                    html5QrCodeModal = new Html5Qrcode("qr-reader-modal");

                    var config = {
                        fps: 30, // High FPS for better Autofocus
                        // qrbox: REMOVED -> Scan Full Frame for maximum detail
                        showTorchButtonIfSupported: true,
                        useBarCodeDetectorIfSupported: false,
                        videoConstraints: {
                            facingMode: "environment",
                            focusMode: "continuous",
                            width: { min: 1280, ideal: 3840 }, // FORCE HD or 4K
                            height: { min: 720, ideal: 2160 }
                        }
                    };

                    html5QrCodeModal.start(
                        { facingMode: "environment" },
                        config,
                        function (text) { onQRCodeScannedModal(text); },
                        function (err) { }
                    ).then(function () {
                        var stopBtn = document.getElementById("stopScanBtnModal");
                        if (stopBtn) stopBtn.style.display = "inline-block";

                        showQRStatusModal("📷 Scanning... Try Zoom Slider if needed!", "info");

                        // Force initial focus attempt
                        setTimeout(function () { applyManualZoom(1.0); }, 500);

                    }).catch(function (err) {
                        isScanningModal = false;
                        console.error("Camera start error:", err);
                        showQRStatusModal("❌ Camera Error: " + err, "error");
                    });

                } catch (e) {
                    isScanningModal = false;
                    console.error("Init error:", e);
                    showQRStatusModal("❌ Init Error", "error");
                }
            }, 200);
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
                console.log("Stop exception:", e);
                isScanningModal = false;
            }

            var stopBtn = document.getElementById("stopScanBtnModal");
            if (stopBtn) stopBtn.style.display = "none";
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
                        var rows = document.querySelectorAll(".emp-row");
                        for (var i = 0; i < rows.length; i++) {
                            if (rows[i].textContent.toUpperCase().indexOf(empId.toUpperCase()) !== -1) {
                                insideRow = rows[i]; break;
                            }
                        }
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

                        showQRStatusModal("✅ Bill #" + billCode + " scanned!" + itemsPreview, "success");
                        // Preview stays open until user closes it manually
                    } else {
                        sessionStorage.setItem("pending_qr_data", JSON.stringify(finalData));
                        window.location.href = "?page=qr-scanner";
                    }
                } else if (insideRow) {
                    // FOUND INSIDE -> Force Exit logic
                    var exitForm = insideRow.querySelector("form");
                    if (exitForm) {
                        stopQRScannerModal(); closeQRScannerModal();
                        showAppConfirm("Employee is already inside. Register EXIT now?", function (conf) {
                            if (conf) { exitForm.onsubmit = null; exitForm.submit(); } else { isProcessingScanModal = false; }
                        }, "Exit Confirmation");
                        return;
                    }
                } else if (empId) {
                    // NOT FOUND INSIDE -> Entry logic
                    if (typeof currentEmployeeModalMode !== "undefined" && currentEmployeeModalMode === "exit") {
                        showQRStatusModal("❌ Employee NOT marked as inside. Cannot exit.", "error");
                        isProcessingScanModal = false; return;
                    }

                    stopQRScannerModal(); closeQRScannerModal();
                    var mode = (typeof currentEmployeeModalMode !== "undefined") ? currentEmployeeModalMode : "inward";
                    openEmployeeModal(mode === "exit" ? "inward" : mode);

                    var vField = document.getElementById("emp_vehicle_number");
                    if (vField) {
                        vField.value = (data && data.vehicle) ? data.vehicle.toUpperCase() : empId.toUpperCase();
                        if (typeof fetchEmployeeByVehicle === "function") fetchEmployeeByVehicle(vField.value);
                    }
                    if (data && !data.vehicle) {
                        var idF = document.getElementById("emp_id"); if (idF) idF.value = empId;
                        var nmF = document.getElementById("emp_name"); if (nmF) nmF.value = empName || "";
                        var dtF = document.getElementById("emp_department"); if (dtF) dtF.value = data.department || "";
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
    