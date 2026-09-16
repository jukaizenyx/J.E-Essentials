<?php
require_once 'cart_functions.php';
je_restore_login();
$products = je_get_products();

// Nav data (previously in includes/nav.php, now inlined below)
$je_user = je_current_user();
$je_cart_count = 0;

foreach ($_SESSION['cart'] as $entry) {
    $je_cart_count += $entry['qty'];
}

$je_notifications = [];
$je_unread_notifications = 0;

if ($je_user) {
    $je_notifications = je_get_notifications((int)$je_user['id']);
    $je_unread_notifications = je_get_unread_notification_count((int)$je_user['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/shop_css/shop.css">
    <title>Document</title>
</head>
<body>
    <header class="site-nav">
    <div class="site-nav-inner">
        <a href="index.php" class="logo">
          <img src="images/je_logo.svg" alt="">
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php#top">HOME</a></li>
                <li><a href="index.php#about">ABOUT US</a></li>
                <li><a href="index.php#app">OUR APP</a></li>
                <li><a href="index.php#app">CONTACT US</a></li>
            </ul>
        </nav>

        <div class="shop-cart-nav">
            <a href="#" class="cart-btn" id="cartTrigger" aria-label="View cart">
                <img src="./images/cart-plus-svgrepo-com.svg" alt="">
                <span class="cart-count" id="cartCount"><?= (int)$je_cart_count ?></span>
            </a>
              

                <?php if ($je_user): ?>
    <div class="notification-nav" id="notificationNav">
        <button
            type="button"
            class="notification-btn"
            id="notificationTrigger"
            aria-label="Notifications"
            aria-expanded="false"
        >
            <span class="notification-icon">🔔</span>
            <?php if ($je_unread_notifications > 0): ?>
                <span class="notification-count"><?= $je_unread_notifications ?></span>
            <?php endif; ?>
        </button>

        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
                <strong>Notifications</strong>
            </div>

            <div class="notification-list">
                <?php if (empty($je_notifications)): ?>
                    <div class="notification-empty">
                        No notifications yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($je_notifications as $notification): ?>
                        <a
                            href="dashboard.php"
                            class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>"
                        >
                            <span class="notification-message">
                                <?= htmlspecialchars($notification['message']) ?>
                            </span>
                            <small>
                                <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                            </small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

            <?php if ($je_user): ?>
                <div class="profile-nav" id="profileNav">
                    <button class="profile-btn" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($je_user['username'], 0, 1))) ?></span>
                        <span class="profile-name"><?= htmlspecialchars($je_user['username']) ?></span>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                    <a href="dashboard.php">Orders</a>
                    <a href="profile.php">Profile</a>
                    <a href="settings.php">Settings</a>

                    <?php if (!empty($je_user['is_admin']) && $je_user['is_admin'] == 1): ?>
                        <hr>
                        <a href="admin.php">Admin Dashboard</a>
                    <?php endif; ?>

                    <hr>
                    <a href="logout.php">Log Out</a>
                </div>
                </div>
            <?php else: ?>
                <a href="login.php?redirect=<?= urlencode(basename($_SERVER['PHP_SELF'])) ?>" class="account-link">LOG IN</a>
            <?php endif; ?>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- ============ Cart modal ============ -->
<div class="modal-overlay" id="cartModalOverlay">
    <div class="modal cart-modal" role="dialog" aria-modal="true" aria-labelledby="cartModalTitle">
        <button class="modal-close" id="cartModalClose" aria-label="Close">&times;</button>
        <h2 id="cartModalTitle">Your Cart</h2>

        <div class="cart-items" id="cartItems">
            <p class="cart-empty" id="cartEmptyMsg">Your cart is empty.</p>
        </div>

        <div class="cart-summary">
            <div class="cart-subtotal-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">₱0</span>
            </div>
            <button type="button" class="btn-primary cart-checkout-btn" id="cartCheckoutBtn">Checkout</button>
        </div>
    </div>
</div>

<!-- ============ Tiny "added to cart" toast ============ -->
<div class="cart-toast" id="cartToast">Added to cart</div>
<script>
    // Server-sourced product + auth data, handed to javascript.js.
    // Prices always come from PHP — the JS never invents a price.
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="/javascript.js"></script>
<script>
const notificationTrigger = document.getElementById('notificationTrigger');
const notificationDropdown = document.getElementById('notificationDropdown');

if (notificationTrigger && notificationDropdown) {
    notificationTrigger.addEventListener('click', function (event) {
        event.stopPropagation();

        const isOpen = notificationDropdown.classList.toggle('is-open');
        notificationTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#notificationNav')) {
            notificationDropdown.classList.remove('is-open');
            notificationTrigger.setAttribute('aria-expanded', 'false');
        }
    });
}
</script>
</body>
</html>
    