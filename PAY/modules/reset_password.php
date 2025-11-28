<?php
require_once INCLUDES_PATH . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_pass = $_POST['password'];

    $stmt = $conn->prepare("UPDATE user_table SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_pass, $user_id);
    $stmt->execute();

    $stmt2 = $conn->prepare("UPDATE password_reset SET status = 'used' WHERE user_id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    echo json_encode(['status' => 'success', 'message' => 'Password reset successfully.']);
}
