<?php
/**
 * AbStock Centralized Router (Front Controller)
 */
require_once 'config/app.php';
session_start();

// Define Base Path
define('ROOT_PATH', __DIR__);

// 1. Path-based Route Determination
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('index.php', '', $script_name);

// Remove base path from URI to get the relative route
$route = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));
$route = trim($route, '/');

// Default to dashboard
$page = $route ?: 'dashboard';
$public_pages = ['login', 'install'];

// 2. Authentication Protection
error_log("Index Trace: Page=$page, Session=" . (isset($_SESSION['user_id']) ? 'Set' : 'Null') . "\n", 3, "debug.log");
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    error_log("Index Trace: Redirecting to login\n", 3, "debug.log");
    redirect('login');
}

// 3. User Role Authorization
$admin_only_pages = ['inventory', 'users'];
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin' && in_array($page, $admin_only_pages)) {
    redirect('dashboard?error=Access Denied');
}

// 4. View Mapping
$view_file = ROOT_PATH . "/views/" . $page . ".php";

if (file_exists($view_file)) {
    define('ROUTED', true);
    include $view_file;
} else {

    // 404 - Not Found
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested page '$page' could not be found.</p>";
    exit();
}
?>
