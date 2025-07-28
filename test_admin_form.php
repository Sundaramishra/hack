<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Test Admin Form</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; }";
echo ".form-group { margin-bottom: 15px; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }";
echo "button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>Test Admin Form</h1>";

// Test form submission
if ($_POST) {
    echo "<h2>Form Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test database insertion
    try {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            echo "<h3>Database Test:</h3>";
            
            // Test if gender field is present
            if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                echo "✅ Gender field present: " . $_POST['gender'] . "<br>";
                
                // Test insertion
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, phone, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $username = $_POST['username'] ?? 'test_user';
                $email = $_POST['email'] ?? 'test@test.com';
                $password = password_hash($_POST['password'] ?? 'test123', PASSWORD_DEFAULT);
                $role = $_POST['role'] ?? 'patient';
                $first_name = $_POST['first_name'] ?? 'Test';
                $last_name = $_POST['last_name'] ?? 'User';
                $phone = $_POST['phone'] ?? '';
                $gender = $_POST['gender'] ?? 'male';
                
                $result = $stmt->execute([$username, $email, $password, $role, $first_name, $last_name, $phone, $gender]);
                
                if ($result) {
                    echo "✅ User added successfully!<br>";
                } else {
                    echo "❌ Error adding user<br>";
                }
            } else {
                echo "❌ Gender field missing or empty<br>";
            }
        } else {
            echo "❌ Database connection failed<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label>First Name:</label>";
echo "<input type='text' name='first_name' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Last Name:</label>";
echo "<input type='text' name='last_name' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Username:</label>";
echo "<input type='text' name='username' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Email:</label>";
echo "<input type='email' name='email' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Phone:</label>";
echo "<input type='tel' name='phone'>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Password:</label>";
echo "<input type='password' name='password' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Role:</label>";
echo "<select name='role' required>";
echo "<option value=''>Select Role</option>";
echo "<option value='admin'>Admin</option>";
echo "<option value='doctor'>Doctor</option>";
echo "<option value='patient'>Patient</option>";
echo "</select>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Gender:</label>";
echo "<select name='gender' required>";
echo "<option value=''>Select Gender</option>";
echo "<option value='male'>Male</option>";
echo "<option value='female'>Female</option>";
echo "<option value='other'>Other</option>";
echo "</select>";
echo "</div>";

echo "<button type='submit'>Test Add User</button>";
echo "</form>";

echo "</body>";
echo "</html>";
?>