<?php
// Settings Loader - Load website settings dynamically
function loadWebsiteSettings() {
    try {
        require_once 'database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT setting_key, setting_value FROM website_settings";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $settings;
    } catch (Exception $e) {
        // Return default settings if database fails
        return [
            'site_name' => 'Hospital CRM',
            'site_logo' => '',
            'site_favicon' => '',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#6366F1'
        ];
    }
}

function generatePageHead($pageTitle = '', $additionalCSS = '') {
    $settings = loadWebsiteSettings();
    $siteName = $settings['site_name'] ?? 'Hospital CRM';
    $favicon = $settings['site_favicon'] ?? '';
    $logo = $settings['site_logo'] ?? '';
    
    $title = $pageTitle ? $pageTitle . ' - ' . $siteName : $siteName;
    
    $head = '
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../handlers/settings.php?action=css">';
    
    if ($favicon) {
        $head .= '
    <link rel="icon" type="image/x-icon" href="../' . htmlspecialchars($favicon) . '">';
    }
    
    if ($additionalCSS) {
        $head .= '
    <style>' . $additionalCSS . '</style>';
    }
    
    $head .= '
    <script>
        tailwind.config = {
            darkMode: "class",
        }
        
        // Load website settings globally
        window.websiteSettings = ' . json_encode($settings) . ';
    </script>';
    
    return $head;
}

function getSiteName() {
    $settings = loadWebsiteSettings();
    return $settings['site_name'] ?? 'Hospital CRM';
}

function getSiteLogo() {
    $settings = loadWebsiteSettings();
    return $settings['site_logo'] ?? '';
}

function renderSiteLogo($classes = 'w-10 h-10', $fallbackIcon = 'fas fa-hospital') {
    $logo = getSiteLogo();
    $siteName = getSiteName();
    
    if ($logo && file_exists('../' . $logo)) {
        return '<img src="../' . htmlspecialchars($logo) . '" alt="' . htmlspecialchars($siteName) . '" class="' . $classes . '">';
    } else {
        // Fallback to icon
        return '<div class="' . $classes . ' bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="' . $fallbackIcon . ' text-white"></i>
                </div>';
    }
}
?>