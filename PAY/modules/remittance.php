<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'summary';

switch ($action) {
    case 'summary':
        ensure_role([2,1]);
        $period_id = intval($_GET['period_id'] ?? 0);
        if (!$period_id) json_error('Missing period_id');
        $stmt = $conn->prepare('SELECT SUM(sss) as sss_total, SUM(philhealth) as philhealth_total, SUM(pag_ibig) as pagibig_total, SUM(tax) as tax_total FROM payroll WHERE payroll_period_id=? AND payroll_status IN (\'approved\',\'locked\')');
        $stmt->bind_param('i', $period_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        json_success(['data' => $row]);
        break;

    default:
        json_error('Unknown action');
}