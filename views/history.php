<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php require_once 'config/db.php'; ?>

<div class="container-fluid">
    <h2 class="mt-4 mb-4">Transaction History</h2>

    <!-- Filter Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="<?= url('history') ?>" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="history">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start" class="form-control" value="<?php echo $_GET['start'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end" class="form-control" value="<?php echo $_GET['end'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filter History</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card table-card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Receipt #</th>
                        <th>Cashier</th>
                        <th>Total Amount</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build Query
                    $sql = "SELECT s.*, u.username as cashier_name 
                            FROM sales s 
                            JOIN users u ON s.user_id = u.id";
                    
                    $params = [];
                    
                    // Filter Logic
                    if (!empty($_GET['start']) && !empty($_GET['end'])) {
                        $sql .= " WHERE DATE(s.sale_date) BETWEEN :start AND :end";
                        $params[':start'] = $_GET['start'];
                        $params[':end'] = $_GET['end'];
                    }

                    $sql .= " ORDER BY s.sale_date DESC";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    if ($stmt->rowCount() > 0):
                        while ($row = $stmt->fetch()):
                    ?>
                        <tr>
                            <td class="ps-4"><?php echo date('M d, Y', strtotime($row['sale_date'])); ?> <small class="text-muted"><?php echo date('h:i A', strtotime($row['sale_date'])); ?></small></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['receipt_number']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                            <td class="fw-bold text-success">₦<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td class="text-end pe-4">
                                <a href="<?= url('receipt?id=' . $row['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-printer me-1"></i> Receipt
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No transactions found for this period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
