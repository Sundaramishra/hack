<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

try {
    $userId = $secureAPI->getUserId();
    global $pdo;
    
    // Get recent meetings where user is host or participant
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*, u.name as host_name,
               (SELECT COUNT(*) FROM meeting_participants mp2 
                WHERE mp2.meeting_id = m.meeting_id 
                AND mp2.left_at IS NULL AND mp2.is_kicked = 0) as participant_count
        FROM meetings m 
        JOIN users u ON m.host_id = u.id
        LEFT JOIN meeting_participants mp ON m.meeting_id = mp.meeting_id
        WHERE (m.host_id = ? OR mp.user_id = ?) 
        AND m.is_active = 1
        ORDER BY m.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$userId, $userId]);
    $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Encrypt sensitive meeting data
    $encryptedMeetings = array_map(function($meeting) {
        return [
            'meeting_id' => $meeting['meeting_id'], // Keep plain for frontend
            'title' => Security::encryptData($meeting['title']),
            'host_name' => Security::encryptData($meeting['host_name']),
            'created_at' => $meeting['created_at'],
            'scheduled_at' => $meeting['scheduled_at'],
            'participant_count' => $meeting['participant_count'],
            'is_host' => $meeting['host_id'] == $GLOBALS['secureAPI']->getUserId(),
            'has_password' => !empty($meeting['password'])
        ];
    }, $meetings);
    
    $secureAPI->logActivity('recent_meetings_requested', [
        'meeting_count' => count($meetings)
    ]);
    
    $secureAPI->sendSecureResponse(['meetings' => $encryptedMeetings]);
} catch (Exception $e) {
    $secureAPI->logActivity('recent_meetings_error', [
        'error' => $e->getMessage()
    ]);
    $secureAPI->sendError('Server error occurred', 500);
}
?>