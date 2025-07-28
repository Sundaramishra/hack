<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simple login check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: simple_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-hospital mr-2"></i>
                    Admin Dashboard
                </h1>
                <a href="simple_logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">
                        <i class="fas fa-user-md mr-2"></i>Doctors
                    </h3>
                    <p class="text-3xl font-bold text-blue-600">0</p>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">
                        <i class="fas fa-user-injured mr-2"></i>Patients
                    </h3>
                    <p class="text-3xl font-bold text-green-600">0</p>
                </div>
                
                <div class="bg-purple-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Appointments
                    </h3>
                    <p class="text-3xl font-bold text-purple-600">0</p>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button class="bg-blue-500 text-white p-4 rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Add Doctor
                    </button>
                    <button class="bg-green-500 text-white p-4 rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Add Patient
                    </button>
                    <button class="bg-purple-500 text-white p-4 rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                    </button>
                    <button class="bg-orange-500 text-white p-4 rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="fas fa-heartbeat mr-2"></i>Manage Vitals
                    </button>
                </div>
            </div>

            <div class="mt-8 bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                <h3 class="text-yellow-800 font-semibold mb-2">
                    <i class="fas fa-info-circle mr-2"></i>System Status
                </h3>
                <p class="text-yellow-700">✅ Simple admin dashboard loaded successfully!</p>
                <p class="text-yellow-700">✅ User: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                <p class="text-yellow-700">✅ Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'admin'); ?></p>
                <p class="text-yellow-700">✅ Session ID: <?php echo session_id(); ?></p>
            </div>
        </div>
    </div>
</body>
</html>