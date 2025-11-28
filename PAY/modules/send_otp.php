<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once INCLUDES_PATH . '/db_connect.php';
require_once MODULES_PATH . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    try {
        $stmt = $conn->prepare("SELECT * FROM user_table WHERE user_name = ? OR user_email = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) throw new Exception('Get result failed: ' . $stmt->error);
        $user = $result->fetch_assoc();

        if ($user) {
            $otp = rand(100000, 999999);
            $token = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt2 = $conn->prepare("INSERT INTO password_reset (user_id, token, created_at, expires_at, status) VALUES (?, ?, NOW(), ?, 'pending')");
            if (!$stmt2) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt2->bind_param("iss", $user['user_id'], $otp, $expires_at);
            $stmt2->execute();

            $subject = "Payroll System Password Reset OTP";
            $body = "<p>Your OTP code is <strong>$otp</strong>. It will expire in 15 minutes.</p>";

            $sent = sendMail($email, $subject, $body);

            if ($sent === true) {
                echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Mail error: ' . $sent]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
    $conn->close();
}
