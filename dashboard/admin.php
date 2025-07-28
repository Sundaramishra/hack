<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Continue with original code

try {
    require_once '../config/database.php';
    require_once '../classes/Auth.php';
    require_once '../classes/User.php';
    require_once '../classes/Appointment.php';
    require_once '../classes/Vitals.php';

    $auth = new Auth();
    $user_manager = new User();
    $appointment_manager = new Appointment();
    $vitals_manager = new Vitals();

    // Check authentication and role
    if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
        header('Location: ../index.php');
        exit();
    }

    $current_user = $auth->getCurrentUser();

    // Get dashboard stats with error handling
    $all_users = [];
    $doctors = [];
    $patients = [];
    $all_appointments = [];
    $upcoming_appointments = [];

    try {
        $all_users = $user_manager->getAllUsers();
        $doctors = $user_manager->getAllUsers('doctor');
        $patients = $user_manager->getAllUsers('patient');
        $all_appointments = $appointment_manager->getAllAppointments();
        $upcoming_appointments = $appointment_manager->getUpcomingAppointments('admin', null);
    } catch (Exception $e) {
        // If database operations fail, use empty arrays
        error_log("Dashboard data error: " . $e->getMessage());
    }

    $stats = [
        'total_doctors' => count($doctors),
        'total_patients' => count($patients),
        'total_appointments' => count($all_appointments),
        'upcoming_appointments' => count($upcoming_appointments)
    ];

} catch (Exception $e) {
    die("System Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    
    <!-- Debug Info (remove in production) -->
    <div class="fixed top-0 right-0 bg-green-500 text-white p-2 text-xs z-50">
        ✅ Admin Dashboard Loaded | User: <?php echo htmlspecialchars($current_user['first_name'] ?? 'Unknown'); ?>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
        <div class="flex items-center justify-center h-16 bg-primary-500">
            <i class="fas fa-hospital text-white text-2xl mr-3"></i>
            <h1 class="text-white text-xl font-bold">Hospital CRM</h1>
        </div>
        
        <nav class="mt-8">
            <a href="#dashboard" class="nav-link active flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </a>
            <a href="#users" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>
                Users Management
            </a>
            <a href="#doctors" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-md mr-3"></i>
                Doctors
            </a>
            <a href="#patients" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-injured mr-3"></i>
                Patients
            </a>
            <a href="#appointments" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>
                Appointments
            </a>
            <a href="#vitals" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>
                Vital Types
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="ml-4 text-xl font-semibold text-gray-800 dark:text-white">Admin Dashboard</h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-moon dark:hidden text-gray-600"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
                        <i class="fas fa-user-shield"></i>
                        <span><?php echo htmlspecialchars(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? '')); ?></span>
                    </div>
                    
                    <a href="../logout.php" class="flex items-center space-x-2 px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Doctors</p>
                                <p class="text-3xl font-bold text-primary-600"><?php echo $stats['total_doctors']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-md text-primary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Patients</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_patients']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-injured text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Appointments</p>
                                <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_appointments']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming</p>
                                <p class="text-3xl font-bold text-orange-600"><?php echo $stats['upcoming_appointments']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Appointments -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Appointments</h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($upcoming_appointments)): ?>
                                <p class="text-gray-500 dark:text-gray-400 text-center">No upcoming appointments</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($upcoming_appointments, 0, 5) as $appointment): ?>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?>
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo date('M j', strtotime($appointment['appointment_date'])); ?>
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <button onclick="openModal('addUserModal')" class="flex flex-col items-center p-4 bg-primary-50 dark:bg-primary-900 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-800 transition-colors">
                                    <i class="fas fa-user-plus text-primary-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Add User</span>
                                </button>
                                
                                <button onclick="showSection('users')" class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                                    <i class="fas fa-users text-green-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-green-700 dark:text-green-300">Manage Users</span>
                                </button>
                                
                                <button onclick="showSection('appointments')" class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors">
                                    <i class="fas fa-calendar-plus text-blue-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300">View Appointments</span>
                                </button>
                                
                                <button onclick="openModal('addVitalTypeModal')" class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-800 transition-colors">
                                    <i class="fas fa-heartbeat text-purple-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Add Vital Type</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Management Section -->
            <div id="users-section" class="content-section hidden">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Users</h3>
                        <button onclick="openModal('addUserModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add User
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($all_users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                                     ($user['role'] === 'doctor' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] != $current_user['id']): ?>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Other sections -->
            <div id="doctors-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Doctors Management</h2>
                    <button onclick="openModal('addDoctorModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Doctor
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Specialization</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Phone</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorsTableBody">
                                <!-- Doctors data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="patients-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Patients Management</h2>
                    <button onclick="openModal('addPatientModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Patient
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Patient ID</th>
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Age</th>
                                    <th class="px-6 py-3">Gender</th>
                                    <th class="px-6 py-3">Phone</th>
                                    <th class="px-6 py-3">Assigned Doctor</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTableBody">
                                <!-- Patients data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="appointments-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Appointments Management</h2>
                    <button onclick="openModal('addAppointmentModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Schedule Appointment
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Patient</th>
                                    <th class="px-6 py-3">Doctor</th>
                                    <th class="px-6 py-3">Date & Time</th>
                                    <th class="px-6 py-3">Type</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTableBody">
                                <!-- Appointments data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="vitals-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Vital Types Management</h2>
                    <button onclick="openModal('addVitalTypeModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Vital Type
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Vital Type</th>
                                    <th class="px-6 py-3">Unit</th>
                                    <th class="px-6 py-3">Normal Range</th>
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="vitalTypesTableBody">
                                <!-- Vital types data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New User</h3>
                    <button onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="addUserForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 mt-1">Min 8 chars, must include: A-z, 0-9, special char</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Role</option>
                            <option value="doctor">Doctor</option>
                            <option value="patient">Patient</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                        <select name="gender" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.classList.toggle('dark', savedTheme === 'dark');
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Navigation
        function showSection(sectionName) {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            document.getElementById(sectionName + '-section').classList.remove('hidden');
            
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active', 'bg-primary-50', 'dark:bg-gray-700');
            });
            
            const activeLink = document.querySelector(`[href="#${sectionName}"]`);
            if (activeLink) {
                activeLink.classList.add('active', 'bg-primary-50', 'dark:bg-gray-700');
            }
        }

        // Nav link clicks
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const href = link.getAttribute('href');
                const sectionName = href.substring(1);
                showSection(sectionName);
            });
        });

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });

        // Add user form submission
        document.getElementById('addUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/users.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('User added successfully!');
                    closeModal('addUserModal');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // User management functions
        function editUser(userId) {
            alert('Edit user functionality - ID: ' + userId);
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                alert('Delete user functionality - ID: ' + userId);
            }
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadDoctors();
            loadPatients();
            loadAppointments();
            loadVitalTypes();
        });

        // Dashboard functions
        async function loadDashboardData() {
            try {
                // Load dashboard statistics
                const stats = <?php echo json_encode($stats); ?>;
                updateDashboardStats(stats);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        async function loadDoctors() {
            try {
                const response = await fetch('../api/doctors.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('doctorsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(doctor => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${doctor.first_name} ${doctor.last_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${doctor.specialization}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${doctor.email}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${doctor.phone || 'N/A'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        ${doctor.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${doctor.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editDoctor(${doctor.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        Edit
                                    </button>
                                    <button onclick="deleteDoctor(${doctor.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }

        async function loadPatients() {
            try {
                const response = await fetch('../api/patients.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('patientsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(patient => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.patient_id}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.first_name} ${patient.last_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.age || 'N/A'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.gender || 'N/A'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.phone || 'N/A'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${patient.assigned_doctor || 'Not assigned'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editPatient(${patient.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        Edit
                                    </button>
                                    <button onclick="deletePatient(${patient.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }

        async function loadAppointments() {
            try {
                const response = await fetch('../api/appointments.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('appointmentsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(appointment => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${appointment.patient_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${appointment.doctor_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${appointment.appointment_date} ${appointment.appointment_time}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${appointment.appointment_type}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        ${appointment.status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 
                                          appointment.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                          'bg-red-100 text-red-800'}">
                                        ${appointment.status}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewAppointment(${appointment.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        View
                                    </button>
                                    <button onclick="editAppointment(${appointment.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2">
                                        Edit
                                    </button>
                                    <button onclick="deleteAppointment(${appointment.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
            }
        }

        async function loadVitalTypes() {
            try {
                const response = await fetch('../api/vital_types.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('vitalTypesTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(vitalType => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${vitalType.name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${vitalType.unit}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${vitalType.normal_range_min} - ${vitalType.normal_range_max}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${vitalType.description || 'N/A'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editVitalType(${vitalType.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        Edit
                                    </button>
                                    <button onclick="deleteVitalType(${vitalType.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading vital types:', error);
            }
        }

        function updateDashboardStats(stats) {
            const totalUsersEl = document.getElementById('totalUsers');
            const totalDoctorsEl = document.getElementById('totalDoctors');
            const totalPatientsEl = document.getElementById('totalPatients');
            const totalAppointmentsEl = document.getElementById('totalAppointments');
            
            if (totalUsersEl) totalUsersEl.textContent = stats.total_users;
            if (totalDoctorsEl) totalDoctorsEl.textContent = stats.total_doctors;
            if (totalPatientsEl) totalPatientsEl.textContent = stats.total_patients;
            if (totalAppointmentsEl) totalAppointmentsEl.textContent = stats.total_appointments;
        }

        console.log('Admin Dashboard loaded successfully!');
    </script>
</body>
</html>