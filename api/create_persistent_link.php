<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

$input = $secureAPI->getSecureInput();
$title = $input['title'] ?? 'Persistent Meeting';
$description = $input['description'] ?? '';

$userId = $secureAPI->getUserId();
$secureAPI->validateCSRF();

try {
    global $pdo;
    
    // Check how many active persistent links user already has
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as link_count 
        FROM persistent_links 
        WHERE user_id = ? AND is_active = 1
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['link_count'] >= 2) {
        $secureAPI->sendError('Maximum 2 persistent links allowed per user', 400, [
            'current_count' => $result['link_count'],
            'max_allowed' => 2
        ]);
    }
    
    // Generate unique persistent link ID
    $linkId = generatePersistentLinkId();
    
    // Create persistent link
    $stmt = $pdo->prepare("
        INSERT INTO persistent_links (link_id, user_id, title, description, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$linkId, $userId, $title, $description])) {
        // Log the creation
        $secureAPI->logActivity('persistent_link_created', [
            'link_id' => $linkId,
            'title' => $title,
            'user_id' => $userId
        ]);
        
        $secureAPI->sendSecureResponse([
            'success' => true,
            'link_id' => $linkId,
            'title' => $title,
            'description' => $description,
            'link_url' => $_SERVER['HTTP_HOST'] . '/meeting.php?persistent=' . $linkId,
            'full_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/meeting.php?persistent=' . $linkId,
            'created_at' => date('Y-m-d H:i:s'),
            'message' => 'Persistent link created successfully'
        ]);
    } else {
        $secureAPI->sendError('Failed to create persistent link', 500);
    }
    
} catch (Exception $e) {
    $secureAPI->logActivity('persistent_link_creation_failed', [
        'user_id' => $userId,
        'title' => $title,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Database error occurred', 500);
}

// Generate unique persistent link ID
function generatePersistentLinkId() {
    return 'pl_' . bin2hex(random_bytes(8)) . '_' . time();
}
?>