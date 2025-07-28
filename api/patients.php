<?php
// Simple Patients API
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method == 'GET') {
        // Get all patients
        $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone 
                  FROM patients p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE u.is_active = 1
                  ORDER BY u.first_name, u.last_name";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $patients
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>