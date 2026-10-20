<?php
/* ============================================================
   admin.php  –  Staff Queue Dashboard: View & Update Orders
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */
require 'menu_data.php';
ensure_session();

/* Handle status update */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_no = isset($_POST['order_no']) ? trim($_POST['order_no']) : '';
    $status   = isset($_POST['status'])   ? trim($_POST['status'])   : '';
    if ($order_no !== '' && $status !== '') {
        update_order_status($order_no, $status);
        $_SESSION['admin_flash'] = ['type'=>'success','msg'=>"Order {$order_no} updated to {$status}."];
    }
    header('Location: admin.php');
    exit;
}

$all_orders = get_orders();
$flash      = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

/* Stats */
$count_received  = count(array_filter($all_orders, fn($o) => $o['status'] === 'Received'));
$count_preparing = count(array_filter($all_orders, fn($o) => $o['status'] === 'Preparing'));
$count_ready     = count(array_filter($all_orders, fn($o) => $o['status'] === 'Ready'));
$revenue         = array_sum(array_column($all_orders, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard – IIT (ISM) Canteen</title>
    <meta name="description" content="Canteen staff queue management dashboard – update order statuses.">
    <link rel="stylesheet" href="styles.css">
    <style>
      .stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:16px; margin-bottom:28px; }
      .stat-card { padding:20px 22px; display:flex; flex-direction:column; gap:4px; }
      .stat-label { font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; font-weight:600; }
      .stat-value { font-size:2rem; font-weight:800; letter-spacing:-1px; }
      .stat-blue   { color:var(--blue); }
      .stat-yellow { color:var(--yellow); }
      .stat-green  { color:var(--green); }
      .stat-orange { color:var(--accent-light); }
      .table-section { padding:24px; }
      .table-section h2 { font-size:1rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; }
      .filter-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
      .admin-header-note { font-size:0.78rem; color:var(--text-dim); margin-bottom:18px; }
    </style>
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a class="nav-brand" href="index.php"><span class="emoji">🍽️</span> ISM Canteen</a>
    <ul class="nav-links">
      <li><a href="index.php" id="nav-menu">🏠 Menu</a></li>
      <li><a href="cart.php" id="nav-cart">🛒 Cart</a></li>
      <li><a href="orders.php" id="nav-orders">📦 My Orders</a></li>
      <li><a href="admin.php" class="active" id="nav-admin">⚙️ Admin</a></li>
    </ul>
  </nav>
</header>

<section class="page-hero" style="padding-bottom:20px;">
  <h1>Staff Dashboard</h1>
  <p>Manage the canteen queue &ndash; update order statuses as meals are prepared.</p>
</section>

<main class="page-content">

  <?php if ($flash): ?>
  <div class="flash flash-<?php echo htmlspecialchars($flash['type']); ?>">
    <?php echo match($flash['type']){ 'success'=>'✅',default=>'ℹ️' }; ?>
    <?php echo htmlspecialchars($flash['msg']); ?>
  </div>
  <?php endif; ?>

  <!-- Stats Row -->
  <div class="stat-grid">
    <div class="card stat-card">
      <span class="stat-label">📬 Received</span>
      <span class="stat-value stat-blue"><?php echo $count_received; ?></span>
    </div>
    <div class="card stat-card">
      <span class="stat-label">👨‍🍳 Preparing</span>
      <span class="stat-value stat-yellow"><?php echo $count_preparing; ?></span>
    </div>
    <div class="card stat-card">
      <span class="stat-label">✅ Ready</span>
      <span class="stat-value stat-green"><?php echo $count_ready; ?></span>
    </div>
    <div class="card stat-card">
      <span class="stat-label">💰 Session Revenue</span>
      <span class="stat-value stat-orange"><?php echo format_price($revenue); ?></span>
    </div>
  </div>

  <?php if (empty($all_orders)): ?>
  <div class="empty-state">
    <div class="empty-icon">🗂️</div>
    <div class="empty-title">No orders this session</div>
    <div class="empty-sub">Orders will appear here once customers place them.</div>
  </div>

  <?php else: ?>

  <!-- Filter Tabs (JS) -->
  <div class="filter-row" id="filter-row" role="tablist">
    <button class="tab-btn active" id="filter-all"      onclick="filterOrders('All')">All (<?php echo count($all_orders); ?>)</button>
    <button class="tab-btn"        id="filter-received" onclick="filterOrders('Received')">📬 Received (<?php echo $count_received; ?>)</button>
    <button class="tab-btn"        id="filter-preparing"onclick="filterOrders('Preparing')">👨‍🍳 Preparing (<?php echo $count_preparing; ?>)</button>
    <button class="tab-btn"        id="filter-ready"    onclick="filterOrders('Ready')">✅ Ready (<?php echo $count_ready; ?>)</button>
  </div>

  <p class="admin-header-note">Updating status here is instant &ndash; customers see changes on their Orders page (auto-refreshes every 15 s).</p>

  <!-- Orders Table -->
  <div class="card">
    <div class="table-section">
      <div class="admin-table-wrap">
        <table id="admin-table">
          <thead>
            <tr>
              <th>Order No.</th>
              <th>Customer</th>
              <th>Phone / Roll</th>
              <th>Items</th>
              <th>Total</th>
              <th>Placed At</th>
              <th>Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_orders as $order): ?>
            <tr class="order-table-row" data-status="<?php echo htmlspecialchars($order['status']); ?>" id="row-<?php echo htmlspecialchars($order['order_no']); ?>">
              <td><strong><?php echo htmlspecialchars($order['order_no']); ?></strong></td>
              <td><?php echo htmlspecialchars($order['name']); ?></td>
              <td><?php echo htmlspecialchars($order['phone']); ?></td>
              <td>
                <?php
                  $summaries = [];
                  foreach ($order['items'] as $item) {
                      $summaries[] = htmlspecialchars($item['name']) . ' ×' . $item['qty'];
                  }
                  echo implode(', ', $summaries);
                ?>
              </td>
              <td><strong style="color:var(--accent-light);"><?php echo format_price($order['total']); ?></strong></td>
              <td style="font-size:0.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($order['placed_at']); ?></td>
              <td>
                <span class="status-badge <?php echo status_class($order['status']); ?>" id="status-badge-<?php echo htmlspecialchars($order['order_no']); ?>">
                  <?php echo htmlspecialchars($order['status']); ?>
                </span>
              </td>
              <td>
                <form method="post" action="admin.php" id="admin-form-<?php echo htmlspecialchars($order['order_no']); ?>">
                  <input type="hidden" name="action"   value="update_status">
                  <input type="hidden" name="order_no" value="<?php echo htmlspecialchars($order['order_no']); ?>">
                  <div style="display:flex;gap:6px;align-items:center;">
                    <select name="status" class="select-status" id="sel-<?php echo htmlspecialchars($order['order_no']); ?>"
                            onchange="this.form.submit()">
                      <option value="Received"  <?php echo $order['status']==='Received'  ? 'selected':'' ?>>📬 Received</option>
                      <option value="Preparing" <?php echo $order['status']==='Preparing' ? 'selected':'' ?>>👨‍🍳 Preparing</option>
                      <option value="Ready"     <?php echo $order['status']==='Ready'     ? 'selected':'' ?>>✅ Ready</option>
                    </select>
                  </div>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php endif; ?>

</main>

<footer class="site-footer">
  <p>NCSM301 &ndash; Web Development Laboratory &nbsp;|&nbsp; IIT (ISM) Dhanbad</p>
</footer>

<script>
/* Client-side filter by status */
function filterOrders(status) {
  /* Update tab styling */
  document.querySelectorAll('.filter-row .tab-btn').forEach(btn => btn.classList.remove('active'));
  var id = 'filter-' + status.toLowerCase();
  var btn = document.getElementById(id);
  if (btn) btn.classList.add('active');

  /* Show/hide rows */
  document.querySelectorAll('.order-table-row').forEach(function(row) {
    row.style.display = (status === 'All' || row.dataset.status === status) ? '' : 'none';
  });
}
</script>

</body>
</html>
