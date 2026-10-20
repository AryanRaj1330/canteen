<?php
/* ============================================================
   index.php  –  Canteen Menu & Add-to-Cart
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */
require 'menu_data.php';
ensure_session();

/* Handle add-to-cart POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $id  = isset($_POST['id'])  ? trim($_POST['id'])  : '';
    $qty = isset($_POST['qty']) ? (int)$_POST['qty']  : 1;
    if ($id !== '' && $qty >= 1) {
        cart_add($id, $qty);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Item added to cart!'];
    }
    header('Location: index.php' . (isset($_GET['cat']) ? '?cat=' . urlencode($_GET['cat']) : ''));
    exit;
}

$all_items  = get_menu_items();
$categories = array_unique(array_column($all_items, 'category'));
$active_cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'All';

$displayed  = ($active_cat === 'All')
    ? $all_items
    : array_filter($all_items, fn($i) => $i['category'] === $active_cat);

$flash       = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cart_count  = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu – IIT (ISM) Canteen</title>
    <meta name="description" content="Browse the IIT ISM Canteen menu, check availability and pre-order your meals.">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- ── Header ── -->
<header class="site-header">
  <nav class="nav">
    <a class="nav-brand" href="index.php">
      <span class="emoji">🍽️</span> ISM Canteen
    </a>
    <ul class="nav-links">
      <li><a href="index.php" class="active" id="nav-menu">🏠 Menu</a></li>
      <li><a href="cart.php" id="nav-cart">
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

<!-- ── Hero ── -->
<section class="page-hero">
  <h1>Today's Menu</h1>
  <p>Pre-order your favourite meals, track your queue position in real time.</p>
</section>

<!-- ── Flash ── -->
<?php if ($flash): ?>
<div class="wrapper">
  <div class="flash flash-<?php echo htmlspecialchars($flash['type']); ?>">
    <?php echo ($flash['type']==='success') ? '✅' : 'ℹ️'; ?>
    <?php echo htmlspecialchars($flash['msg']); ?>
  </div>
</div>
<?php endif; ?>

<!-- ── Menu ── -->
<main class="menu-section wrapper">

  <!-- Category Tabs -->
  <div class="category-tabs" role="tablist">
    <button id="tab-all"
      class="tab-btn <?php echo $active_cat==='All'?'active':''; ?>"
      onclick="window.location='index.php'">
      All
    </button>
    <?php foreach ($categories as $cat): ?>
    <button id="tab-<?php echo htmlspecialchars(strtolower($cat)); ?>"
      class="tab-btn <?php echo $active_cat===$cat?'active':''; ?>"
      onclick="window.location='index.php?cat=<?php echo urlencode($cat); ?>'">
      <?php echo htmlspecialchars($cat); ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Grid -->
  <div class="menu-grid" id="menu-grid">
    <?php foreach ($displayed as $item): ?>
    <article class="menu-item <?php echo $item['available'] ? '' : 'unavailable'; ?>" id="item-<?php echo htmlspecialchars($item['id']); ?>">

      <div class="item-image-placeholder"><?php echo $item['emoji']; ?></div>

      <?php if ($item['available']): ?>
        <span class="badge-avail">Available</span>
      <?php else: ?>
        <span class="badge-unavail">Sold Out</span>
      <?php endif; ?>

      <div class="item-body">
        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
        <div class="item-desc"><?php echo htmlspecialchars($item['desc']); ?></div>
      </div>

      <div class="item-footer">
        <span class="item-price"><?php echo format_price($item['price']); ?></span>

        <?php if ($item['available']): ?>
        <form method="post" action="index.php<?php echo isset($_GET['cat']) ? '?cat='.urlencode($_GET['cat']) : ''; ?>" id="form-<?php echo htmlspecialchars($item['id']); ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
          <input type="hidden" name="qty" value="1">
          <button type="submit" class="btn btn-primary btn-sm" id="add-<?php echo htmlspecialchars($item['id']); ?>">
            + Add
          </button>
        </form>
        <?php else: ?>
        <button class="btn btn-secondary btn-sm" disabled>Unavailable</button>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

</main>

<footer class="site-footer">
  <p>NCSM301 &ndash; Web Development Laboratory &nbsp;|&nbsp; IIT (ISM) Dhanbad</p>
</footer>

</body>
</html>
