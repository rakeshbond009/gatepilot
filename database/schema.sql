-- GatePilot Tenant Schema Template
-- Generated: 2026-03-28 14:58:06

SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================================
-- MASTER CONTROL: Tenants Table (Managed in Admin DB)
-- ==========================================================
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(200) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `db_host` varchar(255) DEFAULT 'localhost',
  `db_name` varchar(100) NOT NULL,
  `db_user` varchar(255) DEFAULT '',
  `db_pass` varchar(255) DEFAULT '',
  `admin_username` varchar(100) NOT NULL,
  `admin_password_hash` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gst_no` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================
-- TENANT TABLES: Individual Instance Schema
-- ==========================================================


CREATE TABLE IF NOT EXISTS `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_customer_name` (`customer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `department_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `driver_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_name` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `license_photo` varchar(255) DEFAULT NULL,
  `transporter_id` int(11) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transporter_id` (`transporter_id`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_driver_name` (`driver_name`),
  CONSTRAINT `driver_master_ibfk_1` FOREIGN KEY (`transporter_id`) REFERENCES `transporter_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(20) DEFAULT NULL,
  `inward_datetime` datetime NOT NULL,
  `outward_datetime` datetime DEFAULT NULL,
  `status` enum('inside','exited') DEFAULT 'inside',
  `inward_by` int(11) DEFAULT NULL,
  `outward_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_vehicle` (`vehicle_number`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `employee_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) DEFAULT NULL,
  `employee_name` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(20) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rc_expiry` date DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `pollution_expiry` date DEFAULT NULL,
  `fitness_expiry` date DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `idx_vehicle` (`vehicle_number`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `manual_registers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_type` varchar(50) NOT NULL,
  `entry_date` date NOT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `challan_no` varchar(50) DEFAULT NULL,
  `gate_pass_no` varchar(50) DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `party_name` varchar(255) DEFAULT NULL,
  `supp_code` varchar(50) DEFAULT NULL,
  `material_desc` text DEFAULT NULL,
  `material_code` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `pack_size` varchar(50) DEFAULT NULL,
  `transporter_name` varchar(255) DEFAULT NULL,
  `security_sign` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `dynamic_data` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `recheck_status` varchar(255) DEFAULT NULL,
  `return_date_time` datetime DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `handed_over_to` varchar(100) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `received_quantity` varchar(100) DEFAULT NULL,
  `out_time_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_register_type` (`register_type`),
  KEY `idx_entry_date` (`entry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `material_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_code` varchar(50) NOT NULL,
  `material_description` text DEFAULT NULL,
  `material_category` varchar(100) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `supp_code` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_material_code` (`material_code`)
) ENGINE=InnoDB AUTO_INCREMENT=525 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `patrol_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_description` text NOT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `reported_by` int(11) NOT NULL,
  `reported_at` datetime DEFAULT current_timestamp(),
  `status` enum('Open','Assigned','Resolved','Closed') DEFAULT 'Open',
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `resolution_remarks` text DEFAULT NULL,
  `closing_remarks` text DEFAULT NULL,
  `status_history` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `reported_by` (`reported_by`),
  KEY `idx_status` (`status`),
  KEY `idx_reported_at` (`reported_at`),
  CONSTRAINT `patrol_issues_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `patrol_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `patrol_issues_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `user_master` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patrol_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` varchar(50) NOT NULL,
  `location_name` varchar(200) NOT NULL,
  `area_site_building` varchar(200) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `location_id` (`location_id`),
  KEY `idx_location_id` (`location_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patrol_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `guard_id` int(11) NOT NULL,
  `guard_name` varchar(100) DEFAULT NULL,
  `scan_datetime` datetime NOT NULL,
  `session_id` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_scan_time` (`scan_datetime`),
  KEY `idx_location` (`location_id`),
  KEY `idx_guard` (`guard_id`),
  CONSTRAINT `patrol_logs_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `patrol_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patrol_logs_ibfk_2` FOREIGN KEY (`guard_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purpose_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purpose_name` varchar(100) NOT NULL,
  `purpose_type` enum('delivery','pickup','service','visit','return','other') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr_scan_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inward_id` int(11) DEFAULT NULL,
  `qr_raw_data` text NOT NULL,
  `qr_type` varchar(50) DEFAULT NULL,
  `parsed_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parsed_data`)),
  `scan_status` enum('success','failed','partial') DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `scanned_by` int(11) DEFAULT NULL,
  `scanned_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `scanned_by` (`scanned_by`),
  KEY `idx_inward_id` (`inward_id`),
  KEY `idx_scan_status` (`scan_status`),
  CONSTRAINT `qr_scan_logs_ibfk_1` FOREIGN KEY (`inward_id`) REFERENCES `truck_inward` (`id`) ON DELETE SET NULL,
  CONSTRAINT `qr_scan_logs_ibfk_2` FOREIGN KEY (`scanned_by`) REFERENCES `user_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `register_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(50) DEFAULT '',
  `color` varchar(50) DEFAULT '#4f46e5',
  `fields_json` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier` varchar(100) NOT NULL,
  `supp_code` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_supp_code` (`supp_code`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transporter_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transporter_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transporter_name` (`transporter_name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `truck_inward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_number` varchar(50) NOT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `driver_mobile` varchar(15) NOT NULL,
  `transporter_id` int(11) DEFAULT NULL,
  `transporter_name` varchar(200) DEFAULT NULL,
  `purpose_id` int(11) DEFAULT NULL,
  `purpose_name` varchar(100) DEFAULT NULL,
  `bill_number` varchar(100) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_json`)),
  `item_count` int(11) DEFAULT 0,
  `total_quantity` decimal(10,2) DEFAULT 0.00,
  `from_location` varchar(200) DEFAULT NULL,
  `to_location` varchar(200) DEFAULT NULL,
  `security_comments` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `vehicle_photo_url` varchar(255) DEFAULT NULL,
  `bill_photo_url` varchar(255) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `inward_date` date NOT NULL,
  `inward_time` time NOT NULL,
  `inward_datetime` datetime NOT NULL,
  `inward_by` int(11) NOT NULL,
  `inward_by_name` varchar(100) DEFAULT NULL,
  `is_exited` tinyint(1) DEFAULT 0,
  `status` enum('inside','exited','cancelled') DEFAULT 'inside',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_number` (`entry_number`),
  KEY `inward_by` (`inward_by`),
  KEY `transporter_id` (`transporter_id`),
  KEY `purpose_id` (`purpose_id`),
  KEY `idx_entry_number` (`entry_number`),
  KEY `idx_vehicle_number` (`vehicle_number`),
  KEY `idx_inward_date` (`inward_date`),
  KEY `idx_status` (`status`),
  KEY `idx_bill_number` (`bill_number`),
  FULLTEXT KEY `ft_search` (`vehicle_number`,`driver_name`,`bill_number`),
  CONSTRAINT `truck_inward_ibfk_1` FOREIGN KEY (`inward_by`) REFERENCES `user_master` (`id`),
  CONSTRAINT `truck_inward_ibfk_2` FOREIGN KEY (`transporter_id`) REFERENCES `transporter_master` (`id`) ON DELETE SET NULL,
  CONSTRAINT `truck_inward_ibfk_3` FOREIGN KEY (`purpose_id`) REFERENCES `purpose_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `truck_outward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inward_id` int(11) NOT NULL,
  `outward_date` date NOT NULL,
  `outward_time` time NOT NULL,
  `outward_datetime` datetime NOT NULL,
  `outward_by` int(11) NOT NULL,
  `outward_by_name` varchar(100) DEFAULT NULL,
  `outward_remarks` text DEFAULT NULL,
  `duration_hours` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `outward_by` (`outward_by`),
  KEY `idx_inward_id` (`inward_id`),
  KEY `idx_outward_date` (`outward_date`),
  CONSTRAINT `truck_outward_ibfk_1` FOREIGN KEY (`inward_id`) REFERENCES `truck_inward` (`id`) ON DELETE CASCADE,
  CONSTRAINT `truck_outward_ibfk_2` FOREIGN KEY (`outward_by`) REFERENCES `user_master` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','security','manager') NOT NULL DEFAULT 'security',
  `super_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `permissions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

;

CREATE TABLE IF NOT EXISTS `vehicle_drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `assigned_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vehicle_driver` (`vehicle_id`,`driver_id`),
  KEY `idx_vehicle_id` (`vehicle_id`),
  KEY `idx_driver_id` (`driver_id`),
  CONSTRAINT `vehicle_drivers_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_drivers_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver_master` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `vehicle_loading_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` varchar(50) DEFAULT 'VCPL/LOG/FR/01',
  `document_date` date DEFAULT curdate(),
  `inward_id` int(11) DEFAULT NULL,
  `reporting_datetime` datetime NOT NULL,
  `vehicle_type_make` varchar(100) DEFAULT NULL,
  `capacity` varchar(50) DEFAULT NULL,
  `loading_location` varchar(200) DEFAULT NULL,
  `body_type` enum('Half','Full','Container','3/4th','Other') DEFAULT NULL,
  `transport_company_id` int(11) DEFAULT NULL,
  `transport_company_name` varchar(200) DEFAULT NULL,
  `vehicle_registration_number` varchar(20) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `engine_number` varchar(50) DEFAULT NULL,
  `permit_number` varchar(50) DEFAULT NULL,
  `documents_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_json`)),
  `platform_cleanliness_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `platform_cleanliness_action` text DEFAULT NULL,
  `platform_cleanliness_remarks` text DEFAULT NULL,
  `platform_gaps_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `platform_gaps_action` text DEFAULT NULL,
  `platform_gaps_remarks` text DEFAULT NULL,
  `cross_bars_removed_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `cross_bars_removed_action` text DEFAULT NULL,
  `cross_bars_removed_remarks` text DEFAULT NULL,
  `tarpaulins_available_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `tarpaulins_available_action` text DEFAULT NULL,
  `tarpaulins_available_remarks` text DEFAULT NULL,
  `driver_smartphone_status` enum('Yes','No') DEFAULT NULL,
  `driver_smartphone_action` text DEFAULT NULL,
  `driver_smartphone_remarks` text DEFAULT NULL,
  `reporting_time_plant` time DEFAULT NULL,
  `reporting_date_plant` date DEFAULT NULL,
  `gate_entry_time` time DEFAULT NULL,
  `gate_entry_date` date DEFAULT NULL,
  `other_remarks` text DEFAULT NULL,
  `other_remarks_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `other_remarks_action` text DEFAULT NULL,
  `security_signature` varchar(100) DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `checked_by_name` varchar(100) DEFAULT NULL,
  `status` enum('draft','completed','cancelled') DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transport_company_id` (`transport_company_id`),
  KEY `checked_by` (`checked_by`),
  KEY `idx_vehicle_registration` (`vehicle_registration_number`),
  KEY `idx_reporting_datetime` (`reporting_datetime`),
  KEY `idx_status` (`status`),
  KEY `idx_document_id` (`document_id`),
  KEY `idx_inward_id` (`inward_id`),
  CONSTRAINT `vehicle_loading_checklist_ibfk_1` FOREIGN KEY (`transport_company_id`) REFERENCES `transporter_master` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_loading_checklist_ibfk_2` FOREIGN KEY (`checked_by`) REFERENCES `user_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vehicle_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_number` varchar(20) NOT NULL,
  `maker` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `registration_validity` date DEFAULT NULL,
  `fitness_validity` date DEFAULT NULL,
  `pollution_validity` date DEFAULT NULL,
  `insurance_validity` date DEFAULT NULL,
  `permit_validity` date DEFAULT NULL,
  `permit_number` varchar(50) DEFAULT NULL,
  `rc_owner_name` varchar(200) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `transporter_id` int(11) DEFAULT NULL,
  `vehicle_class` varchar(50) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `gross_weight` int(11) DEFAULT NULL,
  `fetch_source` enum('manual','vahan','digilocker') DEFAULT 'manual',
  `fetched_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rc_photo` varchar(255) DEFAULT NULL,
  `insurance_photo` varchar(255) DEFAULT NULL,
  `pollution_photo` varchar(255) DEFAULT NULL,
  `fitness_photo` varchar(255) DEFAULT NULL,
  `permit_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_number` (`vehicle_number`),
  KEY `idx_vehicle_number` (`vehicle_number`),
  KEY `driver_id` (`driver_id`),
  KEY `idx_transporter_id` (`transporter_id`),
  CONSTRAINT `vehicle_master_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vehicle_outgoing_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loading_checklist_id` int(11) DEFAULT NULL,
  `inward_id` int(11) DEFAULT NULL,
  `document_id` varchar(50) DEFAULT 'VCPL/LOG/FR/02',
  `document_date` date DEFAULT NULL,
  `reporting_datetime` datetime DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `destination` varchar(200) DEFAULT NULL,
  `tarpaulin_condition_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `tarpaulin_condition_action` text DEFAULT NULL,
  `tarpaulin_condition_remarks` text DEFAULT NULL,
  `wooden_blocks_used_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `wooden_blocks_used_action` text DEFAULT NULL,
  `wooden_blocks_used_remarks` text DEFAULT NULL,
  `rope_tightening_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `rope_tightening_action` text DEFAULT NULL,
  `rope_tightening_remarks` text DEFAULT NULL,
  `number_of_seals` int(11) DEFAULT 0,
  `sealing_method` varchar(200) DEFAULT NULL,
  `sealing_done_by` varchar(100) DEFAULT NULL,
  `sealing_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `sealing_action` text DEFAULT NULL,
  `sealing_remarks` text DEFAULT NULL,
  `documents_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_json`)),
  `leaving_datetime` datetime DEFAULT NULL,
  `naaviq_trip_started` enum('Yes','No') DEFAULT NULL,
  `naaviq_trip_action` text DEFAULT NULL,
  `naaviq_trip_remarks` text DEFAULT NULL,
  `driver_signature` varchar(100) DEFAULT NULL,
  `transporter_signature` varchar(100) DEFAULT NULL,
  `security_signature` varchar(100) DEFAULT NULL,
  `logistic_signature` varchar(100) DEFAULT NULL,
  `status` enum('draft','completed','cancelled') DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_loading_checklist_id` (`loading_checklist_id`),
  KEY `idx_reporting_datetime` (`reporting_datetime`),
  KEY `idx_status` (`status`),
  KEY `idx_inward_id_out` (`inward_id`),
  CONSTRAINT `vehicle_outgoing_checklist_ibfk_1` FOREIGN KEY (`loading_checklist_id`) REFERENCES `vehicle_loading_checklist` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_outgoing_checklist_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vehicle_unloading_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` varchar(50) DEFAULT 'VCPL/STORE/FR/01',
  `document_date` date DEFAULT curdate(),
  `inward_id` int(11) DEFAULT NULL,
  `reporting_datetime` datetime NOT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `body_type` enum('Half','Full','Container','3/4th','Other') DEFAULT NULL,
  `transport_company_id` int(11) DEFAULT NULL,
  `transport_company_name` varchar(200) DEFAULT NULL,
  `vehicle_registration_number` varchar(20) NOT NULL,
  `rc_book_status` enum('Yes','No','NA') DEFAULT NULL,
  `rc_book_details` text DEFAULT NULL,
  `permit_status` enum('Yes','No','NA') DEFAULT NULL,
  `permit_details` text DEFAULT NULL,
  `insurance_status` enum('Yes','No','NA') DEFAULT NULL,
  `insurance_details` text DEFAULT NULL,
  `puc_certificate_status` enum('Yes','No','NA') DEFAULT NULL,
  `puc_certificate_details` text DEFAULT NULL,
  `fitness_certificate_status` enum('Yes','No','NA') DEFAULT NULL,
  `fitness_certificate_details` text DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `driver_mobile` varchar(15) DEFAULT NULL,
  `driver_alcoholic_influence` enum('Yes','No') DEFAULT NULL,
  `license_type` varchar(50) DEFAULT NULL,
  `license_valid_till` date DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `purchase_order_no` varchar(100) DEFAULT NULL,
  `challan_no` varchar(100) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `safety_checks_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`safety_checks_json`)),
  `tanker_sealing_status_obs` enum('OK','NOT OK','NA') DEFAULT NULL,
  `tanker_sealing_status_remarks` text DEFAULT NULL,
  `tanker_emergency_panel_obs` enum('Yes','No','NA') DEFAULT NULL,
  `tanker_emergency_panel_remarks` text DEFAULT NULL,
  `tanker_fall_protection_obs` enum('Yes','No','NA') DEFAULT NULL,
  `tanker_fall_protection_remarks` text DEFAULT NULL,
  `gross_weight_invoice` decimal(10,2) DEFAULT NULL,
  `tare_weight_invoice` decimal(10,2) DEFAULT NULL,
  `net_weight_invoice` decimal(10,2) DEFAULT NULL,
  `other_remarks` text DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `checked_by_name` varchar(100) DEFAULT NULL,
  `status` enum('draft','completed','cancelled') DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transport_company_id` (`transport_company_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `checked_by` (`checked_by`),
  KEY `idx_vehicle_registration` (`vehicle_registration_number`),
  KEY `idx_reporting_datetime` (`reporting_datetime`),
  KEY `idx_status` (`status`),
  KEY `idx_document_id` (`document_id`),
  KEY `idx_challan_no` (`challan_no`),
  KEY `idx_invoice_no` (`invoice_no`),
  KEY `idx_inward_id` (`inward_id`),
  CONSTRAINT `vehicle_unloading_checklist_ibfk_1` FOREIGN KEY (`transport_company_id`) REFERENCES `transporter_master` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_unloading_checklist_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_master` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_unloading_checklist_ibfk_3` FOREIGN KEY (`checked_by`) REFERENCES `user_master` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(200) NOT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vendor_name` (`vendor_name`),
  KEY `idx_gst_number` (`gst_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
