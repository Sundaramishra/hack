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
    
    // Mark participant as left
    if ($userId) {
        $stmt = $pdo->prepare("
            UPDATE meeting_participants 
            SET left_at = NOW() 
            WHERE meeting_id = ? AND user_id = ? AND left_at IS NULL
        ");
        $stmt->execute([$meetingId, $userId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE meeting_participants 
            SET left_at = NOW() 
            WHERE meeting_id = ? AND guest_name = ? AND left_at IS NULL
        ");
        $stmt->execute([$meetingId, $guestName]);
    }
    
    // Check if this was the host leaving
    $isHost = ($userId && $userId == $meeting['host_id']);
    
    // Get remaining participant count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_participants 
        FROM meeting_participants 
        WHERE meeting_id = ? AND left_at IS NULL AND is_kicked = 0
    ");
    $stmt->execute([$meetingId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log the leave activity
    $secureAPI->logActivity('participant_left', [
        'meeting_id' => $meetingId,
        'user_id' => $userId,
        'guest_name' => $guestName,
        'was_host' => $isHost,
        'remaining_participants' => $result['active_participants']
    ]);
    
    // If host left and there are still participants, transfer host to first remaining participant
    if ($isHost && $result['active_participants'] > 0) {
        $stmt = $pdo->prepare("
            SELECT id, user_id, guest_name 
            FROM meeting_participants 
            WHERE meeting_id = ? AND left_at IS NULL AND is_kicked = 0 
            ORDER BY joined_at ASC 
            LIMIT 1
        ");
        $stmt->execute([$meetingId]);
        $newHost = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($newHost) {
            // Update new host
            $stmt = $pdo->prepare("UPDATE meeting_participants SET is_host = 1 WHERE id = ?");
            $stmt->execute([$newHost['id']]);
            
            // Update meeting host_id if new host is a registered user
            if ($newHost['user_id']) {
                $stmt = $pdo->prepare("UPDATE meetings SET host_id = ? WHERE meeting_id = ?");
                $stmt->execute([$newHost['user_id'], $meetingId]);
            }
            
            $secureAPI->logActivity('host_transferred', [
                'meeting_id' => $meetingId,
                'old_host_id' => $userId,
                'new_host_id' => $newHost['user_id'],
                'new_host_name' => $newHost['guest_name']
            ]);
        }
    }
    
    // If no participants left, end the meeting
    if ($result['active_participants'] == 0) {
        $stmt = $pdo->prepare("UPDATE meetings SET is_active = 0, ended_at = NOW() WHERE meeting_id = ?");
        $stmt->execute([$meetingId]);
        
        // Clean up meeting files
        cleanupMeetingFiles($meetingId);
        
        $secureAPI->logActivity('meeting_auto_ended', [
            'meeting_id' => $meetingId,
            'reason' => 'no_participants_remaining'
        ]);
    }
    
    $secureAPI->sendSecureResponse([
        'success' => true,
        'message' => 'Left meeting successfully',
        'meeting_ended' => $result['active_participants'] == 0,
        'host_transferred' => $isHost && $result['active_participants'] > 0
    ]);
    
} catch (Exception $e) {
    $secureAPI->logActivity('leave_meeting_failed', [
        'meeting_id' => $meetingId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Failed to leave meeting', 500);
}
?>