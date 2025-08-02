<?php
include 'includes/db.php';
include 'includes/functions.php';

echo "<h1>Database Connection Test</h1>";

// Test connection
if ($conn) {
    echo "<p style='color:green'>✅ Database connected successfully</p>";
    
    // Test brand logos
    $brands = getBrandLogos();
    echo "<h2>Brand Logos Test:</h2>";
    echo "<p>Found " . count($brands) . " brand logos</p>";
    if (!empty($brands)) {
        foreach ($brands as $brand) {
            echo "<p>- " . $brand['brand_name'] . " (" . $brand['logo_path'] . ")</p>";
        }
    }
    
    // Test portfolio
    $portfolio = getPortfolioItems();
    echo "<h2>Portfolio Test:</h2>";
    echo "<p>Found " . count($portfolio) . " portfolio items</p>";
    if (!empty($portfolio)) {
        foreach ($portfolio as $item) {
            $title = $item['title'] ?? $item['brand_name'] ?? 'No title';
            $thumbnail = $item['thumbnail'] ?? 'No thumbnail';
            echo "<p>- " . $title . " (" . $thumbnail . ")</p>";
        }
    }
    
    // Test services
    $services = getServices();
    echo "<h2>Services Test:</h2>";
    echo "<p>Found " . count($services) . " services</p>";
    if (!empty($services)) {
        foreach ($services as $service) {
            echo "<p>- " . $service['title'] . "</p>";
        }
    }
    
    // Show all tables
    echo "<h2>Database Tables:</h2>";
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        while ($row = mysqli_fetch_row($result)) {
            $table = $row[0];
            $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
            $count = mysqli_fetch_assoc($count_result)['count'];
            echo "<p>- $table ($count records)</p>";
        }
    }
    
} else {
    echo "<p style='color:red'>❌ Database connection failed</p>";
}
?>
