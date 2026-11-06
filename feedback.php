<?php
require_once 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cname = trim($_POST['customer_name'] ?? '');
    $feedbackMsg = trim($_POST['message'] ?? '');
    if (!empty($cname) && !empty($feedbackMsg)) {
        $stmt = $pdo->prepare("INSERT INTO feedback (customer_name, message) VALUES (?, ?)");
        $stmt->execute([$cname, $feedbackMsg]);
        $msg = "Thank you! Your feedback has been submitted to the canteen staff.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Contact & Feedback - Campus Canteen</title><link rel="stylesheet" href="styles.css"></head>
<body>
    <nav class="navbar">
        <div class="brand">Campus Canteen</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="feedback.php" class="active">Feedback</a></li>
            <li><a href="admin.php">Staff Dashboard</a></li>
        </ul>
    </nav>
    <div class="container" style="max-width: 600px; margin: 40px auto;">
        <h1 class="page-title">Canteen Feedback</h1>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <form method="POST" action="feedback.php" style="background:#fff; padding:24px; border:1px solid #e2e8f0; border-radius:8px;">
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Rahul Sharma">
            </div>
            <div class="form-group">
                <label>Your Feedback / Suggestions</label>
                <textarea name="message" class="form-control" rows="4" required placeholder="Share your dining experience or menu suggestions..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
