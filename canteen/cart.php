<?php
/* ============================================================
   cart.php  –  View Cart, Update Quantities, Checkout Form
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */
require 'menu_data.php';
ensure_session();

/* Handle quantity update / remove */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'update') {
        $id  = isset($_POST['id'])  ? trim($_POST['id'])  : '';
        $qty = isset($_POST['qty']) ? (int)$_POST['qty']  : 0;
        if ($id !== '') cart_update($id, $qty);
        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove') {
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        if ($id !== '') cart_remove($id);
        $_SESSION['flash'] = ['type'=>'info','msg'=>'Item removed from cart.'];
        header('Location: cart.php');
        exit;
    }

    if ($action === 'checkout') {
        $name  = isset($_POST['name'])  ? trim($_POST['name'])  : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $note  = isset($_POST['note'])  ? trim($_POST['note'])  : '';

        if ($name === '' || $phone === '') {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Name and phone number are required.'];
            header('Location: cart.php');
            exit;
        }

        if (empty(cart_rows())) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Your cart is empty.'];
            header('Location: cart.php');
            exit;
        }

        $order = place_order($name, $phone, $note);
        $_SESSION['last_order'] = $order;
        header('Location: confirm.php');
        exit;
    }
}

$rows        = cart_rows();
$total       = cart_total();
$cart_count  = cart_count();
$flash       = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart – IIT (ISM) Canteen</title>
    <meta name="description" content="Review your canteen cart items and place a pre-order.">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a class="nav-brand" href="index.php"><span class="emoji">🍽️</span> ISM Canteen</a>
    <ul class="nav-links">
      <li><a href="index.php" id="nav-menu">🏠 Menu</a></li>
      <li><a href="cart.php" class="active" id="nav-cart">
        🛒 Cart
        <?php if ($cart_count > 0): ?>
          <span class="cart-badge" id="cart-count-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="orders.php" id="nav-orders">📦 My Orders</a></li>
      <li><a href="admin.php" id="nav-admin">⚙️ Admin</a></li>
    </ul>
  </nav>
</header>

<section class="page-hero" style="padding-bottom:20px;">
  <h1>Your Cart</h1>
  <p><?php echo $cart_count > 0 ? $cart_count . ' item(s) ready to order' : 'Nothing here yet – browse the menu!'; ?></p>
</section>

<main class="page-content">

  <?php if ($flash): ?>
  <div class="flash flash-<?php echo htmlspecialchars($flash['type']); ?>">
    <?php echo match($flash['type']){ 'success'=>'✅','error'=>'❌',default=>'ℹ️' }; ?>
    <?php echo htmlspecialchars($flash['msg']); ?>
  </div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
  <!-- Empty State -->
  <div class="empty-state">
    <div class="empty-icon">🛒</div>
    <div class="empty-title">Your cart is empty</div>
    <div class="empty-sub">Browse our menu and add your favourite items.</div>
    <a href="index.php" class="btn btn-primary" id="btn-browse-menu">Browse Menu</a>
  </div>

  <?php else: ?>
  <div class="two-col">

    <!-- Left: Cart Items -->
    <div>
      <h2 style="font-size:1rem;font-weight:700;color:var(--text-muted);margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px;">Order Items</h2>
      <div class="cart-items-list" id="cart-items-list">
        <?php foreach ($rows as $row): ?>
        <div class="cart-row" id="cart-row-<?php echo htmlspecialchars($row['id']); ?>">
          <span class="cart-item-emoji"><?php echo $row['emoji']; ?></span>
          <div class="cart-item-info">
            <div class="cart-item-name"><?php echo htmlspecialchars($row['name']); ?></div>
            <div class="cart-item-unit"><?php echo format_price($row['price']); ?> each</div>
          </div>

          <!-- Qty Control -->
          <form method="post" action="cart.php" id="qty-form-<?php echo htmlspecialchars($row['id']); ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <div class="qty-control">
              <button type="submit" class="qty-btn" name="qty"
                value="<?php echo max(0, $row['qty']-1); ?>"
                id="dec-<?php echo htmlspecialchars($row['id']); ?>"
                title="Decrease quantity">−</button>
              <span class="qty-val" id="qty-val-<?php echo htmlspecialchars($row['id']); ?>"><?php echo $row['qty']; ?></span>
              <button type="submit" class="qty-btn" name="qty"
                value="<?php echo $row['qty']+1; ?>"
                id="inc-<?php echo htmlspecialchars($row['id']); ?>"
                title="Increase quantity">+</button>
            </div>
          </form>

          <span class="cart-item-total" id="total-<?php echo htmlspecialchars($row['id']); ?>">
            <?php echo format_price($row['price'] * $row['qty']); ?>
          </span>

          <!-- Remove -->
          <form method="post" action="cart.php" id="remove-form-<?php echo htmlspecialchars($row['id']); ?>">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <button type="submit" class="btn btn-danger btn-sm" id="remove-<?php echo htmlspecialchars($row['id']); ?>" title="Remove item">🗑</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right: Summary + Checkout -->
    <div class="summary-card">
      <div class="card">
        <div class="summary-title">Order Summary</div>

        <div class="summary-rows">
          <?php foreach ($rows as $row): ?>
          <div class="summary-row">
            <span><?php echo htmlspecialchars($row['name']); ?> × <?php echo $row['qty']; ?></span>
            <span><?php echo format_price($row['price'] * $row['qty']); ?></span>
          </div>
          <?php endforeach; ?>
          <div class="summary-row">
            <span>Subtotal</span>
            <span><?php echo format_price($total); ?></span>
          </div>
          <div class="summary-row">
            <span>Platform Fee</span>
            <span>₹0.00</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span id="grand-total"><?php echo format_price($total); ?></span>
          </div>
        </div>

        <!-- Checkout Form -->
        <form method="post" action="cart.php" id="checkout-form">
          <input type="hidden" name="action" value="checkout">

          <div class="form-group">
            <label for="checkout-name">Your Name</label>
            <input type="text" id="checkout-name" name="name"
              placeholder="e.g. Aksha Kumar" required maxlength="80">
          </div>

          <div class="form-group">
            <label for="checkout-phone">Phone / Roll No.</label>
            <input type="tel" id="checkout-phone" name="phone"
              placeholder="e.g. 22JE0001" required maxlength="20">
          </div>

          <div class="form-group">
            <label for="checkout-note">Special Instructions (optional)</label>
            <textarea id="checkout-note" name="note" rows="2"
              placeholder="e.g. Less spicy, extra chutney…" maxlength="200"></textarea>
          </div>

          <button type="submit" class="btn btn-success btn-full" id="btn-place-order">
            ✅ Place Order
          </button>
        </form>

        <div style="margin-top:12px; text-align:center;">
          <a href="index.php" class="btn btn-secondary btn-full" id="btn-continue-shopping" style="margin-top:8px;">
            ← Continue Shopping
          </a>
        </div>
      </div>
    </div>

  </div>
  <?php endif; ?>

</main>

<footer class="site-footer">
  <p>NCSM301 &ndash; Web Development Laboratory &nbsp;|&nbsp; IIT (ISM) Dhanbad</p>
</footer>

</body>
</html>
