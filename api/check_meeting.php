<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$meetingId = $_GET['id'] ?? '';

if (!$meetingId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Meeting ID required']);
    exit();
}

try {
    $meeting = getMeetingById($meetingId);
    
    echo json_encode([
        'success' => true,
        'exists' => $meeting !== false,
        'meeting' => $meeting ? [
            'id' => $meeting['meeting_id'],
            'title' => $meeting['title'],
            'host_name' => $meeting['host_name'],
            'has_password' => !empty($meeting['password'])
        ] : null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>