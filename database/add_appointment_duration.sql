-- Migration: Add duration and appointment_type columns to appointments table
-- Run this to add appointment duration functionality

USE hospital_crm;

-- Add duration column (in minutes)
ALTER TABLE `appointments` 
ADD COLUMN `duration` int(11) DEFAULT 30 AFTER `status`;

-- Add appointment_type column
ALTER TABLE `appointments` 
ADD COLUMN `appointment_type` varchar(50) DEFAULT 'consultation' AFTER `duration`;

-- Update existing appointments with default values
UPDATE `appointments` SET 
    `duration` = 30,
    `appointment_type` = 'consultation'
WHERE `duration` IS NULL OR `appointment_type` IS NULL;

-- Add index for better performance
CREATE INDEX `idx_appointments_duration` ON `appointments` (`duration`);
CREATE INDEX `idx_appointments_type` ON `appointments` (`appointment_type`);

SELECT 'Migration completed successfully! Added duration and appointment_type columns.' as status;