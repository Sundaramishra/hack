<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic PDO connection
    echo "1. Testing direct PDO connection...<br>";
    $pdo = new PDO("mysql:host=localhost;dbname=hospital_crm", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Direct PDO connection successful<br>";
    
    // Test if tables exist
    echo "2. Checking if tables exist...<br>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "❌ No tables found. Please import the database schema first.<br>";
        echo "<strong>Run this SQL file:</strong> database/schema.sql<br>";
    } else {
        echo "✅ Found " . count($tables) . " tables:<br>";
        foreach ($tables as $table) {
            echo "- $table<br>";
        }
    }
    
    // Test users table
    if (in_array('users', $tables)) {
        echo "3. Testing users table...<br>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✅ Users table has " . $result['count'] . " users<br>";
        
        if ($result['count'] == 0) {
            echo "❌ No admin user found. Creating default admin...<br>";
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@hospital.com', $password, 'admin', 'System', 'Administrator']);
            echo "✅ Default admin created: admin@hospital.com / password123<br>";
        }
    }
    
    echo "<h3>✅ Database is ready!</h3>";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "<strong>Fix:</strong><br>";
    echo "1. Make sure MySQL is running<br>";
    echo "2. Create database 'hospital_crm'<br>";
    echo "3. Import database/schema.sql<br>";
    echo "4. Check username/password in config/database.php<br>";
}
?>

<hr>
<h2>Quick Setup Guide</h2>
<ol>
    <li><strong>Create Database:</strong> CREATE DATABASE hospital_crm;</li>
    <li><strong>Import Schema:</strong> mysql -u root -p hospital_crm < database/schema.sql</li>
    <li><strong>Login:</strong> admin@hospital.com / password123</li>
</ol>