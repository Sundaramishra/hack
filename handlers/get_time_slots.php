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
    $stmt = $conn->prepare("SELECT available_from, available_to, consultation_duration FROM doctors WHERE doctor_id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        throw new Exception('Doctor not found');
    }
    
    // Get existing appointments for this doctor on this date
    $stmt = $conn->prepare("SELECT appointment_time, duration FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
    $stmt->execute([$doctorId, $date]);
    $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate time slots
    $slots = [];
    $startTime = strtotime($doctor['available_from']);
    $endTime = strtotime($doctor['available_to']);
    $slotDuration = $doctor['consultation_duration'] * 60; // Convert to seconds
    
    for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
        $timeStr = date('H:i:s', $time);
        $timeDisplay = date('H:i', $time);
        
        // Check if this slot is available
        $available = true;
        foreach ($bookedSlots as $booked) {
            $bookedStart = strtotime($booked['appointment_time']);
            $bookedEnd = $bookedStart + ($booked['duration'] * 60);
            
            // Check for overlap
            if ($time < $bookedEnd && ($time + $slotDuration) > $bookedStart) {
                $available = false;
                break;
            }
        }
        
        // Don't allow booking in the past
        if ($date === date('Y-m-d') && $time <= time()) {
            $available = false;
        }
        
        $slots[] = [
            'time' => $timeDisplay,
            'value' => $timeStr,
            'available' => $available
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