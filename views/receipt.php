<?php
if (!defined('ROUTED')) {
    session_start();
}
require_once 'config/db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login');
}

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$sale_id = $_GET['id'];

// 1. Fetch Sale Header
$stmt = $pdo->prepare("SELECT s.*, u.username as cashier_name 
                       FROM sales s 
                       JOIN users u ON s.user_id = u.id 
                       WHERE s.id = :id");
$stmt->execute([':id' => $sale_id]);
$sale = $stmt->fetch();

if (!$sale) {
    die("Receipt not found.");
}

// 2. Fetch Sale Items
$stmt = $pdo->prepare("SELECT si.*, p.name as product_name 
                       FROM sale_items si 
                       JOIN products p ON si.product_id = p.id 
                       WHERE si.sale_id = :id");
$stmt->execute([':id' => $sale_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo htmlspecialchars($sale['receipt_number']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #e9ecef; }
        .receipt-container {
            max_width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* PRINT STYLES */
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; }
            .receipt-container { 
                box-shadow: none; 
                margin: 0; 
                width: 100%; 
                max-width: 100%; 
                padding: 0;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="text-center mb-4">
        <h4>Mr. Abul Store</h4>
        <p class="mb-0 text-muted">123 Singa Street, Kano</p>
        <p class="small text-muted">Tel: 080-123-45678</p>
    </div>

    <div class="mb-3 border-bottom pb-2">
        <div class="d-flex justify-content-between">
            <small>Receipt #:</small>
            <strong><?php echo $sale['receipt_number']; ?></strong>
        </div>
        <div class="d-flex justify-content-between">
            <small>Date:</small>
            <span><?php echo date('d-m-Y H:i', strtotime($sale['sale_date'])); ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <small>Cashier:</small>
            <span><?php echo htmlspecialchars($sale['cashier_name']); ?></span>
        </div>
    </div>

    <table class="table table-sm border-bottom">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-end"><?php echo number_format($item['price_at_sale'], 2); ?></td>
                <td class="text-end"><?php echo number_format($item['quantity'] * $item['price_at_sale'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <h5>TOTAL:</h5>
        <h4>₦<?php echo number_format($sale['total_amount'], 2); ?></h4>
    </div>

    <div class="text-center mt-4 mb-2">
        <p class="small text-muted">Thank you for your patronage!</p>
        <p class="small text-muted">Goods sold in good condition are not returnable.</p>
    </div>

    <!-- Buttons (Hidden on Print) -->
    <div class="d-grid gap-2 d-md-block text-center no-print mt-4">
        <button onclick="window.print()" class="btn btn-secondary">Print Receipt</button>
        <a href="<?= url('pos') ?>" class="btn btn-success">Back to POS</a>
    </div>
</div>

<script>
    // Auto-print on load if needed, or user clicks button.
    // Uncomment next line to auto-print:
    window.onload = function() { window.print(); }
</script>

</body>
</html>
