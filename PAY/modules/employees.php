<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        $status = $_POST['status'] ?? '';
        $dept_id = intval($_POST['dept_id'] ?? 0);
        $pos_id = intval($_POST['pos_id'] ?? 0);
        $sort = $_POST['sort'] ?? '';
        $order = strtolower($_POST['order'] ?? 'asc');
        $where = [];
        $types = '';
        $params = [];
        if ($status !== '') {
            $where[] = 'u.status=?';
            $types .= 's';
            $params[] = $status;
        }
        if ($dept_id) {
            $where[] = 'd.dept_id=?';
            $types .= 'i';
            $params[] = $dept_id;
        }
        if ($pos_id) {
            $where[] = 'u.pos_id=?';
            $types .= 'i';
            $params[] = $pos_id;
        }
        $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $orderBy = 'u.user_id ASC';
        if ($sort === 'name') {
            $orderBy = 'u.user_name ' . ($order === 'desc' ? 'DESC' : 'ASC');
        }
        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.pos_id, u.status,
                       p.pos_name, p.sg_grade, d.dept_id, d.dept_name,
                       (SELECT sg.step_1 FROM salary_grades sg WHERE sg.salary_grade = p.sg_grade LIMIT 1) AS basic_pay
                FROM user_table u
                LEFT JOIN positions p ON p.pos_id = u.pos_id
                LEFT JOIN departments d ON d.dept_id = p.dept_id
                $whereSql ORDER BY $orderBy";
        $stmt = $conn->prepare($sql);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = [
                'emp_id' => intval($row['user_id']),
                'user_id' => intval($row['user_id']),
                'user_name' => $row['user_name'],
                'user_email' => $row['user_email'],
                'dept_id' => intval($row['dept_id'] ?? 0),
                'dept_name' => $row['dept_name'] ?? '',
                'pos_id' => intval($row['pos_id'] ?? 0),
                'pos_name' => $row['pos_name'] ?? '',
                'sg_grade' => intval($row['sg_grade'] ?? 0),
                'basic_pay' => intval($row['basic_pay'] ?? 0),
                'status' => $row['status']
            ];
        }
        json_success(['data' => $rows]);
        break;

    case 'create':
        ensure_role([2, 1]);
        $user_name = trim($_POST['user_name'] ?? '');
        $user_email = trim($_POST['user_email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_id = intval($_POST['role_id'] ?? 0);
        $pos_id = intval($_POST['pos_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        if ($user_name === '' || $user_email === '' || !$role_id) json_error('Missing fields');
        $stmt = $conn->prepare('INSERT INTO user_table (user_name, user_email, password, role_id, pos_id, status) VALUES (?,?,?,?,?,?)');
        $stmt->bind_param('sssiss', $user_name, $user_email, $password, $role_id, $pos_id, $status);
        $stmt->execute();
        $new_id = intval($conn->insert_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'user_id=' . $new_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "create_user", "Employee Management", ?, NOW())');
        if ($stmtA) {
            $stmtA->bind_param('is', $actor_id, $ar);
            $stmtA->execute();
        }
        json_success(['user_id' => $new_id]);
        break;

    case 'update':
        ensure_role([2, 1]);
        $user_id = intval($_POST['emp_id'] ?? ($_POST['user_id'] ?? 0));
        $pos_id = intval($_POST['pos_id'] ?? 0);
        $status = $_POST['status'] ?? null;
        if (!$user_id) json_error('Missing user_id');
        $fields = [];
        $types = '';
        $params = [];
        if ($pos_id) {
            $fields[] = 'pos_id=?';
            $types .= 'i';
            $params[] = $pos_id;
        }
        if ($status) {
            $fields[] = 'status=?';
            $types .= 's';
            $params[] = $status;
        }
        if (!count($fields)) json_error('Nothing to update');
        $sql = 'UPDATE user_table SET ' . implode(',', $fields) . ' WHERE user_id=?';
        $types .= 'i';
        $params[] = $user_id;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'user_id=' . $user_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_user", "Employee Management", ?, NOW())');
        if ($stmtA) {
            $stmtA->bind_param('is', $actor_id, $ar);
            $stmtA->execute();
        }
        json_success();
        break;

    case 'positions':
        require_login();
        $sql = 'SELECT pos_id, pos_name FROM positions ORDER BY pos_name ASC';
        $res = $conn->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        json_success(['data' => $rows]);
        break;

    case 'profile':
        require_login();
        $emp_id = intval($_POST['emp_id'] ?? 0);
        if (!$emp_id) json_error('Missing emp_id');
        $stmt = $conn->prepare("SELECT u.user_id AS emp_id, u.user_name, u.user_email, u.pos_id, u.status,
                                       p.pos_name, p.sg_grade, p.step, d.dept_id, d.dept_name,
                                       e.hire_date, e.employment_type
                                FROM user_table u
                                LEFT JOIN positions p ON p.pos_id = u.pos_id
                                LEFT JOIN departments d ON d.dept_id = p.dept_id
                                LEFT JOIN employees e ON e.user_id = u.user_id
                                WHERE u.user_id = ? LIMIT 1");
        $stmt->bind_param('i', $emp_id);
        $stmt->execute();
        $base = $stmt->get_result()->fetch_assoc();
        if (!$base) json_error('Not found', 404);
        $pos_id = intval($base['pos_id'] ?? 0);
        $sg_grade = intval($base['sg_grade'] ?? 0);
        $step = $base['step'] ?? 'step_1';
        $ss = $conn->prepare('SELECT basic_pay FROM salary_structure WHERE pos_id=?');
        $ss->bind_param('i', $pos_id);
        $ss->execute();
        $ssRow = $ss->get_result()->fetch_assoc();
        $sg = $conn->prepare('SELECT step_1,step_2,step_3,step_4,step_5,step_6,step_7,step_8 FROM salary_grades WHERE salary_grade=?');
        $sg->bind_param('i', $sg_grade);
        $sg->execute();
        $sgRow = $sg->get_result()->fetch_assoc();
        $basic_pay = 0;
        if ($ssRow && isset($ssRow['basic_pay'])) { $basic_pay = intval($ssRow['basic_pay']); }
        else if ($sgRow && isset($sgRow[$step])) { $basic_pay = intval($sgRow[$step]); }

        $benRows = select_many('SELECT eb.ben_id, b.ben_name, b.type, b.rate_or_formula, eb.status FROM employee_benefits eb JOIN benefits b ON b.ben_id=eb.ben_id WHERE eb.emp_id=?', 'i', $emp_id);
        $dedRows = select_many('SELECT ed.deduct_id, d.deduct_name, d.type, d.rate_or_formula, ed.status FROM employee_deductions ed JOIN deductions d ON d.deduct_id=ed.deduct_id WHERE ed.emp_id=?', 'i', $emp_id);
        json_success(['data' => [
            'emp_id' => intval($base['emp_id']),
            'user_name' => $base['user_name'],
            'user_email' => $base['user_email'],
            'dept_id' => intval($base['dept_id'] ?? 0),
            'dept_name' => $base['dept_name'] ?? '',
            'pos_id' => $pos_id,
            'pos_name' => $base['pos_name'] ?? '',
            'sg_grade' => $sg_grade,
            'status' => $base['status'] ?? 'active',
            'hire_date' => $base['hire_date'] ?? '',
            'employment_type' => $base['employment_type'] ?? '',
            'basic_pay' => $basic_pay,
            'benefits' => $benRows,
            'deductions' => $dedRows
        ]]);
        break;

    case 'catalogs':
        require_login();
        $benefits = select_many('SELECT ben_id, ben_name FROM benefits WHERE status=\'active\'');
        $deds = select_many('SELECT deduct_id, deduct_name FROM deductions WHERE status=\'active\'');
        json_success(['data' => ['benefits' => $benefits, 'deductions' => $deds]]);
        break;

    case 'benefitAssign':
        ensure_role([2,1,3]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $ben_id = intval($_POST['ben_id'] ?? 0);
        if (!$emp_id || !$ben_id) json_error('Missing input');
        insert('INSERT INTO employee_benefits (emp_id, ben_id, status) VALUES (?,?,\'active\')', 'ii', $emp_id, $ben_id);
        json_success();
        break;

    case 'benefitRemove':
        ensure_role([2,1,3]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $ben_id = intval($_POST['ben_id'] ?? 0);
        if (!$emp_id || !$ben_id) json_error('Missing input');
        update('DELETE FROM employee_benefits WHERE emp_id=? AND ben_id=?', 'ii', $emp_id, $ben_id);
        json_success();
        break;

    case 'deductionAssign':
        ensure_role([2,1,3]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $deduct_id = intval($_POST['deduct_id'] ?? 0);
        if (!$emp_id || !$deduct_id) json_error('Missing input');
        insert('INSERT INTO employee_deductions (emp_id, deduct_id, status) VALUES (?,?,\'active\')', 'ii', $emp_id, $deduct_id);
        json_success();
        break;

    case 'deductionRemove':
        ensure_role([2,1,3]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $deduct_id = intval($_POST['deduct_id'] ?? 0);
        if (!$emp_id || !$deduct_id) json_error('Missing input');
        update('DELETE FROM employee_deductions WHERE emp_id=? AND deduct_id=?', 'ii', $emp_id, $deduct_id);
        json_success();
        break;

    case 'updateBasicPay':
        ensure_role([1]);
        $pos_id = intval($_POST['pos_id'] ?? 0);
        $basic_pay = intval($_POST['basic_pay'] ?? 0);
        if (!$pos_id) json_error('Missing pos_id');
        $aff = update('UPDATE salary_structure SET basic_pay=? WHERE pos_id=?', 'ii', $basic_pay, $pos_id);
        if ($aff === 0) {
            insert('INSERT INTO salary_structure (pos_id, basic_pay) VALUES (?,?)', 'ii', $pos_id, $basic_pay);
        }
        json_success();
        break;

    default:
        json_error('Unknown action');
}
