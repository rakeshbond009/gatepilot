<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sample_employees.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['EmployeeID', 'Name', 'Mobile', 'Email', 'Department', 'VehicleNo', 'VehicleType', 'RCExpiry', 'LicenseExpiry', 'PollutionExpiry', 'FitnessExpiry']);
fputcsv($output, ['E101', 'John Doe', '9876543210', 'john@example.com', 'Logistics', 'MH12AB1234', 'Car', '31-12-2025', '31-12-2030', '31-12-2024', '31-12-2026']);
fclose($output);
exit;
?>
