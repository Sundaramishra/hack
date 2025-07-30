<?php
$debug = true; // Set to false in production

require_once __DIR__ . '/includes/db.php';
function clean($str) { return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8'); }
$name = clean($_POST['name'] ?? '');
$email = clean($_POST['email'] ?? '');
$industry = clean($_POST['industry'] ?? '');
$message = clean($_POST['message'] ?? '');
$ok = false;
$errMsg = '';

if ($name && $email && $industry && $message && isset($conn)) {
    if ($debug) error_log("Input validated. Attempting DB insert...");
    if (function_exists('pg_connect') && get_resource_type($conn) === 'pgsql link') {
        // PostgreSQL
        $res = pg_query_params(
            $conn, "INSERT INTO contact_form (name, email, industry, message) VALUES ($1, $2, $3, $4)", [$name, $email, $industry, $message]
        );
        if ($res) {
            $ok = true;
        } else {
            $errMsg = pg_last_error($conn);
            if ($debug) error_log("PostgreSQL error: $errMsg");
        }
    } else {
        // MySQL (mysqli)
        $stmt = mysqli_prepare($conn, "INSERT INTO contact_form (name, email, industry, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $industry, $message);
            $res = mysqli_stmt_execute($stmt);
            if ($res) {
                $ok = true;
            } else {
                $errMsg = mysqli_error($conn);
                if ($debug) error_log("MySQL error: $errMsg");
            }
            mysqli_stmt_close($stmt);
        } else {
            $errMsg = mysqli_error($conn);
            if ($debug) error_log("MySQL prepare error: $errMsg");
        }
    }
} else {
    $errMsg = "Validation failed or DB connection missing";
    if ($debug) error_log("Validation failed or DB connection missing");
}

header('Content-Type: application/json');
echo json_encode(['success' => $ok, 'error' => $debug ? $errMsg : null]);
exit;