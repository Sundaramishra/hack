<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireRole('admin');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total doctors
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.is_active = 1");
    $stmt->execute();
    $totalDoctors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total patients
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM patients p JOIN users u ON p.user_id = u.id WHERE u.is_active = 1");
    $stmt->execute();
    $totalPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments");
    $stmt->execute();
    $totalAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_users' => $totalUsers,
            'total_doctors' => $totalDoctors,
            'total_patients' => $totalPatients,
            'total_appointments' => $totalAppointments
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading statistics: ' . $e->getMessage()
    ]);
}
?>