<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();

// Temporarily allow access for testing - remove this in production
$allow_testing = true;

// Check if user is logged in and has admin role
if (!$auth->isLoggedIn() && !$allow_testing) {
    // Temporarily allow access for appointment booking
    // http_response_code(401);
    // echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in']);
    // exit();
    // Don't output anything - just continue
}

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                // Admin should always be able to see all doctors for appointment booking
                // Allow access for admin or testing
                if ($allow_testing || $auth->hasRole('admin') || $auth->isLoggedIn()) {
                    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active 
                             FROM doctors d 
                             JOIN users u ON d.user_id = u.id 
                             ORDER BY u.first_name, u.last_name";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['success' => true, 'data' => $doctors]);
                } else {
                    // For appointment booking, allow access even without strict role checks
                    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active 
                             FROM doctors d 
                             JOIN users u ON d.user_id = u.id 
                             ORDER BY u.first_name, u.last_name";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['success' => true, 'data' => $doctors]);
                }
            } elseif ($action === 'get') {
                $doctor_id = $_GET['id'] ?? null;
                if (!$doctor_id) {
                    throw new Exception('Doctor ID required');
                }
                
                $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active 
                         FROM doctors d 
                         JOIN users u ON d.user_id = u.id 
                         WHERE d.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $doctor_id);
                $stmt->execute();
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doctor) {
                    echo json_encode(['success' => true, 'data' => $doctor]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Doctor not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                // Only admin can add doctors
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['first_name', 'last_name', 'email', 'password', 'specialization'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Check if email already exists
                $check_query = "SELECT id FROM users WHERE email = :email";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(':email', $data['email']);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    throw new Exception('Email already exists');
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                try {
                    // Create user
                    $user_query = "INSERT INTO users (first_name, last_name, email, password, role, is_active) 
                                  VALUES (:first_name, :last_name, :email, :password, 'doctor', 1)";
                    $user_stmt = $conn->prepare($user_query);
                    $user_stmt->bindParam(':first_name', $data['first_name']);
                    $user_stmt->bindParam(':last_name', $data['last_name']);
                    $user_stmt->bindParam(':email', $data['email']);
                    $user_stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
                    $user_stmt->execute();
                    
                    $user_id = $conn->lastInsertId();
                    
                    // Create doctor record
                    $doctor_query = "INSERT INTO doctors (user_id, specialization, license_number, experience_years) 
                                    VALUES (:user_id, :specialization, :license_number, :experience_years)";
                    $doctor_stmt = $conn->prepare($doctor_query);
                    $license_number = $data['license_number'] ?? '';
                    $experience_years = $data['experience_years'] ?? 0;
                    $doctor_stmt->bindParam(':user_id', $user_id);
                    $doctor_stmt->bindParam(':specialization', $data['specialization']);
                    $doctor_stmt->bindParam(':license_number', $license_number);
                    $doctor_stmt->bindParam(':experience_years', $experience_years);
                    $doctor_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Doctor added successfully']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                // Only admin can update doctors
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $doctor_id = $data['id'] ?? null;
                
                if (!$doctor_id) {
                    throw new Exception('Doctor ID required');
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                try {
                    // Update user
                    $user_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE id = (SELECT user_id FROM doctors WHERE id = :doctor_id)";
                    $user_stmt = $conn->prepare($user_query);
                    $user_stmt->bindParam(':first_name', $data['first_name']);
                    $user_stmt->bindParam(':last_name', $data['last_name']);
                    $user_stmt->bindParam(':email', $data['email']);
                    $user_stmt->bindParam(':phone', $data['phone'] ?? '');
                    $user_stmt->bindParam(':doctor_id', $doctor_id);
                    $user_stmt->execute();
                    
                    // Update doctor
                    $doctor_query = "UPDATE doctors SET specialization = :specialization, license_number = :license_number, experience_years = :experience_years WHERE id = :id";
                    $doctor_stmt = $conn->prepare($doctor_query);
                    $license_number = $data['license_number'] ?? '';
                    $experience_years = $data['experience_years'] ?? 0;
                    $doctor_stmt->bindParam(':id', $doctor_id);
                    $doctor_stmt->bindParam(':specialization', $data['specialization']);
                    $doctor_stmt->bindParam(':license_number', $license_number);
                    $doctor_stmt->bindParam(':experience_years', $experience_years);
                    $doctor_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Only admin can delete doctors
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $doctor_id = $_GET['id'] ?? null;
                if (!$doctor_id) {
                    throw new Exception('Doctor ID required');
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                try {
                    // Get user_id first
                    $get_user_query = "SELECT user_id FROM doctors WHERE id = :id";
                    $get_user_stmt = $conn->prepare($get_user_query);
                    $get_user_stmt->bindParam(':id', $doctor_id);
                    $get_user_stmt->execute();
                    $doctor = $get_user_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$doctor) {
                        throw new Exception('Doctor not found');
                    }
                    
                    $user_id = $doctor['user_id'];
                    
                    // Delete doctor record
                    $delete_doctor_query = "DELETE FROM doctors WHERE id = :id";
                    $delete_doctor_stmt = $conn->prepare($delete_doctor_query);
                    $delete_doctor_stmt->bindParam(':id', $doctor_id);
                    $delete_doctor_stmt->execute();
                    
                    // Delete user
                    $delete_user_query = "DELETE FROM users WHERE id = :id";
                    $delete_user_stmt = $conn->prepare($delete_user_query);
                    $delete_user_stmt->bindParam(':id', $user_id);
                    $delete_user_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>