<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Initialize variables
$username = '';
$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    // Default admin credentials
    $admin_username = 'connectvbind2022';
    $admin_password = 'VBind@2022Dec';
    
    // Verify credentials (Note: In a real application, you would use password_hash and password_verify)
    if ($username === $admin_username && $password === $admin_password) {
        // Set session variables
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        
        // Redirect to dashboard
        redirect('dashboard.php');
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vbind Marketing Agency</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        vbindWhite: '#FFFFFF',
                        vbindOrange: '#F44B12',
                        vbindGrey: '#2B2B2A',
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-md rounded-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-[#2B2B2A]">
                    <span class="text-[#F44B12]">V</span>bind
                </h1>
                <p class="text-gray-600">Admin Panel Login</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-6">
                    <label for="username" class="block text-[#2B2B2A] font-medium mb-2">Username</label>
                    <input type="text" name="username" id="username" class="form-input" value="<?php echo $username; ?>" required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-[#2B2B2A] font-medium mb-2">Password</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-[#F44B12] focus:ring-[#F44B12]">
                        <label for="remember" class="ml-2 block text-sm text-gray-600">Remember me</label>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-3 px-4 bg-[#F44B12] text-white font-medium rounded-md hover:bg-[#d43e0f] focus:outline-none focus:bg-[#d43e0f] transition duration-300">
                    Log In
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-[#F44B12] hover:underline text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
