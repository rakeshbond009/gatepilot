<?php
/**
 * Excel Export for Reports
 */

// Include config for database connection
require_once 'config.php';
$conn = getDatabaseConnection();

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$transporter_filter = isset($_GET['transporter']) ? mysqli_real_escape_string($conn, $_GET['transporter']) : '';
$driver_filter = isset($_GET['driver']) ? mysqli_real_escape_string($conn, $_GET['driver']) : '';
$vehicle_filter = isset($_GET['vehicle']) ? mysqli_real_escape_string($conn, $_GET['vehicle']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
// Selected reports tab: inward / loading / unloading / outward / patrol / employee
$export_tab = strtolower(trim($_GET['tab'] ?? 'inward'));
if (!in_array($export_tab, ['inward', 'loading', 'unloading', 'outward', 'patrol', 'employee', 'registers'], true)) {
    $export_tab = 'inward';
}

// Ensure checklist tables exist if exporting those tabs
if ($export_tab === 'loading' || $export_tab === 'unloading') {
    require_once __DIR__ . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);
}

// Ensure patrol tables exist if exporting patrol
if ($export_tab === 'patrol') {
    require_once __DIR__ . '/database/init_patrol_tables.php';
    initPatrolTables($conn);
}

// Build query + columns based on selected tab
$columns = [];
$result = null;

if ($export_tab === 'inward') {
    // Build WHERE clause (same logic as reports page)
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "ti.inward_date BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "ti.inward_date >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "ti.inward_date <= '$end_date'";
    }
    if ($transporter_filter) {
        $where[] = "ti.transporter_name LIKE '%$transporter_filter%'";
    }
    if ($driver_filter) {
        $where[] = "ti.driver_name LIKE '%$driver_filter%'";
    }
    if ($vehicle_filter) {
        $where[] = "ti.vehicle_number LIKE '%$vehicle_filter%'";
    }
    if ($status_filter) {
        $where[] = "ti.status = '$status_filter'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Entry Number', 'Vehicle Number', 'Driver Name', 'Driver Mobile', 'Transporter', 'Purpose', 'Bill Number', 'Inward Time', 'Outward Time', 'Duration', 'Status'];
    $query = "
        SELECT 
            ti.entry_number as 'Entry Number',
            ti.vehicle_number as 'Vehicle Number',
            ti.driver_name as 'Driver Name',
            ti.driver_mobile as 'Driver Mobile',
            ti.transporter_name as 'Transporter',
            ti.purpose_name as 'Purpose',
            ti.bill_number as 'Bill Number',
            DATE_FORMAT(ti.inward_datetime, '%d-%m-%Y %H:%i') as 'Inward Time',
            DATE_FORMAT(tou.outward_datetime, '%d-%m-%Y %H:%i') as 'Outward Time',
            CONCAT(COALESCE(tou.duration_hours, 0), ' hrs') as 'Duration',
            CASE 
                WHEN ti.status = 'inside' THEN 'Inside'
                WHEN ti.status = 'exited' THEN 'Exited'
                ELSE 'Cancelled'
            END as 'Status'
        FROM truck_inward ti
        LEFT JOIN truck_outward tou ON ti.id = tou.inward_id
        $where_sql
        ORDER BY ti.inward_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
} elseif ($export_tab === 'loading') {
    // Filters similar to reports loading tab
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "DATE(reporting_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "DATE(reporting_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "DATE(reporting_datetime) <= '$end_date'";
    }
    if ($vehicle_filter) {
        $where[] = "vehicle_registration_number LIKE '%$vehicle_filter%'";
    }
    if ($driver_filter) {
        $where[] = "driver_name LIKE '%$driver_filter%'";
    }
    if ($transporter_filter) {
        $where[] = "transport_company_name LIKE '%$transporter_filter%'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Document ID', 'Reporting Date/Time', 'Vehicle Number', 'Vehicle Make', 'Capacity', 'Loading Location', 'Body Type', 'Transporter', 'Driver Name', 'Licence No', 'Status'];
    $query = "
        SELECT
            document_id as 'Document ID',
            DATE_FORMAT(reporting_datetime, '%d-%m-%Y %H:%i') as 'Reporting Date/Time',
            vehicle_registration_number as 'Vehicle Number',
            vehicle_type_make as 'Vehicle Make',
            capacity as 'Capacity',
            loading_location as 'Loading Location',
            body_type as 'Body Type',
            transport_company_name as 'Transporter',
            driver_name as 'Driver Name',
            license_number as 'Licence No',
            status as 'Status'
        FROM vehicle_loading_checklist
        $where_sql
        ORDER BY reporting_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
} elseif ($export_tab === 'outward') {
    // Build WHERE clause for outward
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "DATE(tou.outward_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "DATE(tou.outward_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "DATE(tou.outward_datetime) <= '$end_date'";
    }
    if ($vehicle_filter) {
        $where[] = "ti.vehicle_number LIKE '%$vehicle_filter%'";
    }
    if ($driver_filter) {
        $where[] = "ti.driver_name LIKE '%$driver_filter%'";
    }
    if ($transporter_filter) {
        $where[] = "ti.transporter_name LIKE '%$transporter_filter%'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Outward Date/Time', 'Vehicle Number', 'Driver Name', 'Transporter', 'Outward By', 'Duration (hrs)', 'Remarks'];
    $query = "
        SELECT
            DATE_FORMAT(tou.outward_datetime, '%d-%m-%Y %H:%i') as 'Outward Date/Time',
            ti.vehicle_number as 'Vehicle Number',
            ti.driver_name as 'Driver Name',
            ti.transporter_name as 'Transporter',
            tou.outward_by_name as 'Outward By',
            tou.duration_hours as 'Duration (hrs)',
            tou.outward_remarks as 'Remarks'
        FROM truck_outward tou
        JOIN truck_inward ti ON tou.inward_id = ti.id
        $where_sql
        ORDER BY tou.outward_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
} elseif ($export_tab === 'patrol') {
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "DATE(pl.scan_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "DATE(pl.scan_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "DATE(pl.scan_datetime) <= '$end_date'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Scan Time', 'Location Name', 'Area/Building', 'Guard Name', 'Session ID'];
    $query = "
        SELECT
            DATE_FORMAT(pl.scan_datetime, '%d-%m-%Y %H:%i') as 'Scan Time',
            loc.location_name as 'Location Name',
            loc.area_site_building as 'Area/Building',
            pl.guard_name as 'Guard Name',
            pl.session_id as 'Session ID'
        FROM patrol_logs pl
        JOIN patrol_locations loc ON pl.location_id = loc.id
        $where_sql
        ORDER BY pl.scan_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
} elseif ($export_tab === 'employee') {
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "DATE(e.inward_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "DATE(e.inward_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "DATE(e.inward_datetime) <= '$end_date'";
    }
    if ($driver_filter) {
        $where[] = "e.employee_name LIKE '%$driver_filter%'";
    }
    if ($vehicle_filter) {
        $where[] = "e.vehicle_number LIKE '%$vehicle_filter%'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Employee ID', 'Employee Name', 'Vehicle Number', 'Department', 'Inward Time', 'Outward Time', 'Status', 'Remarks'];
    $query = "
        SELECT
            e.employee_id as 'Employee ID',
            e.employee_name as 'Employee Name',
            e.vehicle_number as 'Vehicle Number',
            em.department as 'Department',
            DATE_FORMAT(e.inward_datetime, '%d-%m-%Y %H:%i') as 'Inward Time',
            DATE_FORMAT(e.outward_datetime, '%d-%m-%Y %H:%i') as 'Outward Time',
            UPPER(e.status) as 'Status',
            e.remarks as 'Remarks'
        FROM employee_entries e
        LEFT JOIN employee_master em ON e.employee_id = em.employee_id
        $where_sql
        ORDER BY e.inward_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
} elseif ($export_tab === 'registers') {
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "entry_date BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "entry_date >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "entry_date <= '$end_date'";
    }
    if ($vehicle_filter) {
        $where[] = "vehicle_no LIKE '%$vehicle_filter%'";
    }
    if ($driver_filter) {
        $where[] = "party_name LIKE '%$driver_filter%'";
    }
    if ($transporter_filter) {
        $where[] = "transporter_name LIKE '%$transporter_filter%'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Date', 'Register Type', 'Vehicle Number', 'Party Name', 'Material', 'Quantity', 'Department', 'Challan No', 'Gate Pass No', 'Ref No', 'Transporter', 'In Time', 'Out Time', 'Recheck Status', 'Return Date', 'Received By', 'Handed To', 'Received Qty', 'Remarks'];
    $query = "
        SELECT
            DATE_FORMAT(entry_date, '%d-%m-%Y') as 'Date',
            REPLACE(register_type, '_', ' ') as 'Register Type',
            vehicle_no as 'Vehicle Number',
            party_name as 'Party Name',
            material_desc as 'Material',
            quantity as 'Quantity',
            department as 'Department',
            challan_no as 'Challan No',
            gate_pass_no as 'Gate Pass No',
            reference_no as 'Ref No',
            transporter_name as 'Transporter',
            DATE_FORMAT(time_in, '%H:%i') as 'In Time',
            IF(time_out IS NOT NULL, DATE_FORMAT(time_out, '%H:%i'), 'Inside') as 'Out Time',
            recheck_status as 'Recheck Status',
            IF(return_date_time IS NOT NULL, DATE_FORMAT(return_date_time, '%d-%m-%Y %H:%i'), '') as 'Return Date',
            received_by as 'Received By',
            handed_over_to as 'Handed To',
            received_quantity as 'Received Qty',
            remarks as 'Remarks'
        FROM manual_registers
        $where_sql
        ORDER BY entry_date DESC, created_at DESC
    ";
    $result = mysqli_query($conn, $query);
} else { // unloading
    $where = [];
    if ($start_date && $end_date) {
        $where[] = "DATE(reporting_datetime) BETWEEN '$start_date' AND '$end_date'";
    } elseif ($start_date) {
        $where[] = "DATE(reporting_datetime) >= '$start_date'";
    } elseif ($end_date) {
        $where[] = "DATE(reporting_datetime) <= '$end_date'";
    }
    if ($vehicle_filter) {
        $where[] = "vehicle_registration_number LIKE '%$vehicle_filter%'";
    }
    if ($driver_filter) {
        $where[] = "driver_name LIKE '%$driver_filter%'";
    }
    if ($transporter_filter) {
        $where[] = "transport_company_name LIKE '%$transporter_filter%'";
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $columns = ['Document ID', 'Reporting Date/Time', 'Vehicle Number', 'Vehicle Make', 'Body Type', 'Transporter', 'Driver Name', 'Driver Mobile', 'Vendor', 'Challan No', 'Invoice No', 'GST No', 'Status'];
    $query = "
        SELECT
            document_id as 'Document ID',
            DATE_FORMAT(reporting_datetime, '%d-%m-%Y %H:%i') as 'Reporting Date/Time',
            vehicle_registration_number as 'Vehicle Number',
            vehicle_type as 'Vehicle Make',
            body_type as 'Body Type',
            transport_company_name as 'Transporter',
            driver_name as 'Driver Name',
            driver_mobile as 'Driver Mobile',
            vendor_name as 'Vendor',
            challan_no as 'Challan No',
            invoice_no as 'Invoice No',
            gst_number as 'GST No',
            status as 'Status'
        FROM vehicle_unloading_checklist
        $where_sql
        ORDER BY reporting_datetime DESC
    ";
    $result = mysqli_query($conn, $query);
}

// Generate filename based on filters
$filename_prefix = 'Report';
switch ($export_tab) {
    case 'inward':
        $filename_prefix = 'Inward_Report';
        break;
    case 'outward':
        $filename_prefix = 'Outward_Report';
        break;
    case 'loading':
        $filename_prefix = 'Loading_Report';
        break;
    case 'unloading':
        $filename_prefix = 'Unloading_Report';
        break;
    case 'patrol':
        $filename_prefix = 'Patrol_Report';
        break;
    case 'employee':
        $filename_prefix = 'Employee_Report';
        break;
    case 'registers':
        $filename_prefix = 'Registers_Report';
        break;
}

$filename_parts = [$filename_prefix];
if ($start_date && $end_date) {
    $filename_parts[] = $start_date . '_to_' . $end_date;
} elseif ($start_date) {
    $filename_parts[] = 'from_' . $start_date;
} elseif ($end_date) {
    $filename_parts[] = 'to_' . $end_date;
}

// Add filter indicators if present
if ($transporter_filter)
    $filename_parts[] = 'Trans_' . substr(preg_replace('/[^A-Za-z0-9]/', '', $transporter_filter), 0, 8);
if ($driver_filter)
    $filename_parts[] = 'Driver_' . substr(preg_replace('/[^A-Za-z0-9]/', '', $driver_filter), 0, 8);
if ($vehicle_filter)
    $filename_parts[] = 'Veh_' . substr(preg_replace('/[^A-Za-z0-9]/', '', $vehicle_filter), 0, 8);
if ($export_tab === 'inward' && $status_filter)
    $filename_parts[] = ucfirst($status_filter);

$filename = implode('_', $filename_parts) . '.xls';
$filename_encoded = rawurlencode($filename);

// Detect mobile user agent
$is_mobile = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $_SERVER['HTTP_USER_AGENT'] ?? '');

ob_clean(); // Ensure no stray output

if ($is_mobile) {
    // For mobile: Output CSV format (using fputcsv for reliability)
    $filename = str_replace('.xls', '.csv', $filename);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    // Output UTF-8 BOM for Excel compatibility if needed, but fputcsv is safer without for some mobile apps
    // fwrite($output, "\xEF\xBB\xBF"); 

    // Header row
    fputcsv($output, $columns);

    // Data rows
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $csv_row = [];
            foreach ($columns as $col) {
                $csv_row[] = $row[$col] ?? '';
            }
            fputcsv($output, $csv_row);
        }
    }
    fclose($output);
} else {
    // For desktop: Output Excel HTML format
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filename_encoded);
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');

    // Output UTF-8 BOM for Excel compatibility
    echo "\xEF\xBB\xBF";

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<style>table { border-collapse: collapse; } th { background: #4F46E5; color: white; border: 1px solid #ccc; } td { border: 1px solid #ccc; }</style>';
    echo '</head><body>';
    echo '<table border="1">';
    echo '<thead><tr>';
    foreach ($columns as $col) {
        echo '<th>' . htmlspecialchars($col) . '</th>';
    }
    echo '</tr></thead><tbody>';

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            foreach ($columns as $col) {
                echo '<td>' . htmlspecialchars($row[$col] ?? '') . '</td>';
            }
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="' . count($columns) . '" style="text-align: center;">No records found.</td></tr>';
    }
    echo '</tbody></table></body></html>';
}


mysqli_close($conn);
?>