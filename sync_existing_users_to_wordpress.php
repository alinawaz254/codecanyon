<?php
/**
 * Project: One-Time User Migration (PHP → WordPress via REST API)
 * Objective: Migrate all existing users to WordPress.
 */

// Set time limit for long execution
set_time_limit(0); 

require_once 'lib/system_load.php';

// Ensure we have access to the service
if (!class_exists('WordPressService')) {
    require_once 'lib/classes/WordPressService.php';
}

$wpService = new WordPressService();
global $db;

$batchSize = 50;
$temporaryPassword = "12345678";
$logFile = ROOT_DIR . '/logs/migration_sync_' . date('Y-m-d') . '.log';

// Ensure log directory exists
if (!is_dir(ROOT_DIR . '/logs')) {
    mkdir(ROOT_DIR . '/logs', 0755, true);
}

function logMigration($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

logMigration("Starting User Migration Batch Processing...");

// Fetch batch of unsynced users
$query = "SELECT * FROM users WHERE wp_synced = 0 LIMIT $batchSize";
$result = $db->query($query);

if (!$result || $result->num_rows == 0) {
    logMigration("No more users to sync or query failed.");
    exit("Migration complete or no users found.\n");
}

$successCount = 0;
$failureCount = 0;

while ($user = $result->fetch_assoc()) {
    $phpUserId = $user['user_id'];
    $email = $user['email'];
    $username = $user['username'];
    $firstName = $user['first_name'] ?? '';
    $lastName = $user['last_name'] ?? '';
    $description = $user['description'] ?? '';
    $status = $user['status'] ?? 'activate';

    logMigration("Processing User ID: $phpUserId | Email: $email");

    try {
        // syncUser handles:
        // 1. Checking if already mapped in php_users_wp_map
        // 2. Checking if email exists in WP (if not mapped)
        // 3. Creating if not exists
        // 4. Updating if exists
        $wpUserId = $wpService->syncUser(
            $phpUserId, 
            $email, 
            $temporaryPassword, 
            $username, 
            $firstName, 
            $lastName, 
            $description, 
            $status
        );

        if ($wpUserId) {
            // Mark as synced locally
            $update = $db->query("UPDATE users SET wp_synced = 1 WHERE user_id = $phpUserId");
            if ($update) {
                logMigration("SUCCESS: User $email synced to WP (ID: $wpUserId)");
                $successCount++;
            } else {
                logMigration("WARNING: User $email synced to WP but failed to update local wp_synced flag: " . $db->error);
            }
        } else {
            logMigration("FAILURE: Could not sync user $email to WordPress. Check wordpress_sync.log for details.");
            $failureCount++;
        }

    } catch (Exception $e) {
        logMigration("ERROR: Exception while processing $email: " . $e->getMessage());
        $failureCount++;
    }
}

logMigration("Batch Complete. Successes: $successCount | Failures: $failureCount");
logMigration("------------------------------------------------------------");

if ($successCount > 0 && $successCount == $batchSize) {
    echo "\nBatch full. You may want to run the script again to process the next set.\n";
}
