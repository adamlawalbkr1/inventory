<?php include ROOT_PATH . '/includes/header.php'; ?>

<!-- Override d-flex from header to center content -->
<style>
    body { background-color: #e9ecef; }
    .d-flex { min-height: 100vh; align-items: center; justify-content: center; }
    .sidebar { display: none; } /* Hide sidebar on login */
    .flex-grow-1 { flex-grow: 0; width: 100%; max-width: 400px; }
</style>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <h3 class="card-title text-center mb-4">Login</h3>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('actions/login.php') ?>" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Login</button>
                <a href="<?= url('register') ?>" class="btn btn-outline-secondary">Don't have an account? Register</a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
