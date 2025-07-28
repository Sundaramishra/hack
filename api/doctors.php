<?php
// Simple Doctors API
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
        $action = $_GET['action'] ?? 'list';
        
        if ($action == 'get' && isset($_GET['id'])) {
            // Get single doctor
            $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone 
                      FROM doctors d 
                      JOIN users u ON d.user_id = u.id 
                      WHERE d.doctor_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_GET['id']]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($doctor) {
                echo json_encode([
                    'success' => true,
                    'data' => $doctor
                ]);
            } else {
                throw new Exception('Doctor not found');
            }
            
        } else {
            // Get all doctors
            $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone 
                      FROM doctors d 
                      JOIN users u ON d.user_id = u.id 
                      WHERE u.is_active = 1
                      ORDER BY u.first_name, u.last_name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $doctors
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>