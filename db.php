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

    // Upgrade existing installations for pending and fulfilled orders.
    try {
        $pdo->exec("ALTER TABLE sales ADD COLUMN status ENUM('pending', 'fulfilled') NOT NULL DEFAULT 'fulfilled'");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }

    try {
        $pdo->exec("ALTER TABLE sales ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }

    try {
        $pdo->exec("ALTER TABLE sales ADD COLUMN completed_at DATETIME NULL");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }

    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN product_type ENUM('standard', 'prepared') NOT NULL DEFAULT 'standard'");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }

    $upgrades = [
        "ALTER TABLE sales ADD COLUMN order_description VARCHAR(500) NULL",
        "ALTER TABLE sales ADD COLUMN plate_required TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE sales ADD COLUMN plate_returned TINYINT(1) NOT NULL DEFAULT 1",
        "ALTER TABLE sales ADD COLUMN payment_method ENUM('cash', 'transfer', 'pos', 'other') NOT NULL DEFAULT 'cash'",
        "ALTER TABLE sales ADD COLUMN payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'paid'",
        "ALTER TABLE sale_items ADD COLUMN item_name VARCHAR(255) NULL",
        "ALTER TABLE sale_items ADD COLUMN is_custom TINYINT(1) NOT NULL DEFAULT 0"
    ];
    foreach ($upgrades as $upgrade) {
        try {
            $pdo->exec($upgrade);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }
    }

    try {
        $pdo->exec("UPDATE sales SET created_at = COALESCE(created_at, sale_date) WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
        $pdo->exec("UPDATE sales SET completed_at = COALESCE(completed_at, sale_date) WHERE status = 'fulfilled' AND (completed_at IS NULL OR completed_at = '0000-00-00 00:00:00')");
    } catch (PDOException $e) {
        error_log('Sales timestamp backfill warning: ' . $e->getMessage());
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS raw_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        unit VARCHAR(30) NOT NULL,
        quantity_in_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
        cost_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0,
        minimum_stock_level DECIMAL(12,3) NOT NULL DEFAULT 0
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS recipes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL UNIQUE,
        servings_produced INT NOT NULL DEFAULT 1,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS recipe_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipe_id INT NOT NULL,
        raw_material_id INT NOT NULL,
        quantity_required DECIMAL(12,3) NOT NULL,
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
        FOREIGN KEY (raw_material_id) REFERENCES raw_materials(id) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NULL,
        raw_material_id INT NULL,
        movement_type ENUM('purchase', 'sale', 'consumption', 'adjustment') NOT NULL,
        quantity DECIMAL(12,3) NOT NULL,
        unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
        reason VARCHAR(255) NULL,
        sale_id INT NULL,
        user_id INT NULL,
        movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
        FOREIGN KEY (raw_material_id) REFERENCES raw_materials(id) ON DELETE SET NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

} catch (PDOException $e) {
    // If connection fails, stop everything
    die("Database Connection Failed: " . $e->getMessage());
}
?>
