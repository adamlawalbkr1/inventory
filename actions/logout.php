<?php
// actions/logout.php
session_start();
require_once '../config/app.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login');
exit();
?>
