<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $doctorId = $_GET['doctor_id'] ?? null;
    $date = $_GET['date'] ?? null;
    
    if (!$doctorId || !$date) {
        throw new Exception('Doctor ID and date are required');
    }
    
    // Get doctor's availability
    $doctorQuery = "SELECT available_from, available_to, consultation_duration, available_days 
                   FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($doctorQuery);
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        throw new Exception('Doctor not found');
    }

    // Check if doctor is available on this day
    $dayOfWeek = date('D', strtotime($date));
    $availableDays = explode(',', $doctor['available_days'] ?? 'Mon,Tue,Wed,Thu,Fri');
    
    if (!in_array($dayOfWeek, $availableDays)) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit;
    }

    // Get existing appointments for this date
    $appointmentQuery = "SELECT appointment_time, duration FROM appointments 
                       WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('cancelled')";
    $stmt = $conn->prepare($appointmentQuery);
    $stmt->execute([$doctorId, $date]);
    $existingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate time slots
    $slots = [];
    $startTime = strtotime($doctor['available_from'] ?? '09:00:00');
    $endTime = strtotime($doctor['available_to'] ?? '17:00:00');
    $duration = ($doctor['consultation_duration'] ?? 30) * 60; // Convert to seconds

    for ($time = $startTime; $time < $endTime; $time += $duration) {
        $slotTime = date('H:i:s', $time);
        $slotEndTime = date('H:i:s', $time + $duration);
        
        // Check if this slot conflicts with existing appointments
        $isAvailable = true;
        foreach ($existingAppointments as $appointment) {
            $appointmentStart = strtotime($appointment['appointment_time']);
            $appointmentEnd = $appointmentStart + (($appointment['duration'] ?? 30) * 60);
            
            if (($time >= $appointmentStart && $time < $appointmentEnd) ||
                ($time + $duration > $appointmentStart && $time + $duration <= $appointmentEnd)) {
                $isAvailable = false;
                break;
            }
        }
        
        $slots[] = [
            'time' => $slotTime,
            'available' => $isAvailable,
            'formatted_time' => date('g:i A', $time)
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $slots
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>