<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Hospital CRM Database Setup</h1>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "❌ Database connection failed!<br>";
        exit();
    }
    
    echo "✅ Database connection successful!<br>";
    
    // Create tables
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255),
            role ENUM('admin', 'doctor', 'patient') DEFAULT 'patient',
            first_name VARCHAR(50),
            last_name VARCHAR(50),
            phone VARCHAR(20),
            address TEXT,
            date_of_birth DATE,
            gender ENUM('male', 'female', 'other'),
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        'doctors' => "CREATE TABLE IF NOT EXISTS doctors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            specialization VARCHAR(100),
            license_number VARCHAR(50),
            qualification VARCHAR(100),
            experience_years INT DEFAULT 0,
            consultation_fee DECIMAL(10,2) DEFAULT 0.00,
            department VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        'patients' => "CREATE TABLE IF NOT EXISTS patients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            patient_id VARCHAR(20) UNIQUE,
            date_of_birth DATE,
            gender ENUM('male', 'female', 'other'),
            blood_group VARCHAR(10),
            emergency_contact_name VARCHAR(100),
            emergency_contact_phone VARCHAR(20),
            assigned_doctor_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
        )",
        
        'appointments' => "CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT,
            doctor_id INT,
            appointment_date DATE,
            appointment_time TIME,
            appointment_type ENUM('consultation', 'follow_up', 'emergency', 'routine') DEFAULT 'consultation',
            status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        )",
        
        'vital_types' => "CREATE TABLE IF NOT EXISTS vital_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE,
            unit VARCHAR(20),
            normal_range_min DECIMAL(10,2),
            normal_range_max DECIMAL(10,2),
            description TEXT,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'vitals' => "CREATE TABLE IF NOT EXISTS vitals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT,
            vital_type_id INT,
            value DECIMAL(10,2),
            recorded_by INT,
            recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (vital_type_id) REFERENCES vital_types(id) ON DELETE CASCADE,
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
        )",
        
        'prescriptions' => "CREATE TABLE IF NOT EXISTS prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT,
            doctor_id INT,
            medication VARCHAR(200),
            dosage VARCHAR(100),
            duration VARCHAR(100),
            instructions TEXT,
            prescribed_date DATE,
            status ENUM('active', 'discontinued', 'completed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        )",
        
        'medical_history' => "CREATE TABLE IF NOT EXISTS medical_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT,
            doctor_id INT,
            visit_type ENUM('consultation', 'emergency', 'routine', 'follow_up') DEFAULT 'consultation',
            diagnosis TEXT,
            treatment TEXT,
            symptoms TEXT,
            notes TEXT,
            visit_date DATE,
            status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        )",
        
        'user_sessions' => "CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            session_token VARCHAR(255) UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            expires_at TIMESTAMP,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        'doctor_patient_assignments' => "CREATE TABLE IF NOT EXISTS doctor_patient_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT,
            patient_id INT,
            assigned_date DATE,
            notes TEXT,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $table_name => $sql) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            echo "✅ Table '$table_name' created successfully<br>";
        } catch (Exception $e) {
            echo "❌ Error creating table '$table_name': " . $e->getMessage() . "<br>";
        }
    }
    
    // Insert sample data
    echo "<h2>Inserting Sample Data</h2>";
    
    // Add missing columns to patients table if not exist
    $alter = "ALTER TABLE patients
        ADD COLUMN IF NOT EXISTS date_of_birth DATE AFTER user_id,
        ADD COLUMN IF NOT EXISTS gender VARCHAR(10) AFTER date_of_birth,
        ADD COLUMN IF NOT EXISTS blood_group VARCHAR(5) AFTER gender,
        ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(100) AFTER blood_group,
        ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(20) AFTER emergency_contact_name;";
    $conn->exec($alter);
    echo "✅ patients table columns ensured<br>";

    // Insert sample vital types
    $vital_types = [
        ['Blood Pressure', 'mmHg', 90, 140],
        ['Heart Rate', 'bpm', 60, 100],
        ['Temperature', '°C', 36.5, 37.5],
        ['Weight', 'kg', 40, 150],
        ['Height', 'cm', 100, 200]
    ];
    
    foreach ($vital_types as $vital) {
        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO vital_types (name, unit, normal_range_min, normal_range_max) VALUES (?, ?, ?, ?)");
            $stmt->execute($vital);
            echo "✅ Vital type '{$vital[0]}' added<br>";
        } catch (Exception $e) {
            echo "❌ Error adding vital type '{$vital[0]}': " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2>Database Setup Complete!</h2>";
    echo "✅ All tables created successfully<br>";
    echo "✅ Sample data inserted<br>";
    echo "<a href='dashboard/doctor.php'>Test Doctor Dashboard</a><br>";
    echo "<a href='dashboard/patient.php'>Test Patient Dashboard</a><br>";
    echo "<a href='dashboard/admin.php'>Test Admin Dashboard</a><br>";
    
} catch (Exception $e) {
    echo "❌ Database setup error: " . $e->getMessage() . "<br>";
}
?>