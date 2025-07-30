<?php
// Directory to save uploaded chunks
$targetDir = "../uploads/portfolio/videos/";

// Get parameters
$chunk = isset($_REQUEST['resumableChunkNumber']) ? intval($_REQUEST['resumableChunkNumber']) : 1;
$filename = isset($_REQUEST['resumableFilename']) ? $_REQUEST['resumableFilename'] : '';
$identifier = isset($_REQUEST['resumableIdentifier']) ? preg_replace('/[^0-9A-Za-z_-]/', '', $_REQUEST['resumableIdentifier']) : '';
$finalPath = $targetDir . $identifier . '-' . $filename;

// Save chunk
if (!empty($_FILES)) {
    $chunkDir = $targetDir . $identifier . "/";
    if (!is_dir($chunkDir)) mkdir($chunkDir, 0777, true);

    $chunkFile = $chunkDir . $chunk;
    move_uploaded_file($_FILES['file']['tmp_name'], $chunkFile);
}

// Check if all chunks uploaded
$totalChunks = isset($_REQUEST['resumableTotalChunks']) ? intval($_REQUEST['resumableTotalChunks']) : 1;
$allUploaded = true;
for ($i = 1; $i <= $totalChunks; $i++) {
    if (!file_exists($targetDir . $identifier . "/" . $i)) {
        $allUploaded = false;
        break;
    }
}

// Assemble if done
if ($allUploaded && $filename) {
    $out = fopen($finalPath, "wb");
    for ($i = 1; $i <= $totalChunks; $i++) {
        $in = fopen($targetDir . $identifier . "/" . $i, "rb");
        stream_copy_to_stream($in, $out);
        fclose($in);
    }
    fclose($out);

    // Clean up chunks
    for ($i = 1; $i <= $totalChunks; $i++) {
        unlink($targetDir . $identifier . "/" . $i);
    }
    rmdir($targetDir . $identifier);

    // Respond with video path to save in DB
    echo json_encode(['success'=>true, 'path'=>substr($finalPath, 3)]);
} else {
    echo json_encode(['success'=>true]); // chunk received
}
?>