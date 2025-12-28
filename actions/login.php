<?php
// actions/login.php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

// Check if data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        redirect('login?error=All fields are required');
        exit();
    }

    try {
        // Prepare selection statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            error_log("Login Debug: User found for '$username'. Stored Pass: '{$user['password']}', Input Pass: '$password'", 3, "../debug.log");
        } else {
            error_log("Login Debug: User '$username' NOT found in DB.", 3, "../debug.log");
        }

        if ($user && $password === $user['password']) {
            // Success: Set Session Variables
            error_log("Login Debug: Password match successful for '$username'.", 3, "../debug.log");
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to Dashboard
            redirect('dashboard');
            exit();
        } else {
            // Failure: Invalid Credentials
            error_log("Login Debug: Password mismatch or user not found for '$username'.", 3, "../debug.log");
            redirect('login?error=Invalid Credentials');
            exit();
        }

    } catch (PDOException $e) {
        // Log error (for developer) and show generic message (for user)
        error_log("Login Error: " . $e->getMessage());
        redirect('login?error=System Error. Try again later.');
        exit();
    }
} else {
    // If accessed directly without POST
    redirect('login');
    exit();
}
?>
