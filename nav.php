<?php
require_once 'cart_functions.php';
$products = je_get_products();

// Nav data (previously in includes/nav.php, now inlined below)
$je_user = je_current_user();
$je_cart_count = 0;
foreach ($_SESSION['cart'] as $entry) {
    $je_cart_count += $entry['qty'];
}
/**
 * Expects je_cart_functions.php to already be required by the including
 * page (so session + helper functions are available).
 */
$je_user = je_current_user();
$je_cart_count = 0;
foreach ($_SESSION['cart'] as $entry) {
    $je_cart_count += $entry['qty'];
}
?>

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
                <li class="nav-mobile-contact"><a href="index.php#contact">CONTACT US</a></li>
            </ul>
        </nav>

        <div class="shop-cart-nav">
            <a href="#" class="cart-btn" id="cartTrigger" aria-label="View cart">
                <img src="./images/cart-plus-svgrepo-com.svg" alt="">
                <span class="cart-count" id="cartCount"><?= (int)$je_cart_count ?></span>
            </a>

            <?php if ($je_user): ?>
                <div class="profile-nav" id="profileNav">
                    <button class="profile-btn" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($je_user['username'], 0, 1))) ?></span>
                        <span class="profile-name"><?= htmlspecialchars($je_user['username']) ?></span>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="profile.php">Profile</a>
                        <a href="settings.php">Settings</a>
                        <hr>
                        <a href="logout.php">Log Out</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php?redirect=<?= urlencode(basename($_SERVER['PHP_SELF'])) ?>" class="account-link">Log In</a>
            <?php endif; ?>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<script src="javascript.js"></script>