<?php
// Direct API test without web server
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Direct API Test ===\n";

// Simulate the request environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = '';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Test data
$test_data = [
    'patient_id' => 1,
    'doctor_id' => 1,
    'appointment_date' => date('Y-m-d', strtotime('+1 day')),
    'appointment_time' => '10:00',
    'appointment_type' => 'consultation',
    'duration' => 30,
    'reason' => 'Test appointment',
    'notes' => 'Testing API'
];

// Mock the input stream
$json_data = json_encode($test_data);
file_put_contents('php://memory', $json_data);

echo "Test data: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

// Start output buffering to catch the API response
ob_start();

try {
    // Mock session and authentication
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'patient';
    $_SESSION['logged_in'] = true;
    
    echo "Session setup complete\n";
    
    // Check if required files exist
    if (!file_exists('api/ApiBase.php')) {
        throw new Exception("ApiBase.php not found");
    }
    if (!file_exists('config/database.php')) {
        throw new Exception("database.php not found");
    }
    
    echo "Required files found\n";
    
    // Test database connection first
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "Database connection successful\n";
    
    // Test if appointments table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Appointments table not found");
    }
    echo "Appointments table exists\n";
    
    // Check table structure
    $stmt = $conn->query("DESCRIBE appointments");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Table columns: " . implode(', ', $columns) . "\n";
    
    // Clear output buffer before including API
    ob_clean();
    
    // Include and run the API
    include 'api/appointments.php';
    
} catch (Exception $e) {
    ob_clean();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Get the API output
$api_output = ob_get_clean();

echo "\n=== API Output ===\n";
echo "Length: " . strlen($api_output) . " bytes\n";
echo "Content:\n";
var_dump($api_output);

if (!empty($api_output)) {
    // Try to parse as JSON
    $decoded = json_decode($api_output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nJSON Decoded Successfully:\n";
        print_r($decoded);
    } else {
        echo "\nJSON Decode Error: " . json_last_error_msg() . "\n";
        echo "First 200 chars: " . substr($api_output, 0, 200) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>