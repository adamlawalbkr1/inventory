<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php require_once 'includes/auth_check.php'; // Protect this page ?>
<?php require_once 'config/db.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h2>Inventory Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            + Add Product
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card table-card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Cost</th>
                        <th>Selling</th>
                        <th>Stock</th>
                        <th>Expiry</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch Products with Category Name
                    $sql = "SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id";
                    
                    // Filter Logic
                    if (isset($_GET['filter'])) {
                        if ($_GET['filter'] == 'low') {
                            $sql .= " WHERE p.stock_quantity <= p.min_stock_level";
                        } elseif ($_GET['filter'] == 'expired') {
                            $sql .= " WHERE p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)";
                        }
                    }

                    $sql .= " ORDER BY p.id DESC";

                    $stmt = $pdo->query($sql);
                    
                    while ($row = $stmt->fetch()):
                        $is_low_stock = $row['stock_quantity'] < $row['min_stock_level'];
                        
                        // Expiry logic
                        $expiry_status = '';
                        $expiry_badge = '';
                        if ($row['expiry_date']) {
                            $today = date('Y-m-d');
                            $expiry_date = $row['expiry_date'];
                            
                            if ($expiry_date < $today) {
                                $expiry_status = 'Expired';
                                $expiry_badge = 'bg-danger text-white';
                            } elseif ($expiry_date <= date('Y-m-d', strtotime('+30 days'))) {
                                $expiry_status = 'Expiring Soon';
                                $expiry_badge = 'bg-warning text-dark';
                            }
                        }
                    ?>
                        <tr class="<?php echo ($is_low_stock || ($expiry_status == 'Expired')) ? 'table-warning' : ''; ?>">
                            <td class="ps-4 text-muted">#<?php echo $row['id']; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                <?php if ($expiry_status): ?>
                                    <span class="badge <?php echo $expiry_badge; ?> small p-1" style="font-size: 0.65rem;"><?php echo $expiry_status; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge rounded-pill bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span></td>
                            <td><?php echo number_format($row['cost_price'], 2); ?></td>
                            <td class="fw-bold text-dark"><?php echo number_format($row['selling_price'], 2); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($is_low_stock): ?>
                                        <span class="badge bg-danger me-2"><?php echo $row['stock_quantity']; ?> (Low)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success me-2"><?php echo $row['stock_quantity']; ?></span>
                                    <?php endif; ?>
                                    <span class="text-muted" style="font-size: 0.75rem;">Min: <?php echo $row['min_stock_level']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($row['expiry_date']): ?>
                                    <div class="<?php echo ($expiry_status == 'Expired' ? 'text-danger fw-bold' : ($expiry_status == 'Expiring Soon' ? 'text-warning' : '')); ?>">
                                        <?php echo date('M j, Y', strtotime($row['expiry_date'])); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-light border edit-btn me-1" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-category="<?php echo $row['category_id']; ?>"
                                    data-cost="<?php echo $row['cost_price']; ?>"
                                    data-selling="<?php echo $row['selling_price']; ?>"
                                    data-qty="<?php echo $row['stock_quantity']; ?>"
                                    data-min="<?php echo $row['min_stock_level']; ?>"
                                    data-expiry="<?php echo $row['expiry_date']; ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <!-- Delete Link -->
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="<?= url('actions/delete_product.php?id=' . $row['id']) ?>" 
                                       class="btn btn-sm btn-light border text-danger"
                                       onclick="return confirm('Are you sure you want to delete this product?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('actions/add_product.php') ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php
                            $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                            while ($c = $cats->fetch()) {
                                echo "<option value='{$c['id']}'>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Cost Price</label>
                            <input type="number" step="0.01" min="0" name="cost_price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Selling Price</label>
                            <input type="number" step="0.01" min="0" name="selling_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Min Alert Level</label>
                            <input type="number" name="min_stock_level" class="form-control" value="5" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('actions/update_product.php') ?>" method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" id="edit_category" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php
                            // Re-fetch categories for the second modal
                            $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                            while ($c = $cats->fetch()) {
                                echo "<option value='{$c['id']}'>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Cost Price</label>
                            <input type="number" step="0.01" min="0" name="cost_price" id="edit_cost" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Selling Price</label>
                            <input type="number" step="0.01" min="0" name="selling_price" id="edit_selling" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" name="stock_quantity" id="edit_qty" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Min Alert Level</label>
                            <input type="number" name="min_stock_level" id="edit_min" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" id="edit_expiry" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // JavaScript to handle Edit Modal Data Passing
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = document.getElementById('editProductModal');
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_category').value = this.dataset.category;
                document.getElementById('edit_cost').value = this.dataset.cost;
                document.getElementById('edit_selling').value = this.dataset.selling;
                document.getElementById('edit_qty').value = this.dataset.qty;
                document.getElementById('edit_min').value = this.dataset.min;
                document.getElementById('edit_expiry').value = this.dataset.expiry;
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
