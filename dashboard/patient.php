<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole('patient');

require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();

$user = $auth->getCurrentUser();
$patientId = $_SESSION['patient_id'];
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $user['theme'] === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Hospital CRM</title>
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
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-injured text-white"></i>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hospital CRM</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Patient Portal</p>
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
            <a href="#" onclick="showSection('book-appointment', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-plus-circle mr-3"></i>Book Appointment
            </a>
            <a href="#" onclick="showSection('vitals', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-heartbeat mr-3"></i>My Vitals
            </a>
            <a href="#" onclick="showSection('prescriptions', this)" class="nav-link flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-prescription-bottle-alt mr-3"></i>My Prescriptions
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
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                            </div>
                            <span class="hidden md:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
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
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Appointments</p>
                                <p id="totalAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-clock text-green-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming</p>
                                <p id="upcomingAppointments" class="text-2xl font-semibold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-user-md text-purple-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">My Doctor</p>
                                <p id="assignedDoctor" class="text-sm font-semibold text-gray-900 dark:text-white">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                                <i class="fas fa-heartbeat text-yellow-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Vitals</p>
                                <p id="lastVitalsDate" class="text-sm font-semibold text-gray-900 dark:text-white">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Appointments -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Appointments</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody id="upcomingAppointmentsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Book Appointment Section -->
            <div id="book-appointmentSection" class="section hidden">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Book New Appointment</h2>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <form id="bookAppointmentForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Doctor</label>
                                <select id="doctorSelect" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Choose a doctor...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Appointment Date</label>
                                <input type="date" id="appointmentDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available Time Slots</label>
                                <div id="timeSlots" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                                    <!-- Time slots will be populated here -->
                                </div>
                                <input type="hidden" id="selectedTime" name="selectedTime">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Appointment Type</label>
                                <select id="appointmentType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="consultation">Consultation</option>
                                    <option value="follow_up">Follow-up</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="routine_checkup">Routine Checkup</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Visit</label>
                                <textarea id="appointmentReason" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Please describe your symptoms or reason for the visit..."></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Appointments Section -->
            <div id="appointmentsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Appointments</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="allAppointmentsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Prescriptions Section -->
            <div id="prescriptionsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Prescriptions</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prescription #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diagnosis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientPrescriptionsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div id="vitalsSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Vitals</h2>
                <!-- Vitals content here -->
            </div>
            
            <div id="profileSection" class="section hidden">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">My Profile</h2>
                <!-- Profile content here -->
            </div>
        </main>
    </div>
    
    <script>
        // Global variables
        let currentSection = 'dashboard';
        const patientId = <?php echo $patientId; ?>;
        let selectedTimeSlot = null;
        
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
                link.classList.remove('active', 'bg-purple-500', 'text-white');
                link.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            // Update clicked element
            if (element) {
                element.classList.add('active', 'bg-purple-500', 'text-white');
                element.classList.remove('text-gray-700', 'dark:text-gray-300');
            }
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'appointments': 'My Appointments',
                'book-appointment': 'Book Appointment',
                'vitals': 'My Vitals',
                'prescriptions': 'My Prescriptions',
                'profile': 'Profile'
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
                case 'book-appointment':
                    loadDoctors();
                    break;
                case 'appointments':
                    loadAllAppointments();
                    break;
                case 'prescriptions':
                    loadPatientPrescriptions();
                    break;
            }
        }
        
        // Dashboard data loading
        async function loadDashboardData() {
            try {
                const response = await fetch('../handlers/patient_stats.php');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('totalAppointments').textContent = result.data.total_appointments;
                    document.getElementById('upcomingAppointments').textContent = result.data.upcoming_appointments;
                    document.getElementById('assignedDoctor').textContent = result.data.assigned_doctor || 'Not assigned';
                    document.getElementById('lastVitalsDate').textContent = result.data.last_vitals_date || 'No records';
                    
                    // Load upcoming appointments
                    const tbody = document.getElementById('upcomingAppointmentsTable');
                    tbody.innerHTML = result.data.upcoming_appointments_list.map(apt => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_date} ${apt.appointment_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.doctor_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_type}</td>
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
        
        // Load doctors for appointment booking
        async function loadDoctors() {
            try {
                const response = await fetch('../handlers/get_doctors.php');
                const result = await response.json();
                
                if (result.success) {
                    const select = document.getElementById('doctorSelect');
                    select.innerHTML = '<option value="">Choose a doctor...</option>' +
                        result.data.map(doctor => 
                            `<option value="${doctor.doctor_id}">Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}</option>`
                        ).join('');
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }
        
        // Load time slots when doctor and date are selected
        async function loadTimeSlots() {
            const doctorId = document.getElementById('doctorSelect').value;
            const date = document.getElementById('appointmentDate').value;
            
            if (!doctorId || !date) {
                document.getElementById('timeSlots').innerHTML = '';
                return;
            }
            
            try {
                const response = await fetch(`../handlers/get_time_slots.php?doctor_id=${doctorId}&date=${date}`);
                const result = await response.json();
                
                if (result.success) {
                    const container = document.getElementById('timeSlots');
                    container.innerHTML = result.data.map(slot => `
                        <button type="button" 
                                onclick="selectTimeSlot('${slot.time}', this)" 
                                class="time-slot px-3 py-2 text-sm border rounded-lg transition-colors duration-200 ${slot.available ? 'border-gray-300 hover:border-purple-500 hover:bg-purple-50 dark:border-gray-600 dark:hover:border-purple-400 dark:hover:bg-purple-900/20' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800'}"
                                ${!slot.available ? 'disabled' : ''}>
                            ${slot.time}
                        </button>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
            }
        }
        
        // Select time slot
        function selectTimeSlot(time, button) {
            // Remove previous selection
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('bg-purple-500', 'text-white', 'border-purple-500');
            });
            
            // Add selection to clicked button
            button.classList.add('bg-purple-500', 'text-white', 'border-purple-500');
            
            // Store selected time
            selectedTimeSlot = time;
            document.getElementById('selectedTime').value = time;
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
        
        // Load all appointments
        async function loadAllAppointments() {
            try {
                const response = await fetch('../handlers/patient_appointments.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('allAppointmentsTable');
                    tbody.innerHTML = result.data.map(apt => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_date} ${apt.appointment_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.doctor_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${apt.appointment_type}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(apt.status)}">${apt.status}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="viewAppointmentDetails(${apt.id})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${apt.status === 'completed' && apt.diagnosis ? `<button onclick="viewPrescription(${apt.id})" class="text-green-600 hover:text-green-800" title="View Prescription"><i class="fas fa-prescription-bottle-alt"></i></button>` : ''}
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
            }
        }
        
        // Load patient prescriptions
        async function loadPatientPrescriptions() {
            try {
                const response = await fetch('../handlers/prescriptions.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('patientPrescriptionsTable');
                    tbody.innerHTML = result.data.map(prescription => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_number}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.doctor_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${prescription.prescription_date}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${prescription.diagnosis || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${prescription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${prescription.status}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="viewPrescriptionDetails(${prescription.id})" class="text-blue-600 hover:text-blue-800" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="printPrescription(${prescription.id})" class="text-green-600 hover:text-green-800" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
            }
        }
        
        // View prescription details
        async function viewPrescriptionDetails(prescriptionId) {
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
                            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Prescription Details</h3>
                                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prescription #</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.prescription_number}</div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.prescription_date}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Doctor</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.doctor_name}</div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.specialization}</div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Diagnosis</label>
                                            <div class="text-gray-900 dark:text-white">${prescription.diagnosis || 'N/A'}</div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Medicines</label>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                ${medicinesHtml || '<p class="text-gray-500 dark:text-gray-400">No medicines prescribed</p>'}
                                            </div>
                                        </div>
                                        
                                        ${prescription.notes ? `
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Notes</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.notes}</div>
                                            </div>
                                        ` : ''}
                                        
                                        ${prescription.follow_up_date ? `
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Follow-up Date</label>
                                                <div class="text-gray-900 dark:text-white">${prescription.follow_up_date}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                    
                                    <div class="flex justify-end mt-6">
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
                alert('Error loading prescription details');
            }
        }
        
        // Print prescription (placeholder)
        function printPrescription(prescriptionId) {
            alert('Print functionality will be implemented');
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('appointmentDate').min = today;
            
            // Add event listeners for time slot loading
            document.getElementById('doctorSelect').addEventListener('change', loadTimeSlots);
            document.getElementById('appointmentDate').addEventListener('change', loadTimeSlots);
            
            // Book appointment form submission
            document.getElementById('bookAppointmentForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!selectedTimeSlot) {
                    alert('Please select a time slot');
                    return;
                }
                
                const formData = {
                    doctor_id: document.getElementById('doctorSelect').value,
                    appointment_date: document.getElementById('appointmentDate').value,
                    appointment_time: selectedTimeSlot,
                    appointment_type: document.getElementById('appointmentType').value,
                    reason: document.getElementById('appointmentReason').value
                };
                
                try {
                    const response = await fetch('../handlers/book_appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Appointment booked successfully!');
                        this.reset();
                        selectedTimeSlot = null;
                        document.getElementById('timeSlots').innerHTML = '';
                        showSection('appointments');
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error booking appointment:', error);
                    alert('Error booking appointment. Please try again.');
                }
            });
            
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