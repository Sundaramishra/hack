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
        
        if ($action === 'list') {
            // Get prescriptions based on user role
            if ($userRole === 'admin') {
                // Admin can see all prescriptions
                $query = "SELECT p.*, 
                                CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                                CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                                pt.patient_code
                         FROM prescriptions p
                         JOIN patients pt ON p.patient_id = pt.patient_id
                         JOIN users up ON pt.user_id = up.id
                         JOIN doctors d ON p.doctor_id = d.doctor_id
                         JOIN users ud ON d.user_id = ud.id
                         ORDER BY p.prescription_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
            } elseif ($userRole === 'doctor') {
                // Doctor can see only their prescriptions
                $doctorId = $_SESSION['doctor_id'];
                $query = "SELECT p.*, 
                                CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                                pt.patient_code
                         FROM prescriptions p
                         JOIN patients pt ON p.patient_id = pt.patient_id
                         JOIN users up ON pt.user_id = up.id
                         WHERE p.doctor_id = ?
                         ORDER BY p.prescription_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute([$doctorId]);
            } elseif ($userRole === 'patient') {
                // Patient can see only their prescriptions
                $patientId = $_SESSION['patient_id'];
                $query = "SELECT p.*, 
                                CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                                d.specialization
                         FROM prescriptions p
                         JOIN doctors d ON p.doctor_id = d.doctor_id
                         JOIN users ud ON d.user_id = ud.id
                         WHERE p.patient_id = ?
                         ORDER BY p.prescription_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute([$patientId]);
            }
            
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $prescriptions
            ]);
            
        } elseif ($action === 'details' && isset($_GET['id'])) {
            $prescriptionId = $_GET['id'];
            
            // Get prescription details with medicines
            $query = "SELECT p.*, 
                            CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                            CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                            d.specialization, d.license_number,
                            pt.patient_code, pt.blood_group, pt.allergies
                     FROM prescriptions p
                     JOIN patients pt ON p.patient_id = pt.patient_id
                     JOIN users up ON pt.user_id = up.id
                     JOIN doctors d ON p.doctor_id = d.doctor_id
                     JOIN users ud ON d.user_id = ud.id
                     WHERE p.id = ?";
            
            // Check access based on role
            if ($userRole === 'doctor') {
                $query .= " AND p.doctor_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$prescriptionId, $_SESSION['doctor_id']]);
            } elseif ($userRole === 'patient') {
                $query .= " AND p.patient_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$prescriptionId, $_SESSION['patient_id']]);
            } else {
                $stmt = $conn->prepare($query);
                $stmt->execute([$prescriptionId]);
            }
            
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prescription) {
                throw new Exception('Prescription not found or access denied');
            }
            
            // Get medicines for this prescription
            $medicineQuery = "SELECT * FROM prescription_medicines WHERE prescription_id = ? ORDER BY id";
            $stmt = $conn->prepare($medicineQuery);
            $stmt->execute([$prescriptionId]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $prescription['medicines'] = $medicines;
            
            echo json_encode([
                'success' => true,
                'data' => $prescription
            ]);
        }
        
    } elseif ($method === 'POST') {
        // Only doctors can create prescriptions
        if ($userRole !== 'doctor') {
            throw new Exception('Only doctors can create prescriptions');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $appointmentId = $input['appointment_id'] ?? null;
        $patientId = $input['patient_id'] ?? null;
        $diagnosis = $input['diagnosis'] ?? null;
        $notes = $input['notes'] ?? null;
        $followUpDate = $input['follow_up_date'] ?? null;
        $medicines = $input['medicines'] ?? [];
        
        if (!$appointmentId || !$patientId || empty($medicines)) {
            throw new Exception('Appointment ID, patient ID, and medicines are required');
        }
        
        $doctorId = $_SESSION['doctor_id'];
        
        // Generate prescription number
        $prescriptionNumber = 'RX' . str_pad($patientId, 3, '0', STR_PAD_LEFT) . '-' . date('Y') . '-' . rand(1000, 9999);
        
        $conn->beginTransaction();
        
        try {
            // Insert prescription
            $prescriptionQuery = "INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, prescription_number, prescription_date, diagnosis, notes, follow_up_date) 
                                 VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?)";
            $stmt = $conn->prepare($prescriptionQuery);
            $stmt->execute([$appointmentId, $patientId, $doctorId, $prescriptionNumber, $diagnosis, $notes, $followUpDate]);
            
            $prescriptionId = $conn->lastInsertId();
            
            // Insert medicines
            $medicineQuery = "INSERT INTO prescription_medicines (prescription_id, medicine_name, dosage, frequency, duration, instructions, quantity) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($medicineQuery);
            
            foreach ($medicines as $medicine) {
                $stmt->execute([
                    $prescriptionId,
                    $medicine['name'],
                    $medicine['dosage'],
                    $medicine['frequency'],
                    $medicine['duration'],
                    $medicine['instructions'] ?? null,
                    $medicine['quantity'] ?? 1
                ]);
            }
            
            // Update appointment with diagnosis
            $updateAppointment = "UPDATE appointments SET diagnosis = ?, status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($updateAppointment);
            $stmt->execute([$diagnosis, $appointmentId]);
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Prescription created successfully',
                'data' => [
                    'prescription_id' => $prescriptionId,
                    'prescription_number' => $prescriptionNumber
                ]
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>