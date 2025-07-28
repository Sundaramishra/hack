<?php
/**
 * Secure Users API for Hospital CRM
 * Requires authentication and admin role only
 */

require_once __DIR__ . '/ApiBase.php';

class UsersApi extends ApiBase {
    
    public function __construct() {
        // Only admin can access users API
        parent::__construct(['admin']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("users_api_$method" . ($action ? "_$action" : ""));
        
        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet($action);
                    break;
                case 'POST':
                    $this->handlePost($action);
                    break;
                case 'PUT':
                    $this->handlePut($action);
                    break;
                case 'DELETE':
                    $this->handleDelete($action);
                    break;
                default:
                    $this->sendError('Method not allowed', 405);
            }
        } catch (Exception $e) {
            $this->sendError('Server error: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleGet($action) {
        switch ($action) {
            case 'list':
                $this->getUsersList();
                break;
            case 'get':
                $this->getUser();
                break;
            case 'stats':
                $this->getUserStats();
                break;
            default:
                $this->getUsersList(); // Default to list
        }
    }
    
    private function getUsersList() {
        $role = $_GET['role'] ?? null;
        
        $query = "SELECT id, username, email, role, first_name, last_name, phone, address, 
                 date_of_birth, gender, is_active, created_at, updated_at
                 FROM users";
        
        $params = [];
        
        if ($role) {
            $query .= " WHERE role = :role";
            $params[':role'] = $role;
        }
        
        $query .= " ORDER BY role, first_name, last_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove password field for security
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        $this->sendSuccess($users, 'Users retrieved successfully');
    }
    
    private function getUser() {
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            $this->sendError('User ID required');
        }
        
        $query = "SELECT id, username, email, role, first_name, last_name, phone, address, 
                 date_of_birth, gender, is_active, created_at, updated_at
                 FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->sendError('User not found', 404);
        }
        
        $this->sendSuccess($user, 'User retrieved successfully');
    }
    
    private function getUserStats() {
        $query = "SELECT 
                 role,
                 COUNT(*) as count,
                 SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count
                 FROM users 
                 GROUP BY role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($stats, 'User statistics retrieved successfully');
    }
    
    private function handlePost($action) {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['username', 'email', 'password', 'role', 'first_name', 'last_name']);
        
        try {
            $this->conn->beginTransaction();
            
            // Check if username or email already exists
            $check_query = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':username', $data['username']);
            $check_stmt->bindParam(':email', $data['email']);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $this->sendError('Username or email already exists');
            }
            
            // Create user
            $query = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, address, date_of_birth, gender, is_active) 
                     VALUES (:username, :email, :password, :role, :first_name, :last_name, :phone, :address, :date_of_birth, :gender, :is_active)";
            $stmt = $this->conn->prepare($query);
            
            $hashed_password = $this->auth->hashPassword($data['password']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone'] ?? null);
            $stmt->bindParam(':address', $data['address'] ?? null);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $stmt->bindParam(':gender', $data['gender'] ?? null);
            $stmt->bindParam(':is_active', $data['is_active'] ?? 1);
            $stmt->execute();
            
            $user_id = $this->conn->lastInsertId();
            
            // Create role-specific records
            if ($data['role'] === 'doctor') {
                $this->createDoctorRecord($user_id, $data);
            } elseif ($data['role'] === 'patient') {
                $this->createPatientRecord($user_id, $data);
            }
            
            $this->conn->commit();
            $this->sendSuccess(['user_id' => $user_id], 'User created successfully');
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendError('Failed to create user: ' . $e->getMessage());
        }
    }
    
    private function createDoctorRecord($user_id, $data) {
        $query = "INSERT INTO doctors (user_id, specialization, license_number, qualification, experience_years, consultation_fee, department)
                 VALUES (:user_id, :specialization, :license_number, :qualification, :experience_years, :consultation_fee, :department)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':specialization', $data['specialization'] ?? 'General');
        $stmt->bindParam(':license_number', $data['license_number'] ?? 'LIC' . rand(1000, 9999));
        $stmt->bindParam(':qualification', $data['qualification'] ?? null);
        $stmt->bindParam(':experience_years', $data['experience_years'] ?? 0);
        $stmt->bindParam(':consultation_fee', $data['consultation_fee'] ?? 0.00);
        $stmt->bindParam(':department', $data['department'] ?? null);
        $stmt->execute();
    }
    
    private function createPatientRecord($user_id, $data) {
        $query = "INSERT INTO patients (user_id, date_of_birth, gender, blood_group, emergency_contact_name, emergency_contact_phone)
                 VALUES (:user_id, :date_of_birth, :gender, :blood_group, :emergency_contact_name, :emergency_contact_phone)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
        $stmt->bindParam(':gender', $data['gender'] ?? null);
        $stmt->bindParam(':blood_group', $data['blood_group'] ?? null);
        $stmt->bindParam(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $stmt->bindParam(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $stmt->execute();
    }
    
    private function handlePut($action) {
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            $this->sendError('User ID required');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $update_fields = [];
            $params = [':user_id' => $user_id];
            
            $allowed_fields = ['username', 'email', 'first_name', 'last_name', 'phone', 'address', 'date_of_birth', 'gender', 'is_active'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            // Handle password update separately
            if (isset($data['password']) && !empty($data['password'])) {
                $update_fields[] = "password = :password";
                $params[':password'] = $this->auth->hashPassword($data['password']);
            }
            
            if (empty($update_fields)) {
                $this->sendError('No valid fields to update');
            }
            
            $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $this->sendSuccess(null, 'User updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update user: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            $this->sendError('User ID required');
        }
        
        // Prevent admin from deleting themselves
        if ($user_id == $this->getCurrentUserId()) {
            $this->sendError('Cannot delete your own account');
        }
        
        try {
            $query = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'User deleted successfully');
            } else {
                $this->sendError('User not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete user: ' . $e->getMessage());
        }
    }
}

// Initialize and handle the request
$api = new UsersApi();
$api->handleRequest();
?>