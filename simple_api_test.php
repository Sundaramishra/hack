<?php
// Simple API test with proper environment simulation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Simple API Test ===\n";

// Set up proper environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET = [];
$_POST = [];

// Test data - minimal required fields only
$test_data = [
    'patient_id' => 1,
    'doctor_id' => 1,
    'appointment_date' => date('Y-m-d', strtotime('+1 day')),
    'appointment_time' => '10:00',
    'reason' => 'Test appointment'
];

echo "Test data: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";

// Create a temporary file to simulate php://input
$temp_input = tempnam(sys_get_temp_dir(), 'api_test');
file_put_contents($temp_input, json_encode($test_data));

// Override php://input stream
stream_wrapper_unregister("php");
stream_wrapper_register("php", "MockPhpStream");

class MockPhpStream {
    private $position = 0;
    private static $data = '';
    
    public static function setData($data) {
        self::$data = $data;
    }
    
    public function stream_open($path, $mode, $options, &$opened_path) {
        if ($path === 'php://input') {
            $this->position = 0;
            return true;
        }
        return false;
    }
    
    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }
    
    public function stream_stat() {
        return [];
    }
}

MockPhpStream::setData(json_encode($test_data));

// Start session and set up authentication
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'patient';
$_SESSION['logged_in'] = true;

echo "Environment setup complete\n";

// Capture API output
ob_start();

try {
    // Include the API
    include 'api/appointments.php';
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "\n=== API Response ===\n";
echo "Output length: " . strlen($output) . " bytes\n";

if (!empty($output)) {
    echo "Raw output:\n";
    var_dump($output);
    
    // Try to parse JSON
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nParsed JSON:\n";
        print_r($json);
        
        if (isset($json['success']) && $json['success']) {
            echo "\n✓ SUCCESS: Appointment created successfully!\n";
        } else {
            echo "\n✗ FAILED: " . ($json['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "\nJSON Parse Error: " . json_last_error_msg() . "\n";
        echo "First 200 chars: " . substr($output, 0, 200) . "\n";
    }
} else {
    echo "No output received\n";
}

// Cleanup
unlink($temp_input);
stream_wrapper_restore("php");

echo "\n=== Test Complete ===\n";
?>