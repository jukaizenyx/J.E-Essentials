<?php
/**
 * Session-backed shopping cart.
 *
 * Cart shape in $_SESSION['cart']:
 *   [
 *     "1_50ml" => ['product_id' => 1, 'size' => '50ml', 'qty' => 2],
 *     ...
 *   ]
 *
 * We only ever store product_id / size / qty. Name, image and price are
 * always re-fetched from products.php when we build a response — that
 * way nothing the browser sends is ever trusted as a price.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function je_get_products(): array
{
    static $products = null;
    if ($products === null) {
        $products = require __DIR__ . '/products.php';
    }
    return $products;
}

function je_cart_key(int $productId, string $size): string
{
    return $productId . '_' . $size;
}

function je_cart_add(int $productId, string $size, int $qty): array
{
    $products = je_get_products();

    if (!isset($products[$productId])) {
        return ['ok' => false, 'error' => 'Product not found.'];
    }
    if (!isset($products[$productId]['sizes'][$size])) {
        return ['ok' => false, 'error' => 'Invalid size selected.'];
    }
    $qty = max(1, min(20, $qty));

    $key = je_cart_key($productId, $size);
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = min(20, $_SESSION['cart'][$key]['qty'] + $qty);
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'size'       => $size,
            'qty'        => $qty,
        ];
    }

    return ['ok' => true];
}

function je_cart_update(int $productId, string $size, int $qty): array
{
    $key = je_cart_key($productId, $size);
    if (!isset($_SESSION['cart'][$key])) {
        return ['ok' => false, 'error' => 'Item not in cart.'];
    }
    if ($qty <= 0) {
        unset($_SESSION['cart'][$key]);
    } else {
        $_SESSION['cart'][$key]['qty'] = max(1, min(20, $qty));
    }
    return ['ok' => true];
}

function je_cart_remove(int $productId, string $size): array
{
    unset($_SESSION['cart'][je_cart_key($productId, $size)]);
    return ['ok' => true];
}

/**
 * Builds the full cart payload (with live product info) that gets sent
 * back to the browser as JSON after every cart action.
 */
function je_cart_snapshot(): array
{
    $products = je_get_products();
    $items = [];
    $subtotal = 0;
    $count = 0;

    foreach ($_SESSION['cart'] as $key => $entry) {
        $product = $products[$entry['product_id']] ?? null;
        if (!$product || !isset($product['sizes'][$entry['size']])) {
            // Product/size no longer exists — drop it silently.
            unset($_SESSION['cart'][$key]);
            continue;
        }
        $price = $product['sizes'][$entry['size']];
        $lineTotal = $price * $entry['qty'];
        $subtotal += $lineTotal;
        $count += $entry['qty'];

        $items[] = [
            'key'        => $key,
            'product_id' => $product['id'],
            'name'       => $product['name'],
            'image'      => $product['image'],
            'size'       => $entry['size'],
            'price'      => $price,
            'qty'        => $entry['qty'],
            'line_total' => $lineTotal,
        ];
    }

    return [
        'ok'       => true,
        'items'    => $items,
        'count'    => $count,
        'subtotal' => $subtotal,
    ];
}

function je_is_logged_in(): bool
{
    // Matches process_login.php: $_SESSION['logged_in'] = true;
    return !empty($_SESSION['logged_in']);
}

function je_current_user(): ?array
{
    if (!je_is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
    ];
}