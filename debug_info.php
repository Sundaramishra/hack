<?php
// Debug Information File - Include this in your dashboards
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<div style='background: #f0f0f0; border: 2px solid #333; padding: 15px; margin: 10px; font-family: Arial;'>";
echo "<h3 style='color: #d32f2f; margin: 0 0 10px 0;'>🔍 DEBUG INFORMATION</h3>";

// 1. PHP Info
echo "<h4 style='color: #1976d2;'>📋 PHP Information:</h4>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (error_reporting() ? 'ON' : 'OFF') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s<br><br>";

// 2. Session Info
echo "<h4 style='color: #1976d2;'>🔐 Session Information:</h4>";
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "Session Status: ACTIVE<br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session Data: <pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "Session Status: NOT ACTIVE<br>";
}
echo "<br>";

// 3. Database Connection Test
echo "<h4 style='color: #1976d2;'>🗄️ Database Connection:</h4>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database Connection: SUCCESS<br>";
        
        // Test tables
        $tables = ['users', 'doctors', 'patients', 'appointments', 'vital_types'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "✅ Table '$table': $count records<br>";
            } catch (Exception $e) {
                echo "❌ Table '$table': " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ Database Connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 4. File System Check
echo "<h4 style='color: #1976d2;'>📁 File System:</h4>";
$files = [
    'config/database.php',
    'classes/Auth.php',
    'classes/User.php',
    'classes/Appointment.php',
    'classes/Vitals.php',
    'api/doctors.php',
    'api/patients.php',
    'api/appointments.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS<br>";
    } else {
        echo "❌ $file: MISSING<br>";
    }
}
echo "<br>";

// 5. API Test
echo "<h4 style='color: #1976d2;'>🌐 API Test:</h4>";
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
                echo "⚠️ API $name: INVALID JSON<br>";
            }
        } else {
            echo "❌ API $name: FAILED<br>";
        }
    } catch (Exception $e) {
        echo "❌ API $name: " . $e->getMessage() . "<br>";
    }
}
echo "<br>";

// 6. Current User Info
echo "<h4 style='color: #1976d2;'>👤 Current User:</h4>";
try {
    $auth = new Auth();
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo "✅ User Logged In<br>";
        echo "User ID: " . ($user['id'] ?? 'N/A') . "<br>";
        echo "Role: " . ($user['role'] ?? 'N/A') . "<br>";
        echo "Name: " . ($user['first_name'] ?? 'N/A') . " " . ($user['last_name'] ?? 'N/A') . "<br>";
    } else {
        echo "❌ User Not Logged In<br>";
    }
} catch (Exception $e) {
    echo "❌ Auth Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 7. JavaScript Console Log
echo "<h4 style='color: #1976d2;'>🖥️ JavaScript Console:</h4>";
echo "<div id='js-console' style='background: #000; color: #0f0; padding: 10px; font-family: monospace; height: 100px; overflow-y: scroll;'>";
echo "Loading JavaScript console...<br>";
echo "</div>";

echo "<script>
console.log('=== DEBUG CONSOLE START ===');
console.log('Page URL:', window.location.href);
console.log('User Agent:', navigator.userAgent);
console.log('Screen Size:', screen.width + 'x' + screen.height);

// Test API calls
async function testAPIs() {
    const apis = ['doctors', 'patients', 'appointments'];
    for (const api of apis) {
        try {
            const response = await fetch('../api/' + api + '.php?action=list');
            const data = await response.json();
            console.log('API ' + api + ':', data.success ? 'SUCCESS' : 'FAILED', data);
        } catch (error) {
            console.error('API ' + api + ' ERROR:', error);
        }
    }
}

testAPIs();

// Update console display
function updateConsole() {
    const consoleDiv = document.getElementById('js-console');
    if (consoleDiv) {
        consoleDiv.innerHTML += 'JavaScript console active - check browser console for details<br>';
    }
}

setTimeout(updateConsole, 1000);
</script>";

echo "<br>";

// 8. Quick Fixes
echo "<h4 style='color: #1976d2;'>🔧 Quick Fixes:</h4>";
echo "<a href='setup_database.php' style='color: #1976d2; text-decoration: none;'>📊 Setup Database</a><br>";
echo "<a href='test_connection.php' style='color: #1976d2; text-decoration: none;'>🧪 Test Connection</a><br>";
echo "<a href='index.php' style='color: #1976d2; text-decoration: none;'>🏠 Go to Home</a><br>";

echo "</div>";
?>