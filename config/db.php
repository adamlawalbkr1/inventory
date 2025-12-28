<?php
// config/db.php

$host = 'localhost';
$user = 'root';     // Default XAMPP user
$pass = '';         // Default XAMPP password is empty
$dbname = 'inventory_system';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    // Set error mode to exception (Critical for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop everything
    die("Database Connection Failed: " . $e->getMessage());
}
?>
