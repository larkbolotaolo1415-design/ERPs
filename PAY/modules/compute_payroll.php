<?php

include_once __DIR__ . '/../init.php';
// sql helpers are auto-loaded via init.php
include_once MODULES_PATH . '/formula_builder.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $period_id = $_POST['period_id'];

    if (empty($period_id)) {
        die(json_encode(['status' => 'error', 'message' => 'period_id missing']));
    }

    $period = select("SELECT * FROM payroll_period WHERE period_id = ? LIMIT 1", "i", $period_id);

    // $period = $period[0];

    //  if status is procesing, just select from table with date range
    //  kapag open, then compute

    if ($period['status'] == "processing") {

        $payrolls = select_many("SELECT 
                                LPAD(p.payroll_id, 2, '0') AS ID,
                                u.user_name AS Name,
                                pos.sg_grade AS `Pay Grade`,
                                CONCAT('P', FORMAT(p.basic_pay, 2)) AS `Basic Pay`,
                                IFNULL(CONCAT('P', FORMAT(p.allowances, 2)), 'P0.00') AS `Allowances`,
                                IFNULL(CONCAT('P', FORMAT(p.ot_pay, 2)), 'P0.00') AS `OT Pay`,
                                IFNULL(CONCAT('P', FORMAT(p.gross_pay, 2)), 'P0.00') AS `Gross Salary`,
                                IFNULL(CONCAT('P', FORMAT(p.total_deduction, 2)), 'P0.00') AS `Deductions`,
                                IFNULL(CONCAT('P', FORMAT(p.net_pay, 2)), 'P0.00') AS `Net Pay`,
                                p.payroll_status AS `Status`
                            FROM payroll p
                            JOIN user_table u 
                                ON p.emp_id = u.user_id
                            JOIN positions pos 
                                ON u.pos_id = pos.pos_id
                            LEFT JOIN salary_grades sg 
                                ON pos.sg_grade = sg.salary_grade
                            WHERE p.payroll_period_id = ?
                            ORDER BY p.payroll_period_id", "i", $period_id);

        echo json_encode(['status' => 'success', 'objects' => $payrolls, 'type' => 'processing']);
        exit;
    } else {

        $period_start_date = $period['start_date'];
        $period_end_date = $period['end_date'];

        // Ang problema is paano maq-query ng isahan yung attendance and other stuff using employee ids na nakalagay sa associative array $employees?
        // It would take so much time to query each attendance of employee id

        $employees = select_many("SELECT * FROM user_table WHERE status = 'active'");
        $table = [];

        // Check every attendance of employees based on their ID's within specific date range
        for ($i = 0; $i < sizeof($employees); $i++) {

            // ==============   ATTENDANCE  ==============

            $attendance = select(
                "SELECT SUM(a.hours_worked) AS total_working_hours,
                COUNT(DISTINCT a.date) AS total_working_days,
                SUM(a.ot_hours) AS ot_hours,
                p.sg_grade,
                p.step,
                sg.step_1,
                sg.step_2,
                sg.step_3,
                sg.step_4,
                sg.step_5,
                sg.step_6,
                sg.step_7,
                sg.step_8
                FROM attendance a
                JOIN user_table u ON a.emp_id = u.user_id
                JOIN positions p ON u.pos_id = p.pos_id
                JOIN salary_grades sg ON sg.salary_grade = p.sg_grade
                WHERE a.emp_id = ? 
                AND a.date BETWEEN ? AND ?
            ",
                "iss",
                $employees[$i]["user_id"],
                $period_start_date,
                $period_end_date
            );

            $step = $attendance['step'];

            // BASIC PAY
            $basic_pay = $attendance[$step];
            $total_days_in_period_month = date('t', strtotime($period_start_date));
            $daily_rate = $basic_pay / $total_days_in_period_month;

            $start = new DateTime($period_start_date);
            $end = new DateTime($period_end_date);

            $interval = $start->diff($end);
            $total_days_in_period = $interval->days + 1;

            $paid_leaves = select_many(
                "SELECT date_from, date_to FROM leave_requests WHERE status = 'approved' AND emp_id = ? AND pay_types = 'paid' AND date_to >= ? AND date_from <= ?",
                "iss",
                $employees[$i]['user_id'],
                $period_start_date,
                $period_end_date
            );

            $unpaid_leaves = select_many(
                "SELECT date_from, date_to FROM leave_requests WHERE status = 'approved' AND emp_id = ? AND pay_types = 'unpaid' AND date_to >= ? AND date_from <= ?",
                "iss",
                $employees[$i]['user_id'],
                $period_start_date,
                $period_end_date
            );

            $paid_leave_days = 0;
            for ($j = 0; $j < sizeof($paid_leaves); $j++) {
                $lf = new DateTime($paid_leaves[$j]['date_from']);
                $lt = new DateTime($paid_leaves[$j]['date_to']);
                $from = $lf > $start ? $lf : $start;
                $to = $lt < $end ? $lt : $end;
                if ($from <= $to) {
                    $paid_leave_days += $from->diff($to)->days + 1;
                }
            }

            $unpaid_leave_days = 0;
            for ($j = 0; $j < sizeof($unpaid_leaves); $j++) {
                $lf = new DateTime($unpaid_leaves[$j]['date_from']);
                $lt = new DateTime($unpaid_leaves[$j]['date_to']);
                $from = $lf > $start ? $lf : $start;
                $to = $lt < $end ? $lt : $end;
                if ($from <= $to) {
                    $unpaid_leave_days += $from->diff($to)->days + 1;
                }
            }

            $schedule = select(
                "SELECT ws.mon,ws.tue,ws.wed,ws.thu,ws.fri,ws.sat,ws.sun
                 FROM employee_work_schedules es
                 JOIN work_schedules ws ON ws.schedule_id=es.schedule_id
                 WHERE es.emp_id=? AND es.effective_from <= ? AND (es.effective_to IS NULL OR es.effective_to >= ?)
                 ORDER BY es.effective_from DESC LIMIT 1",
                "iss",
                $employees[$i]['user_id'],
                $period_end_date,
                $period_start_date
            );

            $dowFlags = [];
            if (!empty($schedule)) {
                $dowFlags = [1 => intval($schedule['mon']), 2 => intval($schedule['tue']), 3 => intval($schedule['wed']), 4 => intval($schedule['thu']), 5 => intval($schedule['fri']), 6 => intval($schedule['sat']), 7 => intval($schedule['sun'])];
            } else {
                $dowFlags = [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 0, 7 => 0];
            }

            $off_days_count = 0;
            $cursor = new DateTime($period_start_date);
            while ($cursor <= $end) {
                $dow = (int)$cursor->format('N');
                if (($dowFlags[$dow] ?? 0) === 0) {
                    $off_days_count++;
                }
                $cursor->modify('+1 day');
            }

            $expected_working_days = max(0, $total_days_in_period - $off_days_count - $paid_leave_days);
            $basic_pay = $daily_rate * $expected_working_days;
            $attendance['basic_pay'] = $basic_pay;
            $attendance['daily_rate'] = $daily_rate;

            $total_working_days = $attendance['total_working_days'] ?? 0;
            $absent_days = max(0, $expected_working_days - $total_working_days);
            $attendance['absent_days'] = $absent_days;
            $attendance['paid_leave_days'] = $paid_leave_days;
            $attendance['unpaid_leave_days'] = $unpaid_leave_days;
            $attendance['off_days'] = $off_days_count;
            $attendance['expected_working_days'] = $expected_working_days;

            $attendance['hourly_rate'] = $attendance['total_working_hours'] > 0 ? ($basic_pay / $attendance['total_working_hours']) : 0;
            $attendance['late_absent_deductions'] = $attendance['daily_rate'] * $absent_days;

            // ==============   OVERTIME  ==============

            // TODO: also do the leave requests

            $overtime = select_many(
                "SELECT * FROM overtime_requests WHERE emp_id = ? AND status = 'approved' AND date BETWEEN ? and ?",
                "iss",
                $employees[$i]["user_id"],
                $period_start_date,
                $period_end_date
            );

            $ot_sum = select(
                "SELECT IFNULL(SUM(a.ot_hours * o.rate),0) AS total FROM attendance a LEFT JOIN overtime_requests o ON a.emp_id = o.emp_id AND a.date = o.date AND o.status = 'approved' WHERE a.emp_id = ? AND a.date BETWEEN ? AND ?",
                "iss",
                $employees[$i]["user_id"],
                $period_start_date,
                $period_end_date
            );

            $total_overtime_pay = $attendance['hourly_rate'] * ($ot_sum['total'] ?? 0);

            // ==============   BENEFITS  ==============

            // TODO: Formula Builder 

            $employee_benefit = select_many(
                "SELECT * FROM employee_benefits WHERE emp_id = ?",
                "i",
                $employees[$i]["user_id"]
            );

            $employee_benefits = [];

            $total_employee_benefits = 0;

            $variables = [];

            // TODO: Gawing dynamic mamaya, basahin if fixed or custom_formula yung type, kapag custom_formula, kunin yung formula.
            for ($j = 0; $j < sizeof($employee_benefit); $j++) {
                $benefit = select("SELECT * FROM benefits WHERE ben_id = ?", "i", $employee_benefit[$j]['ben_id']);
                if ($benefit['type'] == 'fixed') {
                    $total_employee_benefits += $benefit['rate_or_formula'];
                } else if ($benefit['type'] == 'custom_formula') {
                    $total_employee_benefits += evaluateFormula($benefit['rate_or_formula'], $variables);
                }

                array_push($employee_benefits, $benefit);
            }

            // ==============   GROSS PAY  ==============

            $gross_pay = $basic_pay + $total_overtime_pay + $total_employee_benefits;

            $tax_table = select("SELECT * FROM taxes WHERE range_from <= ? AND range_to >= ? LIMIT 1", "ii", $gross_pay, $gross_pay);

            $variables = [
                'gross_pay' => $gross_pay,
                'range_from' => $tax_table['range_from'],
                'rate_on_excess' => $tax_table['rate_on_excess'] / 100,
                'additional_amount' => $tax_table['additional_amount']
            ];

            // ==============   DEDUCTIONS  ==============

            $emp_deductions = select_many(
                "SELECT * FROM employee_deductions WHERE emp_id = ?",
                "i",
                $employees[$i]['user_id']
            );

            $employee_deductions = [];

            // Defaulty add absent deductions here
            $total_deductions = $attendance['late_absent_deductions'];
            $tax = 0;

            for ($j = 0; $j < sizeof($emp_deductions); $j++) {
                $deduction = select("SELECT * FROM deductions WHERE deduct_id = ?", "i", $emp_deductions[$j]['deduct_id']);

                $arr = [];
                array_push($arr, $deduction);

                if ($deduction['type'] == 'fixed') {
                    $total_deductions += $deduction['rate_or_formula'];
                    array_push($arr, $deduction['rate_or_formula']);
                } else if ($deduction['type'] == 'custom_formula') {
                    $total_deductions += evaluateFormula($deduction['rate_or_formula'], $variables);
                    array_push($arr, evaluateFormula($deduction['rate_or_formula'], $variables));
                    if ($deduction['deduct_id'] == 4) {
                        $tax = evaluateFormula($deduction['rate_or_formula'], $variables);
                    }
                }


                array_push($employee_deductions, $arr);
            }

            // ==============   NET PAY  ==============

            $net_pay = $gross_pay - $total_deductions;

            // ==============   PAYROLL INSERTION  ==============

            // PUSHING ALL DETAILS INTO ONE BIG TABLE
            array_push(
                $table,
                [
                    'employee_details' => $employees[$i],
                    'attendance_details' => $attendance,
                    'overtime_details' => $overtime,
                    'benefits_details' => $employee_benefits,
                    'total_employee_benefits' => $total_employee_benefits,
                    'gross_pay' => $gross_pay,
                    'tax_table' => $tax_table,
                    'employee_deduction_from_db' => $emp_deductions,
                    'emp_deductions' => $employee_deductions,
                    'total_deductions' => $total_deductions,
                    'net_pay' => $net_pay,
                    'tax' => $tax,
                    'basic_pay' => $basic_pay,
                    'total_overtime_pay' => $total_overtime_pay,
                    'absent_deduction' => $attendance['late_absent_deductions']
                ]
            );
        }


        echo json_encode(['status' => 'success', 'type' => 'open', 'objects' => $table, 'period_id' => $period_id, 'period_start_date' => $period_start_date, 'period_end_date' => $period_end_date]);
        exit;
    }
}
