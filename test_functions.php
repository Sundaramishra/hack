<!DOCTYPE html>
<html>
<head>
    <title>Functions Test</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .brand-logo { max-width: 120px; max-height: 60px; margin: 5px; border: 1px solid #ddd; }
        .portfolio-thumb { max-width: 150px; max-height: 200px; margin: 5px; border: 1px solid #ddd; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 FUNCTIONS TEST PAGE</h1>
    
    <?php
    include 'includes/db.php';
    include 'includes/functions.php';
    ?>
    
    <div class="test-section">
        <h2>🏢 BRAND LOGOS TEST</h2>
        <?php
        $brands = getBrandLogos();
        if (!empty($brands)) {
            echo '<p class="success">✅ Found ' . count($brands) . ' brands!</p>';
            echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
            foreach ($brands as $brand) {
                echo '<div style="text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">';
                echo '<img src="' . $brand['logo_path'] . '" alt="' . $brand['brand_name'] . '" class="brand-logo"><br>';
                echo '<strong>' . $brand['brand_name'] . '</strong><br>';
                echo '<small>' . $brand['logo_path'] . '</small>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="error">❌ No brands found!</p>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>💼 PORTFOLIO TEST</h2>
        <?php
        $portfolio = getPortfolioItems(5);
        if (!empty($portfolio)) {
            echo '<p class="success">✅ Found ' . count($portfolio) . ' portfolio items!</p>';
            echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
            foreach ($portfolio as $item) {
                echo '<div style="text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">';
                echo '<img src="' . $item['thumbnail'] . '" alt="' . $item['title'] . '" class="portfolio-thumb"><br>';
                echo '<strong>' . $item['title'] . '</strong><br>';
                echo '<small>' . $item['thumbnail'] . '</small>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="error">❌ No portfolio found!</p>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>⚙️ SERVICES TEST</h2>
        <?php
        $services = getServices(4);
        if (!empty($services)) {
            echo '<p class="success">✅ Found ' . count($services) . ' services!</p>';
            echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">';
            foreach ($services as $service) {
                echo '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9;">';
                echo '<h3 style="color: #F44B12; margin-top: 0;">' . $service['title'] . '</h3>';
                $points = explode("\n", $service['description']);
                echo '<ul>';
                foreach ($points as $point) {
                    if (trim($point) !== '') {
                        echo '<li>' . trim($point) . '</li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="error">❌ No services found!</p>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>📊 DATABASE STATUS</h2>
        <?php
        if ($connection) {
            echo '<p class="success">✅ Database connection: SUCCESS</p>';
            echo '<p><strong>Database:</strong> ' . $database . '</p>';
            
            $tables = ['brand_logos', 'portfolio', 'services'];
            foreach ($tables as $table) {
                $result = $connection->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    $count = $connection->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
                    echo '<p class="success">✅ Table <strong>' . $table . '</strong>: EXISTS (' . $count . ' records)</p>';
                } else {
                    echo '<p class="error">❌ Table <strong>' . $table . '</strong>: NOT FOUND</p>';
                }
            }
        } else {
            echo '<p class="error">❌ Database connection: FAILED</p>';
        }
        ?>
    </div>
    
    <p><a href="index.php" style="background: #F44B12; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">← Back to Homepage</a></p>
</body>
</html>
