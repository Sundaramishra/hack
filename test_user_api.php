<?php
// Simple test to debug user API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing User API</h1>";

// Test 1: Check if API file exists
echo "<h2>1. API File Check:</h2>";
if (file_exists('api/users.php')) {
    echo "✅ api/users.php exists<br>";
} else {
    echo "❌ api/users.php missing<br>";
}

// Test 2: Check if required files exist
echo "<h2>2. Required Files Check:</h2>";
$files = [
    'config/database.php',
    'classes/Auth.php',
    'classes/User.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 3: Test API response
echo "<h2>3. API Response Test:</h2>";
echo "<form method='POST' action='api/users.php'>";
echo "<input type='hidden' name='test' value='1'>";
echo "<button type='submit'>Test API Response</button>";
echo "</form>";

// Test 4: Direct API call
echo "<h2>4. Direct API Call:</h2>";
try {
    // Simulate POST request
    $_POST = [
        'username' => 'testuser',
        'email' => 'test@test.com',
        'password' => 'Test123!',
        'role' => 'patient',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '1234567890',
        'gender' => 'male'
    ];
    
    // Capture output
    ob_start();
    include 'api/users.php';
    $output = ob_get_clean();
    
    echo "<h3>Raw Output:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    echo "<h3>JSON Decode Test:</h3>";
    $decoded = json_decode($output, true);
    if ($decoded) {
        echo "✅ JSON is valid<br>";
        echo "<pre>" . print_r($decoded, true) . "</pre>";
    } else {
        echo "❌ JSON is invalid<br>";
        echo "JSON Error: " . json_last_error_msg() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. PHP Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (error_reporting() ? 'ON' : 'OFF') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
?>