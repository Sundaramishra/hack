<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$title = $input['title'] ?? 'Quick Meeting';
$password = $input['password'] ?? null;
$scheduledAt = $input['scheduled_at'] ?? null;

try {
    $meetingId = createMeeting($userId, $title, $password, $scheduledAt);
    
    if ($meetingId) {
        echo json_encode([
            'success' => true,
            'meetingId' => $meetingId,
            'message' => 'Meeting created successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create meeting']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>