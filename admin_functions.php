<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './database/config.php';


/*
|--------------------------------------------------------------------------
| CHECK IF CURRENT USER IS ADMIN
|--------------------------------------------------------------------------
*/

function je_is_admin(): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    global $conn;

    $user_id = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT is_admin
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    return $user && (int) $user['is_admin'] === 1;
}


/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN ACCESS
|--------------------------------------------------------------------------
*/

function je_require_admin(): void
{
    if (!je_is_admin()) {
        header('Location: shop.php');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| GET ALL ORDERS
|--------------------------------------------------------------------------
*/

function je_get_all_orders(): array
{
    global $conn;

    $orders = [];

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            full_name,
            email,
            phone,
            address,
            payment_method,
            status,
            total,
            created_at,
            updated_at
        FROM orders
        ORDER BY created_at DESC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->execute();

    $result = $stmt->get_result();

    while ($order = $result->fetch_assoc()) {

        $order['items'] = [];

        $item_stmt = $conn->prepare("
            SELECT
                id,
                product_id,
                name,
                size,
                price,
                quantity
            FROM order_items
            WHERE order_id = ?
            ORDER BY id ASC
        ");

        if ($item_stmt) {

            $item_stmt->bind_param(
                "i",
                $order['id']
            );

            $item_stmt->execute();

            $item_result = $item_stmt->get_result();

            while ($item = $item_result->fetch_assoc()) {
                $order['items'][] = $item;
            }

            $item_stmt->close();
        }

        $orders[] = $order;
    }

    $stmt->close();

    return $orders;
}


/*
|--------------------------------------------------------------------------
| GET ONE ORDER
|--------------------------------------------------------------------------
*/

function je_get_order(int $order_id): ?array
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            full_name,
            email,
            phone,
            address,
            payment_method,
            status,
            total,
            created_at,
            updated_at
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param(
        "i",
        $order_id
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    $stmt->close();

    if (!$order) {
        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GET ORDER ITEMS
    |--------------------------------------------------------------------------
    */

    $order['items'] = [];

    $item_stmt = $conn->prepare("
        SELECT
            id,
            product_id,
            name,
            size,
            price,
            quantity
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ");

    if ($item_stmt) {

        $item_stmt->bind_param(
            "i",
            $order_id
        );

        $item_stmt->execute();

        $item_result = $item_stmt->get_result();

        while ($item = $item_result->fetch_assoc()) {
            $order['items'][] = $item;
        }

        $item_stmt->close();
    }

    return $order;
}


/*
|--------------------------------------------------------------------------
| UPDATE ORDER STATUS
|--------------------------------------------------------------------------
*/

function je_update_order_status(
    int $order_id,
    string $status
): bool {
    global $conn;

    $allowed_statuses = [
        'Pending',
        'Approved',
        'In Transit',
        'Out for Delivery',
        'Completed',
        'Rejected'
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE orders
        SET
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $status, $order_id);
    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

/*
|--------------------------------------------------------------------------
| ORDER STATISTICS
|--------------------------------------------------------------------------
*/

function je_get_order_stats(): array
{
    global $conn;

    $stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'in transit' => 0,
    'out for delivery' => 0,
    'completed' => 0,
    'rejected' => 0
];

    $result = $conn->query("
        SELECT
            status,
            COUNT(*) AS total
        FROM orders
        GROUP BY status
    ");

    if (!$result) {
        return $stats;
    }

    while ($row = $result->fetch_assoc()) {

        $status = strtolower($row['status']);
        $count = (int) $row['total'];

        $stats['total'] += $count;

        if (isset($stats[$status])) {
            $stats[$status] = $count;
        }
    }

    return $stats;
}