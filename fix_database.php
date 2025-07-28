<?php
/**
 * Quick Fix Script for Hospital CRM Database
 * This script fixes the missing assigned_doctor_id column issue
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "🔧 Hospital CRM Database Fix Script\n";
    echo "=====================================\n\n";
    
    // Check if the column already exists
    $check_query = "SHOW COLUMNS FROM patients LIKE 'assigned_doctor_id'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo "✓ Column 'assigned_doctor_id' already exists in patients table\n";
    } else {
        echo "⚠️  Column 'assigned_doctor_id' not found. Adding it now...\n";
        
        // Add the missing column
        $add_column_query = "ALTER TABLE `patients` ADD COLUMN `assigned_doctor_id` int(11) DEFAULT NULL AFTER `insurance_number`";
        $conn->exec($add_column_query);
        echo "✓ Added 'assigned_doctor_id' column to patients table\n";
        
        // Add foreign key constraint
        try {
            $add_fk_query = "ALTER TABLE `patients` ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`assigned_doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL";
            $conn->exec($add_fk_query);
            echo "✓ Added foreign key constraint for assigned_doctor_id\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "✓ Foreign key constraint already exists\n";
            } else {
                echo "⚠️  Could not add foreign key constraint: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Test the query that was failing
    echo "\n🧪 Testing the fixed query...\n";
    $test_query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                   CONCAT(du.first_name, ' ', du.last_name) as assigned_doctor
                   FROM patients p
                   JOIN users u ON p.user_id = u.id
                   LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id
                   LEFT JOIN users du ON d.user_id = du.id
                   ORDER BY u.first_name, u.last_name
                   LIMIT 1";
    
    $test_stmt = $conn->prepare($test_query);
    $test_stmt->execute();
    $result = $test_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ Query executed successfully!\n";
        echo "   Sample result: " . $result['first_name'] . " " . $result['last_name'] . "\n";
    } else {
        echo "✅ Query executed successfully (no results found, but no errors)\n";
    }
    
    // Optional: Assign some patients to doctors for testing
    echo "\n📋 Setting up sample doctor-patient assignments...\n";
    
    $assignments = [
        ['patient_id' => 1, 'doctor_id' => 1], // Alice Wilson -> John Smith (Cardiology)
        ['patient_id' => 2, 'doctor_id' => 1], // Bob Davis -> John Smith (Cardiology)
        ['patient_id' => 3, 'doctor_id' => 2], // Carol Miller -> Sarah Johnson (Neurology)
        ['patient_id' => 4, 'doctor_id' => 2], // David Garcia -> Sarah Johnson (Neurology)
        ['patient_id' => 5, 'doctor_id' => 3], // Emma Taylor -> Michael Brown (Pediatrics)
    ];
    
    foreach ($assignments as $assignment) {
        $update_query = "UPDATE patients SET assigned_doctor_id = :doctor_id WHERE patient_id = :patient_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':doctor_id', $assignment['doctor_id']);
        $update_stmt->bindParam(':patient_id', $assignment['patient_id']);
        
        if ($update_stmt->execute()) {
            echo "✓ Assigned patient {$assignment['patient_id']} to doctor {$assignment['doctor_id']}\n";
        }
    }
    
    echo "\n🎉 Database fix completed successfully!\n";
    echo "Your Hospital CRM should now work without the 'assigned_doctor_id' error.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>