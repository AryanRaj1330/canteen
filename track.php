<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Campus Canteen</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .track-box {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .progress-bar-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 30px 0 20px;
        }
        .progress-bar-container::before {
            content: '';
            position: absolute;
            top: 14px;
            left: 20px;
            right: 20px;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
        }
        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .step.active .step-circle {
            background: #3b82f6;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        .step.done .step-circle {
            background: #22c55e;
            color: #fff;
        }
        .step-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        .step.active .step-label {
            color: #1e293b;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="brand">Campus Canteen</div>
        <ul class="nav-links">
            <li><a href="index.php">Menu & Order</a></li>
            <li><a href="orders.php">Queue Status</a></li>
            <li><a href="track.php" class="active">Track Order</a></li>
            <li><a href="admin.php">Staff Dashboard</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="track-box">
            <h2 style="margin-bottom: 8px; color: #1e293b;">Track Your Order Status</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
                Enter your order number (e.g. <code>ORD-XXXX</code>) below to view live preparation progress.
            </p>

            <form id="track-form" style="display: flex; gap: 8px; margin-bottom: 24px;">
                <input type="text" id="order_no" name="order_no" class="form-control" placeholder="ORD-XXXX" required style="font-size: 1.1rem; text-transform: uppercase;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Track</button>
            </form>

            <div id="track-result" style="display: none;">
                <div style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 0.85rem; color: #64748b;">Order Number</span>
                            <h3 id="res-order-no" style="color: #1e293b;">-</h3>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.85rem; color: #64748b;">Customer</span>
                            <div id="res-customer" style="font-weight: 600;">-</div>
                        </div>
                    </div>

                    <div class="progress-bar-container">
                        <div class="step" id="step-received">
                            <div class="step-circle">1</div>
                            <div class="step-label">Received</div>
                        </div>
                        <div class="step" id="step-preparing">
                            <div class="step-circle">2</div>
                            <div class="step-label">Preparing</div>
                        </div>
                        <div class="step" id="step-ready">
                            <div class="step-circle">3</div>
                            <div class="step-label">Ready for Pickup</div>
                        </div>
                    </div>

                    <div id="status-desc" style="text-align: center; padding: 12px; background: #f8fafc; border-radius: 6px; font-size: 0.95rem; color: #334155; margin-top: 16px;"></div>
                </div>
            </div>

            <div id="track-error" style="display: none;" class="alert alert-danger"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $('#track-form').on('submit', function(e) {
        e.preventDefault();
        var orderNo = $('#order_no').val().trim().toUpperCase();
        $('#track-error').hide();
        $('#track-result').hide();

        $.get('api.php', { action: 'get_order_status', order_no: orderNo }, function(res) {
            if (res.success) {
                var o = res.order;
                $('#res-order-no').text(o.order_no);
                $('#res-customer').text(o.customer_name);

                $('.step').removeClass('active done');
                if (o.status === 'Received') {
                    $('#step-received').addClass('active');
                    $('#status-desc').text('Your order has been received by the kitchen and is queued.');
                } else if (o.status === 'Preparing') {
                    $('#step-received').addClass('done');
                    $('#step-preparing').addClass('active');
                    $('#status-desc').text('The chef is currently preparing your meal.');
                } else if (o.status === 'Ready') {
                    $('#step-received').addClass('done');
                    $('#step-preparing').addClass('done');
                    $('#step-ready').addClass('done active');
                    $('#status-desc').html('<strong style="color: #15803d;">🎉 Order is Ready! Please collect from the counter.</strong>');
                }

                $('#track-result').fadeIn();
            } else {
                $('#track-error').text(res.message || 'Order not found').show();
            }
        }, 'json');
    });
    </script>
</body>
</html>
