<?php
// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Skip redirect on the login page
    $currentFile = basename($_SERVER['PHP_SELF']);
    if ($currentFile !== 'index.php') {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vbind Admin Panel</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        vbindWhite: '#FFFFFF',
                        vbindOrange: '#F44B12',
                        vbindGrey: '#2B2B2A',
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .admin-sidebar {
            width: 250px;
            transition: all 0.3s;
        }
        
        .admin-sidebar.collapsed {
            width: 70px;
        }
        
        .admin-content {
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        
        .admin-content.expanded {
            width: calc(100% - 70px);
        }
        
        .nav-item-text {
            opacity: 1;
            transition: opacity 0.2s;
        }
        
        .admin-sidebar.collapsed .nav-item-text {
            opacity: 0;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                left: -250px;
                height: 100vh;
                z-index: 50;
            }
            
            .admin-sidebar.active {
                left: 0;
            }
            
            .admin-content {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="admin-sidebar" class="admin-sidebar bg-[#2B2B2A] text-white">
            <div class="p-4 flex justify-between items-center">
                <a href="dashboard.php" class="font-bold text-xl">
                    <span class="text-[#F44B12]">V</span><span class="nav-item-text">bind Admin</span>
                </a>
                <button id="toggle-sidebar" class="text-white p-1 rounded hover:bg-gray-700 lg:flex hidden">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-6">
                <ul>
                    <li class="nav-item">
                        <a href="dashboard.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-tachometer-alt w-6"></i>
                            <span class="nav-item-text ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="services.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-briefcase w-6"></i>
                            <span class="nav-item-text ml-3">Services</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="team.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-users w-6"></i>
                            <span class="nav-item-text ml-3">Team</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="portfolio.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-images w-6"></i>
                            <span class="nav-item-text ml-3">Portfolio</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="brand-logos.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-building w-6"></i>
                            <span class="nav-item-text ml-3">Brand Logos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="hero-reels.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-film w-6"></i>
                            <span class="nav-item-text ml-3">Hero Reels</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-700">
                <ul>
                    <li class="nav-item">
                        <a href="../index.php" target="_blank" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-external-link-alt w-6"></i>
                            <span class="nav-item-text ml-3">View Website</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span class="nav-item-text ml-3">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="admin-content" class="admin-content flex-1 flex flex-col">
            <!-- Top Navigation -->
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <div class="flex items-center">
                    <button id="mobile-menu-button" class="mr-4 p-2 rounded-md text-gray-700 lg:hidden block hover:bg-gray-100">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-[#2B2B2A]">Admin Panel</h2>
                </div>
                
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></span>
                    <a href="logout.php" class="text-gray-600 hover:text-[#F44B12] transition duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4">
    <?php endif; ?>
