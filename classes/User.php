<?php
// Include database configuration
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Create new user (Admin only)
    public function createUser($data) {
        try {
            $auth = new Auth();
            
            // Validate password
            $password_errors = $auth->validatePassword($data['password']);
            if (!empty($password_errors)) {
                return ['success' => false, 'message' => implode(', ', $password_errors)];
            }
            
            // Check if email or username already exists
            $check_query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                           WHERE email = :email OR username = :username";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':email', $data['email']);
            $check_stmt->bindParam(':username', $data['username']);
            $check_stmt->execute();
            
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Email or username already exists'];
            }
            
            // Hash password
            $hashed_password = $auth->hashPassword($data['password']);
            
            // Insert user
            $query = "INSERT INTO " . $this->table_name . " 
                     (username, email, password, role, first_name, last_name, phone, address, date_of_birth, gender) 
                     VALUES (:username, :email, :password, :role, :first_name, :last_name, :phone, :address, :date_of_birth, :gender)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':gender', $data['gender']);
            
            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // Create role-specific record
                if ($data['role'] === 'doctor') {
                    $this->createDoctorRecord($user_id, $data);
                } elseif ($data['role'] === 'patient') {
                    $this->createPatientRecord($user_id, $data);
                }
                
                return ['success' => true, 'message' => 'User created successfully', 'user_id' => $user_id];
            } else {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Create doctor record
    private function createDoctorRecord($user_id, $data) {
        $license_number = 'DOC' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO doctors 
                 (user_id, specialization, license_number, qualification, experience_years, consultation_fee, department) 
                 VALUES (:user_id, :specialization, :license_number, :qualification, :experience_years, :consultation_fee, :department)";
        
        $stmt = $this->conn->prepare($query);
        
        // Prepare variables for binding
        $specialization = $data['specialization'] ?? '';
        $qualification = $data['qualification'] ?? '';
        $experience_years = $data['experience_years'] ?? 0;
        $consultation_fee = $data['consultation_fee'] ?? 0.00;
        $department = $data['department'] ?? '';
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':license_number', $license_number);
        $stmt->bindParam(':qualification', $qualification);
        $stmt->bindParam(':experience_years', $experience_years);
        $stmt->bindParam(':consultation_fee', $consultation_fee);
        $stmt->bindParam(':department', $department);
        
        return $stmt->execute();
    }
    
    // Create patient record
    private function createPatientRecord($user_id, $data) {
        $patient_id = 'PAT' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO patients 
                 (user_id, patient_id, blood_group, emergency_contact_name, emergency_contact_phone, medical_history, allergies, insurance_number) 
                 VALUES (:user_id, :patient_id, :blood_group, :emergency_contact_name, :emergency_contact_phone, :medical_history, :allergies, :insurance_number)";
        
        $stmt = $this->conn->prepare($query);
        
        // Prepare variables for binding
        $blood_group = $data['blood_group'] ?? '';
        $emergency_contact_name = $data['emergency_contact_name'] ?? '';
        $emergency_contact_phone = $data['emergency_contact_phone'] ?? '';
        $medical_history = $data['medical_history'] ?? '';
        $allergies = $data['allergies'] ?? '';
        $insurance_number = $data['insurance_number'] ?? '';
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':blood_group', $blood_group);
        $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
        $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
        $stmt->bindParam(':medical_history', $medical_history);
        $stmt->bindParam(':allergies', $allergies);
        $stmt->bindParam(':insurance_number', $insurance_number);
        
        return $stmt->execute();
    }
    
    // Get all users (Admin only)
    public function getAllUsers($role = null) {
        $query = "SELECT u.*, d.specialization, d.license_number, d.department, 
                        p.patient_id as patient_code, p.blood_group 
                 FROM " . $this->table_name . " u 
                 LEFT JOIN doctors d ON u.id = d.user_id 
                 LEFT JOIN patients p ON u.id = p.user_id";
        
        if ($role) {
            $query .= " WHERE u.role = :role";
        }
        
        $query .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($role) {
            $stmt->bindParam(':role', $role);
        }
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove passwords
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        return $users;
    }
    
    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT u.*, d.*, p.*, 
                        d.id as doctor_id, p.id as patient_table_id,
                        p.patient_id as patient_code
                 FROM " . $this->table_name . " u 
                 LEFT JOIN doctors d ON u.id = d.user_id 
                 LEFT JOIN patients p ON u.id = p.user_id 
                 WHERE u.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
    
    // Update user
    public function updateUser($id, $data) {
        try {
            // Update basic user info
            $query = "UPDATE " . $this->table_name . " 
                     SET first_name = :first_name, last_name = :last_name, phone = :phone, 
                         address = :address, date_of_birth = :date_of_birth, gender = :gender
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':gender', $data['gender']);
            
            if ($stmt->execute()) {
                // Update role-specific data
                $user = $this->getUserById($id);
                if ($user['role'] === 'doctor') {
                    $this->updateDoctorRecord($user['doctor_id'], $data);
                } elseif ($user['role'] === 'patient') {
                    $this->updatePatientRecord($user['patient_table_id'], $data);
                }
                
                return ['success' => true, 'message' => 'User updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Update doctor record
    private function updateDoctorRecord($doctor_id, $data) {
        $query = "UPDATE doctors 
                 SET specialization = :specialization, qualification = :qualification, 
                     experience_years = :experience_years, consultation_fee = :consultation_fee, department = :department
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Prepare variables for binding
        $specialization = $data['specialization'] ?? '';
        $qualification = $data['qualification'] ?? '';
        $experience_years = $data['experience_years'] ?? 0;
        $consultation_fee = $data['consultation_fee'] ?? 0.00;
        $department = $data['department'] ?? '';
        
        $stmt->bindParam(':id', $doctor_id);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':qualification', $qualification);
        $stmt->bindParam(':experience_years', $experience_years);
        $stmt->bindParam(':consultation_fee', $consultation_fee);
        $stmt->bindParam(':department', $department);
        
        return $stmt->execute();
    }
    
    // Update patient record
    private function updatePatientRecord($patient_id, $data) {
        $query = "UPDATE patients 
                 SET blood_group = :blood_group, emergency_contact_name = :emergency_contact_name, 
                     emergency_contact_phone = :emergency_contact_phone, medical_history = :medical_history, 
                     allergies = :allergies, insurance_number = :insurance_number
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Prepare variables for binding
        $blood_group = $data['blood_group'] ?? '';
        $emergency_contact_name = $data['emergency_contact_name'] ?? '';
        $emergency_contact_phone = $data['emergency_contact_phone'] ?? '';
        $medical_history = $data['medical_history'] ?? '';
        $allergies = $data['allergies'] ?? '';
        $insurance_number = $data['insurance_number'] ?? '';
        
        $stmt->bindParam(':id', $patient_id);
        $stmt->bindParam(':blood_group', $blood_group);
        $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
        $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
        $stmt->bindParam(':medical_history', $medical_history);
        $stmt->bindParam(':allergies', $allergies);
        $stmt->bindParam(':insurance_number', $insurance_number);
        
        return $stmt->execute();
    }
    
    // Delete user
    public function deleteUser($id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User deactivated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to deactivate user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Get assigned patients for doctor
    public function getAssignedPatients($doctor_id) {
        $query = "SELECT u.*, p.patient_id as patient_code, p.blood_group, p.emergency_contact_name, 
                        p.emergency_contact_phone, dpa.assigned_date
                 FROM doctor_patient_assignments dpa
                 JOIN patients p ON dpa.patient_id = p.id
                 JOIN users u ON p.user_id = u.id
                 WHERE dpa.doctor_id = :doctor_id AND dpa.is_active = 1 AND u.is_active = 1
                 ORDER BY dpa.assigned_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();
        
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove sensitive data
        foreach ($patients as &$patient) {
            unset($patient['password']);
            // Remove contact details as per requirement
            unset($patient['phone']);
            unset($patient['address']);
        }
        
        return $patients;
    }
    
    // Assign patient to doctor
    public function assignPatientToDoctor($doctor_id, $patient_id, $notes = '') {
        try {
            // Check if already assigned
            $check_query = "SELECT COUNT(*) as count FROM doctor_patient_assignments 
                           WHERE doctor_id = :doctor_id AND patient_id = :patient_id AND is_active = 1";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':doctor_id', $doctor_id);
            $check_stmt->bindParam(':patient_id', $patient_id);
            $check_stmt->execute();
            
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Patient already assigned to this doctor'];
            }
            
            $query = "INSERT INTO doctor_patient_assignments (doctor_id, patient_id, notes) 
                     VALUES (:doctor_id, :patient_id, :notes)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Patient assigned successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to assign patient'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>