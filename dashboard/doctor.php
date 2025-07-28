<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole('doctor');

require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();

$user = $auth->getCurrentUser();
$doctorId = $_SESSION['doctor_id'];
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $user['theme'] === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Hospital CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hospital CRM</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Doctor Panel</p>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <nav class="mt-6">
            <a href="#" onclick="showSection('dashboard', this)" class="nav-link active flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </a>
            <a href="#" onclick="showSection('appointments', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i>My Appointments
            </a>
            <a href="#" onclick="showSection('patients', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>My Patients
            </a>
            <a href="#" onclick="showSection('prescriptions', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>Prescriptions
            </a>
            <a href="#" onclick="showSection('profile', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-user mr-3"></i>Profile
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
                            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                            </div>
                            <span class="hidden md:block">Dr. <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50">
                            <a href="#" onclick="showSection('profile')" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
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
                                <i class="fas fa-calendar-check text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Appointments</p>
                                <p id="todayAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-users text-green-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">My Patients</p>
                                <p id="totalPatients" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-clock text-purple-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                                <p id="pendingAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                                <i class="fas fa-check-circle text-yellow-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed</p>
                                <p id="completedAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Today's Schedule -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today's Schedule</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="todaySchedule" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Appointments Section -->
            <div id="appointmentsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">My Appointments</h2>
                    <div class="flex space-x-2">
                        <button onclick="filterAppointments('all')" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 text-sm">
                            All
                        </button>
                        <button onclick="filterAppointments('today')" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200 text-sm">
                            Today
                        </button>
                        <button onclick="filterAppointments('upcoming')" class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors duration-200 text-sm">
                            Upcoming
                        </button>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
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
            
            <!-- Patients Section -->
            <div id="patientsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">My Patients</h2>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Blood Group</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Visit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <!-- Prescriptions Section -->
            <div id="prescriptionsSection" class="section hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Prescriptions Management</h2>
                    <button onclick="openPrescriptionModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>New Prescription
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prescription #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diagnosis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="prescriptionsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div id="profileSection" class="section hidden">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Profile</h2>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <form id="profileForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                                    <input type="text" id="profileFirstName" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                                    <input type="text" id="profileLastName" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                <input type="email" id="profileEmail" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Specialization</label>
                                <input type="text" id="profileSpecialization" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available From</label>
                                    <input type="time" id="profileAvailableFrom" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available To</label>
                                    <input type="time" id="profileAvailableTo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Prescription Modal -->
    <div id="prescriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Create Prescription</h3>
                    <button onclick="closePrescriptionModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="prescriptionForm" class="space-y-6">
                    <input type="hidden" id="prescriptionAppointmentId">
                    <input type="hidden" id="prescriptionPatientId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Patient</label>
                            <input type="text" id="prescriptionPatientName" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Appointment Date</label>
                            <input type="text" id="prescriptionAppointmentDate" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Diagnosis</label>
                        <textarea id="prescriptionDiagnosis" rows="3" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Enter diagnosis..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Medicines</label>
                        <div id="medicinesContainer" class="space-y-4">
                            <!-- Medicine entries will be added here -->
                        </div>
                        <button type="button" onclick="addMedicine()" class="mt-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                            <i class="fas fa-plus mr-1"></i>Add Medicine
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Follow-up Date</label>
                            <input type="date" id="prescriptionFollowUp" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Notes</label>
                        <textarea id="prescriptionNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Additional instructions or notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closePrescriptionModal()" class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200">Create Prescription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Global variables
        let currentSection = 'dashboard';
        const doctorId = <?php echo $doctorId; ?>;
        
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
        function showSection(sectionName, element) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName + 'Section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            // Update nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active', 'bg-green-500', 'text-white');
                link.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            // Update clicked element
            if (element) {
                element.classList.add('active', 'bg-green-500', 'text-white');
                element.classList.remove('text-gray-700', 'dark:text-gray-300');
            }
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'appointments': 'My Appointments',
                'patients': 'My Patients',
                'prescriptions': 'Prescriptions',
                'profile': 'Profile'
            };
            
            document.getElementById('pageTitle').textContent = titles[sectionName] || 'Dashboard';
            currentSection = sectionName;
            
            // Load section-specific data
            if (sectionName === 'appointments') {
                loadDoctorAppointments();
            }
            
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
                case 'appointments':
                    loadAppointments();
                    break;
                case 'patients':
                    loadPatients();
                    break;
                case 'prescriptions':
                    loadPrescriptions();
                    break;
                case 'profile':
                    loadProfile();
                    break;
            }
        }
        
        // Dashboard data loading
        async function loadDashboardData() {
            try {
                const response = await fetch('../handlers/doctor_stats.php');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('todayAppointments').textContent = result.data.today_appointments;
                    document.getElementById('totalPatients').textContent = result.data.total_patients;
                    document.getElementById('pendingAppointments').textContent = result.data.pending_appointments;
                    document.getElementById('completedAppointments').textContent = result.data.completed_appointments;
                    
                    // Load today's schedule
                    const tbody = document.getElementById('todaySchedule');
                    tbody.innerHTML = result.data.today_schedule.map(apt => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.patient_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_type}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(apt.status)}">${apt.status}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="viewAppointment(${apt.id})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="completeAppointment(${apt.id})" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
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
        
        // Prescription functions
        let medicineCounter = 0;
        
        function loadPrescriptions() {
            fetch('../handlers/prescriptions.php')
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const tbody = document.getElementById('prescriptionsTable');
                        tbody.innerHTML = result.data.map(prescription => `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_number}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.patient_name}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_date}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.diagnosis || 'N/A'}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${prescription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${prescription.status}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <button onclick="viewPrescription(${prescription.id})" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="printPrescription(${prescription.id})" class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('');
                    }
                })
                .catch(error => console.error('Error loading prescriptions:', error));
        }
        
        function openPrescriptionModal(appointmentId = null, patientId = null, patientName = '', appointmentDate = '') {
            document.getElementById('prescriptionModal').classList.remove('hidden');
            document.getElementById('prescriptionAppointmentId').value = appointmentId || '';
            document.getElementById('prescriptionPatientId').value = patientId || '';
            document.getElementById('prescriptionPatientName').value = patientName;
            document.getElementById('prescriptionAppointmentDate').value = appointmentDate;
            
            // Clear form
            document.getElementById('prescriptionDiagnosis').value = '';
            document.getElementById('prescriptionNotes').value = '';
            document.getElementById('prescriptionFollowUp').value = '';
            document.getElementById('medicinesContainer').innerHTML = '';
            medicineCounter = 0;
            
            // Add first medicine row
            addMedicine();
        }
        
        function closePrescriptionModal() {
            document.getElementById('prescriptionModal').classList.add('hidden');
        }
        
        function addMedicine() {
            medicineCounter++;
            const container = document.getElementById('medicinesContainer');
            const medicineDiv = document.createElement('div');
            medicineDiv.className = 'medicine-entry p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            medicineDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medicine Name</label>
                        <input type="text" name="medicine_name_${medicineCounter}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Paracetamol">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosage</label>
                        <input type="text" name="medicine_dosage_${medicineCounter}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., 500mg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequency</label>
                        <select name="medicine_frequency_${medicineCounter}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select frequency</option>
                            <option value="Once daily">Once daily</option>
                            <option value="Twice daily">Twice daily</option>
                            <option value="Three times daily">Three times daily</option>
                            <option value="Four times daily">Four times daily</option>
                            <option value="As needed">As needed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration</label>
                        <input type="text" name="medicine_duration_${medicineCounter}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., 7 days">
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                        <input type="number" name="medicine_quantity_${medicineCounter}" min="1" value="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instructions</label>
                        <input type="text" name="medicine_instructions_${medicineCounter}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Take with food">
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <button type="button" onclick="removeMedicine(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
            `;
            container.appendChild(medicineDiv);
        }
        
        function removeMedicine(button) {
            button.closest('.medicine-entry').remove();
        }
        
        function createPrescriptionFromAppointment(appointmentId, patientId, patientName, appointmentDate) {
            openPrescriptionModal(appointmentId, patientId, patientName, appointmentDate);
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            
            // Prescription form submission
            document.getElementById('prescriptionForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const medicines = [];
                
                // Collect medicine data
                for (let i = 1; i <= medicineCounter; i++) {
                    const name = formData.get(`medicine_name_${i}`);
                    const dosage = formData.get(`medicine_dosage_${i}`);
                    const frequency = formData.get(`medicine_frequency_${i}`);
                    const duration = formData.get(`medicine_duration_${i}`);
                    const quantity = formData.get(`medicine_quantity_${i}`);
                    const instructions = formData.get(`medicine_instructions_${i}`);
                    
                    if (name && dosage && frequency && duration) {
                        medicines.push({
                            name: name,
                            dosage: dosage,
                            frequency: frequency,
                            duration: duration,
                            quantity: parseInt(quantity) || 1,
                            instructions: instructions
                        });
                    }
                }
                
                if (medicines.length === 0) {
                    alert('Please add at least one medicine');
                    return;
                }
                
                const prescriptionData = {
                    appointment_id: document.getElementById('prescriptionAppointmentId').value,
                    patient_id: document.getElementById('prescriptionPatientId').value,
                    diagnosis: document.getElementById('prescriptionDiagnosis').value,
                    notes: document.getElementById('prescriptionNotes').value,
                    follow_up_date: document.getElementById('prescriptionFollowUp').value || null,
                    medicines: medicines
                };
                
                try {
                    const response = await fetch('../handlers/prescriptions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(prescriptionData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Prescription created successfully!');
                        closePrescriptionModal();
                        loadPrescriptions();
                        loadDashboardData(); // Refresh dashboard stats
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error creating prescription:', error);
                    alert('Error creating prescription. Please try again.');
                }
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#userMenu') && !event.target.closest('button[onclick="toggleUserMenu()"]')) {
                    document.getElementById('userMenu').classList.add('hidden');
                }
            });
        });

        // Doctor Appointments Functions
        let currentAppointmentFilter = 'all';
        
        async function loadDoctorAppointments(filter = 'all') {
            try {
                const response = await fetch('../handlers/appointments.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    let appointments = result.data;
                    
                    // Filter appointments based on selection
                    const today = new Date().toISOString().split('T')[0];
                    const now = new Date();
                    
                    if (filter === 'today') {
                        appointments = appointments.filter(apt => apt.appointment_date === today);
                    } else if (filter === 'upcoming') {
                        appointments = appointments.filter(apt => {
                            const aptDateTime = new Date(apt.appointment_date + ' ' + apt.appointment_time);
                            return aptDateTime > now;
                        });
                    }
                    
                    displayDoctorAppointments(appointments);
                } else {
                    showError(result.message || 'Error loading appointments');
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
                showError('Error loading appointments');
            }
        }
        
        function displayDoctorAppointments(appointments) {
            const tbody = document.getElementById('appointmentsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (appointments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>No appointments found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            appointments.forEach(appointment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${appointment.patient_name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${appointment.patient_code || ''}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">${formatDate(appointment.appointment_date)}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">${formatTime(appointment.appointment_time)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            ${appointment.appointment_type || 'Consultation'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(appointment.status)}">
                            ${appointment.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${appointment.status === 'scheduled' ? `
                                <button onclick="markComplete(${appointment.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Mark Complete">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="openPrescriptionModal(${appointment.id}, ${appointment.patient_id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Add Prescription">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                </button>
                            ` : ''}
                            <button onclick="viewAppointmentDetails(${appointment.id})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function filterAppointments(filter) {
            currentAppointmentFilter = filter;
            
            // Update button styles
            document.querySelectorAll('[onclick^="filterAppointments"]').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'bg-green-500', 'bg-purple-500');
                btn.classList.add('bg-gray-500');
            });
            
            event.target.classList.remove('bg-gray-500');
            if (filter === 'all') {
                event.target.classList.add('bg-blue-500');
            } else if (filter === 'today') {
                event.target.classList.add('bg-green-500');
            } else if (filter === 'upcoming') {
                event.target.classList.add('bg-purple-500');
            }
            
            loadDoctorAppointments(filter);
        }
        
        async function markComplete(appointmentId) {
            try {
                const response = await fetch('../handlers/appointments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        appointment_id: appointmentId,
                        status: 'completed'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Appointment marked as completed');
                    loadDoctorAppointments(currentAppointmentFilter);
                } else {
                    showError(result.message || 'Error updating appointment');
                }
            } catch (error) {
                console.error('Error updating appointment:', error);
                showError('Error updating appointment');
            }
        }
        
        function viewAppointmentDetails(appointmentId) {
            showInfo('Appointment details functionality will be implemented');
        }
        
        // Utility functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        
        function formatTime(timeString) {
            const time = new Date('2000-01-01 ' + timeString);
            return time.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        }
        
        function getStatusColor(status) {
            const colors = {
                'scheduled': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'no_show': 'bg-gray-100 text-gray-800',
                'rescheduled': 'bg-yellow-100 text-yellow-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
    </script>
</body>
</html>