-- Migration: Add assigned_doctor_id column to patients table
-- Run this if you already have the database set up and need to add the missing column

USE hospital_crm;

-- Add the assigned_doctor_id column to patients table
ALTER TABLE `patients` 
ADD COLUMN `assigned_doctor_id` int(11) DEFAULT NULL AFTER `insurance_number`;

-- Add foreign key constraint
ALTER TABLE `patients`
ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`assigned_doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL;

-- Optional: Assign some patients to doctors for testing
-- Uncomment the lines below if you want to set up some sample assignments

-- UPDATE patients SET assigned_doctor_id = 1 WHERE patient_id IN (1, 2); -- Assign to John Smith (Cardiology)
-- UPDATE patients SET assigned_doctor_id = 2 WHERE patient_id IN (3, 4); -- Assign to Sarah Johnson (Neurology)  
-- UPDATE patients SET assigned_doctor_id = 3 WHERE patient_id = 5;       -- Assign to Michael Brown (Pediatrics)

SELECT 'Migration completed successfully!' as status;