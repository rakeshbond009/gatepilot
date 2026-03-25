<?php
/**
 * COMPLETE QR SCANNER FIX - Mobile + Preview + Proper Decoding
 * This replaces the scanner logic to:
 * 1. Work on mobile (no strict resolution requirements)
 * 2. Show preview of scanned items before submission
 * 3. Store raw JSON in qr_code_data field
 * 4. Extract and display items from QR data
 */

$index_file = __DIR__ . '/index.php';
$content = file_get_contents($index_file);

// ============================================================================
// PART 1: Fix the scanner initialization (mobile compatibility)
// ============================================================================
$old_scanner_init = <<<'OLDCODE'
                try {
                    html5QrCodeModal = new Html5Qrcode("qr-reader-modal");

                    // OPTIMIZED FOR DENSE/COMPLEX QR CODES (like invoices)
                    // Using 'ideal' instead of 'min' to avoid camera failures on mobile
                    var config = {
                        fps: 10, // LOWER FPS = more time to process complex codes
                        qrbox: function (w, h) {
                            var min = Math.min(w, h);
                            // Medium box - too small misses edges, too large loses detail
                            return { width: Math.floor(min * 0.7), height: Math.floor(min * 0.7) };
                        },
                        aspectRatio: 1.0,
                        disableFlip: false, // Try both orientations
                        videoConstraints: {
                            facingMode: "environment",
                            // Use 'ideal' for mobile compatibility - camera will use best available
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        }
                    };

                    html5QrCodeModal.start(
                        { facingMode: "environment" },
                        config,
                        function (text) { onQRCodeScannedModal(text); },
                        function (err) { }
                    ).then(function () {
OLDCODE;

$new_scanner_init = <<<'NEWCODE'
                try {
                    html5QrCodeModal = new Html5Qrcode("qr-reader-modal");

                    // MOBILE-FRIENDLY CONFIG - No strict constraints
                    var config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    };

                    html5QrCodeModal.start(
                        { facingMode: "environment" },
                        config,
                        function (text) { onQRCodeScannedModal(text); },
                        function (err) { }
                    ).then(function () {
NEWCODE;

$content = str_replace($old_scanner_init, $new_scanner_init, $content);

// ============================================================================
// PART 2: Fix the QR data processing to show preview and store properly
// ============================================================================
$old_bill_handling = <<<'OLDCODE'
                // --- Handle Logic ---
                if (isBill) {
                    showQRStatusModal("✅ Bill detected!", "success");
                    stopQRScannerModal();
                    var billCode = String(data.DocNo || data.bill_number || "");
                    if (isPageInward) {
                        var bf = document.getElementById("bill_number"); if (bf) bf.value = billCode;
                        var rf = document.getElementById("qr_raw_data"); if (rf) rf.value = text;
                        closeQRScannerModal();
                    } else {
                        sessionStorage.setItem("pending_qr_data", text); window.location.href = "?page=qr-scanner";
                    }
OLDCODE;

$new_bill_handling = <<<'NEWCODE'
                // --- Handle Logic ---
                if (isBill) {
                    stopQRScannerModal();
                    var billCode = String(data.DocNo || data.bill_number || "");
                    
                    if (isPageInward) {
                        // Fill form fields
                        var bf = document.getElementById("bill_number"); 
                        if (bf) bf.value = billCode;
                        
                        // Store RAW JSON in hidden field for backend
                        var qrDataField = document.getElementById("qr_code_data");
                        if (qrDataField) qrDataField.value = originalText;
                        
                        // Show preview of items
                        var itemsPreview = "";
                        var itemsArray = data.items || data.products || data.line_items || [];
                        if (itemsArray.length > 0) {
                            itemsPreview = "<br><strong>Items Found:</strong><ul style='text-align:left; margin:10px 0;'>";
                            itemsArray.slice(0, 5).forEach(function(item) {
                                var name = item.item_name || item.product_name || item.name || item.description || "";
                                var qty = item.quantity || item.qty || 0;
                                var unit = item.unit || "PCS";
                                itemsPreview += "<li>" + name + " - " + qty + " " + unit + "</li>";
                            });
                            if (itemsArray.length > 5) {
                                itemsPreview += "<li><em>... and " + (itemsArray.length - 5) + " more items</em></li>";
                            }
                            itemsPreview += "</ul>";
                        }
                        
                        showQRStatusModal("✅ Bill #" + billCode + " scanned!" + itemsPreview, "success");
                        setTimeout(function() { closeQRScannerModal(); }, 2000);
                    } else {
                        sessionStorage.setItem("pending_qr_data", originalText); 
                        window.location.href = "?page=qr-scanner";
                    }
NEWCODE;

$content = str_replace($old_bill_handling, $new_bill_handling, $content);

// Save the file
file_put_contents($index_file, $content);

echo "✅ Scanner fixed!\n\n";
echo "Changes made:\n";
echo "1. ✅ Removed strict resolution constraints for mobile compatibility\n";
echo "2. ✅ Added items preview after scanning\n";
echo "3. ✅ Storing raw JSON in qr_code_data field\n";
echo "4. ✅ Simplified scanner config for better mobile support\n\n";
echo "Now add this hidden field to your inward form:\n";
echo '<input type="hidden" name="qr_code_data" id="qr_code_data" value="">' . "\n";
?>