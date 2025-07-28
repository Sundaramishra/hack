<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    $current_user = $auth->getCurrentUser();
    switch ($current_user['role']) {
        case 'admin':
            header('Location: dashboard/admin.php');
            break;
        case 'doctor':
            header('Location: dashboard/doctor.php');
            break;
        case 'patient':
            header('Location: dashboard/patient.php');
            break;
    }
    exit();
}

$error_message = '';

// Handle login
if ($_POST) {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($identifier) && !empty($password)) {
        $result = $auth->login($identifier, $password);
        
        if ($result['success']) {
            switch ($result['user']['role']) {
                case 'admin':
                    header('Location: dashboard/admin.php');
                    break;
                case 'doctor':
                    header('Location: dashboard/doctor.php');
                    break;
                case 'patient':
                    header('Location: dashboard/patient.php');
                    break;
            }
            exit();
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital CRM - Login</title>
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
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-blue-900 transition-colors duration-300">
    <!-- Theme Toggle -->
    <div class="absolute top-4 right-4">
        <button id="theme-toggle" class="p-2 rounded-lg bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-moon dark:hidden text-gray-600"></i>
            <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
        </button>
    </div>

    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-500 rounded-full mb-4">
                    <i class="fas fa-hospital text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Hospital CRM</h1>
                <p class="text-gray-600 dark:text-gray-300">Comprehensive Healthcare Management System</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 backdrop-blur-sm">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6 text-center">Welcome Back</h2>
                
                <?php if ($error_message): ?>
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <span class="text-red-700 dark:text-red-300"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-user mr-2"></i>Email or Username
                        </label>
                        <input type="text" id="identifier" name="identifier" required
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300"
                               placeholder="Enter your email or username"
                               value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300"
                                   placeholder="Enter your password">
                            <button type="button" id="toggle-password" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Demo Credentials
                    </h3>
                    <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                        <div><strong>Admin:</strong> admin@hospital.com or admin / password123</div>
                        <div><strong>Doctor:</strong> doctor@hospital.com or doctor / password123</div>
                        <div><strong>Patient:</strong> patient@hospital.com or patient / password123</div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-lg p-4">
                    <i class="fas fa-user-md text-primary-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Doctor Portal</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Manage patients & appointments</p>
                </div>
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-lg p-4">
                    <i class="fas fa-heartbeat text-red-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Patient Care</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Track vitals & prescriptions</p>
                </div>
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-lg p-4">
                    <i class="fas fa-cogs text-green-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Admin Control</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Complete system management</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.classList.toggle('dark', savedTheme === 'dark');
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Password toggle functionality
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = togglePassword.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Auto-fill demo credentials
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === '1') {
                document.getElementById('email').value = 'admin@hospital.com';
                document.getElementById('password').value = 'password123';
                e.preventDefault();
            }
        });
    </script>
</body>
</html>