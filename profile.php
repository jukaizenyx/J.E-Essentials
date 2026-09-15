<?php
require_once 'cart_functions.php';
require_once './database/config.php';

if (!je_is_logged_in()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user = je_current_user();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    die("User account not found.");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($first_name === '' || $last_name === '' || $email === '' || $address === '' || $phone === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $middle_name = $middle_name !== '' ? $middle_name : null;

        $update = $conn->prepare("UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ?, phone = ? WHERE id = ?");
        $update->bind_param("ssssssi", $first_name, $middle_name, $last_name, $email, $address, $phone, $user['id']);

        if ($update->execute()) {
            $message = 'Your personal information has been updated successfully.';

            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Something went wrong while updating your information.';
        }

        $update->close();
    }
}

$products = je_get_products();

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
    <link rel="stylesheet" href="./profile_css/profile.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="profile-page">
    <a href="shop.php" class="profile-back">&larr; Go back to shop</a>

    <section class="profile-header">
        <div>
            <p class="profile-eyebrow">MY ACCOUNT</p>
            <h1>Profile</h1>
            <p class="profile-username">@<?= e($profile['username']) ?></p>
        </div>
    </section>

    <?php if ($message): ?>
        <div class="profile-message"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="profile-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="profile-dashboard">
        <aside class="profile-sidebar">
            <p class="profile-sidebar-title">My Account</p>
            <a href="profile.php" class="profile-menu-link active">Personal Information</a>
            <a href="dashboard.php" class="profile-menu-link">My Orders</a>
            <a href="logout.php" class="profile-menu-link profile-logout">Logout</a>
        </aside>

        <section class="profile-content">
            <div class="profile-card">
                <p class="profile-section-label">PERSONAL INFORMATION</p>
                <h2>Your Details</h2>
                <p class="profile-description">Update your personal information below.</p>

                <form method="POST" action="profile.php">
                    <div class="profile-form-grid">
                        <div class="profile-form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= e($profile['first_name']) ?>" required>
                        </div>

                        <div class="profile-form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?= e($profile['middle_name']) ?>">
                        </div>

                        <div class="profile-form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= e($profile['last_name']) ?>" required>
                        </div>

                        <div class="profile-form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= e($profile['email']) ?>" required>
                        </div>

                        <div class="profile-form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?= e($profile['phone']) ?>" required>
                        </div>

                        <div class="profile-form-group profile-full-width">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" required><?= e($profile['address']) ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="profile-save-btn">Save Changes</button>
                </form>
            </div>

            <div class="profile-card">
                <p class="profile-section-label">ACCOUNT INFORMATION</p>
                <h2>Account Details</h2>

                <div class="account-details">

                    <div class="account-detail">
                        <span>Username</span>
                        <strong><?= e($profile['username']) ?></strong>
                    </div>

                    <div class="account-detail">
                        <span>Account Created</span>
                        <strong><?= e($profile['created_at']) ?></strong>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
   window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="javascript.js"></script>
</body>
</html>