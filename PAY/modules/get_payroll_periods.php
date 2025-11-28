<?php

require_once __DIR__ . '/../init.php';
// sql helpers are auto-loaded via init.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payroll_periods = select_many("SELECT * FROM payroll_period", "");
    echo json_encode(['status' => 'success', 'payroll_periods' => $payroll_periods]);
}
