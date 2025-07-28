<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

// Get secure input data
$input = $secureAPI->getSecureInput();

// Validate CSRF token
$secureAPI->validateCSRF();

$title = $input['title'] ?? 'Quick Meeting';
$password = $input['password'] ?? null;
$scheduledAt = $input['scheduled_at'] ?? null;

try {
    $userId = $secureAPI->getUserId();
    $meetingId = createMeeting($userId, $title, $password, $scheduledAt);
    
    if ($meetingId) {
        $secureAPI->logActivity('meeting_created', [
            'meeting_id' => $meetingId,
            'title' => $title,
            'has_password' => !empty($password),
            'is_scheduled' => !empty($scheduledAt)
        ]);
        
        $responseData = [
            'meetingId' => $meetingId,
            'message' => 'Meeting created successfully',
            'joinUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/meeting.php?id=' . $meetingId
        ];
        
        $secureAPI->sendSecureResponse($responseData);
    } else {
        $secureAPI->sendError('Failed to create meeting', 500);
    }
} catch (Exception $e) {
    $secureAPI->logActivity('meeting_creation_failed', [
        'error' => $e->getMessage(),
        'title' => $title
    ]);
    $secureAPI->sendError('Server error occurred', 500);
}
?>