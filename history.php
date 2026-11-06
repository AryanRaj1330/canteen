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
    <title>My Past Orders - Campus Canteen</title>
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

    <div class="container">
        <div style="max-width: 700px; margin: 0 auto;">
            <h1 class="page-title">Order History</h1>
            <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px;">
                Enter your 10-digit mobile number to view all your previous canteen orders.
            </p>

            <form method="GET" action="history.php" style="display: flex; gap: 8px; margin-bottom: 24px;">
                <input type="tel" name="phone" class="form-control" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($phone) ?>" required style="font-size: 1rem;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Find Orders</button>
            </form>

            <?php if (!empty($phone)): ?>
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">No previous orders found for phone number <strong><?= htmlspecialchars($phone) ?></strong>.</div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($orders as $o): ?>
                            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <div>
                                        <span style="font-weight: 700; font-size: 1.1rem; color: #1e293b;">#<?= htmlspecialchars($o['order_no']) ?></span>
                                        <span style="font-size: 0.85rem; color: #64748b; margin-left: 8px;"><?= htmlspecialchars($o['order_time']) ?></span>
                                    </div>
                                    <span class="badge badge-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></span>
                                </div>
                                <div style="color: #475569; font-size: 0.9rem; margin-bottom: 10px;">
                                    <strong>Items:</strong> <?= htmlspecialchars($o['items_summary']) ?>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                                    <span style="font-size: 0.95rem; font-weight: 700;">Total: ₹<?= number_format($o['total_amount'], 2) ?></span>
                                    <span style="font-size: 0.85rem; color: #64748b;">Customer: <?= htmlspecialchars($o['customer_name']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
