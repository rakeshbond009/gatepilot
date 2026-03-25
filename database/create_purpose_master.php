<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config.php';

$conn = getDatabaseConnection();

// Create table
$sql = "CREATE TABLE IF NOT EXISTS `purpose_master` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `purpose_name` VARCHAR(100) NOT NULL,
  `purpose_type` ENUM('delivery', 'pickup', 'service', 'visit', 'return', 'other') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'purpose_master' created successfully or already exists.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// Seed data
$check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM purpose_master");
$row = mysqli_fetch_assoc($check);
if ($row['cnt'] == 0) {
    $seed = "INSERT INTO `purpose_master` (`purpose_name`, `purpose_type`) VALUES
    ('Material Delivery', 'delivery'),
    ('Finished Goods Pickup', 'pickup'),
    ('Raw Material Delivery', 'delivery'),
    ('Equipment Service', 'service'),
    ('Sales Return', 'return'),
    ('Vendor Visit', 'visit'),
    ('Others', 'other')";

    if (mysqli_query($conn, $seed)) {
        echo "Seed data inserted.\n";
    } else {
        echo "Error inserting seed data: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Table already has data.\n";
}

// Check/Add column in truck_inward if not exists (although error was about table missing, not column)
// The schema shows purpose_id int, purpose_name varchar.
// The code in index.php joins on purpose_id.
// Let's verify if purpose_id exists in truck_inward.
$cols = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'purpose_id'");
if (mysqli_num_rows($cols) == 0) {
    // Add columns if they don't exist
    mysqli_query($conn, "ALTER TABLE truck_inward ADD COLUMN purpose_id INT");
    mysqli_query($conn, "ALTER TABLE truck_inward ADD KEY idx_purpose_id (purpose_id)");
    echo "Added purpose_id column to truck_inward.\n";
}

// Also check if index.php uses purpose_name from truck_inward directly (it does in line 12482)
$cols2 = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'purpose_name'");
if (mysqli_num_rows($cols2) == 0) {
    mysqli_query($conn, "ALTER TABLE truck_inward ADD COLUMN purpose_name VARCHAR(100)");
    echo "Added purpose_name column to truck_inward.\n";
}

echo "Done.";
?>