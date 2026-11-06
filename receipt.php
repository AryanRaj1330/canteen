<?php
require_once 'db.php';

$orderNo = trim($_GET['order_no'] ?? '');
if (empty($orderNo)) {
    die("Error: No order number provided.");
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ?");
$stmt->execute([$orderNo]);
$order = $stmt->fetch();

if (!$order) {
    die("Error: Order not found.");
}

$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= htmlspecialchars($order['order_no']) ?> - Campus Canteen</title>
    <style>
        body { font-family: monospace, sans-serif; background: #f8fafc; padding: 20px; }
        .receipt-card { max-width: 400px; margin: 0 auto; background: #fff; padding: 24px; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .receipt-header { text-align: center; border-bottom: 2px dashed #94a3b8; padding-bottom: 12px; margin-bottom: 14px; }
        .receipt-header h2 { margin: 0 0 4px; font-size: 1.4rem; color: #1e293b; }
        .table-receipt { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .table-receipt th, .table-receipt td { padding: 6px 0; text-align: left; font-size: 0.9rem; }
        .table-receipt th { border-bottom: 1px solid #cbd5e1; }
        .table-receipt td.num { text-align: right; }
        .receipt-total { border-top: 2px dashed #94a3b8; padding-top: 10px; margin-top: 10px; font-size: 1.1rem; font-weight: bold; display: flex; justify-content: space-between; }
        .btn-print { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 20px; font-size: 1rem; }
        @media print { .btn-print { display: none; } body { background: #fff; padding: 0; } .receipt-card { border: none; box-shadow: none; width: 100%; } }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="receipt-header">
            <h2>CAMPUS CANTEEN</h2>
            <div style="font-size: 0.85rem; color: #64748b;">Tax Invoice & Cash Receipt</div>
            <div style="margin-top: 6px; font-size: 0.95rem; font-weight: bold;">Order #<?= htmlspecialchars($order['order_no']) ?></div>
            <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($order['created_at']) ?></div>
        </div>

        <div style="font-size: 0.85rem; margin-bottom: 12px;">
            <div><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
            <div><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></div>
            <div><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></div>
        </div>

        <table class="table-receipt">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="num">Qty</th>
                    <th class="num">Price</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['item_name']) ?></td>
                        <td class="num">x<?= $it['quantity'] ?></td>
                        <td class="num">₹<?= number_format($it['price'], 2) ?></td>
                        <td class="num">₹<?= number_format($it['price'] * $it['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="receipt-total">
            <span>TOTAL AMOUNT PAID:</span>
            <span>₹<?= number_format($order['total_amount'], 2) ?></span>
        </div>

        <div style="text-align: center; font-size: 0.8rem; color: #64748b; margin-top: 16px;">
            Thank you for dining with Campus Canteen!<br>Please show this receipt at the collection counter.
        </div>

        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
</body>
</html>
