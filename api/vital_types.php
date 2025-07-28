<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();

// Check if user is logged in and has admin role
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
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
                // Get all vital types
                $query = "SELECT * FROM vital_types ORDER BY name";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $vital_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $vital_types]);
            } elseif ($action === 'get') {
                $vital_type_id = $_GET['id'] ?? null;
                if (!$vital_type_id) {
                    throw new Exception('Vital type ID required');
                }
                
                $query = "SELECT * FROM vital_types WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $vital_type_id);
                $stmt->execute();
                $vital_type = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($vital_type) {
                    echo json_encode(['success' => true, 'data' => $vital_type]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Vital type not found']);
                }
            }
            break;
            
        case 'POST':
            if ($action === 'add') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                $required_fields = ['name', 'unit', 'normal_range_min', 'normal_range_max'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Check if vital type name already exists
                $check_query = "SELECT id FROM vital_types WHERE name = :name";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(':name', $data['name']);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    throw new Exception('Vital type name already exists');
                }
                
                // Create vital type
                $vital_type_query = "INSERT INTO vital_types (name, unit, normal_range_min, normal_range_max, 
                                   description, is_active) 
                                   VALUES (:name, :unit, :normal_range_min, :normal_range_max, :description, 1)";
                $vital_type_stmt = $conn->prepare($vital_type_query);
                $vital_type_stmt->bindParam(':name', $data['name']);
                $vital_type_stmt->bindParam(':unit', $data['unit']);
                $vital_type_stmt->bindParam(':normal_range_min', $data['normal_range_min']);
                $vital_type_stmt->bindParam(':normal_range_max', $data['normal_range_max']);
                $vital_type_stmt->bindParam(':description', $data['description'] ?? '');
                $vital_type_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Vital type added successfully']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                $data = json_decode(file_get_contents('php://input'), true);
                $vital_type_id = $_GET['id'] ?? null;
                
                if (!$vital_type_id) {
                    throw new Exception('Vital type ID required');
                }
                
                // Update vital type
                $update_query = "UPDATE vital_types SET 
                                name = :name,
                                unit = :unit,
                                normal_range_min = :normal_range_min,
                                normal_range_max = :normal_range_max,
                                description = :description
                                WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':name', $data['name']);
                $update_stmt->bindParam(':unit', $data['unit']);
                $update_stmt->bindParam(':normal_range_min', $data['normal_range_min']);
                $update_stmt->bindParam(':normal_range_max', $data['normal_range_max']);
                $update_stmt->bindParam(':description', $data['description'] ?? '');
                $update_stmt->bindParam(':id', $vital_type_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Vital type updated successfully']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                $vital_type_id = $_GET['id'] ?? null;
                
                if (!$vital_type_id) {
                    throw new Exception('Vital type ID required');
                }
                
                // Soft delete vital type
                $delete_query = "UPDATE vital_types SET is_active = 0 WHERE id = :id";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $vital_type_id);
                $delete_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Vital type deleted successfully']);
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