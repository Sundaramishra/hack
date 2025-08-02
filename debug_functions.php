<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'includes/db.php';
include_once 'includes/functions.php';

echo "<h2>Debug Functions Test</h2>";

echo "<h3>1. Testing getBrandLogos():</h3>";
$brandLogos = getBrandLogos();
echo "Count: " . count($brandLogos) . "<br>";
if (!empty($brandLogos)) {
    echo "First logo data:<br>";
    echo "<pre>";
    print_r($brandLogos[0]);
    echo "</pre>";
    
    echo "Checking logo_path access:<br>";
    foreach (array_slice($brandLogos, 0, 3) as $i => $logo) {
        echo "Logo $i: logo_path = " . (isset($logo['logo_path']) ? $logo['logo_path'] : 'NOT SET') . "<br>";
    }
} else {
    echo "No brand logos found!<br>";
}

echo "<h3>2. Testing getFeaturedSlider():</h3>";
$slider = getFeaturedSlider();
echo "Count: " . count($slider) . "<br>";
if (!empty($slider)) {
    echo "First slide data:<br>";
    echo "<pre>";
    print_r($slider[0]);
    echo "</pre>";
} else {
    echo "No slider data found!<br>";
}

echo "<h3>3. Testing getServices():</h3>";
$services = getServices();
echo "Count: " . count($services) . "<br>";
if (!empty($services)) {
    echo "First service data:<br>";
    echo "<pre>";
    print_r($services[0]);
    echo "</pre>";
} else {
    echo "No services found!<br>";
}

echo "<h3>4. Database Connection Test:</h3>";
if (isset($conn)) {
    echo "Database connection: EXISTS<br>";
    if (function_exists('mysqli_get_server_info')) {
        echo "MySQL version: " . mysqli_get_server_info($conn) . "<br>";
    }
} else {
    echo "Database connection: NOT SET<br>";
}
?>