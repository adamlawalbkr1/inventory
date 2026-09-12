<?php
/**
 * config/app.php
 * Dynamic Environment Configuration
 */

const APP_TIMEZONE = 'Africa/Lagos';
date_default_timezone_set(APP_TIMEZONE);

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
 * Redirect Helper - Safely redirects with proper URL encoding
 * @param string $path - URL path with optional query params
 */
function redirect($path) {
    // Parse URL to separate path and query
    $parts = parse_url($path);
    $base_path = $parts['path'] ?? $path;
    
    // Build query string with proper encoding
    $query_string = '';
    if (isset($parts['query'])) {
        // Parse query string into array
        parse_str($parts['query'], $query_params);
        // Rebuild with proper encoding
        $query_string = '?' . http_build_query($query_params);
    }
    
    $final_url = url($base_path) . $query_string;
    header("Location: " . $final_url);
    exit();
}

function business_day_start($days_ago = 0) {
    $timezone = new DateTimeZone(APP_TIMEZONE);
    $day = new DateTimeImmutable('today', $timezone);
    return $day->modify("-$days_ago days");
}
?>
