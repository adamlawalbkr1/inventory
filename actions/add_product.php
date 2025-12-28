<?php
// actions/add_product.php
require_once '../includes/auth_check.php'; // Enforce Admin Access
require_once '../config/db.php';
require_once '../config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect Inputs
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $min_stock = $_POST['min_stock_level'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;

    // Validate (Basic)
    if (empty($name) || empty($category_id) || $cost_price < 0 || $selling_price < 0) {
        redirect('inventory?error=Invalid Input Data');
        exit();
    }

    try {
        $sql = "INSERT INTO products (name, category_id, cost_price, selling_price, stock_quantity, min_stock_level, expiry_date) 
                VALUES (:name, :cat, :cost, :sell, :qty, :min, :exp)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':cat' => $category_id,
            ':cost' => $cost_price,
            ':sell' => $selling_price,
            ':qty' => $stock_quantity,
            ':min' => $min_stock,
            ':exp' => $expiry_date
        ]);

        redirect('inventory?success=Product Added Successfully');
        exit();

    } catch (PDOException $e) {
        error_log("Add Product Error: " . $e->getMessage());
        redirect('inventory?error=Database Error: Could not add product');
        exit();
    }
}
?>
