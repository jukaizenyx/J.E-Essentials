<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './database/config.php';

function je_is_admin(): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    global $conn;
    $user_id = (int)$_SESSION['user_id'];

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

    return $user && (int)$user['is_admin'] === 1;
}

function je_require_admin(): void
{
    if (!je_is_admin()) {
        header('Location: shop.php');
        exit;
    }
}

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
            payment_proof,
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
            $item_stmt->bind_param("i", $order['id']);
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
            payment_proof,
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

    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        return null;
    }

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
        $item_stmt->bind_param("i", $order_id);
        $item_stmt->execute();

        $item_result = $item_stmt->get_result();

        while ($item = $item_result->fetch_assoc()) {
            $order['items'][] = $item;
        }

        $item_stmt->close();
    }

    return $order;
}

function je_update_order_status(int $order_id, string $status): bool
{
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

    $check_stmt = $conn->prepare("
        SELECT user_id, status
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    if (!$check_stmt) {
        return false;
    }

    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();

    $result = $check_stmt->get_result();
    $order = $result->fetch_assoc();
    $check_stmt->close();

    if (!$order) {
        return false;
    }

    $old_status = $order['status'];
    $user_id = (int)$order['user_id'];

    if ($old_status === $status) {
        return true;
    }

    $stmt = $conn->prepare("
        UPDATE orders
        SET status = ?, updated_at = NOW()
        WHERE id = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $status, $order_id);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $stmt->close();

    $message = "Your order #{$order_id} has been updated to {$status}.";

    $notification_stmt = $conn->prepare("
        INSERT INTO notifications
        (user_id, order_id, message)
        VALUES (?, ?, ?)
    ");

    if ($notification_stmt) {
        $notification_stmt->bind_param(
            "iis",
            $user_id,
            $order_id,
            $message
        );

        $notification_stmt->execute();
        $notification_stmt->close();
    }

    return true;
}

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
        'rejected' => 0,
        'cancelled' => 0
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
        $count = (int)$row['total'];

        $stats['total'] += $count;

        if (isset($stats[$status])) {
            $stats[$status] = $count;
        }
    }

    return $stats;
}
function je_get_contact_messages(): array
{
    global $conn;
    $messages = [];

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            email,
            subject,
            message,
            status,
            created_at
        FROM contact_messages
        ORDER BY created_at DESC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($message = $result->fetch_assoc()) {
        $messages[] = $message;
    }

    $stmt->close();

    return $messages;
}

function je_get_unread_contact_count(): int
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM contact_messages
        WHERE status = 'Unread'
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return (int)($row['total'] ?? 0);
}

function je_update_contact_status(int $message_id, string $status): bool
{
    global $conn;

    $allowed_statuses = ['Unread', 'Read'];

    if (!in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE contact_messages
        SET status = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $status, $message_id);

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}