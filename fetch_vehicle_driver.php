<?php
/**
 * AJAX endpoint to fetch vehicle and driver details
 */

// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Load configuration (auto-detects environment)
require_once 'config.php';

session_start();

// Database connection using auto-detected environment settings
$conn = getDatabaseConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if vehicle_master table has is_active column
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'is_active'");
$has_is_active = mysqli_num_rows($check_col) > 0;

try {
    // Get vehicle number from request
    $vehicle_number = isset($_GET['vehicle_number']) ? strtoupper(trim($_GET['vehicle_number'])) : '';

    if (empty($vehicle_number)) {
        echo json_encode(['success' => false, 'message' => 'Vehicle number is required']);
        exit;
    }

// Fetch vehicle details with driver information
$vehicle_number = mysqli_real_escape_string($conn, $vehicle_number);

$where_clause = "v.vehicle_number = '$vehicle_number'";
if ($has_is_active) {
    $where_clause .= " AND v.is_active = 1";
}

$query = "SELECT 
            v.id as vehicle_id,
            v.vehicle_number,
            v.maker,
            v.model,
            v.fuel_type,
            v.rc_owner_name,
            v.driver_id
          FROM vehicle_master v
          WHERE $where_clause
          LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    
    // Get all assigned drivers for this vehicle
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_drivers'");
    $assigned_drivers = [];
    if (mysqli_num_rows($check_table) > 0) {
        $drivers_query = "SELECT 
                            vd.is_primary, 
                            d.id as driver_id,
                            d.driver_name,
                            d.mobile as driver_mobile,
                            d.license_number,
                            d.photo as driver_photo,
                            t.id as transporter_id,
                            t.transporter_name
                          FROM vehicle_drivers vd
                          INNER JOIN driver_master d ON vd.driver_id = d.id AND d.is_active = 1
                          LEFT JOIN transporter_master t ON d.transporter_id = t.id AND t.is_active = 1
                          WHERE vd.vehicle_id = {$data['vehicle_id']}
                          ORDER BY vd.is_primary DESC, d.driver_name";
        
        $drivers_result = mysqli_query($conn, $drivers_query);
        if ($drivers_result) {
            while ($driver = mysqli_fetch_assoc($drivers_result)) {
                $assigned_drivers[] = $driver;
            }
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'found' => true,
        'message' => 'Vehicle found in master',
        'vehicle' => [
            'vehicle_number' => $data['vehicle_number'],
            'maker' => $data['maker'],
            'model' => $data['model'],
            'fuel_type' => $data['fuel_type'],
            'rc_owner' => $data['rc_owner_name']
        ],
        'driver' => null,
        'drivers' => $assigned_drivers,  // All assigned drivers
        'transporter' => null
    ];
    
    // Add primary driver details for backward compatibility
    if (count($assigned_drivers) > 0) {
        $primary_driver = $assigned_drivers[0]; // First one is primary (already sorted)
        $response['driver'] = [
            'driver_name' => $primary_driver['driver_name'],
            'driver_mobile' => $primary_driver['driver_mobile'],
            'license_number' => $primary_driver['license_number'],
            'photo' => $primary_driver['driver_photo']
        ];
        
        // Set transporter from primary driver if available
        if ($primary_driver['transporter_id']) {
            $response['transporter'] = [
                'transporter_id' => $primary_driver['transporter_id'],
                'transporter_name' => $primary_driver['transporter_name']
            ];
        }
    }
    
    echo json_encode($response);
    
} else {
    // Vehicle not found in master
    echo json_encode([
        'success' => true,
        'found' => false,
        'message' => 'New vehicle - Please enter all details'
    ]);
}

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

