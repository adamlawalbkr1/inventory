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

    // Total Revenue (All Time)
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM sales");
    $total_revenue = $stmt->fetchColumn() ?: 0.00;

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
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('D, j M', strtotime($date));
        
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = ?");
        $stmt->execute([$date]);
        $totals[] = $stmt->fetchColumn() ?: 0;
    }

    // --- 3. RECENT TRANSACTIONS (Limit 5) ---
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY sale_date DESC LIMIT 5");
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
                            <h6 class="text-muted text-uppercase mb-1 small fw-semibold ls-1">Revenue</h6>
                            <h3 class="fw-bold mb-0 text-dark">₦<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                        <div class="icon-box bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-currency-dollar fs-4"></i>
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
                                            <a href="<?= url('inventory?search='.urlencode($item['name'])) ?>" class="btn btn-sm btn-outline-primary">Manage</a>
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
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Revenue (₦)',
                data: <?php echo json_encode($totals); ?>,
                borderColor: '#4F46E5', // Primary Indigo
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#4F46E5',
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
