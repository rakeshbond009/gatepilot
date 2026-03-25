<?php
/**
 * AJAX endpoint to fetch comprehensive vehicle details for Loading/Unloading checklists
 * Returns: Vehicle details, Transporter, Driver, and Document information
 */

// Start output buffering to catch any unwanted output
ob_start();

// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Load configuration
require_once 'config.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

session_start();

// Database connection
$conn = getDatabaseConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get vehicle number from request
    $vehicle_number = isset($_GET['vehicle_number']) ? strtoupper(trim($_GET['vehicle_number'])) : '';

    if (empty($vehicle_number)) {
        echo json_encode(['success' => false, 'message' => 'Vehicle number is required']);
        exit;
    }

    $vehicle_number = mysqli_real_escape_string($conn, $vehicle_number);

    // Initialize response
    $response = [
        'success' => true,
        'found' => false,
        'vehicle' => null,
        'transporter' => null,
        'driver' => null,
        'documents' => null,
        'message' => ''
    ];

    // 1. Fetch vehicle details from vehicle_master
    // Check if registration_validity exists, otherwise use registration_date
    $check_reg_validity = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'registration_validity'");
    $reg_field = (mysqli_num_rows($check_reg_validity) > 0) ? 'registration_validity' : 'registration_date';
    
    // Check if permit columns exist
    $check_permit_validity = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'permit_validity'");
    $check_permit_photo = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'permit_photo'");
    
    $permit_validity_field = (mysqli_num_rows($check_permit_validity) > 0) ? 'v.permit_validity' : 'NULL as permit_validity';
    $permit_photo_field = (mysqli_num_rows($check_permit_photo) > 0) ? 'v.permit_photo' : 'NULL as permit_photo';
    
    $vehicle_query = "SELECT 
                        v.id as vehicle_id,
                        v.vehicle_number,
                        v.maker,
                        v.model,
                        v.fuel_type,
                        v.rc_owner_name,
                        v.$reg_field as registration_validity,
                        v.fitness_validity,
                        v.pollution_validity,
                        v.insurance_validity,
                        $permit_validity_field,
                        $permit_photo_field,
                        v.vehicle_class,
                        v.gross_weight,
                        v.driver_id
                      FROM vehicle_master v
                      WHERE v.vehicle_number = '$vehicle_number'
                      LIMIT 1";

    $vehicle_result = mysqli_query($conn, $vehicle_query);

    if ($vehicle_result && mysqli_num_rows($vehicle_result) > 0) {
        $vehicle_data = mysqli_fetch_assoc($vehicle_result);
        $response['found'] = true;
        $response['vehicle'] = [
            'vehicle_id' => $vehicle_data['vehicle_id'],
            'vehicle_number' => $vehicle_data['vehicle_number'],
            'maker' => $vehicle_data['maker'],
            'model' => $vehicle_data['model'],
            'fuel_type' => $vehicle_data['fuel_type'],
            'rc_owner_name' => $vehicle_data['rc_owner_name'],
            'vehicle_class' => $vehicle_data['vehicle_class'],
            'gross_weight' => $vehicle_data['gross_weight'],
            'capacity' => $vehicle_data['gross_weight'] ? $vehicle_data['gross_weight'] . ' kg' : '',
            'permit_photo' => $vehicle_data['permit_photo'] ?? null
        ];

        // 2. Fetch all driver details (needed for driving license info and driver selection)
        $driver_data = null;
        $all_drivers = [];
        
        // Check if vehicle_drivers table exists
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_drivers'");
        if (mysqli_num_rows($check_table) > 0) {
            // Get ALL drivers from vehicle_drivers
            $drivers_query = "SELECT 
                                d.id as driver_id,
                                d.driver_name,
                                d.mobile as driver_mobile,
                                d.license_number,
                                d.license_expiry,
                                d.transporter_id,
                                vd.is_primary,
                                t.id as transporter_id,
                                t.transporter_name,
                                t.gst_number as transporter_gst
                             FROM vehicle_drivers vd
                             INNER JOIN driver_master d ON vd.driver_id = d.id AND d.is_active = 1
                             LEFT JOIN transporter_master t ON d.transporter_id = t.id AND t.is_active = 1
                             WHERE vd.vehicle_id = {$vehicle_data['vehicle_id']}
                             ORDER BY vd.is_primary DESC, d.driver_name";
            
            $drivers_result = mysqli_query($conn, $drivers_query);
            if ($drivers_result && mysqli_num_rows($drivers_result) > 0) {
                while ($driver_row = mysqli_fetch_assoc($drivers_result)) {
                    $all_drivers[] = $driver_row;
                    // Set primary driver as default driver_data
                    if ($driver_row['is_primary'] == 1 && !$driver_data) {
                        $driver_data = $driver_row;
                    }
                }
                // If no primary driver, use first driver
                if (!$driver_data && count($all_drivers) > 0) {
                    $driver_data = $all_drivers[0];
                }
            }
        } else if ($vehicle_data['driver_id']) {
            // Fallback: Get driver directly from driver_master
            $driver_query = "SELECT 
                                d.id as driver_id,
                                d.driver_name,
                                d.mobile as driver_mobile,
                                d.license_number,
                                d.license_expiry,
                                d.transporter_id,
                                1 as is_primary,
                                t.id as transporter_id,
                                t.transporter_name,
                                t.gst_number as transporter_gst
                             FROM driver_master d
                             LEFT JOIN transporter_master t ON d.transporter_id = t.id AND t.is_active = 1
                             WHERE d.id = {$vehicle_data['driver_id']} AND d.is_active = 1
                             LIMIT 1";
            
            $driver_result = mysqli_query($conn, $driver_query);
            if ($driver_result && mysqli_num_rows($driver_result) > 0) {
                $driver_data = mysqli_fetch_assoc($driver_result);
                $all_drivers[] = $driver_data;
            }
        }

        // Get driver license info for driving_licence document
        $driver_license_status = 'NA';
        $driver_license_details = '';
        if ($driver_data && $driver_data['license_number']) {
            $driver_license_status = 'Yes';
            $driver_license_details = 'License No: ' . $driver_data['license_number'];
            if ($driver_data['license_expiry']) {
                $expiry_status = strtotime($driver_data['license_expiry']) >= strtotime('today') ? 'Valid' : 'Expired';
                $driver_license_details .= ', ' . $expiry_status . ' till: ' . date('d-m-Y', strtotime($driver_data['license_expiry']));
                if ($expiry_status == 'Expired') {
                    $driver_license_status = 'No';
                }
            }
        }
        
        // Get permit information from vehicle_master (already fetched in query)
        $permit_status = 'NA';
        $permit_details = '';
        $permit_validity = $vehicle_data['permit_validity'] ?? null;
        
        if ($permit_validity) {
            // Use permit_validity to determine status
            $is_valid = strtotime($permit_validity) >= strtotime('today');
            $permit_status = $is_valid ? 'Yes' : 'No';
            $permit_details = 'Valid till: ' . date('d-m-Y', strtotime($permit_validity));
        }
        
        $response['documents'] = [
            'driving_licence' => [
                'status' => $driver_license_status,
                'details' => $driver_license_details
            ],
            'rc_book' => [
                'status' => $vehicle_data['registration_validity'] ? 
                    (strtotime($vehicle_data['registration_validity']) >= strtotime('today') ? 'Yes' : 'No') : 'NA',
                'validity' => $vehicle_data['registration_validity'],
                'details' => $vehicle_data['registration_validity'] ? 
                    'Valid till: ' . date('d-m-Y', strtotime($vehicle_data['registration_validity'])) : ''
            ],
            'permit' => [
                'status' => $permit_status,
                'validity' => $permit_validity,
                'details' => $permit_details,
                'photo' => $vehicle_data['permit_photo'] ?? null
            ],
            'fitness_certificate' => [
                'status' => $vehicle_data['fitness_validity'] ? 
                    (strtotime($vehicle_data['fitness_validity']) >= strtotime('today') ? 'Yes' : 'No') : 'NA',
                'validity' => $vehicle_data['fitness_validity'],
                'details' => $vehicle_data['fitness_validity'] ? 
                    'Valid till: ' . date('d-m-Y', strtotime($vehicle_data['fitness_validity'])) : ''
            ],
            'puc_certificate' => [
                'status' => $vehicle_data['pollution_validity'] ? 
                    (strtotime($vehicle_data['pollution_validity']) >= strtotime('today') ? 'Yes' : 'No') : 'NA',
                'validity' => $vehicle_data['pollution_validity'],
                'details' => $vehicle_data['pollution_validity'] ? 
                    'Valid till: ' . date('d-m-Y', strtotime($vehicle_data['pollution_validity'])) : ''
            ],
            'insurance' => [
                'status' => $vehicle_data['insurance_validity'] ? 
                    (strtotime($vehicle_data['insurance_validity']) >= strtotime('today') ? 'Yes' : 'No') : 'NA',
                'validity' => $vehicle_data['insurance_validity'],
                'details' => $vehicle_data['insurance_validity'] ? 
                    'Valid till: ' . date('d-m-Y', strtotime($vehicle_data['insurance_validity'])) : ''
            ]
        ];

        // 3. Get transporter from recent truck_inward entries if not found
        if (!$driver_data || !$driver_data['transporter_id']) {
            $transporter_query = "SELECT 
                                    transporter_id,
                                    transporter_name
                                  FROM truck_inward
                                  WHERE vehicle_number = '$vehicle_number'
                                  AND transporter_id IS NOT NULL
                                  ORDER BY inward_datetime DESC
                                  LIMIT 1";
            
            $transporter_result = mysqli_query($conn, $transporter_query);
            if ($transporter_result && mysqli_num_rows($transporter_result) > 0) {
                $transporter_data = mysqli_fetch_assoc($transporter_result);
                if (!$driver_data) {
                    $driver_data = [];
                }
                $driver_data['transporter_id'] = $transporter_data['transporter_id'];
                $driver_data['transporter_name'] = $transporter_data['transporter_name'];
            }
        }

        // Set driver and transporter in response
        if ($driver_data) {
            $response['driver'] = [
                'driver_id' => $driver_data['driver_id'] ?? null,
                'driver_name' => $driver_data['driver_name'] ?? '',
                'driver_mobile' => $driver_data['driver_mobile'] ?? '',
                'license_number' => $driver_data['license_number'] ?? '',
                'license_expiry' => $driver_data['license_expiry'] ?? null,
                'license_type' => $driver_data['license_number'] ? 'Transport/HGV' : ''
            ];
        }
        
        // Add all drivers list for selection when multiple drivers exist
        if (count($all_drivers) > 0) {
            $response['all_drivers'] = array_map(function($d) {
                return [
                    'driver_id' => $d['driver_id'],
                    'driver_name' => $d['driver_name'],
                    'driver_mobile' => $d['driver_mobile'],
                    'license_number' => $d['license_number'] ?? '',
                    'license_expiry' => $d['license_expiry'] ?? null,
                    'is_primary' => $d['is_primary'] ?? 0
                ];
            }, $all_drivers);
            $response['has_multiple_drivers'] = count($all_drivers) > 1;
        } else {
            $response['all_drivers'] = [];
            $response['has_multiple_drivers'] = false;
        }
        
        if ($driver_data && $driver_data['transporter_id']) {
            $response['transporter'] = [
                'transporter_id' => $driver_data['transporter_id'],
                'transporter_name' => $driver_data['transporter_name'] ?? '',
                'gst_number' => $driver_data['transporter_gst'] ?? ''
            ];
        }

        $response['message'] = '✅ Vehicle details found and loaded!';
    } else {
        // Vehicle not in master, check recent entries
        $recent_query = "SELECT 
                            driver_name,
                            driver_mobile,
                            transporter_name,
                            transporter_id
                         FROM truck_inward
                         WHERE vehicle_number = '$vehicle_number'
                         ORDER BY inward_datetime DESC
                         LIMIT 1";
        
        $recent_result = mysqli_query($conn, $recent_query);
        if ($recent_result && mysqli_num_rows($recent_result) > 0) {
            $recent_data = mysqli_fetch_assoc($recent_result);
            $response['found'] = true;
            $response['driver'] = [
                'driver_name' => $recent_data['driver_name'] ?? '',
                'driver_mobile' => $recent_data['driver_mobile'] ?? ''
            ];
            if ($recent_data['transporter_name']) {
                $response['transporter'] = [
                    'transporter_id' => $recent_data['transporter_id'] ?? null,
                    'transporter_name' => $recent_data['transporter_name'] ?? ''
                ];
            }
            $response['message'] = '⚠️ Vehicle not in master, but found recent entry details';
        } else {
            $response['message'] = 'ℹ️ New vehicle - Please enter all details manually';
        }
    }

    // Clear any output buffer and send JSON
    ob_clean();
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
ob_end_flush();
exit;
?>
