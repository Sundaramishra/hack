<?php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Vitals.php';

$auth = new Auth();
$vitals_manager = new Vitals();

// Check authentication
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$current_user = $auth->getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Record new vitals
            if (!isset($_POST['patient_id']) || !isset($_POST['vitals'])) {
                echo json_encode(['success' => false, 'message' => 'Patient ID and vitals data required']);
                break;
            }
            
            $patient_id = $_POST['patient_id'];
            $vitals_data = $_POST['vitals'];
            $notes = $_POST['notes'] ?? '';
            
            // Check if user can access this patient
            if (!$auth->canAccessPatient($patient_id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied to this patient']);
                break;
            }
            
            $result = $vitals_manager->addMultipleVitals($patient_id, $vitals_data, $current_user['id'], $notes);
            echo json_encode($result);
            break;
            
        case 'GET':
            $action = $_GET['action'] ?? 'patient_vitals';
            
            switch ($action) {
                case 'types':
                    // Get all vital types
                    $vital_types = $vitals_manager->getAllVitalTypes();
                    echo json_encode(['success' => true, 'vital_types' => $vital_types]);
                    break;
                    
                case 'patient_vitals':
                    $patient_id = $_GET['patient_id'] ?? null;
                    $vital_type_id = $_GET['vital_type_id'] ?? null;
                    $days = $_GET['days'] ?? 30;
                    
                    if (!$patient_id) {
                        echo json_encode(['success' => false, 'message' => 'Patient ID required']);
                        break;
                    }
                    
                    // Check if user can access this patient
                    if (!$auth->canAccessPatient($patient_id)) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied to this patient']);
                        break;
                    }
                    
                    $vitals = $vitals_manager->getPatientVitals($patient_id, $vital_type_id, $days);
                    echo json_encode(['success' => true, 'vitals' => $vitals]);
                    break;
                    
                case 'latest':
                    $patient_id = $_GET['patient_id'] ?? null;
                    
                    if (!$patient_id) {
                        echo json_encode(['success' => false, 'message' => 'Patient ID required']);
                        break;
                    }
                    
                    // Check if user can access this patient
                    if (!$auth->canAccessPatient($patient_id)) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied to this patient']);
                        break;
                    }
                    
                    $latest_vitals = $vitals_manager->getLatestVitals($patient_id);
                    echo json_encode(['success' => true, 'vitals' => $latest_vitals]);
                    break;
                    
                case 'trends':
                    $patient_id = $_GET['patient_id'] ?? null;
                    $vital_type_id = $_GET['vital_type_id'] ?? null;
                    $days = $_GET['days'] ?? 30;
                    
                    if (!$patient_id || !$vital_type_id) {
                        echo json_encode(['success' => false, 'message' => 'Patient ID and vital type ID required']);
                        break;
                    }
                    
                    // Check if user can access this patient
                    if (!$auth->canAccessPatient($patient_id)) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied to this patient']);
                        break;
                    }
                    
                    $trends = $vitals_manager->getVitalTrends($patient_id, $vital_type_id, $days);
                    echo json_encode(['success' => true, 'trends' => $trends]);
                    break;
                    
                case 'statistics':
                    $patient_id = $_GET['patient_id'] ?? null;
                    $days = $_GET['days'] ?? 30;
                    
                    if (!$patient_id) {
                        echo json_encode(['success' => false, 'message' => 'Patient ID required']);
                        break;
                    }
                    
                    // Check if user can access this patient
                    if (!$auth->canAccessPatient($patient_id)) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied to this patient']);
                        break;
                    }
                    
                    $statistics = $vitals_manager->getVitalStatistics($patient_id, $days);
                    echo json_encode(['success' => true, 'statistics' => $statistics]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'PUT':
            // Update vital type (Admin only)
            if (!$auth->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $vital_type_id = $input['id'] ?? 0;
            
            if (!$vital_type_id) {
                echo json_encode(['success' => false, 'message' => 'Vital type ID required']);
                break;
            }
            
            $result = $vitals_manager->updateVitalType($vital_type_id, $input);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete vital type (Admin only)
            if (!$auth->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $vital_type_id = $input['id'] ?? 0;
            
            if (!$vital_type_id) {
                echo json_encode(['success' => false, 'message' => 'Vital type ID required']);
                break;
            }
            
            $result = $vitals_manager->deleteVitalType($vital_type_id);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>