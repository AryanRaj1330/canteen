<?php
require_once 'db.php';

// Fetch available menu items from database
$stmt = $pdo->query("SELECT * FROM items WHERE is_available = 1 ORDER BY category ASC, name ASC");
$items = $stmt->fetchAll();

// Group items by category for clean presentation
$categories = [];
foreach ($items as $item) {
    $categories[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Pre-Order</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav class="navbar">
        <div class="brand">Campus Canteen</div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="admin.php">Staff Dashboard</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Order Placement Notification Area -->
        <div id="order-alert" style="display: none;"></div>

        <div class="main-layout">
            <!-- Left Column: Menu Items -->
            <div class="menu-section">
                <h1 class="page-title">Available Menu</h1>

                <?php if (empty($categories)): ?>
                    <p>No menu items available right now. Please check back later.</p>
                <?php else: ?>
                    <?php foreach ($categories as $categoryName => $catItems): ?>
                        <h2 style="font-size: 1.25rem; margin: 20px 0 12px; color: #34495e; border-bottom: 2px solid #ddd; padding-bottom: 4px;">
                            <?= htmlspecialchars($categoryName) ?>
                        </h2>
                        <div class="menu-grid">
                            <?php foreach ($catItems as $item): ?>
                                <div class="item-card">
                                    <div>
                                        <span class="item-badge"><?= htmlspecialchars($item['category']) ?></span>
                                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="item-price">₹<?= number_format($item['price'], 2) ?></div>
                                    </div>
                                    <button class="btn btn-primary add-to-cart-btn" data-id="<?= $item['id'] ?>">
                                        + Add to Cart
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right Column: Cart Side Section -->
            <div class="cart-section">
                <div class="cart-title">
                    <span>Your Cart</span>
                    <span id="cart-count-badge" style="font-size: 0.9rem; font-weight: normal; color: #666;">(0 items)</span>
                </div>

                <!-- Dynamic Cart Items Container -->
                <ul class="cart-items" id="cart-items-list">
                    <!-- Populated dynamically via jQuery -->
                </ul>

                <!-- Cart Total Box -->
                <div class="cart-total-box">
                    <span>Total Amount:</span>
                    <span>₹<span id="cart-total">0.00</span></span>
                </div>

                <!-- Customer Details & Checkout Form -->
                <form id="checkout-form">
                    <div class="form-group">
                        <label for="customer_name">Your Name</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>

                    <button type="submit" id="place-order-btn" class="btn btn-success" disabled>
                        Place Pre-Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery CDN (Standard library for AJAX calls) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
    $(document).ready(function() {

        // Function to render cart contents from server response
        function renderCart(cartData) {
            var $list = $('#cart-items-list');
            $list.empty();

            $('#cart-total').text(cartData.total);
            $('#cart-count-badge').text('(' + cartData.count + ' items)');

            // Toggle Checkout button depending on items in cart
            if (cartData.items.length === 0) {
                $list.html('<li class="cart-empty-msg">Your cart is empty</li>');
                $('#place-order-btn').prop('disabled', true);
            } else {
                $('#place-order-btn').prop('disabled', false);

                $.each(cartData.items, function(index, item) {
                    var html = `
                        <li class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-unit-price">₹${item.price} each</div>
                            </div>
                            <div class="cart-item-actions">
                                <button class="btn btn-qty update-qty-btn" data-id="${item.id}" data-qty="${item.qty - 1}">-</button>
                                <span class="cart-item-qty">${item.qty}</span>
                                <button class="btn btn-qty update-qty-btn" data-id="${item.id}" data-qty="${item.qty + 1}">+</button>
                                <span class="cart-item-total">₹${item.subtotal}</span>
                                <button class="btn btn-sm btn-danger-outline remove-item-btn" data-id="${item.id}" title="Remove">✕</button>
                            </div>
                        </li>
                    `;
                    $list.append(html);
                });
            }
        }

        // 1. Initial Load: Fetch existing cart on page load
        $.get('api.php', { action: 'get_cart' }, function(res) {
            if (res.success) {
                renderCart(res.cart);
            }
        });

        // 2. Add Item to Cart via AJAX
        $('.add-to-cart-btn').on('click', function() {
            var itemId = $(this).data('id');
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post('api.php', { action: 'add', item_id: itemId }, function(res) {
                $btn.prop('disabled', false);
                if (res.success) {
                    renderCart(res.cart);
                } else {
                    alert(res.message || 'Could not add item');
                }
            }, 'json');
        });

        // 3. Update Item Quantity (+ or -)
        $(document).on('click', '.update-qty-btn', function() {
            var itemId = $(this).data('id');
            var newQty = $(this).data('qty');

            $.post('api.php', { action: 'update', item_id: itemId, qty: newQty }, function(res) {
                if (res.success) {
                    renderCart(res.cart);
                }
            }, 'json');
        });

        // 4. Remove Item from Cart
        $(document).on('click', '.remove-item-btn', function() {
            var itemId = $(this).data('id');

            $.post('api.php', { action: 'remove', item_id: itemId }, function(res) {
                if (res.success) {
                    renderCart(res.cart);
                }
            }, 'json');
        });

        // 5. Submit Pre-Order (Checkout)
        $('#checkout-form').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#place-order-btn');
            $btn.prop('disabled', true).text('Placing Order...');

            var formData = $(this).serialize() + '&action=checkout';

            $.post('api.php', formData, function(res) {
                $btn.text('Place Pre-Order');

                if (res.success) {
                    // Show success banner with direct link to live queue
                    $('#order-alert').html(`
                        <div class="alert alert-success">
                            <div>
                                <strong>Success!</strong> ${res.message}.
                            </div>
                            <a href="orders.php" class="btn btn-primary btn-sm">View Queue Status</a>
                        </div>
                    `).show();

                    // Reset form and refresh empty cart view
                    $('#checkout-form')[0].reset();
                    $.get('api.php', { action: 'get_cart' }, function(cartRes) {
                        renderCart(cartRes.cart);
                    });

                    // Scroll to top to see notification
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                } else {
                    $btn.prop('disabled', false);
                    alert(res.message || 'Checkout failed');
                }
            }, 'json').fail(function() {
                $btn.prop('disabled', false).text('Place Pre-Order');
                alert('Server error occurred during checkout.');
            });
        });

    });
    </script>
</body>
</html>
