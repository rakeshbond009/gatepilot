<?php
/**
 * Initialize Guard Patrol Tables
 */

function initPatrolTables($conn) {
    // 1. Patrol Locations Table
    $sql = "CREATE TABLE IF NOT EXISTS `patrol_locations` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `location_id` VARCHAR(50) UNIQUE NOT NULL,
      `location_name` VARCHAR(200) NOT NULL,
      `area_site_building` VARCHAR(200),
      `qr_code_data` TEXT,
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_location_id` (`location_id`),
      INDEX `idx_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);

    // 2. Patrol Logs Table
    $sql = "CREATE TABLE IF NOT EXISTS `patrol_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `location_id` INT NOT NULL,
      `guard_id` INT NOT NULL,
      `guard_name` VARCHAR(100),
      `scan_datetime` DATETIME NOT NULL,
      `session_id` VARCHAR(50),
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`location_id`) REFERENCES `patrol_locations`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`guard_id`) REFERENCES `user_master`(`id`) ON DELETE CASCADE,
      INDEX `idx_scan_time` (`scan_datetime`),
      INDEX `idx_location` (`location_id`),
      INDEX `idx_guard` (`guard_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);

    return true;
}
?>
