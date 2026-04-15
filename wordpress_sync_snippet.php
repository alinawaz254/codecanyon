<?php
/**
 * WORDPRESS SYNC SNIPPET (Production Grade)
 * Add this code to your WordPress theme's functions.php file or a custom plugin.
 */

// --- CONFIGURATION ---
define('PHP_PROJECT_WEBHOOK_URL', 'http://your-php-site.com/wp_sync_receiver.php');
define('PHP_PROJECT_SYNC_SECRET', 'WPSyncSecret2026!'); // Must match .env
// ----------------------

/**
 * Register 'user_status' meta for REST API access
 */
add_action('rest_api_init', function () {
    register_meta('user', 'user_status', [
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
    ]);
});

/**
 * 🔥 FORCE ROLE ON USER CREATE (GLOBAL FIX)
 * This ensures EVERY new user becomes "customer"
 */
add_action('user_register', function ($user_id) {
    $user = new \WP_User($user_id);
    $user->set_role('customer');
}, 100);

/**
 * FORCE CUSTOMER ROLE FOR REST API SYNC
 * This hook runs after a user is created or updated via the REST API.
 */
add_action('rest_insert_user', function ($user, $request, $creating) {

    // Debug (optional - remove in production)
    error_log('User created via REST. Forcing role to customer');

    $user->set_role('customer');

}, 100, 3);

/**
 * Block deactivated, banned, or suspended users from logging into WordPress
 */
add_filter('authenticate', function ($user, $username, $password) {
    if ($user instanceof \WP_User) {
        $status = get_user_meta($user->ID, 'user_status', true);
        
        if ($status === 'deactivate') {
            return new \WP_Error('account_inactive', __('Your account is not activated yet please confirm your Email address to activate your account!'));
        } elseif ($status === 'ban' || $status === 'suspend') {
            return new \WP_Error('account_blocked', __('You cannot login your account is ban or suspend. Contact site admin.'));
        }
    }
    return $user;
}, 30, 3);

/**
 * Helper to send sync requests to PHP project
 */
function sync_to_php_project($data)
{
    if (defined('SYNCING_FROM_PHP') && SYNCING_FROM_PHP === true)
        return;

    if (isset($_GET['sync_source']) && $_GET['sync_source'] === 'php')
        return;

    $data['sync_secret'] = PHP_PROJECT_SYNC_SECRET;

    wp_remote_post(PHP_PROJECT_WEBHOOK_URL, [
        'method' => 'POST',
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WP-SYNC-SECRET' => PHP_PROJECT_SYNC_SECRET
        ],
        'body' => json_encode($data),
    ]);
}


/**
 * Hook into User Profile Updates
 */
add_action('profile_update', function ($user_id, $old_user_data) {
    $user = get_userdata($user_id);

    $data = [
        'action' => 'user_updated',
        'email' => $user->user_email,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'wp_user_id' => $user_id,
        'user_status' => get_user_meta($user_id, 'user_status', true)
    ];

    sync_to_php_project($data);
}, 20, 2);

/**
 * Hook into Password Resets (Plain text not easily available here, 
 * but we can sync the event if needed. Real-time plain text sync 
 * usually requires hooking into the update form itself).
 */
add_action('after_password_reset', function ($user) {
    // Note: Plain password not available after reset. 
    // This hook is mainly to notify the other system.
}, 10, 1);

/**
 * Hook into User Deletion
 */
add_action('delete_user', function ($user_id) {
    $user = get_userdata($user_id);
    if (!$user)
        return;

    $data = [
        'action' => 'user_deleted',
        'email' => $user->user_email,
        'wp_user_id' => $user_id
    ];

    sync_to_php_project($data);
});

/**
 * Detect REST API updates from PHP to avoid loops
 */
add_action('rest_api_init', function () {
    if (isset($_GET['sync_source']) && $_GET['sync_source'] === 'php') {
        if (!defined('SYNCING_FROM_PHP')) {
            define('SYNCING_FROM_PHP', true);
        }
    }
});
