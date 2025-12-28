<?php
/**
 * config/app.php
 * Dynamic Environment Configuration
 */

// 1. Detect Protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// 2. Detect Host
$host = $_SERVER['HTTP_HOST'];

// 3. Detect Base Path (Robust method)
// Calculate the relative path from DOCUMENT_ROOT to the project root (dirname(__DIR__))
$project_root = str_replace('\\', '/', dirname(__DIR__));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$base_dir = str_ireplace($doc_root, '', $project_root);

// 4. Define APP_URL
define('APP_URL', rtrim($protocol . $host . $base_dir, '/'));

/**
 * URL Helper Function
 * Generates an absolute URL for the application
 * @param string $path
 * @return string
 */
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect Helper
 * @param string $path
 */
function redirect($path) {
    header("Location: " . url($path));
    exit();
}
?>
