<?php
// Check if user is logged in
$is_login_page = (isset($page) && $page == 'login') || basename($_SERVER['PHP_SELF']) == 'login.php';

// Only check auth here if NOT routed via index.php (which handles it)
$bg_check = (!defined('ROUTED') && !isset($_SESSION['user_id']) && !$is_login_page);
error_log("Header Trace: Routed=" . (defined('ROUTED') ? 'Yes' : 'No') . ", IsLogin=$is_login_page, DoRedirect=" . ($bg_check ? 'Yes' : 'No') . "\n", 3, "debug.log");

if ($bg_check) {
    redirect('login');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Da'am Fast Food</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="d-flex">
