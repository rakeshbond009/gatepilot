<?php
/**
 * Get assigned drivers for a vehicle
 */

header('Content-Type: application/json');

// Load configuration (auto-detects environment)
require_once 'config.php';

// Database connection using auto-detected environment settings
$conn = getDatabaseConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;

if ($vehicle_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID']);
    exit;
}

// Check if vehicle_drivers table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_drivers'");
if (mysqli_num_rows($check_table) == 0) {
    echo json_encode(['success' => true, 'drivers' => []]);
    exit;
}

// Get assigned drivers
$query = "SELECT vd.driver_id, vd.is_primary, d.driver_name, d.mobile
          FROM vehicle_drivers vd
          INNER JOIN driver_master d ON vd.driver_id = d.id
          WHERE vd.vehicle_id = $vehicle_id AND d.is_active = 1
          ORDER BY vd.is_primary DESC, d.driver_name";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
    exit;
}

$drivers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $drivers[] = $row;
}

echo json_encode([
    'success' => true,
    'drivers' => $drivers
]);

mysqli_close($conn);
?>

