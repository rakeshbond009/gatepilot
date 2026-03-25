<?php
$file = 'index.php';
$content = file_get_contents($file);

// 1. Extract and Move decodeJWT
$jwtFuncPattern = '/function decodeJWT\(token\) \{.*?return JSON\.parse\(jsonPayload\);.*?\} catch \(e\) \{.*?return null;.*?\}.*?\}/s';
if (preg_match($jwtFuncPattern, $content, $matches)) {
    $jwtFunc = $matches[0];
    // Remove it from current location
    $content = str_replace($jwtFunc, '', $content);

    // Insert it into the first global script block (e.g., after showAppLoader)
    $content = preg_replace('/function showAppLoader.*?\{.*?\}/s', '$0' . "\n\n        " . $jwtFunc, $content);
}

// 2. Update startQRScannerModal
$newStartFunc = 'function startQRScannerModal() {
            if (isScanningModal) return;

            // Reset UI and State
            isProcessingScanModal = false;
            lastScannedTextModal = "";
            var statusDiv = document.getElementById("qrStatusModal");
            if (statusDiv) { statusDiv.style.display = "none"; statusDiv.innerHTML = ""; }
            var zoomContainer = document.getElementById("zoomContainerModal");
            if (zoomContainer) zoomContainer.style.display = "none";

            // Native Flutter Bridge detection
            if (window.FlutterScanner || window.FlutterScannerChannel) {
                if (!window.FlutterScanner && window.FlutterScannerChannel) {
                    window.FlutterScanner = {
                        postMessage: function (msg) { window.FlutterScannerChannel.postMessage(msg); }
                    };
                }
                window.onNativeScanSuccess = function (decodedText) {
                    lastScannedTextModal = "";
                    onQRCodeScannedModal(decodedText);
                };
                isScanningModal = true;
                window.FlutterScanner.postMessage("startScan");
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showQRStatusModal("❌ Camera access not supported.", "error");
                return;
            }

            if (html5QrCodeModal) {
                try { html5QrCodeModal.clear(); } catch (e) { }
            }

            html5QrCodeModal = new Html5Qrcode("qr-reader-modal");

            // Highly Optimized for small dot QR 1080p
            var videoConstraints = {
                facingMode: "environment",
                width: { min: 640, ideal: 1920, max: 1920 },
                height: { min: 480, ideal: 1080, max: 1080 },
                aspectRatio: { ideal: 1.0 }
            };

            var config = {
                fps: 25,
                qrbox: function (viewfinderWidth, viewfinderHeight) {
                    var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    var qrboxSize = Math.floor(minEdge * 0.85);
                    return { width: qrboxSize, height: qrboxSize };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true
            };

            html5QrCodeModal.start(videoConstraints, config, function(text) {
                onQRCodeScannedModal(text);
            }, function(err) {}).then(function() {
                isScanningModal = true;
                var stopBtn = document.getElementById("stopScanBtnModal");
                if (stopBtn) stopBtn.style.display = "inline-block";
                var startBtn = document.getElementById("startScanBtnModal");
                if (startBtn) startBtn.style.display = "none";
                
                showQRStatusModal("📷 Scanner Ready! Focus on the QR code.", "info");

                // Initialize Zoom and Advanced Features
                setTimeout(function() {
                    try {
                        var track = html5QrCodeModal.getRunningTrack();
                        if (!track) return;
                        var caps = track.getCapabilities();
                        
                        // Force Continuous Focus
                        if (caps.focusMode && caps.focusMode.includes("continuous")) {
                            track.applyConstraints({ advanced: [{ focusMode: "continuous" }] }).catch(function(e){});
                        }

                        // Enable Zoom Slider
                        if (caps.zoom) {
                            var zCont = document.getElementById("zoomContainerModal");
                            var zInp = document.getElementById("zoomInputModal");
                            if (zCont && zInp) {
                                zCont.style.display = "block";
                                zInp.min = caps.zoom.min;
                                zInp.max = caps.zoom.max;
                                zInp.step = caps.zoom.step || 0.1;
                                zInp.value = caps.zoom.min;
                                zInp.oninput = function () {
                                    track.applyConstraints({ advanced: [{ zoom: parseFloat(this.value) }] }).catch(function(e){});
                                };
                            }
                        }
                    } catch (e) {
                        console.warn("Advanced constraints feature error:", e);
                    }
                }, 1000);
            }).catch(function(err) {
                console.error("Scanner Start Error:", err);
                showQRStatusModal("❌ Camera Error: " + (err.message || err), "error");
                isScanningModal = false;
            });
        }';

$content = preg_replace('/function startQRScannerModal\(.*?\).*?\{.*?\}\n\n        function stopQRScannerModal/s', $newStartFunc . "\n\n        function stopQRScannerModal", $content);

// 3. Update onQRCodeScannedModal for defense against undefined
$newOnScanFunc = 'function onQRCodeScannedModal(decodedText) {
            if (!decodedText || typeof decodedText !== "string" || isProcessingScanModal) return;
            var text = decodedText.trim();
            if (text === "") return;

            // Debounce
            var now = Date.now();
            if (text === lastScannedTextModal && now - lastScannedTimeModal < 3000) return;
            if (now - lastScannedTimeModal < 1000) return;

            isProcessingScanModal = true;
            lastScannedTextModal = text;
            lastScannedTimeModal = now;

            showQRStatusModal("⌛ Processing Scan...", "info");

            try {
                var data = null;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    if (typeof decodeJWT === "function") {
                        var jwtData = decodeJWT(text);
                        if (jwtData) data = jwtData.data || jwtData;
                    }
                }

                var isPageInward = window.location.search.indexOf("page=inward") !== -1;

                // Case 1: Employee QR
                if (data && (data.type === "employee" || data.employee_id)) {
                    var empId = (data.id || data.employee_id || "").toString();
                    var empName = data.name || data.employee_name || empId || "Employee";
                    
                    var insideRow = empId ? document.querySelector(".emp-row[data-emp-id=\"" + empId + "\"]") : null;

                    if (insideRow) {
                        var exitForm = insideRow.querySelector("form");
                        if (exitForm) {
                            stopQRScannerModal();
                            closeQRScannerModal();
                            showAppConfirm("Employee " + empName + " is already inside. Log EXIT?", function (conf) {
                                if (conf) {
                                    exitForm.onsubmit = null;
                                    exitForm.submit();
                                }
                            }, "Employee Exit");
                            return;
                        }
                    } else if (typeof currentEmployeeModalMode !== "undefined" && currentEmployeeModalMode === "exit") {
                        showQRStatusModal("❌ Employee " + empName + " is NOT inside.", "error");
                        isProcessingScanModal = false;
                        return;
                    }

                    stopQRScannerModal();
                    closeQRScannerModal();
                    var mode = (typeof currentEmployeeModalMode !== "undefined") ? currentEmployeeModalMode : "both";
                    openEmployeeModal(mode === "exit" ? "both" : mode);
                    
                    var vehicle = data.vehicle || data.vehicle_number;
                    if (vehicle) {
                        var vF = document.getElementById("emp_vehicle_number");
                        if (vF) { vF.value = vehicle; fetchEmployeeByVehicle(vehicle); }
                    } else {
                        var iF = document.getElementById("emp_id");
                        var nF = document.getElementById("emp_name");
                        var dF = document.getElementById("emp_department");
                        if (iF) iF.value = empId;
                        if (nF) nF.value = empName;
                        if (dF) dF.value = data.department || "";
                    }
                    return;
                }

                // Case 2: Bill QR
                if (data && (data.DocNo || data.bill_number)) {
                    showQRStatusModal("✅ Bill detected!", "success");
                    stopQRScannerModal();
                    var billNum = data.DocNo || data.bill_number;
                    if (isPageInward) {
                        var bF = document.getElementById("bill_number");
                        if (bF) bF.value = billNum;
                        var rF = document.getElementById("qr_raw_data");
                        if (rF) rF.value = text;
                        closeQRScannerModal();
                    } else {
                        sessionStorage.setItem("pending_qr_data", text);
                        window.location.href = "?page=qr-scanner";
                    }
                    return;
                }

                // Case 3: Raw Text (Min length 4)
                if (text.length >= 4) {
                    if (isPageInward) {
                        var bF = document.getElementById("bill_number");
                        if (bF) bF.value = text;
                        stopQRScannerModal();
                        closeQRScannerModal();
                        showQRStatusModal("✅ Raw data filled.", "success");
                    } else {
                        stopQRScannerModal();
                        closeQRScannerModal();
                        var mode = (typeof currentEmployeeModalMode !== "undefined") ? currentEmployeeModalMode : "both";
                        openEmployeeModal(mode === "exit" ? "both" : mode);
                        var vF = document.getElementById("emp_vehicle_number");
                        if (vF) {
                            vF.value = text.toUpperCase();
                            fetchEmployeeByVehicle(text);
                        }
                    }
                } else {
                    showQRStatusModal("❌ Unknown or too short QR data", "error");
                    isProcessingScanModal = false;
                }
            } catch (err) {
                console.error("Scan processing error:", err);
                showQRStatusModal("❌ Error: " + (err.message || err || "unknown"), "error");
                isProcessingScanModal = false;
            }
        }';

$content = preg_replace('/function onQRCodeScannedModal\(.*?\).*?\{.*?\}\n\n        function processManualQR/s', $newOnScanFunc . "\n\n        function processManualQR", $content);

file_put_contents($file, $content);
echo "Refined successfully.";
?>