<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';
require_once MODULES_PATH . '/formula_builder.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'list';

function tax_compute($amount, $conn)
{
    $rows = select_many('SELECT * FROM taxes ORDER BY range_from ASC');
    $tax = 0;
    foreach ($rows as $row) {
        $from = intval($row['range_from']);
        $to = intval($row['range_to']);
        if ($amount >= $from && ($to === 0 || $amount <= $to)) {
            $excess = max(0, $amount - $from);
            $rate = floatval($row['rate_on_excess']);
            $add = intval($row['additional_amount']);
            $tax = intval(round($add + ($excess * ($rate / 100.0))));
            break;
        }
    }
    return $tax;
}

switch ($action) {
    case 'list':
        require_login();
        $period_id = intval($_POST['period_id'] ?? $_GET['period_id'] ?? 0);
        $search = trim($_POST['search'] ?? $_GET['search'] ?? '');
        $dept_id = intval($_POST['dept_id'] ?? $_GET['dept_id'] ?? 0);
        $sg = intval($_POST['sg_grade'] ?? $_GET['sg_grade'] ?? 0);
        $status = trim($_POST['status'] ?? $_GET['status'] ?? '');
        $where = [];
        $types = '';
        $params = [];
        if ($period_id) {
            $where[] = 'p.payroll_period_id = ?';
            $types .= 'i';
            $params[] = $period_id;
        }
        if ($dept_id) {
            $where[] = 'p.dept_id = ?';
            $types .= 'i';
            $params[] = $dept_id;
        }
        if ($sg) {
            $where[] = 'po.sg_grade = ?';
            $types .= 'i';
            $params[] = $sg;
        }
        if ($status !== '') {
            $where[] = 'p.payroll_status = ?';
            $types .= 's';
            $params[] = $status;
        }
        if ($search !== '') {
            $where[] = '(u.user_name LIKE ? OR CAST(p.emp_id AS CHAR) LIKE ? OR d.dept_name LIKE ? OR CAST(po.sg_grade AS CHAR) LIKE ?)';
            $types .= 'ssss';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT p.*, u.user_name, d.dept_name, po.pos_name, po.sg_grade
                FROM payroll p
                LEFT JOIN user_table u ON u.user_id = p.emp_id
                LEFT JOIN departments d ON d.dept_id = p.dept_id
                LEFT JOIN positions po ON po.pos_id = p.pos_id
                $whereSql ORDER BY p.payroll_id DESC";
        $rows = select_many($sql, $types, ...$params);
        json_success(['data' => $rows]);
        break;
    case 'recompute':
        ensure_role([2, 1]);
        $period_id = intval($_POST['period_id'] ?? 0);
        if (!$period_id) json_error('Missing period_id');
        update('DELETE FROM payroll WHERE payroll_period_id=?', 'i', $period_id);
        $per = select('SELECT start_date,end_date FROM payroll_period WHERE period_id = ?', 'i', $period_id);
        if (!$per) json_error('Invalid period');
        $start = $per['start_date'];
        $end = $per['end_date'];

        $emps = select_many("SELECT u.user_id, u.pos_id, po.sg_grade, po.dept_id, po.step FROM user_table u LEFT JOIN positions po ON po.pos_id = u.pos_id WHERE u.status='active'");
        $computed = [];
        foreach ($emps as $e) {
            $emp_id = intval($e['user_id']);
            $pos_id = intval($e['pos_id']);
            $user_id = $emp_id;
            $dept_id = intval($e['dept_id'] ?? 0);
            $sg_grade = intval($e['sg_grade'] ?? 0);
            $step = $e['step'] ?? 'step_1';
            $sg = select('SELECT step_1,step_2,step_3,step_4,step_5,step_6,step_7,step_8 FROM salary_grades WHERE salary_grade=? LIMIT 1', 'i', $sg_grade);
            $base = 0;
            if (is_array($sg) && isset($sg[$step])) {
                $base = intval($sg[$step]);
            }
            $days_in_month = intval(date('t', strtotime($start)));
            $daily_rate = $days_in_month > 0 ? ($base / $days_in_month) : 0;
            $start_dt = new DateTime($start);
            $end_dt = new DateTime($end);
            $period_days = $start_dt->diff($end_dt)->days + 1;
            $paidRows = select_many("SELECT date_from,date_to FROM leave_requests WHERE status='approved' AND emp_id=? AND pay_types='paid' AND date_to >= ? AND date_from <= ?", 'iss', $user_id, $start, $end);
            $unpaidRows = select_many("SELECT date_from,date_to FROM leave_requests WHERE status='approved' AND emp_id=? AND pay_types='unpaid' AND date_to >= ? AND date_from <= ?", 'iss', $user_id, $start, $end);
            $paid_leave_days = 0;
            foreach ($paidRows as $lr) {
                $lf = new DateTime($lr['date_from']);
                $lt = new DateTime($lr['date_to']);
                $from = $lf > $start_dt ? $lf : $start_dt;
                $to = $lt < $end_dt ? $lt : $end_dt;
                if ($from <= $to) {
                    $paid_leave_days += ($from->diff($to)->days + 1);
                }
            }
            $unpaid_leave_days = 0;
            foreach ($unpaidRows as $lr) {
                $lf = new DateTime($lr['date_from']);
                $lt = new DateTime($lr['date_to']);
                $from = $lf > $start_dt ? $lf : $start_dt;
                $to = $lt < $end_dt ? $lt : $end_dt;
                if ($from <= $to) {
                    $unpaid_leave_days += ($from->diff($to)->days + 1);
                }
            }
            $sch = select("SELECT ws.mon,ws.tue,ws.wed,ws.thu,ws.fri,ws.sat,ws.sun FROM employee_work_schedules es JOIN work_schedules ws ON ws.schedule_id=es.schedule_id WHERE es.emp_id=? AND es.effective_from <= ? AND (es.effective_to IS NULL OR es.effective_to >= ?) ORDER BY es.effective_from DESC LIMIT 1", 'iss', $user_id, $end, $start);
            $flags = [];
            if (is_array($sch) && !empty($sch)) {
                $flags = [1 => intval($sch['mon']), 2 => intval($sch['tue']), 3 => intval($sch['wed']), 4 => intval($sch['thu']), 5 => intval($sch['fri']), 6 => intval($sch['sat']), 7 => intval($sch['sun'])];
            } else {
                $flags = [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 0, 7 => 0];
            }
            $off_days = 0;
            $cur = new DateTime($start);
            while ($cur <= $end_dt) {
                $dow = (int)$cur->format('N');
                if (($flags[$dow] ?? 0) === 0) {
                    $off_days++;
                }
                $cur->modify('+1 day');
            }
            $expected_working_days = max(0, $period_days - $off_days - $paid_leave_days);

            $att = select('SELECT SUM(hours_worked) as hrs, COUNT(DISTINCT date) as days, SUM(ot_hours) as ots FROM attendance WHERE emp_id=? AND date BETWEEN ? AND ?', 'iss', $emp_id, $start, $end);
            $total_hours = floatval($att['hrs'] ?? 0);
            $ot_hours = floatval($att['ots'] ?? 0);
            $days_worked = intval($att['days'] ?? 0);
            $absent_days = max(0, $expected_working_days - $days_worked);
            $basic_pay = intval(round($daily_rate * $expected_working_days));
            $hourly_rate = ($total_hours > 0) ? ($basic_pay / $total_hours) : 0;

            $ot_sum = select("SELECT IFNULL(SUM(a.ot_hours * o.rate),0) AS total FROM attendance a LEFT JOIN overtime_requests o ON a.emp_id = o.emp_id AND a.date = o.date AND o.status = 'approved' WHERE a.emp_id = ? AND a.date BETWEEN ? AND ?", 'iss', $emp_id, $start, $end);
            $ot_pay = intval(round($hourly_rate * floatval($ot_sum['total'] ?? 0)));

            $emp_ben_rows = select_many('SELECT ben_id FROM employee_benefits WHERE emp_id=?', 'i', $emp_id);
            $employee_benefits = [];
            $allowances = 0;
            $variables = [];
            foreach ($emp_ben_rows as $ebr) {
                $benefit = select('SELECT * FROM benefits WHERE ben_id=?', 'i', intval($ebr['ben_id']));
                if (!$benefit) continue;
                $employee_benefits[] = $benefit;
                if ($benefit['type'] === 'fixed') {
                    $allowances += floatval($benefit['rate_or_formula']);
                } else if ($benefit['type'] === 'custom_formula') {
                    $allowances += floatval(evaluateFormula($benefit['rate_or_formula'], $variables));
                }
            }
            $gross_pay = intval(round($basic_pay + $ot_pay + $allowances));

            $late_absent_deductions = intval(round($daily_rate * $absent_days));
            $tax_table = select('SELECT * FROM taxes WHERE range_from <= ? AND (range_to = 0 OR range_to >= ?) LIMIT 1', 'ii', $gross_pay, $gross_pay);
            $variables = [
                'gross_pay' => $gross_pay,
                'range_from' => intval($tax_table['range_from'] ?? 0),
                'rate_on_excess' => floatval(($tax_table['rate_on_excess'] ?? 0) / 100.0),
                'additional_amount' => intval($tax_table['additional_amount'] ?? 0),
            ];
            $emp_ded_rows = select_many('SELECT deduct_id FROM employee_deductions WHERE emp_id=?', 'i', $emp_id);
            $employee_deductions = [];
            $tax = 0;
            $sss = 0;
            $philhealth = 0;
            $pag_ibig = 0;
            $other_deductions = 0;
            foreach ($emp_ded_rows as $edr) {
                $deduct = select('SELECT * FROM deductions WHERE deduct_id=?', 'i', intval($edr['deduct_id']));
                if (!$deduct) continue;
                $employee_deductions[] = $deduct;
                $val = 0;
                if ($deduct['type'] === 'fixed') {
                    $val = floatval($deduct['rate_or_formula']);
                } else if ($deduct['type'] === 'custom_formula') {
                    $val = floatval(evaluateFormula($deduct['rate_or_formula'], $variables));
                }
                if (strtolower($deduct['deduct_name']) === 'tax' || intval($deduct['deduct_id']) === 4) {
                    $tax = $val;
                } else if (stripos($deduct['deduct_name'], 'sss') !== false) {
                    $sss = $val;
                } else if (stripos($deduct['deduct_name'], 'philhealth') !== false) {
                    $philhealth = $val;
                } else if (stripos($deduct['deduct_name'], 'pag-ibig') !== false || stripos($deduct['deduct_name'], 'pag ibig') !== false) {
                    $pag_ibig = $val;
                } else {
                    $other_deductions += $val;
                }
            }
            $total_deduction = intval(round($late_absent_deductions + $sss + $philhealth + $pag_ibig + $tax + $other_deductions));
            $net_pay = intval($gross_pay - $total_deduction);

            insert('INSERT INTO payroll (emp_id,dept_id,pos_id,basic_pay,days_worked,ot_hours,ot_pay,allowances,gross_pay,late_absent_deductions,sss,philhealth,pag_ibig,tax,other_deductions,total_deduction,net_pay,payroll_status,payroll_period_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'pending\',?)', 'iiiiidiiiiiiiiiiii', $emp_id, $dept_id, $pos_id, $basic_pay, $days_worked, $ot_hours, $ot_pay, $allowances, $gross_pay, $late_absent_deductions, $sss, $philhealth, $pag_ibig, $tax, $other_deductions, $total_deduction, $net_pay, $period_id);
            $computed[] = [
                'employee_details' => ['user_id' => $emp_id, 'pos_id' => $pos_id],
                'attendance_details' => [
                    'total_working_hours' => $total_hours,
                    'total_working_days' => $days_worked,
                    'ot_hours' => $ot_hours,
                    'step' => $step,
                    'daily_rate' => $daily_rate,
                    'basic_pay' => $basic_pay,
                    'absent_days' => $absent_days,
                    'paid_leave_days' => $paid_leave_days,
                    'unpaid_leave_days' => $unpaid_leave_days,
                    'off_days' => $off_days,
                    'expected_working_days' => $expected_working_days,
                    'hourly_rate' => $hourly_rate,
                    'late_absent_deductions' => $late_absent_deductions
                ],
                'benefits_details' => $employee_benefits,
                'total_employee_benefits' => $allowances,
                'gross_pay' => $gross_pay,
                'tax_table' => $tax_table,
                'employee_deduction_from_db' => $emp_ded_rows,
                'emp_deductions' => $employee_deductions,
                'total_deductions' => $total_deduction,
                'net_pay' => $net_pay,
                'tax' => $tax,
                'sss' => $sss,
                'philhealth' => $philhealth,
                'pag_ibig' => $pag_ibig,
                'total_overtime_pay' => $ot_pay,
            ];
        }
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'period_id=' . $period_id . ', count=' . count($computed);
        insert('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "recompute_payroll", "Payroll", ?, NOW())', 'is', $actor_id, $ar);
        json_success(['computed' => $computed, 'type' => 'open', 'objects' => $computed, 'period_id' => $period_id, 'period_start_date' => $start, 'period_end_date' => $end]);
        break;

    case 'setStatus':
        ensure_role([2, 1]);
        $payroll_id = intval($_POST['payroll_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$payroll_id || !in_array($status, ['pending', 'approved', 'locked'])) json_error('Invalid input');
        update('UPDATE payroll SET payroll_status=? WHERE payroll_id=?', 'si', $status, $payroll_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'payroll_id=' . $payroll_id . ', status=' . $status;
        insert('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_payroll_status", "Payroll", ?, NOW())', 'is', $actor_id, $ar);
        json_success();
        break;

    case 'setRemarks':
        ensure_role([2, 1]);
        $payroll_id = intval($_POST['payroll_id'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');
        if (!$payroll_id) json_error('Missing payroll_id');
        update('UPDATE payroll SET remarks=? WHERE payroll_id=?', 'si', $remarks, $payroll_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'payroll_id=' . $payroll_id;
        insert('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_payroll_remarks", "Payroll", ?, NOW())', 'is', $actor_id, $ar);
        json_success();
        break;

    case 'approvePeriod':
        ensure_role([2, 1]);
        $period_id = intval($_POST['period_id'] ?? 0);
        if (!$period_id) json_error('Missing period_id');
        update("UPDATE payroll SET payroll_status='approved' WHERE payroll_period_id=?", 'i', $period_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'period_id=' . $period_id;
        insert('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "approve_payroll_period", "Payroll", ?, NOW())', 'is', $actor_id, $ar);
        json_success();
        break;

    case 'lockPeriod':
        ensure_role([2, 1]);
        $period_id = intval($_POST['period_id'] ?? 0);
        if (!$period_id) json_error('Missing period_id');
        update("UPDATE payroll SET payroll_status='locked' WHERE payroll_period_id=?", 'i', $period_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'period_id=' . $period_id;
        insert('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "lock_payroll_period", "Payroll", ?, NOW())', 'is', $actor_id, $ar);
        json_success();
        break;

    case 'recomputeSelected':
        ensure_role([2, 1]);
        $period_id = intval($_POST['period_id'] ?? 0);
        $idsJson = $_POST['payroll_ids'] ?? '[]';
        $payroll_ids = json_decode($idsJson, true);
        if (!$period_id || !is_array($payroll_ids) || count($payroll_ids) === 0) json_error('Missing input');
        $per = select('SELECT start_date,end_date FROM payroll_period WHERE period_id = ?', 'i', $period_id);
        if (!$per) json_error('Invalid period');
        $start = $per['start_date'];
        $end = $per['end_date'];
        foreach ($payroll_ids as $pid) {
            $row = select('SELECT emp_id, dept_id, pos_id FROM payroll WHERE payroll_id=? AND payroll_period_id=?', 'ii', intval($pid), $period_id);
            if (!$row) {
                continue;
            }
            $e = [
                'user_id' => intval($row['emp_id']),
                'pos_id' => intval($row['pos_id'])
            ];
            $emp_id = $e['user_id'];
            $pos_id = $e['pos_id'];
            $dept_id = intval($row['dept_id'] ?? 0);
            $po = select('SELECT sg_grade, step FROM positions WHERE pos_id=?', 'i', $pos_id);
            $sg_grade = intval($po['sg_grade'] ?? 0);
            $step = $po['step'] ?? 'step_1';
            $sg = select('SELECT step_1,step_2,step_3,step_4,step_5,step_6,step_7,step_8 FROM salary_grades WHERE salary_grade=? LIMIT 1', 'i', $sg_grade);
            $base = 0;
            if (is_array($sg) && isset($sg[$step])) {
                $base = intval($sg[$step]);
            }
            $days_in_month = intval(date('t', strtotime($start)));
            $daily_rate = $days_in_month > 0 ? ($base / $days_in_month) : 0;
            $start_dt = new DateTime($start);
            $end_dt = new DateTime($end);
            $period_days = $start_dt->diff($end_dt)->days + 1;
            $paidRows = select_many("SELECT date_from,date_to FROM leave_requests WHERE status='approved' AND emp_id=? AND pay_types='paid' AND date_to >= ? AND date_from <= ?", 'iss', $emp_id, $start, $end);
            $unpaidRows = select_many("SELECT date_from,date_to FROM leave_requests WHERE status='approved' AND emp_id=? AND pay_types='unpaid' AND date_to >= ? AND date_from <= ?", 'iss', $emp_id, $start, $end);
            $paid_leave_days = 0;
            foreach ($paidRows as $lr) {
                $lf = new DateTime($lr['date_from']);
                $lt = new DateTime($lr['date_to']);
                $from = $lf > $start_dt ? $lf : $start_dt;
                $to = $lt < $end_dt ? $lt : $end_dt;
                if ($from <= $to) {
                    $paid_leave_days += ($from->diff($to)->days + 1);
                }
            }
            $unpaid_leave_days = 0;
            foreach ($unpaidRows as $lr) {
                $lf = new DateTime($lr['date_from']);
                $lt = new DateTime($lr['date_to']);
                $from = $lf > $start_dt ? $lf : $start_dt;
                $to = $lt < $end_dt ? $lt : $end_dt;
                if ($from <= $to) {
                    $unpaid_leave_days += ($from->diff($to)->days + 1);
                }
            }
            $sch = select("SELECT ws.mon,ws.tue,ws.wed,ws.thu,ws.fri,ws.sat,ws.sun FROM employee_work_schedules es JOIN work_schedules ws ON ws.schedule_id=es.schedule_id WHERE es.emp_id=? AND es.effective_from <= ? AND (es.effective_to IS NULL OR es.effective_to >= ?) ORDER BY es.effective_from DESC LIMIT 1", 'iss', $emp_id, $end, $start);
            $flags = [1 => intval($sch['mon'] ?? 1), 2 => intval($sch['tue'] ?? 1), 3 => intval($sch['wed'] ?? 1), 4 => intval($sch['thu'] ?? 1), 5 => intval($sch['fri'] ?? 1), 6 => intval($sch['sat'] ?? 0), 7 => intval($sch['sun'] ?? 0)];
            $off_days = 0;
            $cur = new DateTime($start);
            while ($cur <= $end_dt) {
                $dow = (int)$cur->format('N');
                if (($flags[$dow] ?? 0) === 0) {
                    $off_days++;
                }
                $cur->modify('+1 day');
            }
            $expected_working_days = max(0, $period_days - $off_days - $paid_leave_days);
            $att = select('SELECT SUM(hours_worked) as hrs, COUNT(DISTINCT date) as days, SUM(ot_hours) as ots FROM attendance WHERE emp_id=? AND date BETWEEN ? AND ?', 'iss', $emp_id, $start, $end);
            $total_hours = floatval($att['hrs'] ?? 0);
            $ot_hours = floatval($att['ots'] ?? 0);
            $days_worked = intval($att['days'] ?? 0);
            $absent_days = max(0, $expected_working_days - $days_worked);
            $basic_pay = intval(round($daily_rate * $expected_working_days));
            $hourly_rate = ($total_hours > 0) ? ($basic_pay / $total_hours) : 0;
            $ot_sum = select("SELECT IFNULL(SUM(a.ot_hours * o.rate),0) AS total FROM attendance a LEFT JOIN overtime_requests o ON a.emp_id = o.emp_id AND a.date = o.date AND o.status = 'approved' WHERE a.emp_id = ? AND a.date BETWEEN ? AND ?", 'iss', $emp_id, $start, $end);
            $ot_pay = intval(round($hourly_rate * floatval($ot_sum['total'] ?? 0)));
            $emp_ben_rows = select_many('SELECT ben_id FROM employee_benefits WHERE emp_id=?', 'i', $emp_id);
            $allowances = 0;
            $variables = [];
            foreach ($emp_ben_rows as $ebr) {
                $benefit = select('SELECT * FROM benefits WHERE ben_id=?', 'i', intval($ebr['ben_id']));
                if (!$benefit) continue;
                if ($benefit['type'] === 'fixed') {
                    $allowances += floatval($benefit['rate_or_formula']);
                } else if ($benefit['type'] === 'custom_formula') {
                    $allowances += floatval(evaluateFormula($benefit['rate_or_formula'], $variables));
                }
            }
            $gross_pay = intval(round($basic_pay + $ot_pay + $allowances));
            $late_absent_deductions = intval(round($daily_rate * $absent_days));
            $tax_table = select('SELECT * FROM taxes WHERE range_from <= ? AND (range_to = 0 OR range_to >= ?) LIMIT 1', 'ii', $gross_pay, $gross_pay);
            $variables = ['gross_pay' => $gross_pay, 'range_from' => intval($tax_table['range_from'] ?? 0), 'rate_on_excess' => floatval(($tax_table['rate_on_excess'] ?? 0) / 100.0), 'additional_amount' => intval($tax_table['additional_amount'] ?? 0)];
            $emp_ded_rows = select_many('SELECT deduct_id FROM employee_deductions WHERE emp_id=?', 'i', $emp_id);
            $tax = 0;
            $sss = 0;
            $philhealth = 0;
            $pag_ibig = 0;
            $other_deductions = 0;
            foreach ($emp_ded_rows as $edr) {
                $deduct = select('SELECT * FROM deductions WHERE deduct_id=?', 'i', intval($edr['deduct_id']));
                if (!$deduct) continue;
                $val = 0;
                if ($deduct['type'] === 'fixed') {
                    $val = floatval($deduct['rate_or_formula']);
                } else if ($deduct['type'] === 'custom_formula') {
                    $val = floatval(evaluateFormula($deduct['rate_or_formula'], $variables));
                }
                if (strtolower($deduct['deduct_name']) === 'tax' || intval($deduct['deduct_id']) === 4) {
                    $tax = $val;
                } else if (stripos($deduct['deduct_name'], 'sss') !== false) {
                    $sss = $val;
                } else if (stripos($deduct['deduct_name'], 'philhealth') !== false) {
                    $philhealth = $val;
                } else if (stripos($deduct['deduct_name'], 'pag-ibig') !== false || stripos($deduct['deduct_name'], 'pag ibig') !== false) {
                    $pag_ibig = $val;
                } else {
                    $other_deductions += $val;
                }
            }
            $total_deduction = intval(round($late_absent_deductions + $sss + $philhealth + $pag_ibig + $tax + $other_deductions));
            $net_pay = intval($gross_pay - $total_deduction);
            update('DELETE FROM payroll WHERE payroll_id=?', 'i', intval($pid));
            insert('INSERT INTO payroll (emp_id,dept_id,pos_id,basic_pay,days_worked,ot_hours,ot_pay,allowances,gross_pay,late_absent_deductions,sss,philhealth,pag_ibig,tax,other_deductions,total_deduction,net_pay,payroll_status,payroll_period_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'pending\',?)', 'iiiiidiiiiiiiiiiii', $emp_id, $dept_id, $pos_id, $basic_pay, $days_worked, $ot_hours, $ot_pay, $allowances, $gross_pay, $late_absent_deductions, $sss, $philhealth, $pag_ibig, $tax, $other_deductions, $total_deduction, $net_pay, $period_id);
        }
        json_success();
        break;

    default:
        json_error('Unknown action');
}
