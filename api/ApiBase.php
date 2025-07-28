<?php
/**
 * API Base Class for Hospital CRM
 * Handles authentication and role-based access control
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

class ApiBase {
    protected $auth;
    protected $conn;
    protected $current_user;
    protected $allowed_roles = [];
    
    public function __construct($required_roles = []) {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clean any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Initialize auth and database
        $this->auth = new Auth();
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Set allowed roles for this API
        $this->allowed_roles = $required_roles;
        
        // Check authentication
        $this->checkAuthentication();
        
        // Check role-based access
        $this->checkRoleAccess();
    }
    
    /**
     * Check if user is authenticated
     */
    private function checkAuthentication() {
        if (!$this->auth->isLoggedIn()) {
            $this->sendError('Unauthorized access. Please login first.', 401);
        }
        
        $this->current_user = $this->auth->getCurrentUser();
        
        if (!$this->current_user) {
            $this->sendError('Invalid session. Please login again.', 401);
        }
    }
    
    /**
     * Check role-based access
     */
    private function checkRoleAccess() {
        if (!empty($this->allowed_roles)) {
            $user_role = $this->current_user['role'];
            
            if (!in_array($user_role, $this->allowed_roles)) {
                $this->sendError("Access denied. Required roles: " . implode(', ', $this->allowed_roles), 403);
            }
        }
    }
    
    /**
     * Check if current user is admin
     */
    protected function isAdmin() {
        return $this->current_user['role'] === 'admin';
    }
    
    /**
     * Check if current user is doctor
     */
    protected function isDoctor() {
        return $this->current_user['role'] === 'doctor';
    }
    
    /**
     * Check if current user is patient
     */
    protected function isPatient() {
        return $this->current_user['role'] === 'patient';
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId() {
        return $this->current_user['id'];
    }
    
    /**
     * Get current user role
     */
    protected function getCurrentUserRole() {
        return $this->current_user['role'];
    }
    
    /**
     * Check if user can access specific patient data
     */
    protected function canAccessPatient($patient_id) {
        if ($this->isAdmin()) {
            return true; // Admin can access all patients
        }
        
        if ($this->isDoctor()) {
            // Doctor can access assigned patients
            $query = "SELECT COUNT(*) FROM patients p 
                     LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id 
                     WHERE p.patient_id = :patient_id AND d.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        if ($this->isPatient()) {
            // Patient can only access their own data
            $query = "SELECT COUNT(*) FROM patients WHERE patient_id = :patient_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }
    
    /**
     * Check if user can access specific doctor data
     */
    protected function canAccessDoctor($doctor_id) {
        if ($this->isAdmin()) {
            return true; // Admin can access all doctors
        }
        
        if ($this->isDoctor()) {
            // Doctor can only access their own data
            $query = "SELECT COUNT(*) FROM doctors WHERE doctor_id = :doctor_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }
    
    /**
     * Send success response
     */
    protected function sendSuccess($data = null, $message = 'Success') {
        // Clean output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
    
    /**
     * Send error response
     */
    protected function sendError($message, $code = 400) {
        // Clean output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code
        ]);
        exit();
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $required_fields) {
        $missing = [];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError('Missing required fields: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Log API access for security monitoring
     */
    protected function logAccess($action, $resource_id = null) {
        $log_data = [
            'user_id' => $this->getCurrentUserId(),
            'role' => $this->getCurrentUserRole(),
            'action' => $action,
            'resource_id' => $resource_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // You can implement logging to database or file here
        error_log("API Access: " . json_encode($log_data));
    }
}
?>