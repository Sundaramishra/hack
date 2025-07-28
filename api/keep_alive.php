<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

$input = $secureAPI->getSecureInput();
$meetingId = $input['meeting_id'] ?? '';

if (!$meetingId) {
    $secureAPI->sendError('Meeting ID required', 400);
}

// Validate meeting access
$meeting = $secureAPI->validateMeetingAccess($meetingId);

try {
    global $pdo;
    
    $userId = $secureAPI->getUserId();
    $guestName = $input['guest_name'] ?? null;
    
    // Update participant last activity
    if ($userId) {
        $stmt = $pdo->prepare("
            UPDATE meeting_participants 
            SET last_activity = NOW() 
            WHERE meeting_id = ? AND user_id = ? AND left_at IS NULL
        ");
        $stmt->execute([$meetingId, $userId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE meeting_participants 
            SET last_activity = NOW() 
            WHERE meeting_id = ? AND guest_name = ? AND left_at IS NULL
        ");
        $stmt->execute([$meetingId, $guestName]);
    }
    
    // Get meeting status and participant count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_participants 
        FROM meeting_participants 
        WHERE meeting_id = ? AND left_at IS NULL AND is_kicked = 0
    ");
    $stmt->execute([$meetingId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if meeting is still active
    $stmt = $pdo->prepare("SELECT is_active FROM meetings WHERE meeting_id = ?");
    $stmt->execute([$meetingId]);
    $meetingStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$meetingStatus || !$meetingStatus['is_active']) {
        $secureAPI->sendError('Meeting has ended', 410);
    }
    
    $secureAPI->sendSecureResponse([
        'status' => 'alive',
        'meeting_active' => true,
        'participant_count' => $result['active_participants'],
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    $secureAPI->logActivity('keep_alive_failed', [
        'meeting_id' => $meetingId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Keep alive failed', 500);
}
?>