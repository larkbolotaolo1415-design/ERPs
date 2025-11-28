<?php
require '../../core/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'invalid_request';
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo 'missing_fields';
    exit;
}

// Check if user exists
$stmt = $connection->prepare("SELECT * FROM employees WHERE LOWER(email) = LOWER(?)");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo 'email_not_found';
    exit;
}

// Update password
$update = $connection->prepare("UPDATE employees SET password = ? WHERE LOWER(email) = LOWER(?)");
$update->bind_param("ss", $password, $email);

if ($update->execute()) {
    echo 'success';
} else {
    echo 'error';
}

$update->close();
$stmt->close();
$connection->close();
?>
