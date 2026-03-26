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
}
else {
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
            }
            else {
                $error = "Cannot save loading entry: Vehicle $vehicle_registration_number does not have an inward entry with status INSIDE. Please do Inward entry first.";
            }
        }
    }
    else {
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

    // Insert into database (only if validation passed)
    if (empty($error)) {
        $sql = "INSERT INTO vehicle_loading_checklist (
            document_date, inward_id, reporting_datetime, vehicle_type_make, capacity, loading_location, body_type,
            transport_company_id, transport_company_name, vehicle_registration_number,
            driver_name, license_number,
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
            $success = "Loading checklist saved successfully!";
            // Reset form or redirect
            $_POST = array();
        }
        else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Get transporters for dropdown
$transporters_query = "SELECT id, transporter_name FROM transporter_master WHERE is_active = 1 ORDER BY transporter_name";
$transporters_result = mysqli_query($conn, $transporters_query);
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .observation-group > div {
            display: flex;
            flex-direction: column;
        }
        .observation-group > div label {
            margin-bottom: 5px;
            white-space: normal;
            word-wrap: break-word;
        }
        .observation-group > div input,
        .observation-group > div select {
            width: 100%;
            min-width: 0;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }
        .observation-group > div input:focus,
        .observation-group > div select:focus {
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
        .sub-item > label {
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
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
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
            .btn, .no-print, .bottom-nav {
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

        <a href="<?php echo $is_included ? '?page=dashboard' : 'index.php?page=dashboard'; ?>" class="btn btn-secondary btn-full" style="margin-bottom: 15px; display: block; position: relative; z-index: 10;">
            ← Back
        </a>
        
        <!-- Form Header with Gradient -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📦</div>
                <div>
                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Vehicle Loading Checklist</h1>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Document ID: VCPL/LOG/FR/01 | Date: <?php echo date('d.m.Y'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Auto-fill Message -->
        <div id="vehicle-fetch-message" style="display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-left: 4px solid;"></div>

        <form method="POST" action="" id="loadingForm">
            <input type="hidden" name="form_type" value="loading_checklist">
            <input type="hidden" name="inward_id" id="inward_id" value="">
            <!-- Section 1: General Information -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #3b82f6; background: linear-gradient(to right, #eff6ff 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #3b82f6; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);">1</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📋 General Information</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Date, time, and vehicle details</p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📅</span>
                            <span>Date Of Reporting <span class="required">*</span></span>
                        </label>
                        <input type="datetime-local" name="reporting_datetime" value="<?php echo date('Y-m-d\TH:i'); ?>" required style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🚛</span>
                            <span>Vehicle Make <span class="required">*</span></span>
                        </label>
                        <input type="text" name="vehicle_type_make" id="vehicle_type_make" required style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>⚖️</span>
                            <span>Capacity</span>
                        </label>
                        <input type="text" name="capacity" id="capacity" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📍</span>
                            <span>Location For Loading</span>
                        </label>
                        <input type="text" name="loading_location" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🚚</span>
                            <span>Body Of Vehicle</span>
                        </label>
                        <select name="body_type" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                            <option value="">Select</option>
                            <option value="Half">Half</option>
                            <option value="Full">Full</option>
                            <option value="Container">Container</option>
                            <option value="3/4th">3/4th</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🏢</span>
                            <span>Transport Company</span>
                        </label>
                        <select name="transport_company_id" id="transport_company_id" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                            <option value="">Select Transport Company</option>
                            <?php
mysqli_data_seek($transporters_result, 0);
while ($row = mysqli_fetch_assoc($transporters_result)): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['transporter_name']); ?></option>
                            <?php
endwhile; ?>
                        </select>
                        <input type="hidden" name="transport_company_name" id="transport_company_name">
                    </div>
                </div>
            </div>

            <!-- Section 2: Vehicle & Driver Details -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #10b981; background: linear-gradient(to right, #f0fdf4 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">2</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">🚗 Vehicle & Driver Details</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Enter vehicle number - details will auto-fill</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>🚛</span>
                        <span>Vehicle Registration Number <span class="required">*</span></span>
                    </label>
                    <input type="text" name="vehicle_registration_number" id="vehicle_registration_number" required style="text-transform: uppercase; font-size: 16px; font-weight: 500; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onblur="fetchVehicleDetailsForLoading()" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; fetchVehicleDetailsForLoading();">
                    <small style="color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 6px;">
                        <span>💡</span>
                        <span>Enter vehicle number and press Tab - details will auto-fill from master</span>
                    </small>
                    <div id="vehicle-inside-message" style="display:none; margin-top: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; font-weight: 600; border-left: 4px solid;"></div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>👤</span>
                            <span>Driver Name</span>
                        </label>
                        <div id="driver_selection_container">
                            <input type="text" name="driver_name" id="driver_name" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                            <input type="hidden" name="selected_driver_id" id="selected_driver_id">
                        </div>
                        <div id="driver_dropdown_container" style="display: none; margin-top: 10px;">
                            <label style="font-weight: 600; color: #374151; margin-bottom: 5px; display: block; font-size: 13px;">Select Driver (Multiple drivers found):</label>
                            <select name="driver_selection" id="driver_selection" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                                <option value="">-- Select Driver --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🪪</span>
                            <span>Licence No.</span>
                        </label>
                        <input type="text" name="license_number" id="license_number" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>
            </div>

            <!-- Section 3: Document Availability & Validity -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #f59e0b; background: linear-gradient(to right, #fffbeb 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">3</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📄 Availability Of Document & Validity</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Check document status and validity</p>
                    </div>
                </div>
                <?php
$documents = [
    'driving_licence' => 'A) Driving Licence',
    'rc_book' => 'B) RC Book',
    'permit' => 'C) Permit',
    'insurance' => 'D) Insurance',
    'puc_certificate' => 'E) PUC Certificate'
];
foreach ($documents as $key => $label):
?>
                <div class="sub-item">
                    <label><strong><?php echo $label; ?></strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="doc_<?php echo $key; ?>_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="doc_<?php echo $key; ?>_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="doc_<?php echo $key; ?>_remarks">
                        </div>
                    </div>
                </div>
                <?php
endforeach; ?>
            </div>

            <!-- Platform Condition -->
            <div class="form-section">
                <h3>🏗️ Platform Condition (Check Fitness For Loading)</h3>
                <div class="sub-item">
                    <label><strong>A) Cleanliness</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="platform_cleanliness_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="platform_cleanliness_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="platform_cleanliness_remarks">
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
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="platform_gaps_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="platform_gaps_remarks">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Other Checks -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #ec4899; background: linear-gradient(to right, #fdf2f8 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #ec4899; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(236, 72, 153, 0.3);">5</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">✅ Other Checks</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Cross bars, tarpaulins, and smartphone check</p>
                    </div>
                </div>
                <div class="sub-item">
                    <label><strong>Removal Of Cross Bars on Top</strong></label>
                    <div class="observation-group">
                        <div>
                            <label>Observation:</label>
                            <select name="cross_bars_removed_obs">
                                <option value="">Select</option>
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="cross_bars_removed_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="cross_bars_removed_remarks">
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
                                <option value="OK">OK</option>
                                <option value="NOT OK">NOT OK</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="tarpaulins_available_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="tarpaulins_available_remarks">
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
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div>
                            <label>Action If NOT:</label>
                            <input type="text" name="driver_smartphone_action">
                        </div>
                        <div>
                            <label>Remarks:</label>
                            <input type="text" name="driver_smartphone_remarks">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 6: Time Tracking -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #06b6d4; background: linear-gradient(to right, #ecfeff 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #06b6d4; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);">6</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">⏰ Time Tracking</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Record entry and reporting times</p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🕐</span>
                            <span>Reporting Time (Plant)</span>
                        </label>
                        <input type="time" name="reporting_time_plant" value="<?php echo date('H:i'); ?>" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <small id="reporting_time_plant_12" style="display:block; margin-top:6px; color:#6b7280; font-size:12px;"></small>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📅</span>
                            <span>Reporting Date (Plant)</span>
                        </label>
                        <input type="date" name="reporting_date_plant" value="<?php echo date('Y-m-d'); ?>" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>🚪</span>
                            <span>Gate Entry Time</span>
                        </label>
                        <input type="time" name="gate_entry_time" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        <small id="gate_entry_time_12" style="display:block; margin-top:6px; color:#6b7280; font-size:12px;"></small>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                            <span>📅</span>
                            <span>Gate Entry Date</span>
                        </label>
                        <input type="date" name="gate_entry_date" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s;" onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 0 3px rgba(6, 182, 212, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    </div>
                </div>
            </div>

            <!-- Section 7: Other Remarks -->
            <div class="card" style="margin-bottom: 20px; border-left: 4px solid #ef4444; background: linear-gradient(to right, #fef2f2 0%, white 10%);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                    <div style="background: #ef4444; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);">7</div>
                    <div>
                        <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 700;">📝 Other (Any Specific Remarks)</h3>
                        <p style="margin: 3px 0 0 0; color: #6b7280; font-size: 12px;">Additional observations and notes</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                        <span>📝</span>
                        <span>Remarks</span>
                    </label>
                    <textarea name="other_remarks" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; transition: all 0.3s; min-height: 100px; resize: vertical; font-family: inherit;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 3px rgba(239, 68, 68, 0.1)';" onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"></textarea>
                </div>
                <div class="observation-group" style="margin-top: 15px;">
                    <div>
                        <label>Observation:</label>
                        <select name="other_remarks_obs" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                            <option value="">Select</option>
                            <option value="OK">OK</option>
                            <option value="NOT OK">NOT OK</option>
                            <option value="NA">NA</option>
                        </select>
                    </div>
                    <div>
                        <label>Action If NOT:</label>
                        <input type="text" name="other_remarks_action" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px;">
                    </div>
                </div>
            </div>
                
            <!-- Submit Button Section -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 25px; margin-top: 30px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);">
                <button type="submit" name="submit_loading" id="submitLoadingBtn" class="btn btn-primary btn-full" style="background: white; color: #667eea; padding: 16px 32px; font-size: 18px; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s; border: none; width: 100%;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                    <span id="submitBtnText">📦 SAVE LOADING CHECKLIST</span>
                    <span id="submitBtnLoader" style="display: none;">
                        <span style="display: inline-block; width: 20px; height: 20px; border: 3px solid #667eea; border-top: 3px solid transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 10px; vertical-align: middle;"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <script>
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

        // Initialize 12-hour display for reporting time
        (function initTimeDisplays() {
            const rptTime = document.querySelector('input[name="reporting_time_plant"]');
            const rptTime12 = document.getElementById('reporting_time_plant_12');
            if (rptTime && rptTime12) {
                rptTime12.textContent = rptTime.value ? ('12-hour: ' + to12Hour(rptTime.value)) : '';
                rptTime.addEventListener('input', () => {
                    rptTime12.textContent = rptTime.value ? ('12-hour: ' + to12Hour(rptTime.value)) : '';
                });
            }
        })();

        // Auto-fill transport company name
        document.getElementById('transport_company_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('transport_company_name').value = selectedOption.text;
        });

        // Auto-fill outgoing customer name
        document.getElementById('outgoing_customer_id')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const hiddenName = document.getElementById('outgoing_customer_name');
            if (hiddenName) hiddenName.value = selectedOption?.text || '';
        });

        // Show loader on form submit
        document.getElementById('loadingForm')?.addEventListener('submit', function(e) {
            const inwardIdField = document.getElementById('inward_id');
            const insideMsg = document.getElementById('vehicle-inside-message');
            if (!inwardIdField || !inwardIdField.value) {
                // Block submission if vehicle is not INSIDE
                e.preventDefault();
                if (insideMsg) {
                    insideMsg.style.display = 'block';
                    insideMsg.style.background = '#fee2e2';
                    insideMsg.style.color = '#991b1b';
                    insideMsg.style.borderLeftColor = '#ef4444';
                    insideMsg.textContent = '❌ Cannot save: Vehicle does not have an active inward entry with status INSIDE.';
                }
                return;
            }

            const submitBtn = document.getElementById('submitLoadingBtn');
            const btnText = document.getElementById('submitBtnText');
            const btnLoader = document.getElementById('submitBtnLoader');
            
            if (submitBtn && btnText && btnLoader) {
                // Disable button and show loader
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';
            }
        });
        
        // Fetch vehicle details for loading checklist
        function fetchVehicleDetailsForLoading() {
            const vehicleNumber = document.getElementById('vehicle_registration_number').value.trim().toUpperCase();
            const messageDiv = document.getElementById('vehicle-fetch-message');
            const inwardIdField = document.getElementById('inward_id');
            const insideMsg = document.getElementById('vehicle-inside-message');
            const submitBtn = document.getElementById('submitLoadingBtn');
            
            if (!vehicleNumber || vehicleNumber.length < 3) {
                messageDiv.style.display = 'none';
                if (inwardIdField) inwardIdField.value = '';
                if (insideMsg) insideMsg.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            // Also link this checklist to the current "inside" gate entry (if any)
            fetch('check_vehicle_inside.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to check vehicle inside')))
                .then(data => {
                    if (inwardIdField) {
                        inwardIdField.value = (data && data.isInside && data.entry_id) ? data.entry_id : '';
                    }

                    // Auto-fill Gate Entry Date/Time from INSIDE inward entry
                    const gateEntryDate = document.querySelector('input[name="gate_entry_date"]');
                    const gateEntryTime = document.querySelector('input[name="gate_entry_time"]');
                    const gateEntryTime12 = document.getElementById('gate_entry_time_12');
                    if (data && data.isInside && data.inward_date && data.inward_time) {
                        if (gateEntryDate) gateEntryDate.value = data.inward_date;
                        if (gateEntryTime) gateEntryTime.value = data.inward_time;
                        if (gateEntryTime12) {
                            gateEntryTime12.textContent = data.inward_time_12 ? ('12-hour: ' + data.inward_time_12) : ('12-hour: ' + to12Hour(data.inward_time));
                        }
                    } else {
                        if (gateEntryDate) gateEntryDate.value = '';
                        if (gateEntryTime) gateEntryTime.value = '';
                        if (gateEntryTime12) gateEntryTime12.textContent = '';
                    }

                    if (insideMsg) {
                        insideMsg.style.display = 'block';
                        if (data && data.isInside && data.entry_id) {
                            insideMsg.style.background = '#d1fae5';
                            insideMsg.style.color = '#065f46';
                            insideMsg.style.borderLeftColor = '#10b981';
                            insideMsg.textContent = '✅ Vehicle is INSIDE (Inward: ' + (data.entry_number || data.entry_id) + '). Loading entry allowed.';
                            if (submitBtn) submitBtn.disabled = false;
                        } else {
                            insideMsg.style.background = '#fee2e2';
                            insideMsg.style.color = '#991b1b';
                            insideMsg.style.borderLeftColor = '#ef4444';
                            insideMsg.textContent = '❌ Vehicle is NOT INSIDE. Do inward entry first. Loading entry blocked.';
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
                        insideMsg.textContent = '❌ Could not validate inside status. Loading entry blocked.';
                    }
                    if (submitBtn) submitBtn.disabled = true;
                });

            messageDiv.style.display = 'block';
            messageDiv.style.background = '#fef3c7';
            messageDiv.style.color = '#92400e';
            messageDiv.style.border = '1px solid #fbbf24';
            messageDiv.textContent = '⏳ Fetching vehicle details...';

            fetch('fetch_checklist_vehicle_details.php?vehicle_number=' + encodeURIComponent(vehicleNumber))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error('Expected JSON but got: ' + text.substring(0, 100));
                        });
                    }
                })
                .then(data => {
                    if (!data || typeof data !== 'object') {
                        throw new Error('Invalid response format');
                    }
                    if (data.success && data.found) {
                        // Fill vehicle details
                        if (data.vehicle) {
                            if (data.vehicle.maker && data.vehicle.model) {
                                document.getElementById('vehicle_type_make').value = 
                                    (data.vehicle.maker + ' ' + (data.vehicle.model || '')).trim();
                            }
                            if (data.vehicle.capacity) {
                                document.getElementById('capacity').value = data.vehicle.capacity;
                            }
                        }

                        // Handle multiple drivers - show dropdown if 2+ drivers
                        const driverDropdownContainer = document.getElementById('driver_dropdown_container');
                        const driverSelection = document.getElementById('driver_selection');
                        const driverNameInput = document.getElementById('driver_name');
                        const selectedDriverId = document.getElementById('selected_driver_id');
                        
                        if (data.has_multiple_drivers && data.all_drivers && data.all_drivers.length > 1) {
                            // Show dropdown and populate it
                            driverDropdownContainer.style.display = 'block';
                            driverSelection.innerHTML = '<option value="">-- Select Driver --</option>';
                            
                            data.all_drivers.forEach(function(driver) {
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
                            driverSelection.addEventListener('change', function() {
                                const selectedId = this.value;
                                const selectedDriver = data.all_drivers.find(d => d.driver_id == selectedId);
                                if (selectedDriver) {
                                    driverNameInput.value = selectedDriver.driver_name;
                                    selectedDriverId.value = selectedDriver.driver_id;
                                    
                                    // Update driving license info if available
                                    if (selectedDriver.license_number) {
                                        const dlSelect = document.querySelector('select[name="doc_driving_licence_obs"]');
                                        if (dlSelect) {
                                            const isExpired = selectedDriver.license_expiry && 
                                                new Date(selectedDriver.license_expiry) < new Date();
                                            dlSelect.value = isExpired ? 'NOT OK' : 'OK';
                                        }
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
                            if (data.driver && data.driver.driver_name) {
                                driverNameInput.value = data.driver.driver_name;
                                if (data.driver.driver_id) {
                                    selectedDriverId.value = data.driver.driver_id;
                                }
                            }
                        }
                        
                        // Fill driver details (for backward compatibility)
                        if (data.driver) {
                            if (data.driver.driver_name && !data.has_multiple_drivers) {
                                document.getElementById('driver_name').value = data.driver.driver_name;
                            }
                            if (data.driver.license_number) {
                                document.getElementById('license_number').value = data.driver.license_number;
                            }
                        }

                        // Fill transporter details
                        if (data.transporter) {
                            const transporterSelect = document.getElementById('transport_company_id');
                            if (data.transporter.transporter_id) {
                                transporterSelect.value = data.transporter.transporter_id;
                            }
                            document.getElementById('transport_company_name').value = data.transporter.transporter_name || '';
                            // Trigger change event to update hidden field
                            transporterSelect.dispatchEvent(new Event('change'));
                        }

                        // Fill document observations
                        if (data.documents) {
                            // Driving Licence
                            if (data.documents.driving_licence && data.documents.driving_licence.status) {
                                const dlSelect = document.querySelector('select[name="doc_driving_licence_obs"]');
                                if (dlSelect) {
                                    dlSelect.value = data.documents.driving_licence.status === 'Yes' ? 'OK' : 
                                                     (data.documents.driving_licence.status === 'No' ? 'NOT OK' : 'NA');
                                    if (data.documents.driving_licence.details) {
                                        const dlRemarks = document.querySelector('input[name="doc_driving_licence_remarks"]');
                                        if (dlRemarks) dlRemarks.value = data.documents.driving_licence.details;
                                    }
                                }
                            }

                            // RC Book
                            if (data.documents.rc_book && data.documents.rc_book.status) {
                                const rcSelect = document.querySelector('select[name="doc_rc_book_obs"]');
                                if (rcSelect) {
                                    rcSelect.value = data.documents.rc_book.status === 'Yes' ? 'OK' : 
                                                    (data.documents.rc_book.status === 'No' ? 'NOT OK' : 'NA');
                                    if (data.documents.rc_book.details) {
                                        const rcRemarks = document.querySelector('input[name="doc_rc_book_remarks"]');
                                        if (rcRemarks) rcRemarks.value = data.documents.rc_book.details;
                                    }
                                }
                            }

                            // Permit
                            if (data.documents.permit && data.documents.permit.status) {
                                const permitSelect = document.querySelector('select[name="doc_permit_obs"]');
                                if (permitSelect) {
                                    permitSelect.value = data.documents.permit.status === 'Yes' ? 'OK' : 
                                                         (data.documents.permit.status === 'No' ? 'NOT OK' : 'NA');
                                    if (data.documents.permit.details) {
                                        const permitRemarks = document.querySelector('input[name="doc_permit_remarks"]');
                                        if (permitRemarks) permitRemarks.value = data.documents.permit.details;
                                    }
                                }
                            }

                            // Insurance
                            if (data.documents.insurance && data.documents.insurance.status) {
                                const insSelect = document.querySelector('select[name="doc_insurance_obs"]');
                                if (insSelect) {
                                    insSelect.value = data.documents.insurance.status === 'Yes' ? 'OK' : 
                                                     (data.documents.insurance.status === 'No' ? 'NOT OK' : 'NA');
                                    if (data.documents.insurance.details) {
                                        const insRemarks = document.querySelector('input[name="doc_insurance_remarks"]');
                                        if (insRemarks) insRemarks.value = data.documents.insurance.details;
                                    }
                                }
                            }

                            // PUC Certificate
                            if (data.documents.puc_certificate && data.documents.puc_certificate.status) {
                                const pucSelect = document.querySelector('select[name="doc_puc_certificate_obs"]');
                                if (pucSelect) {
                                    pucSelect.value = data.documents.puc_certificate.status === 'Yes' ? 'OK' : 
                                                      (data.documents.puc_certificate.status === 'No' ? 'NOT OK' : 'NA');
                                    if (data.documents.puc_certificate.details) {
                                        const pucRemarks = document.querySelector('input[name="doc_puc_certificate_remarks"]');
                                        if (pucRemarks) pucRemarks.value = data.documents.puc_certificate.details;
                                    }
                                }
                            }
                        }

                        if (messageDiv) {
                            messageDiv.style.background = '#d1fae5';
                            messageDiv.style.color = '#065f46';
                            messageDiv.style.borderLeft = '4px solid #10b981';
                            messageDiv.textContent = '✅ ' + (data.message || 'Vehicle details loaded successfully!');
                        }
                    } else {
                        if (messageDiv) {
                            messageDiv.style.background = '#fee2e2';
                            messageDiv.style.color = '#991b1b';
                            messageDiv.style.borderLeft = '4px solid #ef4444';
                            messageDiv.textContent = 'ℹ️ ' + (data.message || 'Vehicle not found. Please enter details manually.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (messageDiv) {
                        messageDiv.style.background = '#fee2e2';
                        messageDiv.style.color = '#991b1b';
                        messageDiv.style.borderLeft = '4px solid #ef4444';
                        messageDiv.textContent = '❌ Error fetching vehicle details. Please try again.';
                    }
                });
        }
    </script>
    
    </div><!-- end container -->
</div><!-- end loading-form -->

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
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager')): ?>
        <a href="?page=management">
            <span class="icon">📈</span>
            Management
        </a>
        <a href="?page=admin">
            <span class="icon">⚙️</span>
            Masters
        </a>
        <?php
    endif; ?>
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
