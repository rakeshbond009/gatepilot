<?php
/**
 * VEHICLE LOADING CHECKLIST FORM
 * Based on VCPL/LOG/FR/01 format
 */

// Check if being included in index.php or run standalone
$is_included = (basename($_SERVER['PHP_SELF']) != 'loading_checklist.php');

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
$success = '';
$error = '';

// Handle form submission (do NOT depend on submit button name - Enter key may skip it)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'loading_checklist') {
    // Sanitize inputs
    $reporting_datetime = mysqli_real_escape_string($conn, $_POST['reporting_datetime']);
    $inward_id = !empty($_POST['inward_id']) ? intval($_POST['inward_id']) : 'NULL';
    $vehicle_type_make = mysqli_real_escape_string($conn, $_POST['vehicle_type_make']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $loading_location = mysqli_real_escape_string($conn, $_POST['loading_location']);
    $body_type = mysqli_real_escape_string($conn, $_POST['body_type']);
    $transport_company_id = !empty($_POST['transport_company_id']) ? intval($_POST['transport_company_id']) : 'NULL';
    $transport_company_name = mysqli_real_escape_string($conn, $_POST['transport_company_name']);
    $vehicle_registration_number = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_registration_number']));
    $driver_name = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $license_number = mysqli_real_escape_string($conn, $_POST['license_number']);

    // Customer/Destination/Items details
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : 'NULL';
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);

    // Prepare verified items JSON
    if (!empty($_POST['verified_items_json'])) {
        $verified_items_json = $_POST['verified_items_json'];
    } else {
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
                    'item_code' => $code,
                    'item_name' => $desc,
                    'quantity' => $observed_qty ?: $expected_qty,
                    'unit' => $unit,
                    'item_remarks' => $item_remarks,
                    'is_verified' => $is_verified
                ];
            }
        }
        $verified_items_json = json_encode($verified_items_list);
    }

    // ✅ VALIDATION: Vehicle must have an inward entry with status = inside (do not allow otherwise)
    $inside_inward_id = null;
    if (!empty($vehicle_registration_number)) {
        $inside_q = mysqli_query(
            $conn,
            "SELECT id 
             FROM truck_inward 
             WHERE vehicle_number = '$vehicle_registration_number' AND status = 'inside'
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
             WHERE id = $inward_id_int AND vehicle_number = '$vehicle_registration_number' AND status = 'inside'
             LIMIT 1"
        );
        if (!$verify_q || mysqli_num_rows($verify_q) == 0) {
            // Fall back to latest inside entry if available
            if ($inside_inward_id) {
                $inward_id = $inside_inward_id;
            } else {
                $error = "Cannot save loading entry: Vehicle $vehicle_registration_number does not have an inward entry with status INSIDE. Please do Inward entry first.";
            }
        }
    } else {
        // No inward_id and no inside entry found
        if (!$inside_inward_id) {
            $error = "Cannot save loading entry: Vehicle $vehicle_registration_number does not have an inward entry with status INSIDE. Please do Inward entry first.";
        }
    }

    // Document checks
    $documents = [];
    $doc_types = ['driving_licence', 'rc_book', 'permit', 'insurance', 'puc_certificate'];
    foreach ($doc_types as $doc_type) {
        if (isset($_POST["doc_{$doc_type}_obs"])) {
            $documents[] = [
                'type' => $doc_type,
                'observation' => $_POST["doc_{$doc_type}_obs"],
                'action' => mysqli_real_escape_string($conn, $_POST["doc_{$doc_type}_action"] ?? ''),
                'remarks' => mysqli_real_escape_string($conn, $_POST["doc_{$doc_type}_remarks"] ?? '')
            ];
        }
    }
    $documents_json = json_encode($documents);

    // Platform condition
    $platform_cleanliness_obs = mysqli_real_escape_string($conn, $_POST['platform_cleanliness_obs'] ?? '');
    $platform_cleanliness_action = mysqli_real_escape_string($conn, $_POST['platform_cleanliness_action'] ?? '');
    $platform_cleanliness_remarks = mysqli_real_escape_string($conn, $_POST['platform_cleanliness_remarks'] ?? '');
    $platform_gaps_obs = mysqli_real_escape_string($conn, $_POST['platform_gaps_obs'] ?? '');
    $platform_gaps_action = mysqli_real_escape_string($conn, $_POST['platform_gaps_action'] ?? '');
    $platform_gaps_remarks = mysqli_real_escape_string($conn, $_POST['platform_gaps_remarks'] ?? '');

    // Other checks
    $cross_bars_removed_obs = mysqli_real_escape_string($conn, $_POST['cross_bars_removed_obs'] ?? '');
    $cross_bars_removed_action = mysqli_real_escape_string($conn, $_POST['cross_bars_removed_action'] ?? '');
    $cross_bars_removed_remarks = mysqli_real_escape_string($conn, $_POST['cross_bars_removed_remarks'] ?? '');
    $tarpaulins_available_obs = mysqli_real_escape_string($conn, $_POST['tarpaulins_available_obs'] ?? '');
    $tarpaulins_available_action = mysqli_real_escape_string($conn, $_POST['tarpaulins_available_action'] ?? '');
    $tarpaulins_available_remarks = mysqli_real_escape_string($conn, $_POST['tarpaulins_available_remarks'] ?? '');
    $driver_smartphone_status = mysqli_real_escape_string($conn, $_POST['driver_smartphone_status'] ?? '');
    $driver_smartphone_action = mysqli_real_escape_string($conn, $_POST['driver_smartphone_action'] ?? '');
    $driver_smartphone_remarks = mysqli_real_escape_string($conn, $_POST['driver_smartphone_remarks'] ?? '');

    // Time tracking
    $reporting_time_plant = mysqli_real_escape_string($conn, $_POST['reporting_time_plant'] ?? '');
    $reporting_date_plant = mysqli_real_escape_string($conn, $_POST['reporting_date_plant'] ?? '');
    $gate_entry_time = mysqli_real_escape_string($conn, $_POST['gate_entry_time'] ?? '');
    $gate_entry_date = mysqli_real_escape_string($conn, $_POST['gate_entry_date'] ?? '');

    // Other remarks
    $other_remarks = mysqli_real_escape_string($conn, $_POST['other_remarks'] ?? '');
    $other_remarks_obs = mysqli_real_escape_string($conn, $_POST['other_remarks_obs'] ?? '');
    $other_remarks_action = mysqli_real_escape_string($conn, $_POST['other_remarks_action'] ?? '');

    $checked_by = $_SESSION['user_id'];
    $checked_by_name = $_SESSION['full_name'];

    // Set document_date to current date if not provided
    $document_date = date('Y-m-d');

    // Insert or Update database (only if validation passed)
    if (empty($error)) {
        $edit_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

        // Final permission check for edit
        if ($edit_id > 0 && !hasPermission('actions.edit_record')) {
            $_SESSION['error_msg'] = "🚫 Permission Denied: You do not have permission to edit records.";
            header("Location: ?page=loading-details&id=" . $edit_id);
            exit;
        }

        if ($edit_id > 0) {
            $sql = "UPDATE vehicle_loading_checklist SET 
                reporting_datetime = '$reporting_datetime', vehicle_type_make = '$vehicle_type_make', 
                capacity = '$capacity', loading_location = '$loading_location', body_type = '$body_type', 
                transport_company_id = " . ($transport_company_id != 'NULL' ? $transport_company_id : 'NULL') . ", 
                transport_company_name = '$transport_company_name', vehicle_registration_number = '$vehicle_registration_number',
                driver_name = '$driver_name', license_number = '$license_number',
                customer_id = " . ($customer_id != 'NULL' ? $customer_id : 'NULL') . ", 
                customer_name = '$customer_name', destination = '$destination',
                verified_items_json = '" . mysqli_real_escape_string($conn, $verified_items_json) . "',
                documents_json = '" . mysqli_real_escape_string($conn, $documents_json) . "',
                platform_cleanliness_obs = '$platform_cleanliness_obs', platform_cleanliness_action = '$platform_cleanliness_action', platform_cleanliness_remarks = '$platform_cleanliness_remarks',
                platform_gaps_obs = '$platform_gaps_obs', platform_gaps_action = '$platform_gaps_action', platform_gaps_remarks = '$platform_gaps_remarks',
                cross_bars_removed_obs = '$cross_bars_removed_obs', cross_bars_removed_action = '$cross_bars_removed_action', cross_bars_removed_remarks = '$cross_bars_removed_remarks',
                tarpaulins_available_obs = '$tarpaulins_available_obs', tarpaulins_available_action = '$tarpaulins_available_action', tarpaulins_available_remarks = '$tarpaulins_available_remarks',
                driver_smartphone_status = '$driver_smartphone_status', driver_smartphone_action = '$driver_smartphone_action', driver_smartphone_remarks = '$driver_smartphone_remarks',
                reporting_time_plant = " . ($reporting_time_plant ? "'$reporting_time_plant'" : 'NULL') . ", reporting_date_plant = " . ($reporting_date_plant ? "'$reporting_date_plant'" : 'NULL') . ",
                gate_entry_time = " . ($gate_entry_time ? "'$gate_entry_time'" : 'NULL') . ", gate_entry_date = " . ($gate_entry_date ? "'$gate_entry_date'" : 'NULL') . ",
                other_remarks = '$other_remarks', other_remarks_obs = '$other_remarks_obs', other_remarks_action = '$other_remarks_action',
                inward_id = " . ($inward_id !== 'NULL' ? $inward_id : 'NULL') . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = $edit_id";

            if (mysqli_query($conn, $sql)) {
                $_SESSION['success_msg'] = "✅ Loading checklist updated successfully!";
                logActivity($conn, 'LOADING_UPDATE', 'Checklists', "Updated Loading Checklist ID: $edit_id\n" . auditFromPost($_POST));
                header("Location: ?page=loading-details&id=" . $edit_id);
                exit;
            }
        } else {
            $sql = "INSERT INTO vehicle_loading_checklist (
                document_date, inward_id, reporting_datetime, vehicle_type_make, capacity, loading_location, body_type,
                transport_company_id, transport_company_name, vehicle_registration_number,
                driver_name, license_number,
                customer_id, customer_name, destination, verified_items_json,
                documents_json,
                platform_cleanliness_obs, platform_cleanliness_action, platform_cleanliness_remarks,
                platform_gaps_obs, platform_gaps_action, platform_gaps_remarks,
                cross_bars_removed_obs, cross_bars_removed_action, cross_bars_removed_remarks,
                tarpaulins_available_obs, tarpaulins_available_action, tarpaulins_available_remarks,
                driver_smartphone_status, driver_smartphone_action, driver_smartphone_remarks,
                reporting_time_plant, reporting_date_plant, gate_entry_time, gate_entry_date,
                other_remarks, other_remarks_obs, other_remarks_action,
                checked_by, checked_by_name, status
            ) VALUES (
                '$document_date', " . ($inward_id !== 'NULL' ? $inward_id : 'NULL') . ", '$reporting_datetime', '$vehicle_type_make', '$capacity', '$loading_location', '$body_type',
                " . ($transport_company_id != 'NULL' ? $transport_company_id : 'NULL') . ", '$transport_company_name', '$vehicle_registration_number',
                '$driver_name', '$license_number',
                " . ($customer_id != 'NULL' ? $customer_id : 'NULL') . ", '$customer_name', '$destination', '" . mysqli_real_escape_string($conn, $verified_items_json) . "',
                '$documents_json',
                '$platform_cleanliness_obs', '$platform_cleanliness_action', '$platform_cleanliness_remarks',
                '$platform_gaps_obs', '$platform_gaps_action', '$platform_gaps_remarks',
                '$cross_bars_removed_obs', '$cross_bars_removed_action', '$cross_bars_removed_remarks',
                '$tarpaulins_available_obs', '$tarpaulins_available_action', '$tarpaulins_available_remarks',
                '$driver_smartphone_status', '$driver_smartphone_action', '$driver_smartphone_remarks',
                " . ($reporting_time_plant ? "'$reporting_time_plant'" : 'NULL') . ", " . ($reporting_date_plant ? "'$reporting_date_plant'" : 'NULL') . ",
                " . ($gate_entry_time ? "'$gate_entry_time'" : 'NULL') . ", " . ($gate_entry_date ? "'$gate_entry_date'" : 'NULL') . ",
                '$other_remarks', '$other_remarks_obs', '$other_remarks_action',
                $checked_by, '$checked_by_name', 'completed'
            )";
            if (mysqli_query($conn, $sql)) {
                $last_id = mysqli_insert_id($conn);
                $_SESSION['success_msg'] = "✅ Loading checklist saved successfully!";
                logActivity($conn, 'LOADING_CREATE', 'Checklists', "Created Loading Checklist:\n" . auditFromPost($_POST));
                header("Location: ?page=loading-details&id=" . $last_id);
                exit;
            }
        }

        if (!isset($_SESSION['success_msg'])) {
            $_SESSION['error_msg'] = "❌ Error saving checklist: " . mysqli_error($conn);
            header("Location: ?page=loading" . ($edit_id > 0 ? "&id=$edit_id" : ""));
            exit;
        }
    }
}

// Fetch existing data for Edit Mode
$edit_data = null;
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($edit_id > 0) {
    if (!hasPermission('actions.edit_record')) {
        echo "<div class='container'><div class='alert alert-error'>🚫 Permission Denied: You do not have permission to edit records.</div><a href='?page=loading-details&id=$edit_id' class='btn btn-secondary'>Back to Details</a></div>";
        exit;
    }
    $res = mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist WHERE id = $edit_id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_data = mysqli_fetch_assoc($res);
        // Pre-parse JSON for easier access in form
        if (!empty($edit_data['documents_json'])) {
            $edit_data['docs'] = json_decode($edit_data['documents_json'], true);
        }
    }
}

$verified_items_loaded = [];
if ($edit_data) {
    $verified_items_loaded = !empty($edit_data['verified_items_json']) ? json_decode($edit_data['verified_items_json'], true) : [];
}

// Get transporters for dropdown
$transporters_query = "SELECT id, transporter_name FROM transporter_master WHERE is_active = 1 ORDER BY transporter_name";
$transporters_result = mysqli_query($conn, $transporters_query);

// Get customers for searchable list (with address for autofill)
$customers_query = "SELECT id, customer_name, address, location FROM customer_master WHERE is_active = 1 ORDER BY customer_name";
$customers_result = mysqli_query($conn, $customers_query);

// Get materials for searchable list
$materials_query = "SELECT id, material_code, material_description FROM material_master WHERE is_active = 1 ORDER BY material_description";
$materials_result = mysqli_query($conn, $materials_query);
?>
<?php if (!$is_included): ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vehicle Loading Checklist - VCPL/LOG/FR/01</title>
        <?php
endif; ?>
    <style>
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

            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 3px solid #3b82f6;
            }

            .header h1 {
                color: #1e40af;
                font-size: 28px;
                margin-bottom: 10px;
            }

            .header .doc-info {
                color: #6b7280;
                font-size: 14px;
            }

            .form-section {
                margin-bottom: 30px;
                padding: 20px;
                background: #f9fafb;
                border-radius: 8px;
                border-left: 4px solid #3b82f6;
            }

            .form-section h3 {
                color: #1e40af;
                margin-bottom: 20px;
                font-size: 18px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .form-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-bottom: 15px;
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
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .form-group textarea {
                resize: vertical;
                min-height: 80px;
            }

            .checkbox-group {
                display: flex;
                gap: 15px;
                align-items: center;
                flex-wrap: wrap;
            }

            .checkbox-group label {
                display: flex;
                align-items: center;
                gap: 5px;
                font-weight: normal;
                cursor: pointer;
            }

            .checkbox-group input[type="radio"] {
                width: auto;
            }

            .observation-group {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
                margin-top: 10px;
            }

            .observation-group label {
                font-size: 12px;
                font-weight: normal;
            }

            .observation-group>div {
                display: flex;
                flex-direction: column;
            }

            .observation-group>div label {
                margin-bottom: 5px;
                white-space: normal;
                word-wrap: break-word;
            }

            .observation-group>div input,
            .observation-group>div select {
                width: 100%;
                min-width: 0;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                transition: all 0.3s;
                font-size: 14px;
            }

            .observation-group>div input:focus,
            .observation-group>div select:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* Mobile responsive - stack vertically on small screens */
            @media (max-width: 768px) {
                .observation-group {
                    grid-template-columns: 1fr;
                    gap: 15px;
                }

                .sub-item {
                    margin-left: 0;
                    padding: 12px;
                }

                .sub-item label {
                    font-size: 14px;
                    margin-bottom: 8px;
                    display: block;
                    word-wrap: break-word;
                    white-space: normal;
                }

                .form-row {
                    grid-template-columns: 1fr;
                }

                .container {
                    padding: 15px;
                }
            }

            @media (max-width: 480px) {
                .observation-group {
                    gap: 12px;
                }

                .sub-item {
                    padding: 10px;
                }

                .form-section {
                    padding: 15px;
                }

                .form-section h3 {
                    font-size: 16px;
                }
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
                background: #3b82f6;
                color: white;
            }

            .btn-primary:hover {
                background: #2563eb;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            }

            .btn-secondary {
                background: #6b7280;
                color: white;
            }

            .btn-secondary:hover {
                background: #4b5563;
            }

            .alert {
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 20px;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #10b981;
            }

            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #ef4444;
            }

            .required {
                color: #ef4444;
            }

            .sub-item {
                margin-left: 30px;
                margin-top: 10px;
                padding: 15px;
                background: white;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
            }

            .sub-item label {
                font-size: 13px;
                display: block;
                word-wrap: break-word;
                white-space: normal;
                line-height: 1.4;
            }

            .sub-item>label {
                margin-bottom: 10px;
                font-weight: 600;
                color: #374151;
            }

            /* Out-going check section alignment overrides */
            #outgoingCheckSection .sub-item {
                margin-left: 0;
            }

            #outgoingCheckSection .observation-group {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 12px;
            }

            #outgoingCheckSection .two-col {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                align-items: end;
            }

            @media (max-width: 768px) {
                #outgoingCheckSection .two-col {
                    grid-template-columns: 1fr;
                }
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

            .bottom-nav::-webkit-scrollbar {
                height: 4px;
            }

            .bottom-nav::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
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

            .bottom-nav a:hover {
                background: #f3f4f6;
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

            <?php
        endif; ?>
    </style>
    <?php if (!$is_included): ?>
    </head>

    <body>
        <?php
    endif; ?>
    <div class="loading-form">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php
            endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php
            endif; ?>

            <button type="button" onclick="goBack();" class="btn btn-secondary" style="margin-bottom: 20px;">←
                Back</button>

            <!-- Form Header with Gradient -->
            <div
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📦</div>
                    <div>
                        <h1
                            style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            Vehicle Loading Checklist</h1>
                        <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Document ID:
                            VCPL/LOG/FR/01 | Date: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Auto-fill Message -->
            <div id="vehicle-fetch-message"
                style="display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-left: 4px solid;">
            </div>

            <form method="POST" action="" id="loadingForm">
                <input type="hidden" name="form_type" value="loading_checklist">
                <input type="hidden" name="inward_id" id="inward_id"
                    value="<?php echo htmlspecialchars($edit_data['inward_id'] ?? ''); ?>">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
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
                    <div class="form-group" style="margin-bottom: 25px;">
                        <label
                            style="font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 8px; font-size: 15px;">
                            <span style="font-size: 20px;">🚛</span>
                            <span>Vehicle Registration Number <span class="required">*</span></span>
                        </label>
                        <input type="text" name="vehicle_registration_number" id="vehicle_registration_number"
                            value="<?php echo htmlspecialchars($edit_data['vehicle_registration_number'] ?? ''); ?>"
                            required placeholder="e.g., MH 12 AB 1234"
                            style="text-transform: uppercase; font-size: 18px; font-weight: 700; padding: 15px 18px; border: 2px solid #3b82f6; border-radius: 12px; transition: all 0.3s; width: 100%; letter-spacing: 1px; color: #1e40af;"
                            onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.2)';"
                            onblur="this.style.borderColor='#3b82f6'; this.style.boxShadow='none'; fetchVehicleDetailsForLoading();">
                        <div id="vehicle-inside-message"
                            style="display:none; margin-top: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; font-weight: 600; border-left: 4px solid;">
                        </div>
                        <!-- Inward Materials Summary Display -->
                        <div id="inward-items-summary" 
                             style="display:none; margin-top: 10px; padding: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px;">
                            <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">📦 Items Recorded at Gate (Inward)</div>
                            <div id="inward-items-content" style="font-size: 13px; color: #1e293b; line-height: 1.4;"></div>
                        </div>
                        <small
                            style="color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 8px;">
                            <span>💡</span>
                            <span>Details will auto-fill from Inward master once number is entered</span>
                        </small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📅</span>
                                <span>Date Of Reporting <span class="required">*</span></span>
                            </label>
                            <input type="datetime-local" name="reporting_datetime"
                                value="<?php echo date('Y-m-d\TH:i', $edit_data ? strtotime($edit_data['reporting_datetime']) : time()); ?>"
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
                            <input type="text" name="vehicle_type_make" id="vehicle_type_make"
                                value="<?php echo htmlspecialchars($edit_data['vehicle_type_make'] ?? ''); ?>" required
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>⚖️</span>
                                <span>Capacity</span>
                            </label>
                            <input type="text" name="capacity" id="capacity"
                                value="<?php echo htmlspecialchars($edit_data['capacity'] ?? ''); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📍</span>
                                <span>Location For Loading</span>
                            </label>
                            <input type="text" name="loading_location"
                                value="<?php echo htmlspecialchars($edit_data['loading_location'] ?? ''); ?>"
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
                            <select name="body_type"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select</option>
                                <option value="Half" <?php echo ($edit_data['body_type'] ?? '') == 'Half' ? 'selected' : ''; ?>>Half</option>
                                <option value="Full" <?php echo ($edit_data['body_type'] ?? '') == 'Full' ? 'selected' : ''; ?>>Full</option>
                                <option value="Container" <?php echo ($edit_data['body_type'] ?? '') == 'Container' ? 'selected' : ''; ?>>Container</option>
                                <option value="3/4th" <?php echo ($edit_data['body_type'] ?? '') == '3/4th' ? 'selected' : ''; ?>>3/4th</option>
                                <option value="Other" <?php echo ($edit_data['body_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🏢</span>
                                <span>Transport Company</span>
                            </label>
                            <select name="transport_company_id" id="transport_company_id"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <option value="">Select Transport Company</option>
                                <?php
                                if ($transporters_result):
                                    mysqli_data_seek($transporters_result, 0);
                                    while ($row = mysqli_fetch_assoc($transporters_result)): ?>
                                        <option value="<?php echo $row['id']; ?>" <?php echo ($edit_data['transport_company_id'] ?? '') == $row['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['transporter_name']); ?>
                                        </option>
                                        <?php
                                    endwhile;
                                endif; ?>
                            </select>
                            <input type="hidden" name="transport_company_name" id="transport_company_name">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer & Destination Details -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #8b5cf6; background: linear-gradient(to right, #f5f3ff 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #8b5cf6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);">
                            2</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📍 Customer &
                                Destination</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Select customer and
                                destination town</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🏢</span>
                                <span>Customer Name</span>
                            </label>
                            <div style="position: relative; width: 100%;">
                                <input type="text" name="customer_name" id="customer_name"
                                    value="<?php echo htmlspecialchars($edit_data['customer_name'] ?? ''); ?>"
                                    placeholder="Search or select customer" list="customer_list" autocomplete="off"
                                    style="padding: 12px 40px 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; width: 100%; box-sizing: border-box; background: white;"
                                    onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139, 92, 246, 0.1)';"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 14px;">▼</span>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id"
                                value="<?php echo htmlspecialchars($edit_data['customer_id'] ?? ''); ?>">
                            <datalist id="customer_list">
                                <?php
                                if ($customers_result):
                                    mysqli_data_seek($customers_result, 0);
                                    while ($cust = mysqli_fetch_assoc($customers_result)): ?>
                                        <option value="<?php echo htmlspecialchars($cust['customer_name']); ?>"
                                            data-id="<?php echo $cust['id']; ?>"
                                            data-address="<?php echo htmlspecialchars($cust['address'] ?? $cust['location'] ?? ''); ?>">
                                        <?php endwhile;
                                endif; ?>
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📍</span>
                                <span>Destination</span>
                            </label>
                            <input type="text" name="destination" id="destination"
                                value="<?php echo htmlspecialchars($edit_data['destination'] ?? ''); ?>"
                                placeholder="Enter destination town/city"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; width: 100%;">
                        </div>
                    </div>

                    <!-- Section 2: Material Items Information (Inward Style) -->
                    <div id="manual_items_section"
                        style="margin-top: 25px; padding-top: 20px; border-top: 2px dashed #e5e7eb;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4
                                style="margin: 0; color: #1f2937; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                <span>📦</span> Material Items Information
                            </h4>
                            <span id="items_count_badge"
                                style="background: #8b5cf6; color: white; display: none; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">0
                                Items</span>
                        </div>

                        <div id="items_list_container"
                            style="max-height: 250px; overflow-y: auto; margin-bottom: 15px; display: none;">
                            <table style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
                                <thead>
                                    <tr
                                        style="text-align: left; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                                        <th style="padding: 0 10px; width: 80px;">Code</th>
                                        <th style="padding: 0 10px;">Item Description</th>
                                        <th style="padding: 0 10px; width: 80px;">Qty</th>
                                        <th style="padding: 0 10px; width: 80px;">Unit</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="items_tbody"></tbody>
                            </table>
                        </div>

                        <div
                            style="background: #f8fafc; padding: 18px; border-radius: 12px; border: 2px solid #e2e8f0; display: grid; grid-template-columns: 100px 1fr 80px 80px 40px; gap: 10px; align-items: end;">
                            <div>
                                <label
                                    style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">CODE</label>
                                <input type="text" id="new_item_code" placeholder="Code" list="material_datalist" oninput="handleMaterialAutofill(this)"
                                    style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                            </div>
                            <div>
                                <label
                                    style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">ITEM
                                    NAME</label>
                                <input type="text" id="new_item_name" placeholder="Search item..."
                                    list="material_datalist" oninput="handleMaterialAutofill(this)"
                                    style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                            </div>
                            <div>
                                <label
                                    style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">QTY</label>
                                <input type="number" id="new_item_qty" step="any" placeholder="0"
                                    style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                            </div>
                            <div>
                                <label
                                    style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">UNIT</label>
                                <select id="new_item_unit"
                                    style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: white;">
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
                                style="background: #8b5cf6; color: white; border: none; padding: 0; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; height: 40px; width: 40px;">
                                <span style="font-size: 20px; font-weight: bold;">+</span>
                            </button>
                        </div>
                        <input type="hidden" name="verified_items_json" id="items_hidden_input">
                        <datalist id="material_datalist">
                            <?php
                            if ($materials_result):
                                mysqli_data_seek($materials_result, 0);
                                while ($mat = mysqli_fetch_assoc($materials_result)): ?>
                                    <option
                                        value="<?php echo "[{$mat['material_code']}] " . htmlspecialchars($mat['material_description']); ?>"
                                        data-code="<?php echo htmlspecialchars($mat['material_code']); ?>"
                                        data-name="<?php echo htmlspecialchars($mat['material_description']); ?>">
                                    <?php endwhile;
                            endif; ?>
                        </datalist>
                    </div>
                </div>

                <!-- Section 3: Vehicle & Driver Details -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">
                            3</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚗 Vehicle &
                                Driver Details</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Enter vehicle number -
                                details will auto-fill</p>
                        </div>
                    </div>



                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>👤</span>
                                <span>Driver Name</span>
                            </label>
                            <div id="driver_selection_container">
                                <input type="text" name="driver_name" id="driver_name"
                                    value="<?php echo htmlspecialchars($edit_data['driver_name'] ?? ''); ?>"
                                    style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                    onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                <input type="hidden" name="selected_driver_id" id="selected_driver_id">
                            </div>
                            <div id="driver_dropdown_container" style="display: none; margin-top: 10px;">
                                <label
                                    style="font-weight: 600; color: #374151; margin-bottom: 5px; display: block; font-size: 13px;">Select
                                    Driver (Multiple drivers found):</label>
                                <select name="driver_selection" id="driver_selection"
                                    style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                                    <option value="">-- Select Driver --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🪪</span>
                                <span>Licence No.</span>
                            </label>
                            <input type="text" name="license_number" id="license_number"
                                value="<?php echo htmlspecialchars($edit_data['license_number'] ?? ''); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>


                </div>

                <!-- Section 4: Document Availability & Validity -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                            4</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📄 Availability Of
                                Document & Validity</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Check document status and
                                validity</p>
                        </div>
                    </div>
                    <?php
                    $document_labels = [
                        'driving_licence' => 'A) Driving Licence',
                        'rc_book' => 'B) RC Book',
                        'permit' => 'C) Permit',
                        'insurance' => 'D) Insurance',
                        'puc_certificate' => 'E) PUC Certificate'
                    ];
                    foreach ($document_labels as $key => $label):
                        // Find matching doc in edit_data
                        $doc_val = ['observation' => '', 'action' => '', 'remarks' => ''];
                        if (!empty($edit_data['docs'])) {
                            foreach ($edit_data['docs'] as $d) {
                                if (($d['type'] ?? '') == $key) {
                                    $doc_val = $d;
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="sub-item">
                            <label><strong><?php echo $label; ?></strong></label>
                            <div class="observation-group">
                                <div>
                                    <label>Observation:</label>
                                    <select name="doc_<?php echo $key; ?>_obs">
                                        <option value="">Select</option>
                                        <option value="OK" <?php echo ($doc_val['observation'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                        <option value="NOT OK" <?php echo ($doc_val['observation'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                        <option value="NA" <?php echo ($doc_val['observation'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Action If NOT:</label>
                                    <input type="text" name="doc_<?php echo $key; ?>_action"
                                        value="<?php echo htmlspecialchars($doc_val['action'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label>Remarks:</label>
                                    <input type="text" name="doc_<?php echo $key; ?>_remarks"
                                        value="<?php echo htmlspecialchars($doc_val['remarks'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>
                </div>

                <!-- Section 5: Platform Condition -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #3b82f6; background: linear-gradient(to right, #eff6ff 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #3b82f6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">
                            5</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🏗️ Platform
                                Condition</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Check Fitness For Loading</p>
                        </div>
                    </div>
                    <div class="sub-item">
                        <label><strong>A) Cleanliness</strong></label>
                        <div class="observation-group">
                            <div>
                                <label>Observation:</label>
                                <select name="platform_cleanliness_obs">
                                    <option value="">Select</option>
                                    <option value="OK" <?php echo ($edit_data['platform_cleanliness_obs'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                    <option value="NOT OK" <?php echo ($edit_data['platform_cleanliness_obs'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                    <option value="NA" <?php echo ($edit_data['platform_cleanliness_obs'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                                </select>
                            </div>
                            <div>
                                <label>Action If NOT:</label>
                                <input type="text" name="platform_cleanliness_action"
                                    value="<?php echo htmlspecialchars($edit_data['platform_cleanliness_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label>Remarks:</label>
                                <input type="text" name="platform_cleanliness_remarks"
                                    value="<?php echo htmlspecialchars($edit_data['platform_cleanliness_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="sub-item">
                        <label><strong>B) Gaps/Sharp object inside truck on floor/Side/Front walls</strong></label>
                        <div class="observation-group">
                            <div>
                                <label>Observation:</label>
                                <select name="platform_gaps_obs">
                                    <option value="">Select</option>
                                    <option value="OK" <?php echo ($edit_data['platform_gaps_obs'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                    <option value="NOT OK" <?php echo ($edit_data['platform_gaps_obs'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                    <option value="NA" <?php echo ($edit_data['platform_gaps_obs'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                                </select>
                            </div>
                            <div>
                                <label>Action If NOT:</label>
                                <input type="text" name="platform_gaps_action"
                                    value="<?php echo htmlspecialchars($edit_data['platform_gaps_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label>Remarks:</label>
                                <input type="text" name="platform_gaps_remarks"
                                    value="<?php echo htmlspecialchars($edit_data['platform_gaps_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Other Checks -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #ec4899; background: linear-gradient(to right, #fdf2f8 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #ec4899; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(236, 72, 153, 0.3);">
                            6</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">✅ Other Checks
                            </h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Cross bars, tarpaulins, and
                                smartphone check</p>
                        </div>
                    </div>
                    <div class="sub-item">
                        <label><strong>Removal Of Cross Bars on Top</strong></label>
                        <div class="observation-group">
                            <div>
                                <label>Observation:</label>
                                <select name="cross_bars_removed_obs">
                                    <option value="">Select</option>
                                    <option value="OK" <?php echo ($edit_data['cross_bars_removed_obs'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                    <option value="NOT OK" <?php echo ($edit_data['cross_bars_removed_obs'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                    <option value="NA" <?php echo ($edit_data['cross_bars_removed_obs'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                                </select>
                            </div>
                            <div>
                                <label>Action If NOT:</label>
                                <input type="text" name="cross_bars_removed_action"
                                    value="<?php echo htmlspecialchars($edit_data['cross_bars_removed_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label>Remarks:</label>
                                <input type="text" name="cross_bars_removed_remarks"
                                    value="<?php echo htmlspecialchars($edit_data['cross_bars_removed_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="sub-item">
                        <label><strong>Tarpaulins (Minimum 3 Required)</strong></label>
                        <div class="observation-group">
                            <div>
                                <label>Observation:</label>
                                <select name="tarpaulins_available_obs">
                                    <option value="">Select</option>
                                    <option value="OK" <?php echo ($edit_data['tarpaulins_available_obs'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                    <option value="NOT OK" <?php echo ($edit_data['tarpaulins_available_obs'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                    <option value="NA" <?php echo ($edit_data['tarpaulins_available_obs'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                                </select>
                            </div>
                            <div>
                                <label>Action If NOT:</label>
                                <input type="text" name="tarpaulins_available_action"
                                    value="<?php echo htmlspecialchars($edit_data['tarpaulins_available_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label>Remarks:</label>
                                <input type="text" name="tarpaulins_available_remarks"
                                    value="<?php echo htmlspecialchars($edit_data['tarpaulins_available_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="sub-item">
                        <label><strong>Driver Carrying Smartphone OR Not</strong></label>
                        <div class="observation-group">
                            <div>
                                <label>Observation:</label>
                                <select name="driver_smartphone_status">
                                    <option value="">Select</option>
                                    <option value="Yes" <?php echo ($edit_data['driver_smartphone_status'] ?? '') == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="No" <?php echo ($edit_data['driver_smartphone_status'] ?? '') == 'No' ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div>
                                <label>Action If NOT:</label>
                                <input type="text" name="driver_smartphone_action"
                                    value="<?php echo htmlspecialchars($edit_data['driver_smartphone_action'] ?? ''); ?>">
                            </div>
                            <div>
                                <label>Remarks:</label>
                                <input type="text" name="driver_smartphone_remarks"
                                    value="<?php echo htmlspecialchars($edit_data['driver_smartphone_remarks'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 7: Time Tracking -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #06b6d4; background: linear-gradient(to right, #ecfeff 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #06b6d4; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);">
                            7</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">⏰ Time Tracking
                            </h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Record entry and reporting
                                times</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🕐</span>
                                <span>Reporting Time (Plant)</span>
                            </label>
                            <input type="time" name="reporting_time_plant"
                                value="<?php echo htmlspecialchars($edit_data['reporting_time_plant'] ?? date('H:i')); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                            <small id="reporting_time_plant_12"
                                style="display:block; margin-top:6px; color:#6b7280; font-size:12px;"></small>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📅</span>
                                <span>Reporting Date (Plant)</span>
                            </label>
                            <input type="date" name="reporting_date_plant"
                                value="<?php echo htmlspecialchars($edit_data['reporting_date_plant'] ?? date('Y-m-d')); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>🚪</span>
                                <span>Gate Entry Time</span>
                            </label>
                            <input type="time" name="gate_entry_time"
                                value="<?php echo htmlspecialchars($edit_data['gate_entry_time'] ?? ''); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                            <small id="gate_entry_time_12"
                                style="display:block; margin-top:6px; color:#6b7280; font-size:12px;"></small>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <span>📅</span>
                                <span>Gate Entry Date</span>
                            </label>
                            <input type="date" name="gate_entry_date"
                                value="<?php echo htmlspecialchars($edit_data['gate_entry_date'] ?? ''); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                </div>

                <!-- Section 8: Other Remarks -->
                <div class="card"
                    style="margin-bottom: 20px; border-left: 4px solid #ef4444; background: linear-gradient(to right, #fef2f2 0%, white 10%);">
                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <div
                            style="background: #ef4444; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);">
                            8</div>
                        <div>
                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📝 Other (Any
                                Specific Remarks)</h3>
                            <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Additional observations and
                                notes</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📝</span>
                            <span>Remarks</span>
                        </label>
                        <textarea name="other_remarks"
                            style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; min-height: 100px; resize: vertical; font-family: inherit;"
                            onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 3px rgba(239, 68, 68, 0.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"><?php echo htmlspecialchars($edit_data['other_remarks'] ?? ''); ?></textarea>
                    </div>
                    <div class="observation-group" style="margin-top: 15px;">
                        <div>
                            <label>Observation:</label>
                            <select name="other_remarks_obs"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                                <option value="">Select</option>
                                <option value="OK" <?php echo ($edit_data['other_remarks_obs'] ?? '') == 'OK' ? 'selected' : ''; ?>>OK</option>
                                <option value="NOT OK" <?php echo ($edit_data['other_remarks_obs'] ?? '') == 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                <option value="NA" <?php echo ($edit_data['other_remarks_obs'] ?? '') == 'NA' ? 'selected' : ''; ?>>NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="other_remarks_action"
                                value="<?php echo htmlspecialchars($edit_data['other_remarks_action'] ?? ''); ?>"
                                style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                        </div>
                    </div>
                </div>

                <!-- Submit Button Section -->
                <div
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 25px; margin-top: 30px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
                    <?php
                    $can_submit = true;
                    if ($edit_id > 0 && !hasPermission('actions.edit_record')) {
                        $can_submit = false;
                    }

                    if ($can_submit): ?>
                        <button type="submit" name="submit_loading" id="submitLoadingBtn" class="btn btn-primary btn-full"
                            style="background: white; color: #667eea; padding: 16px 32px; font-size: 18px; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s; border: none; width: 100%;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                            <span
                                id="submitBtnText"><?php echo $edit_id > 0 ? '📦 UPDATE LOADING CHECKLIST' : '📦 SAVE LOADING CHECKLIST'; ?></span>
                            <span id="submitBtnLoader" style="display: none;">
                                <span
                                    style="display: inline-block; width: 20px; height: 20px; border: 3px solid #667eea; border-top: 3px solid transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 10px; vertical-align: middle;"></span>
                                Processing...
                            </span>
                        </button>
                    <?php else: ?>
                        <div
                            style="background: rgba(255,255,255,0.2); color: white; padding: 20px; border-radius: 12px; font-weight: 600; text-align: center; border: 1px dashed rgba(255,255,255,0.5);">
                            ⚠️ View Only Mode: You do not have permission to edit this checklist.
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <script>
            // Utility: Convert 24h to 12h
            function to12Hour(time24) {
                if (!time24 || typeof time24 !== 'string') return '';
                const parts = time24.split(':');
                if (parts.length < 2) return '';
                let h = parseInt(parts[0], 10);
                const m = parts[1];
                if (isNaN(h)) return '';
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                if (h === 0) h = 12;
                const hh = (h < 10 ? '0' : '') + h;
                return `${hh}:${m} ${ampm}`;
            }

            // Global State for Manual Items
            if (typeof window.manualItems === 'undefined') {
                window.manualItems = <?php echo !empty($edit_data['verified_items_json']) ? $edit_data['verified_items_json'] : '[]'; ?>;
            }

            // --- Function Definitions (Global Scope) ---

            window.renderItems = function() {
                const tbody = document.getElementById('items_tbody');
                const container = document.getElementById('items_list_container');
                const badge = document.getElementById('items_count_badge');
                const hiddenInput = document.getElementById('items_hidden_input');

                if (!tbody) return;
                tbody.innerHTML = '';

                if (window.manualItems && window.manualItems.length > 0) {
                    if (container) container.style.display = 'block';
                    if (badge) {
                        badge.style.display = 'inline-block';
                        badge.textContent = window.manualItems.length + ' Items';
                    }

                    window.manualItems.forEach((item, index) => {
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

                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(window.manualItems);
                }
            };

            window.deleteItem = function(index) {
                window.manualItems.splice(index, 1);
                window.renderItems();
            };

            window.addItemManually = function() {
                const code = document.getElementById('new_item_code').value.trim();
                const nameInput = document.getElementById('new_item_name');
                const name = nameInput.value.trim();
                const qty = document.getElementById('new_item_qty').value.trim();
                const unit = document.getElementById('new_item_unit').value;

                if (!name || !qty) {
                    alert('Item Name and Quantity are required!');
                    return;
                }

                const item = {
                    item_code: code || 'N/A',
                    item_name: name.replace(/^\[.*?\]\s*/, ''), // Clean [CODE] prefix
                    quantity: parseFloat(qty),
                    unit: unit,
                    is_verified: 1
                };

                window.manualItems.push(item);
                window.renderItems();

                // Clear inputs
                document.getElementById('new_item_code').value = '';
                nameInput.value = '';
                document.getElementById('new_item_qty').value = '';
                nameInput.focus();
            };

            window.handleMaterialAutofill = function(el) {
                const val = el.value.trim();
                if (!val) return;

                const datalist = document.getElementById('material_datalist');
                if (!datalist) return;

                const options = datalist.getElementsByTagName('option');
                for (let i = 0; i < options.length; i++) {
                    const opt = options[i];
                    const optCode = opt.getAttribute('data-code');
                    const optValue = opt.value; // "[CODE] NAME"

                    // Match either by code or the full datalist value
                    if (optCode === val || optValue === val) {
                        document.getElementById('new_item_code').value = optCode;
                        document.getElementById('new_item_name').value = optValue;
                        // Focus Qty after selection
                        document.getElementById('new_item_qty').focus();
                        break;
                    }
                }
            };

            window.fetchVehicleDetailsForLoading = function() {
                const vehicleNumberInput = document.getElementById('vehicle_registration_number');
                if (!vehicleNumberInput) return;
                
                const vehicleNumber = vehicleNumberInput.value.trim().toUpperCase();
                const messageDiv = document.getElementById('vehicle-fetch-message');
                const inwardIdField = document.getElementById('inward_id');
                const insideMsg = document.getElementById('vehicle-inside-message');
                const submitBtn = document.getElementById('submitLoadingBtn');

                if (!vehicleNumber || vehicleNumber.length < 3) {
                    if (messageDiv) messageDiv.style.display = 'none';
                    if (inwardIdField) inwardIdField.value = '';
                    if (insideMsg) insideMsg.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                // Status Message Update
                if (messageDiv) {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#fef3c7';
                    messageDiv.style.color = '#92400e';
                    messageDiv.style.border = '1px solid #fbbf24';
                    messageDiv.style.borderLeft = '4px solid #f59e0b';
                    messageDiv.textContent = '⏳ Fetching vehicle details and status...';
                }

                // 1. Check if vehicle is INSIDE
                fetch('check_vehicle_inside.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                    .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to check vehicle inside')))
                    .then(data => {
                        if (inwardIdField) {
                            inwardIdField.value = (data && data.isInside && data.entry_id) ? data.entry_id : '';
                        }

                        if (insideMsg) {
                            insideMsg.style.display = 'block';
                            const inwardItemsSummary = document.getElementById('inward-items-summary');
                            const inwardItemsContent = document.getElementById('inward-items-content');

                            if (data && data.isInside) {
                                insideMsg.style.background = '#d1fae5';
                                insideMsg.style.color = '#065f46';
                                insideMsg.style.borderLeft = '4px solid #10b981';
                                insideMsg.textContent = '✅ Vehicle is INSIDE (Entry No: ' + (data.entry_number || data.entry_id) + '). Loading allowed.';
                                if (submitBtn) submitBtn.disabled = false;
                                
                                // Auto-fill Entry Time
                                const gateDate = document.querySelector('input[name="gate_entry_date"]');
                                const gateTime = document.querySelector('input[name="gate_entry_time"]');
                                if (gateDate && data.inward_date) gateDate.value = data.inward_date;
                                if (gateTime && data.inward_time) gateTime.value = data.inward_time;

                                // Show Inward Items Summary
                                if (inwardItemsSummary && inwardItemsContent && data.items_json) {
                                    try {
                                        const items = JSON.parse(data.items_json);
                                        if (items && items.length > 0) {
                                            inwardItemsSummary.style.display = 'block';
                                            inwardItemsContent.innerHTML = items.map(i => 
                                                `• <strong>${i.item_name || 'Item'}</strong>: ${i.quantity || 0} ${i.unit || 'Unit'}`
                                            ).join('<br>');
                                        } else {
                                            inwardItemsSummary.style.display = 'none';
                                        }
                                    } catch (e) {
                                        console.error('JSON Parse error for items:', e);
                                        inwardItemsSummary.style.display = 'none';
                                    }
                                }
                            } else {
                                insideMsg.style.background = '#fee2e2';
                                insideMsg.style.color = '#991b1b';
                                insideMsg.style.borderLeft = '4px solid #ef4444';
                                insideMsg.textContent = '❌ Vehicle is NOT INSIDE. Please perform Inward entry first. Loading blocked.';
                                if (submitBtn) submitBtn.disabled = true;
                                if (inwardItemsSummary) inwardItemsSummary.style.display = 'none';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Inside Check Error:', err);
                        if (submitBtn) submitBtn.disabled = true;
                    });

                // 2. Fetch Vehicle/Driver Master Details
                fetch('fetch_checklist_vehicle_details.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.found) {
                            // Vehicle Make/Model
                            const typeMakeInput = document.getElementById('vehicle_type_make');
                            if (typeMakeInput && data.vehicle) {
                                typeMakeInput.value = (data.vehicle.maker + ' ' + (data.vehicle.model || '')).trim();
                            }
                            
                            // Capacity
                            const capacityInput = document.getElementById('capacity');
                            if (capacityInput && data.vehicle && data.vehicle.capacity) {
                                capacityInput.value = data.vehicle.capacity;
                            }

                            // Driver Name
                            const driverInput = document.getElementById('driver_name');
                            if (driverInput && data.driver && data.driver.driver_name) {
                                driverInput.value = data.driver.driver_name;
                            }
                            
                            // License
                            const licenseInput = document.getElementById('license_number');
                            if (licenseInput && data.driver && data.driver.license_number) {
                                licenseInput.value = data.driver.license_number;
                            }

                            // Transporter
                            const transSelect = document.getElementById('transport_company_id');
                            if (transSelect && data.transporter && data.transporter.transporter_id) {
                                transSelect.value = data.transporter.transporter_id;
                                transSelect.dispatchEvent(new Event('change'));
                            }

                            if (messageDiv) {
                                messageDiv.style.background = '#d1fae5';
                                messageDiv.style.color = '#065f46';
                                messageDiv.style.borderLeft = '4px solid #10b981';
                                messageDiv.textContent = '✅ Vehicle details loaded successfully!';
                            }
                        } else {
                            if (messageDiv) {
                                messageDiv.style.background = '#fef3c7';
                                messageDiv.style.color = '#92400e';
                                messageDiv.style.borderLeft = '4px solid #f59e0b';
                                messageDiv.textContent = 'ℹ️ Vehicle not in master. Please enter details manually.';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Master Fetch Error:', err);
                    });
            };

            // --- Initialization and Event Listeners ---

            document.addEventListener('DOMContentLoaded', function () {
                // 1. Initial manual items render
                window.renderItems();

                // 2. Customer -> Destination Autofill
                const customerInput = document.getElementById('customer_name');
                const list = document.getElementById('customer_list');
                const destField = document.getElementById('destination');
                const customerIdField = document.getElementById('customer_id');

                const handleCustomerSelection = function() {
                    const val = customerInput.value;
                    let found = false;
                    if (list) {
                        for (let i = 0; i < list.options.length; i++) {
                            if (list.options[i].value === val) {
                                if (customerIdField) customerIdField.value = list.options[i].getAttribute('data-id');
                                if (destField) destField.value = list.options[i].getAttribute('data-address');
                                found = true;
                                break;
                            }
                        }
                    }
                    if (!found && customerIdField) customerIdField.value = '';
                };

                if (customerInput) {
                    customerInput.addEventListener('input', handleCustomerSelection);
                    customerInput.addEventListener('change', handleCustomerSelection);
                }

                // 3. Transport Company Hidden Field Sync
                const transportSelect = document.getElementById('transport_company_id');
                const transportNameHidden = document.getElementById('transport_company_name');
                if (transportSelect && transportNameHidden) {
                    transportSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        transportNameHidden.value = (selectedOption && selectedOption.value) ? selectedOption.text.trim() : '';
                    });
                }

                // 4. Form Submission Hardening (Inside Validation)
                const loadingForm = document.getElementById('loadingForm');
                loadingForm?.addEventListener('submit', function (e) {
                    const inwardId = document.getElementById('inward_id').value;
                    const insideMsg = document.getElementById('vehicle-inside-message');
                    const vehicleNum = document.getElementById('vehicle_registration_number').value.trim();

                    if (!inwardId || inwardId === '') {
                        e.preventDefault();
                        if (insideMsg) {
                            insideMsg.style.display = 'block';
                            insideMsg.style.background = '#fee2e2';
                            insideMsg.style.color = '#991b1b';
                            insideMsg.style.borderLeft = '4px solid #ef4444';
                            insideMsg.textContent = '❌ Submission Blocked: Vehicle "' + vehicleNum + '" must be INSIDE. Please do Inward first.';
                        }
                        alert('Error: Vehicle must have an active "INSIDE" status to proceed with loading.');
                        return false;
                    }

                    // Show loader
                    const submitBtn = document.getElementById('submitLoadingBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.7';
                        const btnText = document.getElementById('submitBtnText');
                        const btnLoader = document.getElementById('submitBtnLoader');
                        if (btnText) btnText.style.display = 'none';
                        if (btnLoader) btnLoader.style.display = 'inline-block';
                    }
                });

                // 5. Time display handlers
                const rptTime = document.querySelector('input[name="reporting_time_plant"]');
                const rptTime12 = document.getElementById('reporting_time_plant_12');
                if (rptTime && rptTime12) {
                    const updateRptTime = () => { rptTime12.textContent = rptTime.value ? ('12-hour: ' + to12Hour(rptTime.value)) : ''; };
                    rptTime.addEventListener('input', updateRptTime);
                    updateRptTime();
                }
            });
        </script>

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
                <a href="?page=loading" class="active">
                    <span class="icon">📦</span>
                    Loading
                </a>
                <a href="?page=unloading">
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
    // End of loading_checklist.php
    ?>
