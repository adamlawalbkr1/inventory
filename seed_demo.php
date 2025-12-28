<?php
// seed_demo.php
require 'config/db.php';
require 'config/app.php';

echo "<h1>Starting Demo Data Seeding...</h1>";

try {
    // ----------------------------------------------------
    // STEP 1: CLEANUP
    // ----------------------------------------------------
    echo "<p>Cleaner: Removing old data...</p>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE sale_items");
    $pdo->exec("TRUNCATE TABLE sales");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("TRUNCATE TABLE users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ----------------------------------------------------
    // STEP 2: USERS
    // ----------------------------------------------------
    echo "<p>Users: Creating admin and staff...</p>";
    $pass = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $pass, 'admin']);
    $stmt->execute(['staff', $pass, 'staff']);

    // ----------------------------------------------------
    // STEP 3: CATEGORIES
    // ----------------------------------------------------
    echo "<p>Categories: Creating Drinks, Snacks, Essentials...</p>";
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute(['Drinks']);
    $cat_drinks = $pdo->lastInsertId();
    
    $stmt->execute(['Snacks']);
    $cat_snacks = $pdo->lastInsertId();
    
    $stmt->execute(['Essentials']);
    $cat_essentials = $pdo->lastInsertId();

    // ----------------------------------------------------
    // STEP 4: PRODUCTS
    // ----------------------------------------------------
    echo "<p>Products: Seeding 15 mixed items...</p>";
    $products_data = [
        // Drinks (High Stock)
        ['Coke 50cl', $cat_drinks, 200, 300, 100, 10],
        ['Fanta 50cl', $cat_drinks, 200, 300, 85, 10],
        ['Pepsi 50cl', $cat_drinks, 190, 290, 120, 10],
        ['Water 75cl', $cat_drinks, 100, 150, 200, 20],
        ['Maltina', $cat_drinks, 400, 500, 50, 5],
        
        // Snacks (Low Stock - Triggers Alerts)
        ['Gala Sausage', $cat_snacks, 150, 250, 4, 10],      // Low
        ['Tasty Time', $cat_snacks, 120, 200, 2, 20],       // Low
        ['Plantain Chips', $cat_snacks, 300, 450, 5, 15],  // Low
        ['Popcorn Big', $cat_snacks, 800, 1200, 1, 5],      // Low
        ['Digestive', $cat_snacks, 500, 700, 3, 10],        // Low
        
        // Essentials (High Value)
        ['Champagne', $cat_drinks, 15000, 25000, 10, 2],
        ['Red Wine', $cat_drinks, 4500, 8000, 15, 3],
        ['Whisky', $cat_drinks, 8000, 14000, 8, 2],
        ['Body Spray', $cat_essentials, 1200, 2500, 30, 5],
        ['Toothpaste', $cat_essentials, 600, 900, 40, 5],
    ];

    $prod_stmt = $pdo->prepare("INSERT INTO products (name, category_id, cost_price, selling_price, stock_quantity, min_stock_level) VALUES (?, ?, ?, ?, ?, ?)");
    
    $product_ids = []; // Keep track for sales generation

    foreach ($products_data as $p) {
        $prod_stmt->execute($p);
        $product_ids[] = [
            'id' => $pdo->lastInsertId(),
            'price' => $p[3]
        ];
    }

    // ----------------------------------------------------
    // STEP 5: SALES HISTORY (The "Zig-Zag" Chart)
    // ----------------------------------------------------
    echo "<p>History: Generating sales for the last 7 days...</p>";
    
    // Corrected SQL based on install.php schema
    // sales: id, user_id, receipt_number, total_amount, sale_date
    $sale_head_stmt = $pdo->prepare("INSERT INTO sales (user_id, receipt_number, total_amount, sale_date) VALUES (?, ?, ?, ?)");
    
    // sale_items: id, sale_id, product_id, quantity, price_at_sale (NO subtotal column)
    $sale_item_stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");

    // Iterate backwards from yesterday to 7 days ago
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d H:i:s', strtotime("-$i days"));
        $num_sales = rand(3, 6); // 3 to 6 sales per day

        for ($j = 0; $j < $num_sales; $j++) {
            // Create a random cart
            $cart_total = 0;
            $items = [];
            $num_items = rand(1, 4);
            
            for ($k = 0; $k < $num_items; $k++) {
                $rand_prod = $product_ids[array_rand($product_ids)];
                $qty = rand(1, 3);
                $sub = $rand_prod['price'] * $qty;
                
                $items[] = [
                    'pid' => $rand_prod['id'],
                    'qty' => $qty,
                    'price' => $rand_prod['price'],
                    'sub' => $sub
                ];
                $cart_total += $sub;
            }

            // Generate Receipt Number
            $receipt_no = 'REC-' . strtoupper(uniqid());

            // Insert Sale Header
            $sale_head_stmt->execute([1, $receipt_no, $cart_total, $date]); // User 1 is Admin
            $sale_id = $pdo->lastInsertId();

            // Insert Sale Items
            foreach ($items as $item) {
                // Removed 'subtotal' from execute params
                $sale_item_stmt->execute([$sale_id, $item['pid'], $item['qty'], $item['price']]);
            }
        }
    }

    echo "<h2 style='color:green;'>SUCCESS! Database is Golden.</h2>";
    echo "<hr>";
    echo "<h3>Ready for Defense:</h3>";
    echo "<ul>";
    echo "<li><strong>Users:</strong> admin / password</li>";
    echo "<li><strong>Low Stock:</strong> 5 items are critical (Check Dashboard)</li>";
    echo "<li><strong>Values:</strong> High value items included for Revenue boost</li>";
    echo "</ul>";
    echo "<br><a href='" . url('login') . "' style='background:blue; color:white; padding:10px; text-decoration:none; border-radius:5px;'>Login Now</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>ERROR: " . $e->getMessage() . "</h2>";
}
