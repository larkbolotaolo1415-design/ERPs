<?php
require '../../core/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'invalid_request';
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo 'empty';
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $connection->prepare("SELECT * FROM employees WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo 'not_found';
    exit;
}

// Assuming passwords are stored in plain text (like your example).
// For production, you should hash passwords using password_hash() and verify using password_verify().
// For plain text passwords (testing only)
// Use password_hash() & password_verify() in production
if ($user['password'] !== $password) {
    echo 'wrong_password';
    exit;
}

// Return user info as JSON
echo json_encode([
    'id' => $user['id'],
    'firstname' => $user['firstname'],
    'lastname' => $user['lastname'],
    'email' => $user['email'],
    'user_role' => $user['user_role'],
    'emp_id' => $user['emp_id'],
    'otp_code' => $user['otp_code'],
    'otp_expiry' => $user['otp_expiry']
]);
?>
