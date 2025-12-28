<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h4 class="fw-bold text-dark mb-0">My Profile</h4>
                    <p class="text-muted small mb-0">Manage your account settings and security</p>
                </div>
                <div class="card-body p-4">
                    <!-- Flash Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle me-2"></i> Password updated successfully!
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- User Info (Read Only) -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Username</label>
                        <input type="text" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                    </div>
                    <div class="mb-4 text-center">
                         <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-block mb-2">
                             <i class="bi bi-person-fill fs-1"></i>
                         </div>
                         <h5 class="fw-bold text-dark"><?php echo ucfirst($_SESSION['role']); ?> Account</h5>
                    </div>

                    <hr class="my-4">

                    <!-- Change Password Form -->
                    <h5 class="fw-bold text-dark mb-3">Change Password</h5>
                    <form action="<?= url('actions/update_profile.php') ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Enter new password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_new_password" class="form-control" required placeholder="Re-type new password">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
