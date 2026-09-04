<?php

function validateRegistration($data) {
    $errors = [];

    // ----- Username -----
    if (empty($data['username'])) {
        $errors['username'] = "Username is required.";
    } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 20) {
        $errors['username'] = "Username must be between 3 and 20 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
        $errors['username'] = "Username can only contain letters, numbers, and underscores.";
    }

    // ----- First Name -----
    if (empty($data['first_name'])) {
        $errors['first_name'] = "First name is required.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $data['first_name'])) {
        $errors['first_name'] = "First name can only contain letters and spaces.";
    } elseif (strlen($data['first_name']) > 50) {
        $errors['first_name'] = "First name is too long.";
    }

    // ----- Middle Name -----
    if (!empty($data['middle_name'])) {
        if (!preg_match('/^[a-zA-Z ]+$/', $data['middle_name'])) {
            $errors['middle_name'] = "Middle name can only contain letters and spaces.";
        } elseif (strlen($data['middle_name']) > 50) {
            $errors['middle_name'] = "Middle name is too long.";
        }
    }

    // ----- Last Name -----
    if (empty($data['last_name'])) {
        $errors['last_name'] = "Last name is required.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $data['last_name'])) {
        $errors['last_name'] = "Last name can only contain letters and spaces.";
    } elseif (strlen($data['last_name']) > 50) {
        $errors['last_name'] = "Last name is too long.";
    }

    // ----- Email -----
    if (empty($data['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    // ----- Address -----
    if (empty($data['address'])) {
        $errors['address'] = "Address is required.";
    } elseif (strlen($data['address']) > 255) {
        $errors['address'] = "Address is too long.";
    }

    // ----- Phone -----
    if (empty($data['phone'])) {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
        $errors['phone'] = "Phone number must be 10–15 digits, numbers only.";
    }

    // ----- Password + Confirm Password -----
    if (empty($data['password']) || empty($data['confirm_password'])) {
        $errors['password'] = "Password and confirm password are required.";
    } else {
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = "Passwords do not match.";
        }

        $passwordError = validatePasswordStrength($data['password']);
        if ($passwordError) {
            $errors['password'] = $passwordError;
        }
    }

    return $errors; // e.g. ['email' => 'Please enter a valid email address.', 'phone' => '...']
}

function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must contain at least one special character.";
    }
    return null; // no error
}