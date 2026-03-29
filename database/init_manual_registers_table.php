<?php
/**
 * Initialize Manual Registers Table
 */

function initManualRegistersTable($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS `manual_registers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `register_type` VARCHAR(50) NOT NULL,
        `entry_date` DATE NOT NULL,
        `vehicle_no` VARCHAR(50),
        `challan_no` VARCHAR(50),
        `gate_pass_no` VARCHAR(50),
        `time_in` TIME,
        `time_out` TIME,
        `party_name` VARCHAR(255),
        `material_desc` TEXT,
        `quantity` VARCHAR(100),
        `transporter_name` VARCHAR(255),
        `security_sign` VARCHAR(100),
        `remarks` TEXT,
        `department` VARCHAR(100),
        `recheck_status` VARCHAR(255),
        `return_date_time` DATETIME,
        `received_by` VARCHAR(100),
        `handed_over_to` VARCHAR(100),
        `reference_no` VARCHAR(100),
        `received_quantity` VARCHAR(100),
        `out_time_date` DATETIME,
        `dynamic_data` TEXT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_register_type` (`register_type`),
        INDEX `idx_entry_date` (`entry_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!mysqli_query($conn, $sql)) {
        error_log("Error creating manual_registers table: " . mysqli_error($conn));
        return false;
    }
    return true;
}
?>