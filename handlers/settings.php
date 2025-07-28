<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireRole('admin'); // Only admin can change settings

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'all';

        if ($action === 'all') {
            // Get all website settings
            $query = "SELECT setting_key, setting_value, setting_type, description FROM website_settings ORDER BY setting_key";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert to key-value pairs
            $settingsArray = [];
            foreach ($settings as $setting) {
                $settingsArray[$setting['setting_key']] = [
                    'value' => $setting['setting_value'],
                    'type' => $setting['setting_type'],
                    'description' => $setting['description']
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => $settingsArray
            ]);

        } elseif ($action === 'get') {
            $key = $_GET['key'] ?? null;
            
            if (!$key) {
                throw new Exception('Setting key is required');
            }

            $query = "SELECT setting_value FROM website_settings WHERE setting_key = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$key]);
            $value = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => $value
            ]);

        } elseif ($action === 'css') {
            // Generate dynamic CSS based on current settings
            $query = "SELECT setting_key, setting_value FROM website_settings WHERE setting_type = 'color' OR setting_key = 'custom_css'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $css = generateDynamicCSS($settings);
            
            header('Content-Type: text/css');
            echo $css;
            exit;
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? 'update';

        if ($action === 'upload') {
            // Handle file upload (logo/favicon)
            $uploadType = $_POST['upload_type'] ?? null; // 'logo' or 'favicon'
            
            if (!$uploadType || !in_array($uploadType, ['logo', 'favicon'])) {
                throw new Exception('Invalid upload type');
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed');
            }

            $file = $_FILES['file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if ($uploadType === 'favicon') {
                $allowedTypes[] = 'image/x-icon';
                $allowedTypes[] = 'image/vnd.microsoft.icon';
            }

            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only images are allowed.');
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = '../uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $uploadType . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Save file info to database
                $fileQuery = "INSERT INTO uploaded_files (original_name, file_name, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($fileQuery);
                $stmt->execute([
                    $file['name'],
                    $fileName,
                    'uploads/' . $fileName,
                    $file['type'],
                    $file['size'],
                    $_SESSION['user_id']
                ]);

                // Update website setting
                $settingKey = $uploadType === 'logo' ? 'site_logo' : 'site_favicon';
                $updateQuery = "UPDATE website_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute(['uploads/' . $fileName, $_SESSION['user_id'], $settingKey]);

                echo json_encode([
                    'success' => true,
                    'message' => ucfirst($uploadType) . ' uploaded successfully',
                    'file_path' => 'uploads/' . $fileName
                ]);
            } else {
                throw new Exception('Failed to save uploaded file');
            }

        } elseif ($action === 'update') {
            $input = json_decode(file_get_contents('php://input'), true);
            $settings = $input['settings'] ?? [];

            if (empty($settings)) {
                throw new Exception('No settings provided');
            }

            $conn->beginTransaction();

            try {
                foreach ($settings as $key => $value) {
                    // Validate color values
                    if (strpos($key, 'color') !== false && !preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                        throw new Exception("Invalid color format for $key");
                    }

                    $updateQuery = "UPDATE website_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->execute([$value, $_SESSION['user_id'], $key]);
                }

                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Settings updated successfully'
                ]);

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        }

    } elseif ($method === 'DELETE') {
        // Delete uploaded file
        $fileType = $_GET['type'] ?? null;
        
        if (!$fileType || !in_array($fileType, ['logo', 'favicon'])) {
            throw new Exception('Invalid file type');
        }

        $settingKey = $fileType === 'logo' ? 'site_logo' : 'site_favicon';
        
        // Get current file path
        $query = "SELECT setting_value FROM website_settings WHERE setting_key = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$settingKey]);
        $filePath = $stmt->fetchColumn();

        if ($filePath && file_exists('../' . $filePath)) {
            unlink('../' . $filePath);
        }

        // Clear setting
        $updateQuery = "UPDATE website_settings SET setting_value = '', updated_by = ? WHERE setting_key = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([$_SESSION['user_id'], $settingKey]);

        echo json_encode([
            'success' => true,
            'message' => ucfirst($fileType) . ' removed successfully'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateDynamicCSS($settings) {
    $primaryColor = $settings['primary_color'] ?? '#3B82F6';
    $secondaryColor = $settings['secondary_color'] ?? '#6366F1';
    $accentColor = $settings['accent_color'] ?? '#10B981';
    $dangerColor = $settings['danger_color'] ?? '#EF4444';
    $warningColor = $settings['warning_color'] ?? '#F59E0B';
    $infoColor = $settings['info_color'] ?? '#06B6D4';
    $sidebarColor = $settings['sidebar_color'] ?? '#1F2937';
    $headerGradientStart = $settings['header_gradient_start'] ?? '#3B82F6';
    $headerGradientEnd = $settings['header_gradient_end'] ?? '#6366F1';
    $customCSS = $settings['custom_css'] ?? '';

    $css = "
    :root {
        --primary-color: {$primaryColor};
        --secondary-color: {$secondaryColor};
        --accent-color: {$accentColor};
        --danger-color: {$dangerColor};
        --warning-color: {$warningColor};
        --info-color: {$infoColor};
        --sidebar-color: {$sidebarColor};
        --header-gradient-start: {$headerGradientStart};
        --header-gradient-end: {$headerGradientEnd};
    }

    /* Primary Color Overrides */
    .bg-blue-500, .bg-primary { background-color: var(--primary-color) !important; }
    .bg-blue-600, .bg-primary-dark { background-color: color-mix(in srgb, var(--primary-color) 90%, black) !important; }
    .text-blue-500, .text-primary { color: var(--primary-color) !important; }
    .border-blue-500, .border-primary { border-color: var(--primary-color) !important; }
    .hover\\:bg-blue-600:hover { background-color: color-mix(in srgb, var(--primary-color) 90%, black) !important; }
    .focus\\:ring-blue-500:focus { --tw-ring-color: var(--primary-color) !important; }

    /* Secondary Color Overrides */
    .bg-indigo-500, .bg-secondary { background-color: var(--secondary-color) !important; }
    .bg-indigo-600, .bg-secondary-dark { background-color: color-mix(in srgb, var(--secondary-color) 90%, black) !important; }
    .text-indigo-500, .text-secondary { color: var(--secondary-color) !important; }
    .hover\\:bg-indigo-600:hover { background-color: color-mix(in srgb, var(--secondary-color) 90%, black) !important; }

    /* Success/Accent Color */
    .bg-green-500, .bg-accent { background-color: var(--accent-color) !important; }
    .bg-green-600, .bg-accent-dark { background-color: color-mix(in srgb, var(--accent-color) 90%, black) !important; }
    .text-green-500, .text-accent { color: var(--accent-color) !important; }
    .hover\\:bg-green-600:hover { background-color: color-mix(in srgb, var(--accent-color) 90%, black) !important; }

    /* Danger Color */
    .bg-red-500, .bg-danger { background-color: var(--danger-color) !important; }
    .bg-red-600, .bg-danger-dark { background-color: color-mix(in srgb, var(--danger-color) 90%, black) !important; }
    .text-red-500, .text-danger { color: var(--danger-color) !important; }
    .hover\\:bg-red-600:hover { background-color: color-mix(in srgb, var(--danger-color) 90%, black) !important; }

    /* Warning Color */
    .bg-yellow-500, .bg-warning { background-color: var(--warning-color) !important; }
    .text-yellow-500, .text-warning { color: var(--warning-color) !important; }

    /* Info Color */
    .bg-cyan-500, .bg-info { background-color: var(--info-color) !important; }
    .text-cyan-500, .text-info { color: var(--info-color) !important; }

    /* Sidebar Color */
    .sidebar-bg { background-color: var(--sidebar-color) !important; }

    /* Header Gradient */
    .header-gradient {
        background: linear-gradient(135deg, var(--header-gradient-start), var(--header-gradient-end)) !important;
    }

    /* Gradient Backgrounds */
    .bg-gradient-to-r.from-blue-500.to-indigo-600,
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
    }

    /* Button Styles */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    .btn-primary:hover {
        background-color: color-mix(in srgb, var(--primary-color) 90%, black);
        border-color: color-mix(in srgb, var(--primary-color) 90%, black);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        color: white;
    }
    .btn-secondary:hover {
        background-color: color-mix(in srgb, var(--secondary-color) 90%, black);
        border-color: color-mix(in srgb, var(--secondary-color) 90%, black);
    }

    /* Custom CSS */
    {$customCSS}
    ";

    return $css;
}
?>