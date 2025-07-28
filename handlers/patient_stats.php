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
    $today = date('Y-m-d');
    
    // Get total appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ?");
    $stmt->execute([$patientId]);
    $totalAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get upcoming appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ? AND appointment_date >= ? AND status = 'scheduled'");
    $stmt->execute([$patientId, $today]);
    $upcomingAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get assigned doctor
    $query = "SELECT CONCAT('Dr. ', u.first_name, ' ', u.last_name) as doctor_name
              FROM patients p
              LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
              LEFT JOIN users u ON d.user_id = u.id
              WHERE p.patient_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$patientId]);
    $assignedDoctor = $stmt->fetch(PDO::FETCH_ASSOC)['doctor_name'] ?? null;
    
    // Get last vitals date
    $stmt = $conn->prepare("SELECT DATE(recorded_at) as last_date FROM vitals WHERE patient_id = ? ORDER BY recorded_at DESC LIMIT 1");
    $stmt->execute([$patientId]);
    $lastVitalsDate = $stmt->fetch(PDO::FETCH_ASSOC)['last_date'] ?? null;
    
    // Get upcoming appointments list
    $query = "SELECT a.*, 
                     CONCAT('Dr. ', ud.first_name, ' ', ud.last_name) as doctor_name
              FROM appointments a
              JOIN doctors d ON a.doctor_id = d.doctor_id
              JOIN users ud ON d.user_id = ud.id
              WHERE a.patient_id = ? AND a.appointment_date >= ? AND a.status = 'scheduled'
              ORDER BY a.appointment_date ASC, a.appointment_time ASC
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$patientId, $today]);
    $upcomingAppointmentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_appointments' => $totalAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'assigned_doctor' => $assignedDoctor,
            'last_vitals_date' => $lastVitalsDate,
            'upcoming_appointments_list' => $upcomingAppointmentsList
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading statistics: ' . $e->getMessage()
    ]);
}
?>