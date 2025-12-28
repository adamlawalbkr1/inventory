<?php
// actions/delete_product.php
require_once '../includes/auth_check.php'; // Enforce Admin Access
require_once '../config/db.php';
require_once '../config/app.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        redirect('inventory?success=Product Deleted Successfully');
        exit();

    } catch (PDOException $e) {
        error_log("Delete Product Error: " . $e->getMessage());
        redirect('inventory?error=Could not delete product. It may be linked to sales.');
        exit();
    }
}
?>
