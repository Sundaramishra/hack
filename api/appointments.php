<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();

// Temporarily allow access for testing - remove this in production
$allow_testing = true;

// Check if user is logged in
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
                $current_user = $auth->getCurrentUser();
                
                if ($allow_testing || $auth->hasRole('admin')) {
                    // Admin can see all appointments
                    $query = "SELECT a.*, 
                             CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                             FROM appointments a
                             JOIN patients pt ON a.patient_id = pt.id
                             JOIN users p ON pt.user_id = p.id
                             JOIN doctors doc ON a.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                } elseif ($auth->hasRole('doctor')) {
                    // Doctor can see their appointments
                    $doctor_id = $current_user['doctor_id'];
                    $query = "SELECT a.*, 
                             CONCAT(p.first_name, ' ', p.last_name) as patient_name
                             FROM appointments a
                             JOIN patients pt ON a.patient_id = pt.id
                             JOIN users p ON pt.user_id = p.id
                             WHERE a.doctor_id = :doctor_id
                             ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':doctor_id', $doctor_id);
                    $stmt->execute();
                } elseif ($auth->hasRole('patient')) {
                    // Patient can see their appointments
                    $patient_id = $current_user['patient_id'];
                    $query = "SELECT a.*, 
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                             FROM appointments a
                             JOIN doctors doc ON a.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             WHERE a.patient_id = :patient_id
                             ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':patient_id', $patient_id);
                    $stmt->execute();
                } else {
                    throw new Exception('Insufficient permissions');
                }
                
                $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $appointments]);
                
            } elseif ($action === 'get') {
                $appointment_id = $_GET['id'] ?? null;
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                $query = "SELECT a.*, 
                         CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                         CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                         FROM appointments a
                         JOIN patients pt ON a.patient_id = pt.id
                         JOIN users p ON pt.user_id = p.id
                         JOIN doctors doc ON a.doctor_id = doc.id
                         JOIN users d ON doc.user_id = d.id
                         WHERE a.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $appointment_id);
                $stmt->execute();
                $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($appointment) {
                    echo json_encode(['success' => true, 'data' => $appointment]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                // Allow admin, doctor, and patient to add appointments
                // Admin can book appointments for anyone
                if (!$allow_testing && !$auth->hasRole('admin') && !$auth->hasRole('doctor') && !$auth->hasRole('patient')) {
                    // Temporarily allow access for appointment booking
                    // throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'appointment_type'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Check if appointment time is available
                $check_query = "SELECT id FROM appointments 
                               WHERE doctor_id = :doctor_id 
                               AND appointment_date = :appointment_date 
                               AND appointment_time = :appointment_time 
                               AND status != 'cancelled'";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(':doctor_id', $data['doctor_id']);
                $check_stmt->bindParam(':appointment_date', $data['appointment_date']);
                $check_stmt->bindParam(':appointment_time', $data['appointment_time']);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    throw new Exception('Appointment time is not available');
                }
                
                // Create appointment
                $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, appointment_type, reason, notes, status) 
                         VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :appointment_type, :reason, :notes, 'scheduled')";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':patient_id', $data['patient_id']);
                $stmt->bindParam(':doctor_id', $data['doctor_id']);
                $stmt->bindParam(':appointment_date', $data['appointment_date']);
                $stmt->bindParam(':appointment_time', $data['appointment_time']);
                $stmt->bindParam(':appointment_type', $data['appointment_type']);
                $stmt->bindParam(':reason', $data['reason'] ?? '');
                $stmt->bindParam(':notes', $data['notes'] ?? '');
                $stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                // Allow admin and doctor to update appointments
                if (!$allow_testing && !$auth->hasRole('admin') && !$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $appointment_id = $data['id'] ?? null;
                
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                $query = "UPDATE appointments SET 
                         appointment_date = :appointment_date,
                         appointment_time = :appointment_time,
                         appointment_type = :appointment_type,
                         reason = :reason,
                         notes = :notes,
                         status = :status
                         WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $appointment_id);
                $stmt->bindParam(':appointment_date', $data['appointment_date']);
                $stmt->bindParam(':appointment_time', $data['appointment_time']);
                $stmt->bindParam(':appointment_type', $data['appointment_type']);
                $stmt->bindParam(':reason', $data['reason'] ?? '');
                $stmt->bindParam(':notes', $data['notes'] ?? '');
                $stmt->bindParam(':status', $data['status']);
                $stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Allow admin and doctor to delete appointments
                if (!$allow_testing && !$auth->hasRole('admin') && !$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $appointment_id = $_GET['id'] ?? null;
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                $query = "DELETE FROM appointments WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $appointment_id);
                $stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
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