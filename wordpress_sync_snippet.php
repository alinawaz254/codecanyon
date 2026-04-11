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
 * Register 'is_suspended' meta for REST API access
 */
add_action('rest_api_init', function () {
    register_meta('user', 'is_suspended', [
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
    ]);
});

/**
 * FORCE CUSTOMER ROLE FOR REST API SYNC
 * This hook runs after a user is created or updated via the REST API.
 */
add_action('rest_insert_user', function ($user, $request, $creating) {
    // Check if the request is coming from our PHP sync
    if ($request->get_param('sync_source') === 'php') {
        // Force role to customer
        $user->set_role('customer');

        // Double check WooCommerce role specifically if needed
        if (class_exists('WooCommerce')) {
            $user->set_role('customer');
        }
    }
}, 10, 3);

/**
 * Block suspended users from logging into WordPress
 */
add_filter('authenticate', function ($user, $username, $password) {
    if ($user instanceof WP_User) {
        $is_suspended = get_user_meta($user->ID, 'is_suspended', true);
        if ($is_suspended) {
            return new WP_Error('user_suspended', __('This account has been suspended by the administrator.'));
        }
    }
    return $user;
}, 30, 3);

/**
 * Helper to send sync requests to PHP project
 */
function sync_to_php_project($data)
{
    // Prevent infinite loops if the request originated from PHP
    if (defined('SYNCING_FROM_PHP') && SYNCING_FROM_PHP === true)
        return;

    // Also check for the query parameter in the current request
    if (isset($_GET['sync_source']) && $_GET['sync_source'] === 'php')
        return;

    $data['sync_secret'] = PHP_PROJECT_SYNC_SECRET;

    wp_remote_post(PHP_PROJECT_WEBHOOK_URL, [
        'method' => 'POST',
        'timeout' => 15,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WP-SYNC-SECRET' => PHP_PROJECT_SYNC_SECRET
        ],
        'body' => json_encode($data),
        'cookies' => []
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
        'is_suspended' => get_user_meta($user_id, 'is_suspended', true)
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
