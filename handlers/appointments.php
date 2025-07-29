<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];
    $userRole = $_SESSION['role'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'list') {
            // Get appointments based on user role
            if ($userRole === 'admin') {
                // Admin can see all appointments
                $query = "SELECT a.*, 
                                CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                                CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                                d.specialization,
                                p.patient_code
                         FROM appointments a
                         JOIN patients p ON a.patient_id = p.patient_id
                         JOIN users up ON p.user_id = up.id
                         JOIN doctors d ON a.doctor_id = d.doctor_id
                         JOIN users ud ON d.user_id = ud.id
                         ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
            } elseif ($userRole === 'doctor') {
                // Doctor can see only their appointments
                $doctorId = $_SESSION['doctor_id'];
                $query = "SELECT a.*, 
                                CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                                p.patient_code, p.blood_group, p.allergies
                         FROM appointments a
                         JOIN patients p ON a.patient_id = p.patient_id
                         JOIN users up ON p.user_id = up.id
                         WHERE a.doctor_id = ?
                         ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute([$doctorId]);
            } elseif ($userRole === 'patient') {
                // Patient can see only their appointments
                $patientId = $_SESSION['patient_id'];
                $query = "SELECT a.*, 
                                CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                                d.specialization
                         FROM appointments a
                         JOIN doctors d ON a.doctor_id = d.doctor_id
                         JOIN users ud ON d.user_id = ud.id
                         WHERE a.patient_id = ?
                         ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute([$patientId]);
            }

            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $appointments
            ]);

        } elseif ($action === 'available_slots') {
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
            $availableDays = explode(',', $doctor['available_days']);
            if (!in_array($dayOfWeek, $availableDays)) {
                echo json_encode([
                    'success' => true,
                    'data' => []
                ]);
                return;
            }

            // Get existing appointments for this date
            $appointmentQuery = "SELECT appointment_time, duration FROM appointments 
                               WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('cancelled')";
            $stmt = $conn->prepare($appointmentQuery);
            $stmt->execute([$doctorId, $date]);
            $existingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Generate time slots
            $slots = [];
            $startTime = strtotime($doctor['available_from']);
            $endTime = strtotime($doctor['available_to']);
            $duration = $doctor['consultation_duration'] * 60; // Convert to seconds

            for ($time = $startTime; $time < $endTime; $time += $duration) {
                $slotTime = date('H:i:s', $time);
                $slotEndTime = date('H:i:s', $time + $duration);
                
                // Check if this slot conflicts with existing appointments
                $isAvailable = true;
                foreach ($existingAppointments as $appointment) {
                    $appointmentStart = strtotime($appointment['appointment_time']);
                    $appointmentEnd = $appointmentStart + ($appointment['duration'] * 60);
                    
                    if (($time >= $appointmentStart && $time < $appointmentEnd) ||
                        ($time + $duration > $appointmentStart && $time + $duration <= $appointmentEnd)) {
                        $isAvailable = false;
                        break;
                    }
                }

                $slots[] = [
                    'time' => $slotTime,
                    'display_time' => date('g:i A', $time),
                    'end_time' => $slotEndTime,
                    'available' => $isAvailable
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => $slots
            ]);

        } elseif ($action === 'today_schedule') {
            // For doctors to see today's schedule
            if ($userRole !== 'doctor') {
                throw new Exception('Only doctors can view schedule');
            }

            $doctorId = $_SESSION['doctor_id'];
            $today = date('Y-m-d');

            $query = "SELECT a.*, 
                            CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                            p.patient_code, p.phone
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users up ON p.user_id = up.id
                     WHERE a.doctor_id = ? AND a.appointment_date = ?
                     ORDER BY a.appointment_time ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$doctorId, $today]);
            $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $schedule
            ]);
        }

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'book';

        if ($action === 'book' || $action === 'create') {
            $patientId = $input['patient_id'] ?? null;
            $doctorId = $input['doctor_id'] ?? null;
            $appointmentDate = $input['appointment_date'] ?? null;
            $appointmentTime = $input['appointment_time'] ?? null;
            $reason = $input['reason'] ?? null;
            $symptoms = $input['symptoms'] ?? null;
            $appointmentType = $input['appointment_type'] ?? 'consultation';
            $duration = $input['duration'] ?? 30;

            if (!$patientId || !$doctorId || !$appointmentDate || !$appointmentTime) {
                throw new Exception('Patient, doctor, date, and time are required');
            }

            // Check if slot is still available
            $checkQuery = "SELECT COUNT(*) FROM appointments 
                          WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
                          AND status NOT IN ('cancelled')";
            $stmt = $conn->prepare($checkQuery);
            $stmt->execute([$doctorId, $appointmentDate, $appointmentTime]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('This time slot is no longer available');
            }

            // Insert appointment
            $insertQuery = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, 
                                                    duration, appointment_type, reason, symptoms, created_by_user_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([
                $patientId, $doctorId, $appointmentDate, $appointmentTime,
                $duration, $appointmentType, $reason, $symptoms, $_SESSION['user_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'appointment_id' => $conn->lastInsertId()
            ]);

        } elseif ($action === 'update_status') {
            $appointmentId = $input['appointment_id'] ?? null;
            $status = $input['status'] ?? null;
            $diagnosis = $input['diagnosis'] ?? null;
            $notes = $input['notes'] ?? null;

            if (!$appointmentId || !$status) {
                throw new Exception('Appointment ID and status are required');
            }

            // Check access
            if ($userRole === 'doctor') {
                $checkQuery = "SELECT COUNT(*) FROM appointments WHERE id = ? AND doctor_id = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->execute([$appointmentId, $_SESSION['doctor_id']]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Access denied');
                }
            } elseif ($userRole === 'patient') {
                throw new Exception('Patients cannot update appointment status');
            }

            $updateQuery = "UPDATE appointments SET status = ?, diagnosis = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$status, $diagnosis, $notes, $appointmentId]);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);
        }

    } elseif ($method === 'PUT') {
        // Reschedule appointment
        $input = json_decode(file_get_contents('php://input'), true);
        $appointmentId = $input['appointment_id'] ?? null;
        $newDate = $input['new_date'] ?? null;
        $newTime = $input['new_time'] ?? null;

        if (!$appointmentId || !$newDate || !$newTime) {
            throw new Exception('Appointment ID, new date, and new time are required');
        }

        // Get current appointment details
        $currentQuery = "SELECT * FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($currentQuery);
        $stmt->execute([$appointmentId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            throw new Exception('Appointment not found');
        }

        // Check access
        if ($userRole === 'patient' && $appointment['patient_id'] != $_SESSION['patient_id']) {
            throw new Exception('Access denied');
        } elseif ($userRole === 'doctor' && $appointment['doctor_id'] != $_SESSION['doctor_id']) {
            throw new Exception('Access denied');
        }

        // Check if new slot is available
        $checkQuery = "SELECT COUNT(*) FROM appointments 
                      WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
                      AND id != ? AND status NOT IN ('cancelled')";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$appointment['doctor_id'], $newDate, $newTime, $appointmentId]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('The new time slot is not available');
        }

        // Update appointment
        $updateQuery = "UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'rescheduled' 
                       WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([$newDate, $newTime, $appointmentId]);

        echo json_encode([
            'success' => true,
            'message' => 'Appointment rescheduled successfully'
        ]);

    } elseif ($method === 'DELETE') {
        // Cancel appointment
        $appointmentId = $_GET['id'] ?? null;
        
        if (!$appointmentId) {
            throw new Exception('Appointment ID is required');
        }

        // Get appointment details
        $query = "SELECT * FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$appointmentId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            throw new Exception('Appointment not found');
        }

        // Check access
        if ($userRole === 'patient' && $appointment['patient_id'] != $_SESSION['patient_id']) {
            throw new Exception('Access denied');
        } elseif ($userRole === 'doctor' && $appointment['doctor_id'] != $_SESSION['doctor_id']) {
            throw new Exception('Access denied');
        }

        // Update status to cancelled instead of deleting
        $updateQuery = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([$appointmentId]);

        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>