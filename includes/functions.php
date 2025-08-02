<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get database type (PostgreSQL or MySQL)
function isDatabasePostgres() {
    global $conn;
    return $conn && get_class($conn) === 'PgSql\\Connection';
}

// Clean and sanitize input data
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    
    if (isDatabasePostgres()) {
        // For PostgreSQL, no equivalent function, manually escape
        $data = str_replace("'", "''", $data);
    } else {
        // For MySQL
        $data = mysqli_real_escape_string($conn, $data);
    }
    
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Redirect to a page
function redirect($location) {
    header("Location: $location");
    exit;
}

// Show alert message
function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Display alert message
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alertType = $_SESSION['alert']['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
        
        echo '<div class="' . $alertType . ' px-4 py-3 rounded relative mb-4 border" role="alert">';
        echo '<span class="block sm:inline">' . $_SESSION['alert']['message'] . '</span>';
        echo '<span class="absolute top-0 bottom-0 right-0 px-4 py-3">';
        echo '<svg class="fill-current h-6 w-6 text-gray-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>';
        echo '</span>';
        echo '</div>';
        
        unset($_SESSION['alert']);
    }
}

// Upload a file and return the file path
function uploadFile($file, $destination = 'uploads/') {
    // Create directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    $fileName = basename($file['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate unique file name
    $uniqueName = uniqid() . '.' . $fileType;
    $targetPath = $destination . $uniqueName;
    
    // Check if file is a valid format
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'svg', 'webp', 'heic']; // Added webp and heic
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

// Delete a file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
        return true;
    }
    return false;
}

// Run query based on database type
function runQuery($query) {
    global $conn;
    
    if (isDatabasePostgres()) {
        return pg_query($conn, $query);
    } else {
        return mysqli_query($conn, $query);
    }
}

// Fetch all rows from result based on database type
function fetchAllRows($result) {
    if (!$result) {
        return [];
    }
    
    $rows = [];
    
    if (isDatabasePostgres()) {
        return pg_fetch_all($result) ?: [];
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

// Fetch single row from result based on database type
function fetchRow($result) {
    if (!$result) {
        return null;
    }
    
    if (isDatabasePostgres()) {
        return pg_fetch_assoc($result);
    } else {
        return mysqli_fetch_assoc($result);
    }
}

// Get number of rows in result
function getNumRows($result) {
    if (!$result) {
        return 0;
    }
    
    if (isDatabasePostgres()) {
        return pg_num_rows($result);
    } else {
        return mysqli_num_rows($result);
    }
}

// Get all services
function getServices() {
    global $conn;
    $services = [];
    
    $query = "SELECT * FROM services ORDER BY id DESC";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $services = fetchAllRows($result);
    }
    
    return $services;
}

// Get a specific service by ID
function getService($id) {
    global $conn;
    
    $id = (int) $id;
    $query = "SELECT * FROM services WHERE id = $id";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        return fetchRow($result);
    }
    
    return null;
}

// Get all team members
function getTeamMembers() {
    global $conn;
    $members = [];
    
    $query = "SELECT * FROM team ORDER BY id DESC";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $members = fetchAllRows($result);
    }
    
    return $members;
}

// Get a specific team member by ID
function getTeamMember($id) {
    global $conn;
    
    $id = (int) $id;
    $query = "SELECT * FROM team WHERE id = $id";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        return fetchRow($result);
    }
    
    return null;
}

// Get all portfolio items
function getPortfolioItems() {
    global $conn;
    $items = [];
    
    $query = "SELECT * FROM portfolio ORDER BY id DESC";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $items = fetchAllRows($result);
    }
    
    return $items;
}

// Get a specific portfolio item by ID
function getPortfolioItem($id) {
    global $conn;
    
    $id = (int) $id;
    $query = "SELECT * FROM portfolio WHERE id = $id";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        return fetchRow($result);
    }
    
    return null;
}

// Get portfolio images
function getPortfolioImages($portfolioId) {
    global $conn;
    $images = [];
    
    $portfolioId = (int) $portfolioId;
    $query = "SELECT * FROM portfolio_images WHERE portfolio_id = $portfolioId ORDER BY position";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $images = fetchAllRows($result);
    }
    
    return $images;
}

// Get all brand logos
function getBrandLogos() {
    global $conn;
    $logos = [];
    
    $query = "SELECT * FROM brand_logos ORDER BY id DESC";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $logos = fetchAllRows($result);
    }
    
    return $logos;
}

// Get all hero reels
function getHeroReels() {
    global $conn;
    $reels = [];
    
    $query = "SELECT * FROM hero_reels ORDER BY id DESC";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $reels = fetchAllRows($result);
    }
    
    return $reels;
}

// Get a specific hero reel by ID
function getHeroReel($id) {
    global $conn;
    
    $id = (int) $id;
    $query = "SELECT * FROM hero_reels WHERE id = $id";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        return fetchRow($result);
    }
    
    return null;
}

// Get featured slider data from portfolio (top 5 items)
function getFeaturedSlider() {
    global $conn;
    $slides = [];
    
    $query = "SELECT id, title, brand_name, description, thumbnail FROM portfolio ORDER BY id LIMIT 5";
    $result = runQuery($query);
    
    if ($result && getNumRows($result) > 0) {
        $slides = fetchAllRows($result);
    }
    
    return $slides;
}
?>
