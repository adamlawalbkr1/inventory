<?php
function consume_sale_stock(PDO $pdo, array $items, ?int $sale_id = null, ?int $user_id = null): void
{
    foreach ($items as $item) {
        $product_id = (int) $item['id'];
        $quantity = (int) $item['qty'];
        if ($product_id <= 0) {
            continue;
        }

        $stmt = $pdo->prepare("SELECT name, product_type, stock_quantity, expiry_date FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) {
            throw new RuntimeException('Product not found.');
        }
        if ($product['expiry_date'] && $product['expiry_date'] < date('Y-m-d')) {
            throw new RuntimeException("Cannot sell {$product['name']}: it has expired.");
        }

        if ($product['product_type'] === 'prepared') {
            $recipe = $pdo->prepare("SELECT id, servings_produced FROM recipes WHERE product_id = ?");
            $recipe->execute([$product_id]);
            $recipe = $recipe->fetch();
            if (!$recipe) {
                throw new RuntimeException("No recipe is configured for {$product['name']}.");
            }

            $ingredients = $pdo->prepare("SELECT ri.raw_material_id, ri.quantity_required, rm.name, rm.quantity_in_stock, rm.cost_per_unit
                                          FROM recipe_items ri
                                          JOIN raw_materials rm ON rm.id = ri.raw_material_id
                                          WHERE ri.recipe_id = ? FOR UPDATE");
            $ingredients->execute([$recipe['id']]);
            $ingredients = $ingredients->fetchAll();
            foreach ($ingredients as $ingredient) {
                $required = ((float) $ingredient['quantity_required'] / (int) $recipe['servings_produced']) * $quantity;
                if ((float) $ingredient['quantity_in_stock'] < $required) {
                    throw new RuntimeException("Insufficient raw material: {$ingredient['name']}.");
                }
            }
            foreach ($ingredients as $ingredient) {
                $required = ((float) $ingredient['quantity_required'] / (int) $recipe['servings_produced']) * $quantity;
                $update = $pdo->prepare("UPDATE raw_materials SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?");
                $update->execute([$required, $ingredient['raw_material_id']]);
                $movement = $pdo->prepare("INSERT INTO stock_movements (raw_material_id, movement_type, quantity, unit_cost, reason, sale_id, user_id) VALUES (?, 'consumption', ?, ?, ?, ?, ?)");
                $movement->execute([$ingredient['raw_material_id'], -$required, $ingredient['cost_per_unit'] ?? 0, 'Prepared sale consumption', $sale_id, $user_id]);
            }
        } else {
            if ((int) $product['stock_quantity'] < $quantity) {
                throw new RuntimeException("Out of stock: {$product['name']}.");
            }
            $update = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $update->execute([$quantity, $product_id]);
            $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, movement_type, quantity, unit_cost, reason, sale_id, user_id) VALUES (?, 'sale', ?, ?, ?, ?, ?)");
            $movement->execute([$product_id, -$quantity, $product['cost_price'] ?? 0, 'Sale consumption', $sale_id, $user_id]);
        }
    }
}

function recipe_cost(PDO $pdo, int $product_id): ?float
{
    $stmt = $pdo->prepare("SELECT r.id, r.servings_produced, SUM(ri.quantity_required * rm.cost_per_unit) AS batch_cost
                           FROM recipes r
                           JOIN recipe_items ri ON ri.recipe_id = r.id
                           JOIN raw_materials rm ON rm.id = ri.raw_material_id
                           WHERE r.product_id = ?
                           GROUP BY r.id, r.servings_produced");
    $stmt->execute([$product_id]);
    $recipe = $stmt->fetch();
    return $recipe ? (float) $recipe['batch_cost'] / max(1, (int) $recipe['servings_produced']) : null;
}
?>
