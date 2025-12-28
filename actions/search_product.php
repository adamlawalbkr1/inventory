<?php
// actions/search_product.php
require_once '../config/db.php';
require_once '../config/app.php';

header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $search = trim($_GET['query']);
    
    if ($search === '') {
        // Auto-Load: Return top 50 items (Quick Pick)
        $stmt = $pdo->prepare("SELECT id, name, selling_price, stock_quantity, min_stock_level 
                               FROM products 
                               WHERE stock_quantity > 0 
                               ORDER BY name ASC 
                               LIMIT 50");
        $stmt->execute();
    } else {
        // Search: Filter by name
        $stmt = $pdo->prepare("SELECT id, name, selling_price, stock_quantity, min_stock_level 
                               FROM products 
                               WHERE name LIKE :search 
                               AND stock_quantity > 0 
                               LIMIT 10");
        $stmt->execute([':search' => "%$search%"]);
    }
    
    $results = $stmt->fetchAll();
    echo json_encode($results);
} else {
    echo json_encode([]);
}
?>
