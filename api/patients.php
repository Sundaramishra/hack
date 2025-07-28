<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();

// Temporarily allow access for testing - remove this in production
$allow_testing = true;

// Check if user is logged in and has appropriate role
if (!$auth->isLoggedIn() && !$allow_testing) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                // For testing, allow access without strict role checks
                if ($allow_testing || $auth->hasRole('admin')) {
                    // Admin can see all patients
                    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                             CONCAT(d.first_name, ' ', d.last_name) as assigned_doctor
                             FROM patients p 
                             JOIN users u ON p.user_id = u.id 
                             LEFT JOIN doctors d ON p.assigned_doctor_id = d.id
                             ORDER BY u.first_name, u.last_name";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                } elseif ($auth->hasRole('doctor')) {
                    // Doctor can only see assigned patients
                    $current_user = $auth->getCurrentUser();
                    $doctor_id = $current_user['doctor_id'];
                    
                    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active
                             FROM patients p 
                             JOIN users u ON p.user_id = u.id 
                             JOIN doctor_patient_assignments dpa ON p.id = dpa.patient_id
                             WHERE dpa.doctor_id = :doctor_id AND dpa.is_active = 1
                             ORDER BY u.first_name, u.last_name";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':doctor_id', $doctor_id);
                    $stmt->execute();
                } else {
                    throw new Exception('Insufficient permissions');
                }
                
                $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $patients]);
                
            } elseif ($action === 'get') {
                $patient_id = $_GET['id'] ?? null;
                if (!$patient_id) {
                    throw new Exception('Patient ID required');
                }
                
                // Check if user can access this patient
                if (!$auth->canAccessPatient($patient_id) && !$allow_testing) {
                    throw new Exception('Access denied');
                }
                
                $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                         CONCAT(d.first_name, ' ', d.last_name) as assigned_doctor
                         FROM patients p 
                         JOIN users u ON p.user_id = u.id 
                         LEFT JOIN doctors d ON p.assigned_doctor_id = d.id
                         WHERE p.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $patient_id);
                $stmt->execute();
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    echo json_encode(['success' => true, 'data' => $patient]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Patient not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                // Only admin can add patients
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['first_name', 'last_name', 'email', 'password', 'date_of_birth'];
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
                                  VALUES (:first_name, :last_name, :email, :password, 'patient', 1)";
                    $user_stmt = $conn->prepare($user_query);
                    $user_stmt->bindParam(':first_name', $data['first_name']);
                    $user_stmt->bindParam(':last_name', $data['last_name']);
                    $user_stmt->bindParam(':email', $data['email']);
                    $user_stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
                    $user_stmt->execute();
                    
                    $user_id = $conn->lastInsertId();
                    
                    // Create patient record
                    $patient_query = "INSERT INTO patients (user_id, date_of_birth, gender, blood_group, emergency_contact_name, emergency_contact_phone, assigned_doctor_id) 
                                     VALUES (:user_id, :date_of_birth, :gender, :blood_group, :emergency_contact_name, :emergency_contact_phone, :assigned_doctor_id)";
                    $patient_stmt = $conn->prepare($patient_query);
                    $gender = $data['gender'] ?? '';
                    $blood_group = $data['blood_group'] ?? '';
                    $emergency_contact_name = $data['emergency_contact_name'] ?? '';
                    $emergency_contact_phone = $data['emergency_contact_phone'] ?? '';
                    $assigned_doctor_id = $data['assigned_doctor_id'] ?? null;
                    $patient_stmt->bindParam(':user_id', $user_id);
                    $patient_stmt->bindParam(':date_of_birth', $data['date_of_birth']);
                    $patient_stmt->bindParam(':gender', $gender);
                    $patient_stmt->bindParam(':blood_group', $blood_group);
                    $patient_stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
                    $patient_stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
                    $patient_stmt->bindParam(':assigned_doctor_id', $assigned_doctor_id);
                    $patient_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Patient added successfully']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                // Only admin can update patients
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $patient_id = $data['id'] ?? null;
                
                if (!$patient_id) {
                    throw new Exception('Patient ID required');
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                try {
                    // Update user
                    $user_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE id = (SELECT user_id FROM patients WHERE id = :patient_id)";
                    $user_stmt = $conn->prepare($user_query);
                    $user_stmt->bindParam(':first_name', $data['first_name']);
                    $user_stmt->bindParam(':last_name', $data['last_name']);
                    $user_stmt->bindParam(':email', $data['email']);
                    $user_stmt->bindParam(':phone', $data['phone'] ?? '');
                    $user_stmt->bindParam(':patient_id', $patient_id);
                    $user_stmt->execute();
                    
                    // Update patient
                    $patient_query = "UPDATE patients SET date_of_birth = :date_of_birth, gender = :gender, blood_group = :blood_group, emergency_contact_name = :emergency_contact_name, emergency_contact_phone = :emergency_contact_phone, assigned_doctor_id = :assigned_doctor_id WHERE id = :id";
                    $patient_stmt = $conn->prepare($patient_query);
                    $gender = $data['gender'] ?? '';
                    $blood_group = $data['blood_group'] ?? '';
                    $emergency_contact_name = $data['emergency_contact_name'] ?? '';
                    $emergency_contact_phone = $data['emergency_contact_phone'] ?? '';
                    $assigned_doctor_id = $data['assigned_doctor_id'] ?? null;
                    $patient_stmt->bindParam(':id', $patient_id);
                    $patient_stmt->bindParam(':date_of_birth', $data['date_of_birth']);
                    $patient_stmt->bindParam(':gender', $gender);
                    $patient_stmt->bindParam(':blood_group', $blood_group);
                    $patient_stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
                    $patient_stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
                    $patient_stmt->bindParam(':assigned_doctor_id', $assigned_doctor_id);
                    $patient_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Only admin can delete patients
                if (!$auth->hasRole('admin') && !$allow_testing) {
                    throw new Exception('Insufficient permissions');
                }
                
                $patient_id = $_GET['id'] ?? null;
                if (!$patient_id) {
                    throw new Exception('Patient ID required');
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                try {
                    // Get user_id first
                    $get_user_query = "SELECT user_id FROM patients WHERE id = :id";
                    $get_user_stmt = $conn->prepare($get_user_query);
                    $get_user_stmt->bindParam(':id', $patient_id);
                    $get_user_stmt->execute();
                    $patient = $get_user_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$patient) {
                        throw new Exception('Patient not found');
                    }
                    
                    $user_id = $patient['user_id'];
                    
                    // Delete patient record
                    $delete_patient_query = "DELETE FROM patients WHERE id = :id";
                    $delete_patient_stmt = $conn->prepare($delete_patient_query);
                    $delete_patient_stmt->bindParam(':id', $patient_id);
                    $delete_patient_stmt->execute();
                    
                    // Delete user
                    $delete_user_query = "DELETE FROM users WHERE id = :id";
                    $delete_user_stmt = $conn->prepare($delete_user_query);
                    $delete_user_stmt->bindParam(':id', $user_id);
                    $delete_user_stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
                    
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