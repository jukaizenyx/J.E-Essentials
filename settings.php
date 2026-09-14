<?php
require_once 'cart_functions.php';
if (!je_is_logged_in()) {
    header('Location: login.php?redirect=settings.php');
    exit;
}
$user = je_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
    <link rel="stylesheet" href="./settings_css/settings.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="settings-page">
    <a href="shop.php" class="profile-back">&larr; Go back to shop</a>

    <section class="settings-header">
        <p class="profile-eyebrow">MY ACCOUNT</p>
        <h1>Settings</h1>
        <p>Manage your account and shopping preferences.</p>
    </section>

    <div class="settings-container">
        <div class="settings-card">
            <p class="profile-section-label">ACCOUNT</p>
            <h2>Account Settings</h2>

            <a href="profile.php" class="settings-option">
                <div>
                    <strong>Personal Information</strong>
                    <p>Update your name, email, phone number, and address.</p>
                </div>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="settings-card">
            <p class="profile-section-label">SECURITY</p>
            <h2>Password & Security</h2>

            <a href="change_password.php" class="settings-option">
                <div>
                    <strong>Change Password</strong>
                    <p>Update your account password.</p>
                </div>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="settings-card">
            <p class="profile-section-label">SHOPPING</p>
            <h2>Shopping</h2>

            <a href="orders.php" class="settings-option">
                <div>
                    <strong>My Orders</strong>
                    <p>View your previous and current orders.</p>
                </div>
                <span>&rarr;</span>
            </a>

            <a href="cart.php" class="settings-option">
                <div>
                    <strong>My Cart</strong>
                    <p>View the products currently in your cart.</p>
                </div>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="settings-card">
            <p class="profile-section-label">ACCOUNT ACTIONS</p>
            <h2>Manage Account</h2>

            <a href="logout.php" class="settings-option settings-logout">
                <div>
                    <strong>Logout</strong>
                    <p>Sign out of your J.E Essentials account.</p>
                </div>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</main>
<script>
    // Server-sourced product + auth data, handed to javascript.js.
    // Prices always come from PHP — the JS never invents a price.
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="/javascript.js"></script>
</body>
</html>