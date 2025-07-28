<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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
                $current_user = $auth->getCurrentUser();
                
                if ($auth->hasRole('admin')) {
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
                // Only admin and doctors can add appointments
                if (!$auth->hasRole('admin') && !$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'appointment_type'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Check if doctor is available at this time
                $check_query = "SELECT COUNT(*) as count FROM appointments 
                               WHERE doctor_id = :doctor_id AND appointment_date = :date 
                               AND appointment_time = :time AND status != 'cancelled'";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(':doctor_id', $data['doctor_id']);
                $check_stmt->bindParam(':date', $data['appointment_date']);
                $check_stmt->bindParam(':time', $data['appointment_time']);
                $check_stmt->execute();
                
                $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
                if ($result['count'] > 0) {
                    throw new Exception('Doctor is not available at this time');
                }
                
                // Create appointment
                $appointment_query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, 
                                    appointment_time, appointment_type, status, notes, created_at) 
                                    VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, 
                                    :appointment_type, 'scheduled', :notes, NOW())";
                $appointment_stmt = $conn->prepare($appointment_query);
                $appointment_stmt->bindParam(':patient_id', $data['patient_id']);
                $appointment_stmt->bindParam(':doctor_id', $data['doctor_id']);
                $appointment_stmt->bindParam(':appointment_date', $data['appointment_date']);
                $appointment_stmt->bindParam(':appointment_time', $data['appointment_time']);
                $appointment_stmt->bindParam(':appointment_type', $data['appointment_type']);
                $appointment_stmt->bindParam(':notes', $data['notes'] ?? '');
                $appointment_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully']);
                
            } elseif ($action === 'update_status') {
                $appointment_id = $_GET['id'] ?? null;
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                if (empty($data['status'])) {
                    throw new Exception('Status is required');
                }
                
                // Update appointment status
                $update_query = "UPDATE appointments SET status = :status, updated_at = NOW() WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':status', $data['status']);
                $update_stmt->bindParam(':id', $appointment_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                $appointment_id = $_GET['id'] ?? null;
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                // Update appointment
                $update_query = "UPDATE appointments SET 
                                appointment_date = :appointment_date,
                                appointment_time = :appointment_time,
                                appointment_type = :appointment_type,
                                notes = :notes,
                                updated_at = NOW()
                                WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':appointment_date', $data['appointment_date']);
                $update_stmt->bindParam(':appointment_time', $data['appointment_time']);
                $update_stmt->bindParam(':appointment_type', $data['appointment_type']);
                $update_stmt->bindParam(':notes', $data['notes'] ?? '');
                $update_stmt->bindParam(':id', $appointment_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Only admin can delete appointments
                if (!$auth->hasRole('admin')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $appointment_id = $_GET['id'] ?? null;
                
                if (!$appointment_id) {
                    throw new Exception('Appointment ID required');
                }
                
                // Soft delete appointment
                $delete_query = "UPDATE appointments SET status = 'cancelled', updated_at = NOW() WHERE id = :id";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $appointment_id);
                $delete_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
            }
            break;
            
        default:
            throw new Exception('Invalid method');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>