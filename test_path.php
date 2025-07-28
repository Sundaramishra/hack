<?php
echo "<h1>Path Test</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Test if API files exist
$api_files = ['patients.php', 'doctors.php', 'appointments.php'];
foreach ($api_files as $file) {
    $path = __DIR__ . '/../api/' . $file;
    if (file_exists($path)) {
        echo "<p>✅ API file exists: $path</p>";
    } else {
        echo "<p>❌ API file not found: $path</p>";
    }
}

// Test direct API call
echo "<h2>Testing API Call</h2>";
$api_url = '../api/patients.php?action=list';
echo "<p>Testing: $api_url</p>";

if (file_exists(__DIR__ . '/../api/patients.php')) {
    echo "<p>✅ API file exists</p>";
    // Try to include it
    ob_start();
    include __DIR__ . '/../api/patients.php';
    $output = ob_get_clean();
    echo "<p>API Response:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} else {
    echo "<p>❌ API file not found</p>";
}
?>