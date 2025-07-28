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
    $userId = $_SESSION['user_id'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'profile';

        if ($action === 'profile') {
            // Get user profile based on role
            if ($userRole === 'admin') {
                $query = "SELECT u.*, 
                                COUNT(DISTINCT p.patient_id) as total_patients,
                                COUNT(DISTINCT d.doctor_id) as total_doctors,
                                COUNT(DISTINCT a.id) as total_appointments
                         FROM users u
                         LEFT JOIN patients p ON 1=1
                         LEFT JOIN doctors d ON 1=1
                         LEFT JOIN appointments a ON 1=1
                         WHERE u.id = ?
                         GROUP BY u.id";
                
            } elseif ($userRole === 'doctor') {
                $query = "SELECT u.*, d.doctor_id, d.specialization, d.license_number, 
                                d.qualification, d.experience_years, d.consultation_fee,
                                d.available_from, d.available_to, d.available_days,
                                d.consultation_duration, d.status,
                                COUNT(DISTINCT p.patient_id) as assigned_patients,
                                COUNT(DISTINCT a.id) as total_appointments,
                                COUNT(DISTINCT CASE WHEN a.appointment_date = CURDATE() THEN a.id END) as today_appointments
                         FROM users u
                         JOIN doctors d ON u.id = d.user_id
                         LEFT JOIN patients p ON d.doctor_id = p.assigned_doctor_id
                         LEFT JOIN appointments a ON d.doctor_id = a.doctor_id
                         WHERE u.id = ?
                         GROUP BY u.id";
                
            } elseif ($userRole === 'patient') {
                $query = "SELECT u.*, p.patient_id, p.patient_code, p.blood_group, 
                                p.allergies, p.medical_history, p.emergency_contact_name,
                                p.emergency_contact_phone, p.insurance_number, p.registration_date,
                                CONCAT(ud.first_name, ' ', ud.last_name) as assigned_doctor_name,
                                d.specialization as doctor_specialization,
                                COUNT(DISTINCT a.id) as total_appointments,
                                COUNT(DISTINCT CASE WHEN a.appointment_date >= CURDATE() THEN a.id END) as upcoming_appointments,
                                COUNT(DISTINCT pr.id) as total_prescriptions
                         FROM users u
                         JOIN patients p ON u.id = p.user_id
                         LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                         LEFT JOIN users ud ON d.user_id = ud.id
                         LEFT JOIN appointments a ON p.patient_id = a.patient_id
                         LEFT JOIN prescriptions pr ON p.patient_id = pr.patient_id
                         WHERE u.id = ?
                         GROUP BY u.id";
            }

            $stmt = $conn->prepare($query);
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                throw new Exception('Profile not found');
            }

            // Get recent activity based on role
            $recentActivity = [];
            
            if ($userRole === 'doctor') {
                // Recent appointments for doctor
                $activityQuery = "SELECT 'appointment' as type, 
                                        CONCAT('Appointment with ', up.first_name, ' ', up.last_name) as description,
                                        a.appointment_date as date,
                                        a.appointment_time as time,
                                        a.status
                                 FROM appointments a
                                 JOIN patients p ON a.patient_id = p.patient_id
                                 JOIN users up ON p.user_id = up.id
                                 WHERE a.doctor_id = ?
                                 ORDER BY a.appointment_date DESC, a.appointment_time DESC
                                 LIMIT 5";
                $stmt = $conn->prepare($activityQuery);
                $stmt->execute([$profile['doctor_id']]);
                $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($userRole === 'patient') {
                // Recent appointments and prescriptions for patient
                $activityQuery = "SELECT 'appointment' as type,
                                        CONCAT('Appointment with Dr. ', ud.first_name, ' ', ud.last_name) as description,
                                        a.appointment_date as date,
                                        a.appointment_time as time,
                                        a.status
                                 FROM appointments a
                                 JOIN doctors d ON a.doctor_id = d.doctor_id
                                 JOIN users ud ON d.user_id = ud.id
                                 WHERE a.patient_id = ?
                                 UNION ALL
                                 SELECT 'prescription' as type,
                                        CONCAT('Prescription from Dr. ', ud.first_name, ' ', ud.last_name) as description,
                                        pr.prescription_date as date,
                                        '00:00:00' as time,
                                        pr.status
                                 FROM prescriptions pr
                                 JOIN doctors d ON pr.doctor_id = d.doctor_id
                                 JOIN users ud ON d.user_id = ud.id
                                 WHERE pr.patient_id = ?
                                 ORDER BY date DESC, time DESC
                                 LIMIT 5";
                $stmt = $conn->prepare($activityQuery);
                $stmt->execute([$profile['patient_id'], $profile['patient_id']]);
                $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $profile['recent_activity'] = $recentActivity;

            echo json_encode([
                'success' => true,
                'data' => $profile
            ]);

        } elseif ($action === 'stats') {
            // Get profile statistics
            $stats = [];
            
            if ($userRole === 'doctor') {
                $doctorId = $_SESSION['doctor_id'];
                
                // Today's appointments
                $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
                $stmt->execute([$doctorId]);
                $stats['today_appointments'] = $stmt->fetchColumn();
                
                // This week's appointments
                $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
                $stmt->execute([$doctorId]);
                $stats['week_appointments'] = $stmt->fetchColumn();
                
                // Total patients
                $stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = ?");
                $stmt->execute([$doctorId]);
                $stats['total_patients'] = $stmt->fetchColumn();
                
            } elseif ($userRole === 'patient') {
                $patientId = $_SESSION['patient_id'];
                
                // Total appointments
                $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
                $stmt->execute([$patientId]);
                $stats['total_appointments'] = $stmt->fetchColumn();
                
                // Upcoming appointments
                $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND appointment_date >= CURDATE()");
                $stmt->execute([$patientId]);
                $stats['upcoming_appointments'] = $stmt->fetchColumn();
                
                // Total prescriptions
                $stmt = $conn->prepare("SELECT COUNT(*) FROM prescriptions WHERE patient_id = ?");
                $stmt->execute([$patientId]);
                $stats['total_prescriptions'] = $stmt->fetchColumn();
            }

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        }

    } elseif ($method === 'PUT') {
        // Update profile
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'update_basic';

        if ($action === 'update_basic') {
            $firstName = $input['first_name'] ?? null;
            $lastName = $input['last_name'] ?? null;
            $email = $input['email'] ?? null;
            $phone = $input['phone'] ?? null;
            $dateOfBirth = $input['date_of_birth'] ?? null;
            $gender = $input['gender'] ?? null;
            $address = $input['address'] ?? null;

            if (!$firstName || !$lastName || !$email) {
                throw new Exception('First name, last name, and email are required');
            }

            // Check if email is unique (excluding current user)
            $emailCheck = "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($emailCheck);
            $stmt->execute([$email, $userId]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Email already exists');
            }

            // Update basic user info
            $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                           date_of_birth = ?, gender = ?, address = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } elseif ($action === 'update_password') {
            $currentPassword = $input['current_password'] ?? null;
            $newPassword = $input['new_password'] ?? null;
            $confirmPassword = $input['confirm_password'] ?? null;

            if (!$currentPassword || !$newPassword || !$confirmPassword) {
                throw new Exception('All password fields are required');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }

            // Validate new password
            $passwordValidation = $auth->validatePassword($newPassword);
            if ($passwordValidation !== true) {
                throw new Exception($passwordValidation);
            }

            // Verify current password
            $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $currentHash)) {
                throw new Exception('Current password is incorrect');
            }

            // Update password
            $newHash = $auth->hashPassword($newPassword);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } elseif ($action === 'update_role_specific') {
            // Update role-specific information
            if ($userRole === 'doctor') {
                $specialization = $input['specialization'] ?? null;
                $qualification = $input['qualification'] ?? null;
                $experienceYears = $input['experience_years'] ?? null;
                $consultationFee = $input['consultation_fee'] ?? null;
                $availableFrom = $input['available_from'] ?? null;
                $availableTo = $input['available_to'] ?? null;
                $availableDays = $input['available_days'] ?? null;
                $consultationDuration = $input['consultation_duration'] ?? null;

                $updateQuery = "UPDATE doctors SET specialization = ?, qualification = ?, 
                               experience_years = ?, consultation_fee = ?, available_from = ?, 
                               available_to = ?, available_days = ?, consultation_duration = ? 
                               WHERE user_id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute([
                    $specialization, $qualification, $experienceYears, $consultationFee,
                    $availableFrom, $availableTo, $availableDays, $consultationDuration, $userId
                ]);

            } elseif ($userRole === 'patient') {
                $bloodGroup = $input['blood_group'] ?? null;
                $allergies = $input['allergies'] ?? null;
                $medicalHistory = $input['medical_history'] ?? null;
                $emergencyContactName = $input['emergency_contact_name'] ?? null;
                $emergencyContactPhone = $input['emergency_contact_phone'] ?? null;
                $insuranceNumber = $input['insurance_number'] ?? null;

                $updateQuery = "UPDATE patients SET blood_group = ?, allergies = ?, 
                               medical_history = ?, emergency_contact_name = ?, 
                               emergency_contact_phone = ?, insurance_number = ? 
                               WHERE user_id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute([
                    $bloodGroup, $allergies, $medicalHistory, $emergencyContactName,
                    $emergencyContactPhone, $insuranceNumber, $userId
                ]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } elseif ($action === 'update_theme') {
            $theme = $input['theme'] ?? 'light';
            
            if (!in_array($theme, ['light', 'dark'])) {
                throw new Exception('Invalid theme');
            }

            $stmt = $conn->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
            $stmt->execute([$theme, $userId]);

            // Update session
            $_SESSION['theme'] = $theme;

            echo json_encode([
                'success' => true,
                'message' => 'Theme updated successfully'
            ]);
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>