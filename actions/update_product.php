<?php
// actions/update_product.php
require_once '../includes/auth_check.php'; // Enforce Admin Access
require_once '../config/db.php';
require_once '../config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $min_stock = $_POST['min_stock_level'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;

    try {
        $sql = "UPDATE products SET 
                name = :name, 
                category_id = :cat, 
                cost_price = :cost, 
                selling_price = :sell, 
                stock_quantity = :qty, 
                min_stock_level = :min, 
                expiry_date = :exp 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':cat' => $category_id,
            ':cost' => $cost_price,
            ':sell' => $selling_price,
            ':qty' => $stock_quantity,
            ':min' => $min_stock,
            ':exp' => $expiry_date,
            ':id' => $id
        ]);

        redirect('inventory?success=Product Updated Successfully');
        exit();

    } catch (PDOException $e) {
        error_log("Update Product Error: " . $e->getMessage());
        redirect('inventory?error=Database Error: Could not update product');
        exit();
    }
}
?>
