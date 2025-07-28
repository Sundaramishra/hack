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
    
    // Get all files for this meeting
    $stmt = $pdo->prepare("
        SELECT mf.*, u.name as uploader_name 
        FROM meeting_files mf 
        LEFT JOIN users u ON mf.uploaded_by = u.id 
        WHERE mf.meeting_id = ? 
        ORDER BY mf.uploaded_at DESC
    ");
    $stmt->execute([$meetingId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format file data for response
    $fileList = array_map(function($file) {
        return [
            'id' => $file['id'],
            'name' => Security::encryptData($file['original_name']),
            'size' => $file['file_size'],
            'type' => $file['mime_type'],
            'uploaded_by' => $file['uploaded_by'],
            'uploader_name' => Security::encryptData($file['uploader_name'] ?? 'Guest'),
            'uploaded_at' => $file['uploaded_at'],
            'download_url' => '/api/download_file.php?id=' . $file['id'],
            'size_formatted' => formatFileSize($file['file_size'])
        ];
    }, $files);
    
    $secureAPI->logActivity('meeting_files_requested', [
        'meeting_id' => $meetingId,
        'file_count' => count($files)
    ]);
    
    $secureAPI->sendSecureResponse([
        'files' => $fileList,
        'total_count' => count($files)
    ]);
    
} catch (Exception $e) {
    $secureAPI->logActivity('meeting_files_request_failed', [
        'meeting_id' => $meetingId,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Failed to retrieve files', 500);
}

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>