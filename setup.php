<?php
/**
 * Hospital CRM Setup Script
 * This script helps initialize the database with sample data
 */

// Database configuration
$host = 'localhost';
$db_name = 'hospital_crm';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✓ Database '$db_name' created/verified\n";
    
    // Switch to the database
    $pdo->exec("USE `$db_name`");
    
    // Check if we should load the updated schema with sample data
    $sql_file = 'database/hospital_crm_updated.sql';
    
    if (file_exists($sql_file)) {
        echo "✓ Found updated SQL file with sample data\n";
        
        // Read and execute SQL file
        $sql_content = file_get_contents($sql_file);
        
        // Remove the database creation and USE statements since we already handled that
        $sql_content = preg_replace('/CREATE DATABASE.*?;/i', '', $sql_content);
        $sql_content = preg_replace('/USE.*?;/i', '', $sql_content);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(\/\*|--|\!)/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Skip duplicate entry errors and other non-critical errors
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✓ Database schema and sample data loaded successfully\n";
    } else {
        echo "✗ SQL file not found: $sql_file\n";
        exit(1);
    }
    
    // Verify tables were created
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Created " . count($tables) . " tables: " . implode(', ', $tables) . "\n";
    
    // Verify sample data
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $doctor_count = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $patient_count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    
    echo "✓ Sample data loaded:\n";
    echo "  - Users: $user_count\n";
    echo "  - Doctors: $doctor_count\n";
    echo "  - Patients: $patient_count\n";
    
    echo "\n🎉 Hospital CRM setup completed successfully!\n";
    echo "\nDefault login credentials:\n";
    echo "Admin: admin / password123\n";
    echo "Doctor: john.smith / password123\n";
    echo "Patient: alice.wilson / password123\n";
    echo "\nYou can now access the system at: http://localhost/hospital-crm/\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>