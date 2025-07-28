<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Patients API</h1>";

// Test database connection
echo "<h2>1. Testing Database Connection</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit();
}

// Test if patients table exists
echo "<h2>2. Testing Patients Table</h2>";
try {
    $query = "SHOW TABLES LIKE 'patients'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ Patients table exists<br>";
    } else {
        echo "❌ Patients table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking patients table: " . $e->getMessage() . "<br>";
}

// Test if users table exists
echo "<h2>3. Testing Users Table</h2>";
try {
    $query = "SHOW TABLES LIKE 'users'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking users table: " . $e->getMessage() . "<br>";
}

// Test if doctors table exists
echo "<h2>4. Testing Doctors Table</h2>";
try {
    $query = "SHOW TABLES LIKE 'doctors'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ Doctors table exists<br>";
    } else {
        echo "❌ Doctors table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking doctors table: " . $e->getMessage() . "<br>";
}

// Count records in each table
echo "<h2>5. Counting Records</h2>";
try {
    $tables = ['users', 'patients', 'doctors'];
    foreach ($tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 $table table: " . $result['count'] . " records<br>";
    }
} catch (Exception $e) {
    echo "❌ Error counting records: " . $e->getMessage() . "<br>";
}

// Test the actual patients query
echo "<h2>6. Testing Patients Query</h2>";
try {
    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
             CONCAT(d.first_name, ' ', d.last_name) as assigned_doctor
             FROM patients p 
             JOIN users u ON p.user_id = u.id 
             LEFT JOIN doctors d ON p.assigned_doctor_id = d.id
             ORDER BY u.first_name, u.last_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Query executed successfully<br>";
    echo "📊 Found " . count($patients) . " patients<br>";
    
    if (count($patients) > 0) {
        echo "<h3>Patient List:</h3>";
        echo "<ul>";
        foreach ($patients as $patient) {
            echo "<li>" . $patient['first_name'] . " " . $patient['last_name'] . " (ID: " . $patient['id'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ No patients found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error executing patients query: " . $e->getMessage() . "<br>";
}

// Test doctors query
echo "<h2>7. Testing Doctors Query</h2>";
try {
    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.is_active 
             FROM doctors d 
             JOIN users u ON d.user_id = u.id 
             ORDER BY u.first_name, u.last_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Query executed successfully<br>";
    echo "📊 Found " . count($doctors) . " doctors<br>";
    
    if (count($doctors) > 0) {
        echo "<h3>Doctor List:</h3>";
        echo "<ul>";
        foreach ($doctors as $doctor) {
            echo "<li>Dr. " . $doctor['first_name'] . " " . $doctor['last_name'] . " (" . $doctor['specialization'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ No doctors found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error executing doctors query: " . $e->getMessage() . "<br>";
}

echo "<h2>8. Test API Response</h2>";
echo "<p>Testing the actual API response:</p>";
echo "<pre>";
ob_start();
include 'api/patients.php?action=list';
$output = ob_get_clean();
echo htmlspecialchars($output);
echo "</pre>";
?>