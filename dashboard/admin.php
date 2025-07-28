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
    <title>Admin Dashboard - Hospital CRM</title>
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
    
    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg z-50">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hospital text-white"></i>
                </div>
                <div class="ml-3">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hospital CRM</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <nav class="mt-6">
            <a href="javascript:void(0)" onclick="showSection('dashboard')" class="nav-link active flex items-center px-6 py-3 text-white bg-blue-500 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </a>
            <a href="javascript:void(0)" onclick="showSection('users')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>Users Management
            </a>
            <a href="javascript:void(0)" onclick="showSection('doctors')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-md mr-3"></i>Doctors
            </a>
            <a href="javascript:void(0)" onclick="showSection('patients')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-injured mr-3"></i>Patients
            </a>
            <a href="javascript:void(0)" onclick="showSection('appointments')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>Appointments
            </a>
            <a href="javascript:void(0)" onclick="showSection('vitals')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>Vitals
            </a>
            <a href="javascript:void(0)" onclick="showSection('prescriptions')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>Prescriptions
            </a>
            <a href="javascript:void(0)" onclick="showSection('settings')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-cog mr-3"></i>Website Settings
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="ml-64">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4">
                <h1 id="pageTitle" class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                        <i id="themeIcon" class="fas fa-moon text-gray-600 dark:text-yellow-400"></i>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                            </div>
                            <span class="hidden md:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50 border border-gray-200 dark:border-gray-700">
                            <a href="javascript:void(0)" onclick="showSection('profile')" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content Area -->
        <main class="p-6">
            <!-- Dashboard Section -->
            <div id="dashboardSection" class="section">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard Overview</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">150</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-user-md text-green-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Doctors</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">25</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-user-injured text-purple-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Patients</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">125</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                                <i class="fas fa-calendar-alt text-yellow-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Appointments</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">89</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                    <p class="text-gray-600 dark:text-gray-400">Dashboard content loaded successfully!</p>
                </div>
            </div>
            
            <!-- Users Section -->
            <div id="usersSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Users Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Users management section loaded!</p>
                    <button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </button>
                </div>
            </div>
            
            <!-- Doctors Section -->
            <div id="doctorsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Doctors Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Doctors management section loaded!</p>
                    <button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Doctor
                    </button>
                </div>
            </div>
            
            <!-- Patients Section -->
            <div id="patientsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Patients Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Patients management section loaded!</p>
                    <button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Patient
                    </button>
                </div>
            </div>
            
            <!-- Appointments Section -->
            <div id="appointmentsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Appointments Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Appointments management section loaded!</p>
                    <button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Book Appointment
                    </button>
                </div>
            </div>
            
            <!-- Vitals Section -->
            <div id="vitalsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Vitals Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Vitals management section loaded!</p>
                    <button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Vital Record
                    </button>
                </div>
            </div>
            
            <!-- Prescriptions Section -->
            <div id="prescriptionsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Prescriptions Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Prescriptions management section loaded!</p>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settingsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Website Settings</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <p class="text-gray-600 dark:text-gray-400">Website settings section loaded!</p>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profileSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h2>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-2xl font-bold"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-sm text-blue-600 dark:text-blue-400">Administrator</p>
                        </div>
                    </div>
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        console.log('Admin dashboard script loaded');
        
        // Theme Management
        function toggleTheme() {
            console.log('Theme toggle clicked');
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                themeIcon.className = 'fas fa-moon text-gray-600';
                localStorage.setItem('theme', 'light');
                showSuccess('Switched to light theme');
                console.log('Switched to light theme');
            } else {
                html.classList.add('dark');
                themeIcon.className = 'fas fa-sun text-yellow-400';
                localStorage.setItem('theme', 'dark');
                showSuccess('Switched to dark theme');
                console.log('Switched to dark theme');
            }
        }
        
        // User Menu Toggle
        function toggleUserMenu() {
            console.log('User menu toggle clicked');
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
            console.log('User menu toggled, hidden:', menu.classList.contains('hidden'));
        }
        
        // Section Management
        function showSection(sectionName) {
            console.log('Showing section:', sectionName);
            
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName + 'Section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
                console.log('Section shown:', sectionName);
                showInfo('Loaded ' + sectionName + ' section');
            } else {
                console.error('Section not found:', sectionName + 'Section');
                showError('Section not found: ' + sectionName);
                return;
            }
            
            // Update navigation
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.classList.remove('active', 'bg-blue-500', 'text-white');
                link.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            // Find and highlight active link
            const activeLink = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
            if (activeLink) {
                activeLink.classList.add('active', 'bg-blue-500', 'text-white');
                activeLink.classList.remove('text-gray-700', 'dark:text-gray-300');
            }
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'users': 'Users Management',
                'doctors': 'Doctors Management',
                'patients': 'Patients Management',
                'appointments': 'Appointments Management',
                'vitals': 'Vitals Management',
                'prescriptions': 'Prescriptions Management',
                'settings': 'Website Settings',
                'profile': 'My Profile'
            };
            
            const pageTitle = document.getElementById('pageTitle');
            pageTitle.textContent = titles[sectionName] || 'Dashboard';
            
            // Hide user menu if open
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing admin dashboard');
            
            // Load saved theme
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                html.classList.add('dark');
                themeIcon.className = 'fas fa-sun text-yellow-400';
                console.log('Dark theme loaded from localStorage');
            } else {
                html.classList.remove('dark');
                themeIcon.className = 'fas fa-moon text-gray-600';
                console.log('Light theme loaded');
            }
            
            // Close user menu when clicking outside
            document.addEventListener('click', function(event) {
                const userMenu = document.getElementById('userMenu');
                const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
                
                if (!userButton && !event.target.closest('#userMenu')) {
                    if (userMenu && !userMenu.classList.contains('hidden')) {
                        userMenu.classList.add('hidden');
                        console.log('User menu closed by outside click');
                    }
                }
            });
            
            console.log('Admin dashboard initialized successfully');
            showSuccess('Admin dashboard loaded successfully!');
        });
    </script>
</body>
</html>