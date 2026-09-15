<?php
require_once 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($full_name === '' || $email === '' || $subject === '' || $message === '') {
    header('Location: index.php#contact&error=empty');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php#contact&error=email');
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO contact_messages (name, email, subject, message)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("ssss", $full_name, $email, $subject, $message);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: index.php#contact&success=1');
    exit;
}

$stmt->close();
die("Database Error: " . $conn->error);