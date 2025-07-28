<?php
/**
 * Secure Patients API for Hospital CRM
 * Requires authentication and role-based access
 */

require_once __DIR__ . '/ApiBase.php';

class PatientsApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("patients_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getPatientsList();
                break;
            case 'get':
                $this->getPatient();
                break;
            default:
                $this->sendError('Invalid action');
        }
    }
    
    private function getPatientsList() {
        if ($this->isAdmin()) {
            // Admin can see all patients
            $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                     CONCAT(du.first_name, ' ', du.last_name) as assigned_doctor
                     FROM patients p
                     JOIN users u ON p.user_id = u.id
                     LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                     LEFT JOIN users du ON d.user_id = du.id
                     ORDER BY u.first_name, u.last_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
        } elseif ($this->isDoctor()) {
            // Doctor can only see assigned patients
            $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                     CONCAT(du.first_name, ' ', du.last_name) as assigned_doctor
                     FROM patients p
                     JOIN users u ON p.user_id = u.id
                     LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                     LEFT JOIN users du ON d.user_id = du.id
                     WHERE d.user_id = :user_id
                     ORDER BY u.first_name, u.last_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            
        } elseif ($this->isPatient()) {
            // Patient can only see their own data
            $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                     CONCAT(du.first_name, ' ', du.last_name) as assigned_doctor
                     FROM patients p
                     JOIN users u ON p.user_id = u.id
                     LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                     LEFT JOIN users du ON d.user_id = du.id
                     WHERE p.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
        }
        
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($patients, 'Patients retrieved successfully');
    }
    
    private function getPatient() {
        $patient_id = $_GET['id'] ?? null;
        if (!$patient_id) {
            $this->sendError('Patient ID required');
        }
        
        // Check if user can access this patient
        if (!$this->canAccessPatient($patient_id)) {
            $this->sendError('Access denied to this patient data', 403);
        }
        
        $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                 CONCAT(du.first_name, ' ', du.last_name) as assigned_doctor
                 FROM patients p
                 JOIN users u ON p.user_id = u.id
                 LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                 LEFT JOIN users du ON d.user_id = du.id
                 WHERE p.patient_id = :patient_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$patient) {
            $this->sendError('Patient not found', 404);
        }
        
        $this->sendSuccess($patient, 'Patient retrieved successfully');
    }
    
    private function handlePost($action) {
        // Only admin can create new patients
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can create new patients', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['first_name', 'last_name', 'email', 'username', 'password']);
        
        try {
            $this->conn->beginTransaction();
            
            // Create user first
            $user_query = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, address, date_of_birth, gender) 
                          VALUES (:username, :email, :password, 'patient', :first_name, :last_name, :phone, :address, :date_of_birth, :gender)";
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
            
            // Create patient record
            $patient_query = "INSERT INTO patients (user_id, date_of_birth, gender, blood_group, emergency_contact_name, emergency_contact_phone, assigned_doctor_id)
                             VALUES (:user_id, :date_of_birth, :gender, :blood_group, :emergency_contact_name, :emergency_contact_phone, :assigned_doctor_id)";
            $patient_stmt = $this->conn->prepare($patient_query);
            
            $assigned_doctor_id = $data['assigned_doctor_id'] ?? null;
            $patient_stmt->bindParam(':user_id', $user_id);
            $patient_stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $patient_stmt->bindParam(':gender', $data['gender'] ?? null);
            $patient_stmt->bindParam(':blood_group', $data['blood_group'] ?? null);
            $patient_stmt->bindParam(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
            $patient_stmt->bindParam(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
            $patient_stmt->bindParam(':assigned_doctor_id', $assigned_doctor_id);
            $patient_stmt->execute();
            
            $this->conn->commit();
            $this->sendSuccess(['user_id' => $user_id], 'Patient created successfully');
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendError('Failed to create patient: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        $patient_id = $_GET['id'] ?? null;
        if (!$patient_id) {
            $this->sendError('Patient ID required');
        }
        
        // Check access permissions
        if (!$this->canAccessPatient($patient_id)) {
            $this->sendError('Access denied to update this patient', 403);
        }
        
        // Patients can only update limited fields
        if ($this->isPatient()) {
            $this->updatePatientLimited($patient_id);
        } else {
            $this->updatePatientFull($patient_id);
        }
    }
    
    private function updatePatientLimited($patient_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Patients can only update their contact info and emergency contacts
        $allowed_fields = ['phone', 'address', 'emergency_contact_name', 'emergency_contact_phone'];
        $update_fields = [];
        $params = [':patient_id' => $patient_id];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($update_fields)) {
            $this->sendError('No valid fields to update');
        }
        
        try {
            // Update user table
            if (isset($data['phone']) || isset($data['address'])) {
                $user_query = "UPDATE users u JOIN patients p ON u.id = p.user_id SET ";
                $user_updates = [];
                if (isset($data['phone'])) $user_updates[] = "u.phone = :phone";
                if (isset($data['address'])) $user_updates[] = "u.address = :address";
                $user_query .= implode(', ', $user_updates) . " WHERE p.patient_id = :patient_id";
                
                $user_stmt = $this->conn->prepare($user_query);
                $user_stmt->execute($params);
            }
            
            // Update patient table
            $patient_fields = array_intersect($allowed_fields, ['emergency_contact_name', 'emergency_contact_phone']);
            if (!empty($patient_fields)) {
                $patient_updates = [];
                foreach ($patient_fields as $field) {
                    if (isset($data[$field])) {
                        $patient_updates[] = "$field = :$field";
                    }
                }
                
                if (!empty($patient_updates)) {
                    $patient_query = "UPDATE patients SET " . implode(', ', $patient_updates) . " WHERE patient_id = :patient_id";
                    $patient_stmt = $this->conn->prepare($patient_query);
                    $patient_stmt->execute($params);
                }
            }
            
            $this->sendSuccess(null, 'Patient information updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update patient: ' . $e->getMessage());
        }
    }
    
    private function updatePatientFull($patient_id) {
        // Admin and doctors can update more fields
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->conn->beginTransaction();
            
            // Update user table
            $user_query = "UPDATE users u JOIN patients p ON u.id = p.user_id 
                          SET u.first_name = :first_name, u.last_name = :last_name, u.email = :email, 
                              u.phone = :phone, u.address = :address, u.date_of_birth = :date_of_birth, u.gender = :gender
                          WHERE p.patient_id = :patient_id";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(':first_name', $data['first_name']);
            $user_stmt->bindParam(':last_name', $data['last_name']);
            $user_stmt->bindParam(':email', $data['email']);
            $user_stmt->bindParam(':phone', $data['phone'] ?? null);
            $user_stmt->bindParam(':address', $data['address'] ?? null);
            $user_stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $user_stmt->bindParam(':gender', $data['gender'] ?? null);
            $user_stmt->bindParam(':patient_id', $patient_id);
            $user_stmt->execute();
            
            // Update patient table
            $patient_query = "UPDATE patients SET date_of_birth = :date_of_birth, gender = :gender, blood_group = :blood_group,
                             emergency_contact_name = :emergency_contact_name, emergency_contact_phone = :emergency_contact_phone, 
                             assigned_doctor_id = :assigned_doctor_id WHERE patient_id = :patient_id";
            $patient_stmt = $this->conn->prepare($patient_query);
            
            $assigned_doctor_id = $data['assigned_doctor_id'] ?? null;
            $patient_stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $patient_stmt->bindParam(':gender', $data['gender'] ?? null);
            $patient_stmt->bindParam(':blood_group', $data['blood_group'] ?? null);
            $patient_stmt->bindParam(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
            $patient_stmt->bindParam(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
            $patient_stmt->bindParam(':assigned_doctor_id', $assigned_doctor_id);
            $patient_stmt->bindParam(':patient_id', $patient_id);
            $patient_stmt->execute();
            
            $this->conn->commit();
            $this->sendSuccess(null, 'Patient updated successfully');
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendError('Failed to update patient: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        // Only admin can delete patients
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can delete patients', 403);
        }
        
        $patient_id = $_GET['id'] ?? null;
        if (!$patient_id) {
            $this->sendError('Patient ID required');
        }
        
        try {
            $query = "DELETE FROM users WHERE id = (SELECT user_id FROM patients WHERE patient_id = :patient_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'Patient deleted successfully');
            } else {
                $this->sendError('Patient not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete patient: ' . $e->getMessage());
        }
    }
}

// Initialize and handle the request
$api = new PatientsApi();
$api->handleRequest();
?>