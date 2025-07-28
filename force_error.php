<?php
// Force all errors to show
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>🔍 ERROR FORCE TEST</h1>";
echo "<p>If you see this, PHP is working!</p>";

// Test database
echo "<h2>Database Test:</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database OK<br>";
    } else {
        echo "❌ Database FAILED<br>";
    }
} catch (Exception $e) {
    echo "❌ Database ERROR: " . $e->getMessage() . "<br>";
}

// Test classes
echo "<h2>Classes Test:</h2>";
try {
    require_once 'classes/Auth.php';
    echo "✅ Auth.php OK<br>";
} catch (Exception $e) {
    echo "❌ Auth.php ERROR: " . $e->getMessage() . "<br>";
}

try {
    require_once 'classes/User.php';
    echo "✅ User.php OK<br>";
} catch (Exception $e) {
    echo "❌ User.php ERROR: " . $e->getMessage() . "<br>";
}

// Test dashboard files
echo "<h2>Dashboard Files:</h2>";
$files = ['dashboard/doctor.php', 'dashboard/patient.php', 'dashboard/admin.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file EXISTS<br>";
    } else {
        echo "❌ $file MISSING<br>";
    }
}

// Force an error to test
echo "<h2>Error Test:</h2>";
echo "Testing error display...<br>";
// Uncomment next line to force an error
// echo $undefined_variable;

echo "<h2>✅ DONE!</h2>";
?>