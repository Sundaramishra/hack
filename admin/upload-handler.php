<?php
// upload-handler.php: Handles Uppy video uploads for portfolio admin
header('Content-Type: application/json');

$targetDir = '../uploads/portfolio/videos/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (!empty($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = uniqid('video_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $targetFile = $targetDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Return relative path for DB storage (remove ../)
        $relativePath = ltrim(str_replace('..', '', $targetFile), '/');
        http_response_code(200);
        echo json_encode(['status' => 'success', 'path' => $relativePath]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
}
?>
