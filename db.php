<?php
// Start session for cart management across requests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Staff Dashboard Security PIN
if (!defined('STAFF_PIN')) {
    define('STAFF_PIN', '1234');
}

// Database configuration (Default XAMPP / MariaDB credentials)
$host = 'localhost';
$dbname = 'canteen_db';
$username = 'root';
$password = '';

try {
    // 1. Primary: Try connecting to MySQL/MariaDB (XAMPP default)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // 2. Fallback: If MySQL is not running yet, use zero-config SQLite so localhost works immediately
    $sqliteFile = __DIR__ . '/canteen_db.sqlite';
    $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Create tables if they do not exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS status_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            old_status TEXT NOT NULL,
            new_status TEXT NOT NULL,
            changed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL,
            is_available INTEGER DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_no TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL,
            phone TEXT NOT NULL,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'Received',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            item_name TEXT NOT NULL,
            price REAL NOT NULL,
            quantity INTEGER NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
    ");

    // Seed menu items if empty
    $count = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO items (name, category, price, is_available) VALUES
            ('Veg Burger', 'Food', 50.00, 1),
            ('Chicken Sandwich', 'Food', 80.00, 1),
            ('Hot Samosa (2 pcs)', 'Food', 25.00, 1),
            ('Masala Dosa', 'Food', 60.00, 1),
            ('Cold Coffee', 'Beverages', 40.00, 1),
            ('Lemon Iced Tea', 'Beverages', 30.00, 1);
        ");
    }
}

// Helper function to fetch all orders with their ordered items (works universally)
function fetchAllOrdersWithItems($pdo) {
    $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
    $items = $pdo->query("SELECT * FROM order_items")->fetchAll();

    $itemsMap = [];
    foreach ($items as $item) {
        $itemsMap[$item['order_id']][] = $item['item_name'] . ' (x' . $item['quantity'] . ')';
    }

    foreach ($orders as &$order) {
        $order['items_summary'] = isset($itemsMap[$order['id']]) ? implode(', ', $itemsMap[$order['id']]) : 'No items';
        $order['order_time'] = !empty($order['created_at']) ? date('h:i A', strtotime($order['created_at'])) : date('h:i A');
    }
    return $orders;
}
?>
