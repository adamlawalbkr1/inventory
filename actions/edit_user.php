<?php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

// RBAC: Admin only
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // 1. Basic validation
    if (empty($user_id) || empty($username) || empty($role)) {
        redirect('users?error=All fields except password are required.');
        exit();
    }

    try {
        // 2. Prepare the base query
        $query = "UPDATE users SET username = ?, role = ?";
        $params = [$username, $role];

        // 3. Password Check
        if (!empty($password)) {
            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $password;
        }

        // 4. Finalize query
        $query .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute($params)) {
            redirect('users?success=User updated successfully.');
        } else {
            redirect('users?error=System failure. Try again.');
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            redirect('users?error=Username already exists.');
        } else {
            redirect('users?error=Database error: ' . $e->getMessage());
        }
    }
} else {
    redirect('users');
}
?>
