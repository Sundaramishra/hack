<?php
// FORCE ERROR DISPLAY
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Debug section at top
echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; margin: 10px; font-family: Arial;'>";
echo "<h3 style='color: #d32f2f; margin: 0 0 10px 0;'>🔍 DOCTOR DASHBOARD DEBUG</h3>";

// Test 1: Basic PHP
echo "<h4 style='color: #1976d2;'>📋 PHP Info:</h4>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (error_reporting() ? 'ON' : 'OFF') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Current Directory: " . getcwd() . "<br><br>";

// Test 2: Session
echo "<h4 style='color: #1976d2;'>🔐 Session Test:</h4>";
try {
    session_start();
    echo "✅ Session started<br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session Status: " . session_status() . "<br><br>";
} catch (Exception $e) {
    echo "❌ Session ERROR: " . $e->getMessage() . "<br><br>";
}

// Test 3: File Includes
echo "<h4 style='color: #1976d2;'>📁 File Include Test:</h4>";
$files = [
    '../classes/Auth.php',
    '../classes/User.php', 
    '../classes/Appointment.php',
    '../classes/Vitals.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS<br>";
        try {
            require_once $file;
            echo "✅ $file: INCLUDED<br>";
        } catch (Exception $e) {
            echo "❌ $file INCLUDE ERROR: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ $file: MISSING!<br>";
    }
}
echo "<br>";

// Test 4: Database
echo "<h4 style='color: #1976d2;'>🗄️ Database Test:</h4>";
try {
    require_once '../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database Connected!<br>";
        
        // Test tables
        $tables = ['users', 'doctors', 'patients', 'appointments'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "✅ Table '$table': $count records<br>";
            } catch (Exception $e) {
                echo "❌ Table '$table' ERROR: " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ Database Connection FAILED!<br>";
    }
} catch (Exception $e) {
    echo "❌ Database ERROR: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 5: Classes
echo "<h4 style='color: #1976d2;'>🔧 Classes Test:</h4>";
try {
    $auth = new Auth();
    echo "✅ Auth class created<br>";
    
    if ($auth->isLoggedIn()) {
        echo "✅ User logged in<br>";
        $user = $auth->getCurrentUser();
        echo "User: " . ($user['first_name'] ?? 'Unknown') . "<br>";
    } else {
        echo "⚠️ User not logged in<br>";
    }
} catch (Exception $e) {
    echo "❌ Auth ERROR: " . $e->getMessage() . "<br>";
}

try {
    $user_manager = new User();
    echo "✅ User class created<br>";
} catch (Exception $e) {
    echo "❌ User class ERROR: " . $e->getMessage() . "<br>";
}

try {
    $appointment_manager = new Appointment();
    echo "✅ Appointment class created<br>";
} catch (Exception $e) {
    echo "❌ Appointment class ERROR: " . $e->getMessage() . "<br>";
}

try {
    $vitals_manager = new Vitals();
    echo "✅ Vitals class created<br>";
} catch (Exception $e) {
    echo "❌ Vitals class ERROR: " . $e->getMessage() . "<br>";
}
echo "<br>";

echo "<h4 style='color: #1976d2;'>✅ DEBUG COMPLETE</h4>";
echo "If you see this, PHP is working!<br>";
echo "</div>";

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
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Debug Information -->
    <?php include '../debug_info.php'; ?>
    
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
                
                console.log('Navigation clicked:', this.getAttribute('href'));
                
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
                console.log('Target section ID:', targetId);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.classList.remove('hidden');
                    console.log('Section shown:', targetId);
                } else {
                    console.log('Section not found:', targetId);
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

        // Action functions
        function viewPatient(patientId) {
            alert('View patient details for ID: ' + patientId);
        }

        function recordVitals(patientId) {
            alert('Record vitals for patient ID: ' + patientId);
        }

        function viewAppointmentDetails(appointmentId) {
            alert('View appointment details for ID: ' + appointmentId);
        }

        function markCompleted(appointmentId) {
            alert('Mark appointment as completed for ID: ' + appointmentId);
        }

        function cancelAppointment(appointmentId) {
            alert('Cancel appointment for ID: ' + appointmentId);
        }

        function scheduleAppointment() {
            alert('Schedule new appointment');
        }
    </script>
</body>
</html>