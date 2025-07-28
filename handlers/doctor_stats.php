<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('doctor');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $doctorId = $_SESSION['doctor_id'];
    $today = date('Y-m-d');
    
    // Get today's appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
    $stmt->execute([$doctorId, $today]);
    $todayAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get total patients assigned to this doctor
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM patients WHERE assigned_doctor_id = ?");
    $stmt->execute([$doctorId]);
    $totalPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get pending appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND status = 'scheduled' AND appointment_date >= ?");
    $stmt->execute([$doctorId, $today]);
    $pendingAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get completed appointments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND status = 'completed'");
    $stmt->execute([$doctorId]);
    $completedAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get today's schedule
    $query = "SELECT a.*, 
                     CONCAT(up.first_name, ' ', up.last_name) as patient_name
              FROM appointments a
              JOIN patients p ON a.patient_id = p.patient_id
              JOIN users up ON p.user_id = up.id
              WHERE a.doctor_id = ? AND a.appointment_date = ?
              ORDER BY a.appointment_time ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$doctorId, $today]);
    $todaySchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'today_appointments' => $todayAppointments,
            'total_patients' => $totalPatients,
            'pending_appointments' => $pendingAppointments,
            'completed_appointments' => $completedAppointments,
            'today_schedule' => $todaySchedule
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading statistics: ' . $e->getMessage()
    ]);
}
?>