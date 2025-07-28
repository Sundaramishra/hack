<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('admin');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get all users
        $query = "SELECT id, username, email, first_name, last_name, role, is_active, created_at 
                  FROM users 
                  ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
        
    } elseif ($method === 'POST') {
        // Create new user
        $input = json_decode(file_get_contents('php://input'), true);
        
        $username = $input['username'];
        $email = $input['email'];
        $password = $input['password'];
        $firstName = $input['firstName'];
        $lastName = $input['lastName'];
        $role = $input['role'];
        
        // Validate password
        $passwordValidation = $auth->validatePassword($password);
        if ($passwordValidation !== true) {
            throw new Exception($passwordValidation);
        }
        
        // Hash password
        $hashedPassword = $auth->hashPassword($password);
        
        // Insert user
        $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $role]);
        
        if ($result) {
            $userId = $conn->lastInsertId();
            
            // Create role-specific record
            if ($role === 'doctor') {
                $doctorQuery = "INSERT INTO doctors (user_id, specialization, license_number) 
                               VALUES (?, 'General Practice', ?)";
                $stmt = $conn->prepare($doctorQuery);
                $stmt->execute([$userId, 'LIC' . str_pad($userId, 6, '0', STR_PAD_LEFT)]);
            } elseif ($role === 'patient') {
                $patientQuery = "INSERT INTO patients (user_id, patient_code) 
                                VALUES (?, ?)";
                $stmt = $conn->prepare($patientQuery);
                $stmt->execute([$userId, 'PAT' . str_pad($userId, 6, '0', STR_PAD_LEFT)]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } else {
            throw new Exception('Failed to create user');
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>