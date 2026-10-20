<?php
/* ============================================================
   orders.php  –  Customer: View All My Orders & Live Status
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */
require 'menu_data.php';
ensure_session();

$orders     = get_orders();
$cart_count = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders – IIT (ISM) Canteen</title>
    <meta name="description" content="Track the status of your canteen pre-orders in real time.">
    <link rel="stylesheet" href="styles.css">
    <style>
      /* Auto-refresh indicator */
      .refresh-note { font-size: 0.78rem; color: var(--text-dim); margin-left: 6px; }
    </style>
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a class="nav-brand" href="index.php"><span class="emoji">🍽️</span> ISM Canteen</a>
    <ul class="nav-links">
      <li><a href="index.php" id="nav-menu">🏠 Menu</a></li>
      <li><a href="cart.php" id="nav-cart">
        🛒 Cart
        <?php if ($cart_count > 0): ?>
          <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="orders.php" class="active" id="nav-orders">📦 My Orders</a></li>
      <li><a href="admin.php" id="nav-admin">⚙️ Admin</a></li>
    </ul>
  </nav>
</header>

<section class="page-hero" style="padding-bottom:20px;">
  <h1>My Orders</h1>
  <p>Real-time status for all your canteen pre-orders this session.
    <span class="refresh-note">(Refreshes every 15s)</span></p>
</section>

<main class="page-content">

  <?php if (empty($orders)): ?>
  <div class="empty-state">
    <div class="empty-icon">📦</div>
    <div class="empty-title">No orders yet</div>
    <div class="empty-sub">Head to the menu, add items and place your first order!</div>
    <a href="index.php" class="btn btn-primary" id="btn-go-menu">Browse Menu</a>
  </div>

  <?php else: ?>
  <div class="orders-grid" id="orders-grid">
    <?php foreach ($orders as $idx => $order): ?>
    <article class="card order-card" id="order-card-<?php echo htmlspecialchars($order['order_no']); ?>">

      <!-- Header: Order No + Status Badge -->
      <div class="order-header">
        <div class="order-number">
          <small>Order Number</small>
          <?php echo htmlspecialchars($order['order_no']); ?>
        </div>
        <span class="status-badge <?php echo status_class($order['status']); ?>" id="badge-<?php echo htmlspecialchars($order['order_no']); ?>">
          <?php
            echo match($order['status']) {
              'Received'  => '📬',
              'Preparing' => '👨‍🍳',
              'Ready'     => '✅',
              default     => '📬'
            };
          ?>
          <?php echo htmlspecialchars($order['status']); ?>
        </span>
      </div>

      <!-- Status Progress Steps -->
      <div class="status-progress">
        <?php foreach (progress_steps($order['status']) as $step): ?>
        <div class="progress-step <?php echo htmlspecialchars($step['class']); ?>">
          <div class="progress-dot">
            <?php echo $step['class']==='done' ? '✓' : ($step['class']==='active' ? '●' : ''); ?>
          </div>
          <div class="progress-label"><?php echo htmlspecialchars($step['label']); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Items List -->
      <div class="order-items-list">
        <?php foreach ($order['items'] as $item): ?>
        <div class="order-item-row">
          <span><?php echo $item['emoji']; ?> <?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['qty']; ?></span>
          <span><?php echo format_price($item['price'] * $item['qty']); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Footer: Total + Meta -->
      <div class="order-footer">
        <div>
          <div class="order-total-label">Total Amount</div>
          <div class="order-total-amount"><?php echo format_price($order['total']); ?></div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:0.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($order['name']); ?></div>
          <div style="font-size:0.75rem;color:var(--text-dim);"><?php echo htmlspecialchars($order['placed_at']); ?></div>
          <?php if ($order['note']): ?>
          <div style="font-size:0.75rem;color:var(--text-dim);margin-top:4px;">📝 <?php echo htmlspecialchars($order['note']); ?></div>
          <?php endif; ?>
        </div>
      </div>

    </article>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;margin-top:28px;">
    <a href="index.php" class="btn btn-primary" id="btn-order-more">+ Order More</a>
  </div>
  <?php endif; ?>

</main>

<footer class="site-footer">
  <p>NCSM301 &ndash; Web Development Laboratory &nbsp;|&nbsp; IIT (ISM) Dhanbad</p>
</footer>

<!-- Auto-refresh every 15 seconds to pick up admin status changes -->
<script>
  setTimeout(function() { window.location.reload(); }, 15000);
</script>

</body>
</html>
