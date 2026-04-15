<?php
/**
 * WordPress Synchronization Receiver (Webhook)
 * Handles incoming data from WordPress to sync back to the PHP project.
 */

// Load basic system and environment
require_once __DIR__ . '/lib/classes/Env.php';
Env::load(__DIR__ . '/.env');

// Security Check: Match the Sync Secret
$receivedSecret = $_SERVER['HTTP_X_WP_SYNC_SECRET'] ?? ($_POST['sync_secret'] ?? '');
$storedSecret = Env::get('WORDPRESS_SYNC_SECRET');

if (empty($storedSecret) || $receivedSecret !== $storedSecret) {
    http_response_code(403);
    die('Forbidden: Invalid Sync Secret');
}

// Load System Core (Database, Classes)
require_once __DIR__ . '/lib/system_load.php';

// Get JSON Data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action']) || !isset($data['email'])) {
    http_response_code(400);
    die('Bad Request: Missing data');
}

$action = $data['action'];
$email = $data['email'];
$wpUserId = intval($data['wp_user_id'] ?? 0);

// Prevent infinite loops: If the request identifies as coming from PHP sync, ignore it
if (isset($data['sync_source']) && $data['sync_source'] === 'php') {
    die('Ignored: Request originated from PHP project');
}

global $db;
$usersModel = new Users();

// Find local user by Mapping Table first, then by Email
$localUser = null;
if ($wpUserId > 0) {
    $query = "SELECT u.* FROM users u JOIN php_users_wp_map map ON u.user_id = map.php_user_id WHERE map.wp_user_id = $wpUserId LIMIT 1";
    $res = $db->query($query);
    $localUser = $res->fetch_assoc();
}

if (!$localUser) {
    $query = "SELECT * FROM users WHERE email='" . $db->real_escape_string($email) . "' LIMIT 1";
    $res = $db->query($query);
    $localUser = $res->fetch_assoc();

    // If found by email but not mapped, create the mapping now
    if ($localUser && $wpUserId > 0) {
        $phpId = $localUser['user_id'];
        $db->query("INSERT INTO php_users_wp_map (php_user_id, wp_user_id) VALUES ($phpId, $wpUserId) ON DUPLICATE KEY UPDATE wp_user_id = $wpUserId");
    }
}

switch ($action) {
    case 'user_updated':
        if ($localUser) {
            $userId = $localUser['user_id'];

            // Update profile data in local DB
            if (isset($data['first_name']))
                $usersModel->update_user_row($userId, 'first_name', $db->real_escape_string($data['first_name']));
            if (isset($data['last_name']))
                $usersModel->update_user_row($userId, 'last_name', $db->real_escape_string($data['last_name']));
            if (isset($data['email']))
                $usersModel->update_user_row($userId, 'email', $db->real_escape_string($data['email']));

            // Handle status sync
            if (isset($data['user_status'])) {
                $usersModel->update_user_row($userId, 'status', $db->real_escape_string($data['user_status']));
            }

            echo "User updated: $email";
        }
        break;

    case 'password_updated':
        if ($localUser && isset($data['new_password'])) {
            $userId = $localUser['user_id'];
            $newPass = $data['new_password'];

            $password_hash = get_option('password_hash');
            if ($password_hash == "argon2") {
                $options = ['cost' => 12];
                $hashedPass = password_hash($newPass, PASSWORD_DEFAULT, $options);
            } else {
                $hashedPass = md5($newPass);
            }

            $usersModel->update_user_row($userId, 'password', $hashedPass);
            echo "Password updated: $email";
        }
        break;

    case 'user_deleted':
        if ($localUser) {
            $usersModel->delete_user('admin', $localUser['user_id']);
            echo "User deleted: $email";
        }
        break;

    default:
        http_response_code(400);
        die('Invalid Action');
}
