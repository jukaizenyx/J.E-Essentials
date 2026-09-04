<?php

session_start();
require 'database/config.php';
require 'function.php';

if (isset($_POST['login'])) {

    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];

    if (empty($login_id) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter both fields.';
        header("Location: login.php");
        exit;
    }

    $user = findUserByLoginId($conn, $login_id);

    if (!$user) {
        // No matching username/email at all
        $_SESSION['login_error'] = 'Incorrect username/email or password.';
        header("Location: login.php");
        exit;
    }

    // password_verify checks the typed password -> the hashed one in the database
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Incorrect username/email or password.';
        header("Location: login.php");
        exit;
    }

    // Success — save what you need in the session, then send them into the shop
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['logged_in'] = true;

    header("Location: shop.php"); // change 'shop.php' to whatever your main shop page is called
    exit;

} else {
    // Someone tried to load this file directly without submitting the form
    header("Location: login.php");
    exit;
}

?>