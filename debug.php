<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Hospital CRM Debug</h1>";

// Check PHP version
echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Test database connection
echo "<h2>Database Connection Test:</h2>";
try {
    $host = 'localhost';
    $db_name = 'hospital_crm';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['users', 'doctors', 'patients', 'appointments', 'prescriptions'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p style='color: blue;'>✅ Table '$table': $count records</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p style='color: orange;'>💡 Make sure MySQL is running and database 'hospital_crm' exists</p>";
}

// Check if files exist
echo "<h2>File Check:</h2>";
$files = [
    'includes/auth.php',
    'config/database.php',
    'login.php',
    'index.php',
    'logout.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

// Test session
echo "<h2>Session Test:</h2>";
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "<p style='color: green;'>✅ Sessions working</p>";
} else {
    echo "<p style='color: red;'>❌ Sessions not working</p>";
}

echo "<h2>Quick Links:</h2>";
echo "<a href='login.php' style='margin: 10px; padding: 10px; background: blue; color: white; text-decoration: none;'>Go to Login</a>";
echo "<a href='index.php' style='margin: 10px; padding: 10px; background: green; color: white; text-decoration: none;'>Go to Index</a>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f5f5f5;
}

h1, h2 {
    color: #333;
}

p {
    padding: 5px;
    margin: 5px 0;
}
</style>