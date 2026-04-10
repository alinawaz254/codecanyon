<?php

require_once __DIR__ . '/Env.php';

class WordPressService {
    private $baseUrl;
    private $apiUrl;
    private $username;
    private $password;
    private $logFile;

    public function __construct() {
        Env::load(dirname(__DIR__, 2) . '/.env');
        $this->baseUrl  = Env::get('WORDPRESS_BASE_URL');
        $this->apiUrl   = Env::get('WORDPRESS_API_URL');
        $this->username = Env::get('WORDPRESS_ADMIN_USERNAME');
        $this->password = Env::get('WORDPRESS_ADMIN_PASSWORD');
        
        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/wordpress_sync.log';
    }

    /**
     * Create a user in WordPress
     */
    public function createUser($email, $password, $username) {
        $data = [
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => 'subscriber' // Default role
        ];

        return $this->request('POST', '/users', $data);
    }

    /**
     * Authenticate user with WordPress
     */
    public function loginUser($username, $password) {
        // WordPress REST API doesn't have a native "login" endpoint for subscribers without plugins like JWT
        // However, we can attempt to fetch a token or simply validate via a test request.
        // Assuming JWT plugin is used as per user's prompt mention.
        $tokenUrl = Env::get('WORDPRESS_BASE_URL') . '/wp-json/jwt-auth/v1/token';
        
        $data = [
            'username' => $username,
            'password' => $password
        ];

        return $this->directRequest('POST', $tokenUrl, $data);
    }

    /**
     * Update user password in WordPress
     */
    public function updatePassword($email, $newPassword) {
        // First find user ID by email
        // We add context=edit to ensure 'email' field is returned in the response
        $users = $this->request('GET', '/users?context=edit&search=' . urlencode($email));
        
        if (is_array($users) && !empty($users)) {
            $wpUserId = 0;
            foreach ($users as $u) {
                if ($u['email'] === $email) {
                    $wpUserId = $u['id'];
                    break;
                }
            }

            if ($wpUserId) {
                $data = [
                    'password' => $newPassword
                ];
                return $this->request('POST', "/users/{$wpUserId}", $data);
            }
        }

        $this->logSyncError("Could not find WordPress user for email: $email");
        return false;
    }

    /**
     * Helper to make API requests to configured WP REST API
     */
    private function request($method, $endpoint, $data = null) {
        $url = rtrim($this->apiUrl, '/') . $endpoint;
        return $this->directRequest($method, $url, $data, true);
    }

    /**
     * Direct cURL request
     */
    private function directRequest($method, $url, $data = null, $useAuth = true) {
        $ch = curl_init($url);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($useAuth) {
            // Using Basic Auth (works with Application Passwords)
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        $this->logSyncAttempt($method, $url, $data, $httpCode, $response, $error);

        if ($error) {
            return false;
        }

        return json_decode($response, true);
    }

    private function logSyncAttempt($method, $url, $data, $code, $response, $error) {
        $timestamp = date('Y-m-d H:i:s');
        // Mask password in logs
        if (isset($data['password'])) $data['password'] = '********';
        
        $logEntry = "[$timestamp] $method $url | Status: $code\n";
        $logEntry .= "Request Data: " . json_encode($data) . "\n";
        if ($error) $logEntry .= "cURL Error: $error\n";
        $logEntry .= "Response: $response\n";
        $logEntry .= str_repeat('-', 50) . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    private function logSyncError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] ERROR: $message\n";
        $logEntry .= str_repeat('-', 50) . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}
