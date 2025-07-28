<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];
    $userRole = $_SESSION['role'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'types') {
            // Get all vital types
            $query = "SELECT * FROM vital_types WHERE is_active = 1 ORDER BY is_default DESC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $types
            ]);

        } elseif ($action === 'patient_vitals') {
            $patientId = $_GET['patient_id'] ?? null;
            
            if (!$patientId) {
                throw new Exception('Patient ID is required');
            }

            // Check access based on role
            if ($userRole === 'patient') {
                if ($patientId != $_SESSION['patient_id']) {
                    throw new Exception('Access denied');
                }
            } elseif ($userRole === 'doctor') {
                // Check if patient is assigned to this doctor or has appointment
                $checkQuery = "SELECT COUNT(*) FROM patients p 
                              LEFT JOIN appointments a ON p.patient_id = a.patient_id 
                              WHERE p.patient_id = ? AND (p.assigned_doctor_id = ? OR a.doctor_id = ?)";
                $stmt = $conn->prepare($checkQuery);
                $stmt->execute([$patientId, $_SESSION['doctor_id'], $_SESSION['doctor_id']]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Access denied - Patient not assigned');
                }
            }

            // Get patient vitals with vital type info
            $query = "SELECT pv.*, vt.name as vital_name, vt.unit, vt.normal_range_min, vt.normal_range_max,
                             CONCAT(u.first_name, ' ', u.last_name) as recorded_by
                      FROM patient_vitals pv
                      JOIN vital_types vt ON pv.vital_type_id = vt.id
                      JOIN users u ON pv.recorded_by_user_id = u.id
                      WHERE pv.patient_id = ?
                      ORDER BY pv.recorded_date DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$patientId]);
            $vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by vital type for better display
            $groupedVitals = [];
            foreach ($vitals as $vital) {
                $typeName = $vital['vital_name'];
                if (!isset($groupedVitals[$typeName])) {
                    $groupedVitals[$typeName] = [
                        'type_info' => [
                            'name' => $vital['vital_name'],
                            'unit' => $vital['unit'],
                            'normal_range_min' => $vital['normal_range_min'],
                            'normal_range_max' => $vital['normal_range_max']
                        ],
                        'records' => []
                    ];
                }
                $groupedVitals[$typeName]['records'][] = [
                    'id' => $vital['id'],
                    'value' => $vital['value'],
                    'recorded_date' => $vital['recorded_date'],
                    'recorded_by' => $vital['recorded_by'],
                    'notes' => $vital['notes']
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => $groupedVitals
            ]);

        } elseif ($action === 'latest_vitals') {
            $patientId = $_GET['patient_id'] ?? ($_SESSION['role'] === 'patient' ? $_SESSION['patient_id'] : null);
            
            if (!$patientId) {
                throw new Exception('Patient ID is required');
            }

            // Get latest vitals for each type
            $query = "SELECT pv.*, vt.name as vital_name, vt.unit, vt.normal_range_min, vt.normal_range_max
                      FROM patient_vitals pv
                      JOIN vital_types vt ON pv.vital_type_id = vt.id
                      WHERE pv.patient_id = ? AND pv.id IN (
                          SELECT MAX(id) FROM patient_vitals 
                          WHERE patient_id = ? 
                          GROUP BY vital_type_id
                      )
                      ORDER BY vt.name";
            $stmt = $conn->prepare($query);
            $stmt->execute([$patientId, $patientId]);
            $latestVitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $latestVitals
            ]);
        }

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'add_vital';

        if ($action === 'add_vital') {
            // Only doctors and admin can add vitals
            if (!in_array($userRole, ['admin', 'doctor'])) {
                throw new Exception('Only doctors and admin can add vitals');
            }

            $patientId = $input['patient_id'] ?? null;
            $vitalTypeId = $input['vital_type_id'] ?? null;
            $value = $input['value'] ?? null;
            $notes = $input['notes'] ?? null;

            if (!$patientId || !$vitalTypeId || !$value) {
                throw new Exception('Patient ID, vital type, and value are required');
            }

            // Insert vital record
            $query = "INSERT INTO patient_vitals (patient_id, vital_type_id, value, recorded_by_user_id, notes)
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$patientId, $vitalTypeId, $value, $_SESSION['user_id'], $notes]);

            echo json_encode([
                'success' => true,
                'message' => 'Vital recorded successfully'
            ]);

        } elseif ($action === 'add_vital_type') {
            // Only admin can add custom vital types
            if ($userRole !== 'admin') {
                throw new Exception('Only admin can add custom vital types');
            }

            $name = $input['name'] ?? null;
            $unit = $input['unit'] ?? null;
            $normalRangeMin = $input['normal_range_min'] ?? null;
            $normalRangeMax = $input['normal_range_max'] ?? null;
            $description = $input['description'] ?? null;

            if (!$name) {
                throw new Exception('Vital type name is required');
            }

            $query = "INSERT INTO vital_types (name, unit, normal_range_min, normal_range_max, description, is_default)
                     VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$name, $unit, $normalRangeMin, $normalRangeMax, $description]);

            echo json_encode([
                'success' => true,
                'message' => 'Custom vital type added successfully'
            ]);
        }

    } elseif ($method === 'DELETE') {
        // Only admin can delete vital types
        if ($userRole !== 'admin') {
            throw new Exception('Only admin can delete vital types');
        }

        $vitalTypeId = $_GET['id'] ?? null;
        if (!$vitalTypeId) {
            throw new Exception('Vital type ID is required');
        }

        // Don't allow deletion of default vital types
        $checkQuery = "SELECT is_default FROM vital_types WHERE id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$vitalTypeId]);
        $vitalType = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vitalType) {
            throw new Exception('Vital type not found');
        }

        if ($vitalType['is_default']) {
            throw new Exception('Cannot delete default vital types');
        }

        // Soft delete by setting is_active to 0
        $query = "UPDATE vital_types SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$vitalTypeId]);

        echo json_encode([
            'success' => true,
            'message' => 'Vital type deleted successfully'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>