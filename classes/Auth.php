<?php
// Include database configuration
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    private $table_name = "users";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Password complexity validation
    public function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }
    
    // Hash password
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Verify password
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Login function
    public function login($email, $password) {
        try {
            $query = "SELECT u.*, d.id as doctor_id, p.id as patient_id 
                     FROM " . $this->table_name . " u 
                     LEFT JOIN doctors d ON u.id = d.user_id 
                     LEFT JOIN patients p ON u.id = p.user_id 
                     WHERE u.email = :email AND u.is_active = 1 LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($this->verifyPassword($password, $user['password'])) {
                    // Generate session token
                    $session_token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
                    
                    // Save session
                    $session_query = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
                                    VALUES (:user_id, :token, :ip, :user_agent, :expires_at)";
                    $session_stmt = $this->conn->prepare($session_query);
                    $session_stmt->bindParam(':user_id', $user['id']);
                    $session_stmt->bindParam(':token', $session_token);
                    $session_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
                    $session_stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
                    $session_stmt->bindParam(':expires_at', $expires_at);
                    $session_stmt->execute();
                    
                    // Set session
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['session_token'] = $session_token;
                    $_SESSION['doctor_id'] = $user['doctor_id'];
                    $_SESSION['patient_id'] = $user['patient_id'];
                    
                    unset($user['password']);
                    return ['success' => true, 'user' => $user];
                } else {
                    return ['success' => false, 'message' => 'Invalid password'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
            // Verify session in database
            $query = "SELECT * FROM user_sessions WHERE user_id = :user_id AND session_token = :token 
                     AND expires_at > NOW() AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':token', $_SESSION['session_token']);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        }
        return false;
    }
    
    // Get current user
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $query = "SELECT u.*, d.id as doctor_id, p.id as patient_id, p.patient_id as patient_code
                     FROM " . $this->table_name . " u 
                     LEFT JOIN doctors d ON u.id = d.user_id 
                     LEFT JOIN patients p ON u.id = p.user_id 
                     WHERE u.id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                unset($user['password']);
                return $user;
            }
        }
        return null;
    }
    
    // Logout
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['session_token'])) {
            // Deactivate session in database
            $query = "UPDATE user_sessions SET is_active = 0 WHERE session_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $_SESSION['session_token']);
            $stmt->execute();
        }
        
        session_destroy();
        return true;
    }
    
    // Check user role
    public function hasRole($role) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    // Check if user has access to patient
    public function canAccessPatient($patient_id) {
        $current_user = $this->getCurrentUser();
        
        if ($current_user['role'] === 'admin') {
            return true;
        }
        
        if ($current_user['role'] === 'patient') {
            return $current_user['patient_id'] == $patient_id;
        }
        
        if ($current_user['role'] === 'doctor') {
            // Check if doctor is assigned to this patient
            $query = "SELECT COUNT(*) as count FROM doctor_patient_assignments 
                     WHERE doctor_id = :doctor_id AND patient_id = :patient_id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $current_user['doctor_id']);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        }
        
        return false;
    }
}
?>