<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('patient');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $patientId = $_SESSION['patient_id'];
    
    // Get all appointments for this patient
    $query = "SELECT a.*, 
                     CONCAT('Dr. ', ud.first_name, ' ', ud.last_name) as doctor_name,
                     d.specialization
              FROM appointments a
              JOIN doctors d ON a.doctor_id = d.doctor_id
              JOIN users ud ON d.user_id = ud.id
              WHERE a.patient_id = ?
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$patientId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $appointments
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading appointments: ' . $e->getMessage()
    ]);
}
?>