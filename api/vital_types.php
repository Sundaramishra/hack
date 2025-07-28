<?php
/**
 * Secure Vital Types API for Hospital CRM
 * Requires authentication and role-based access
 */

require_once __DIR__ . '/ApiBase.php';

class VitalTypesApi extends ApiBase {
    
    public function __construct() {
        // Allow admin, doctor, and patient roles (patients can view vital types)
        parent::__construct(['admin', 'doctor', 'patient']);
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        $this->logAccess("vital_types_api_$method" . ($action ? "_$action" : ""));
        
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
                $this->getVitalTypesList();
                break;
            case 'get':
                $this->getVitalType();
                break;
            case 'active':
                $this->getActiveVitalTypes();
                break;
            default:
                $this->getVitalTypesList();
        }
    }
    
    private function getVitalTypesList() {
        if ($this->isAdmin()) {
            // Admin can see all vital types including inactive ones
            $query = "SELECT * FROM vital_types ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
        } else {
            // Doctors and patients can only see active vital types
            $query = "SELECT * FROM vital_types WHERE is_active = 1 ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        }
        
        $vital_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($vital_types, 'Vital types retrieved successfully');
    }
    
    private function getVitalType() {
        $vital_type_id = $_GET['id'] ?? null;
        if (!$vital_type_id) {
            $this->sendError('Vital type ID required');
        }
        
        if ($this->isAdmin()) {
            // Admin can see any vital type
            $query = "SELECT * FROM vital_types WHERE id = :vital_type_id";
        } else {
            // Others can only see active vital types
            $query = "SELECT * FROM vital_types WHERE id = :vital_type_id AND is_active = 1";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vital_type_id', $vital_type_id);
        $stmt->execute();
        
        $vital_type = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$vital_type) {
            $this->sendError('Vital type not found', 404);
        }
        
        $this->sendSuccess($vital_type, 'Vital type retrieved successfully');
    }
    
    private function getActiveVitalTypes() {
        // All roles can get active vital types for recording vitals
        $query = "SELECT * FROM vital_types WHERE is_active = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $vital_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($vital_types, 'Active vital types retrieved successfully');
    }
    
    private function handlePost($action) {
        // Only admin can create vital types
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can create vital types', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $this->validateRequired($data, ['name', 'unit']);
        
        try {
            // Check if vital type name already exists
            $check_query = "SELECT COUNT(*) FROM vital_types WHERE name = :name";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':name', $data['name']);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $this->sendError('Vital type with this name already exists');
            }
            
            $query = "INSERT INTO vital_types (name, unit, normal_range_min, normal_range_max, description, is_active)
                     VALUES (:name, :unit, :normal_range_min, :normal_range_max, :description, :is_active)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':normal_range_min', $data['normal_range_min'] ?? null);
            $stmt->bindParam(':normal_range_max', $data['normal_range_max'] ?? null);
            $stmt->bindParam(':description', $data['description'] ?? null);
            $stmt->bindParam(':is_active', $data['is_active'] ?? 1);
            $stmt->execute();
            
            $vital_type_id = $this->conn->lastInsertId();
            $this->sendSuccess(['vital_type_id' => $vital_type_id], 'Vital type created successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to create vital type: ' . $e->getMessage());
        }
    }
    
    private function handlePut($action) {
        // Only admin can update vital types
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can update vital types', 403);
        }
        
        $vital_type_id = $_GET['id'] ?? null;
        if (!$vital_type_id) {
            $this->sendError('Vital type ID required');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $update_fields = [];
            $params = [':vital_type_id' => $vital_type_id];
            
            $allowed_fields = ['name', 'unit', 'normal_range_min', 'normal_range_max', 'description', 'is_active'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($update_fields)) {
                $this->sendError('No valid fields to update');
            }
            
            // Check if name already exists (excluding current record)
            if (isset($data['name'])) {
                $check_query = "SELECT COUNT(*) FROM vital_types WHERE name = :name AND id != :vital_type_id";
                $check_stmt = $this->conn->prepare($check_query);
                $check_stmt->bindParam(':name', $data['name']);
                $check_stmt->bindParam(':vital_type_id', $vital_type_id);
                $check_stmt->execute();
                
                if ($check_stmt->fetchColumn() > 0) {
                    $this->sendError('Vital type with this name already exists');
                }
            }
            
            $query = "UPDATE vital_types SET " . implode(', ', $update_fields) . " WHERE id = :vital_type_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $this->sendSuccess(null, 'Vital type updated successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to update vital type: ' . $e->getMessage());
        }
    }
    
    private function handleDelete($action) {
        // Only admin can delete vital types
        if (!$this->isAdmin()) {
            $this->sendError('Only admin can delete vital types', 403);
        }
        
        $vital_type_id = $_GET['id'] ?? null;
        if (!$vital_type_id) {
            $this->sendError('Vital type ID required');
        }
        
        try {
            // Check if vital type is being used in patient_vitals
            $usage_query = "SELECT COUNT(*) FROM patient_vitals WHERE vital_type_id = :vital_type_id";
            $usage_stmt = $this->conn->prepare($usage_query);
            $usage_stmt->bindParam(':vital_type_id', $vital_type_id);
            $usage_stmt->execute();
            
            if ($usage_stmt->fetchColumn() > 0) {
                // If vital type is in use, deactivate instead of delete
                $query = "UPDATE vital_types SET is_active = 0 WHERE id = :vital_type_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':vital_type_id', $vital_type_id);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $this->sendSuccess(null, 'Vital type deactivated successfully (cannot delete as it is in use)');
                } else {
                    $this->sendError('Vital type not found', 404);
                }
            } else {
                // Safe to delete if not in use
                $query = "DELETE FROM vital_types WHERE id = :vital_type_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':vital_type_id', $vital_type_id);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $this->sendSuccess(null, 'Vital type deleted successfully');
                } else {
                    $this->sendError('Vital type not found', 404);
                }
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete vital type: ' . $e->getMessage());
        }
    }
}

// Initialize and handle the request
$api = new VitalTypesApi();
$api->handleRequest();
?>