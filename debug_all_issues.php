<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h1>🔍 COMPLETE SYSTEM DEBUG</h1>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h2>1. Database Connection</h2>";
    echo "✅ Database connected successfully<br><br>";
    
    // Check all tables
    echo "<h2>2. Database Tables Check</h2>";
    $tables = ['users', 'doctors', 'patients', 'appointments', 'prescriptions', 'prescription_medicines', 'vital_types', 'patient_vitals'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✅ $table: $count records<br>";
        } catch (Exception $e) {
            echo "❌ $table: ERROR - " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><h2>3. Prescription Query Test</h2>";
    try {
        $query = "SELECT p.*, 
                        CONCAT(up.first_name, ' ', up.last_name) as patient_name,
                        CONCAT(ud.first_name, ' ', ud.last_name) as doctor_name,
                        pt.patient_code
                 FROM prescriptions p
                 JOIN patients pt ON p.patient_id = pt.patient_id
                 JOIN users up ON pt.user_id = up.id
                 JOIN doctors d ON p.doctor_id = d.doctor_id
                 JOIN users ud ON d.user_id = ud.id
                 ORDER BY p.prescription_date DESC LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prescription) {
            echo "✅ Prescription query working<br>";
            echo "Sample prescription: " . $prescription['prescription_number'] . " for " . $prescription['patient_name'] . "<br>";
        } else {
            echo "⚠️ No prescriptions found in database<br>";
        }
    } catch (Exception $e) {
        echo "❌ Prescription query failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h2>4. Appointments Query Test</h2>";
    try {
        $query = "SELECT a.*, 
                        CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                        CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                 FROM appointments a
                 JOIN patients p ON a.patient_id = p.patient_id
                 JOIN users pu ON p.user_id = pu.id
                 JOIN doctors d ON a.doctor_id = d.doctor_id
                 JOIN users du ON d.user_id = du.id
                 ORDER BY a.appointment_date DESC LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($appointment) {
            echo "✅ Appointments query working<br>";
            echo "Sample appointment: " . $appointment['patient_name'] . " with " . $appointment['doctor_name'] . "<br>";
        } else {
            echo "⚠️ No appointments found in database<br>";
        }
    } catch (Exception $e) {
        echo "❌ Appointments query failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h2>5. Users/Doctors/Patients Test</h2>";
    try {
        // Check users
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $adminCount = $stmt->fetchColumn();
        echo "✅ Admin users: $adminCount<br>";
        
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'");
        $doctorCount = $stmt->fetchColumn();
        echo "✅ Doctor users: $doctorCount<br>";
        
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'patient'");
        $patientCount = $stmt->fetchColumn();
        echo "✅ Patient users: $patientCount<br>";
        
        // Check if doctors table has proper data
        $stmt = $conn->query("SELECT COUNT(*) FROM doctors");
        $doctorRecords = $stmt->fetchColumn();
        echo "✅ Doctor records: $doctorRecords<br>";
        
        // Check if patients table has proper data
        $stmt = $conn->query("SELECT COUNT(*) FROM patients");
        $patientRecords = $stmt->fetchColumn();
        echo "✅ Patient records: $patientRecords<br>";
        
    } catch (Exception $e) {
        echo "❌ Users query failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h2>6. Vital Types Test</h2>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM vital_types WHERE is_active = 1");
        $vitalCount = $stmt->fetchColumn();
        echo "✅ Active vital types: $vitalCount<br>";
        
        if ($vitalCount > 0) {
            $stmt = $conn->query("SELECT name FROM vital_types WHERE is_active = 1 LIMIT 3");
            $vitals = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Sample vitals: " . implode(', ', $vitals) . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Vitals query failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h2>7. Check Sample Data</h2>";
    
    // Check if we have sample data
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $hasAdmin = $stmt->fetchColumn() > 0;
    echo ($hasAdmin ? "✅" : "❌") . " Admin user exists<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM appointments");
    $hasAppointments = $stmt->fetchColumn() > 0;
    echo ($hasAppointments ? "✅" : "❌") . " Sample appointments exist<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM prescriptions");
    $hasPrescriptions = $stmt->fetchColumn() > 0;
    echo ($hasPrescriptions ? "✅" : "❌") . " Sample prescriptions exist<br>";
    
    echo "<br><h2>8. Handler Files Check</h2>";
    $handlers = [
        'handlers/admin_appointments.php',
        'handlers/admin_stats.php', 
        'handlers/prescriptions.php',
        'handlers/appointments.php',
        'handlers/admin_users.php',
        'handlers/vitals.php',
        'handlers/get_time_slots.php'
    ];
    
    foreach ($handlers as $handler) {
        if (file_exists($handler)) {
            echo "✅ $handler exists<br>";
        } else {
            echo "❌ $handler MISSING<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ CRITICAL ERROR</h2>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><h2>🎯 SUMMARY</h2>";
echo "<p>This debug shows exactly what's working and what's broken.</p>";
echo "<p><a href='dashboard/admin.php'>Go to Admin Dashboard</a></p>";
?>