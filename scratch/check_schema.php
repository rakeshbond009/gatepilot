<?php
include 'config.php';
$m = getMasterDatabaseConnection();
if (!$m) die("Connection failed\n");
$r = mysqli_query($m, "DESCRIBE tenants");
if (!$r) die("Query failed: " . mysqli_error($m) . "\n");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
