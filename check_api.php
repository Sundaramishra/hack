<?php
// Check API dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== API Dependency Check ===\n";

// Test 1: Database config
echo "1. Testing database config...\n";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    if ($conn) {
        echo "   ✓ Database connection: SUCCESS\n";
    } else {
        echo "   ✗ Database connection: FAILED\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Auth class
echo "2. Testing Auth class...\n";
try {
    require_once 'classes/Auth.php';
    $auth = new Auth();
    echo "   ✓ Auth class: SUCCESS\n";
} catch (Exception $e) {
    echo "   ✗ Auth error: " . $e->getMessage() . "\n";
}

// Test 3: ApiBase class
echo "3. Testing ApiBase class...\n";
try {
    require_once 'api/ApiBase.php';
    echo "   ✓ ApiBase class: SUCCESS\n";
} catch (Exception $e) {
    echo "   ✗ ApiBase error: " . $e->getMessage() . "\n";
}

// Test 4: Session
echo "4. Testing session...\n";
try {
    session_start();
    echo "   ✓ Session: SUCCESS (ID: " . session_id() . ")\n";
} catch (Exception $e) {
    echo "   ✗ Session error: " . $e->getMessage() . "\n";
}

// Test 5: JSON functions
echo "5. Testing JSON functions...\n";
$test_data = ['test' => 'data'];
$json = json_encode($test_data);
$decoded = json_decode($json, true);
if ($decoded && $decoded['test'] === 'data') {
    echo "   ✓ JSON functions: SUCCESS\n";
} else {
    echo "   ✗ JSON functions: FAILED\n";
}

// Test 6: Try to create AppointmentsApi
echo "6. Testing AppointmentsApi instantiation...\n";
try {
    // Mock session data for testing
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    
    require_once 'api/appointments.php';
    echo "   ✓ AppointmentsApi file included successfully\n";
    
    // Try to create instance (this might fail due to headers already sent)
    // $api = new AppointmentsApi();
    // echo "   ✓ AppointmentsApi instance created\n";
    
} catch (Exception $e) {
    echo "   ✗ AppointmentsApi error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
?>