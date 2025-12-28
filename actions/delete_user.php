<?php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

// RBAC: Admin only
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        redirect('users?error=You cannot delete your own account.');
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            redirect('users?success=User deleted successfully.');
        } else {
            redirect('users?error=Failed to delete user.');
        }
    } catch (PDOException $e) {
        redirect('users?error=Database error: ' . $e->getMessage());
    }
} else {
    redirect('users');
}
?>
