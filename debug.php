<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug started...<br>";

try {
    echo "1. Testing session start...<br>";
    session_start();
    echo "✅ Session started successfully<br>";
    
    echo "2. Testing database config...<br>";
    require_once 'config/database.php';
    echo "✅ Database config loaded<br>";
    
    echo "3. Testing database connection...<br>";
    $database = new Database();
    $conn = $database->getConnection();
    if ($conn) {
        echo "✅ Database connected successfully<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    echo "4. Testing Auth class...<br>";
    require_once 'classes/Auth.php';
    $auth = new Auth();
    echo "✅ Auth class loaded<br>";
    
    echo "5. Testing User class...<br>";
    require_once 'classes/User.php';
    $user_manager = new User();
    echo "✅ User class loaded<br>";
    
    echo "6. Testing login status...<br>";
    if ($auth->isLoggedIn()) {
        echo "✅ User is logged in<br>";
        $current_user = $auth->getCurrentUser();
        echo "User role: " . $current_user['role'] . "<br>";
    } else {
        echo "❌ User is not logged in<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "Debug completed!";
?>