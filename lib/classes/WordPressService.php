<?php

require_once __DIR__ . '/Env.php';

class WordPressService
{
    private $baseUrl;
    private $apiUrl;
    private $username;
    private $password;
    private $defaultRole;
    private $logFile;

    public function __construct()
    {
        Env::load(dirname(__DIR__, 2) . '/.env');
        $this->baseUrl = Env::get('WORDPRESS_BASE_URL');
        $this->apiUrl = Env::get('WORDPRESS_API_URL');
        $this->username = Env::get('WORDPRESS_ADMIN_USERNAME');
        $this->password = Env::get('WORDPRESS_ADMIN_PASSWORD');
        $this->defaultRole = Env::get('WORDPRESS_DEFAULT_ROLE', 'customer');

        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/wordpress_sync.log';
    }

    /**
     * Get WordPress User ID from Local Mapping
     */
    public function getMappedWpId($phpUserId)
    {
        global $db;
        $id = intval($phpUserId);
        $query = "SELECT wp_user_id FROM php_users_wp_map WHERE php_user_id = $id LIMIT 1";
        $res = $db->query($query);
        if ($res && $row = $res->fetch_assoc()) {
            return intval($row['wp_user_id']);
        }
        return null;
    }

    /**
     * Save Mapping to Local DB
     */
    public function saveMapping($phpUserId, $wpUserId)
    {
        global $db;
        $php = intval($phpUserId);
        $wp = intval($wpUserId);
        $query = "INSERT INTO php_users_wp_map (php_user_id, wp_user_id) 
                  VALUES ($php, $wp) 
                  ON DUPLICATE KEY UPDATE wp_user_id = $wp";
        return $db->query($query);
    }

    /**
     * Remove Mapping from Local DB
     */
    public function removeMappingByPhpId($phpUserId)
    {
        global $db;
        $id = intval($phpUserId);
        $query = "DELETE FROM php_users_wp_map WHERE php_user_id = $id";
        return $db->query($query);
    }
    public function deleteMapping($phpUserId)
    {
        global $db;

        $db->query("
            DELETE FROM php_users_wp_map
            WHERE php_user_id = $phpUserId
        ");
    }
    /**
     * Sync User to WordPress (Create or Update)
     */
    /**
     * Sync User to WordPress (Create or Update)
     * Handles: 
     * 1. Create on both sides if user exists nowhere.
     * 2. Create on missing side if user exists on only one side.
     * 3. Update both sides if details differ.
     */
    public function syncUser($phpUserId = null, $email = null, $password = null, $username = null, $firstName = '', $lastName = '', $description = '', $status = 'activate')
    {
        global $db;
        
        // 1. Identify Local User
        if (!$phpUserId && $email) {
            $email_esc = $db->real_escape_string($email);
            $res = $db->query("SELECT user_id FROM users WHERE email = '$email_esc' LIMIT 1");
            if ($res && $row = $res->fetch_assoc()) {
                $phpUserId = intval($row['user_id']);
            }
        }

        // If we still don't have an email but have a PHP ID, fetch details
        if ($phpUserId && !$email) {
            $res = $db->query("SELECT email, username, first_name, last_name, status FROM users WHERE user_id = " . intval($phpUserId));
            if ($res && $row = $res->fetch_assoc()) {
                $email = $row['email'];
                $username = $username ?: $row['username'];
                $firstName = $firstName ?: $row['first_name'];
                $lastName = $lastName ?: $row['last_name'];
                $status = $status ?: $row['status'];
            }
        }

        if (!$email) return false;

        // 2. Identify WordPress User
        $wpUserId = null;
        if ($phpUserId) {
            $wpUserId = $this->getMappedWpId($phpUserId);
        }
        
        if (!$wpUserId) {
            $wpUserId = $this->findWpUserId($email);
        }

        // 3. Create Locally if missing
        if (!$phpUserId) {
            $u_name = $username ?: $email;
            $f_name = $firstName ?: '';
            $l_name = $lastName ?: '';
            $u_status = $status ?: 'activate';
            $reg_date = date('Y-m-d');
            $u_type = get_option('register_user_level') ?: 'subscriber';
            
            // If we found it on WP, try to pull more details
            if ($wpUserId) {
                $wpUser = $this->request('GET', "/users/{$wpUserId}?context=edit");
                if ($wpUser) {
                    $u_name = $wpUser['username'] ?: $u_name;
                    $f_name = $wpUser['first_name'] ?: $f_name;
                    $l_name = $wpUser['last_name'] ?: $l_name;
                }
            }

            // Password handling
            $plain_pass = $password ?: bin2hex(random_bytes(8));
            $password_hash_type = get_option('password_hash');
            if ($password_hash_type == "argon2") {
                $hashed_pass = password_hash($plain_pass, PASSWORD_DEFAULT, ['cost' => 12]);
            } else {
                $hashed_pass = md5($plain_pass);
            }
            
            $u_name_esc = $db->real_escape_string($u_name);
            $email_esc = $db->real_escape_string($email);
            $f_name_esc = $db->real_escape_string($f_name);
            $l_name_esc = $db->real_escape_string($l_name);
            
            $insert_query = "INSERT INTO users (first_name, last_name, username, email, password, date_register, user_type, status) 
                            VALUES ('$f_name_esc', '$l_name_esc', '$u_name_esc', '$email_esc', '$hashed_pass', '$reg_date', '$u_type', '$u_status')";
            $db->query($insert_query);
            $phpUserId = $db->insert_id;
        }

        if (!$phpUserId) return false;

        // 4. Prepare WordPress Data
        $data = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'nickname' => $username ?: $email,
            'description' => $description,
            'roles' => [$this->defaultRole],
            'meta' => [
                'user_status' => $status
            ]
        ];
        
        if ($password) {
            $data['password'] = $password;
        }

        // 5. Create or Update WordPress
        if ($wpUserId) {
            // Check if user still exists on WP (REST API might return 404 if deleted manually)
            $verify = $this->request('GET', "/users/{$wpUserId}");
            if ($verify && isset($verify['id'])) {
                // Update Existing
                $response = $this->request('PUT', "/users/{$wpUserId}", $data);
            } else {
                // Re-create if missing on WP side but mapped
                $data['username'] = $username ?: $email;
                $response = $this->request('POST', "/users", $data);
            }
        } else {
            // Create New on WP side
            $data['username'] = $username ?: $email;
            $response = $this->request('POST', "/users", $data);
        }

        // 6. Finalize Sync & Mapping
        if ($response && isset($response['id'])) {
            $wpUserId = $response['id'];
            $this->saveMapping($phpUserId, $wpUserId);
            
            // Mark as synced locally
            $db->query("UPDATE users SET wp_synced = 1 WHERE user_id = $phpUserId");
            
            return $wpUserId;
        }

        return false;
    }
    /**
     * Delete a user in WordPress
     */
    public function deleteUser($phpUserId)
    {
        $wpUserId = $this->getMappedWpId($phpUserId);
        if ($wpUserId) {
            $this->removeMappingByPhpId($phpUserId);
            return $this->request('DELETE', "/users/{$wpUserId}?force=true&reassign=1");
        }
        return false;
    }

    /**
     * Update user status in WordPress via Meta
     */
    public function updateStatus($phpUserId, $status)
    {
        $wpUserId = $this->getMappedWpId($phpUserId);
        if ($wpUserId) {
            $data = [
                'meta' => [
                    'user_status' => $status
                ]
            ];
            return $this->request('POST', "/users/{$wpUserId}", $data);
        }
        return false;
    }

    /**
     * Find WordPress User ID by Email
     */
    public function findWpUserId($email)
    {
        $users = $this->request('GET', '/users?context=edit&search=' . urlencode($email));
        if (is_array($users) && !empty($users)) {
            foreach ($users as $u) {
                if ($u['email'] === $email) {
                    return intval($u['id']);
                }
            }
        }
        return null;
    }

    /**
     * Helper to make API requests to configured WP REST API
     */
    private function request($method, $endpoint, $data = null)
    {
        $separator = (strpos($endpoint, '?') === false) ? '?' : '&';
        $url = rtrim($this->apiUrl, '/') . $endpoint . $separator . 'sync_source=php';
        return $this->directRequest($method, $url, $data, true);
    }

    /**
     * Direct cURL request
     */
    private function directRequest($method, $url, $data = null, $useAuth = true)
    {
        $ch = curl_init($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($useAuth) {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->logSyncAttempt($method, $url, $data, $httpCode, $response, $error);

        if ($error)
            return false;

        return json_decode($response, true);
    }

    private function logSyncAttempt($method, $url, $data, $code, $response, $error)
    {
        $timestamp = date('Y-m-d H:i:s');
        if (isset($data['password']))
            $data['password'] = '********';
        $logEntry = "[$timestamp] $method $url | Status: $code\n";
        $logEntry .= "Request Data: " . json_encode($data) . "\n";
        if ($error)
            $logEntry .= "cURL Error: $error\n";
        $logEntry .= "Response: $response\n";
        $logEntry .= str_repeat('-', 50) . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}
