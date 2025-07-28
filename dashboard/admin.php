<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole('admin');

require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();

$user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $user['theme'] === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hospital text-white"></i>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hospital CRM</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Admin Panel</p>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <nav class="mt-6">
            <a href="#" onclick="showSection('dashboard')" class="nav-link active flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </a>
            <a href="#" onclick="showSection('users')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>Users
            </a>
            <a href="#" onclick="showSection('doctors')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-md mr-3"></i>Doctors
            </a>
            <a href="#" onclick="showSection('patients')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-injured mr-3"></i>Patients
            </a>
            <a href="#" onclick="showSection('appointments')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>Appointments
            </a>
            <a href="#" onclick="showSection('book-appointment')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-plus mr-3"></i>Book Appointment
            </a>
            <a href="#" onclick="showSection('vitals')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>Vitals
            </a>
            <a href="#" onclick="showSection('prescriptions')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>Prescriptions
            </a>
            <a href="#" onclick="showSection('custom-vitals')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-plus-circle mr-3"></i>Custom Vitals
            </a>
            <a href="#" onclick="showSection('profile')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user-circle mr-3"></i>My Profile
            </a>
            <a href="#" onclick="showSection('website-settings')" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-cog mr-3"></i>Website Settings
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 id="pageTitle" class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-moon dark:hidden text-gray-600"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                    
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                            </div>
                            <span class="hidden md:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
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
            </div>
        </header>
        
        <!-- Content Area -->
        <main class="p-6">
            <!-- Dashboard Section -->
            <div id="dashboardSection" class="section">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Stats Cards -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p id="totalUsers" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
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
                                <p id="totalDoctors" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
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
                                <p id="totalPatients" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
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
                                <p id="totalAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Appointments -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Appointments</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentAppointments" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Users Section -->
            <div id="usersSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Users Management</h2>
                    <button onclick="openUserModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Other sections will be added here -->
            <div id="doctorsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Doctors Management</h2>
                <!-- Doctors content here -->
            </div>
            
            <div id="patientsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Patients Management</h2>
                <!-- Patients content here -->
            </div>
            
                    <div id="appointmentsSection" class="section hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Appointments Management</h2>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Appointments will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="book-appointmentSection" class="section hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Book New Appointment</h2>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form id="appointmentForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="patientSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user mr-2"></i>Select Patient
                            </label>
                            <select id="patientSelect" name="patient_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Choose a patient...</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="doctorSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user-md mr-2"></i>Select Doctor
                            </label>
                            <select id="doctorSelect" name="doctor_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" onchange="loadTimeSlots()">
                                <option value="">Choose a doctor...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="appointmentDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-calendar mr-2"></i>Appointment Date
                            </label>
                            <input type="date" id="appointmentDate" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" onchange="loadTimeSlots()">
                        </div>
                        
                        <div>
                            <label for="appointmentType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-clipboard mr-2"></i>Appointment Type
                            </label>
                            <select id="appointmentType" name="appointment_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="consultation">Consultation</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="emergency">Emergency</option>
                                <option value="routine_checkup">Routine Checkup</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            <i class="fas fa-clock mr-2"></i>Available Time Slots
                        </label>
                        <div id="timeSlotsContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                            <p class="col-span-full text-gray-500 dark:text-gray-400 text-center py-4">Select a doctor and date to view available slots</p>
                        </div>
                        <input type="hidden" id="selectedTimeSlot" name="appointment_time">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-comment mr-2"></i>Reason for Visit
                            </label>
                            <input type="text" id="reason" name="reason" placeholder="e.g., Regular checkup, Follow-up" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-hourglass-half mr-2"></i>Duration (minutes)
                            </label>
                            <select id="duration" name="duration" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="symptoms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-notes-medical mr-2"></i>Symptoms (Optional)
                        </label>
                        <textarea id="symptoms" name="symptoms" rows="3" placeholder="Describe any symptoms or additional information..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="resetAppointmentForm()" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
            
            <div id="vitalsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Vitals Management</h2>
                <!-- Vitals content here -->
            </div>
            
            <!-- Prescriptions Section -->
            <div id="prescriptionsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Prescriptions Management</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prescription #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diagnosis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adminPrescriptionsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
                    <div id="custom-vitalsSection" class="section hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Custom Vitals Management</h2>
            <!-- Custom vitals content here -->
        </div>

        <div id="profileSection" class="section hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Profile</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Info Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <!-- Profile Header -->
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-t-lg p-6">
                            <div class="flex items-center">
                                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <span class="text-white text-2xl font-bold" id="profileInitials">A</span>
                                </div>
                                <div class="ml-6 text-white">
                                    <h3 class="text-2xl font-semibold" id="profileName">Loading...</h3>
                                    <p class="text-blue-100" id="profileRole">Administrator</p>
                                    <p class="text-blue-100 text-sm" id="profileEmail">Loading...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Form -->
                        <div class="p-6">
                            <form id="profileForm" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-user mr-2"></i>First Name
                                        </label>
                                        <input type="text" id="firstName" name="first_name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-user mr-2"></i>Last Name
                                        </label>
                                        <input type="text" id="lastName" name="last_name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-envelope mr-2"></i>Email
                                        </label>
                                        <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-phone mr-2"></i>Phone
                                        </label>
                                        <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-calendar mr-2"></i>Date of Birth
                                        </label>
                                        <input type="date" id="dateOfBirth" name="date_of_birth" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-venus-mars mr-2"></i>Gender
                                        </label>
                                        <select id="gender" name="gender" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>Address
                                    </label>
                                    <textarea id="address" name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"></textarea>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-save mr-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mt-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-lock mr-2"></i>Change Password
                            </h3>
                            
                            <form id="passwordForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                                    <input type="password" id="currentPassword" name="current_password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                        <input type="password" id="newPassword" name="new_password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                        <input type="password" id="confirmPassword" name="confirm_password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-key mr-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-chart-bar mr-2"></i>System Overview
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Users</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="totalUsers">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Doctors</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="totalDoctors">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Patients</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="totalPatients">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Appointments</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="totalAppointments">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-info-circle mr-2"></i>Account Info
                        </h3>
                        
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Username</span>
                                <p class="font-medium text-gray-900 dark:text-white" id="profileUsername">-</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Member Since</span>
                                <p class="font-medium text-gray-900 dark:text-white" id="memberSince">-</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Last Updated</span>
                                <p class="font-medium text-gray-900 dark:text-white" id="lastUpdated">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Theme Settings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-palette mr-2"></i>Theme Settings
                        </h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="light" class="mr-3">
                                <i class="fas fa-sun mr-2"></i>Light Theme
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="dark" class="mr-3">
                                <i class="fas fa-moon mr-2"></i>Dark Theme
                            </label>
                        </div>
                        
                        <button onclick="updateTheme()" class="mt-4 w-full px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-check mr-2"></i>Apply Theme
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="website-settingsSection" class="section hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Website Settings</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- General Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-globe mr-2"></i>General Settings
                    </h3>
                    
                    <form id="generalSettingsForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-tag mr-2"></i>Website Name
                            </label>
                            <input type="text" id="siteName" name="site_name" placeholder="Hospital CRM" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-image mr-2"></i>Website Logo
                            </label>
                            <div class="flex items-center space-x-4">
                                <div id="logoPreview" class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <input type="file" id="logoUpload" accept="image/*" class="hidden">
                                    <button type="button" onclick="document.getElementById('logoUpload').click()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-upload mr-2"></i>Upload Logo
                                    </button>
                                    <button type="button" onclick="removeLogo()" class="ml-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash mr-2"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-star mr-2"></i>Favicon
                            </label>
                            <div class="flex items-center space-x-4">
                                <div id="faviconPreview" class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                    <i class="fas fa-star text-gray-400 text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <input type="file" id="faviconUpload" accept="image/*,.ico" class="hidden">
                                    <button type="button" onclick="document.getElementById('faviconUpload').click()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-upload mr-2"></i>Upload Favicon
                                    </button>
                                    <button type="button" onclick="removeFavicon()" class="ml-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash mr-2"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Color Customization -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-palette mr-2"></i>Color Customization
                    </h3>
                    
                    <form id="colorSettingsForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Primary Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="primaryColor" name="primary_color" value="#3B82F6" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="primaryColorText" value="#3B82F6" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Secondary Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="secondaryColor" name="secondary_color" value="#6366F1" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="secondaryColorText" value="#6366F1" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Success Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="accentColor" name="accent_color" value="#10B981" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="accentColorText" value="#10B981" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Danger Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="dangerColor" name="danger_color" value="#EF4444" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="dangerColorText" value="#EF4444" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warning Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="warningColor" name="warning_color" value="#F59E0B" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="warningColorText" value="#F59E0B" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Info Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="infoColor" name="info_color" value="#06B6D4" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" id="infoColorText" value="#06B6D4" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <button type="button" onclick="resetColors()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-undo mr-2"></i>Reset to Default
                            </button>
                            <button type="submit" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>Save Colors
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Advanced Settings -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-code mr-2"></i>Advanced Settings
                    </h3>
                    
                    <form id="advancedSettingsForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-css3-alt mr-2"></i>Custom CSS
                            </label>
                            <textarea id="customCSS" name="custom_css" rows="8" placeholder="/* Add your custom CSS here */" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm resize-none"></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Add custom CSS to override default styles. Use !important for better specificity.</p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>Save Advanced Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Preview Section -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-eye mr-2"></i>Live Preview
                    </h3>
                    
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                        <div class="flex items-center justify-between mb-4 p-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg text-white">
                            <div class="flex items-center">
                                <div id="previewLogo" class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <span id="previewSiteName" class="text-xl font-semibold">Hospital CRM</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors">Primary</button>
                                <button class="px-4 py-2 bg-green-500 hover:bg-green-600 rounded-lg transition-colors">Success</button>
                                <button class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg transition-colors">Danger</button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sample Card</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">This is how your content will look with the current color scheme.</p>
                            </div>
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Another Card</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Colors will update in real-time as you change them.</p>
                            </div>
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Third Card</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Preview your changes before saving.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </main>
    </div>
    
    <!-- User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="userModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Add User</h3>
                    <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="userForm" class="space-y-4">
                    <input type="hidden" id="userId" name="userId">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input type="text" id="firstName" name="firstName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                        <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="patient">Patient</option>
                        </select>
                    </div>
                    
                    <div id="passwordField">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Min 8 chars, uppercase, lowercase, number, special char</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeUserModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Global variables
        let currentSection = 'dashboard';
        
        // Theme Management
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Sidebar Management
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // User Menu
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Section Management
        let currentSection = 'dashboard';
        
        function showSection(sectionName) {
            currentSection = sectionName;
            
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionName + 'Section').classList.remove('hidden');
            
            // Update nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active', 'bg-blue-500', 'text-white');
                link.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            event.target.classList.add('active', 'bg-blue-500', 'text-white');
            event.target.classList.remove('text-gray-700', 'dark:text-gray-300');
            
            // Load section-specific data
            if (sectionName === 'appointments') {
                loadAppointments();
            } else if (sectionName === 'book-appointment') {
                loadPatientsAndDoctors();
            } else if (sectionName === 'profile') {
                loadProfile();
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
                'custom-vitals': 'Custom Vitals Management'
            };
            
            document.getElementById('pageTitle').textContent = titles[sectionName] || 'Dashboard';
            currentSection = sectionName;
            
            // Load section data
            loadSectionData(sectionName);
            
            // Close sidebar on mobile
            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        }
        
        // Load section data
        function loadSectionData(section) {
            switch(section) {
                case 'dashboard':
                    loadDashboardData();
                    break;
                case 'users':
                    loadUsers();
                    break;
                case 'prescriptions':
                    loadAdminPrescriptions();
                    break;
                // Add other cases as needed
            }
        }
        
        // Dashboard data loading
        async function loadDashboardData() {
            try {
                // Load stats
                const statsResponse = await fetch('../handlers/admin_stats.php');
                const stats = await statsResponse.json();
                
                if (stats.success) {
                    document.getElementById('totalUsers').textContent = stats.data.total_users;
                    document.getElementById('totalDoctors').textContent = stats.data.total_doctors;
                    document.getElementById('totalPatients').textContent = stats.data.total_patients;
                    document.getElementById('totalAppointments').textContent = stats.data.total_appointments;
                }
                
                // Load recent appointments
                const appointmentsResponse = await fetch('../handlers/admin_appointments.php?action=recent');
                const appointments = await appointmentsResponse.json();
                
                if (appointments.success) {
                    const tbody = document.getElementById('recentAppointments');
                    tbody.innerHTML = appointments.data.map(apt => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.patient_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.doctor_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_date} ${apt.appointment_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(apt.status)}">${apt.status}</span>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }
        
        // Users management
        async function loadUsers() {
            try {
                const response = await fetch('../handlers/admin_users.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('usersTable');
                    tbody.innerHTML = result.data.map(user => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${user.first_name} ${user.last_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${user.email}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getRoleColor(user.role)}">${user.role}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${user.is_active ? 'Active' : 'Inactive'}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }
        
        // User modal functions
        function openUserModal(userId = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('userModalTitle');
            const form = document.getElementById('userForm');
            
            if (userId) {
                title.textContent = 'Edit User';
                // Load user data for editing
                loadUserData(userId);
            } else {
                title.textContent = 'Add User';
                form.reset();
                document.getElementById('passwordField').style.display = 'block';
                document.getElementById('password').required = true;
            }
            
            modal.classList.remove('hidden');
        }
        
        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
        
        // Admin prescriptions management
        async function loadAdminPrescriptions() {
            try {
                const response = await fetch('../handlers/prescriptions.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('adminPrescriptionsTable');
                    tbody.innerHTML = result.data.map(prescription => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_number}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.patient_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.doctor_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_date}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.diagnosis || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${prescription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${prescription.status}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="viewAdminPrescription(${prescription.id})" class="text-blue-600 hover:text-blue-800" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="printPrescription(${prescription.id})" class="text-green-600 hover:text-green-800" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button onclick="deletePrescription(${prescription.id})" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
            }
        }
        
        async function viewAdminPrescription(prescriptionId) {
            try {
                const response = await fetch(`../handlers/prescriptions.php?action=details&id=${prescriptionId}`);
                const result = await response.json();
                
                if (result.success) {
                    const prescription = result.data;
                    let medicinesHtml = '';
                    
                    if (prescription.medicines && prescription.medicines.length > 0) {
                        medicinesHtml = prescription.medicines.map(medicine => `
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-3 mb-3 last:border-b-0">
                                <div class="font-medium text-gray-900 dark:text-white">${medicine.medicine_name}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <span class="inline-block mr-4"><strong>Dosage:</strong> ${medicine.dosage}</span>
                                    <span class="inline-block mr-4"><strong>Frequency:</strong> ${medicine.frequency}</span>
                                    <span class="inline-block mr-4"><strong>Duration:</strong> ${medicine.duration}</span>
                                    <span class="inline-block"><strong>Quantity:</strong> ${medicine.quantity}</span>
                                </div>
                                ${medicine.instructions ? `<div class="text-sm text-gray-500 dark:text-gray-400 mt-1"><strong>Instructions:</strong> ${medicine.instructions}</div>` : ''}
                            </div>
                        `).join('');
                    }
                    
                    const modalHtml = `
                        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Prescription Details - ${prescription.prescription_number}</h3>
                                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient Information</label>
                                                <div class="text-gray-900 dark:text-white">
                                                    <div><strong>Name:</strong> ${prescription.patient_name}</div>
                                                    <div><strong>Code:</strong> ${prescription.patient_code}</div>
                                                    <div><strong>Blood Group:</strong> ${prescription.blood_group || 'N/A'}</div>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Doctor Information</label>
                                                <div class="text-gray-900 dark:text-white">
                                                    <div><strong>Name:</strong> ${prescription.doctor_name}</div>
                                                    <div><strong>Specialization:</strong> ${prescription.specialization}</div>
                                                    <div><strong>License:</strong> ${prescription.license_number}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prescription Details</label>
                                                <div class="text-gray-900 dark:text-white">
                                                    <div><strong>Number:</strong> ${prescription.prescription_number}</div>
                                                    <div><strong>Date:</strong> ${prescription.prescription_date}</div>
                                                    <div><strong>Status:</strong> ${prescription.status}</div>
                                                    ${prescription.follow_up_date ? `<div><strong>Follow-up:</strong> ${prescription.follow_up_date}</div>` : ''}
                                                </div>
                                            </div>
                                            
                                            ${prescription.allergies ? `
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allergies</label>
                                                    <div class="text-red-600 dark:text-red-400">${prescription.allergies}</div>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Diagnosis</label>
                                            <div class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">${prescription.diagnosis || 'N/A'}</div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prescribed Medicines</label>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                ${medicinesHtml || '<p class="text-gray-500 dark:text-gray-400">No medicines prescribed</p>'}
                                            </div>
                                        </div>
                                        
                                        ${prescription.notes ? `
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Notes</label>
                                                <div class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">${prescription.notes}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                    
                                    <div class="flex justify-end mt-6 space-x-3">
                                        <button onclick="printPrescription(${prescription.id})" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200">
                                            <i class="fas fa-print mr-1"></i>Print
                                        </button>
                                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                }
            } catch (error) {
                console.error('Error loading prescription details:', error);
                                    showError('Error loading prescription details');
            }
        }
        
        function printPrescription(prescriptionId) {
                            showInfo('Print functionality will be implemented');
        }
        
        function deletePrescription(prescriptionId) {
            if (confirm('Are you sure you want to delete this prescription?')) {
                showWarning('Delete functionality will be implemented');
            }
        }
        
        // Utility functions
        function getStatusColor(status) {
            const colors = {
                'scheduled': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'no_show': 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        function getRoleColor(role) {
            const colors = {
                'admin': 'bg-purple-100 text-purple-800',
                'doctor': 'bg-blue-100 text-blue-800',
                'patient': 'bg-green-100 text-green-800'
            };
            return colors[role] || 'bg-gray-100 text-gray-800';
        }
        
        // Appointment booking functions
        async function loadPatientsAndDoctors() {
            try {
                // Load patients
                const patientsResponse = await fetch('../handlers/admin_users.php?action=list&role=patient');
                const patientsResult = await patientsResponse.json();
                
                if (patientsResult.success) {
                    const patientSelect = document.getElementById('patientSelect');
                    patientSelect.innerHTML = '<option value="">Choose a patient...</option>';
                    
                    patientsResult.data.forEach(patient => {
                        const option = document.createElement('option');
                        option.value = patient.patient_id;
                        option.textContent = `${patient.first_name} ${patient.last_name} (${patient.patient_code})`;
                        patientSelect.appendChild(option);
                    });
                }
                
                // Load doctors
                const doctorsResponse = await fetch('../handlers/get_doctors.php');
                const doctorsResult = await doctorsResponse.json();
                
                if (doctorsResult.success) {
                    const doctorSelect = document.getElementById('doctorSelect');
                    doctorSelect.innerHTML = '<option value="">Choose a doctor...</option>';
                    
                    doctorsResult.data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.doctor_id;
                        option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}`;
                        doctorSelect.appendChild(option);
                    });
                }
                
            } catch (error) {
                console.error('Error loading patients and doctors:', error);
                showError('Error loading patients and doctors');
            }
        }

        async function loadTimeSlots() {
            const doctorId = document.getElementById('doctorSelect').value;
            const date = document.getElementById('appointmentDate').value;
            const container = document.getElementById('timeSlotsContainer');
            
            if (!doctorId || !date) {
                container.innerHTML = '<p class="col-span-full text-gray-500 dark:text-gray-400 text-center py-4">Select a doctor and date to view available slots</p>';
                return;
            }
            
            try {
                const response = await fetch(`../handlers/appointments.php?action=available_slots&doctor_id=${doctorId}&date=${date}`);
                const result = await response.json();
                
                if (result.success) {
                    if (result.data.length === 0) {
                        container.innerHTML = '<p class="col-span-full text-gray-500 dark:text-gray-400 text-center py-4">No available slots for this date</p>';
                        return;
                    }
                    
                    container.innerHTML = '';
                    result.data.forEach(slot => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `p-2 text-sm rounded-lg border transition-colors duration-200 ${
                            slot.available 
                                ? 'border-blue-300 text-blue-700 hover:bg-blue-50 dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-900/20' 
                                : 'border-gray-300 text-gray-400 cursor-not-allowed dark:border-gray-600 dark:text-gray-500'
                        }`;
                        button.textContent = slot.display_time;
                        button.disabled = !slot.available;
                        
                        if (slot.available) {
                            button.onclick = () => selectTimeSlot(slot.time, button);
                        }
                        
                        container.appendChild(button);
                    });
                } else {
                    showError(result.message || 'Error loading time slots');
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                showError('Error loading time slots');
            }
        }

        function selectTimeSlot(time, button) {
            // Remove selection from other buttons
            document.querySelectorAll('#timeSlotsContainer button').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'dark:bg-blue-600');
                btn.classList.add('border-blue-300', 'text-blue-700', 'hover:bg-blue-50', 'dark:border-blue-600', 'dark:text-blue-300', 'dark:hover:bg-blue-900/20');
            });
            
            // Add selection to clicked button
            button.classList.remove('border-blue-300', 'text-blue-700', 'hover:bg-blue-50', 'dark:border-blue-600', 'dark:text-blue-300', 'dark:hover:bg-blue-900/20');
            button.classList.add('bg-blue-500', 'text-white', 'dark:bg-blue-600');
            
            document.getElementById('selectedTimeSlot').value = time;
        }

        function resetAppointmentForm() {
            document.getElementById('appointmentForm').reset();
            document.getElementById('selectedTimeSlot').value = '';
            document.getElementById('timeSlotsContainer').innerHTML = '<p class="col-span-full text-gray-500 dark:text-gray-400 text-center py-4">Select a doctor and date to view available slots</p>';
        }

        // Handle appointment form submission
        document.addEventListener('DOMContentLoaded', function() {
            const appointmentForm = document.getElementById('appointmentForm');
            if (appointmentForm) {
                appointmentForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const appointmentData = {
                        action: 'book',
                        patient_id: formData.get('patient_id'),
                        doctor_id: formData.get('doctor_id'),
                        appointment_date: formData.get('appointment_date'),
                        appointment_time: formData.get('appointment_time'),
                        appointment_type: formData.get('appointment_type'),
                        duration: formData.get('duration'),
                        reason: formData.get('reason'),
                        symptoms: formData.get('symptoms')
                    };
                    
                    if (!appointmentData.appointment_time) {
                        showWarning('Please select a time slot');
                        return;
                    }
                    
                    try {
                        const response = await fetch('../handlers/appointments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(appointmentData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showSuccess('Appointment booked successfully!');
                            resetAppointmentForm();
                            // Reload appointments if on appointments section
                            if (currentSection === 'appointments') {
                                loadAppointments();
                            }
                        } else {
                            showError(result.message || 'Error booking appointment');
                        }
                    } catch (error) {
                        console.error('Error booking appointment:', error);
                        showError('Error booking appointment');
                    }
                });
            }
        });

        async function loadAppointments() {
            try {
                const response = await fetch('../handlers/appointments.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('appointmentsTableBody');
                    tbody.innerHTML = '';
                    
                    result.data.forEach(appointment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${appointment.patient_name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${appointment.patient_code}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${appointment.doctor_name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${appointment.specialization}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">${appointment.appointment_date}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${appointment.appointment_time}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(appointment.status)}">
                                    ${appointment.status}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editAppointment(${appointment.id})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="cancelAppointment(${appointment.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    showError(result.message || 'Error loading appointments');
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
                showError('Error loading appointments');
            }
        }

        function editAppointment(appointmentId) {
            showInfo('Edit appointment functionality will be implemented');
        }

        async function cancelAppointment(appointmentId) {
            if (!confirm('Are you sure you want to cancel this appointment?')) {
                return;
            }
            
            try {
                const response = await fetch(`../handlers/appointments.php?id=${appointmentId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Appointment cancelled successfully');
                    loadAppointments();
                } else {
                    showError(result.message || 'Error cancelling appointment');
                }
            } catch (error) {
                console.error('Error cancelling appointment:', error);
                showError('Error cancelling appointment');
            }
        }

        // Profile Management Functions
        async function loadProfile() {
            try {
                const response = await fetch('../handlers/profile.php?action=profile');
                const result = await response.json();
                
                if (result.success) {
                    const profile = result.data;
                    
                    // Update profile header
                    document.getElementById('profileName').textContent = `${profile.first_name} ${profile.last_name}`;
                    document.getElementById('profileEmail').textContent = profile.email;
                    document.getElementById('profileInitials').textContent = (profile.first_name.charAt(0) + profile.last_name.charAt(0)).toUpperCase();
                    
                    // Fill form fields
                    document.getElementById('firstName').value = profile.first_name || '';
                    document.getElementById('lastName').value = profile.last_name || '';
                    document.getElementById('email').value = profile.email || '';
                    document.getElementById('phone').value = profile.phone || '';
                    document.getElementById('dateOfBirth').value = profile.date_of_birth || '';
                    document.getElementById('gender').value = profile.gender || '';
                    document.getElementById('address').value = profile.address || '';
                    
                    // Update account info
                    document.getElementById('profileUsername').textContent = profile.username;
                    document.getElementById('memberSince').textContent = new Date(profile.created_at).toLocaleDateString();
                    document.getElementById('lastUpdated').textContent = new Date(profile.updated_at).toLocaleDateString();
                    
                    // Update stats
                    document.getElementById('totalUsers').textContent = profile.total_users || '0';
                    document.getElementById('totalDoctors').textContent = profile.total_doctors || '0';
                    document.getElementById('totalPatients').textContent = profile.total_patients || '0';
                    document.getElementById('totalAppointments').textContent = profile.total_appointments || '0';
                    
                    // Set theme radio
                    const themeRadios = document.querySelectorAll('input[name="theme"]');
                    themeRadios.forEach(radio => {
                        if (radio.value === profile.theme_preference) {
                            radio.checked = true;
                        }
                    });
                    
                } else {
                    showError(result.message || 'Error loading profile');
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                showError('Error loading profile');
            }
        }

        // Handle profile form submission
        document.addEventListener('DOMContentLoaded', function() {
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const profileData = {
                        action: 'update_basic',
                        first_name: formData.get('first_name'),
                        last_name: formData.get('last_name'),
                        email: formData.get('email'),
                        phone: formData.get('phone'),
                        date_of_birth: formData.get('date_of_birth'),
                        gender: formData.get('gender'),
                        address: formData.get('address')
                    };
                    
                    try {
                        const response = await fetch('../handlers/profile.php', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(profileData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showSuccess('Profile updated successfully!');
                            loadProfile(); // Reload profile data
                        } else {
                            showError(result.message || 'Error updating profile');
                        }
                    } catch (error) {
                        console.error('Error updating profile:', error);
                        showError('Error updating profile');
                    }
                });
            }

            // Handle password change form
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const passwordData = {
                        action: 'update_password',
                        current_password: formData.get('current_password'),
                        new_password: formData.get('new_password'),
                        confirm_password: formData.get('confirm_password')
                    };
                    
                    try {
                        const response = await fetch('../handlers/profile.php', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(passwordData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showSuccess('Password changed successfully!');
                            passwordForm.reset();
                        } else {
                            showError(result.message || 'Error changing password');
                        }
                    } catch (error) {
                        console.error('Error changing password:', error);
                        showError('Error changing password');
                    }
                });
            }
        });

        function updateTheme() {
            const selectedTheme = document.querySelector('input[name="theme"]:checked');
            if (!selectedTheme) {
                showWarning('Please select a theme');
                return;
            }
            
            const themeData = {
                action: 'update_theme',
                theme: selectedTheme.value
            };
            
            fetch('../handlers/profile.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(themeData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showSuccess('Theme updated successfully!');
                    // Apply theme immediately
                    if (selectedTheme.value === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    localStorage.setItem('theme', selectedTheme.value);
                } else {
                    showError(result.message || 'Error updating theme');
                }
            })
            .catch(error => {
                console.error('Error updating theme:', error);
                showError('Error updating theme');
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#userMenu') && !event.target.closest('button[onclick="toggleUserMenu()"]')) {
                    document.getElementById('userMenu').classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>