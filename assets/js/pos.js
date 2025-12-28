// assets/js/pos.js

document.addEventListener('DOMContentLoaded', () => {
    const searchBar = document.getElementById('search-bar');
    const productList = document.getElementById('product-list');
    const cartBody = document.getElementById('cart-body');
    const grandTotalSpan = document.getElementById('grand-total');

    // New Buttons
    const btnSave = document.getElementById('btn-save');
    const btnPrint = document.getElementById('btn-print');

    let cart = [];

    // --- FEATURE A: SEARCH ---
    // --- FEATURE A: SEARCH & AUTO-LOAD ---
    function fetchProducts(query = '') {
        // Show loading or prompt if query is short (but allow empty for auto-load)
        if (query.length > 0 && query.length < 2) {
            productList.innerHTML = '<div class="col-12 text-center text-muted mt-5"><p>Type at least 2 characters...</p></div>';
            return;
        }

        const url = `actions/search_product.php?query=${encodeURIComponent(query)}`;

        fetch(url)
            .then(res => res.json())
            .then(products => {
                productList.innerHTML = '';
                if (products.length === 0) {
                    productList.innerHTML = '<div class="col-12 text-center text-danger mt-3"><p>No in-stock products found.</p></div>';
                    return;
                }

                products.forEach(p => {
                    const isLow = p.stock_quantity <= p.min_stock_level;
                    productList.innerHTML += `
                        <div class="col-md-4 col-sm-6">
                            <div class="card product-card h-100" onclick="addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.selling_price}, ${p.stock_quantity})">
                                <div class="card-body text-center">
                                    <h6 class="card-title">${p.name}</h6>
                                    <p class="card-text text-primary fw-bold">₦${parseFloat(p.selling_price).toFixed(2)}</p>
                                    <small class="text-muted">Stock: ${p.stock_quantity}</small>
                                    ${isLow ? '<br><span class="badge bg-danger mt-1">Low Stock</span>' : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
            })
            .catch(err => console.error('Search Error:', err));
    }

    // Auto-load on page load
    fetchProducts();

    // Search Listener
    searchBar.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        fetchProducts(query);
    });

    // --- FEATURE B: ADD TO CART ---
    window.addToCart = (id, name, price, stock) => {
        // Check if item exists
        const existingItem = cart.find(item => item.id === id);

        if (existingItem) {
            if (existingItem.qty < stock) {
                existingItem.qty++;
            } else {
                alert('Cannot add more. Stock limit reached!');
            }
        } else {
            cart.push({ id, name, price, qty: 1, max_stock: stock });
        }

        renderCart();
    };

    window.removeFromCart = (index) => {
        cart.splice(index, 1);
        renderCart();
    };

    function renderCart() {
        cartBody.innerHTML = '';
        let total = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;

            cartBody.innerHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td class="text-end">${parseFloat(item.price).toFixed(2)}</td>
                    <td class="text-center">
                        <input type="number" min="1" max="${item.max_stock}" value="${item.qty}" 
                            class="form-control form-control-sm text-center p-0" 
                            style="width: 50px; margin: 0 auto;"
                            onchange="updateQty(${index}, this.value)">
                    </td>
                    <td class="text-end">${itemTotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-danger py-0" onclick="removeFromCart(${index})">&times;</button>
                    </td>
                </tr>
            `;
        });

        grandTotalSpan.innerText = total.toFixed(2);
    }

    window.updateQty = (index, newQty) => {
        newQty = parseInt(newQty);
        if (newQty > 0 && newQty <= cart[index].max_stock) {
            cart[index].qty = newQty;
        } else {
            alert(`Invalid Quantity! Max available: ${cart[index].max_stock}`);
        }
        renderCart();
    };

    // --- FEATURE C: PROCESS SALE (Refactored) ---
    function processSale(shouldPrint) {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        const activeBtn = shouldPrint ? btnPrint : btnSave;
        const originalText = activeBtn.innerHTML;

        // Disable both buttons
        btnSave.disabled = true;
        btnPrint.disabled = true;
        activeBtn.innerText = 'Processing...';

        fetch('actions/save_sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cart)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Success Logic
                    if (shouldPrint) {
                        window.open('receipt?id=' + data.sale_id, '_blank', 'width=600,height=800');
                    } else {
                        alert('Sale Successful!');
                    }

                    // Cleanup
                    cart = [];
                    renderCart();
                    searchBar.value = '';
                    fetchProducts(); // Reload Quick Pick list
                    searchBar.focus(); // Ready for next sale
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Checkout Error:', err);
                alert('System Error during checkout.');
            })
            .finally(() => {
                btnSave.disabled = false;
                btnPrint.disabled = false;
                activeBtn.innerHTML = originalText;
            });
    }

    // Event Listeners for Checkout
    if (btnSave) btnSave.addEventListener('click', () => processSale(false));
    if (btnPrint) btnPrint.addEventListener('click', () => processSale(true));
});
