<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

$input = $secureAPI->getSecureInput();
$linkId = $input['link_id'] ?? '';

if (!$linkId) {
    $secureAPI->sendError('Link ID required', 400);
}

$userId = $secureAPI->getUserId();
$secureAPI->validateCSRF();

try {
    global $pdo;
    
    // Verify user owns this link
    $stmt = $pdo->prepare("
        SELECT * FROM persistent_links 
        WHERE link_id = ? AND user_id = ? AND is_active = 1
    ");
    $stmt->execute([$linkId, $userId]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$link) {
        $secureAPI->sendError('Link not found or you do not have permission', 404);
    }
    
    // Check if there are active sessions using this link
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_sessions 
        FROM meeting_sessions 
        WHERE persistent_link_id = ? AND is_active = 1
    ");
    $stmt->execute([$linkId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // End all active sessions for this persistent link
    if ($result['active_sessions'] > 0) {
        $stmt = $pdo->prepare("
            UPDATE meeting_sessions 
            SET is_active = 0, ended_at = NOW() 
            WHERE persistent_link_id = ? AND is_active = 1
        ");
        $stmt->execute([$linkId]);
        
        // Also end the actual meetings
        $stmt = $pdo->prepare("
            UPDATE meetings m 
            JOIN meeting_sessions ms ON m.meeting_id = ms.meeting_id 
            SET m.is_active = 0 
            WHERE ms.persistent_link_id = ? AND m.is_active = 1
        ");
        $stmt->execute([$linkId]);
        
        // Clean up files from ended meetings
        $stmt = $pdo->prepare("
            SELECT DISTINCT ms.meeting_id 
            FROM meeting_sessions ms 
            WHERE ms.persistent_link_id = ?
        ");
        $stmt->execute([$linkId]);
        $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($meetings as $meeting) {
            cleanupMeetingFiles($meeting['meeting_id']);
        }
    }
    
    // Deactivate the persistent link
    $stmt = $pdo->prepare("
        UPDATE persistent_links 
        SET is_active = 0, cancelled_at = NOW() 
        WHERE link_id = ? AND user_id = ?
    ");
    
    if ($stmt->execute([$linkId, $userId])) {
        // Log the cancellation
        $secureAPI->logActivity('persistent_link_cancelled', [
            'link_id' => $linkId,
            'title' => $link['title'],
            'user_id' => $userId,
            'active_sessions_ended' => $result['active_sessions']
        ]);
        
        $secureAPI->sendSecureResponse([
            'success' => true,
            'message' => 'Persistent link cancelled successfully',
            'link_id' => $linkId,
            'active_sessions_ended' => $result['active_sessions']
        ]);
    } else {
        $secureAPI->sendError('Failed to cancel persistent link', 500);
    }
    
} catch (Exception $e) {
    $secureAPI->logActivity('persistent_link_cancellation_failed', [
        'link_id' => $linkId,
        'user_id' => $userId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Database error occurred', 500);
}
?>