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
    <div class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg z-50">
        <!-- Header -->
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
        
        <!-- Navigation -->
        <nav class="mt-6">
            <a href="javascript:void(0)" onclick="showSection('dashboard')" class="nav-link active flex items-center px-6 py-3 text-white bg-blue-500">
                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </a>
            <a href="javascript:void(0)" onclick="showSection('users')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-users mr-3"></i>Users Management
            </a>
            <a href="javascript:void(0)" onclick="showSection('doctors')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-user-md mr-3"></i>Doctors
            </a>
            <a href="javascript:void(0)" onclick="showSection('patients')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-user-injured mr-3"></i>Patients
            </a>
            <a href="javascript:void(0)" onclick="showSection('appointments')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-calendar-alt mr-3"></i>Appointments
            </a>
            <a href="javascript:void(0)" onclick="showSection('prescriptions')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>Prescriptions
            </a>
            <a href="javascript:void(0)" onclick="showSection('vitals')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-heartbeat mr-3"></i>Vitals
            </a>
            <a href="javascript:void(0)" onclick="showSection('settings')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-cog mr-3"></i>Settings
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
                    <button onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                        <i id="themeIcon" class="fas fa-moon text-gray-600 dark:text-yellow-400"></i>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p id="totalUsers" class="text-2xl font-semibold text-gray-900 dark:text-white">Loading...</p>
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
                                <p id="totalDoctors" class="text-2xl font-semibold text-gray-900 dark:text-white">Loading...</p>
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
                                <p id="totalPatients" class="text-2xl font-semibold text-gray-900 dark:text-white">Loading...</p>
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
                                <p id="totalAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Appointments</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Patient</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Doctor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentAppointments" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Loading recent appointments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Users Section -->
            <div id="usersSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Users Management</h2>
                    <button onclick="showInfo('Add user functionality')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading users...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Doctors Section -->
            <div id="doctorsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Doctors Management</h2>
                    <button onclick="showInfo('Add doctor functionality')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Doctor
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Specialization</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">License</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorsTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading doctors...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Patients Section -->
            <div id="patientsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Patients Management</h2>
                    <button onclick="showInfo('Add patient functionality')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Patient
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Patient Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Blood Group</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading patients...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Appointments Section -->
            <div id="appointmentsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Appointments Management</h2>
                    <button onclick="showInfo('Book appointment functionality')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Book Appointment
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading appointments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Prescriptions Section -->
            <div id="prescriptionsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Prescriptions Management</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prescription #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="prescriptionsTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading prescriptions...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Vitals Section -->
            <div id="vitalsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Vitals Management</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vital Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="vitalsTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Loading vitals...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settingsSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Website Settings</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Name</label>
                                <input type="text" value="Hospital CRM" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Email</label>
                                <input type="email" value="admin@hospital.com" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <button onclick="showSuccess('Settings saved successfully!')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Save Settings
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Info</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Version:</span>
                                <span class="text-gray-900 dark:text-white">1.0.0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">PHP Version:</span>
                                <span class="text-gray-900 dark:text-white"><?php echo phpversion(); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Database:</span>
                                <span class="text-gray-900 dark:text-white">MySQL</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profileSection" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-2xl font-bold"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-sm text-blue-600 dark:text-blue-400">Administrator</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" readonly>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button onclick="showSuccess('Profile updated successfully!')" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            Update Profile
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        console.log('Admin Dashboard Loading...');
        
        // Theme Management
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                themeIcon.className = 'fas fa-moon text-gray-600';
                localStorage.setItem('theme', 'light');
                if (window.showSuccess) showSuccess('Switched to light theme');
            } else {
                html.classList.add('dark');
                themeIcon.className = 'fas fa-sun text-yellow-400';
                localStorage.setItem('theme', 'dark');
                if (window.showSuccess) showSuccess('Switched to dark theme');
            }
        }
        
        // User Menu Toggle
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Section Management
        function showSection(sectionName) {
            console.log('Showing section:', sectionName);
            
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName + 'Section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            // Update navigation
            document.querySelectorAll('.nav-link').forEach(link => {
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
                'prescriptions': 'Prescriptions Management',
                'vitals': 'Vitals Management',
                'settings': 'Website Settings',
                'profile': 'My Profile'
            };
            
            document.getElementById('pageTitle').textContent = titles[sectionName] || 'Dashboard';
            
            // Hide user menu
            const userMenu = document.getElementById('userMenu');
            if (!userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
            }
            
            // Load section data
            loadSectionData(sectionName);
        }
        
        // Load section data
        async function loadSectionData(section) {
            console.log('Loading data for:', section);
            
            switch(section) {
                case 'dashboard':
                    await loadDashboardStats();
                    break;
                case 'users':
                    await loadUsers();
                    break;
                case 'doctors':
                    await loadDoctors();
                    break;
                case 'patients':
                    await loadPatients();
                    break;
                case 'appointments':
                    await loadAppointments();
                    break;
                case 'prescriptions':
                    await loadPrescriptions();
                    break;
                case 'vitals':
                    await loadVitals();
                    break;
            }
        }
        
        // Dashboard Stats
        async function loadDashboardStats() {
            try {
                const response = await fetch('../handlers/admin_stats.php');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('totalUsers').textContent = result.data.total_users || '0';
                    document.getElementById('totalDoctors').textContent = result.data.total_doctors || '0';
                    document.getElementById('totalPatients').textContent = result.data.total_patients || '0';
                    document.getElementById('totalAppointments').textContent = result.data.total_appointments || '0';
                }
                
                // Load recent appointments
                const appointmentsResponse = await fetch('../handlers/admin_appointments.php');
                const appointmentsResult = await appointmentsResponse.json();
                
                if (appointmentsResult.success) {
                    displayRecentAppointments(appointmentsResult.data);
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
                document.getElementById('totalUsers').textContent = 'Error';
                document.getElementById('totalDoctors').textContent = 'Error';
                document.getElementById('totalPatients').textContent = 'Error';
                document.getElementById('totalAppointments').textContent = 'Error';
            }
        }
        
        function displayRecentAppointments(appointments) {
            const tbody = document.getElementById('recentAppointments');
            tbody.innerHTML = '';
            
            if (appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No recent appointments</td></tr>';
                return;
            }
            
            appointments.slice(0, 5).forEach(appointment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${appointment.patient_name}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${appointment.doctor_name}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${appointment.appointment_date}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(appointment.status)}">
                            ${appointment.status}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Users
        async function loadUsers() {
            try {
                const response = await fetch('../handlers/admin_users.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    displayUsers(result.data);
                } else {
                    if (window.showError) showError('Error loading users');
                }
            } catch (error) {
                console.error('Error loading users:', error);
                if (window.showError) showError('Error loading users');
            }
        }
        
        function displayUsers(users) {
            const tbody = document.getElementById('usersTable');
            tbody.innerHTML = '';
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found</td></tr>';
                return;
            }
            
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${user.first_name} ${user.last_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${user.email}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${getRoleColor(user.role)}">
                            ${user.role}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${user.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="showInfo('Edit user: ${user.first_name}')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showWarning('Delete user: ${user.first_name}')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Doctors
        async function loadDoctors() {
            try {
                const response = await fetch('../handlers/admin_users.php?action=list&role=doctor');
                const result = await response.json();
                
                if (result.success) {
                    displayDoctors(result.data);
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }
        
        function displayDoctors(doctors) {
            const tbody = document.getElementById('doctorsTable');
            tbody.innerHTML = '';
            
            if (doctors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No doctors found</td></tr>';
                return;
            }
            
            doctors.forEach(doctor => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">Dr. ${doctor.first_name} ${doctor.last_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${doctor.specialization || 'Not specified'}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${doctor.license_number || 'Not provided'}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${doctor.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${doctor.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="showInfo('Edit doctor: Dr. ${doctor.first_name}')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showWarning('Delete doctor: Dr. ${doctor.first_name}')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Patients
        async function loadPatients() {
            try {
                const response = await fetch('../handlers/admin_users.php?action=list&role=patient');
                const result = await response.json();
                
                if (result.success) {
                    displayPatients(result.data);
                }
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }
        
        function displayPatients(patients) {
            const tbody = document.getElementById('patientsTable');
            tbody.innerHTML = '';
            
            if (patients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No patients found</td></tr>';
                return;
            }
            
            patients.forEach(patient => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${patient.first_name} ${patient.last_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${patient.patient_code || 'Not assigned'}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${patient.blood_group || 'Not specified'}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${patient.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${patient.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="showInfo('Edit patient: ${patient.first_name}')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showWarning('Delete patient: ${patient.first_name}')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Appointments
        async function loadAppointments() {
            try {
                const response = await fetch('../handlers/appointments.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    displayAppointments(result.data);
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
            }
        }
        
        function displayAppointments(appointments) {
            const tbody = document.getElementById('appointmentsTable');
            tbody.innerHTML = '';
            
            if (appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No appointments found</td></tr>';
                return;
            }
            
            appointments.forEach(appointment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${appointment.patient_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${appointment.doctor_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${appointment.appointment_date} ${appointment.appointment_time}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(appointment.status)}">
                            ${appointment.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="showInfo('Edit appointment')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showWarning('Cancel appointment')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Prescriptions
        async function loadPrescriptions() {
            try {
                const response = await fetch('../handlers/prescriptions.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    displayPrescriptions(result.data);
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
            }
        }
        
        function displayPrescriptions(prescriptions) {
            const tbody = document.getElementById('prescriptionsTable');
            tbody.innerHTML = '';
            
            if (prescriptions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No prescriptions found</td></tr>';
                return;
            }
            
            prescriptions.forEach(prescription => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.prescription_number}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.patient_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.doctor_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.prescription_date}</td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="showInfo('View prescription')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="showInfo('Print prescription')" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Vitals
        async function loadVitals() {
            document.getElementById('vitalsTable').innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Vitals functionality coming soon</td></tr>';
        }
        
        // Utility Functions
        function getStatusColor(status) {
            const colors = {
                'scheduled': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'pending': 'bg-yellow-100 text-yellow-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        function getRoleColor(role) {
            const colors = {
                'admin': 'bg-red-100 text-red-800',
                'doctor': 'bg-blue-100 text-blue-800',
                'patient': 'bg-green-100 text-green-800'
            };
            return colors[role] || 'bg-gray-100 text-gray-800';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Dashboard Initialized');
            
            // Load saved theme
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                html.classList.add('dark');
                themeIcon.className = 'fas fa-sun text-yellow-400';
            }
            
            // Load dashboard by default
            loadSectionData('dashboard');
            
            // Close user menu when clicking outside
            document.addEventListener('click', function(event) {
                const userMenu = document.getElementById('userMenu');
                const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
                
                if (!userButton && !event.target.closest('#userMenu')) {
                    if (!userMenu.classList.contains('hidden')) {
                        userMenu.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>
</html>