<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Add Sample Data</h1>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "✅ Database connected successfully<br>";
    
    // Start transaction
    $conn->beginTransaction();
    
    // Add sample users
    echo "<h2>Adding Sample Users</h2>";
    
    // Add admin user
    $admin_query = "INSERT INTO users (first_name, last_name, username, email, password, role, is_active) 
                    VALUES ('Admin', 'User', 'admin', 'admin@hospital.com', :password, 'admin', 1)";
    $admin_stmt = $conn->prepare($admin_query);
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_stmt->bindParam(':password', $admin_password);
    $admin_stmt->execute();
    echo "✅ Admin user added<br>";
    
    // Add doctor users
    $doctors = [
        ['John', 'Smith', 'john.smith@hospital.com', 'Cardiology'],
        ['Sarah', 'Johnson', 'sarah.johnson@hospital.com', 'Neurology'],
        ['Michael', 'Brown', 'michael.brown@hospital.com', 'Pediatrics']
    ];
    
    foreach ($doctors as $doctor) {
        // Add user
        $user_query = "INSERT INTO users (first_name, last_name, username, email, password, role, is_active) 
                      VALUES (:first_name, :last_name, :username, :email, :password, 'doctor', 1)";
        $user_stmt = $conn->prepare($user_query);
        $username = strtolower($doctor[0]) . '.' . strtolower($doctor[1]);
        $doctor_password = password_hash('doctor123', PASSWORD_DEFAULT);
        $user_stmt->bindParam(':first_name', $doctor[0]);
        $user_stmt->bindParam(':last_name', $doctor[1]);
        $user_stmt->bindParam(':username', $username);
        $user_stmt->bindParam(':email', $doctor[2]);
        $user_stmt->bindParam(':password', $doctor_password);
        $user_stmt->execute();
        
        $user_id = $conn->lastInsertId();
        
        // Add doctor record
        $doctor_query = "INSERT INTO doctors (user_id, specialization, license_number, experience_years) 
                        VALUES (:user_id, :specialization, :license, :experience)";
        $doctor_stmt = $conn->prepare($doctor_query);
        $license = 'LIC' . rand(1000, 9999);
        $experience = rand(5, 20);
        $doctor_stmt->bindParam(':user_id', $user_id);
        $doctor_stmt->bindParam(':specialization', $doctor[3]);
        $doctor_stmt->bindParam(':license', $license);
        $doctor_stmt->bindParam(':experience', $experience);
        $doctor_stmt->execute();
        
        echo "✅ Doctor {$doctor[0]} {$doctor[1]} added<br>";
    }
    
    // Add patient users
    $patients = [
        ['Alice', 'Wilson', 'alice.wilson@email.com', '1990-05-15', 'Female', 'A+'],
        ['Bob', 'Davis', 'bob.davis@email.com', '1985-08-22', 'Male', 'O+'],
        ['Carol', 'Miller', 'carol.miller@email.com', '1992-12-10', 'Female', 'B+'],
        ['David', 'Garcia', 'david.garcia@email.com', '1988-03-25', 'Male', 'AB+'],
        ['Emma', 'Taylor', 'emma.taylor@email.com', '1995-07-18', 'Female', 'A-']
    ];
    
    echo "<h2>Adding Sample Patients</h2>";
    
    foreach ($patients as $patient) {
        // Add user
        $user_query = "INSERT INTO users (first_name, last_name, username, email, password, role, is_active) 
                      VALUES (:first_name, :last_name, :username, :email, :password, 'patient', 1)";
        $user_stmt = $conn->prepare($user_query);
        $username = strtolower($patient[0]) . '.' . strtolower($patient[1]);
        $patient_password = password_hash('patient123', PASSWORD_DEFAULT);
        $user_stmt->bindParam(':first_name', $patient[0]);
        $user_stmt->bindParam(':last_name', $patient[1]);
        $user_stmt->bindParam(':username', $username);
        $user_stmt->bindParam(':email', $patient[2]);
        $user_stmt->bindParam(':password', $patient_password);
        $user_stmt->execute();
        
        $user_id = $conn->lastInsertId();
        
        // Add patient record
        $patient_query = "INSERT INTO patients (user_id, date_of_birth, gender, blood_group, emergency_contact_name, emergency_contact_phone) 
                         VALUES (:user_id, :dob, :gender, :blood_group, :emergency_name, :emergency_phone)";
        $patient_stmt = $conn->prepare($patient_query);
        $emergency_name = $patient[0] . ' ' . $patient[1] . ' Sr.';
        $emergency_phone = '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999);
        $patient_stmt->bindParam(':user_id', $user_id);
        $patient_stmt->bindParam(':dob', $patient[3]);
        $patient_stmt->bindParam(':gender', $patient[4]);
        $patient_stmt->bindParam(':blood_group', $patient[5]);
        $patient_stmt->bindParam(':emergency_name', $emergency_name);
        $patient_stmt->bindParam(':emergency_phone', $emergency_phone);
        $patient_stmt->execute();
        
        echo "✅ Patient {$patient[0]} {$patient[1]} added<br>";
    }
    
    // Commit transaction
    $conn->commit();
    echo "<h2>✅ Sample data added successfully!</h2>";
    
    // Show summary
    echo "<h3>Summary:</h3>";
    $tables = ['users', 'patients', 'doctors'];
    foreach ($tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 $table table: " . $result['count'] . " records<br>";
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>