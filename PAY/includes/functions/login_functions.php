<?php

include_once __DIR__ . '/../../init.php';

// LOGIN ATTEMPT
function insert_login_attempt($user_id, $status)
{

    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (user_id, attempt_time, login_status) VALUES (?, NOW(), ?)");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
}

// LOGOUT
function logout_session($ses_id)
{
    global $conn;
    // REMOVE SESSION ID IF EXISTENT
    // search for the session first
    $stmt = $conn->prepare("SELECT session_id FROM sessions WHERE session_id = ? LIMIT 1");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param("s", $ses_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    // INSERT ENDED SESSION
    if ($result->num_rows > 0) {
        $stmt2 = $conn->prepare("UPDATE sessions SET logout_time = NOW(), session_status = 'ended' WHERE session_id = ?");
        if (!$stmt2) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt2->bind_param("s", $ses_id);
        $stmt2->execute();
        $stmt2->close();
        return true;
    }
    return false;
}
