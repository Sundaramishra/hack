<?php
/**
 * Secure Appointments API for Hospital CRM
 * Requires authentication and role-based access
 */

// Disable error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

require_once __DIR__ . '/ApiBase.php';

class AppointmentsApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("appointments_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getAppointmentsList();
                break;
            case 'get':
                $this->getAppointment();
                break;
            case 'upcoming':
                $this->getUpcomingAppointments();
                break;
            default:
                $this->sendError('Invalid action');
        }
    }
    
    private function getAppointmentsList() {
        if ($this->isAdmin()) {
            // Admin can see all appointments
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
                     ORDER BY a.appointment_date DESC, a.appointment_time DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
        } elseif ($this->isDoctor()) {
            // Doctor can only see their appointments
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
                     WHERE doc.user_id = :user_id
                     ORDER BY a.appointment_date DESC, a.appointment_time DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            
        } elseif ($this->isPatient()) {
            // Patient can only see their appointments
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                     d.specialization as doctor_specialization
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
                     WHERE p.user_id = :user_id
                     ORDER BY a.appointment_date DESC, a.appointment_time DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
        }
        
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($appointments, 'Appointments retrieved successfully');
    }
    
    private function getAppointment() {
        $appointment_id = $_GET['id'] ?? null;
        if (!$appointment_id) {
            $this->sendError('Appointment ID required');
        }
        
        // Check if user can access this appointment
        if (!$this->canAccessAppointment($appointment_id)) {
            $this->sendError('Access denied to this appointment', 403);
        }
        
        $query = "SELECT a.*, 
                 CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                 CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                 d.specialization as doctor_specialization,
                 pu.email as patient_email, pu.phone as patient_phone,
                 du.email as doctor_email, du.phone as doctor_phone
                 FROM appointments a
                 JOIN patients p ON a.patient_id = p.patient_id
                 JOIN users pu ON p.user_id = pu.id
                 JOIN doctors doc ON a.doctor_id = doc.doctor_id
                 JOIN users du ON doc.user_id = du.id
                 LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
                 WHERE a.id = :appointment_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':appointment_id', $appointment_id);
        $stmt->execute();
        
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$appointment) {
            $this->sendError('Appointment not found', 404);
        }
        
        $this->sendSuccess($appointment, 'Appointment retrieved successfully');
    }
    
    private function getUpcomingAppointments() {
        $limit = $_GET['limit'] ?? 10;
        
        if ($this->isAdmin()) {
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     WHERE a.appointment_date >= CURDATE() AND a.status = 'scheduled'
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
        } elseif ($this->isDoctor()) {
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     WHERE doc.user_id = :user_id AND a.appointment_date >= CURDATE() AND a.status = 'scheduled'
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
        } elseif ($this->isPatient()) {
            $query = "SELECT a.*, 
                     CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                     CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users pu ON p.user_id = pu.id
                     JOIN doctors doc ON a.doctor_id = doc.doctor_id
                     JOIN users du ON doc.user_id = du.id
                     WHERE p.user_id = :user_id AND a.appointment_date >= CURDATE() AND a.status = 'scheduled'
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($appointments, 'Upcoming appointments retrieved successfully');
    }
    
    private function handlePost($action) {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time']);
        
        // Check permissions
        if ($this->isPatient()) {
            // Patient can only book appointments for themselves
            $patient_query = "SELECT patient_id FROM patients WHERE user_id = :user_id";
            $patient_stmt = $this->conn->prepare($patient_query);
            $patient_stmt->bindParam(':user_id', $this->getCurrentUserId());
            $patient_stmt->execute();
            $patient_data = $patient_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient_data || $patient_data['patient_id'] != $data['patient_id']) {
                $this->sendError('You can only book appointments for yourself', 403);
            }
        } elseif ($this->isDoctor()) {
            // Doctor can book appointments for their assigned patients
            if (!$this->canAccessPatient($data['patient_id'])) {
                $this->sendError('You can only book appointments for your assigned patients', 403);
            }
        }
        // Admin can book for anyone
        
        try {
            // Check for conflicting appointments
            $conflict_query = "SELECT COUNT(*) FROM appointments 
                              WHERE doctor_id = :doctor_id 
                              AND appointment_date = :appointment_date 
                              AND appointment_time = :appointment_time 
                              AND status != 'cancelled'";
            $conflict_stmt = $this->conn->prepare($conflict_query);
            $conflict_stmt->bindParam(':doctor_id', $data['doctor_id']);
            $conflict_stmt->bindParam(':appointment_date', $data['appointment_date']);
            $conflict_stmt->bindParam(':appointment_time', $data['appointment_time']);
            $conflict_stmt->execute();
            
            if ($conflict_stmt->fetchColumn() > 0) {
                $this->sendError('Doctor is not available at this time slot');
            }
            
            // Check if duration and appointment_type columns exist
            $check_columns = "SHOW COLUMNS FROM appointments LIKE 'duration'";
            $check_stmt = $this->conn->prepare($check_columns);
            $check_stmt->execute();
            $has_duration = $check_stmt->fetchColumn() !== false;
            
            // Create appointment with or without new columns
            if ($has_duration) {
                $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, created_by_user_id, status, duration, appointment_type)
                         VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, :notes, :created_by_user_id, 'scheduled', :duration, :appointment_type)";
                $stmt = $this->conn->prepare($query);
                
                $stmt->bindParam(':patient_id', $data['patient_id']);
                $stmt->bindParam(':doctor_id', $data['doctor_id']);
                $stmt->bindParam(':appointment_date', $data['appointment_date']);
                $stmt->bindParam(':appointment_time', $data['appointment_time']);
                $stmt->bindParam(':reason', $data['reason'] ?? null);
                $stmt->bindParam(':notes', $data['notes'] ?? null);
                $stmt->bindParam(':created_by_user_id', $this->getCurrentUserId());
                $stmt->bindParam(':duration', $data['duration'] ?? 30);
                $stmt->bindParam(':appointment_type', $data['appointment_type'] ?? 'consultation');
            } else {
                // Fallback to old schema without duration/appointment_type
                $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, created_by_user_id, status)
                         VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, :notes, :created_by_user_id, 'scheduled')";
                $stmt = $this->conn->prepare($query);
                
                $stmt->bindParam(':patient_id', $data['patient_id']);
                $stmt->bindParam(':doctor_id', $data['doctor_id']);
                $stmt->bindParam(':appointment_date', $data['appointment_date']);
                $stmt->bindParam(':appointment_time', $data['appointment_time']);
                $stmt->bindParam(':reason', $data['reason'] ?? null);
                $stmt->bindParam(':notes', $data['notes'] ?? null);
                $stmt->bindParam(':created_by_user_id', $this->getCurrentUserId());
            }
            
            $stmt->execute();
            
            $appointment_id = $this->conn->lastInsertId();
            $this->sendSuccess(['appointment_id' => $appointment_id], 'Appointment created successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to create appointment: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        $appointment_id = $_GET['id'] ?? null;
        if (!$appointment_id) {
            $this->sendError('Appointment ID required');
        }
        
        // Check if user can access this appointment
        if (!$this->canAccessAppointment($appointment_id)) {
            $this->sendError('Access denied to update this appointment', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $update_fields = [];
            $params = [':appointment_id' => $appointment_id];
            
            // Only allow certain fields to be updated based on role
            if ($this->isAdmin()) {
                $allowed_fields = ['appointment_date', 'appointment_time', 'reason', 'notes', 'status'];
            } elseif ($this->isDoctor()) {
                $allowed_fields = ['notes', 'status']; // Doctor can update notes and status
            } elseif ($this->isPatient()) {
                $allowed_fields = ['reason']; // Patient can only update reason before appointment
            }
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($update_fields)) {
                $this->sendError('No valid fields to update');
            }
            
            $query = "UPDATE appointments SET " . implode(', ', $update_fields) . " WHERE id = :appointment_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $this->sendSuccess(null, 'Appointment updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update appointment: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        $appointment_id = $_GET['id'] ?? null;
        if (!$appointment_id) {
            $this->sendError('Appointment ID required');
        }
        
        // Check if user can access this appointment
        if (!$this->canAccessAppointment($appointment_id)) {
            $this->sendError('Access denied to cancel this appointment', 403);
        }
        
        try {
            // Instead of deleting, mark as cancelled
            $query = "UPDATE appointments SET status = 'cancelled' WHERE id = :appointment_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendSuccess(null, 'Appointment cancelled successfully');
            } else {
                $this->sendError('Appointment not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to cancel appointment: ' . $e->getMessage());
        }
    }
    
    private function canAccessAppointment($appointment_id) {
        if ($this->isAdmin()) {
            return true; // Admin can access all appointments
        }
        
        if ($this->isDoctor()) {
            // Doctor can access appointments where they are the assigned doctor
            $query = "SELECT COUNT(*) FROM appointments a 
                     JOIN doctors d ON a.doctor_id = d.doctor_id 
                     WHERE a.id = :appointment_id AND d.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        if ($this->isPatient()) {
            // Patient can access their own appointments
            $query = "SELECT COUNT(*) FROM appointments a 
                     JOIN patients p ON a.patient_id = p.patient_id 
                     WHERE a.id = :appointment_id AND p.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->bindParam(':user_id', $this->getCurrentUserId());
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }
}

// Clean any unwanted output
ob_clean();

// Log the request for debugging
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'input' => file_get_contents('php://input'),
    'session' => session_id()
];
file_put_contents(__DIR__ . '/appointments_debug.log', json_encode($log_data) . "\n", FILE_APPEND);

// Initialize and handle the request
try {
    $api = new AppointmentsApi();
    $api->handleRequest();
} catch (Exception $e) {
    // Log the error
    file_put_contents(__DIR__ . '/appointments_debug.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Clean output buffer and send error
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'API Error: ' . $e->getMessage()
    ]);
}
?>