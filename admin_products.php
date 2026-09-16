<?php
require_once 'database/config.php';
require_once 'admin_functions.php';

je_require_admin();

$message = '';
$error = '';
$edit_product = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $sizes = $_POST['size'] ?? [];
        $prices = $_POST['size_price'] ?? [];

        $size_data = [];

        foreach ($sizes as $index => $size) {
            $size = trim($size);
            $price = trim($prices[$index] ?? '');

            if ($size !== '' && $price !== '' && is_numeric($price)) {
                $size_data[$size] = (float)$price;
            }
        }

        if ($name === '' || $description === '' || $image === '') {
            $error = 'Please fill in all product fields.';
        } elseif (empty($size_data)) {
            $error = 'Please add at least one size and price.';
        } else {
            $first_price = reset($size_data);
            $sizes_json = json_encode($size_data);

            if ($action === 'add') {
                $stmt = $conn->prepare("
                    INSERT INTO products (name, description, price, sizes, image)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssdss",
                    $name,
                    $description,
                    $first_price,
                    $sizes_json,
                    $image
                );

                if ($stmt->execute()) {
                    $message = 'Product added successfully.';
                } else {
                    $error = 'Failed to add product: ' . $stmt->error;
                }

                $stmt->close();
            }

            if ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);

                $stmt = $conn->prepare("
                    UPDATE products
                    SET name = ?, description = ?, price = ?, sizes = ?, image = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "ssdssi",
                    $name,
                    $description,
                    $first_price,
                    $sizes_json,
                    $image,
                    $id
                );

                if ($stmt->execute()) {
                    $message = 'Product updated successfully.';
                } else {
                    $error = 'Failed to update product: ' . $stmt->error;
                }

                $stmt->close();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $message = 'Product deleted successfully.';
            } else {
                $error = 'Failed to delete product.';
            }

            $stmt->close();
        }
    }
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();

    $stmt->close();

    if ($edit_product) {
        $edit_product['sizes'] = json_decode($edit_product['sizes'], true) ?? [];
    }
}

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

$products = [];

while ($product = mysqli_fetch_assoc($result)) {
    $product['sizes'] = json_decode($product['sizes'], true) ?? [];
    $products[] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="admin_css/admin.css">
    <link rel="stylesheet" href="shop_css/shop.css">
    <style>
        .products-page {
            padding: 40px;
        }

        .products-header {
            margin-bottom: 25px;
        }

        .products-header h1 {
            margin: 0;
        }

        .product-form {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .product-form h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .sizes-section {
            margin-bottom: 20px;
        }

        .sizes-section h3 {
            margin-bottom: 10px;
        }

        .size-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .size-row input {
            flex: 1;
            padding: 11px 13px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .remove-size {
            border: none;
            background: #f5dede;
            color: #a33;
            padding: 0 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .add-size {
            border: none;
            background: #eee;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
        }

        .product-submit {
            padding: 11px 20px;
            border: none;
            border-radius: 8px;
            background: #222;
            color: #fff;
            cursor: pointer;
        }

        .cancel-edit {
            margin-left: 10px;
            text-decoration: none;
            color: #555;
        }

        .products-table-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 12px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .products-table th {
            background: #f7f2e8;
        }

        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }

        .sizes-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn,
        .delete-btn {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }

        .edit-btn {
            background: #eee;
            color: #222;
        }

        .delete-btn {
            background: #f5dede;
            color: #a33;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background: #fdeaea;
            color: #b3261e;
        }
    </style>
</head>
<body>

<?php require 'nav-admin.php'; ?>

<main class="products-page">

    <div class="products-header">
        <h1>Products</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="product-form">
        <h2><?= $edit_product ? 'Edit Product' : 'Add Product' ?></h2>

        <form method="post">
            <input
                type="hidden"
                name="action"
                value="<?= $edit_product ? 'edit' : 'add' ?>"
            >

            <?php if ($edit_product): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$edit_product['id'] ?>"
                >
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Product Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    name="description"
                    id="description"
                    required
                ><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Image Path</label>
                <input
                    type="text"
                    name="image"
                    id="image"
                    placeholder="images/product.jpg"
                    value="<?= htmlspecialchars($edit_product['image'] ?? '') ?>"
                    required
                >
            </div>

            <div class="sizes-section">
                <h3>Sizes & Prices</h3>

                <div id="sizeContainer">
                    <?php if ($edit_product && !empty($edit_product['sizes'])): ?>

                        <?php foreach ($edit_product['sizes'] as $size => $price): ?>
                            <div class="size-row">
                                <input
                                    type="text"
                                    name="size[]"
                                    value="<?= htmlspecialchars($size) ?>"
                                    placeholder="Size"
                                    required
                                >

                                <input
                                    type="number"
                                    name="size_price[]"
                                    value="<?= htmlspecialchars($price) ?>"
                                    placeholder="Price"
                                    step="0.01"
                                    min="0"
                                    required
                                >

                                <button
                                    type="button"
                                    class="remove-size"
                                    onclick="removeSize(this)"
                                >
                                    Remove
                                </button>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="size-row">
                            <input
                                type="text"
                                name="size[]"
                                placeholder="Size"
                                required
                            >

                            <input
                                type="number"
                                name="size_price[]"
                                placeholder="Price"
                                step="0.01"
                                min="0"
                                required
                            >

                            <button
                                type="button"
                                class="remove-size"
                                onclick="removeSize(this)"
                            >
                                Remove
                            </button>
                        </div>

                    <?php endif; ?>
                </div>

                <button type="button" class="add-size" onclick="addSize()">
                    + Add Size
                </button>
            </div>

            <button type="submit" class="product-submit">
                <?= $edit_product ? 'Update Product' : 'Add Product' ?>
            </button>

            <?php if ($edit_product): ?>
                <a href="admin_products.php" class="cancel-edit">
                    Cancel
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="products-table-wrapper">
        <table class="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Sizes & Prices</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($products)): ?>

                    <tr>
                        <td colspan="5">No products yet.</td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($products as $product): ?>

                        <tr>
                            <td><?= (int)$product['id'] ?></td>

                            <td>
                                <img
                                    src="<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="product-image"
                                >
                            </td>

                            <td>
                                <?= htmlspecialchars($product['name']) ?>
                            </td>

                            <td>
                                <div class="sizes-list">
                                    <?php foreach ($product['sizes'] as $size => $price): ?>
                                        <span>
                                            <?= htmlspecialchars($size) ?>
                                            — ₱<?= number_format((float)$price, 2) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        href="admin_products.php?edit=<?= (int)$product['id'] ?>"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        onsubmit="return confirm('Delete this product?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$product['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-btn"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
function addSize() {
    const container = document.getElementById('sizeContainer');

    const row = document.createElement('div');
    row.className = 'size-row';

    row.innerHTML = `
        <input type="text" name="size[]" placeholder="Size" required>
        <input type="number" name="size_price[]" placeholder="Price" step="0.01" min="0" required>
        <button type="button" class="remove-size" onclick="removeSize(this)">Remove</button>
    `;

    container.appendChild(row);
}

function removeSize(button) {
    const container = document.getElementById('sizeContainer');
    const rows = container.querySelectorAll('.size-row');

    if (rows.length > 1) {
        button.parentElement.remove();
    }
}
</script>

</body>
</html>