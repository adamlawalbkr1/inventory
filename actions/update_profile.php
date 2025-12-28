<?php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 1. Validation: New passwords match
    if ($new_password !== $confirm_new_password) {
        redirect('profile?error=New passwords do not match');
        exit();
    }

    // 2. Verification: Check current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password'])) {
        // 3. Update: Hash and Save
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($update_stmt->execute([$hashed_password, $user_id])) {
            redirect('profile?success=1');
        } else {
            redirect('profile?error=Database error. Try again.');
        }
    } else {
        redirect('profile?error=Current password is incorrect');
    }
} else {
    redirect('profile');
}
?>
