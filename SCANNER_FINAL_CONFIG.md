# QR Scanner Final Configuration (Android WebView Compatible)
**Status**: WORKING
**Last Updated**: 2026-01-16
**Tested On**: Android Mobile App (WebView)

## 📌 Context
This configuration was specifically tuned to read **Dense E-Invoice QR Codes** on Android devices running within a WebView. Previous configurations using `qrbox` (cropping) or Native Barcode Detectors failed due to low resolution or implementation bugs in the WebView.

## ✅ The "Golden" Configuration
This setup works because:
1.  **Full Frame Scanning**: We REMOVED `qrbox`. This forces the scanner to analyze the *entire* high-resolution video feed, not just a cropped 250px square. This is critical for dense QRs.
2.  **High Resolution**: We enforce `min: 1280` to prevent the browser from serving a low-quality stream.
3.  **FPS 30**: Higher frame rate allows the autofocus to settle faster.
4.  **Native Disabled**: `useBarCodeDetectorIfSupported: false`. The native Android API inside some WebViews is buggy. The JavaScript (ZXing) engine proved more robust here.

### 1. `initializeScanner()` Logic
Copy and replace the `initializeScanner` function in `index.php` with this if it breaks:

```javascript
function initializeScanner() {
    setTimeout(function () {
        try {
            html5QrCodeModal = new Html5Qrcode("qr-reader-modal");

            var config = {
                fps: 30, // High FPS for better Autofocus
                // qrbox: REMOVED -> Scan Full Frame for maximum detail (Critical for Dense QRs)
                showTorchButtonIfSupported: true,
                useBarCodeDetectorIfSupported: false, // Force JS Engine (More reliable on WebView)
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
                setTimeout(function() { applyManualZoom(1.0); }, 500);

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
```

## 🔍 The "Hybrid" Zoom Logic
Many Android WebViews claim to not support Zoom, even if the camera can. We implemented a fallback that uses **CSS Scaling** if the Hardware Zoom fails.

### 2. Manual Zoom Functions
These functions must be present in the global scope:

```javascript
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
            applyCSSZoom(val); // Fallback if API not present
        }
    } catch (e) { 
        console.error(e); 
        applyCSSZoom(val); // Fallback on crash
    }
}

function applyCSSZoom(val) {
    var v = document.querySelector("#qr-reader-modal video");
    if (v) {
        v.style.transform = "scale(" + val + ")";
        v.style.transformOrigin = "center center";
    }
}
```

### 3. HTML/CSS Requirements
The container must have `overflow: hidden` so the CSS zoomed video doesn't bleed out.

```html
<!-- Scanner Area -->
<div id="qr-reader-modal" style="width: 100%; min-height: 300px; overflow: hidden; position: relative;"></div>

<!-- Zoom Control -->
<div id="zoomContainerModal" style="margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 8px; position: relative; z-index: 10;">
    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #475569;">🔍 Manual Zoom</label>
    <input type="range" id="zoomInputModal" min="1" max="5" step="0.5" value="1" oninput="applyManualZoom(this.value)" style="width: 100%;">
</div>
```

## 🚀 Summary
If the scanner stops working:
1.  **Check `videoConstraints`**: Ensure `min: 1280` is there.
2.  **Check `qrbox`**: Ensure it is **REMOVED** or commented out.
3.  **Check `useBarCodeDetectorIfSupported`**: Ensure it is **FALSE**.

---

## 📷 Complete Camera Lifecycle Code
If you lose the start/stop logic, here is the robust version:

### 4. Start & Stop Functions

```javascript
var html5QrCodeModal = null;
var isScanningModal = false;

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
}

function openQRScannerModal() {
    var modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    modal.show();
    // Auto-start on mobile if desired:
    if (/Mobi|Android/i.test(navigator.userAgent)) {
        setTimeout(startQRScannerModal, 500);
    }
}
```

---

## 📸 Native Camera & Gallery Implementation
To open the Camera or Gallery directly on button click (working on Android WebView & Desktop), use this **Label-Triggered Pattern**.

### 1. HTML: Hidden Inputs + Trigger Labels
Place this inside your form. We use `id` linking to make the Labels trigger the Inputs.

```html
<!-- 1. DRIVER PHOTO (Example) -->
<div class="form-group mb-3">
    <label class="fw-bold mb-2">Driver Photo</label>
    
    <!-- Hidden Actual Inputs -->
    <!-- Camera: capture="environment" forces camera on mobile -->
    <input type="file" id="driver_cam" name="driver_photo" accept="image/*" capture="environment" style="display:none" onchange="previewImage(this, 'driver_preview')">
    <!-- Gallery: No capture attribute opens file picker -->
    <input type="file" id="driver_gal" name="driver_photo" accept="image/*" style="display:none" onchange="previewImage(this, 'driver_preview')">

    <!-- Visible Buttons (Labels acting as Buttons) -->
    <div class="d-flex gap-2">
        <!-- Open Camera -->
        <label for="driver_cam" class="btn btn-primary flex-grow-1">
            📷 Camera
        </label>
        
        <!-- Open Gallery -->
        <label for="driver_gal" class="btn btn-secondary flex-grow-1">
            🖼️ Gallery
        </label>
    </div>

    <!-- Preview Container -->
    <div class="mt-2 text-center" style="display:none;" id="driver_preview_container">
        <img id="driver_preview" src="" style="max-height: 150px; border-radius: 8px; border: 2px solid #ddd;">
        <button type="button" class="btn btn-sm btn-danger d-block mx-auto mt-1" onclick="clearImage('driver')">Remove</button>
    </div>
</div>
```

### 2. JavaScript: Preview & Logic
Add this script to handle previews and ensuring the Camera button works on all devices.

```javascript
/* Show Image Preview */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Show the preview image
            var img = document.getElementById(previewId);
            img.src = e.target.result;
            
            // Show the container (parent of the img)
            img.parentElement.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/* Clear Image */
function clearImage(prefix) {
    // Clear inputs
    var cam = document.getElementById(prefix + '_cam');
    if(cam) cam.value = "";
    
    var gal = document.getElementById(prefix + '_gal');
    if(gal) gal.value = "";
    
    // Hide preview
    var preview = document.getElementById(prefix + '_preview');
    if(preview) {
        preview.src = "";
        preview.parentElement.style.display = 'none';
    }
}

/* 
   ANDROID WEBVIEW FIX:
   Some WebViews don't trigger file inputs if triggered by JS .click().
   Using <label for="..."> is the safest Native HTML way.
   No extra JS needed for the opening part!
*/
```
