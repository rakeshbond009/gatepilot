<?php
/**
 * Initialize Centralized App Issues Table
 */

function initAppIssuesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `app_issues` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `app_name` VARCHAR(100) NOT NULL,
      `client_name` VARCHAR(100),
      `client_contact` VARCHAR(100),
      `reported_by` VARCHAR(50),
      `issue_type` ENUM('Bug', 'Feature Request', 'UI Issue', 'Access Issue', 'Other') DEFAULT 'Bug',
      `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
      `description` TEXT NOT NULL,
      `photo_url` VARCHAR(255),
      `status` ENUM('Pending', 'In Progress', 'Resolved', 'Closed', 'Invalid') DEFAULT 'Pending',
      `admin_remarks` TEXT,
      `status_history` TEXT,
      `reported_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_app` (`app_name`),
      INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!mysqli_query($conn, $sql)) {
        error_log("Error creating app_issues table: " . mysqli_error($conn));
        return false;
    }
    return true;
}
?>
