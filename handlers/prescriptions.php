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
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Rate limiting for prescription access attempts
    $rateLimitKey = 'prescription_attempts_' . $userId;
    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'timestamp' => time()];
    }
    
    // Reset counter every 5 minutes
    if (time() - $_SESSION[$rateLimitKey]['timestamp'] > 300) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'timestamp' => time()];
    }
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'print' && isset($_GET['id'])) {
            $prescriptionId = $_GET['id'];
            
            // Get prescription details for printing
            $query = "SELECT p.*, 
                            CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                            CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                            d.specialization, d.license_number,
                            COALESCE(pt.patient_code, CONCAT('P', LPAD(pt.patient_id, 3, '0'))) as patient_code,
                            pt.blood_group, pt.allergies
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
            
            // Additional security check - verify user has permission to view this prescription
            if ($userRole === 'patient' && $prescription['patient_id'] != $_SESSION['patient_id']) {
                error_log("SECURITY ALERT: Patient ID {$_SESSION['patient_id']} attempted to access prescription ID {$prescriptionId} belonging to patient ID {$prescription['patient_id']}");
                echo '<script>alert("Access denied - This prescription does not belong to you"); window.close();</script>';
                exit;
            }
            
            if ($userRole === 'doctor' && $prescription['doctor_id'] != $_SESSION['doctor_id']) {
                error_log("SECURITY ALERT: Doctor ID {$_SESSION['doctor_id']} attempted to access prescription ID {$prescriptionId} belonging to doctor ID {$prescription['doctor_id']}");
                echo '<script>alert("Access denied - This prescription was not issued by you"); window.close();</script>';
                exit;
            }
            
            // Get medicines for this prescription
            $medicineQuery = "SELECT * FROM prescription_medicines WHERE prescription_id = ? ORDER BY id";
            $stmt = $conn->prepare($medicineQuery);
            $stmt->execute([$prescriptionId]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Use HTML optimized for PDF printing
            header('Content-Type: text/html');
        
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Prescription - ' . htmlspecialchars($prescription['prescription_number']) . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: white;
            color: black;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .prescription-info { 
            margin-bottom: 20px; 
            overflow: hidden;
        }
        .patient-info, .doctor-info { 
            display: inline-block; 
            width: 48%; 
            vertical-align: top; 
            margin-bottom: 15px;
        }
        .medicines { 
            margin-top: 20px; 
        }
        .medicine-item { 
            border: 1px solid #ddd; 
            padding: 10px; 
            margin-bottom: 10px; 
            page-break-inside: avoid;
        }
        .print-btn { 
            text-align: center; 
            margin: 20px 0; 
        }
        .print-btn button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        @media print { 
            .print-btn { display: none; }
            body { margin: 0; }
            .header { border-bottom: 2px solid #000; }
        }
        @page {
            size: A4;
            margin: 1in;
        }
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
        <button onclick="printToPDF()">Save as PDF</button>
        <button onclick="window.close()">Close</button>
    </div>
    
    <script>
        function printToPDF() {
            // Open print dialog which allows saving as PDF
            window.print();
        }
        
        window.onload = function() {
            // Auto-open print dialog for PDF save
            setTimeout(function() {
                printToPDF();
            }, 500);
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
                $doctorId = $_SESSION['doctor_id'] ?? 0;
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
                $patientId = $_SESSION['patient_id'] ?? 0;
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
            } else {
                throw new Exception('Invalid user role');
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
                            COALESCE(pt.patient_code, CONCAT('P', LPAD(pt.patient_id, 3, '0'))) as patient_code,
                            pt.blood_group, pt.allergies
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
                $stmt->execute([$prescriptionId, $_SESSION['doctor_id'] ?? 0]);
            } elseif ($userRole === 'patient') {
                $query .= " AND p.patient_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$prescriptionId, $_SESSION['patient_id'] ?? 0]);
            } else {
                $stmt = $conn->prepare($query);
                $stmt->execute([$prescriptionId]);
            }
            
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prescription) {
                throw new Exception('Prescription not found or access denied');
            }
            
            // Additional security check - verify user has permission to view this prescription
            if ($userRole === 'patient' && $prescription['patient_id'] != ($_SESSION['patient_id'] ?? 0)) {
                error_log("SECURITY ALERT: Patient ID {$_SESSION['patient_id']} attempted to access prescription details ID {$prescriptionId} belonging to patient ID {$prescription['patient_id']}");
                throw new Exception('Access denied - This prescription does not belong to you');
            }
            
            if ($userRole === 'doctor' && $prescription['doctor_id'] != ($_SESSION['doctor_id'] ?? 0)) {
                error_log("SECURITY ALERT: Doctor ID {$_SESSION['doctor_id']} attempted to access prescription details ID {$prescriptionId} belonging to doctor ID {$prescription['doctor_id']}");
                throw new Exception('Access denied - This prescription was not issued by you');
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
        } else {
            throw new Exception('Invalid action');
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
        
        $doctorId = $_SESSION['doctor_id'] ?? 0;
        
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
    } else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>