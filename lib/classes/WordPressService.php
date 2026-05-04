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
    public function syncUser($phpUserId, $email, $password = null, $username = null, $firstName = '', $lastName = '', $description = '', $status = 'activate')
    {
        // Prepare user data
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
        
        // Check if we have a mapping
        $mappedWpId = $this->getMappedWpId($phpUserId);
        
        // Check if user exists in WordPress by email
        $wpUserByEmail = $this->findWpUserId($email);
        
        // SCENARIO 1: User exists in WordPress (either by mapping or email lookup)
        $wpUserId = null;
        
        if ($mappedWpId) {
            // Verify the mapped user actually exists in WordPress
            $verify = $this->request('GET', "/users/{$mappedWpId}");
            if ($verify && isset($verify['id'])) {
                $wpUserId = $mappedWpId;
            } else {
                // Stale mapping - delete it
                $this->deleteMapping($phpUserId);
            }
        }
        
        if (!$wpUserId && $wpUserByEmail) {
            // User exists by email but wasn't mapped
            $wpUserId = $wpUserByEmail;
            // Save the mapping for next time
            $this->saveMapping($phpUserId, $wpUserId);
        }
        
        // SCENARIO 2: User exists in WordPress - UPDATE them
        if ($wpUserId) {
            $response = $this->request('POST', "/users/{$wpUserId}", $data);
            if ($response && isset($response['id'])) {
                return $response['id'];
            }
            return false;
        }
        
        // SCENARIO 3: User does NOT exist in WordPress - CREATE them
        if (!$username) {
            $username = $email;
        }
        $data['username'] = $username;
        
        $response = $this->request('POST', "/users", $data);
        
        if ($response && isset($response['id'])) {
            // Save the mapping for future syncs
            $this->saveMapping($phpUserId, $response['id']);
            return $response['id'];
        }
        
        // Log the failure
        $this->logSyncAttempt('POST', '/users', $data, 0, json_encode($response), 'User creation failed');
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
