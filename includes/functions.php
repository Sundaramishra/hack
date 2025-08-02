<?php
function getBrandLogos() {
    global $connection;
    if (!$connection) return [];
    $sql = "SELECT id, brand_name, logo_path FROM brand_logos WHERE status = 'active' ORDER BY id ASC";
    $result = $connection->query($sql);
    $brands = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
    }
    return $brands;
}

function getPortfolioItems($limit = 5) {
    global $connection;
    if (!$connection) return [];
    $sql = "SELECT id, title, thumbnail FROM portfolio WHERE status = 'active' ORDER BY id DESC LIMIT " . intval($limit);
    $result = $connection->query($sql);
    $portfolio = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $portfolio[] = $row;
        }
    }
    return $portfolio;
}

function getServices($limit = 4) {
    global $connection;
    if (!$connection) return [];
    $sql = "SELECT id, title, description FROM services WHERE status = 'active' ORDER BY id ASC LIMIT " . intval($limit);
    $result = $connection->query($sql);
    $services = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    return $services;
}

function clean($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function createFallbackImage($text, $width = 300, $height = 400) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">
        <rect width="'.$width.'" height="'.$height.'" fill="#252525"/>
        <text x="'.($width/2).'" y="'.($height/2).'" text-anchor="middle" fill="#F44B12" font-family="Arial" font-size="16">'..clean($text).'</text>
    </svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>
