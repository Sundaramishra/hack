<?php
session_start();
require_once '../config/security.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

try {
    $csrfToken = Security::generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'csrf_token' => $csrfToken,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate CSRF token'
    ]);
}
?>