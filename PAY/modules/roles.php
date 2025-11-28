<?php

include_once __DIR__ . '/../init.php';
require_once INCLUDES_PATH . '/functions/auth_functions.php';
require_once INCLUDES_PATH . '/functions/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    json_error('Invalid request method', 405);
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_login();
        $stmt = $conn->prepare('SELECT role_id, role_name FROM roles_table ORDER BY role_id ASC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) { $rows[] = $row; }
        json_success(['data' => $rows]);
        break;

    case 'pagesCatalog':
        require_login();
        $stmt = $conn->prepare('SELECT page_key, display_name, page_link, group_name FROM pages_catalog ORDER BY group_name, display_name');
        if ($stmt && $stmt->execute()) {
            $res = $stmt->get_result();
            $rows = [];
            while ($row = $res->fetch_assoc()) { $rows[] = ['key' => $row['page_key'], 'name' => $row['display_name'], 'link' => $row['page_link'], 'group' => $row['group_name']]; }
            json_success(['data' => $rows]);
        } else {
            json_error('pages_catalog not available');
        }
        break;

    case 'getPermissions':
        require_login();
        $role_id = intval($_GET['role_id'] ?? $_POST['role_id'] ?? 0);
        if (!$role_id) json_error('Invalid role_id');
        $stmt = $conn->prepare('SELECT page_key, can_access FROM role_page_permissions WHERE role_id=?');
        $stmt->bind_param('i', $role_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $perms = [];
        while ($row = $res->fetch_assoc()) { $perms[$row['page_key']] = intval($row['can_access']); }
        json_success(['data' => $perms]);
        break;

    case 'setPermissions':
        ensure_role([1]);
        $role_id = intval($_POST['role_id'] ?? 0);
        $payload = json_decode($_POST['payload'] ?? '[]', true);
        if (!$role_id || !is_array($payload)) json_error('Invalid input');
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete existing permissions for this role
            $stmtDel = $conn->prepare('DELETE FROM role_page_permissions WHERE role_id=?');
            $stmtDel->bind_param('i', $role_id);
            $stmtDel->execute();
            
            // Insert new permissions
            $stmt = $conn->prepare('INSERT INTO role_page_permissions (role_id, page_key, can_access) VALUES (?, ?, ?)');
            $changed = 0;
            foreach ($payload as $key => $val) {
                $can = intval($val) ? 1 : 0;
                // Only insert if access is granted (1), skip if 0
                if ($can === 1) {
                    $stmt->bind_param('isi', $role_id, $key, $can);
                    $stmt->execute();
                    $changed++;
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Audit log
            $actor_id = intval($_SESSION['user_id'] ?? 0);
            $ar = 'role_id=' . $role_id . ', items=' . $changed;
            $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_role_permissions", "Role Management", ?, NOW())');
            if ($stmtA) { 
                $stmtA->bind_param('is', $actor_id, $ar); 
                $stmtA->execute(); 
            } else { 
                $stmtB = $conn->prepare('INSERT INTO audit_table (user_id, action, timestamp) VALUES (?, "update_role_permissions", NOW())'); 
                if ($stmtB) { 
                    $stmtB->bind_param('i', $actor_id); 
                    $stmtB->execute(); 
                } 
            }
            
            json_success();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            json_error('Failed to update permissions: ' . $e->getMessage());
        }
        break;

    case 'create':
        ensure_role([1]);
        $name = trim($_POST['role_name'] ?? '');
        if ($name === '') json_error('Missing role_name');
        $stmt = $conn->prepare('INSERT INTO roles_table (role_name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $new_id = intval($conn->insert_id);
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'role_id=' . $new_id . ', name=' . $name;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "create_role", "Role Management", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); } else { $stmtB = $conn->prepare('INSERT INTO audit_table (user_id, action, timestamp) VALUES (?, "create_role", NOW())'); if ($stmtB) { $stmtB->bind_param('i', $actor_id); $stmtB->execute(); } }
        json_success(['role_id' => $new_id]);
        break;

    case 'update':
        ensure_role([1]);
        $role_id = intval($_POST['role_id'] ?? 0);
        $name = trim($_POST['role_name'] ?? '');
        if (!$role_id || $name === '') json_error('Invalid input');
        $stmt = $conn->prepare('UPDATE roles_table SET role_name=? WHERE role_id=?');
        $stmt->bind_param('si', $name, $role_id);
        $stmt->execute();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'role_id=' . $role_id . ', name=' . $name;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "update_role", "Role Management", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); } else { $stmtB = $conn->prepare('INSERT INTO audit_table (user_id, action, timestamp) VALUES (?, "update_role", NOW())'); if ($stmtB) { $stmtB->bind_param('i', $actor_id); $stmtB->execute(); } }
        json_success();
        break;

    case 'delete':
        ensure_role([1]);
        $role_id = intval($_POST['role_id'] ?? 0);
        if (!$role_id) json_error('Invalid input');
        $stmt = $conn->prepare('DELETE FROM roles_table WHERE role_id=?');
        $stmt->bind_param('i', $role_id);
        $stmt->execute();
        $actor_id = intval($_SESSION['user_id'] ?? 0);
        $ar = 'role_id=' . $role_id;
        $stmtA = $conn->prepare('INSERT INTO audit_table (user_id, action, module, affected_record, timestamp) VALUES (?, "delete_role", "Role Management", ?, NOW())');
        if ($stmtA) { $stmtA->bind_param('is', $actor_id, $ar); $stmtA->execute(); } else { $stmtB = $conn->prepare('INSERT INTO audit_table (user_id, action, timestamp) VALUES (?, "delete_role", NOW())'); if ($stmtB) { $stmtB->bind_param('i', $actor_id); $stmtB->execute(); } }
        json_success();
        break;

    default:
        json_error('Unknown action');
}