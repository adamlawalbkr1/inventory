<?php
// actions/register.php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

// Check if data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        redirect('register?error=All fields are required');
        exit();
    }

    if ($password !== $confirm_password) {
        redirect('register?error=Passwords do not match');
        exit();
    }

    try {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            redirect('register?error=Username already exists');
            exit();
        }

        // Hash Password (REMOVED)
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert User
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'staff')");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password
        ]);

        // Success
        redirect('login?success=Registration successful! Please login.');
        exit();

    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage(), 3, "../debug.log");
        redirect('register?error=System Error. Try again later.');
        exit();
    }
} else {
    redirect('register');
    exit();
}
?>
