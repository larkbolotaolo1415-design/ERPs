<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        $stmt = $conn->prepare('SELECT period_id,start_date,end_date,status FROM payroll_period ORDER BY period_id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'create':
        ensure_role([2,1]);
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        if (!$start || !$end) json_error('Missing dates');
        $stmt = $conn->prepare("INSERT INTO payroll_period (start_date,end_date,status) VALUES (?,?, 'open')");
        $stmt->bind_param('ss', $start, $end);
        $stmt->execute();
        $new_id = intval($conn->insert_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'period_id=' . $new_id . ', start=' . $start . ', end=' . $end;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "create_period", "Periods", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['period_id' => $new_id]);
        break;

    case 'setStatus':
        ensure_role([2,1]);
        $period_id = intval($_POST['period_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$period_id || !in_array($status, ['open','processing','locked','archived'])) json_error('Invalid input');
        $stmt = $conn->prepare('UPDATE payroll_period SET status=? WHERE period_id=?');
        $stmt->bind_param('si', $status, $period_id);
        $stmt->execute();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'period_id=' . $period_id . ', status=' . $status;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_period_status", "Periods", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success();
        break;


    default:
        json_error('Unknown action');
}
