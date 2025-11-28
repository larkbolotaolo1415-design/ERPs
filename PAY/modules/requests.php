<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? $_POST['type'] ?? 'leave';
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if (!in_array($type, ['leave','overtime','bonus'])) json_error('Invalid type');

switch ($action) {
    case 'list':
        require_login();
        $emp_id = intval($_GET['emp_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $date_from = $_GET['date_from'] ?? null;
        $date_to = $_GET['date_to'] ?? null;
        $where = [];
        $types = '';
        $params = [];
        if ($emp_id) { $where[] = 'emp_id = ?'; $types .= 'i'; $params[] = $emp_id; }
        if ($status !== '') { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
        if ($date_from) { $where[] = 'date_from >= ?'; $types .= 's'; $params[] = $date_from; }
        if ($date_to) { $where[] = 'date_to <= ?'; $types .= 's'; $params[] = $date_to; }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        if ($type === 'leave') {
            $sql = 'SELECT * FROM leave_requests ' . $whereSql . ' ORDER BY leave_id DESC';
        } elseif ($type === 'overtime') {
            $sql = 'SELECT * FROM overtime_requests ' . $whereSql . ' ORDER BY overtime_id DESC';
        } else {
            $sql = 'SELECT * FROM bonus_adjustments ' . $whereSql . ' ORDER BY ba_id DESC';
        }
        $stmt = $conn->prepare($sql);
        if ($types !== '') { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'request':
        ensure_role([4,2]);
        if ($type === 'leave') {
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $leave_type = trim($_POST['leave_type'] ?? '');
            $date_from = $_POST['date_from'] ?? null;
            $date_to = $_POST['date_to'] ?? null;
            $days = intval($_POST['days'] ?? 0);
            $pay_types = $_POST['pay_types'] ?? 'paid';
            if (!$emp_id || $leave_type === '' || !$date_from || !$date_to) json_error('Missing fields');
            $stmt = $conn->prepare('INSERT INTO leave_requests (emp_id,leave_type,date_from,date_to,days,pay_types,status) VALUES (?,?,?,?,?,? ,\'pending\')');
            $stmt->bind_param('isssis', $emp_id, $leave_type, $date_from, $date_to, $days, $pay_types);
            $stmt->execute();
            json_success(['leave_id' => $conn->insert_id]);
        } elseif ($type === 'overtime') {
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $date_one = $_POST['date'] ?? ($_POST['date_from'] ?? null);
            $rate = floatval($_POST['rate'] ?? 0);
            if (!$emp_id || !$date_one) json_error('Missing fields');
            $stmt = $conn->prepare('INSERT INTO overtime_requests (emp_id, date, rate, status) VALUES (?, ?, ?, \'pending\')');
            $stmt->bind_param('isd', $emp_id, $date_one, $rate);
            $stmt->execute();
            json_success(['overtime_id' => $conn->insert_id]);
        } else {
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $type_ba = $_POST['ba_type'] ?? 'bonus';
            $date_from = $_POST['date_from'] ?? null;
            $date_to = $_POST['date_to'] ?? null;
            $amount = intval($_POST['amount'] ?? 0);
            if (!$emp_id || $description === '' || !$date_from || !$date_to || !$amount) json_error('Missing fields');
            $stmt = $conn->prepare('INSERT INTO bonus_adjustments (emp_id,description,type,date_from,date_to,amount,status) VALUES (?,?,?,?,?,? ,\'pending\')');
            $stmt->bind_param('issssi', $emp_id, $description, $type_ba, $date_from, $date_to, $amount);
            $stmt->execute();
            json_success(['ba_id' => $conn->insert_id]);
        }
        break;

    case 'approve':
        ensure_role([3]);
        $approved_by = $_SESSION['user_id'] ?? 0;
        if ($type === 'leave') {
            $leave_id = intval($_POST['leave_id'] ?? 0);
            if (!$leave_id) json_error('Missing leave_id');
            $stmt = $conn->prepare('UPDATE leave_requests SET status=\'approved\',approved_by=? WHERE leave_id=?');
            $stmt->bind_param('ii', $approved_by, $leave_id);
            $stmt->execute();
            json_success();
        } elseif ($type === 'overtime') {
            $overtime_id = intval($_POST['overtime_id'] ?? 0);
            if (!$overtime_id) json_error('Missing overtime_id');
            $stmt = $conn->prepare('UPDATE overtime_requests SET status=\'approved\',approved_by=? WHERE overtime_id=?');
            $stmt->bind_param('ii', $approved_by, $overtime_id);
            $stmt->execute();
            json_success();
        } else {
            $ba_id = intval($_POST['ba_id'] ?? 0);
            if (!$ba_id) json_error('Missing ba_id');
            $stmt = $conn->prepare('UPDATE bonus_adjustments SET status=\'approved\',approved_by=? WHERE ba_id=?');
            $stmt->bind_param('ii', $approved_by, $ba_id);
            $stmt->execute();
            json_success();
        }
        break;

    case 'reject':
        ensure_role([3]);
        $approved_by = $_SESSION['user_id'] ?? 0;
        if ($type === 'leave') {
            $leave_id = intval($_POST['leave_id'] ?? 0);
            if (!$leave_id) json_error('Missing leave_id');
            $stmt = $conn->prepare('UPDATE leave_requests SET status=\'rejected\',approved_by=? WHERE leave_id=?');
            $stmt->bind_param('ii', $approved_by, $leave_id);
            $stmt->execute();
            json_success();
        } elseif ($type === 'overtime') {
            $overtime_id = intval($_POST['overtime_id'] ?? 0);
            if (!$overtime_id) json_error('Missing overtime_id');
            $stmt = $conn->prepare('UPDATE overtime_requests SET status=\'rejected\',approved_by=? WHERE overtime_id=?');
            $stmt->bind_param('ii', $approved_by, $overtime_id);
            $stmt->execute();
            json_success();
        } else {
            $ba_id = intval($_POST['ba_id'] ?? 0);
            if (!$ba_id) json_error('Missing ba_id');
            $stmt = $conn->prepare('UPDATE bonus_adjustments SET status=\'rejected\',approved_by=? WHERE ba_id=?');
            $stmt->bind_param('ii', $approved_by, $ba_id);
            $stmt->execute();
            json_success();
        }
        break;

    default:
        json_error('Unknown action');
}