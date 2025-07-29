<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session as admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "<h1>Handler Debug Test</h1>";

echo "<h2>1. Test Admin Appointments Handler</h2>";
try {
    ob_start();
    include 'handlers/admin_appointments.php';
    $output = ob_get_clean();
    $result = json_decode($output, true);
    
    if ($result && $result['success']) {
        echo "✅ Admin appointments working: " . count($result['data']) . " appointments found<br>";
        if (!empty($result['data'])) {
            echo "First appointment: " . $result['data'][0]['patient_name'] . " with " . $result['data'][0]['doctor_name'] . "<br>";
        }
    } else {
        echo "❌ Admin appointments failed: " . ($result['message'] ?? 'Unknown error') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Admin appointments error: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Test Prescriptions Handler</h2>";
try {
    $_GET['action'] = 'list';
    ob_start();
    include 'handlers/prescriptions.php';
    $output = ob_get_clean();
    $result = json_decode($output, true);
    
    if ($result && $result['success']) {
        echo "✅ Prescriptions working: " . count($result['data']) . " prescriptions found<br>";
        if (!empty($result['data'])) {
            echo "First prescription: " . $result['data'][0]['prescription_number'] . " for " . $result['data'][0]['patient_name'] . "<br>";
        }
    } else {
        echo "❌ Prescriptions failed: " . ($result['message'] ?? 'Unknown error') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Prescriptions error: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Test Admin Stats Handler</h2>";
try {
    ob_start();
    include 'handlers/admin_stats.php';
    $output = ob_get_clean();
    $result = json_decode($output, true);
    
    if ($result && $result['success']) {
        echo "✅ Admin stats working<br>";
        echo "Users: " . ($result['data']['total_users'] ?? 0) . "<br>";
        echo "Doctors: " . ($result['data']['total_doctors'] ?? 0) . "<br>";
        echo "Patients: " . ($result['data']['total_patients'] ?? 0) . "<br>";
        echo "Appointments: " . ($result['data']['total_appointments'] ?? 0) . "<br>";
    } else {
        echo "❌ Admin stats failed: " . ($result['message'] ?? 'Unknown error') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Admin stats error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test Database Tables</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check appointments
    $stmt = $conn->query("SELECT COUNT(*) FROM appointments");
    $appointmentCount = $stmt->fetchColumn();
    echo "✅ Appointments table: $appointmentCount records<br>";
    
    // Check prescriptions
    $stmt = $conn->query("SELECT COUNT(*) FROM prescriptions");
    $prescriptionCount = $stmt->fetchColumn();
    echo "✅ Prescriptions table: $prescriptionCount records<br>";
    
    // Check users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "✅ Users table: $userCount records<br>";
    
    // Check doctors
    $stmt = $conn->query("SELECT COUNT(*) FROM doctors");
    $doctorCount = $stmt->fetchColumn();
    echo "✅ Doctors table: $doctorCount records<br>";
    
    // Check patients
    $stmt = $conn->query("SELECT COUNT(*) FROM patients");
    $patientCount = $stmt->fetchColumn();
    echo "✅ Patients table: $patientCount records<br>";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Test Specific Query</h2>";
try {
    // Test the exact query from admin_appointments.php
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
            CONCAT(du.first_name, ' ', du.last_name) as doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN users pu ON p.user_id = pu.id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN users du ON d.user_id = du.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 10
    ");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Direct query result: " . count($appointments) . " appointments<br>";
    foreach ($appointments as $apt) {
        echo "- " . $apt['patient_name'] . " with " . $apt['doctor_name'] . " on " . $apt['appointment_date'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Direct query error: " . $e->getMessage() . "<br>";
}

echo "<h2>Debug Complete</h2>";
echo "<p><a href='dashboard/admin.php'>Go to Admin Dashboard</a></p>";
?>