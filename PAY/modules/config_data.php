<?php
include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$resource = $_GET['resource'] ?? $_POST['resource'] ?? '';

require_login();

function fetch_all($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    return $rows;
}

if ($action === 'list') {
    switch ($resource) {
        case 'deductions':
            $stmt = $conn->prepare('SELECT deduct_id, deduct_name, type, rate_or_formula, minimum, maximum, status FROM deductions ORDER BY deduct_name');
            json_success(['data' => fetch_all($stmt)]);
            break;
        case 'taxes':
            $stmt = $conn->prepare('SELECT tax_id, range_from, range_to, rate_on_excess, additional_amount FROM taxes ORDER BY range_from');
            json_success(['data' => fetch_all($stmt)]);
            break;
        case 'benefits':
            $stmt = $conn->prepare('SELECT ben_id, ben_name, type, eligibility, status FROM benefits ORDER BY ben_name');
            json_success(['data' => fetch_all($stmt)]);
            break;
        default:
            json_error('Unknown resource');
    }
    exit;
}

if ($action === 'update') {
    switch ($resource) {
        case 'deductions':
            $id = intval($_POST['deduct_id'] ?? 0);
            $name = trim($_POST['deduct_name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $rof = trim($_POST['rate_or_formula'] ?? '');
            $min = intval($_POST['minimum'] ?? 0);
            $max = intval($_POST['maximum'] ?? 0);
            $status = trim($_POST['status'] ?? 'active');
            $stmt = $conn->prepare('UPDATE deductions SET deduct_name=?, type=?, rate_or_formula=?, minimum=?, maximum=?, status=? WHERE deduct_id=?');
            $stmt->bind_param('sssiisi', $name, $type, $rof, $min, $max, $status, $id);
            $stmt->execute();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'deduct_id=' . $id . ', name=' . $name;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_deduction", "System Configuration", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success(['updated' => $stmt->affected_rows]);
            break;
        case 'taxes':
            $id = intval($_POST['tax_id'] ?? 0);
            $rf = intval($_POST['range_from'] ?? 0);
            $rt = intval($_POST['range_to'] ?? 0);
            $roe = intval($_POST['rate_on_excess'] ?? 0);
            $add = intval($_POST['additional_amount'] ?? 0);
            $stmt = $conn->prepare('UPDATE taxes SET range_from=?, range_to=?, rate_on_excess=?, additional_amount=? WHERE tax_id=?');
            $stmt->bind_param('iiiii', $rf, $rt, $roe, $add, $id);
            $stmt->execute();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'tax_id=' . $id;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_tax", "System Configuration", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success(['updated' => $stmt->affected_rows]);
            break;
        case 'benefits':
            $id = intval($_POST['ben_id'] ?? 0);
            $name = trim($_POST['ben_name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $elig = trim($_POST['eligibility'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $stmt = $conn->prepare('UPDATE benefits SET ben_name=?, type=?, eligibility=?, status=? WHERE ben_id=?');
            $stmt->bind_param('ssssi', $name, $type, $elig, $status, $id);
            $stmt->execute();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'ben_id=' . $id . ', name=' . $name;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_benefit", "System Configuration", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success(['updated' => $stmt->affected_rows]);
            break;
        default:
            json_error('Unknown resource');
    }
    exit;
}

json_error('Unsupported action');