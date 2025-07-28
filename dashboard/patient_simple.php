<?php
// FORCE ALL ERRORS TO SHOW
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Simple Patient Test</title>";
echo "</head>";
echo "<body>";

echo "<h1 style='color: red;'>🔍 SIMPLE PATIENT TEST</h1>";

// Test 1: Basic PHP
echo "<h2>PHP Test:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "File: " . __FILE__ . "<br>";
echo "Directory: " . getcwd() . "<br><br>";

// Test 2: Session
echo "<h2>Session Test:</h2>";
try {
    session_start();
    echo "✅ Session started<br>";
    echo "Session ID: " . session_id() . "<br><br>";
} catch (Exception $e) {
    echo "❌ Session ERROR: " . $e->getMessage() . "<br><br>";
}

// Test 3: File Check
echo "<h2>File Check:</h2>";
$files = [
    '../classes/Auth.php',
    '../classes/User.php',
    '../config/database.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS<br>";
    } else {
        echo "❌ $file: MISSING!<br>";
    }
}
echo "<br>";

// Test 4: Include Test
echo "<h2>Include Test:</h2>";
try {
    require_once '../classes/Auth.php';
    echo "✅ Auth.php included<br>";
    
    $auth = new Auth();
    echo "✅ Auth class created<br>";
} catch (Exception $e) {
    echo "❌ Include ERROR: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 5: Database Test
echo "<h2>Database Test:</h2>";
try {
    require_once '../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database Connected!<br>";
    } else {
        echo "❌ Database Failed!<br>";
    }
} catch (Exception $e) {
    echo "❌ Database ERROR: " . $e->getMessage() . "<br>";
}

echo "<br>";
echo "<h2>✅ TEST COMPLETE</h2>";
echo "If you see this, everything is working!";

echo "</body>";
echo "</html>";
?>