<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Invalid request method', 405);
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        $search = trim($_GET['search'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $sort = $_GET['sort'] ?? 'user_id';
        $order = strtoupper($_GET['order'] ?? 'ASC');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];
        $types = '';
        if ($search !== '') {
            $where[] = '(u.user_name LIKE ? OR u.user_email LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }
        if ($role !== '') {
            $where[] = 'r.role_name = ?';
            $params[] = $role;
            $types .= 's';
        }
        if ($status !== '') {
            $where[] = 'u.status = ?';
            $params[] = $status;
            $types .= 's';
        }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $allowedSort = ['user_id','user_name','user_email','role_name','status'];
        if (!in_array($sort, $allowedSort)) $sort = 'user_id';
        $order = $order === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT u.user_id,u.user_name,u.user_email,u.status,r.role_name
                FROM user_table u LEFT JOIN roles_table r ON u.role_id=r.role_id
                $whereSql ORDER BY $sort $order LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        $types2 = $types . 'ii';
        $params2 = $params;
        $params2[] = $limit;
        $params2[] = $offset;
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }

        $countSql = "SELECT COUNT(*) as cnt FROM user_table u LEFT JOIN roles_table r ON u.role_id=r.role_id $whereSql";
        $stmt2 = $conn->prepare($countSql);
        if ($types !== '') $stmt2->bind_param($types, ...$params);
        $stmt2->execute();
        $cntRes = $stmt2->get_result();
        $total = $cntRes->fetch_assoc()['cnt'] ?? 0;
        json_success(['data' => $rows, 'total' => intval($total), 'page' => $page, 'limit' => $limit]);
        break;

    case 'updateStatus':
        ensure_role([1]);
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';
        if (!$user_id || !in_array($new_status, ['active','inactive'])) json_error('Invalid input');
        $stmt = $conn->prepare('UPDATE user_table SET status=? WHERE user_id=?');
        $stmt->bind_param('si', $new_status, $user_id);
        $stmt->execute();
        json_success();
        break;

    case 'updateRole':
        ensure_role([1]);
        $user_id = intval($_POST['user_id'] ?? 0);
        $role_id = intval($_POST['role_id'] ?? 0);
        if (!$user_id || !$role_id) json_error('Invalid input');
        $stmt = $conn->prepare('UPDATE user_table SET role_id=? WHERE user_id=?');
        $stmt->bind_param('ii', $role_id, $user_id);
        $stmt->execute();
        json_success();
        break;

    case 'bulkUpdateRole':
        ensure_role([1]);
        $role_id = intval($_POST['role_id'] ?? 0);
        if (!$role_id) json_error('Invalid input');
        $stmt = $conn->prepare('UPDATE user_table SET role_id=?');
        $stmt->bind_param('i', $role_id);
        $stmt->execute();
        json_success(['updated' => $conn->affected_rows]);
        break;

    case 'create':
        ensure_role([1]);
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['user_email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role_id = intval($_POST['role_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if ($name === '' || $email === '' || $password === '' || !$role_id)
            json_error('Missing fields');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            json_error('Invalid email format');

        // Enforce password policy...
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Check duplicate email
        $stmt0 = $conn->prepare('SELECT user_id FROM user_table WHERE user_email=? LIMIT 1');
        $stmt0->bind_param('s', $email);
        $stmt0->execute();
        $res0 = $stmt0->get_result();
        if ($res0 && $res0->num_rows > 0) json_error('Email already exists');

        // Insert
        $stmt = $conn->prepare('INSERT INTO user_table (user_name,user_email,password,role_id,status) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssis', $name, $email, $hashed, $role_id, $status);
        if (!$stmt->execute()) json_error('Insert failed: '.$conn->error);

        $new_id = intval($conn->insert_id);

        // Audit
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'user_id=' . $new_id . ', email=' . $email;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "create_user", "User Management", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }

        json_success(['user_id' => $new_id]);
        break;

    case 'update':
        ensure_role([1]);
        $user_id = intval($_POST['user_id'] ?? 0);
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['user_email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role_id = intval($_POST['role_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$user_id) json_error('Invalid input');
        $fields = [];
        $types = '';
        $params = [];
        if ($name !== '') { $fields[] = 'user_name=?'; $types .= 's'; $params[] = $name; }
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email format');
            $fields[] = 'user_email=?'; $types .= 's'; $params[] = $email;
        }
        if ($password !== '') {
            // Enforce password policy only if changing password
            $policy = [];
            $sp = $conn->prepare('SELECT setting_name,value FROM settings_table WHERE setting_name IN ("Minimum Password Length","Require Uppercase Letters","Require Lowercase Letters","Require Numbers","Require Symbols")');
            if ($sp && $sp->execute()) {
                $rp = $sp->get_result();
                while ($row = $rp->fetch_assoc()) { $policy[$row['setting_name']] = $row['value']; }
            }
            $minLen = max(6, intval($policy['Minimum Password Length'] ?? 8));
            $needUpper = ($policy['Require Uppercase Letters'] ?? '1') == '1';
            $needLower = ($policy['Require Lowercase Letters'] ?? '1') == '1';
            $needNum = ($policy['Require Numbers'] ?? '1') == '1';
            $needSym = ($policy['Require Symbols'] ?? '1') == '1';
            $errs = [];
            if (strlen($password) < $minLen) $errs[] = 'Password too short';
            if ($needUpper && !preg_match('/[A-Z]/', $password)) $errs[] = 'Needs uppercase';
            if ($needLower && !preg_match('/[a-z]/', $password)) $errs[] = 'Needs lowercase';
            if ($needNum && !preg_match('/\d/', $password)) $errs[] = 'Needs number';
            if ($needSym && !preg_match('/[^A-Za-z0-9]/', $password)) $errs[] = 'Needs symbol';
            if (count($errs)) json_error('Password policy violation: ' . implode(', ', $errs));
            $fields[] = 'password=?'; $types .= 's'; $params[] = $password;
        }
        if ($role_id) { $fields[] = 'role_id=?'; $types .= 'i'; $params[] = $role_id; }
        if ($status !== '') { $fields[] = 'status=?'; $types .= 's'; $params[] = $status; }
        if (!count($fields)) json_error('Nothing to update');
        $sql = 'UPDATE user_table SET ' . implode(',', $fields) . ' WHERE user_id=?';
        $stmt = $conn->prepare($sql);
        $types .= 'i';
        $params[] = $user_id;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'user_id=' . $user_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_user", "User Management", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success();
        break;

    case 'get':
        require_login();
        $user_id = intval($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
        if (!$user_id) json_error('Invalid input');
        $stmt = $conn->prepare('SELECT u.user_id,u.user_name,u.user_email,u.status,u.role_id,r.role_name FROM user_table u LEFT JOIN roles_table r ON u.role_id=r.role_id WHERE u.user_id=?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) json_error('Not found', 404);
        json_success(['data' => $row]);
        break;

    case 'delete':
        ensure_role([1]);
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) json_error('Invalid input');


        // Check for references in bonus_adjustments (approved_by)
        $stmtBonus = $conn->prepare('SELECT ba_id FROM bonus_adjustments WHERE approved_by=? LIMIT 1');
        $stmtBonus->bind_param('i', $user_id);
        $stmtBonus->execute();
        $resBonus = $stmtBonus->get_result();
        if ($resBonus && $resBonus->num_rows > 0) {
            json_error('Cannot delete user: referenced as bonus approver.');
        }

        // Check for references in departments (head_emp_id)
        $stmtDept = $conn->prepare('SELECT dept_id FROM departments WHERE head_emp_id=? LIMIT 1');
        $stmtDept->bind_param('i', $user_id);
        $stmtDept->execute();
        $resDept = $stmtDept->get_result();
        if ($resDept && $resDept->num_rows > 0) {
            json_error('Cannot delete user: referenced as department head.');
        }

        // Check for references in leave_requests (approved_by)
        $stmtLeave = $conn->prepare('SELECT leave_id FROM leave_requests WHERE approved_by=? LIMIT 1');
        $stmtLeave->bind_param('i', $user_id);
        $stmtLeave->execute();
        $resLeave = $stmtLeave->get_result();
        if ($resLeave && $resLeave->num_rows > 0) {
            json_error('Cannot delete user: referenced as leave approver.');
        }

        // Check for references in overtime_requests (approved_by)
        $stmtOT = $conn->prepare('SELECT overtime_id FROM overtime_requests WHERE approved_by=? LIMIT 1');
        $stmtOT->bind_param('i', $user_id);
        $stmtOT->execute();
        $resOT = $stmtOT->get_result();
        if ($resOT && $resOT->num_rows > 0) {
            json_error('Cannot delete user: referenced as overtime approver.');
        }

        $conn->begin_transaction();
        try {
            $stmtPR = $conn->prepare('DELETE FROM password_reset WHERE user_id=?');
            if ($stmtPR) { $stmtPR->bind_param('i', $user_id); $stmtPR->execute(); }
            $stmtLA = $conn->prepare('DELETE FROM login_attempts WHERE user_id=?');
            if ($stmtLA) { $stmtLA->bind_param('i', $user_id); $stmtLA->execute(); }
            $stmtAU = $conn->prepare('DELETE FROM audit_table WHERE user_id=?');
            if ($stmtAU) { $stmtAU->bind_param('i', $user_id); $stmtAU->execute(); }
            $stmtS = $conn->prepare('DELETE FROM sessions WHERE user_id=?');
            if ($stmtS) { $stmtS->bind_param('i', $user_id); $stmtS->execute(); }
            $stmtE = $conn->prepare('DELETE FROM employees WHERE user_id=?');
            if ($stmtE) { $stmtE->bind_param('i', $user_id); $stmtE->execute(); }
            $stmtU = $conn->prepare('DELETE FROM user_table WHERE user_id=?');
            $stmtU->bind_param('i', $user_id);
            $stmtU->execute();
            $conn->commit();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'user_id=' . $user_id;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "delete_user", "User Management", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success();
        } catch (Exception $e) {
            $conn->rollback();
            json_error('Delete failed: ' . $e->getMessage());
        }
        break;

    default:
        json_error('Unknown action', 400);
}