<?php
require_once 'secure_api.php';
require_once '../includes/functions.php';

// Validate meeting access
$input = $secureAPI->getSecureInput();
$meetingId = $input['meeting_id'] ?? '';

if (!$meetingId) {
    $secureAPI->sendError('Meeting ID required', 400);
}

$meeting = $secureAPI->validateMeetingAccess($meetingId);

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $secureAPI->sendError('No file uploaded or upload error', 400);
}

$file = $_FILES['file'];

// Validate file size (256MB = 268435456 bytes)
$maxSize = 268435456; // 256MB
if ($file['size'] > $maxSize) {
    $secureAPI->sendError('File size exceeds 256MB limit', 400);
}

// Validate file type (security check)
$allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'text/csv',
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
    'video/mp4', 'video/avi', 'video/mov', 'video/wmv',
    'audio/mp3', 'audio/wav', 'audio/ogg'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    $secureAPI->sendError('File type not allowed', 400);
}

// Create meeting-specific upload directory
$uploadDir = '../uploads/meetings/' . $meetingId . '/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate secure filename
$originalName = basename($file['name']);
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$safeFileName = Security::generateSecureRandomString(16) . '.' . $extension;
$filePath = $uploadDir . $safeFileName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    $secureAPI->sendError('Failed to save file', 500);
}

try {
    global $pdo;
    
    // Store file info in database
    $stmt = $pdo->prepare("
        INSERT INTO meeting_files (meeting_id, original_name, file_name, file_size, mime_type, uploaded_by, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $meetingId,
        $originalName,
        $safeFileName,
        $file['size'],
        $mimeType,
        $secureAPI->getUserId()
    ]);
    
    $fileId = $pdo->lastInsertId();
    
    // Log file upload
    $secureAPI->logActivity('file_uploaded', [
        'meeting_id' => $meetingId,
        'file_id' => $fileId,
        'file_name' => $originalName,
        'file_size' => $file['size'],
        'mime_type' => $mimeType
    ]);
    
    // Broadcast file upload to all meeting participants
    broadcastToMeeting($meetingId, [
        'type' => 'file_uploaded',
        'file' => [
            'id' => $fileId,
            'name' => $originalName,
            'size' => $file['size'],
            'type' => $mimeType,
            'uploaded_by' => $secureAPI->getUserId(),
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
    $secureAPI->sendSecureResponse([
        'file_id' => $fileId,
        'original_name' => $originalName,
        'file_size' => $file['size'],
        'mime_type' => $mimeType,
        'download_url' => '/api/download_file.php?id=' . $fileId,
        'message' => 'File uploaded successfully'
    ]);
    
} catch (Exception $e) {
    // Clean up file if database insert fails
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    $secureAPI->logActivity('file_upload_failed', [
        'meeting_id' => $meetingId,
        'file_name' => $originalName,
        'error' => $e->getMessage()
    ]);
    
    $secureAPI->sendError('Database error occurred', 500);
}
?>