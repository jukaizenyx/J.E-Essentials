<?php
function checkEmailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function registerUser($conn, $username, $first_name, $middle_name, $last_name, $email, $address, $password, $phone) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (`username`, `first_name`, `middle_name`, `last_name`, `email`, `address`, `password`, `phone`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $username, $first_name, $middle_name, $last_name, $email, $address, $hashed_password, $phone);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
function findUserByLoginId($conn, $login_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $login_id, $login_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // returns array, or null if no row found
    $stmt->close();
 
    return $user;
}
?>