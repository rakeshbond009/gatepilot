<?php
function initAuditLogsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `audit_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT DEFAULT NULL,
        `username` VARCHAR(100) DEFAULT NULL,
        `activity_type` VARCHAR(50) NOT NULL,
        `module` VARCHAR(50) NOT NULL,
        `details` TEXT,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    return mysqli_query($conn, $sql);
}
?>
