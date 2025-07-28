<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital CRM - Simple Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-hospital text-white text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Hospital CRM</h2>
                <p class="mt-2 text-sm text-gray-600">Simple Login Test</p>
            </div>
            
            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form method="POST" action="test_login.php" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter username"
                        >
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter password"
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-indigo-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 font-medium"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>
                
                <!-- Demo Credentials -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Demo Credentials:</h3>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div><strong>Admin:</strong> admin / Hospital@123</div>
                        <div><strong>Doctor:</strong> dr_sharma / Hospital@123</div>
                        <div><strong>Patient:</strong> patient_john / Hospital@123</div>
                    </div>
                </div>
                
                <!-- Debug Links -->
                <div class="mt-4 text-center">
                    <a href="debug.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-bug mr-1"></i>Debug Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>