<?php
/* ============================================================
   confirm.php  –  Order Confirmation (Post-Redirect-Get)
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */
require 'menu_data.php';
ensure_session();

$order = $_SESSION['last_order'] ?? null;

/* If no order in session, redirect to menu */
if (!$order) {
    header('Location: index.php');
    exit;
}

/* Clear last_order so refresh doesn't re-show stale data
   (the order still lives in $_SESSION['orders']) */
unset($_SESSION['last_order']);

$cart_count = cart_count();
$steps      = progress_steps($order['status']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed – IIT (ISM) Canteen</title>
    <meta name="description" content="Your canteen order has been placed successfully.">
    <link rel="stylesheet" href="styles.css">
    <style>
      /* Bounce-in for the confirmation card */
      @keyframes bounceIn {
        0%   { opacity:0; transform: scale(0.8) translateY(30px); }
        60%  { opacity:1; transform: scale(1.03) translateY(-6px); }
        100% { opacity:1; transform: scale(1)    translateY(0); }
      }
      .confirm-box { animation: bounceIn 0.55s cubic-bezier(0.4,0,0.2,1) both; }
    </style>
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a class="nav-brand" href="index.php"><span class="emoji">🍽️</span> ISM Canteen</a>
    <ul class="nav-links">
      <li><a href="index.php" id="nav-menu">🏠 Menu</a></li>
      <li><a href="cart.php" id="nav-cart">🛒 Cart</a></li>
      <li><a href="orders.php" id="nav-orders" class="active">📦 My Orders</a></li>
      <li><a href="admin.php" id="nav-admin">⚙️ Admin</a></li>
    </ul>
  </nav>
</header>

<main class="page-content">
  <div class="confirm-box card" id="confirm-box">
    <span class="confirm-icon">🎉</span>
    <h1 style="font-size:1.3rem;font-weight:700;margin-bottom:4px;">Order Placed Successfully!</h1>
    <div class="confirm-order-no" id="confirm-order-no"><?php echo htmlspecialchars($order['order_no']); ?></div>
    <p class="confirm-hint">Save your order number to track status on the Orders page.</p>

    <!-- Status Progress -->
    <div class="status-progress" id="status-progress" aria-label="Order status">
      <?php foreach ($steps as $step): ?>
      <div class="progress-step <?php echo htmlspecialchars($step['class']); ?>">
        <div class="progress-dot">
          <?php echo $step['class']==='done' ? '✓' : ($step['class']==='active' ? '●' : ''); ?>
        </div>
        <div class="progress-label"><?php echo htmlspecialchars($step['label']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Details -->
    <div class="confirm-details">
      <div class="confirm-detail-row">
        <span>Customer</span>
        <span><?php echo htmlspecialchars($order['name']); ?></span>
      </div>
      <div class="confirm-detail-row">
        <span>Phone / Roll</span>
        <span><?php echo htmlspecialchars($order['phone']); ?></span>
      </div>
      <div class="confirm-detail-row">
        <span>Status</span>
        <span class="status-badge <?php echo status_class($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
      </div>
      <div class="confirm-detail-row">
        <span>Placed At</span>
        <span><?php echo htmlspecialchars($order['placed_at']); ?></span>
      </div>
      <?php if ($order['note']): ?>
      <div class="confirm-detail-row">
        <span>Note</span>
        <span><?php echo htmlspecialchars($order['note']); ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Items -->
    <div class="confirm-details">
      <div style="font-size:0.85rem;font-weight:700;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">Items Ordered</div>
      <?php foreach ($order['items'] as $item): ?>
      <div class="confirm-detail-row">
        <span><?php echo $item['emoji']; ?> <?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['qty']; ?></span>
        <span><?php echo format_price($item['price'] * $item['qty']); ?></span>
      </div>
      <?php endforeach; ?>
      <div class="confirm-detail-row" style="font-weight:700;color:var(--text-primary);margin-top:4px;">
        <span>Total Paid</span>
        <span style="color:var(--accent-light);"><?php echo format_price($order['total']); ?></span>
      </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
      <a href="orders.php" class="btn btn-primary" id="btn-track-orders">📦 Track Orders</a>
      <a href="index.php" class="btn btn-secondary" id="btn-order-more">🍽️ Order More</a>
    </div>
  </div>
</main>

<footer class="site-footer">
  <p>NCSM301 &ndash; Web Development Laboratory &nbsp;|&nbsp; IIT (ISM) Dhanbad</p>
</footer>

</body>
</html>
