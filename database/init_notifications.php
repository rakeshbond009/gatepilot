<?php
function initNotificationsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `type` VARCHAR(50) NOT NULL, -- 'inward', 'outward', 'loading', 'unloading', 'mismatch', etc.
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT,
        `link` VARCHAR(255),
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_is_read (`is_read`),
        INDEX idx_created (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    return mysqli_query($conn, $sql);
}
?>
