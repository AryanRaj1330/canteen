# Campus Canteen Pre-Order & Queue Management System

A minimal, clean, and fully functional Canteen Pre-Order and Live Queue System built with **Raw PHP 8**, **jQuery (AJAX)**, **HTML5/CSS3**, and **MySQL/MariaDB**.

Designed specifically for **college lab exams, practical evaluations, and viva voce**, with minimal, highly readable, and easily explainable code.

---

## 📁 File Structure

```text
canteen_system/
├── canteen_db.sql     # Database schema and sample seed menu items
├── db.php             # PDO database connection & session initialization
├── index.php          # Customer menu and dynamic AJAX slide-in cart
├── api.php            # AJAX backend API for cart actions, checkout, & status updates
├── orders.php         # Customer live queue screen (auto-updates via setInterval)
├── admin.php          # Staff kitchen dashboard to update order status
├── styles.css         # Minimal, clean responsive styling (No bloated frameworks)
└── README.md          # Setup instructions & Viva Q&A Guide
```

---

## 🚀 Setup Instructions (XAMPP / WAMP / LAMP)

### Step 1: Copy to Web Root
Copy the `canteen_system` folder into your web server document root:
- **XAMPP Windows:** `C:\xampp\htdocs\canteen_system`
- **Linux LAMP:** `/var/www/html/canteen_system`
- **Mac MAMP:** `/Applications/MAMP/htdocs/canteen_system`

### Step 2: Import Database
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open your browser and navigate to `http://localhost/phpmyadmin`.
3. Click on **Import** tab.
4. Choose the file `canteen_db.sql` and click **Go/Import**.
   *(It automatically creates the database `canteen_db` and seeds 6 items across Food & Beverages).*

### Step 3: Access the Application
Open your browser and visit:
- **Customer Pre-Order & Cart:** [http://localhost/canteen_system/index.php](http://localhost/canteen_system/index.php)
- **Customer Live Queue Screen:** [http://localhost/canteen_system/orders.php](http://localhost/canteen_system/orders.php)
- **Staff Kitchen Dashboard:** [http://localhost/canteen_system/admin.php](http://localhost/canteen_system/admin.php)

---

## 🛠️ System Architecture & Workflow

```
[ Customer Browser (index.php) ]
       │
       │ (1) Add/Update/Remove/Checkout (jQuery AJAX)
       ▼
 [ api.php ] ──────────────► [ $_SESSION['cart'] ] (Holds temporary cart in memory)
       │
       │ (2) On Checkout: PDO Transaction
       ▼
 [ MySQL Database: canteen_db ]
   ├── items (Menu list)
   ├── orders (Order Header: ORD-XXXX, Name, Phone, Total, Status)
   └── order_items (Ordered line items & quantities)
       ▲
       │ (3) Status Update (jQuery AJAX)
 [ Staff Dashboard (admin.php) ]
       │
       │ (4) Live Polling (setInterval every 3s via AJAX)
 [ Customer Queue (orders.php) ]
```

---

## 🎓 Viva Voce Cheat Sheet (Examiner Questions & Answers)

### Q1: Why use PDO instead of mysqli?
> **Answer:** PDO (PHP Data Objects) provides a unified, database-independent interface with prepared statements, preventing SQL injection. It also provides clean exception handling (`PDO::ERRMODE_EXCEPTION`) and transaction management.

### Q2: Why is the Cart stored in `$_SESSION` instead of the database?
> **Answer:** Cart items are temporary and user-specific. Storing them in PHP sessions avoids unnecessary database writes and reads for every quantity increment/decrement. The database is only touched when the order is finalized during checkout.

### Q3: How does AJAX prevent full page reload?
> **Answer:** jQuery's `$.post()` and `$.get()` execute asynchronous HTTP requests in the background. The server (`api.php`) responds with lightweight JSON data. jQuery callbacks parse the JSON and update only the relevant DOM elements (cart list, badges, tables) without refreshing the browser.

### Q4: Why use a Database Transaction on checkout?
> **Answer:** Checkout requires inserting data into two related tables: `orders` and `order_items`. Using `$pdo->beginTransaction()`, `$pdo->commit()`, and `$pdo->rollBack()` ensures atomicity (ACID properties). If inserting an item fails, the entire order is rolled back, preventing orphaned rows.

### Q5: How does the Live Queue page auto-refresh?
> **Answer:** `orders.php` sets a JavaScript timer using `setInterval(refreshOrders, 3000)`. Every 3 seconds, an AJAX GET request fetches recent order statuses from `api.php?action=get_orders` and re-renders the table rows dynamically.
