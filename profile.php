<?php
require_once 'cart_functions.php';
if (!je_is_logged_in()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}
$user = je_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="simple-page">
    <h1>Profile</h1>
    <p class="simple-page-sub">Username: <?= htmlspecialchars($user['username']) ?></p>
    <p class="simple-page-sub">User ID: <?= htmlspecialchars((string)$user['id']) ?></p>
    <p>This is a placeholder — plug in editable profile fields backed by your users table here.</p>
</main>
</body>
</html>