<?php
require_once 'config.php';
$conn = getDatabaseConnection();
$res = mysqli_query($conn, "DESCRIBE vehicle_master");
while ($row = mysqli_fetch_assoc($res)) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}
// Also check if any column exists for transporter
$res = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_master LIKE 'transporter_id'");
if (mysqli_num_rows($res) == 0) {
    echo "Column 'transporter_id' DOES NOT exist.\n";
} else {
    echo "Column 'transporter_id' EXISTS.\n";
}
unlink(__FILE__);
