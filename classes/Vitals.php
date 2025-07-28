<?php
require_once 'config/database.php';

class Vitals {
    private $conn;
    private $table_name = "patient_vitals";
    private $vital_types_table = "vital_types";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Add vital record
    public function addVital($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (patient_id, vital_type_id, value, recorded_by_user_id, notes) 
                     VALUES (:patient_id, :vital_type_id, :value, :recorded_by_user_id, :notes)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':vital_type_id', $data['vital_type_id']);
            $stmt->bindParam(':value', $data['value']);
            $stmt->bindParam(':recorded_by_user_id', $data['recorded_by_user_id']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Vital record added successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to add vital record'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Get patient vitals
    public function getPatientVitals($patient_id, $vital_type_id = null, $days = 30) {
        $query = "SELECT pv.*, vt.name as vital_name, vt.unit, vt.normal_range_min, vt.normal_range_max,
                        u.first_name, u.last_name
                 FROM " . $this->table_name . " pv
                 JOIN " . $this->vital_types_table . " vt ON pv.vital_type_id = vt.id
                 JOIN users u ON pv.recorded_by_user_id = u.id
                 WHERE pv.patient_id = :patient_id 
                 AND pv.recorded_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $params = [':patient_id' => $patient_id, ':days' => $days];
        
        if ($vital_type_id) {
            $query .= " AND pv.vital_type_id = :vital_type_id";
            $params[':vital_type_id'] = $vital_type_id;
        }
        
        $query .= " ORDER BY pv.recorded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add status (normal, low, high) based on normal ranges
        foreach ($vitals as &$vital) {
            if ($vital['normal_range_min'] && $vital['normal_range_max']) {
                if ($vital['value'] < $vital['normal_range_min']) {
                    $vital['status'] = 'low';
                } elseif ($vital['value'] > $vital['normal_range_max']) {
                    $vital['status'] = 'high';
                } else {
                    $vital['status'] = 'normal';
                }
            } else {
                $vital['status'] = 'unknown';
            }
        }
        
        return $vitals;
    }
    
    // Get latest vitals for patient
    public function getLatestVitals($patient_id) {
        $query = "SELECT pv.*, vt.name as vital_name, vt.unit, vt.normal_range_min, vt.normal_range_max
                 FROM " . $this->table_name . " pv
                 JOIN " . $this->vital_types_table . " vt ON pv.vital_type_id = vt.id
                 WHERE pv.patient_id = :patient_id 
                 AND pv.id IN (
                     SELECT MAX(id) 
                     FROM " . $this->table_name . " 
                     WHERE patient_id = :patient_id 
                     GROUP BY vital_type_id
                 )
                 ORDER BY vt.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        
        $vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add status
        foreach ($vitals as &$vital) {
            if ($vital['normal_range_min'] && $vital['normal_range_max']) {
                if ($vital['value'] < $vital['normal_range_min']) {
                    $vital['status'] = 'low';
                } elseif ($vital['value'] > $vital['normal_range_max']) {
                    $vital['status'] = 'high';
                } else {
                    $vital['status'] = 'normal';
                }
            } else {
                $vital['status'] = 'unknown';
            }
        }
        
        return $vitals;
    }
    
    // Get vital trends for charts
    public function getVitalTrends($patient_id, $vital_type_id, $days = 30) {
        $query = "SELECT DATE(recorded_at) as date, AVG(value) as avg_value, 
                        MIN(value) as min_value, MAX(value) as max_value,
                        COUNT(*) as readings_count
                 FROM " . $this->table_name . " 
                 WHERE patient_id = :patient_id AND vital_type_id = :vital_type_id
                 AND recorded_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 GROUP BY DATE(recorded_at)
                 ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':vital_type_id', $vital_type_id);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all vital types
    public function getAllVitalTypes() {
        $query = "SELECT * FROM " . $this->vital_types_table . " 
                 WHERE is_active = 1 
                 ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Add new vital type (Admin only)
    public function addVitalType($data) {
        try {
            $query = "INSERT INTO " . $this->vital_types_table . " 
                     (name, unit, normal_range_min, normal_range_max, description) 
                     VALUES (:name, :unit, :normal_range_min, :normal_range_max, :description)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':normal_range_min', $data['normal_range_min']);
            $stmt->bindParam(':normal_range_max', $data['normal_range_max']);
            $stmt->bindParam(':description', $data['description']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Vital type added successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to add vital type'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Update vital type (Admin only)
    public function updateVitalType($id, $data) {
        try {
            $query = "UPDATE " . $this->vital_types_table . " 
                     SET name = :name, unit = :unit, normal_range_min = :normal_range_min, 
                         normal_range_max = :normal_range_max, description = :description
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':normal_range_min', $data['normal_range_min']);
            $stmt->bindParam(':normal_range_max', $data['normal_range_max']);
            $stmt->bindParam(':description', $data['description']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Vital type updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update vital type'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Delete vital type (Admin only)
    public function deleteVitalType($id) {
        try {
            $query = "UPDATE " . $this->vital_types_table . " SET is_active = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Vital type deactivated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to deactivate vital type'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Get vital statistics for dashboard
    public function getVitalStatistics($patient_id, $days = 7) {
        $query = "SELECT vt.name, COUNT(pv.id) as reading_count,
                        AVG(pv.value) as avg_value, MIN(pv.value) as min_value, MAX(pv.value) as max_value,
                        SUM(CASE WHEN pv.value < vt.normal_range_min THEN 1 ELSE 0 END) as low_count,
                        SUM(CASE WHEN pv.value > vt.normal_range_max THEN 1 ELSE 0 END) as high_count
                 FROM " . $this->table_name . " pv
                 JOIN " . $this->vital_types_table . " vt ON pv.vital_type_id = vt.id
                 WHERE pv.patient_id = :patient_id 
                 AND pv.recorded_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 GROUP BY vt.id, vt.name
                 ORDER BY vt.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Add multiple vitals at once (for vital signs form)
    public function addMultipleVitals($patient_id, $vitals_data, $recorded_by_user_id, $notes = '') {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (patient_id, vital_type_id, value, recorded_by_user_id, notes) 
                     VALUES (:patient_id, :vital_type_id, :value, :recorded_by_user_id, :notes)";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($vitals_data as $vital_type_id => $value) {
                if (!empty($value)) {
                    $stmt->bindParam(':patient_id', $patient_id);
                    $stmt->bindParam(':vital_type_id', $vital_type_id);
                    $stmt->bindParam(':value', $value);
                    $stmt->bindParam(':recorded_by_user_id', $recorded_by_user_id);
                    $stmt->bindParam(':notes', $notes);
                    $stmt->execute();
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Vitals recorded successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>