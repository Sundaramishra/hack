<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username_or_email, $password) {
        try {
            // Check if input is email or username
            $query = "SELECT u.*, 
                            d.doctor_id, d.specialization,
                            p.patient_id, p.patient_code, p.assigned_doctor_id
                     FROM users u
                     LEFT JOIN doctors d ON u.id = d.user_id
                     LEFT JOIN patients p ON u.id = p.user_id
                     WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['theme'] = $user['theme_preference'];
                
                // Role-specific session data
                if ($user['role'] == 'doctor') {
                    $_SESSION['doctor_id'] = $user['doctor_id'];
                    $_SESSION['specialization'] = $user['specialization'];
                } elseif ($user['role'] == 'patient') {
                    $_SESSION['patient_id'] = $user['patient_id'];
                    $_SESSION['patient_code'] = $user['patient_code'];
                    $_SESSION['assigned_doctor_id'] = $user['assigned_doctor_id'];
                }
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit();
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: ../unauthorized.php');
            exit();
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'role' => $_SESSION['role'],
            'theme' => $_SESSION['theme'] ?? 'light'
        ];
    }
    
    public function validatePassword($password) {
        // Password must be at least 8 characters with uppercase, lowercase, number, and special character
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return "Password must contain at least one special character";
        }
        
        return true;
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>