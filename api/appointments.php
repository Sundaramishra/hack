<?php
// Simple Appointments API
header('Content-Type: application/json');
session_start();

// Simple database connection
require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method == 'POST') {
        // Get JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }
        
        // Validate required fields
        if (empty($data['patient_id']) || empty($data['doctor_id']) || 
            empty($data['appointment_date']) || empty($data['appointment_time'])) {
            throw new Exception('Missing required fields');
        }
        
        // Insert appointment
        $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, created_by_user_id, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 1, 'scheduled', NOW())";
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([
            $data['patient_id'],
            $data['doctor_id'],
            $data['appointment_date'],
            $data['appointment_time'],
            $data['reason'] ?? 'General consultation',
            $data['notes'] ?? null
        ]);
        
        if ($result) {
            $appointment_id = $conn->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Appointment created successfully',
                'data' => ['appointment_id' => $appointment_id]
            ]);
        } else {
            throw new Exception('Failed to create appointment');
        }
        
    } elseif ($method == 'GET') {
        // Get appointments list
        $query = "SELECT a.*, 
                         CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                         CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                  FROM appointments a 
                  LEFT JOIN patients pt ON a.patient_id = pt.patient_id
                  LEFT JOIN users p ON pt.user_id = p.id
                  LEFT JOIN doctors dt ON a.doctor_id = dt.doctor_id  
                  LEFT JOIN users d ON dt.user_id = d.id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointments retrieved successfully',
            'data' => $appointments
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>