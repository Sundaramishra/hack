-- Website Settings Table for Dynamic Configuration
CREATE TABLE IF NOT EXISTS website_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'color', 'file', 'boolean') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default website settings
INSERT INTO website_settings (setting_key, setting_value, setting_type, description) VALUES
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
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);