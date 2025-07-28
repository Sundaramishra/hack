<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Handle login
if ($_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $user = getUserByEmail($email);
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Please fill all fields';
    }
}

// Handle registration
if ($_POST['action'] === 'register') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($name && $email && $password && $confirmPassword) {
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (!validatePassword($password)) {
            $error = 'Password must be at least 8 characters with uppercase, lowercase, number and special character';
        } elseif (getUserByEmail($email)) {
            $error = 'Email already exists';
        } else {
            if (createUser($name, $email, $password)) {
                $success = 'Account created successfully! Please login.';
            } else {
                $error = 'Failed to create account';
            }
        }
    } else {
        $error = 'Please fill all fields';
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milo Meet - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-video"></i>
            <h1>Milo Meet</h1>
            <p>Connect with anyone, anywhere</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <!-- Login Form -->
            <form id="loginForm" class="auth-form active" method="POST">
                <input type="hidden" name="action" value="login">
                <h2>Sign In</h2>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
                
                <p class="switch-form">
                    Don't have an account? 
                    <a href="#" onclick="switchForm('register')">Sign Up</a>
                </p>
            </form>

            <!-- Registration Form -->
            <form id="registerForm" class="auth-form" method="POST">
                <input type="hidden" name="action" value="register">
                <h2>Create Account</h2>
                
                <div class="form-group">
                    <label for="reg_name">Full Name</label>
                    <input type="text" id="reg_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" required>
                    <small class="password-hint">
                        Password must contain: 8+ characters, uppercase, lowercase, number, special character
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
                
                <p class="switch-form">
                    Already have an account? 
                    <a href="#" onclick="switchForm('login')">Sign In</a>
                </p>
            </form>
        </div>

        <!-- Guest Join Option -->
        <div class="guest-join">
            <div class="divider">
                <span>OR</span>
            </div>
            <h3>Join as Guest</h3>
            <p>Join a meeting without creating an account</p>
            <div class="guest-form">
                <input type="text" id="guestMeetingId" placeholder="Enter meeting ID" class="input-field">
                <input type="text" id="guestName" placeholder="Your name" class="input-field">
                <button class="btn btn-secondary" onclick="joinAsGuest()">Join Meeting</button>
            </div>
        </div>
    </div>

    <script>
        function switchForm(formType) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (formType === 'register') {
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
            } else {
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
            }
        }

        function joinAsGuest() {
            const meetingId = document.getElementById('guestMeetingId').value.trim();
            const guestName = document.getElementById('guestName').value.trim();
            
            if (!meetingId || !guestName) {
                alert('Please enter both meeting ID and your name');
                return;
            }
            
            // Redirect to meeting with guest parameters
            window.location.href = `meeting.php?id=${meetingId}&guest=${encodeURIComponent(guestName)}`;
        }

        // Password strength indicator
        document.getElementById('reg_password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[@$!%*?&]/.test(password)
            };
            
            // Visual feedback could be added here
        });
    </script>
</body>
</html>