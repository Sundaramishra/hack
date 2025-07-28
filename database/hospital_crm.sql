-- Hospital CRM Database Schema
CREATE DATABASE IF NOT EXISTS hospital_crm;
USE hospital_crm;

-- Users table (base table for all user types)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `theme_preference` enum('light','dark') DEFAULT 'light',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Doctors table
CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `license_number` varchar(50) NOT NULL UNIQUE,
  `qualification` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `consultation_fee` decimal(10,2) DEFAULT 0.00,
  `available_from` time DEFAULT '09:00:00',
  `available_to` time DEFAULT '17:00:00',
  `available_days` varchar(20) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
  `consultation_duration` int(11) DEFAULT 30,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`doctor_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_doctors_specialization` (`specialization`),
  KEY `idx_doctors_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Patients table
CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `patient_code` varchar(20) NOT NULL UNIQUE,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(15) DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `insurance_number` varchar(50) DEFAULT NULL,
  `assigned_doctor_id` int(11) DEFAULT NULL,
  `registration_date` date NOT NULL DEFAULT (curdate()),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`patient_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL,
  KEY `idx_patients_code` (`patient_code`),
  KEY `idx_patients_blood_group` (`blood_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Appointments table
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `duration` int(11) DEFAULT 30,
  `appointment_type` varchar(50) DEFAULT 'consultation',
  `status` enum('scheduled','completed','cancelled','no_show','rescheduled') DEFAULT 'scheduled',
  `reason` varchar(255) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_appointments_date` (`appointment_date`),
  KEY `idx_appointments_time` (`appointment_time`),
  KEY `idx_appointments_status` (`status`),
  KEY `idx_appointments_type` (`appointment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Prescriptions table
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `prescription_number` varchar(50) NOT NULL UNIQUE,
  `prescription_date` date NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  KEY `idx_prescriptions_number` (`prescription_number`),
  KEY `idx_prescriptions_date` (`prescription_date`),
  KEY `idx_prescriptions_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Prescription medicines table
CREATE TABLE `prescription_medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL,
  `medicine_name` varchar(200) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  KEY `idx_prescription_medicines_name` (`medicine_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Vitals table
CREATE TABLE `vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL COMMENT 'in kg',
  `height` decimal(5,2) DEFAULT NULL COMMENT 'in cm',
  `bmi` decimal(4,2) DEFAULT NULL,
  `blood_pressure_systolic` int(11) DEFAULT NULL,
  `blood_pressure_diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL COMMENT 'beats per minute',
  `temperature` decimal(4,2) DEFAULT NULL COMMENT 'in celsius',
  `respiratory_rate` int(11) DEFAULT NULL COMMENT 'breaths per minute',
  `oxygen_saturation` decimal(4,2) DEFAULT NULL COMMENT 'percentage',
  `blood_sugar` decimal(5,2) DEFAULT NULL COMMENT 'mg/dL',
  `cholesterol` decimal(5,2) DEFAULT NULL COMMENT 'mg/dL',
  `notes` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_vitals_patient` (`patient_id`),
  KEY `idx_vitals_date` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Custom vitals table (for additional vitals that admin can add)
CREATE TABLE `custom_vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vital_name` varchar(100) NOT NULL,
  `vital_unit` varchar(20) DEFAULT NULL,
  `normal_range_min` decimal(10,2) DEFAULT NULL,
  `normal_range_max` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_custom_vitals_name` (`vital_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Patient custom vitals values
CREATE TABLE `patient_custom_vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `custom_vital_id` int(11) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`custom_vital_id`) REFERENCES `custom_vitals` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_patient_custom_vitals` (`patient_id`, `custom_vital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role`) VALUES
('admin', 'admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin');

-- Insert sample doctors
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`) VALUES
('dr_sharma', 'dr.sharma@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rajesh', 'Sharma', '9876543210', 'doctor'),
('dr_patel', 'dr.patel@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Patel', '9876543211', 'doctor');

INSERT INTO `doctors` (`user_id`, `specialization`, `license_number`, `qualification`, `experience_years`, `consultation_fee`) VALUES
(2, 'Cardiology', 'MED001', 'MBBS, MD Cardiology', 10, 1500.00),
(3, 'Pediatrics', 'MED002', 'MBBS, MD Pediatrics', 8, 1200.00);

-- Insert sample patients
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `date_of_birth`, `gender`) VALUES
('patient_john', 'john@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '9876543212', 'patient', '1990-05-15', 'male'),
('patient_jane', 'jane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '9876543213', 'patient', '1985-08-20', 'female');

INSERT INTO `patients` (`user_id`, `patient_code`, `blood_group`, `assigned_doctor_id`) VALUES
(4, 'PAT001', 'O+', 1),
(5, 'PAT002', 'A+', 2);

-- Sample appointments
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_by_user_id`) VALUES
(1, 1, '2024-01-15', '10:00:00', 'Regular checkup', 'completed', 4),
(2, 2, '2024-01-16', '11:00:00', 'Fever and cough', 'completed', 5);

-- Sample prescriptions
INSERT INTO `prescriptions` (`appointment_id`, `patient_id`, `doctor_id`, `prescription_number`, `prescription_date`, `diagnosis`, `notes`) VALUES
(1, 1, 1, 'RX001-2024', '2024-01-15', 'Hypertension - mild', 'Patient advised to reduce salt intake and exercise regularly'),
(2, 2, 2, 'RX002-2024', '2024-01-16', 'Upper respiratory tract infection', 'Complete rest for 3 days, increase fluid intake');

-- Sample prescription medicines
INSERT INTO `prescription_medicines` (`prescription_id`, `medicine_name`, `dosage`, `frequency`, `duration`, `instructions`, `quantity`) VALUES
(1, 'Amlodipine', '5mg', 'Once daily', '30 days', 'Take in the morning with food', 30),
(1, 'Aspirin', '75mg', 'Once daily', '30 days', 'Take after dinner', 30),
(2, 'Amoxicillin', '500mg', 'Three times daily', '7 days', 'Take with food, complete the course', 21),
(2, 'Paracetamol', '500mg', 'As needed', '5 days', 'For fever, maximum 4 times per day', 20),
(2, 'Cough Syrup', '10ml', 'Three times daily', '5 days', 'Take after meals', 1);

-- Default password for all users is: Hospital@123