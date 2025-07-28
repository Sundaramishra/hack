<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$error = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded login for testing
    if ($email === 'admin@hospital.com' && $password === 'password123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['first_name'] = 'System';
        $_SESSION['last_name'] = 'Administrator';
        
        header('Location: simple_admin.php');
        exit();
    } else {
        $error = 'Invalid email or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital CRM - Simple Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-500 rounded-full mb-4">
                    <i class="fas fa-hospital text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Hospital CRM</h1>
                <p class="text-gray-600">Simple Login (Testing Version)</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Welcome Back</h2>
                
                <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <span class="text-red-700"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your email address"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your password">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Test Credentials
                    </h3>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div><strong>Email:</strong> admin@hospital.com</div>
                        <div><strong>Password:</strong> password123</div>
                    </div>
                </div>

                <!-- Auto-fill button -->
                <div class="mt-4 text-center">
                    <button type="button" onclick="autoFill()" class="text-blue-500 hover:text-blue-700 text-sm">
                        <i class="fas fa-magic mr-1"></i>Auto-fill credentials
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="mt-6 bg-green-50 border border-green-200 p-4 rounded-lg text-center">
                <p class="text-green-700 text-sm">
                    <i class="fas fa-check-circle mr-2"></i>
                    Simple login system loaded successfully!
                </p>
            </div>
        </div>
    </div>

    <script>
        function autoFill() {
            document.getElementById('email').value = 'admin@hospital.com';
            document.getElementById('password').value = 'password123';
        }
    </script>
</body>
</html>