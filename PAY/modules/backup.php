<?php
include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');
require_login();

$action = $_GET['action'] ?? $_POST['action'] ?? 'export';

function table_rows($conn, $sql) {
    $res = $conn->query($sql);
    $rows = [];
    if ($res) { while ($row = $res->fetch_assoc()) { $rows[] = $row; } }
    return $rows;
}

function ensure_backup_table($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS backup_snapshots (
        backup_id INT AUTO_INCREMENT PRIMARY KEY,
        file_name VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        created_by INT NOT NULL,
        size_bytes INT NOT NULL,
        data_json LONGTEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
}

function ensure_schedule_table($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS backup_schedule (
        schedule_id INT PRIMARY KEY DEFAULT 1,
        enabled TINYINT(1) NOT NULL DEFAULT 0,
        frequency ENUM('hourly','daily','weekly') NOT NULL DEFAULT 'daily',
        run_time VARCHAR(5) NOT NULL DEFAULT '22:00',
        next_run DATETIME NULL,
        last_run DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    $conn->query("INSERT IGNORE INTO backup_schedule (schedule_id, enabled, frequency, run_time, next_run, last_run) VALUES (1, 0, 'daily', '22:00', NULL, NULL)");
}

function sql_quote($conn, $v) {
    if ($v === null) return 'NULL';
    return "'" . $conn->real_escape_string($v) . "'";
}

function generate_sql_dump($conn) {
    $out = "SET FOREIGN_KEY_CHECKS=0;\n";
    $res = $conn->query('SHOW TABLES');
    $tables = [];
    if ($res) { while ($r = $res->fetch_array()) { $tables[] = $r[0]; } }
    foreach ($tables as $t) {
        $rc = $conn->query("SHOW CREATE TABLE `" . $conn->real_escape_string($t) . "`");
        $cr = $rc && $rc->num_rows ? $rc->fetch_assoc() : null;
        $create = $cr ? ($cr['Create Table'] ?? '') : '';
        if ($create !== '') {
            $out .= "DROP TABLE IF EXISTS `" . $t . "`;\n" . $create . ";\n";
        }
        $rd = $conn->query("SELECT * FROM `" . $conn->real_escape_string($t) . "`");
        if ($rd) {
            while ($row = $rd->fetch_assoc()) {
                $cols = array_keys($row);
                $vals = [];
                foreach ($cols as $c) { $vals[] = sql_quote($conn, $row[$c]); }
                $out .= "INSERT INTO `" . $t . "` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n";
            }
        }
    }
    $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $out;
}

if ($action === 'export') {
    ensure_backup_table($conn);
    $sql = generate_sql_dump($conn);
    $actor_id = intval($_SESSION['user_id'] ?? 0);
    $fname = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
    $size = strlen($sql);
    $stmt = $conn->prepare('INSERT INTO backup_snapshots (file_name, created_at, created_by, size_bytes, data_json) VALUES (?, NOW(), ?, ?, ?)');
    if ($stmt) { $stmt->bind_param('siis', $fname, $actor_id, $size, $sql); $stmt->execute(); }
    json_success(['file_name' => $fname]);
    exit;
}

if ($action === 'list') {
    ensure_backup_table($conn);
    ensure_schedule_table($conn);
    $stmt = $conn->prepare('SELECT b.backup_id, b.file_name, b.created_at, b.size_bytes, u.user_name FROM backup_snapshots b LEFT JOIN user_table u ON u.user_id=b.created_by ORDER BY b.created_at DESC');
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    $sch = $conn->query("SELECT enabled, frequency, run_time, next_run, last_run FROM backup_schedule WHERE schedule_id=1");
    $meta = $sch && $sch->num_rows ? $sch->fetch_assoc() : null;
    json_success(['data' => $rows, 'schedule' => $meta]);
    exit;
}

if ($action === 'download') {
    ensure_backup_table($conn);
    $id = intval($_GET['backup_id'] ?? $_POST['backup_id'] ?? 0);
    if (!$id) json_error('Invalid backup_id');
    $stmt = $conn->prepare('SELECT file_name, data_json FROM backup_snapshots WHERE backup_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) json_error('Not found', 404);
    json_success(['file_name' => $row['file_name'], 'data' => $row['data_json']]);
    exit;
}

if ($action === 'restore') {
    ensure_role([1]);
    ensure_backup_table($conn);
    ensure_schedule_table($conn);
    $id = intval($_POST['backup_id'] ?? ($_GET['backup_id'] ?? 0));
    if (!$id) json_error('Invalid backup_id');
    $stmt = $conn->prepare('SELECT data_json FROM backup_snapshots WHERE backup_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) json_error('Not found', 404);
    $json = $row['data_json'];
    $payload = json_decode($json, true);
    if (!$payload) json_error('Invalid format');
    $data = isset($payload['data']) ? $payload['data'] : $payload;
    if (!is_array($data)) json_error('Invalid format');
    $conn->begin_transaction();
    try {
        if (isset($data['departments'])) {
            foreach ($data['departments'] as $r) {
                if (array_key_exists('head_emp_id', $r) || array_key_exists('num_of_emps', $r)) {
                    $hasHead = array_key_exists('head_emp_id', $r) && $r['head_emp_id'] !== null;
                    $hasNum = array_key_exists('num_of_emps', $r) && $r['num_of_emps'] !== null;
                    if ($hasHead && $hasNum) {
                        $head = intval($r['head_emp_id']);
                        $num = intval($r['num_of_emps']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, head_emp_id, num_of_emps) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), head_emp_id=VALUES(head_emp_id), num_of_emps=VALUES(num_of_emps)');
                        $stmt1->bind_param('isii', $r['dept_id'], $r['dept_name'], $head, $num);
                        $stmt1->execute();
                    } else if ($hasHead && !$hasNum) {
                        $head = intval($r['head_emp_id']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, head_emp_id) VALUES (?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), head_emp_id=VALUES(head_emp_id)');
                        $stmt1->bind_param('isi', $r['dept_id'], $r['dept_name'], $head);
                        $stmt1->execute();
                    } else if (!$hasHead && $hasNum) {
                        $num = intval($r['num_of_emps']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, num_of_emps) VALUES (?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), num_of_emps=VALUES(num_of_emps)');
                        $stmt1->bind_param('isi', $r['dept_id'], $r['dept_name'], $num);
                        $stmt1->execute();
                    } else {
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name) VALUES (?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name)');
                        $stmt1->bind_param('is', $r['dept_id'], $r['dept_name']);
                        $stmt1->execute();
                    }
                } else {
                    $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name) VALUES (?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name)');
                    $stmt1->bind_param('is', $r['dept_id'], $r['dept_name']);
                    $stmt1->execute();
                }
            }
        }
        if (isset($data['positions'])) {
            foreach ($data['positions'] as $r) {
                $stmt2 = $conn->prepare('INSERT INTO positions (pos_id, pos_name, dept_id, sg_grade) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE pos_name=VALUES(pos_name), dept_id=VALUES(dept_id), sg_grade=VALUES(sg_grade)');
                $stmt2->bind_param('isii', $r['pos_id'], $r['pos_name'], $r['dept_id'], $r['sg_grade']);
                $stmt2->execute();
            }
        }
        if (isset($data['salary_structure'])) {
            foreach ($data['salary_structure'] as $r) {
                $sid = isset($r['sal_struct_id']) ? intval($r['sal_struct_id']) : (isset($r['sal_struct']) ? intval($r['sal_struct']) : (isset($r['pos_id']) ? intval($r['pos_id']) : 0));
                $pid = intval($r['pos_id'] ?? 0);
                $bp = intval($r['basic_pay'] ?? 0);
                if ($sid === 0 || $pid === 0) { continue; }
                $stmt3 = $conn->prepare('REPLACE INTO salary_structure (sal_struct_id, pos_id, basic_pay) VALUES (?,?,?)');
                $stmt3->bind_param('iii', $sid, $pid, $bp);
                $stmt3->execute();
            }
        }
        if (isset($data['deductions'])) {
            foreach ($data['deductions'] as $r) {
                $did = intval($r['deduct_id'] ?? 0);
                if ($did === 0) { continue; }
                $name = $r['deduct_name'] ?? '';
                $type = $r['type'] ?? null;
                if ($type !== null && !in_array($type, ['percentage','fixed','custom_formula'])) { $type = null; }
                $rof = $r['rate_or_formula'] ?? null;
                $min = isset($r['minimum']) ? intval($r['minimum']) : null;
                $max = isset($r['maximum']) ? intval($r['maximum']) : null;
                $status = $r['status'] ?? 'active';
                $stmt4 = $conn->prepare('REPLACE INTO deductions (deduct_id, deduct_name, type, rate_or_formula, minimum, maximum, status) VALUES (?,?,?,?,?,?,?)');
                $stmt4->bind_param('isssiis', $did, $name, $type, $rof, $min, $max, $status);
                $stmt4->execute();
            }
        }
        if (isset($data['taxes'])) {
            foreach ($data['taxes'] as $r) {
                $tid = intval($r['tax_id'] ?? 0);
                if ($tid === 0) { continue; }
                $rf = intval($r['range_from'] ?? 0);
                $rt = intval($r['range_to'] ?? 0);
                $roe = intval($r['rate_on_excess'] ?? 0);
                $add = intval($r['additional_amount'] ?? 0);
                $stmt5 = $conn->prepare('REPLACE INTO taxes (tax_id, range_from, range_to, rate_on_excess, additional_amount) VALUES (?,?,?,?,?)');
                $stmt5->bind_param('iiiii', $tid, $rf, $rt, $roe, $add);
                $stmt5->execute();
            }
        }
        if (isset($data['benefits'])) {
            foreach ($data['benefits'] as $r) {
                $stmt6 = $conn->prepare('REPLACE INTO benefits (ben_id, ben_name, type, eligibility, status) VALUES (?,?,?,?,?)');
                $stmt6->bind_param('issss', $r['ben_id'], $r['ben_name'], $r['type'], $r['eligibility'], $r['status']);
                $stmt6->execute();
            }
        }
        $deptIds = [];
        if (isset($data['departments']) && is_array($data['departments'])) { foreach ($data['departments'] as $r) { if (isset($r['dept_id'])) $deptIds[] = intval($r['dept_id']); } }
        $posIds = [];
        if (isset($data['positions']) && is_array($data['positions'])) { foreach ($data['positions'] as $r) { if (isset($r['pos_id'])) $posIds[] = intval($r['pos_id']); } }
        $salPosIds = [];
        if (isset($data['salary_structure']) && is_array($data['salary_structure'])) { foreach ($data['salary_structure'] as $r) { if (isset($r['pos_id'])) $salPosIds[] = intval($r['pos_id']); } }
        $dedIds = [];
        if (isset($data['deductions']) && is_array($data['deductions'])) { foreach ($data['deductions'] as $r) { if (isset($r['deduct_id'])) $dedIds[] = intval($r['deduct_id']); } }
        $taxIds = [];
        if (isset($data['taxes']) && is_array($data['taxes'])) { foreach ($data['taxes'] as $r) { if (isset($r['tax_id'])) $taxIds[] = intval($r['tax_id']); } }
        $benIds = [];
        if (isset($data['benefits']) && is_array($data['benefits'])) { foreach ($data['benefits'] as $r) { if (isset($r['ben_id'])) $benIds[] = intval($r['ben_id']); } }

        if (count($salPosIds) > 0) { $in = implode(',', $salPosIds); $conn->query("DELETE FROM salary_structure WHERE pos_id NOT IN ($in)"); }
        if (count($posIds) > 0) { $in = implode(',', $posIds); $conn->query("DELETE FROM positions WHERE pos_id NOT IN ($in)"); }
        if (count($deptIds) > 0) { $in = implode(',', $deptIds); $conn->query("DELETE FROM departments WHERE dept_id NOT IN ($in)"); }
        if (count($dedIds) > 0) { $in = implode(',', $dedIds); $conn->query("DELETE FROM deductions WHERE deduct_id NOT IN ($in)"); }
        if (count($taxIds) > 0) { $in = implode(',', $taxIds); $conn->query("DELETE FROM taxes WHERE tax_id NOT IN ($in)"); }
        if (count($benIds) > 0) { $in = implode(',', $benIds); $conn->query("DELETE FROM benefits WHERE ben_id NOT IN ($in)"); }
        $conn->commit();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $counts = [];
        foreach (['departments','positions','salary_structure','deductions','taxes','benefits'] as $k) { $counts[$k] = isset($data[$k]) && is_array($data[$k]) ? count($data[$k]) : 0; }
        $ar = 'restore=' . json_encode($counts);
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "restore_backup_data", "Backup", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['restored' => true]);
    } catch (Throwable $e) {
        $conn->rollback();
        json_error('Restore failed: ' . $e->getMessage());
    }
    exit;
}

if ($action === 'delete') {
    ensure_role([1]);
    ensure_backup_table($conn);
    ensure_schedule_table($conn);
    $id = intval($_POST['backup_id'] ?? 0);
    if (!$id) json_error('Invalid backup_id');
    $stmt = $conn->prepare('DELETE FROM backup_snapshots WHERE backup_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    json_success();
    exit;
}

if ($action === 'schedule_get') {
    ensure_schedule_table($conn);
    $res = $conn->query("SELECT enabled, frequency, run_time, next_run, last_run FROM backup_schedule WHERE schedule_id=1");
    $row = $res && $res->num_rows ? $res->fetch_assoc() : null;
    json_success(['data' => $row]);
    exit;
}

if ($action === 'schedule_set') {
    ensure_role([1]);
    ensure_schedule_table($conn);
    $enabled = intval($_POST['enabled'] ?? 0) ? 1 : 0;
    $frequency = $_POST['frequency'] ?? 'daily';
    if (!in_array($frequency, ['hourly','daily','weekly'])) $frequency = 'daily';
    $run_time = $_POST['run_time'] ?? '22:00';
    if (!preg_match('/^\d{2}:\d{2}$/', $run_time)) $run_time = '22:00';
    $now = new DateTime();
    $next = null;
    if ($enabled) {
        $parts = explode(':', $run_time);
        $nextDt = new DateTime();
        $nextDt->setTime(intval($parts[0]), intval($parts[1]));
        if ($nextDt <= $now) {
            if ($frequency === 'hourly') { $nextDt = (clone $now)->modify('+1 hour'); }
            else if ($frequency === 'daily') { $nextDt = (clone $now)->modify('+1 day'); $nextDt->setTime(intval($parts[0]), intval($parts[1])); }
            else { $nextDt = (clone $now)->modify('+1 week'); $nextDt->setTime(intval($parts[0]), intval($parts[1])); }
        }
        $next = $nextDt->format('Y-m-d H:i:s');
    }
    $stmt = $conn->prepare('UPDATE backup_schedule SET enabled=?, frequency=?, run_time=?, next_run=? WHERE schedule_id=1');
    $stmt->bind_param('isss', $enabled, $frequency, $run_time, $next);
    $stmt->execute();
    json_success(['enabled' => $enabled, 'frequency' => $frequency, 'run_time' => $run_time, 'next_run' => $next]);
    exit;
}

if ($action === 'run_scheduled') {
    ensure_schedule_table($conn);
    $res = $conn->query("SELECT enabled, frequency, run_time, next_run FROM backup_schedule WHERE schedule_id=1");
    $row = $res && $res->num_rows ? $res->fetch_assoc() : null;
    if (!$row || intval($row['enabled']) !== 1) { json_success(['ran' => false]); exit; }
    $now = new DateTime();
    $next_run = $row['next_run'] ? new DateTime($row['next_run']) : null;
    if ($next_run && $now < $next_run) { json_success(['ran' => false, 'next_run' => $row['next_run']]); exit; }
    ensure_backup_table($conn);
    $sql = generate_sql_dump($conn);
    $actor_id = intval($_SESSION['user_id'] ?? 0);
    $fname = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
    $size = strlen($sql);
    $stmt = $conn->prepare('INSERT INTO backup_snapshots (file_name, created_at, created_by, size_bytes, data_json) VALUES (?, NOW(), ?, ?, ?)');
    if ($stmt) { $stmt->bind_param('siis', $fname, $actor_id, $size, $sql); $stmt->execute(); }
    $parts = explode(':', $row['run_time']);
    $nextDt = new DateTime();
    if ($row['frequency'] === 'hourly') { $nextDt = (clone $now)->modify('+1 hour'); }
    else if ($row['frequency'] === 'daily') { $nextDt = (clone $now)->modify('+1 day'); $nextDt->setTime(intval($parts[0]), intval($parts[1])); }
    else { $nextDt = (clone $now)->modify('+1 week'); $nextDt->setTime(intval($parts[0]), intval($parts[1])); }
    $stmt2 = $conn->prepare('UPDATE backup_schedule SET last_run=NOW(), next_run=? WHERE schedule_id=1');
    $nr = $nextDt->format('Y-m-d H:i:s');
    $stmt2->bind_param('s', $nr);
    $stmt2->execute();
    json_success(['ran' => true, 'file_name' => $fname, 'next_run' => $nr]);
    exit;
}

if ($action === 'import') {
    ensure_role([1]);
    $json = $_POST['data'] ?? '';
    if ($json === '') json_error('No data');
    $payload = json_decode($json, true);
    if (!$payload || !isset($payload['data'])) json_error('Invalid format');
    $data = $payload['data'];
    $conn->begin_transaction();
    try {
        if (isset($data['departments'])) {
            foreach ($data['departments'] as $r) {
                if (array_key_exists('head_emp_id', $r) || array_key_exists('num_of_emps', $r)) {
                    $hasHead = array_key_exists('head_emp_id', $r) && $r['head_emp_id'] !== null;
                    $hasNum = array_key_exists('num_of_emps', $r) && $r['num_of_emps'] !== null;
                    if ($hasHead && $hasNum) {
                        $head = intval($r['head_emp_id']);
                        $num = intval($r['num_of_emps']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, head_emp_id, num_of_emps) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), head_emp_id=VALUES(head_emp_id), num_of_emps=VALUES(num_of_emps)');
                        $stmt1->bind_param('isii', $r['dept_id'], $r['dept_name'], $head, $num);
                        $stmt1->execute();
                    } else if ($hasHead && !$hasNum) {
                        $head = intval($r['head_emp_id']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, head_emp_id) VALUES (?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), head_emp_id=VALUES(head_emp_id)');
                        $stmt1->bind_param('isi', $r['dept_id'], $r['dept_name'], $head);
                        $stmt1->execute();
                    } else if (!$hasHead && $hasNum) {
                        $num = intval($r['num_of_emps']);
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name, num_of_emps) VALUES (?,?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name), num_of_emps=VALUES(num_of_emps)');
                        $stmt1->bind_param('isi', $r['dept_id'], $r['dept_name'], $num);
                        $stmt1->execute();
                    } else {
                        $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name) VALUES (?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name)');
                        $stmt1->bind_param('is', $r['dept_id'], $r['dept_name']);
                        $stmt1->execute();
                    }
                } else {
                    $stmt1 = $conn->prepare('INSERT INTO departments (dept_id, dept_name) VALUES (?,?) ON DUPLICATE KEY UPDATE dept_name=VALUES(dept_name)');
                    $stmt1->bind_param('is', $r['dept_id'], $r['dept_name']);
                    $stmt1->execute();
                }
            }
        }
        if (isset($data['positions'])) {
            foreach ($data['positions'] as $r) {
                $stmt2 = $conn->prepare('INSERT INTO positions (pos_id, pos_name, dept_id, sg_grade) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE pos_name=VALUES(pos_name), dept_id=VALUES(dept_id), sg_grade=VALUES(sg_grade)');
                $stmt2->bind_param('isii', $r['pos_id'], $r['pos_name'], $r['dept_id'], $r['sg_grade']);
                $stmt2->execute();
            }
        }
        if (isset($data['salary_structure'])) {
            foreach ($data['salary_structure'] as $r) {
                $sid = isset($r['sal_struct_id']) ? intval($r['sal_struct_id']) : (isset($r['sal_struct']) ? intval($r['sal_struct']) : (isset($r['pos_id']) ? intval($r['pos_id']) : 0));
                $pid = intval($r['pos_id'] ?? 0);
                $bp = intval($r['basic_pay'] ?? 0);
                if ($sid === 0 || $pid === 0) { continue; }
                $stmt3 = $conn->prepare('REPLACE INTO salary_structure (sal_struct_id, pos_id, basic_pay) VALUES (?,?,?)');
                $stmt3->bind_param('iii', $sid, $pid, $bp);
                $stmt3->execute();
            }
        }
        if (isset($data['deductions'])) {
            foreach ($data['deductions'] as $r) {
                $did = intval($r['deduct_id'] ?? 0);
                if ($did === 0) { continue; }
                $name = $r['deduct_name'] ?? '';
                $type = $r['type'] ?? null;
                if ($type !== null && !in_array($type, ['percentage','fixed','custom_formula'])) { $type = null; }
                $rof = $r['rate_or_formula'] ?? null;
                $min = isset($r['minimum']) ? intval($r['minimum']) : null;
                $max = isset($r['maximum']) ? intval($r['maximum']) : null;
                $status = $r['status'] ?? 'active';
                $stmt4 = $conn->prepare('REPLACE INTO deductions (deduct_id, deduct_name, type, rate_or_formula, minimum, maximum, status) VALUES (?,?,?,?,?,?,?)');
                $stmt4->bind_param('isssiis', $did, $name, $type, $rof, $min, $max, $status);
                $stmt4->execute();
            }
        }
        if (isset($data['taxes'])) {
            foreach ($data['taxes'] as $r) {
                $tid = intval($r['tax_id'] ?? 0);
                if ($tid === 0) { continue; }
                $rf = intval($r['range_from'] ?? 0);
                $rt = intval($r['range_to'] ?? 0);
                $roe = intval($r['rate_on_excess'] ?? 0);
                $add = intval($r['additional_amount'] ?? 0);
                $stmt5 = $conn->prepare('REPLACE INTO taxes (tax_id, range_from, range_to, rate_on_excess, additional_amount) VALUES (?,?,?,?,?)');
                $stmt5->bind_param('iiiii', $tid, $rf, $rt, $roe, $add);
                $stmt5->execute();
            }
        }
        if (isset($data['benefits'])) {
            foreach ($data['benefits'] as $r) {
                $stmt6 = $conn->prepare('REPLACE INTO benefits (ben_id, ben_name, type, eligibility, status) VALUES (?,?,?,?,?)');
                $stmt6->bind_param('issss', $r['ben_id'], $r['ben_name'], $r['type'], $r['eligibility'], $r['status']);
                $stmt6->execute();
            }
        }
        $deptIds = [];
        if (isset($data['departments']) && is_array($data['departments'])) { foreach ($data['departments'] as $r) { if (isset($r['dept_id'])) $deptIds[] = intval($r['dept_id']); } }
        $posIds = [];
        if (isset($data['positions']) && is_array($data['positions'])) { foreach ($data['positions'] as $r) { if (isset($r['pos_id'])) $posIds[] = intval($r['pos_id']); } }
        $salPosIds = [];
        if (isset($data['salary_structure']) && is_array($data['salary_structure'])) { foreach ($data['salary_structure'] as $r) { if (isset($r['pos_id'])) $salPosIds[] = intval($r['pos_id']); } }
        $dedIds = [];
        if (isset($data['deductions']) && is_array($data['deductions'])) { foreach ($data['deductions'] as $r) { if (isset($r['deduct_id'])) $dedIds[] = intval($r['deduct_id']); } }
        $taxIds = [];
        if (isset($data['taxes']) && is_array($data['taxes'])) { foreach ($data['taxes'] as $r) { if (isset($r['tax_id'])) $taxIds[] = intval($r['tax_id']); } }
        $benIds = [];
        if (isset($data['benefits']) && is_array($data['benefits'])) { foreach ($data['benefits'] as $r) { if (isset($r['ben_id'])) $benIds[] = intval($r['ben_id']); } }

        if (count($salPosIds) > 0) { $in = implode(',', $salPosIds); $conn->query("DELETE FROM salary_structure WHERE pos_id NOT IN ($in)"); }
        if (count($posIds) > 0) { $in = implode(',', $posIds); $conn->query("DELETE FROM positions WHERE pos_id NOT IN ($in)"); }
        if (count($deptIds) > 0) { $in = implode(',', $deptIds); $conn->query("DELETE FROM departments WHERE dept_id NOT IN ($in)"); }
        if (count($dedIds) > 0) { $in = implode(',', $dedIds); $conn->query("DELETE FROM deductions WHERE deduct_id NOT IN ($in)"); }
        if (count($taxIds) > 0) { $in = implode(',', $taxIds); $conn->query("DELETE FROM taxes WHERE tax_id NOT IN ($in)"); }
        if (count($benIds) > 0) { $in = implode(',', $benIds); $conn->query("DELETE FROM benefits WHERE ben_id NOT IN ($in)"); }
        $conn->commit();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $counts = [];
        foreach (['departments','positions','salary_structure','deductions','taxes','benefits'] as $k) { $counts[$k] = isset($data[$k]) && is_array($data[$k]) ? count($data[$k]) : 0; }
        $ar = 'import=' . json_encode($counts);
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "import_backup_data", "Backup", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); }
        json_success(['imported' => true]);
    } catch (Throwable $e) {
        $conn->rollback();
        json_error('Import failed');
    }
    exit;
}

json_error('Unsupported action');