<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'history';

switch ($action) {
    case 'history':
        require_login();
        $emp_id = intval($_GET['emp_id'] ?? 0);
        if (!$emp_id && isset($_SESSION['user_id'])) {
            $stmtE = $conn->prepare('SELECT emp_id FROM employees WHERE user_id = ? LIMIT 1');
            $stmtE->bind_param('i', $_SESSION['user_id']);
            $stmtE->execute();
            $rowE = $stmtE->get_result()->fetch_assoc();
            $emp_id = intval($rowE['emp_id'] ?? 0);
        }
        if (!$emp_id) json_error('Missing emp_id');
        $stmt = $conn->prepare('SELECT p.*, pp.start_date, pp.end_date FROM payroll p LEFT JOIN payroll_period pp ON pp.period_id = p.payroll_period_id WHERE p.emp_id=? ORDER BY p.payroll_id DESC');
        $stmt->bind_param('i', $emp_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'detail':
        require_login();
        $payroll_id = intval($_GET['payroll_id'] ?? 0);
        if (!$payroll_id) json_error('Missing payroll_id');
        $stmt = $conn->prepare('SELECT p.*, u.user_name, d.dept_name, po.pos_name, pp.start_date, pp.end_date FROM payroll p LEFT JOIN employees e ON e.emp_id = p.emp_id LEFT JOIN user_table u ON u.user_id = e.user_id LEFT JOIN departments d ON d.dept_id = p.dept_id LEFT JOIN positions po ON po.pos_id = p.pos_id LEFT JOIN payroll_period pp ON pp.period_id = p.payroll_period_id WHERE payroll_id=?');
        $stmt->bind_param('i', $payroll_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) json_error('Not found', 404);
        json_success(['data' => $row]);
        break;

    default:
        json_error('Unknown action');
}