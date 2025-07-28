<?php
require_once 'config/database.php';

class Appointment {
    private $conn;
    private $table_name = "appointments";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Create new appointment
    public function createAppointment($data) {
        try {
            // Check if doctor is available at that time
            $availability_check = $this->checkDoctorAvailability($data['doctor_id'], $data['appointment_date'], $data['appointment_time']);
            if (!$availability_check['available']) {
                return ['success' => false, 'message' => $availability_check['message']];
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, created_by_user_id) 
                     VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, :notes, :created_by_user_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $data['patient_id']);
            $stmt->bindParam(':doctor_id', $data['doctor_id']);
            $stmt->bindParam(':appointment_date', $data['appointment_date']);
            $stmt->bindParam(':appointment_time', $data['appointment_time']);
            $stmt->bindParam(':reason', $data['reason']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':created_by_user_id', $data['created_by_user_id']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Appointment created successfully', 'appointment_id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create appointment'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Check doctor availability
    private function checkDoctorAvailability($doctor_id, $date, $time) {
        // Check if there's already an appointment at this time
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE doctor_id = :doctor_id AND appointment_date = :date AND appointment_time = :time 
                 AND status IN ('scheduled', 'completed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            return ['available' => false, 'message' => 'Doctor is not available at this time'];
        }
        
        return ['available' => true, 'message' => 'Doctor is available'];
    }
    
    // Get appointments by patient
    public function getAppointmentsByPatient($patient_id) {
        $query = "SELECT a.*, 
                        u.first_name as doctor_first_name, u.last_name as doctor_last_name,
                        d.specialization, d.department
                 FROM " . $this->table_name . " a
                 JOIN doctors d ON a.doctor_id = d.id
                 JOIN users u ON d.user_id = u.id
                 WHERE a.patient_id = :patient_id
                 ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get appointments by doctor
    public function getAppointmentsByDoctor($doctor_id) {
        $query = "SELECT a.*, 
                        u.first_name as patient_first_name, u.last_name as patient_last_name,
                        p.patient_id as patient_code, p.blood_group
                 FROM " . $this->table_name . " a
                 JOIN patients p ON a.patient_id = p.id
                 JOIN users u ON p.user_id = u.id
                 WHERE a.doctor_id = :doctor_id
                 ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all appointments (Admin only)
    public function getAllAppointments($status = null, $date = null) {
        $query = "SELECT a.*, 
                        u1.first_name as patient_first_name, u1.last_name as patient_last_name,
                        u2.first_name as doctor_first_name, u2.last_name as doctor_last_name,
                        p.patient_id as patient_code, d.specialization
                 FROM " . $this->table_name . " a
                 JOIN patients p ON a.patient_id = p.id
                 JOIN users u1 ON p.user_id = u1.id
                 JOIN doctors d ON a.doctor_id = d.id
                 JOIN users u2 ON d.user_id = u2.id";
        
        $conditions = [];
        $params = [];
        
        if ($status) {
            $conditions[] = "a.status = :status";
            $params[':status'] = $status;
        }
        
        if ($date) {
            $conditions[] = "a.appointment_date = :date";
            $params[':date'] = $date;
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update appointment status
    public function updateAppointmentStatus($id, $status, $notes = '') {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET status = :status, notes = :notes 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Appointment updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update appointment'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Cancel appointment
    public function cancelAppointment($id, $reason = '') {
        return $this->updateAppointmentStatus($id, 'cancelled', $reason);
    }
    
    // Get upcoming appointments
    public function getUpcomingAppointments($user_role, $user_id) {
        $query = "SELECT a.*, 
                        u1.first_name as patient_first_name, u1.last_name as patient_last_name,
                        u2.first_name as doctor_first_name, u2.last_name as doctor_last_name,
                        p.patient_id as patient_code, d.specialization
                 FROM " . $this->table_name . " a
                 JOIN patients p ON a.patient_id = p.id
                 JOIN users u1 ON p.user_id = u1.id
                 JOIN doctors d ON a.doctor_id = d.id
                 JOIN users u2 ON d.user_id = u2.id
                 WHERE a.appointment_date >= CURDATE() AND a.status = 'scheduled'";
        
        if ($user_role === 'doctor') {
            $query .= " AND a.doctor_id = :user_id";
        } elseif ($user_role === 'patient') {
            $query .= " AND a.patient_id = :user_id";
        }
        
        $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        if ($user_role !== 'admin') {
            $stmt->bindParam(':user_id', $user_id);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get appointment by ID
    public function getAppointmentById($id) {
        $query = "SELECT a.*, 
                        u1.first_name as patient_first_name, u1.last_name as patient_last_name,
                        u2.first_name as doctor_first_name, u2.last_name as doctor_last_name,
                        p.patient_id as patient_code, p.blood_group, p.medical_history,
                        d.specialization, d.license_number, d.department
                 FROM " . $this->table_name . " a
                 JOIN patients p ON a.patient_id = p.id
                 JOIN users u1 ON p.user_id = u1.id
                 JOIN doctors d ON a.doctor_id = d.id
                 JOIN users u2 ON d.user_id = u2.id
                 WHERE a.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
    
    // Get available time slots for a doctor on a specific date
    public function getAvailableTimeSlots($doctor_id, $date) {
        // Get doctor's working hours (you can make this dynamic)
        $start_time = '09:00';
        $end_time = '17:00';
        $slot_duration = 30; // 30 minutes per slot
        
        // Generate all possible time slots
        $slots = [];
        $current_time = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        
        while ($current_time < $end_timestamp) {
            $slots[] = date('H:i', $current_time);
            $current_time += ($slot_duration * 60);
        }
        
        // Get booked slots
        $query = "SELECT appointment_time FROM " . $this->table_name . " 
                 WHERE doctor_id = :doctor_id AND appointment_date = :date 
                 AND status IN ('scheduled', 'completed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Remove booked slots from available slots
        $available_slots = array_diff($slots, $booked_slots);
        
        return array_values($available_slots);
    }
}
?>