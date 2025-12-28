<?php
// actions/save_sale.php
session_start();
require_once '../config/db.php';
require_once '../config/app.php';

header('Content-Type: application/json');

// Check Auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input)) {
    echo json_encode(['success' => false, 'message' => 'Empty Cart']);
    exit();
}

try {
    // 1. Start Transaction (Crucial)
    $pdo->beginTransaction();

    // 2. Calculate Total & Create Sale Header
    $total_amount = 0;
    foreach ($input as $item) {
        $total_amount += ($item['price'] * $item['qty']);
    }

    $receipt_number = 'REC-' . strtoupper(uniqid()); // Simple unique ID
    
    $stmt = $pdo->prepare("INSERT INTO sales (user_id, receipt_number, total_amount, sale_date) VALUES (:uid, :rec, :total, NOW())");
    $stmt->execute([
        ':uid' => $_SESSION['user_id'],
        ':rec' => $receipt_number,
        ':total' => $total_amount
    ]);
    
    $sale_id = $pdo->lastInsertId();

    // 3. Process Line Items
    foreach ($input as $item) {
        $prod_id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        // A. Check Stock and Expiry
        $check = $pdo->prepare("SELECT name, stock_quantity, expiry_date FROM products WHERE id = :id");
        $check->execute([':id' => $prod_id]);
        $prod = $check->fetch();

        // Stock check
        if ($prod['stock_quantity'] < $qty) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => "Out of Stock for Product: " . $prod['name']]);
            exit();
        }

        // Expiry check
        if ($prod['expiry_date'] && $prod['expiry_date'] < date('Y-m-d')) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => "Cannot sell " . $prod['name'] . ": It has expired on " . $prod['expiry_date']]);
            exit();
        }

        // B. Deduct Stock
        $deduct = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :id");
        $deduct->execute([':qty' => $qty, ':id' => $prod_id]);

        // C. Insert Sale Item
        $insert_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES (:sid, :pid, :qty, :price)");
        $insert_item->execute([
            ':sid' => $sale_id,
            ':pid' => $prod_id,
            ':qty' => $qty,
            ':price' => $price
        ]);
    }

    // 4. Commit Transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'sale_id' => $sale_id]);

} catch (Exception $e) {
    // 5. On Any Error - ROLLBACK
    $pdo->rollBack();
    error_log("Transaction Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database Transaction Failed']);
}
?>
