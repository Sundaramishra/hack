<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Hospital CRM System Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!<br>";
        
        // Test if tables exist
        $tables = ['users', 'doctors', 'patients', 'appointments', 'vital_types'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "✅ Table '$table' exists with $count records<br>";
            } catch (Exception $e) {
                echo "❌ Table '$table' error: " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ Database connection failed!<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test API endpoints
echo "<h2>2. API Endpoints Test</h2>";

$apis = [
    'doctors.php' => 'api/doctors.php?action=list',
    'patients.php' => 'api/patients.php?action=list',
    'appointments.php' => 'api/appointments.php?action=list',
    'vital_types.php' => 'api/vital_types.php?action=list'
];

foreach ($apis as $api_name => $api_url) {
    echo "<h3>Testing $api_name</h3>";
    try {
        $response = file_get_contents($api_url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data) {
                echo "✅ API $api_name working - Response: " . substr($response, 0, 200) . "...<br>";
            } else {
                echo "❌ API $api_name returned invalid JSON<br>";
            }
        } else {
            echo "❌ API $api_name failed to load<br>";
        }
    } catch (Exception $e) {
        echo "❌ API $api_name error: " . $e->getMessage() . "<br>";
    }
}

// Test dashboard files
echo "<h2>3. Dashboard Files Test</h2>";
$dashboards = [
    'admin.php' => 'dashboard/admin.php',
    'doctor.php' => 'dashboard/doctor.php',
    'patient.php' => 'dashboard/patient.php'
];

foreach ($dashboards as $dashboard_name => $dashboard_path) {
    if (file_exists($dashboard_path)) {
        echo "✅ Dashboard $dashboard_name exists<br>";
    } else {
        echo "❌ Dashboard $dashboard_name missing<br>";
    }
}

echo "<h2>4. PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "Current Directory: " . getcwd() . "<br>";
?>