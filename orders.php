<?php
require_once 'db.php';

// Initial fetch of orders with item summaries
$orders = fetchAllOrdersWithItems($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Canteen Queue Status</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav class="navbar">
        <div class="brand">Campus Canteen</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php" class="active">Queue Status</a></li>
            <?php $pendingCountNav = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'Ready'")->fetchColumn(); ?>
            <li><a href="admin.php">Staff Dashboard <span class="badge" style="background: #dc2626; color: #fff; border-radius: 10px; padding: 2px 7px; font-size: 0.75rem;"><?= $pendingCountNav ?></span></a></li>
        </ul>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 class="page-title" style="margin-bottom: 4px;">Live Order Queue</h1>
                <p style="color: #666; font-size: 0.9rem;">
                    Auto-updating every 3 seconds &bull; Last updated: <span id="last-updated">Just now</span>
                </p>
            </div>
            <button id="manual-refresh-btn" class="btn btn-primary btn-sm">↻ Refresh Now</button>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items Ordered</th>
                        <th>Total</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <?php if (empty($orders)): ?>
                        <tr class="no-orders-row">
                            <td colspan="6" style="text-align: center; color: #888; padding: 24px;">No active orders yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_no']) ?></strong></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['items_summary'] ?: 'No items') ?></td>
                                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['order_time']) ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'badge-received';
                                        if ($order['status'] === 'Preparing') $badgeClass = 'badge-preparing';
                                        if ($order['status'] === 'Ready') $badgeClass = 'badge-ready';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
    $(document).ready(function() {

        // Helper function to return correct badge CSS class
        function getBadgeClass(status) {
            switch (status) {
                case 'Preparing': return 'badge-preparing';
                case 'Ready': return 'badge-ready';
                default: return 'badge-received';
            }
        }

        // AJAX function to fetch live order list from api.php
        function refreshOrders() {
            $.get('api.php', { action: 'get_orders' }, function(res) {
                if (res.success) {
                    var $tbody = $('#orders-tbody');
                    $tbody.empty();

                    if (!res.orders || res.orders.length === 0) {
                        $tbody.html('<tr class="no-orders-row"><td colspan="6" style="text-align: center; color: #888; padding: 24px;">No active orders yet.</td></tr>');
                    } else {
                        $.each(res.orders, function(index, o) {
                            var badgeClass = getBadgeClass(o.status);
                            var row = `
                                <tr>
                                    <td><strong>${o.order_no}</strong></td>
                                    <td>${o.customer_name}</td>
                                    <td>${o.items_summary || 'No items'}</td>
                                    <td>₹${parseFloat(o.total_amount).toFixed(2)}</td>
                                    <td>${o.order_time}</td>
                                    <td><span class="badge ${badgeClass}">${o.status}</span></td>
                                </tr>
                            `;
                            $tbody.append(row);
                        });
                    }

                    // Update timestamp display
                    var now = new Date();
                    $('#last-updated').text(now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
                }
            }, 'json');
        }

        // Periodic live queue update every 3 seconds
        var timer = setInterval(refreshOrders, 3000);

        // Manual refresh button click
        $('#manual-refresh-btn').on('click', function() {
            refreshOrders();
        });

    });
    </script>
</body>
</html>
