<?php
// Include the DB connection file
require_once 'db_connect.php';

// Sanitize and get POST data
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$industry = trim($_POST['industry'] ?? '');
$message  = trim($_POST['message'] ?? '');

if ($name && $email && $industry && $message) {
    // Prepare and insert into DB
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, industry, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $industry, $message);

    if ($stmt->execute()) {
        // Redirect with success
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?success=1");
        exit;
    } else {
        // Error
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1");
        exit;
    }
} else {
    // Missing field
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1");
    exit;
}
?>
