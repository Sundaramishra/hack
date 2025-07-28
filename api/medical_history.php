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
                
                if ($auth->hasRole('patient')) {
                    // Patient can see their medical history
                    $patient_id = $current_user['patient_id'];
                    $query = "SELECT mh.*, 
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                             FROM medical_history mh
                             JOIN doctors doc ON mh.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             WHERE mh.patient_id = :patient_id
                             ORDER BY mh.visit_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':patient_id', $patient_id);
                    $stmt->execute();
                } elseif ($auth->hasRole('doctor')) {
                    // Doctor can see medical history of their patients
                    $doctor_id = $current_user['doctor_id'];
                    $query = "SELECT mh.*, 
                             CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                             FROM medical_history mh
                             JOIN patients pt ON mh.patient_id = pt.id
                             JOIN users pat ON pt.user_id = pat.id
                             WHERE mh.doctor_id = :doctor_id
                             ORDER BY mh.visit_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':doctor_id', $doctor_id);
                    $stmt->execute();
                } elseif ($auth->hasRole('admin')) {
                    // Admin can see all medical history
                    $query = "SELECT mh.*, 
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                             CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                             FROM medical_history mh
                             JOIN doctors doc ON mh.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             JOIN patients pt ON mh.patient_id = pt.id
                             JOIN users pat ON pt.user_id = pat.id
                             ORDER BY mh.visit_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                } else {
                    throw new Exception('Insufficient permissions');
                }
                
                $medical_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $medical_history]);
                
            } elseif ($action === 'get') {
                $history_id = $_GET['id'] ?? null;
                if (!$history_id) {
                    throw new Exception('Medical history ID required');
                }
                
                $query = "SELECT mh.*, 
                         CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                         CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                         FROM medical_history mh
                         JOIN doctors doc ON mh.doctor_id = doc.id
                         JOIN users d ON doc.user_id = d.id
                         JOIN patients pt ON mh.patient_id = pt.id
                         JOIN users pat ON pt.user_id = pat.id
                         WHERE mh.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $history_id);
                $stmt->execute();
                $history = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($history) {
                    echo json_encode(['success' => true, 'data' => $history]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Medical history not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                // Only doctors can add medical history
                if (!$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['patient_id', 'visit_type', 'diagnosis', 'treatment'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                $current_user = $auth->getCurrentUser();
                $doctor_id = $current_user['doctor_id'];
                
                // Create medical history entry
                $history_query = "INSERT INTO medical_history (patient_id, doctor_id, visit_type, diagnosis, 
                                treatment, symptoms, notes, visit_date, status) 
                                VALUES (:patient_id, :doctor_id, :visit_type, :diagnosis, :treatment, 
                                :symptoms, :notes, :visit_date, 'completed')";
                $history_stmt = $conn->prepare($history_query);
                $history_stmt->bindParam(':patient_id', $data['patient_id']);
                $history_stmt->bindParam(':doctor_id', $doctor_id);
                $history_stmt->bindParam(':visit_type', $data['visit_type']);
                $history_stmt->bindParam(':diagnosis', $data['diagnosis']);
                $history_stmt->bindParam(':treatment', $data['treatment']);
                $history_stmt->bindParam(':symptoms', $data['symptoms'] ?? '');
                $history_stmt->bindParam(':notes', $data['notes'] ?? '');
                $history_stmt->bindParam(':visit_date', $data['visit_date'] ?? date('Y-m-d'));
                $history_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Medical history added successfully']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                $data = json_decode(file_get_contents('php://input'), true);
                $history_id = $_GET['id'] ?? null;
                
                if (!$history_id) {
                    throw new Exception('Medical history ID required');
                }
                
                // Update medical history
                $update_query = "UPDATE medical_history SET 
                                visit_type = :visit_type,
                                diagnosis = :diagnosis,
                                treatment = :treatment,
                                symptoms = :symptoms,
                                notes = :notes,
                                visit_date = :visit_date,
                                status = :status
                                WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':visit_type', $data['visit_type']);
                $update_stmt->bindParam(':diagnosis', $data['diagnosis']);
                $update_stmt->bindParam(':treatment', $data['treatment']);
                $update_stmt->bindParam(':symptoms', $data['symptoms'] ?? '');
                $update_stmt->bindParam(':notes', $data['notes'] ?? '');
                $update_stmt->bindParam(':visit_date', $data['visit_date']);
                $update_stmt->bindParam(':status', $data['status']);
                $update_stmt->bindParam(':id', $history_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Medical history updated successfully']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Only doctors can delete medical history
                if (!$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $history_id = $_GET['id'] ?? null;
                
                if (!$history_id) {
                    throw new Exception('Medical history ID required');
                }
                
                // Soft delete medical history
                $delete_query = "UPDATE medical_history SET status = 'deleted' WHERE id = :id";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $history_id);
                $delete_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Medical history deleted successfully']);
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