<?php
require_once 'config/database.php';

// Password validation function
function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Generate secure password hash
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate unique meeting ID
function generateMeetingId() {
    return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
}

// Get user by ID
function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user by email
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create new user
function createUser($name, $email, $password) {
    global $pdo;
    if (!validatePassword($password)) {
        return false;
    }
    
    $hashedPassword = hashPassword($password);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword]);
}

// Create meeting
function createMeeting($hostId, $title = 'Quick Meeting', $password = null, $scheduledAt = null) {
    global $pdo;
    $meetingId = generateMeetingId();
    
    $stmt = $pdo->prepare("INSERT INTO meetings (meeting_id, host_id, title, password, scheduled_at) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$meetingId, $hostId, $title, $password ? hashPassword($password) : null, $scheduledAt])) {
        return $meetingId;
    }
    return false;
}

// Get meeting by ID
function getMeetingById($meetingId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT m.*, u.name as host_name FROM meetings m JOIN users u ON m.host_id = u.id WHERE m.meeting_id = ? AND m.is_active = 1");
    $stmt->execute([$meetingId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Join meeting
function joinMeeting($meetingId, $userId = null, $guestName = null) {
    global $pdo;
    
    // Check if meeting exists and is active
    $meeting = getMeetingById($meetingId);
    if (!$meeting) {
        return false;
    }
    
    // Check if user is already in meeting
    $stmt = $pdo->prepare("SELECT * FROM meeting_participants WHERE meeting_id = ? AND (user_id = ? OR guest_name = ?) AND left_at IS NULL AND is_kicked = 0");
    $stmt->execute([$meetingId, $userId, $guestName]);
    if ($stmt->fetch()) {
        return true; // Already in meeting
    }
    
    // Add participant
    $isHost = ($userId && $userId == $meeting['host_id']);
    $stmt = $pdo->prepare("INSERT INTO meeting_participants (meeting_id, user_id, guest_name, is_host) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$meetingId, $userId, $guestName, $isHost]);
}

// Get meeting participants
function getMeetingParticipants($meetingId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT mp.*, u.name as user_name 
        FROM meeting_participants mp 
        LEFT JOIN users u ON mp.user_id = u.id 
        WHERE mp.meeting_id = ? AND mp.left_at IS NULL AND mp.is_kicked = 0
        ORDER BY mp.is_host DESC, mp.joined_at ASC
    ");
    $stmt->execute([$meetingId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Send message
function sendMessage($meetingId, $senderId, $senderName, $message, $messageType = 'text', $isBroadcast = false) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO messages (meeting_id, sender_id, sender_name, message, message_type, is_broadcast) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$meetingId, $senderId, $senderName, $message, $messageType, $isBroadcast]);
}

// Get messages
function getMessages($meetingId, $limit = 50) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE meeting_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$meetingId, $limit]);
    return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Pin message
function pinMessage($messageId, $meetingId, $hostId) {
    global $pdo;
    
    // Verify host permission
    $meeting = getMeetingById($meetingId);
    if (!$meeting || $meeting['host_id'] != $hostId) {
        return false;
    }
    
    // Unpin all other messages first
    $stmt = $pdo->prepare("UPDATE messages SET is_pinned = 0 WHERE meeting_id = ?");
    $stmt->execute([$meetingId]);
    
    // Pin the selected message
    $stmt = $pdo->prepare("UPDATE messages SET is_pinned = 1 WHERE id = ? AND meeting_id = ?");
    return $stmt->execute([$messageId, $meetingId]);
}

// Get pinned messages
function getPinnedMessages($meetingId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE meeting_id = ? AND is_pinned = 1 ORDER BY created_at DESC");
    $stmt->execute([$meetingId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Update participant permissions
function updateParticipantPermissions($meetingId, $participantId, $permissions, $hostId) {
    global $pdo;
    
    // Verify host permission
    $meeting = getMeetingById($meetingId);
    if (!$meeting || $meeting['host_id'] != $hostId) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO meeting_permissions (meeting_id, user_id, guest_name, can_chat, can_share_screen, can_unmute, can_enable_video) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        can_chat = VALUES(can_chat),
        can_share_screen = VALUES(can_share_screen),
        can_unmute = VALUES(can_unmute),
        can_enable_video = VALUES(can_enable_video)
    ");
    
    return $stmt->execute([
        $meetingId,
        $permissions['user_id'],
        $permissions['guest_name'],
        $permissions['can_chat'],
        $permissions['can_share_screen'],
        $permissions['can_unmute'],
        $permissions['can_enable_video']
    ]);
}

// Kick participant
function kickParticipant($meetingId, $participantId, $hostId) {
    global $pdo;
    
    // Verify host permission
    $meeting = getMeetingById($meetingId);
    if (!$meeting || $meeting['host_id'] != $hostId) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE meeting_participants SET is_kicked = 1, left_at = NOW() WHERE id = ? AND meeting_id = ?");
    return $stmt->execute([$participantId, $meetingId]);
}

// Mute participant
function muteParticipant($meetingId, $participantId, $hostId, $mute = true) {
    global $pdo;
    
    // Verify host permission
    $meeting = getMeetingById($meetingId);
    if (!$meeting || $meeting['host_id'] != $hostId) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE meeting_participants SET is_muted = ? WHERE id = ? AND meeting_id = ?");
    return $stmt->execute([$mute, $participantId, $meetingId]);
}

// Toggle hand raise
function toggleHandRaise($meetingId, $participantId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE meeting_participants SET hand_raised = NOT hand_raised WHERE id = ? AND meeting_id = ?");
    return $stmt->execute([$participantId, $meetingId]);
}

// End meeting
function endMeeting($meetingId, $hostId) {
    global $pdo;
    
    // Verify host permission
    $meeting = getMeetingById($meetingId);
    if (!$meeting || $meeting['host_id'] != $hostId) {
        return false;
    }
    
    // Mark meeting as inactive
    $stmt = $pdo->prepare("UPDATE meetings SET is_active = 0 WHERE meeting_id = ?");
    $stmt->execute([$meetingId]);
    
    // Mark all participants as left
    $stmt = $pdo->prepare("UPDATE meeting_participants SET left_at = NOW() WHERE meeting_id = ? AND left_at IS NULL");
    return $stmt->execute([$meetingId]);
}

// Clean old data (call this periodically)
function cleanOldData() {
    global $pdo;
    
    // Remove messages older than 30 days
    $stmt = $pdo->prepare("DELETE FROM messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    
    // Mark inactive meetings older than 24 hours
    $stmt = $pdo->prepare("UPDATE meetings SET is_active = 0 WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND is_active = 1");
    $stmt->execute();
}
?>