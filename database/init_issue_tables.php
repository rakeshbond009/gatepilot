<?php
/**
 * Initialize Issue/Ticket Tables
 */

function initIssueTables($conn)
{
    // Patrol Issues / Tickets Table
    $sql = "CREATE TABLE IF NOT EXISTS `patrol_issues` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `issue_description` TEXT NOT NULL,
      `photo_url` VARCHAR(255),
      `location_id` INT,
      `reported_by` INT NOT NULL,
      `reported_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `status` ENUM('Open', 'Assigned', 'Resolved', 'Closed') DEFAULT 'Open',
      `assigned_to` INT DEFAULT NULL,
      `assigned_at` DATETIME DEFAULT NULL,
      `resolution_remarks` TEXT,
      `resolved_at` DATETIME DEFAULT NULL,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`location_id`) REFERENCES `patrol_locations`(`id`) ON DELETE SET NULL,
      FOREIGN KEY (`reported_by`) REFERENCES `user_master`(`id`) ON DELETE CASCADE,
      INDEX `idx_status` (`status`),
      INDEX `idx_reported_at` (`reported_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!mysqli_query($conn, $sql)) {
        error_log("Error creating patrol_issues table: " . mysqli_error($conn));
    }

    return true;
}
?>