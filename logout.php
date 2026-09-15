<?php
/**
 * If you already have your own logout.php elsewhere, delete this one and
 * just make sure it clears the same three session keys process_login.php
 * sets: user_id, username, logged_in.
 */
require_once 'cart_functions.php';
je_delete_remember_token();
unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['logged_in']);
header('Location: shop.php');
exit;

?>