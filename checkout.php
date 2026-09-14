<?php
require_once 'cart_functions.php';
require_once './database/config.php';

if (!je_is_logged_in()) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$user = je_current_user();
$cart = je_cart_snapshot();

if (empty($cart['items'])) {
    header('Location: cart.php');
    exit;
}

$products = je_get_products();
$shipping_fee = 80;
$error = '';

$full_name = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$full_name = preg_replace('/\s+/', ' ', $full_name);
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$saved_address = $user['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $house = trim($_POST['house'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    if ($full_name === '' || $email === '' || $phone === '' || $house === '' || $street === '' || $barangay === '' || $city === '' || $province === '' || $zip === '' || $payment_method === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($payment_method, ['Cash on Delivery', 'GCash', 'Bank Transfer'], true)) {
        $error = 'Please select a valid payment method.';
    } else {
        $address = $house . ', ' . $street . ', ' . $barangay . ', ' . $city . ', ' . $province . ' ' . $zip;
        $subtotal = 0;

        foreach ($cart['items'] as $item) {
            $subtotal += (float)$item['line_total'];
        }

        $total_amount = $subtotal + $shipping_fee;

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, email, phone, address, payment_method, subtotal, shipping_fee, total_amount, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
            $stmt->bind_param("isssssddd", $user['id'], $full_name, $email, $phone, $address, $payment_method, $subtotal, $shipping_fee, $total_amount);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();

            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");

            foreach ($cart['items'] as $item) {
                $product_id = (int)($item['product_id'] ?? $item['id'] ?? 0);
                $quantity = (int)$item['qty'];
                $price = (float)($item['price'] ?? ($item['line_total'] / max($quantity, 1)));
                $item_subtotal = (float)$item['line_total'];

                if ($product_id <= 0) {
                    throw new Exception('Invalid product in cart.');
                }

                $item_stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $price, $item_subtotal);
                $item_stmt->execute();
            }

            $item_stmt->close();

            /*
             * Replace this with the cart-clearing function already used
             * in your cart_functions.php if it has a different name.
             */
            je_clear_cart();

            $conn->commit();

            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Something went wrong while placing your order. Please try again.';
        }
    }
}

$subtotal = 0;

foreach ($cart['items'] as $item) {
    $subtotal += (float)$item['line_total'];
}

$total_amount = $subtotal + $shipping_fee;

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — J.E Eullaran Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
    <link rel="stylesheet" href="./checkout_css/checkout.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="checkout-page">
    <a href="shop.php" class="checkout-back">&larr; Back to cart</a>

    <section class="checkout-header">
        <p class="checkout-eyebrow">J.E EULLARAN ESSENTIALS</p>
        <h1>Checkout</h1>
        <p>Complete your delivery information and place your order.</p>
    </section>

    <?php if ($error): ?>
        <div class="checkout-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="checkout-layout">
        <section class="checkout-form-card">
            <div class="checkout-section-title">
                <p>DELIVERY INFORMATION</p>
                <h2>Customer Details</h2>
            </div>

            <div class="checkout-form-grid">
                <div class="checkout-form-group checkout-full">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= e($full_name) ?>" required>
                </div>

                <div class="checkout-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
                </div>

                <div class="checkout-form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= e($phone) ?>" required>
                </div>

                <div class="checkout-form-group">
                    <label for="house">House/Unit No.</label>
                    <input type="text" id="house" name="house" placeholder="e.g. 123" required>
                </div>

                <div class="checkout-form-group">
                    <label for="street">Street</label>
                    <input type="text" id="street" name="street" value="<?= e($saved_address) ?>" required>
                </div>

                <div class="checkout-form-group">
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" name="barangay" required>
                </div>

                <div class="checkout-form-group">
                    <label for="city">City/Municipality</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="checkout-form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" required>
                </div>

                <div class="checkout-form-group">
                    <label for="zip">ZIP Code</label>
                    <input type="text" id="zip" name="zip" required>
                </div>
            </div>

            <div class="checkout-section-title checkout-payment-title">
                <p>PAYMENT</p>
                <h2>Payment Method</h2>
            </div>

            <div class="payment-options">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="Cash on Delivery" required>
                    <span>
                        <strong>Cash on Delivery</strong>
                        <small>Pay when your order arrives.</small>
                    </span>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="GCash">
                    <span>
                        <strong>GCash</strong>
                        <small>Payment through GCash.</small>
                    </span>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="Bank Transfer">
                    <span>
                        <strong>Bank Transfer</strong>
                        <small>Pay through bank transfer.</small>
                    </span>
                </label>
            </div>
        </section>

        <aside class="checkout-summary">
            <div class="checkout-summary-card">
                <p class="checkout-summary-label">YOUR ORDER</p>
                <h2>Order Summary</h2>

                <div class="checkout-products">
                    <?php foreach ($cart['items'] as $item): ?>
                        <?php
                        $image = $item['image'] ?? $item['image_url'] ?? '';
                        $image = $image !== '' ? $image : './images/placeholder.jpg';
                        ?>
                        <div class="checkout-product">
                            <img src="<?= e($image) ?>" alt="<?= e($item['name']) ?>">

                            <div class="checkout-product-info">
                                <strong><?= e($item['name']) ?></strong>
                                <?php if (!empty($item['size'])): ?>
                                    <small>Size: <?= e($item['size']) ?></small>
                                <?php endif; ?>
                                <small>Qty: <?= e($item['qty']) ?></small>
                            </div>

                            <span>₱<?= number_format((float)$item['line_total'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-totals">
                    <div>
                        <span>Subtotal</span>
                        <strong>₱<?= number_format($subtotal, 2) ?></strong>
                    </div>

                    <div>
                        <span>Shipping Fee</span>
                        <strong>₱<?= number_format($shipping_fee, 2) ?></strong>
                    </div>

                    <div class="checkout-grand-total">
                        <span>Total</span>
                        <strong>₱<?= number_format($total_amount, 2) ?></strong>
                    </div>
                </div>

                <button type="submit" class="checkout-place-btn">Place Order</button>
            </div>
        </aside>
    </form>
</main>

<script>
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="/javascript.js"></script>
</body>
</html>