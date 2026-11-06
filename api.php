<?php
// Core API endpoint for AJAX operations
header('Content-Type: application/json');
require_once 'db.php';

// Ensure cart array exists in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Helper function to return formatted cart summary
function getCartSummary() {
    $cart = $_SESSION['cart'] ?? [];
    $items = [];
    $total = 0;
    $count = 0;

    foreach ($cart as $id => $item) {
        $subtotal = $item['price'] * $item['qty'];
        $total += $subtotal;
        $count += $item['qty'];
        $items[] = [
            'id'       => $item['id'],
            'name'     => $item['name'],
            'price'    => number_format($item['price'], 2, '.', ''),
            'qty'      => $item['qty'],
            'subtotal' => number_format($subtotal, 2, '.', '')
        ];
    }

    return [
        'items' => $items,
        'total' => number_format($total, 2, '.', ''),
        'count' => $count
    ];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // 1. ADD ITEM TO CART
    case 'add':
        $itemId = (int)($_POST['item_id'] ?? 0);
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
            exit;
        }

        if (isset($_SESSION['cart'][$itemId])) {
            $_SESSION['cart'][$itemId]['qty'] += 1;
        } else {
            // Verify item from database
            $stmt = $pdo->prepare("SELECT id, name, price FROM items WHERE id = ? AND is_available = 1");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();

            if ($item) {
                $_SESSION['cart'][$itemId] = [
                    'id'    => (int)$item['id'],
                    'name'  => $item['name'],
                    'price' => (float)$item['price'],
                    'qty'   => 1
                ];
            } else {
                echo json_encode(['success' => false, 'message' => 'Item is unavailable']);
                exit;
            }
        }

        echo json_encode(['success' => true, 'cart' => getCartSummary()]);
        break;

    // 2. UPDATE ITEM QUANTITY
    case 'update':
        $itemId = (int)($_POST['item_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 0);

        if ($itemId > 0) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$itemId]);
            } elseif (isset($_SESSION['cart'][$itemId])) {
                $_SESSION['cart'][$itemId]['qty'] = $qty;
            }
        }

        echo json_encode(['success' => true, 'cart' => getCartSummary()]);
        break;

    // 3. REMOVE ITEM FROM CART
    case 'remove':
        $itemId = (int)($_POST['item_id'] ?? 0);
        if (isset($_SESSION['cart'][$itemId])) {
            unset($_SESSION['cart'][$itemId]);
        }

        echo json_encode(['success' => true, 'cart' => getCartSummary()]);
        break;

    // 4. GET CART STATUS
    case 'get_cart':
        echo json_encode(['success' => true, 'cart' => getCartSummary()]);
        break;

    // 5. CHECKOUT (PLACE ORDER)
    case 'checkout':
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty. Please add items.']);
            exit;
        }

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Please provide both customer name and phone.']);
            exit;
        }

        // Calculate final total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        // Generate unique order number (e.g. ORD-7429)
        $orderNo = 'ORD-' . strtoupper(substr(uniqid(), -4));

        $pdo->beginTransaction();
        try {
            // Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (order_no, customer_name, phone, total_amount, status) VALUES (?, ?, ?, ?, 'Received')");
            $stmt->execute([$orderNo, $name, $phone, $total]);
            $orderId = $pdo->lastInsertId();

            // Insert each item into order_items table
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, price, quantity) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $item) {
                $itemStmt->execute([$orderId, $item['name'], $item['price'], $item['qty']]);
            }

            $pdo->commit();

            // Clear session cart
            $_SESSION['cart'] = [];

            echo json_encode([
                'success'  => true,
                'order_no' => $orderNo,
                'message'  => "Order placed successfully! Your Order No is #$orderNo"
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
        }
        break;

    // 6. UPDATE ORDER STATUS (Staff Dashboard - Protected by Staff PIN session)
    case 'update_status':
        if (empty($_SESSION['staff_logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Staff login required.']);
            exit;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['Received', 'Preparing', 'Ready'];

        if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID or status']);
        }
        break;

    // TOGGLE FAVORITE ITEM IN SESSION
    case 'toggle_favorite':
        if (!isset($_SESSION['favorites'])) $_SESSION['favorites'] = [];
        $id = (int)($_POST['item_id'] ?? 0);
        if (in_array($id, $_SESSION['favorites'])) {
            $_SESSION['favorites'] = array_values(array_diff($_SESSION['favorites'], [$id]));
            $isFav = false;
        } else {
            $_SESSION['favorites'][] = $id;
            $isFav = true;
        }
        echo json_encode(['success' => true, 'is_favorite' => $isFav, 'favorites' => $_SESSION['favorites']]);
        break;

    // 7. GET ALL ORDERS (For Live Queue Refresh)
    case 'get_orders':
        $orders = fetchAllOrdersWithItems($pdo);
        echo json_encode(['success' => true, 'orders' => $orders]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
