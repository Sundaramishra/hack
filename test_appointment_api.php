<?php
// Test appointments API directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Appointments API ===\n";

// Start session (required for API)
session_start();

// Mock a logged in user (you'll need to adjust this based on your session structure)
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'patient';

// Set up request environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET = [];

// Test data
$test_data = [
    'patient_id' => 1,
    'doctor_id' => 1,
    'appointment_date' => date('Y-m-d', strtotime('+1 day')),
    'appointment_time' => '10:00',
    'reason' => 'Test appointment',
    'notes' => 'Testing API directly'
];

echo "Test data: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

// Capture any output
ob_start();

// Mock php://input
$input_data = json_encode($test_data);
file_put_contents('php://temp', $input_data);

try {
    // Include the API
    require_once 'api/appointments.php';
    
    // Create API instance
    $api = new AppointmentsApi();
    $api->handleRequest();
    
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$output = ob_get_clean();
echo "API Output:\n";
echo $output;
echo "\n=== End Test ===\n";
?>