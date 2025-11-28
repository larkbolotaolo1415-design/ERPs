<?php

include_once __DIR__ . '/../init.php';

$payslips = select_many("SELECT * FROM payroll", "");
json_success(['data' => $payslips]);
