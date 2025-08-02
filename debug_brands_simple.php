<!DOCTYPE html>
<html>
<head>
    <title>Brands Debug</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .brand-test { border: 2px solid #F44B12; padding: 20px; margin: 20px 0; }
        .brand-logo { max-width: 120px; max-height: 60px; margin: 10px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>🔍 BRANDS SECTION DEBUG</h1>
    
    <?php
    include 'includes/db.php';
    include 'includes/functions.php';
    
    echo "<div class='brand-test'>";
    echo "<h2>Database Connection:</h2>";
    if ($connection) {
        echo "✅ Connected to database: $database<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    echo "<h2>Brand Logos Function Test:</h2>";
    $brands = getBrandLogos();
    echo "Found " . count($brands) . " brands<br>";
    
    if (!empty($brands)) {
        echo "<h3>Brand Logos:</h3>";
        foreach ($brands as $brand) {
            echo "<div style='display:inline-block; margin:10px; text-align:center; border:1px solid #ccc; padding:10px;'>";
            echo "<img src='" . $brand['logo_path'] . "' alt='" . $brand['brand_name'] . "' class='brand-logo'><br>";
            echo "<strong>" . $brand['brand_name'] . "</strong><br>";
            echo "<small>" . $brand['logo_path'] . "</small>";
            echo "</div>";
        }
    } else {
        echo "❌ No brands found!";
    }
    echo "</div>";
    
    echo "<div class='brand-test'>";
    echo "<h2>File Check:</h2>";
    $files = glob('uploads/brand-logos/*.svg');
    echo "SVG files found: " . count($files) . "<br>";
    foreach ($files as $file) {
        echo "- $file<br>";
    }
    echo "</div>";
    ?>
    
    <div class="brand-test">
        <h2>Manual Brands Section Test:</h2>
        <div style="background:#fff; border-radius:32px; min-height:200px; padding:40px 20px; margin:20px auto; box-shadow:0 20px 54px 0 rgba(44,44,44,0.08); text-align:center;">
            <h3>This should be visible!</h3>
            <img src="uploads/brand-logos/somesa.svg" alt="Somesa" style="max-width:120px; max-height:60px; margin:10px;">
            <img src="uploads/brand-logos/rental.svg" alt="Rental" style="max-width:120px; max-height:60px; margin:10px;">
            <img src="uploads/brand-logos/vaibhav.svg" alt="Vaibhav" style="max-width:120px; max-height:60px; margin:10px;">
        </div>
    </div>
</body>
</html>
