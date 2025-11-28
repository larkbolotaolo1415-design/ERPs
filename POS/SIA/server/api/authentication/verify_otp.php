<?php
require __DIR__ . '/../../core/connection.php';  // Database connection

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');

if (!$email || !$otp) {
    echo "missing_fields";
    exit;
}

// 🔍 Retrieve OTP & expiry from employees table
$stmt = $connection->prepare(
    "SELECT otp_code, otp_expiry FROM employees WHERE LOWER(email) = LOWER(?)"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $storedOtp = $row['otp_code'];
    $expiry = strtotime($row['otp_expiry']);
    $now = time();

    // ✅ Validate OTP
    if ($storedOtp === $otp && $now <= $expiry) {
        echo "verified";
    } else {
        echo "invalid_otp";
    }
} else {
    echo "email_not_found";
}

$stmt->close();
$connection->close();
