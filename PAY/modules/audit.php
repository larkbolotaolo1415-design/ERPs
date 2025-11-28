<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        ensure_role([1]);
        $user = trim($_GET['user'] ?? '');
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $actionType = trim($_GET['actionType'] ?? '');
        $module = trim($_GET['module'] ?? '');
        $limit = intval($_GET['limit'] ?? 20);
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $where = [];
        $types = '';
        $params = [];
        if ($user !== '') { $where[] = 'u.user_name LIKE ?'; $types .= 's'; $params[] = '%'.$user.'%'; }
        if ($role !== '') { $where[] = 'r.role_name = ?'; $types .= 's'; $params[] = $role; }
        if ($actionType !== '') { $where[] = 'a.action = ?'; $types .= 's'; $params[] = $actionType; }
        if ($module !== '') { $where[] = 'a.module = ?'; $types .= 's'; $params[] = $module; }
        if ($from !== '') { $where[] = 'a.timestamp >= ?'; $types .= 's'; $params[] = $from.' 00:00:00'; }
        if ($to !== '') { $where[] = 'a.timestamp <= ?'; $types .= 's'; $params[] = $to.' 23:59:59'; }
        $where[] = 'a.action <> "login"';
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = 'SELECT a.audit_id, a.user_id, u.user_name, r.role_name, a.action, a.module, a.affected_record, a.timestamp FROM audit_table a LEFT JOIN user_table u ON u.user_id = a.user_id LEFT JOIN roles_table r ON r.role_id=u.role_id ' . $whereSql . ' ORDER BY a.timestamp DESC LIMIT ? OFFSET ?';
        $stmt = $conn->prepare($sql);
        $types2 = $types . 'ii';
        $params2 = $params; $params2[] = $limit; $params2[] = $offset;
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        $sqlCount = 'SELECT COUNT(*) AS cnt FROM audit_table a LEFT JOIN user_table u ON u.user_id = a.user_id LEFT JOIN roles_table r ON r.role_id=u.role_id ' . $whereSql;
        $stmtC = $conn->prepare($sqlCount);
        if ($types !== '') $stmtC->bind_param($types, ...$params);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        $total = intval(($resC->fetch_assoc()['cnt']) ?? 0);
        json_success(['data' => $rows, 'total' => $total]);
        break;

    case 'accessLogs':
        ensure_role([1]);
        $status = trim($_GET['status'] ?? '');
        $user = trim($_GET['user'] ?? '');
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');
        $limit = intval($_GET['limit'] ?? 20);
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $where = [];
        $types = '';
        $params = [];
        if ($status !== '') { $where[] = 's.session_status = ?'; $types .= 's'; $params[] = $status; }
        if ($user !== '') { $where[] = 'u.user_name LIKE ?'; $types .= 's'; $params[] = '%'.$user.'%'; }
        if ($from !== '') { $where[] = 's.login_time >= ?'; $types .= 's'; $params[] = $from.' 00:00:00'; }
        if ($to !== '') { $where[] = 's.login_time <= ?'; $types .= 's'; $params[] = $to.' 23:59:59'; }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = 'SELECT s.session_id, u.user_name, s.login_time, s.logout_time, s.session_status FROM sessions s LEFT JOIN user_table u ON u.user_id=s.user_id ' . $whereSql . ' ORDER BY s.login_time DESC LIMIT ? OFFSET ?';
        $stmt = $conn->prepare($sql);
        $types2 = $types . 'ii';
        $params2 = $params; $params2[] = $limit; $params2[] = $offset;
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        $sqlCount = 'SELECT COUNT(*) AS cnt FROM sessions s LEFT JOIN user_table u ON u.user_id=s.user_id ' . $whereSql;
        $stmtC = $conn->prepare($sqlCount);
        if ($types !== '') $stmtC->bind_param($types, ...$params);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        $total = intval(($resC->fetch_assoc()['cnt']) ?? 0);
        json_success(['data' => $rows, 'total' => $total]);
        break;

    case 'meta':
        ensure_role([1]);
        $acts = [];
        $mods = [];
        $stmt1 = $conn->prepare('SELECT DISTINCT action FROM audit_table WHERE action <> "login" ORDER BY action');
        if ($stmt1) { $stmt1->execute(); $r1 = $stmt1->get_result(); while ($row=$r1->fetch_assoc()) { $acts[] = $row['action']; } }
        $stmt2 = $conn->prepare('SELECT DISTINCT module FROM audit_table WHERE module IS NOT NULL AND module <> "" ORDER BY module');
        if ($stmt2) { $stmt2->execute(); $r2 = $stmt2->get_result(); while ($row=$r2->fetch_assoc()) { $mods[] = $row['module']; } }
        json_success(['actions' => $acts, 'modules' => $mods]);
        break;

    default:
        json_error('Unknown action');
}