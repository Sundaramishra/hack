<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/notifications.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Debug Admin Dashboard</h1>
        
        <!-- Theme Toggle -->
        <div class="mb-6">
            <button onclick="toggleTheme()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block"></i>
                Toggle Theme
            </button>
            
            <button onclick="testFunction()" class="ml-4 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">
                Test Function
            </button>
        </div>
        
        <!-- Navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Navigation Test</h2>
            <div class="space-y-2">
                <a href="#" onclick="showSection('dashboard', this); return false;" class="nav-link active block px-4 py-2 bg-blue-500 text-white rounded-lg">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="#" onclick="showSection('users', this); return false;" class="nav-link block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="#" onclick="showSection('appointments', this); return false;" class="nav-link block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-calendar-alt mr-2"></i>Appointments
                </a>
            </div>
        </div>
        
        <!-- User Menu Test -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">User Menu Test</h2>
            <div class="relative inline-block">
                <button onclick="toggleUserMenu()" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                    </div>
                    <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50">
                    <a href="#" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <a href="../logout.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Sections -->
        <div class="space-y-6">
            <div id="dashboardSection" class="section bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard Section</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">This is the dashboard section content.</p>
            </div>
            
            <div id="usersSection" class="section hidden bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Users Section</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">This is the users section content.</p>
            </div>
            
            <div id="appointmentsSection" class="section hidden bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Appointments Section</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">This is the appointments section content.</p>
            </div>
        </div>
    </div>

    <script>
        // Debug function
        function debugLog(message) {
            console.log('[Debug Admin]', message);
            alert('[Debug] ' + message); // Also show alert for visibility
        }
        
        // Test function
        function testFunction() {
            debugLog('Test function called successfully!');
            showSuccess('Test function working!');
        }
        
        // Theme Management
        function toggleTheme() {
            debugLog('Toggle theme clicked');
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                debugLog('Switched to light theme');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                debugLog('Switched to dark theme');
            }
        }
        
        // User Menu
        function toggleUserMenu() {
            debugLog('Toggle user menu clicked');
            const menu = document.getElementById('userMenu');
            if (menu) {
                menu.classList.toggle('hidden');
                debugLog('User menu toggled - hidden: ' + menu.classList.contains('hidden'));
            } else {
                debugLog('ERROR: User menu element not found');
            }
        }
        
        // Section Management
        function showSection(sectionName, element) {
            debugLog('Show section called: ' + sectionName);
            
            try {
                // Hide all sections
                const sections = document.querySelectorAll('.section');
                debugLog('Found ' + sections.length + ' sections');
                sections.forEach(section => {
                    section.classList.add('hidden');
                });
                
                // Show selected section
                const targetSection = document.getElementById(sectionName + 'Section');
                if (targetSection) {
                    targetSection.classList.remove('hidden');
                    debugLog('Section shown: ' + sectionName);
                } else {
                    debugLog('ERROR: Section not found: ' + sectionName + 'Section');
                    return;
                }
                
                // Update nav links
                const navLinks = document.querySelectorAll('.nav-link');
                debugLog('Found ' + navLinks.length + ' nav links');
                navLinks.forEach(link => {
                    link.classList.remove('active', 'bg-blue-500', 'text-white');
                    link.classList.add('text-gray-700', 'dark:text-gray-300');
                });
                
                // Update clicked element
                if (element) {
                    element.classList.add('active', 'bg-blue-500', 'text-white');
                    element.classList.remove('text-gray-700', 'dark:text-gray-300');
                    debugLog('Navigation link updated');
                } else {
                    debugLog('WARNING: No element provided to update');
                }
                
                debugLog('Section switching completed successfully');
                
            } catch (error) {
                debugLog('ERROR in showSection: ' + error.message);
                console.error('Error in showSection:', error);
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('DOM Content Loaded - Debug Admin Dashboard');
            
            // Load saved theme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                debugLog('Dark theme loaded from localStorage');
            }
            
            // Test if elements exist
            const userMenu = document.getElementById('userMenu');
            const dashboardSection = document.getElementById('dashboardSection');
            const navLinks = document.querySelectorAll('.nav-link');
            
            debugLog('Elements check:');
            debugLog('- User menu: ' + (userMenu ? 'Found' : 'NOT FOUND'));
            debugLog('- Dashboard section: ' + (dashboardSection ? 'Found' : 'NOT FOUND'));
            debugLog('- Nav links: ' + navLinks.length + ' found');
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#userMenu') && !event.target.closest('button[onclick="toggleUserMenu()"]')) {
                    const userMenu = document.getElementById('userMenu');
                    if (userMenu && !userMenu.classList.contains('hidden')) {
                        userMenu.classList.add('hidden');
                        debugLog('User menu closed by outside click');
                    }
                }
            });
            
            debugLog('Debug admin dashboard initialized successfully');
        });
        
        // Error handler
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            debugLog('JavaScript Error: ' + msg + ' at line ' + lineNo);
            return false;
        };
    </script>
</body>
</html>