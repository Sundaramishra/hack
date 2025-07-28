<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT d.doctor_id, d.specialization, d.consultation_fee, d.available_from, d.available_to,
                     u.first_name, u.last_name, u.email
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE u.is_active = 1 AND d.status = 'active'
              ORDER BY u.first_name, u.last_name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $doctors
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading doctors: ' . $e->getMessage()
    ]);
}
?>