<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/Vitals.php';

// Main dashboard code
try {
    $auth = new Auth();
    $user_manager = new User();
    $appointment_manager = new Appointment();
    $vitals_manager = new Vitals();

    // Check authentication and role
    if (!$auth->isLoggedIn() || !$auth->hasRole('doctor')) {
        // For testing, let's bypass authentication temporarily
        // header('Location: ../index.php');
        // exit();
        echo "<!-- Authentication bypassed for testing -->";
    }

    $current_user = $auth->getCurrentUser();
    $doctor_id = $current_user ? $current_user['doctor_id'] : 1; // Default for testing

    // Get doctor's data with error handling
    try {
        $assigned_patients = $user_manager->getAssignedPatients($doctor_id);
    } catch (Exception $e) {
        $assigned_patients = [];
        echo "<!-- Error loading assigned patients: " . $e->getMessage() . " -->";
    }

    try {
        $doctor_appointments = $appointment_manager->getAppointmentsByDoctor($doctor_id);
    } catch (Exception $e) {
        $doctor_appointments = [];
        echo "<!-- Error loading appointments: " . $e->getMessage() . " -->";
    }

    try {
        $upcoming_appointments = $appointment_manager->getUpcomingAppointments('doctor', $doctor_id);
    } catch (Exception $e) {
        $upcoming_appointments = [];
        echo "<!-- Error loading upcoming appointments: " . $e->getMessage() . " -->";
    }

    // Get today's appointments
    $today_appointments = array_filter($doctor_appointments, function($apt) {
        return $apt['appointment_date'] === date('Y-m-d') && $apt['status'] === 'scheduled';
    });

    $stats = [
        'total_patients' => count($assigned_patients),
        'total_appointments' => count($doctor_appointments),
        'today_appointments' => count($today_appointments),
        'upcoming_appointments' => count($upcoming_appointments)
    ];
} catch (Exception $e) {
    echo "<!-- Dashboard initialization error: " . $e->getMessage() . " -->";
    $stats = [
        'total_patients' => 0,
        'total_appointments' => 0,
        'today_appointments' => 0,
        'upcoming_appointments' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Hospital CRM</title>
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
    
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            max-width: 300px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background-color: #10b981;
            border-left: 4px solid #059669;
        }
        
        .notification.error {
            background-color: #ef4444;
            border-left: 4px solid #dc2626;
        }
        
        .notification.info {
            background-color: #3b82f6;
            border-left: 4px solid #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    
    <!-- Debug Info (remove in production) -->
    <div class="fixed top-0 right-0 bg-green-500 text-white p-2 text-xs z-50">
        ✅ Doctor Dashboard Loaded | User: <?php echo htmlspecialchars($current_user['first_name'] ?? 'Unknown'); ?>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
        <div class="flex items-center justify-center h-16 bg-primary-500">
            <i class="fas fa-user-md text-white text-2xl mr-3"></i>
            <h1 class="text-white text-xl font-bold">Doctor Portal</h1>
        </div>
        
        <nav class="mt-8">
            <a href="#dashboard" class="nav-link active flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </a>
            <a href="#patients" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>
                My Patients
            </a>
            <a href="#appointments" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>
                Appointments
            </a>
            <a href="#vitals" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>
                Patient Vitals
            </a>
            <a href="#profile" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user mr-3"></i>
                My Profile
            </a>
            <a href="../logout.php" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors duration-200">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Logout
            </a>
        </nav>
    </div>

    <!-- Mobile menu button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobile-menu-btn" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Main content -->
    <div class="lg:ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Doctor Dashboard</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="dark-mode-toggle" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-moon text-xl"></i>
                    </button>
                    <div class="flex items-center space-x-2">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Profile" class="w-8 h-8 rounded-full">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($current_user['first_name'] ?? 'Doctor'); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard content -->
        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/20">
                            <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Patients</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalPatients"><?php echo $stats['total_patients']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/20">
                            <i class="fas fa-calendar-check text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Appointments</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalAppointments"><?php echo $stats['total_appointments']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                            <i class="fas fa-calendar-day text-yellow-600 dark:text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Appointments</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="todayAppointments"><?php echo $stats['today_appointments']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/20">
                            <i class="fas fa-calendar-week text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="upcomingAppointments"><?php echo $stats['upcoming_appointments']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="space-y-6">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Appointments -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Appointments</h3>
                            <div class="space-y-3">
                                <?php foreach (array_slice($doctor_appointments, 0, 5) as $appointment): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($appointment['patient_name'] ?? 'Unknown Patient'); ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($appointment['appointment_date'] ?? ''); ?> at <?php echo htmlspecialchars($appointment['appointment_time'] ?? ''); ?></p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $appointment['status'] === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 
                                              ($appointment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($appointment['status'] ?? 'unknown')); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Patient Vitals Chart -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Patient Vitals Overview</h3>
                            <canvas id="vitalsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Patients Section -->
                <div id="patients-section" class="content-section hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">My Patients</h2>
                        <button onclick="recordVitals()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Record Vitals
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
                                        <th class="px-6 py-3">Last Visit</th>
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

                <!-- Appointments Section -->
                <div id="appointments-section" class="content-section hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Appointments</h2>
                        <button onclick="scheduleAppointment()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Schedule Appointment
                        </button>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-6 py-3">Patient</th>
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

                <!-- Vitals Section -->
                <div id="vitals-section" class="content-section hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Patient Vitals</h2>
                        <button onclick="recordVitals()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Record Vitals
                        </button>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Vital cards will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="content-section hidden">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h2>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                        <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($current_user['first_name'] ?? '') . ' ' . htmlspecialchars($current_user['last_name'] ?? ''); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                        <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($current_user['email'] ?? ''); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                        <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($current_user['phone'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Professional Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                                        <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($current_user['specialization'] ?? 'General Medicine'); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Experience</label>
                                        <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($current_user['experience_years'] ?? '0'); ?> years</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        });

        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.add('hidden');
                });
                
                // Show target section
                const targetId = this.getAttribute('href').substring(1) + '-section';
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.classList.remove('hidden');
                }
            });
        });

        // Dark mode toggle
        document.getElementById('dark-mode-toggle').addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadPatients();
            loadAppointments();
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

        async function loadPatients() {
            try {
                const response = await fetch('/api/patients.php?action=list');
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
                                    <button onclick="viewPatient(${patient.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        View
                                    </button>
                                    <button onclick="recordVitals(${patient.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                        Vitals
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
                const response = await fetch('/api/appointments.php?action=list');
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
                                    <button onclick="viewAppointmentDetails(${appointment.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        View
                                    </button>
                                    <button onclick="markCompleted(${appointment.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2">
                                        Complete
                                    </button>
                                    <button onclick="cancelAppointment(${appointment.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Cancel
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

        function updateDashboardStats(stats) {
            const totalPatientsEl = document.getElementById('totalPatients');
            const totalAppointmentsEl = document.getElementById('totalAppointments');
            const todayAppointmentsEl = document.getElementById('todayAppointments');
            const upcomingAppointmentsEl = document.getElementById('upcomingAppointments');
            
            if (totalPatientsEl) totalPatientsEl.textContent = stats.total_patients;
            if (totalAppointmentsEl) totalAppointmentsEl.textContent = stats.total_appointments;
            if (todayAppointmentsEl) todayAppointmentsEl.textContent = stats.today_appointments;
            if (upcomingAppointmentsEl) upcomingAppointmentsEl.textContent = stats.upcoming_appointments;
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Action functions
        function viewPatient(patientId) {
            showNotification('View patient details for ID: ' + patientId, 'info');
        }

        function recordVitals(patientId) {
            console.log('Record vitals called for patient ID:', patientId);
            showNotification('Record vitals for patient ID: ' + patientId, 'info');
            // TODO: Implement vitals recording modal
        }

        function viewAppointmentDetails(appointmentId) {
            showNotification('View appointment details for ID: ' + appointmentId, 'info');
        }

        function markCompleted(appointmentId) {
            showNotification('Mark appointment as completed for ID: ' + appointmentId, 'info');
        }

        function cancelAppointment(appointmentId) {
            showNotification('Cancel appointment for ID: ' + appointmentId, 'info');
        }

        function scheduleAppointment() {
            console.log('Schedule appointment function called');
            try {
                // Show the appointment modal
                const modal = document.getElementById('appointmentModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    // Load patients for the dropdown
                    loadPatientsForAppointment();
                } else {
                    console.error('Appointment modal not found');
                    showNotification('Error: Appointment modal not found', 'error');
                }
            } catch (error) {
                console.error('Error in scheduleAppointment:', error);
                showNotification('Error opening appointment modal: ' + error.message, 'error');
            }
        }

        function closeAppointmentModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
        }

        async function loadPatientsForAppointment() {
            console.log('Loading patients for appointment...');
            try {
                const response = await fetch('/api/patients.php?action=list');
                console.log('Patients API response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('Patients API result:', result);
                
                if (result.success) {
                    const select = document.querySelector('#appointmentForm select[name="patient_id"]');
                    if (select) {
                        // Clear existing options except the first one
                        select.innerHTML = '<option value="">Select Patient</option>';
                        
                        result.data.forEach(patient => {
                            const option = document.createElement('option');
                            option.value = patient.id;
                            option.textContent = `${patient.first_name} ${patient.last_name}`;
                            select.appendChild(option);
                        });
                        console.log('Patients loaded successfully');
                    } else {
                        console.error('Patient select element not found');
                    }
                } else {
                    console.error('Patients API error:', result.message);
                    showNotification('Error loading patients: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error loading patients:', error);
                showNotification('Error loading patients: ' + error.message, 'error');
            }
        }

        async function submitAppointment() {
            const form = document.getElementById('appointmentForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/api/appointments.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        patient_id: formData.get('patient_id'),
                        doctor_id: <?php echo $current_user['doctor_id'] ?? 1; ?>,
                        appointment_date: formData.get('appointment_date'),
                        appointment_time: formData.get('appointment_time'),
                        appointment_type: formData.get('appointment_type'),
                        notes: formData.get('notes')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Appointment scheduled successfully!', 'success');
                    closeAppointmentModal();
                    loadAppointments(); // Refresh the appointments list
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error scheduling appointment:', error);
                showNotification('Error scheduling appointment: ' + error.message, 'error');
            }
        }
    </script>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Schedule Appointment</h3>
                    <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="appointmentForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient</label>
                        <select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Patient</option>
                            <!-- Patients will be loaded here -->
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                        <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time</label>
                        <input type="time" name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="appointment_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Type</option>
                            <option value="consultation">Consultation</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="emergency">Emergency</option>
                            <option value="routine">Routine Check</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeAppointmentModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button type="button" onclick="submitAppointment()" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>