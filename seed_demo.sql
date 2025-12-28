-- seed_demo.sql
-- Run this script to reset the database and populate it with professional demo data

SET FOREIGN_KEY_CHECKS = 0;

-- 1. CLEANUP & SCHEMA SETUP
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 2. CREATE TABLES

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff'
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT NOT NULL DEFAULT 5,
    expiry_date DATE DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price_at_sale DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS = 1;

-- 2. USERS: Create Admin and Staff (Password: 'password')
-- Hash for 'password': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, username, password, role) VALUES 
(1, 'admin', 'password', 'admin'),
(2, 'staff', 'password', 'staff');

-- 3. CATEGORIES: Create base categories
INSERT INTO categories (id, name) VALUES 
(1, 'Drinks'),
(2, 'Snacks'),
(3, 'Essentials'),
(4, 'Pharmaceuticals');

-- 4. PRODUCTS: Mixed items (High Stock, Low Stock, and Expiring)
INSERT INTO products (id, category_id, name, cost_price, selling_price, stock_quantity, min_stock_level, expiry_date) VALUES 
-- Drinks
(1, 1, 'Coke 50cl', 200.00, 300.00, 100, 10, DATE_ADD(CURDATE(), INTERVAL 6 MONTH)),
(2, 1, 'Fanta 50cl', 200.00, 300.00, 85, 10, DATE_ADD(CURDATE(), INTERVAL 5 MONTH)),
(3, 1, 'Maltina', 400.00, 500.00, 50, 5, DATE_ADD(CURDATE(), INTERVAL 4 MONTH)),
(4, 1, 'Water 75cl', 100.00, 150.00, 200, 20, DATE_ADD(CURDATE(), INTERVAL 12 MONTH)),

-- Snacks (Trigger Low Stock Alerts)
(5, 2, 'Gala Sausage', 150.00, 250.00, 4, 10, DATE_ADD(CURDATE(), INTERVAL 5 DAY)), -- Expiring Soon & Low Stock
(6, 2, 'Tasty Time', 120.00, 200.00, 2, 20, DATE_ADD(CURDATE(), INTERVAL 15 DAY)), -- Low Stock
(7, 2, 'Plantain Chips', 300.00, 450.00, 5, 15, DATE_ADD(CURDATE(), INTERVAL 2 MONTH)), -- Low Stock
(8, 2, 'Popcorn Big', 800.00, 1200.00, 1, 5, DATE_ADD(CURDATE(), INTERVAL 3 MONTH)), -- Low Stock

-- High Value / Expiring
(9, 1, 'Champagne', 15000.00, 25000.00, 10, 2, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
(10, 1, 'Red Wine', 4500.00, 8000.00, 15, 3, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
(11, 3, 'Body Spray', 1200.00, 2500.00, 30, 5, NULL),
(12, 3, 'Toothpaste', 600.00, 900.00, 40, 5, DATE_ADD(CURDATE(), INTERVAL 18 MONTH)),

-- Expired Item (Demo for POS Blocking)
(13, 4, 'Paracetamol (Expired)', 50.00, 100.00, 100, 20, DATE_SUB(CURDATE(), INTERVAL 1 DAY));

-- 5. SALES HISTORY: Simulations for the last 7 days (Zig-Zag performance)

-- Day 7 (Today)
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(1, 1, 'REC-TODAY01', 1200.00, NOW());
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(1, 1, 4, 300.00);

-- Day 6 (Yesterday)
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(2, 1, 'REC-YST01', 5000.00, DATE_SUB(NOW(), INTERVAL 1 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(2, 3, 10, 500.00);

-- Day 5
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(3, 1, 'REC-D501', 2500.00, DATE_SUB(NOW(), INTERVAL 2 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(3, 11, 1, 2500.00);

-- Day 4 (Peak)
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(4, 1, 'REC-D401', 25000.00, DATE_SUB(NOW(), INTERVAL 3 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(4, 9, 1, 25000.00);

-- Day 3
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(5, 1, 'REC-D301', 450.00, DATE_SUB(NOW(), INTERVAL 4 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(5, 7, 1, 450.00);

-- Day 2
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(6, 1, 'REC-D201', 900.00, DATE_SUB(NOW(), INTERVAL 5 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(6, 12, 1, 900.00);

-- Day 1
INSERT INTO sales (id, user_id, receipt_number, total_amount, sale_date) VALUES 
(7, 1, 'REC-D101', 1500.00, DATE_SUB(NOW(), INTERVAL 6 DAY));
INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES 
(7, 3, 3, 500.00);
