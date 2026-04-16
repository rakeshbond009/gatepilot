<?php
/**
 * VEHICLE UNLOADING CHECKLIST FORM
 * Based on VCPL/STORE/FR/01 format
 */

// Check if being included in index.php or run standalone
$is_included = (basename($_SERVER['PHP_SELF']) != 'unloading_checklist.php');

if (!$is_included) {
    require_once 'config.php';
    session_start();

    // Check if logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit;
    }

    $conn = getDatabaseConnection();
} else {
    // Being included from index.php - variables already set
    if (!isset($conn)) {
        $conn = getDatabaseConnection();
    }
}

// Initialize tables if needed
require_once __DIR__ . '/database/init_loading_unloading_tables.php';
initLoadingUnloadingTables($conn);
$all_uoms = [];
$u_res = mysqli_query($conn, "SELECT uom_code FROM uom_master WHERE is_active=1 ORDER BY uom_code");
if ($u_res) {
    while ($u_row = mysqli_fetch_assoc($u_res)) {
        $all_uoms[] = $u_row['uom_code'];
    }
}
if (empty($all_uoms)) {
    $all_uoms = ['NOS', 'KGS', 'PCS', 'MTR', 'LTR', 'BOX', 'BAG', 'UNIT', 'BUNDLE', 'PKT', 'SET'];
}
$error = '';
$success = '';

$edit_data = null;
$safety_checks_loaded = [];
$verified_items_loaded = [];

if (isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist WHERE id = $edit_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_data = mysqli_fetch_assoc($res);
        // Already loaded above, now decode JSON fields
        $safety_checks_loaded = !empty($edit_data['safety_checks_json']) ? json_decode($edit_data['safety_checks_json'], true) : [];
        $verified_items_loaded = !empty($edit_data['verified_items_json']) ? json_decode($edit_data['verified_items_json'], true) : [];
    }
}

// Handle form submission (do NOT depend on submit button name - Enter key may skip it)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'unloading_checklist') {
    // Sanitize inputs
    $reporting_datetime = mysqli_real_escape_string($conn, $_POST['reporting_datetime']);
    $inward_id = !empty($_POST['inward_id']) ? intval($_POST['inward_id']) : 'NULL';
    $vehicle_type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    $body_type = mysqli_real_escape_string($conn, $_POST['body_type'] ?? '');
    $transport_company_id = !empty($_POST['transport_company_id']) ? intval($_POST['transport_company_id']) : 'NULL';
    $transport_company_name = mysqli_real_escape_string($conn, $_POST['transport_company_name']);
    $vehicle_registration_number = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_registration_number']));

    // ✅ VALIDATION: Vehicle must have an inward entry with status = inside (do not allow otherwise)
    $inside_inward_id = null;
    if (!empty($vehicle_registration_number)) {
        $inside_q = mysqli_query(
            $conn,
            "SELECT id 
             FROM truck_inward 
             WHERE vehicle_number = '$vehicle_registration_number' AND status != 'exited'
             ORDER BY inward_datetime DESC
             LIMIT 1"
        );
        if ($inside_q && mysqli_num_rows($inside_q) > 0) {
            $inside_row = mysqli_fetch_assoc($inside_q);
            $inside_inward_id = intval($inside_row['id']);
        }
    }

    // If UI didn't send inward_id, auto-link to current inside entry.
    if ($inward_id === 'NULL' && $inside_inward_id) {
        $inward_id = $inside_inward_id;
    }

    // If inward_id provided, ensure it matches an 'inside' inward entry for this vehicle.
    if ($inward_id !== 'NULL') {
        $inward_id_int = intval($inward_id);
        $verify_q = mysqli_query(
            $conn,
            "SELECT id 
             FROM truck_inward 
             WHERE id = $inward_id_int AND vehicle_number = '$vehicle_registration_number' AND status != 'exited'
             LIMIT 1"
        );
        if (!$verify_q || mysqli_num_rows($verify_q) == 0) {
            // Fall back to latest inside entry if available
            if ($inside_inward_id) {
                $inward_id = $inside_inward_id;
            } else {
                $error = "Cannot save unloading entry: Vehicle $vehicle_registration_number does not have an active inward record. Please do Inward entry first.";
            }
        }
    } else {
        // No inward_id and no inside entry found
        if (!$inside_inward_id) {
            $error = "Cannot save unloading entry: Vehicle $vehicle_registration_number does not have an active inward record. Please do Inward entry first.";
        }
    }

    // Vehicle specific details
    $rc_book_status = mysqli_real_escape_string($conn, $_POST['rc_book_status'] ?? '');
    $rc_book_details = mysqli_real_escape_string($conn, $_POST['rc_book_details'] ?? '');
    $permit_status = mysqli_real_escape_string($conn, $_POST['permit_status'] ?? '');
    $permit_details = mysqli_real_escape_string($conn, $_POST['permit_details'] ?? '');
    $insurance_status = mysqli_real_escape_string($conn, $_POST['insurance_status'] ?? '');
    $insurance_details = mysqli_real_escape_string($conn, $_POST['insurance_details'] ?? '');
    $puc_certificate_status = mysqli_real_escape_string($conn, $_POST['puc_certificate_status'] ?? '');
    $puc_certificate_details = mysqli_real_escape_string($conn, $_POST['puc_certificate_details'] ?? '');
    $fitness_certificate_status = mysqli_real_escape_string($conn, $_POST['fitness_certificate_status'] ?? '');
    $fitness_certificate_details = mysqli_real_escape_string($conn, $_POST['fitness_certificate_details'] ?? '');

    // Driver specific details
    $driver_name = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $driver_mobile = mysqli_real_escape_string($conn, $_POST['driver_mobile']);
    $driver_alcoholic_influence = mysqli_real_escape_string($conn, $_POST['driver_alcoholic_influence'] ?? '');
    $license_type = mysqli_real_escape_string($conn, $_POST['license_type']);
    $license_valid_till = mysqli_real_escape_string($conn, $_POST['license_valid_till'] ?? '');

    // Vendor/Challan/Invoice details
    $vendor_id = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 'NULL';
    $vendor_name = mysqli_real_escape_string($conn, $_POST['vendor_name']);
    $purchase_order_no = mysqli_real_escape_string($conn, $_POST['purchase_order_no']);
    $challan_no = mysqli_real_escape_string($conn, $_POST['challan_no']);
    $invoice_no = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    $gst_number = mysqli_real_escape_string($conn, $_POST['gst_number']);

    // Safety checks
    $safety_checks = [];
    $safety_items = [
        'driver_cleaner_safety_induction',
        'ppe_provided',
        'wheel_chocks_provided',
        'fire_extinguisher_available',
        'first_aid_box_available',
        'cleaner_available',
        'no_oil_leakage',
        'reverse_horn_available',
        'tyre_condition_good',
        'indicators_horn_lights_working',
        'seat_belt_available',
        'hazard_warning_triangle_available',
        'rear_view_mirrors_good',
        'tailgate_rear_guard_condition'
    ];
    foreach ($safety_items as $item) {
        if (isset($_POST["safety_{$item}"])) {
            $safety_checks[] = [
                'item' => $item,
                'status' => $_POST["safety_{$item}"],
                'remarks' => mysqli_real_escape_string($conn, $_POST["safety_{$item}_remarks"] ?? '')
            ];
        }
    }
    $safety_checks_json = json_encode($safety_checks);
    $checked_by = $_SESSION['user_id'];
    $checked_by_name = $_SESSION['full_name'];

    // Set document_date to current date if not provided
    $document_date = date('Y-m-d');

    // Prepare verified items JSON
    $verified_items_list = [];
    if (isset($_POST['item_descriptions']) && is_array($_POST['item_descriptions'])) {
        foreach ($_POST['item_descriptions'] as $index => $desc) {
            $expected_qty = $_POST['item_quantities'][$index] ?? '';
            $observed_qty = $_POST['item_observed_qtys'][$index] ?? '';
            $item_remarks = $_POST['item_remarks'][$index] ?? '';
            $unit = $_POST['item_units'][$index] ?? '';
            $code = $_POST['item_codes'][$index] ?? '';
            $is_verified = isset($_POST['verified_items'][$index]) ? 1 : 0;

            $verified_items_list[] = [
                'description' => $desc,
                'expected_quantity' => $expected_qty,
                'observed_quantity' => $observed_qty,
                'item_remarks' => $item_remarks,
                'item_code' => $code,
                'is_verified' => $is_verified
            ];
        }
    }
    // Special checks for BO Tankers
    $tanker_sealing_status_obs = mysqli_real_escape_string($conn, $_POST['tanker_sealing_status_obs'] ?? '');
    $tanker_sealing_status_remarks = mysqli_real_escape_string($conn, $_POST['tanker_sealing_status_remarks'] ?? '');
    $tanker_emergency_panel_obs = mysqli_real_escape_string($conn, $_POST['tanker_emergency_panel_obs'] ?? '');
    $tanker_emergency_panel_remarks = mysqli_real_escape_string($conn, $_POST['tanker_emergency_panel_remarks'] ?? '');
    $tanker_fall_protection_obs = mysqli_real_escape_string($conn, $_POST['tanker_fall_protection_obs'] ?? '');
    $tanker_fall_protection_remarks = mysqli_real_escape_string($conn, $_POST['tanker_fall_protection_remarks'] ?? '');

    // Weight information
    $gross_weight_invoice = !empty($_POST['gross_weight_invoice']) ? floatval($_POST['gross_weight_invoice']) : NULL;
    $tare_weight_invoice = !empty($_POST['tare_weight_invoice']) ? floatval($_POST['tare_weight_invoice']) : NULL;
    $net_weight_invoice = !empty($_POST['net_weight_invoice']) ? floatval($_POST['net_weight_invoice']) : NULL;

    // Other remarks
    $other_remarks = mysqli_real_escape_string($conn, $_POST['other_remarks'] ?? '');

    $verified_items_json = json_encode($verified_items_list);

    // Insert or Update database (only if validation passed)
    if (empty($error)) {
        $edit_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

        // Permission check for editing
        if ($edit_id > 0 && !hasPermission('actions.edit_record')) {
            $error = "❌ Permission Denied: You do not have permission to edit existing checklist records.";
        }

        if (empty($error) && $edit_id > 0) {
            $sql = "UPDATE vehicle_unloading_checklist SET 
                reporting_datetime = '$reporting_datetime', vehicle_type = '$vehicle_type', body_type = '$body_type', 
                transport_company_id = " . ($transport_company_id != 'NULL' ? $transport_company_id : 'NULL') . ", 
                transport_company_name = '$transport_company_name', vehicle_registration_number = '$vehicle_registration_number',
                rc_book_status = '$rc_book_status', rc_book_details = '$rc_book_details', permit_status = '$permit_status', permit_details = '$permit_details',
                insurance_status = '$insurance_status', insurance_details = '$insurance_details', PUC_certificate_status = '$puc_certificate_status', PUC_certificate_details = '$puc_certificate_details',
                fitness_certificate_status = '$fitness_certificate_status', fitness_certificate_details = '$fitness_certificate_details',
                driver_name = '$driver_name', driver_mobile = '$driver_mobile', driver_alcoholic_influence = '$driver_alcoholic_influence', license_type = '$license_type', 
                license_valid_till = " . ($license_valid_till ? "'$license_valid_till'" : 'NULL') . ",
                vendor_id = " . ($vendor_id != 'NULL' ? $vendor_id : 'NULL') . ", vendor_name = '$vendor_name', purchase_order_no = '$purchase_order_no', 
                challan_no = '$challan_no', invoice_no = '$invoice_no', gst_number = '$gst_number',
                safety_checks_json = '" . mysqli_real_escape_string($conn, $safety_checks_json) . "', verified_items_json = '" . mysqli_real_escape_string($conn, $verified_items_json) . "',
                tanker_sealing_status_obs = '$tanker_sealing_status_obs', tanker_sealing_status_remarks = '$tanker_sealing_status_remarks',
                tanker_emergency_panel_obs = '$tanker_emergency_panel_obs', tanker_emergency_panel_remarks = '$tanker_emergency_panel_remarks',
                tanker_fall_protection_obs = '$tanker_fall_protection_obs', tanker_fall_protection_remarks = '$tanker_fall_protection_remarks',
                gross_weight_invoice = " . ($gross_weight_invoice !== NULL ? $gross_weight_invoice : 'NULL') . ", 
                tare_weight_invoice = " . ($tare_weight_invoice !== NULL ? $tare_weight_invoice : 'NULL') . ", 
                net_weight_invoice = " . ($net_weight_invoice !== NULL ? $net_weight_invoice : 'NULL') . ",
                other_remarks = '$other_remarks', inward_id = " . ($inward_id !== 'NULL' ? $inward_id : 'NULL') . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = $edit_id";
        } else {
            $sql = "INSERT INTO vehicle_unloading_checklist (
                document_date, inward_id, reporting_datetime, vehicle_type, body_type, transport_company_id, transport_company_name, vehicle_registration_number,
                rc_book_status, rc_book_details, permit_status, permit_details,
                insurance_status, insurance_details, PUC_certificate_status, PUC_certificate_details,
                fitness_certificate_status, fitness_certificate_details,
                driver_name, driver_mobile, driver_alcoholic_influence, license_type, license_valid_till,
                vendor_id, vendor_name, purchase_order_no, challan_no, invoice_no, gst_number,
                safety_checks_json, verified_items_json,
                tanker_sealing_status_obs, tanker_sealing_status_remarks,
                tanker_emergency_panel_obs, tanker_emergency_panel_remarks,
                tanker_fall_protection_obs, tanker_fall_protection_remarks,
                gross_weight_invoice, tare_weight_invoice, net_weight_invoice,
                other_remarks, checked_by, checked_by_name, status
            ) VALUES (
                '$document_date', " . ($inward_id !== 'NULL' ? $inward_id : 'NULL') . ", '$reporting_datetime', '$vehicle_type', '$body_type', " . ($transport_company_id != 'NULL' ? $transport_company_id : 'NULL') . ", '$transport_company_name', '$vehicle_registration_number',
                '$rc_book_status', '$rc_book_details', '$permit_status', '$permit_details',
                '$insurance_status', '$insurance_details', '$puc_certificate_status', '$puc_certificate_details',
                '$fitness_certificate_status', '$fitness_certificate_details',
                '$driver_name', '$driver_mobile', '$driver_alcoholic_influence', '$license_type', " . ($license_valid_till ? "'$license_valid_till'" : 'NULL') . ",
                " . ($vendor_id != 'NULL' ? $vendor_id : 'NULL') . ", '$vendor_name', '$purchase_order_no', '$challan_no', '$invoice_no', '$gst_number',
                '" . mysqli_real_escape_string($conn, $safety_checks_json) . "', '" . mysqli_real_escape_string($conn, $verified_items_json) . "',
                '$tanker_sealing_status_obs', '$tanker_sealing_status_remarks',
                '$tanker_emergency_panel_obs', '$tanker_emergency_panel_remarks',
                '$tanker_fall_protection_obs', '$tanker_fall_protection_remarks',
                " . ($gross_weight_invoice !== NULL ? $gross_weight_invoice : 'NULL') . ", " . ($tare_weight_invoice !== NULL ? $tare_weight_invoice : 'NULL') . ", " . ($net_weight_invoice !== NULL ? $net_weight_invoice : 'NULL') . ",
                '$other_remarks', $checked_by, '$checked_by_name', 'completed'
            )";
        }


        if (mysqli_query($conn, $sql)) {
            $last_id = $edit_id > 0 ? $edit_id : mysqli_insert_id($conn);

            $_SESSION['success_msg'] = "✅ Unloading checklist saved successfully!";
            logActivity($conn, 'UNLOADING_CREATE', 'Checklists', "Created Unloading Checklist:\n" . auditFromPost($_POST));
            addNotification($conn, 'unloading', 'Unloading Started', "Vehicle $vehicle_registration_number unloading checklist completed.", "?page=unloading-details&id=" . $last_id);
            header("Location: ?page=unloading-details&id=" . $last_id);
            exit;
        } else {
            $_SESSION['error_msg'] = "❌ Error saving checklist: " . mysqli_error($conn);
            header("Location: ?page=unloading");
            exit;
        }
    }
}

// Get transporters and vendors for dropdowns
$transporters_query = "SELECT id, transporter_name FROM transporter_master WHERE is_active = 1 ORDER BY transporter_name";
$transporters_result = mysqli_query($conn, $transporters_query);

$vendors_query = "SELECT id, supplier as vendor_name, gst_number FROM supplier_master WHERE is_active = 1 ORDER BY supplier";
// Result will be fetched closer to the select box to ensure connection availability
?>
<?php if (!$is_included): ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vehicle Unloading Checklist - VCPL/STORE/FR/01</title>
        <?php
endif; ?>
    <style>
        .unloading-form .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }

        .unloading-form .form-section h3 {
            color: #059669;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .unloading-form .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .unloading-form .checkbox-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .unloading-form .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
            cursor: pointer;
        }

        .unloading-form .checkbox-group input[type="radio"] {
            width: auto;
        }

        .unloading-form .safety-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .unloading-form .safety-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .unloading-form .safety-item label {
            font-size: 13px;
            font-weight: normal;
            margin-bottom: 8px;
            display: block;
        }

        .unloading-form .safety-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        .unloading-form .sub-item {
            margin-left: 30px;
            margin-top: 10px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .unloading-form .sub-item label {
            font-size: 13px;
        }

        .unloading-form .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .unloading-form .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .unloading-form .required {
            color: #ef4444;
        }

        <?php if (!$is_included): ?>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f5f7fa;
                padding: 20px;
                color: #333;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                padding-bottom: 80px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #374151;
                font-size: 14px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 14px;
                transition: border-color 0.3s;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                outline: none;
                border-color: #10b981;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            }

            .form-group textarea {
                resize: vertical;
                min-height: 80px;
            }

            .btn {
                padding: 12px 30px;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }

            .btn-primary {
                background: #10b981;
                color: white;
            }

            .btn-primary:hover {
                background: #059669;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            }

            .btn-secondary {
                background: #6b7280;
                color: white;
            }

            .btn-secondary:hover {
                background: #4b5563;
            }

            /* Bottom Navigation */
            .bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                display: flex;
                flex-wrap: nowrap;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .bottom-nav a {
                padding: 12px 20px;
                text-align: center;
                text-decoration: none;
                color: #666;
                font-size: 11px;
                transition: all 0.3s;
                white-space: nowrap;
                flex-shrink: 0;
                min-width: fit-content;
            }

            .bottom-nav a.active {
                color: #4F46E5;
                background: #EEF2FF;
            }

            .bottom-nav .icon {
                font-size: 24px;
                display: block;
                margin-bottom: 4px;
            }

            @media print {

                .btn,
                .no-print,
                .bottom-nav {
                    display: none;
                }

                .container {
                    box-shadow: none;
                }
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            <?php
        endif; ?>
    </style>
    <?php if (!$is_included): ?>
    </head>

    <body>
        <?php
    endif; ?>
    <div class="unloading-form">
        <div class="container">

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php
            endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php
            endif; ?>

            <button type="button" onclick="goBack();" class="btn btn-secondary btn-full"
                style="margin-bottom: 20px; text-align: left;">← Back</button>

            <!-- Form Header with Gradient -->
            <div
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📥</div>
                    <div>
                        <h1
                            style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <?php echo $edit_data ? 'Edit Unloading Checklist' : 'Vehicle Unloading Checklist'; ?>
                        </h1>
                        <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Document ID:
                            <?php echo $edit_data ? htmlspecialchars($edit_data['document_id']) : 'VCPL/STORE/FR/01'; ?>
                            | Date:
                            <?php echo $edit_data ? date('d.m.Y', strtotime($edit_data['document_date'])) : date('d.m.Y'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Auto-fill Message -->
            <div id="vehicle-fetch-message"
                style="display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-left: 4px solid;">
            </div>

            <form method="POST" action="" id="unloadingForm">
                <input type="hidden" name="form_type" value="unloading_checklist">
                <input type="hidden" name="inward_id" id="inward_id"
                    value="<?php echo $edit_data ? $edit_data['inward_id'] : ''; ?>">
                <input type="hidden" name="edit_id" value="<?php echo $edit_data ? $edit_data['id'] : ''; ?>">
                <!-- Section 1: General Information -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #3b82f6; background: linear-gradient(to right, #eff6ff 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #3b82f6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            1</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📋 General
                                Information</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Date, time, and vehicle
                                details</p>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🚛</span>
                                <span>Vehicle Registration Number <span class="required">*</span></span>
                            </label>
                            <input type="text" name="vehicle_registration_number" id="vehicle_registration_number"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['vehicle_registration_number']) : ''; ?>"
                                required
                                style="text-transform: uppercase; font-size: 16px; font-weight: 700; padding: 14px 18px; border: 2px solid #3b82f6; border-radius: 12px; transition: all 0.3s; width: 100%; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.05);"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.15)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; fetchVehicleDetailsForUnloading();">
                            <small
                                style="color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 8px;">
                                <span>💡</span>
                                <span>Enter vehicle number and press Tab - details will auto-fill from master</span>
                            </small>
                            <div id="vehicle-inside-message"
                                style="display:none; margin-top: 10px; padding: 12px 15px; border-radius: 10px; font-size: 13px; font-weight: 600; border-left: 4px solid;">
                            </div>
                            <button type="button" id="btn-verify-items" class="btn"
                                style="display: <?php echo ($edit_data && !empty($edit_data['inward_id'])) ? 'block' : 'none'; ?>; margin-top: 10px; width: 100%; background: #4f46e5; color: white; border-radius: 10px; font-weight: 600; padding: 12px; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);">
                                🔍 Verify Inward Items
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📅</span>
                                <span>Date Reporting <span class="required">*</span></span>
                            </label>
                            <input type="datetime-local" name="reporting_datetime"
                                value="<?php echo $edit_data ? date('Y-m-d\TH:i', strtotime($edit_data['reporting_datetime'])) : date('Y-m-d\TH:i'); ?>"
                                required
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🚛</span>
                                <span>Vehicle Make <span class="required">*</span></span>
                            </label>
                            <input type="text" name="vehicle_type" id="vehicle_type"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['vehicle_type']) : ''; ?>"
                                required
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🚚</span>
                                <span>Body Of Vehicle</span>
                            </label>
                            <select name="body_type" id="body_type"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Half">Half</option>
                                <option value="Full">Full</option>
                                <option value="Container">Container</option>
                                <option value="3/4th">3/4th</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🏢</span>
                                <span>Name Of Transport Company</span>
                            </label>
                            <select name="transport_company_id" id="transport_company_id"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select Transport Company</option>
                                <?php
                                if ($transporters_result):
                                    mysqli_data_seek($transporters_result, 0);
                                    while ($row = mysqli_fetch_assoc($transporters_result)):
                                        ?>
                                        <option value="<?php echo $row['id']; ?>" <?php echo ($edit_data && $edit_data['transport_company_id'] == $row['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['transporter_name']); ?></option>
                                        <?php
                                    endwhile;
                                endif; ?>
                            </select>
                            <input type="hidden" name="transport_company_name" id="transport_company_name"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['transport_company_name']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Vehicle Specific Details -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">
                            2</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚗 Vehicle
                                Specific Details</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Document status and validity
                                checks</p>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📋</span>
                                <span>A) RC Book</span>
                            </label>
                            <select name="rc_book_status" id="rc_book_status"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['rc_book_status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['rc_book_status'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="NA" <?php echo ($edit_data && $edit_data['rc_book_status'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                            </select>
                            <input type="text" name="rc_book_details" id="rc_book_details"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['rc_book_details']) : ''; ?>"
                                placeholder="Details"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📄</span>
                                <span>B) Permit</span>
                            </label>
                            <select name="permit_status" id="permit_status"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['permit_status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['permit_status'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="NA" <?php echo ($edit_data && $edit_data['permit_status'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                            </select>
                            <input type="text" name="permit_details" id="permit_details"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['permit_details']) : ''; ?>"
                                placeholder="Details"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🛡️</span>
                                <span>C) Insurance</span>
                            </label>
                            <select name="insurance_status" id="insurance_status"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['insurance_status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['insurance_status'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="NA" <?php echo ($edit_data && $edit_data['insurance_status'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                            </select>
                            <input type="text" name="insurance_details" id="insurance_details"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['insurance_details']) : ''; ?>"
                                placeholder="Details"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🌿</span>
                                <span>D) PUC Certificate</span>
                            </label>
                            <select name="puc_certificate_status" id="puc_certificate_status"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['puc_certificate_status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['puc_certificate_status'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="NA" <?php echo ($edit_data && $edit_data['puc_certificate_status'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                            </select>
                            <input type="text" name="puc_certificate_details" id="puc_certificate_details"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['puc_certificate_details']) : ''; ?>"
                                placeholder="Details"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span>
                                <span>E) Fitness Certificate</span>
                            </label>
                            <select name="fitness_certificate_status" id="fitness_certificate_status"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['fitness_certificate_status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['fitness_certificate_status'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="NA" <?php echo ($edit_data && $edit_data['fitness_certificate_status'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                            </select>
                            <input type="text" name="fitness_certificate_details" id="fitness_certificate_details"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['fitness_certificate_details']) : ''; ?>"
                                placeholder="Details"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Driver Specific Details -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                            3</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">👤 Driver Specific
                                Details</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Driver information and
                                license details</p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Driver Name & Mobile No.</label>
                            <div id="driver_selection_container">
                                <input type="text" name="driver_name" id="driver_name"
                                    value="<?php echo $edit_data ? htmlspecialchars($edit_data['driver_name']) : ''; ?>"
                                    placeholder="Driver Name">
                                <input type="text" name="driver_mobile" id="driver_mobile"
                                    value="<?php echo $edit_data ? htmlspecialchars($edit_data['driver_mobile']) : ''; ?>"
                                    placeholder="Mobile No." style="margin-top: 5px;">
                                <input type="hidden" name="selected_driver_id" id="selected_driver_id"
                                    value="<?php echo $edit_data ? ($edit_data['selected_driver_id'] ?? '') : ''; ?>">
                            </div>
                            <div id="driver_dropdown_container" style="display: none; margin-top: 10px;">
                                <label
                                    style="font-weight: 600; color: #374151; margin-bottom: 5px; display: block;">Select
                                    Driver (Multiple drivers found):</label>
                                <select name="driver_selection" id="driver_selection"
                                    style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px;">
                                    <option value="">-- Select Driver --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Driver/Cleaner Not Under Alcoholic Influence (Hangover)</label>
                            <select name="driver_alcoholic_influence" id="driver_alcoholic_influence">
                                <option value="">Select</option>
                                <option value="Yes" <?php echo ($edit_data && $edit_data['driver_alcoholic_influence'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                <option value="No" <?php echo ($edit_data && $edit_data['driver_alcoholic_influence'] == 'No') ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>License Type (Transport/HGV), Valid Till</label>
                            <input type="text" name="license_type" id="license_type"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['license_type']) : ''; ?>"
                                placeholder="License Type">
                            <input type="date" name="license_valid_till" id="license_valid_till"
                                value="<?php echo $edit_data ? $edit_data['license_valid_till'] : ''; ?>"
                                style="margin-top: 5px;">
                        </div>
                    </div>
                </div>

                <!-- Vendor/Challan/Invoice Details -->
                <div class="form-section">
                    <h3>📄 Vendor/Challan/Invoice Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🏪</span>
                                <span>Vendor Name</span>
                            </label>
                            <div style="position: relative; width: 100%;">
                                <input type="text" name="vendor_name" id="vendor_name"
                                    value="<?php echo $edit_data ? htmlspecialchars($edit_data['vendor_name']) : ''; ?>"
                                    placeholder="Search or select vendor" list="vendor_list"
                                    style="padding: 12px 40px 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; width: 100%; box-sizing: border-box; background: white;"
                                    onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                    autocomplete="off">
                                <span
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 14px;">▼</span>
                            </div>
                            <datalist id="vendor_list">
                                <?php
                                $v_res = mysqli_query($conn, $vendors_query);
                                if ($v_res && mysqli_num_rows($v_res) > 0):
                                    while ($row = mysqli_fetch_assoc($v_res)):
                                        $gst_value = !empty($row['gst_number']) ? htmlspecialchars($row['gst_number']) : '';
                                        ?>
                                        <option data-id="<?php echo $row['id']; ?>" data-gst="<?php echo $gst_value; ?>"
                                            value="<?php echo htmlspecialchars($row['vendor_name']); ?>"></option>
                                        <?php
                                    endwhile;
                                endif; ?>
                            </datalist>
                            <input type="hidden" name="vendor_id" id="vendor_id"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['vendor_id']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📋</span>
                                <span>Purchase Order No.</span>
                            </label>
                            <input type="text" name="purchase_order_no"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['purchase_order_no']) : ''; ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📄</span>
                                <span>Challan No.</span>
                            </label>
                            <input type="text" name="challan_no"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['challan_no']) : ''; ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🧾</span>
                                <span>Invoice No.</span>
                            </label>
                            <input type="text" name="invoice_no"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['invoice_no']) : ''; ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🏷️</span>
                                <span>GST No.</span>
                            </label>
                            <input type="text" name="gst_number" id="gst_number"
                                value="<?php echo $edit_data ? htmlspecialchars($edit_data['gst_number']) : ''; ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                </div>

                <!-- Section 5: Common Safety Checks -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #ec4899; background: linear-gradient(to right, #fdf2f8 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #ec4899; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(236, 72, 153, 0.3);">
                            5</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">✅ Common Point For
                                All Vehicle (Safety & Condition Checks)</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Safety equipment and vehicle
                                condition verification</p>
                        </div>
                    </div>
                    <div class="safety-grid">
                        <?php
                        $safety_items = [
                            'driver_cleaner_safety_induction' => 'Driver & Cleaner Safety Induction Done',
                            'ppe_provided' => 'PPE Provided',
                            'wheel_chocks_provided' => 'Wheel Chocks Provided',
                            'fire_extinguisher_available' => 'Fire Extinguisher Available',
                            'first_aid_box_available' => 'First Aid Box Available',
                            'cleaner_available' => 'Cleaner Available',
                            'no_oil_leakage' => 'No Oil Leakage From Vehicle',
                            'reverse_horn_available' => 'Reverse Horn Available',
                            'tyre_condition_good' => 'Tyre Condition is Good',
                            'indicators_horn_lights_working' => 'Indicators, Horn, Lights in Working Condition',
                            'seat_belt_available' => 'Seat Belt Available',
                            'hazard_warning_triangle_available' => 'Hazard Warning Triangle Available',
                            'rear_view_mirrors_good' => 'Both Rear View Mirrors in Good Condition',
                            'tailgate_rear_guard_condition' => 'Tailgate (Fallca), Rear Guard, Container Door Condition'
                        ];
                        foreach ($safety_items as $key => $label):
                            $status = '';
                            $remarks = '';
                            foreach ($safety_checks_loaded as $chk) {
                                if ($chk['item'] == $key) {
                                    $status = $chk['status'];
                                    $remarks = $chk['remarks'];
                                    break;
                                }
                            }
                            ?>
                            <div class="safety-item">
                                <label><?php echo $label; ?></label>
                                <select name="safety_<?php echo $key; ?>">
                                    <option value="">Select</option>
                                    <option value="Yes" <?php echo $status == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="No" <?php echo $status == 'No' ? 'selected' : ''; ?>>No</option>
                                    <option value="NA" <?php echo $status == 'NA' ? 'selected' : ''; ?>>NA</option>
                                </select>
                                <input type="text" name="safety_<?php echo $key; ?>_remarks"
                                    value="<?php echo htmlspecialchars($remarks); ?>" placeholder="Remarks (if NOT OK)"
                                    style="margin-top: 5px; width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                            </div>
                            <?php
                        endforeach; ?>
                    </div>
                </div>

                <!-- Special Checks For BO Tankers -->
                <div class="form-section">
                    <h3>🚛 Special Checks For BO Tankers</h3>
                    <div class="sub-item">
                        <label><strong>Sealing Status Of Tankers</strong></label>
                        <select name="tanker_sealing_status_obs" style="width: 200px; margin-right: 10px;">
                            <option value="">Select</option>
                            <option value="OK" <?php echo ($edit_data && $edit_data['tanker_sealing_status_obs'] == 'OK') ? 'selected' : ''; ?>>OK</option>
                            <option value="NOT OK" <?php echo ($edit_data && $edit_data['tanker_sealing_status_obs'] == 'NOT OK') ? 'selected' : ''; ?>>NOT OK</option>
                            <option value="NA" <?php echo ($edit_data && $edit_data['tanker_sealing_status_obs'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                        </select>
                        <input type="text" name="tanker_sealing_status_remarks"
                            value="<?php echo $edit_data ? htmlspecialchars($edit_data['tanker_sealing_status_remarks']) : ''; ?>"
                            placeholder="Remarks (if NOT OK)" style="width: calc(100% - 220px);">
                    </div>
                    <div class="sub-item">
                        <label><strong>Emergency Information Panel Marked On The Tanker</strong></label>
                        <select name="tanker_emergency_panel_obs" style="width: 200px; margin-right: 10px;">
                            <option value="">Select</option>
                            <option value="Yes" <?php echo ($edit_data && $edit_data['tanker_emergency_panel_obs'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="No" <?php echo ($edit_data && $edit_data['tanker_emergency_panel_obs'] == 'No') ? 'selected' : ''; ?>>No</option>
                            <option value="NA" <?php echo ($edit_data && $edit_data['tanker_emergency_panel_obs'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                        </select>
                        <input type="text" name="tanker_emergency_panel_remarks"
                            value="<?php echo $edit_data ? htmlspecialchars($edit_data['tanker_emergency_panel_remarks']) : ''; ?>"
                            placeholder="Remarks (if NOT OK)" style="width: calc(100% - 220px);">
                    </div>
                    <div class="sub-item">
                        <label><strong>Fall Protection Railing & Platform Available</strong></label>
                        <select name="tanker_fall_protection_obs" style="width: 200px; margin-right: 10px;">
                            <option value="">Select</option>
                            <option value="Yes" <?php echo ($edit_data && $edit_data['tanker_fall_protection_obs'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="No" <?php echo ($edit_data && $edit_data['tanker_fall_protection_obs'] == 'No') ? 'selected' : ''; ?>>No</option>
                            <option value="NA" <?php echo ($edit_data && $edit_data['tanker_fall_protection_obs'] == 'NA') ? 'selected' : ''; ?>>NA</option>
                        </select>
                        <input type="text" name="tanker_fall_protection_remarks"
                            value="<?php echo $edit_data ? htmlspecialchars($edit_data['tanker_fall_protection_remarks']) : ''; ?>"
                            placeholder="Remarks (if NOT OK)" style="width: calc(100% - 220px);">
                    </div>
                </div>

                <!-- Weight Information -->
                <div class="form-section">
                    <h3>⚖️ Weight Information (As Per Invoice)</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gross Weight At The Time Of Entry</label>
                            <input type="number" name="gross_weight_invoice" id="gross_weight_invoice"
                                value="<?php echo $edit_data ? $edit_data['gross_weight_invoice'] : ''; ?>" step="0.01"
                                placeholder="In kg" oninput="calculateNetWeightAudit()">
                        </div>
                        <div class="form-group">
                            <label>Tare Weight At The Time Of Entry</label>
                            <input type="number" name="tare_weight_invoice" id="tare_weight_invoice"
                                value="<?php echo $edit_data ? $edit_data['tare_weight_invoice'] : ''; ?>" step="0.01"
                                placeholder="In kg" oninput="calculateNetWeightAudit()">
                        </div>
                        <div class="form-group">
                            <label>Net Weight At The Time Of Entry</label>
                            <input type="number" name="net_weight_invoice" id="net_weight_invoice"
                                value="<?php echo $edit_data ? $edit_data['net_weight_invoice'] : ''; ?>" step="0.01"
                                placeholder="In kg">
                        </div>
                    </div>
                </div>

                <!-- Other Remarks -->
                <div class="form-section">
                    <h3>📝 Other (Any Specific Remarks)</h3>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea
                            name="other_remarks"><?php echo $edit_data ? htmlspecialchars($edit_data['other_remarks']) : ''; ?></textarea>
                    </div>

                    <!-- Items Verification Modal -->
                    <div id="itemsModal" class="modal"
                        style="display:none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
                        <div class="modal-content"
                            style="background-color: #fefefe; margin: 10% auto; padding: 0; border: none; width: 90%; max-width: 600px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalSlideIn 0.3s ease-out;">
                            <div
                                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px; color: white; display: flex; justify-content: space-between; align-items: center;">
                                <h2 style="margin: 0; font-size: 20px; font-weight: 700;">
                                    📦 Verify Items <span id="modal-item-count"
                                        style="background: rgba(255,255,255,0.2); font-size: 14px; padding: 2px 10px; border-radius: 20px; margin-left: 10px; border: 1px solid rgba(255,255,255,0.3);">0</span>
                                </h2>
                                <span class="close-modal"
                                    style="color: white; font-size: 28px; font-weight: bold; cursor: pointer; transition: 0.3s;"
                                    onclick="document.getElementById('itemsModal').style.display='none'">&times;</span>
                            </div>
                            <div style="padding: 20px;">
                                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">Please verify and tick
                                    the items recorded during the inward process.</p>

                                <!-- Search & Select All -->
                                <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                                    <div style="position: relative; flex: 1;">
                                        <span
                                            style="position: absolute; left: 12px; top: 10px; color: #94a3b8;">🔍</span>
                                        <input type="text" id="modal-item-search" placeholder="Search items..."
                                            style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                                            onkeyup="filterModalItems()">
                                    </div>
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4b5563; background: #f3f4f6; padding: 8px 12px; border-radius: 10px; cursor: pointer; border: 1px solid #e5e7eb;">
                                        <input type="checkbox" id="modal-select-all" onclick="toggleAllModalItems(this)"
                                            style="width: 18px; height: 18px; accent-color: #4f46e5;">
                                        <span>Verify All</span>
                                    </label>
                                </div>

                                <div id="modal-items-list"
                                    style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                                    <!-- Items will be populated here by JS -->
                                    <div style="text-align: center; padding: 20px; color: #6b7280;">
                                        <i>No items found for this inward entry.</i>
                                    </div>
                                </div>
                                <div style="margin-top: 25px; display: flex; gap: 10px;">
                                    <button type="button" class="btn btn-primary" style="flex: 1;"
                                        onclick="document.getElementById('itemsModal').style.display='none'">Confirm
                                        Verification</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        @keyframes modalSlideIn {
                            from {
                                transform: translateY(-30px);
                                opacity: 0;
                            }

                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }

                        .modal-item-row {
                            display: flex;
                            align-items: center;
                            gap: 15px;
                            padding: 12px;
                            border: 1px solid #f3f4f6;
                            border-radius: 10px;
                            margin-bottom: 8px;
                            transition: all 0.2s;
                        }

                        .modal-item-row:hover {
                            background: #f9fafb;
                            border-color: #4f46e5;
                        }

                        .modal-item-row input[type="checkbox"] {
                            width: 22px;
                            height: 22px;
                            cursor: pointer;
                            accent-color: #4f46e5;
                        }

                        .modal-item-row .item-info {
                            flex: 1;
                        }

                        .modal-item-row .item-name {
                            font-weight: 600;
                            color: #1f2937;
                            display: block;
                        }

                        .modal-item-row .item-qty {
                            font-size: 13px;
                            color: #6b7280;
                            margin-top: 2px;
                            display: block;
                        }
                    </style>

                    <div style="text-align: center; margin-top: 30px;">
                        <?php
                        $show_submit = true;
                        if ($edit_data && !hasPermission('actions.edit_record')) {
                            $show_submit = false;
                        }

                        if ($show_submit): ?>
                            <button type="submit" name="submit_unloading" id="submitUnloadingBtn" class="btn btn-primary"
                                style="padding: 12px 30px; font-weight: 700; border-radius: 12px; border: none; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                                <?php echo $edit_data ? 'Update Checklist' : 'Save Checklist'; ?>
                            </button>
                        <?php else: ?>
                            <div
                                style="background: #fee2e2; color: #ef4444; padding: 12px 20px; border-radius: 10px; font-weight: 600; border: 1px solid #fecaca;">
                                ⚠️ View Only: You don't have permission to edit this record.
                            </div>
                        <?php endif; ?>
                        <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Reset Form</button>
                        <button type="button" onclick="window.history.back();" class="btn btn-secondary"
                            style="margin-left: 10px;">Back / Cancel</button>
                    </div>
            </form>

            <script>
                function calculateNetWeightAudit() {
                    const gross = parseFloat(document.getElementById('gross_weight_invoice').value) || 0;
                    const tare = parseFloat(document.getElementById('tare_weight_invoice').value) || 0;
                    document.getElementById('net_weight_invoice').value = (gross - tare).toFixed(2);
                }

                // Wait for DOM to be ready
                document.addEventListener('DOMContentLoaded', function () {
                    // Block submit if vehicle is not INSIDE (no inward_id)
                    const unloadingForm = document.getElementById('unloadingForm');
                    if (unloadingForm) {
                        unloadingForm.addEventListener('submit', function (e) {
                            const inwardIdField = document.getElementById('inward_id');
                            const insideMsg = document.getElementById('vehicle-inside-message');
                            if (!inwardIdField || !inwardIdField.value) {
                                e.preventDefault();
                                if (insideMsg) {
                                    insideMsg.style.display = 'block';
                                    insideMsg.style.background = '#fee2e2';
                                    insideMsg.style.color = '#991b1b';
                                    insideMsg.style.borderLeftColor = '#ef4444';
                                    insideMsg.textContent = '❌ Cannot save: Vehicle does not have an active inward entry.';
                                }
                                return;
                            }
                        });
                    }

                    // Auto-fill transport company name
                    const transportSelect = document.getElementById('transport_company_id');
                    if (transportSelect) {
                        transportSelect.addEventListener('change', function () {
                            const selectedOption = this.options[this.selectedIndex];
                            const transportNameField = document.getElementById('transport_company_name');
                            if (transportNameField) {
                                transportNameField.value = selectedOption.text;
                            }
                        });
                    }

                    // Auto-fill vendor ID and GST number based on selected datalist item
                    const vendorInput = document.getElementById('vendor_name');
                    if (vendorInput) {
                        vendorInput.addEventListener('input', function () {
                            const val = this.value;
                            const list = document.getElementById('vendor_list');
                            let vendorId = '';
                            let gstNumber = '';

                            if (list) {
                                for (let i = 0; i < list.options.length; i++) {
                                    if (list.options[i].value === val) {
                                        vendorId = list.options[i].getAttribute('data-id');
                                        gstNumber = list.options[i].getAttribute('data-gst');
                                        break;
                                    }
                                }
                            }

                            const vendorIdField = document.getElementById('vendor_id');
                            const gstNumberField = document.getElementById('gst_number');

                            if (vendorIdField) {
                                vendorIdField.value = vendorId;
                            }

                            if (gstNumberField && vendorId !== '') { // only overwrite if valid vendor found
                                gstNumberField.value = gstNumber || '';
                            }
                        });
                    }

                    // If we have pre-loaded items (Edit mode), populate the items modal
                    if (preLoadedItems) {
                        renderInwardItems(preLoadedItems);
                        const btnVerify = document.getElementById('btn-verify-items');
                        if (btnVerify) btnVerify.style.display = 'block';
                    }
                    <?php if ($edit_data && !empty($edit_data['inward_id']) && empty($verified_items_loaded)): ?>
                        // If in edit mode with inward_id but no verifications yet, fetch original items from inward entry
                        fetch('check_vehicle_inside.php?inward_id=<?php echo $edit_data['inward_id']; ?>')
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.items_json) {
                                    try {
                                        const items = JSON.parse(data.items_json);
                                        renderInwardItems(items);
                                        const btnVerify = document.getElementById('btn-verify-items');
                                        if (btnVerify) btnVerify.style.display = 'block';
                                    } catch (e) {
                                        console.error('Error parsing inward items:', e);
                                    }
                                }
                            });
                    <?php endif; ?>

                    // Setup verify button click handler (manually reopen modal)
                    const btnVerify = document.getElementById('btn-verify-items');
                    if (btnVerify) {
                        btnVerify.onclick = function () {
                            const modal = document.getElementById('itemsModal');
                            if (modal) {
                                modal.style.display = 'block';
                                console.log('Opening items modal...');
                            }
                        };
                    }
                });

                // Global variable to store loaded items for edit mode
                let preLoadedItems = <?php echo !empty($verified_items_loaded) ? json_encode($verified_items_loaded) : 'null'; ?>;

                function fetchVehicleDetailsForUnloading() {
                    const vehicleNumber = document.getElementById('vehicle_registration_number').value.trim().toUpperCase();
                    const messageDiv = document.getElementById('vehicle-fetch-message');
                    const inwardIdField = document.getElementById('inward_id');
                    const insideMsg = document.getElementById('vehicle-inside-message');
                    const submitBtn = document.getElementById('submitUnloadingBtn');

                    if (!vehicleNumber || vehicleNumber.length < 3) {
                        if (messageDiv) messageDiv.style.display = 'none';
                        if (inwardIdField) inwardIdField.value = '';
                        if (insideMsg) insideMsg.style.display = 'none';
                        if (submitBtn) submitBtn.disabled = false;
                        return;
                    }

                    // Also link this checklist to the current "inside" gate entry (if any)
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Loading...';
                    }

                    fetch('check_vehicle_inside.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                        .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to check vehicle inside')))
                        .then(data => {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Save Checklist';
                            }
                            if (inwardIdField) {
                                inwardIdField.value = (data && data.isInside && data.entry_id) ? data.entry_id : '';
                            }

                            const btnVerify = document.getElementById('btn-verify-items');
                            if (data && data.isInside && data.entry_id) {
                                // Handle inward items list
                                const modalList = document.getElementById('modal-items-list');
                                if (modalList && data.items_json) {
                                    try {
                                        const items = JSON.parse(data.items_json);
                                        renderInwardItems(items);
                                        if (btnVerify) btnVerify.style.display = 'block';
                                        document.getElementById('itemsModal').style.display = 'block';
                                    } catch (e) {
                                        console.error('Error parsing items_json:', e);
                                        modalList.innerHTML = '<div style="text-align: center; padding: 20px; color: #ef4444;"><i>Error loading items.</i></div>';
                                    }
                                } else {
                                    if (modalList) modalList.innerHTML = '<div style="text-align: center; padding: 20px; color: #6b7280;"><i>No items recorded for this inward.</i></div>';
                                    if (btnVerify) btnVerify.style.display = 'none';
                                }

                                if (insideMsg) {
                                    insideMsg.style.display = 'block';
                                    insideMsg.style.background = '#d1fae5';
                                    insideMsg.style.color = '#065f46';
                                    insideMsg.style.borderLeftColor = '#10b981';
                                    insideMsg.textContent = '✅ Vehicle is ON PREMISES (Inward: ' + (data.entry_number || data.entry_id) + '). Unloading entry allowed.';
                                    if (submitBtn) submitBtn.disabled = false;
                                }
                            } else {
                                if (btnVerify) btnVerify.style.display = 'none';
                                if (insideMsg) {
                                    insideMsg.style.display = 'block';
                                    insideMsg.style.background = '#fee2e2';
                                    insideMsg.style.color = '#991b1b';
                                    insideMsg.style.borderLeftColor = '#ef4444';
                                    insideMsg.textContent = '❌ Vehicle is NOT ON PREMISES. Do inward entry first. Unloading entry blocked.';
                                    if (submitBtn) submitBtn.disabled = true;
                                }
                            }
                        })
                        .catch(() => {
                            if (inwardIdField) inwardIdField.value = '';
                            if (insideMsg) {
                                insideMsg.style.display = 'block';
                                insideMsg.style.background = '#fee2e2';
                                insideMsg.style.color = '#991b1b';
                                insideMsg.style.borderLeftColor = '#ef4444';
                                insideMsg.textContent = '❌ Could not validate inside status. Unloading entry blocked.';
                            }
                            if (submitBtn) submitBtn.disabled = true;
                        });


                    if (messageDiv) {
                        messageDiv.style.display = 'block';
                        messageDiv.style.background = '#fef3c7';
                        messageDiv.style.color = '#92400e';
                        messageDiv.style.borderLeft = '4px solid #fbbf24';
                        messageDiv.textContent = '⏳ Fetching vehicle details...';
                    }

                    fetch('fetch_checklist_vehicle_details.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.found) {
                                // Fill vehicle details
                                if (data.vehicle) {
                                    if (data.vehicle.maker && data.vehicle.model) {
                                        document.getElementById('vehicle_type').value =
                                            (data.vehicle.maker + ' ' + (data.vehicle.model || '')).trim();
                                    }
                                }

                                // Handle multiple drivers - show dropdown if 2+ drivers
                                const driverDropdownContainer = document.getElementById('driver_dropdown_container');
                                const driverSelection = document.getElementById('driver_selection');
                                const driverNameInput = document.getElementById('driver_name');
                                const driverMobileInput = document.getElementById('driver_mobile');
                                const selectedDriverId = document.getElementById('selected_driver_id');

                                if (data.has_multiple_drivers && data.all_drivers && data.all_drivers.length > 1) {
                                    // Show dropdown and populate it
                                    driverDropdownContainer.style.display = 'block';
                                    driverSelection.innerHTML = '<option value="">-- Select Driver --</option>';

                                    data.all_drivers.forEach(function (driver) {
                                        const option = document.createElement('option');
                                        option.value = driver.driver_id;
                                        option.textContent = driver.driver_name + ' (' + driver.driver_mobile + ')' +
                                            (driver.is_primary ? ' - Primary' : '');
                                        if (driver.is_primary) {
                                            option.selected = true;
                                        }
                                        driverSelection.appendChild(option);
                                    });

                                    // Handle driver selection change
                                    driverSelection.addEventListener('change', function () {
                                        const selectedId = this.value;
                                        const selectedDriver = data.all_drivers.find(d => d.driver_id == selectedId);
                                        if (selectedDriver) {
                                            driverNameInput.value = selectedDriver.driver_name;
                                            driverMobileInput.value = selectedDriver.driver_mobile;
                                            selectedDriverId.value = selectedDriver.driver_id;

                                            // Update license info if available
                                            if (selectedDriver.license_number) {
                                                document.getElementById('license_type').value = 'Transport/HGV';
                                            }
                                            if (selectedDriver.license_expiry) {
                                                document.getElementById('license_valid_till').value = selectedDriver.license_expiry;
                                            }
                                        }
                                    });

                                    // Auto-select primary driver on load
                                    const primaryDriver = data.all_drivers.find(d => d.is_primary == 1) || data.all_drivers[0];
                                    if (primaryDriver) {
                                        driverSelection.value = primaryDriver.driver_id;
                                        driverSelection.dispatchEvent(new Event('change'));
                                    }
                                } else {
                                    // Single driver or no drivers - hide dropdown
                                    driverDropdownContainer.style.display = 'none';

                                    // Fill driver details
                                    if (data.driver) {
                                        if (data.driver.driver_name) {
                                            driverNameInput.value = data.driver.driver_name;
                                        }
                                        if (data.driver.driver_mobile) {
                                            driverMobileInput.value = data.driver.driver_mobile;
                                        }
                                        if (data.driver.driver_id) {
                                            selectedDriverId.value = data.driver.driver_id;
                                        }
                                        if (data.driver.license_type) {
                                            document.getElementById('license_type').value = data.driver.license_type;
                                        }
                                        if (data.driver.license_expiry) {
                                            document.getElementById('license_valid_till').value = data.driver.license_expiry;
                                        }
                                    }
                                }

                                // Fill transporter details
                                if (data.transporter) {
                                    const transporterSelect = document.getElementById('transport_company_id');
                                    if (data.transporter.transporter_id) {
                                        transporterSelect.value = data.transporter.transporter_id;
                                    }
                                    document.getElementById('transport_company_name').value = data.transporter.transporter_name || '';
                                    transporterSelect.dispatchEvent(new Event('change'));
                                }

                                // Fill document statuses
                                if (data.documents) {
                                    // RC Book
                                    if (data.documents.rc_book && data.documents.rc_book.status) {
                                        document.getElementById('rc_book_status').value = data.documents.rc_book.status;
                                        if (data.documents.rc_book.details) {
                                            document.getElementById('rc_book_details').value = data.documents.rc_book.details;
                                        }
                                    }

                                    // Permit
                                    if (data.documents.permit && data.documents.permit.status) {
                                        document.getElementById('permit_status').value = data.documents.permit.status;
                                        if (data.documents.permit.details) {
                                            document.getElementById('permit_details').value = data.documents.permit.details;
                                        }
                                    }

                                    // Insurance
                                    if (data.documents.insurance && data.documents.insurance.status) {
                                        document.getElementById('insurance_status').value = data.documents.insurance.status;
                                        if (data.documents.insurance.details) {
                                            document.getElementById('insurance_details').value = data.documents.insurance.details;
                                        }
                                    }

                                    // PUC Certificate
                                    if (data.documents.puc_certificate && data.documents.puc_certificate.status) {
                                        document.getElementById('puc_certificate_status').value = data.documents.puc_certificate.status;
                                        if (data.documents.puc_certificate.details) {
                                            document.getElementById('puc_certificate_details').value = data.documents.puc_certificate.details;
                                        }
                                    }

                                    // Fitness Certificate
                                    if (data.documents.fitness_certificate && data.documents.fitness_certificate.status) {
                                        document.getElementById('fitness_certificate_status').value = data.documents.fitness_certificate.status;
                                        if (data.documents.fitness_certificate.details) {
                                            document.getElementById('fitness_certificate_details').value = data.documents.fitness_certificate.details;
                                        }
                                    }
                                }

                                messageDiv.style.background = '#d1fae5';
                                messageDiv.style.color = '#065f46';
                                messageDiv.style.border = '1px solid #10b981';
                                messageDiv.textContent = '✅ ' + (data.message || 'Vehicle details loaded successfully!');
                            } else {
                                messageDiv.style.background = '#fee2e2';
                                messageDiv.style.color = '#991b1b';
                                messageDiv.style.border = '1px solid #ef4444';
                                messageDiv.textContent = 'ℹ️ ' + (data.message || 'Vehicle not found. Please enter details manually.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            messageDiv.style.background = '#fee2e2';
                            messageDiv.style.color = '#991b1b';
                            messageDiv.style.border = '1px solid #ef4444';
                            messageDiv.textContent = '❌ Error fetching vehicle details. Please try again.';
                        });
                }

                const allowedUoms = <?php echo json_encode($all_uoms); ?>;

                function renderInwardItems(items) {
                    const modalList = document.getElementById('modal-items-list');
                    const btnVerify = document.getElementById('btn-verify-items');
                    const countBadge = document.getElementById('modal-item-count');
                    const selectAllCheck = document.getElementById('modal-select-all');

                    if (!modalList) return;

                    // Update count badge
                    if (countBadge) countBadge.textContent = items ? items.length : 0;

                    // Reset Select All checkbox
                    if (selectAllCheck) selectAllCheck.checked = false;

                    if (items && items.length > 0) {
                        let html = '';
                        items.forEach((item, index) => {
                            const itemName = item.description || item.item_name || item.item_description || 'Unknown Item';
                            const itemCode = item.item_code || item.sku || '';
                            const qty = item.expected_quantity || item.quantity || '0';
                            const observed = item.observed_quantity || qty;
                            const remarks = item.item_remarks || '';
                            const unit = item.unit || 'NOS';
                            const isVerified = (item.is_verified == 1) || (typeof item.is_verified === 'undefined');

                            let unitOptions = '';
                            allowedUoms.forEach(u => {
                                const isSelected = (u === unit) ? 'selected' : '';
                                unitOptions += `<option value="${u}" ${isSelected}>${u}</option>`;
                            });

                            html += `
                        <div class="modal-item-row" style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 16px; transition: all 0.2s; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="padding-top: 2px;">
                                <input type="checkbox" name="verified_items[${index}]" class="item-verify-checkbox" id="item_check_${index}" style="width: 22px; height: 22px; cursor: pointer; accent-color: #6366f1; border-radius: 6px;" ${isVerified ? 'checked' : ''}>
                            </div>
                            <div class="item-info" style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <label for="item_check_${index}" style="display: block; font-weight: 700; color: #1e293b; font-size: 15px; cursor: pointer; line-height: 1.4;">${itemName}</label>
                                    <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-size: 12px; color: #64748b;">
                                        Expected: <strong>${qty} 
                                        <select name="item_units[${index}]" style="display: inline-block; width: auto; padding: 2px 4px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; background: white; margin-left: 4px;">
                                            ${unitOptions}
                                        </select>
                                        </strong>
                                    </span>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 10px; margin-top: 8px;">
                                    <div>
                                        <label style="display: block; font-size: 11px; color: #64748b; margin-bottom: 4px; font-weight: 600;">Observed Qty</label>
                                        <input type="text" name="item_observed_qtys[${index}]" value="${observed}" style="width: 100%; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11px; color: #64748b; margin-bottom: 4px; font-weight: 600;">Remarks / Mismatch (if any)</label>
                                        <input type="text" name="item_remarks[${index}]" value="${remarks}" placeholder="e.g. 2 boxes damaged..." style="width: 100%; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                                    </div>
                                </div>

                                <input type="hidden" name="item_descriptions[${index}]" value="${itemName}">
                                <input type="hidden" name="item_quantities[${index}]" value="${qty}">
                                <input type="hidden" name="item_codes[${index}]" value="${itemCode}">
                            </div>
                        </div>
                    `;
                        });
                        modalList.innerHTML = html;
                        if (btnVerify) btnVerify.style.display = 'block';
                    } else {
                        modalList.innerHTML = '<div style="text-align: center; padding: 20px; color: #6b7280;"><i>No items recorded.</i></div>';
                        if (btnVerify) btnVerify.style.display = 'none';
                    }
                }

                // Filter items in the verification modal
                function filterModalItems() {
                    const query = document.getElementById('modal-item-search').value.toLowerCase();
                    const rows = document.querySelectorAll('.modal-item-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const isMatch = text.includes(query);
                        row.style.display = isMatch ? 'flex' : 'none';
                    });
                }

                // Toggle all checkboxes in the modal
                function toggleAllModalItems(source) {
                    const checkboxes = document.querySelectorAll('.item-verify-checkbox');
                    checkboxes.forEach(checkbox => {
                        const row = checkbox.closest('.modal-item-row');
                        // Only toggle visible items if filtered, or all if not filtered
                        if (row && row.style.display !== 'none') {
                            checkbox.checked = source.checked;
                        }
                    });
                    console.log('Toggle all: ' + source.checked + ' applied to visible items.');
                }
            </script>

        </div><!-- end container -->
    </div><!-- end unloading-form -->

    <?php if (!$is_included): ?>
        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="?page=dashboard">
                <span class="icon">🏠</span>
                Home
            </a>
            <a href="?page=inward">
                <span class="icon">➕</span>
                Inward
            </a>
            <a href="?page=inside">
                <span class="icon">🚛</span>
                Inside
            </a>
            <a href="?page=reports">
                <span class="icon">📊</span>
                Reports
            </a>
            <a href="?page=loading">
                <span class="icon">📦</span>
                Loading
            </a>
            <a href="?page=unloading" class="active">
                <span class="icon">📥</span>
                Unloading
            </a>
            <?php if (hasPermission('pages.management')): ?>
                <a href="?page=management">
                    <span class="icon">📈</span>
                    Management
                </a>
            <?php endif; ?>
            <?php if (hasPermission('pages.masters')): ?>
                <a href="?page=admin">
                    <span class="icon">⚙️</span>
                    Masters
                </a>
            <?php endif; ?>
            <a href="?page=logout">
                <span class="icon">🚪</span>
                Logout
            </a>
        </div>
    </body>

    </html>
    <?php
    endif;
    // End of unloading_checklist.php
