<?php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

// RBAC
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // 1. Validation: Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirect('users?error=Username already exists');
        exit();
    }

    // 2. Hash & Insert
    // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$username, $password, $role])) {
        redirect('users?success=1');
    } else {
        redirect('users?error=System failure. Try again.');
    }
} else {
    redirect('users');
}
?>
