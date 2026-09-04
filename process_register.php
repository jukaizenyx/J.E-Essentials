<?php

session_start();
require 'database/config.php';
require 'function.php';
require 'validation.php';

if (isset($_POST['register'])) {

    $errors = validateRegistration($_POST);

    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;   // whole array now
        $_SESSION['old_input'] = $_POST;          // so the form can refill what they typed
    } elseif (checkEmailExists($conn, $_POST['email'])) {
        $_SESSION['register_errors'] = ['email' => 'Email is already registered!'];
        $_SESSION['old_input'] = $_POST;
    } else {
        $success = registerUser(
            $conn,
            $_POST['username'],
            $_POST['first_name'],
            $_POST['middle_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['address'],
            $_POST['confirm_password'],
            $_POST['phone']
        );

        if ($success) {
            $_SESSION['register_success'] = 'Account created successfully!';
        } else {
            $_SESSION['register_errors'] = ['general' => 'Something went wrong. Please try again.'];
        }
    }
}

header("Location: register.php");
exit;

?>