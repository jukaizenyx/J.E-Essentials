<?php
require_once 'admin_functions.php';

je_require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        $message_status = trim($_POST['message_status'] ?? '');

        if ($message_id > 0 && je_update_contact_status($message_id, $message_status)) {
            header('Location: admin.php?message_updated=1');
            exit;
        }

        header('Location: admin.php?message_updated=0');
        exit;
    }

    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($order_id > 0 && je_update_order_status($order_id, $status)) {
        header('Location: admin.php?updated=1');
        exit;
    }

    header('Location: admin.php?updated=0');
    exit;
}
$orders = je_get_all_orders();
$stats = je_get_order_stats();
$contact_messages = je_get_contact_messages();
$unread_contact_count = je_get_unread_contact_count();

function admin_e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — J.E Essentials</title>
    <link rel="stylesheet" href="./admin_css/admin.css">
    <link rel="stylesheet" href="./shop_css/shop.css">
    <link rel="stylesheet" href="./style_css/style.css">
</head>
<body>
    <?php require 'nav-admin.php'; ?>
<main class="admin-container">
    <div class="admin-heading">
        <h2>Admin Dashboard</h2>
        <p>Manage customer orders and update their status.</p>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <?php if ($_GET['updated'] === '1'): ?>
            <div class="update-message">Order status updated successfully.</div>
        <?php else: ?>
            <div class="update-message error">Unable to update the order status.</div>
        <?php endif; ?>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Orders</span>
            <span class="stat-number"><?= (int)$stats['total'] ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending</span>
            <span class="stat-number"><?= (int)$stats['pending'] ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Approved</span>
            <span class="stat-number"><?= (int)$stats['approved'] ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Completed</span>
            <span class="stat-number"><?= (int)$stats['completed'] ?></span>
        </div>
    </section>

    <section class="orders-section">
        <div class="orders-section-header">
            <h3>Orders</h3>
            <span class="orders-count">
                <?= count($orders) ?>
                <?= count($orders) === 1 ? 'order' : 'orders' ?>
            </span>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>No orders have been placed yet.</p>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <div class="order-row order-row-header">
                    <span>Order</span>
                    <span>Customer</span>
                    <span>Total</span>
                    <span>Date</span>
                    <span>Status</span>
                    <span>Action</span>
                </div>

                <?php foreach ($orders as $order): ?>
                    <?php
                    $status = (string)$order['status'];
                    $status_class = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $status));
                    $order_date = date('M j, Y', strtotime($order['created_at']));
                    ?>
                    <div class="order-row">
                        <div class="order-number">#<?= (int)$order['id'] ?></div>
                        <div>
                            <div class="customer-name"><?= admin_e($order['full_name']) ?></div>
                            <div class="customer-email"><?= admin_e($order['email']) ?></div>
                        </div>
                        <div class="order-total">₱<?= number_format((float)$order['total'], 2) ?></div>
                        <div class="order-date"><?= admin_e($order_date) ?></div>
                        <div>
                            <span class="status-badge status-<?= admin_e($status_class) ?>">
                                <?= admin_e($status) ?>
                            </span>
                        </div>
                        <div>
                            <button type="button" class="view-order-btn" data-order-id="<?= (int)$order['id'] ?>">View</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

        <section class="inquiries-section">
    <div class="inquiries-section-header">
        <div>
            <h3>Customer Inquiries</h3>
            <span class="inquiries-count">
                <?= count($contact_messages) ?>
                <?= count($contact_messages) === 1 ? 'inquiry' : 'inquiries' ?>
            </span>
        </div>

        <?php if ($unread_contact_count > 0): ?>
            <span class="unread-count">
                <?= $unread_contact_count ?> unread
            </span>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['message_updated'])): ?>
        <?php if ($_GET['message_updated'] === '1'): ?>
            <div class="update-message">
                Inquiry status updated successfully.
            </div>
        <?php else: ?>
            <div class="update-message error">
                Unable to update the inquiry.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($contact_messages)): ?>
        <div class="empty-inquiries">
            <p>No customer inquiries yet.</p>
        </div>
    <?php else: ?>
        <div class="inquiries-list">
            <?php foreach ($contact_messages as $message): ?>
                <div class="inquiry-card <?= $message['status'] === 'Unread' ? 'is-unread' : '' ?>">
                    <div class="inquiry-header">
                        <div>
                            <h4><?= admin_e($message['subject']) ?></h4>
                            <p class="inquiry-sender">
                                <?= admin_e($message['name']) ?>
                                ·
                                <?= admin_e($message['email']) ?>
                            </p>
                        </div>

                        <span class="inquiry-status inquiry-status-<?= strtolower($message['status']) ?>">
                            <?= admin_e($message['status']) ?>
                        </span>
                    </div>

                    <div class="inquiry-message">
                        <?= nl2br(admin_e($message['message'])) ?>
                    </div>

                    <div class="inquiry-footer">
                        <span class="inquiry-date">
                            <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                        </span>

                        <?php if ($message['status'] === 'Unread'): ?>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                                <input type="hidden" name="message_status" value="Read">
                                <button type="submit" class="mark-read-btn">
                                    Mark as Read
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                                <input type="hidden" name="message_status" value="Unread">
                                <button type="submit" class="mark-unread-btn">
                                    Mark as Unread
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>

<div class="order-modal-overlay" id="orderModalOverlay">
    <div class="order-modal" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <div class="modal-header">
            <h2 id="orderModalTitle">Order</h2>
            <button type="button" class="modal-close" id="orderModalClose" aria-label="Close">&times;</button>
        </div>

        <div class="detail-section">
            <h3 class="detail-section-title">Customer Information</h3>
            <div class="customer-details">
                <div class="detail-box">
                    <span class="detail-label">Name</span>
                    <span class="detail-value" id="modalCustomerName"></span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value" id="modalCustomerPhone"></span>
                </div>
                <div class="detail-box full">
                    <span class="detail-label">Email</span>
                    <span class="detail-value" id="modalCustomerEmail"></span>
                </div>
                <div class="detail-box full">
                    <span class="detail-label">Address</span>
                    <span class="detail-value" id="modalCustomerAddress"></span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3 class="detail-section-title">Products</h3>
            <div class="order-items" id="modalOrderItems"></div>
            <div class="order-total-row">
                <span>Total</span>
                <strong id="modalOrderTotal">₱0.00</strong>
            </div>
        </div>

                <div class="detail-section">
            <h3 class="detail-section-title">Payment</h3>
            <div class="detail-box">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value" id="modalPaymentMethod"></span>
            </div>
            <div class="payment-proof-box" id="paymentProofBox">
                <span class="detail-label">Proof of Payment</span>
                <div class="payment-proof-preview">
                    <img id="modalPaymentProof" src="" alt="Payment Proof">
                </div>
                <a id="paymentProofLink" href="#" target="_blank" class="payment-proof-view">View Full Image</a>
            </div>
            <div class="payment-proof-none" id="paymentProofNone">
                No payment screenshot uploaded.
            </div>
        </div>

        <div class="status-update">
            <form method="POST" action="admin.php">
                <input type="hidden" name="order_id" id="modalOrderId">
                <label for="modalStatus">Update Order Status</label>
             <select name="status" class="status-select" id="modalStatus">
            <option value="Pending">Pending</option>
    <option value="Approved">Approved</option>
    <option value="In Transit">In Transit</option>
    <option value="Out for Delivery">Out for Delivery</option>
    <option value="Completed">Completed</option>
    <option value="Rejected">Rejected</option>
</select>
                <button type="submit" class="update-status-btn">Update Status</button>
            </form>
        </div>
    </div>
</div>
<script>
const adminOrders = <?= json_encode($orders, JSON_UNESCAPED_SLASHES) ?>;
const orderModalOverlay = document.getElementById('orderModalOverlay');
const orderModalClose = document.getElementById('orderModalClose');
const modalOrderTitle = document.getElementById('orderModalTitle');
const modalCustomerName = document.getElementById('modalCustomerName');
const modalCustomerPhone = document.getElementById('modalCustomerPhone');
const modalCustomerEmail = document.getElementById('modalCustomerEmail');
const modalCustomerAddress = document.getElementById('modalCustomerAddress');
const modalOrderItems = document.getElementById('modalOrderItems');
const modalOrderTotal = document.getElementById('modalOrderTotal');
const modalPaymentMethod = document.getElementById('modalPaymentMethod');
const modalPaymentProof = document.getElementById('modalPaymentProof');
const paymentProofBox = document.getElementById('paymentProofBox');
const paymentProofLink = document.getElementById('paymentProofLink');
const paymentProofNone = document.getElementById('paymentProofNone');
const modalOrderId = document.getElementById('modalOrderId');
const modalStatus = document.getElementById('modalStatus');

function openOrderModal(orderId) {
    const order = adminOrders.find(item => Number(item.id) === Number(orderId));
    if (!order) return;

    modalOrderTitle.textContent = `Order #${order.id}`;
    modalCustomerName.textContent = order.full_name || '—';
    modalCustomerPhone.textContent = order.phone || '—';
    modalCustomerEmail.textContent = order.email || '—';
    modalCustomerAddress.textContent = order.address || '—';
    modalPaymentMethod.textContent = order.payment_method || '—';

        if (order.payment_proof) {
            const proofPath = `uploads/payment_proofs/${order.payment_proof}`;

            modalPaymentProof.src = proofPath;
            paymentProofLink.href = proofPath;

            paymentProofBox.style.display = 'block';
            paymentProofNone.style.display = 'none';
        } else {
            modalPaymentProof.src = '';
            paymentProofLink.href = '#';

            paymentProofBox.style.display = 'none';
            paymentProofNone.style.display = 'block';
        }

modalOrderId.value = order.id;
    modalStatus.value = order.status;
    modalOrderItems.innerHTML = '';

    if (!order.items || order.items.length === 0) {
        modalOrderItems.innerHTML = '<div class="no-items">No products found.</div>';
    } else {
        order.items.forEach(item => {
            const itemRow = document.createElement('div');
            itemRow.className = 'order-item';

            const itemInfo = document.createElement('div');
            itemInfo.className = 'order-item-info';

            const itemName = document.createElement('div');
            itemName.className = 'order-item-name';
            itemName.textContent = item.name;

            const itemSize = document.createElement('div');
            itemSize.className = 'order-item-size';
            itemSize.textContent = item.size ? `Size: ${item.size}` : '';

            itemInfo.appendChild(itemName);
            itemInfo.appendChild(itemSize);

            const itemQuantity = document.createElement('div');
            itemQuantity.className = 'order-item-qty';
            itemQuantity.textContent = `×${item.quantity}`;

            const itemPrice = document.createElement('div');
            itemPrice.className = 'order-item-price';
            itemPrice.textContent = `₱${Number(item.price * item.quantity).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            })}`;

            itemRow.appendChild(itemInfo);
            itemRow.appendChild(itemQuantity);
            itemRow.appendChild(itemPrice);
            modalOrderItems.appendChild(itemRow);
        });
    }

    modalOrderTotal.textContent = `₱${Number(order.total).toLocaleString('en-PH', {
        minimumFractionDigits: 2
    })}`;

    orderModalOverlay.classList.add('is-open');
    document.body.classList.add('modal-open');
}

function closeOrderModal() {
    orderModalOverlay.classList.remove('is-open');
    document.body.classList.remove('modal-open');
}

document.querySelectorAll('.view-order-btn').forEach(button => {
    button.addEventListener('click', () => {
        openOrderModal(button.dataset.orderId);
    });
});

orderModalClose.addEventListener('click', closeOrderModal);

orderModalOverlay.addEventListener('click', event => {
    if (event.target === orderModalOverlay) {
        closeOrderModal();
    }
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && orderModalOverlay.classList.contains('is-open')) {
        closeOrderModal();
    }
});

</script>
<script src="javascript.js"></script>
</body>
</html>