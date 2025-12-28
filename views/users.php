<?php include 'includes/header.php'; ?>
<?php 
// RBAC: Admin only
if ($_SESSION['role'] !== 'admin') {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied. Admins only.</div></div>";
    include 'includes/footer.php';
    exit();
}
include 'includes/sidebar.php'; 
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">User Management</h2>
            <p class="text-muted small mb-0">Manage system administrators and staff accounts</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-2"></i> Add New User
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i> User created successfully!
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once 'config/db.php';
                        $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
                        while ($user = $stmt->fetch()):
                        ?>
                        <tr>
                            <td class="ps-4">#<?php echo $user['id']; ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-primary' : 'bg-primary-subtle text-primary'; ?> px-3 py-2">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary edit-btn" 
                                        data-id="<?php echo $user['id']; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                                        data-role="<?php echo $user['role']; ?>">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <a href="<?= url('actions/delete_user.php?id=' . $user['id']) ?>" 
                                   class="btn btn-sm btn-danger ms-1"
                                   onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold">Create System User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('actions/add_user.php') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="User unique ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Initial password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">System Role</label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Staff (Limited Access)</option>
                            <option value="admin">Administrator (Full Control)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top p-4 bg-light">
                    <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold">Edit System User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('actions/edit_user.php') ?>" method="POST">
                <input type="hidden" name="user_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">System Role</label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Staff (Limited Access)</option>
                            <option value="admin">Administrator (Full Control)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Entrer new password">
                        <small class="text-muted">Leave blank to keep current password.</small>
                    </div>
                </div>
                <div class="modal-footer border-top p-4 bg-light">
                    <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const user = btn.getAttribute('data-username');
            const role = btn.getAttribute('data-role');

            // Populate Modal Fields
            document.querySelector('#editUserModal [name="user_id"]').value = id;
            document.querySelector('#editUserModal [name="username"]').value = user;
            document.querySelector('#editUserModal [name="role"]').value = role;
            document.querySelector('#editUserModal [name="password"]').value = ''; // Clear previous input

            // Open Modal
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
