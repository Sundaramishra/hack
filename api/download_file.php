<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

$fileId = $_GET['id'] ?? '';

if (!$fileId || !is_numeric($fileId)) {
    $secureAPI->sendError('Invalid file ID', 400);
}

try {
    global $pdo;
    
    // Get file info and validate access
    $stmt = $pdo->prepare("
        SELECT mf.*, m.meeting_id, m.is_active 
        FROM meeting_files mf 
        JOIN meetings m ON mf.meeting_id = m.meeting_id 
        WHERE mf.id = ?
    ");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        $secureAPI->sendError('File not found', 404);
    }
    
    // Check if meeting is still active
    if (!$file['is_active']) {
        $secureAPI->sendError('File no longer available (meeting ended)', 410);
    }
    
    // Validate user has access to this meeting
    $meeting = $secureAPI->validateMeetingAccess($file['meeting_id']);
    
    // Build file path
    $filePath = '../uploads/meetings/' . $file['meeting_id'] . '/' . $file['file_name'];
    
    if (!file_exists($filePath)) {
        $secureAPI->sendError('File not found on disk', 404);
    }
    
    // Log file download
    $secureAPI->logActivity('file_downloaded', [
        'meeting_id' => $file['meeting_id'],
        'file_id' => $fileId,
        'file_name' => $file['original_name']
    ]);
    
    // Set headers for file download
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . $file['file_size']);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    // Output file
    readfile($filePath);
    exit();
    
} catch (Exception $e) {
    $secureAPI->logActivity('file_download_failed', [
        'file_id' => $fileId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Download failed', 500);
}
?>