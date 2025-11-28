<?php

require_once __DIR__ . '/../../init.php';

function require_login()
{
    $u = current_user();
    if ($u === null) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

function current_user()
{
    static $cached = null;
    if ($cached !== null) return $cached;
    $sid = $_COOKIE['session_id'] ?? null;
    if (!$sid) {
        $cached = null;
        return null;
    }
    $row = select("SELECT s.user_id, u.user_name, u.role_id FROM sessions s JOIN user_table u ON u.user_id=s.user_id WHERE s.session_id=? AND s.session_status='active' LIMIT 1", 's', $sid);
    if (!$row) {
        $cached = null;
        return null;
    }
    $_SESSION['user_id'] = intval($row['user_id']);
    $_SESSION['user_name'] = $row['user_name'];
    $_SESSION['role_id'] = intval($row['role_id']);
    $cached = ['user_id' => intval($row['user_id']), 'user_name' => $row['user_name'], 'role_id' => intval($row['role_id'])];
    return $cached;
}

function user_has_role($role)
{
    $u = current_user();
    $rid = $u['role_id'] ?? null;
    if ($rid === null) return false;
    if (is_numeric($role)) return intval($rid) === intval($role);
    global $conn;
    $stmt = $conn->prepare('SELECT role_id FROM roles_table WHERE role_name = ? LIMIT 1');
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        return intval($rid) === intval($row['role_id']);
    }
    return false;
}

function ensure_role($roles)
{
    require_login();
    $ok = false;
    foreach ((array)$roles as $r) {
        if (user_has_role($r)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
        exit;
    }
}
