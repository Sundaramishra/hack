<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/Vitals.php';

// Continue with original code
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/Vitals.php';

$auth = new Auth();
$user_manager = new User();
$appointment_manager = new Appointment();
$vitals_manager = new Vitals();

// Check authentication and role
if (!$auth->isLoggedIn() || !$auth->hasRole('patient')) {
    header('Location: ../index.php');
    exit();
}

$current_user = $auth->getCurrentUser();
$patient_id = $current_user['patient_id'];

// Get patient's data
$patient_appointments = $appointment_manager->getAppointmentsByPatient($patient_id);
$upcoming_appointments = $appointment_manager->getUpcomingAppointments('patient', $patient_id);
$patient_vitals = $vitals_manager->getLatestVitals($patient_id);
$vital_statistics = $vitals_manager->getVitalStatistics($patient_id, 30);

// Get recent vitals (last 7 days)
$recent_vitals = $vitals_manager->getPatientVitals($patient_id, null, 7);

$stats = [
    'total_appointments' => count($patient_appointments),
    'upcoming_appointments' => count($upcoming_appointments),
    'vital_readings' => count($recent_vitals),
    'last_checkup' => !empty($patient_appointments) ? max(array_column($patient_appointments, 'appointment_date')) : null
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Hospital CRM</title>
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
        
        
        <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
        <div class="flex items-center justify-center h-16 bg-primary-500">
            <i class="fas fa-user-injured text-white text-2xl mr-3"></i>
            <h1 class="text-white text-xl font-bold">Patient Portal</h1>
        </div>
        
        <nav class="mt-8">
            <a href="#dashboard" class="nav-link active flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </a>
            <a href="#appointments" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>
                My Appointments
            </a>
            <a href="#vitals" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>
                My Vitals
            </a>
            <a href="#prescriptions" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>
                Prescriptions
            </a>
            <a href="#medical-history" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-file-medical mr-3"></i>
                Medical History
            </a>
            <a href="#profile" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user mr-3"></i>
                My Profile
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
                        Welcome, <?php echo htmlspecialchars(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? '')); ?>
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-moon dark:hidden text-gray-600"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
                        <i class="fas fa-id-card"></i>
                        <span>ID: <?php echo htmlspecialchars($current_user['patient_code'] ?? 'N/A'); ?></span>
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
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Appointments</p>
                                <p class="text-3xl font-bold text-primary-600"><?php echo $stats['total_appointments']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-primary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo $stats['upcoming_appointments']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Recent Vitals</p>
                                <p class="text-3xl font-bold text-red-600"><?php echo $stats['vital_readings']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-heartbeat text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Checkup</p>
                                <p class="text-lg font-bold text-blue-600">
                                    <?php echo $stats['last_checkup'] ? date('M j', strtotime($stats['last_checkup'])) : 'None'; ?>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-stethoscope text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments and Latest Vitals -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Upcoming Appointments -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-calendar-check text-primary-500 mr-2"></i>
                                Upcoming Appointments
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($upcoming_appointments)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No upcoming appointments</p>
                                    <button onclick="showSection('appointments')" class="mt-4 text-primary-500 hover:text-primary-700 text-sm">
                                        View All Appointments
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($upcoming_appointments, 0, 3) as $appointment): ?>
                                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user-md text-white"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        <?php echo htmlspecialchars($appointment['specialization']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                                </p>
                                                <p class="text-sm text-primary-600">
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4 text-center">
                                    <button onclick="showSection('appointments')" class="text-primary-500 hover:text-primary-700 text-sm">
                                        View All Appointments →
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Latest Vitals -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                                Latest Vitals
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($patient_vitals)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-heartbeat text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No vital signs recorded yet</p>
                                    <button onclick="showSection('vitals')" class="mt-4 text-red-500 hover:text-red-700 text-sm">
                                        View Vitals History
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 gap-3">
                                    <?php foreach (array_slice($patient_vitals, 0, 6) as $vital): ?>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                                    <?php 
                                                    switch($vital['status']) {
                                                        case 'normal': echo 'bg-green-100 dark:bg-green-900'; break;
                                                        case 'high': echo 'bg-red-100 dark:bg-red-900'; break;
                                                        case 'low': echo 'bg-yellow-100 dark:bg-yellow-900'; break;
                                                        default: echo 'bg-gray-100 dark:bg-gray-900';
                                                    }
                                                    ?>">
                                                    <i class="fas fa-circle text-xs 
                                                        <?php 
                                                        switch($vital['status']) {
                                                            case 'normal': echo 'text-green-600'; break;
                                                            case 'high': echo 'text-red-600'; break;
                                                            case 'low': echo 'text-yellow-600'; break;
                                                            default: echo 'text-gray-600';
                                                        }
                                                        ?>"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($vital['vital_name']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        <?php echo date('M j, g:i A', strtotime($vital['recorded_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    <?php echo $vital['value']; ?> <?php echo $vital['unit']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4 text-center">
                                    <button onclick="showSection('vitals')" class="text-red-500 hover:text-red-700 text-sm">
                                        View All Vitals →
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Health Overview -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Health Overview (Last 30 Days)</h3>
                    </div>
                    <div class="p-6">
                        <?php if (empty($vital_statistics)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 dark:text-gray-400">No health data available for the last 30 days</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($vital_statistics as $stat): ?>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-3"><?php echo htmlspecialchars($stat['name']); ?></h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Readings:</span>
                                                <span class="font-medium text-gray-900 dark:text-white"><?php echo $stat['reading_count']; ?></span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Average:</span>
                                                <span class="font-medium text-gray-900 dark:text-white"><?php echo round($stat['avg_value'], 1); ?></span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Range:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">
                                                    <?php echo round($stat['min_value'], 1); ?> - <?php echo round($stat['max_value'], 1); ?>
                                                </span>
                                            </div>
                                            <?php if ($stat['high_count'] > 0 || $stat['low_count'] > 0): ?>
                                                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                                    <?php if ($stat['high_count'] > 0): ?>
                                                        <div class="flex items-center text-xs text-red-600">
                                                            <i class="fas fa-arrow-up mr-1"></i>
                                                            <?php echo $stat['high_count']; ?> high readings
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($stat['low_count'] > 0): ?>
                                                        <div class="flex items-center text-xs text-yellow-600">
                                                            <i class="fas fa-arrow-down mr-1"></i>
                                                            <?php echo $stat['low_count']; ?> low readings
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Appointments Section -->
            <div id="appointments-section" class="content-section hidden">
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 md:mb-0">My Appointments</h2>
                        <div class="flex space-x-3">
                            <button onclick="openBookAppointmentModal()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Book Appointment
                            </button>
                            <select id="appointmentFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                <option value="all">All Appointments</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($patient_appointments as $appointment): ?>
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
                                    <i class="fas fa-user-md text-primary-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($appointment['specialization']); ?>
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
                                <button onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-1"></i> Details
                                </button>
                                <?php if ($appointment['status'] === 'scheduled' && strtotime($appointment['appointment_date']) > time()): ?>
                                <button onclick="requestReschedule(<?php echo $appointment['id']; ?>)" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-calendar-alt mr-1"></i> Reschedule
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($patient_appointments)): ?>
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
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">My Vital Signs</h2>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex space-x-3 mb-4 md:mb-0">
                            <select id="vitalsTimeRange" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                <option value="7">Last 7 Days</option>
                                <option value="30">Last 30 Days</option>
                                <option value="90">Last 3 Months</option>
                                <option value="365">Last Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Vitals Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($patient_vitals as $vital): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($vital['vital_name']); ?></h3>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    <?php 
                                    switch($vital['status']) {
                                        case 'normal': echo 'bg-green-100 dark:bg-green-900'; break;
                                        case 'high': echo 'bg-red-100 dark:bg-red-900'; break;
                                        case 'low': echo 'bg-yellow-100 dark:bg-yellow-900'; break;
                                        default: echo 'bg-gray-100 dark:bg-gray-900';
                                    }
                                    ?>">
                                    <i class="fas fa-circle text-xs 
                                        <?php 
                                        switch($vital['status']) {
                                            case 'normal': echo 'text-green-600'; break;
                                            case 'high': echo 'text-red-600'; break;
                                            case 'low': echo 'text-yellow-600'; break;
                                            default: echo 'text-gray-600';
                                        }
                                        ?>"></i>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    <?php echo $vital['value']; ?>
                                    <span class="text-lg font-normal text-gray-500"><?php echo $vital['unit']; ?></span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                    Normal: <?php echo $vital['normal_range_min']; ?>-<?php echo $vital['normal_range_max']; ?> <?php echo $vital['unit']; ?>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Last recorded: <?php echo date('M j, Y g:i A', strtotime($vital['recorded_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vitals History Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vitals Trends</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="vitalsChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Recent Vitals History -->
                <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Readings</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vital Sign</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Recorded By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($recent_vitals as $vital): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo date('M j, Y g:i A', strtotime($vital['recorded_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($vital['vital_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo $vital['value']; ?> <?php echo $vital['unit']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php 
                                            switch($vital['status']) {
                                                case 'normal': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                                case 'high': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                                case 'low': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                                default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                            }
                                            ?>">
                                            <?php echo ucfirst($vital['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($vital['first_name'] . ' ' . $vital['last_name']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Prescriptions Section -->
            <div id="prescriptions-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">My Prescriptions</h2>
                    <button onclick="downloadPrescription()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Doctor</th>
                                    <th class="px-6 py-3">Medication</th>
                                    <th class="px-6 py-3">Dosage</th>
                                    <th class="px-6 py-3">Duration</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="prescriptionsTableBody">
                                <!-- Prescriptions data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Medical History Section -->
            <div id="medical-history-section" class="content-section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Medical History</h2>
                    <button onclick="exportHistory()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-file-export mr-2"></i>Export
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Doctor</th>
                                    <th class="px-6 py-3">Diagnosis</th>
                                    <th class="px-6 py-3">Treatment</th>
                                    <th class="px-6 py-3">Notes</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="medicalHistoryTableBody">
                                <!-- Medical history data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="profile-section" class="content-section hidden">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Info -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($current_user['first_name']); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($current_user['last_name']); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient ID</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($current_user['patient_code'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($current_user['email']); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars(ucfirst($current_user['gender'] ?? 'Not specified')); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <?php echo $current_user['date_of_birth'] ? date('M j, Y', strtotime($current_user['date_of_birth'])) : 'Not specified'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical Info -->
                    <div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Medical Information</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Blood Group</label>
                                    <div class="px-3 py-2 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                                        <span class="text-red-800 dark:text-red-200 font-semibold">
                                            <?php echo htmlspecialchars($current_user['blood_group'] ?? 'Not specified'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact</label>
                                    <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                        <div class="text-sm text-gray-900 dark:text-white font-medium">
                                            <?php echo htmlspecialchars($current_user['emergency_contact_name'] ?? 'Not specified'); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($current_user['emergency_contact_phone'] ?? 'No phone'); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Allergies</label>
                                    <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <?php echo htmlspecialchars($current_user['allergies'] ?? 'None specified'); ?>
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
                    case 'upcoming':
                        show = date >= today && status === 'scheduled';
                        break;
                    default:
                        show = status === filter;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        });

        // Appointment functions
        function viewAppointmentDetails(appointmentId) {
            alert('View appointment details - ID: ' + appointmentId);
        }

        function requestReschedule(appointmentId) {
            alert('Request reschedule - ID: ' + appointmentId);
        }

        // Initialize vitals chart
        if (document.getElementById('vitalsChart')) {
            const ctx = document.getElementById('vitalsChart').getContext('2d');
            const vitalsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Blood Pressure (Systolic)',
                        data: [120, 125, 118, 122, 119, 121],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadAppointments();
            loadPrescriptions();
            loadMedicalHistory();
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
                                    <button onclick="viewAppointmentDetails(${appointment.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        View
                                    </button>
                                    <button onclick="requestReschedule(${appointment.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        Reschedule
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

        async function loadPrescriptions() {
            try {
                const response = await fetch('../api/prescriptions.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('prescriptionsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(prescription => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${prescription.prescribed_date}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${prescription.doctor_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${prescription.medication}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${prescription.dosage}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${prescription.duration}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        ${prescription.status === 'active' ? 'bg-green-100 text-green-800' : 
                                          prescription.status === 'discontinued' ? 'bg-red-100 text-red-800' : 
                                          'bg-gray-100 text-gray-800'}">
                                        ${prescription.status}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="downloadPrescription(${prescription.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        Download
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
            }
        }

        async function loadMedicalHistory() {
            try {
                const response = await fetch('../api/medical_history.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('medicalHistoryTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        result.data.forEach(history => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${history.visit_date}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${history.doctor_name}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${history.diagnosis}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${history.treatment}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${history.notes}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="exportHistory(${history.id})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                        Export
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading medical history:', error);
            }
        }

        function updateDashboardStats(stats) {
            const totalAppointmentsEl = document.getElementById('totalAppointments');
            const upcomingAppointmentsEl = document.getElementById('upcomingAppointments');
            const totalPrescriptionsEl = document.getElementById('totalPrescriptions');
            const recentVitalsEl = document.getElementById('recentVitals');
            
            if (totalAppointmentsEl) totalAppointmentsEl.textContent = stats.total_appointments;
            if (upcomingAppointmentsEl) upcomingAppointmentsEl.textContent = stats.upcoming_appointments;
            if (totalPrescriptionsEl) totalPrescriptionsEl.textContent = stats.total_prescriptions;
            if (recentVitalsEl) recentVitalsEl.textContent = stats.recent_vitals;
        }

        // New functions for prescriptions and medical history
        async function downloadPrescription(prescriptionId = null) {
            try {
                const action = prescriptionId ? 'download' : 'list';
                const url = `../api/prescriptions.php?action=${action}`;
                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    let content = '';
                    if (data.length === 0) {
                        content = 'No prescriptions found.';
                    } else {
                        content = `Date,Doctor,Medication,Dosage,Duration,Status\n`;
                        data.forEach(prescription => {
                            content += `${prescription.prescribed_date},${prescription.doctor_name},${prescription.medication},${prescription.dosage},${prescription.duration},${prescription.status}\n`;
                        });
                    }

                    const blob = new Blob([content], { type: 'text/csv' });
                    const urlBlob = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = urlBlob;
                    a.download = `patient_prescriptions_${prescriptionId ? 'details' : 'all'}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(urlBlob);
                } else {
                    alert('Error downloading prescriptions: ' + result.message);
                }
            } catch (error) {
                console.error('Error downloading prescriptions:', error);
                alert('Error downloading prescriptions: ' + error.message);
            }
        }

        async function exportHistory(historyId = null) {
            try {
                const action = historyId ? 'export' : 'list';
                const url = `../api/medical_history.php?action=${action}`;
                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    let content = '';
                    if (data.length === 0) {
                        content = 'No medical history found.';
                    } else {
                        content = `Date,Doctor,Diagnosis,Treatment,Notes\n`;
                        data.forEach(history => {
                            content += `${history.visit_date},${history.doctor_name},${history.diagnosis},${history.treatment},${history.notes}\n`;
                        });
                    }

                    const blob = new Blob([content], { type: 'text/csv' });
                    const urlBlob = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = urlBlob;
                    a.download = `patient_medical_history_${historyId ? 'details' : 'all'}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(urlBlob);
                } else {
                    alert('Error exporting medical history: ' + result.message);
                }
            } catch (error) {
                console.error('Error exporting medical history:', error);
                alert('Error exporting medical history: ' + error.message);
            }
        }

        // Appointment booking functions
        function openBookAppointmentModal() {
            console.log('Open book appointment modal function called');
            try {
                const modal = document.getElementById('bookAppointmentModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    loadDoctorsForBooking();
                } else {
                    console.error('Book appointment modal not found');
                    alert('Error: Book appointment modal not found');
                }
            } catch (error) {
                console.error('Error in openBookAppointmentModal:', error);
                alert('Error opening book appointment modal: ' + error.message);
            }
        }

        function closeBookAppointmentModal() {
            document.getElementById('bookAppointmentModal').classList.add('hidden');
        }

        async function loadDoctorsForBooking() {
            console.log('Loading doctors for booking...');
            try {
                const response = await fetch('../api/doctors.php?action=list');
                console.log('Doctors API response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('Doctors API result:', result);
                
                if (result.success) {
                    const select = document.querySelector('#bookAppointmentForm select[name="doctor_id"]');
                    if (select) {
                        select.innerHTML = '<option value="">Select Doctor</option>';
                        
                        result.data.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id;
                            option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name} (${doctor.specialization})`;
                            select.appendChild(option);
                        });
                        console.log('Doctors loaded successfully');
                    } else {
                        console.error('Doctor select element not found');
                    }
                } else {
                    console.error('Doctors API error:', result.message);
                    alert('Error loading doctors: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
                alert('Error loading doctors: ' + error.message);
            }
        }

        async function submitBookAppointment() {
            const form = document.getElementById('bookAppointmentForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('../api/appointments.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        patient_id: <?php echo $current_user['patient_id'] ?? 1; ?>,
                        doctor_id: formData.get('doctor_id'),
                        appointment_date: formData.get('appointment_date'),
                        appointment_time: formData.get('appointment_time'),
                        appointment_type: formData.get('appointment_type'),
                        reason: formData.get('reason'),
                        notes: formData.get('notes')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Appointment booked successfully!');
                    closeBookAppointmentModal();
                    location.reload(); // Refresh the page to show new appointment
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                alert('Error booking appointment: ' + error.message);
            }
        }
    </script>

    <!-- Book Appointment Modal -->
    <div id="bookAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Book Appointment</h3>
                    <button onclick="closeBookAppointmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="bookAppointmentForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor</label>
                        <select name="doctor_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Doctor</option>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                        <input type="text" name="reason" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Brief reason for appointment">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeBookAppointmentModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button type="button" onclick="submitBookAppointment()" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            Book Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>