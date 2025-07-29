<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch($method) {
        case 'GET':
            if ($action === 'all') {
                // Get all settings
                $stmt = $conn->prepare("SELECT * FROM website_settings ORDER BY setting_key");
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $settings
                ]);
                
            } elseif ($action === 'get') {
                // Get specific setting
                $key = $_GET['key'] ?? '';
                if (empty($key)) {
                    throw new Exception('Setting key is required');
                }
                
                $stmt = $conn->prepare("SELECT * FROM website_settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $setting = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$setting) {
                    throw new Exception('Setting not found');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $setting
                ]);
                
            } elseif ($action === 'public') {
                // Get public settings (for frontend)
                $publicKeys = [
                    'site_name', 'site_logo', 'favicon', 'primary_color', 
                    'secondary_color', 'accent_color', 'dark_mode_enabled'
                ];
                
                $placeholders = str_repeat('?,', count($publicKeys) - 1) . '?';
                $stmt = $conn->prepare("SELECT setting_key, setting_value FROM website_settings WHERE setting_key IN ($placeholders)");
                $stmt->execute($publicKeys);
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
                echo json_encode([
                    'success' => true,
                    'data' => $settings
                ]);
            }
            break;
            
        case 'POST':
            if ($action === 'update') {
                // Update settings
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (empty($input['settings'])) {
                    throw new Exception('Settings data is required');
                }
                
                $conn->beginTransaction();
                
                $stmt = $conn->prepare("UPDATE website_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                
                $updatedCount = 0;
                foreach ($input['settings'] as $key => $value) {
                    $stmt->execute([$value, $key]);
                    if ($stmt->rowCount() > 0) {
                        $updatedCount++;
                    }
                }
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => "Updated $updatedCount settings successfully",
                    'updated_count' => $updatedCount
                ]);
                
            } elseif ($action === 'upload') {
                // Handle file uploads (logo, favicon)
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed');
                }
                
                $settingKey = $_POST['setting_key'] ?? '';
                if (empty($settingKey)) {
                    throw new Exception('Setting key is required');
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon'];
                $fileType = $_FILES['file']['type'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Invalid file type. Only images are allowed.');
                }
                
                // Create assets directory if it doesn't exist
                $uploadDir = '../assets/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $filename = $settingKey . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $filename;
                $relativePath = '/assets/images/' . $filename;
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
                    // Update database with new file path
                    $stmt = $conn->prepare("UPDATE website_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                    $stmt->execute([$relativePath, $settingKey]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'File uploaded successfully',
                        'file_path' => $relativePath
                    ]);
                } else {
                    throw new Exception('Failed to save uploaded file');
                }
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>