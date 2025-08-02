<?php
$host = "localhost";
$username = "root"; 
$password = "";
$database = "vbind_agency";

$connection = new mysqli($host, $username, $password);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($connection->query($sql) === TRUE) {
    $connection->select_db($database);
} else {
    die("Error creating database: " . $connection->error);
}

$connection->set_charset("utf8mb4");

$tables = [
    "CREATE TABLE IF NOT EXISTS `brand_logos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `brand_name` varchar(255) NOT NULL,
        `logo_path` varchar(255) NOT NULL,
        `status` varchar(20) DEFAULT 'active',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `portfolio` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `thumbnail` varchar(255) NOT NULL,
        `status` varchar(20) DEFAULT 'active',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `services` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `status` varchar(20) DEFAULT 'active',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $table) {
    $connection->query($table);
}

$check_brands = $connection->query("SELECT COUNT(*) as count FROM brand_logos")->fetch_assoc();
if ($check_brands['count'] == 0) {
    $brands = [
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Somesa Kitchen', 'uploads/brand-logos/somesa.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Rental Space', 'uploads/brand-logos/rental.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Vaibhav Hair', 'uploads/brand-logos/vaibhav.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Gravityy Motors', 'uploads/brand-logos/gravityy.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Crystal Studio', 'uploads/brand-logos/crystal.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Vartak Academy', 'uploads/brand-logos/vartak.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Daley Caterers', 'uploads/brand-logos/daley.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Dum Biryani', 'uploads/brand-logos/dum.svg')",
        "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('Ishwar Motors', 'uploads/brand-logos/ishwar.svg')"
    ];
    foreach ($brands as $brand) {
        $connection->query($brand);
    }
}

$check_portfolio = $connection->query("SELECT COUNT(*) as count FROM portfolio")->fetch_assoc();
if ($check_portfolio['count'] == 0) {
    $portfolio = [
        "INSERT INTO portfolio (title, thumbnail) VALUES ('Ishwar Motors', 'uploads/portfolio/thumbnails/ishwar.webp')",
        "INSERT INTO portfolio (title, thumbnail) VALUES ('Vaibhav Hair', 'uploads/portfolio/thumbnails/vaibhav.png')",
        "INSERT INTO portfolio (title, thumbnail) VALUES ('DUM BIRYANI', 'uploads/portfolio/thumbnails/dum.png')",
        "INSERT INTO portfolio (title, thumbnail) VALUES ('Somesa Kitchen', 'uploads/portfolio/thumbnails/somesa.png')",
        "INSERT INTO portfolio (title, thumbnail) VALUES ('Vartak Academy', 'uploads/portfolio/thumbnails/vartak.png')"
    ];
    foreach ($portfolio as $item) {
        $connection->query($item);
    }
}

$check_services = $connection->query("SELECT COUNT(*) as count FROM services")->fetch_assoc();
if ($check_services['count'] == 0) {
    $services = [
        "INSERT INTO services (title, description) VALUES ('Branding', 'Brand Name Curation\nBrand Identity\nPackaging\nBrand Story\nCollaterals')",
        "INSERT INTO services (title, description) VALUES ('Social Media Marketing', 'Social Media Management\nSocial Media Strategy\nPlatform Optimization\nContent Creation\nPaid Ads')",
        "INSERT INTO services (title, description) VALUES ('Video & Designing', 'Short/Long form videos\nSocial media posts\nCampaign videos\nMotion graphics\nProduct/Lifestyle videos')",
        "INSERT INTO services (title, description) VALUES ('CGI Ads', 'Campaign videos\nCorporate 3D videos\nCGI animated video')"
    ];
    foreach ($services as $service) {
        $connection->query($service);
    }
}

$GLOBALS['connection'] = $connection;
?>
