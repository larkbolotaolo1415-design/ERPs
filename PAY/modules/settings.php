<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        ensure_role([1]);
        $stmt = $conn->prepare('SELECT setting_id,setting_name,value,description FROM settings_table ORDER BY setting_id ASC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'update':
        ensure_role([1]);
        $id = intval($_POST['setting_id'] ?? 0);
        $name = trim($_POST['setting_name'] ?? '');
        $value = trim($_POST['value'] ?? '');
        if ($id) {
            $stmt = $conn->prepare('UPDATE settings_table SET value=? WHERE setting_id=?');
            $stmt->bind_param('si', $value, $id);
            $stmt->execute();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'setting_id=' . $id . ', value=' . $value;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_setting", "Security Controls", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success();
        } else if ($name !== '') {
            $stmt = $conn->prepare('UPDATE settings_table SET value=? WHERE setting_name=?');
            $stmt->bind_param('ss', $value, $name);
            $stmt->execute();
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'setting_name=' . $name . ', value=' . $value;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_setting", "Security Controls", ?, NOW())');
            if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
            json_success();
        } else {
            json_error('Invalid input');
        }
        break;

    default:
        json_error('Unknown action');
}