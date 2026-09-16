<?php

require_once 'cart_functions.php';
je_restore_login();
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
$order_success = false;
$payment_proof = null;

/* ---------- Calculate cart totals ---------- */

$subtotal = 0;

foreach ($cart['items'] as $item) {
    $subtotal += (float)$item['line_total'];
}

$total_amount = $subtotal + $shipping_fee;


/* ---------- Customer information ---------- */

$full_name = trim(
    ($user['first_name'] ?? '') . ' ' .
    ($user['middle_name'] ?? '') . ' ' .
    ($user['last_name'] ?? '')
);

$full_name = preg_replace('/\s+/', ' ', $full_name);

$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$saved_address = $user['address'] ?? '';


/* =========================================================
   PROCESS CHECKOUT
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $address_type = $_POST['address_type'] ?? 'profile';
    $payment_proof_file = $_FILES['payment_proof'] ?? null;


    /* ---------- Basic validation ---------- */

    if (
        $full_name === '' ||
        $email === '' ||
        $phone === '' ||
        $payment_method === ''
    ) {

        $error = 'Please fill in all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif (!in_array(
        $payment_method,
        ['Cash on Delivery', 'GCash', 'Bank Transfer'],
        true
    )) {

        $error = 'Please select a valid payment method.';
    }
    if ($error === '' && $payment_method === 'GCash') {
    if (!$payment_proof_file || $payment_proof_file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please upload your GCash payment proof.';
    } elseif ($payment_proof_file['error'] !== UPLOAD_ERR_OK) {
        $error = 'There was a problem uploading your payment proof.';
    } elseif ($payment_proof_file['size'] > 5 * 1024 * 1024) {
        $error = 'Payment proof must not exceed 5MB.';
    } else {
        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $file_type = mime_content_type($payment_proof_file['tmp_name']);

        if (!in_array($file_type, $allowed_types, true)) {
            $error = 'Please upload a JPG, PNG, or WEBP image.';
        }
    }
}

if ($error === '') {
    if ($address_type === 'profile') {
        $address = trim($user['address'] ?? '');

        if ($address === '') {
            $error = 'Please add an address to your profile first.';
        }
    } elseif ($address_type === 'new') {
        $house = trim($_POST['house'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $zip = trim($_POST['zip'] ?? '');

        if (
            $house === '' ||
            $street === '' ||
            $barangay === '' ||
            $city === '' ||
            $province === '' ||
            $zip === ''
        ) {
            $error = 'Please complete the delivery address.';
        } else {
            $address = "$house, $street, $barangay, $city, $province, $zip";
        }
    } else {
        $error = 'Invalid address type.';
    }
}


    /* ---------- Address validation ---------- */

    elseif ($address_type === 'profile') {

        $address = trim($user['address'] ?? '');

        if ($address === '') {
            $error = 'Please add an address to your profile first.';
        }

    } elseif ($address_type === 'new') {

        $house = trim($_POST['house'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $zip = trim($_POST['zip'] ?? '');

        if (
            $house === '' ||
            $street === '' ||
            $barangay === '' ||
            $city === '' ||
            $province === '' ||
            $zip === ''
        ) {

            $error = 'Please complete the delivery address.';

        } else {

            $address = "$house, $street, $barangay, $city, $province, $zip";
        }

    } else {

        $error = 'Invalid address type.';
    }


    /* =====================================================
       ONLY PLACE ORDER IF THERE ARE NO VALIDATION ERRORS
       ===================================================== */

    if ($error === '') {

        $conn->begin_transaction();

        try {
                    /* ---------- Upload payment proof ---------- */

            if ($payment_method === 'GCash') {
                $upload_dir = __DIR__ . '/uploads/payment_proofs/';

                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        throw new Exception('Could not create payment proof folder.');
                    }
                }

                $extension = strtolower(
                    pathinfo(
                        $payment_proof_file['name'],
                        PATHINFO_EXTENSION
                    )
                );

                $payment_proof = 'proof_' . uniqid('', true) . '.' . $extension;

                $upload_path = $upload_dir . $payment_proof;

                if (!move_uploaded_file(
                    $payment_proof_file['tmp_name'],
                    $upload_path
                )) {
                    throw new Exception('Failed to save payment proof.');
                }
            }
            /* ---------- Create order ---------- */

            $user_id = (int)$user['id'];

                $stmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    full_name,
                    email,
                    phone,
                    address,
                    payment_method,
                    payment_proof,
                    status,
                    total,
                    created_at
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())
            ");
            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare order query: ' . $conn->error
                );
            }

                $stmt->bind_param(
                "issssssd",
                $user_id,
                $full_name,
                $email,
                $phone,
                $address,
                $payment_method,
                $payment_proof,
                $total_amount
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to insert order: ' . $stmt->error
                );
            }

            $order_id = $conn->insert_id;

            $stmt->close();


            /* ---------- Create order items ---------- */

            $item_stmt = $conn->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    name,
                    size,
                    price,
                    quantity
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$item_stmt) {
                throw new Exception(
                    'Failed to prepare order items query: ' . $conn->error
                );
            }


            foreach ($cart['items'] as $item) {

                /* Product ID */

                $product_id = (int)(
                    $item['product_id']
                    ?? $item['id']
                    ?? 0
                );


                /* Product name */

                $name = (string)(
                    $item['name']
                    ?? $item['product_name']
                    ?? ''
                );


                /* Product size */

                $size = (string)(
                    $item['size']
                    ?? ''
                );


                /* Quantity */

                $quantity = (int)(
                    $item['qty']
                    ?? $item['quantity']
                    ?? 1
                );


                /* Price */

                $price = (float)(
                    $item['price']
                    ?? 0
                );


                /* Validate product */

                if ($product_id <= 0) {
                    throw new Exception(
                        'Invalid product in cart.'
                    );
                }

                if ($name === '') {
                    throw new Exception(
                        'Product name is missing from the cart.'
                    );
                }


                /* Insert order item */

                $item_stmt->bind_param(
                    "iissdi",
                    $order_id,
                    $product_id,
                    $name,
                    $size,
                    $price,
                    $quantity
                );


                if (!$item_stmt->execute()) {
                    throw new Exception(
                        'Failed to insert order item: '
                        . $item_stmt->error
                    );
                }
            }

            $item_stmt->close();


            /* ---------- Clear cart ---------- */

            je_clear_cart();


            /* ---------- Finish transaction ---------- */

            $conn->commit();

            /* ---------- Show success modal ---------- */

            $order_success = true;


        } catch (Exception $e) {

            $conn->rollback();

            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}


/* ---------- Escape output ---------- */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
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

    <form method="POST" action="checkout.php" class="checkout-layout" enctype="multipart/form-data">
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
        </div>

        <br>
       

        <div class="checkout-section-title checkout-address-title">
            <p>DELIVERY ADDRESS</p>
            <h2>Where should we deliver?</h2>
        </div>

        <div class="address-options">
            <label class="address-option">
                <input type="radio" name="address_type" value="profile" checked>
                <span>
                    <strong>Use my profile address</strong>
                    <small>
                        <?= !empty($saved_address) ? e($saved_address) : 'No address saved in your profile.' ?>
                    </small>
                </span>
            </label>

            <label class="address-option">
                <input type="radio" name="address_type" value="new">
                <span>
                    <strong>Use a different delivery address</strong>
                    <small>Enter another address for this order.</small>
                </span>
            </label>
        </div>

        <div id="new-address-fields" class="checkout-form-grid new-address-fields">
            <div class="checkout-form-group">
                <label for="house">House/Unit No.</label>
                <input type="text" id="house" name="house" placeholder="e.g. 123">
            </div>

            <div class="checkout-form-group">
                <label for="street">Street</label>
                <input type="text" id="street" name="street" placeholder="e.g. Rizal Street">
            </div>

            <div class="checkout-form-group">
                <label for="barangay">Barangay</label>
                <input type="text" id="barangay" name="barangay" placeholder="e.g. Poblacion">
            </div>

            <div class="checkout-form-group">
                <label for="city">City/Municipality</label>
                <input type="text" id="city" name="city" placeholder="e.g. Cebu City">
            </div>

            <div class="checkout-form-group">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" placeholder="e.g. Cebu">
            </div>

            <div class="checkout-form-group">
                <label for="zip">ZIP Code</label>
                <input type="text" id="zip" name="zip" placeholder="e.g. 6000">
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
            <strong>QrPH</strong>
            <small>Payment through Qr Code.</small>
        </span>
    </label>
   <!-- Proof of Payment -->
<div id="payment-proof-section" class="payment-proof-section">
    <div class="payment-proof-content">
        <h3>Proof of Payment</h3>
        <p>After completing your QrPH payment, upload a screenshot of your payment confirmation.</p>

        <label for="payment_proof" class="payment-proof-label">
            Upload Payment Screenshot
        </label>

        <input
            type="file"
            name="payment_proof"
            id="payment_proof"
            accept="image/jpeg,image/png,image/webp"
        >

        <small>Accepted: JPG, PNG, or WEBP. Maximum size: 5MB.</small>
    </div>
</div>

</div>

<!-- GCash QR CODE -->
<div id="gcash-payment" class="gcash-payment">

    <div class="gcash-payment-content">
        <h3>Pay with QrPH</h3>

        <p>Scan the QR code below using your QR scanner.</p>

        <img 
            src="images/qrph.png" 
            alt="QRPH"
            class="gcash-qr"
        >

        <p class="gcash-note">
            After payment, please keep your payment confirmation.
        </p>
    </div>
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
<!-- ================= ORDER SUCCESS MODAL ================= -->

<?php if ($order_success): ?>

<div class="order-success-overlay">
    <div class="order-success-modal">

        <div class="success-icon">
            ✓
        </div>

        <h2>Order Placed Successfully!</h2>

        <p>
            Thank you for your order.
            Your order has been successfully placed.
        </p>

        <div class="success-order-number">
            Order #<?= e($order_id) ?>
        </div>

        <div class="success-modal-actions">

            <a href="shop.php" class="success-shop-btn">
                Go to Shop
            </a>

            <a href="dashboard.php" class="success-orders-btn">
                View Orders
            </a>

        </div>

    </div>
</div>

<?php endif; ?>
<script>
const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
const paymentProofSection = document.getElementById('payment-proof-section');
const paymentProofInput = document.getElementById('payment_proof');

function updatePaymentProof() {
    const selected = document.querySelector(
        'input[name="payment_method"]:checked'
    );

    const isGCash = selected && selected.value === 'GCash';

    if (paymentProofSection) {
        paymentProofSection.style.display = isGCash ? 'block' : 'none';
    }

    if (paymentProofInput) {
        paymentProofInput.required = isGCash;

        if (!isGCash) {
            paymentProofInput.value = '';
        }
    }
}

paymentMethods.forEach(function (radio) {
    radio.addEventListener('change', updatePaymentProof);
});

updatePaymentProof();
</script>
<script>
document.querySelectorAll('input[name="address_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const fields = document.getElementById('new-address-fields');

        if (fields) {
            fields.style.display = this.value === 'new' ? 'grid' : 'none';
        }
    });
});
</script>
<script>
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="/javascript.js"></script>
</body>
</html>