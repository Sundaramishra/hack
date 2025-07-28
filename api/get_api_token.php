<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Validate CSRF token
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!Security::validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    // Generate new API token
    $apiToken = Security::generateAPIToken($userId, $sessionId);
    
    // Store token hash in database for tracking
    global $pdo;
    $tokenHash = hash('sha256', $apiToken);
    $expiresAt = time() + 3600; // 1 hour
    
    $stmt = $pdo->prepare("INSERT INTO api_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $tokenHash, $expiresAt]);
    
    // Log token generation
    Security::logSecurityEvent('api_token_generated', [
        'user_id' => $userId,
        'expires_at' => $expiresAt
    ]);
    
    echo json_encode([
        'success' => true,
        'api_token' => $apiToken,
        'expires_at' => $expiresAt,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    Security::logSecurityEvent('api_token_generation_failed', [
        'user_id' => $_SESSION['user_id'] ?? null,
        'error' => $e->getMessage()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate API token'
    ]);
}
?>