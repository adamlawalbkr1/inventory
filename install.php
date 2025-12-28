<?php
// install.php
require_once 'config/app.php';

// 1. Connect to MySQL Server (No Database selected yet)
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Starting Installation...</h3>";

    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS inventory_system");
    echo "✅ Database 'inventory_system' created/checked.<br>";

    // 3. Select Database
    $pdo->exec("USE inventory_system");

    // 4. Create Tables (Normalized Structure)

    // Table: Users
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'staff') DEFAULT 'staff',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_users);
    echo "✅ Table 'users' created.<br>";

    // Table: Categories
    $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
    $pdo->exec($sql_categories);
    echo "✅ Table 'categories' created.<br>";

    // Table: Products (With Expiry Date & Min Stock Alert)
    $sql_products = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(100) NOT NULL,
        cost_price DECIMAL(10,2) NOT NULL,
        selling_price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        min_stock_level INT NOT NULL DEFAULT 5,
        expiry_date DATE NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql_products);
    echo "✅ Table 'products' created.<br>";

    // Table: Sales (Receipt Header)
    $sql_sales = "CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        receipt_number VARCHAR(20) NOT NULL UNIQUE,
        total_amount DECIMAL(10,2) NOT NULL,
        sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql_sales);
    echo "✅ Table 'sales' created.<br>";

    // Table: Sale Items (Individual items in a receipt)
    $sql_sale_items = "CREATE TABLE IF NOT EXISTS sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price_at_sale DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $pdo->exec($sql_sale_items);
    echo "✅ Table 'sale_items' created.<br>";

    // 5. SEED DATA (The "First User" Problem)

    // Check if admin exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        // Create Default Admin
        // Password is 'password' (Hashed)
        $password = password_hash('password', PASSWORD_DEFAULT); 
        $sql = "INSERT INTO users (username, password, role) VALUES ('admin', '$password', 'admin')";
        $pdo->exec($sql);
        echo "🎉 <strong>Admin User Created!</strong><br>Username: <code>admin</code><br>Password: <code>password</code><br>";
    } else {
        echo "ℹ️ Admin user already exists.<br>";
    }

    // Seed Categories
    $pdo->exec("INSERT IGNORE INTO categories (name) VALUES ('Soft Drinks'), ('Alcohol'), ('Snacks')");
    echo "✅ Dummy Categories inserted.<br>";

    // Seed Products
    $pdo->exec("INSERT IGNORE INTO products (category_id, name, cost_price, selling_price, stock_quantity, min_stock_level) VALUES 
        (1, 'Coke 50cl', 150.00, 200.00, 50, 10),
        (1, 'Fanta 50cl', 150.00, 200.00, 2, 10), -- Low stock example
        (3, 'Gala Sausage', 100.00, 150.00, 100, 20)
    ");
    echo "✅ Dummy Products inserted.<br>";

    echo "<hr><h3>Installation Complete!</h3>";
    echo "<a href='" . url('login') . "'>Go to Login Page</a>";

} catch (PDOException $e) {
    die("Installation Failed: " . $e->getMessage());
}
?>
