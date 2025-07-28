<?php
require_once '../config/database.php';
require_once '../config/security.php';

class SecureAPI {
    private $userToken = null;
    private $userId = null;
    private $clientIP = null;
    
    public function __construct() {
        $this->clientIP = Security::getClientIP();
        
        // Set security headers
        $this->setSecurityHeaders();
        
        // Validate request method
        if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])) {
            $this->sendError('Method not allowed', 405);
        }
        
        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Rate limiting
        if (!Security::checkRateLimit($this->clientIP)) {
            Security::logSecurityEvent('rate_limit_exceeded', ['ip' => $this->clientIP]);
            $this->sendError('Rate limit exceeded', 429);
        }
        
        // Validate API headers (except for public endpoints)
        if (!$this->isPublicEndpoint()) {
            if (!Security::validateAPIHeaders()) {
                Security::logSecurityEvent('invalid_api_headers', ['ip' => $this->clientIP]);
                $this->sendError('Invalid API headers', 400);
            }
            
            // Validate API token
            $token = $this->getAuthToken();
            if (!$token) {
                Security::logSecurityEvent('missing_auth_token', ['ip' => $this->clientIP]);
                $this->sendError('Authentication required', 401);
            }
            
            $tokenData = Security::validateAPIToken($token);
            if (!$tokenData) {
                Security::logSecurityEvent('invalid_auth_token', ['ip' => $this->clientIP]);
                $this->sendError('Invalid or expired token', 401);
            }
            
            $this->userToken = $tokenData;
            $this->userId = $tokenData['user_id'];
            
            // Validate request signature
            $signature = $_SERVER['HTTP_X_API_SIGNATURE'] ?? '';
            $timestamp = $_SERVER['HTTP_X_REQUEST_TIME'] ?? '';
            $rawData = file_get_contents('php://input');
            
            if (!Security::validateRequestSignature($rawData, $timestamp, $signature)) {
                Security::logSecurityEvent('invalid_request_signature', [
                    'ip' => $this->clientIP,
                    'user_id' => $this->userId
                ]);
                $this->sendError('Invalid request signature', 401);
            }
        }
    }
    
    private function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        // CORS headers for your domain only
        $allowedOrigins = [
            'https://' . $_SERVER['HTTP_HOST'],
            'https://www.' . $_SERVER['HTTP_HOST']
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Token, X-API-Signature, X-Request-Time, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    private function isPublicEndpoint() {
        $publicEndpoints = [
            '/api/check_meeting.php',
            '/api/public_meeting_info.php'
        ];
        
        $currentEndpoint = $_SERVER['REQUEST_URI'];
        foreach ($publicEndpoints as $endpoint) {
            if (strpos($currentEndpoint, $endpoint) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getAuthToken() {
        // Try header first
        $authHeader = $_SERVER['HTTP_X_API_TOKEN'] ?? '';
        if ($authHeader) {
            return $authHeader;
        }
        
        // Try Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    public function getSecureInput() {
        $rawData = file_get_contents('php://input');
        
        if (empty($rawData)) {
            return [];
        }
        
        // Check if data is encrypted
        $data = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Invalid JSON data', 400);
        }
        
        // If encrypted, decrypt it
        if (isset($data['encrypted']) && $data['encrypted'] === true) {
            try {
                $decryptedData = Security::decryptAPIRequest($data['data']);
                return Security::sanitizeInput($decryptedData);
            } catch (Exception $e) {
                Security::logSecurityEvent('decryption_failed', [
                    'ip' => $this->clientIP,
                    'user_id' => $this->userId,
                    'error' => $e->getMessage()
                ]);
                $this->sendError('Failed to decrypt request data', 400);
            }
        }
        
        return Security::sanitizeInput($data);
    }
    
    public function sendSecureResponse($data, $encrypt = true) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($encrypt && $this->userToken) {
            $response = Security::encryptAPIResponse($data, $this->userToken);
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'timestamp' => time()
            ];
        }
        
        // Add response signature
        $responseJson = json_encode($response);
        $signature = Security::generateRequestSignature($responseJson, time());
        
        header('X-Response-Signature: ' . $signature);
        header('X-Response-Time: ' . time());
        
        echo $responseJson;
        exit();
    }
    
    public function sendError($message, $code = 400, $details = []) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $message,
            'code' => $code,
            'timestamp' => time()
        ];
        
        if (!empty($details) && $_ENV['APP_DEBUG'] ?? false) {
            $response['details'] = $details;
        }
        
        echo json_encode($response);
        exit();
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getUserToken() {
        return $this->userToken;
    }
    
    public function getClientIP() {
        return $this->clientIP;
    }
    
    public function validateCSRF() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::validateCSRFToken($token)) {
            Security::logSecurityEvent('csrf_validation_failed', [
                'ip' => $this->clientIP,
                'user_id' => $this->userId
            ]);
            $this->sendError('CSRF token validation failed', 403);
        }
    }
    
    public function logActivity($action, $details = []) {
        Security::logSecurityEvent($action, array_merge($details, [
            'user_id' => $this->userId,
            'ip' => $this->clientIP
        ]));
    }
    
    public function validateMeetingAccess($meetingId) {
        global $pdo;
        
        try {
            // Check if user is host or participant
            $stmt = $pdo->prepare("
                SELECT m.*, 
                       (m.host_id = ?) as is_host,
                       (SELECT COUNT(*) FROM meeting_participants mp 
                        WHERE mp.meeting_id = ? AND mp.user_id = ? 
                        AND mp.left_at IS NULL AND mp.is_kicked = 0) as is_participant
                FROM meetings m 
                WHERE m.meeting_id = ? AND m.is_active = 1
            ");
            $stmt->execute([$this->userId, $meetingId, $this->userId, $meetingId]);
            $meeting = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$meeting) {
                $this->sendError('Meeting not found or inactive', 404);
            }
            
            if (!$meeting['is_host'] && !$meeting['is_participant']) {
                $this->sendError('Access denied to this meeting', 403);
            }
            
            return $meeting;
        } catch (Exception $e) {
            $this->sendError('Database error', 500);
        }
    }
}

// Auto-instantiate for included files
if (!defined('SECURE_API_MANUAL_INIT')) {
    $secureAPI = new SecureAPI();
}
?>