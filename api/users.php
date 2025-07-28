<?php
header('Content-Type: application/json');
session_start();

require_once '../classes/Auth.php';
require_once '../classes/User.php';

$auth = new Auth();
$user_manager = new User();

// Check authentication and admin role
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Create new user
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'gender' => $_POST['gender'] ?? '',
                // Role-specific fields
                'specialization' => $_POST['specialization'] ?? '',
                'qualification' => $_POST['qualification'] ?? '',
                'experience_years' => $_POST['experience_years'] ?? 0,
                'consultation_fee' => $_POST['consultation_fee'] ?? 0.00,
                'department' => $_POST['department'] ?? '',
                'blood_group' => $_POST['blood_group'] ?? '',
                'emergency_contact_name' => $_POST['emergency_contact_name'] ?? '',
                'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? '',
                'medical_history' => $_POST['medical_history'] ?? '',
                'allergies' => $_POST['allergies'] ?? '',
                'insurance_number' => $_POST['insurance_number'] ?? ''
            ];
            
            $result = $user_manager->createUser($data);
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update user
            $input = json_decode(file_get_contents('php://input'), true);
            $user_id = $input['id'] ?? 0;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'User ID required']);
                break;
            }
            
            $result = $user_manager->updateUser($user_id, $input);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete user
            $input = json_decode(file_get_contents('php://input'), true);
            $user_id = $input['id'] ?? 0;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'User ID required']);
                break;
            }
            
            $result = $user_manager->deleteUser($user_id);
            echo json_encode($result);
            break;
            
        case 'GET':
            // Get users
            $role = $_GET['role'] ?? null;
            $user_id = $_GET['id'] ?? null;
            
            if ($user_id) {
                $user = $user_manager->getUserById($user_id);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                $users = $user_manager->getAllUsers($role);
                echo json_encode(['success' => true, 'users' => $users]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>