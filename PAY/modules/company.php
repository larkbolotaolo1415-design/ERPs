<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$resource = $_GET['resource'] ?? $_POST['resource'] ?? 'departments';
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        switch ($resource) {
            case 'departments':
                $stmt = $conn->prepare('SELECT dept_id, dept_name FROM departments ORDER BY dept_name ASC');
                $stmt->execute();
                $res = $stmt->get_result();
                $rows = [];
                while ($row = $res->fetch_assoc()) { $rows[] = $row; }
                json_success(['data' => $rows]);
                break;

            case 'org_settings':
                $stmt = $conn->prepare("SELECT setting_name, value FROM settings_table WHERE setting_name IN ('Company Name','Company Address','Company Contact')");
                $stmt->execute();
                $res = $stmt->get_result();
                $out = [];
                while ($row = $res->fetch_assoc()) { $out[$row['setting_name']] = $row['value']; }
                json_success(['data' => $out]);
                break;

            case 'positions':
                $stmt = $conn->prepare('SELECT p.pos_id, p.pos_name, p.sg_grade, p.dept_id, d.dept_name FROM positions p LEFT JOIN departments d ON d.dept_id = p.dept_id ORDER BY d.dept_name, p.pos_name');
                $stmt->execute();
                $res = $stmt->get_result();
                $rows = [];
                while ($row = $res->fetch_assoc()) { $rows[] = $row; }
                json_success(['data' => $rows]);
                break;

            case 'salary_structures':
                // Make sure this query matches your table structure
                $stmt = $conn->prepare('SELECT ss.pos_id, ss.basic_pay, p.pos_name, d.dept_name 
                                    FROM salary_structure ss 
                                    LEFT JOIN positions p ON p.pos_id = ss.pos_id 
                                    LEFT JOIN departments d ON d.dept_id = p.dept_id 
                                    ORDER BY d.dept_name, p.pos_name');
                $stmt->execute();
                $res = $stmt->get_result();
                $rows = [];
                while ($row = $res->fetch_assoc()) { 
                    $rows[] = $row; 
                }
                json_success(['data' => $rows]);
                break;

            case 'salary_grades':
                $stmt = $conn->prepare('SELECT DISTINCT sg_grade FROM positions ORDER BY sg_grade');
                $stmt->execute();
                $res = $stmt->get_result();
                $grades = [];
                while ($row = $res->fetch_assoc()) { $grades[] = $row['sg_grade']; }
                json_success(['data' => $grades]);
                break;

            case 'salary_grades_full':
                $stmt = $conn->prepare('SELECT * FROM salary_grades ORDER BY salary_grade ASC');
                $stmt->execute();
                $res = $stmt->get_result();
                $rows = [];
                while ($row = $res->fetch_assoc()) { 
                    $rows[] = $row; 
                }
                json_success(['data' => $rows]);
                break;

            default:
                json_error('Unknown resource');
        }
        break;

    case 'update':
        ensure_role([1]);
        switch ($resource) {
            case 'departments':
                $dept_id = intval($_POST['dept_id'] ?? 0);
                $name = trim($_POST['dept_name'] ?? '');
                if (!$dept_id || $name === '') json_error('Invalid input');
                $stmt = $conn->prepare('UPDATE departments SET dept_name=? WHERE dept_id=?');
                $stmt->bind_param('si', $name, $dept_id);
                $stmt->execute();
                $actor_id = intval($_SESSION['user_id'] ?? 0);
                $ar = 'dept_id=' . $dept_id . ', name=' . $name;
                $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_department", "Company", ?, NOW())');
                if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
                json_success();
                break;

            case 'positions':
                $pos_id = intval($_POST['pos_id'] ?? 0);
                $pos_name = trim($_POST['pos_name'] ?? '');
                $dept_id = intval($_POST['dept_id'] ?? 0);
                $sg_grade = intval($_POST['sg_grade'] ?? 0);
                if (!$pos_id || $pos_name === '') json_error('Invalid input');
                $stmt = $conn->prepare('UPDATE positions SET pos_name=?, dept_id=?, sg_grade=? WHERE pos_id=?');
                $stmt->bind_param('siii', $pos_name, $dept_id, $sg_grade, $pos_id);
                $stmt->execute();
                $actor_id = intval($_SESSION['user_id'] ?? 0);
                $ar = 'pos_id=' . $pos_id . ', name=' . $pos_name . ', dept_id=' . $dept_id . ', sg_grade=' . $sg_grade;
                $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_position", "Company", ?, NOW())');
                if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
                json_success();
                break;

            case 'salary_structures':
                $pos_id = intval($_POST['pos_id'] ?? 0);
                $basic_pay = intval($_POST['basic_pay'] ?? 0);
                if (!$pos_id) json_error('Invalid input');
                $stmt = $conn->prepare('UPDATE salary_structure SET basic_pay=? WHERE pos_id=?');
                $stmt->bind_param('ii', $basic_pay, $pos_id);
                $stmt->execute();
                $actor_id = intval($_SESSION['user_id'] ?? 0);
                $ar = 'pos_id=' . $pos_id . ', basic_pay=' . $basic_pay;
                $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_salary_structure", "Company", ?, NOW())');
                if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
                json_success();
                break;

            case 'salary_grades':
                $salary_grade = intval($_POST['salary_grade'] ?? 0);
                $s1 = intval($_POST['step_1'] ?? 0);
                $s2 = intval($_POST['step_2'] ?? 0);
                $s3 = intval($_POST['step_3'] ?? 0);
                $s4 = intval($_POST['step_4'] ?? 0);
                $s5 = intval($_POST['step_5'] ?? 0);
                $s6 = intval($_POST['step_6'] ?? 0);
                $s7 = intval($_POST['step_7'] ?? 0);
                $s8 = intval($_POST['step_8'] ?? 0);
                if (!$salary_grade) json_error('Invalid input');
                $stmt = $conn->prepare('UPDATE salary_grades SET step_1=?, step_2=?, step_3=?, step_4=?, step_5=?, step_6=?, step_7=?, step_8=? WHERE salary_grade=?');
                $stmt->bind_param('iiiiiiiii', $s1, $s2, $s3, $s4, $s5, $s6, $s7, $s8, $salary_grade);
                $stmt->execute();
                $actor_id = intval($_SESSION['user_id'] ?? 0);
                $ar = 'salary_grade=' . $salary_grade;
                $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_salary_grade", "Company", ?, NOW())');
                if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
                json_success();
                break;

            default:
                json_error('Unknown resource');
        }
        break;

    default:
        json_error('Unknown action');
}
