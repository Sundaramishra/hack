<?php
class WebsiteSettings {
    private static $settings = null;
    private static $db = null;
    
    public static function init() {
        if (self::$db === null) {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            self::$db = $database->getConnection();
        }
        
        if (self::$settings === null) {
            self::loadSettings();
        }
    }
    
    private static function loadSettings() {
        try {
            $stmt = self::$db->prepare("SELECT setting_key, setting_value FROM website_settings");
            $stmt->execute();
            self::$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            // Fallback to default values if table doesn't exist
            self::$settings = [
                'site_name' => 'Hospital CRM',
                'site_logo' => '/assets/images/logo.png',
                'favicon' => '/assets/images/favicon.ico',
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E40AF',
                'accent_color' => '#10B981',
                'contact_email' => 'admin@hospital.com',
                'contact_phone' => '+1234567890',
                'address' => '123 Hospital Street, Medical City',
                'dark_mode_enabled' => 'true',
                'maintenance_mode' => 'false'
            ];
        }
    }
    
    public static function get($key, $default = null) {
        self::init();
        return self::$settings[$key] ?? $default;
    }
    
    public static function getAll() {
        self::init();
        return self::$settings;
    }
    
    public static function set($key, $value) {
        self::init();
        
        try {
            $stmt = self::$db->prepare("UPDATE website_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
            
            // Update cached value
            self::$settings[$key] = $value;
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function refresh() {
        self::$settings = null;
        self::init();
    }
    
    // Helper methods for common settings
    public static function getSiteName() {
        return self::get('site_name', 'Hospital CRM');
    }
    
    public static function getSiteLogo() {
        return self::get('site_logo', '/assets/images/logo.png');
    }
    
    public static function getFavicon() {
        return self::get('favicon', '/assets/images/favicon.ico');
    }
    
    public static function getPrimaryColor() {
        return self::get('primary_color', '#3B82F6');
    }
    
    public static function getSecondaryColor() {
        return self::get('secondary_color', '#1E40AF');
    }
    
    public static function getAccentColor() {
        return self::get('accent_color', '#10B981');
    }
    
    public static function isDarkModeEnabled() {
        return self::get('dark_mode_enabled', 'true') === 'true';
    }
    
    public static function isMaintenanceMode() {
        return self::get('maintenance_mode', 'false') === 'true';
    }
    
    public static function getContactEmail() {
        return self::get('contact_email', 'admin@hospital.com');
    }
    
    public static function getContactPhone() {
        return self::get('contact_phone', '+1234567890');
    }
    
    public static function getAddress() {
        return self::get('address', '123 Hospital Street, Medical City');
    }
}
?>