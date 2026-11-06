<?php
require_once 'db.php';
$orderNo = trim($_GET['order_no'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ?");
$stmt->execute([$orderNo]);
$order = $stmt->fetch();
$items = [];
if ($order) {
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order['id']]);
    $items = $itemStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head><title>Receipt #<?= htmlspecialchars($orderNo) ?></title><link rel="stylesheet" href="styles.css"></head>
<body style="padding: 20px; background: #f8fafc;">
    <div style="max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <h2 style="text-align: center; margin-bottom: 4px;">CAMPUS CANTEEN</h2>
        <p style="text-align: center; color: #64748b; font-size: 0.85rem; margin-bottom: 16px;">Order Receipt #<?= htmlspecialchars($orderNo) ?></p>
        <?php if ($order): ?>
            <div><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
            <div><strong>Time:</strong> <?= htmlspecialchars($order['created_at']) ?></div>
            <hr style="margin: 12px 0; border: none; border-top: 1px dashed #cbd5e1;">
            <?php foreach ($items as $it): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span><?= htmlspecialchars($it['item_name']) ?> (x<?= $it['quantity'] ?>)</span>
                    <span>₹<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <hr style="margin: 12px 0; border: none; border-top: 1px dashed #cbd5e1;">
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem;">
                <span>Total:</span>
                <span>₹<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <button onclick="window.print()" class="btn btn-primary" style="width: 100%; margin-top: 16px;">🖨️ Print Receipt</button>
        <?php else: ?>
            <p>Order not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>