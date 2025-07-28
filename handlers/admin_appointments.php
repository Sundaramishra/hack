<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('admin');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'recent') {
        // Get recent appointments (last 10)
        $query = "SELECT a.*, 
                         CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                         CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name
                  FROM appointments a
                  JOIN patients p ON a.patient_id = p.patient_id
                  JOIN users up ON p.user_id = up.id
                  JOIN doctors d ON a.doctor_id = d.doctor_id
                  JOIN users ud ON d.user_id = ud.id
                  ORDER BY a.created_at DESC
                  LIMIT 10";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $appointments
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading appointments: ' . $e->getMessage()
    ]);
}
?>