# GatePilot Scanner & Camera Implementation Guide

This document provides a technical overview of how QR scanning and photo capture are implemented in the GatePilot system. Use this as a first reference if hardware integration issues arise.

---

## 1. QR Code Scanning System
**Primary Library:** [html5-qrcode](https://github.com/mebjas/html5-qrcode)

### Implementation Logic
The system uses a centralized modal-based scanner defined in `includes/footer.php` and `includes/scripts.php`.

- **Global Function:** `openQRScannerModal(targetId)`
- **Start Scanner:** `startQRScannerModal()`
  - Initializes `Html5Qrcode` on the `#qr-reader-modal` element.
  - Configures FPS (10) and QR Box size (250px).
  - Attempts to use the "environment" (back) camera by default.
- **Processing Data:** `onQRCodeScannedModal(text)`
  - **GST E-Invoices:** Automatically detects JWT payloads (usually found in government e-invoices), decodes them, and extracts the `DocNo` (Bill Number) and `ItemList`.
  - **Employee IDs:** Matches patterns for JSON data containing `employee_id` or `vehicle`.
  - **Auto-Fill:** If a match is found, it stops the scanner, closes the modal, and populates the relevant form fields on the active page.

---

## 2. Handling Complex & High-Density QRs
The system is specifically tuned for dense GST E-Invoices and government-issued codes.

### Optimization Strategy ("Golden Config")
- **4K/UHD Constraints:** Requests 3840x2160 resolution to resolve tiny pixels in dense codes.
- **Full-Frame Scan:** The `qrbox` (scan square) is removed to allow the decoder to use the full camera sensor resolution.
- **Native Acceleration:** `useBarCodeDetectorIfSupported: true` maps the task to the device's hardware-accelerated Barcode API where available. 

### Multi-Step Decoding Logic
Upon a successful scan, the string is processed through five layers:
1. **JSON Direct:** Standard object parsing.
2. **Base64 -> JSON:** Extracts hidden data from encoded strings.
3. **URL Decode -> JSON:** Handles symbols and complex characters.
4. **JWT / E-Invoice:** Breaks down `Header.Payload.Signature` formats, fixes Base64Url padding, and extracts the `doc_details` or `data` block.
5. **Pipe Format (`|`):** Support for legacy pipe-separated GST invoice QRs.

---

## 3. Special Features
- **Manual Zoom:** Controlled via `applyManualZoom(val)`. Uses the `applyConstraints` API to handle hardware zoom on supported mobile devices.
- **Camera Switching:** `switchCamera(deviceId)` allows users to toggle between Front/Back or Multiple USB cameras.
- **Manual Input Fallback:** A textarea in the scanner modal allows "pasting" QR data if the camera fails.

---

## 4. Photo & Webcam Capture
Used for capturing Driver Photos, Vehicle Photos, and Bill Photos.

### Implementation Workflow
The system intelligently switches between Native Mobile Capture and Web-based Capture.

- **Function:** `openWebcamCapture(inputId, previewCallback)`
- **Mobile Logic:** 
  - Detected via User-Agent Regex.
  - Triggers the native phone camera using: `<input type="file" accept="image/*" capture="camera">`.
- **PC/Desktop Logic:**
  1. Opens `#webcamModal`.
  2. Requests `navigator.mediaDevices.getUserMedia({ video: true })`.
  3. Stream is displayed in a `<video>` element.
  4. **Snapshot:** `takeWebcamSnapshot()` draws the current video frame to a hidden `<canvas>`, converts it to a `Blob`, then to a `File` object.
  5. **Injection:** The generated file is injected into the hidden file input using the `DataTransfer` API, making it ready for standard PHP `$_FILES` processing.

---

## 5. Storage & Mapping
- **Temporary Preview:** `showAppPreview(file, targetId)` uses `FileReader.readAsDataURL` to show the photo to the user before they click "Save".
- **Server Side:** Photos are uploaded to the `uploads/` directory with unique timestamps to prevent overwriting.

---

## 6. Troubleshooting Guide

| Issue | Possible Cause | Solution |
| :--- | :--- | :--- |
| **"Loading..." Hangs** | Camera Permission | Ensure the site is running on **HTTPS** or **localhost**. Browsers block cameras on insecure HTTP sites. |
| **Camera Won't Start** | Resource Locked | Another app (Zoom, Teams) or another browser tab is using the camera. Close other apps and refresh. |
| **Black Screen** | Permission/Driver | Check Browser Settings -> Site Settings -> Camera. Ensure "Allow" is selected. |
| **QR Not Decoding** | Low Light/Focus | Use the **Manual Zoom** slider or the **Torch** (if supported). Ensure the QR code fits within the scanner box. |
| **Mobile Capture Fails** | Storage Full | Some mobile browsers fail to trigger the camera if device storage is critically low. |

---

## 7. Key Files for Reference
- `includes/footer.php`: Contains the HTML structure for Scanner and Webcam modals.
- `includes/scripts.php`: Contains the JavaScript logic for `Html5Qrcode` and `getUserMedia`.
- `pages/core_ops.php`: Example implementation for Truck Inward photos and QR scanning.
- `pages/patrol_and_registers.php`: Example implementation for Guard Patrol QR scanning.

---
*Documentation generated for GatePilot v26.04.16*
