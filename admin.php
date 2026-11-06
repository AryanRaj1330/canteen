<?php
require_once 'db.php';

// 1. Handle Logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['staff_logged_in']);
    header('Location: admin.php');
    exit;
}

// 2. Handle Login submission
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_pin'])) {
    if (trim($_POST['staff_pin']) === STAFF_PIN) {
        $_SESSION['staff_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = 'Incorrect PIN. Please try again.';
    }
}

$isLoggedIn = !empty($_SESSION['staff_logged_in']);

// 3. If not logged in, show PIN login screen
if (!$isLoggedIn) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Campus Canteen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav class="navbar">
        <div class="brand">Campus Canteen &mdash; Staff Panel</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="admin.php" class="active">Staff Dashboard</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="login-card">
            <div class="lock-icon">🔒</div>
            <h2 style="margin-bottom: 6px; color: #2c3e50;">Staff Authentication</h2>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">
                Enter the staff security PIN to access orders management.
            </p>

            <?php if (!empty($loginError)): ?>
                <div class="alert alert-danger" style="margin-bottom: 16px; text-align: left;">
                    <span><?= htmlspecialchars($loginError) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="staff_pin">Security PIN</label>
                    <input type="password" id="staff_pin" name="staff_pin" class="form-control pin-input" maxlength="10" placeholder="••••" required autofocus autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 1rem;">
                    Unlock Dashboard
                </button>
            </form>
            <p style="margin-top: 16px; font-size: 0.8rem; color: #888;">
                Default PIN: <code>1234</code> (configurable in <code>db.php</code>)
            </p>
        </div>
    </div>

</body>
</html>
<?php
    exit;
}

// 4. Fetch all orders for authenticated staff view
$orders = fetchAllOrdersWithItems($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Canteen Queue</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav class="navbar">
        <div class="brand">Campus Canteen &mdash; Staff Panel</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="admin.php" class="active">Staff Dashboard</a></li>
            <li><a href="admin.php?action=logout" style="color: #feb2b2;">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 class="page-title" style="margin-bottom: 4px;">Orders Management</h1>
                <p style="color: #666; font-size: 0.9rem;">
                    Update order preparation and ready states in real time.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="admin.php" class="btn btn-primary btn-sm">↻ Reload Table</a>
                <a href="admin.php?action=logout" class="btn btn-danger-outline btn-sm">Log Out</a>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Items Ordered</th>
                        <th>Total</th>
                        <th>Time</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 24px;">No customer orders placed yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_no']) ?></strong></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                                <td><?= htmlspecialchars($order['items_summary'] ?: 'No items') ?></td>
                                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['order_time']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <select class="form-control status-select" data-id="<?= $order['id'] ?>" data-current="<?= $order['status'] ?>" style="width: auto; padding: 4px 8px;">
                                            <option value="Received" <?= $order['status'] === 'Received' ? 'selected' : '' ?>>Received</option>
                                            <option value="Preparing" <?= $order['status'] === 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                                            <option value="Ready" <?= $order['status'] === 'Ready' ? 'selected' : '' ?>>Ready</option>
                                        </select>
                                        <span class="status-feedback" id="feedback-<?= $order['id'] ?>" style="display: none;">Saved ✓</span>
                                    </div>
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

        // Handle Status Change dropdown
        $('.status-select').on('change', function() {
            var $dropdown = $(this);
            var orderId = $dropdown.data('id');
            var newStatus = $dropdown.val();
            var $feedback = $('#feedback-' + orderId);

            $dropdown.prop('disabled', true);

            $.post('api.php', {
                action: 'update_status',
                order_id: orderId,
                status: newStatus
            }, function(res) {
                $dropdown.prop('disabled', false);

                if (res.success) {
                    $dropdown.data('current', newStatus);
                    $feedback.text('Saved ✓').css('color', '#15803d').fadeIn(200).delay(1500).fadeOut(400);
                } else {
                    // Revert dropdown in UI
                    $dropdown.val($dropdown.data('current'));
                    $feedback.text('Reverted ✕').css('color', '#dc2626').fadeIn(200).delay(2000).fadeOut(400);
                    alert(res.message || 'Status update failed');
                }
            }, 'json').fail(function() {
                $dropdown.prop('disabled', false);
                $feedback.text('Error ✕').css('color', '#dc2626').fadeIn(200);
            });
        });

    });
    </script>
</body>
</html>