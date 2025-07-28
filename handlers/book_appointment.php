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
    $userId = $_SESSION['user_id'];
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $doctorId = $input['doctor_id'] ?? null;
    $appointmentDate = $input['appointment_date'] ?? null;
    $appointmentTime = $input['appointment_time'] ?? null;
    $appointmentType = $input['appointment_type'] ?? 'consultation';
    $reason = $input['reason'] ?? null;
    
    if (!$doctorId || !$appointmentDate || !$appointmentTime) {
        throw new Exception('Doctor, date, and time are required');
    }
    
    // Check if the time slot is still available
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
    $stmt->execute([$doctorId, $appointmentDate, $appointmentTime]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existing > 0) {
        throw new Exception('This time slot is no longer available');
    }
    
    // Get doctor's consultation duration
    $stmt = $conn->prepare("SELECT consultation_duration FROM doctors WHERE doctor_id = ?");
    $stmt->execute([$doctorId]);
    $duration = $stmt->fetch(PDO::FETCH_ASSOC)['consultation_duration'] ?? 30;
    
    // Insert the appointment
    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, duration, appointment_type, reason, status, created_by_user_id, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, NOW())";
    
    $stmt = $conn->prepare($query);
    $result = $stmt->execute([
        $patientId,
        $doctorId,
        $appointmentDate,
        $appointmentTime,
        $duration,
        $appointmentType,
        $reason,
        $userId
    ]);
    
    if ($result) {
        $appointmentId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'data' => ['appointment_id' => $appointmentId]
        ]);
    } else {
        throw new Exception('Failed to book appointment');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>