<?php
// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>ERROR DEBUG</title>";
echo "<style>";
echo "body { font-family: Arial; background: #f0f0f0; padding: 20px; }";
echo ".error-box { background: #ffebee; border: 2px solid #f44336; padding: 15px; margin: 10px; border-radius: 5px; }";
echo ".success-box { background: #e8f5e8; border: 2px solid #4caf50; padding: 15px; margin: 10px; border-radius: 5px; }";
echo ".info-box { background: #e3f2fd; border: 2px solid #2196f3; padding: 15px; margin: 10px; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1 style='color: #d32f2f;'>🔍 ERROR DEBUG PAGE</h1>";

// Test 1: Basic PHP
echo "<div class='info-box'>";
echo "<h3>📋 PHP Information:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (error_reporting() ? 'ON' : 'OFF') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Current Directory: " . getcwd() . "<br>";
echo "</div>";

// Test 2: Database Connection
echo "<div class='info-box'>";
echo "<h3>🗄️ Database Test:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<span style='color: green;'>✅ Database Connected!</span><br>";
        
        // Test tables
        $tables = ['users', 'doctors', 'patients', 'appointments'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "✅ Table '$table': $count records<br>";
            } catch (Exception $e) {
                echo "<span style='color: red;'>❌ Table '$table' ERROR: " . $e->getMessage() . "</span><br>";
            }
        }
    } else {
        echo "<span style='color: red;'>❌ Database Connection FAILED!</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database ERROR: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 3: File Existence
echo "<div class='info-box'>";
echo "<h3>📁 File Check:</h3>";
$files = [
    'config/database.php',
    'classes/Auth.php',
    'classes/User.php',
    'classes/Appointment.php',
    'classes/Vitals.php',
    'dashboard/doctor.php',
    'dashboard/patient.php',
    'dashboard/admin.php',
    'api/doctors.php',
    'api/patients.php',
    'api/appointments.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS<br>";
    } else {
        echo "<span style='color: red;'>❌ $file: MISSING!</span><br>";
    }
}
echo "</div>";

// Test 4: Include Test
echo "<div class='info-box'>";
echo "<h3>🔧 Include Test:</h3>";
try {
    require_once 'classes/Auth.php';
    echo "✅ Auth.php included successfully<br>";
    
    $auth = new Auth();
    echo "✅ Auth class created successfully<br>";
    
    if ($auth->isLoggedIn()) {
        echo "✅ User is logged in<br>";
        $user = $auth->getCurrentUser();
        echo "User: " . ($user['first_name'] ?? 'Unknown') . "<br>";
    } else {
        echo "⚠️ User not logged in<br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Include ERROR: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 5: API Test
echo "<div class='info-box'>";
echo "<h3>🌐 API Test:</h3>";
$apis = [
    'doctors' => 'api/doctors.php?action=list',
    'patients' => 'api/patients.php?action=list',
    'appointments' => 'api/appointments.php?action=list'
];

foreach ($apis as $name => $url) {
    try {
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data) {
                echo "✅ API $name: WORKING<br>";
            } else {
                echo "<span style='color: orange;'>⚠️ API $name: INVALID JSON</span><br>";
            }
        } else {
            echo "<span style='color: red;'>❌ API $name: FAILED</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ API $name ERROR: " . $e->getMessage() . "</span><br>";
    }
}
echo "</div>";

// Test 6: Dashboard Test
echo "<div class='info-box'>";
echo "<h3>📊 Dashboard Test:</h3>";
echo "<a href='dashboard/doctor.php' target='_blank'>🔗 Test Doctor Dashboard</a><br>";
echo "<a href='dashboard/patient.php' target='_blank'>🔗 Test Patient Dashboard</a><br>";
echo "<a href='dashboard/admin.php' target='_blank'>🔗 Test Admin Dashboard</a><br>";
echo "</div>";

// Test 7: Force Error (for testing)
echo "<div class='error-box'>";
echo "<h3>🧪 Force Error Test:</h3>";
echo "If you see this, error display is working!<br>";
// Uncomment the next line to test error display
// echo $undefined_variable; // This will cause an error
echo "</div>";

echo "<div class='success-box'>";
echo "<h3>✅ SUMMARY:</h3>";
echo "If you can see this page, PHP is working!<br>";
echo "Check the boxes above for specific issues.<br>";
echo "Red = Error, Green = Success, Blue = Info<br>";
echo "</div>";

echo "</body>";
echo "</html>";
?>