<?php

include_once __DIR__ . '/../init.php';
// sql helpers are auto-loaded via init.php

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listSchedules':
        require_login();
        $rows = select_many('SELECT schedule_id,name,mon,tue,wed,thu,fri,sat,sun FROM work_schedules ORDER BY name');
        json_success(['data' => $rows]);
        break;

    case 'createSchedule':
        ensure_role([1]);
        $name = trim($_POST['name'] ?? '');
        $mon = intval($_POST['mon'] ?? 1);
        $tue = intval($_POST['tue'] ?? 1);
        $wed = intval($_POST['wed'] ?? 1);
        $thu = intval($_POST['thu'] ?? 1);
        $fri = intval($_POST['fri'] ?? 1);
        $sat = intval($_POST['sat'] ?? 0);
        $sun = intval($_POST['sun'] ?? 0);
        if ($name === '') json_error('Invalid name');
        $maxIdRow = select('SELECT IFNULL(MAX(schedule_id),0) AS mid FROM work_schedules');
        $newId = intval(($maxIdRow['mid'] ?? 0)) + 1;
        insert('INSERT INTO work_schedules (schedule_id,name,mon,tue,wed,thu,fri,sat,sun) VALUES (?,?,?,?,?,?,?,?,?)', 'isiiiiiii', $newId, $name, $mon, $tue, $wed, $thu, $fri, $sat, $sun);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'schedule_id=' . $newId . ', name=' . $name;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "create_schedule", "Work Schedules", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['schedule_id' => $newId]);
        break;

    case 'updateSchedule':
        ensure_role([1]);
        $sid = intval($_POST['schedule_id'] ?? 0);
        if (!$sid) json_error('Missing schedule_id');
        $name = trim($_POST['name'] ?? '');
        $mon = intval($_POST['mon'] ?? 1);
        $tue = intval($_POST['tue'] ?? 1);
        $wed = intval($_POST['wed'] ?? 1);
        $thu = intval($_POST['thu'] ?? 1);
        $fri = intval($_POST['fri'] ?? 1);
        $sat = intval($_POST['sat'] ?? 0);
        $sun = intval($_POST['sun'] ?? 0);
        update('UPDATE work_schedules SET name=?, mon=?, tue=?, wed=?, thu=?, fri=?, sat=?, sun=? WHERE schedule_id=?', 'siiiiiiii', $name, $mon, $tue, $wed, $thu, $fri, $sat, $sun, $sid);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'schedule_id=' . $sid . ', name=' . $name;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_schedule", "Work Schedules", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['updated' => 1]);
        break;

    case 'deleteSchedule':
        ensure_role([1]);
        $sid = intval($_POST['schedule_id'] ?? 0);
        if (!$sid) json_error('Missing schedule_id');
        $inUse = select('SELECT COUNT(*) AS c FROM employee_work_schedules WHERE schedule_id=?', 'i', $sid);
        if (intval($inUse['c'] ?? 0) > 0) json_error('Schedule in use');
        $stmt = $conn->prepare('DELETE FROM work_schedules WHERE schedule_id=?');
        if ($stmt) { $stmt->bind_param('i', $sid); $stmt->execute(); }
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'schedule_id=' . $sid;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "delete_schedule", "Work Schedules", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success();
        break;

    case 'listAssignments':
        require_login();
        $emp_id = intval($_GET['emp_id'] ?? $_POST['emp_id'] ?? 0);
        if ($emp_id) {
            $rows = select_many('SELECT ews_id,emp_id,schedule_id,effective_from,effective_to FROM employee_work_schedules WHERE emp_id=? ORDER BY effective_from DESC', 'i', $emp_id);
            json_success(['data' => $rows]);
        } else {
            $rows = select_many('SELECT e.user_name,ews.emp_id,ews.schedule_id,ews.effective_from,ews.effective_to,ws.name FROM employee_work_schedules ews JOIN user_table e ON e.user_id=ews.emp_id JOIN work_schedules ws ON ws.schedule_id=ews.schedule_id ORDER BY ews.effective_from DESC');
            json_success(['data' => $rows]);
        }
        break;

    case 'assignSchedule':
        ensure_role([1]);
        $emp_id = intval($_POST['emp_id'] ?? 0);
        $schedule_id = intval($_POST['schedule_id'] ?? 0);
        $effective_from = $_POST['effective_from'] ?? null;
        $effective_to = $_POST['effective_to'] ?? null;
        if (!$emp_id || !$schedule_id || !$effective_from) json_error('Missing fields');
        $maxIdRow = select('SELECT IFNULL(MAX(ews_id),0) AS mid FROM employee_work_schedules');
        $newId = intval(($maxIdRow['mid'] ?? 0)) + 1;
        insert('INSERT INTO employee_work_schedules (ews_id,emp_id,schedule_id,effective_from,effective_to) VALUES (?,?,?,?,?)', 'iiiss', $newId, $emp_id, $schedule_id, $effective_from, $effective_to);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'emp_id=' . $emp_id . ', schedule_id=' . $schedule_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "assign_schedule", "Work Schedules", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['ews_id' => $newId]);
        break;

    case 'revokeAssignment':
        ensure_role([1]);
        $ews_id = intval($_POST['ews_id'] ?? 0);
        if (!$ews_id) json_error('Missing ews_id');
        $stmt = $conn->prepare('DELETE FROM employee_work_schedules WHERE ews_id=?');
        if ($stmt) { $stmt->bind_param('i', $ews_id); $stmt->execute(); }
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'ews_id=' . $ews_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "revoke_schedule", "Work Schedules", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success();
        break;

    default:
        json_error('Unknown action');
}
