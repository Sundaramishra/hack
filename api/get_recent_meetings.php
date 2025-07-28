<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'meetings' => []]);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    global $pdo;
    
    // Get recent meetings where user is host or participant
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*, u.name as host_name
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
    
    echo json_encode([
        'success' => true,
        'meetings' => $meetings
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>