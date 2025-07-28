<?php
/**
 * Secure Medical History API for Hospital CRM
 * Requires authentication and role-based access
 */

require_once __DIR__ . '/ApiBase.php';

class MedicalHistoryApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("medical_history_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getMedicalHistoryList();
                break;
            case 'get':
                $this->getMedicalHistory();
                break;
            case 'patient':
                $this->getPatientMedicalHistory();
                break;
            default:
                $this->getMedicalHistoryList();
        }
    }
    
    private function getMedicalHistoryList() {
        if ($this->isAdmin()) {
            // Admin can see all medical history records
            $query = "SELECT mh.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM medical_history mh
                     LEFT JOIN patients pt ON mh.patient_id = pt.patient_id
                     LEFT JOIN users pu ON pt.user_id = pu.id
                     LEFT JOIN doctors doc ON mh.doctor_id = doc.doctor_id
                     LEFT JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                     ORDER BY mh.record_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
        } elseif ($this->isDoctor()) {
            // Doctor can see medical history for their assigned patients
            $query = "SELECT mh.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM medical_history mh
                     LEFT JOIN patients pt ON mh.patient_id = pt.patient_id
                     LEFT JOIN users pu ON pt.user_id = pu.id
                     LEFT JOIN doctors doc ON mh.doctor_id = doc.doctor_id
                     LEFT JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                     WHERE pt.assigned_doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = :user_id)
                        OR mh.doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = :user_id)
                     ORDER BY mh.record_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            
        } elseif ($this->isPatient()) {
            // Patient can only see their own medical history
            $query = "SELECT mh.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM medical_history mh
                     LEFT JOIN patients pt ON mh.patient_id = pt.patient_id
                     LEFT JOIN users pu ON pt.user_id = pu.id
                     LEFT JOIN doctors doc ON mh.doctor_id = doc.doctor_id
                     LEFT JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                     WHERE pt.user_id = :user_id
                     ORDER BY mh.record_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
        }
        
        $medical_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($medical_history, 'Medical history retrieved successfully');
    }
    
    private function getMedicalHistory() {
        $history_id = $_GET['id'] ?? null;
        if (!$history_id) {
            $this->sendError('Medical history ID required');
        }
        
        // Check if user can access this medical history record
        if (!$this->canAccessMedicalHistory($history_id)) {
            $this->sendError('Access denied to this medical history record', 403);
        }
        
        $query = "SELECT mh.*, 
                 CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                 CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                 d.specialization as doctor_specialization,
                 pu.email as patient_email, pu.phone as patient_phone,
                 du.email as doctor_email, du.phone as doctor_phone
                 FROM medical_history mh
                 LEFT JOIN patients pt ON mh.patient_id = pt.patient_id
                 LEFT JOIN users pu ON pt.user_id = pu.id
                 LEFT JOIN doctors doc ON mh.doctor_id = doc.doctor_id
                 LEFT JOIN users du ON doc.user_id = du.id
                 LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                 WHERE mh.id = :history_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':history_id', $history_id);
        $stmt->execute();
        
        $medical_history = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$medical_history) {
            $this->sendError('Medical history record not found', 404);
        }
        
        $this->sendSuccess($medical_history, 'Medical history record retrieved successfully');
    }
    
    private function getPatientMedicalHistory() {
        $patient_id = $_GET['patient_id'] ?? null;
        if (!$patient_id) {
            $this->sendError('Patient ID required');
        }
        
        // Check if user can access this patient's medical history
        if (!$this->canAccessPatient($patient_id)) {
            $this->sendError('Access denied to this patient\'s medical history', 403);
        }
        
        $query = "SELECT mh.*, 
                 CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                 d.specialization as doctor_specialization
                 FROM medical_history mh
                 LEFT JOIN doctors doc ON mh.doctor_id = doc.doctor_id
                 LEFT JOIN users du ON doc.user_id = du.id
                 LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                 WHERE mh.patient_id = :patient_id
                 ORDER BY mh.record_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        
        $medical_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($medical_history, 'Patient medical history retrieved successfully');
    }
    
    private function handlePost($action) {
        // Only admin and doctors can create medical history records
        if (!$this->isAdmin() && !$this->isDoctor()) {
            $this->sendError('Only admin and doctors can create medical history records', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['patient_id', 'condition', 'diagnosis']);
        
        // Check permissions
        if ($this->isDoctor()) {
            // Doctor can only create records for their assigned patients
            if (!$this->canAccessPatient($data['patient_id'])) {
                $this->sendError('You can only create medical history for your assigned patients', 403);
            }
            
            // Set doctor_id to current user's doctor_id
            $doctor_query = "SELECT doctor_id FROM doctors WHERE user_id = :user_id";
            $doctor_stmt = $this->conn->prepare($doctor_query);
            $doctor_stmt->bindParam(':user_id', $this->getCurrentUserId());
            $doctor_stmt->execute();
            $doctor_data = $doctor_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doctor_data) {
                $this->sendError('Doctor record not found');
            }
            
            $data['doctor_id'] = $doctor_data['doctor_id'];
        }
        
        try {
            $query = "INSERT INTO medical_history (patient_id, doctor_id, condition_name, diagnosis, treatment, 
                     medications, allergies, notes, record_date, severity)
                     VALUES (:patient_id, :doctor_id, :condition_name, :diagnosis, :treatment, 
                     :medications, :allergies, :notes, :record_date, :severity)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id'] ?? null);
            $stmt->bindParam(':condition_name', $data['condition']);
            $stmt->bindParam(':diagnosis', $data['diagnosis']);
            $stmt->bindParam(':treatment', $data['treatment'] ?? null);
            $stmt->bindParam(':medications', $data['medications'] ?? null);
            $stmt->bindParam(':allergies', $data['allergies'] ?? null);
            $stmt->bindParam(':notes', $data['notes'] ?? null);
            $stmt->bindParam(':record_date', $data['record_date'] ?? date('Y-m-d'));
            $stmt->bindParam(':severity', $data['severity'] ?? 'moderate');
            $stmt->execute();
            
            $history_id = $this->conn->lastInsertId();
            $this->sendSuccess(['history_id' => $history_id], 'Medical history record created successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to create medical history record: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        $history_id = $_GET['id'] ?? null;
        if (!$history_id) {
            $this->sendError('Medical history ID required');
        }
        
        // Check if user can access this medical history record
        if (!$this->canAccessMedicalHistory($history_id)) {
            $this->sendError('Access denied to update this medical history record', 403);
        }
        
        // Only the creating doctor or admin can update medical history
        if (!$this->isAdmin() && !$this->isCreatingDoctor($history_id)) {
            $this->sendError('Only the creating doctor or admin can update medical history records', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $update_fields = [];
            $params = [':history_id' => $history_id];
            
            $allowed_fields = ['condition_name', 'diagnosis', 'treatment', 'medications', 'allergies', 'notes', 'severity'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($update_fields)) {
                $this->sendError('No valid fields to update');
            }
            
            $query = "UPDATE medical_history SET " . implode(', ', $update_fields) . " WHERE id = :history_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $this->sendSuccess(null, 'Medical history record updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update medical history record: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        // Only admin can delete medical history records
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can delete medical history records', 403);
        }
        
        $history_id = $_GET['id'] ?? null;
        if (!$history_id) {
            $this->sendError('Medical history ID required');
        }
        
        try {
            $query = "DELETE FROM medical_history WHERE id = :history_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':history_id', $history_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'Medical history record deleted successfully');
            } else {
                $this->sendError('Medical history record not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete medical history record: ' . $e->getMessage());
        }
    }
    
    private function canAccessMedicalHistory($history_id) {
        if ($this->isAdmin()) {
            return true; // Admin can access all medical history
        }
        
        if ($this->isDoctor()) {
            // Doctor can access medical history for their assigned patients or records they created
            $query = "SELECT COUNT(*) FROM medical_history mh
                     LEFT JOIN patients p ON mh.patient_id = p.patient_id
                     LEFT JOIN doctors d ON mh.doctor_id = d.doctor_id
                     WHERE mh.id = :history_id 
                     AND (p.assigned_doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = :user_id)
                          OR d.user_id = :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':history_id', $history_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        if ($this->isPatient()) {
            // Patient can access their own medical history
            $query = "SELECT COUNT(*) FROM medical_history mh 
                     JOIN patients p ON mh.patient_id = p.patient_id 
                     WHERE mh.id = :history_id AND p.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':history_id', $history_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }
    
    private function isCreatingDoctor($history_id) {
        $query = "SELECT COUNT(*) FROM medical_history mh 
                 JOIN doctors d ON mh.doctor_id = d.doctor_id 
                 WHERE mh.id = :history_id AND d.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':history_id', $history_id);
        $stmt->bindParam(':user_id', $this->getCurrentUserId());
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}

// Initialize and handle the request
$api = new MedicalHistoryApi();
$api->handleRequest();
?>