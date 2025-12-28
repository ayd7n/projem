<?php
include 'config.php';

// Add para_birimi column to gelir_yonetimi table
$alter_sql = "ALTER TABLE `gelir_yonetimi` ADD COLUMN `para_birimi` VARCHAR(3) NOT NULL DEFAULT 'TL' AFTER `tutar`";

if ($connection->query($alter_sql)) {
    echo "Table 'gelir_yonetimi' altered successfully - para_birimi column added.\n";
} else {
    echo "Error altering table 'gelir_yonetimi': " . $connection->error . "\n";
}

// Create index for better performance
$index_sql = "ALTER TABLE `gelir_yonetimi` ADD INDEX `idx_para_birimi` (`para_birimi`)";

if ($connection->query($index_sql)) {
    echo "Index 'idx_para_birimi' created successfully.\n";
} else {
    echo "Error creating index: " . $connection->error . "\n";
}

echo "Database updates completed.";
?>
