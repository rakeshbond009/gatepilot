-- Truck Movement - Database Patch for Hostinger
-- Run this SQL in phpMyAdmin to ensure your live database matches the laptop version.

-- 1. Ensure Employee Master has all required fields
ALTER TABLE `employee_master` 
ADD COLUMN IF NOT EXISTS `email` varchar(100) DEFAULT NULL AFTER `vehicle_type`,
ADD COLUMN IF NOT EXISTS `vehicle_type` varchar(50) DEFAULT NULL AFTER `fitness_expiry`,
ADD COLUMN IF NOT EXISTS `rc_expiry` date DEFAULT NULL AFTER `updated_at`,
ADD COLUMN IF NOT EXISTS `license_expiry` date DEFAULT NULL AFTER `rc_expiry`,
ADD COLUMN IF NOT EXISTS `pollution_expiry` date DEFAULT NULL AFTER `license_expiry`,
ADD COLUMN IF NOT EXISTS `fitness_expiry` date DEFAULT NULL AFTER `pollution_expiry`;

-- 2. Ensure Driver Master has all fields
ALTER TABLE `driver_master`
ADD COLUMN IF NOT EXISTS `photo_url` varchar(255) DEFAULT NULL AFTER `transporter_id`;

-- 3. Ensure Vehicle Master has all fields
ALTER TABLE `vehicle_master`
ADD COLUMN IF NOT EXISTS `rc_photo` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `insurance_photo` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `pollution_photo` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `fitness_photo` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `permit_photo` varchar(255) DEFAULT NULL;

-- 4. Audit Logs Table (If not already present)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Clear any bad sessions (Optional)
-- TRUNCATE TABLE `patrol_logs`; -- Only run if needed
