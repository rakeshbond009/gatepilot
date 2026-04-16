<?php
/**
 * AJAX endpoint to fetch loading checklist details for outward auto-fill
 * Given an inward_id, it finds the associated loading checklist details
 */

require_once 'includes/init.php';

// The connection is already established by init.php as $conn
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$inward_id = isset($_GET['inward_id']) ? intval($_GET['inward_id']) : 0;

if ($inward_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid inward ID']);
    exit;
}

$query = "
    SELECT 
        customer_id,
        customer_name,
        destination
    FROM vehicle_loading_checklist
    WHERE inward_id = $inward_id 
    ORDER BY created_at DESC
    LIMIT 1
";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

$debug_query_type = 'inward_id';

// Fallback: If no loading checklist found by specific inward_id, try by vehicle number
if (!$data && $inward_id > 0) {
    $v_res = mysqli_query($conn, "SELECT vehicle_number FROM truck_inward WHERE id = $inward_id");
    if ($v_row = mysqli_fetch_assoc($v_res)) {
        $v_num = trim($v_row['vehicle_number']);
        $v_clean = mysqli_real_escape_string($conn, str_replace(' ', '', $v_num));
        
        $debug_query_type = 'vehicle_number_fuzzy';
        
        $query = "
            SELECT 
                customer_id,
                customer_name,
                destination
            FROM vehicle_loading_checklist
            WHERE REPLACE(vehicle_registration_number, ' ', '') = '$v_clean'
            ORDER BY created_at DESC
            LIMIT 1
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);
    }
}

if ($data) {
    echo json_encode([
        'success' => true,
        'found' => true,
        'data' => $data
    ]);
} else {
    echo json_encode([
        'success' => true,
        'found' => false,
        'message' => 'No loading checklist found for this entry'
    ]);
}

mysqli_close($conn);
?>