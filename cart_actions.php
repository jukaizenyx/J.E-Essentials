<?php
require_once 'cart_functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $size      = trim((string)($_POST['size'] ?? ''));
        $qty       = (int)($_POST['qty'] ?? 1);
        $result    = je_cart_add($productId, $size, $qty);
        if (!$result['ok']) {
            http_response_code(400);
            echo json_encode($result);
            exit;
        }
        break;

    case 'update':
        $productId = (int)($_POST['product_id'] ?? 0);
        $size      = trim((string)($_POST['size'] ?? ''));
        $qty       = (int)($_POST['qty'] ?? 1);
        $result    = je_cart_update($productId, $size, $qty);
        if (!$result['ok']) {
            http_response_code(400);
            echo json_encode($result);
            exit;
        }
        break;

    case 'remove':
        $productId = (int)($_POST['product_id'] ?? 0);
        $size      = trim((string)($_POST['size'] ?? ''));
        je_cart_remove($productId, $size);
        break;

    case 'get':
        // Just read the cart, no mutation.
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
        exit;
}

$snapshot = je_cart_snapshot();
$snapshot['logged_in'] = je_is_logged_in();
echo json_encode($snapshot);