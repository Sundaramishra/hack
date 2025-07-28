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
                    // Patient can see their prescriptions
                    $patient_id = $current_user['patient_id'];
                    $query = "SELECT p.*, 
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                             FROM prescriptions p
                             JOIN doctors doc ON p.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             WHERE p.patient_id = :patient_id
                             ORDER BY p.prescribed_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':patient_id', $patient_id);
                    $stmt->execute();
                } elseif ($auth->hasRole('doctor')) {
                    // Doctor can see prescriptions they wrote
                    $doctor_id = $current_user['doctor_id'];
                    $query = "SELECT p.*, 
                             CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                             FROM prescriptions p
                             JOIN patients pt ON p.patient_id = pt.id
                             JOIN users pat ON pt.user_id = pat.id
                             WHERE p.doctor_id = :doctor_id
                             ORDER BY p.prescribed_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':doctor_id', $doctor_id);
                    $stmt->execute();
                } elseif ($auth->hasRole('admin')) {
                    // Admin can see all prescriptions
                    $query = "SELECT p.*, 
                             CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                             CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                             FROM prescriptions p
                             JOIN doctors doc ON p.doctor_id = doc.id
                             JOIN users d ON doc.user_id = d.id
                             JOIN patients pt ON p.patient_id = pt.id
                             JOIN users pat ON pt.user_id = pat.id
                             ORDER BY p.prescribed_date DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                } else {
                    throw new Exception('Insufficient permissions');
                }
                
                $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $prescriptions]);
                
            } elseif ($action === 'get') {
                $prescription_id = $_GET['id'] ?? null;
                if (!$prescription_id) {
                    throw new Exception('Prescription ID required');
                }
                
                $query = "SELECT p.*, 
                         CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                         CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                         FROM prescriptions p
                         JOIN doctors doc ON p.doctor_id = doc.id
                         JOIN users d ON doc.user_id = d.id
                         JOIN patients pt ON p.patient_id = pt.id
                         JOIN users pat ON pt.user_id = pat.id
                         WHERE p.id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $prescription_id);
                $stmt->execute();
                $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($prescription) {
                    echo json_encode(['success' => true, 'data' => $prescription]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Prescription not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                // Only doctors can add prescriptions
                if (!$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['patient_id', 'medication', 'dosage', 'duration'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                $current_user = $auth->getCurrentUser();
                $doctor_id = $current_user['doctor_id'];
                
                // Create prescription
                $prescription_query = "INSERT INTO prescriptions (patient_id, doctor_id, medication, dosage, 
                                     duration, instructions, prescribed_date, status) 
                                     VALUES (:patient_id, :doctor_id, :medication, :dosage, :duration, 
                                     :instructions, NOW(), 'active')";
                $prescription_stmt = $conn->prepare($prescription_query);
                $prescription_stmt->bindParam(':patient_id', $data['patient_id']);
                $prescription_stmt->bindParam(':doctor_id', $doctor_id);
                $prescription_stmt->bindParam(':medication', $data['medication']);
                $prescription_stmt->bindParam(':dosage', $data['dosage']);
                $prescription_stmt->bindParam(':duration', $data['duration']);
                $prescription_stmt->bindParam(':instructions', $data['instructions'] ?? '');
                $prescription_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Prescription added successfully']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                $data = json_decode(file_get_contents('php://input'), true);
                $prescription_id = $_GET['id'] ?? null;
                
                if (!$prescription_id) {
                    throw new Exception('Prescription ID required');
                }
                
                // Update prescription
                $update_query = "UPDATE prescriptions SET 
                                medication = :medication,
                                dosage = :dosage,
                                duration = :duration,
                                instructions = :instructions,
                                status = :status
                                WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':medication', $data['medication']);
                $update_stmt->bindParam(':dosage', $data['dosage']);
                $update_stmt->bindParam(':duration', $data['duration']);
                $update_stmt->bindParam(':instructions', $data['instructions'] ?? '');
                $update_stmt->bindParam(':status', $data['status']);
                $update_stmt->bindParam(':id', $prescription_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Prescription updated successfully']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                // Only doctors can delete prescriptions
                if (!$auth->hasRole('doctor')) {
                    throw new Exception('Insufficient permissions');
                }
                
                $prescription_id = $_GET['id'] ?? null;
                
                if (!$prescription_id) {
                    throw new Exception('Prescription ID required');
                }
                
                // Soft delete prescription
                $delete_query = "UPDATE prescriptions SET status = 'discontinued' WHERE id = :id";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $prescription_id);
                $delete_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Prescription discontinued successfully']);
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