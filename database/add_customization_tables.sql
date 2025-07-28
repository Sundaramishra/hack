-- Add customization tables to hospital_crm database
USE hospital_crm;

-- Website settings table
CREATE TABLE `website_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','color','file','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default website settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'Hospital CRM', 'text', 'Website name/title'),
('site_logo', '', 'file', 'Website logo image path'),
('site_favicon', '', 'file', 'Website favicon path'),
('primary_color', '#3B82F6', 'color', 'Primary theme color'),
('secondary_color', '#6366F1', 'color', 'Secondary theme color'),
('accent_color', '#10B981', 'color', 'Accent color for success states'),
('danger_color', '#EF4444', 'color', 'Color for error/danger states'),
('warning_color', '#F59E0B', 'color', 'Color for warning states'),
('info_color', '#06B6D4', 'color', 'Color for info states'),
('sidebar_color', '#1F2937', 'color', 'Sidebar background color'),
('header_gradient_start', '#3B82F6', 'color', 'Header gradient start color'),
('header_gradient_end', '#6366F1', 'color', 'Header gradient end color'),
('custom_css', '', 'text', 'Custom CSS code'),
('color_scheme', '{"light": {"bg": "#F9FAFB", "text": "#111827", "card": "#FFFFFF"}, "dark": {"bg": "#111827", "text": "#F9FAFB", "card": "#1F2937"}}', 'json', 'Light and dark theme colors');

-- Create uploads directory structure table
CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_file_type` (`file_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;