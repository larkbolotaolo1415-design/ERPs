<?php

include_once __DIR__ . '/../init.php';
require_once MODULES_PATH . '/remember_me.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CREDENTIAL TRIMMING
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember_me = trim($_POST['remember_me']);

    login($email, $password, $remember_me);
}

function login($email, $password, $remember_me)
{
    global $conn;
    global $config;
    // DEFAULT EXPIRY LENGTH
    // TODO: gawin dynamic sa database

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {

        // GET RESULTING USERS BASED ON EMAIL
        $stmt = $conn->prepare("SELECT * FROM user_table WHERE user_email = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) throw new Exception('Get result failed: ' . $stmt->error);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Check lock policy
            $lockLimit = 0;
            $stp = $conn->prepare("SELECT value FROM settings_table WHERE setting_name='Lock Account After' LIMIT 1");
            if ($stp && $stp->execute()) { $rp = $stp->get_result(); if ($rp && $rp->num_rows>0) { $lockLimit = intval(($rp->fetch_assoc()['value']) ?? 0); } }
            if ($lockLimit > 0) {
                $stmtFail = $conn->prepare("SELECT COUNT(*) AS cnt FROM login_attempts WHERE user_id=? AND login_status='failed' AND attempt_time >= NOW() - INTERVAL 1 DAY");
                if ($stmtFail) { $stmtFail->bind_param('i', $row['user_id']); $stmtFail->execute(); $resFail = $stmtFail->get_result(); $failCnt = intval(($resFail->fetch_assoc()['cnt']) ?? 0); if ($failCnt >= $lockLimit) { echo json_encode(['status' => 'error', 'message' => 'Account locked due to failed attempts.']); exit; } }
            }

            if ($password === $row['password']) {

                // INACTIVITY SCANNING
                if ($row['status'] === 'inactive') {
                    echo json_encode(['status' => 'error', 'message' => 'Account is inactive.']);
                    exit;
                }

                // SUCCESS LOGIN ATTEMPT
                insert_login_attempt($row['user_id'], "success");

                // HANDLE REMEMBER ME
                $generated_r_session_id = remember_me($remember_me, $row['user_id'], $config['default_expiry_days']);
                if ($generated_r_session_id != "") {
                    setcookie("remember_me", $generated_r_session_id, time() + (86400 * $config['default_expiry_days']), "/");
                } else {
                    unset($_COOKIE["remember_me"]);
                }

                // SAVE DATA IN SESSIONS
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['role_id'] = $row['role_id'];

                // ASSIGN SESSION ID IF NON EXISTENT
                if (!isset($_COOKIE['session_id'])) {

                    // GENERATE SESSION ID
                    $generated_session_id = secureSessionID($config['login_session_secret_key'], $config['login_session_id_length']);

                    // SAVE SESSION ID LOCALLY IN COOKIE
                    setcookie("session_id", $generated_session_id, time() + (86400 * $config['default_expiry_days']), "/");
                    
                    // ALSO SAVE IN $_SESSION FOR AJAX REQUESTS
                    $_SESSION['session_id'] = $generated_session_id;

                    $stmt3 = $conn->prepare("INSERT INTO sessions (session_id, user_id, login_time, session_status) VALUES (?, ?, NOW(), 'active')");
                    if (!$stmt3) throw new Exception('Prepare failed: ' . $conn->error);

                    $stmt3->bind_param("si", $generated_session_id, $row['user_id']);
                    $stmt3->execute();

                    // QUERY ROLE 
                    $role = null;
                    $role_stmt = $conn->prepare("SELECT role_name FROM roles_table WHERE role_id = ? LIMIT 1");
                    if (!$role_stmt) throw new Exception('Prepare failed: ' . $conn->error);
                    $role_stmt->bind_param("i", $row['role_id']);
                    $role_stmt->execute();
                    $role_result = $role_stmt->get_result();
                    if ($role_result && $role_result->num_rows > 0) {
                        $role_row = $role_result->fetch_assoc();
                        $role = $role_row['role_name'];
                    }
                }

                // Ensure role name available even if cookie already existed
                if (!isset($role) || $role === null) {
                    $role_stmt2 = $conn->prepare("SELECT role_name FROM roles_table WHERE role_id = ? LIMIT 1");
                    if ($role_stmt2) { $role_stmt2->bind_param("i", $row['role_id']); $role_stmt2->execute(); $r2 = $role_stmt2->get_result(); if ($r2 && $r2->num_rows>0) { $role = $r2->fetch_assoc()['role_name']; } }
                }

                echo json_encode(['status' => 'success', 'user_role' => $role]);
            } else {
                // FAILED LOGIN ATTEMPT
                insert_login_attempt($row['user_id'], "failed");

                echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }

    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($conn) && $conn) $conn->close();
}
