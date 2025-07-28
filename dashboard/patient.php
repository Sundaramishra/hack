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

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

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
            showNotification('View appointment details - ID: ' + appointmentId, 'info');
        }

        function requestReschedule(appointmentId) {
            showNotification('Request reschedule - ID: ' + appointmentId, 'info');
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
                const response = await fetch(getApiPath('appointments.php') + '?action=list');
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
                const response = await fetch(getApiPath('prescriptions.php') + '?action=list');
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
                const response = await fetch(getApiPath('medical_history.php') + '?action=list');
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
                const url = getApiPath('prescriptions.php') + `?action=${action}`;
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
                    showNotification('Error downloading prescriptions: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error downloading prescriptions:', error);
                showNotification('Error downloading prescriptions: ' + error.message, 'error');
            }
        }

        async function exportHistory(historyId = null) {
            try {
                const action = historyId ? 'export' : 'list';
                const url = getApiPath('medical_history.php') + `?action=${action}`;
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
                    showNotification('Error exporting medical history: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error exporting medical history:', error);
                showNotification('Error exporting medical history: ' + error.message, 'error');
            }
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
                    showNotification('Error: Book appointment modal not found', 'error');
                }
            } catch (error) {
                console.error('Error in openBookAppointmentModal:', error);
                showNotification('Error opening book appointment modal: ' + error.message, 'error');
            }
        }

        function closeBookAppointmentModal() {
            document.getElementById('bookAppointmentModal').classList.add('hidden');
        }

        async function loadDoctorsForBooking() {
            console.log('Loading doctors for booking...');
            try {
                const response = await fetch(getApiPath('doctors.php') + '?action=list');
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
                    showNotification('Error loading doctors: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
                showNotification('Error loading doctors: ' + error.message, 'error');
            }
        }

        async function submitBookAppointment() {
            const form = document.getElementById('bookAppointmentForm');
            const formData = new FormData(form);
            
            // Validate required fields
            if (!formData.get('doctor_id')) {
                showNotification('Please select a doctor', 'error');
                return;
            }
            
            if (!formData.get('appointment_date')) {
                showNotification('Please select an appointment date', 'error');
                return;
            }
            
            if (!document.getElementById('selectedTime').value) {
                showNotification('Please select a time slot', 'error');
                return;
            }
            
            // Appointment type is optional for backward compatibility
            const appointmentType = formData.get('appointment_type');
            
            // Get duration based on appointment type
            let duration = 30; // Default duration
            if (appointmentType === 'custom') {
                duration = parseInt(formData.get('custom_duration')) || 30;
            } else if (appointmentType && typeof currentDuration !== 'undefined') {
                duration = currentDuration;
            }
            
            try {
                const appointmentData = {
                    patient_id: <?php echo $current_user['patient_id'] ?? 1; ?>,
                    doctor_id: formData.get('doctor_id'),
                    appointment_date: formData.get('appointment_date'),
                    appointment_time: document.getElementById('selectedTime').value,
                    reason: formData.get('reason'),
                    notes: formData.get('notes')
                };
                
                // Add optional fields if available
                if (appointmentType) {
                    appointmentData.appointment_type = appointmentType;
                }
                
                if (duration) {
                    appointmentData.duration = duration;
                }
                
                console.log('Submitting appointment:', appointmentData);
                
                const response = await fetch(getApiPath('appointments.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(appointmentData)
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                if (!responseText.trim()) {
                    throw new Error('Empty response from server');
                }
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    console.error('Response was:', responseText.substring(0, 500));
                    throw new Error('Invalid JSON response from server. Response: ' + responseText.substring(0, 100));
                }
                
                console.log('Appointment result:', result);
                
                if (result.success) {
                    showNotification(`Appointment booked successfully for ${duration} minutes!`, 'success');
                    closeBookAppointmentModal();
                    location.reload(); // Refresh the page to show new appointment
                    
                    // Reset form
                    form.reset();
                    document.getElementById('selectedTime').value = '';
                    document.getElementById('durationInfo').classList.add('hidden');
                    document.getElementById('customDurationDiv').classList.add('hidden');
                    document.getElementById('doctorInfo').classList.add('hidden');
                    document.getElementById('availabilityChart').classList.add('hidden');
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                
                let errorMessage = 'Error booking appointment: ';
                if (error.message.includes('JSON')) {
                    errorMessage += 'Server returned invalid response. Please check if you are logged in and try again.';
                } else if (error.message.includes('Empty response')) {
                    errorMessage += 'No response from server. Please check your connection and try again.';
                } else {
                    errorMessage += error.message;
                }
                
                showNotification(errorMessage, 'error');
            }
        }

        function getApiPath(apiFile) {
            let path = window.location.pathname;
            let base = path.split('/dashboard')[0];
            if (!base.endsWith('/')) base += '/';
            return base + 'api/' + apiFile;
        }

        // Global variables for appointment booking
        let selectedDoctor = null;
        let currentDuration = 30; // Default 30 minutes
        let bookedSlots = [];

        // Update duration based on appointment type
        function updateDuration() {
            const appointmentType = document.getElementById('appointmentType');
            const customDurationDiv = document.getElementById('customDurationDiv');
            const durationInfo = document.getElementById('durationInfo');
            const selectedDurationSpan = document.getElementById('selectedDuration');
            
            if (appointmentType.value === 'custom') {
                customDurationDiv.classList.remove('hidden');
                currentDuration = parseInt(document.getElementById('customDuration').value) || 30;
            } else {
                customDurationDiv.classList.add('hidden');
                const selectedOption = appointmentType.options[appointmentType.selectedIndex];
                currentDuration = parseInt(selectedOption.getAttribute('data-duration')) || 30;
            }
            
            selectedDurationSpan.textContent = currentDuration;
            durationInfo.classList.remove('hidden');
            
            // Reload time slots if date and doctor are selected
            if (selectedDoctor && document.getElementById('appointmentDate').value) {
                loadTimeSlots();
            }
        }

        // Handle custom duration change
        document.addEventListener('DOMContentLoaded', function() {
            const customDuration = document.getElementById('customDuration');
            if (customDuration) {
                customDuration.addEventListener('change', function() {
                    if (document.getElementById('appointmentType').value === 'custom') {
                        currentDuration = parseInt(this.value);
                        document.getElementById('selectedDuration').textContent = currentDuration;
                        
                        // Reload time slots
                        if (selectedDoctor && document.getElementById('appointmentDate').value) {
                            loadTimeSlots();
                        }
                    }
                });
            }
        });

        // Load doctor availability information
        async function loadDoctorAvailability() {
            const doctorSelect = document.getElementById('doctorSelect');
            const doctorInfo = document.getElementById('doctorInfo');
            const availabilityChart = document.getElementById('availabilityChart');
            const doctorDetails = document.getElementById('doctorDetails');
            
            if (!doctorSelect.value) {
                doctorInfo.classList.add('hidden');
                availabilityChart.classList.add('hidden');
                selectedDoctor = null;
                return;
            }
            
            try {
                const response = await fetch(getApiPath(`doctors.php?action=get&id=${doctorSelect.value}`));
                const result = await response.json();
                
                if (result.success) {
                    selectedDoctor = result.data;
                    
                    // Show doctor information
                    doctorDetails.innerHTML = `
                        <div><strong>Dr. ${selectedDoctor.first_name} ${selectedDoctor.last_name}</strong></div>
                        <div>Specialization: ${selectedDoctor.specialization || 'General'}</div>
                        <div>Experience: ${selectedDoctor.experience_years || 0} years</div>
                        <div>Department: ${selectedDoctor.department || 'General'}</div>
                        ${selectedDoctor.consultation_fee ? `<div>Consultation Fee: ₹${selectedDoctor.consultation_fee}</div>` : ''}
                    `;
                    
                    doctorInfo.classList.remove('hidden');
                    
                    // Load weekly availability chart
                    await loadWeeklyAvailability();
                    availabilityChart.classList.remove('hidden');
                    
                    // Load time slots if date is selected
                    if (document.getElementById('appointmentDate').value) {
                        loadTimeSlots();
                    }
                } else {
                    showNotification('Error loading doctor information: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error loading doctor availability:', error);
                showNotification('Error loading doctor information', 'error');
            }
        }

        // Load weekly availability chart with real appointment data
        async function loadWeeklyAvailability() {
            if (!selectedDoctor) return;
            
            const weeklyChart = document.getElementById('weeklyChart');
            const today = new Date();
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            
            try {
                // Get appointments for this doctor for the current week
                const response = await fetch(getApiPath('appointments.php?action=list'));
                const result = await response.json();
                
                let weeklyAppointments = [];
                if (result.success) {
                    weeklyAppointments = result.data.filter(apt => 
                        apt.doctor_id == selectedDoctor.doctor_id && 
                        apt.status !== 'cancelled'
                    );
                }
                
                // Get current week dates
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                
                let chartHTML = '';
                
                for (let i = 0; i < 7; i++) {
                    const date = new Date(startOfWeek);
                    date.setDate(startOfWeek.getDate() + i);
                    
                    const dateStr = date.toISOString().split('T')[0];
                    const dayName = days[i];
                    const isToday = date.toDateString() === today.toDateString();
                    const isPast = date < today && !isToday;
                    
                    // Count appointments for this day
                    const dayAppointments = weeklyAppointments.filter(apt => 
                        apt.appointment_date === dateStr
                    );
                    
                    // Check if doctor is available on this day (default: Mon-Fri)
                    const availableDays = selectedDoctor.available_days ? 
                        JSON.parse(selectedDoctor.available_days) : 
                        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                    
                    const dayLower = dayName.toLowerCase();
                    const isDoctorAvailable = availableDays.includes(dayLower) || 
                                            availableDays.includes(dayName.toLowerCase()) ||
                                            (i >= 1 && i <= 5); // Default Mon-Fri if no specific days
                    
                    let statusClass, statusText;
                    
                    if (isPast) {
                        statusClass = 'bg-gray-400';
                        statusText = 'Past';
                    } else if (!isDoctorAvailable) {
                        statusClass = 'bg-gray-300';
                        statusText = 'Off';
                    } else if (dayAppointments.length >= 10) { // Assuming max 10 slots per day
                        statusClass = 'bg-red-500';
                        statusText = 'Full';
                    } else if (dayAppointments.length > 0) {
                        statusClass = 'bg-yellow-500';
                        statusText = `${dayAppointments.length} booked`;
                    } else {
                        statusClass = 'bg-green-500';
                        statusText = 'Available';
                    }
                    
                    chartHTML += `
                        <div class="text-center p-1 rounded ${isToday ? 'ring-2 ring-blue-500' : ''}">
                            <div class="font-semibold text-xs mb-1 ${isToday ? 'text-blue-600' : 'text-gray-700 dark:text-gray-300'}">${dayName}</div>
                            <div class="text-xs mb-2 text-gray-600 dark:text-gray-400">${date.getDate()}</div>
                            <div class="h-12 flex flex-col items-center justify-center ${statusClass} text-white rounded text-xs p-1" 
                                 title="${statusText} - ${dayAppointments.length} appointments on ${dateStr}">
                                <div class="font-bold">${dayAppointments.length}</div>
                                <div class="text-xs opacity-90">${statusText}</div>
                            </div>
                        </div>
                    `;
                }
                
                weeklyChart.innerHTML = chartHTML;
                
            } catch (error) {
                console.error('Error loading weekly availability:', error);
                weeklyChart.innerHTML = '<div class="col-span-7 text-center text-red-500 text-xs">Error loading availability</div>';
            }
        }

        // Load available time slots for selected date
        async function loadTimeSlots() {
            const doctorId = document.getElementById('doctorSelect').value;
            const appointmentDate = document.getElementById('appointmentDate').value;
            const timeSlotsContainer = document.getElementById('timeSlots');
            
            if (!doctorId || !appointmentDate) {
                timeSlotsContainer.innerHTML = `
                    <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-4">
                        Select a doctor and date to view available slots
                    </div>
                `;
                return;
            }
            
            // Show loading
            timeSlotsContainer.innerHTML = `
                <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-4">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading available slots...
                </div>
            `;
            
            try {
                // Get existing appointments for this doctor and date
                const response = await fetch(getApiPath(`appointments.php?action=list`));
                const result = await response.json();
                
                if (result.success) {
                    // Filter appointments for selected doctor and date
                    bookedSlots = result.data
                        .filter(apt => apt.doctor_id == doctorId && apt.appointment_date === appointmentDate)
                        .map(apt => ({
                            time: apt.appointment_time,
                            duration: apt.duration || 30 // Default 30 minutes if not specified
                        }));
                    
                    generateTimeSlots();
                } else {
                    // If no appointments or error, still generate slots
                    bookedSlots = [];
                    generateTimeSlots();
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
                bookedSlots = [];
                generateTimeSlots();
            }
        }

        // Generate time slots based on doctor availability and current bookings
        function generateTimeSlots() {
            const timeSlotsContainer = document.getElementById('timeSlots');
            
            // Default working hours (can be customized per doctor)
            const startTime = selectedDoctor?.available_time_start || '09:00';
            const endTime = selectedDoctor?.available_time_end || '17:00';
            
            const slots = [];
            const slotInterval = 30; // 30-minute intervals for better visualization
            
            // Convert time strings to minutes
            const startMinutes = timeToMinutes(startTime);
            const endMinutes = timeToMinutes(endTime);
            
            // Generate all possible slots
            for (let minutes = startMinutes; minutes < endMinutes; minutes += slotInterval) {
                const timeStr = minutesToTime(minutes);
                const isAvailable = isSlotAvailable(minutes, currentDuration);
                
                // Check if this slot is currently booked
                const isBooked = bookedSlots.some(bookedSlot => {
                    const bookedStart = timeToMinutes(bookedSlot.time);
                    return Math.abs(bookedStart - minutes) < slotInterval;
                });
                
                slots.push({
                    time: timeStr,
                    minutes: minutes,
                    available: isAvailable,
                    booked: isBooked
                });
            }
            
            // Render slots with better visual representation
            let slotsHTML = '';
            slots.forEach(slot => {
                let buttonClass, iconClass, statusText;
                
                if (slot.booked) {
                    buttonClass = 'bg-red-100 text-red-800 border-red-300 cursor-not-allowed dark:bg-red-800 dark:text-red-100 dark:border-red-600';
                    iconClass = 'fas fa-times';
                    statusText = 'Booked';
                } else if (slot.available) {
                    buttonClass = 'bg-green-100 hover:bg-green-200 text-green-800 border-green-300 hover:border-green-400 dark:bg-green-800 dark:hover:bg-green-700 dark:text-green-100 dark:border-green-600 cursor-pointer';
                    iconClass = 'fas fa-check';
                    statusText = 'Available';
                } else {
                    buttonClass = 'bg-gray-100 text-gray-600 border-gray-300 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600';
                    iconClass = 'fas fa-clock';
                    statusText = 'Unavailable';
                }
                
                slotsHTML += `
                    <button type="button" 
                            class="p-3 text-sm border-2 rounded-lg transition-all duration-200 ${buttonClass} flex flex-col items-center space-y-1"
                            ${slot.available && !slot.booked ? `onclick="selectTimeSlot('${slot.time}')"` : 'disabled'}
                            title="${statusText} - ${slot.time} (${currentDuration} min duration)">
                        <i class="${iconClass} text-xs"></i>
                        <span class="font-medium">${slot.time}</span>
                        <span class="text-xs opacity-75">${statusText}</span>
                    </button>
                `;
            });
            
            if (slots.length === 0) {
                slotsHTML = `
                    <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-8">
                        <i class="fas fa-calendar-times text-2xl mb-2"></i>
                        <div>No available slots for selected date</div>
                        <div class="text-xs mt-1">Doctor may be unavailable or fully booked</div>
                    </div>
                `;
            }
            
            timeSlotsContainer.innerHTML = slotsHTML;
        }

        // Check if a time slot is available
        function isSlotAvailable(startMinutes, duration) {
            const endMinutes = startMinutes + duration;
            
            // Check against all booked slots
            for (let bookedSlot of bookedSlots) {
                const bookedStart = timeToMinutes(bookedSlot.time);
                const bookedEnd = bookedStart + bookedSlot.duration;
                
                // Check for overlap
                if ((startMinutes < bookedEnd) && (endMinutes > bookedStart)) {
                    return false;
                }
            }
            
            return true;
        }

        // Select a time slot
        function selectTimeSlot(time) {
            // Remove previous selection
            document.querySelectorAll('#timeSlots button').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-blue-500', 'ring-opacity-50', 'transform', 'scale-105');
                btn.classList.remove('bg-blue-100', 'dark:bg-blue-800', 'border-blue-400');
            });
            
            // Add selection to clicked button
            const clickedButton = event.target.closest('button');
            clickedButton.classList.add('ring-4', 'ring-blue-500', 'ring-opacity-50', 'transform', 'scale-105');
            clickedButton.classList.add('bg-blue-100', 'dark:bg-blue-800', 'border-blue-400');
            
            // Set selected time
            document.getElementById('selectedTime').value = time;
            
            // Show confirmation
            showNotification(`Selected time slot: ${time} (${currentDuration} minutes)`, 'success');
        }

        // Utility functions
        function timeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        function minutesToTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        }
    </script>

    <!-- Book Appointment Modal -->
    <div id="bookAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Book Appointment</h3>
                    <button onclick="closeBookAppointmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column - Form -->
                        <div class="space-y-4">
                            <form id="bookAppointmentForm">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor</label>
                                    <select name="doctor_id" id="doctorSelect" required onchange="loadDoctorAvailability()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Doctor</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                                    <input type="date" name="appointment_date" id="appointmentDate" required min="<?php echo date('Y-m-d'); ?>" onchange="loadTimeSlots()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available Time Slots</label>
                                    <div id="timeSlots" class="grid grid-cols-3 gap-2 min-h-[100px] p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                        <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-4">
                                            Select a doctor and date to view available slots
                                        </div>
                                    </div>
                                    <input type="hidden" name="appointment_time" id="selectedTime" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Appointment Type & Duration</label>
                                    <select name="appointment_type" id="appointmentType" onchange="updateDuration()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Appointment Type</option>
                                        <option value="consultation" data-duration="30">Consultation (30 min)</option>
                                        <option value="follow-up" data-duration="15">Follow-up (15 min)</option>
                                        <option value="routine-checkup" data-duration="20">Routine Checkup (20 min)</option>
                                        <option value="emergency" data-duration="45">Emergency (45 min)</option>
                                        <option value="procedure" data-duration="60">Minor Procedure (60 min)</option>
                                        <option value="therapy" data-duration="45">Therapy Session (45 min)</option>
                                        <option value="custom" data-duration="0">Custom Duration</option>
                                    </select>
                                </div>
                                
                                <div id="customDurationDiv" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Duration (minutes)</label>
                                    <select name="custom_duration" id="customDuration" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option value="10">10 minutes</option>
                                        <option value="15">15 minutes</option>
                                        <option value="20">20 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60">60 minutes</option>
                                        <option value="90">90 minutes</option>
                                        <option value="120">120 minutes</option>
                                    </select>
                                </div>
                                
                                <div id="durationInfo" class="hidden">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                                            <span class="text-sm text-blue-700 dark:text-blue-300">
                                                Duration: <span id="selectedDuration">0</span> minutes
                                            </span>
                                        </div>
                                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                            This will affect available time slots
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason for Visit</label>
                                    <input type="text" name="reason" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Brief reason for appointment">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Any additional notes..."></textarea>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Right Column - Doctor Info & Availability Chart -->
                        <div class="space-y-4">
                            <div id="doctorInfo" class="hidden">
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Doctor Information</h4>
                                    <div id="doctorDetails" class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                        <!-- Doctor details will be populated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Weekly Availability Chart -->
                            <div id="availabilityChart" class="hidden">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Weekly Availability</h4>
                                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <div id="weeklyChart" class="grid grid-cols-7 gap-1 text-xs">
                                        <!-- Weekly chart will be populated here -->
                                    </div>
                                    <div class="flex justify-center mt-4 space-x-4 text-xs">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                                            <span class="text-gray-600 dark:text-gray-400">Available</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                                            <span class="text-gray-600 dark:text-gray-400">Booked</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-gray-300 rounded mr-1"></div>
                                            <span class="text-gray-600 dark:text-gray-400">Unavailable</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
                        <button type="button" onclick="closeBookAppointmentModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button type="button" onclick="submitBookAppointment()" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Book Appointment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>