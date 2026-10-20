# Canteen Pre-Order & Queue System
### NCSM301 – Web Development Laboratory | IIT (ISM) Dhanbad

---

## Project Overview
A PHP session-based canteen ordering system with:
- **Menu page** with 20 items across 5 categories, availability badges, and category filter tabs
- **Cart** with quantity controls (add / decrement / remove) and live order total
- **Checkout form** (name + phone/roll number) with server-side validation
- **Order confirmation** with order number, animated progress steps and item summary
- **Orders page** – shows all session orders with Received → Preparing → Ready status + auto-refresh
- **Admin / Staff dashboard** – stats tiles, filterable table, instant status update via dropdown

---

## File Structure
```
canteen/
├── index.php       ← Menu page (category tabs + add to cart)
├── cart.php        ← View cart, update qty, checkout form
├── confirm.php     ← Order confirmation + progress steps
├── orders.php      ← Track all session orders (auto-refresh 15 s)
├── admin.php       ← Staff queue dashboard (update statuses)
├── menu_data.php   ← Central data layer: menu catalogue + session helpers
└── styles.css      ← Premium dark-mode glassmorphism stylesheet
```

---

## How to Run (Standalone XAMPP – Lab 9 setup)

1. **Start XAMPP** – open `xampp-control.exe` and click **Start** next to Apache  
   *(Port 8080 as configured in Lab 9)*

2. **Copy the `canteen` folder** into your XAMPP `htdocs` directory:
   ```
   xampp\htdocs\canteen\
   ```

3. Open your browser and navigate to:
   ```
   http://localhost:8080/canteen/
   ```

---

## Features Demonstrated

| Feature                  | PHP Concept Used                              |
|--------------------------|-----------------------------------------------|
| Menu catalogue           | PHP arrays, `foreach`, `htmlspecialchars`     |
| Category filter          | `$_GET`, URL query strings                    |
| Add to cart              | `$_POST`, `session_start()`, `$_SESSION`      |
| Cart qty update / remove | POST forms with hidden inputs + `$_SESSION`   |
| Order total calculation  | Arithmetic, array functions                   |
| Checkout validation      | Server-side trim + empty check, redirect PRG  |
| Order number generation  | `uniqid()`, `md5()`, `strtoupper()`           |
| Order status updates     | `$_SESSION` mutation, `header('Location:...')` |
| Admin table filter       | JavaScript + `data-status` HTML attribute      |
| XSS prevention           | `htmlspecialchars()` on all output            |

---

## Cart & Session Flow

```
index.php  →  POST add item  →  $_SESSION['cart']
cart.php   →  POST update qty / remove
cart.php   →  POST checkout  →  place_order()  →  $_SESSION['orders']
confirm.php ← $_SESSION['last_order']   (cleared after one view – PRG pattern)
orders.php  ← $_SESSION['orders']       (auto-refreshes every 15 s)
admin.php   →  POST update_status  →  mutates $_SESSION['orders']
```

---

## Status Values
| Status    | Badge Colour | Meaning                      |
|-----------|-------------|------------------------------|
| Received  | 🔵 Blue     | Order registered in queue    |
| Preparing | 🟡 Yellow   | Kitchen is cooking           |
| Ready     | 🟢 Green    | Ready for collection (pulses)|
