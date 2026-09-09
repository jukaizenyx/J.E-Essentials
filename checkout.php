<?php
require_once'cart_functions.php';

if (!je_is_logged_in()) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$cart = je_cart_snapshot();
$user = je_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="simple-page">
    <h1>Checkout</h1>
    <p class="simple-page-sub">Logged in as <?= htmlspecialchars($user['username']) ?></p>

    <?php if (empty($cart['items'])): ?>
        <p>Your cart is empty. <a href="shop.php">Go back to the shop</a>.</p>
    <?php else: ?>
        <div class="checkout-list">
            <?php foreach ($cart['items'] as $item): ?>
                <div class="checkout-row">
                    <span><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['size']) ?>) × <?= $item['qty'] ?></span>
                    <span>₱<?= number_format($item['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="checkout-row checkout-total">
                <span>Total</span>
                <span>₱<?= number_format($cart['subtotal']) ?></span>
            </div>
        </div>
        <p class="simple-page-sub">Payment isn't wired up yet — hook this button to your payment provider when you're ready.</p>
        <button type="button" class="btn-primary" disabled>Place Order</button>
    <?php endif; ?>
</main>
</body>
</html>