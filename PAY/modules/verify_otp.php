<?php
require_once INCLUDES_PATH . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT u.user_id, pr.* 
                            FROM user_table u 
                            JOIN password_reset pr ON u.user_id = pr.user_id 
                            WHERE (u.user_name = ? OR u.user_email = ?) AND pr.token = ? 
                            AND pr.status = 'pending' 
                            ORDER BY pr.created_at DESC LIMIT 1");
    $stmt->bind_param("sss", $email, $email, $otp);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (strtotime($row['expires_at']) > time()) {
            echo json_encode(['status' => 'success', 'user_id' => $row['user_id']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'OTP expired.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
    }

    $conn->close();
}
