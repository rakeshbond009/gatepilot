<?php
/**
 * Initialize Loading and Unloading Checklist Tables
 * This file can be included to automatically create the required tables
 */

function initLoadingUnloadingTables($conn) {
    // Check if tables already exist
    $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
    $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");
    
    // Apply schema migrations (idempotent) when tables exist
    if (mysqli_num_rows($check_loading) > 0) {
        // ... (existing loading checklist migrations)
    }

    // Migration for vehicle_outgoing_checklist: Add inward_id if missing
    $check_outgoing = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_outgoing_checklist'");
    if (mysqli_num_rows($check_outgoing) > 0) {
        $check_inward_id_outgoing = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_outgoing_checklist LIKE 'inward_id'");
        if ($check_inward_id_outgoing && mysqli_num_rows($check_inward_id_outgoing) == 0) {
            mysqli_query(
                $conn,
                "ALTER TABLE `vehicle_outgoing_checklist` 
                 ADD COLUMN `inward_id` INT DEFAULT NULL AFTER `loading_checklist_id`,
                 ADD INDEX `idx_inward_id_out` (`inward_id`)"
            );
        }
    }

    if (mysqli_num_rows($check_unloading) > 0) {
        // Link checklist to a specific gate entry (supports multiple entries per day)
        $check_inward_id_unloading = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_unloading_checklist LIKE 'inward_id'");
        if ($check_inward_id_unloading && mysqli_num_rows($check_inward_id_unloading) == 0) {
            mysqli_query(
                $conn,
                "ALTER TABLE `vehicle_unloading_checklist` 
                 ADD COLUMN `inward_id` INT DEFAULT NULL AFTER `document_date`,
                 ADD INDEX `idx_inward_id` (`inward_id`)"
            );
        }

        $check_body_type = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_unloading_checklist LIKE 'body_type'");
        if ($check_body_type && mysqli_num_rows($check_body_type) == 0) {
            mysqli_query(
                $conn,
                "ALTER TABLE `vehicle_unloading_checklist` 
                 ADD COLUMN `body_type` ENUM('Half', 'Full', 'Container', '3/4th', 'Other') DEFAULT NULL 
                 AFTER `vehicle_type`"
            );
        }
    }

    // Do NOT early-return here: we also need vendor/customer masters and outgoing checklist table
    // which may not exist in older installations.
    
    // Create vendor_master table
    $sql = "CREATE TABLE IF NOT EXISTS `vendor_master` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `vendor_name` VARCHAR(200) NOT NULL,
      `gst_number` VARCHAR(15),
      `contact_person` VARCHAR(100),
      `mobile` VARCHAR(15),
      `email` VARCHAR(100),
      `address` TEXT,
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_vendor_name` (`vendor_name`),
      INDEX `idx_gst_number` (`gst_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);
    
    // Create customer_master table
    $sql = "CREATE TABLE IF NOT EXISTS `customer_master` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `customer_name` VARCHAR(200) NOT NULL,
      `contact_person` VARCHAR(100),
      `mobile` VARCHAR(15),
      `email` VARCHAR(100),
      `address` TEXT,
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_customer_name` (`customer_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);
    
    // Create vehicle_loading_checklist table
    $sql = "CREATE TABLE IF NOT EXISTS `vehicle_loading_checklist` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `document_id` VARCHAR(50) DEFAULT 'VCPL/LOG/FR/01',
      `document_date` DATE,
      `inward_id` INT DEFAULT NULL,
      `reporting_datetime` DATETIME NOT NULL,
      `vehicle_type_make` VARCHAR(100),
      `capacity` VARCHAR(50),
      `loading_location` VARCHAR(200),
      `body_type` ENUM('Half', 'Full', 'Container', '3/4th', 'Other') DEFAULT NULL,
      `transport_company_id` INT,
      `transport_company_name` VARCHAR(200),
      `vehicle_registration_number` VARCHAR(20) NOT NULL,
      `driver_name` VARCHAR(100),
      `license_number` VARCHAR(50),
      `documents_json` JSON,
      `platform_cleanliness_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `platform_cleanliness_action` TEXT,
      `platform_cleanliness_remarks` TEXT,
      `platform_gaps_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `platform_gaps_action` TEXT,
      `platform_gaps_remarks` TEXT,
      `cross_bars_removed_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `cross_bars_removed_action` TEXT,
      `cross_bars_removed_remarks` TEXT,
      `tarpaulins_available_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `tarpaulins_available_action` TEXT,
      `tarpaulins_available_remarks` TEXT,
      `driver_smartphone_status` ENUM('Yes', 'No') DEFAULT NULL,
      `driver_smartphone_action` TEXT,
      `driver_smartphone_remarks` TEXT,
      `reporting_time_plant` TIME,
      `reporting_date_plant` DATE,
      `gate_entry_time` TIME,
      `gate_entry_date` DATE,
      `other_remarks` TEXT,
      `other_remarks_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `other_remarks_action` TEXT,
      `security_signature` VARCHAR(100),
      `checked_by` INT,
      `checked_by_name` VARCHAR(100),
      `status` ENUM('draft', 'completed', 'cancelled') DEFAULT 'draft',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`transport_company_id`) REFERENCES `transporter_master`(`id`) ON DELETE SET NULL,
      FOREIGN KEY (`checked_by`) REFERENCES `user_master`(`id`) ON DELETE SET NULL,
      INDEX `idx_vehicle_registration` (`vehicle_registration_number`),
      INDEX `idx_reporting_datetime` (`reporting_datetime`),
      INDEX `idx_inward_id` (`inward_id`),
      INDEX `idx_status` (`status`),
      INDEX `idx_document_id` (`document_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);
    
    // Create vehicle_unloading_checklist table
    $sql = "CREATE TABLE IF NOT EXISTS `vehicle_unloading_checklist` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `document_id` VARCHAR(50) DEFAULT 'VCPL/STORE/FR/01',
      `document_date` DATE,
      `inward_id` INT DEFAULT NULL,
      `reporting_datetime` DATETIME NOT NULL,
      `vehicle_type` VARCHAR(100),
      `body_type` ENUM('Half', 'Full', 'Container', '3/4th', 'Other') DEFAULT NULL,
      `transport_company_id` INT,
      `transport_company_name` VARCHAR(200),
      `vehicle_registration_number` VARCHAR(20) NOT NULL,
      `rc_book_status` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `rc_book_details` TEXT,
      `permit_status` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `permit_details` TEXT,
      `insurance_status` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `insurance_details` TEXT,
      `puc_certificate_status` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `puc_certificate_details` TEXT,
      `fitness_certificate_status` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `fitness_certificate_details` TEXT,
      `driver_name` VARCHAR(100),
      `driver_mobile` VARCHAR(15),
      `driver_alcoholic_influence` ENUM('Yes', 'No') DEFAULT NULL,
      `license_type` VARCHAR(50),
      `license_valid_till` DATE,
      `vendor_id` INT,
      `vendor_name` VARCHAR(200),
      `purchase_order_no` VARCHAR(100),
      `challan_no` VARCHAR(100),
      `invoice_no` VARCHAR(100),
      `gst_number` VARCHAR(15),
      `safety_checks_json` JSON,
      `tanker_sealing_status_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `tanker_sealing_status_remarks` TEXT,
      `tanker_emergency_panel_obs` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `tanker_emergency_panel_remarks` TEXT,
      `tanker_fall_protection_obs` ENUM('Yes', 'No', 'NA') DEFAULT NULL,
      `tanker_fall_protection_remarks` TEXT,
      `gross_weight_invoice` DECIMAL(10,2),
      `tare_weight_invoice` DECIMAL(10,2),
      `net_weight_invoice` DECIMAL(10,2),
      `other_remarks` TEXT,
      `checked_by` INT,
      `checked_by_name` VARCHAR(100),
      `status` ENUM('draft', 'completed', 'cancelled') DEFAULT 'draft',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`transport_company_id`) REFERENCES `transporter_master`(`id`) ON DELETE SET NULL,
      FOREIGN KEY (`vendor_id`) REFERENCES `vendor_master`(`id`) ON DELETE SET NULL,
      FOREIGN KEY (`checked_by`) REFERENCES `user_master`(`id`) ON DELETE SET NULL,
      INDEX `idx_vehicle_registration` (`vehicle_registration_number`),
      INDEX `idx_reporting_datetime` (`reporting_datetime`),
      INDEX `idx_inward_id` (`inward_id`),
      INDEX `idx_status` (`status`),
      INDEX `idx_document_id` (`document_id`),
      INDEX `idx_challan_no` (`challan_no`),
      INDEX `idx_invoice_no` (`invoice_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);
    
    // Create vehicle_outgoing_checklist table
    $sql = "CREATE TABLE IF NOT EXISTS `vehicle_outgoing_checklist` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `loading_checklist_id` INT,
      `inward_id` INT DEFAULT NULL,
      `document_id` VARCHAR(50) DEFAULT 'VCPL/LOG/FR/02',
      `document_date` DATE,
      `reporting_datetime` DATETIME,
      `customer_id` INT,
      `customer_name` VARCHAR(200),
      `destination` VARCHAR(200),
      `tarpaulin_condition_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `tarpaulin_condition_action` TEXT,
      `tarpaulin_condition_remarks` TEXT,
      `wooden_blocks_used_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `wooden_blocks_used_action` TEXT,
      `wooden_blocks_used_remarks` TEXT,
      `rope_tightening_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `rope_tightening_action` TEXT,
      `rope_tightening_remarks` TEXT,
      `number_of_seals` INT DEFAULT 0,
      `sealing_method` VARCHAR(200),
      `sealing_done_by` VARCHAR(100),
      `sealing_obs` ENUM('OK', 'NOT OK', 'NA') DEFAULT NULL,
      `sealing_action` TEXT,
      `sealing_remarks` TEXT,
      `documents_json` JSON,
      `leaving_datetime` DATETIME,
      `naaviq_trip_started` ENUM('Yes', 'No') DEFAULT NULL,
      `naaviq_trip_action` TEXT,
      `naaviq_trip_remarks` TEXT,
      `driver_signature` VARCHAR(100),
      `transporter_signature` VARCHAR(100),
      `security_signature` VARCHAR(100),
      `logistic_signature` VARCHAR(100),
      `status` ENUM('draft', 'completed', 'cancelled') DEFAULT 'draft',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`loading_checklist_id`) REFERENCES `vehicle_loading_checklist`(`id`) ON DELETE SET NULL,
      FOREIGN KEY (`customer_id`) REFERENCES `customer_master`(`id`) ON DELETE SET NULL,
      INDEX `idx_loading_checklist_id` (`loading_checklist_id`),
      INDEX `idx_reporting_datetime` (`reporting_datetime`),
      INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);
    
    // Insert sample data
    $check_vendor = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM vendor_master");
    $vendor_count = 0;
    if ($check_vendor) {
        $vendor_row = mysqli_fetch_assoc($check_vendor);
        $vendor_count = $vendor_row['cnt'];
    }
    if ($vendor_count == 0) {
        mysqli_query($conn, "INSERT IGNORE INTO `vendor_master` (`vendor_name`, `gst_number`, `contact_person`, `mobile`) VALUES
            ('ABC Suppliers Pvt Ltd', '27AAACW0287ATZN', 'Rajesh Kumar', '9876543210'),
            ('XYZ Materials Ltd', '27AABCT1332L1ZJ', 'Suresh Sharma', '9876543211')");
    }
    
    $check_customer = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM customer_master");
    $customer_count = 0;
    if ($check_customer) {
        $customer_row = mysqli_fetch_assoc($check_customer);
        $customer_count = $customer_row['cnt'];
    }
    if ($customer_count == 0) {
        mysqli_query($conn, "INSERT IGNORE INTO `customer_master` (`customer_name`, `contact_person`, `mobile`) VALUES
            ('Customer A Industries', 'Amit Singh', '9876543220'),
            ('Customer B Enterprises', 'Vikram Patel', '9876543221')");
    }
    
    return true;
}
