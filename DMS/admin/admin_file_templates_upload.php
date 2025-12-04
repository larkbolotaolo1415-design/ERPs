<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$redirectPath = '../api/file_templates.php';
$redirectTarget = @realpath(__DIR__ . '/../api/file_templates.php');
if ($redirectTarget && !empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = rtrim(str_replace('\\', '/', @realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $normalized = str_replace('\\', '/', $redirectTarget);
    if ($docRoot && strpos($normalized, $docRoot) === 0) {
        $relativePath = substr($normalized, strlen($docRoot));
        $redirectPath = $relativePath === '' ? '/' : $relativePath;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$assignedUserId = isset($_POST['assigned_user_id']) ? (int)$_POST['assigned_user_id'] : 0;
$assignedRole = strtolower(trim($_POST['assigned_user_role'] ?? ''));
$validRoles = ['doctor', 'patient'];

if ($assignedUserId <= 0 || !in_array($assignedRole, $validRoles, true)) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$assigneeStmt = $pdo->prepare('SELECT id, role, user_type_id FROM users WHERE id = ? LIMIT 1');
$assigneeStmt->execute([$assignedUserId]);
$assignee = $assigneeStmt->fetch();

if (!$assignee || strtolower((string)$assignee['role']) !== $assignedRole) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$assignedUserTypeId = (int)($assignee['user_type_id'] ?? 0);
if ($assignedUserTypeId <= 0) {
    $typeStmt = $pdo->prepare('SELECT id FROM user_types WHERE name = ? LIMIT 1');
    $typeStmt->execute([ucfirst($assignedRole)]);
    $typeRow = $typeStmt->fetch();
    $assignedUserTypeId = $typeRow ? (int)$typeRow['id'] : 0;
}

if ($assignedUserTypeId <= 0) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$allowed = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/png',
    'image/jpeg'
];

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$name = trim($_POST['name'] ?? '');
$orig = $_FILES['file']['name'];
if ($name === '') {
    $name = $orig;
}

$tmp = $_FILES['file']['tmp_name'];
$size = (int)$_FILES['file']['size'];

if ($size > 10 * 1024 * 1024) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $tmp);
finfo_close($finfo);

if (!in_array($mime, $allowed, true)) {
    $_SESSION['admin_file_upload_status'] = 'invalid';
    header('Location: ' . $redirectPath);
    exit();
}

$name = trim($name);
$orig = trim($orig);

try {
    $pdo->beginTransaction();

    // Ensure uploads/templates directory exists (file-path storage)
    $baseDir = dirname(__DIR__);
    $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'templates';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    // Sanitize and generate conflict-safe file name
    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $orig);
    if ($safeName === '' || $safeName === '.' || $safeName === '..') {
        $safeName = 'template';
    }
    $uniqueName = uniqid('tpl_', true) . '_' . $safeName;
    $targetPathFs = $uploadDir . DIRECTORY_SEPARATOR . $uniqueName;
    $relativePath = 'uploads/templates/' . $uniqueName;

    if (!move_uploaded_file($tmp, $targetPathFs)) {
        $_SESSION['admin_file_upload_status'] = 'failed';
        header('Location: ' . $redirectPath);
        exit();
    }

    $stmt = $pdo->prepare('
        INSERT INTO files (name, original_filename, mime_type, size, file_path, uploader_id)
        VALUES (?,?,?,?,?,?)
    ');
    $stmt->execute([$name, $orig, $mime, $size, $relativePath, $_SESSION['user_id']]);
    $newId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare('
        INSERT INTO file_permissions (file_id, user_id, user_type_id, can_download)
        VALUES (?,?,?,1)
    ');
    $stmt->execute([$newId, $assignedUserId, $assignedUserTypeId]);

    $stmt = $pdo->prepare('
        INSERT INTO audit_logs (admin_id, action, target_type, target_id, details)
        VALUES (?,?,?,?,?)
    ');
    $stmt->execute([
        $_SESSION['user_id'],
        'upload',
        'file',
        $newId,
        json_encode([
            'name' => $name,
            'orig' => $orig,
            'assigned_user_id' => $assignedUserId,
            'assigned_user_type_id' => $assignedUserTypeId
        ])
    ]);

    $pdo->commit();

    $_SESSION['admin_file_upload_status'] = 'success';
    header('Location: ' . $redirectPath);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['admin_file_upload_status'] = 'failed';
    header('Location: ' . $redirectPath);
    exit();
}
?>
