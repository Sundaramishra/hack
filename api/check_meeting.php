<?php
// This is a public endpoint, so we define the constant to prevent auto-init
define('SECURE_API_MANUAL_INIT', true);
require_once 'secure_api.php';
require_once '../includes/functions.php';

// Manual init for public endpoint
$secureAPI = new SecureAPI();

$meetingId = Security::sanitizeInput($_GET['id'] ?? '');

if (!$meetingId) {
    $secureAPI->sendError('Meeting ID required', 400);
}

// Validate meeting ID format
if (!preg_match('/^[A-Z0-9]{10}$/', $meetingId)) {
    $secureAPI->sendError('Invalid meeting ID format', 400);
}

try {
    $meeting = getMeetingById($meetingId);
    
    $responseData = [
        'exists' => $meeting !== false,
        'meeting' => $meeting ? [
            'id' => Security::encryptData($meeting['meeting_id']),
            'title' => Security::encryptData($meeting['title']),
            'host_name' => Security::encryptData($meeting['host_name']),
            'has_password' => !empty($meeting['password']),
            'participant_count' => count(getMeetingParticipants($meetingId))
        ] : null
    ];
    
    $secureAPI->logActivity('meeting_info_requested', [
        'meeting_id' => $meetingId,
        'exists' => $meeting !== false
    ]);
    
    $secureAPI->sendSecureResponse($responseData, false); // Don't encrypt public data
} catch (Exception $e) {
    $secureAPI->logActivity('meeting_check_error', [
        'meeting_id' => $meetingId,
        'error' => $e->getMessage()
    ]);
    $secureAPI->sendError('Server error occurred', 500);
}
?>