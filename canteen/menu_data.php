<?php
/* ============================================================
   menu_data.php  –  Central item catalogue & session helpers
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   ============================================================ */

/* ---------- Menu Catalogue ---------- */
function get_menu_items(): array {
    return [
        /* Breakfast */
        ['id'=>'B01','name'=>'Masala Dosa',        'desc'=>'Crispy dosa with spiced potato filling, served with chutney & sambar.','price'=>45, 'category'=>'Breakfast','emoji'=>'🥞','available'=>true],
        ['id'=>'B02','name'=>'Idli Sambar',         'desc'=>'Soft steamed idlis with fresh sambar and coconut chutney.','price'=>35, 'category'=>'Breakfast','emoji'=>'🍚','available'=>true],
        ['id'=>'B03','name'=>'Poha',                'desc'=>'Light flattened rice with mustard, onion, peas and coriander.','price'=>25, 'category'=>'Breakfast','emoji'=>'🍛','available'=>false],
        ['id'=>'B04','name'=>'Bread Omelette',      'desc'=>'Fluffy two-egg omelette served with buttered toast slices.','price'=>30, 'category'=>'Breakfast','emoji'=>'🍳','available'=>true],

        /* Lunch */
        ['id'=>'L01','name'=>'Veg Thali',           'desc'=>'Rice, roti, dal, sabji, raita, salad and pickle.','price'=>70, 'category'=>'Lunch','emoji'=>'🍽️','available'=>true],
        ['id'=>'L02','name'=>'Chicken Biryani',     'desc'=>'Aromatic basmati rice slow-cooked with tender chicken and spices.','price'=>90, 'category'=>'Lunch','emoji'=>'🍗','available'=>true],
        ['id'=>'L03','name'=>'Paneer Butter Masala','desc'=>'Rich tomato-cream gravy with cottage cheese cubes. Served with naan.','price'=>80, 'category'=>'Lunch','emoji'=>'🧀','available'=>true],
        ['id'=>'L04','name'=>'Egg Fried Rice',      'desc'=>'Wok-tossed rice with eggs, veggies and soy sauce.','price'=>55, 'category'=>'Lunch','emoji'=>'🍳','available'=>false],

        /* Snacks */
        ['id'=>'S01','name'=>'Samosa (2 pcs)',       'desc'=>'Golden fried pastry filled with spiced potatoes and peas.','price'=>20, 'category'=>'Snacks','emoji'=>'🔺','available'=>true],
        ['id'=>'S02','name'=>'Vada Pav',             'desc'=>'Mumbai-style spiced potato fritter in a soft bun.','price'=>18, 'category'=>'Snacks','emoji'=>'🍔','available'=>true],
        ['id'=>'S03','name'=>'French Fries',         'desc'=>'Crispy golden fries with ketchup and mayo dips.','price'=>35, 'category'=>'Snacks','emoji'=>'🍟','available'=>true],
        ['id'=>'S04','name'=>'Spring Rolls (3 pcs)', 'desc'=>'Crunchy veg spring rolls with sweet chilli sauce.','price'=>40, 'category'=>'Snacks','emoji'=>'🥢','available'=>false],

        /* Beverages */
        ['id'=>'V01','name'=>'Masala Chai',          'desc'=>'Freshly brewed spiced tea with milk.','price'=>12, 'category'=>'Beverages','emoji'=>'☕','available'=>true],
        ['id'=>'V02','name'=>'Cold Coffee',          'desc'=>'Blended cold coffee with ice cream and chocolate drizzle.','price'=>45, 'category'=>'Beverages','emoji'=>'🧋','available'=>true],
        ['id'=>'V03','name'=>'Fresh Lime Soda',      'desc'=>'Refreshing lime soda – sweet, salty or mixed.','price'=>20, 'category'=>'Beverages','emoji'=>'🍋','available'=>true],
        ['id'=>'V04','name'=>'Mango Lassi',          'desc'=>'Thick blended yoghurt drink with ripe Alphonso mango.','price'=>35, 'category'=>'Beverages','emoji'=>'🥭','available'=>true],

        /* Desserts */
        ['id'=>'D01','name'=>'Gulab Jamun (2 pcs)', 'desc'=>'Soft milk-solid dumplings soaked in rose-flavoured sugar syrup.','price'=>30, 'category'=>'Desserts','emoji'=>'🍯','available'=>true],
        ['id'=>'D02','name'=>'Ice Cream Scoop',     'desc'=>'Choice of vanilla, chocolate or strawberry with a wafer.','price'=>25, 'category'=>'Desserts','emoji'=>'🍨','available'=>true],
        ['id'=>'D03','name'=>'Gajar Halwa',         'desc'=>'Classic carrot pudding slow-cooked with ghee, milk and dry fruits.','price'=>40, 'category'=>'Desserts','emoji'=>'🥕','available'=>false],
    ];
}

/* Find a single item by id */
function find_item(string $id): ?array {
    foreach (get_menu_items() as $item) {
        if ($item['id'] === $id) return $item;
    }
    return null;
}

/* ---------- Session helpers ---------- */

function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['cart']))   $_SESSION['cart']   = [];
    if (!isset($_SESSION['orders'])) $_SESSION['orders'] = [];
}

/* Cart */
function cart_count(): int {
    ensure_session();
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

function cart_add(string $id, int $qty = 1): void {
    ensure_session();
    $item = find_item($id);
    if (!$item || !$item['available']) return;
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$id] = ['id'=>$id,'qty'=>$qty];
    }
}

function cart_update(string $id, int $qty): void {
    ensure_session();
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id]['qty'] = $qty;
    }
}

function cart_remove(string $id): void {
    ensure_session();
    unset($_SESSION['cart'][$id]);
}

function cart_clear(): void {
    ensure_session();
    $_SESSION['cart'] = [];
}

function cart_total(): float {
    ensure_session();
    $total = 0.0;
    foreach ($_SESSION['cart'] as $id => $row) {
        $item = find_item($id);
        if ($item) $total += $item['price'] * $row['qty'];
    }
    return $total;
}

function cart_rows(): array {
    ensure_session();
    $rows = [];
    foreach ($_SESSION['cart'] as $id => $row) {
        $item = find_item($id);
        if ($item) {
            $rows[] = array_merge($item, ['qty' => $row['qty']]);
        }
    }
    return $rows;
}

/* Orders */
function generate_order_number(): string {
    return 'ORD-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function place_order(string $name, string $phone, string $note): array {
    ensure_session();
    $rows   = cart_rows();
    $total  = cart_total();
    $order  = [
        'order_no'  => generate_order_number(),
        'name'      => htmlspecialchars(trim($name)),
        'phone'     => htmlspecialchars(trim($phone)),
        'note'      => htmlspecialchars(trim($note)),
        'items'     => $rows,
        'total'     => $total,
        'status'    => 'Received',
        'placed_at' => date('Y-m-d H:i:s'),
    ];
    $_SESSION['orders'][] = $order;
    cart_clear();
    return $order;
}

function get_orders(): array {
    ensure_session();
    return array_reverse($_SESSION['orders']); // newest first
}

function update_order_status(string $order_no, string $status): bool {
    ensure_session();
    $allowed = ['Received','Preparing','Ready'];
    if (!in_array($status, $allowed)) return false;
    foreach ($_SESSION['orders'] as &$o) {
        if ($o['order_no'] === $order_no) { $o['status'] = $status; return true; }
    }
    return false;
}

/* ---------- HTML helpers ---------- */

function status_class(string $s): string {
    return match($s) {
        'Received'  => 'status-received',
        'Preparing' => 'status-preparing',
        'Ready'     => 'status-ready',
        default     => 'status-received',
    };
}

function progress_steps(string $status): array {
    $steps = ['Received','Preparing','Ready'];
    $idx   = array_search($status, $steps);
    $out   = [];
    foreach ($steps as $i => $s) {
        $cls = 'pending';
        if ($i < $idx)  $cls = 'done';
        if ($i === $idx) $cls = 'active';
        $out[] = ['label'=>$s,'class'=>$cls];
    }
    return $out;
}

function format_price(float $p): string {
    return '₹' . number_format($p, 2);
}
