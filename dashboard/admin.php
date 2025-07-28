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
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                        <input type="text" name="firstName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="lastName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="patient">Patient</option>
                        </select>
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
                        <select name="patientId" id="appointmentPatientSelect" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Loading patients...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Doctor</label>
                        <select name="doctorId" id="appointmentDoctorSelect" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Loading doctors...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                        <input type="date" name="appointmentDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                        <input type="time" name="appointmentTime" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
                    await loadAppointmentSelects();
                    break;
                case 'prescriptions':
                    await loadPrescriptions();
                    break;
                case 'vitals':
                    await loadVitals();
                    await loadVitalSelects();
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
                    const patientSelect = document.getElementById('appointmentPatientSelect');
                    patientSelect.innerHTML = '<option value="">Select Patient</option>';
                    patientsResult.data.forEach(patient => {
                        patientSelect.innerHTML += `<option value="${patient.id}">${patient.first_name} ${patient.last_name}</option>`;
                    });
                }
                
                // Load doctors for appointment modal
                const doctorsResponse = await fetch('../handlers/admin_users.php?action=list&role=doctor');
                const doctorsResult = await doctorsResponse.json();
                
                if (doctorsResult.success) {
                    const doctorSelect = document.getElementById('appointmentDoctorSelect');
                    doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                    doctorsResult.data.forEach(doctor => {
                        doctorSelect.innerHTML += `<option value="${doctor.id}">Dr. ${doctor.first_name} ${doctor.last_name}</option>`;
                    });
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
                const vitalTypesResponse = await fetch('../handlers/vitals.php?action=vital_types');
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
            if (window.showInfo) showInfo('Doctor creation modal - Add specialization, license, etc.');
        }
        
        function openPatientModal() {
            if (window.showInfo) showInfo('Patient creation modal - Add blood group, medical history, etc.');
        }
        
        function openAppointmentModal() {
            document.getElementById('appointmentModal').classList.remove('hidden');
        }
        
        function closeAppointmentModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
            document.getElementById('appointmentForm').reset();
        }
        
        function openVitalModal() {
            if (window.showInfo) showInfo('Add vital record functionality');
        }
        
        function openCustomVitalModal() {
            if (window.showInfo) showInfo('Manage custom vital types functionality');
        }
        
        // Form Submissions
        async function submitUser(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            try {
                const response = await fetch('../handlers/admin_users.php?action=create', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('User created successfully!');
                    closeUserModal();
                    loadUsers();
                } else {
                    if (window.showError) showError(result.message || 'Error creating user');
                }
            } catch (error) {
                console.error('Error creating user:', error);
                if (window.showError) showError('Error creating user');
            }
        }
        
        async function submitAppointment(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            try {
                const response = await fetch('../handlers/appointments.php?action=create', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('Appointment booked successfully!');
                    closeAppointmentModal();
                    loadAppointments();
                } else {
                    if (window.showError) showError(result.message || 'Error booking appointment');
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                if (window.showError) showError('Error booking appointment');
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
                const formData = new FormData();
                formData.append('patient_id', patientId);
                formData.append('vital_type_id', vitalTypeId);
                formData.append('value', value);
                
                const response = await fetch('../handlers/vitals.php?action=add_vital', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    if (window.showSuccess) showSuccess('Vital record added successfully!');
                    document.getElementById('vitalValue').value = '';
                    loadVitals();
                } else {
                    if (window.showError) showError(result.message || 'Error adding vital record');
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
            if (window.showInfo) showInfo('View prescription functionality - ID: ' + prescriptionId);
        }
        
        function printPrescription(prescriptionId) {
            if (window.showInfo) showInfo('Print prescription functionality - ID: ' + prescriptionId);
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