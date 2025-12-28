<!-- Sidebar -->
<div class="sidebar d-flex flex-column p-4">
    <a href="<?= url('dashboard') ?>" class="d-flex align-items-center mb-4 mb-md-0 text-white text-decoration-none">
        <i class="bi bi-box-seam-fill fs-3 me-2 text-primary"></i>
        <h5 class="fs-5 fw-bold mb-0">AbStock</h5>
    </a>
    
    <div class="mt-4 mb-4 text-white-50">
        <small class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Menu</small>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?= url('dashboard') ?>" class="nav-link <?php echo ($page ?? '') == 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="<?= url('pos') ?>" class="nav-link <?php echo ($page ?? '') == 'pos' ? 'active' : ''; ?>">
                <i class="bi bi-cart4 me-2"></i>
                Point of Sale
            </a>
        </li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="<?= url('inventory') ?>" class="nav-link <?php echo ($page ?? '') == 'inventory' ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-data me-2"></i>
                Inventory
            </a>
        </li>
        <li>
            <a href="<?= url('users') ?>" class="nav-link <?php echo ($page ?? '') == 'users' ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i>
                Manage Users
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="<?= url('history') ?>" class="nav-link <?php echo ($page ?? '') == 'history' ? 'active' : ''; ?>">
                <i class="bi bi-clock-history me-2"></i>
                History
            </a>
        </li>
        <li>
            <a href="<?= url('profile') ?>" class="nav-link <?php echo ($page ?? '') == 'profile' ? 'active' : ''; ?>">
                <i class="bi bi-person-gear me-2"></i>
                My Profile
            </a>
        </li>
    </ul>

    <hr>
    
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px;">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item text-danger" href="<?= url('actions/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </div>
</div>

<!-- Main Content Wrapper -->
<div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
