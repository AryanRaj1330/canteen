/* ============================================================
   app.js  –  Canteen Pre-Order & Queue System
   NCSM301 | Web Development Laboratory | IIT (ISM) Dhanbad
   Student : Akshay Reddy  |  Roll No. : 24JE0404
   Pure HTML + JS version (localStorage replaces PHP session)
   ============================================================ */

/* ════════════════════════════════════════════════════════════
   1.  MENU DATA  (mirrors menu_data.php)
════════════════════════════════════════════════════════════ */
const MENU = [
  /* ── Breakfast ── */
  { id:'B01', name:'Masala Dosa',   desc:'Crispy dosa with spiced potato filling, served with chutney & sambar.', price:45, cat:'Breakfast', emoji:'🥞', avail:true,
    img:'images/masala_dosa.jpg' },
  { id:'B02', name:'Idli Sambar',   desc:'Soft steamed idlis with fresh sambar and coconut chutney.',             price:35, cat:'Breakfast', emoji:'🍚', avail:true,
    img:'images/idli_sambar.jpg' },
  { id:'B03', name:'Poha',          desc:'Light flattened rice with mustard, onion, peas and coriander.',         price:25, cat:'Breakfast', emoji:'🍛', avail:false,
    img:'https://images.unsplash.com/photo-1789991184412-c29c9dfa2b39?w=600&h=400&fit=crop&auto=format' },
  { id:'B04', name:'Bread Omelette',desc:'Fluffy two-egg omelette served with buttered toast slices.',            price:30, cat:'Breakfast', emoji:'🍳', avail:true,
    img:'images/bread_omelette.jpg' },

  /* ── Lunch ── */
  { id:'L01', name:'Veg Thali',            desc:'Rice, roti, dal, sabji, raita, salad and pickle.',                      price:70, cat:'Lunch', emoji:'🍽️', avail:true,
    img:'images/veg_thali.jpg' },
  { id:'L02', name:'Chicken Biryani',      desc:'Aromatic basmati rice slow-cooked with tender chicken and spices.',     price:90, cat:'Lunch', emoji:'🍗', avail:true,
    img:'images/chicken_biryani.jpg' },
  { id:'L03', name:'Paneer Butter Masala', desc:'Rich tomato-cream gravy with cottage cheese cubes. Served with naan.',  price:80, cat:'Lunch', emoji:'🧀', avail:true,
    img:'images/paneer_butter_masala.jpg' },
  { id:'L04', name:'Egg Fried Rice',       desc:'Wok-tossed rice with eggs, veggies and soy sauce.',                    price:55, cat:'Lunch', emoji:'🍳', avail:false,
    img:'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&h=400&fit=crop&auto=format' },
  { id:'L05', name:'Chicken Butter Masala',desc:'Tender chicken in a silky tomato-butter-cream sauce. Served with naan.', price:95, cat:'Lunch', emoji:'🍛', avail:true,
    img:'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=600&h=400&fit=crop&auto=format' },

  /* ── Breads ── */
  { id:'BR1', name:'Tandoori Roti', desc:'Whole-wheat flatbread baked in a clay tandoor oven – smoky and soft.',  price:15, cat:'Breads', emoji:'🫓', avail:true,
    img:'images/Tandoor_roti.jpg' },
  { id:'BR2', name:'Tawa Roti',     desc:'Thin whole-wheat flatbread cooked on a cast-iron tawa with butter.',    price:7,  cat:'Breads', emoji:'🫓', avail:true,
    img:'images/Tawa_roti.webp' },

  /* ── Snacks ── */
  { id:'S01', name:'Samosa (2 pcs)',       desc:'Golden fried pastry filled with spiced potatoes and peas.',          price:20, cat:'Snacks', emoji:'🔺', avail:true,
    img:'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=600&h=400&fit=crop&auto=format' },
  { id:'S02', name:'Vada Pav',             desc:'Mumbai-style spiced potato fritter served in 1 soft pav bun.',       price:18, cat:'Snacks', emoji:'🍔', avail:true,
    img:'https://images.unsplash.com/photo-1606491956689-2ea866880c84?w=600&h=400&fit=crop&auto=format' },
  { id:'S03', name:'French Fries',         desc:'Crispy golden fries with ketchup and mayo dips.',                    price:35, cat:'Snacks', emoji:'🍟', avail:true,
    img:'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?w=600&h=400&fit=crop&auto=format' },
  { id:'S04', name:'Spring Rolls (3 pcs)', desc:'Crunchy veg spring rolls with sweet chilli sauce.',                  price:40, cat:'Snacks', emoji:'🥢', avail:false,
    img:'images/spring_rolls.jpg' },

  /* ── Beverages ── */
  { id:'V01', name:'Masala Chai',     desc:'Freshly brewed spiced tea with milk.',                           price:12, cat:'Beverages', emoji:'☕', avail:true,
    img:'images/Masala_chai.jpg' },
  { id:'V02', name:'Cold Coffee',     desc:'Blended cold coffee with ice cream and chocolate drizzle.',      price:45, cat:'Beverages', emoji:'🧋', avail:true,
    img:'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&h=400&fit=crop&auto=format' },
  { id:'V03', name:'Fresh Lime Soda', desc:'Refreshing lime soda – sweet, salty or mixed.',                  price:20, cat:'Beverages', emoji:'🍋', avail:true,
    img:'images/Lime_soda.jpg' },
  { id:'V04', name:'Mango Lassi',     desc:'Thick blended yoghurt drink with ripe Alphonso mango.',          price:35, cat:'Beverages', emoji:'🥭', avail:true,
    img:'images/Mango_lassi.jpg' },

  /* ── Desserts ── */
  { id:'D01', name:'Gulab Jamun (2 pcs)', desc:'Soft milk-solid dumplings soaked in rose-flavoured sugar syrup.',  price:30, cat:'Desserts', emoji:'🍯', avail:true,
    img:'images/Gulab_jamun.jpg' },
  { id:'D02', name:'Ice Cream Scoop',     desc:'Choose your favourite flavour — served with a crispy wafer.',      price:25, cat:'Desserts', emoji:'🍨', avail:true,
    img:'images/Vanilla.jpeg',
    flavours:[
      { name:'Vanilla',      price:25, img:'images/Vanilla.jpeg' },
      { name:'Chocolate',    price:35, img:'images/chocolate.jpg' },
      { name:'Butterscotch', price:35, img:'images/butterscotch.jpeg' },
      { name:'Strawberry',   price:25, img:'images/strawberry.png' },
    ]},
  { id:'D03', name:'Gajar Halwa', desc:'Classic carrot pudding slow-cooked with ghee, milk and dry fruits.',  price:40, cat:'Desserts', emoji:'🥕', avail:false,
    img:'images/Gajar Halwa.webp' },
];

const CATEGORIES = ['All', ...new Set(MENU.map(i => i.cat))];

/* Apply persisted availability overrides from localStorage */
function loadAvailability() { try { return JSON.parse(localStorage.getItem('canteen_avail') || '{}'); } catch { return {}; } }
function saveAvailability(a) { localStorage.setItem('canteen_avail', JSON.stringify(a)); }
function getMenu() {
  const overrides = loadAvailability();
  return MENU.map(item => ((item.id in overrides) ? { ...item, avail: overrides[item.id] } : { ...item }));
}
function findItem(id) {
  const overrides = loadAvailability();
  const item = MENU.find(i => i.id === id) || null;
  if (!item) return null;
  return (id in overrides) ? { ...item, avail: overrides[id] } : { ...item };
}
const fmtPrice = p => '₹' + p.toFixed(2);


/* ════════════════════════════════════════════════════════════
   2.  STORAGE  (localStorage  ≅  $_SESSION)
════════════════════════════════════════════════════════════ */
function loadCart()   { try { return JSON.parse(localStorage.getItem('canteen_cart')   || '{}');  } catch { return {}; } }
function loadOrders() { try { return JSON.parse(localStorage.getItem('canteen_orders') || '[]'); } catch { return []; } }
function saveCart(c)  { localStorage.setItem('canteen_cart',   JSON.stringify(c)); }
function saveOrders(o){ localStorage.setItem('canteen_orders', JSON.stringify(o)); }

/* Cart helpers */
function cartCount() {
  const c = loadCart();
  return Object.values(c).reduce((s, r) => s + r.qty, 0);
}
function cartAdd(id, qty = 1, flavour = null, price = null) {
  const item = findItem(id);
  if (!item || !item.avail) return;
  const key  = flavour ? `${id}__${flavour}` : id;
  const p    = price ?? item.price;
  const c    = loadCart();
  if (c[key]) { c[key].qty += qty; } else { c[key] = { qty, flavour, price: p }; }
  saveCart(c);
}
function cartUpdate(key, qty) {
  const c = loadCart();
  if (qty <= 0) delete c[key]; else { c[key] = { ...c[key], qty }; }
  saveCart(c);
}
function cartRemove(key) { const c = loadCart(); delete c[key]; saveCart(c); }
function cartClear()     { saveCart({}); }
function cartRows() {
  return Object.entries(loadCart()).map(([key, r]) => {
    const baseId = key.includes('__') ? key.split('__')[0] : key;
    const item   = findItem(baseId);
    if (!item) return null;
    // Use stored per-flavour price if available, else item base price
    const price = r.price ?? item.price;
    return { ...item, price, qty: r.qty, flavour: r.flavour || null, _key: key };
  }).filter(Boolean);
}
function cartTotal() {
  return cartRows().reduce((s, r) => s + r.price * r.qty, 0);
}

/* Order helpers */
function genOrderNo() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let out = 'ORD-';
  for (let i = 0; i < 6; i++) out += chars[Math.floor(Math.random() * chars.length)];
  return out;
}
function placeOrder(name, phone, note) {
  const rows  = cartRows();
  const total = cartTotal();
  const now   = new Date();
  const ts    = now.getFullYear() + '-'
              + String(now.getMonth()+1).padStart(2,'0') + '-'
              + String(now.getDate()).padStart(2,'0') + ' '
              + String(now.getHours()).padStart(2,'0') + ':'
              + String(now.getMinutes()).padStart(2,'0') + ':'
              + String(now.getSeconds()).padStart(2,'0');
  const order = {
    order_no: genOrderNo(),
    name: escHtml(name.trim()),
    phone: escHtml(phone.trim()),
    note: escHtml(note.trim()),
    items: rows,
    total,
    status: 'Received',
    placed_at: ts,
  };
  const orders = loadOrders();
  orders.push(order);
  saveOrders(orders);
  cartClear();
  return order;
}
function updateStatus(order_no, status) {
  const allowed = ['Received','Preparing','Ready'];
  if (!allowed.includes(status)) return;
  const orders = loadOrders();
  const o = orders.find(o => o.order_no === order_no);
  if (o) { o.status = status; saveOrders(orders); }
}

/* ════════════════════════════════════════════════════════════
   3.  UTILITIES
════════════════════════════════════════════════════════════ */
function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}
function statusClass(s) {
  return { Received:'s-received', Preparing:'s-preparing', Ready:'s-ready' }[s] || 's-received';
}
function statusIcon(s) {
  return { Received:'📬', Preparing:'👨‍🍳', Ready:'✅' }[s] || '📬';
}
function progressSteps(status) {
  const steps = ['Received','Preparing','Ready'];
  const idx   = steps.indexOf(status);
  return steps.map((s, i) => ({
    label: s,
    cls:   i < idx ? 'done' : (i === idx ? 'active' : 'pending'),
  }));
}
function flash(zoneId, msg, type = 'success') {
  const icon = { success:'✅', error:'❌', info:'ℹ️' }[type] || 'ℹ️';
  const z = document.getElementById(zoneId);
  if (!z) return;
  z.innerHTML = `<div class="flash flash-${type}">${icon} ${escHtml(msg)}</div>`;
  setTimeout(() => { z.innerHTML = ''; }, 3500);
}
function updateBadge() {
  const n = cartCount();
  const badge = document.getElementById('cart-badge');
  if (badge) { badge.textContent = n; badge.style.display = n > 0 ? 'inline-flex' : 'none'; }
}

/* ════════════════════════════════════════════════════════════
   ADMIN AUTH  (password: 1234 · auto-lock after 5 min inactivity)
════════════════════════════════════════════════════════════ */
const ADMIN_PASS    = '1234';
const LOCK_TIMEOUT  = 5 * 60 * 1000;   // 5 minutes in ms
let   _lockTimer    = null;
let   _countdownInt = null;
let   _lockAt       = null;

function isAdminAuth() { return sessionStorage.getItem('admin_auth') === '1'; }

/* ── Timer helpers ── */
function _updateCountdown() {
  if (!_lockAt) return;
  const rem  = Math.max(0, _lockAt - Date.now());
  const mins = Math.floor(rem / 60000);
  const secs = Math.floor((rem % 60000) / 1000);
  const disp = document.getElementById('admin-timer-display');
  if (disp) {
    disp.textContent = `🔒 Auto-lock in ${mins}:${String(secs).padStart(2,'0')}`;
    disp.style.color = rem < 60000 ? 'var(--red)' : 'var(--tm)';
    disp.style.fontWeight = rem < 60000 ? '700' : '400';
  }
}

function _resetLock() {
  if (!isAdminAuth()) return;
  clearTimeout(_lockTimer);
  _lockAt    = Date.now() + LOCK_TIMEOUT;
  _lockTimer = setTimeout(_autoLock, LOCK_TIMEOUT);
}

function _autoLock() {
  stopAdminTimer();
  sessionStorage.removeItem('admin_auth');
  const overlay = document.getElementById('admin-overlay');
  if (overlay) overlay.classList.remove('hidden');
  const pw  = document.getElementById('admin-pw');
  if (pw) { pw.value = ''; pw.focus(); }
  const err = document.getElementById('login-error');
  if (err) err.textContent = '';
  // Show message inside the login box
  const sub = document.querySelector('.login-sub');
  if (sub) sub.textContent = '⏱️ Session timed out due to inactivity.';
}

function startAdminTimer() {
  stopAdminTimer();
  _lockAt        = Date.now() + LOCK_TIMEOUT;
  _lockTimer     = setTimeout(_autoLock, LOCK_TIMEOUT);
  _countdownInt  = setInterval(_updateCountdown, 1000);
  ['mousemove','click','keydown','touchstart'].forEach(e =>
    document.addEventListener(e, _resetLock, { passive: true }));
}

function stopAdminTimer() {
  clearTimeout(_lockTimer);
  clearInterval(_countdownInt);
  _lockTimer = _countdownInt = _lockAt = null;
  ['mousemove','click','keydown','touchstart'].forEach(e =>
    document.removeEventListener(e, _resetLock));
  const disp = document.getElementById('admin-timer-display');
  if (disp) disp.textContent = '';
}

/* ── Login / Logout ── */
function adminLogin() {
  const pw  = (document.getElementById('admin-pw') || {}).value || '';
  const err = document.getElementById('login-error');
  const box = document.getElementById('login-box');
  if (pw === ADMIN_PASS) {
    sessionStorage.setItem('admin_auth', '1');
    document.getElementById('admin-overlay').classList.add('hidden');
    document.getElementById('admin-pw').value = '';
    if (err) err.textContent = '';
    // Restore subtitle in case it was changed by auto-lock
    const sub = document.querySelector('.login-sub');
    if (sub) sub.textContent = 'Enter your admin password to continue';
    startAdminTimer();
    renderAdmin();
  } else {
    if (err) err.textContent = '❌ Incorrect password. Try again.';
    if (box) { box.classList.remove('shake'); void box.offsetWidth; box.classList.add('shake'); }
    document.getElementById('admin-pw').focus();
  }
}

function adminLogout() {
  stopAdminTimer();
  sessionStorage.removeItem('admin_auth');
  const overlay = document.getElementById('admin-overlay');
  if (overlay) overlay.classList.remove('hidden');
  const pw  = document.getElementById('admin-pw');
  if (pw) { pw.value = ''; }
  const err = document.getElementById('login-error');
  if (err) err.textContent = '';
  const sub = document.querySelector('.login-sub');
  if (sub) sub.textContent = 'Enter your admin password to continue';
  showPage('menu');
  flash('menu-flash-zone', '🔒 Logged out of staff dashboard.', 'info');
}

function togglePwEye() {
  const inp = document.getElementById('admin-pw');
  const btn = document.getElementById('pw-eye');
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁️' : '🙈';
}

/* ════════════════════════════════════════════════════════════
   4.  PAGE ROUTER
════════════════════════════════════════════════════════════ */
let currentPage = 'menu';

function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-links button').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  const nb = document.getElementById('nav-' + name);
  if (nb) nb.classList.add('active');
  currentPage = name;
  updateBadge();
  if (name === 'menu')   renderMenu();
  if (name === 'cart')   renderCart();
  if (name === 'orders') renderOrders();
  if (name === 'admin') {
    // Show overlay if not authenticated, hide it if already authenticated
    const overlay = document.getElementById('admin-overlay');
    if (isAdminAuth()) {
      if (overlay) overlay.classList.add('hidden');
      renderAdmin();
    } else {
      if (overlay) overlay.classList.remove('hidden');
      setTimeout(() => { const pw = document.getElementById('admin-pw'); if (pw) pw.focus(); }, 100);
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ════════════════════════════════════════════════════════════
   5.  RENDER: MENU
════════════════════════════════════════════════════════════ */
let activeCat = 'All';

function renderMenu() {
  renderCatTabs();
  renderMenuGrid();
}

function renderCatTabs() {
  const tabs = document.getElementById('cat-tabs');
  if (!tabs) return;
  tabs.innerHTML = CATEGORIES.map(cat => `
    <button class="tab-btn ${cat === activeCat ? 'active' : ''}"
            id="tab-${escHtml(cat.toLowerCase())}"
            onclick="setCat('${escHtml(cat)}')">
      ${escHtml(cat)}
    </button>
  `).join('');
}

function setCat(cat) {
  activeCat = cat;
  renderMenuGrid();
  renderCatTabs();
}

function renderMenuGrid() {
  const grid = document.getElementById('menu-grid');
  if (!grid) return;
  const menuItems = getMenu();
  const filtered = activeCat === 'All' ? menuItems : menuItems.filter(i => i.cat === activeCat);

  grid.innerHTML = filtered.map(item => `
    <article class="menu-item ${item.avail ? '' : 'unavail'}" id="item-${item.id}">
      ${item.img
        ? `<img src="${item.img}" alt="${escHtml(item.name)}" class="item-img-photo"
               onerror="this.outerHTML='<div class=\\'item-img\\'>${item.emoji}</div>'">`
        : `<div class="item-img">${item.emoji}</div>`
      }
      <span class="avail-badge ${item.avail ? 'badge-green' : 'badge-red'}">
        ${item.avail ? 'Available' : 'Sold Out'}
      </span>
      <div class="item-body">
        <div class="item-name">${escHtml(item.name)}</div>
        <div class="item-desc">${escHtml(item.desc)}</div>
        ${item.flavours ? `
          <div class="flavour-row">
            <span class="flavour-lbl">🍦 Flavour:</span>
            <select id="flavour-${item.id}" class="flavour-sel"
              onchange="updateFlavourCard('${item.id}')">
              ${item.flavours.map(f =>
                `<option value="${f.name}" data-img="${f.img}" data-price="${f.price}">${f.name} – ₹${f.price}</option>`
              ).join('')}
            </select>
          </div>` : ''}
      </div>
      <div class="item-footer">
        <span class="item-price">${fmtPrice(item.price)}</span>
        ${item.avail
          ? `<button class="btn btn-primary btn-sm" id="add-${item.id}"
               onclick="addToCart('${item.id}')">+ Add</button>`
          : `<button class="btn btn-secondary btn-sm" disabled>Sold Out</button>`
        }
      </div>
    </article>
  `).join('');
}

function addToCart(id) {
  const item = findItem(id);
  if (!item) return;
  if (!item.avail) {
    flash('menu-flash-zone', `❌ ${item.name} is currently sold out!`, 'error');
    return;
  }
  let flavour = null;
  let price   = item.price;
  if (item.flavours) {
    const sel = document.getElementById(`flavour-${id}`);
    const fName = sel ? sel.value : item.flavours[0].name;
    const fObj  = item.flavours.find(f => f.name === fName);
    flavour = fName;
    price   = fObj ? fObj.price : item.price;
  }
  cartAdd(id, 1, flavour, price);
  updateBadge();
  const label = flavour ? `${item.name} – ${flavour}` : item.name;
  flash('menu-flash-zone', `${item.emoji} ${label} added to cart!`, 'success');
}

/* Called by onchange on the flavour <select> – swaps card image + price instantly */
function updateFlavourCard(id) {
  const item = findItem(id);
  if (!item || !item.flavours) return;
  const sel  = document.getElementById(`flavour-${id}`);
  if (!sel) return;
  const opt  = sel.options[sel.selectedIndex];
  const fImg = opt.dataset.img;
  const fPrice = parseFloat(opt.dataset.price);

  // Swap the card image
  const cardImg = document.querySelector(`#item-${id} .item-img-photo`);
  if (cardImg && fImg) cardImg.src = fImg;

  // Update the price shown in the footer
  const priceEl = document.querySelector(`#item-${id} .item-price`);
  if (priceEl) priceEl.textContent = fmtPrice(fPrice);
}

/* ════════════════════════════════════════════════════════════
   6.  RENDER: CART
════════════════════════════════════════════════════════════ */
function renderCart() {
  const rows = cartRows();
  const total = cartTotal();
  const subEl = document.getElementById('cart-hero-sub');
  if (subEl) {
    const n = cartCount();
    subEl.textContent = n > 0 ? `${n} item(s) ready to order` : 'Nothing here yet – browse the menu!';
  }

  const body = document.getElementById('cart-body');
  if (!body) return;

  if (rows.length === 0) {
    body.innerHTML = `
      <div class="empty">
        <div class="e-icon">🛒</div>
        <div class="e-title">Your cart is empty</div>
        <div class="e-sub">Browse our menu and add your favourite items.</div>
        <button class="btn btn-primary" id="btn-browse-menu" onclick="showPage('menu')">Browse Menu</button>
      </div>`;
    return;
  }

  const hasSoldOut = rows.some(r => !r.avail);

  const summaryRows = rows.map(r => `
    <div class="s-row">
      <span>${escHtml(r.name)}${r.flavour ? ` <em style="color:var(--acl);font-size:.8em">(${r.flavour})</em>` : ''} × ${r.qty}${!r.avail ? ' <strong style="color:var(--red)">(Sold Out)</strong>' : ''}</span>
      <span>${fmtPrice(r.price * r.qty)}</span>
    </div>`).join('');

  body.innerHTML = `
    <div class="two-col">
      <!-- Items -->
      <div>
        <div class="col-title">Order Items</div>
        <div class="cart-list" id="cart-list">
          ${rows.map(r => `
            <div class="cart-row ${!r.avail ? 'unavail' : ''}" id="crow-${r._key}">
              <span class="c-emoji">${r.emoji}</span>
              <div class="c-info">
                <div class="c-name">
                  ${escHtml(r.name)}${r.flavour ? `<span class="c-flavour"> – ${r.flavour}</span>` : ''}
                  ${!r.avail ? `<span class="avail-badge badge-red" style="position:static;display:inline-block;margin-left:8px;font-size:0.7rem;padding:2px 7px;">Sold Out</span>` : ''}
                </div>
                <div class="c-unit">${fmtPrice(r.price)} each ${!r.avail ? '<span style="color:var(--red);font-size:0.78rem;">(Currently sold out)</span>' : ''}</div>
              </div>
              <div class="qty-ctrl">
                <button class="qty-btn" id="dec-${r._key}"
                  onclick="changeQty('${r._key}', ${r.qty - 1})" title="Decrease">−</button>
                <span class="qty-val" id="qv-${r._key}">${r.qty}</span>
                <button class="qty-btn" id="inc-${r._key}"
                  onclick="changeQty('${r._key}', ${r.qty + 1})" title="Increase">+</button>
              </div>
              <span class="c-total" id="ct-${r._key}">${fmtPrice(r.price * r.qty)}</span>
              <button class="btn btn-danger btn-sm" id="remove-${r._key}"
                onclick="removeItem('${r._key}')" title="Remove">🗑</button>
            </div>`).join('')}
        </div>
      </div>

      <!-- Summary + Checkout -->
      <div class="summary-card">
        <div class="card summary-inner">
          <div class="s-title">Order Summary</div>
          <div class="s-rows">
            ${summaryRows}
            <div class="s-row"><span>Subtotal</span><span>${fmtPrice(total)}</span></div>
            <div class="s-row"><span>Platform Fee</span><span>₹0.00</span></div>
            <div class="s-row total">
              <span>Total</span><span id="grand-total">${fmtPrice(total)}</span>
            </div>
          </div>

          ${hasSoldOut ? `
            <div class="flash flash-error" style="margin-bottom:14px;font-size:0.82rem;line-height:1.4">
              ⚠️ Some items in your cart are currently sold out. Please remove them using the 🗑 button to place your order.
            </div>` : ''}

          <!-- Checkout Form -->
          <div class="form-group">
            <label for="co-name">Your Name</label>
            <input type="text" id="co-name" placeholder="e.g. Akshay Reddy" maxlength="80">
          </div>
          <div class="form-group">
            <label for="co-phone">Phone Number</label>
            <input type="tel" id="co-phone" placeholder="e.g. 9876543210" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
          </div>
          <div class="form-group">
            <label for="co-note">Special Instructions (optional)</label>
            <textarea id="co-note" rows="2" placeholder="e.g. Less spicy, extra chutney…" maxlength="200"></textarea>
          </div>
          <button class="btn btn-success btn-full" id="btn-place-order" onclick="checkout()" ${hasSoldOut ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''}>
            ✅ Place Order
          </button>
          <button class="btn btn-secondary btn-full" id="btn-continue" onclick="showPage('menu')"
            style="margin-top:8px">← Continue Shopping</button>
        </div>
      </div>
    </div>`;
}

function changeQty(id, qty) {
  cartUpdate(id, qty);
  updateBadge();
  renderCart(); // full re-render keeps summary in sync
}

function removeItem(id) {
  cartRemove(id);
  updateBadge();
  flash('cart-flash-zone', 'Item removed from cart.', 'info');
  renderCart();
}

function checkout() {
  const name  = (document.getElementById('co-name')  || {}).value || '';
  const phone = (document.getElementById('co-phone') || {}).value || '';
  const note  = (document.getElementById('co-note')  || {}).value || '';

  if (!name.trim()) { flash('cart-flash-zone', 'Please enter your name.', 'error'); return; }
  if (!phone.trim()) { flash('cart-flash-zone', 'Please enter your phone number.', 'error'); return; }
  if (!/^\d{10}$/.test(phone.trim())) { flash('cart-flash-zone', 'Phone number must be exactly 10 digits.', 'error'); return; }
  const rows = cartRows();
  if (rows.length === 0) { flash('cart-flash-zone', 'Your cart is empty.', 'error'); return; }
  const soldOutItems = rows.filter(r => !r.avail);
  if (soldOutItems.length > 0) {
    flash('cart-flash-zone', `❌ Cannot place order: "${soldOutItems.map(i => i.name).join(', ')}" is sold out. Please remove it from your cart.`, 'error');
    return;
  }

  const order = placeOrder(name, phone, note);
  renderConfirm(order);
  showPage('confirm');
}

/* ════════════════════════════════════════════════════════════
   7.  RENDER: CONFIRM
════════════════════════════════════════════════════════════ */
function renderConfirm(order) {
  const steps = progressSteps(order.status).map(s => `
    <div class="step ${s.cls}">
      <div class="step-dot">${s.cls==='done'?'✓':s.cls==='active'?'●':''}</div>
      <div class="step-lbl">${escHtml(s.label)}</div>
    </div>`).join('');

  const itemsHtml = order.items.map(i => `
    <div class="c-row">
      <span>${i.emoji} ${escHtml(i.name)}${i.flavour ? ` <span class="c-flavour">– ${i.flavour}</span>` : ''} × ${i.qty}</span>
      <span>${fmtPrice(i.price * i.qty)}</span>
    </div>`).join('');

  document.getElementById('confirm-content').innerHTML = `
    <span class="c-icon">🎉</span>
    <h1 style="font-size:1.3rem;font-weight:700;margin-bottom:4px">Order Placed Successfully!</h1>
    <div class="c-order-no" id="confirm-order-no">${escHtml(order.order_no)}</div>
    <p class="c-hint">Save your order number to track status on the Orders page.</p>

    <div class="steps">${steps}</div>

    <div class="c-details">
      <div class="c-row"><span>Customer</span><span>${escHtml(order.name)}</span></div>
      <div class="c-row"><span>Phone Number</span><span>${escHtml(order.phone)}</span></div>
      <div class="c-row">
        <span>Status</span>
        <span class="sbadge ${statusClass(order.status)}">${statusIcon(order.status)} ${escHtml(order.status)}</span>
      </div>
      <div class="c-row"><span>Placed At</span><span>${escHtml(order.placed_at)}</span></div>
      ${order.note ? `<div class="c-row"><span>Note</span><span>${escHtml(order.note)}</span></div>` : ''}
    </div>

    <div class="c-details">
      <div style="font-size:.85rem;font-weight:700;color:var(--tm);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Items Ordered</div>
      ${itemsHtml}
      <div class="c-row" style="font-weight:700;color:var(--tp);margin-top:4px">
        <span>Total Paid</span>
        <span style="color:var(--acl)">${fmtPrice(order.total)}</span>
      </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
      <button class="btn btn-primary"   id="btn-track-orders" onclick="showPage('orders')">📦 Track Orders</button>
      <button class="btn btn-secondary" id="btn-order-more"   onclick="showPage('menu')">🍽️ Order More</button>
    </div>`;
}

/* ════════════════════════════════════════════════════════════
   8.  RENDER: ORDERS
════════════════════════════════════════════════════════════ */
function renderOrders() {
  const body   = document.getElementById('orders-body');
  if (!body) return;
  const orders = [...loadOrders()].reverse();

  if (orders.length === 0) {
    body.innerHTML = `
      <div class="empty">
        <div class="e-icon">📦</div>
        <div class="e-title">No orders yet</div>
        <div class="e-sub">Head to the menu, add items and place your first order!</div>
        <button class="btn btn-primary" id="btn-go-menu" onclick="showPage('menu')">Browse Menu</button>
      </div>`;
    return;
  }

  body.innerHTML = `
    <div class="orders-grid" id="orders-grid">
      ${orders.map(order => {
        const steps = progressSteps(order.status).map(s => `
          <div class="step ${s.cls}">
            <div class="step-dot">${s.cls==='done'?'✓':s.cls==='active'?'●':''}</div>
            <div class="step-lbl">${escHtml(s.label)}</div>
          </div>`).join('');

        const itemsHtml = order.items.map(i => `
          <div class="o-item-row">
            <span>${i.emoji} ${escHtml(i.name)}${i.flavour ? ` <span class="c-flavour">– ${i.flavour}</span>` : ''} × ${i.qty}</span>
            <span>${fmtPrice(i.price * i.qty)}</span>
          </div>`).join('');

        return `
          <article class="card order-card" id="ocard-${escHtml(order.order_no)}">
            <div class="order-hdr">
              <div class="order-no">
                <small>Order Number</small>
                ${escHtml(order.order_no)}
              </div>
              <span class="sbadge ${statusClass(order.status)}" id="badge-${escHtml(order.order_no)}">
                ${statusIcon(order.status)} ${escHtml(order.status)}
              </span>
            </div>
            <div class="steps">${steps}</div>
            <div class="o-items">${itemsHtml}</div>
            <div class="order-ftr">
              <div>
                <div style="font-size:.85rem;color:var(--tm)">Total Amount</div>
                <div style="font-size:1.15rem;font-weight:800;color:var(--acl)">${fmtPrice(order.total)}</div>
              </div>
              <div style="text-align:right">
                <div style="font-size:.8rem;color:var(--tm)">${escHtml(order.name)}</div>
                <div style="font-size:.75rem;color:var(--acl)">${escHtml(order.phone)}</div>
                <div style="font-size:.75rem;color:var(--td)">${escHtml(order.placed_at)}</div>
                ${order.note ? `<div style="font-size:.75rem;color:var(--td);margin-top:4px">📝 ${escHtml(order.note)}</div>` : ''}
              </div>
            </div>
          </article>`;
      }).join('')}
    </div>
    <div style="text-align:center;margin-top:28px">
      <button class="btn btn-primary" id="btn-order-more-orders" onclick="showPage('menu')">+ Order More</button>
    </div>`;
}

/* ════════════════════════════════════════════════════════════
   9.  RENDER: ADMIN
════════════════════════════════════════════════════════════ */
let adminFilter = 'All';

function renderAdmin() {
  const body   = document.getElementById('admin-body');
  if (!body) return;
  const orders = [...loadOrders()].reverse();

  const cRec  = orders.filter(o => o.status === 'Received').length;
  const cPre  = orders.filter(o => o.status === 'Preparing').length;
  const cRdy  = orders.filter(o => o.status === 'Ready').length;
  const rev   = orders.reduce((s, o) => s + o.total, 0);

  const statsHtml = `
    <div class="stat-grid">
      <div class="card stat-card"><span class="stat-lbl">📬 Received</span><span class="stat-val sv-blue">${cRec}</span></div>
      <div class="card stat-card"><span class="stat-lbl">👨‍🍳 Preparing</span><span class="stat-val sv-yellow">${cPre}</span></div>
      <div class="card stat-card"><span class="stat-lbl">✅ Ready</span><span class="stat-val sv-green">${cRdy}</span></div>
      <div class="card stat-card"><span class="stat-lbl">💰 Session Revenue</span><span class="stat-val sv-orange">${fmtPrice(rev)}</span></div>
    </div>`;

  if (orders.length === 0) {
    body.innerHTML = statsHtml + `
      <div class="empty">
        <div class="e-icon">🗂️</div>
        <div class="e-title">No orders this session</div>
        <div class="e-sub">Orders appear here once customers place them.</div>
      </div>`;
    return;
  }

  const filterBtns = `
    <div class="filter-row" id="admin-filter-row">
      ${['All','Received','Preparing','Ready'].map(f => `
        <button class="tab-btn ${adminFilter===f?'active':''}" id="filter-${f.toLowerCase()}"
          onclick="setAdminFilter('${f}')">
          ${f==='Received'?'📬 ':''}${f==='Preparing'?'👨‍🍳 ':''}${f==='Ready'?'✅ ':''}
          ${escHtml(f)} (${f==='All'?orders.length:orders.filter(o=>o.status===f).length})
        </button>`).join('')}
    </div>
    <p class="admin-note">Updating status here is instant – customers see the change on their Orders page.</p>`;

  const tableRows = orders.map(o => {
    const itemsSummary = o.items.map(i => `${escHtml(i.name)} ×${i.qty}`).join(', ');
    return `
      <tr class="admin-row" data-status="${escHtml(o.status)}" id="arow-${escHtml(o.order_no)}"
          style="${adminFilter!=='All'&&adminFilter!==o.status?'display:none':''}">
        <td><strong>${escHtml(o.order_no)}</strong></td>
        <td>${escHtml(o.name)}</td>
        <td>${escHtml(o.phone)}</td>
        <td style="max-width:200px;white-space:normal">${itemsSummary}</td>
        <td><strong style="color:var(--acl)">${fmtPrice(o.total)}</strong></td>
        <td style="font-size:.8rem;color:var(--tm)">${escHtml(o.placed_at)}</td>
        <td><span class="sbadge ${statusClass(o.status)}">${statusIcon(o.status)} ${escHtml(o.status)}</span></td>
        <td>
          <select class="sel-status" id="sel-${escHtml(o.order_no)}"
            onchange="adminUpdateStatus('${escHtml(o.order_no)}', this.value)">
            <option value="Received"  ${o.status==='Received' ?'selected':''}>📬 Received</option>
            <option value="Preparing" ${o.status==='Preparing'?'selected':''}>👨‍🍳 Preparing</option>
            <option value="Ready"     ${o.status==='Ready'    ?'selected':''}>✅ Ready</option>
          </select>
        </td>
      </tr>`;
  }).join('');

  /* ── Menu Availability Section ── */
  const menuRows = getMenu().map(item => {
    return `
      <tr>
        <td>${item.emoji} <strong>${escHtml(item.name)}</strong></td>
        <td><small style="color:var(--tm)">${escHtml(item.cat)}</small></td>
        <td><span class="sbadge ${item.avail ? 'badge-green' : 'badge-red'}" id="avbadge-${item.id}">
          ${item.avail ? '✅ Available' : '❌ Sold Out'}
        </span></td>
        <td>
          <button id="avtog-${item.id}" class="btn ${item.avail ? 'btn-danger' : 'btn-primary'} btn-sm"
            onclick="toggleAvailability('${item.id}')">
            ${item.avail ? '🚫 Mark Sold Out' : '✅ Mark Available'}
          </button>
        </td>
      </tr>`;
  }).join('');

  const availSection = `
    <div class="card" style="margin-bottom:24px">
      <div class="admin-sec-title">🍽️ Menu Availability</div>
      <div class="admin-table-wrap">
        <table id="avail-table">
          <thead><tr><th>Item</th><th>Category</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>${menuRows}</tbody>
        </table>
      </div>
    </div>`;

  body.innerHTML = `
    ${statsHtml}
    ${availSection}
    ${filterBtns}
    <div class="card">
      <div class="admin-sec-title">All Orders</div>
      <div class="admin-table-wrap">
        <table id="admin-table">
          <thead>
            <tr>
              <th>Order No.</th><th>Customer</th><th>Phone</th>
              <th>Items</th><th>Total</th><th>Placed At</th><th>Status</th><th>Update</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      </div>
    </div>`;
}

function setAdminFilter(f) {
  adminFilter = f;
  renderAdmin();
}

function adminUpdateStatus(order_no, status) {
  updateStatus(order_no, status);
  flash('admin-flash-zone', `Order ${order_no} → ${status}`, 'success');
  renderAdmin();
}

function toggleAvailability(id) {
  const item    = MENU.find(i => i.id === id);
  if (!item) return;
  const avail   = loadAvailability();
  const current = (id in avail) ? avail[id] : item.avail;
  avail[id]     = !current;
  saveAvailability(avail);
  const newState = avail[id];
  flash('admin-flash-zone',
    `${item.emoji} ${item.name} marked as ${newState ? '✅ Available' : '❌ Sold Out'}`, 'success');
  // Update just the row cells inline (no full re-render)
  const badge = document.getElementById(`avbadge-${id}`);
  const btn   = document.getElementById(`avtog-${id}`);
  if (badge) {
    badge.className = `sbadge ${newState ? 'badge-green' : 'badge-red'}`;
    badge.textContent = newState ? '✅ Available' : '❌ Sold Out';
  }
  if (btn) {
    btn.className = `btn ${newState ? 'btn-danger' : 'btn-primary'} btn-sm`;
    btn.textContent = newState ? '🚫 Mark Sold Out' : '✅ Mark Available';
  }
  // Re-render menu grid so changes reflect in the customer menu immediately
  renderMenuGrid();
}

/* ════════════════════════════════════════════════════════════
   10.  INIT
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  updateBadge();
  renderMenu();
  document.getElementById('nav-menu').classList.add('active');
});
