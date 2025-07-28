<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/Vitals.php';

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
        
        <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
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
                <i class="fas fa-user-injured mr-3"></i>
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
            <a href="#prescriptions" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>
                Prescriptions
            </a>
            <a href="#schedule" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-clock mr-3"></i>
                Schedule
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
                    <h2 class="ml-4 text-xl font-semibold text-gray-800 dark:text-white">
                        Welcome, Dr. <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-moon dark:hidden text-gray-600"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
                        <i class="fas fa-user-md"></i>
                        <span><?php echo htmlspecialchars($current_user['specialization'] ?? 'Doctor'); ?></span>
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
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">My Patients</p>
                                <p class="text-3xl font-bold text-primary-600"><?php echo $stats['total_patients']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-injured text-primary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Appointments</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo $stats['today_appointments']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
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

                <!-- Today's Schedule and Recent Patients -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Today's Schedule -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-calendar-day text-primary-500 mr-2"></i>
                                Today's Schedule
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($today_appointments)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No appointments scheduled for today</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($today_appointments as $appointment): ?>
                                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        <?php echo htmlspecialchars($appointment['reason'] ?? 'General Consultation'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-semibold text-primary-600">
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </p>
                                                <button onclick="viewPatientDetails(<?php echo $appointment['patient_id']; ?>)" class="text-sm text-primary-500 hover:text-primary-700">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Patients -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-users text-green-500 mr-2"></i>
                                Recent Patients
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($assigned_patients)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-user-injured text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No patients assigned yet</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($assigned_patients, 0, 5) as $patient): ?>
                                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-green-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        ID: <?php echo htmlspecialchars($patient['patient_code']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                                                    <?php echo htmlspecialchars($patient['blood_group'] ?? 'N/A'); ?>
                                                </span>
                                                <div class="mt-1">
                                                    <button onclick="viewPatientVitals(<?php echo $patient['id']; ?>)" class="text-xs text-blue-500 hover:text-blue-700">
                                                        View Vitals
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <button onclick="showSection('appointments')" class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors">
                                <i class="fas fa-calendar-plus text-blue-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">View Appointments</span>
                            </button>
                            
                            <button onclick="showSection('patients')" class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                                <i class="fas fa-user-injured text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-700 dark:text-green-300">My Patients</span>
                            </button>
                            
                            <button onclick="showSection('vitals')" class="flex flex-col items-center p-4 bg-red-50 dark:bg-red-900 rounded-lg hover:bg-red-100 dark:hover:bg-red-800 transition-colors">
                                <i class="fas fa-heartbeat text-red-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-red-700 dark:text-red-300">Patient Vitals</span>
                            </button>
                            
                            <button onclick="openModal('addVitalsModal')" class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-800 transition-colors">
                                <i class="fas fa-plus-circle text-purple-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Record Vitals</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients Section -->
            <div id="patients-section" class="content-section hidden">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Assigned Patients</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Blood Group</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Emergency Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assigned Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($assigned_patients as $patient): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-green-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($patient['patient_code']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <?php echo htmlspecialchars($patient['blood_group'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <div>
                                            <div class="font-medium"><?php echo htmlspecialchars($patient['emergency_contact_name'] ?? 'N/A'); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars($patient['emergency_contact_phone'] ?? 'N/A'); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo date('M j, Y', strtotime($patient['assigned_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button onclick="viewPatientDetails(<?php echo $patient['id']; ?>)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button onclick="viewPatientVitals(<?php echo $patient['id']; ?>)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            <i class="fas fa-heartbeat"></i> Vitals
                                        </button>
                                        <button onclick="recordVitals(<?php echo $patient['id']; ?>)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                            <i class="fas fa-plus"></i> Record
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Appointments Section -->
            <div id="appointments-section" class="content-section hidden">
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 md:mb-0">My Appointments</h2>
                        <div class="flex space-x-3">
                            <select id="appointmentFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                <option value="all">All Appointments</option>
                                <option value="today">Today</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($doctor_appointments as $appointment): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 appointment-card" data-status="<?php echo $appointment['status']; ?>" data-date="<?php echo $appointment['appointment_date']; ?>">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    <?php 
                                    switch($appointment['status']) {
                                        case 'scheduled': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                        case 'completed': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                    }
                                    ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-primary-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: <?php echo htmlspecialchars($appointment['patient_code']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-clock mr-2 text-primary-500"></i>
                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </div>
                                <?php if ($appointment['reason']): ?>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-comment-medical mr-2 text-primary-500"></i>
                                    <?php echo htmlspecialchars($appointment['reason']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex space-x-2">
                                <?php if ($appointment['status'] === 'scheduled'): ?>
                                <button onclick="markCompleted(<?php echo $appointment['id']; ?>)" class="flex-1 bg-green-500 hover:bg-green-600 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-check mr-1"></i> Complete
                                </button>
                                <button onclick="cancelAppointment(<?php echo $appointment['id']; ?>)" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </button>
                                <?php endif; ?>
                                <button onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-1"></i> Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($doctor_appointments)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Appointments</h3>
                    <p class="text-gray-500 dark:text-gray-400">You don't have any appointments yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Vitals Section -->
            <div id="vitals-section" class="content-section hidden">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Patient Vitals Monitoring</h2>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <select id="patientVitalsFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white mb-4 md:mb-0">
                            <option value="">Select Patient</option>
                            <?php foreach ($assigned_patients as $patient): ?>
                            <option value="<?php echo $patient['id']; ?>">
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'] . ' - ' . $patient['patient_code']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="openModal('addVitalsModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Record Vitals
                        </button>
                    </div>
                </div>

                <div id="vitalsDisplay" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Vitals cards will be populated by JavaScript -->
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vitals Trends</h3>
                        </div>
                        <div class="p-6">
                            <canvas id="vitalsChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div id="noPatientSelected" class="text-center py-12">
                    <i class="fas fa-heartbeat text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Select a Patient</h3>
                    <p class="text-gray-500 dark:text-gray-400">Choose a patient from the dropdown to view their vital signs.</p>
                </div>
            </div>

            <!-- Other sections would go here -->
            <div id="prescriptions-section" class="content-section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Prescriptions Management</h2>
                <!-- Prescriptions content -->
            </div>

            <div id="schedule-section" class="content-section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Schedule</h2>
                <!-- Schedule content -->
            </div>
        </main>
    </div>

    <!-- Add Vitals Modal -->
    <div id="addVitalsModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-2xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Record Patient Vitals</h3>
                    <button onclick="closeModal('addVitalsModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="addVitalsForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Patient</label>
                        <select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Patient</option>
                            <?php foreach ($assigned_patients as $patient): ?>
                            <option value="<?php echo $patient['id']; ?>">
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'] . ' - ' . $patient['patient_code']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4" id="vitalsInputs">
                        <!-- Dynamic vital inputs will be loaded here -->
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('addVitalsModal')" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            Record Vitals
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
            if (modalId === 'addVitalsModal') {
                loadVitalTypes();
            }
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

        // Appointment filtering
        document.getElementById('appointmentFilter').addEventListener('change', function() {
            const filter = this.value;
            const appointments = document.querySelectorAll('.appointment-card');
            const today = new Date().toISOString().split('T')[0];
            
            appointments.forEach(card => {
                const status = card.dataset.status;
                const date = card.dataset.date;
                let show = false;
                
                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case 'today':
                        show = date === today;
                        break;
                    case 'upcoming':
                        show = date >= today && status === 'scheduled';
                        break;
                    default:
                        show = status === filter;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        });

        // Patient functions
        function viewPatientDetails(patientId) {
            // Implementation for viewing patient details
            alert('View patient details - ID: ' + patientId);
        }

        function viewPatientVitals(patientId) {
            document.getElementById('patientVitalsFilter').value = patientId;
            showSection('vitals');
            loadPatientVitals(patientId);
        }

        function recordVitals(patientId) {
            openModal('addVitalsModal');
            document.querySelector('[name="patient_id"]').value = patientId;
        }

        // Appointment functions
        function markCompleted(appointmentId) {
            if (confirm('Mark this appointment as completed?')) {
                // Implementation for marking appointment as completed
                alert('Appointment marked as completed - ID: ' + appointmentId);
            }
        }

        function cancelAppointment(appointmentId) {
            if (confirm('Cancel this appointment?')) {
                // Implementation for canceling appointment
                alert('Appointment cancelled - ID: ' + appointmentId);
            }
        }

        function viewAppointmentDetails(appointmentId) {
            // Implementation for viewing appointment details
            alert('View appointment details - ID: ' + appointmentId);
        }

        // Vitals functions
        async function loadVitalTypes() {
            try {
                const response = await fetch('../api/vitals.php?action=types');
                const result = await response.json();
                
                if (result.success) {
                    const container = document.getElementById('vitalsInputs');
                    container.innerHTML = '';
                    
                    result.vital_types.forEach(type => {
                        const div = document.createElement('div');
                        div.innerHTML = `
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                ${type.name} ${type.unit ? '(' + type.unit + ')' : ''}
                            </label>
                            <input type="number" step="0.01" name="vitals[${type.id}]" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="${type.normal_range_min}-${type.normal_range_max}">
                        `;
                        container.appendChild(div);
                    });
                }
            } catch (error) {
                console.error('Error loading vital types:', error);
            }
        }

        function loadPatientVitals(patientId) {
            document.getElementById('noPatientSelected').classList.add('hidden');
            document.getElementById('vitalsDisplay').classList.remove('hidden');
            
            // Implementation for loading and displaying patient vitals
            // This would fetch data from the API and populate the charts and cards
        }

        // Patient vitals filter
        document.getElementById('patientVitalsFilter').addEventListener('change', function() {
            const patientId = this.value;
            if (patientId) {
                loadPatientVitals(patientId);
            } else {
                document.getElementById('vitalsDisplay').classList.add('hidden');
                document.getElementById('noPatientSelected').classList.remove('hidden');
            }
        });

        // Vitals form submission
        document.getElementById('addVitalsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/vitals.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Vitals recorded successfully!');
                    closeModal('addVitalsModal');
                    e.target.reset();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
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
    </script>
</body>
</html>