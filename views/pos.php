<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<style>
    /* Custom POS Styles */
    .product-card { cursor: pointer; transition: transform 0.1s; }
    .product-card:hover { transform: scale(1.02); border-color: #0d6efd; }
    .pos-container { height: calc(100vh - 60px); overflow: hidden; }
    .scrollable-column { height: 100%; overflow-y: auto; }
</style>

<div class="container-fluid pos-container">
    <div class="row h-100">
        <!-- LEFT COLUMN: Product Search & Grid -->
        <div class="col-md-7 border-end p-3 scrollable-column">
            <h4 class="mb-3">Product Catalog</h4>
            
            <div class="mb-4">
                <input type="text" id="search-bar" class="form-control form-control-lg" placeholder="Start typing to search products..." autofocus>
            </div>

            <div id="product-list" class="row g-3">
                <!-- Search results will be injected here via JS -->
                <div class="col-12 text-center text-muted mt-5">
                    <p>Type in the search box to find products.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Cart & Checkout -->
        <div class="col-md-5 p-3 d-flex flex-column h-100">
            <h4 class="mb-3">Current Sale</h4>
            
            <div class="flex-grow-1 overflow-auto bg-white border rounded">
                <table class="table table-striped table-sm mb-0">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Price</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <!-- Cart Items injected here -->
                    </tbody>
                </table>
            </div>

            <div class="mt-3 border-top pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Total:</h3>
                    <h3 class="text-success fw-bold">₦<span id="grand-total">0.00</span></h3>
                </div>
                
                <div class="d-flex gap-2">
                    <button id="btn-save" class="btn btn-success flex-grow-1 py-3">
                        <i class="bi bi-check-circle-fill me-2"></i> Complete Sale
                    </button>
                    <button id="btn-print" class="btn btn-primary flex-grow-1 py-3">
                        <i class="bi bi-printer-fill me-2"></i> Complete & Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('assets/js/pos.js') ?>"></script>

<?php include 'includes/footer.php'; ?>
