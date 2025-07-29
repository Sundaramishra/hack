<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 1;  
$_SESSION['role'] = 'admin';

echo "<h1>🔧 DIRECT HANDLER TESTING</h1>";

echo "<h2>1. Testing Prescriptions Handler</h2>";
try {
    $_GET['action'] = 'list';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    include 'handlers/prescriptions.php';
    $output = ob_get_clean();
    
    echo "<strong>Prescriptions Handler Output:</strong><br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $result = json_decode($output, true);
    if ($result) {
        if ($result['success']) {
            echo "✅ Prescriptions handler working!<br>";
        } else {
            echo "❌ Prescriptions handler error: " . $result['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response from prescriptions handler<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Prescriptions handler exception: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h2>2. Testing Admin Appointments Handler</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    unset($_GET['action']);
    
    ob_start();
    include 'handlers/admin_appointments.php';
    $output = ob_get_clean();
    
    echo "<strong>Admin Appointments Handler Output:</strong><br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $result = json_decode($output, true);
    if ($result) {
        if ($result['success']) {
            echo "✅ Admin appointments handler working!<br>";
        } else {
            echo "❌ Admin appointments handler error: " . $result['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response from admin appointments handler<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Admin appointments handler exception: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h2>3. Testing Appointment Booking</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Simulate appointment booking data
    $appointmentData = [
        'action' => 'book',
        'patient_id' => '1',
        'doctor_id' => '1', 
        'appointment_date' => '2024-12-20',
        'appointment_time' => '10:00:00',
        'reason' => 'Test appointment'
    ];
    
    // Simulate JSON input
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($appointmentData);
    
    ob_start();
    include 'handlers/appointments.php';
    $output = ob_get_clean();
    
    echo "<strong>Appointment Booking Handler Output:</strong><br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $result = json_decode($output, true);
    if ($result) {
        if ($result['success']) {
            echo "✅ Appointment booking working!<br>";
        } else {
            echo "❌ Appointment booking error: " . $result['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response from appointment booking<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Appointment booking exception: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h2>4. Testing Time Slots Handler</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['doctor_id'] = '1';
    $_GET['date'] = '2024-12-20';
    
    if (file_exists('handlers/get_time_slots.php')) {
        ob_start();
        include 'handlers/get_time_slots.php';
        $output = ob_get_clean();
        
        echo "<strong>Time Slots Handler Output:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
        $result = json_decode($output, true);
        if ($result) {
            if ($result['success']) {
                echo "✅ Time slots handler working!<br>";
            } else {
                echo "❌ Time slots handler error: " . $result['message'] . "<br>";
            }
        } else {
            echo "❌ Invalid JSON response from time slots handler<br>";
        }
    } else {
        echo "❌ Time slots handler file missing!<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Time slots handler exception: " . $e->getMessage() . "<br>";
}

echo "<br><h2>🎯 CONCLUSION</h2>";
echo "<p>This shows the exact errors from each handler.</p>";
?>