<?php
require_once 'includes/auth.php';

$auth = new Auth();

// If user is logged in, redirect to appropriate dashboard
if ($auth->isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: dashboard/$role.php");
    exit();
}

// Otherwise, redirect to login
header('Location: login.php');
exit();
?>