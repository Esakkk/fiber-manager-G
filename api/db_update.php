<?php
require_once 'config.php';

try {
    // 1. Update odp_ports table
    $sql1 = "ALTER TABLE `odp_ports` 
             ADD COLUMN `lat` DECIMAL(10,8) NULL DEFAULT NULL AFTER `target_port`,
             ADD COLUMN `lng` DECIMAL(11,8) NULL DEFAULT NULL AFTER `lat`,
             ADD COLUMN `onu_number` VARCHAR(50) NULL DEFAULT NULL AFTER `lng`,
             ADD COLUMN `modem_type` VARCHAR(100) NULL DEFAULT NULL AFTER `onu_number`,
             ADD COLUMN `description` TEXT NULL DEFAULT NULL AFTER `modem_type`,
             ADD COLUMN `has_photo` TINYINT(1) DEFAULT 0 AFTER `description`";
             
    try {
        $pdo->exec($sql1);
        echo "odp_ports updated successfully.\n";
    } catch(PDOException $e) {
        echo "Error updating odp_ports (maybe columns already exist?): " . $e->getMessage() . "\n";
    }

    // Add path_coordinates specifically
    try {
        $pdo->exec("ALTER TABLE `odp_ports` ADD COLUMN `path_coordinates` TEXT NULL DEFAULT NULL AFTER `has_photo`");
        echo "odp_ports.path_coordinates added successfully.\n";
    } catch(PDOException $e) {
        echo "Error adding path_coordinates: " . $e->getMessage() . "\n";
    }

    // 2. Create port_photos table
    $sql2 = "CREATE TABLE IF NOT EXISTS `port_photos` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `port_id` int(11) NOT NULL,
              `filename` varchar(255) NOT NULL,
              `original_name` varchar(255) NOT NULL,
              `file_size` int(11) NOT NULL,
              `is_primary` tinyint(1) DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              FOREIGN KEY (`port_id`) REFERENCES `odp_ports` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            
    $pdo->exec($sql2);
    echo "port_photos table created successfully.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
