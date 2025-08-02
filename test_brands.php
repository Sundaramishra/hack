<?php
include 'includes/db.php';
include 'includes/functions.php';

echo "<h1>Brand Logos Test</h1>";

$brands = getBrandLogos();
echo "<h2>Found " . count($brands) . " brands:</h2>";

if (!empty($brands)) {
    echo "<div style='display:flex; flex-wrap:wrap; gap:20px; margin:20px 0;'>";
    foreach ($brands as $brand) {
        echo "<div style='border:1px solid #ccc; padding:10px; text-align:center;'>";
        echo "<img src='" . $brand['logo_path'] . "' alt='" . $brand['brand_name'] . "' style='max-width:120px; max-height:60px; display:block; margin:0 auto;'>";
        echo "<p><strong>" . $brand['brand_name'] . "</strong></p>";
        echo "<p><small>" . $brand['logo_path'] . "</small></p>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p style='color:red;'>No brands found in database!</p>";
}

echo "<h2>Database Connection Test:</h2>";
if ($connection) {
    echo "<p style='color:green;'>✅ Database connected successfully</p>";
    
    $result = $connection->query("SHOW TABLES");
    echo "<p>Tables in database:</p><ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
}
?>
