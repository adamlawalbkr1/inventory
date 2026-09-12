<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard</h2>
            <p class="text-muted small mb-0"><i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="d-flex gap-2">
           <a href="<?= url('pos') ?>" class="btn btn-primary d-flex align-items-center">
               <i class="bi bi-cart-plus me-2"></i> New Sale
           </a>
        </div>
    </div>

    <?php
    require_once 'config/db.php';

    // --- 1. STATISTICS LOGIC ---
    
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    $stock_summary = $pdo->query("SELECT
                                                  COALESCE(SUM(CASE WHEN product_type = 'standard' THEN stock_quantity ELSE 0 END), 0) AS units_in_stock,
                                                  COALESCE(SUM(CASE WHEN product_type = 'standard' THEN stock_quantity * cost_price ELSE 0 END), 0) AS stock_cost,
                                                  COALESCE(SUM(CASE WHEN product_type = 'standard' THEN stock_quantity * selling_price ELSE 0 END), 0) AS stock_value
                                              FROM products")->fetch(PDO::FETCH_ASSOC);
    $stock_margin = (float) $stock_summary['stock_value'] - (float) $stock_summary['stock_cost'];

    // Daily revenue resets automatically at 12:00 AM UTC+1. Sales remain in history.
    $today_start = business_day_start();
    $tomorrow_start = $today_start->modify('+1 day');
    $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE status = 'fulfilled' AND sale_date >= ? AND sale_date < ?");
    $stmt->execute([
        $today_start->format('Y-m-d H:i:s'),
        $tomorrow_start->format('Y-m-d H:i:s')
    ]);
    $daily_revenue = $stmt->fetchColumn() ?: 0.00;

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(si.quantity), 0)
                           FROM sale_items si
                           JOIN sales s ON s.id = si.sale_id
                           WHERE s.status = 'fulfilled' AND s.sale_date >= ? AND s.sale_date < ?");
    $stmt->execute([
        $today_start->format('Y-m-d H:i:s'),
        $tomorrow_start->format('Y-m-d H:i:s')
    ]);
    $daily_items_sold = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(si.quantity * CASE WHEN p.product_type = 'prepared' THEN COALESCE((SELECT SUM(ri.quantity_required * rm.cost_per_unit) / r.servings_produced FROM recipes r JOIN recipe_items ri ON ri.recipe_id = r.id JOIN raw_materials rm ON rm.id = ri.raw_material_id WHERE r.product_id = p.id), 0) ELSE p.cost_price END), 0) FROM sale_items si JOIN sales s ON s.id = si.sale_id JOIN products p ON p.id = si.product_id WHERE s.status = 'fulfilled' AND s.sale_date >= ? AND s.sale_date < ?");
    $stmt->execute([
        $today_start->format('Y-m-d H:i:s'),
        $tomorrow_start->format('Y-m-d H:i:s')
    ]);
    $daily_cost = $stmt->fetchColumn() ?: 0.00;

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date >= ? AND expense_date < ?");
    $stmt->execute([
        $today_start->format('Y-m-d H:i:s'),
        $tomorrow_start->format('Y-m-d H:i:s')
    ]);
    $daily_expenses = $stmt->fetchColumn() ?: 0.00;
    $net_profit = $daily_revenue - $daily_cost - $daily_expenses;

    $stmt = $pdo->prepare("SELECT p.name,
                                  SUM(si.quantity) AS units_sold,
                                  SUM(si.quantity * si.price_at_sale) AS item_revenue,
                                  SUM(si.quantity * p.cost_price) AS item_cost
                           FROM sale_items si
                           JOIN sales s ON s.id = si.sale_id
                           JOIN products p ON p.id = si.product_id
                           WHERE s.status = 'fulfilled' AND s.sale_date >= ? AND s.sale_date < ?
                           GROUP BY p.id, p.name
                           ORDER BY units_sold DESC, item_revenue DESC");
    $stmt->execute([
        $today_start->format('Y-m-d H:i:s'),
        $tomorrow_start->format('Y-m-d H:i:s')
    ]);
    $sales_by_item = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Low Stock Items
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level");
    $low_stock_count = $stmt->fetchColumn();

    // Expiring Soon (Within 30 Days)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE expiry_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)");
    $expiring_count = $stmt->fetchColumn();

    // --- 2. CHART DATA LOGIC (Last 7 Days) ---
    $dates = [];
    $totals = [];
    for ($i = 6; $i >= 0; $i--) {
        $day_start = business_day_start($i);
        $day_end = $day_start->modify('+1 day');
        $dates[] = $day_start->format('D, j M');

        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE status = 'fulfilled' AND sale_date >= ? AND sale_date < ?");
        $stmt->execute([
            $day_start->format('Y-m-d H:i:s'),
            $day_end->format('Y-m-d H:i:s')
        ]);
        $totals[] = $stmt->fetchColumn() ?: 0;
    }

    // --- 3. RECENT TRANSACTIONS (Limit 5) ---
    $stmt = $pdo->query("SELECT s.*,
                                (SELECT GROUP_CONCAT(CONCAT(p.name, ' (x', si.quantity, ')') ORDER BY p.name SEPARATOR ', ')
                                FROM sale_items si
                                 JOIN products p ON si.product_id = p.id
                                 WHERE si.sale_id = s.id) as item_summary
                         FROM sales s
                         WHERE s.status = 'fulfilled'
                         ORDER BY s.sale_date DESC LIMIT 5");
    $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 4. STOCK ALERTS (Low Stock OR Expiring) ---
    $stmt = $pdo->prepare("SELECT * FROM products WHERE stock_quantity <= min_stock_level OR (expiry_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)) LIMIT 5");
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Row 1: Statistic Cards -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Total Products -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Products</h6>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_products); ?></h3>
                        </div>
                        <div class="icon-box bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Today's Revenue</h6>
                            <h3 class="fw-bold mb-0 text-dark">₦<?php echo number_format($daily_revenue, 2); ?></h3>
                        </div>
                        <div class="icon-box bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Items Sold -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Today's Items Sold</h6>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($daily_items_sold); ?></h3>
                        </div>
                        <div class="icon-box bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-bag-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Low Stock -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= url('inventory?filter=low') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Low Stock</h6>
                                <h3 class="fw-bold mb-0 text-danger"><?php echo number_format($low_stock_count); ?></h3>
                            </div>
                            <div class="icon-box bg-danger-subtle text-danger rounded-circle">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Today's Expenses</h6>
                            <h3 class="fw-bold mb-0 text-danger">₦<?php echo number_format($daily_expenses, 2); ?></h3>
                        </div>
                        <div class="icon-box bg-danger-subtle text-danger rounded-circle">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Today's Net Profit</h6>
                                <h3 class="fw-bold mb-0 <?php echo $net_profit >= 0 ? 'text-success' : 'text-danger'; ?>">₦<?php echo number_format($net_profit, 2); ?></h3>
                            </div>
                            <div class="icon-box bg-success-subtle text-success rounded-circle">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Card 4: Expiring -->
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= url('inventory?filter=expired') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Expiring</h6>
                                <h3 class="fw-bold mb-0 text-warning"><?php echo number_format($expiring_count); ?></h3>
                            </div>
                            <div class="icon-box bg-warning-subtle text-warning rounded-circle">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Stock Bookkeeping -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark mb-0">Stock Bookkeeping</h5>
                    <a href="<?= url('inventory') ?>" class="btn btn-sm btn-light text-primary">View Inventory</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-xl-3">
                            <div class="stock-summary-item">
                                <span class="text-muted small d-block">Units in Stock</span>
                                <strong class="fs-4"><?php echo number_format((int) $stock_summary['units_in_stock']); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stock-summary-item">
                                <span class="text-muted small d-block">Estimated Cost</span>
                                <strong class="fs-4">₦<?php echo number_format((float) $stock_summary['stock_cost'], 2); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stock-summary-item">
                                <span class="text-muted small d-block">Estimated Sale Value</span>
                                <strong class="fs-4 text-primary">₦<?php echo number_format((float) $stock_summary['stock_value'], 2); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stock-summary-item">
                                <span class="text-muted small d-block">Potential Gross Margin</span>
                                <strong class="fs-4 text-success">₦<?php echo number_format($stock_margin, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Estimates use current stock quantities and the recorded cost and selling prices.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Analytics & Recent Sales -->
    <div class="row g-4 mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                     <h5 class="card-title fw-bold text-dark mb-0">Sales Overview</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark mb-0">Recent Sales</h5>
                    <a href="<?= url('history') ?>" class="btn btn-sm btn-light text-primary fw-medium small">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($recent_sales)): ?>
                            <div class="p-4 text-center text-muted small">No recent transactions found.</div>
                        <?php else: ?>
                            <?php foreach ($recent_sales as $sale): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-light">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-muted rounded-circle me-3" style="width: 36px; height: 36px; font-size: 1rem;">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 fw-medium text-dark small"><?php echo htmlspecialchars($sale['receipt_number']); ?></p>
                                            <small class="d-block text-dark text-truncate" style="max-width: 210px;" title="<?php echo htmlspecialchars($sale['item_summary'] ?? 'Unknown item'); ?>"><?php echo htmlspecialchars($sale['item_summary'] ?? 'Unknown item'); ?></small>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M j, g:i A', strtotime($sale['sale_date'])); ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-dark small">₦<?php echo number_format($sale['total_amount']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Sales by Item -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark mb-0">Sales by Item</h5>
                    <span class="text-muted small">Today</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                    <th>Cost</th>
                                    <th class="text-end pe-4">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales_by_item)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted small">No item sales recorded today.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($sales_by_item as $item):
                                        $item_profit = (float) $item['item_revenue'] - (float) $item['item_cost'];
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-medium text-dark"><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo number_format((int) $item['units_sold']); ?></td>
                                            <td>₦<?php echo number_format((float) $item['item_revenue'], 2); ?></td>
                                            <td>₦<?php echo number_format((float) $item['item_cost'], 2); ?></td>
                                            <td class="text-end pe-4 fw-semibold <?php echo $item_profit >= 0 ? 'text-success' : 'text-danger'; ?>">₦<?php echo number_format($item_profit, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Stock Alerts -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                 <div class="card-header bg-white border-0 py-3">
                     <h5 class="card-title fw-bold text-dark mb-0">Stock Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alerts)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted small">No active alerts. Great job!</td></tr>
                                <?php else: ?>
                                    <?php foreach ($alerts as $item): 
                                        $is_low = $item['stock_quantity'] <= $item['min_stock_level'];
                                        $days_to_expiry = (strtotime($item['expiry_date']) - time()) / (60 * 60 * 24);
                                        $is_expiring = $days_to_expiry <= 30;
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark"><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="small text-muted">ID: #<?php echo $item['category_id']; // Ideally fetch name ?></td>
                                        <td class="fw-bold"><?php echo $item['stock_quantity']; ?> <span class="text-muted fw-normal small">/ <?php echo $item['min_stock_level']; ?></span></td>
                                        <td>
                                            <?php if($is_low): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Low Stock</span>
                                            <?php endif; ?>
                                            <?php if($is_expiring): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Expiring</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="<?= url('inventory?search=' . urlencode($item['name'])) ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Gradient Background
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(249, 115, 22, 0.2)');
    gradient.addColorStop(1, 'rgba(249, 115, 22, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Revenue (₦)',
                data: <?php echo json_encode($totals); ?>,
                borderColor: '#F97316',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#F97316',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1E293B',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₦' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: 'Inter', size: 11 },
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Inter', size: 11 }
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
