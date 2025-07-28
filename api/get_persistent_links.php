<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

$userId = $secureAPI->getUserId();

try {
    global $pdo;
    
    // Get user's persistent links
    $stmt = $pdo->prepare("
        SELECT 
            pl.*,
            (SELECT COUNT(*) FROM meeting_sessions ms WHERE ms.persistent_link_id = pl.link_id AND ms.is_active = 1) as active_sessions,
            (SELECT COUNT(*) FROM meeting_sessions ms WHERE ms.persistent_link_id = pl.link_id) as total_sessions
        FROM persistent_links pl 
        WHERE pl.user_id = ? AND pl.is_active = 1 
        ORDER BY pl.created_at DESC
    ");
    $stmt->execute([$userId]);
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format links for response
    $formattedLinks = array_map(function($link) {
        return [
            'id' => $link['id'],
            'link_id' => $link['link_id'],
            'title' => Security::encryptData($link['title']),
            'description' => Security::encryptData($link['description']),
            'link_url' => $_SERVER['HTTP_HOST'] . '/meeting.php?persistent=' . $link['link_id'],
            'full_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/meeting.php?persistent=' . $link['link_id'],
            'created_at' => $link['created_at'],
            'active_sessions' => $link['active_sessions'],
            'total_sessions' => $link['total_sessions'],
            'is_active' => $link['is_active']
        ];
    }, $links);
    
    $secureAPI->logActivity('persistent_links_requested', [
        'user_id' => $userId,
        'link_count' => count($links)
    ]);
    
    $secureAPI->sendSecureResponse([
        'links' => $formattedLinks,
        'total_count' => count($links),
        'max_allowed' => 2,
        'can_create_more' => count($links) < 2
    ]);
    
} catch (Exception $e) {
    $secureAPI->logActivity('persistent_links_request_failed', [
        'user_id' => $userId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Failed to retrieve persistent links', 500);
}
?>