<?php
require_once 'lib/system_load.php';

global $db;

// 1. Add wp_synced column if it doesn't exist
$check_column = $db->query("SHOW COLUMNS FROM users LIKE 'wp_synced'");
if ($check_column->num_rows == 0) {
    echo "Adding wp_synced column to users table...\n";
    $db->query("ALTER TABLE users ADD wp_synced TINYINT(1) DEFAULT 0");
    if ($db->error) {
        die("Error adding column: " . $db->error);
    }
    echo "Column added successfully.\n";
} else {
    echo "wp_synced column already exists.\n";
}

// 2. Check if mapping table exists
$check_table = $db->query("SHOW TABLES LIKE 'php_users_wp_map'");
if ($check_table->num_rows == 0) {
    echo "Creating php_users_wp_map table...\n";
    $db->query("CREATE TABLE php_users_wp_map (
        id INT AUTO_INCREMENT PRIMARY KEY,
        php_user_id INT NOT NULL,
        wp_user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (php_user_id),
        UNIQUE KEY (wp_user_id)
    )");
    if ($db->error) {
        die("Error creating table: " . $db->error);
    }
    echo "Table created successfully.\n";
} else {
    echo "php_users_wp_map table already exists.\n";
}

echo "Database preparation complete.\n";
