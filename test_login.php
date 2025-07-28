<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Test Results</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
    echo "<p><strong>Password:</strong> " . str_repeat('*', strlen($password)) . "</p>";
    
    // Test database connection
    try {
        $conn = new PDO("mysql:host=localhost;dbname=hospital_crm", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✅ Database connected successfully!</p>";
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p style='color: blue;'>✅ User found: " . $user['first_name'] . " " . $user['last_name'] . " (Role: " . $user['role'] . ")</p>";
            
            // Check password
            if (password_verify($password, $user['password_hash'])) {
                echo "<p style='color: green;'>✅ Password correct!</p>";
                echo "<p style='color: green;'>🎉 Login would be successful!</p>";
                
                // Show redirect info
                echo "<p><strong>Would redirect to:</strong> dashboard/" . $user['role'] . ".php</p>";
                
                echo "<h2>Available Actions:</h2>";
                echo "<a href='dashboard/" . $user['role'] . ".php' style='padding: 10px; background: green; color: white; text-decoration: none; margin: 5px;'>Go to " . ucfirst($user['role']) . " Dashboard</a>";
                
            } else {
                echo "<p style='color: red;'>❌ Password incorrect!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found!</p>";
        }
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: orange;'>No POST data received</p>";
}

echo "<br><br>";
echo "<a href='simple_login.php' style='padding: 10px; background: blue; color: white; text-decoration: none;'>Back to Login</a>";
echo "<a href='debug.php' style='padding: 10px; background: orange; color: white; text-decoration: none; margin-left: 10px;'>Debug Page</a>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f5f5f5;
}
</style>