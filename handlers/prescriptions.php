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
        
        if ($action === 'print' && isset($_GET['id'])) {
            $prescriptionId = $_GET['id'];
            
            // Get prescription details for printing
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
                echo '<script>alert("Prescription not found or access denied"); window.close();</script>';
                exit;
            }
            
            // Get medicines for this prescription
            $medicineQuery = "SELECT * FROM prescription_medicines WHERE prescription_id = ? ORDER BY id";
            $stmt = $conn->prepare($medicineQuery);
            $stmt->execute([$prescriptionId]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output as downloadable HTML file
            $filename = 'Prescription_' . $prescription['prescription_number'] . '_' . date('Y-m-d') . '.html';
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Prescription - ' . htmlspecialchars($prescription['prescription_number']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .prescription-info { margin-bottom: 20px; }
        .patient-info, .doctor-info { display: inline-block; width: 48%; vertical-align: top; }
        .medicines { margin-top: 20px; }
        .medicine-item { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .print-btn { text-align: center; margin: 20px 0; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Medical Prescription</h1>
        <p>Prescription #: ' . htmlspecialchars($prescription['prescription_number']) . '</p>
        <p>Date: ' . date('F j, Y', strtotime($prescription['prescription_date'])) . '</p>
    </div>
    
    <div class="prescription-info">
        <div class="doctor-info">
            <h3>Doctor Information</h3>
            <p><strong>Name:</strong> Dr. ' . htmlspecialchars($prescription['doctor_name']) . '</p>
            <p><strong>Specialization:</strong> ' . htmlspecialchars($prescription['specialization']) . '</p>
            ' . ($prescription['license_number'] ? '<p><strong>License:</strong> ' . htmlspecialchars($prescription['license_number']) . '</p>' : '') . '
        </div>
        
        <div class="patient-info">
            <h3>Patient Information</h3>
            <p><strong>Name:</strong> ' . htmlspecialchars($prescription['patient_name']) . '</p>
            <p><strong>Patient ID:</strong> ' . htmlspecialchars($prescription['patient_code']) . '</p>
            ' . ($prescription['blood_group'] ? '<p><strong>Blood Group:</strong> ' . htmlspecialchars($prescription['blood_group']) . '</p>' : '') . '
            ' . ($prescription['allergies'] ? '<p><strong>Allergies:</strong> ' . htmlspecialchars($prescription['allergies']) . '</p>' : '') . '
        </div>
    </div>
    
    ' . ($prescription['diagnosis'] ? '<div><h3>Diagnosis</h3><p>' . htmlspecialchars($prescription['diagnosis']) . '</p></div>' : '') . '
    
    <div class="medicines">
        <h3>Prescribed Medicines</h3>';
        
        foreach ($medicines as $medicine) {
            echo '<div class="medicine-item">
                <h4>' . htmlspecialchars($medicine['medicine_name']) . '</h4>
                <p><strong>Dosage:</strong> ' . htmlspecialchars($medicine['dosage']) . '</p>
                <p><strong>Frequency:</strong> ' . htmlspecialchars($medicine['frequency']) . '</p>
                <p><strong>Duration:</strong> ' . htmlspecialchars($medicine['duration']) . '</p>
                <p><strong>Quantity:</strong> ' . ($medicine['quantity'] ?: 1) . '</p>
                ' . ($medicine['instructions'] ? '<p><strong>Instructions:</strong> ' . htmlspecialchars($medicine['instructions']) . '</p>' : '') . '
            </div>';
        }
        
        echo '</div>
    
    ' . ($prescription['notes'] ? '<div><h3>Additional Notes</h3><p>' . htmlspecialchars($prescription['notes']) . '</p></div>' : '') . '
    
    ' . ($prescription['follow_up_date'] ? '<div><h3>Follow-up Date</h3><p>' . date('F j, Y', strtotime($prescription['follow_up_date'])) . '</p></div>' : '') . '
    
    <div class="print-btn">
        <button onclick="window.print()">Print Prescription</button>
        <button onclick="window.close()">Close</button>
    </div>
    
    <script>
        // Auto-download message
        window.onload = function() {
            setTimeout(function() {
                alert('Prescription downloaded successfully! You can now print it from your downloads folder.');
                window.close();
            }, 1000);
        }
    </script>
</body>
</html>';
            exit;
            
        } elseif ($action === 'list') {
            // Get prescriptions based on user role
            if ($userRole === 'admin') {
                // Admin can see all prescriptions
                $query = "SELECT p.*, 
                                CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                                CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                                COALESCE(pt.patient_code, CONCAT('P', LPAD(pt.patient_id, 3, '0'))) as patient_code
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
                                COALESCE(pt.patient_code, CONCAT('P', LPAD(pt.patient_id, 3, '0'))) as patient_code
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
    error_log("Prescription handler error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ]);
}
?>