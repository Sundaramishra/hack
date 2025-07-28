<?php
/**
 * Secure Prescriptions API for Hospital CRM
 * Requires authentication and role-based access
 */

require_once __DIR__ . '/ApiBase.php';

class PrescriptionsApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("prescriptions_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getPrescriptionsList();
                break;
            case 'get':
                $this->getPrescription();
                break;
            default:
                $this->getPrescriptionsList();
        }
    }
    
    private function getPrescriptionsList() {
        if ($this->isAdmin()) {
            // Admin can see all prescriptions
            $query = "SELECT p.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM prescriptions p
                     JOIN patients pt ON p.patient_id = pt.patient_id
                     JOIN users pu ON pt.user_id = pu.id
                     JOIN doctors doc ON p.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
                     ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
        } elseif ($this->isDoctor()) {
            // Doctor can only see prescriptions they created
            $query = "SELECT p.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM prescriptions p
                     JOIN patients pt ON p.patient_id = pt.patient_id
                     JOIN users pu ON pt.user_id = pu.id
                     JOIN doctors doc ON p.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
                     WHERE doc.user_id = :user_id
                     ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            
        } elseif ($this->isPatient()) {
            // Patient can only see their own prescriptions
            $query = "SELECT p.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM prescriptions p
                     JOIN patients pt ON p.patient_id = pt.patient_id
                     JOIN users pu ON pt.user_id = pu.id
                     JOIN doctors doc ON p.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
                     WHERE pt.user_id = :user_id
                     ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
        }
        
        $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($prescriptions, 'Prescriptions retrieved successfully');
    }
    
    private function getPrescription() {
        $prescription_id = $_GET['id'] ?? null;
        if (!$prescription_id) {
            $this->sendError('Prescription ID required');
        }
        
        // Check if user can access this prescription
        if (!$this->canAccessPrescription($prescription_id)) {
            $this->sendError('Access denied to this prescription', 403);
        }
        
        $query = "SELECT p.*, 
                 CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                 CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                 d.specialization as doctor_specialization,
                 pu.email as patient_email, pu.phone as patient_phone,
                 du.email as doctor_email, du.phone as doctor_phone
                 FROM prescriptions p
                 JOIN patients pt ON p.patient_id = pt.patient_id
                 JOIN users pu ON pt.user_id = pu.id
                 JOIN doctors doc ON p.doctor_id = doc.doctor_id
                 JOIN users du ON doc.user_id = du.id
                 LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
                 WHERE p.id = :prescription_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prescription_id', $prescription_id);
        $stmt->execute();
        
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prescription) {
            $this->sendError('Prescription not found', 404);
        }
        
        $this->sendSuccess($prescription, 'Prescription retrieved successfully');
    }
    
    private function handlePost($action) {
        // Only admin and doctors can create prescriptions
        if (!$this->isAdmin() && !$this->isDoctor()) {
            $this->sendError('Only admin and doctors can create prescriptions', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['patient_id', 'doctor_id', 'diagnosis', 'medications']);
        
        // Check permissions
        if ($this->isDoctor()) {
            // Doctor can only create prescriptions for their assigned patients
            if (!$this->canAccessPatient($data['patient_id'])) {
                $this->sendError('You can only create prescriptions for your assigned patients', 403);
            }
            
            // Verify doctor_id matches current user
            $doctor_query = "SELECT doctor_id FROM doctors WHERE user_id = :user_id";
            $doctor_stmt = $this->conn->prepare($doctor_query);
            $doctor_stmt->bindParam(':user_id', $this->getCurrentUserId());
            $doctor_stmt->execute();
            $doctor_data = $doctor_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doctor_data || $doctor_data['doctor_id'] != $data['doctor_id']) {
                $this->sendError('You can only create prescriptions as yourself', 403);
            }
        }
        
        try {
            $query = "INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, diagnosis, medications, instructions, follow_up_date)
                     VALUES (:appointment_id, :patient_id, :doctor_id, :diagnosis, :medications, :instructions, :follow_up_date)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':appointment_id', $data['appointment_id'] ?? null);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':diagnosis', $data['diagnosis']);
            $stmt->bindParam(':medications', $data['medications']);
            $stmt->bindParam(':instructions', $data['instructions'] ?? null);
            $stmt->bindParam(':follow_up_date', $data['follow_up_date'] ?? null);
            $stmt->execute();
            
            $prescription_id = $this->conn->lastInsertId();
            $this->sendSuccess(['prescription_id' => $prescription_id], 'Prescription created successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to create prescription: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        $prescription_id = $_GET['id'] ?? null;
        if (!$prescription_id) {
            $this->sendError('Prescription ID required');
        }
        
        // Check if user can access this prescription
        if (!$this->canAccessPrescription($prescription_id)) {
            $this->sendError('Access denied to update this prescription', 403);
        }
        
        // Only the prescribing doctor or admin can update prescriptions
        if (!$this->isAdmin() && !$this->isPrescribingDoctor($prescription_id)) {
            $this->sendError('Only the prescribing doctor or admin can update prescriptions', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $update_fields = [];
            $params = [':prescription_id' => $prescription_id];
            
            $allowed_fields = ['diagnosis', 'medications', 'instructions', 'follow_up_date'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($update_fields)) {
                $this->sendError('No valid fields to update');
            }
            
            $query = "UPDATE prescriptions SET " . implode(', ', $update_fields) . " WHERE id = :prescription_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $this->sendSuccess(null, 'Prescription updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update prescription: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        // Only admin can delete prescriptions
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can delete prescriptions', 403);
        }
        
        $prescription_id = $_GET['id'] ?? null;
        if (!$prescription_id) {
            $this->sendError('Prescription ID required');
        }
        
        try {
            $query = "DELETE FROM prescriptions WHERE id = :prescription_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':prescription_id', $prescription_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'Prescription deleted successfully');
            } else {
                $this->sendError('Prescription not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete prescription: ' . $e->getMessage());
        }
    }
    
    private function canAccessPrescription($prescription_id) {
        if ($this->isAdmin()) {
            return true; // Admin can access all prescriptions
        }
        
        if ($this->isDoctor()) {
            // Doctor can access prescriptions they created
            $query = "SELECT COUNT(*) FROM prescriptions p 
                     JOIN doctors d ON p.doctor_id = d.doctor_id 
                     WHERE p.id = :prescription_id AND d.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':prescription_id', $prescription_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        if ($this->isPatient()) {
            // Patient can access their own prescriptions
            $query = "SELECT COUNT(*) FROM prescriptions p 
                     JOIN patients pt ON p.patient_id = pt.patient_id 
                     WHERE p.id = :prescription_id AND pt.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':prescription_id', $prescription_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }
    
    private function isPrescribingDoctor($prescription_id) {
        $query = "SELECT COUNT(*) FROM prescriptions p 
                 JOIN doctors d ON p.doctor_id = d.doctor_id 
                 WHERE p.id = :prescription_id AND d.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prescription_id', $prescription_id);
        $stmt->bindParam(':user_id', $this->getCurrentUserId());
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}

// Initialize and handle the request
$api = new PrescriptionsApi();
$api->handleRequest();
?>