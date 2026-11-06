<?php
require_once 'db.php';
$phone = trim($_GET['phone'] ?? '');
$orders = [];
if (!empty($phone)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY id DESC");
    $stmt->execute([$phone]);
    $orders = $stmt->fetchAll();

    $items = $pdo->query("SELECT * FROM order_items")->fetchAll();
    $itemsMap = [];
    foreach ($items as $item) {
        $itemsMap[$item['order_id']][] = $item['item_name'] . ' (x' . $item['quantity'] . ')';
    }
    foreach ($orders as &$order) {
        $order['items_summary'] = isset($itemsMap[$order['id']]) ? implode(', ', $itemsMap[$order['id']]) : 'No items';
        $order['order_time'] = !empty($order['created_at']) ? date('M d, Y h:i A', strtotime($order['created_at'])) : date('M d, Y');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Re-Order - Campus Canteen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="brand">Campus Canteen</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="history.php" class="active">My Orders</a></li>
            <li><a href="admin.php">Staff Dashboard</a></li>
        </ul>
    </nav>

    <div class="container" style="max-width: 700px; margin: 0 auto;">
        <h1 class="page-title">Order History</h1>
        <form method="GET" action="history.php" style="display: flex; gap: 8px; margin-bottom: 24px;">
            <input type="tel" name="phone" class="form-control" placeholder="Enter 10-digit Phone..." value="<?= htmlspecialchars($phone) ?>" required>
            <button type="submit" class="btn btn-primary">Find Orders</button>
        </form>

        <?php if (!empty($orders)): ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($orders as $o): ?>
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div><strong>#<?= htmlspecialchars($o['order_no']) ?></strong> <span style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($o['order_time']) ?></span></div>
                            <span class="badge badge-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></span>
                        </div>
                        <div style="color: #475569; margin-bottom: 12px; font-size: 0.9rem;"><?= htmlspecialchars($o['items_summary']) ?></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                            <strong>₹<?= number_format($o['total_amount'], 2) ?></strong>
                            <button class="btn btn-primary btn-sm reorder-btn" data-id="<?= $o['id'] ?>">🔁 Re-Order</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $('.reorder-btn').on('click', function() {
        var orderId = $(this).data('id');
        $.post('api.php', { action: 'reorder', order_id: orderId }, function(res) {
            if (res.success) {
                alert('Items from this order have been loaded into your cart!');
                window.location.href = 'index.php';
            } else {
                alert(res.message);
            }
        }, 'json');
    });
    </script>
</body>
</html>
