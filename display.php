<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ready Orders Display - Campus Canteen</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #0f172a; color: #f8fafc; padding: 24px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #334155; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { font-size: 2.2rem; color: #22c55e; letter-spacing: 1px; }
        .clock { font-size: 1.6rem; color: #94a3b8; font-weight: 700; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .token-card { background: #1e293b; border: 2px solid #22c55e; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 0 20px rgba(34, 197, 94, 0.2); animation: pulse 2s infinite alternate; }
        .token-no { font-size: 2.4rem; font-weight: 800; color: #4ade80; margin-bottom: 6px; }
        .token-cust { font-size: 1.1rem; color: #cbd5e1; }
        .empty-msg { font-size: 1.4rem; color: #64748b; text-align: center; grid-column: 1 / -1; padding: 80px 0; }
        @keyframes pulse { from { box-shadow: 0 0 10px rgba(34, 197, 94, 0.1); } to { box-shadow: 0 0 25px rgba(34, 197, 94, 0.4); } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>NOW READY FOR PICKUP 🔔</h1>
            <p style="color: #94a3b8; margin-top: 4px;">Please collect your meal at the delivery counter</p>
        </div>
        <div class="clock" id="clock">--:--:--</div>
    </div>

    <div class="grid" id="ready-grid">
        <div class="empty-msg">No orders ready for pickup currently.</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    function updateClock() {
        $('#clock').text(new Date().toLocaleTimeString());
    }
    setInterval(updateClock, 1000);
    updateClock();

    function fetchReadyOrders() {
        $.get('api.php', { action: 'get_ready' }, function(res) {
            if (res.success) {
                var $grid = $('#ready-grid');
                $grid.empty();
                if (res.orders.length === 0) {
                    $grid.html('<div class="empty-msg">No orders ready for pickup currently.</div>');
                } else {
                    $.each(res.orders, function(idx, o) {
                        $grid.append(`
                            <div class="token-card">
                                <div class="token-no">${o.order_no}</div>
                                <div class="token-cust">${o.customer_name}</div>
                            </div>
                        `);
                    });
                }
            }
        }, 'json');
    }
    fetchReadyOrders();
    setInterval(fetchReadyOrders, 3000);
    </script>
</body>
</html>
