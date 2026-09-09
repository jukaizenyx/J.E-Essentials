<?php
require_once 'cart_functions.php';
if (!je_is_logged_in()) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}
$user = je_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — J.E Essentials</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="simple-page">
    <h1>Dashboard</h1>
    <p class="simple-page-sub">Welcome back, <?= htmlspecialchars($user['username']) ?>.</p>
    <p>This is a placeholder — plug in order history, recommendations, etc. here.</p>
</main>
</body>
</html>