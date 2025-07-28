-- Hospital CRM Database Schema
CREATE DATABASE IF NOT EXISTS hospital_crm;
USE hospital_crm;

-- Users table (Admin, Doctor, Patient)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor specific information
CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    qualification TEXT,
    experience_years INT DEFAULT 0,
    consultation_fee DECIMAL(10,2) DEFAULT 0.00,
    available_days VARCHAR(100), -- JSON format: ["monday", "tuesday", ...]
    available_time_start TIME,
    available_time_end TIME,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Patient specific information
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    blood_group VARCHAR(5),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(15),
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    insurance_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Doctor-Patient Assignment
CREATE TABLE doctor_patient_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (doctor_id, patient_id)
);

-- Appointments
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    reason VARCHAR(255),
    notes TEXT,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

-- Prescriptions
CREATE TABLE prescriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT,
    medications TEXT, -- JSON format for multiple medications
    instructions TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Vital Signs
CREATE TABLE vital_signs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    recorded_by_user_id INT NOT NULL,
    blood_pressure_systolic INT,
    blood_pressure_diastolic INT,
    heart_rate INT,
    temperature DECIMAL(4,2),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    oxygen_saturation INT,
    respiratory_rate INT,
    blood_sugar DECIMAL(5,2),
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id)
);

-- Vital Types (for dynamic addition by admin)
CREATE TABLE vital_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    unit VARCHAR(20),
    normal_range_min DECIMAL(10,2),
    normal_range_max DECIMAL(10,2),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patient Vitals (dynamic vitals)
CREATE TABLE patient_vitals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    vital_type_id INT NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    recorded_by_user_id INT NOT NULL,
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (vital_type_id) REFERENCES vital_types(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id)
);

-- Sessions table for login management
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password, role, first_name, last_name, phone) VALUES 
('admin', 'admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', '1234567890');

-- Insert default vital types
INSERT INTO vital_types (name, unit, normal_range_min, normal_range_max, description) VALUES
('Blood Pressure (Systolic)', 'mmHg', 90, 140, 'Systolic blood pressure'),
('Blood Pressure (Diastolic)', 'mmHg', 60, 90, 'Diastolic blood pressure'),
('Heart Rate', 'bpm', 60, 100, 'Heart rate per minute'),
('Temperature', '°C', 36.1, 37.2, 'Body temperature in Celsius'),
('Weight', 'kg', 0, 300, 'Body weight in kilograms'),
('Height', 'cm', 0, 250, 'Height in centimeters'),
('Oxygen Saturation', '%', 95, 100, 'Blood oxygen saturation percentage'),
('Respiratory Rate', 'breaths/min', 12, 20, 'Breaths per minute'),
('Blood Sugar', 'mg/dL', 70, 140, 'Blood glucose level');

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_doctor ON appointments(doctor_id);
CREATE INDEX idx_appointments_patient ON appointments(patient_id);
CREATE INDEX idx_vitals_patient ON vital_signs(patient_id);
CREATE INDEX idx_vitals_date ON vital_signs(recorded_at);
CREATE INDEX idx_patient_vitals_patient ON patient_vitals(patient_id);
CREATE INDEX idx_patient_vitals_date ON patient_vitals(recorded_at);