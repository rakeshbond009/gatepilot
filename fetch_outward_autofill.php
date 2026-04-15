<?php
/**
 * AJAX endpoint to fetch loading checklist details for outward auto-fill
 * Given an inward_id, it finds the associated loading checklist details
 */

header('Content-Type: application/json');
require_once 'config.php';

$conn = getDatabaseConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$inward_id = isset($_GET['inward_id']) ? intval($_GET['inward_id']) : 0;

if ($inward_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid inward ID']);
    exit;
}

// Fetch the most recent completed loading checklist for this inward entry
$query = "
    SELECT 
        customer_id,
        customer_name,
        destination
    FROM vehicle_loading_checklist
    WHERE inward_id = $inward_id 
    AND status = 'completed'
    ORDER BY created_at DESC
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
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
