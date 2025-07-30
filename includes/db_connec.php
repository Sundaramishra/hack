<?php
$host = 'localhost';
$user = 'your_db_user';
$pass = 'your_db_password';
$db   = 'your_db_name';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Database connection error: ' . $conn->connect_error);
}
?>