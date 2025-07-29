<?php
require_once '../includes/auth.php';
require_once '../includes/settings.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();

// Load website settings
$siteName = WebsiteSettings::getSiteName();
$siteLogo = WebsiteSettings::getSiteLogo();
$favicon = WebsiteSettings::getFavicon();
$primaryColor = WebsiteSettings::getPrimaryColor();
$secondaryColor = WebsiteSettings::getSecondaryColor();
$accentColor = WebsiteSettings::getAccentColor();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars($siteName); ?></title>
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/notifications.js"></script>
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
        }
        .bg-primary { background-color: var(--primary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .border-primary { border-color: var(--primary-color) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        .text-secondary { color: var(--secondary-color) !important; }
        .bg-accent { background-color: var(--accent-color) !important; }
        .text-accent { color: var(--accent-color) !important; }
    </style>
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
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($siteName); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-6">
            <a href="javascript:void(0)" onclick="showSection('dashboard')" class="nav-link active flex items-center px-6 py-3 text-white bg-primary">
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
                <i class="fas fa-heartbeat mr-3"></i>Vitals Management
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
                    <button onclick="openUserModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
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
                    <button onclick="openDoctorModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
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
                    <button onclick="openPatientModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
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
                    <button onclick="openAppointmentModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
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
            
            <!-- Vitals Management Section -->
            <div id="vitalsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Vitals Management</h2>
                    <div class="flex space-x-3">
                        <button onclick="openVitalModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add Vital Record
                        </button>
                        <button onclick="openCustomVitalModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-cog mr-2"></i>Manage Vital Types
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Patient Vitals -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Patient Vitals</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Patient</label>
                                <select id="vitalPatientSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Loading patients...</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vital Type</label>
                                <select id="vitalTypeSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Loading vital types...</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Value</label>
                                <input type="text" id="vitalValue" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Enter vital value">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                                <textarea id="vitalNotes" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                            <button onclick="addVitalRecord()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg">
                                <i class="fas fa-plus mr-2"></i>Add Vital Record
                            </button>
                        </div>
                    </div>
                    
                    <!-- Doctor Vitals (Admin can view all) -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Doctor Vitals Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Doctor</label>
                                <select id="doctorVitalSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Loading doctors...</option>
                                </select>
                            </div>
                            <button onclick="viewDoctorPatientVitals()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg">
                                <i class="fas fa-eye mr-2"></i>View Doctor's Patient Vitals
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Vital Records</h3>
                    </div>
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
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- General Settings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General Settings</h3>
                        <form id="generalSettingsForm" onsubmit="saveGeneralSettings(event)">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Name</label>
                                    <input type="text" id="siteName" name="site_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Hospital CRM">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Email</label>
                                    <input type="email" id="contactEmail" name="contact_email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="admin@hospital.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Phone</label>
                                    <input type="tel" id="contactPhone" name="contact_phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="+1234567890">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hospital Address</label>
                                    <textarea id="address" name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="123 Hospital Street, Medical City"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-save mr-2"></i>Save General Settings
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Theme & Colors -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Theme & Colors</h3>
                        <form id="themeSettingsForm" onsubmit="saveThemeSettings(event)">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Primary Color</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="color" id="primaryColor" name="primary_color" class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-md">
                                        <input type="text" id="primaryColorText" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="#3B82F6">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Secondary Color</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="color" id="secondaryColor" name="secondary_color" class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-md">
                                        <input type="text" id="secondaryColorText" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="#1E40AF">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Accent Color</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="color" id="accentColor" name="accent_color" class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-md">
                                        <input type="text" id="accentColorText" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="#10B981">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="darkModeEnabled" name="dark_mode_enabled" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="darkModeEnabled" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Dark Mode Toggle</label>
                                </div>
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-palette mr-2"></i>Save Theme Settings
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Logo & Favicon -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo & Favicon</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Website Logo</label>
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <img id="logoPreview" src="" alt="Logo" class="w-full h-full object-contain rounded-lg hidden">
                                        <i class="fas fa-image text-gray-400 text-2xl" id="logoPlaceholder"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" id="logoFile" accept="image/*" class="hidden" onchange="previewFile('logo')">
                                        <button type="button" onclick="document.getElementById('logoFile').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                            <i class="fas fa-upload mr-2"></i>Upload Logo
                                        </button>
                                        <button type="button" onclick="uploadFile('site_logo', 'logoFile')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm ml-2">
                                            <i class="fas fa-save mr-2"></i>Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Favicon</label>
                                <div class="flex items-center space-x-4">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                        <img id="faviconPreview" src="" alt="Favicon" class="w-full h-full object-contain rounded hidden">
                                        <i class="fas fa-star text-gray-400 text-sm" id="faviconPlaceholder"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" id="faviconFile" accept="image/*,.ico" class="hidden" onchange="previewFile('favicon')">
                                        <button type="button" onclick="document.getElementById('faviconFile').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                            <i class="fas fa-upload mr-2"></i>Upload Favicon
                                        </button>
                                        <button type="button" onclick="uploadFile('favicon', 'faviconFile')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm ml-2">
                                            <i class="fas fa-save mr-2"></i>Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Information</h3>
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
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                                <span id="lastUpdated" class="text-gray-900 dark:text-white">Loading...</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Maintenance Mode</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable to put website in maintenance</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="maintenanceMode" class="sr-only peer" onchange="toggleMaintenanceMode()">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
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

    <!-- Modals -->
    <!-- User Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New User</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="userForm" onsubmit="submitUser(event)">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                            <select name="gender" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select Role</option>
                            <option value="admin">Admin Only</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">For doctors/patients, use respective sections</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Book Appointment</h3>
                <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="appointmentForm" onsubmit="submitAppointment(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Patient</label>
                        <div class="relative">
                            <input type="text" id="patientSearch" placeholder="Search patient by name..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" onkeyup="searchPatients(this.value)">
                            <select name="patient_id" id="appointmentPatientSelect" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white mt-2">
                                <option value="">Loading patients...</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Doctor</label>
                        <select name="doctor_id" id="appointmentDoctorSelect" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Loading doctors...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                        <input type="date" name="appointment_date" id="appointmentDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" onchange="loadTimeSlots()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available Time Slots</label>
                        <div id="timeSlotsContainer" class="grid grid-cols-3 gap-2 max-h-40 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4 col-span-3">Select doctor and date first</div>
                        </div>
                        <input type="hidden" name="appointment_time" id="selectedTimeSlot" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason</label>
                        <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Reason for appointment"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAppointmentModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Book Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Doctor Modal -->
    <div id="doctorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Doctor</h3>
                <button onclick="closeDoctorModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="doctorForm" onsubmit="submitDoctor(event)">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                            <select name="gender" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Specialization</label>
                        <input type="text" name="specialization" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Cardiology">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">License Number <span class="text-gray-500">(Optional)</span></label>
                        <input type="text" name="license_number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Experience (Years)</label>
                        <input type="number" name="experience_years" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeDoctorModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Add Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patient Modal -->
    <div id="patientModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Patient</h3>
                <button onclick="closePatientModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="patientForm" onsubmit="submitPatient(event)">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                            <select name="gender" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Blood Group</label>
                        <select name="blood_group" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Allergies</label>
                        <textarea name="allergies" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Any known allergies"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closePatientModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Add Patient
                    </button>
                </div>
            </form>
        </div>
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
            } else {
                html.classList.add('dark');
                themeIcon.className = 'fas fa-sun text-yellow-400';
                localStorage.setItem('theme', 'dark');
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
                link.classList.remove('active', 'bg-primary', 'text-white');
                link.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            // Find and highlight active link
            const activeLink = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
            if (activeLink) {
                activeLink.classList.add('active', 'bg-primary', 'text-white');
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
                    await loadAppointmentSelects();
                    break;
                case 'prescriptions':
                    await loadPrescriptions();
                    break;
                case 'vitals':
                    await loadVitals();
                    await loadVitalSelects();
                    break;
                case 'settings':
                    await loadSettings();
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
                if (!appointmentsResponse.ok) {
                    throw new Error(`Appointments API error! status: ${appointmentsResponse.status}`);
                }
                const appointmentsResult = await appointmentsResponse.json();
                
                if (appointmentsResult.success) {
                    displayRecentAppointments(appointmentsResult.data);
                } else {
                    console.error('Recent appointments error:', appointmentsResult.message);
                    document.getElementById('recentAppointments').innerHTML = 
                        '<tr><td colspan="4" class="px-4 py-8 text-center text-red-500">Error: ' + (appointmentsResult.message || 'Failed to load appointments') + '</td></tr>';
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
                document.getElementById('totalUsers').textContent = 'Error';
                document.getElementById('totalDoctors').textContent = 'Error';
                document.getElementById('totalPatients').textContent = 'Error';
                document.getElementById('totalAppointments').textContent = 'Error';
                
                // Show error in recent appointments too
                document.getElementById('recentAppointments').innerHTML = 
                    '<tr><td colspan="4" class="px-4 py-8 text-center text-red-500">Error loading data: ' + error.message + '</td></tr>';
            }
        }
        
        function displayRecentAppointments(appointments) {
            const tbody = document.getElementById('recentAppointments');
            tbody.innerHTML = '';
            
            if (appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No recent appointments</td></tr>';
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
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No users found</td></tr>';
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
                        <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900">
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
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No doctors found</td></tr>';
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
                        <button onclick="editDoctor(${doctor.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteDoctor(${doctor.id})" class="text-red-600 hover:text-red-900">
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
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No patients found</td></tr>';
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
                        <button onclick="editPatient(${patient.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deletePatient(${patient.id})" class="text-red-600 hover:text-red-900">
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
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No appointments found</td></tr>';
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
                        <button onclick="editAppointment(${appointment.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="cancelAppointment(${appointment.id})" class="text-red-600 hover:text-red-900">
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
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                
                if (result.success) {
                    displayPrescriptions(result.data);
                } else {
                    console.error('Prescriptions API error:', result.message);
                    document.getElementById('prescriptionsTable').innerHTML = 
                        '<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Error: ' + (result.message || 'Failed to load prescriptions') + '</td></tr>';
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
                document.getElementById('prescriptionsTable').innerHTML = 
                    '<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Error loading prescriptions: ' + error.message + '</td></tr>';
            }
        }
        
        function displayPrescriptions(prescriptions) {
            const tbody = document.getElementById('prescriptionsTable');
            tbody.innerHTML = '';
            
            if (prescriptions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No prescriptions found</td></tr>';
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
                        <button onclick="viewPrescription(${prescription.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="printPrescription(${prescription.id})" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load Vitals
        async function loadVitals() {
            try {
                const response = await fetch('../handlers/vitals.php?action=patient_vitals&patient_id=all');
                const result = await response.json();
                
                if (result.success) {
                    displayVitals(result.data);
                } else {
                    document.getElementById('vitalsTable').innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No vitals found</td></tr>';
                }
            } catch (error) {
                console.error('Error loading vitals:', error);
                document.getElementById('vitalsTable').innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Error loading vitals</td></tr>';
            }
        }
        
        function displayVitals(vitals) {
            const tbody = document.getElementById('vitalsTable');
            tbody.innerHTML = '';
            
            if (vitals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No vitals found</td></tr>';
                return;
            }
            
            vitals.forEach(vital => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${vital.patient_name || 'Unknown'}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${vital.vital_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${vital.value} ${vital.unit || ''}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${vital.recorded_date}</td>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="editVital(${vital.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteVital(${vital.id})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Load dropdown selects for appointments and vitals
        async function loadAppointmentSelects() {
            try {
                // Load patients for appointment modal
                const patientsResponse = await fetch('../handlers/admin_users.php?action=list&role=patient');
                const patientsResult = await patientsResponse.json();
                
                if (patientsResult.success) {
                    // Store all patients for search functionality
                    allPatients = patientsResult.data.map(patient => ({
                        patient_id: patient.patient_id,
                        name: `${patient.first_name} ${patient.last_name}`,
                        patient_code: patient.patient_code || `P${patient.patient_id.toString().padStart(3, '0')}`
                    }));
                    
                    const patientSelect = document.getElementById('appointmentPatientSelect');
                    patientSelect.innerHTML = '<option value="">Select Patient</option>';
                    allPatients.forEach(patient => {
                        patientSelect.innerHTML += `<option value="${patient.patient_id}">${patient.name} (${patient.patient_code})</option>`;
                    });
                }
                
                // Load doctors for appointment modal
                const doctorsResponse = await fetch('../handlers/admin_users.php?action=list&role=doctor');
                const doctorsResult = await doctorsResponse.json();
                
                if (doctorsResult.success) {
                    const doctorSelect = document.getElementById('appointmentDoctorSelect');
                    doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                    doctorsResult.data.forEach(doctor => {
                        doctorSelect.innerHTML += `<option value="${doctor.doctor_id}">Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}</option>`;
                    });
                    
                    // Add change event to reload time slots when doctor changes
                    doctorSelect.onchange = loadTimeSlots;
                }
            } catch (error) {
                console.error('Error loading appointment selects:', error);
            }
        }
        
        async function loadVitalSelects() {
            try {
                // Load patients for vitals
                const patientsResponse = await fetch('../handlers/admin_users.php?action=list&role=patient');
                const patientsResult = await patientsResponse.json();
                
                if (patientsResult.success) {
                    const patientSelect = document.getElementById('vitalPatientSelect');
                    patientSelect.innerHTML = '<option value="">Select Patient</option>';
                    patientsResult.data.forEach(patient => {
                        patientSelect.innerHTML += `<option value="${patient.id}">${patient.first_name} ${patient.last_name}</option>`;
                    });
                }
                
                // Load doctors for vitals overview
                const doctorsResponse = await fetch('../handlers/admin_users.php?action=list&role=doctor');
                const doctorsResult = await doctorsResponse.json();
                
                if (doctorsResult.success) {
                    const doctorSelect = document.getElementById('doctorVitalSelect');
                    doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                    doctorsResult.data.forEach(doctor => {
                        doctorSelect.innerHTML += `<option value="${doctor.id}">Dr. ${doctor.first_name} ${doctor.last_name}</option>`;
                    });
                }
                
                // Load vital types
                const vitalTypesResponse = await fetch('../handlers/vitals.php?action=types');
                const vitalTypesResult = await vitalTypesResponse.json();
                
                if (vitalTypesResult.success) {
                    const vitalTypeSelect = document.getElementById('vitalTypeSelect');
                    vitalTypeSelect.innerHTML = '<option value="">Select Vital Type</option>';
                    vitalTypesResult.data.forEach(vitalType => {
                        vitalTypeSelect.innerHTML += `<option value="${vitalType.id}">${vitalType.name} (${vitalType.unit})</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading vital selects:', error);
            }
        }
        
        // Modal Functions
        function openUserModal() {
            document.getElementById('userModal').classList.remove('hidden');
        }
        
        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
            document.getElementById('userForm').reset();
        }
        
        function openDoctorModal() {
            document.getElementById('doctorModal').classList.remove('hidden');
        }
        
        function closeDoctorModal() {
            document.getElementById('doctorModal').classList.add('hidden');
            document.getElementById('doctorForm').reset();
        }
        
        function openPatientModal() {
            document.getElementById('patientModal').classList.remove('hidden');
        }
        
        function closePatientModal() {
            document.getElementById('patientModal').classList.add('hidden');
            document.getElementById('patientForm').reset();
        }
        
        function openAppointmentModal() {
            document.getElementById('appointmentModal').classList.remove('hidden');
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('appointmentDate').min = today;
            
            loadAppointmentSelects();
        }
        
        function closeAppointmentModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
            document.getElementById('appointmentForm').reset();
            document.getElementById('patientSearch').value = '';
            document.getElementById('timeSlotsContainer').innerHTML = '<div class="text-center text-gray-500 py-4 col-span-3">Select doctor and date first</div>';
            document.getElementById('selectedTimeSlot').value = '';
        }
        
        // Patient search functionality
        let allPatients = [];
        
        function searchPatients(searchTerm) {
            const select = document.getElementById('appointmentPatientSelect');
            const filteredPatients = allPatients.filter(patient => 
                patient.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                patient.patient_code.toLowerCase().includes(searchTerm.toLowerCase())
            );
            
            select.innerHTML = '<option value="">Select Patient</option>';
            filteredPatients.forEach(patient => {
                const option = document.createElement('option');
                option.value = patient.patient_id;
                option.textContent = `${patient.name} (${patient.patient_code})`;
                select.appendChild(option);
            });
        }
        
        // Load time slots based on selected doctor and date
        async function loadTimeSlots() {
            const doctorId = document.getElementById('appointmentDoctorSelect').value;
            const date = document.getElementById('appointmentDate').value;
            const container = document.getElementById('timeSlotsContainer');
            
            if (!doctorId || !date) {
                container.innerHTML = '<div class="text-center text-gray-500 py-4 col-span-3">Select doctor and date first</div>';
                document.getElementById('selectedTimeSlot').value = '';
                return;
            }
            
            try {
                container.innerHTML = '<div class="text-center text-gray-500 py-4 col-span-3"><i class="fas fa-spinner fa-spin mr-2"></i>Loading time slots...</div>';
                
                const response = await fetch(`../handlers/get_time_slots.php?doctor_id=${doctorId}&date=${date}`);
                const result = await response.json();
                
                if (result.success) {
                    if (result.data.length === 0) {
                        container.innerHTML = '<div class="text-center text-gray-500 py-4 col-span-3">No available slots for this date</div>';
                        return;
                    }
                    
                    container.innerHTML = '';
                    result.data.forEach(slot => {
                        const slotDiv = document.createElement('div');
                        slotDiv.className = `p-2 text-center text-sm rounded-lg cursor-pointer border-2 transition-all ${
                            slot.available 
                                ? 'border-gray-300 bg-white hover:border-blue-500 hover:bg-blue-50 dark:bg-gray-700 dark:border-gray-600 dark:hover:border-blue-400' 
                                : 'border-red-300 bg-red-50 text-red-500 cursor-not-allowed dark:bg-red-900/20 dark:border-red-600'
                        }`;
                        slotDiv.textContent = slot.time;
                        
                        if (slot.available) {
                            slotDiv.onclick = function() {
                                // Remove previous selection
                                container.querySelectorAll('.border-blue-500').forEach(el => {
                                    el.classList.remove('border-blue-500', 'bg-blue-500', 'text-white');
                                    el.classList.add('border-gray-300', 'bg-white', 'hover:border-blue-500', 'hover:bg-blue-50');
                                });
                                
                                // Add selection to clicked slot
                                slotDiv.classList.remove('border-gray-300', 'bg-white', 'hover:border-blue-500', 'hover:bg-blue-50');
                                slotDiv.classList.add('border-blue-500', 'bg-blue-500', 'text-white');
                                
                                // Set hidden input value
                                document.getElementById('selectedTimeSlot').value = slot.time;
                            };
                        } else {
                            slotDiv.innerHTML = `${slot.time}<br><small>Booked</small>`;
                        }
                        
                        container.appendChild(slotDiv);
                    });
                } else {
                    container.innerHTML = '<div class="text-center text-red-500 py-4 col-span-3">Error loading time slots</div>';
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                container.innerHTML = '<div class="text-center text-red-500 py-4 col-span-3">Error loading time slots</div>';
            }
        }
        
        function openVitalModal() {
            // Show the vital record form that's already in the UI
            document.getElementById('vitalsSection').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('vitalPatientSelect').focus();
        }
        
        function openCustomVitalModal() {
            // Create modal for adding custom vital types
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Custom Vital Type</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="customVitalForm" onsubmit="submitCustomVital(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vital Name</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Heart Rate">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit</label>
                                <input type="text" name="unit" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., bpm, mmHg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Normal Range (Optional)</label>
                                <input type="text" name="normal_range" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., 60-100">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                                Cancel
                            </button>
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                Add Vital Type
                            </button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // Submit Custom Vital Type
        async function submitCustomVital(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            const vitalData = {
                name: formData.get('name'),
                unit: formData.get('unit'),
                normal_range: formData.get('normal_range') || null
            };
            
            try {
                const response = await fetch('../handlers/vitals.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_type',
                        ...vitalData
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Custom vital type added successfully!');
                    event.target.closest('.fixed').remove();
                    loadVitalSelects(); // Refresh vital types dropdown
                } else {
                    alert('Error: ' + (result.message || 'Failed to add vital type'));
                }
            } catch (error) {
                console.error('Error adding vital type:', error);
                alert('Error adding vital type: ' + error.message);
            }
        }

        // Form Submissions
        async function submitUser(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Convert FormData to JSON
            const userData = {};
            for (let [key, value] of formData.entries()) {
                userData[key] = value;
            }
            userData.action = 'create';
            
            try {
                const response = await fetch('../handlers/admin_users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('User created successfully!');
                    closeUserModal();
                    loadUsers();
                } else {
                    alert('Error: ' + (result.message || 'Error creating user'));
                }
            } catch (error) {
                console.error('Error creating user:', error);
                alert('Error creating user. Check console for details.');
            }
        }
        
        async function submitAppointment(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Convert FormData to JSON
            const appointmentData = {};
            for (let [key, value] of formData.entries()) {
                appointmentData[key] = value;
            }
            appointmentData.action = 'book';
            
            // Validate required fields
            if (!appointmentData.patient_id || !appointmentData.doctor_id || !appointmentData.appointment_date || !appointmentData.appointment_time) {
                alert('Please fill all required fields');
                return;
            }
            
            try {
                const response = await fetch('../handlers/appointments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(appointmentData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Appointment booked successfully!');
                    closeAppointmentModal();
                    loadAppointments();
                } else {
                    alert('Error: ' + (result.message || 'Error booking appointment'));
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                alert('Error booking appointment. Check console for details.');
            }
        }
        
        async function submitDoctor(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Convert FormData to JSON
            const doctorData = {};
            for (let [key, value] of formData.entries()) {
                doctorData[key] = value;
            }
            doctorData.role = 'doctor';
            doctorData.action = 'create';
            
            try {
                const response = await fetch('../handlers/admin_users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(doctorData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Doctor created successfully!');
                    closeDoctorModal();
                    loadDoctors();
                } else {
                    alert('Error: ' + (result.message || 'Error creating doctor'));
                }
            } catch (error) {
                console.error('Error creating doctor:', error);
                if (window.showError) showError('Error creating doctor');
            }
        }
        
        async function submitPatient(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Convert FormData to JSON
            const patientData = {};
            for (let [key, value] of formData.entries()) {
                patientData[key] = value;
            }
            patientData.role = 'patient';
            patientData.action = 'create';
            
            try {
                const response = await fetch('../handlers/admin_users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(patientData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Patient created successfully!');
                    closePatientModal();
                    loadPatients();
                } else {
                    alert('Error: ' + (result.message || 'Error creating patient'));
                }
            } catch (error) {
                console.error('Error creating patient:', error);
                if (window.showError) showError('Error creating patient');
            }
        }
        
        // Vital Functions
        async function addVitalRecord() {
            const patientId = document.getElementById('vitalPatientSelect').value;
            const vitalTypeId = document.getElementById('vitalTypeSelect').value;
            const value = document.getElementById('vitalValue').value;
            
            if (!patientId || !vitalTypeId || !value) {
                if (window.showError) showError('Please fill all fields');
                return;
            }
            
            try {
                const vitalData = {
                    action: 'add_vital',
                    patient_id: patientId,
                    vital_type_id: vitalTypeId,
                    value: value,
                    notes: document.getElementById('vitalNotes').value || null
                };
                
                const response = await fetch('../handlers/vitals.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(vitalData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Vital record added successfully!');
                    document.getElementById('vitalValue').value = '';
                    document.getElementById('vitalNotes').value = '';
                    loadVitals();
                } else {
                    alert('Error: ' + (result.message || 'Error adding vital record'));
                }
            } catch (error) {
                console.error('Error adding vital record:', error);
                if (window.showError) showError('Error adding vital record');
            }
        }
        
        function viewDoctorPatientVitals() {
            const doctorId = document.getElementById('doctorVitalSelect').value;
            if (!doctorId) {
                if (window.showError) showError('Please select a doctor');
                return;
            }
            if (window.showInfo) showInfo('Viewing vitals for doctor\'s patients');
        }
        
        // Action Functions
        function editUser(userId) {
            if (window.showInfo) showInfo('Edit user functionality - ID: ' + userId);
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                if (window.showInfo) showInfo('Delete user functionality - ID: ' + userId);
            }
        }
        
        function editDoctor(doctorId) {
            if (window.showInfo) showInfo('Edit doctor functionality - ID: ' + doctorId);
        }
        
        function deleteDoctor(doctorId) {
            if (confirm('Are you sure you want to delete this doctor?')) {
                if (window.showInfo) showInfo('Delete doctor functionality - ID: ' + doctorId);
            }
        }
        
        function editPatient(patientId) {
            if (window.showInfo) showInfo('Edit patient functionality - ID: ' + patientId);
        }
        
        function deletePatient(patientId) {
            if (confirm('Are you sure you want to delete this patient?')) {
                if (window.showInfo) showInfo('Delete patient functionality - ID: ' + patientId);
            }
        }
        
        function editAppointment(appointmentId) {
            if (window.showInfo) showInfo('Edit appointment functionality - ID: ' + appointmentId);
        }
        
        function cancelAppointment(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                if (window.showInfo) showInfo('Cancel appointment functionality - ID: ' + appointmentId);
            }
        }
        
        function viewPrescription(prescriptionId) {
            // Create and show prescription modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Prescription Details</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="prescriptionContent" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
                        <p class="text-gray-600 dark:text-gray-400">Loading prescription details...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Fetch prescription details with CSRF token
            const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
            fetch(`../handlers/prescriptions.php?action=details&id=${prescriptionId}&token=${csrfToken}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const prescription = result.data;
                        document.getElementById('prescriptionContent').innerHTML = `
                            <div class="space-y-6 text-left">
                                <!-- Header -->
                                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">Prescription #${prescription.prescription_number}</h4>
                                            <p class="text-gray-600 dark:text-gray-400">Date: ${formatDate(prescription.prescription_date)}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-900 dark:text-white">Dr. ${prescription.doctor_name}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">${prescription.specialization}</p>
                                            ${prescription.license_number ? `<p class="text-xs text-gray-500">License: ${prescription.license_number}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Patient Info -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Patient Information</h5>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Name:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-white">${prescription.patient_name}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Patient ID:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-white">${prescription.patient_code}</span>
                                        </div>
                                        ${prescription.blood_group ? `
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Blood Group:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-white">${prescription.blood_group}</span>
                                        </div>
                                        ` : ''}
                                        ${prescription.allergies ? `
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Allergies:</span>
                                            <span class="ml-2 font-medium text-red-600">${prescription.allergies}</span>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                                
                                <!-- Diagnosis -->
                                ${prescription.diagnosis ? `
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Diagnosis</h5>
                                    <p class="text-gray-700 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">${prescription.diagnosis}</p>
                                </div>
                                ` : ''}
                                
                                <!-- Medicines -->
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-3">Prescribed Medicines</h5>
                                    <div class="space-y-3">
                                        ${prescription.medicines.map(medicine => `
                                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h6 class="font-medium text-gray-900 dark:text-white">${medicine.medicine_name}</h6>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Qty: ${medicine.quantity || 1}</span>
                                                </div>
                                                <div class="grid grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-400">Dosage:</span>
                                                        <span class="ml-1 font-medium text-gray-900 dark:text-white">${medicine.dosage}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-400">Frequency:</span>
                                                        <span class="ml-1 font-medium text-gray-900 dark:text-white">${medicine.frequency}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-400">Duration:</span>
                                                        <span class="ml-1 font-medium text-gray-900 dark:text-white">${medicine.duration}</span>
                                                    </div>
                                                </div>
                                                ${medicine.instructions ? `
                                                <div class="mt-2">
                                                    <span class="text-gray-600 dark:text-gray-400">Instructions:</span>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">${medicine.instructions}</p>
                                                </div>
                                                ` : ''}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                
                                <!-- Notes -->
                                ${prescription.notes ? `
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Additional Notes</h5>
                                    <p class="text-gray-700 dark:text-gray-300 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">${prescription.notes}</p>
                                </div>
                                ` : ''}
                                
                                <!-- Follow-up -->
                                ${prescription.follow_up_date ? `
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Follow-up Date</h5>
                                    <p class="text-gray-700 dark:text-gray-300 font-medium">${formatDate(prescription.follow_up_date)}</p>
                                </div>
                                ` : ''}
                                
                                <!-- Actions -->
                                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button onclick="printPrescription(${prescriptionId})" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-file-pdf mr-2"></i>Save as PDF
                                    </button>
                                    <button onclick="this.closest('.fixed').remove()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                        Close
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        document.getElementById('prescriptionContent').innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-triangle text-2xl text-red-500 mb-2"></i>
                                <p class="text-red-600 dark:text-red-400">${result.message || 'Failed to load prescription details'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading prescription:', error);
                    document.getElementById('prescriptionContent').innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-500 mb-2"></i>
                            <p class="text-red-600 dark:text-red-400">Error loading prescription details</p>
                        </div>
                    `;
                });
        }
        
        function printPrescription(prescriptionId) {
            // Open prescription in new window for PDF save with CSRF token
            const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
            const printWindow = window.open(`../handlers/prescriptions.php?action=print&id=${prescriptionId}&token=${csrfToken}`, '_blank');
            if (!printWindow) {
                alert('Please allow popups to save prescription as PDF');
            }
        }
        
        function editVital(vitalId) {
            if (window.showInfo) showInfo('Edit vital functionality - ID: ' + vitalId);
        }
        
        function deleteVital(vitalId) {
            if (confirm('Are you sure you want to delete this vital record?')) {
                if (window.showInfo) showInfo('Delete vital functionality - ID: ' + vitalId);
            }
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
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        
        function formatTime(timeString) {
            if (!timeString) return 'N/A';
            const time = new Date('2000-01-01 ' + timeString);
            return time.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        }
        
        function getAppointmentStatusColor(status) {
            const colors = {
                'scheduled': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'no_show': 'bg-gray-100 text-gray-800',
                'rescheduled': 'bg-yellow-100 text-yellow-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        function getPrescriptionStatusColor(status) {
            const colors = {
                'active': 'bg-green-100 text-green-800',
                'completed': 'bg-blue-100 text-blue-800',
                'expired': 'bg-red-100 text-red-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        // Settings Functions
        async function loadSettings() {
            try {
                const response = await fetch('../handlers/website_settings.php?action=all');
                const result = await response.json();
                
                if (result.success) {
                    const settings = {};
                    result.data.forEach(setting => {
                        settings[setting.setting_key] = setting.setting_value;
                    });
                    
                    // Populate form fields
                    document.getElementById('siteName').value = settings.site_name || '';
                    document.getElementById('contactEmail').value = settings.contact_email || '';
                    document.getElementById('contactPhone').value = settings.contact_phone || '';
                    document.getElementById('address').value = settings.address || '';
                    
                    // Color inputs
                    document.getElementById('primaryColor').value = settings.primary_color || '#3B82F6';
                    document.getElementById('primaryColorText').value = settings.primary_color || '#3B82F6';
                    document.getElementById('secondaryColor').value = settings.secondary_color || '#1E40AF';
                    document.getElementById('secondaryColorText').value = settings.secondary_color || '#1E40AF';
                    document.getElementById('accentColor').value = settings.accent_color || '#10B981';
                    document.getElementById('accentColorText').value = settings.accent_color || '#10B981';
                    
                    // Checkboxes
                    document.getElementById('darkModeEnabled').checked = settings.dark_mode_enabled === 'true';
                    document.getElementById('maintenanceMode').checked = settings.maintenance_mode === 'true';
                    
                    // Preview images
                    if (settings.site_logo) {
                        document.getElementById('logoPreview').src = settings.site_logo;
                        document.getElementById('logoPreview').classList.remove('hidden');
                        document.getElementById('logoPlaceholder').classList.add('hidden');
                    }
                    
                    if (settings.favicon) {
                        document.getElementById('faviconPreview').src = settings.favicon;
                        document.getElementById('faviconPreview').classList.remove('hidden');
                        document.getElementById('faviconPlaceholder').classList.add('hidden');
                    }
                    
                    // Last updated
                    const lastUpdated = result.data.reduce((latest, setting) => {
                        const date = new Date(setting.updated_at);
                        return date > latest ? date : latest;
                    }, new Date(0));
                    
                    document.getElementById('lastUpdated').textContent = lastUpdated.toLocaleDateString();
                    
                    // Sync color inputs
                    syncColorInputs();
                }
            } catch (error) {
                console.error('Error loading settings:', error);
                if (window.showError) showError('Error loading settings');
            }
        }
        
        function syncColorInputs() {
            // Sync color picker with text input
            document.getElementById('primaryColor').addEventListener('input', function() {
                document.getElementById('primaryColorText').value = this.value;
            });
            document.getElementById('primaryColorText').addEventListener('input', function() {
                document.getElementById('primaryColor').value = this.value;
            });
            
            document.getElementById('secondaryColor').addEventListener('input', function() {
                document.getElementById('secondaryColorText').value = this.value;
            });
            document.getElementById('secondaryColorText').addEventListener('input', function() {
                document.getElementById('secondaryColor').value = this.value;
            });
            
            document.getElementById('accentColor').addEventListener('input', function() {
                document.getElementById('accentColorText').value = this.value;
            });
            document.getElementById('accentColorText').addEventListener('input', function() {
                document.getElementById('accentColor').value = this.value;
            });
        }
        
        async function saveGeneralSettings(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const settings = {};
            
            for (let [key, value] of formData.entries()) {
                settings[key] = value;
            }
            
            try {
                const response = await fetch('../handlers/website_settings.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ settings })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('General settings saved successfully!');
                } else {
                    if (window.showError) showError(result.message || 'Error saving settings');
                }
            } catch (error) {
                console.error('Error saving general settings:', error);
                if (window.showError) showError('Error saving general settings');
            }
        }
        
        async function saveThemeSettings(event) {
            event.preventDefault();
            
            const settings = {
                primary_color: document.getElementById('primaryColor').value,
                secondary_color: document.getElementById('secondaryColor').value,
                accent_color: document.getElementById('accentColor').value,
                dark_mode_enabled: document.getElementById('darkModeEnabled').checked ? 'true' : 'false'
            };
            
            try {
                const response = await fetch('../handlers/website_settings.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ settings })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('Theme settings saved successfully!');
                    // Apply colors immediately
                    applyThemeColors(settings);
                } else {
                    if (window.showError) showError(result.message || 'Error saving theme settings');
                }
            } catch (error) {
                console.error('Error saving theme settings:', error);
                if (window.showError) showError('Error saving theme settings');
            }
        }
        
        function applyThemeColors(settings) {
            // Apply CSS custom properties for dynamic theming
            const root = document.documentElement;
            root.style.setProperty('--primary-color', settings.primary_color);
            root.style.setProperty('--secondary-color', settings.secondary_color);
            root.style.setProperty('--accent-color', settings.accent_color);
        }
        
        function previewFile(type) {
            const fileInput = document.getElementById(type + 'File');
            const preview = document.getElementById(type + 'Preview');
            const placeholder = document.getElementById(type + 'Placeholder');
            
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                
                reader.readAsDataURL(fileInput.files[0]);
            }
        }
        
        async function uploadFile(settingKey, fileInputId) {
            const fileInput = document.getElementById(fileInputId);
            
            if (!fileInput.files || !fileInput.files[0]) {
                if (window.showError) showError('Please select a file first');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('setting_key', settingKey);
            
            try {
                const response = await fetch('../handlers/website_settings.php?action=upload', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('File uploaded successfully!');
                    // Update favicon if needed
                    if (settingKey === 'favicon') {
                        const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
                        link.type = 'image/x-icon';
                        link.rel = 'shortcut icon';
                        link.href = result.file_path;
                        document.getElementsByTagName('head')[0].appendChild(link);
                    }
                } else {
                    if (window.showError) showError(result.message || 'Error uploading file');
                }
            } catch (error) {
                console.error('Error uploading file:', error);
                if (window.showError) showError('Error uploading file');
            }
        }
        
        async function toggleMaintenanceMode() {
            const isEnabled = document.getElementById('maintenanceMode').checked;
            
            const settings = {
                maintenance_mode: isEnabled ? 'true' : 'false'
            };
            
            try {
                const response = await fetch('../handlers/website_settings.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ settings })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) {
                        showSuccess(isEnabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled');
                    }
                } else {
                    if (window.showError) showError(result.message || 'Error updating maintenance mode');
                    // Revert checkbox state
                    document.getElementById('maintenanceMode').checked = !isEnabled;
                }
            } catch (error) {
                console.error('Error toggling maintenance mode:', error);
                if (window.showError) showError('Error updating maintenance mode');
                // Revert checkbox state
                document.getElementById('maintenanceMode').checked = !isEnabled;
            }
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