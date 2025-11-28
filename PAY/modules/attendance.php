<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        $emp_id = intval($_GET['emp_id'] ?? 0);
        $date_from = $_GET['date_from'] ?? null;
        $date_to = $_GET['date_to'] ?? null;
        $where = [];
        $params = [];
        $types = '';
        if ($emp_id) { $where[] = 'emp_id = ?'; $params[] = $emp_id; $types .= 'i'; }
        if ($date_from) { $where[] = 'date >= ?'; $params[] = $date_from; $types .= 's'; }
        if ($date_to) { $where[] = 'date <= ?'; $params[] = $date_to; $types .= 's'; }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $stmt = $conn->prepare("SELECT att_id,emp_id,date,time_in,time_out,hours_worked,ot_hours,status FROM attendance $whereSql ORDER BY date DESC");
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'logTimeIn':
        ensure_role([4,2]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $time_in = $_POST['time_in'] ?? date('H:i:s');
        if (!$emp_id) json_error('Missing emp_id');
        $stmt = $conn->prepare('INSERT INTO attendance (emp_id,date,time_in,status) VALUES (?,?,?,\'active\')');
        $stmt->bind_param('iss', $emp_id, $date, $time_in);
        $stmt->execute();
        json_success(['att_id' => $conn->insert_id]);
        break;

    case 'logTimeOut':
        ensure_role([4,2]);
        $att_id = intval($_POST['att_id'] ?? 0);
        $time_out = $_POST['time_out'] ?? date('H:i:s');
        $hours_worked = floatval($_POST['hours_worked'] ?? 0);
        $ot_hours = floatval($_POST['ot_hours'] ?? 0);
        if (!$att_id) json_error('Missing att_id');
        $stmt = $conn->prepare('UPDATE attendance SET time_out=?,hours_worked=?,ot_hours=? WHERE att_id=?');
        $stmt->bind_param('sddi', $time_out, $hours_worked, $ot_hours, $att_id);
        $stmt->execute();
        json_success();
        break;

    default:
        json_error('Unknown action');
}
