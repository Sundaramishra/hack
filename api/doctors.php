<?php
/**
 * Secure Doctors API for Hospital CRM
 * Requires authentication and role-based access
 */

require_once __DIR__ . '/ApiBase.php';

class DoctorsApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles (patients can view doctors for appointments)
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("doctors_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getDoctorsList();
                break;
            case 'get':
                $this->getDoctor();
                break;
            case 'available':
                $this->getAvailableDoctors();
                break;
            default:
                $this->sendError('Invalid action');
        }
    }
    
    private function getDoctorsList() {
        if ($this->isAdmin()) {
            // Admin can see all doctors with full details
            $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                     u.date_of_birth, u.gender, u.address
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     ORDER BY u.first_name, u.last_name";
            
        } elseif ($this->isDoctor()) {
            // Doctor can see basic info of other doctors
            $query = "SELECT d.doctor_id, d.specialization, d.department, d.experience_years,
                     u.first_name, u.last_name, u.email
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     WHERE u.is_active = 1
                     ORDER BY u.first_name, u.last_name";
            
        } elseif ($this->isPatient()) {
            // Patient can see basic doctor info for appointments
            $query = "SELECT d.doctor_id, d.specialization, d.department, d.experience_years,
                     d.consultation_fee, d.available_days, d.available_time_start, d.available_time_end,
                     u.first_name, u.last_name
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     WHERE u.is_active = 1
                     ORDER BY u.first_name, u.last_name";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($doctors, 'Doctors retrieved successfully');
    }
    
    private function getDoctor() {
        $doctor_id = $_GET['id'] ?? null;
        if (!$doctor_id) {
            $this->sendError('Doctor ID required');
        }
        
        if ($this->isAdmin()) {
            // Admin can see full doctor details
            $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                     u.date_of_birth, u.gender, u.address
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     WHERE d.doctor_id = :doctor_id";
            
        } elseif ($this->isDoctor()) {
            // Doctor can see their own full details or basic info of others
            if ($this->canAccessDoctor($doctor_id)) {
                $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                         u.date_of_birth, u.gender, u.address
                         FROM doctors d
                         JOIN users u ON d.user_id = u.id
                         WHERE d.doctor_id = :doctor_id";
            } else {
                $query = "SELECT d.doctor_id, d.specialization, d.department, d.experience_years,
                         u.first_name, u.last_name, u.email
                         FROM doctors d
                         JOIN users u ON d.user_id = u.id
                         WHERE d.doctor_id = :doctor_id AND u.is_active = 1";
            }
            
        } elseif ($this->isPatient()) {
            // Patient can see basic doctor info
            $query = "SELECT d.doctor_id, d.specialization, d.department, d.experience_years,
                     d.consultation_fee, d.available_days, d.available_time_start, d.available_time_end,
                     u.first_name, u.last_name
                     FROM doctors d
                     JOIN users u ON d.user_id = u.id
                     WHERE d.doctor_id = :doctor_id AND u.is_active = 1";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();
        
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$doctor) {
            $this->sendError('Doctor not found', 404);
        }
        
        $this->sendSuccess($doctor, 'Doctor retrieved successfully');
    }
    
    private function getAvailableDoctors() {
        // All roles can see available doctors for appointments
        $date = $_GET['date'] ?? null;
        $specialization = $_GET['specialization'] ?? null;
        
        $query = "SELECT d.doctor_id, d.specialization, d.department, d.consultation_fee,
                 d.available_days, d.available_time_start, d.available_time_end,
                 u.first_name, u.last_name
                 FROM doctors d
                 JOIN users u ON d.user_id = u.id
                 WHERE u.is_active = 1";
        
        $params = [];
        
        if ($specialization) {
            $query .= " AND d.specialization LIKE :specialization";
            $params[':specialization'] = "%$specialization%";
        }
        
        $query .= " ORDER BY u.first_name, u.last_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($doctors, 'Available doctors retrieved successfully');
    }
    
    private function handlePost($action) {
        // Only admin can create new doctors
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can create new doctors', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['first_name', 'last_name', 'email', 'username', 'password', 'specialization', 'license_number']);
        
        try {
            $this->conn->beginTransaction();
            
            // Create user first
            $user_query = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, address, date_of_birth, gender) 
                          VALUES (:username, :email, :password, 'doctor', :first_name, :last_name, :phone, :address, :date_of_birth, :gender)";
            $user_stmt = $this->conn->prepare($user_query);
            
            $hashed_password = $this->auth->hashPassword($data['password']);
            $user_stmt->bindParam(':username', $data['username']);
            $user_stmt->bindParam(':email', $data['email']);
            $user_stmt->bindParam(':password', $hashed_password);
            $user_stmt->bindParam(':first_name', $data['first_name']);
            $user_stmt->bindParam(':last_name', $data['last_name']);
            $user_stmt->bindParam(':phone', $data['phone'] ?? null);
            $user_stmt->bindParam(':address', $data['address'] ?? null);
            $user_stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $user_stmt->bindParam(':gender', $data['gender'] ?? null);
            $user_stmt->execute();
            
            $user_id = $this->conn->lastInsertId();
            
            // Create doctor record
            $doctor_query = "INSERT INTO doctors (user_id, specialization, license_number, qualification, experience_years, consultation_fee, available_days, available_time_start, available_time_end, department)
                            VALUES (:user_id, :specialization, :license_number, :qualification, :experience_years, :consultation_fee, :available_days, :available_time_start, :available_time_end, :department)";
            $doctor_stmt = $this->conn->prepare($doctor_query);
            
            $doctor_stmt->bindParam(':user_id', $user_id);
            $doctor_stmt->bindParam(':specialization', $data['specialization']);
            $doctor_stmt->bindParam(':license_number', $data['license_number']);
            $doctor_stmt->bindParam(':qualification', $data['qualification'] ?? null);
            $doctor_stmt->bindParam(':experience_years', $data['experience_years'] ?? 0);
            $doctor_stmt->bindParam(':consultation_fee', $data['consultation_fee'] ?? 0.00);
            $doctor_stmt->bindParam(':available_days', $data['available_days'] ?? null);
            $doctor_stmt->bindParam(':available_time_start', $data['available_time_start'] ?? null);
            $doctor_stmt->bindParam(':available_time_end', $data['available_time_end'] ?? null);
            $doctor_stmt->bindParam(':department', $data['department'] ?? null);
            $doctor_stmt->execute();
            
            $this->conn->commit();
            $this->sendSuccess(['user_id' => $user_id], 'Doctor created successfully');
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendError('Failed to create doctor: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        $doctor_id = $_GET['id'] ?? null;
        if (!$doctor_id) {
            $this->sendError('Doctor ID required');
        }
        
        // Check access permissions
        if (!$this->isAdmin() && !$this->canAccessDoctor($doctor_id)) {
            $this->sendError('Access denied to update this doctor', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->conn->beginTransaction();
            
            // Update user table
            $user_query = "UPDATE users u JOIN doctors d ON u.id = d.user_id 
                          SET u.first_name = :first_name, u.last_name = :last_name, u.email = :email, 
                              u.phone = :phone, u.address = :address, u.date_of_birth = :date_of_birth, u.gender = :gender
                          WHERE d.doctor_id = :doctor_id";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(':first_name', $data['first_name']);
            $user_stmt->bindParam(':last_name', $data['last_name']);
            $user_stmt->bindParam(':email', $data['email']);
            $user_stmt->bindParam(':phone', $data['phone'] ?? null);
            $user_stmt->bindParam(':address', $data['address'] ?? null);
            $user_stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $user_stmt->bindParam(':gender', $data['gender'] ?? null);
            $user_stmt->bindParam(':doctor_id', $doctor_id);
            $user_stmt->execute();
            
            // Update doctor table
            $doctor_query = "UPDATE doctors SET specialization = :specialization, license_number = :license_number, 
                            qualification = :qualification, experience_years = :experience_years, consultation_fee = :consultation_fee,
                            available_days = :available_days, available_time_start = :available_time_start, 
                            available_time_end = :available_time_end, department = :department
                            WHERE doctor_id = :doctor_id";
            $doctor_stmt = $this->conn->prepare($doctor_query);
            
            $doctor_stmt->bindParam(':specialization', $data['specialization']);
            $doctor_stmt->bindParam(':license_number', $data['license_number']);
            $doctor_stmt->bindParam(':qualification', $data['qualification'] ?? null);
            $doctor_stmt->bindParam(':experience_years', $data['experience_years'] ?? 0);
            $doctor_stmt->bindParam(':consultation_fee', $data['consultation_fee'] ?? 0.00);
            $doctor_stmt->bindParam(':available_days', $data['available_days'] ?? null);
            $doctor_stmt->bindParam(':available_time_start', $data['available_time_start'] ?? null);
            $doctor_stmt->bindParam(':available_time_end', $data['available_time_end'] ?? null);
            $doctor_stmt->bindParam(':department', $data['department'] ?? null);
            $doctor_stmt->bindParam(':doctor_id', $doctor_id);
            $doctor_stmt->execute();
            
            $this->conn->commit();
            $this->sendSuccess(null, 'Doctor updated successfully');
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendError('Failed to update doctor: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        // Only admin can delete doctors
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can delete doctors', 403);
        }
        
        $doctor_id = $_GET['id'] ?? null;
        if (!$doctor_id) {
            $this->sendError('Doctor ID required');
        }
        
        try {
            $query = "DELETE FROM users WHERE id = (SELECT user_id FROM doctors WHERE doctor_id = :doctor_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'Doctor deleted successfully');
            } else {
                $this->sendError('Doctor not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete doctor: ' . $e->getMessage());
        }
    }
}

// Initialize and handle the request
$api = new DoctorsApi();
$api->handleRequest();
?>