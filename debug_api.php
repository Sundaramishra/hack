<?php
// Debug script to test appointments API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test data for appointment booking
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

echo "=== Testing Appointments API ===\n";
echo "Test Data: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

// Set up the request
$url = 'http://localhost/api/appointments.php';
$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Cookie: PHPSESSID=test_session'
        ],
        'method' => 'POST',
        'content' => json_encode($test_data)
    ]
];

$context = stream_context_create($options);

echo "=== Making API Request ===\n";
echo "URL: $url\n";
echo "Method: POST\n";
echo "Headers: " . implode(', ', $options['http']['header']) . "\n\n";

// Make the request
$response = file_get_contents($url, false, $context);

echo "=== API Response ===\n";
echo "Raw Response:\n";
var_dump($response);
echo "\n";

if ($response === false) {
    echo "ERROR: Failed to get response from API\n";
    echo "HTTP Response Headers:\n";
    print_r($http_response_header ?? 'No headers available');
} else {
    echo "Response Length: " . strlen($response) . " bytes\n";
    
    // Try to decode JSON
    $decoded = json_decode($response, true);
    $json_error = json_last_error();
    
    if ($json_error === JSON_ERROR_NONE) {
        echo "JSON Decoded Successfully:\n";
        print_r($decoded);
    } else {
        echo "JSON Decode Error: " . json_last_error_msg() . "\n";
        echo "Raw response (first 500 chars):\n";
        echo substr($response, 0, 500) . "\n";
        
        // Check for common issues
        if (strpos($response, '<') === 0) {
            echo "WARNING: Response appears to be HTML, not JSON\n";
        }
        
        if (strpos($response, 'Fatal error') !== false) {
            echo "WARNING: PHP Fatal Error detected in response\n";
        }
        
        if (strpos($response, 'Parse error') !== false) {
            echo "WARNING: PHP Parse Error detected in response\n";
        }
    }
}

echo "\n=== Direct API File Test ===\n";
// Test if we can directly access the API file
if (file_exists('api/appointments.php')) {
    echo "API file exists: api/appointments.php\n";
    
    // Check if we can read it
    $api_content = file_get_contents('api/appointments.php', false, null, 0, 200);
    echo "First 200 chars of API file:\n";
    echo $api_content . "\n";
} else {
    echo "ERROR: API file not found: api/appointments.php\n";
}

echo "\n=== Environment Check ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Script Path: " . __FILE__ . "\n";

// Check if database connection works
if (file_exists('config/database.php')) {
    echo "Database config exists\n";
    try {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        echo "Database connection: SUCCESS\n";
    } catch (Exception $e) {
        echo "Database connection: FAILED - " . $e->getMessage() . "\n";
    }
} else {
    echo "Database config: NOT FOUND\n";
}

echo "\n=== Test Complete ===\n";
?>