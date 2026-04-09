<script>
    // --- Global Manual Items Management ---
    let manualItems = [];

    function addItemManually() {
        const code = document.getElementById('new_item_code').value.trim();
        const name = document.getElementById('new_item_name').value.trim();
        const qty = document.getElementById('new_item_qty').value.trim();
        const unit = document.getElementById('new_item_unit').value;

        if (!name || !qty) {
            showCustomAlert('Item Name and Quantity are required!', 'Missing Info');
            return;
        }

        const item = {
            item_code: code || 'N/A',
            item_name: name,
            quantity: parseFloat(qty),
            unit: unit
        };

        manualItems.push(item);
        renderItems();

        // Clear inputs
        document.getElementById('new_item_code').value = '';
        document.getElementById('new_item_name').value = '';
        document.getElementById('new_item_qty').value = '';
        document.getElementById('new_item_name').focus();
    }

    // --- Custom Alert Modal ---
    function showCustomAlert(message, title = 'Attention') {
        const modalId = 'customAlertModal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.6); display: flex; align-items: center;
                justify-content: center; z-index: 99999; backdrop-filter: blur(4px);
                transition: opacity 0.3s ease; opacity: 0;
            `;
            modal.innerHTML = `
                <div style="background: white; width: 90%; max-width: 400px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.3); transform: scale(0.9); transition: transform 0.3s ease;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; color: white; display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 24px;">⚠️</span>
                        <h3 id="alertTitle" style="margin: 0; font-size: 18px; font-weight: 700;"></h3>
                    </div>
                    <div style="padding: 25px; text-align: center;">
                        <p id="alertMessage" style="margin: 0; color: #4b5563; line-height: 1.6; font-size: 15px; font-weight: 500;"></p>
                    </div>
                    <div style="padding: 15px 20px 20px; display: flex; justify-content: center;">
                        <button onclick="closeCustomAlert()" style="background: #667eea; color: white; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; width: 100%; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">OK, GOT IT</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        document.getElementById('alertTitle').textContent = title;
        document.getElementById('alertMessage').textContent = message;
        
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('div').style.transform = 'scale(1)';
        }, 10);
    }

    function closeCustomAlert() {
        const modal = document.getElementById('customAlertModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('div').style.transform = 'scale(0.9)';
            setTimeout(() => modal.style.display = 'none', 300);
        }
    }

    function validateManualItemsCommit() {
        const name = document.getElementById('new_item_name')?.value.trim();
        const qty = document.getElementById('new_item_qty')?.value.trim();
        
        if (name || qty) {
            showCustomAlert('You have entered item details (' + (name || 'Unnamed') + ') but haven\'t clicked the "Add" (+) button. Please add the item to the list or clear the fields before submitting.', 'Action Required');
            const nameField = document.getElementById('new_item_name');
            if (nameField) nameField.focus();
            document.getElementById('manual_items_section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }

    function deleteItem(index) {
        manualItems.splice(index, 1);
        renderItems();
    }

    function renderItems() {
        const tbody = document.getElementById('items_tbody');
        const container = document.getElementById('items_list_container');
        const badge = document.getElementById('items_count_badge');
        const hiddenInput = document.getElementById('items_hidden_input');

        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (manualItems.length > 0) {
            if (container) container.style.display = 'block';
            if (badge) {
                badge.style.display = 'inline-block';
                badge.textContent = manualItems.length + ' Items';
            }
            
            manualItems.forEach((item, index) => {
                const row = document.createElement('tr');
                row.style.background = 'white';
                row.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-family: monospace; font-size: 13px; color: #64748b;">${item.item_code}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #1e293b;">${item.item_name}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #8b5cf6;">${item.quantity}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; color: #64748b;">${item.unit}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                        <button type="button" onclick="deleteItem(${index})" style="background: #fee2e2; color: #ef4444; border: none; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; font-size: 14px;">&times;</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            if (container) container.style.display = 'none';
            if (badge) badge.style.display = 'none';
        }

        // Sync with hidden input
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(manualItems);
        }
    }

    // Global function for QR scanner to populate items
    window.populateManualItemsList = function(itemsArray) {
        console.log('Populating manual items list from QR data:', itemsArray);
        if (!itemsArray || !Array.isArray(itemsArray)) return;
        
        // Standardize format from various QR structures
        const standardizedItems = itemsArray.map(item => {
            return {
                item_code: item.item_code || item.product_code || item.sku || 'N/A',
                item_name: item.item_name || item.product_name || item.name || item.description || 'Unknown Item',
                quantity: parseFloat(item.quantity || item.qty || 0),
                unit: item.unit || item.uom || 'PCS'
            };
        });

        manualItems = standardizedItems;
        renderItems();
        
        // Highlight the section
        const section = document.getElementById('manual_items_section');
        if (section) {
            section.style.background = '#f5f3ff';
            setTimeout(() => section.style.background = 'transparent', 2000);
        }
    };
</script>
<?php if ($page == 'inward'):
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
        <?php if (isset($session_success)): ?>
            <div class="alert alert-success">
                <?php echo $session_success; ?>
            </div>
            <?php
        endif; ?>
        <?php if (isset($session_error)): ?>
            <div class="alert alert-error">
                <?php echo $session_error; ?>
            </div>
            <?php
        endif; ?>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <a href="?page=dashboard" class="btn btn-secondary"
                style="flex: 1; display: block; position: relative; z-index: 10; text-align: center; text-decoration: none; padding: 10px; border-radius: 6px; background: #6b7280; color: white;">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Form Header with Gradient -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🚛</div>
                <div>
                    <h1
                        style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Truck Inward Entry</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Record new vehicle
                        entry into the facility</p>
                </div>
            </div>
        </div>

        <!-- Auto-fill Message -->
        <div id="autofill-message"
            style="display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-left: 4px solid;">
        </div>

        <form method="POST" action="?page=inward" enctype="multipart/form-data" id="inwardForm">

            <!-- Section 1: Vehicle Information -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #3b82f6; background: linear-gradient(to right, #eff6ff 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #3b82f6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                        1</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚗 Vehicle
                            Information
                        </h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Enter vehicle details -
                            auto-fill
                            available</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>🚛</span>
                        <span>Vehicle Number *</span>
                    </label>
                    <input type="text" name="vehicle_number" id="vehicle_number" placeholder="MH12AB1234" required
                        style="text-transform: uppercase; font-size: 16px; font-weight: 500; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                        onblur="fetchVehicleDetails()"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; fetchVehicleDetails();">
                    <small
                        style="color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 6px;">
                        <span>💡</span>
                        <span>Enter vehicle number and press Tab - details will auto-fill from master</span>
                    </small>
                </div>
            </div>

            <!-- Section 2: Driver Details -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">
                        2</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">👨‍✈️ Driver
                            Details
                        </h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Driver information and
                            contact
                            details</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>👤</span>
                            <span>Driver Name *</span>
                        </label>
                        <input type="text" name="driver_name" id="driver_name" placeholder="Enter driver name" required
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📱</span>
                            <span>Driver Mobile *</span>
                        </label>
                        <input type="tel" name="driver_mobile" id="driver_mobile" placeholder="9876543210" maxlength="10"
                            inputmode="numeric" pattern="[0-9]{10}" required
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>
            </div>

            <!-- Section 3: Transporter & Purpose -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                        3</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚚 Transporter &
                            Purpose</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Company details and visit
                            purpose
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🏢</span>
                            <span>Transporter Name</span>
                        </label>
                        <input type="hidden" name="transporter_id" id="transporter_id_hidden">
                        <input type="text" name="transporter_name" id="transporter_name"
                            placeholder="Company/Transporter name" list="transporter_list"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <datalist id="transporter_list">
                            <?php
                            $transporters = mysqli_query($conn, "SELECT id, transporter_name FROM transporter_master WHERE is_active=1 ORDER BY transporter_name");
                            while ($trans = mysqli_fetch_assoc($transporters)) {
                                echo "<option data-id='{$trans['id']}' value='{$trans['transporter_name']}'>";
                            }
                            ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🎯</span>
                            <span>Purpose</span>
                        </label>
                        <input type="hidden" name="purpose_id" id="purpose_id_hidden">
                        <input type="text" name="purpose_name" id="purpose_name" placeholder="Select or type purpose"
                            list="purpose_list"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <datalist id="purpose_list">
                            <?php
                            $purposes = mysqli_query($conn, "SELECT id, purpose_name FROM purpose_master WHERE is_active=1 ORDER BY purpose_name");
                            while ($purp = mysqli_fetch_assoc($purposes)) {
                                echo "<option data-id='{$purp['id']}' value='{$purp['purpose_name']}'>";
                            }
                            ?>
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- Section 4: Bill & Location Details -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #8b5cf6; background: linear-gradient(to right, #faf5ff 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #8b5cf6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);">
                        4</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📄 Bill & Location
                            Details</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Documentation and location
                            information</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span>
                        <span>Bill/Challan Number</span>
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="bill_number" id="bill_number" placeholder="Bill number"
                            style="flex: 1; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <button type="button" onclick="openQRScannerModal()" class="btn"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; font-size: 14px; white-space: nowrap; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3); transition: transform 0.2s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='translateY(0)';">
                            📷 Scan QR
                        </button>
                    </div>
                    <input type="hidden" name="qr_code_data" id="qr_code_data">
                    <input type="hidden" name="qr_raw_data" id="qr_raw_data">
                    <small
                        style="color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 6px;">
                        <span>💡</span>
                        <span>Scan QR code from bill/challan to auto-fill details</span>
                    </small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📍</span>
                            <span>From Location</span>
                        </label>
                        <input type="text" name="from_location" id="from_location" placeholder="Source location"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🎯</span>
                            <span>To Location</span>
                        </label>
                        <input type="text" name="to_location" id="to_location" placeholder="Destination location"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                            onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>

                <!-- Manual Items Entry Section -->
                <div id="manual_items_section"
                    style="margin-top: 25px; padding-top: 20px; border-top: 2px dashed #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #1f2937; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <span>📦</span> Material Items Information
                        </h4>
                        <span id="items_count_badge" class="badge" style="background: #8b5cf6; color: white; display: none;">0 Items</span>
                    </div>

                    <div id="items_list_container" style="max-height: 250px; overflow-y: auto; margin-bottom: 15px; display: none;">
                        <table style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
                            <thead>
                                <tr style="text-align: left; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <th style="padding: 0 10px;">Code</th>
                                    <th style="padding: 0 10px;">Item Description</th>
                                    <th style="padding: 0 10px;">Qty</th>
                                    <th style="padding: 0 10px;">Unit</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="items_tbody"><!-- Items will be appended here --></tbody>
                        </table>
                    </div>

                    <div style="background: #f8fafc; padding: 18px; border-radius: 12px; border: 2px solid #e2e8f0; display: grid; grid-template-columns: 1fr 2fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">CODE</label>
                            <input type="text" id="new_item_code" placeholder="Code" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">ITEM NAME</label>
                            <input type="text" id="new_item_name" placeholder="Name of material" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">QUANTITY</label>
                            <input type="number" id="new_item_qty" step="any" placeholder="0" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">UNIT</label>
                            <select id="new_item_unit" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: white;">
                                <option value="NOS">NOS</option>
                                <option value="KGS">KGS</option>
                                <option value="PCS">PCS</option>
                                <option value="MTS">MTS</option>
                                <option value="LTR">LTR</option>
                                <option value="BOX">BOX</option>
                                <option value="BAG">BAG</option>
                                <option value="UNIT">UNIT</option>
                                <option value="BUNDLE">BUNDLE</option>
                                <option value="PKT">PKT</option>
                                <option value="SET">SET</option>
                            </select>
                        </div>
                        <button type="button" onclick="addItemManually()" id="addItemBtn"
                            style="background: #8b5cf6; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; height: 40px; width: 40px;"
                            onmouseover="this.style.background='#7c3aed'" onmouseout="this.style.background='#8b5cf6'">
                            <span style="font-size: 20px; font-weight: bold;">+</span>
                        </button>
                    </div>
                    <input type="hidden" name="items" id="items_hidden_input">
                </div>
            </div>

            <!-- Section 5: Additional Information -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #ef4444; background: linear-gradient(to right, #fef2f2 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #ef4444; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);">
                        5</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📝 Additional
                            Information</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Security comments and
                            observations</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>🔒</span>
                        <span>Security Comments</span>
                    </label>
                    <textarea name="security_comments" placeholder="Any observations or notes..."
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; min-height: 100px; resize: vertical; font-family: inherit;"
                        onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 3px rgba(239, 68, 68, 0.1)';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"></textarea>
                </div>
            </div>

            <!-- Section 6: Photo Attachments -->
            <div class="card"
                style="margin-bottom: 25px; border-left: 4px solid #ec4899; background: linear-gradient(to right, #fdf2f8 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #ec4899; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(236, 72, 153, 0.3);">
                        6</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📸 Photo
                            Attachments
                        </h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Upload vehicle and bill
                            photos
                            (Optional)</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; margin-bottom: 12px; display: block;">📸 Attach
                        Photos (Optional)</label>
                    <div id="photo_upload_container"
                        style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <!-- Photos Hidden Inputs (Robust Hiding for WebView) -->
                        <div
                            style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                            <input type="file" name="vehicle_photo" id="vehicle_photo_file" accept="image/*">
                            <input type="file" id="vehicle_photo_camera" accept="image/*" capture="environment">
                            <input type="file" name="bill_photo" id="bill_photo_file" accept="image/*">
                            <input type="file" id="bill_photo_camera" accept="image/*" capture="environment">
                        </div>

                        <!-- Vehicle Photo Buttons -->
                        <div style="background: #eff6ff; padding: 15px; border-radius: 12px; border: 2px dashed #3b82f6;">
                            <small
                                style="display: block; color: #1e40af; margin-bottom: 10px; font-weight: 600; font-size: 13px;">🚗
                                Vehicle Photo:</small>
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <label for="vehicle_photo_camera" id="vehicle_camera_label" class="btn"
                                    style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 10px 14px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3); transition: transform 0.2s; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;"
                                    onmouseover="this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.transform='translateY(0)';">
                                    📷 Camera
                                </label>
                                <label for="vehicle_photo_file" class="btn"
                                    style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 10px 14px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3); transition: transform 0.2s; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;"
                                    onmouseover="this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.transform='translateY(0)';">
                                    📁 Gallery
                                </label>
                            </div>
                            <span id="vehicle_photo_name"
                                style="font-size: 11px; color: #10b981; display: block; font-weight: 600;"></span>
                        </div>

                        <!-- Bill Photo Buttons -->
                        <div style="background: #f0fdf4; padding: 15px; border-radius: 12px; border: 2px dashed #10b981;">
                            <small
                                style="display: block; color: #065f46; margin-bottom: 10px; font-weight: 600; font-size: 13px;">📄
                                Bill/Challan Photo:</small>
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <label for="bill_photo_camera" id="bill_camera_label" class="btn"
                                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 14px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3); transition: transform 0.2s; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;"
                                    onmouseover="this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.transform='translateY(0)';">
                                    📷 Camera
                                </label>
                                <label for="bill_photo_file" class="btn"
                                    style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 10px 14px; font-size: 13px; flex: 1; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3); transition: transform 0.2s; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;"
                                    onmouseover="this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.transform='translateY(0)';">
                                    📁 Gallery
                                </label>
                            </div>
                            <span id="bill_photo_name"
                                style="font-size: 11px; color: #10b981; display: block; font-weight: 600;"></span>
                        </div>
                    </div>

                    <!-- Preview Images -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <img id="vehicle_photo_preview"
                            style="display: none; width: 100%; max-height: 180px; object-fit: cover; border-radius: 12px; border: 3px solid #3b82f6; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2);">
                        <img id="bill_photo_preview"
                            style="display: none; width: 100%; max-height: 180px; object-fit: cover; border-radius: 12px; border: 3px solid #10b981; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2);">
                    </div>
                </div>
            </div>

            <!-- Hidden input to ensure form is recognized -->
            <input type="hidden" name="submit_inward" value="1">

            <!-- Submit Button Section -->
            <div
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 25px; margin-top: 30px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
                <button type="submit" id="submitInwardBtn" class="btn btn-primary btn-full"
                    style="background: white; color: #667eea; padding: 16px 32px; font-size: 18px; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s; border: none;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                    <span id="submitBtnText">🚛 SUBMIT INWARD ENTRY</span>
                    <span id="submitBtnLoader" style="display: none;">
                        <span
                            style="display: inline-block; width: 20px; height: 20px; border: 3px solid #667eea; border-top: 3px solid transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 10px; vertical-align: middle;"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <script>
        let fetchTimeout;

        // Add CSS animation for spinner
        const style = document.createElement('style');
        style.textContent = `
                            @keyframes spin {
                                0 % { transform: rotate(0deg); }
                                100 % { transform: rotate(360deg); }
                            }
                        `;
        document.head.appendChild(style);

        // Show loader on form submit
        document.getElementById('inwardForm')?.addEventListener('submit', function (e) {
            // Validate manual items first
            if (typeof validateManualItemsCommit === 'function' && !validateManualItemsCommit()) {
                e.preventDefault();
                return;
            }
            const vehicleNumber = document.getElementById('vehicle_number').value.trim().toUpperCase();

            // Check if vehicle is already inside before submitting
            if (vehicleNumber && vehicleNumber.length >= 4) {
                e.preventDefault(); // Prevent form submission temporarily

                checkVehicleInside(vehicleNumber).then(status => {
                    if (status.isInside) {
                        // Vehicle is inside - show error and prevent submission
                        const messageDiv = document.getElementById('autofill-message');
                        messageDiv.innerHTML = '⚠️ <strong>ERROR:</strong> Cannot create inward entry. Vehicle <strong>' + vehicleNumber + '</strong> is already inside! Entry #: <strong>' + status.entry_number + '</strong>. Please complete the outward entry first.';
                        messageDiv.style.display = 'block';
                        messageDiv.style.background = '#fee2e2';
                        messageDiv.style.color = '#991b1b';
                        messageDiv.style.borderLeftColor = '#ef4444';
                        messageDiv.style.fontWeight = '600';

                        // Scroll to message
                        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                        // Re-enable submit button
                        const submitBtn = document.getElementById('submitInwardBtn');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.cursor = 'pointer';
                        }
                        return false;
                    } else {
                        // Vehicle is not inside - proceed with submission
                        const submitBtn = document.getElementById('submitInwardBtn');
                        const btnText = document.getElementById('submitBtnText');
                        const btnLoader = document.getElementById('submitBtnLoader');

                        // Disable button and show loader
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.7';
                        submitBtn.style.cursor = 'not-allowed';
                        btnText.style.display = 'none';
                        btnLoader.style.display = 'inline-block';

                        // Submit the form
                        e.target.submit();
                    }
                });
            } else {
                // No vehicle number - allow normal submission
                const submitBtn = document.getElementById('submitInwardBtn');
                const btnText = document.getElementById('submitBtnText');
                const btnLoader = document.getElementById('submitBtnLoader');

                // Disable button and show loader
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';
            }
        });

        // Check if vehicle is already inside
        function checkVehicleInside(vehicleNumber) {
            if (!vehicleNumber || vehicleNumber.length < 4) {
                return Promise.resolve({ isInside: false });
            }

            return fetch('check_vehicle_inside.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                .then(response => response.json())
                .catch(error => {
                    console.error('Error checking vehicle status:', error);
                    return { isInside: false };
                });
        }

        function fetchVehicleDetails() {
            const vehicleNumber = document.getElementById('vehicle_number').value.trim().toUpperCase();

            if (!vehicleNumber || vehicleNumber.length < 4) {
                // Clear message if vehicle number is too short
                const messageDiv = document.getElementById('autofill-message');
                messageDiv.style.display = 'none';
                return;
            }

            // Clear any existing timeout
            clearTimeout(fetchTimeout);

            // Show loading state
            const messageDiv = document.getElementById('autofill-message');
            messageDiv.innerHTML = '🔍 Checking vehicle status...';
            messageDiv.style.display = 'block';
            messageDiv.style.background = '#eff6ff';
            messageDiv.style.color = '#1e40af';
            messageDiv.style.borderLeftColor = '#3b82f6';

            // First check if vehicle is already inside
            checkVehicleInside(vehicleNumber).then(status => {
                if (status.isInside) {
                    // Vehicle is already inside - show warning and prevent submission
                    messageDiv.innerHTML = '⚠️ <strong>WARNING:</strong> Vehicle <strong>' + vehicleNumber + '</strong> is already inside! Entry #: <strong>' + status.entry_number + '</strong> (In: ' + status.inward_datetime + ', Driver: ' + status.driver_name + '). Please complete the outward entry first.';
                    messageDiv.style.background = '#fee2e2';
                    messageDiv.style.color = '#991b1b';
                    messageDiv.style.borderLeftColor = '#ef4444';
                    messageDiv.style.fontWeight = '600';

                    // Disable submit button
                    const submitBtn = document.getElementById('submitInwardBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.5';
                        submitBtn.style.cursor = 'not-allowed';
                    }

                    // Highlight vehicle number field
                    const vehicleInput = document.getElementById('vehicle_number');
                    if (vehicleInput) {
                        vehicleInput.style.borderColor = '#ef4444';
                        vehicleInput.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    }
                    return; // Don't proceed with fetching vehicle details
                } else {
                    // Vehicle is not inside - proceed with normal flow
                    // Enable submit button
                    const submitBtn = document.getElementById('submitInwardBtn');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }

                    // Reset vehicle number field styling
                    const vehicleInput = document.getElementById('vehicle_number');
                    if (vehicleInput) {
                        vehicleInput.style.borderColor = '#e5e7eb';
                        vehicleInput.style.boxShadow = 'none';
                    }

                    // Continue with fetching vehicle details
                    messageDiv.innerHTML = '🔍 Fetching vehicle and driver details from master...';
                    messageDiv.style.background = '#eff6ff';
                    messageDiv.style.color = '#1e40af';
                    messageDiv.style.borderLeftColor = '#3b82f6';

                    // Debounce the API call
                    fetchTimeout = setTimeout(() => {
                        fetch('fetch_vehicle_driver.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.text();
                            })
                            .then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    console.error('Invalid JSON:', text);
                                    throw new Error('Invalid server response');
                                }
                            })
                            .then(data => {
                                if (data.success && data.found) {
                                    // Auto-fill vehicle and driver details
                                    // Handle multiple drivers
                                    let showMultipleDrivers = false;

                                    if (data.drivers && data.drivers.length > 0) {
                                        if (data.drivers.length === 1) {
                                            // Only one driver - auto-fill directly
                                            const driver = data.drivers[0];
                                            document.getElementById('driver_name').value = driver.driver_name || '';
                                            document.getElementById('driver_mobile').value = driver.driver_mobile || '';

                                            if (driver.transporter_name) {
                                                document.getElementById('transporter_name').value = driver.transporter_name || '';
                                                document.getElementById('transporter_id_hidden').value = driver.transporter_id || '';
                                            }
                                        } else {
                                            // Multiple drivers - show selection UI
                                            showMultipleDrivers = true;
                                        }
                                    } else if (data.driver) {
                                        // Fallback for backward compatibility
                                        document.getElementById('driver_name').value = data.driver.driver_name || '';
                                        document.getElementById('driver_mobile').value = data.driver.driver_mobile || '';
                                    }

                                    if (data.transporter) {
                                        document.getElementById('transporter_name').value = data.transporter.transporter_name || '';
                                        document.getElementById('transporter_id_hidden').value = data.transporter.transporter_id || '';
                                    }

                                    // Show driver selection UI if multiple drivers, otherwise show success message
                                    if (showMultipleDrivers) {
                                        showDriverSelection(data.drivers);
                                    } else {
                                        // Show success message
                                        let message = '✅ ' + data.message;
                                        if (data.vehicle && data.vehicle.maker) {
                                            message += ' (' + data.vehicle.maker + ' ' + (data.vehicle.model || '') + ')';
                                        }
                                        messageDiv.innerHTML = message;
                                        messageDiv.style.background = '#f0fdf4';
                                        messageDiv.style.color = '#065f46';
                                        messageDiv.style.borderLeftColor = '#10b981';

                                        // Hide message after 5 seconds
                                        setTimeout(() => {
                                            messageDiv.style.display = 'none';
                                        }, 5000);
                                    }

                                } else {
                                    // No previous records found
                                    messageDiv.innerHTML = 'ℹ️ New vehicle - Please enter all details';
                                    messageDiv.style.background = '#fffbeb';
                                    messageDiv.style.color = '#92400e';
                                    messageDiv.style.borderLeftColor = '#f59e0b';

                                    // Hide message after 3 seconds
                                    setTimeout(() => {
                                        messageDiv.style.display = 'none';
                                    }, 3000);
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching vehicle details:', error);
                                messageDiv.innerHTML = '⚠️ Error loading data. Please enter details manually.';
                                messageDiv.style.background = '#fef2f2';
                                messageDiv.style.color = '#991b1b';
                                messageDiv.style.borderLeftColor = '#ef4444';
                                setTimeout(() => {
                                    messageDiv.style.display = 'none';
                                }, 3000);
                            });
                    }, 500); // Wait 500ms after user stops typing
                }
            });
        }

        // Show driver selection dialog when multiple drivers are available
        function showDriverSelection(drivers) {
            const messageDiv = document.getElementById('autofill-message');

            let html = '<div style="padding: 10px;">';
            html += '<strong style="font-size: 14px; display: block; margin-bottom: 10px;">👥 Multiple Drivers Available - Select One:</strong>';
            html += '<div style="display: grid; gap: 8px;">';

            drivers.forEach((driver, index) => {
                const isPrimary = driver.is_primary == 1;
                const borderColor = isPrimary ? '#3b82f6' : '#d1d5db';
                const bgColor = isPrimary ? '#dbeafe' : 'white';

                html += '<button type="button" onclick="selectDriver(' + index + ')" ';
                html += 'style="padding: 12px; border: 2px solid ' + borderColor + '; ';
                html += 'border-radius: 8px; background: ' + bgColor + '; ';
                html += 'cursor: pointer; text-align: left; transition: all 0.2s;" ';
                html += 'onmouseover="this.style.borderColor=\'#3b82f6\'; this.style.background=\'#dbeafe\';" ';
                html += 'onmouseout="this.style.borderColor=\'' + borderColor + '\'; this.style.background=\'' + bgColor + '\';">';
                html += '<div style="font-weight: 600; font-size: 14px;">' + driver.driver_name;
                if (isPrimary) {
                    html += ' <span style="background: #3b82f6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px;">PRIMARY</span>';
                }
                html += '</div>';
                html += '<div style="color: #666; font-size: 12px; margin-top: 4px;">📞 ' + driver.driver_mobile + '</div>';
                if (driver.transporter_name) {
                    html += '<div style="color: #666; font-size: 12px;">🚚 ' + driver.transporter_name + '</div>';
                }
                html += '</button>';
            });

            html += '</div>';
            html += '<button type="button" onclick="closeDriverSelection()" style="margin-top: 10px; padding: 8px 15px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">Cancel</button>';
            html += '</div>';

            messageDiv.innerHTML = html;
            messageDiv.style.display = 'block';
            messageDiv.style.background = '#eff6ff';
            messageDiv.style.color = '#1e40af';
            messageDiv.style.borderLeftColor = '#3b82f6';

            // Store drivers data globally for selection
            window.availableDrivers = drivers;
        }

        function selectDriver(index) {
            const driver = window.availableDrivers[index];

            // Fill driver details
            document.getElementById('driver_name').value = driver.driver_name || '';
            document.getElementById('driver_mobile').value = driver.driver_mobile || '';

            // Fill transporter if available
            if (driver.transporter_name) {
                document.getElementById('transporter_name').value = driver.transporter_name || '';
                document.getElementById('transporter_id_hidden').value = driver.transporter_id || '';
            }

            // Show success message
            const messageDiv = document.getElementById('autofill-message');
            messageDiv.innerHTML = '✅ Driver selected: ' + driver.driver_name;
            messageDiv.style.background = '#f0fdf4';
            messageDiv.style.color = '#065f46';
            messageDiv.style.borderLeftColor = '#10b981';

            // Hide message after 3 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);

            // Clear stored drivers
            window.availableDrivers = null;
        }

        function closeDriverSelection() {
            document.getElementById('autofill-message').style.display = 'none';
            window.availableDrivers = null;
        }

        // Also trigger on Enter key
        document.getElementById('vehicle_number')?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                fetchVehicleDetails();
            }
        });

        // Capture transporter ID when selected from datalist
        document.getElementById('transporter_name')?.addEventListener('input', function () {
            const value = this.value;
            const options = document.querySelectorAll('#transporter_list option');
            let foundId = null;

            options.forEach(option => {
                if (option.value === value) {
                    foundId = option.getAttribute('data-id');
                }
            });

            document.getElementById('transporter_id_hidden').value = foundId || '';
        });

        // Capture purpose ID when selected from datalist
        document.getElementById('purpose_name')?.addEventListener('input', function () {
            const value = this.value;
            const options = document.querySelectorAll('#purpose_list option');
            let foundId = null;

            options.forEach(option => {
                if (option.value === value) {
                    foundId = option.getAttribute('data-id');
                }
            });

            document.getElementById('purpose_id_hidden').value = foundId || '';
        });

        // Detect if device is mobile - more strict detection
        function isMobileDevice() {
            // Check user agent for mobile keywords
            const mobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            // Check screen size - mobile devices typically have smaller screens
            const smallScreen = window.innerWidth <= 768;

            // Check touch points (but require small screen too to avoid touchscreen PCs)
            const touchDevice = navigator.maxTouchPoints > 0 && smallScreen;

            const isMobile = mobileUA || touchDevice;

            console.log('Mobile Detection:');
            console.log('- User Agent Mobile:', mobileUA);
            console.log('- Small Screen (<= 768px):', smallScreen);
            console.log('- Touch Device:', touchDevice);
            console.log('- Final Result (Is Mobile):', isMobile);
            console.log('- User Agent:', navigator.userAgent);
            console.log('- Screen Width:', window.innerWidth);
            console.log('- Touch Points:', navigator.maxTouchPoints);

            return isMobile;
        }


        // Hide camera buttons on PC, show only on mobile - execute immediately
        (function () {
            const isMobile = isMobileDevice();
            console.log('Hiding camera buttons on PC:', !isMobile);

            // Always show camera buttons - the functions will handle device differences
            console.log('Mobile device detected:', isMobile);
            console.log('Camera buttons will be shown - functions handle device differences');
        })();

        // Helper to trigger clicks reliably on Android
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

        // Open camera for vehicle photo
        function openVehicleCamera() {
            if (isMobileDevice()) {
                safeClick('vehicle_photo_camera');
            } else {
                if (typeof openWebcamCapture === 'function') {
                    openWebcamCapture('vehicle_photo_file', 'updateVehiclePreview');
                } else {
                    safeClick('vehicle_photo_file');
                }
            }
        }

        // Preview wrappers for webcam modal
        function updateVehiclePreview(input) {
            if (input.files && input.files[0]) {
                document.getElementById('vehicle_photo_name').textContent = '✓ Photo captured';
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('vehicle_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        function updateBillPreview(input) {
            if (input.files && input.files[0]) {
                document.getElementById('bill_photo_name').textContent = '✓ Photo captured';
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('bill_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Intercept camera label clicks on desktop to show webcam modal
        document.addEventListener('DOMContentLoaded', function () {
            const vehicleCameraLabel = document.getElementById('vehicle_camera_label');
            const billCameraLabel = document.getElementById('bill_camera_label');

            if (vehicleCameraLabel) {
                vehicleCameraLabel.addEventListener('click', function (e) {
                    if (!detectMobileForWebcam()) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof openWebcamCapture === 'function') {
                            openWebcamCapture('vehicle_photo_file', 'updateVehiclePreview');
                        }
                    }
                });
            }

            if (billCameraLabel) {
                billCameraLabel.addEventListener('click', function (e) {
                    if (!detectMobileForWebcam()) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof openWebcamCapture === 'function') {
                            openWebcamCapture('bill_photo_file', 'updateBillPreview');
                        }
                    }
                });
            }
        });

        // Handle vehicle photo from camera
        document.getElementById('vehicle_photo_camera')?.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // RELIABLE WEBVIEW FIX: Swap names instead of using DataTransfer
                const mainInput = document.getElementById('vehicle_photo_file');
                e.target.name = "vehicle_photo";
                mainInput.name = "";

                // Show preview
                document.getElementById('vehicle_photo_name').textContent = '✓ Photo captured';
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('vehicle_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle vehicle photo from file upload
        document.getElementById('vehicle_photo_file')?.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // RELIABLE WEBVIEW FIX: Swap names back
                e.target.name = "vehicle_photo";
                const cameraInput = document.getElementById('vehicle_photo_camera');
                if (cameraInput) cameraInput.name = "";

                document.getElementById('vehicle_photo_name').textContent = '✓ ' + file.name.substring(0, 15);
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('vehicle_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle bill photo from camera
        document.getElementById('bill_photo_camera')?.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // RELIABLE WEBVIEW FIX: Swap names instead of using DataTransfer
                const mainInput = document.getElementById('bill_photo_file');
                e.target.name = "bill_photo";
                mainInput.name = "";

                // Show preview
                document.getElementById('bill_photo_name').textContent = '✓ Photo captured';
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('bill_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle bill photo from file upload
        document.getElementById('bill_photo_file')?.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // RELIABLE WEBVIEW FIX: Swap names back
                e.target.name = "bill_photo";
                const cameraInput = document.getElementById('bill_photo_camera');
                if (cameraInput) cameraInput.name = "";

                document.getElementById('bill_photo_name').textContent = '✓ ' + file.name.substring(0, 15);
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('bill_photo_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <?php
    // ==================== LOADING CHECKLIST ====================
elseif ($page == 'loading'):

    // Initialize tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);
    // Include the form
    include dirname(__DIR__) . '/loading_checklist.php';

    // ==================== UNLOADING CHECKLIST ====================
elseif ($page == 'unloading'):

    // Initialize tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);
    // Include the form
    include dirname(__DIR__) . '/unloading_checklist.php';

    // ==================== TRUCK OUTWARD ====================
elseif ($page == 'outward'):

    // Initialize tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Get trucks inside
    $inside_trucks = mysqli_query($conn, "SELECT * FROM truck_inward WHERE status = 'inside' ORDER BY inward_datetime DESC");

    // Get customers for OUT-GOING CHECK dropdown
    $customers_query = "SELECT id, customer_name FROM customer_master WHERE is_active = 1 ORDER BY customer_name";
    $customers_result = mysqli_query($conn, $customers_query);
    ?>
    <div class="container">
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <?php echo $success_msg; ?>
            </div>
            <?php
        endif; ?>
        <?php if (isset($session_success)): ?>
            <div class="alert alert-success">
                <?php echo $session_success; ?>
            </div>
            <?php
        endif; ?>
        <?php if (isset($session_error)): ?>
            <div class="alert alert-error">
                <?php echo $session_error; ?>
            </div>
            <?php
        endif; ?>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <a href="?page=dashboard" class="btn btn-secondary"
                style="flex: 1; display: block; position: relative; z-index: 10; text-align: center; text-decoration: none; padding: 10px; border-radius: 6px; background: #6b7280; color: white;">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Form Header with Gradient -->
        <div
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">➡️</div>
                <div>
                    <h1
                        style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Truck Outward Entry</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Record vehicle exit
                        from the facility</p>
                </div>
            </div>
        </div>

        <form method="POST">
            <!-- Section 1: Select Vehicle -->
            <div class="card"
                style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">
                        1</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚛 Select Vehicle
                            to
                            Exit</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Choose the vehicle that is
                            exiting the facility</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>🚚</span>
                        <span>Select Truck to Exit *</span>
                    </label>
                    <select name="inward_id" required
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; font-size: 14px;"
                        onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <option value="">-- Select Vehicle --</option>
                        <?php
                        mysqli_data_seek($inside_trucks, 0);
                        while ($truck = mysqli_fetch_assoc($inside_trucks)): ?>
                            <option value="<?php echo $truck['id']; ?>">
                                <?php echo $truck['vehicle_number'] . ' - ' . $truck['driver_name'] . ' (In: ' . date('d/m/Y h:i A', strtotime($truck['inward_datetime'])) . ')'; ?>
                            </option>
                            <?php
                        endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Section 2: Exit Remarks -->
            <div class="card"
                style="margin-bottom: 25px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                        2</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📝 Exit Remarks
                        </h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Any observations or notes
                            about
                            the exit</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>💬</span>
                        <span>Outward Remarks</span>
                    </label>
                    <textarea name="outward_remarks" placeholder="Any observations or notes..."
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; min-height: 100px; resize: vertical; font-family: inherit;"
                        onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245, 158, 11, 0.1)';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"></textarea>
                </div>
            </div>

            <!-- Section 3: OUT-GOING CHECK (VCPL/LOG/FR/02) -->
            <div class="card" id="outgoingCheckSection"
                style="margin-bottom: 20px; border-left: 4px solid #8b5cf6; background: linear-gradient(to right, #f5f3ff 0%, white 10%);">
                <div
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div
                        style="background: #8b5cf6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);">
                        3</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚚 OUT-GOING CHECK
                        </h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Customer, destination,
                            sealing
                            and dispatch checks</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Date & Time Of Reporting
                            (Out-going)</label>
                        <input type="datetime-local" name="outgoing_reporting_datetime"
                            value="<?php echo date('Y-m-d\TH:i'); ?>"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Customer</label>
                        <select name="outgoing_customer_id" id="outgoing_customer_id"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;"
                            onchange="const sel = this.options[this.selectedIndex]; document.getElementById('outgoing_customer_name').value = sel.text;">
                            <option value="">Select Customer</option>
                            <?php
                            if ($customers_result) {
                                mysqli_data_seek($customers_result, 0);
                                while ($row = mysqli_fetch_assoc($customers_result)): ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['customer_name']); ?>
                                    </option>
                                    <?php
                                endwhile;
                            } ?>
                        </select>
                        <input type="hidden" name="outgoing_customer_name" id="outgoing_customer_name">
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151;">Destination</label>
                    <input type="text" name="outgoing_destination" placeholder="Destination"
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                </div>

                <div class="sub-item">
                    <label><strong>Condition Of Tarpaulin / Method Of Lying (Damage/Holes/Coverage)</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="tarpaulin_condition_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="tarpaulin_condition_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="tarpaulin_condition_remarks">
                        </div>
                    </div>
                </div>

                <div class="sub-item">
                    <label><strong>Use Of "L" Angle / Wooden Blocks (In Case Of Open Trucks)</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="wooden_blocks_used_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="wooden_blocks_used_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="wooden_blocks_used_remarks">
                        </div>
                    </div>
                </div>

                <div class="sub-item">
                    <label><strong>Rope Tightening</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="rope_tightening_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="rope_tightening_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="rope_tightening_remarks">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">No. Of Seals</label>
                        <input type="number" name="number_of_seals" min="0" value="0"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Sealing Method</label>
                        <input type="text" name="sealing_method" placeholder="Method of sealing"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151;">Sealing Done By</label>
                    <input type="text" name="sealing_done_by" placeholder="Name"
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                </div>

                <div class="sub-item">
                    <label><strong>Sealing Check</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="sealing_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="sealing_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="sealing_remarks">
                        </div>
                    </div>
                </div>

                <div class="sub-item">
                    <label><strong>Document Check</strong> (CE Invoice, Way Bill, LR Copy, TREM Card)</label>
                    <div
                        style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 10px;">
                        <?php
                        $out_doc_items = [
                            'ce_invoice' => 'CE Invoice',
                            'way_bill' => 'Way Bill',
                            'lr_copy' => 'LR Copy',
                            'trem_card' => 'TREM Card'
                        ];
                        foreach ($out_doc_items as $k => $lbl): ?>
                            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px;">
                                <div style="font-weight:700; margin-bottom:8px; font-size: 13px;">
                                    <?php echo htmlspecialchars($lbl); ?>
                                </div>
                                <select name="out_doc_<?php echo $k; ?>_status"
                                    style="width:100%; padding:10px; border:2px solid #e5e7eb; border-radius:10px; margin-bottom: 8px;">
                                    <option value="">Select</option>
                                    <option value="OK">OK</option>
                                    <option value="NOT OK">NOT OK</option>
                                    <option value="NA">NA</option>
                                </select>
                                <input type="text" name="out_doc_<?php echo $k; ?>_remarks" placeholder="Remarks"
                                    style="width:100%; padding:10px; border:2px solid #e5e7eb; border-radius:10px;">
                            </div>
                            <?php
                        endforeach; ?>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Date & Time Of Leaving</label>
                        <input type="datetime-local" name="leaving_datetime" value="<?php echo date('Y-m-d\TH:i'); ?>"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Naaviq Trip Started OR Not</label>
                        <select name="naaviq_trip_started"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Naaviq Action (if NOT)</label>
                        <input type="text" name="naaviq_trip_action" placeholder="Action"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Naaviq Remarks</label>
                        <input type="text" name="naaviq_trip_remarks" placeholder="Remarks"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Driver Sign</label>
                        <input type="text" name="out_driver_signature" placeholder="Driver signature/name"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Transporter Sign</label>
                        <input type="text" name="out_transporter_signature" placeholder="Transporter signature/name"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Security Sign</label>
                        <input type="text" name="out_security_signature" placeholder="Security signature/name"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151;">Logistic Sign</label>
                        <input type="text" name="out_logistic_signature" placeholder="Logistic signature/name"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Submit Button Section -->
            <div
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);">
                <button type="submit" name="submit_outward" class="btn btn-success btn-full"
                    style="background: white; color: #10b981; padding: 16px 32px; font-size: 18px; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s; border: none; width: 100%;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                    ✅ CONFIRM OUTWARD EXIT & DISPATCH
                </button>
            </div>
        </form>

        <!-- Show trucks inside -->
        <div class="card">
            <h2>Trucks Currently Inside</h2>
            <?php
            mysqli_data_seek($inside_trucks, 0);
            if (mysqli_num_rows($inside_trucks) == 0):
                ?>
                <p style="text-align: center; color: #666; padding: 20px;">No trucks inside currently</p>
                <?php
            else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Inward Time</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($truck = mysqli_fetch_assoc($inside_trucks)):
                                $duration_hrs = (time() - strtotime($truck['inward_datetime'])) / 3600;
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $truck['vehicle_number']; ?>
                                    </td>
                                    <td>
                                        <?php echo $truck['driver_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y h:i A', strtotime($truck['inward_datetime'])); ?>
                                    </td>
                                    <td>
                                        <?php echo formatDuration($duration_hrs); ?>
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

    <?php
    // ==================== TRUCK LIST ====================
elseif ($page == 'inside'):

    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    $where = ["ti.status = 'inside'"];
    if ($search) {
        $where[] = "(ti.vehicle_number LIKE '%$search%' OR ti.driver_name LIKE '%$search%' OR ti.bill_number LIKE '%$search%' OR ti.transporter_name LIKE '%$search%')";
    }

    $where_sql = 'WHERE ' . implode(' AND ', $where);

    // Get trucks currently inside
    $entries = mysqli_query($conn, "SELECT ti.*, vm.permit_validity FROM truck_inward ti LEFT JOIN vehicle_master vm ON ti.vehicle_number = vm.vehicle_number $where_sql ORDER BY ti.inward_datetime ASC");
    $total_inside = mysqli_num_rows($entries);
    ?>
    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>🚛 Trucks Inside Gate</h2>
                <div
                    style="background: #fef3c7; color: #92400e; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 18px;">
                    Total:
                    <?php echo $total_inside; ?>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="inside">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search vehicle, driver, transporter, bill..."
                        value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <?php if ($search): ?>
                        <a href="?page=inside" class="btn btn-secondary">🔄 Clear</a>
                        <?php
                    endif; ?>
                </div>
            </form>

            <!-- Table -->
            <?php if ($total_inside > 0): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Entry #</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Transporter</th>
                                <th>Time In</th>
                                <th>Permit Expiry</th>
                                <th>Duration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($entry = mysqli_fetch_assoc($entries)):
                                // Calculate current duration for inside trucks
                                $duration_hrs = (time() - strtotime($entry['inward_datetime'])) / 3600;
                                $duration_display = formatDuration($duration_hrs);
                                ?>
                                <tr>
                                    <td><strong>
                                            <?php echo $entry['entry_number']; ?>
                                        </strong></td>
                                    <td><strong>
                                            <?php echo $entry['vehicle_number']; ?>
                                        </strong></td>
                                    <td>
                                        <?php echo $entry['driver_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo $entry['transporter_name'] ?: '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo strtoupper(date('d-M-y h:i A', strtotime($entry['inward_datetime']))); ?>
                                    </td>
                                    <td>
                                        <?php if ($entry['permit_validity']):
                                            $is_expired = strtotime($entry['permit_validity']) < time();
                                            ?>
                                            <span style="color: <?php echo $is_expired ? '#ef4444' : '#10b981'; ?>; font-weight: 600;">
                                                <?php echo strtoupper(date('d-M-y', strtotime($entry['permit_validity']))); ?>
                                                <?php echo $is_expired ? ' (Exp)' : ''; ?>
                                            </span>
                                            <?php
                                        else: ?>
                                            <span style="color: #9ca3af;">N/A</span>
                                            <?php
                                        endif; ?>
                                    </td>
                                    <td><strong style="color: #f59e0b;">
                                            <?php echo $duration_display; ?>
                                        </strong></td>
                                    <td onclick="event.stopPropagation();">
                                        <a href="?page=inward-details&id=<?php echo intval($entry['id']); ?>" class="btn btn-sm"
                                            style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px; margin-right: 5px; text-decoration: none;">👁️
                                            View</a>
                                        <a href="?page=outward&id=<?php echo intval($entry['id']); ?>" class="btn btn-sm"
                                            style="background: #10b981; color: white; padding: 5px 10px; font-size: 12px; text-decoration: none;">➡️
                                            Exit</a>
                                    </td>
                                </tr>
                                <?php
                            endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 20px;">🚛</div>
                    <h3 style="color: #333; margin-bottom: 10px;">No Trucks Inside</h3>
                    <p>All trucks have exited the gate.</p>
                </div>
                <?php
            endif; ?>
        </div>

        <a href="?page=dashboard" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10;">
            ← Back
        </a>
    </div>

    <?php
    // ==================== ENTRY DETAILS ====================
elseif ($page == 'details' || $page == 'inward-details' || $page == 'outward-details'):

    // Sanitize and validate ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo "<div class='container'><div class='alert alert-error'>Invalid entry ID!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    // Escape ID for SQL query (though intval already makes it safe)
    $id_escaped = mysqli_real_escape_string($conn, $id);

    // Determine view type
    $is_outward_view = ($page == 'outward-details');
    $is_inward_view = ($page == 'inward-details');
    $view_title = $is_outward_view ? 'Outward Details' : ($is_inward_view ? 'Inward Details' : 'Entry Details');

    // Fetch entry with proper ID validation
    $entry_result = mysqli_query($conn, "SELECT * FROM truck_inward WHERE id = $id_escaped LIMIT 1");

    if (!$entry_result) {
        echo "<div class='container'><div class='alert alert-error'>Database error: " . mysqli_error($conn) . "</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    if (mysqli_num_rows($entry_result) == 0) {
        echo "<div class='container'><div class='alert alert-error'>Entry not found! (ID: $id)</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $entry = mysqli_fetch_assoc($entry_result);

    // Verify entry was fetched correctly
    if (!$entry || !isset($entry['id'])) {
        echo "<div class='container'><div class='alert alert-error'>Error loading entry data!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $outward_result = mysqli_query($conn, "SELECT * FROM truck_outward WHERE inward_id = $id_escaped LIMIT 1");
    $outward = ($outward_result && mysqli_num_rows($outward_result) > 0) ? mysqli_fetch_assoc($outward_result) : null;

    // Fetch Loading Checklist Details
    $loading_result = mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist WHERE inward_id = $id_escaped LIMIT 1");
    $loading_checklist = ($loading_result && mysqli_num_rows($loading_result) > 0) ? mysqli_fetch_assoc($loading_result) : null;

    // Fetch Unloading Checklist Details
    $unloading_result = mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist WHERE inward_id = $id_escaped LIMIT 1");
    $unloading_checklist = ($unloading_result && mysqli_num_rows($unloading_result) > 0) ? mysqli_fetch_assoc($unloading_result) : null;

    // Fetch Outgoing Checklist Details
    $outgoing_result = mysqli_query($conn, "SELECT * FROM vehicle_outgoing_checklist WHERE inward_id = $id_escaped LIMIT 1");
    $outgoing_checklist = ($outgoing_result && mysqli_num_rows($outgoing_result) > 0) ? mysqli_fetch_assoc($outgoing_result) : null;

    // Fetch driver photo from driver_master using driver mobile
    $driver_photo = null;
    if ($entry['driver_mobile']) {
        $driver_mobile = mysqli_real_escape_string($conn, $entry['driver_mobile']);
        $driver_result = mysqli_query($conn, "SELECT photo FROM driver_master WHERE mobile = '$driver_mobile' AND is_active = 1 LIMIT 1");
        if ($driver_result && mysqli_num_rows($driver_result) > 0) {
            $driver_data = mysqli_fetch_assoc($driver_result);
            $driver_photo = $driver_data['photo'];
        }
    }
    ?>
    <div class="container">
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">✅ Entry updated successfully!</div>
            <?php
        endif; ?>

        <div class="card">
            <h2>📄
                <?php echo $view_title; ?>
            </h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 24px; margin-bottom: 5px;">
                            <?php echo $entry['vehicle_number']; ?>
                        </h3>
                        <p style="color: #666;">Entry #
                            <?php echo $entry['entry_number']; ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo $entry['status'] == 'inside' ? 'warning' : 'success'; ?>"
                        style="font-size: 14px;">
                        <?php echo strtoupper($entry['status']); ?>
                    </span>
                </div>
            </div>

            <table class="entry-details-table" style="margin: 0;">
                <tr>
                    <th>Driver Name</th>
                    <td>
                        <?php echo $entry['driver_name']; ?>
                    </td>
                </tr>
                <tr>
                    <th>Driver Mobile</th>
                    <td>
                        <?php echo $entry['driver_mobile']; ?>
                    </td>
                </tr>
                <tr>
                    <th>Transporter</th>
                    <td>
                        <?php echo $entry['transporter_name'] ?: 'N/A'; ?>
                    </td>
                </tr>
                <?php if (!$is_outward_view): ?>
                    <tr>
                        <th>Purpose</th>
                        <td>
                            <?php echo $entry['purpose_name'] ?: 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Bill Number</th>
                        <td>
                            <?php echo $entry['bill_number'] ?: 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>From Location</th>
                        <td>
                            <?php echo $entry['from_location'] ?: 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>To Location</th>
                        <td>
                            <?php echo $entry['to_location'] ?: 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Inward Time</th>
                        <td>
                            <?php echo strtoupper(date('d-M-y h:i A', strtotime($entry['inward_datetime']))); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Inward By</th>
                        <td>
                            <?php echo $entry['inward_by_name']; ?>
                        </td>
                    </tr>
                    <?php
                endif; ?>

                <?php if ($outward && is_array($outward) && (!$is_inward_view)):
                    // Recalculate duration if it's 0 (legacy entries or timezone issues)
                    $display_duration = $outward['duration_hours'];
                    if ($display_duration == 0) {
                        $display_duration = (strtotime($outward['outward_datetime']) - strtotime($entry['inward_datetime'])) / 3600;
                    }
                    ?>
                    <tr>
                        <th>Outward Time</th>
                        <td>
                            <?php echo strtoupper(date('d-M-y h:i A', strtotime($outward['outward_datetime']))); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>
                            <?php echo formatDuration($display_duration); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Outward By</th>
                        <td>
                            <?php echo $outward['outward_by_name']; ?>
                        </td>
                    </tr>
                    <?php
                endif; ?>
            </table>

            <?php if (!$is_outward_view && $entry['security_comments']): ?>
                <div
                    style="margin-top: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <strong>Security Comments:</strong><br>
                    <?php echo nl2br($entry['security_comments']); ?>
                </div>
                <?php
            endif; ?>

            <!-- Items from QR/Bill -->
            <?php
            $items_display = [];
            if (!$is_outward_view && !empty($entry['items_json'])) {
                $items_json = json_decode($entry['items_json'], true);

                // Fix for potential double-encoding
                if (is_string($items_json)) {
                    $items_json = json_decode($items_json, true);
                }

                if (is_array($items_json) && count($items_json) > 0) {
                    $items_display = $items_json;
                }
            }
            // Also check QR data for items if items_json is empty
            if (!$is_outward_view && empty($items_display) && !empty($entry['qr_code_data'])) {
                $qr_json = json_decode($entry['qr_code_data'], true);
                if ($qr_json) {
                    if (isset($qr_json['items']) && is_array($qr_json['items'])) {
                        $items_display = $qr_json['items'];
                    } elseif (isset($qr_json['products']) && is_array($qr_json['products'])) {
                        $items_display = array_map(function ($p) {
                            return [
                                'item_code' => $p['product_code'] ?? $p['sku'] ?? '',
                                'item_name' => $p['product_name'] ?? $p['name'] ?? '',
                                'quantity' => $p['quantity'] ?? $p['qty'] ?? 0,
                                'unit' => $p['unit'] ?? 'PCS'
                            ];
                        }, $qr_json['products']);
                    } elseif (isset($qr_json['line_items']) && is_array($qr_json['line_items'])) {
                        $items_display = array_map(function ($l) {
                            return [
                                'item_code' => $l['item_code'] ?? '',
                                'item_name' => $l['description'] ?? $l['item_name'] ?? '',
                                'quantity' => $l['quantity'] ?? 0,
                                'unit' => $l['unit'] ?? 'PCS'
                            ];
                        }, $qr_json['line_items']);
                    }
                }
            }
            ?>
            <?php if (!empty($items_display)): ?>
                <div
                    style="margin-top: 20px; padding: 15px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 4px;">
                    <strong style="font-size: 16px; display: block; margin-bottom: 15px;">📦 Items from Bill/QR Code
                        (
                        <?php echo count($items_display); ?> items):
                    </strong>
                    <div class="table-wrapper" style="margin-top: 10px;">
                        <table style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr style="background: #3b82f6; color: white;">
                                    <th style="padding: 10px; text-align: left;">Item Code</th>
                                    <th style="padding: 10px; text-align: left;">Item Name</th>
                                    <th style="padding: 10px; text-align: right;">Quantity</th>
                                    <th style="padding: 10px; text-align: center;">Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_qty = 0;
                                foreach ($items_display as $item):
                                    $qty = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                                    $total_qty += $qty;
                                    ?>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 10px;">
                                            <?php echo htmlspecialchars($item['item_code'] ?? $item['code'] ?? $item['product_code'] ?? '-'); ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <strong>
                                                <?php echo htmlspecialchars($item['item_name'] ?? $item['name'] ?? $item['product_name'] ?? $item['description'] ?? '-'); ?>
                                            </strong>
                                        </td>
                                        <td style="padding: 10px; text-align: right; font-weight: 600;">
                                            <?php echo number_format($qty, 2); ?>
                                        </td>
                                        <td style="padding: 10px; text-align: center;">
                                            <?php echo htmlspecialchars($item['unit'] ?? $item['uom'] ?? 'PCS'); ?>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <tr style="background: #e0e7ff; font-weight: 600; border-top: 2px solid #3b82f6;">
                                    <td colspan="2" style="padding: 10px; text-align: right;"><strong>Total:</strong>
                                    </td>
                                    <td style="padding: 10px; text-align: right;">
                                        <?php echo number_format($total_qty, 2); ?>
                                    </td>
                                    <td style="padding: 10px;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endif; ?>

            <!-- QR Code Data Display -->
            <?php if (!$is_outward_view && !empty($entry['qr_code_data'])): ?>
                <div
                    style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-left: 4px solid #8b5cf6; border-radius: 4px;">
                    <strong style="font-size: 16px; display: block; margin-bottom: 10px;">📷 QR Code Scanned
                        Data:</strong>
                    <div
                        style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #d1d5db; max-height: 300px; overflow-y: auto;">
                        <pre
                            style="margin: 0; font-size: 12px; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace;"><?php
                            $display_qr = $entry['qr_code_data'];
                            // If it looks like a JWT, try to decode it for display
                            if (strpos($display_qr, '.') !== false && count(explode('.', $display_qr)) === 3) {
                                try {
                                    $parts = explode('.', $display_qr);
                                    $payload = $parts[1];
                                    // Decode Base64Url
                                    $base64 = str_replace(['-', '_'], ['+', '/'], $payload);
                                    $decoded = base64_decode($base64);
                                    if ($decoded) {
                                        $jwt_json = json_decode($decoded, true);
                                        if (isset($jwt_json['data'])) {
                                            if (is_string($jwt_json['data'])) {
                                                $inner_json = json_decode($jwt_json['data'], true);
                                                if ($inner_json) {
                                                    $display_qr = json_encode($inner_json, JSON_PRETTY_PRINT);
                                                }
                                            } else {
                                                $display_qr = json_encode($jwt_json['data'], JSON_PRETTY_PRINT);
                                            }
                                        } else {
                                            $display_qr = json_encode($jwt_json, JSON_PRETTY_PRINT);
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Keep original if decoding fails
                                }
                            } else {
                                // Try to pretty-print if it's already JSON
                                $json_test = json_decode($display_qr, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $display_qr = json_encode($json_test, JSON_PRETTY_PRINT);
                                }
                            }
                            echo htmlspecialchars($display_qr);
                            ?></pre>
                    </div>
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 8px;">
                        💡 This is the raw QR code data scanned from the bill/challan
                    </small>
                </div>
                <?php
            endif; ?>

            <?php if (!$is_inward_view && $outward && is_array($outward) && !empty($outward['outward_remarks'])): ?>
                <div
                    style="margin-top: 15px; padding: 15px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 4px;">
                    <strong>Outward Remarks:</strong><br>
                    <?php echo nl2br($outward['outward_remarks']); ?>
                </div>
                <?php
            endif; ?>

            <!-- Loading Checklist Details -->
            <?php if (!$is_outward_view && $loading_checklist): ?>
                <div
                    style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 12px; border-left: 5px solid #0ea5e9;">
                    <h3
                        style="margin-top: 0; margin-bottom: 20px; color: #0369a1; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                        <span>📦</span> Loading Checklist (VCPL/LOG/FR/01)
                    </h3>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Vehicle
                                Make</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($loading_checklist['vehicle_type_make'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Capacity</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($loading_checklist['capacity'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Loading
                                Location</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($loading_checklist['loading_location'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Body
                                Type</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($loading_checklist['body_type'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <strong
                            style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 10px;">Documents
                            & Fitness Checks</strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                            <?php
                            $lc_docs = json_decode($loading_checklist['documents_json'] ?? '[]', true) ?: [];
                            $lc_doc_labels = [
                                'driving_licence' => 'Driving Licence',
                                'rc_book' => 'RC Book',
                                'permit' => 'Permit',
                                'insurance' => 'Insurance',
                                'puc_certificate' => 'PUC Certificate'
                            ];
                            foreach ($lc_docs as $doc):
                                $observation = $doc['observation'] ?? 'N/A';
                                $badge_color = ($observation == 'OK' || $observation == 'Yes' || $observation == 'Valid') ? 'success' : (($observation == 'NOT OK' || $observation == 'No' || $observation == 'Expired') ? 'error' : 'secondary');
                                ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">
                                            <?php echo $lc_doc_labels[$doc['type']] ?? ucwords($doc['type']); ?>
                                        </span>
                                        <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 10px;">
                                            <?php echo $observation; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($doc['remarks'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;"><strong>Remarks:</strong>
                                            <?php echo htmlspecialchars($doc['remarks']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endforeach; ?>

                            <!-- Platform Condition -->
                            <?php
                            $fitness_checks = [
                                'Cleanliness' => ['obs' => $loading_checklist['platform_cleanliness_obs'], 'act' => $loading_checklist['platform_cleanliness_action'], 'rem' => $loading_checklist['platform_cleanliness_remarks']],
                                'Gaps/Sharp Objects' => ['obs' => $loading_checklist['platform_gaps_obs'], 'act' => $loading_checklist['platform_gaps_action'], 'rem' => $loading_checklist['platform_gaps_remarks']],
                                'Cross Bars Removed' => ['obs' => $loading_checklist['cross_bars_removed_obs'], 'act' => $loading_checklist['cross_bars_removed_action'], 'rem' => $loading_checklist['cross_bars_removed_remarks']],
                                'Tarpaulins (Min 3)' => ['obs' => $loading_checklist['tarpaulins_available_obs'], 'act' => $loading_checklist['tarpaulins_available_action'], 'rem' => $loading_checklist['tarpaulins_available_remarks']],
                                'Driver Smartphone' => ['obs' => $loading_checklist['driver_smartphone_status'], 'act' => $loading_checklist['driver_smartphone_action'], 'rem' => $loading_checklist['driver_smartphone_remarks']]
                            ];
                            foreach ($fitness_checks as $label => $check):
                                $badge_color = ($check['obs'] == 'OK' || $check['obs'] == 'Yes') ? 'success' : (($check['obs'] == 'NOT OK' || $check['obs'] == 'No') ? 'error' : 'secondary');
                                ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">
                                            <?php echo $label; ?>
                                        </span>
                                        <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 10px;">
                                            <?php echo $check['obs'] ?? 'N/A'; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($check['act'])): ?>
                                        <div style="font-size: 11px; color: #059669; margin-top: 4px;"><strong>Action:</strong>
                                            <?php echo htmlspecialchars($check['act']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                    <?php if (!empty($check['rem'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;"><strong>Remarks:</strong>
                                            <?php echo htmlspecialchars($check['rem']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endforeach; ?>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Reporting Time
                                (Plant)</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $loading_checklist['reporting_time_plant'] ? date('h:i A', strtotime($loading_checklist['reporting_time_plant'])) : 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Gate Entry
                                Time</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $loading_checklist['gate_entry_time'] ? date('h:i A', strtotime($loading_checklist['gate_entry_time'])) : 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Checked
                                By</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($loading_checklist['checked_by_name'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php
            endif; ?>

            <!-- Unloading Checklist Details -->
            <?php if (!$is_outward_view && $unloading_checklist): ?>
                <div
                    style="margin-top: 30px; padding: 20px; background: #fdf2f8; border-radius: 12px; border-left: 5px solid #db2777;">
                    <h3
                        style="margin-top: 0; margin-bottom: 20px; color: #9d174d; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                        <span>📥</span> Unloading Checklist (VCPL/STORE/FR/01)
                    </h3>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Vehicle
                                Type</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($unloading_checklist['vehicle_type'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Body
                                Type</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($unloading_checklist['body_type'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Vendor
                                Name</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($unloading_checklist['vendor_name'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong
                                style="color: #64748b; font-size: 11px; text-transform: uppercase;">Challan/Invoice</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars(($unloading_checklist['challan_no'] ?: $unloading_checklist['invoice_no']) ?: 'N/A'); ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <strong
                            style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 10px;">Safety
                            & Document Checks</strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                            <?php
                            $safety_checks = json_decode($unloading_checklist['safety_checks_json'] ?? '[]', true) ?: [];
                            $unloading_labels = [
                                'driver_cleaner_safety_induction' => 'Driver & Cleaner Safety Induction',
                                'ppe_provided' => 'PPE Provided',
                                'wheel_chocks_provided' => 'Wheel Chocks Provided',
                                'fire_extinguisher_available' => 'Fire Extinguisher Available',
                                'first_aid_box_available' => 'First Aid Box Available',
                                'cleaner_available' => 'Cleaner Available',
                                'no_oil_leakage' => 'No Oil Leakage',
                                'reverse_horn_available' => 'Reverse Horn Available',
                                'tyre_condition_good' => 'Tyre Condition',
                                'indicators_horn_lights_working' => 'Indicators/Horn/Lights',
                                'seat_belt_available' => 'Seat Belt Available',
                                'hazard_warning_triangle_available' => 'Hazard Warning Triangle',
                                'rear_view_mirrors_good' => 'Rear View Mirrors',
                                'tailgate_rear_guard_condition' => 'Tailgate/Rear Guard'
                            ];
                            foreach ($safety_checks as $check):
                                $observation = $check['observation'] ?? $check['status'] ?? 'N/A';
                                $badge_color = ($observation == 'OK' || $observation == 'Yes' || $observation == 'Valid') ? 'success' : (($observation == 'NOT OK' || $observation == 'No' || $observation == 'Expired') ? 'error' : 'secondary');
                                $item_key = $check['type'] ?? $check['item'] ?? '';
                                $label = $check['label'] ?? $unloading_labels[$item_key] ?? ucwords(str_replace('_', ' ', $item_key));
                                ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">
                                            <?php echo htmlspecialchars($label); ?>
                                        </span>
                                        <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 10px;">
                                            <?php echo $observation; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($check['remarks'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            <?php echo htmlspecialchars($check['remarks']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endforeach; ?>

                            <!-- Tanker Specific Checks if applicable -->
                            <?php if ($unloading_checklist['tanker_sealing_status_obs']): ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">Tanker Sealing Status</span>
                                        <span
                                            class="badge badge-<?php echo $unloading_checklist['tanker_sealing_status_obs'] == 'OK' ? 'success' : 'error'; ?>"
                                            style="font-size: 10px;">
                                            <?php echo $unloading_checklist['tanker_sealing_status_obs']; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($unloading_checklist['tanker_sealing_status_remarks'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            <?php echo htmlspecialchars($unloading_checklist['tanker_sealing_status_remarks']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endif; ?>

                            <?php if ($unloading_checklist['tanker_emergency_panel_obs']): ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">Emergency Info Panel</span>
                                        <span
                                            class="badge badge-<?php echo ($unloading_checklist['tanker_emergency_panel_obs'] == 'Yes' || $unloading_checklist['tanker_emergency_panel_obs'] == 'OK') ? 'success' : 'error'; ?>"
                                            style="font-size: 10px;">
                                            <?php echo $unloading_checklist['tanker_emergency_panel_obs']; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($unloading_checklist['tanker_emergency_panel_remarks'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            <?php echo htmlspecialchars($unloading_checklist['tanker_emergency_panel_remarks']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endif; ?>

                            <?php if ($unloading_checklist['tanker_fall_protection_obs']): ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">Fall Protection Railing</span>
                                        <span
                                            class="badge badge-<?php echo ($unloading_checklist['tanker_fall_protection_obs'] == 'Yes' || $unloading_checklist['tanker_fall_protection_obs'] == 'OK') ? 'success' : 'error'; ?>"
                                            style="font-size: 10px;">
                                            <?php echo $unloading_checklist['tanker_fall_protection_obs']; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($unloading_checklist['tanker_fall_protection_remarks'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            <?php echo htmlspecialchars($unloading_checklist['tanker_fall_protection_remarks']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Gross
                                Weight</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $unloading_checklist['gross_weight_invoice'] ?: 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Tare
                                Weight</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $unloading_checklist['tare_weight_invoice'] ?: 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Net
                                Weight</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $unloading_checklist['net_weight_invoice'] ?: 'N/A'; ?>
                            </span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Reporting
                                Time</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $unloading_checklist['reporting_datetime'] ? date('d/m/Y h:i A', strtotime($unloading_checklist['reporting_datetime'])) : 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Checked
                                By</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($unloading_checklist['checked_by_name'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($unloading_checklist['other_remarks'])): ?>
                        <div
                            style="margin-top: 20px; padding: 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Other
                                Remarks</strong><br>
                            <div style="font-size: 13px; margin-top: 5px;">
                                <?php echo nl2br(htmlspecialchars($unloading_checklist['other_remarks'])); ?>
                            </div>
                        </div>
                        <?php
                    endif; ?>
                </div>
                <?php
            endif; ?>

            <!-- Outgoing Checklist Details -->
            <?php if (!$is_inward_view && $outgoing_checklist): ?>
                <div
                    style="margin-top: 30px; padding: 20px; background: #f5f3ff; border-radius: 12px; border-left: 5px solid #8b5cf6;">
                    <h3
                        style="margin-top: 0; margin-bottom: 5px; color: #6d28d9; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                        <span>🚚</span> Outgoing Check (VCPL/LOG/FR/02)
                    </h3>
                    <p style="color: #666; font-size: 12px; margin-bottom: 20px;">
                        Document ID:
                        <?php echo htmlspecialchars($outgoing_checklist['document_id'] ?? 'VCPL/LOG/FR/02'); ?>
                        |
                        Date:
                        <?php echo $outgoing_checklist['document_date'] ? date('d-M-y', strtotime($outgoing_checklist['document_date'])) : 'N/A'; ?>
                    </p>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Customer</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($outgoing_checklist['customer_name'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Destination</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($outgoing_checklist['destination'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Reporting
                                Time</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $outgoing_checklist['reporting_datetime'] ? date('d/m/Y h:i A', strtotime($outgoing_checklist['reporting_datetime'])) : 'N/A'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Leaving
                                Time</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $outgoing_checklist['leaving_datetime'] ? date('d/m/Y h:i A', strtotime($outgoing_checklist['leaving_datetime'])) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <strong
                            style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 10px;">Dispatch
                            Checks</strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                            <?php
                            $out_checks = [
                                'Tarpaulin Condition' => ['obs' => $outgoing_checklist['tarpaulin_condition_obs'], 'act' => $outgoing_checklist['tarpaulin_condition_action'], 'rem' => $outgoing_checklist['tarpaulin_condition_remarks']],
                                'Wooden Blocks Used' => ['obs' => $outgoing_checklist['wooden_blocks_used_obs'], 'act' => $outgoing_checklist['wooden_blocks_used_action'], 'rem' => $outgoing_checklist['wooden_blocks_used_remarks']],
                                'Rope Tightening' => ['obs' => $outgoing_checklist['rope_tightening_obs'], 'act' => $outgoing_checklist['rope_tightening_action'], 'rem' => $outgoing_checklist['rope_tightening_remarks']],
                                'Sealing Status' => ['obs' => $outgoing_checklist['sealing_obs'], 'act' => $outgoing_checklist['sealing_action'], 'rem' => $outgoing_checklist['sealing_remarks']],
                                'Naaviq Trip Started' => ['obs' => $outgoing_checklist['naaviq_trip_started'], 'act' => $outgoing_checklist['naaviq_trip_action'], 'rem' => $outgoing_checklist['naaviq_trip_remarks']]
                            ];
                            foreach ($out_checks as $label => $check):
                                $badge_color = ($check['obs'] == 'OK' || $check['obs'] == 'Yes') ? 'success' : (($check['obs'] == 'NOT OK' || $check['obs'] == 'No') ? 'error' : 'secondary');
                                ?>
                                <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 13px; font-weight: 600;">
                                            <?php echo $label; ?>
                                        </span>
                                        <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 10px;">
                                            <?php echo $check['obs'] ?? 'N/A'; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($check['act'])): ?>
                                        <div style="font-size: 11px; color: #059669; margin-top: 4px;"><strong>Action:</strong>
                                            <?php echo htmlspecialchars($check['act']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                    <?php if (!empty($check['rem'])): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;"><strong>Remarks:</strong>
                                            <?php echo htmlspecialchars($check['rem']); ?>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                                <?php
                            endforeach; ?>
                        </div>
                    </div>

                    <!-- Outgoing Documents Check -->
                    <?php
                    $out_docs = json_decode($outgoing_checklist['documents_json'] ?? '[]', true) ?: [];
                    if (!empty($out_docs)):
                        ?>
                        <div style="margin-bottom: 20px;">
                            <strong
                                style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 10px;">Outgoing
                                Documents Checklist</strong>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                                <?php foreach ($out_docs as $doc):
                                    $badge_color = $doc['status'] == 'OK' ? 'success' : ($doc['status'] == 'NOT OK' ? 'error' : 'secondary');
                                    ?>
                                    <div style="padding: 10px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-size: 13px; font-weight: 600;">
                                                <?php echo htmlspecialchars($doc['label'] ?? ucwords(str_replace('_', ' ', $doc['type']))); ?>
                                            </span>
                                            <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 10px;">
                                                <?php echo $doc['status']; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($doc['remarks'])): ?>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                                <?php echo htmlspecialchars($doc['remarks']); ?>
                                            </div>
                                            <?php
                                        endif; ?>
                                    </div>
                                    <?php
                                endforeach; ?>
                            </div>
                        </div>
                        <?php
                    endif; ?>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">No. of
                                Seals</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo $outgoing_checklist['number_of_seals'] ?: '0'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Sealing
                                Method</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($outgoing_checklist['sealing_method'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Sealing Done
                                By</strong><br>
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($outgoing_checklist['sealing_done_by'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 10px; padding: 15px; border: 1px solid #e2e8f0;">
                        <strong
                            style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 10px;">Authorized
                            Signatures (Confirming dispatch)</strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                            <div>
                                <span style="font-size: 11px; color: #64748b;">Driver:</span><br>
                                <span style="font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($outgoing_checklist['driver_signature'] ?: '-'); ?>
                                </span>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #64748b;">Transporter:</span><br>
                                <span style="font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($outgoing_checklist['transporter_signature'] ?: '-'); ?>
                                </span>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #64748b;">Security:</span><br>
                                <span style="font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($outgoing_checklist['security_signature'] ?: '-'); ?>
                                </span>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #64748b;">Logistic:</span><br>
                                <span style="font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($outgoing_checklist['logistic_signature'] ?: '-'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            endif; ?>

            <!-- Photos Section -->
            <div style="margin-top: 20px;">
                <?php if (!$is_outward_view && !empty($entry['vehicle_photo_url'])): ?>
                    <div style="margin-bottom: 20px;">
                        <strong>🚛 Vehicle Photo:</strong><br>
                        <img src="<?php echo $entry['vehicle_photo_url']; ?>"
                            style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-top: 10px; border: 2px solid #3b82f6; cursor: pointer;"
                            onclick="window.open(this.src, '_blank')" title="Click to open in new tab">
                    </div>
                    <?php
                endif; ?>

                <?php if (!empty($driver_photo)): ?>
                    <div style="margin-bottom: 20px;">
                        <strong>👤 Driver Photo (from Driver Master):</strong><br>
                        <img src="<?php echo $driver_photo; ?>"
                            style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-top: 10px; border: 2px solid #8b5cf6; cursor: pointer;"
                            onclick="window.open(this.src, '_blank')" title="Click to open in new tab">
                    </div>
                    <?php
                endif; ?>

                <?php if (!$is_outward_view && !empty($entry['bill_photo_url'])): ?>
                    <div style="margin-bottom: 20px;">
                        <strong>📄 Bill/Challan Photo:</strong><br>
                        <img src="<?php echo $entry['bill_photo_url']; ?>"
                            style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-top: 10px; border: 2px solid #10b981; cursor: pointer;"
                            onclick="window.open(this.src, '_blank')" title="Click to open in new tab">
                    </div>
                    <?php
                endif; ?>
            </div>
        </div>

        <?php if (hasPermission('actions.edit_record')): ?>
            <div style="display: flex; gap: 10px; margin-top: 20px; margin-bottom: 20px;">
                <a href="?page=edit-inward&id=<?php echo $entry['id']; ?>" class="btn btn-primary"
                    style="flex: 1; padding: 12px 20px; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; border-radius: 6px; background: #3b82f6; color: white; transition: all 0.2s;"
                    onmouseover="this.style.background='#2563eb'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.background='#3b82f6'; this.style.transform='translateY(0)';">
                    ✏️ Edit Inward Entry
                </a>
                <?php if ($outward && is_array($outward)): ?>
                    <a href="?page=edit-outward&id=<?php echo $outward['id']; ?>" class="btn btn-success"
                        style="flex: 1; padding: 12px 20px; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; border-radius: 6px; background: #10b981; color: white; transition: all 0.2s;"
                        onmouseover="this.style.background='#059669'; this.style.transform='translateY(-2px)';"
                        onmouseout="this.style.background='#10b981'; this.style.transform='translateY(0)';">
                        ✏️ Edit Outward Entry
                    </a>
                    <?php
                endif; ?>
            </div>
            <?php
        endif; ?>

        <button onclick="goBack()" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s; width: 100%;"
            onmouseover="this.style.background='#4b5563';" onmouseout="this.style.background='#6b7280';">
            ← Back
        </button>
        <script>
            function goBack() {
                // Check if there's a previous page in history
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    // Fallback to trucks inside page if no history
                    window.location.href = '?page=inside';
                }
            }
        </script>
    </div>

    <?php
    // ==================== LOADING CHECKLIST DETAILS ====================
elseif ($page == 'loading-details'):

    // Sanitize and validate ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo "<div class='container'><div class='alert alert-error'>Invalid entry ID!</div><a href='?page=loading' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    // Initialize tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Fetch entry
    $id_escaped = mysqli_real_escape_string($conn, $id);
    $entry_result = mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist WHERE id = $id_escaped LIMIT 1");

    if (!$entry_result || mysqli_num_rows($entry_result) == 0) {
        echo "<div class='container'><div class='alert alert-error'>Loading checklist not found!</div><a href='?page=loading' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $entry = mysqli_fetch_assoc($entry_result);

    // Parse documents JSON
    $documents = [];
    if (!empty($entry['documents_json'])) {
        $documents = json_decode($entry['documents_json'], true);
    }

    // Fetch OUT-GOING CHECK (VCPL/LOG/FR/02) if available
    $outgoing = null;
    $outgoing_documents = [];
    $check_outgoing = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_outgoing_checklist'");
    if ($check_outgoing && mysqli_num_rows($check_outgoing) > 0) {
        $out_rs = mysqli_query($conn, "SELECT * FROM vehicle_outgoing_checklist WHERE loading_checklist_id = $id_escaped ORDER BY created_at DESC LIMIT 1");
        if ($out_rs && mysqli_num_rows($out_rs) > 0) {
            $outgoing = mysqli_fetch_assoc($out_rs);
            if (!empty($outgoing['documents_json'])) {
                $outgoing_documents = json_decode($outgoing['documents_json'], true) ?: [];
            }
        }
    }
    ?>
    <div class="container" style="padding-bottom: 120px;">
        <div class="card">
            <h2>📦 Loading Checklist Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 24px; margin-bottom: 5px;">
                            <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                        </h3>
                        <p style="color: #666;">Document ID:
                            <?php echo htmlspecialchars($entry['document_id'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <span
                        class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>"
                        style="font-size: 14px;">
                        <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>

            <table class="entry-details-table" style="margin: 0;">
                <tr>
                    <th>Document Date</th>
                    <td>
                        <?php echo $entry['document_date'] ? date('d-M-y', strtotime($entry['document_date'])) : 'N/A'; ?>
                    </td>
                </tr>
                <tr>
                    <th>Reporting Date/Time</th>
                    <td>
                        <?php echo date('d-M-y h:i A', strtotime($entry['reporting_datetime'])); ?>
                    </td>
                </tr>
                <tr>
                    <th>Vehicle Type/Make</th>
                    <td>
                        <?php echo htmlspecialchars($entry['vehicle_type_make'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Capacity</th>
                    <td>
                        <?php echo htmlspecialchars($entry['capacity'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Loading for Location</th>
                    <td>
                        <?php echo htmlspecialchars($entry['loading_location'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Body Type</th>
                    <td>
                        <?php echo htmlspecialchars($entry['body_type'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Transport Company</th>
                    <td>
                        <?php echo htmlspecialchars($entry['transport_company_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Driver Name</th>
                    <td>
                        <?php echo htmlspecialchars($entry['driver_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>License Number</th>
                    <td>
                        <?php echo htmlspecialchars($entry['license_number'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <?php if (!empty($documents)): ?>
                    <tr>
                        <th>Documents Status</th>
                        <td>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin-top: 5px;">
                                <?php
                                // Documents are stored as array of objects with 'type', 'observation', 'action', 'remarks'
                                $doc_labels = [
                                    'driving_licence' => 'Driving Licence',
                                    'rc_book' => 'RC Book',
                                    'permit' => 'Permit',
                                    'insurance' => 'Insurance',
                                    'puc_certificate' => 'PUC Certificate'
                                ];

                                // Check if documents is an array of objects
                                if (isset($documents[0]) && is_array($documents[0]) && isset($documents[0]['type'])) {
                                    // It's an array of objects
                                    foreach ($documents as $doc):
                                        $doc_type = $doc['type'] ?? '';
                                        $observation = $doc['observation'] ?? 'N/A';
                                        $action = $doc['action'] ?? '';
                                        $remarks = $doc['remarks'] ?? '';
                                        $label = $doc_labels[$doc_type] ?? ucwords(str_replace('_', ' ', $doc_type));
                                        $badge_color = ($observation == 'Valid' || $observation == 'Yes') ? 'success' : (($observation == 'Expired' || $observation == 'No') ? 'error' : 'secondary');
                                        ?>
                                        <div
                                            style="padding: 10px; background: #f3f4f6; border-radius: 6px; border-left: 3px solid <?php echo $badge_color == 'success' ? '#10b981' : ($badge_color == 'error' ? '#ef4444' : '#6b7280'); ?>;">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                                <strong style="font-size: 13px; color: #374151;">
                                                    <?php echo htmlspecialchars($label); ?>
                                                </strong>
                                                <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 11px;">
                                                    <?php echo htmlspecialchars($observation); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($action)): ?>
                                                <div style="font-size: 12px; color: #059669; margin-top: 3px;">
                                                    <strong>Action:</strong>
                                                    <?php echo nl2br(htmlspecialchars($action)); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                            <?php if (!empty($remarks)): ?>
                                                <div style="font-size: 12px; color: #666; margin-top: 3px; font-style: italic;">
                                                    <strong>Remarks:</strong>
                                                    <?php echo nl2br(htmlspecialchars($remarks)); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                        </div>
                                        <?php
                                    endforeach;
                                } else {
                                    // Fallback: treat as key-value array
                                    foreach ($doc_labels as $key => $label):
                                        if (isset($documents[$key])):
                                            $doc = $documents[$key];
                                            $status = is_array($doc) ? ($doc['status'] ?? $doc['observation'] ?? 'N/A') : $doc;
                                            $badge_color = ($status == 'Valid' || $status == 'Yes') ? 'success' : (($status == 'Expired' || $status == 'No') ? 'error' : 'secondary');
                                            ?>
                                            <div style="padding: 8px; background: #f3f4f6; border-radius: 4px;">
                                                <strong>
                                                    <?php echo $label; ?>:
                                                </strong>
                                                <span class="badge badge-<?php echo $badge_color; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </div>
                                            <?php
                                        endif;
                                    endforeach;
                                } ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                endif; ?>
            </table>

            <!-- Platform Condition Section -->
            <div
                style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <h3
                    style="margin-top: 0; margin-bottom: 20px; color: #1e40af; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    🏗️ Platform Condition (Check Fitness For Loading)
                </h3>
                <table class="entry-details-table" style="margin: 0;">
                    <tr>
                        <th style="width: 40%;">A) Cleanliness</th>
                        <td>
                            <div>
                                <span
                                    class="badge badge-<?php echo $entry['platform_cleanliness_obs'] == 'OK' ? 'success' : ($entry['platform_cleanliness_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                    <?php echo htmlspecialchars($entry['platform_cleanliness_obs'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if (!empty($entry['platform_cleanliness_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['platform_cleanliness_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['platform_cleanliness_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['platform_cleanliness_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>B) Gaps/Sharp object inside truck on floor/Side/Front walls</th>
                        <td>
                            <div>
                                <span
                                    class="badge badge-<?php echo $entry['platform_gaps_obs'] == 'OK' ? 'success' : ($entry['platform_gaps_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                    <?php echo htmlspecialchars($entry['platform_gaps_obs'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if (!empty($entry['platform_gaps_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['platform_gaps_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['platform_gaps_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['platform_gaps_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Other Checks Section -->
            <div
                style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #10b981;">
                <h3
                    style="margin-top: 0; margin-bottom: 20px; color: #059669; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    ✅ Other Checks
                </h3>
                <table class="entry-details-table" style="margin: 0;">
                    <tr>
                        <th style="width: 40%;">Removal Of Cross Bars on Top</th>
                        <td>
                            <div>
                                <span
                                    class="badge badge-<?php echo $entry['cross_bars_removed_obs'] == 'OK' ? 'success' : ($entry['cross_bars_removed_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                    <?php echo htmlspecialchars($entry['cross_bars_removed_obs'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if (!empty($entry['cross_bars_removed_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['cross_bars_removed_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['cross_bars_removed_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['cross_bars_removed_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Tarpaulins (Minimum 3 Required)</th>
                        <td>
                            <div>
                                <span
                                    class="badge badge-<?php echo $entry['tarpaulins_available_obs'] == 'OK' ? 'success' : ($entry['tarpaulins_available_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                    <?php echo htmlspecialchars($entry['tarpaulins_available_obs'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if (!empty($entry['tarpaulins_available_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['tarpaulins_available_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['tarpaulins_available_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['tarpaulins_available_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Driver Carrying Smartphone OR Not</th>
                        <td>
                            <div>
                                <span
                                    class="badge badge-<?php echo $entry['driver_smartphone_status'] == 'Yes' ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars($entry['driver_smartphone_status'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if (!empty($entry['driver_smartphone_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['driver_smartphone_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['driver_smartphone_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['driver_smartphone_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Time Tracking Section -->
            <div
                style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                <h3
                    style="margin-top: 0; margin-bottom: 20px; color: #7c3aed; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    ⏰ Time Tracking
                </h3>
                <table class="entry-details-table" style="margin: 0;">
                    <?php if ($entry['gate_entry_time']): ?>
                        <tr>
                            <th>Gate Entry Time</th>
                            <td>
                                <?php echo date('d/m/Y h:i A', strtotime($entry['gate_entry_date'] . ' ' . $entry['gate_entry_time'])); ?>
                            </td>
                        </tr>
                        <?php
                    endif; ?>
                    <?php if ($entry['reporting_time_plant']): ?>
                        <tr>
                            <th>Reporting Time at Plant</th>
                            <td>
                                <?php echo date('d/m/Y h:i A', strtotime($entry['reporting_date_plant'] . ' ' . $entry['reporting_time_plant'])); ?>
                            </td>
                        </tr>
                        <?php
                    endif; ?>
                </table>
            </div>

            <!-- Out-going check section removed from Loading Checklist Details as requested -->

            <!-- Other Remarks Section -->
            <div
                style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <h3
                    style="margin-top: 0; margin-bottom: 20px; color: #d97706; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    📝 Other (Any Specific Remarks)
                </h3>
                <table class="entry-details-table" style="margin: 0;">
                    <tr>
                        <th>Other Remarks</th>
                        <td>
                            <?php if (!empty($entry['other_remarks_obs'])): ?>
                                <div style="margin-bottom: 5px;">
                                    <span
                                        class="badge badge-<?php echo $entry['other_remarks_obs'] == 'OK' ? 'success' : ($entry['other_remarks_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars($entry['other_remarks_obs']); ?>
                                    </span>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['other_remarks_action'])): ?>
                                <div style="margin-top: 5px; color: #059669; font-size: 13px;">
                                    <strong>Action:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['other_remarks_action'])); ?>
                                </div>
                                <?php
                            endif; ?>
                            <?php if (!empty($entry['other_remarks'])): ?>
                                <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                    <strong>Remarks:</strong>
                                    <?php echo nl2br(htmlspecialchars($entry['other_remarks'])); ?>
                                </div>
                                <?php
                            endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Additional Information -->
            <table class="entry-details-table" style="margin-top: 30px;">
                <tr>
                    <th>Checked By</th>
                    <td>
                        <?php echo htmlspecialchars($entry['checked_by_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Security Signature</th>
                    <td>
                        <?php echo htmlspecialchars($entry['security_signature'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>
                        <?php echo date('d/m/Y h:i A', strtotime($entry['created_at'])); ?>
                    </td>
                </tr>
            </table>
        </div>

        <button onclick="goBack()" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s; width: 100%;"
            onmouseover="this.style.background='#4b5563';" onmouseout="this.style.background='#6b7280';">
            ← Back
        </button>
        <script>
            function goBack() {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '?page=loading';
                }
            }
        </script>
    </div>

    <?php
    // ==================== UNLOADING CHECKLIST DETAILS ====================
elseif ($page == 'unloading-details'):

    // Sanitize and validate ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo "<div class='container'><div class='alert alert-error'>Invalid entry ID!</div><a href='?page=unloading' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    // Initialize tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Fetch entry
    $id_escaped = mysqli_real_escape_string($conn, $id);
    $entry_result = mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist WHERE id = $id_escaped LIMIT 1");

    if (!$entry_result || mysqli_num_rows($entry_result) == 0) {
        echo "<div class='container'><div class='alert alert-error'>Unloading checklist not found!</div><a href='?page=unloading' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $entry = mysqli_fetch_assoc($entry_result);

    // Parse safety checks JSON
    $safety_checks = [];
    if (!empty($entry['safety_checks_json'])) {
        $safety_checks = json_decode($entry['safety_checks_json'], true);
    }
    ?>
    <div class="container" style="padding-bottom: 120px;">
        <div class="card">
            <h2>📥 Unloading Checklist Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 24px; margin-bottom: 5px;">
                            <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                        </h3>
                        <p style="color: #666;">Document ID:
                            <?php echo htmlspecialchars($entry['document_id'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <span
                        class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>"
                        style="font-size: 14px;">
                        <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>

            <table class="entry-details-table" style="margin: 0;">
                <tr>
                    <th>Reporting Date/Time</th>
                    <td>
                        <?php echo date('d-M-y h:i A', strtotime($entry['reporting_datetime'])); ?>
                    </td>
                </tr>
                <tr>
                    <th>Vehicle Type</th>
                    <td>
                        <?php echo htmlspecialchars($entry['vehicle_type'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Body Type</th>
                    <td>
                        <?php echo htmlspecialchars($entry['body_type'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Transport Company</th>
                    <td>
                        <?php echo htmlspecialchars($entry['transport_company_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Driver Name</th>
                    <td>
                        <?php echo htmlspecialchars($entry['driver_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Driver Mobile</th>
                    <td>
                        <?php echo htmlspecialchars($entry['driver_mobile'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>License Type</th>
                    <td>
                        <?php echo htmlspecialchars($entry['license_type'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>License Valid Till</th>
                    <td>
                        <?php echo $entry['license_valid_till'] ? date('d/m/Y', strtotime($entry['license_valid_till'])) : 'N/A'; ?>
                    </td>
                </tr>
                <tr>
                    <th>Driver Alcoholic Influence</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['driver_alcoholic_influence'] == 'No' ? 'success' : 'error'; ?>">
                            <?php echo htmlspecialchars($entry['driver_alcoholic_influence'] ?? 'N/A'); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Vendor Name</th>
                    <td>
                        <?php echo htmlspecialchars($entry['vendor_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Purchase Order No</th>
                    <td>
                        <?php echo htmlspecialchars($entry['purchase_order_no'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Challan No</th>
                    <td>
                        <?php echo htmlspecialchars($entry['challan_no'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Invoice No</th>
                    <td>
                        <?php echo htmlspecialchars($entry['invoice_no'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>GST Number</th>
                    <td>
                        <?php echo htmlspecialchars($entry['gst_number'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>RC Book Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['rc_book_status'] == 'Yes' ? 'success' : ($entry['rc_book_status'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['rc_book_status'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['rc_book_details'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['rc_book_details'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Permit Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['permit_status'] == 'Yes' ? 'success' : ($entry['permit_status'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['permit_status'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['permit_details'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['permit_details'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Insurance Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['insurance_status'] == 'Yes' ? 'success' : ($entry['insurance_status'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['insurance_status'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['insurance_details'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['insurance_details'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>PUC Certificate Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['puc_certificate_status'] == 'Yes' ? 'success' : ($entry['puc_certificate_status'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['puc_certificate_status'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['puc_certificate_details'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['puc_certificate_details'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Fitness Certificate Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['fitness_certificate_status'] == 'Yes' ? 'success' : ($entry['fitness_certificate_status'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['fitness_certificate_status'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['fitness_certificate_details'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['fitness_certificate_details'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <?php if (!empty($safety_checks)): ?>
                    <tr>
                        <th>Safety Checks</th>
                        <td>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin-top: 5px;">
                                <?php
                                // Check if safety_checks is an array of objects (from JSON) or a simple array
                                if (isset($safety_checks[0]) && is_array($safety_checks[0]) && isset($safety_checks[0]['item'])) {
                                    // It's an array of objects (from JSON decode)
                                    foreach ($safety_checks as $check_item):
                                        $item_name = $check_item['item'] ?? '';
                                        $item_status = $check_item['status'] ?? 'N/A';
                                        $item_remarks = $check_item['remarks'] ?? '';
                                        $display_name = ucwords(str_replace('_', ' ', $item_name));
                                        $badge_color = ($item_status == 'Yes' || $item_status == 'OK') ? 'success' : (($item_status == 'No' || $item_status == 'NOT OK') ? 'error' : 'secondary');
                                        ?>
                                        <div
                                            style="padding: 10px; background: #f3f4f6; border-radius: 6px; border-left: 3px solid <?php echo $badge_color == 'success' ? '#10b981' : ($badge_color == 'error' ? '#ef4444' : '#6b7280'); ?>;">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                                <strong style="font-size: 13px; color: #374151;">
                                                    <?php echo htmlspecialchars($display_name); ?>
                                                </strong>
                                                <span class="badge badge-<?php echo $badge_color; ?>" style="font-size: 11px;">
                                                    <?php echo htmlspecialchars($item_status); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($item_remarks)): ?>
                                                <div style="font-size: 12px; color: #666; margin-top: 5px; font-style: italic;">
                                                    <?php echo nl2br(htmlspecialchars($item_remarks)); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                        </div>
                                        <?php
                                    endforeach;
                                } else {
                                    // Fallback: treat as key-value array
                                    foreach ($safety_checks as $check => $value):
                                        $value_display = is_array($value) ? json_encode($value) : (string) $value;
                                        $value_check = is_array($value) ? 'N/A' : $value;
                                        ?>
                                        <div style="padding: 8px; background: #f3f4f6; border-radius: 4px;">
                                            <strong>
                                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $check))); ?>:
                                            </strong>
                                            <span
                                                class="badge badge-<?php echo $value_check == 'Yes' || $value_check == 'OK' ? 'success' : ($value_check == 'No' || $value_check == 'NOT OK' ? 'error' : 'secondary'); ?>">
                                                <?php echo htmlspecialchars($value_display); ?>
                                            </span>
                                        </div>
                                        <?php
                                    endforeach;
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                endif; ?>
                <tr>
                    <th>Tanker Sealing Status</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['tanker_sealing_status_obs'] == 'OK' ? 'success' : ($entry['tanker_sealing_status_obs'] == 'NOT OK' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['tanker_sealing_status_obs'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['tanker_sealing_status_remarks'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['tanker_sealing_status_remarks'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Tanker Emergency Panel</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['tanker_emergency_panel_obs'] == 'Yes' ? 'success' : ($entry['tanker_emergency_panel_obs'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['tanker_emergency_panel_obs'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['tanker_emergency_panel_remarks'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['tanker_emergency_panel_remarks'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Tanker Fall Protection</th>
                    <td>
                        <span
                            class="badge badge-<?php echo $entry['tanker_fall_protection_obs'] == 'Yes' ? 'success' : ($entry['tanker_fall_protection_obs'] == 'No' ? 'error' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($entry['tanker_fall_protection_obs'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($entry['tanker_fall_protection_remarks'])): ?>
                            <div style="margin-top: 5px; color: #666; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($entry['tanker_fall_protection_remarks'])); ?>
                            </div>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <?php if ($entry['gross_weight_invoice']): ?>
                    <tr>
                        <th>Gross Weight (Invoice)</th>
                        <td>
                            <?php echo number_format($entry['gross_weight_invoice'], 2); ?> kg
                        </td>
                    </tr>
                    <?php
                endif; ?>
                <?php if ($entry['tare_weight_invoice']): ?>
                    <tr>
                        <th>Tare Weight (Invoice)</th>
                        <td>
                            <?php echo number_format($entry['tare_weight_invoice'], 2); ?> kg
                        </td>
                    </tr>
                    <?php
                endif; ?>
                <?php if ($entry['net_weight_invoice']): ?>
                    <tr>
                        <th>Net Weight (Invoice)</th>
                        <td>
                            <?php echo number_format($entry['net_weight_invoice'], 2); ?> kg
                        </td>
                    </tr>
                    <?php
                endif; ?>
                <?php if (!empty($entry['other_remarks'])): ?>
                    <tr>
                        <th>Other Remarks</th>
                        <td>
                            <?php echo nl2br(htmlspecialchars($entry['other_remarks'])); ?>
                        </td>
                    </tr>
                    <?php
                endif; ?>
                <tr>
                    <th>Checked By</th>
                    <td>
                        <?php echo htmlspecialchars($entry['checked_by_name'] ?? 'N/A'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>
                        <?php echo date('d/m/Y h:i A', strtotime($entry['created_at'])); ?>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 30px; margin-bottom: 100px;">
            <button onclick="goBack()" class="btn btn-secondary btn-full"
                style="display: block; position: relative; z-index: 10; padding: 12px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s; width: 100%;"
                onmouseover="this.style.background='#4b5563';" onmouseout="this.style.background='#6b7280';">
                ← Back
            </button>
        </div>
        <script>
            function goBack() {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '?page=unloading';
                }
            }
        </script>
    </div>

    <?php
    // ==================== EDIT INWARD ENTRY ====================
elseif ($page == 'edit-inward'):

    // Check permission
    if (!hasPermission('actions.edit_record')) {
        echo "<div class='container'><div class='alert alert-error'>Access denied! You do not have permission to edit records.</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        echo "<div class='container'><div class='alert alert-error'>Invalid entry ID!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $entry = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM truck_inward WHERE id = $id"));
    if (!$entry) {
        echo "<div class='container'><div class='alert alert-error'>Entry not found!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    // Get transporters and purposes for dropdowns
    $transporters = mysqli_query($conn, "SELECT * FROM transporter_master WHERE is_active = 1 ORDER BY transporter_name");
    $purposes = mysqli_query($conn, "SELECT * FROM purpose_master WHERE is_active = 1 ORDER BY purpose_name");
    ?>
    <div class="container">
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-error">
                <?php echo $error_msg; ?>
            </div>
            <?php
        endif; ?>

        <div
            style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px;">✏️</div>
                <div>
                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">Edit Inward Entry</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Entry
                        #
                        <?php echo $entry['entry_number']; ?>
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="editInwardForm">
            <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Vehicle & Driver Information</h3>

                <div class="form-group">
                    <label>Vehicle Number *</label>
                    <input type="text" name="vehicle_number"
                        value="<?php echo htmlspecialchars($entry['vehicle_number']); ?>" required
                        style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label>Driver Name *</label>
                    <input type="text" name="driver_name" value="<?php echo htmlspecialchars($entry['driver_name']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Driver Mobile *</label>
                    <input type="text" name="driver_mobile" value="<?php echo htmlspecialchars($entry['driver_mobile']); ?>"
                        required>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Transporter & Purpose</h3>

                <div class="form-group">
                    <label>Transporter Name</label>
                    <input type="hidden" name="transporter_id" id="edit_transporter_id_hidden"
                        value="<?php echo $entry['transporter_id']; ?>">
                    <input type="text" name="transporter_name" id="edit_transporter_name"
                        value="<?php echo htmlspecialchars($entry['transporter_name']); ?>" list="edit_transporter_list"
                        autocomplete="off"
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    <datalist id="edit_transporter_list">
                        <?php
                        mysqli_data_seek($transporters, 0);
                        while ($trans = mysqli_fetch_assoc($transporters)):
                            echo '<option data-id="' . $trans['id'] . '" value="' . htmlspecialchars($trans['transporter_name'], ENT_QUOTES) . '">';
                        endwhile;
                        ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label>Purpose</label>
                    <input type="hidden" name="purpose_id" id="edit_purpose_id_hidden"
                        value="<?php echo $entry['purpose_id']; ?>">
                    <input type="text" name="purpose_name" id="edit_purpose_name"
                        value="<?php echo htmlspecialchars($entry['purpose_name']); ?>" list="edit_purpose_list"
                        autocomplete="off"
                        style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; width: 100%;">
                    <datalist id="edit_purpose_list">
                        <?php
                        mysqli_data_seek($purposes, 0);
                        while ($purp = mysqli_fetch_assoc($purposes)):
                            echo '<option data-id="' . $purp['id'] . '" value="' . htmlspecialchars($purp['purpose_name'], ENT_QUOTES) . '">';
                        endwhile;
                        ?>
                    </datalist>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Bill & Location Details</h3>

                <div class="form-group">
                    <label>Bill Number</label>
                    <input type="text" name="bill_number" value="<?php echo htmlspecialchars($entry['bill_number']); ?>">
                </div>

                <div class="form-group">
                    <label>From Location</label>
                    <input type="text" name="from_location"
                        value="<?php echo htmlspecialchars($entry['from_location']); ?>">
                </div>

                <div class="form-group">
                    <label>To Location</label>
                    <input type="text" name="to_location" value="<?php echo htmlspecialchars($entry['to_location']); ?>">
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Date & Time</h3>

                <div class="form-group">
                    <label>Inward Date & Time *</label>
                    <input type="datetime-local" name="inward_datetime"
                        value="<?php echo date('Y-m-d\TH:i', strtotime($entry['inward_datetime'])); ?>" required>
                </div>
            </div>

            <div class="card" id="manual_items_section" style="margin-bottom: 20px; border: 1px solid #e0e7ff; transition: all 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">📦 Material Items Information</h3>
                    <span id="items_count_badge" style="background: #8b5cf6; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: none;">0 Items</span>
                </div>

                <!-- Hidden Input to store JSON -->
                <input type="hidden" name="items" id="items_hidden_input">

                <div id="items_list_container" style="display: none; margin-bottom: 20px;">
                    <table style="width: 100%; border-collapse: collapse; background: #f8fafc; border-radius: 12px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                <th style="padding: 12px 10px;">Code</th>
                                <th style="padding: 12px 10px;">Item Description</th>
                                <th style="padding: 12px 10px;">Qty</th>
                                <th style="padding: 12px 10px;">Unit</th>
                                <th style="padding: 12px 10px;"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody"></tbody>
                    </table>
                </div>

                <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; display: grid; grid-template-columns: 1fr 2fr 1fr 1fr auto; gap: 8px; align-items: end;">
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">CODE</label>
                        <input type="text" id="new_item_code" placeholder="Code" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">ITEM NAME</label>
                        <input type="text" id="new_item_name" placeholder="Name/Desc" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">QTY</label>
                        <input type="number" id="new_item_qty" step="any" placeholder="0.0" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">UNIT</label>
                        <select id="new_item_unit" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; appearance: none; background: white;">
                            <option value="NOS">NOS</option>
                            <option value="KGS">KGS</option>
                            <option value="PCS">PCS</option>
                            <option value="MTS">MTS</option>
                            <option value="LTR">LTR</option>
                            <option value="BOX">BOX</option>
                            <option value="BAG">BAG</option>
                            <option value="UNIT">UNIT</option>
                            <option value="BUNDLE">BUNDLE</option>
                            <option value="PKT">PKT</option>
                            <option value="SET">SET</option>
                        </select>
                    </div>
                    <button type="button" onclick="addItemManually()" style="background: #3b82f6; color: white; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 800; cursor: pointer; height: 38px;">+</button>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Security Comments</h3>

                <div class="form-group">
                    <label>Comments</label>
                    <textarea name="security_comments"
                        rows="4"><?php echo htmlspecialchars($entry['security_comments']); ?></textarea>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize manualItems from existing JSON
                    var existingItems = <?php echo !empty($entry['items_json']) ? $entry['items_json'] : '[]'; ?>;
                    if (typeof existingItems === 'string') {
                        try { existingItems = JSON.parse(existingItems); } catch(e) { existingItems = []; }
                    }
                    if (Array.isArray(existingItems) && existingItems.length > 0) {
                        manualItems = existingItems;
                        renderItems();
                    }
                });
            </script>

            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Photos (Leave empty to keep existing)</h3>

                <!-- Vehicle Photo -->
                <div class="form-group"
                    style="background: #eff6ff; padding: 15px; border-radius: 12px; border: 2px dashed #3b82f6; margin-bottom: 20px;">
                    <label style="color: #1e40af; font-weight: 600; margin-bottom: 10px; display: block;">🚗 Vehicle
                        Photo:</label>

                    <!-- Hidden inputs for name-swapping fix -->
                    <div style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                        <input type="file" name="vehicle_photo" id="edit_vehicle_photo_file" accept="image/*"
                            onchange="updateEditPreview(this, 'vehicle')">
                        <input type="file" id="edit_vehicle_photo_camera" accept="image/*" capture="environment"
                            onchange="transferEditCameraFile(this, 'vehicle')">
                    </div>

                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <label for="edit_vehicle_photo_camera" id="edit_vehicle_camera_label" class="btn"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 10px; flex: 1; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">
                            📷 Camera
                        </label>
                        <label for="edit_vehicle_photo_file" class="btn"
                            style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 10px; flex: 1; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">
                            📁 Gallery
                        </label>
                    </div>
                    <span id="edit_vehicle_photo_name"
                        style="font-size: 11px; color: #10b981; display: block; font-weight: 600;"></span>
                    <div id="edit_vehicle_preview_container" style="margin-top: 10px; display: none;">
                        <img id="edit_vehicle_preview_img" src=""
                            style="max-width: 100%; max-height: 150px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <?php if ($entry['vehicle_photo_url']): ?>
                        <small style="display: block; margin-top: 5px;">Existing: <a
                                href="<?php echo $entry['vehicle_photo_url']; ?>" target="_blank" style="color: #3b82f6;">View
                                Current Photo</a></small>
                        <?php
                    endif; ?>
                </div>

                <!-- Bill Photo -->
                <div class="form-group"
                    style="background: #f0fdf4; padding: 15px; border-radius: 12px; border: 2px dashed #10b981;">
                    <label style="color: #065f46; font-weight: 600; margin-bottom: 10px; display: block;">📄
                        Bill/Challan Photo:</label>

                    <!-- Hidden inputs -->
                    <div style="opacity: 0.1; position: absolute; z-index: -1; width: 1px; height: 1px; overflow: hidden;">
                        <input type="file" name="bill_photo" id="edit_bill_photo_file" accept="image/*"
                            onchange="updateEditPreview(this, 'bill')">
                        <input type="file" id="edit_bill_photo_camera" accept="image/*" capture="environment"
                            onchange="transferEditCameraFile(this, 'bill')">
                    </div>

                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <label for="edit_bill_photo_camera" id="edit_bill_camera_label" class="btn"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px; flex: 1; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">
                            📷 Camera
                        </label>
                        <label for="edit_bill_photo_file" class="btn"
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 10px; flex: 1; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-bottom: 0;">
                            📁 Gallery
                        </label>
                    </div>
                    <span id="edit_bill_photo_name"
                        style="font-size: 11px; color: #10b981; display: block; font-weight: 600;"></span>
                    <div id="edit_bill_preview_container" style="margin-top: 10px; display: none;">
                        <img id="edit_bill_preview_img" src=""
                            style="max-width: 100%; max-height: 150px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <?php if ($entry['bill_photo_url']): ?>
                        <small style="display: block; margin-top: 5px;">Existing: <a
                                href="<?php echo $entry['bill_photo_url']; ?>" target="_blank" style="color: #10b981;">View
                                Current Photo</a></small>
                        <?php
                    endif; ?>
                </div>
            </div>

            <script>
                function updateEditPreview(input, type) {
                    if (input.files && input.files[0]) {
                        // RELIABLE WEBVIEW FIX: Swap names back
                        const name = type === 'vehicle' ? 'vehicle_photo' : 'bill_photo';
                        input.name = name;
                        const cameraInput = document.getElementById('edit_' + type + '_photo_camera');
                        if (cameraInput) cameraInput.name = "";

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const container = document.getElementById('edit_' + type + '_preview_container');
                            const img = document.getElementById('edit_' + type + '_preview_img');
                            const nameSpan = document.getElementById('edit_' + type + '_photo_name');

                            img.src = e.target.result;
                            container.style.display = 'block';
                            nameSpan.textContent = '✓ Photo Selected';
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function transferEditCameraFile(cameraInput, type) {
                    if (cameraInput.files && cameraInput.files[0]) {
                        // RELIABLE WEBVIEW FIX: Swap names
                        const name = type === 'vehicle' ? 'vehicle_photo' : 'bill_photo';
                        const fileInput = document.getElementById('edit_' + type + '_photo_file');
                        cameraInput.name = name;
                        fileInput.name = "";

                        // Update preview
                        updateEditPreview(cameraInput, type);

                        // Visual feedback
                        const label = document.getElementById('edit_' + type + '_camera_label');
                        label.style.background = '#10b981';
                        label.innerHTML = '✅ Photo Captured';
                    }
                }

                // Intercept camera labels for desktop webcam
                document.addEventListener('DOMContentLoaded', function () {
                    const types = ['vehicle', 'bill'];
                    types.forEach(function (type) {
                        const label = document.getElementById('edit_' + type + '_camera_label');
                        if (label) {
                            label.addEventListener('click', function (e) {
                                if (!detectMobileForWebcam()) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (typeof openWebcamCapture === 'function') {
                                        openWebcamCapture('edit_' + type + '_photo_file', 'updateEditPreviewWrapper_' + type);
                                    }
                                }
                            });
                        }
                    });
                });

                // Wrapper for webcam callbacks
                function updateEditPreviewWrapper_vehicle(input) { updateEditPreview(input, 'vehicle'); }
                function updateEditPreviewWrapper_bill(input) { updateEditPreview(input, 'bill'); }

                // Sync Transporter ID from datalist
                document.getElementById('edit_transporter_name')?.addEventListener('input', function () {
                    const val = this.value;
                    const opts = document.querySelectorAll('#edit_transporter_list option');
                    let foundId = null;
                    opts.forEach(o => { if (o.value === val) foundId = o.getAttribute('data-id'); });
                    const hidden = document.getElementById('edit_transporter_id_hidden');
                    if (hidden) hidden.value = foundId || '';
                });

                // Sync Purpose ID from datalist
                document.getElementById('edit_purpose_name')?.addEventListener('input', function () {
                    const val = this.value;
                    const opts = document.querySelectorAll('#edit_purpose_list option');
                    let foundId = null;
                    opts.forEach(o => { if (o.value === val) foundId = o.getAttribute('data-id'); });
                    const hidden = document.getElementById('edit_purpose_id_hidden');
                    if (hidden) hidden.value = foundId || '';
                });

                // Validate manual items commit on edit form submit
                document.getElementById('editInwardForm')?.addEventListener('submit', function (e) {
                    if (typeof validateManualItemsCommit === 'function' && !validateManualItemsCommit()) {
                        e.preventDefault();
                    }
                });
            </script>

            <?php if (!empty($entry['qr_code_data'])): ?>
                <div class="card" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 15px;">QR Code Data</h3>
                    <div class="form-group">
                        <label>QR Code Data</label>
                        <textarea name="qr_code_data"
                            rows="6"><?php echo htmlspecialchars($entry['qr_code_data']); ?></textarea>
                    </div>
                </div>
                <?php
            endif; ?>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="update_inward" class="btn btn-primary"
                    style="flex: 1; padding: 12px 20px; font-size: 16px; font-weight: 600;">
                    💾 Update Entry
                </button>
                <a href="?page=details&id=<?php echo $entry['id']; ?>" class="btn btn-secondary"
                    style="padding: 12px 20px; font-size: 16px; font-weight: 600; text-decoration: none; text-align: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php
    // ==================== EDIT OUTWARD ENTRY ====================
elseif ($page == 'edit-outward'):

    // Check permission
    if (!hasPermission('actions.edit_record')) {
        echo "<div class='container'><div class='alert alert-error'>Access denied! You do not have permission to edit records.</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        echo "<div class='container'><div class='alert alert-error'>Invalid entry ID!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $outward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM truck_outward WHERE id = $id"));
    if (!$outward) {
        echo "<div class='container'><div class='alert alert-error'>Outward entry not found!</div><a href='?page=inside' class='btn btn-secondary'>Back</a></div>";
        exit;
    }

    $inward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM truck_inward WHERE id = {$outward['inward_id']}"));
    $checklist = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vehicle_outgoing_checklist WHERE inward_id = {$outward['inward_id']} LIMIT 1"));
    
    // Parse documents JSON
    $saved_docs = [];
    if ($checklist && !empty($checklist['documents_json'])) {
        $decoded = json_decode($checklist['documents_json'], true);
        if (is_array($decoded)) {
            foreach($decoded as $d) {
                $saved_docs[$d['type']] = ['status' => $d['status'], 'remarks' => $d['remarks'] ?? ''];
            }
        }
    }

    // Get customers for dropdown
    $customers_result = mysqli_query($conn, "SELECT id, customer_name FROM customer_master WHERE is_active = 1 ORDER BY customer_name");
    ?>
    <div class="container">
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-error">
                <?php echo $error_msg; ?>
            </div>
            <?php
        endif; ?>

        <div
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px;">✏️</div>
                <div>
                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">Edit Outward Entry</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Vehicle:
                        <?php echo htmlspecialchars($inward['vehicle_number']); ?>
                    </p>
                </div>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $outward['id']; ?>">
            <input type="hidden" name="inward_id" value="<?php echo $outward['inward_id']; ?>">

            <div class="card" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                    <div style="background: #10b981; color: white; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">1</div>
                    <h3 style="margin: 0;">Outward Basic Details</h3>
                </div>

                <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Outward Date & Time</label>
                        <input type="datetime-local" name="outward_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($outward['outward_datetime'] ?? 'now')); ?>" 
                               style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db;" required>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Status</label>
                        <select name="outgoing_status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db;">
                            <?php 
                            $curr_s = $outgoing_checklist['status'] ?? 'completed';
                            foreach(['draft'=>'Draft','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v)
                                echo "<option value='$k' ".($curr_s==$k?'selected':'').">$v</option>";
                            ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 180px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Checklist Doc ID</label>
                        <input type="text" name="outgoing_doc_id" value="<?php echo htmlspecialchars($outgoing_checklist['document_id'] ?? 'VCPL/LOG/FR/02'); ?>" 
                               style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db;">
                    </div>
                </div>


                <div class="form-group">
                    <label>Outward Remarks</label>
                    <textarea name="outward_remarks" rows="3"><?php echo htmlspecialchars($outward['outward_remarks']); ?></textarea>
                </div>
            </div>

            <!-- OUT-GOING CHECK SECTION -->
            <div class="card" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                    <div style="background: #8b5cf6; color: white; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                    <h3 style="margin: 0;">Out-going Checklist (VCPL/LOG/FR/02)</h3>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Reporting Date & Time</label>
                        <input type="datetime-local" name="outgoing_reporting_datetime"
                            value="<?php echo $checklist && $checklist['reporting_datetime'] ? date('Y-m-d\TH:i', strtotime($checklist['reporting_datetime'])) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="outgoing_customer_id" id="outgoing_customer_id" onchange="const sel = this.options[this.selectedIndex]; document.getElementById('outgoing_customer_name').value = sel.text;">
                            <option value="">Select Customer</option>
                            <?php
                            if ($customers_result) {
                                mysqli_data_seek($customers_result, 0);
                                while ($row = mysqli_fetch_assoc($customers_result)): ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($checklist && $checklist['customer_id'] == $row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['customer_name']); ?>
                                    </option>
                                <?php endwhile;
                            } ?>
                        </select>
                        <input type="hidden" name="outgoing_customer_name" id="outgoing_customer_name" value="<?php echo htmlspecialchars($checklist['customer_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Destination</label>
                    <input type="text" name="outgoing_destination" value="<?php echo htmlspecialchars($checklist['destination'] ?? ''); ?>" placeholder="Destination">
                </div>

                <!-- Observations -->
                <?php
                $obs_fields = [
                    'tarpaulin_condition' => 'Tarpaulin Condition / Method of Lying',
                    'wooden_blocks_used' => 'Use of "L" Angle / Wooden Blocks',
                    'rope_tightening' => 'Rope Tightening',
                    'sealing' => 'Sealing Check'
                ];
                foreach ($obs_fields as $key => $lbl): ?>
                    <div style="margin-top: 15px; border: 1px solid #f1f5f9; border-radius: 10px; padding: 12px; background: #fafafa;">
                        <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 8px;"><?php echo $lbl; ?></label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 10px;">Observation</label>
                                <select name="<?php echo $key; ?>_obs">
                                    <option value="">Select</option>
                                    <?php foreach(['OK','NOT OK','NA'] as $v) echo "<option value='$v' ".($checklist && $checklist[$key.'_obs']==$v ? 'selected' : '').">$v</option>"; ?>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 10px;">Action if NOT</label>
                                <input type="text" name="<?php echo $key; ?>_action" value="<?php echo htmlspecialchars($checklist[$key.'_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label style="font-size: 10px;">Remarks</label>
                                <input type="text" name="<?php echo $key; ?>_remarks" value="<?php echo htmlspecialchars($checklist[$key.'_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label>Number of Seals</label>
                        <input type="number" name="number_of_seals" value="<?php echo $checklist['number_of_seals'] ?? 0; ?>">
                    </div>
                    <div class="form-group">
                        <label>Sealing Method</label>
                        <input type="text" name="sealing_method" value="<?php echo htmlspecialchars($checklist['sealing_method'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Sealing Done By</label>
                        <input type="text" name="sealing_done_by" value="<?php echo htmlspecialchars($checklist['sealing_done_by'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="card" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                    <div style="background: #3b82f6; color: white; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                    <h3 style="margin: 0;">Document Check (CE Invoice, Way Bill, LR Copy, TREM Card)</h3>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <?php
                    $out_doc_items = [
                        'ce_invoice' => 'CE Invoice',
                        'way_bill' => 'Way Bill',
                        'lr_copy' => 'LR Copy',
                        'trem_card' => 'TREM Card'
                    ];
                    foreach ($out_doc_items as $k => $lbl): 
                        $curr_status = $saved_docs[$k]['status'] ?? '';
                        $curr_rem = $saved_docs[$k]['remarks'] ?? '';
                    ?>
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px;">
                            <div style="font-weight:700; margin-bottom:8px; font-size: 13px;"><?php echo htmlspecialchars($lbl); ?></div>
                            <select name="out_doc_<?php echo $k; ?>_status" style="width:100%; border-radius:8px; margin-bottom: 8px;">
                                <option value="">Select</option>
                                <?php foreach(['OK','NOT OK','NA'] as $v) echo "<option value='$v' ".($curr_status==$v ? 'selected' : '').">$v</option>"; ?>
                            </select>
                            <input type="text" name="out_doc_<?php echo $k; ?>_remarks" value="<?php echo htmlspecialchars($curr_rem); ?>" placeholder="Remarks" style="width:100%; border-radius:8px;">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Final Section -->
            <div class="card" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                    <div style="background: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">4</div>
                    <h3 style="margin: 0;">Leaving Details & Signatures</h3>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Date & Time of Leaving</label>
                        <input type="datetime-local" name="leaving_datetime" 
                            value="<?php echo $checklist && $checklist['leaving_datetime'] ? date('Y-m-d\TH:i', strtotime($checklist['leaving_datetime'])) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Naaviq Trip Started?</label>
                        <select name="naaviq_trip_started">
                            <option value="">Select</option>
                            <option value="Yes" <?php echo ($checklist && $checklist['naaviq_trip_started']=='Yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="No" <?php echo ($checklist && $checklist['naaviq_trip_started']=='No') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label>Naaviq Action (if NOT)</label>
                        <input type="text" name="naaviq_trip_action" value="<?php echo htmlspecialchars($checklist['naaviq_trip_action'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Naaviq Remarks</label>
                        <input type="text" name="naaviq_trip_remarks" value="<?php echo htmlspecialchars($checklist['naaviq_trip_remarks'] ?? ''); ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                    <div class="form-group">
                        <label style="font-size: 11px;">Driver Sign</label>
                        <input type="text" name="out_driver_signature" value="<?php echo htmlspecialchars($checklist['driver_signature'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px;">Transporter Sign</label>
                        <input type="text" name="out_transporter_signature" value="<?php echo htmlspecialchars($checklist['transporter_signature'] ?? ''); ?>">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                    <div class="form-group">
                        <label style="font-size: 11px;">Security Sign</label>
                        <input type="text" name="out_security_signature" value="<?php echo htmlspecialchars($checklist['security_signature'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px;">Logistics Sign</label>
                        <input type="text" name="out_logistic_signature" value="<?php echo htmlspecialchars($checklist['logistic_signature'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px; margin-bottom: 50px;">
                <button type="submit" name="update_outward" class="btn btn-success"
                    style="flex: 1; padding: 15px 20px; font-size: 18px; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                    💾 SAVE ALL CHANGES
                </button>
                <a href="?page=outward-details&id=<?php echo $outward['inward_id']; ?>" class="btn btn-secondary"
                    style="padding: 15px 20px; font-size: 16px; font-weight: 600; text-decoration: none; text-align: center; border-radius: 12px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php
    // ==================== DRIVER DETAIL ====================
elseif ($page == 'driver-detail'):

    $id = $_GET['id'];
    $driver = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT dm.*, tm.transporter_name 
        FROM driver_master dm
        LEFT JOIN transporter_master tm ON dm.transporter_id = tm.id
        WHERE dm.id = $id
    "));

    if (!$driver) {
        echo "<div class='container'><div class='alert alert-error'>Driver not found!</div></div>";
        exit;
    }

    // Get trip history
    $trips = mysqli_query($conn, "
        SELECT * FROM truck_inward 
        WHERE driver_mobile = '{$driver['mobile']}' 
        ORDER BY inward_datetime DESC 
        LIMIT 10
    ");
    ?>
    <div class="container">
        <div class="card">
            <h2>👨‍✈️ Driver Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: start;">
                    <?php if (!empty($driver['photo'])): ?>
                        <div>
                            <img src="<?php echo $driver['photo']; ?>"
                                style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 3px solid #3b82f6; cursor: pointer;"
                                onclick="window.open(this.src, '_blank')" title="Click to view full size">
                        </div>
                        <?php
                    endif; ?>
                    <div>
                        <h3 style="font-size: 24px; margin-bottom: 10px; color: #1f2937;">
                            <?php echo $driver['driver_name']; ?>
                        </h3>
                        <p style="color: #666; font-size: 16px; margin: 5px 0;">
                            <strong>📱 Mobile:</strong>
                            <?php echo $driver['mobile']; ?>
                        </p>
                        <?php if ($driver['transporter_name']): ?>
                            <p style="color: #666; font-size: 16px; margin: 5px 0;">
                                <strong>🚛 Transporter:</strong>
                                <?php echo $driver['transporter_name']; ?>
                            </p>
                            <?php
                        endif; ?>
                    </div>
                </div>
            </div>

            <table style="margin: 0;">
                <tr>
                    <td style="font-weight: 600; width: 200px;">Driver Name</td>
                    <td>
                        <?php echo $driver['driver_name']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Mobile Number</td>
                    <td>
                        <?php echo $driver['mobile']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">License Number</td>
                    <td>
                        <?php echo $driver['license_number'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">License Expiry</td>
                    <td>
                        <?php
                        if ($driver['license_expiry']) {
                            $is_expired = strtotime($driver['license_expiry']) < time();
                            echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                            echo date('d F, Y', strtotime($driver['license_expiry']));
                            if ($is_expired)
                                echo ' (Expired)';
                            echo '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Transporter</td>
                    <td>
                        <?php echo $driver['transporter_name'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Status</td>
                    <td>
                        <span
                            class="badge badge-<?php echo (isset($driver['is_active']) && $driver['is_active']) ? 'success' : 'danger'; ?>">
                            <?php echo (isset($driver['is_active']) && $driver['is_active']) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                </tr>
            </table>

            <?php if (!empty($driver['license_photo'])): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                    <strong style="font-size: 16px; color: #1f2937;">🪪 License Photo:</strong><br>
                    <img src="<?php echo $driver['license_photo']; ?>"
                        style="max-width: 100%; max-height: 400px; border-radius: 8px; margin-top: 10px; border: 3px solid #8b5cf6; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                        onclick="window.open(this.src, '_blank')" title="Click to open in new tab">
                </div>
                <?php
            endif; ?>

            <?php if (mysqli_num_rows($trips) > 0): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">🚛 Recent Trip History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Entry #</th>
                                <th>Vehicle</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trip = mysqli_fetch_assoc($trips)): ?>
                                <tr style="cursor: pointer;"
                                    onclick="window.location.href='?page=details&id=<?php echo intval($trip['id']); ?>'">
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($trip['inward_date'])); ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['entry_number']; ?>
                                    </td>
                                    <td><strong>
                                            <?php echo $trip['vehicle_number']; ?>
                                        </strong></td>
                                    <td>
                                        <?php echo $trip['from_location']; ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['to_location']; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo $trip['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                            <?php echo strtoupper($trip['status']); ?>
                                        </span>
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

        <button onclick="goBack()" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s; width: 100%;"
            onmouseover="this.style.background='#4b5563';" onmouseout="this.style.background='#6b7280';">
            ← Back
        </button>
        <script>
            function goBack() {
                // Check if there's a previous page in history
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    // Fallback to drivers page if no history
                    window.location.href = '?page=admin&master=drivers';
                }
            }
        </script>
    </div>

    <?php
    // ==================== VEHICLE DETAIL ====================
elseif ($page == 'vehicle-detail'):

    $id = $_GET['id'];
    $vehicle = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT vm.*, tm.transporter_name
        FROM vehicle_master vm
        LEFT JOIN transporter_master tm ON vm.transporter_id = tm.id
        WHERE vm.id = $id
    "));

    if (!$vehicle) {
        echo "<div class='container'><div class='alert alert-error'>Vehicle not found!</div></div>";
        exit;
    }

    // Get all assigned drivers for this vehicle
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_drivers'");
    $assigned_drivers = [];
    if (mysqli_num_rows($check_table) > 0) {
        $drivers_result = mysqli_query($conn, "
            SELECT vd.is_primary, d.id, d.driver_name, d.mobile as driver_mobile, d.photo
            FROM vehicle_drivers vd
            INNER JOIN driver_master d ON vd.driver_id = d.id
            WHERE vd.vehicle_id = $id AND d.is_active = 1
            ORDER BY vd.is_primary DESC, d.driver_name
        ");
        while ($drv = mysqli_fetch_assoc($drivers_result)) {
            $assigned_drivers[] = $drv;
        }
    }

    // Get trip history
    $trips = mysqli_query($conn, "
        SELECT * FROM truck_inward 
        WHERE vehicle_number = '{$vehicle['vehicle_number']}' 
        ORDER BY inward_datetime DESC 
        LIMIT 10
    ");

    // Helper function to check if file exists
    function fileExists($path)
    {
        if (empty($path))
            return false;
        // Handle both relative and absolute paths
        if (preg_match('#^https?://#', $path)) {
            return false; // Can't check remote URLs
        }
        $file_path = dirname(__DIR__) . '/' . ltrim($path, '/');
        return file_exists($file_path);
    }

    // Check which driver photos exist - display if path exists in database
    foreach ($assigned_drivers as &$driver) {
        $driver['photo_exists'] = !empty($driver['photo']);
    }
    unset($driver);

    // Check which vehicle document photos exist - display if path exists in database
    $vehicle['rc_photo_exists'] = !empty($vehicle['rc_photo']);
    $vehicle['insurance_photo_exists'] = !empty($vehicle['insurance_photo']);
    $vehicle['pollution_photo_exists'] = !empty($vehicle['pollution_photo']);
    $vehicle['fitness_photo_exists'] = !empty($vehicle['fitness_photo']);
    $vehicle['permit_photo_exists'] = !empty($vehicle['permit_photo']);
    ?>
    <div class="container">
        <div class="card">
            <h2>🚛 Vehicle Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 28px; margin-bottom: 5px;">
                    <?php echo $vehicle['vehicle_number']; ?>
                </h3>
                <p style="color: #666;">
                    <?php echo $vehicle['maker'] ? $vehicle['maker'] . ' ' . $vehicle['model'] : 'Make/Model not specified'; ?>
                </p>
            </div>

            <table style="margin: 0;">
                <tr>
                    <td style="font-weight: 600; width: 200px;">Vehicle Number</td>
                    <td><strong style="font-size: 18px;">
                            <?php echo $vehicle['vehicle_number']; ?>
                        </strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Transporter</td>
                    <td><strong style="color: #4f46e5; font-size: 18px;">
                            <?php echo $vehicle['transporter_name'] ?: '<span style="color:#94a3b8">Untagged</span>'; ?>
                        </strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; vertical-align: top;">Assigned Drivers</td>
                    <td>
                        <?php if (count($assigned_drivers) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <?php foreach ($assigned_drivers as $driver): ?>
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; padding: 10px; background: <?php echo $driver['is_primary'] ? '#dbeafe' : '#f9fafb'; ?>; border-radius: 8px; border: 1px solid <?php echo $driver['is_primary'] ? '#3b82f6' : '#e5e7eb'; ?>;">
                                        <?php if (!empty($driver['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($driver['photo']); ?>"
                                                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid <?php echo $driver['is_primary'] ? '#3b82f6' : '#9ca3af'; ?>;"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <?php
                                        else: ?>
                                            <div
                                                style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $driver['is_primary'] ? '#3b82f6' : '#9ca3af'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                                <?php echo strtoupper(substr($driver['driver_name'], 0, 1)); ?>
                                            </div>
                                            <?php
                                        endif; ?>
                                        <div style="flex: 1;">
                                            <strong>
                                                <?php echo $driver['driver_name']; ?>
                                            </strong>
                                            <?php if ($driver['is_primary']): ?>
                                                <span
                                                    style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px; font-weight: 600;">PRIMARY</span>
                                                <?php
                                            endif; ?>
                                            <br>
                                            <small style="color: #666;">
                                                <?php echo $driver['driver_mobile']; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php
                                endforeach; ?>
                            </div>
                            <?php
                        else: ?>
                            <span style="color: #9ca3af;">No drivers assigned</span>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Maker / Model</td>
                    <td>
                        <?php echo $vehicle['maker'] ? $vehicle['maker'] . ' / ' . $vehicle['model'] : '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Fuel Type</td>
                    <td>
                        <?php echo $vehicle['fuel_type'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Registration Validity</td>
                    <td>
                        <?php echo $vehicle['registration_validity'] ? date('d F, Y', strtotime($vehicle['registration_validity'])) : '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">RC Owner Name</td>
                    <td>
                        <?php echo $vehicle['rc_owner_name'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Fitness Validity</td>
                    <td>
                        <?php
                        if ($vehicle['fitness_validity']) {
                            $is_expired = strtotime($vehicle['fitness_validity']) < time();
                            echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                            echo date('d F, Y', strtotime($vehicle['fitness_validity']));
                            if ($is_expired)
                                echo ' (Expired)';
                            echo '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Pollution Validity</td>
                    <td>
                        <?php
                        if ($vehicle['pollution_validity']) {
                            $is_expired = strtotime($vehicle['pollution_validity']) < time();
                            echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                            echo date('d F, Y', strtotime($vehicle['pollution_validity']));
                            if ($is_expired)
                                echo ' (Expired)';
                            echo '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Insurance Validity</td>
                    <td>
                        <?php
                        if ($vehicle['insurance_validity']) {
                            $is_expired = strtotime($vehicle['insurance_validity']) < time();
                            echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                            echo date('d F, Y', strtotime($vehicle['insurance_validity']));
                            if ($is_expired)
                                echo ' (Expired)';
                            echo '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Permit Validity</td>
                    <td>
                        <?php
                        if (isset($vehicle['permit_validity']) && $vehicle['permit_validity']) {
                            $is_expired = strtotime($vehicle['permit_validity']) < time();
                            echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                            echo date('d F, Y', strtotime($vehicle['permit_validity']));
                            if ($is_expired)
                                echo ' (Expired)';
                            echo '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <?php if (mysqli_num_rows($trips) > 0): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">🚛 Recent Trip History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Entry #</th>
                                <th>Driver</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trip = mysqli_fetch_assoc($trips)): ?>
                                <tr style="cursor: pointer;"
                                    onclick="window.location.href='?page=details&id=<?php echo intval($trip['id']); ?>'">
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($trip['inward_date'])); ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['entry_number']; ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['driver_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['from_location']; ?>
                                    </td>
                                    <td>
                                        <?php echo $trip['to_location']; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo $trip['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                            <?php echo strtoupper($trip['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            endif; ?>

            <!-- Document Photos Section -->
            <?php if ($vehicle['rc_photo_exists'] || $vehicle['insurance_photo_exists'] || $vehicle['pollution_photo_exists'] || $vehicle['fitness_photo_exists']): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">📄 Document Photos</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">

                        <?php if ($vehicle['rc_photo_exists']): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 8px;">🆔 RC Certificate:</strong>
                                <img src="<?php echo htmlspecialchars($vehicle['rc_photo']); ?>"
                                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #3b82f6; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')" title="Click to view full size"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EImage not found%3C/text%3E%3C/svg%3E';">
                            </div>
                            <?php
                        endif; ?>

                        <?php if ($vehicle['insurance_photo_exists']): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 8px;">🛡️ Insurance:</strong>
                                <img src="<?php echo htmlspecialchars($vehicle['insurance_photo']); ?>"
                                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')" title="Click to view full size"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EImage not found%3C/text%3E%3C/svg%3E';">
                            </div>
                            <?php
                        endif; ?>

                        <?php if ($vehicle['pollution_photo_exists']): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 8px;">🌿 Pollution Certificate:</strong>
                                <img src="<?php echo htmlspecialchars($vehicle['pollution_photo']); ?>"
                                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #f59e0b; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')" title="Click to view full size"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EImage not found%3C/text%3E%3C/svg%3E';">
                            </div>
                            <?php
                        endif; ?>

                        <?php if ($vehicle['fitness_photo_exists']): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 8px;">✅ Fitness Certificate:</strong>
                                <img src="<?php echo htmlspecialchars($vehicle['fitness_photo']); ?>"
                                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #8b5cf6; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')" title="Click to view full size"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EImage not found%3C/text%3E%3C/svg%3E';">
                            </div>
                            <?php
                        endif; ?>

                        <?php if ($vehicle['permit_photo_exists']): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 8px;">📋 Permit Certificate:</strong>
                                <img src="<?php echo htmlspecialchars($vehicle['permit_photo']); ?>"
                                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #ec4899; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')" title="Click to view full size"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EImage not found%3C/text%3E%3C/svg%3E';">
                            </div>
                            <?php
                        endif; ?>

                    </div>
                </div>
                <?php
            endif; ?>
        </div>

        <button onclick="goBack()" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s; width: 100%;"
            onmouseover="this.style.background='#4b5563';" onmouseout="this.style.background='#6b7280';">
            ← Back
        </button>
        <script>
            function goBack() {
                // Check if there's a previous page in history
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    // Fallback to vehicles page if no history
                    window.location.href = '?page=admin&master=vehicles';
                }
            }
        </script>
    </div>

    <?php
    // ==================== TRANSPORTER DETAIL ====================
elseif ($page == 'transporter-detail'):

    $id = $_GET['id'];
    $transporter = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transporter_master WHERE id = $id"));

    if (!$transporter) {
        echo "<div class='container'><div class='alert alert-error'>Transporter not found!</div></div>";
        exit;
    }

    // Get associated drivers
    $drivers = mysqli_query($conn, "
        SELECT * FROM driver_master 
        WHERE transporter_id = $id AND is_active = 1
        ORDER BY driver_name
    ");

    // Get trip history
    $trips = mysqli_query($conn, "
        SELECT * FROM truck_inward 
        WHERE transporter_id = $id 
        ORDER BY inward_datetime DESC 
        LIMIT 10
    ");
    ?>
    <div class="container">
        <div class="card">
            <h2>🏢 Transporter Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 24px; margin-bottom: 5px;">
                    <?php echo $transporter['transporter_name']; ?>
                </h3>
                <?php if ($transporter['contact_person']): ?>
                    <p style="color: #666;">Contact:
                        <?php echo $transporter['contact_person']; ?>
                    </p>
                    <?php
                endif; ?>
            </div>

            <table style="margin: 0; width: 100%;">
                <tr>
                    <td style="font-weight: 600; width: 200px; word-break: break-word; overflow-wrap: break-word;">
                        Transporter Name</td>
                    <td style="word-break: break-word; overflow-wrap: break-word;"><strong style="font-size: 18px;">
                            <?php echo htmlspecialchars($transporter['transporter_name']); ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">Contact Person
                    </td>
                    <td style="word-break: break-word; overflow-wrap: break-word;">
                        <?php echo htmlspecialchars($transporter['contact_person'] ?: '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">Mobile</td>
                    <td style="word-break: break-word; overflow-wrap: break-word;">
                        <?php echo htmlspecialchars($transporter['mobile'] ?: '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">Email</td>
                    <td style="word-break: break-word; overflow-wrap: break-word;">
                        <?php echo htmlspecialchars($transporter['email'] ?: '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">GST Number</td>
                    <td style="word-break: break-word; overflow-wrap: break-word;">
                        <?php echo htmlspecialchars($transporter['gst_number'] ?: '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">Address</td>
                    <td style="word-break: break-word; overflow-wrap: break-word;">
                        <?php echo $transporter['address'] ? nl2br(htmlspecialchars($transporter['address'])) : '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; word-break: break-word; overflow-wrap: break-word;">Status</td>
                    <td>
                        <span
                            class="badge badge-<?php echo (isset($transporter['is_active']) && $transporter['is_active']) ? 'success' : 'danger'; ?>">
                            <?php echo (isset($transporter['is_active']) && $transporter['is_active']) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                </tr>
            </table>

            <?php if (mysqli_num_rows($drivers) > 0): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">👨‍✈️ Associated Drivers (
                        <?php echo mysqli_num_rows($drivers); ?>)
                    </h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Driver Name</th>
                                    <th>Mobile</th>
                                    <th>License Number</th>
                                    <th>License Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($driver = mysqli_fetch_assoc($drivers)): ?>
                                    <tr style="cursor: pointer;"
                                        onclick="window.location.href='?page=driver-detail&id=<?php echo $driver['id']; ?>'">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px; min-width: 150px;">
                                                <?php if ($driver['photo']): ?>
                                                    <img src="<?php echo $driver['photo']; ?>"
                                                        style="width: 30px; height: 30px; object-fit: cover; border-radius: 50%; border: 2px solid #3b82f6; flex-shrink: 0;">
                                                    <?php
                                                endif; ?>
                                                <strong style="word-break: break-word; overflow-wrap: break-word;">
                                                    <?php echo htmlspecialchars($driver['driver_name']); ?>
                                                </strong>
                                            </div>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 120px;">
                                            <?php echo htmlspecialchars($driver['mobile']); ?>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 150px;">
                                            <?php echo htmlspecialchars($driver['license_number'] ?: '-'); ?>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <?php
                                            if ($driver['license_expiry']) {
                                                $is_expired = strtotime($driver['license_expiry']) < time();
                                                echo '<span class="badge badge-' . ($is_expired ? 'danger' : 'success') . '">';
                                                echo date('d/m/Y', strtotime($driver['license_expiry']));
                                                echo '</span>';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endif; ?>

            <?php if (mysqli_num_rows($trips) > 0): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">🚛 Recent Trip History</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Entry #</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($trip = mysqli_fetch_assoc($trips)): ?>
                                    <tr style="cursor: pointer;"
                                        onclick="window.location.href='?page=details&id=<?php echo intval($trip['id']); ?>'">
                                        <td style="white-space: nowrap;">
                                            <?php echo date('d/m/Y', strtotime($trip['inward_date'])); ?>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 100px;">
                                            <?php echo htmlspecialchars($trip['entry_number']); ?>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 120px;">
                                            <strong>
                                                <?php echo htmlspecialchars($trip['vehicle_number']); ?>
                                            </strong>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 150px;">
                                            <?php echo htmlspecialchars($trip['driver_name']); ?>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 150px;">
                                            <?php echo htmlspecialchars($trip['from_location']); ?>
                                        </td>
                                        <td style="word-break: break-word; overflow-wrap: break-word; max-width: 150px;">
                                            <?php echo htmlspecialchars($trip['to_location']); ?>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <span
                                                class="badge badge-<?php echo $trip['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                                <?php echo strtoupper($trip['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endif; ?>
        </div>

        <a href="?page=admin&master=transporters" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10;">← Back
            to
            Transporters</a>
    </div>

    <?php
    // ==================== USER DETAIL ====================
elseif ($page == 'user-detail'):

    $id = $_GET['id'];
    $m_db = MASTER_DB_NAME;
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $m_db.user_master WHERE id = $id"));

    if (!$user) {
        echo "<div class='container'><div class='alert alert-error'>User not found!</div></div>";
        exit;
    }

    // Get recent activities (entries created by this user)
    $activities = mysqli_query($conn, "
        SELECT * FROM truck_inward 
        WHERE inward_by = $id 
        ORDER BY inward_datetime DESC 
        LIMIT 10
    ");
    ?>
    <div class="container">
        <div class="card">
            <h2>👤 User Details</h2>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: start;">
                    <?php if (!empty($user['photo'])): ?>
                        <div>
                            <img src="<?php echo $user['photo']; ?>"
                                style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 3px solid #3b82f6; cursor: pointer;"
                                onclick="window.open(this.src, '_blank')" title="Click to view full size">
                        </div>
                        <?php
                    endif; ?>
                    <div>
                        <h3 style="font-size: 24px; margin-bottom: 10px; color: #1f2937;">
                            <?php echo $user['full_name']; ?>
                        </h3>
                        <p style="color: #666; font-size: 16px; margin: 5px 0;">
                            <strong>👤 Username:</strong>
                            <?php echo $user['username']; ?>
                        </p>
                        <p style="color: #666; font-size: 16px; margin: 5px 0;">
                            <strong>🎭 Role:</strong> <span class="badge badge-info">
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <table style="margin: 0;">
                <tr>
                    <td style="font-weight: 600; width: 200px;">Username</td>
                    <td>
                        <?php echo $user['username']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Full Name</td>
                    <td><strong>
                            <?php echo $user['full_name']; ?>
                        </strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Role</td>
                    <td><span class="badge badge-info">
                            <?php echo strtoupper($user['role']); ?>
                        </span></td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Email</td>
                    <td>
                        <?php echo $user['email'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Mobile</td>
                    <td>
                        <?php echo $user['mobile'] ?: '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Status</td>
                    <td>
                        <span
                            class="badge badge-<?php echo (isset($user['is_active']) && $user['is_active']) ? 'success' : 'danger'; ?>">
                            <?php echo (isset($user['is_active']) && $user['is_active']) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Created At</td>
                    <td>
                        <?php echo date('d F, Y h:i A', strtotime($user['created_at'])); ?>
                    </td>
                </tr>
            </table>

            <?php if (mysqli_num_rows($activities) > 0): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">📋 Recent Activity (Entries Created)</h3>
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table style="min-width: 600px;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Entry #</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($activity = mysqli_fetch_assoc($activities)): ?>
                                    <tr style="cursor: pointer;"
                                        onclick="window.location.href='?page=details&id=<?php echo intval($activity['id']); ?>'">
                                        <td style="white-space: nowrap;">
                                            <?php echo date('d/m/Y', strtotime($activity['inward_date'])); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($activity['entry_number']); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($activity['vehicle_number']); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($activity['driver_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($activity['from_location']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($activity['to_location']); ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $activity['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                                <?php echo strtoupper($activity['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endif; ?>
        </div>

        <a href="?page=admin&master=users" class="btn btn-secondary btn-full"
            style="margin-top: 20px; margin-bottom: 20px; display: block; position: relative; z-index: 10;">← Back
            to
            Users</a>
    </div>

    <?php
    // ==================== QR SCANNER ====================
elseif ($page == 'qr-scanner'):

    ?>
    <div class="container">
        <div class="card">
            <h2>📷 QR Code Scanner</h2>

            <?php if (isset($qr_result)): ?>
                <div class="alert alert-success">
                    ✅ QR Code Scanned Successfully!
                </div>

                <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <strong>Raw Data:</strong>
                    <pre
                        style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($qr_result['raw']); ?></pre>
                </div>

                <?php if ($qr_result['parsed']): ?>
                    <div style="background: #d1fae5; padding: 15px; border-radius: 8px; margin: 15px 0;">
                        <strong>Parsed Data:</strong>
                        <pre
                            style="white-space: pre-wrap; word-wrap: break-word;"><?php echo print_r($qr_result['parsed'], true); ?></pre>
                    </div>
                    <?php
                endif; ?>

                <?php if ($qr_result['items']): ?>
                    <div style="background: #dbeafe; padding: 15px; border-radius: 8px; margin: 15px 0;">
                        <strong>Items Found:</strong>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <?php foreach ($qr_result['items'] as $item): ?>
                                <li>
                                    <?php echo $item['name'] ?? $item['item_name'] ?? 'Unknown'; ?> - Qty:
                                    <?php echo $item['quantity'] ?? $item['qty'] ?? '?'; ?>
                                </li>
                                <?php
                            endforeach; ?>
                        </ul>
                    </div>
                    <?php
                endif; ?>

                <a href="?page=inward" class="btn btn-primary btn-full" style="margin-top: 15px;">
                    Continue to Inward Entry
                </a>
                <?php
            else: ?>
                <p style="margin-bottom: 20px; color: #666;">
                    Scan QR code on bill/challan to auto-fill entry details
                </p>

                <!-- QR Scanner Camera Section -->
                <div class="qr-scanner">
                    <div id="qr-reader" style="width: 100%;"></div>
                    <div class="qr-scanner-controls">
                        <button type="button" id="startScanBtn" onclick="startQRScanner()" class="btn btn-primary">
                            📷 Start Camera Scanner
                        </button>
                        <button type="button" id="stopScanBtn" onclick="stopQRScanner()" class="btn btn-secondary"
                            style="display: none;">
                            ⏹️ Stop Scanner
                        </button>
                    </div>
                    <div id="zoomContainer"
                        style="display:none; margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 8px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #475569;">🔍
                            Camera Zoom Control</label>
                        <input type="range" id="zoomInput"
                            style="width: 100%; height: 8px; border-radius: 4px; background: #e2e8f0; cursor: pointer;">
                    </div>
                    <div id="qrStatus" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none;">
                    </div>
                </div>

                <!-- Manual Input Option -->
                <div class="qr-manual-input">
                    <p style="margin-bottom: 15px; color: #666; text-align: center;">
                        <strong>OR</strong> Enter QR data manually:
                    </p>
                    <form method="POST" id="qrManualForm">
                        <div class="form-group">
                            <label>Paste or Enter QR Data:</label>
                            <textarea name="qr_data" id="qr_data_input" rows="6" placeholder="Paste QR code data here..."
                                required></textarea>
                        </div>

                        <button type="submit" name="process_qr" class="btn btn-primary btn-full">
                            PROCESS QR DATA
                        </button>
                    </form>
                </div>
                <?php
            endif; ?>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isScanning = false;

        function showQRStatus(message, type = 'info') {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.style.display = 'block';
            statusDiv.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'error' ? 'error' : 'info');
            statusDiv.textContent = message;

            if (type === 'success') {
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 3000);
            }
        }

        function startQRScanner() {
            if (isScanning) return;

            // Native Flutter Bridge detection
            if (window.FlutterScanner || window.FlutterScannerChannel) {
                if (!window.FlutterScanner && window.FlutterScannerChannel) {
                    window.FlutterScanner = {
                        postMessage: function (msg) { window.FlutterScannerChannel.postMessage(msg); }
                    };
                }

                window.onNativeScanSuccess = function (decodedText) {
                    onQRCodeScanned(decodedText);
                };

                window.FlutterScanner.postMessage('startScan');
                return;
            }

            const startBtn = document.getElementById('startScanBtn');
            const stopBtn = document.getElementById('stopScanBtn');
            const zoomContainer = document.getElementById('zoomContainer');

            // Check if camera is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showQRStatus('❌ Camera access not supported in this browser. Please use manual input.', 'error');
                return;
            }

            // Clear previous scanner if exists
            if (html5QrCode) {
                html5QrCode.clear();
            }

            html5QrCode = new Html5Qrcode("qr-reader");

            // Determine camera facing mode (prefer back camera on mobile)
            const facingMode = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
                ? { facingMode: "environment" }
                : { facingMode: "user" };

            html5QrCode.start(
                facingMode,
                {
                    fps: 20,
                    qrbox: function (viewfinderWidth, viewfinderHeight) {
                        var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        var boxSize = Math.floor(minEdge * 0.7);
                        return { width: boxSize, height: boxSize };
                    },
                    aspectRatio: 1.0
                },
                (decodedText, decodedResult) => {
                    // Success callback
                    onQRCodeScanned(decodedText);
                },
                (errorMessage) => {
                    // Ignore
                }
            ).then(() => {
                isScanning = true;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
                showQRStatus('📷 Camera started. Point at QR code to scan...', 'info');

                // Try to initialize zoom
                try {
                    const track = html5QrCode.getRunningTrack();
                    const capabilities = track.getCapabilities();
                    if (capabilities.zoom) {
                        zoomContainer.style.display = 'block';
                        const zoomInput = document.getElementById('zoomInput');
                        zoomInput.min = capabilities.zoom.min;
                        zoomInput.max = capabilities.zoom.max;
                        zoomInput.step = capabilities.zoom.step || 0.1;
                        zoomInput.value = capabilities.zoom.min;

                        zoomInput.oninput = function () {
                            const zoomVal = parseFloat(this.value);
                            track.applyConstraints({ advanced: [{ zoom: zoomVal }] });
                        };
                    }
                } catch (e) {
                    console.warn('Zoom not supported:', e);
                }
            }).catch((err) => {
                console.error('Failed to start scanner:', err);
                showQRStatus('❌ Failed to start camera: ' + err, 'error');
                isScanning = false;
            });
        }

        function stopQRScanner() {
            if (!html5QrCode || !isScanning) return;

            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                isScanning = false;
                document.getElementById('startScanBtn').style.display = 'inline-block';
                document.getElementById('stopScanBtn').style.display = 'none';
                showQRStatus('⏹️ Scanner stopped.', 'info');
                setTimeout(() => {
                    document.getElementById('qrStatus').style.display = 'none';
                }, 2000);
            }).catch((err) => {
                console.error('Failed to stop scanner:', err);
            });
        }

        function onQRCodeScanned(decodedText) {
            if (!decodedText || decodedText.trim() === '') return;

            // Check if it's an employee QR code
            try {
                let data = null;
                try {
                    data = JSON.parse(decodedText);
                } catch (e) {
                    data = decodeJWT(decodedText);
                }

                if (data && (data.type === 'employee' || data.employee_id)) {
                    showQRStatus('✅ Employee QR Scanned!', 'success');
                    stopQRScanner();

                    // Redirect to inward page with employee modal open and data filled
                    // We'll use session storage to pass the data
                    sessionStorage.setItem('auto_open_employee', JSON.stringify(data));
                    window.location.href = '?page=inward';
                    return;
                }
            } catch (e) {
                // Not a JSON/JWT QR code, continue with standard processing
            }

            showQRStatus('✅ QR Code scanned successfully! Processing...', 'success');
            stopQRScanner();

            // Auto-fill the manual input and submit
            document.getElementById('qr_data_input').value = decodedText;

            // Submit the form automatically
            setTimeout(() => {
                document.getElementById('qrManualForm').submit();
            }, 500);
        }

        function decodeJWT(token) {
            try {
                const base64Url = token.split('.')[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
                return JSON.parse(jsonPayload);
            } catch (e) {
                return null;
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().catch(() => { });
            }
        });

        // Auto-start scanner on mobile devices if page loads directly
        document.addEventListener('DOMContentLoaded', function () {
            // Check for pending QR data from session storage
            const pendingData = sessionStorage.getItem('pending_qr_data');
            if (pendingData) {
                sessionStorage.removeItem('pending_qr_data');
                const input = document.getElementById('qr_data_input');
                const form = document.getElementById('qrManualForm');
                if (input && form) {
                    input.value = pendingData;
                    showQRStatus('✅ Processing transfered QR data...', 'success');
                    setTimeout(() => form.submit(), 500);
                    return; // Don't auto-start camera if we are processing data
                }
            }

            // Check if we're on a mobile device
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            // Auto-start on mobile after a short delay
            if (isMobile) {
                setTimeout(() => {
                    // Only auto-start if user hasn't interacted yet
                    const startBtn = document.getElementById('startScanBtn');
                    if (startBtn && startBtn.style.display !== 'none') {
                        startQRScanner();
                    }
                }, 1000);
            }
        });
    </script>

    <?php
    // ==================== REPORTS ====================
elseif ($page == 'reports'):

    // Get filter values
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $transporter_filter = isset($_GET['transporter']) ? mysqli_real_escape_string($conn, $_GET['transporter']) : '';
    $driver_filter = isset($_GET['driver']) ? mysqli_real_escape_string($conn, $_GET['driver']) : '';
    $vehicle_filter = isset($_GET['vehicle']) ? mysqli_real_escape_string($conn, $_GET['vehicle']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $department_filter = isset($_GET['department']) ? mysqli_real_escape_string($conn, $_GET['department']) : '';

    // Build WHERE clause
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "inward_date BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "inward_date >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "inward_date <= '$end_date'";
    }
    if ($transporter_filter) {
        $where[] = "transporter_name LIKE '%$transporter_filter%'";
    }
    if ($driver_filter) {
        $where[] = "driver_name LIKE '%$driver_filter%'";
    }
    if ($vehicle_filter) {
        $where[] = "vehicle_number LIKE '%$vehicle_filter%'";
    }
    if ($status_filter) {
        $where[] = "status = '$status_filter'";
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Initialize loading/unloading tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Get filtered entries
    $entries = mysqli_query($conn, "SELECT * FROM truck_inward $where_sql ORDER BY inward_datetime DESC LIMIT 200");

    // Extract all items for the items-received report
    $all_received_items = [];
    if ($entries && mysqli_num_rows($entries) > 0) {
        while ($row = mysqli_fetch_assoc($entries)) {
            $items = !empty($row['items_json']) ? json_decode($row['items_json'], true) : [];
            // Handle double-encoded JS string if necessary
            if (is_string($items)) { $items = json_decode($items, true); }
            
            if (is_array($items)) {
                foreach ($items as $item) {
                    $item['parent_entry_num'] = $row['entry_number'] ?? 'N/A';
                    $item['parent_inward_date'] = $row['inward_date'] ?? '-';
                    $item['parent_vehicle'] = $row['vehicle_number'] ?? '-';
                    $item['parent_transporter'] = $row['transporter_name'] ?? '-';
                    $item['parent_id'] = $row['id'];
                    $all_received_items[] = $item;
                }
            }
        }
        mysqli_data_seek($entries, 0); // Reset for original inward tab
    }

    // Get loading/unloading entries
    $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
    $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");

    $loading_entries = [];
    $unloading_entries = [];

    if (mysqli_num_rows($check_loading) > 0) {
        // Build WHERE clause for loading
        $loading_where = [];
        if ($start_date && $end_date) {
            $loading_where[] = "DATE(reporting_datetime) BETWEEN '$start_date' AND '$end_date'";
        } elseif ($start_date) {
            $loading_where[] = "DATE(reporting_datetime) >= '$start_date'";
        } elseif ($end_date) {
            $loading_where[] = "DATE(reporting_datetime) <= '$end_date'";
        }
        if ($vehicle_filter) {
            $loading_where[] = "vehicle_registration_number LIKE '%$vehicle_filter%'";
        }
        if ($driver_filter) {
            $loading_where[] = "driver_name LIKE '%$driver_filter%'";
        }
        if ($transporter_filter) {
            $loading_where[] = "transport_company_name LIKE '%$transporter_filter%'";
        }
        $loading_where_sql = $loading_where ? 'WHERE ' . implode(' AND ', $loading_where) : '';
        $loading_query = mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist $loading_where_sql ORDER BY reporting_datetime DESC LIMIT 200");
        while ($row = mysqli_fetch_assoc($loading_query)) {
            $loading_entries[] = $row;
        }
    }

    if (mysqli_num_rows($check_unloading) > 0) {
        // Build WHERE clause for unloading
        $unloading_where = [];
        if ($start_date && $end_date) {
            $unloading_where[] = "DATE(reporting_datetime) BETWEEN '$start_date' AND '$end_date'";
        } elseif ($start_date) {
            $unloading_where[] = "DATE(reporting_datetime) >= '$start_date'";
        } elseif ($end_date) {
            $unloading_where[] = "DATE(reporting_datetime) <= '$end_date'";
        }
        if ($vehicle_filter) {
            $unloading_where[] = "vehicle_registration_number LIKE '%$vehicle_filter%'";
        }
        if ($driver_filter) {
            $unloading_where[] = "driver_name LIKE '%$driver_filter%'";
        }
        if ($transporter_filter) {
            $unloading_where[] = "transport_company_name LIKE '%$transporter_filter%'";
        }
        $unloading_where_sql = $unloading_where ? 'WHERE ' . implode(' AND ', $unloading_where) : '';
        $unloading_query = mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist $unloading_where_sql ORDER BY reporting_datetime DESC LIMIT 200");
        while ($row = mysqli_fetch_assoc($unloading_query)) {
            $unloading_entries[] = $row;
        }
    }

    // Get outward entries
    $outward_entries = [];
    $outward_where = [];
    if ($start_date && $end_date) {
        $outward_where[] = "DATE(tou.outward_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $outward_where[] = "DATE(tou.outward_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $outward_where[] = "DATE(tou.outward_datetime) <= '$end_date'";
    }
    if ($vehicle_filter) {
        $outward_where[] = "ti.vehicle_number LIKE '%$vehicle_filter%'";
    }
    if ($driver_filter) {
        $outward_where[] = "ti.driver_name LIKE '%$driver_filter%'";
    }
    if ($transporter_filter) {
        $outward_where[] = "ti.transporter_name LIKE '%$transporter_filter%'";
    }
    $outward_where_sql = $outward_where ? 'WHERE ' . implode(' AND ', $outward_where) : '';
    $outward_query = mysqli_query($conn, "SELECT tou.*, ti.vehicle_number, ti.driver_name, ti.transporter_name, ti.purpose_name 
                                        FROM truck_outward tou 
                                        JOIN truck_inward ti ON tou.inward_id = ti.id 
                                        $outward_where_sql 
                                        ORDER BY tou.outward_datetime DESC LIMIT 200");
    if ($outward_query) {
        while ($row = mysqli_fetch_assoc($outward_query)) {
            $outward_entries[] = $row;
        }
    }

    // Get patrol logs
    $patrol_entries = [];
    $patrol_analysis = [];
    $active_locations_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM patrol_locations WHERE is_active = 1"))['cnt'];

    // Build WHERE clause for patrol logs
    $patrol_where = [];
    if ($start_date && $end_date) {
        $patrol_where[] = "DATE(pl.scan_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $patrol_where[] = "DATE(pl.scan_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $patrol_where[] = "DATE(pl.scan_datetime) <= '$end_date'";
    }
    // If user searches by name, apply to guard name as well
    if ($driver_filter) {
        $patrol_where[] = "pl.guard_name LIKE '%$driver_filter%'";
    }
    $patrol_where_sql = $patrol_where ? 'WHERE ' . implode(' AND ', $patrol_where) : '';

    $patrol_query = mysqli_query($conn, "SELECT pl.*, loc.location_name, loc.area_site_building 
                                        FROM patrol_logs pl 
                                        JOIN patrol_locations loc ON pl.location_id = loc.id 
                                        $patrol_where_sql 
                                        ORDER BY pl.scan_datetime DESC");

    // Get employee entries
    $employee_entries = [];
    $employee_where = [];
    if ($start_date && $end_date) {
        $employee_where[] = "DATE(e.inward_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $employee_where[] = "DATE(e.inward_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $employee_where[] = "DATE(e.inward_datetime) <= '$end_date'";
    }
    if ($driver_filter) {
        $employee_where[] = "e.employee_name LIKE '%$driver_filter%'";
    }
    if ($vehicle_filter) {
        $employee_where[] = "e.vehicle_number LIKE '%$vehicle_filter%'";
    }
    if ($department_filter) {
        $employee_where[] = "em.department LIKE '%$department_filter%'";
    }
    if ($status_filter) {
        $employee_where[] = "e.status = '$status_filter'";
    }
    $employee_where_sql = $employee_where ? 'WHERE ' . implode(' AND ', $employee_where) : '';


    $m_db = MASTER_DB_NAME;
    $employee_query = mysqli_query($conn, "SELECT e.*, em.department, 
                                                u.username as inward_by_name, 
                                                u2.username as outward_by_name
                                              FROM employee_entries e
                                              LEFT JOIN employee_master em ON e.employee_id = em.employee_id
                                              LEFT JOIN $m_db.user_master u ON e.inward_by = u.id
                                              LEFT JOIN $m_db.user_master u2 ON e.outward_by = u2.id
                                              $employee_where_sql
                                              ORDER BY e.inward_datetime DESC");

    if ($patrol_query) {
        $logs_by_guard_date = [];
        while ($row = mysqli_fetch_assoc($patrol_query)) {
            $patrol_entries[] = $row;

            // Group for analysis
            $date = date('Y-m-d', strtotime($row['scan_datetime']));
            $key = $row['guard_id'] . '_' . $date;

            if (!isset($logs_by_guard_date[$key])) {
                $logs_by_guard_date[$key] = [
                    'guard_name' => $row['guard_name'],
                    'date' => $date,
                    'scans' => [],
                    'locations' => []
                ];
            }
            $logs_by_guard_date[$key]['scans'][] = $row;
            $logs_by_guard_date[$key]['locations'][$row['location_id']] = true;
        }

        // Performance Analysis
        foreach ($logs_by_guard_date as $key => $data) {
            $scans = array_reverse($data['scans']); // Oldest first
            $unique_locs = count($data['locations']);
            $total_scans = count($scans);

            $alerts = [];
            if ($unique_locs < $active_locations_count) {
                $alerts[] = "Missed " . ($active_locations_count - $unique_locs) . " locations";
            }

            // Check time gaps
            $max_gap = 0;
            for ($i = 1; $i < count($scans); $i++) {
                $gap = strtotime($scans[$i]['scan_datetime']) - strtotime($scans[$i - 1]['scan_datetime']);
                if ($gap > $max_gap)
                    $max_gap = $gap;

                if ($gap > 3600) { // More than 1 hour gap
                    $alerts[] = "Unusual delay: " . round($gap / 60) . " mins at " . date('h:i A', strtotime($scans[$i]['scan_datetime']));
                }
            }

            $patrol_analysis[] = [
                'guard_id' => $data['scans'][0]['guard_id'],
                'guard_name' => $data['guard_name'],
                'date' => $data['date'],
                'total_scans' => $total_scans,
                'unique_locations' => $unique_locs,
                'completion_pct' => $active_locations_count > 0 ? round(($unique_locs / $active_locations_count) * 100) : 0,
                'max_gap_mins' => round($max_gap / 60),
                'alerts' => $alerts,
                'journey' => $scans // Pass the sorted scans for timeline
            ];
        }
    }

    // Process employee entries
    if ($employee_query) {
        while ($row = mysqli_fetch_assoc($employee_query)) {
            $employee_entries[] = $row;
        }
    }



    // Get filter options
    $transporters = mysqli_query($conn, "SELECT DISTINCT transporter_name FROM truck_inward WHERE transporter_name IS NOT NULL AND transporter_name != '' ORDER BY transporter_name");
    $drivers = mysqli_query($conn, "SELECT DISTINCT driver_name FROM truck_inward WHERE driver_name IS NOT NULL AND driver_name != '' ORDER BY driver_name LIMIT 100");
    $vehicles = mysqli_query($conn, "SELECT DISTINCT vehicle_number FROM truck_inward WHERE vehicle_number IS NOT NULL ORDER BY vehicle_number LIMIT 100");
    ?>
    <div class="container">
        <a href="?page=dashboard" class="btn btn-secondary btn-full"
            style="margin-bottom: 15px; display: block; position: relative; z-index: 10;">
            ← Back
        </a>
        <div class="card">
            <h2>📊 Reports & Analytics</h2>

            <!-- Vehicle-wise timeline shortcut -->
            <div
                style="background: #f8fafc; border: 1px solid #e5e7eb; padding: 12px 14px; border-radius: 10px; margin: 12px 0 18px 0;">
                <div
                    style="display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                    <div style="color: #374151; font-weight: 600;">🧭 Vehicle-wise History (Gate → Loading/Unloading
                        →
                        Exit)</div>
                    <?php if (!empty($vehicle_filter)): ?>
                        <a href="?page=vehicle-history&vehicle=<?php echo urlencode($vehicle_filter); ?>"
                            class="btn btn-primary" style="text-decoration: none;">View History:
                            <?php echo htmlspecialchars(strtoupper($vehicle_filter)); ?>
                        </a>
                        <?php
                    else: ?>
                        <span style="color: #6b7280; font-size: 13px;">Select a vehicle above to enable</span>
                        <?php
                    endif; ?>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="reports">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Start
                            Date</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"
                            style="width: 100%;">
                    </div>
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">End
                            Date</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                            style="width: 100%;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Transporter</label>
                        <input type="text" name="transporter" list="transporter_list"
                            placeholder="Select or type transporter"
                            value="<?php echo htmlspecialchars($transporter_filter); ?>" style="width: 100%;">
                        <datalist id="transporter_list">
                            <?php while ($t = mysqli_fetch_assoc($transporters)): ?>
                                <option value="<?php echo htmlspecialchars($t['transporter_name']); ?>">
                                    <?php
                            endwhile; ?>
                        </datalist>
                    </div>
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Driver</label>
                        <input type="text" name="driver" list="driver_list" placeholder="Select or type driver"
                            value="<?php echo htmlspecialchars($driver_filter); ?>" style="width: 100%;">
                        <datalist id="driver_list">
                            <?php while ($d = mysqli_fetch_assoc($drivers)): ?>
                                <option value="<?php echo htmlspecialchars($d['driver_name']); ?>">
                                    <?php
                            endwhile; ?>
                        </datalist>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Vehicle</label>
                        <input type="text" name="vehicle" list="vehicle_list" placeholder="Select or type vehicle"
                            value="<?php echo htmlspecialchars($vehicle_filter); ?>" style="width: 100%;">
                        <datalist id="vehicle_list">
                            <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                                <option value="<?php echo htmlspecialchars($v['vehicle_number']); ?>">
                                    <?php
                            endwhile; ?>
                        </datalist>
                    </div>
                    <div>
                        <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Department</label>
                        <input type="text" name="department" list="department_list" placeholder="Select or type department"
                            value="<?php echo htmlspecialchars($department_filter); ?>" style="width: 100%;">
                        <datalist id="department_list">
                            <?php
                            $departments = mysqli_query($conn, "SELECT department_name FROM department_master ORDER BY department_name");
                            if ($departments) {
                                while ($dept = mysqli_fetch_assoc($departments)): ?>
                                    <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                                        <?php
                                endwhile;
                            } ?>

                        </datalist>
                    </div>
                </div>



                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">🔍 GENERATE REPORT</button>
                    <a href="?page=reports" class="btn btn-secondary"
                        style="flex: 0.3; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;"
                        onclick="document.getElementById('status').value='';">🔄 RESET</a>
                </div>
            </form>

            <!-- Tabs for different entry types -->
            <div
                style="margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <div style="display: flex; gap: 5px; min-width: max-content; padding-bottom: 2px;">
                    <button onclick="showTab('inward')" id="tab-inward" class="tab-btn active"
                        style="padding: 10px 15px; border: none; background: #3b82f6; color: white; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        🚛 Inward (
                        <?php echo mysqli_num_rows($entries); ?>)
                    </button>
                    <button onclick="showTab('outward')" id="tab-outward" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #e5e7eb; color: #666; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        📤 Outward (
                        <?php echo count($outward_entries); ?>)
                    </button>
                    <button onclick="showTab('loading')" id="tab-loading" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #e5e7eb; color: #666; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        📦 Loading (
                        <?php echo count($loading_entries); ?>)
                    </button>
                    <button onclick="showTab('unloading')" id="tab-unloading" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #e5e7eb; color: #666; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        📥 Unloading (
                        <?php echo count($unloading_entries); ?>)
                    </button>
                    <button onclick="showTab('patrol')" id="tab-patrol" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #e5e7eb; color: #666; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        👮 Patrol (
                        <?php
                        $patrol_where = [];
                        if ($start_date && $end_date) {
                            $patrol_where[] = "DATE(scan_datetime) BETWEEN '$start_date' AND '$end_date'";
                        } elseif ($start_date) {
                            $patrol_where[] = "DATE(scan_datetime) >= '$start_date'";
                        } elseif ($end_date) {
                            $patrol_where[] = "DATE(scan_datetime) <= '$end_date'";
                        }
                        $patrol_where_sql = $patrol_where ? 'WHERE ' . implode(' AND ', $patrol_where) : '';
                        $patrol_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM patrol_logs $patrol_where_sql"))['cnt'];
                        echo $patrol_count;
                        ?>)
                    </button>
                    <button onclick="showTab('employee')" id="tab-employee" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #e5e7eb; color: #666; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        👤 Employees (
                        <?php echo count($employee_entries); ?>)
                    </button>
                    <button onclick="showTab('items-received')" id="tab-items-received" class="tab-btn"
                        style="padding: 10px 15px; border: none; background: #fef3c7; color: #92400e; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 13px;">
                        📦 Items (<?php echo count($all_received_items); ?>)
                    </button>

                </div>
            </div>

            <!-- Inward Entries Tab -->
            <div id="tab-content-inward" class="tab-content">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Entry #</th>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Transporter</th>
                                <th>Purpose</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($entries) > 0): ?>
                                <?php
                                mysqli_data_seek($entries, 0);
                                while ($entry = mysqli_fetch_assoc($entries)): ?>
                                    <tr onclick="window.location='?page=inward-details&id=<?php echo intval($entry['id']); ?>'"
                                        style="cursor: pointer;">
                                        <td><strong>
                                                <?php echo $entry['entry_number']; ?>
                                            </strong></td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($entry['inward_date'])); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo $entry['vehicle_number']; ?>
                                            </strong></td>
                                        <td>
                                            <?php echo $entry['driver_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $entry['transporter_name'] ?: '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['purpose_name'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo $entry['from_location']; ?>
                                        </td>
                                        <td>
                                            <?php echo $entry['to_location']; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $entry['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                                <?php echo strtoupper($entry['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                                        No inward records found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Outward Entries Tab -->
            <div id="tab-content-outward" class="tab-content" style="display: none;">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Outward Time</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Transporter</th>
                                <th>Purpose</th>
                                <th>Outward By</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($outward_entries) > 0): ?>
                                <?php foreach ($outward_entries as $entry): ?>
                                    <tr onclick="window.location='?page=outward-details&id=<?php echo intval($entry['inward_id']); ?>'"
                                        style="cursor: pointer;">
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($entry['outward_datetime'])); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['vehicle_number']); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['driver_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['transporter_name'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['purpose_name'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['outward_by_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo formatDuration($entry['duration_hours']); ?>
                                        </td>
                                        <td><span class="badge badge-success">EXITED</span></td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                                        No outward records found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loading Entries Tab -->
            <div id="tab-content-loading" class="tab-content" style="display: none;">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Document ID</th>
                                <th>Date/Time</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Transport Company</th>
                                <th>Loading for Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($loading_entries) > 0): ?>
                                <?php foreach ($loading_entries as $entry): ?>
                                    <tr onclick="window.location='?page=loading-details&id=<?php echo intval($entry['id']); ?>'"
                                        style="cursor: pointer;">
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['document_id'] ?? '-'); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($entry['reporting_datetime'])); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['driver_name'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['transport_company_name'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['loading_location'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>">
                                                <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                        No loading records found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Unloading Entries Tab -->
            <div id="tab-content-unloading" class="tab-content" style="display: none;">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Document ID</th>
                                <th>Date/Time</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Transport Company</th>
                                <th>Vendor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($unloading_entries) > 0): ?>
                                <?php foreach ($unloading_entries as $entry): ?>
                                    <tr onclick="window.location='?page=unloading-details&id=<?php echo intval($entry['id']); ?>'"
                                        style="cursor: pointer;">
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['document_id'] ?? '-'); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($entry['reporting_datetime'])); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['driver_name'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['transport_company_name'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['vendor_name'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>">
                                                <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                        No unloading records found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Patrol Logs Tab -->
            <div id="tab-content-patrol" class="tab-content" style="display: none;">
                <!-- Performance Analysis Summary -->
                <?php if (!empty($patrol_analysis)): ?>
                    <div style="margin-bottom: 25px;">
                        <h3
                            style="font-size: 16px; margin-bottom: 15px; color: #4338ca; display: flex; align-items: center; gap: 8px;">
                            <span>📈</span> Patrol Performance Analysis (by Guard/Day)
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                            <?php foreach ($patrol_analysis as $perf):
                                $status_color = $perf['completion_pct'] >= 100 ? '#10b981' : ($perf['completion_pct'] >= 70 ? '#f59e0b' : '#ef4444');
                                ?>
                                <div class="card"
                                    style="margin: 0; padding: 15px; border-left: 5px solid <?php echo $status_color; ?>;">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                        <div>
                                            <strong style="font-size: 15px;">
                                                <?php echo htmlspecialchars($perf['guard_name']); ?>
                                            </strong>
                                            <div style="font-size: 12px; color: #666;">
                                                <?php echo date('d M, Y', strtotime($perf['date'])); ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-size: 18px; font-weight: 800; color: <?php echo $status_color; ?>;">
                                                <?php echo $perf['completion_pct']; ?>%
                                            </div>
                                            <div style="font-size: 10px; color: #999; text-transform: uppercase;">Coverage
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 15px; margin-bottom: 12px; font-size: 13px;">
                                        <div>
                                            <span style="color: #666;">Locations:</span>
                                            <strong>
                                                <?php echo $perf['unique_locations']; ?> /
                                                <?php echo $active_locations_count; ?>
                                            </strong>
                                        </div>
                                        <div>
                                            <span style="color: #666;">Max Gap:</span>
                                            <strong style="<?php echo $perf['max_gap_mins'] > 60 ? 'color: #ef4444;' : ''; ?>">
                                                <?php echo $perf['max_gap_mins']; ?>
                                                mins
                                            </strong>
                                        </div>
                                    </div>

                                    <?php if (!empty($perf['alerts'])): ?>
                                        <div
                                            style="background: #fff5f5; border-radius: 6px; padding: 8px 12px; border: 1px solid #fee2e2;">
                                            <div
                                                style="font-size: 11px; font-weight: 700; color: #b91c1c; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                                <span>⚠️</span> ALERTS DETECTED
                                            </div>
                                            <ul style="margin: 0; padding-left: 15px; font-size: 11px; color: #7f1d1d;">
                                                <?php foreach ($perf['alerts'] as $alert): ?>
                                                    <li>
                                                        <?php echo $alert; ?>
                                                    </li>
                                                    <?php
                                                endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php
                                    else: ?>
                                        <div
                                            style="background: #f0fdf4; border-radius: 6px; padding: 8px 12px; color: #166534; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                                            <span>✅</span> Perfect patrol. No issues detected.
                                        </div>
                                        <?php
                                    endif; ?>

                                    <button
                                        onclick='showPatrolTimeline(<?php echo htmlspecialchars(json_encode($perf), ENT_QUOTES, "UTF-8"); ?>)'
                                        class="btn btn-sm"
                                        style="margin-top: 12px; width: 100%; background: #4338ca; color: white; border-radius: 6px; font-size: 12px; padding: 8px;">
                                        📍 View Journey Timeline
                                    </button>
                                </div>
                                <?php
                            endforeach; ?>
                        </div>
                    </div>
                    <?php
                endif; ?>

                <div id="patrolTimelineModal"
                    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); z-index: 10001; overflow-y: auto; padding: 20px; backdrop-filter: blur(4px);">
                    <div
                        style="max-width: 550px; margin: 20px auto; background: #f8fafc; border-radius: 24px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 1px solid rgba(255,255,255,0.1);">
                        <!-- Header with Gradient -->
                        <div
                            style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 30px 25px; position: relative;">
                            <button onclick="document.getElementById('patrolTimelineModal').style.display='none'"
                                style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; border-radius: 12px; width: 36px; height: 36px; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.2s;"
                                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.2)'">✕</button>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div
                                    style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; backdrop-filter: blur(5px);">
                                    👮</div>
                                <div>
                                    <h3 id="timeline_guard_name"
                                        style="margin: 0; color: white; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;">
                                        Guard Journey</h3>
                                    <p id="timeline_date"
                                        style="margin: 4px 0 0 0; color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 500;">
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 30px 25px; background: #f8fafc;">
                            <div id="timeline_content" style="position: relative; padding-left: 45px;">
                                <!-- Vertical Line -->
                                <div
                                    style="position: absolute; left: 19px; top: 10px; bottom: 10px; width: 3px; background: linear-gradient(to bottom, #4f46e5, #7c3aed); border-radius: 10px; opacity: 0.2;">
                                </div>
                                <!-- Journey items will be injected here -->
                            </div>
                        </div>

                        <!-- Footer -->
                        <div style="padding: 20px; background: white; text-align: center; border-top: 1px solid #e2e8f0;">
                            <button onclick="document.getElementById('patrolTimelineModal').style.display='none'"
                                class="btn btn-secondary"
                                style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 600;">Close
                                Journey
                                Log</button>
                        </div>
                    </div>
                </div>

                <script>
                    function showPatrolTimeline(perf) {
                        document.getElementById('timeline_guard_name').textContent = perf.guard_name;
                        document.getElementById('timeline_date').textContent = new Date(perf.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });

                        const content = document.getElementById('timeline_content');
                        content.innerHTML = '<!-- Vertical Line --><div style="position: absolute; left: 19px; top: 10px; bottom: 10px; width: 3px; background: linear-gradient(to bottom, #4f46e5, #7c3aed); border-radius: 10px; opacity: 0.2;"></div>';

                        perf.journey.forEach((scan, index) => {
                            const item = document.createElement('div');
                            item.className = 'timeline-item';
                            item.style.position = 'relative';
                            item.style.marginBottom = '30px';

                            // Timeline Marker (Outer Ring)
                            const markerOuter = document.createElement('div');
                            markerOuter.style.position = 'absolute';
                            markerOuter.style.left = '-35px';
                            markerOuter.style.top = '4px';
                            markerOuter.style.width = '20px';
                            markerOuter.style.height = '20px';
                            markerOuter.style.borderRadius = '50%';
                            markerOuter.style.background = '#fff';
                            markerOuter.style.border = '3px solid #4f46e5';
                            markerOuter.style.boxShadow = '0 0 0 4px rgba(79, 70, 229, 0.1)';
                            markerOuter.style.zIndex = '2';

                            // Marker Inner Dot
                            const markerInner = document.createElement('div');
                            markerInner.style.position = 'absolute';
                            markerInner.style.left = '4px';
                            markerInner.style.top = '4px';
                            markerInner.style.width = '6px';
                            markerInner.style.height = '6px';
                            markerInner.style.borderRadius = '50%';
                            markerInner.style.background = '#4f46e5';
                            markerOuter.appendChild(markerInner);

                            // Time Gap Indicator
                            if (index > 0) {
                                const prevTime = new Date(perf.journey[index - 1].scan_datetime);
                                const currTime = new Date(scan.scan_datetime);
                                const diffMins = Math.round((currTime - prevTime) / 60000);

                                const gapContainer = document.createElement('div');
                                gapContainer.style.position = 'absolute';
                                gapContainer.style.left = '-45px';
                                gapContainer.style.top = '-25px';
                                gapContainer.style.width = '40px';
                                gapContainer.style.textAlign = 'right';
                                gapContainer.style.fontSize = '11px';
                                gapContainer.style.fontWeight = '700';
                                gapContainer.style.color = diffMins > 60 ? '#ef4444' : '#94a3b8';
                                gapContainer.innerHTML = `<span style="background:#f1f5f9; padding: 2px 6px; border-radius: 10px; border: 1px solid #e2e8f0;">${diffMins}m</span>`;
                                item.appendChild(gapContainer);
                            }

                            const timeStr = new Date(scan.scan_datetime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                            item.innerHTML += `
                                <div style="background: white; padding: 15px 18px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                        <span style="font-size: 11px; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.5px;">${timeStr}</span>
                                                        <span style="font-size: 10px; background: ${index === 0 ? '#ecfdf5' : '#f8fafc'}; color: ${index === 0 ? '#059669' : '#64748b'}; padding: 3px 8px; border-radius: 20px; font-weight: 700; border: 1px solid ${index === 0 ? '#d1fae5' : '#e2e8f0'};">
                                                            ${index === 0 ? 'START' : 'STEP ' + (index + 1)}
                                                        </span>
                                                    </div>
                                                    <div style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">${scan.location_name}</div>
                                                    <div style="display: flex; align-items: center; gap: 5px; color: #64748b; font-size: 13px;">
                                                        <span>📍</span>
                                                        <span>${scan.area_site_building || 'Main Facility Area'}</span>
                                                    </div>
                                                </div>
                                            `;
                            item.appendChild(markerOuter);
                            content.appendChild(item);
                        });

                        document.getElementById('patrolTimelineModal').style.display = 'block';
                    }
                </script>

                <h3 style="font-size: 16px; margin-bottom: 10px; color: #374151;">📋 Detailed Scan Logs</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Guard Name</th>
                                <th>Location</th>
                                <th>Area/Building</th>
                                <th>Scan Time</th>
                                <th>Session ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($patrol_entries) > 0): ?>
                                <?php foreach ($patrol_entries as $entry): ?>
                                    <tr>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['guard_name']); ?>
                                            </strong></td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['location_name']); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['area_site_building']); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y h:i A', strtotime($entry['scan_datetime'])); ?>
                                        </td>
                                        <td><small style="color: #666;">
                                                <?php echo htmlspecialchars($entry['session_id']); ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                        No patrol logs found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Employee Entries Tab -->
            <div id="tab-content-employee" class="tab-content" style="display: none;">
                <h3 style="font-size: 16px; margin-bottom: 10px; color: #374151;">👤 Employee Entry/Exit Records
                </h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Employee ID</th>
                                <th>Department</th>
                                <th>Vehicle Number</th>
                                <th>Entry Time</th>
                                <th>Exit Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($employee_entries) > 0): ?>
                                <?php foreach ($employee_entries as $entry): ?>
                                    <tr onclick="viewEmployeeEntry('<?php echo $entry['id']; ?>', '<?php echo addslashes($entry['employee_name']); ?>', '<?php echo $entry['status']; ?>')"
                                        style="cursor: pointer;">
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['employee_name']); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['employee_id']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($entry['department'] ?: '-'); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($entry['vehicle_number']); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo strtoupper(date('d-M-y h:i A', strtotime($entry['inward_datetime']))); ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['outward_datetime']): ?>
                                                <?php echo date('d/m/Y h:i A', strtotime($entry['outward_datetime'])); ?>
                                                <?php
                                            else: ?>
                                                <span style="color: #666; font-style: italic;">Still Inside</span>
                                                <?php
                                            endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['outward_datetime']): ?>
                                                <?php
                                                $start = strtotime($entry['inward_datetime']);
                                                $end = strtotime($entry['outward_datetime']);
                                                $diff = $end - $start;
                                                $hours = floor($diff / 3600);
                                                $minutes = floor(($diff % 3600) / 60);
                                                echo "{$hours}h {$minutes}m";
                                                ?>
                                                <?php
                                            else: ?>
                                                <?php
                                                $start = strtotime($entry['inward_datetime']);
                                                $now = time();
                                                $diff = $now - $start;
                                                $hours = floor($diff / 3600);
                                                $minutes = floor(($diff % 3600) / 60);
                                                echo "<span style='color: #059669;'>{$hours}h {$minutes}m (current)</span>";
                                                ?>
                                                <?php
                                            endif; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $entry['status'] == 'inside' ? 'warning' : 'success'; ?>">
                                                <?php echo strtoupper($entry['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small style="color: #666;">
                                                <strong>In:</strong>
                                                <?php echo htmlspecialchars($entry['inward_by_name'] ?: 'System Admin'); ?><br>
                                                <?php if ($entry['outward_datetime']): ?>
                                                    <strong>Out:</strong>
                                                    <?php echo htmlspecialchars($entry['outward_by_name'] ?: 'System Admin'); ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach; ?>
                                <?php
                            else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                                        No employee entry records found matching the selected filters.
                                    </td>
                                </tr>
                                <?php
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Items Received Tab -->
            <div id="tab-content-items-received" class="tab-content" style="display: none;">
                <h3 style="font-size: 16px; margin-bottom: 15px; color: #92400e; display: flex; align-items: center; gap: 8px;">
                    <span>📦</span> Material Items Received (Consolidated)
                </h3>
                <div class="table-wrapper">
                    <table id="received_items_table">
                        <thead>
                            <tr style="background: #fffbeb;">
                                <th style="color: #92400e;">Entry #</th>
                                <th style="color: #92400e;">Date</th>
                                <th style="color: #92400e;">Vehicle</th>
                                <th style="color: #92400e;">Item Code</th>
                                <th style="color: #92400e;">Item Name</th>
                                <th style="color: #92400e;">Qty</th>
                                <th style="color: #92400e;">Unit</th>
                                <th style="color: #92400e;">Transporter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_received_items)): ?>
                                <?php $last_parent_id = null; ?>
                                <?php foreach ($all_received_items as $item): 
                                    $is_duplicate = ($last_parent_id === $item['parent_id']);
                                    $last_parent_id = $item['parent_id'];
                                ?>
                                    <tr onclick="window.location='?page=inward-details&id=<?php echo intval($item['parent_id'] ?? 0); ?>'"
                                        style="cursor: pointer; transition: background 0.2s; <?php echo $is_duplicate ? 'border-top: none;' : 'border-top: 2px solid #fef3c7;'; ?>">
                                        <td><strong><?php echo !$is_duplicate ? htmlspecialchars($item['parent_entry_num'] ?? 'N/A') : ''; ?></strong></td>
                                        <td style="color: #64748b; font-size: 13px;"><?php echo !$is_duplicate ? date('d/m/Y', strtotime($item['parent_inward_date'] ?? 'today')) : ''; ?></td>
                                        <td><strong><?php echo !$is_duplicate ? htmlspecialchars($item['parent_vehicle'] ?? '-') : ''; ?></strong></td>
                                        <td style="font-family: monospace; font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($item['item_name'] ?? $item['item_description'] ?? 'Unknown'); ?></strong></td>
                                        <td style="font-weight: 700; color: #d97706;"><?php echo htmlspecialchars($item['quantity'] ?? '0'); ?></td>
                                        <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($item['unit'] ?? 'PCS'); ?></td>
                                        <td style="font-size: 11px; color: #6b7280;"><?php echo !$is_duplicate ? htmlspecialchars($item['parent_transporter'] ?? '-') : ''; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
                                        <div style="font-size: 30px; margin-bottom: 10px;">📦</div>
                                        No material items found in the selected entries.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                function updateExportLink(activeTab) {
                    const link = document.getElementById('exportLink');
                    if (!link) return;
                    try {
                        const url = new URL(link.href, window.location.origin);
                        url.searchParams.set('tab', activeTab);
                        link.href = 'export.php' + url.search;

                        // Specific filename per tab
                        let filename = '';
                        switch (activeTab) {
                            case 'inward': filename = 'Inward_Report'; break;
                            case 'outward': filename = 'Outward_Report'; break;
                            case 'loading': filename = 'Loading_Report'; break;
                            case 'unloading': filename = 'Unloading_Report'; break;
                            case 'patrol': filename = 'Patrol_Report'; break;
                            case 'employee': filename = 'Employee_Report'; break;
                            case 'registers': filename = 'Registers_Report'; break;
                            case 'items-received': filename = 'Material_Items_Received'; break;
                            default: filename = 'Truck_Report_' + activeTab;
                        }
                        link.setAttribute('download', filename + '.xls');
                    } catch (e) {
                        console.error('Update export link error:', e);
                    }
                }

                function showTab(tabName) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.style.display = 'none';
                    });

                    // Remove active class from all tabs
                    document.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.style.background = '#e5e7eb';
                        btn.style.color = '#666';
                    });

                    // Show selected tab content
                    document.getElementById('tab-content-' + tabName).style.display = 'block';

                    // Add active class to selected tab
                    const activeTab = document.getElementById('tab-' + tabName);
                    activeTab.style.background = '#3b82f6';
                    activeTab.style.color = 'white';

                    // Ensure export matches selected tab
                    updateExportLink(tabName);
                }
            </script>

            <!-- Export Button -->
            <?php if (mysqli_num_rows($entries) > 0 || count($loading_entries) > 0 || count($unloading_entries) > 0 || count($outward_entries) > 0 || count($patrol_entries) > 0 || count($employee_entries) > 0): ?>
                <a id="exportLink"
                    href="export.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&transporter=<?php echo urlencode($transporter_filter); ?>&driver=<?php echo urlencode($driver_filter); ?>&vehicle=<?php echo urlencode($vehicle_filter); ?>&status=<?php echo urlencode($status_filter); ?>&tab=inward"
                    class="btn btn-success btn-full" style="margin-top: 20px;" download="Inward_Report.xls">
                    📥 EXPORT TO EXCEL
                </a>
                <?php
            endif; ?>
        </div>
    </div>

    <?php
    // ==================== VEHICLE HISTORY (GATE → CHECKLISTS → EXIT) ====================
elseif ($page == 'vehicle-history'):
    // Initialize loading/unloading tables (also applies migrations)
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    $vehicle_number = isset($_GET['vehicle']) ? strtoupper(trim($_GET['vehicle'])) : '';
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
    $transporter_filter = isset($_GET['transporter']) ? trim($_GET['transporter']) : '';
    $driver_filter = isset($_GET['driver']) ? trim($_GET['driver']) : '';
    $department_filter = isset($_GET['department']) ? trim($_GET['department']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

    $vehicle_escaped = $vehicle_number ? mysqli_real_escape_string($conn, $vehicle_number) : '';
    $trans_escaped = $transporter_filter ? mysqli_real_escape_string($conn, $transporter_filter) : '';
    $driver_escaped = $driver_filter ? mysqli_real_escape_string($conn, $driver_filter) : '';
    $dept_escaped = $department_filter ? mysqli_real_escape_string($conn, $department_filter) : '';
    $status_escaped = $status_filter ? mysqli_real_escape_string($conn, $status_filter) : '';

    // Normalized vehicle number (handles spaces/dashes differences across entries)
    $vehicle_norm = $vehicle_number ? str_replace([' ', '-'], '', $vehicle_number) : '';
    $vehicle_norm_escaped = $vehicle_norm ? mysqli_real_escape_string($conn, $vehicle_norm) : '';
    $from_date_escaped = $from_date ? mysqli_real_escape_string($conn, $from_date) : '';
    $to_date_escaped = $to_date ? mysqli_real_escape_string($conn, $to_date) : '';

    $visits = [];
    $employee_visits = [];
    $loading_by_inward = [];
    $unloading_by_inward = [];
    $unassigned_loading = [];
    $unassigned_unloading = [];

    // Fetch all unique vehicles for search datalist (Pre-fetch for search box)
    $vehicle_list = [];
    $all_vehicles_sql = "
        SELECT DISTINCT vehicle_number as v FROM truck_inward 
        UNION 
        SELECT DISTINCT vehicle_no as v FROM manual_registers 
        UNION 
        SELECT DISTINCT vehicle_registration_number as v FROM vehicle_loading_checklist
        UNION
        SELECT DISTINCT vehicle_registration_number as v FROM vehicle_unloading_checklist
        ORDER BY v ASC";
    $all_v_rs = mysqli_query($conn, $all_vehicles_sql);
    if ($all_v_rs) {
        while ($vrow = mysqli_fetch_assoc($all_v_rs)) {
            if ($vrow['v']) {
                $vehicle_list[] = strtoupper(trim($vrow['v']));
            }
        }
    }
    $vehicle_list = array_unique($vehicle_list);

    // Fetch Truck Visits
    if ($vehicle_number) {
        $visit_where = [];
        if ($vehicle_escaped) {
            $visit_where[] = "ti.vehicle_number LIKE '%$vehicle_escaped%'";
        }
        if ($trans_escaped) {
            $visit_where[] = "ti.transporter_name LIKE '%$trans_escaped%'";
        }
        if ($driver_escaped) {
            $visit_where[] = "ti.driver_name LIKE '%$driver_escaped%'";
        }
        if ($from_date_escaped) {
            $visit_where[] = "ti.inward_date >= '$from_date_escaped'";
        }
        if ($to_date_escaped) {
            $visit_where[] = "ti.inward_date <= '$to_date_escaped'";
        }
        if ($status_escaped) {
            $visit_where[] = "ti.status = '$status_escaped'";
        }

        $visit_where_sql = $visit_where ? 'WHERE ' . implode(' AND ', $visit_where) : '';

        $visit_sql = "
            SELECT 
                ti.*,
                to1.outward_datetime,
                to1.outward_by_name,
                to1.outward_remarks
            FROM truck_inward ti
            LEFT JOIN truck_outward to1 ON to1.inward_id = ti.id
            $visit_where_sql
            ORDER BY ti.inward_datetime DESC
            LIMIT 200";

        $visit_rs = mysqli_query($conn, $visit_sql);
        while ($visit_rs && ($row = mysqli_fetch_assoc($visit_rs))) {
            $visits[] = $row;
        }

        // 2) Get Employee Visits (Filtered)
        $emp_where = [];
        if ($vehicle_escaped) {
            $emp_where[] = "e.vehicle_number LIKE '%$vehicle_escaped%'";
        }
        if ($dept_escaped) {
            $emp_where[] = "em.department LIKE '%$dept_escaped%'";
        }
        if ($from_date_escaped) {
            $emp_where[] = "DATE(e.inward_datetime) >= '$from_date_escaped'";
        }
        if ($to_date_escaped) {
            $emp_where[] = "DATE(e.inward_datetime) <= '$to_date_escaped'";
        }
        if ($status_escaped) {
            $emp_where[] = "e.status = '$status_escaped'";
        }

        $emp_where_sql = $emp_where ? 'WHERE ' . implode(' AND ', $emp_where) : '';

        $m_db = MASTER_DB_NAME;
        $emp_visit_sql = "
            SELECT e.*, em.department, u.username as inward_by_name, u2.username as outward_by_name
            FROM employee_entries e
            LEFT JOIN employee_master em ON e.employee_id = em.employee_id
            LEFT JOIN $m_db.user_master u ON e.inward_by = u.id
            LEFT JOIN $m_db.user_master u2 ON e.outward_by = u2.id
            $emp_where_sql
            ORDER BY e.inward_datetime DESC
            LIMIT 200";

        $emp_visit_rs = mysqli_query($conn, $emp_visit_sql);
        while ($emp_visit_rs && ($row = mysqli_fetch_assoc($emp_visit_rs))) {
            $employee_visits[] = $row;
        }

        // Preload loading/unloading checklists for this vehicle (for old records without inward_id)
        $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
        $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");

        $all_loading = [];
        $all_unloading = [];

        if (count($visits) > 0) {
            // Compute a non-overlapping window per visit: end = outward_datetime, else next inward - 1 second, else NOW
            $visits_asc = $visits;
            usort($visits_asc, function ($a, $b) {
                return strtotime($a['inward_datetime']) <=> strtotime($b['inward_datetime']);
            });

            for ($i = 0; $i < count($visits_asc); $i++) {
                $start_ts = strtotime($visits_asc[$i]['inward_datetime']);
                $end_ts = null;
                if (!empty($visits_asc[$i]['outward_datetime'])) {
                    $end_ts = strtotime($visits_asc[$i]['outward_datetime']);
                } else {
                    $next_start_ts = ($i + 1 < count($visits_asc)) ? strtotime($visits_asc[$i + 1]['inward_datetime']) : null;
                    if ($next_start_ts) {
                        $end_ts = $next_start_ts - 1;
                    } else {
                        $end_ts = time();
                    }
                }
                $visits_asc[$i]['_window_start_ts'] = $start_ts;
                $visits_asc[$i]['_window_end_ts'] = $end_ts;
                $visits_asc[$i]['_loading'] = [];
                $visits_asc[$i]['_unloading'] = [];
            }

            // Fetch checklists for this vehicle (do NOT restrict to visit windows;
            // otherwise checklists entered after "exit" won't appear at all)
            // Match by normalized vehicle number to avoid formatting mismatch (spaces / dashes)
            $checklist_where = ["REPLACE(REPLACE(UPPER(vehicle_registration_number), ' ', ''), '-', '') = '$vehicle_norm_escaped'"];
            if ($from_date_escaped && $to_date_escaped) {
                $checklist_where[] = "DATE(reporting_datetime) BETWEEN '$from_date_escaped' AND '$to_date_escaped'";
            } elseif ($from_date_escaped) {
                $checklist_where[] = "DATE(reporting_datetime) >= '$from_date_escaped'";
            } elseif ($to_date_escaped) {
                $checklist_where[] = "DATE(reporting_datetime) <= '$to_date_escaped'";
            }
            $checklist_where_sql = 'WHERE ' . implode(' AND ', $checklist_where);

            if ($check_loading && mysqli_num_rows($check_loading) > 0) {
                $loading_sql = "
                    SELECT *
                    FROM vehicle_loading_checklist
                    $checklist_where_sql
                    ORDER BY reporting_datetime ASC
                    LIMIT 1000
                ";
                $rs = mysqli_query($conn, $loading_sql);
                while ($rs && ($r = mysqli_fetch_assoc($rs))) {
                    $all_loading[] = $r;
                }
            }

            if ($check_unloading && mysqli_num_rows($check_unloading) > 0) {
                $unloading_sql = "
                    SELECT *
                    FROM vehicle_unloading_checklist
                    $checklist_where_sql
                    ORDER BY reporting_datetime ASC
                    LIMIT 1000
                ";
                $rs = mysqli_query($conn, $unloading_sql);
                while ($rs && ($r = mysqli_fetch_assoc($rs))) {
                    $all_unloading[] = $r;
                }
            }

            // First, attach checklists that have inward_id (exact match)
            foreach ($all_loading as $r) {
                if (!empty($r['inward_id'])) {
                    $iid = (string) $r['inward_id'];
                    if (!isset($loading_by_inward[$iid]))
                        $loading_by_inward[$iid] = [];
                    $loading_by_inward[$iid][] = $r;
                }
            }
            foreach ($all_unloading as $r) {
                if (!empty($r['inward_id'])) {
                    $iid = (string) $r['inward_id'];
                    if (!isset($unloading_by_inward[$iid]))
                        $unloading_by_inward[$iid] = [];
                    $unloading_by_inward[$iid][] = $r;
                }
            }

            // Then, time-window match any records without inward_id (legacy)
            $ptr = 0;
            foreach ($all_loading as $r) {
                if (!empty($r['inward_id']))
                    continue;
                $ts = strtotime($r['reporting_datetime']);
                while ($ptr < count($visits_asc) && $ts > $visits_asc[$ptr]['_window_end_ts']) {
                    $ptr++;
                }
                if ($ptr < count($visits_asc) && $ts >= $visits_asc[$ptr]['_window_start_ts'] && $ts <= $visits_asc[$ptr]['_window_end_ts']) {
                    $visits_asc[$ptr]['_loading'][] = $r;
                } else {
                    $unassigned_loading[] = $r;
                }
            }

            $ptr = 0;
            foreach ($all_unloading as $r) {
                if (!empty($r['inward_id']))
                    continue;
                $ts = strtotime($r['reporting_datetime']);
                while ($ptr < count($visits_asc) && $ts > $visits_asc[$ptr]['_window_end_ts']) {
                    $ptr++;
                }
                if ($ptr < count($visits_asc) && $ts >= $visits_asc[$ptr]['_window_start_ts'] && $ts <= $visits_asc[$ptr]['_window_end_ts']) {
                    $visits_asc[$ptr]['_unloading'][] = $r;
                } else {
                    $unassigned_unloading[] = $r;
                }
            }

            // Copy attachments back to $visits (descending order rendering)
            $visits_map = [];
            foreach ($visits_asc as $v) {
                $visits_map[(string) $v['id']] = $v;
            }
            foreach ($visits as &$v) {
                $idStr = (string) $v['id'];
                if (isset($visits_map[$idStr])) {
                    $v['_window_start_ts'] = $visits_map[$idStr]['_window_start_ts'];
                    $v['_window_end_ts'] = $visits_map[$idStr]['_window_end_ts'];
                    $v['_loading_legacy'] = $visits_map[$idStr]['_loading'];
                    $v['_unloading_legacy'] = $visits_map[$idStr]['_unloading'];
                } else {
                    $v['_loading_legacy'] = [];
                    $v['_unloading_legacy'] = [];
                }
            }
            unset($v);
        }

        // Additional Fetch for Tabs (Loading, Unloading, Outward, Patrol)
        $loading_entries = [];
        $unloading_entries = [];
        $outward_entries = [];
        $patrol_count = 0;

        // Loading Checklist Fetch
        $lc_where = [];
        if ($vehicle_norm_escaped)
            $lc_where[] = "REPLACE(REPLACE(UPPER(vehicle_registration_number), ' ', ''), '-', '') = '$vehicle_norm_escaped'";
        if ($trans_escaped)
            $lc_where[] = "transport_company_name LIKE '%$trans_escaped%'";
        if ($driver_escaped)
            $lc_where[] = "driver_name LIKE '%$driver_escaped%'";
        if ($from_date_escaped)
            $lc_where[] = "DATE(reporting_datetime) >= '$from_date_escaped'";
        if ($to_date_escaped)
            $lc_where[] = "DATE(reporting_datetime) <= '$to_date_escaped'";
        $lc_sql = "SELECT * FROM vehicle_loading_checklist" . ($lc_where ? " WHERE " . implode(" AND ", $lc_where) : "") . " ORDER BY reporting_datetime DESC";
        $lc_rs = mysqli_query($conn, $lc_sql);
        while ($lc_rs && $row = mysqli_fetch_assoc($lc_rs))
            $loading_entries[] = $row;

        // Unloading Checklist Fetch
        $uc_where = [];
        if ($vehicle_norm_escaped)
            $uc_where[] = "REPLACE(REPLACE(UPPER(vehicle_registration_number), ' ', ''), '-', '') = '$vehicle_norm_escaped'";
        if ($trans_escaped)
            $uc_where[] = "transport_company_name LIKE '%$trans_escaped%'";
        if ($driver_escaped)
            $uc_where[] = "driver_name LIKE '%$driver_escaped%'";
        if ($from_date_escaped)
            $uc_where[] = "DATE(reporting_datetime) >= '$from_date_escaped'";
        if ($to_date_escaped)
            $uc_where[] = "DATE(reporting_datetime) <= '$to_date_escaped'";
        $uc_sql = "SELECT * FROM vehicle_unloading_checklist" . ($uc_where ? " WHERE " . implode(" AND ", $uc_where) : "") . " ORDER BY reporting_datetime DESC";
        $uc_rs = mysqli_query($conn, $uc_sql);
        while ($uc_rs && $row = mysqli_fetch_assoc($uc_rs))
            $unloading_entries[] = $row;

        // Outward Fetch
        $out_where = [];
        if ($vehicle_escaped)
            $out_where[] = "ti.vehicle_number LIKE '%$vehicle_escaped%'";
        if ($trans_escaped)
            $out_where[] = "ti.transporter_name LIKE '%$trans_escaped%'";
        if ($driver_escaped)
            $out_where[] = "ti.driver_name LIKE '%$driver_escaped%'";
        if ($from_date_escaped)
            $out_where[] = "DATE(to1.outward_datetime) >= '$from_date_escaped'";
        if ($to_date_escaped)
            $out_where[] = "DATE(to1.outward_datetime) <= '$to_date_escaped'";
        $out_sql = "SELECT to1.*, ti.vehicle_number, ti.driver_name, ti.transporter_name FROM truck_outward to1 JOIN truck_inward ti ON to1.inward_id = ti.id" . ($out_where ? " WHERE " . implode(" AND ", $out_where) : "") . " ORDER BY to1.outward_datetime DESC";
        $out_rs = mysqli_query($conn, $out_sql);
        while ($out_rs && $row = mysqli_fetch_assoc($out_rs))
            $outward_entries[] = $row;

        // Patrol Count Fetch
        $pc_where = [];
        if ($from_date_escaped)
            $pc_where[] = "DATE(scan_datetime) >= '$from_date_escaped'";
        if ($to_date_escaped)
            $pc_where[] = "DATE(scan_datetime) <= '$to_date_escaped'";
        $pc_sql = "SELECT COUNT(*) as cnt FROM patrol_logs" . ($pc_where ? " WHERE " . implode(" AND ", $pc_where) : "");
        $pc_res = mysqli_fetch_assoc(mysqli_query($conn, $pc_sql));
        $patrol_count = $pc_res['cnt'] ?? 0;
    } // End if ($vehicle_number)
    ?>
    <div class="container" style="padding-bottom: 120px;">
        <a href="?page=dashboard" class="btn btn-secondary btn-full"
            style="margin-bottom: 15px; display: block; position: relative; z-index: 10;">
            ← Back
        </a>
        <div class="card">
            <h2>🧭 Vehicle History (Gate Entry → Loading/Unloading → Exit)</h2>

            <form method="GET" style="margin: 15px 0 20px 0;">
                <input type="hidden" name="page" value="vehicle-history">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label
                            style="font-size: 13px; color: #6b7280; display: block; margin-bottom: 5px; font-weight: 500;">Vehicle
                            Number</label>
                        <input type="text" name="vehicle" value="<?php echo htmlspecialchars($vehicle_number); ?>"
                            placeholder=""
                            style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>
                    <div>
                        <label
                            style="font-size: 13px; color: #6b7280; display: block; margin-bottom: 5px; font-weight: 500;">From
                            (optional)</label>
                        <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #4b5563;">
                    </div>
                    <div>
                        <label
                            style="font-size: 13px; color: #6b7280; display: block; margin-bottom: 5px; font-weight: 500;">To
                            (optional)</label>
                        <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #4b5563;">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"
                        style="flex: 1; padding: 12px 15px; font-weight: 700; background: #7c5ea5; border: none; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: white; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span style="font-size: 14px;">🔍</span> SHOW HISTORY
                    </button>
                    <a href="?page=vehicle-history" class="btn btn-secondary"
                        style="text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 12px 25px; background: #6b7280; border-radius: 6px; font-weight: 600; color: white; gap: 6px;">
                        <span style="font-size: 14px;">🔄</span> RESET
                    </a>
                </div>
            </form>

            <?php if (!empty($vehicle_number)): ?>
                <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: center;">
                    <span
                        style="background: #f3f4f6; padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 13px; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">Vehicle:
                        <?php echo htmlspecialchars($vehicle_number); ?></span>
                    <span
                        style="background: #f3f4f6; padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 13px; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">Truck
                        Visits: <?php echo count($visits); ?></span>
                    <span
                        style="background: #f3f4f6; padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 13px; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">Employee
                        Visits: <?php echo count($employee_visits); ?></span>
                </div>

                <?php
                $timeline = [];
                // Build Truck timeline
                foreach ($visits as $v) {
                    $ref = 'IN' . date('Ymd', strtotime($v['inward_datetime'])) . str_pad($v['id'], 4, '0', STR_PAD_LEFT);

                    // Inward
                    $purpose = !empty($v['purpose_name']) ? $v['purpose_name'] : 'Material Delivery';
                    $transporter = !empty($v['transporter_name']) ? $v['transporter_name'] : 'Fast Movers Transport';
                    $timeline[] = [
                        'datetime' => $v['inward_datetime'],
                        'type_label' => 'TRUCK INWARD',
                        'type_icon' => '🚛',
                        'border_color' => '#3b82f6',
                        'text_color' => '#1d4ed8',
                        'ref' => $ref,
                        'summary' => 'Driver: ' . htmlspecialchars($v['driver_name'] ?? '') . ' | Transporter: ' . htmlspecialchars($transporter) . ' | Purpose: ' . htmlspecialchars($purpose)
                    ];

                    // Outward
                    if (!empty($v['outward_datetime'])) {
                        $remarks = !empty($v['outward_remarks']) ? ' | Remarks: ' . htmlspecialchars($v['outward_remarks']) : 'ok';
                        $timeline[] = [
                            'datetime' => $v['outward_datetime'],
                            'type_label' => 'TRUCK EXIT',
                            'type_icon' => '➡️',
                            'border_color' => '#10b981',
                            'text_color' => '#059669',
                            'ref' => $ref,
                            'summary' => 'Exited by: ' . htmlspecialchars($v['outward_by_name'] ?? 'System Administrator') . $remarks
                        ];
                    }

                    $in_id = (string) $v['id'];
                    $l_docs = isset($loading_by_inward[$in_id]) ? $loading_by_inward[$in_id] : (isset($v['_loading_legacy']) ? $v['_loading_legacy'] : []);
                    foreach ($l_docs as $doc) {
                        $timeline[] = [
                            'datetime' => $doc['reporting_datetime'],
                            'type_label' => 'LOADING',
                            'type_icon' => '📦',
                            'border_color' => '#f472b6',
                            'text_color' => '#db2777',
                            'ref' => $ref,
                            'summary' => 'Body: ' . htmlspecialchars($doc['vehicle_body_type'] ?? 'Container') . ' | Transporter: ' . htmlspecialchars($doc['transport_company_name'] ?? $transporter)
                        ];
                    }

                    $u_docs = isset($unloading_by_inward[$in_id]) ? $unloading_by_inward[$in_id] : (isset($v['_unloading_legacy']) ? $v['_unloading_legacy'] : []);
                    foreach ($u_docs as $doc) {
                        $timeline[] = [
                            'datetime' => $doc['reporting_datetime'],
                            'type_label' => 'UNLOADING',
                            'type_icon' => '📥',
                            'border_color' => '#f59e0b',
                            'text_color' => '#d97706',
                            'ref' => $ref,
                            'summary' => 'Body: ' . htmlspecialchars($doc['vehicle_body_type'] ?? 'Container') . ' | Transporter: ' . htmlspecialchars($doc['transport_company_name'] ?? $transporter)
                        ];
                    }
                }

                // Build Employee timeline
                foreach ($employee_visits as $ev) {
                    $ref = 'EMP' . date('Ymd', strtotime($ev['inward_datetime'])) . str_pad($ev['id'], 4, '0', STR_PAD_LEFT);
                    $timeline[] = [
                        'datetime' => $ev['inward_datetime'],
                        'type_label' => 'EMP INWARD',
                        'type_icon' => '👤',
                        'border_color' => '#6366f1',
                        'text_color' => '#4338ca',
                        'ref' => $ref,
                        'summary' => 'Employee: ' . htmlspecialchars($ev['employee_name'] ?? '') . ' | Dept: ' . htmlspecialchars($ev['department'] ?? '')
                    ];
                    if (!empty($ev['outward_datetime'])) {
                        $timeline[] = [
                            'datetime' => $ev['outward_datetime'],
                            'type_label' => 'EMP EXIT',
                            'type_icon' => '🚶',
                            'border_color' => '#6366f1',
                            'text_color' => '#4338ca',
                            'ref' => $ref,
                            'summary' => 'Exited by: ' . htmlspecialchars($ev['outward_by_name'] ?? 'System')
                        ];
                    }
                }

                usort($timeline, function ($a, $b) {
                    return strtotime($b['datetime']) <=> strtotime($a['datetime']);
                });
                ?>

                <div
                    style="border: 1px solid #e5e7eb; border-radius: 12px; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
                    <div style="padding: 15px 20px; border-bottom: 1px solid #e5e7eb; background: #ffffff;">
                        <h3
                            style="margin: 0; font-size: 15px; color: #111827; display: flex; align-items: center; gap: 8px; font-weight: 700;">
                            📅 Chronological Timeline (Newest First)
                        </h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e5e7eb; color: #6b7280; background: #ffffff;">
                                    <th style="padding: 12px 20px; font-weight: 600;">Date/Time</th>
                                    <th style="padding: 12px 20px; font-weight: 600;">Entry Type</th>
                                    <th style="padding: 12px 20px; font-weight: 600;">Visit (Ref)</th>
                                    <th style="padding: 12px 20px; font-weight: 600;">Summary</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($timeline) === 0): ?>
                                    <tr>
                                        <td colspan="4" style="padding: 20px; text-align: center; color: #6b7280;">No history found
                                            for this vehicle.</td>
                                    </tr>
                                    <?php
                                else: ?>
                                    <?php foreach ($timeline as $t): ?>
                                        <tr style="border-bottom: 1px solid #e5e7eb; position: relative; background: #ffffff;">
                                            <td style="padding: 16px 20px; font-weight: 700; color: #111827;">
                                                <?php echo date('d/m/Y h:i A', strtotime($t['datetime'])); ?>
                                            </td>
                                            <td style="padding: 16px 20px; font-weight: 700; color: <?php echo $t['text_color']; ?>;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <span style="font-size: 14px;"><?php echo $t['type_icon']; ?></span>
                                                    <span><?php echo htmlspecialchars($t['type_label']); ?></span>
                                                </div>
                                            </td>
                                            <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">
                                                <?php echo htmlspecialchars($t['ref']); ?>
                                            </td>
                                            <td style="padding: 16px 20px; color: #6b7280; font-weight: 400; position: relative;">
                                                <?php echo $t['summary']; ?>
                                                <div
                                                    style="position: absolute; right: 2px; top: 15px; width: 4px; height: 24px; border-radius: 4px; background: <?php echo $t['border_color']; ?>; opacity: 1;">
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach; ?>
                                    <?php
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endif; ?>

        </div> <!-- end card -->
    </div> <!-- end container -->

    <?php
    // ==================== DOCUMENT EXPIRY ALERTS ====================
elseif ($page == 'document-expiry-alerts'):

    // Check for documents expiring within 15 days - Group by Vehicle
    $alert_days = 15;
    $alert_threshold = date('Y-m-d', strtotime("+$alert_days days"));
    $today = date('Y-m-d');

    // Get all vehicles with expiring documents
    $vehicles_query = mysqli_query($conn, "SELECT id, vehicle_number, 
        registration_validity, 
        fitness_validity, 
        pollution_validity, 
        insurance_validity,
        permit_validity,
        DATEDIFF(COALESCE(
            LEAST(
                COALESCE(registration_validity, '9999-12-31'),
                COALESCE(fitness_validity, '9999-12-31'),
                COALESCE(pollution_validity, '9999-12-31'),
                COALESCE(insurance_validity, '9999-12-31'),
                COALESCE(permit_validity, '9999-12-31')
            ), '9999-12-31'
        ), CURDATE()) as min_days_remaining
        FROM vehicle_master 
        WHERE (
            (registration_validity IS NOT NULL AND registration_validity <= '$alert_threshold') OR
            (fitness_validity IS NOT NULL AND fitness_validity <= '$alert_threshold') OR
            (pollution_validity IS NOT NULL AND pollution_validity <= '$alert_threshold') OR
            (insurance_validity IS NOT NULL AND insurance_validity <= '$alert_threshold') OR
            (permit_validity IS NOT NULL AND permit_validity <= '$alert_threshold')
        )
        ORDER BY min_days_remaining ASC");

    $vehicles_with_alerts = [];
    while ($vehicle = mysqli_fetch_assoc($vehicles_query)) {
        $vehicle_id = $vehicle['id'];
        $vehicle_number = $vehicle['vehicle_number'];
        $docs = [];

        // Check each document type
        if ($vehicle['registration_validity'] && $vehicle['registration_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['registration_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Registration Certificate',
                'expiry_date' => $vehicle['registration_validity'],
                'days_remaining' => $days,
                'icon' => '🆔'
            ];
        }

        if ($vehicle['fitness_validity'] && $vehicle['fitness_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['fitness_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Fitness Certificate',
                'expiry_date' => $vehicle['fitness_validity'],
                'days_remaining' => $days,
                'icon' => '✅'
            ];
        }

        if ($vehicle['pollution_validity'] && $vehicle['pollution_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['pollution_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Pollution Certificate',
                'expiry_date' => $vehicle['pollution_validity'],
                'days_remaining' => $days,
                'icon' => '🌿'
            ];
        }

        if ($vehicle['insurance_validity'] && $vehicle['insurance_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['insurance_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Insurance Certificate',
                'expiry_date' => $vehicle['insurance_validity'],
                'days_remaining' => $days,
                'icon' => '🛡️'
            ];
        }

        if (isset($vehicle['permit_validity']) && $vehicle['permit_validity'] && $vehicle['permit_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['permit_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Permit',
                'expiry_date' => $vehicle['permit_validity'],
                'days_remaining' => $days,
                'icon' => '📄'
            ];
        }

        if (count($docs) > 0) {
            // Sort documents by urgency
            usort($docs, function ($a, $b) {
                return $a['days_remaining'] - $b['days_remaining'];
            });

            $vehicles_with_alerts[] = [
                'id' => $vehicle_id,
                'vehicle_number' => $vehicle_number,
                'link' => "?page=vehicle-detail&id=$vehicle_id",
                'docs' => $docs,
                'min_days_remaining' => $vehicle['min_days_remaining']
            ];
        }
    }

    // Get expiring driver licenses (separate section)
    $expiring_licenses = [];
    $licenses_query = mysqli_query($conn, "SELECT id, driver_name, mobile, license_number, license_expiry, DATEDIFF(license_expiry, CURDATE()) as days_remaining 
        FROM driver_master 
        WHERE license_expiry IS NOT NULL 
        AND license_expiry <= '$alert_threshold' 
        AND is_active = 1 
        ORDER BY license_expiry ASC");
    while ($row = mysqli_fetch_assoc($licenses_query)) {
        $expiring_licenses[] = [
            'id' => $row['id'],
            'driver_name' => $row['driver_name'],
            'license_number' => $row['license_number'],
            'expiry_date' => $row['license_expiry'],
            'days_remaining' => $row['days_remaining'],
            'link' => "?page=driver-detail&id={$row['id']}"
        ];
    }

    // Sort vehicles by most urgent document
    usort($vehicles_with_alerts, function ($a, $b) {
        return $a['min_days_remaining'] - $b['min_days_remaining'];
    });
    ?>
    <div class="container">
        <div
            style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0;">⚠️ Document Expiry Alerts</h1>
            <button onclick="goBack()" class="btn btn-secondary"
                style="padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; border-radius: 6px; background: #6b7280; color: white; transition: all 0.2s;"
                onmouseover="this.style.background='#4b5563'; this.style.transform='scale(1.05)';"
                onmouseout="this.style.background='#6b7280'; this.style.transform='scale(1)';">
                ← Back
            </button>
        </div>

        <div
            style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
            <div
                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: #92400e; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Document Expiry Alerts (Expiring within
                    <?php echo $alert_days; ?> days)
                </h3>
                <span
                    style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    <?php echo count($vehicles_with_alerts); ?>
                    Vehicle
                    <?php echo count($vehicles_with_alerts) != 1 ? 's' : ''; ?>
                    <?php if (count($expiring_licenses) > 0): ?>
                        ,
                        <?php echo count($expiring_licenses); ?>
                        Driver
                        <?php echo count($expiring_licenses) != 1 ? 's' : ''; ?>
                        <?php
                    endif; ?>
                </span>
            </div>
            <div>
                <?php if (count($vehicles_with_alerts) > 0): ?>
                    <?php foreach ($vehicles_with_alerts as $vehicle):
                        $is_urgent = $vehicle['min_days_remaining'] <= 7 || $vehicle['min_days_remaining'] < 0;
                        $bg_color = $is_urgent ? '#fee2e2' : '#fef3c7';
                        $border_color = $is_urgent ? '#ef4444' : '#f59e0b';
                        ?>
                        <div
                            style="background: <?php echo $bg_color; ?>; border-left: 4px solid <?php echo $border_color; ?>; padding: 15px; margin-bottom: 12px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div
                                style="display: flex; align-items: start; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                <div style="flex: 1; min-width: 250px;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                        <span style="font-size: 24px;">🚛</span>
                                        <div>
                                            <strong style="color: #92400e; font-size: 18px;">
                                                <?php echo htmlspecialchars($vehicle['vehicle_number']); ?>
                                            </strong>
                                            <div
                                                style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 600; font-size: 14px; margin-top: 2px;">
                                                <?php
                                                if ($vehicle['min_days_remaining'] < 0) {
                                                    echo '❌ Most urgent document expired ' . abs($vehicle['min_days_remaining']) . ' day' . (abs($vehicle['min_days_remaining']) > 1 ? 's' : '') . ' ago!';
                                                } elseif ($vehicle['min_days_remaining'] == 0) {
                                                    echo '⚠️ Most urgent document expires today!';
                                                } elseif ($vehicle['min_days_remaining'] <= 7) {
                                                    echo '⚠️ Most urgent document expires in ' . $vehicle['min_days_remaining'] . ' days!';
                                                } else {
                                                    echo '⚠️ Documents expiring within ' . $alert_days . ' days';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-left: 34px;">
                                        <?php foreach ($vehicle['docs'] as $doc):
                                            $doc_urgent = $doc['days_remaining'] <= 7 || $doc['days_remaining'] < 0;
                                            ?>
                                            <div
                                                style="background: <?php echo $doc_urgent ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; padding: 10px; margin-bottom: 8px; border-radius: 5px; border-left: 3px solid <?php echo $doc_urgent ? '#ef4444' : '#f59e0b'; ?>;">
                                                <div
                                                    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                                    <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                                        <span style="font-size: 18px;">
                                                            <?php echo $doc['icon']; ?>
                                                        </span>
                                                        <div>
                                                            <strong style="color: #78350f; font-size: 14px;">
                                                                <?php echo htmlspecialchars($doc['type']); ?>
                                                            </strong>
                                                            <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                Expires:
                                                                <?php echo date('d M, Y', strtotime($doc['expiry_date'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <div
                                                            style="color: <?php echo $doc_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 700; font-size: 14px;">
                                                            <?php
                                                            if ($doc['days_remaining'] < 0) {
                                                                echo '❌ Expired ' . abs($doc['days_remaining']) . ' day' . (abs($doc['days_remaining']) > 1 ? 's' : '') . ' ago';
                                                            } elseif ($doc['days_remaining'] == 0) {
                                                                echo '⚠️ Expires Today';
                                                            } elseif ($doc['days_remaining'] == 1) {
                                                                echo '⚠️ Expires Tomorrow';
                                                            } else {
                                                                echo $doc['days_remaining'] . ' days left';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        endforeach; ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <a href="<?php echo $vehicle['link']; ?>"
                                        style="background: <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap; transition: all 0.2s;"
                                        onmouseover="this.style.background='<?php echo $is_urgent ? '#dc2626' : '#d97706'; ?>'; this.style.transform='scale(1.05)';"
                                        onmouseout="this.style.background='<?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>'; this.style.transform='scale(1)';">
                                        View Vehicle →
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>

                    <?php if (count($expiring_licenses) > 0): ?>
                        <!-- Driver License Alerts -->
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f59e0b;">
                            <h4
                                style="color: #92400e; font-size: 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <span>🪪</span> Driver License Expiry Alerts
                            </h4>
                            <?php foreach ($expiring_licenses as $license):
                                $is_urgent = $license['days_remaining'] <= 7 || $license['days_remaining'] < 0;
                                $bg_color = $is_urgent ? '#fee2e2' : '#fef3c7';
                                $border_color = $is_urgent ? '#ef4444' : '#f59e0b';
                                ?>
                                <div
                                    style="background: <?php echo $bg_color; ?>; border-left: 4px solid <?php echo $border_color; ?>; padding: 15px; margin-bottom: 12px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    <div
                                        style="display: flex; align-items: start; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                        <div style="flex: 1; min-width: 250px;">
                                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                                <span style="font-size: 24px;">🪪</span>
                                                <div>
                                                    <strong style="color: #92400e; font-size: 18px;">
                                                        <?php echo htmlspecialchars($license['driver_name']); ?>
                                                    </strong>
                                                    <div
                                                        style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 600; font-size: 14px; margin-top: 2px;">
                                                        <?php
                                                        if ($license['days_remaining'] < 0) {
                                                            echo '❌ License expired ' . abs($license['days_remaining']) . ' day' . (abs($license['days_remaining']) > 1 ? 's' : '') . ' ago!';
                                                        } elseif ($license['days_remaining'] == 0) {
                                                            echo '⚠️ License expires today!';
                                                        } elseif ($license['days_remaining'] <= 7) {
                                                            echo '⚠️ License expires in ' . $license['days_remaining'] . ' days!';
                                                        } else {
                                                            echo '⚠️ License expiring within ' . $alert_days . ' days';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="margin-left: 34px;">
                                                <div
                                                    style="background: <?php echo $is_urgent ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; padding: 10px; margin-bottom: 8px; border-radius: 5px; border-left: 3px solid <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>;">
                                                    <div
                                                        style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                                        <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                                            <span style="font-size: 18px;">🪪</span>
                                                            <div>
                                                                <strong style="color: #78350f; font-size: 14px;">Driver
                                                                    License</strong>
                                                                <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                    License Number:
                                                                    <strong>
                                                                        <?php echo htmlspecialchars($license['license_number']); ?>
                                                                    </strong>
                                                                </div>
                                                                <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                    Expires:
                                                                    <?php echo date('d M, Y', strtotime($license['expiry_date'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div style="text-align: right;">
                                                            <div
                                                                style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 700; font-size: 14px;">
                                                                <?php
                                                                if ($license['days_remaining'] < 0) {
                                                                    echo '❌ Expired ' . abs($license['days_remaining']) . ' day' . (abs($license['days_remaining']) > 1 ? 's' : '') . ' ago';
                                                                } elseif ($license['days_remaining'] == 0) {
                                                                    echo '⚠️ Expires Today';
                                                                } elseif ($license['days_remaining'] == 1) {
                                                                    echo '⚠️ Expires Tomorrow';
                                                                } else {
                                                                    echo $license['days_remaining'] . ' days left';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <a href="<?php echo $license['link']; ?>"
                                                style="background: <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap; transition: all 0.2s;"
                                                onmouseover="this.style.background='<?php echo $is_urgent ? '#dc2626' : '#d97706'; ?>'; this.style.transform='scale(1.05)';"
                                                onmouseout="this.style.background='<?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>'; this.style.transform='scale(1)';">
                                                View Driver →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endforeach; ?>
                        </div>
                        <?php
                    endif; ?>
                    <?php
                else: ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <div style="font-size: 48px; margin-bottom: 15px;">✅</div>
                        <h3 style="color: #374151; margin-bottom: 10px;">No Expiring Documents</h3>
                        <p>All documents are up to date!</p>
                    </div>
                    <?php
                endif; ?>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            // Check if there's a previous page in history
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Fallback to dashboard if no history
                window.location.href = '?page=dashboard';
            }
        }
    </script>

    <?php
    // ==================== MANAGEMENT DASHBOARD ====================
elseif ($page == 'management'):

    // Date filters
    $filter_period = isset($_GET['period']) ? $_GET['period'] : 'today';
    $custom_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $custom_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Determine date range based on selected period
    switch ($filter_period) {
        case 'custom':
            // Validate custom dates
            if (!empty($custom_start) && !empty($custom_end)) {
                $period_start = date('Y-m-d', strtotime($custom_start));
                $period_end = date('Y-m-d', strtotime($custom_end));
                // Ensure start is before end
                if ($period_start > $period_end) {
                    $temp = $period_start;
                    $period_start = $period_end;
                    $period_end = $temp;
                }
                $period_condition = "DATE(inward_date) >= '$period_start' AND DATE(inward_date) <= '$period_end'";
                $period_condition_outward = "DATE(outward_date) >= '$period_start' AND DATE(outward_date) <= '$period_end'";
                $period_label = date('d/m/Y', strtotime($period_start)) . " - " . date('d/m/Y', strtotime($period_end));
                $period_label_short = "Custom";
                $days_back = (strtotime($period_end) - strtotime($period_start)) / 86400;
            } else {
                // Fallback to today if custom dates not provided
                $period_start = date('Y-m-d');
                $period_condition = "DATE(inward_date) = CURDATE()";
                $period_condition_outward = "DATE(outward_date) = CURDATE()";
                $period_label = "Today";
                $period_label_short = "Today";
                $days_back = 0;
            }
            break;
        case 'today':
            $period_start = date('Y-m-d');
            $period_condition = "DATE(inward_date) = CURDATE()";
            $period_condition_outward = "DATE(outward_date) = CURDATE()";
            $period_label = "Today";
            $period_label_short = "Today";
            $days_back = 0;
            break;
        case 'week':
            $period_start = date('Y-m-d', strtotime('monday this week'));
            $period_condition = "inward_date >= '$period_start'";
            $period_condition_outward = "outward_date >= '$period_start'";
            $period_label = "This Week";
            $period_label_short = "Week";
            $days_back = 7;
            break;
        case 'month':
            $period_start = date('Y-m-01');
            $period_condition = "inward_date >= '$period_start'";
            $period_condition_outward = "outward_date >= '$period_start'";
            $period_label = "This Month";
            $period_label_short = "Month";
            $days_back = 30;
            break;
        case 'year':
            $period_start = date('Y-01-01');
            $period_condition = "inward_date >= '$period_start'";
            $period_condition_outward = "outward_date >= '$period_start'";
            $period_label = "This Year";
            $period_label_short = "Year";
            $days_back = 365;
            break;
        case 'all':
            $period_start = '1970-01-01'; // Very old date to include all
            $period_condition = "1=1"; // Always true - no date filter
            $period_condition_outward = "1=1"; // Always true - no date filter
            $period_label = "All Time";
            $period_label_short = "All Time";
            $days_back = 999999;
            break;
        default:
            $period_start = date('Y-m-d');
            $period_condition = "DATE(inward_date) = CURDATE()";
            $period_condition_outward = "DATE(outward_date) = CURDATE()";
            $period_label = "Today";
            $period_label_short = "Today";
            $days_back = 0;
    }

    // Initialize loading/unloading tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Get stats for selected period
    $period_inward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE $period_condition"));
    $period_outward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_outward WHERE $period_condition_outward"));
    $today_inside = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE status = 'inside'"));

    // Get loading/unloading stats
    $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
    $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");
    $period_loading = 0;
    $period_unloading = 0;
    $recent_loading_entries = [];
    $recent_unloading_entries = [];

    if (mysqli_num_rows($check_loading) > 0) {
        $loading_condition = str_replace('inward_date', 'DATE(reporting_datetime)', $period_condition);
        $period_loading = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist WHERE $loading_condition"));

        // Get recent loading entries for the period
        $recent_loading_query = mysqli_query($conn, "SELECT id, document_id, vehicle_registration_number, driver_name, transport_company_name, loading_location, reporting_datetime, status FROM vehicle_loading_checklist WHERE $loading_condition ORDER BY reporting_datetime DESC LIMIT 5");
        while ($row = mysqli_fetch_assoc($recent_loading_query)) {
            $recent_loading_entries[] = $row;
        }
    }

    if (mysqli_num_rows($check_unloading) > 0) {
        $unloading_condition = str_replace('inward_date', 'DATE(reporting_datetime)', $period_condition);
        $period_unloading = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist WHERE $unloading_condition"));

        // Get recent unloading entries for the period
        $recent_unloading_query = mysqli_query($conn, "SELECT id, document_id, vehicle_registration_number, driver_name, transport_company_name, vendor_name, reporting_datetime, status FROM vehicle_unloading_checklist WHERE $unloading_condition ORDER BY reporting_datetime DESC LIMIT 5");
        while ($row = mysqli_fetch_assoc($recent_unloading_query)) {
            $recent_unloading_entries[] = $row;
        }
    }

    // Operational Metrics
    $avg_duration_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(duration_hours) as avg_duration FROM truck_outward WHERE $period_condition_outward"));
    $avg_duration = round($avg_duration_result['avg_duration'] ?? 0, 2);

    $total_duration_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(duration_hours) as total_duration FROM truck_outward WHERE $period_condition_outward"));
    $total_duration = round($total_duration_result['total_duration'] ?? 0, 2);

    // Peak hours analysis (based on period)
    if ($filter_period == 'today') {
        $peak_hours = mysqli_query($conn, "SELECT HOUR(inward_time) as hour, COUNT(*) as count FROM truck_inward WHERE DATE(inward_date) = CURDATE() GROUP BY HOUR(inward_time) ORDER BY count DESC LIMIT 5");
    } else {
        $peak_hours = mysqli_query($conn, "SELECT HOUR(inward_time) as hour, COUNT(*) as count FROM truck_inward WHERE $period_condition GROUP BY HOUR(inward_time) ORDER BY count DESC LIMIT 5");
    }

    // Top Transporters (based on period)
    $top_transporters = mysqli_query($conn, "SELECT transporter_name, COUNT(*) as trips FROM truck_inward WHERE $period_condition AND transporter_name IS NOT NULL AND transporter_name != '' GROUP BY transporter_name ORDER BY trips DESC LIMIT 10");

    // Top Vehicles (based on period)
    $top_vehicles = mysqli_query($conn, "SELECT vehicle_number, COUNT(*) as trips FROM truck_inward WHERE $period_condition GROUP BY vehicle_number ORDER BY trips DESC LIMIT 10");

    // Top Drivers (based on period)
    $top_drivers = mysqli_query($conn, "SELECT driver_name, driver_mobile, COUNT(*) as trips FROM truck_inward WHERE $period_condition GROUP BY driver_name, driver_mobile ORDER BY trips DESC LIMIT 10");

    // Purpose Distribution (based on period)
    $purpose_dist = mysqli_query($conn, "SELECT purpose_name, COUNT(*) as count FROM truck_inward WHERE $period_condition AND purpose_name IS NOT NULL GROUP BY purpose_name ORDER BY count DESC");

    // Purpose Type Distribution (based on period)
    $purpose_type_dist = mysqli_query($conn, "SELECT pm.purpose_type, COUNT(*) as count FROM truck_inward ti LEFT JOIN purpose_master pm ON ti.purpose_id = pm.id WHERE $period_condition AND pm.purpose_type IS NOT NULL GROUP BY pm.purpose_type ORDER BY count DESC");

    // Master Data Statistics (static - not filtered)
    $total_transporters = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transporter_master WHERE is_active=1"));
    $total_drivers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM driver_master WHERE is_active=1"));
    $total_vehicles = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_master"));
    $total_purposes = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM purpose_master WHERE is_active=1"));

    $m_db = MASTER_DB_NAME;
    $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM $m_db.user_master WHERE is_active=1"));

    // Daily trend (based on period - show last 7 days for today/week, last 30 for month, last 12 months for year, all dates for custom, monthly for all time)
    if ($filter_period == 'all') {
        // For all time, show monthly view (last 12 months)
        $daily_trend = mysqli_query($conn, "SELECT DATE_FORMAT(inward_date, '%Y-%m') as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE_FORMAT(outward_date, '%Y-%m') = DATE_FORMAT(ti.inward_date, '%Y-%m')) as outward_count FROM truck_inward ti GROUP BY DATE_FORMAT(inward_date, '%Y-%m') ORDER BY date DESC LIMIT 12");
    } elseif ($filter_period == 'year') {
        $daily_trend = mysqli_query($conn, "SELECT DATE_FORMAT(inward_date, '%Y-%m') as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE_FORMAT(outward_date, '%Y-%m') = DATE_FORMAT(ti.inward_date, '%Y-%m')) as outward_count FROM truck_inward ti WHERE $period_condition GROUP BY DATE_FORMAT(inward_date, '%Y-%m') ORDER BY date DESC LIMIT 12");
    } elseif ($filter_period == 'month') {
        $daily_trend = mysqli_query($conn, "SELECT DATE(inward_date) as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE(outward_date) = DATE(ti.inward_date)) as outward_count FROM truck_inward ti WHERE $period_condition GROUP BY DATE(inward_date) ORDER BY date DESC LIMIT 30");
    } elseif ($filter_period == 'custom') {
        if ($days_back > 90) {
            // For custom ranges longer than 90 days, show monthly view
            $daily_trend = mysqli_query($conn, "SELECT DATE_FORMAT(inward_date, '%Y-%m') as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE_FORMAT(outward_date, '%Y-%m') = DATE_FORMAT(ti.inward_date, '%Y-%m')) as outward_count FROM truck_inward ti WHERE $period_condition GROUP BY DATE_FORMAT(inward_date, '%Y-%m') ORDER BY date DESC");
        } else {
            // For custom ranges 90 days or less, show daily view (all days in range)
            $daily_trend = mysqli_query($conn, "SELECT DATE(inward_date) as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE(outward_date) = DATE(ti.inward_date)) as outward_count FROM truck_inward ti WHERE $period_condition GROUP BY DATE(inward_date) ORDER BY date DESC");
        }
    } else {
        $daily_trend = mysqli_query($conn, "SELECT DATE(inward_date) as date, COUNT(*) as inward_count, (SELECT COUNT(*) FROM truck_outward WHERE DATE(outward_date) = DATE(ti.inward_date)) as outward_count FROM truck_inward ti WHERE $period_condition GROUP BY DATE(inward_date) ORDER BY date DESC LIMIT 7");
    }

    // Efficiency Metrics
    $pending_exits = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE status = 'inside' AND inward_date < CURDATE()"));
    $period_efficiency = $period_inward > 0 ? round(($period_outward / $period_inward) * 100, 1) : 0;

    // Longest duration trucks (currently inside) - filtered by selected period
    // Only show trucks that entered during the selected period and are still inside
    if ($filter_period == 'all') {
        $longest_inside = mysqli_query($conn, "SELECT entry_number, vehicle_number, driver_name, transporter_name, inward_datetime, TIMESTAMPDIFF(HOUR, inward_datetime, NOW()) as hours_inside FROM truck_inward WHERE status = 'inside' ORDER BY hours_inside DESC LIMIT 5");
    } else {
        // Convert period_condition to work with inward_datetime field
        $longest_condition = str_replace('inward_date', 'DATE(inward_datetime)', $period_condition);
        $longest_inside = mysqli_query($conn, "SELECT entry_number, vehicle_number, driver_name, transporter_name, inward_datetime, TIMESTAMPDIFF(HOUR, inward_datetime, NOW()) as hours_inside FROM truck_inward WHERE status = 'inside' AND ($longest_condition) ORDER BY hours_inside DESC LIMIT 5");
    }

    // Expiring Documents - Vehicles with documents expiring in next 90 days or already expired
    $days_ahead = 90;
    $expiry_threshold = date('Y-m-d', strtotime("+$days_ahead days"));

    // Expiring Fitness Certificates (RC Fitness)
    $expiring_fitness = mysqli_query($conn, "SELECT id, vehicle_number, fitness_validity, DATEDIFF(fitness_validity, CURDATE()) as days_remaining FROM vehicle_master WHERE fitness_validity IS NOT NULL AND fitness_validity <= '$expiry_threshold' ORDER BY fitness_validity ASC LIMIT 20");

    // Expiring Pollution Certificates
    $expiring_pollution = mysqli_query($conn, "SELECT id, vehicle_number, pollution_validity, DATEDIFF(pollution_validity, CURDATE()) as days_remaining FROM vehicle_master WHERE pollution_validity IS NOT NULL AND pollution_validity <= '$expiry_threshold' ORDER BY pollution_validity ASC LIMIT 20");

    // Expiring Insurance Certificates
    $expiring_insurance = mysqli_query($conn, "SELECT id, vehicle_number, insurance_validity, DATEDIFF(insurance_validity, CURDATE()) as days_remaining FROM vehicle_master WHERE insurance_validity IS NOT NULL AND insurance_validity <= '$expiry_threshold' ORDER BY insurance_validity ASC LIMIT 20");

    // Expiring RC (Registration Certificate) - Based on registration date + 15 years
    $expiring_rc = mysqli_query($conn, "SELECT id, vehicle_number, registration_validity, fitness_validity, 
        registration_validity as rc_expiry,
        DATEDIFF(registration_validity, CURDATE()) as rc_days_remaining
        FROM vehicle_master 
        WHERE registration_validity IS NOT NULL 
        AND registration_validity <= '$expiry_threshold'
        ORDER BY registration_validity ASC LIMIT 20");

    // Expiring Driver Licenses
    $expiring_licenses = mysqli_query($conn, "SELECT id, driver_name, mobile, license_number, license_expiry, DATEDIFF(license_expiry, CURDATE()) as days_remaining FROM driver_master WHERE license_expiry IS NOT NULL AND license_expiry <= '$expiry_threshold' AND is_active = 1 ORDER BY license_expiry ASC LIMIT 20");
    ?>
    <div class="container" style="padding-bottom: 120px;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0; font-size: 24px;">📊 Management Dashboard</h1>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <a href="?page=management&period=today"
                    class="btn <?php echo $filter_period == 'today' ? 'btn-primary' : 'btn-secondary'; ?>"
                    style="padding: 8px 15px; font-size: 13px;">Today</a>
                <a href="?page=management&period=week"
                    class="btn <?php echo $filter_period == 'week' ? 'btn-primary' : 'btn-secondary'; ?>"
                    style="padding: 8px 15px; font-size: 13px;">Week</a>
                <a href="?page=management&period=month"
                    class="btn <?php echo $filter_period == 'month' ? 'btn-primary' : 'btn-secondary'; ?>"
                    style="padding: 8px 15px; font-size: 13px;">Month</a>
                <a href="?page=management&period=year"
                    class="btn <?php echo $filter_period == 'year' ? 'btn-primary' : 'btn-secondary'; ?>"
                    style="padding: 8px 15px; font-size: 13px;">Year</a>
                <a href="?page=management&period=all"
                    class="btn <?php echo $filter_period == 'all' ? 'btn-primary' : 'btn-secondary'; ?>"
                    style="padding: 8px 15px; font-size: 13px;">All Time</a>

                <!-- Custom Date Range -->
                <div
                    style="display: flex; gap: 5px; align-items: center; padding: 5px; background: <?php echo $filter_period == 'custom' ? '#EEF2FF' : 'transparent'; ?>; border-radius: 8px; border: 2px solid <?php echo $filter_period == 'custom' ? '#4F46E5' : '#e5e7eb'; ?>;">
                    <form method="GET" action="" style="display: flex; gap: 5px; align-items: center;">
                        <input type="hidden" name="page" value="management">
                        <input type="hidden" name="period" value="custom">
                        <input type="date" name="start_date"
                            value="<?php echo !empty($custom_start) ? htmlspecialchars($custom_start) : date('Y-m-d', strtotime('-7 days')); ?>"
                            style="padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; width: 140px;">
                        <span style="color: #666; font-size: 12px;">to</span>
                        <input type="date" name="end_date"
                            value="<?php echo !empty($custom_end) ? htmlspecialchars($custom_end) : date('Y-m-d'); ?>"
                            style="padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; width: 140px;">
                        <button type="submit"
                            class="btn <?php echo $filter_period == 'custom' ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 6px 12px; font-size: 12px; margin: 0;">Apply</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;"
            class="responsive-grid-2">
            <!-- Operational Metrics -->
            <div class="card" style="overflow: hidden; word-wrap: break-word;">
                <h3 style="margin-top: 0;">⚙️ Operational Metrics</h3>
                <div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Average Duration (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="white-space: nowrap;">
                            <?php echo formatDuration($avg_duration); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Total Duration (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="white-space: nowrap;">
                            <?php echo formatDuration($total_duration); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Inward Count (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($period_inward); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Outward Count (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($period_outward); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Efficiency (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span
                            style="color: <?php echo $period_efficiency >= 80 ? '#10b981' : ($period_efficiency >= 60 ? '#f59e0b' : '#ef4444'); ?>; font-weight: 600; white-space: nowrap;">
                            <?php echo $period_efficiency; ?>%
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Loading Checklists (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="color: #ec4899; font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($period_loading); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Unloading Checklists (
                            <?php echo $period_label; ?>)
                        </strong>
                        <span style="color: #14b8a6; font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($period_unloading); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <strong>Pending Exits</strong>
                        <span
                            style="color: <?php echo $pending_exits > 0 ? '#ef4444' : '#10b981'; ?>; font-weight: 600; white-space: nowrap;">
                            <?php echo $pending_exits; ?>
                        </span>
                    </div>

                    <!-- Ticket Metrics -->
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #f3f4f6; margin-top: 5px;">
                        <strong>Open Tickets</strong>
                        <?php
                        $t_open = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM patrol_issues WHERE status IN ('Open', 'Assigned')"));
                        ?>
                        <span style="color: #f59e0b; font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($t_open); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Tickets Resolved (
                            <?php echo $period_label; ?>)
                        </strong>
                        <?php
                        $ticket_date_filter = "";
                        if ($filter_period == 'today')
                            $ticket_date_filter = "AND DATE(resolved_at) = CURDATE()";
                        elseif ($filter_period == 'week')
                            $ticket_date_filter = "AND resolved_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                        elseif ($filter_period == 'month')
                            $ticket_date_filter = "AND resolved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                        elseif ($filter_period == 'year')
                            $ticket_date_filter = "AND resolved_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                        elseif ($filter_period == 'custom' && !empty($custom_start) && !empty($custom_end))
                            $ticket_date_filter = "AND DATE(resolved_at) BETWEEN '$custom_start' AND '$custom_end'";

                        $t_resolved = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM patrol_issues WHERE status IN ('Resolved', 'Closed') $ticket_date_filter"));
                        ?>
                        <span style="color: #10b981; font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($t_resolved); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Master Data Overview -->
            <div class="card" style="overflow: hidden; word-wrap: break-word;">
                <h3 style="margin-top: 0;">📋 Master Data Overview</h3>
                <div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <span>🚚 Transporters</span>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($total_transporters); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <span>👨‍✈️ Drivers</span>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($total_drivers); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <span>🚛 Vehicles</span>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($total_vehicles); ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <span>🎯 Purposes</span>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($total_purposes); ?>
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span>👥 Users</span>
                        <span style="font-weight: 600; white-space: nowrap;">
                            <?php echo number_format($total_users); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- OPEN Ticket Aging Analysis -->
        <div class="card" style="margin-bottom: 20px;">
            <h3 style="margin-top: 0; display:flex; justify-content:space-between; align-items:center;">
                <span>⏳ Open Ticket Aging Analysis</span>
                <span
                    style="font-size:12px; font-weight:normal; background:#fee2e2; color:#ef4444; padding:2px 8px; border-radius:12px;">Open
                    Issues</span>
            </h3>
            <div class="responsive-grid-3"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <?php
                // Calculate Aging
                $aging_query = mysqli_query($conn, "SELECT 
                            SUM(CASE WHEN DATEDIFF(NOW(), created_at) = 0 THEN 1 ELSE 0 END) as less_24h,
                            SUM(CASE WHEN DATEDIFF(NOW(), created_at) = 1 THEN 1 ELSE 0 END) as between_24_48h,
                            SUM(CASE WHEN DATEDIFF(NOW(), created_at) > 1 THEN 1 ELSE 0 END) as more_48h
                            FROM patrol_issues WHERE status IN ('Open', 'Assigned')");
                $aging = mysqli_fetch_assoc($aging_query);
                ?>
                <div onclick="window.location.href='?page=tickets&aging=less_24h'"
                    style="background: #ecfdf5; padding: 15px; border-radius: 8px; border: 1px solid #a7f3d0; cursor: pointer; transition: transform 0.2s;">
                    <div style="color: #047857; font-size: 13px; font-weight: 600;">Fresh (< 24h)</div>
                            <div style="font-size: 24px; font-weight: bold; color: #065f46;">
                                <?php echo (int) $aging['less_24h']; ?>
                            </div>
                    </div>
                    <div onclick="window.location.href='?page=tickets&aging=24_48h'"
                        style="background: #fffbeb; padding: 15px; border-radius: 8px; border: 1px solid #fcd34d; cursor: pointer; transition: transform 0.2s;">
                        <div style="color: #b45309; font-size: 13px; font-weight: 600;">Warning (24-48 Hrs)</div>
                        <div style="font-size: 24px; font-weight: bold; color: #92400e;">
                            <?php echo (int) $aging['between_24_48h']; ?>
                        </div>
                    </div>
                    <div onclick="window.location.href='?page=tickets&aging=more_48h'"
                        style="background: #fef2f2; padding: 15px; border-radius: 8px; border: 1px solid #fca5a5; cursor: pointer; transition: transform 0.2s;">
                        <div style="color: #b91c1c; font-size: 13px; font-weight: 600;">Critical (2+ Days)</div>
                        <div style="font-size: 24px; font-weight: bold; color: #991b1b;">
                            <?php echo (int) $aging['more_48h']; ?>
                        </div>
                    </div>
                </div>

                <!-- Critical Tickets List -->
                <?php
                $critical_tickets = mysqli_query($conn, "SELECT pi.*, pl.location_name, em.employee_name as assigned_to_name 
                        FROM patrol_issues pi 
                        LEFT JOIN patrol_locations pl ON pi.location_id = pl.id 
                        LEFT JOIN employee_master em ON pi.assigned_to = em.id
                        WHERE pi.status IN ('Open', 'Assigned') AND DATEDIFF(NOW(), pi.created_at) >= 1 
                        ORDER BY pi.created_at ASC LIMIT 5");

                if (mysqli_num_rows($critical_tickets) > 0):
                    ?>
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #374151;">⚠️ Long Pending Issues (Yesterday
                        or
                        Older)
                    </h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; font-size: 15px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                    <th style="padding: 10px; text-align: left;">Time Elapsed</th>
                                    <th style="padding: 10px; text-align: left;">Location</th>
                                    <th style="padding: 10px; text-align: left;">Description</th>
                                    <th style="padding: 10px; text-align: left;">Assigned To</th>
                                    <th style="padding: 10px; text-align: left;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($ct = mysqli_fetch_assoc($critical_tickets)):
                                    $hours_elapsed = floor((time() - strtotime($ct['created_at'])) / 3600);
                                    ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 10px; color: #ef4444; font-weight: 600;">
                                            <?php echo $hours_elapsed; ?> hrs
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php echo htmlspecialchars($ct['location_name']); ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php echo htmlspecialchars(substr($ct['issue_description'], 0, 50)) . (strlen($ct['issue_description']) > 50 ? '...' : ''); ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php echo $ct['assigned_to_name'] ? htmlspecialchars($ct['assigned_to_name']) : '<span style="color:#9ca3af;">Unassigned</span>'; ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <a href="?page=tickets&ticket_id=<?php echo $ct['id']; ?>" class="btn btn-sm"
                                                style="background:#3b82f6; color:white; padding:4px 8px; font-size:11px;">View</a>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                endif; ?>
                <!-- Open Ticket Analysis (Turnaround Time) -->
                <div style="margin-top: 30px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #374151;">👥 Total Ticket Analysis
                        (Turnaround
                        Time)</h4>

                    <?php
                    $open_stats = [];
                    // Note: Date filter doesn't apply to OPEN tickets, current status is what matters.
                
                    $open_stats_query = mysqli_query($conn, "
                            SELECT em.employee_name,
                            COUNT(pi.id) as total_assigned,
                            SUM(CASE WHEN pi.status IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as closed_count,
                            SUM(CASE WHEN pi.status NOT IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as open_count,
                            SUM(CASE WHEN pi.status NOT IN ('Resolved', 'Closed') AND DATEDIFF(NOW(), pi.created_at) = 0 THEN 1 ELSE 0 END) as fresh_count,
                            SUM(CASE WHEN pi.status NOT IN ('Resolved', 'Closed') AND DATEDIFF(NOW(), pi.created_at) = 1 THEN 1 ELSE 0 END) as warning_mid_count,
                            SUM(CASE WHEN pi.status NOT IN ('Resolved', 'Closed') AND DATEDIFF(NOW(), pi.created_at) > 1 THEN 1 ELSE 0 END) as warning_high_count
                            
                            FROM employee_master em
                            JOIN patrol_issues pi ON em.id = pi.assigned_to
                            WHERE em.is_active = 1
                            GROUP BY em.id
                            ORDER BY total_assigned DESC, open_count DESC
                            LIMIT 20
                        ");

                    while ($row = mysqli_fetch_assoc($open_stats_query)) {
                        $open_stats[] = $row;
                    }

                    if (empty($open_stats)): ?>
                        <div
                            style="padding: 20px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 8px;">
                            No open
                            tickets found</div>
                        <?php
                    else: ?>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; font-size: 15px; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                        <th style="padding: 10px; text-align: left; width: 25%;">Name</th>
                                        <th style="padding: 10px; text-align: center;">Total Assigned</th>
                                        <th style="padding: 10px; text-align: center;">Total Closed</th>
                                        <th style="padding: 10px; text-align: center;">Open</th>
                                        <th style="padding: 10px; text-align: center;">Fresh (Today)</th>
                                        <th style="padding: 10px; text-align: center;">Warning (24-48 Hrs)</th>
                                        <th style="padding: 10px; text-align: center; color: #b91c1c;">Warning (2+ Days)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($open_stats as $os): ?>
                                        <tr style="border-bottom: 1px solid #f3f4f6;">
                                            <td style="padding: 10px; font-weight: 500; color: #111827;">
                                                <?php echo htmlspecialchars($os['employee_name']); ?>
                                            </td>
                                            <td style="padding: 10px; text-align: center; font-weight: 600; color: #374151;">
                                                <?php echo $os['total_assigned']; ?>
                                            </td>
                                            <td style="padding: 10px; text-align: center; font-weight: 600; color: #374151;">
                                                <?php echo $os['closed_count']; ?>
                                            </td>
                                            <td style="padding: 10px; text-align: center; font-weight: 600; color: #d97706;">
                                                <?php echo $os['open_count']; ?>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $os['fresh_count'] > 0 ? 'background:#ecfdf5; color:#047857; padding:4px 10px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $os['fresh_count']; ?>
                                                </span>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $os['warning_mid_count'] > 0 ? 'background:#fffbeb; color:#b45309; padding:4px 8px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $os['warning_mid_count']; ?>
                                                </span>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $os['warning_high_count'] > 0 ? 'background:#fef2f2; color:#b91c1c; padding:4px 8px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $os['warning_high_count']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    endif; ?>
                </div>
                <!-- Closed Ticket Analysis -->
                <div style="margin-top: 30px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #374151;">✅ Closed Ticket Analysis
                        (Turnaround Time)</h4>

                    <?php
                    $closed_stats = [];
                    if (!isset($ticket_date_filter))
                        $ticket_date_filter = "AND DATE(resolved_at) = CURDATE()";

                    $closed_stats_query = mysqli_query($conn, "
                            SELECT em.employee_name,
                            COUNT(pi.id) as closed_count,
                            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, pi.created_at, COALESCE(pi.resolved_at, pi.updated_at)) < 24 THEN 1 ELSE 0 END) as tat_fresh,
                            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, pi.created_at, COALESCE(pi.resolved_at, pi.updated_at)) BETWEEN 24 AND 48 THEN 1 ELSE 0 END) as tat_warning_mid,
                            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, pi.created_at, COALESCE(pi.resolved_at, pi.updated_at)) > 48 THEN 1 ELSE 0 END) as tat_warning_high
                            
                            FROM employee_master em
                            JOIN patrol_issues pi ON em.id = pi.assigned_to
                            WHERE em.is_active = 1 
                            AND pi.status IN ('Resolved', 'Closed')
                            $ticket_date_filter
                            GROUP BY em.id
                            HAVING closed_count > 0
                            ORDER BY closed_count DESC
                            LIMIT 20
                        ");

                    while ($row = mysqli_fetch_assoc($closed_stats_query)) {
                        $closed_stats[] = $row;
                    }

                    if (empty($closed_stats)): ?>
                        <div
                            style="padding: 20px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 8px;">
                            No
                            tickets closed in this period</div>
                        <?php
                    else: ?>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; font-size: 15px; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                        <th style="padding: 10px; text-align: left; width: 25%;">Name</th>
                                        <th style="padding: 10px; text-align: center;">Closed</th>
                                        <th style="padding: 10px; text-align: center;">Fresh (< 24h)</th>
                                        <th style="padding: 10px; text-align: center;">Warning (24 - 48 Hrs)</th>
                                        <th style="padding: 10px; text-align: center; color: #b91c1c;">Warning (>48 Hrs)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($closed_stats as $cs): ?>
                                        <tr style="border-bottom: 1px solid #f3f4f6;">
                                            <td style="padding: 10px; font-weight: 500; color: #111827;">
                                                <?php echo htmlspecialchars($cs['employee_name']); ?>
                                            </td>
                                            <td style="padding: 10px; text-align: center; font-weight: 600; color: #059669;">
                                                <?php echo $cs['closed_count']; ?>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $cs['tat_fresh'] > 0 ? 'background:#ecfdf5; color:#047857; padding:4px 10px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $cs['tat_fresh']; ?>
                                                </span>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $cs['tat_warning_mid'] > 0 ? 'background:#fffbeb; color:#b45309; padding:4px 8px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $cs['tat_warning_mid']; ?>
                                                </span>
                                            </td>

                                            <td style="padding: 10px; text-align: center;">
                                                <span
                                                    style="<?php echo $cs['tat_warning_high'] > 0 ? 'background:#fef2f2; color:#b91c1c; padding:4px 8px; border-radius:4px; font-weight:600;' : 'color:#d1d5db;'; ?>">
                                                    <?php echo $cs['tat_warning_high']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    endif; ?>
                </div>
            </div>

            <!-- Three Column Layout for Top Performers -->
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 20px;"
                class="responsive-grid-3">
                <!-- Top Transporters -->
                <div class="card">
                    <h3 style="margin-top: 0;">🏆 Top Transporters (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <div
                            style="display: flex; justify-content: space-between; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            <span>Transporter</span>
                            <span>Trips</span>
                        </div>
                        <?php while ($row = mysqli_fetch_assoc($top_transporters)): ?>
                            <div
                                style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <span>
                                    <?php echo htmlspecialchars($row['transporter_name']); ?>
                                </span>
                                <span style="font-weight: 600; white-space: nowrap;">
                                    <?php echo number_format($row['trips']); ?>
                                </span>
                            </div>
                            <?php
                        endwhile; ?>
                    </div>
                </div>

                <!-- Top Vehicles -->
                <div class="card">
                    <h3 style="margin-top: 0;">🚛 Top Vehicles (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <div
                            style="display: flex; justify-content: space-between; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            <span>Vehicle</span>
                            <span>Trips</span>
                        </div>
                        <?php while ($row = mysqli_fetch_assoc($top_vehicles)): ?>
                            <div
                                style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <span>
                                    <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                </span>
                                <span style="font-weight: 600; white-space: nowrap;">
                                    <?php echo number_format($row['trips']); ?>
                                </span>
                            </div>
                            <?php
                        endwhile; ?>
                    </div>
                </div>

                <!-- Top Drivers -->
                <div class="card">
                    <h3 style="margin-top: 0;">👨‍✈️ Top Drivers (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <div
                            style="display: flex; justify-content: space-between; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            <span>Driver</span>
                            <span>Trips</span>
                        </div>
                        <?php while ($row = mysqli_fetch_assoc($top_drivers)): ?>
                            <div
                                style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <div>
                                    <div>
                                        <?php echo htmlspecialchars($row['driver_name']); ?>
                                    </div>
                                    <div style="font-size: 11px; color: #666;">
                                        <?php echo htmlspecialchars($row['driver_mobile']); ?>
                                    </div>
                                </div>
                                <span style="font-weight: 600; white-space: nowrap;">
                                    <?php echo number_format($row['trips']); ?>
                                </span>
                            </div>
                            <?php
                        endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Loading and Unloading Cards -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;"
                class="responsive-grid-2">
                <!-- Recent Loading Checklists -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #ec4899;">📦 Recent Loading Checklists
                            (
                            <?php echo $period_label; ?>)
                        </h3>
                        <a href="?page=reports"
                            style="font-size: 12px; color: #ec4899; text-decoration: none; font-weight: 600;">View
                            All
                            →</a>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
                        <?php if (count($recent_loading_entries) > 0): ?>
                            <?php foreach ($recent_loading_entries as $entry): ?>
                                <div onclick="window.location='?page=loading-details&id=<?php echo intval($entry['id']); ?>'"
                                    style="padding: 12px; margin-bottom: 10px; background: #fef2f2; border-left: 4px solid #ec4899; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='#fce7f3'; this.style.transform='translateX(5px)';"
                                    onmouseout="this.style.background='#fef2f2'; this.style.transform='translateX(0)';">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                                                <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #6b7280;">
                                                <?php echo htmlspecialchars($entry['driver_name'] ?? 'N/A'); ?>
                                            </div>
                                            <?php if (!empty($entry['loading_location'])): ?>
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                                    📍
                                                    <?php echo htmlspecialchars($entry['loading_location']); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                        </div>
                                        <span
                                            class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>"
                                            style="font-size: 10px;">
                                            <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                    <div
                                        style="font-size: 11px; color: #6b7280; display: flex; justify-content: space-between; align-items: center;">
                                        <span>
                                            <?php echo date('d/m/Y H:i', strtotime($entry['reporting_datetime'])); ?>
                                        </span>
                                        <?php if (!empty($entry['transport_company_name'])): ?>
                                            <span style="color: #9ca3af;">🚚
                                                <?php echo htmlspecialchars($entry['transport_company_name']); ?>
                                            </span>
                                            <?php
                                        endif; ?>
                                    </div>
                                </div>
                                <?php
                            endforeach; ?>
                            <?php
                        else: ?>
                            <div style="text-align: center; padding: 30px; color: #9ca3af;">
                                <div style="font-size: 32px; margin-bottom: 10px;">📦</div>
                                <p style="margin: 0; font-size: 13px;">No loading checklists found for
                                    <?php echo $period_label; ?>
                                </p>
                            </div>
                            <?php
                        endif; ?>
                    </div>
                </div>

                <!-- Recent Unloading Checklists -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #14b8a6;">📥 Recent Unloading Checklists
                            (
                            <?php echo $period_label; ?>)
                        </h3>
                        <a href="?page=reports"
                            style="font-size: 12px; color: #14b8a6; text-decoration: none; font-weight: 600;">View
                            All
                            →</a>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
                        <?php if (count($recent_unloading_entries) > 0): ?>
                            <?php foreach ($recent_unloading_entries as $entry): ?>
                                <div onclick="window.location='?page=unloading-details&id=<?php echo intval($entry['id']); ?>'"
                                    style="padding: 12px; margin-bottom: 10px; background: #f0fdfa; border-left: 4px solid #14b8a6; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='#ccfbf1'; this.style.transform='translateX(5px)';"
                                    onmouseout="this.style.background='#f0fdfa'; this.style.transform='translateX(0)';">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                                                <?php echo htmlspecialchars($entry['vehicle_registration_number']); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #6b7280;">
                                                <?php echo htmlspecialchars($entry['driver_name'] ?? 'N/A'); ?>
                                            </div>
                                            <?php if (!empty($entry['vendor_name'])): ?>
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                                    🏢
                                                    <?php echo htmlspecialchars($entry['vendor_name']); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                        </div>
                                        <span
                                            class="badge badge-<?php echo $entry['status'] == 'completed' ? 'success' : ($entry['status'] == 'draft' ? 'warning' : 'secondary'); ?>"
                                            style="font-size: 10px;">
                                            <?php echo strtoupper($entry['status'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                    <div
                                        style="font-size: 11px; color: #6b7280; display: flex; justify-content: space-between; align-items: center;">
                                        <span>
                                            <?php echo date('d/m/Y H:i', strtotime($entry['reporting_datetime'])); ?>
                                        </span>
                                        <?php if (!empty($entry['transport_company_name'])): ?>
                                            <span style="color: #9ca3af;">🚚
                                                <?php echo htmlspecialchars($entry['transport_company_name']); ?>
                                            </span>
                                            <?php
                                        endif; ?>
                                    </div>
                                </div>
                                <?php
                            endforeach; ?>
                            <?php
                        else: ?>
                            <div style="text-align: center; padding: 30px; color: #9ca3af;">
                                <div style="font-size: 32px; margin-bottom: 10px;">📥</div>
                                <p style="margin: 0; font-size: 13px;">No unloading checklists found for
                                    <?php echo $period_label; ?>
                                </p>
                            </div>
                            <?php
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purpose Distribution and Daily Trend -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;"
                class="responsive-grid-2">
                <!-- Purpose Distribution -->
                <div class="card">
                    <h3 style="margin-top: 0;">🎯 Purpose Distribution (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <div
                            style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            <div style="flex: 1;">Purpose</div>
                            <div style="text-align: right; margin-right: 10px; min-width: 60px;">Count</div>
                            <div style="text-align: right; min-width: 100px;">%</div>
                        </div>
                        <?php
                        $total_purpose_count = 0;
                        $purpose_data = [];
                        while ($row = mysqli_fetch_assoc($purpose_dist)) {
                            $purpose_data[] = $row;
                            $total_purpose_count += $row['count'];
                        }
                        foreach ($purpose_data as $row):
                            $percentage = $total_purpose_count > 0 ? round(($row['count'] / $total_purpose_count) * 100, 1) : 0;
                            ?>
                            <div
                                style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <div style="flex: 1;">
                                    <?php echo htmlspecialchars($row['purpose_name']); ?>
                                </div>
                                <div
                                    style="text-align: right; margin-right: 10px; font-weight: 600; min-width: 60px; white-space: nowrap;">
                                    <?php echo number_format($row['count']); ?>
                                </div>
                                <div style="min-width: 100px;">
                                    <div style="background: #e5e7eb; border-radius: 4px; height: 20px; position: relative;">
                                        <div
                                            style="background: #3b82f6; height: 100%; width: <?php echo $percentage; ?>%; border-radius: 4px;">
                                        </div>
                                        <span
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 11px; font-weight: 600;">
                                            <?php echo $percentage; ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach; ?>
                    </div>
                </div>

                <!-- Daily Trend -->
                <div class="card">
                    <h3 style="margin-top: 0;">📈
                        <?php echo ($filter_period == 'year' || $filter_period == 'all' || ($filter_period == 'custom' && $days_back > 90)) ? 'Monthly' : 'Daily'; ?>
                        Trend (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <div
                            style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            <div style="flex: 1;">Date</div>
                            <div style="text-align: right; margin-right: 10px; min-width: 60px;">In</div>
                            <div style="text-align: right; margin-right: 10px; min-width: 60px;">Out</div>
                            <div style="text-align: right; min-width: 70px;">Balance</div>
                        </div>
                        <?php while ($row = mysqli_fetch_assoc($daily_trend)):
                            $balance = $row['inward_count'] - $row['outward_count'];
                            if ($filter_period == 'year' || $filter_period == 'all' || ($filter_period == 'custom' && $days_back > 90)) {
                                $display_date = date('M Y', strtotime($row['date'] . '-01'));
                            } else {
                                $display_date = date('d/m/Y', strtotime($row['date']));
                            }
                            ?>
                            <div style="display: flex; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <div style="flex: 1;">
                                    <?php echo $display_date; ?>
                                </div>
                                <div
                                    style="text-align: right; margin-right: 10px; color: #3b82f6; font-weight: 600; min-width: 60px; white-space: nowrap;">
                                    <?php echo number_format($row['inward_count']); ?>
                                </div>
                                <div
                                    style="text-align: right; margin-right: 10px; color: #10b981; font-weight: 600; min-width: 60px; white-space: nowrap;">
                                    <?php echo number_format($row['outward_count']); ?>
                                </div>
                                <div
                                    style="text-align: right; color: <?php echo $balance > 0 ? '#ef4444' : '#10b981'; ?>; font-weight: 600; min-width: 70px; white-space: nowrap;">
                                    <?php echo $balance > 0 ? '+' . number_format($balance) : number_format($balance); ?>
                                </div>
                            </div>
                            <?php
                        endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Peak Hours and Longest Inside -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;"
                class="responsive-grid-2">
                <!-- Peak Hours -->
                <div class="card">
                    <h3 style="margin-top: 0;">⏰ Peak Hours (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <?php if (mysqli_num_rows($peak_hours) > 0): ?>
                            <div
                                style="display: flex; justify-content: space-between; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                                <span>Hour</span>
                                <span>Entries</span>
                            </div>
                            <?php while ($row = mysqli_fetch_assoc($peak_hours)): ?>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                    <span>
                                        <?php echo str_pad($row['hour'], 2, '0', STR_PAD_LEFT); ?>:00 -
                                        <?php echo str_pad($row['hour'] + 1, 2, '0', STR_PAD_LEFT); ?>:00
                                    </span>
                                    <span style="font-weight: 600; white-space: nowrap;">
                                        <?php echo number_format($row['count']); ?>
                                    </span>
                                </div>
                                <?php
                            endwhile; ?>
                            <?php
                        else: ?>
                            <p style="padding: 20px; text-align: center; color: #666;">No data for
                                <?php echo strtolower($period_label); ?> yet
                            </p>
                            <?php
                        endif; ?>
                    </div>
                </div>

                <!-- Longest Inside Trucks -->
                <div class="card">
                    <h3 style="margin-top: 0;">⚠️ Longest Inside Trucks (
                        <?php echo $period_label; ?>)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                        <?php if (mysqli_num_rows($longest_inside) > 0): ?>
                            <div
                                style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                <div style="min-width: 80px;">Entry #</div>
                                <div style="flex: 1; margin-left: 10px;">Vehicle</div>
                                <div style="text-align: right; min-width: 70px;">Hours</div>
                            </div>
                            <?php while ($row = mysqli_fetch_assoc($longest_inside)): ?>
                                <div
                                    style="display: flex; align-items: center; padding: 6px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px;">
                                    <div style="min-width: 80px;">
                                        <?php echo htmlspecialchars($row['entry_number']); ?>
                                    </div>
                                    <div style="flex: 1; margin-left: 10px;">
                                        <div>
                                            <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                        </div>
                                        <div style="font-size: 10px; color: #666;">
                                            <?php echo date('d/m H:i', strtotime($row['inward_datetime'])); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right; min-width: 70px;">
                                        <span
                                            style="color: <?php echo $row['hours_inside'] > 24 ? '#ef4444' : ($row['hours_inside'] > 12 ? '#f59e0b' : '#10b981'); ?>; font-weight: 600; white-space: nowrap;">
                                            <?php echo number_format($row['hours_inside']); ?>h
                                        </span>
                                    </div>
                                </div>
                                <?php
                            endwhile; ?>
                            <?php
                        else: ?>
                            <p style="padding: 20px; text-align: center; color: #666;">All trucks have exited</p>
                            <?php
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Expiring Documents Section -->
            <div style="margin-top: 30px;">
                <h2 style="margin-bottom: 20px; font-size: 20px; color: #1f2937;">⚠️ Expiring Documents &
                    Certificates
                </h2>

                <!-- Five Column Layout for Expiring Documents -->
                <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 20px; margin-bottom: 20px;"
                    class="responsive-grid-5">
                    <!-- Expiring RC (Registration Certificate) -->
                    <div class="card" style="cursor: pointer; transition: all 0.3s;"
                        onclick="window.location.href='?page=admin&master=vehicles'"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <h3 style="margin-top: 0;">🚗 Expiring RC</h3>
                        <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                            <?php if (mysqli_num_rows($expiring_rc) > 0): ?>
                                <div
                                    style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">Vehicle</div>
                                    <div style="text-align: right; min-width: 70px;">Days</div>
                                </div>
                                <?php while ($row = mysqli_fetch_assoc($expiring_rc)):
                                    $days = $row['rc_days_remaining'] ?? 0;
                                    $is_expired = $days < 0;
                                    $days_display = $is_expired ? abs($days) : $days;
                                    $badge_color = $is_expired ? '#ef4444' : ($days <= 30 ? '#f59e0b' : '#10b981');
                                    $expiry_date = $row['rc_expiry'] ? date('d/m/Y', strtotime($row['rc_expiry'])) : '-';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px; cursor: pointer; transition: background 0.2s;"
                                        onclick="event.stopPropagation(); window.location.href='?page=vehicle-detail&id=<?php echo $row['id']; ?>'"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='transparent';">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                            </div>
                                            <div style="font-size: 10px; color: #666;">
                                                <?php echo $expiry_date; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; min-width: 70px;">
                                            <span
                                                style="color: <?php echo $badge_color; ?>; font-weight: 600; white-space: nowrap;">
                                                <?php echo $is_expired ? '-' : ''; ?>
                                                <?php echo number_format($days_display); ?>d
                                            </span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <p style="padding: 20px; text-align: center; color: #666; font-size: 12px;">No expiring
                                    RC
                                    certificates</p>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <!-- Expiring Fitness Certificates -->
                    <div class="card" style="cursor: pointer; transition: all 0.3s;"
                        onclick="window.location.href='?page=admin&master=vehicles'"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <h3 style="margin-top: 0;">🔧 Expiring Fitness</h3>
                        <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                            <?php if (mysqli_num_rows($expiring_fitness) > 0): ?>
                                <div
                                    style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">Vehicle</div>
                                    <div style="text-align: right; min-width: 70px;">Days</div>
                                </div>
                                <?php while ($row = mysqli_fetch_assoc($expiring_fitness)):
                                    $days = $row['days_remaining'] ?? 0;
                                    $is_expired = $days < 0;
                                    $days_display = $is_expired ? abs($days) : $days;
                                    $badge_color = $is_expired ? '#ef4444' : ($days <= 30 ? '#f59e0b' : '#10b981');
                                    $expiry_date = $row['fitness_validity'] ? date('d/m/Y', strtotime($row['fitness_validity'])) : '-';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px; cursor: pointer; transition: background 0.2s;"
                                        onclick="event.stopPropagation(); window.location.href='?page=vehicle-detail&id=<?php echo $row['id']; ?>'"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='transparent';">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                            </div>
                                            <div style="font-size: 10px; color: #666;">
                                                <?php echo $expiry_date; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; min-width: 70px;">
                                            <span
                                                style="color: <?php echo $badge_color; ?>; font-weight: 600; white-space: nowrap;">
                                                <?php echo $is_expired ? '-' : ''; ?>
                                                <?php echo number_format($days_display); ?>d
                                            </span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <p style="padding: 20px; text-align: center; color: #666; font-size: 12px;">No expiring
                                    fitness
                                    certificates</p>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <!-- Expiring Pollution Certificates -->
                    <div class="card" style="cursor: pointer; transition: all 0.3s;"
                        onclick="window.location.href='?page=admin&master=vehicles'"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <h3 style="margin-top: 0;">🌱 Expiring Pollution</h3>
                        <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                            <?php if (mysqli_num_rows($expiring_pollution) > 0): ?>
                                <div
                                    style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">Vehicle</div>
                                    <div style="text-align: right; min-width: 70px;">Days</div>
                                </div>
                                <?php while ($row = mysqli_fetch_assoc($expiring_pollution)):
                                    $days = $row['days_remaining'] ?? 0;
                                    $is_expired = $days < 0;
                                    $days_display = $is_expired ? abs($days) : $days;
                                    $badge_color = $is_expired ? '#ef4444' : ($days <= 30 ? '#f59e0b' : '#10b981');
                                    $expiry_date = $row['pollution_validity'] ? date('d/m/Y', strtotime($row['pollution_validity'])) : '-';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px; cursor: pointer; transition: background 0.2s;"
                                        onclick="event.stopPropagation(); window.location.href='?page=vehicle-detail&id=<?php echo $row['id']; ?>'"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='transparent';">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                            </div>
                                            <div style="font-size: 10px; color: #666;">
                                                <?php echo $expiry_date; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; min-width: 70px;">
                                            <span
                                                style="color: <?php echo $badge_color; ?>; font-weight: 600; white-space: nowrap;">
                                                <?php echo $is_expired ? '-' : ''; ?>
                                                <?php echo number_format($days_display); ?>d
                                            </span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <p style="padding: 20px; text-align: center; color: #666; font-size: 12px;">No expiring
                                    pollution certificates</p>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <!-- Expiring Insurance Certificates -->
                    <div class="card" style="cursor: pointer; transition: all 0.3s;"
                        onclick="window.location.href='?page=admin&master=vehicles'"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <h3 style="margin-top: 0;">🛡️ Expiring Insurance</h3>
                        <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                            <?php if (mysqli_num_rows($expiring_insurance) > 0): ?>
                                <div
                                    style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">Vehicle</div>
                                    <div style="text-align: right; min-width: 70px;">Days</div>
                                </div>
                                <?php while ($row = mysqli_fetch_assoc($expiring_insurance)):
                                    $days = $row['days_remaining'] ?? 0;
                                    $is_expired = $days < 0;
                                    $days_display = $is_expired ? abs($days) : $days;
                                    $badge_color = $is_expired ? '#ef4444' : ($days <= 30 ? '#f59e0b' : '#10b981');
                                    $expiry_date = $row['insurance_validity'] ? date('d/m/Y', strtotime($row['insurance_validity'])) : '-';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px; cursor: pointer; transition: background 0.2s;"
                                        onclick="event.stopPropagation(); window.location.href='?page=vehicle-detail&id=<?php echo $row['id']; ?>'"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='transparent';">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                            </div>
                                            <div style="font-size: 10px; color: #666;">
                                                <?php echo $expiry_date; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; min-width: 70px;">
                                            <span
                                                style="color: <?php echo $badge_color; ?>; font-weight: 600; white-space: nowrap;">
                                                <?php echo $is_expired ? '-' : ''; ?>
                                                <?php echo number_format($days_display); ?>d
                                            </span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <p style="padding: 20px; text-align: center; color: #666; font-size: 12px;">No expiring
                                    insurance certificates</p>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <!-- Expiring Driver Licenses -->
                    <div class="card" style="cursor: pointer; transition: all 0.3s;"
                        onclick="window.location.href='?page=admin&master=drivers'"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <h3 style="margin-top: 0;">🪪 Expiring Licenses</h3>
                        <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
                            <?php if (mysqli_num_rows($expiring_licenses) > 0): ?>
                                <div
                                    style="display: flex; padding: 8px; background: #f3f4f6; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">Driver</div>
                                    <div style="text-align: right; min-width: 70px;">Days</div>
                                </div>
                                <?php while ($row = mysqli_fetch_assoc($expiring_licenses)):
                                    $days = $row['days_remaining'] ?? 0;
                                    $is_expired = $days < 0;
                                    $days_display = $is_expired ? abs($days) : $days;
                                    $badge_color = $is_expired ? '#ef4444' : ($days <= 30 ? '#f59e0b' : '#10b981');
                                    $expiry_date = $row['license_expiry'] ? date('d/m/Y', strtotime($row['license_expiry'])) : '-';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 12px; cursor: pointer; transition: background 0.2s;"
                                        onclick="event.stopPropagation(); window.location.href='?page=driver-detail&id=<?php echo $row['id']; ?>'"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='transparent';">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($row['driver_name']); ?>
                                            </div>
                                            <div style="font-size: 10px; color: #666;">
                                                <?php echo $expiry_date; ?>
                                            </div>
                                            <?php if ($row['license_number']): ?>
                                                <div style="font-size: 10px; color: #999;">
                                                    <?php echo htmlspecialchars($row['license_number']); ?>
                                                </div>
                                                <?php
                                            endif; ?>
                                        </div>
                                        <div style="text-align: right; min-width: 70px;">
                                            <span
                                                style="color: <?php echo $badge_color; ?>; font-weight: 600; white-space: nowrap;">
                                                <?php echo $is_expired ? '-' : ''; ?>
                                                <?php echo number_format($days_display); ?>d
                                            </span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                                <?php
                            else: ?>
                                <p style="padding: 20px; text-align: center; color: #666; font-size: 12px;">No expiring
                                    driver
                                    licenses</p>
                                <?php
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
endif; ?>