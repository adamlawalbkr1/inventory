<?php
if (!defined('ROUTED')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// 1. Basic Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

// 2. Role-Based Access Control (RBAC) - Admin Only
// This file is included ONLY in pages that require ADMIN access.
if ($_SESSION['role'] !== 'admin') {
    die("<h3>Access Denied</h3><p>You do not have permission to view this page.</p><a href='index.php?page=dashboard'>Return to Dashboard</a>");
}
?>
