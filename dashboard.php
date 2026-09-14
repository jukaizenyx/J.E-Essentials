<?php
require_once 'cart_functions.php';
if (!je_is_logged_in()) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}
$products = je_get_products();
$je_user = je_current_user();

// Expects each order shaped as:
// ['id' => int, 'created_at' => 'Y-m-d H:i:s', 'status' => 'pending'|'approved'|'cancelled'|'completed',
//  'total' => float, 'items' => [['name' => string, 'quantity' => int, 'price' => float], ...]]
$je_orders = function_exists('je_get_user_orders') ? je_get_user_orders($je_user['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
    <link rel="stylesheet" href="./dashboard_css/dashboard.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="simple-page">
    <a href="shop.php" class="profile-back">&larr; Go back to shop</a>
    <h1>Orders</h1>
    <p class="simple-page-sub">Welcome back, <?= htmlspecialchars($je_user['username']) ?>.</p>
    <?php if (empty($je_orders)): ?>
        <div class="orders-empty">
            <p>No orders yet. Once you check out, they'll show up here.</p>
            <a href="shop.php" class="btn-primary">Browse the shop</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($je_orders as $order):
                $status = $order['status'];
                $can_cancel = ($status === 'pending');
                $status_label = $status === 'pending' ? 'Pending approval' : ucfirst($status);
                $order_date = date('M j, Y \a\t g:i A', strtotime($order['created_at']));
            ?>
            <article class="order-card" data-order-id="<?= (int) $order['id'] ?>" data-status="<?= htmlspecialchars($status) ?>">
                <header class="order-card__head">
                    <div class="order-card__id">
                        <span class="order-card__label">Order</span>
                        <span class="order-card__number">#<?= (int) $order['id'] ?></span>
                    </div>
                    <time class="order-card__date"><?= htmlspecialchars($order_date) ?></time>
                    <span class="order-status order-status--<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status_label) ?></span>
                </header>

                <ul class="order-card__items">
                    <?php foreach ($order['items'] as $item): ?>
                    <li class="order-item">
                        <span class="order-item__name"><?= htmlspecialchars($item['name']) ?></span>
                        <span class="order-item__qty">&times;<?= (int) $item['quantity'] ?></span>
                        <span class="order-item__price">&#8369;<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <footer class="order-card__foot">
                    <div class="order-card__total">
                        <span>Total</span>
                        <strong>&#8369;<?= number_format($order['total'], 2) ?></strong>
                    </div>
                    <button
                        type="button"
                        class="btn-cancel-order"
                        data-order-id="<?= (int) $order['id'] ?>"
                        <?= $can_cancel ? '' : 'disabled' ?>
                    ><?= $status === 'cancelled' ? 'Cancelled' : 'Cancel order' ?></button>
                </footer>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<script>
    document.querySelectorAll('.btn-cancel-order').forEach(function (btn) {
    btn.addEventListener('click', function () {
        if (this.disabled) return;
        if (!confirm("Cancel this order? This can't be undone.")) return;

        var card = this.closest('.order-card');
        var orderId = this.dataset.orderId;
        var originalLabel = this.textContent;

        this.disabled = true;
        this.textContent = 'Cancelling…';

        fetch('cancel_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                card.dataset.status = 'cancelled';
                var badge = card.querySelector('.order-status');
                badge.className = 'order-status order-status--cancelled';
                badge.textContent = 'Cancelled';
                btn.textContent = 'Cancelled';
            } else {
                alert(data.message || 'Could not cancel this order.');
                btn.disabled = false;
                btn.textContent = originalLabel;
            }
        })
        .catch(function () {
            alert('Something went wrong. Please try again.');
            btn.disabled = false;
            btn.textContent = originalLabel;
        });
    });
});
</script>
<script>
    // Server-sourced product + auth data, handed to javascript.js.
    // Prices always come from PHP — the JS never invents a price.
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="/javascript.js"></script>
</body>
</html>