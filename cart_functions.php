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

// Provides the mysqli connection as $conn. Adjust this require if your
// connection file lives elsewhere or uses a different variable name.
require_once './database/config.php';

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

    global $conn;

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        return null;
    }

    $stmt = $conn->prepare("
        SELECT 
            id,
            username,
            first_name,
            middle_name,
            last_name,
            email,
            phone,
            address,
            is_admin,
            created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    return $user ?: null;
}
/**
 * All orders for a user, newest first, each with its line items attached.
 * Returns [] if the user has no orders.
 *
 * Shape returned per order:
 *   ['id' => int, 'status' => string, 'total' => float, 'created_at' => string,
 *    'items' => [['name' => string, 'size' => ?string, 'price' => float, 'quantity' => int], ...]]
 */
function je_get_user_orders(int $userId): array
{
    global $conn;

    $orders = [];

    $stmt = $conn->prepare(
        'SELECT id, status, total, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['items'] = [];
        $orders[(int) $row['id']] = $row;
    }
    $stmt->close();

    if (empty($orders)) {
        return [];
    }

    $orderIds = array_keys($orders);
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $itemsStmt = $conn->prepare(
        "SELECT order_id, name, size, price, quantity FROM order_items WHERE order_id IN ($placeholders)"
    );
    $itemsStmt->bind_param($types, ...$orderIds);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    while ($item = $itemsResult->fetch_assoc()) {
        $orders[(int) $item['order_id']]['items'][] = [
            'name'     => $item['name'],
            'size'     => $item['size'],
            'price'    => (float) $item['price'],
            'quantity' => (int) $item['quantity'],
        ];
    }
    $itemsStmt->close();

    foreach ($orders as &$order) {
        $order['id'] = (int) $order['id'];
        $order['total'] = (float) $order['total'];
    }
    unset($order);

    return array_values($orders);
}

/**
 * Cancels an order, but only if it belongs to $userId and is still 'pending'.
 * Once an order is 'approved' (or already cancelled/completed) this refuses,
 * so a stale button click or a forged request can't cancel a locked-in order.
 */
function je_cancel_order(int $orderId, int $userId): array
{
    global $conn;

    $stmt = $conn->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        return ['ok' => false, 'error' => 'Order not found.'];
    }
    if ($order['status'] !== 'pending') {
        return ['ok' => false, 'error' => 'This order can no longer be cancelled.'];
    }

    $update = $conn->prepare(
        "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'"
    );
    $update->bind_param('ii', $orderId, $userId);
    $update->execute();
    $cancelled = $update->affected_rows > 0;
    $update->close();

    return $cancelled
        ? ['ok' => true]
        : ['ok' => false, 'error' => 'This order can no longer be cancelled.'];
}

function je_clear_cart() {
    $_SESSION['cart'] = [];
}