<?php
/**
 * Check if a vehicle is already inside the facility
 * Returns JSON response with vehicle status
 */

require_once 'config.php';
session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = getDatabaseConnection();

if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get vehicle number or inward id from query parameters
$vehicle_number = isset($_GET['vehicle_number']) ? strtoupper(trim($_GET['vehicle_number'])) : '';
$inward_id = isset($_GET['inward_id']) ? intval($_GET['inward_id']) : 0;

if ($inward_id > 0) {
    // Check by inward_id directly (used for edit mode)
    $query = "SELECT id, entry_number, inward_datetime, driver_name, status, items_json 
              FROM truck_inward 
              WHERE id = $inward_id 
              LIMIT 1";
} elseif (!empty($vehicle_number) && strlen($vehicle_number) >= 4) {
    // Escape vehicle number for SQL
    $vehicle_number = mysqli_real_escape_string($conn, $vehicle_number);

    // Check if vehicle is already inside
    $query = "SELECT id, entry_number, inward_datetime, driver_name, status, items_json 
              FROM truck_inward 
              WHERE vehicle_number = '$vehicle_number' AND status != 'exited' 
              ORDER BY inward_datetime DESC 
              LIMIT 1";
} else {
    header('Content-Type: application/json');
    echo json_encode(['isInside' => false, 'message' => 'Invalid parameters']);
    exit;
}

$result = mysqli_query($conn, $query);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $entry = mysqli_fetch_assoc($result);

    // Format datetime for display
    $inward_ts = strtotime($entry['inward_datetime']);
    $inward_datetime = date('d/m/Y h:i A', $inward_ts);

    // Machine-readable values (for form auto-fill)
    $inward_date = date('Y-m-d', $inward_ts); // for <input type="date">
    $inward_time = date('H:i', $inward_ts); // for <input type="time">
    $inward_time_12 = date('h:i A', $inward_ts); // 12-hour display (e.g. 02:15 PM)

    header('Content-Type: application/json');
    echo json_encode([
        'isInside' => true,
        'entry_id' => $entry['id'],
        'entry_number' => $entry['entry_number'],
        'inward_datetime' => $inward_datetime,
        'inward_date' => $inward_date,
        'inward_time' => $inward_time,
        'inward_time_12' => $inward_time_12,
        'driver_name' => $entry['driver_name'],
        'items_json' => $entry['items_json']
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'isInside' => false,
        'message' => 'Vehicle is not currently inside'
    ]);
}

mysqli_close($conn);
?>