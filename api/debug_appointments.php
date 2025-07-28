<?php
// Debug version of appointments API
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

echo "=== DEBUG APPOINTMENTS API ===\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "\n";

// Get raw input
$raw_input = file_get_contents('php://input');
echo "Raw Input: " . $raw_input . "\n";

// Try to decode JSON
$data = json_decode($raw_input, true);
echo "Decoded Data: " . print_r($data, true) . "\n";

// Check if we can connect to database
try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "Database connection: SUCCESS\n";
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "\n";
}

// Check if session is working
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";

// Try to include ApiBase
try {
    require_once __DIR__ . '/ApiBase.php';
    echo "ApiBase included: SUCCESS\n";
} catch (Exception $e) {
    echo "ApiBase include: FAILED - " . $e->getMessage() . "\n";
}

// Try to create AppointmentsApi
try {
    require_once __DIR__ . '/appointments.php';
    echo "AppointmentsApi included: SUCCESS\n";
} catch (Exception $e) {
    echo "AppointmentsApi include: FAILED - " . $e->getMessage() . "\n";
}

$debug_output = ob_get_clean();

// Now send as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Debug complete',
    'debug_output' => $debug_output
]);
?>