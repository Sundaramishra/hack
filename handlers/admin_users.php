<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('admin');

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';

    if ($method === 'GET') {
        if ($action === 'list') {
            $role = $_GET['role'] ?? null;
            
            if ($role === 'patient') {
                // Get patients with user data
                $query = "SELECT p.patient_id, p.patient_code, p.blood_group, p.allergies,
                                u.id as user_id, u.first_name, u.last_name, u.email, u.phone,
                                u.date_of_birth, u.gender, u.is_active,
                                CONCAT(ud.first_name, ' ', ud.last_name) as assigned_doctor_name
                         FROM patients p
                         JOIN users u ON p.user_id = u.id
                         LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                         LEFT JOIN users ud ON d.user_id = ud.id
                         ORDER BY u.first_name, u.last_name";
            } elseif ($role === 'doctor') {
                // Get doctors with user data
                $query = "SELECT d.doctor_id, d.specialization, d.license_number, d.experience_years,
                                u.id as user_id, u.first_name, u.last_name, u.email, u.phone,
                                u.date_of_birth, u.gender, u.is_active
                         FROM doctors d
                         JOIN users u ON d.user_id = u.id
                         ORDER BY u.first_name, u.last_name";
            } else {
                // Get all users
                $query = "SELECT u.*, 
                                d.doctor_id, d.specialization,
                                p.patient_id, p.patient_code
                         FROM users u
                         LEFT JOIN doctors d ON u.id = d.user_id
                         LEFT JOIN patients p ON u.id = p.user_id
                         ORDER BY u.role, u.first_name, u.last_name";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $users
            ]);

        } elseif ($action === 'stats') {
            // Get user statistics
            $stats = [];
            
            // Total users
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Users by role
            $stmt = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role");
            $roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($roleStats as $roleStat) {
                $stats['total_' . $roleStat['role'] . 's'] = $roleStat['count'];
            }
            
            // Recent registrations (last 30 days)
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['recent_registrations'] = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        }

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'create';

        if ($action === 'create') {
            $role = $input['role'] ?? null;
            $firstName = $input['first_name'] ?? null;
            $lastName = $input['last_name'] ?? null;
            $email = $input['email'] ?? null;
            $username = $input['username'] ?? null;
            $password = $input['password'] ?? null;
            $phone = $input['phone'] ?? null;
            $dateOfBirth = $input['date_of_birth'] ?? null;
            $gender = $input['gender'] ?? null;

            if (!$role || !$firstName || !$lastName || !$email || !$username || !$password) {
                throw new Exception('All required fields must be filled');
            }

            // Validate password
            $passwordValidation = $auth->validatePassword($password);
            if ($passwordValidation !== true) {
                throw new Exception($passwordValidation);
            }

            $conn->beginTransaction();

            try {
                // Create user
                $userQuery = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, date_of_birth, gender, role)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($userQuery);
                $stmt->execute([
                    $username, $email, $auth->hashPassword($password),
                    $firstName, $lastName, $phone, $dateOfBirth, $gender, $role
                ]);

                $userId = $conn->lastInsertId();

                // Create role-specific record
                if ($role === 'doctor') {
                    $specialization = $input['specialization'] ?? null;
                    $licenseNumber = $input['license_number'] ?? null;
                    $qualification = $input['qualification'] ?? null;
                    $experienceYears = $input['experience_years'] ?? 0;
                    $consultationFee = $input['consultation_fee'] ?? 0.00;

                    if (!$specialization) {
                        throw new Exception('Specialization is required for doctors');
                    }

                    $doctorQuery = "INSERT INTO doctors (user_id, specialization, license_number, qualification, experience_years, consultation_fee)
                                   VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($doctorQuery);
                    $stmt->execute([$userId, $specialization, $licenseNumber, $qualification, $experienceYears, $consultationFee]);

                } elseif ($role === 'patient') {
                    $bloodGroup = $input['blood_group'] ?? null;
                    $allergies = $input['allergies'] ?? null;
                    $medicalHistory = $input['medical_history'] ?? null;
                    $emergencyContactName = $input['emergency_contact_name'] ?? null;
                    $emergencyContactPhone = $input['emergency_contact_phone'] ?? null;
                    $assignedDoctorId = $input['assigned_doctor_id'] ?? null;

                    // Generate patient code
                    $stmt = $conn->query("SELECT COUNT(*) + 1 FROM patients");
                    $patientNumber = $stmt->fetchColumn();
                    $patientCode = 'P' . str_pad($patientNumber, 4, '0', STR_PAD_LEFT);

                    $patientQuery = "INSERT INTO patients (user_id, patient_code, blood_group, allergies, medical_history, emergency_contact_name, emergency_contact_phone, assigned_doctor_id)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($patientQuery);
                    $stmt->execute([$userId, $patientCode, $bloodGroup, $allergies, $medicalHistory, $emergencyContactName, $emergencyContactPhone, $assignedDoctorId]);
                }

                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => ucfirst($role) . ' created successfully',
                    'data' => ['user_id' => $userId]
                ]);

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        }

    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;
        $action = $input['action'] ?? 'update';

        if (!$userId) {
            throw new Exception('User ID is required');
        }

        if ($action === 'toggle_status') {
            $query = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$userId]);

            echo json_encode([
                'success' => true,
                'message' => 'User status updated successfully'
            ]);

        } elseif ($action === 'update') {
            // Update user functionality can be implemented here
            echo json_encode([
                'success' => false,
                'message' => 'Update functionality not implemented yet'
            ]);
        }

    } elseif ($method === 'DELETE') {
        $userId = $_GET['user_id'] ?? null;
        
        if (!$userId) {
            throw new Exception('User ID is required');
        }

        // Don't allow deletion of admin users
        $checkQuery = "SELECT role FROM users WHERE id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('User not found');
        }

        if ($user['role'] === 'admin') {
            throw new Exception('Cannot delete admin users');
        }

        // Soft delete by setting is_active to 0
        $query = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId]);

        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>