<?php
// Simplified appointments API for debugging
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering and session
ob_start();
session_start();

// Clean any unwanted output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    // Get and validate input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Connect to database
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Simple insert without conflict checking for now
    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, created_by_user_id, status, created_at)
             VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, :notes, 1, 'scheduled', NOW())";
    
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':patient_id', $data['patient_id']);
    $stmt->bindParam(':doctor_id', $data['doctor_id']);
    $stmt->bindParam(':appointment_date', $data['appointment_date']);
    $stmt->bindParam(':appointment_time', $data['appointment_time']);
    $stmt->bindParam(':reason', $data['reason'] ?? 'General consultation');
    $stmt->bindParam(':notes', $data['notes'] ?? null);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->lastInsertId();
        
        // Clean output buffer before sending response
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment created successfully',
            'data' => [
                'appointment_id' => $appointment_id,
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time']
            ]
        ]);
    } else {
        throw new Exception('Failed to insert appointment');
    }
    
} catch (Exception $e) {
    // Clean output buffer and send error
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'input_received' => isset($input) ? strlen($input) : 0,
            'data_parsed' => isset($data) ? true : false,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}
?>