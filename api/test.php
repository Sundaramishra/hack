<?php
// Simple test API to verify JSON responses
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Clean any previous output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    // Test response
    $response = [
        'success' => true,
        'message' => 'Test API working correctly',
        'data' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Test API Error: ' . $e->getMessage()
    ]);
}
?>