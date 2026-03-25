<?php
/**
 * AJAX endpoint to fetch vehicle details by vehicle number
 * Returns last entry details for auto-fill
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

$vehicle_number = isset($_GET['vehicle_number']) ? strtoupper(mysqli_real_escape_string($conn, $_GET['vehicle_number'])) : '';

if (empty($vehicle_number)) {
    echo json_encode(['success' => false, 'message' => 'Vehicle number required']);
    exit;
}

// Get the most recent entry for this vehicle
$query = "
    SELECT 
        driver_name,
        driver_mobile,
        transporter_name,
        purpose_name,
        from_location,
        to_location
    FROM truck_inward
    WHERE vehicle_number = '$vehicle_number'
    ORDER BY inward_datetime DESC
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'found' => true,
        'data' => $data,
        'message' => '✅ Previous details found for this vehicle!'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'found' => false,
        'message' => 'No previous records found for this vehicle'
    ]);
}

mysqli_close($conn);
?>

