<?php
require_once 'cart_functions.php';
header('Content-Type: application/json');

if (!je_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order.']);
    exit;
}

$user = je_current_user();
$result = je_cancel_order($orderId, (int) $user['id']);

if (!$result['ok']) {
    http_response_code(409);
}

echo json_encode([
    'success' => $result['ok'],
    'message' => $result['ok'] ? null : $result['error'],
]);