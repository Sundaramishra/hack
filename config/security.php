<?php
class Security {
    // Encryption keys - Change these in production!
    private static $encryption_key = 'MiloMeet2024SecureKey!@#$%^&*()_+';
    private static $jwt_secret = 'MiloMeetJWTSecret2024!@#$%^&*()_+';
    private static $api_salt = 'MiloMeetAPISalt2024!@#$%^&*()_+';
    
    // API rate limiting
    private static $rate_limit = 100; // requests per minute
    private static $rate_window = 60; // seconds
    
    /**
     * Generate secure API token for authenticated requests
     */
    public static function generateAPIToken($userId, $sessionId = null) {
        $payload = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'issued_at' => time(),
            'expires_at' => time() + 3600, // 1 hour
            'nonce' => bin2hex(random_bytes(16))
        ];
        
        return self::encryptData(json_encode($payload));
    }
    
    /**
     * Validate and decode API token
     */
    public static function validateAPIToken($token) {
        try {
            $decrypted = self::decryptData($token);
            $payload = json_decode($decrypted, true);
            
            if (!$payload || $payload['expires_at'] < time()) {
                return false;
            }
            
            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encryptData($data) {
        $key = hash('sha256', self::$encryption_key, true);
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $encrypted, self::$api_salt, true);
        
        return base64_encode($iv . $hmac . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encryptedData) {
        $data = base64_decode($encryptedData);
        
        if (strlen($data) < 48) {
            throw new Exception('Invalid encrypted data');
        }
        
        $iv = substr($data, 0, 16);
        $hmac = substr($data, 16, 32);
        $encrypted = substr($data, 48);
        
        $key = hash('sha256', self::$encryption_key, true);
        $expectedHmac = hash_hmac('sha256', $encrypted, self::$api_salt, true);
        
        if (!hash_equals($hmac, $expectedHmac)) {
            throw new Exception('HMAC verification failed');
        }
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }
        
        return $decrypted;
    }
    
    /**
     * Generate secure request signature
     */
    public static function generateRequestSignature($data, $timestamp) {
        $message = $data . $timestamp . self::$api_salt;
        return hash_hmac('sha256', $message, self::$jwt_secret);
    }
    
    /**
     * Validate request signature
     */
    public static function validateRequestSignature($data, $timestamp, $signature) {
        $expectedSignature = self::generateRequestSignature($data, $timestamp);
        return hash_equals($signature, $expectedSignature);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier) {
        global $pdo;
        
        try {
            // Clean old entries
            $stmt = $pdo->prepare("DELETE FROM api_rate_limit WHERE created_at < ?");
            $stmt->execute([time() - self::$rate_window]);
            
            // Count recent requests
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_rate_limit WHERE identifier = ? AND created_at > ?");
            $stmt->execute([$identifier, time() - self::$rate_window]);
            $count = $stmt->fetchColumn();
            
            if ($count >= self::$rate_limit) {
                return false;
            }
            
            // Log this request
            $stmt = $pdo->prepare("INSERT INTO api_rate_limit (identifier, created_at) VALUES (?, ?)");
            $stmt->execute([$identifier, time()]);
            
            return true;
        } catch (Exception $e) {
            // If rate limiting fails, allow request but log error
            error_log("Rate limiting error: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Sanitize and validate input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Secure random string generation
     */
    public static function generateSecureRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash sensitive data with salt
     */
    public static function hashWithSalt($data, $salt = null) {
        if ($salt === null) {
            $salt = self::generateSecureRandomString(16);
        }
        return hash('sha256', $data . $salt . self::$api_salt) . ':' . $salt;
    }
    
    /**
     * Verify hashed data
     */
    public static function verifyHash($data, $hash) {
        $parts = explode(':', $hash);
        if (count($parts) !== 2) {
            return false;
        }
        
        $expectedHash = hash('sha256', $data . $parts[1] . self::$api_salt);
        return hash_equals($parts[0], $expectedHash);
    }
    
    /**
     * Get client IP address securely
     */
    public static function getClientIP() {
        $ipSources = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = $_SERVER[$source];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = []) {
        global $pdo;
        
        try {
            $logData = [
                'event' => $event,
                'ip_address' => self::getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'timestamp' => time(),
                'details' => json_encode($details)
            ];
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (event, ip_address, user_agent, timestamp, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $logData['event'],
                $logData['ip_address'],
                $logData['user_agent'],
                $logData['timestamp'],
                $logData['details']
            ]);
        } catch (Exception $e) {
            error_log("Security logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Validate API request headers
     */
    public static function validateAPIHeaders() {
        $required_headers = [
            'Content-Type',
            'X-API-Signature',
            'X-Request-Time',
            'X-API-Token'
        ];
        
        foreach ($required_headers as $header) {
            if (!isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))])) {
                return false;
            }
        }
        
        // Validate timestamp (prevent replay attacks)
        $timestamp = $_SERVER['HTTP_X_REQUEST_TIME'] ?? 0;
        if (abs(time() - $timestamp) > 300) { // 5 minutes tolerance
            return false;
        }
        
        return true;
    }
    
    /**
     * Encrypt API response
     */
    public static function encryptAPIResponse($data, $userToken) {
        $jsonData = json_encode($data);
        $encrypted = self::encryptData($jsonData);
        
        return [
            'encrypted' => true,
            'data' => $encrypted,
            'timestamp' => time(),
            'signature' => self::generateRequestSignature($encrypted, time())
        ];
    }
    
    /**
     * Decrypt API request
     */
    public static function decryptAPIRequest($encryptedData) {
        try {
            return json_decode(self::decryptData($encryptedData), true);
        } catch (Exception $e) {
            throw new Exception('Failed to decrypt API request');
        }
    }
}

// Create security tables if they don't exist
global $pdo;
if ($pdo) {
    $securityTables = "
    CREATE TABLE IF NOT EXISTS api_rate_limit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        identifier VARCHAR(255) NOT NULL,
        created_at INT NOT NULL,
        INDEX (identifier, created_at)
    );
    
    CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        timestamp INT NOT NULL,
        details TEXT,
        INDEX (event, timestamp),
        INDEX (ip_address, timestamp)
    );
    
    CREATE TABLE IF NOT EXISTS api_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_used_at INT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX (token_hash),
        INDEX (user_id, is_active),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";
    
    try {
        $pdo->exec($securityTables);
    } catch (Exception $e) {
        error_log("Security tables creation error: " . $e->getMessage());
    }
}
?>