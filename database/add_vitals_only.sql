-- Add vitals tables to existing hospital_crm database
USE hospital_crm;

-- Website Settings Table (if not exists)
CREATE TABLE IF NOT EXISTS `website_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL UNIQUE,
    `setting_value` text DEFAULT NULL,
    `setting_type` enum('text','color','file','boolean') DEFAULT 'text',
    `description` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Vital types table
CREATE TABLE IF NOT EXISTS `vital_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `normal_range_min` decimal(10,2) DEFAULT NULL,
  `normal_range_max` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Patient vitals table
CREATE TABLE IF NOT EXISTS `patient_vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `vital_type_id` int(11) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `recorded_date` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`vital_type_id`) REFERENCES `vital_types` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_patient_vitals_date` (`recorded_date`),
  KEY `idx_patient_vitals_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default website settings (only if not exists)
INSERT IGNORE INTO `website_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'Hospital CRM', 'text', 'Website title/name'),
('site_logo', '/assets/images/logo.png', 'file', 'Website logo path'),
('favicon', '/assets/images/favicon.ico', 'file', 'Website favicon path'),
('primary_color', '#3B82F6', 'color', 'Primary theme color'),
('secondary_color', '#1E40AF', 'color', 'Secondary theme color'),
('accent_color', '#10B981', 'color', 'Accent color'),
('contact_email', 'admin@hospital.com', 'text', 'Contact email address'),
('contact_phone', '+1234567890', 'text', 'Contact phone number'),
('address', '123 Hospital Street, Medical City', 'text', 'Hospital address'),
('dark_mode_enabled', 'true', 'boolean', 'Enable dark mode toggle'),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status');

-- Insert default vital types (only if not exists)
INSERT IGNORE INTO `vital_types` (`name`, `unit`, `normal_range_min`, `normal_range_max`, `description`, `is_default`) VALUES
('Blood Pressure Systolic', 'mmHg', 90, 120, 'Systolic blood pressure', 1),
('Blood Pressure Diastolic', 'mmHg', 60, 80, 'Diastolic blood pressure', 1),
('Heart Rate', 'bpm', 60, 100, 'Heart rate per minute', 1),
('Body Temperature', '°F', 97.0, 99.0, 'Body temperature in Fahrenheit', 1),
('Weight', 'kg', 40, 150, 'Body weight in kilograms', 1),
('Height', 'cm', 100, 220, 'Body height in centimeters', 1),
('Blood Sugar', 'mg/dL', 70, 140, 'Blood glucose level', 1),
('Oxygen Saturation', '%', 95, 100, 'Blood oxygen saturation', 1),
('Respiratory Rate', '/min', 12, 20, 'Breaths per minute', 1),
('BMI', 'kg/m²', 18.5, 24.9, 'Body Mass Index', 1);