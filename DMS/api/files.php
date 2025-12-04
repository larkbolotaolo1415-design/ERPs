<?php
require_once __DIR__ . '/../includes/db_connect.php';

// ----------------------
// User must be logged in
// ----------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// ----------------------
// Helper: get user_type_id from legacy roles
// ----------------------
function currentUserTypeId(PDO $pdo): ?int {
    if (!empty($_SESSION['user_type_id'])) {
        return (int)$_SESSION['user_type_id'];
    }
    if (empty($_SESSION['user_role'])) {
        return null;
    }

    $roleKey = strtolower((string)$_SESSION['user_role']);
    $map = [
        'admin' => 'Admin',
        'doctor' => 'Doctor',
        'nurse' => 'Nurse',
        'staff' => 'Staff',
        'manager' => 'Manager',
        'patient' => 'Patient'
    ];
    $name = $map[$roleKey] ?? null;
    if (!$name) {
        $stmt = $pdo->prepare('SELECT user_type_id FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
        $row = $stmt->fetch();
        if ($row && (int)$row['user_type_id'] > 0) {
            $_SESSION['user_type_id'] = (int)$row['user_type_id'];
            return (int)$row['user_type_id'];
        }
        return null;
    }

    $stmt = $pdo->prepare('SELECT id FROM user_types WHERE name = ? LIMIT 1');
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if ($row) {
        $_SESSION['user_type_id'] = (int)$row['id'];
        return (int)$row['id'];
    }
    return null;
}

// ----------------------
// Get file ID from query
// ----------------------
$fileId = (int)($_GET['id'] ?? 0);
if ($fileId <= 0) {
    http_response_code(400);
    exit('Invalid file id');
}

// ----------------------
// Admin flag
// ----------------------
$isAdmin = ($_SESSION['user_role'] ?? null) === 'admin';

// ----------------------
// Load file metadata (without reading BLOB yet)
// ----------------------
$stmt = $pdo->prepare('SELECT id, original_filename, mime_type, size FROM files WHERE id = ?');
$stmt->execute([$fileId]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    exit('File not found');
}

// ----------------------
// Check permission for non-admin users
// ----------------------
$allowed = $isAdmin;
if (!$allowed) {
    $userId = (int)$_SESSION['user_id'];
    $userTypeId = currentUserTypeId($pdo);

    $stmt = $pdo->prepare('SELECT can_download FROM file_permissions WHERE file_id = ? AND user_id = ?');
    $stmt->execute([$fileId, $userId]);
    if ($stmt->fetch()) $allowed = true;

    if (!$allowed && $userTypeId) {
        $stmt = $pdo->prepare('SELECT 1 FROM file_permissions WHERE file_id = ? AND user_type_id = ?');
        $stmt->execute([$fileId, $userTypeId]);
        if ($stmt->fetch()) $allowed = true;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit('Forbidden');
}

// ----------------------
// Determine if download requested
// ----------------------
$isDownload = isset($_GET['download']) && $_GET['download'] === '1';

// ----------------------
// Enforce can_download permission for non-admin
// ----------------------
if ($isDownload && !$isAdmin) {
    $can = false;
    $stmt = $pdo->prepare('SELECT can_download FROM file_permissions WHERE file_id = ? AND user_id = ?');
    $stmt->execute([$fileId, $userId]);
    $perm = $stmt->fetch();
    if ($perm && (int)$perm['can_download'] === 1) {
        $can = true;
    }

    if (!$can && $userTypeId) {
        $stmt = $pdo->prepare('SELECT can_download FROM file_permissions WHERE file_id = ? AND user_type_id = ?');
        $stmt->execute([$fileId, $userTypeId]);
        $perm = $stmt->fetch();
        if ($perm && (int)$perm['can_download'] === 1) {
            $can = true;
        }
    }

    if (!$can) {
        http_response_code(403);
        exit('Download not permitted');
    }
}

// ----------------------
// Audit log: view/download
// ----------------------
try {
    $action = $isDownload ? 'download_file_template' : 'view_file_template';
    $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)');
    $stmt->execute([
        (int)$_SESSION['user_id'], $action, 'file', $fileId,
        json_encode(['filename' => $file['original_filename'], 'ts' => date('c')])
    ]);
} catch (Exception $e) {
    // TIDY: Silent audit log failure
}

$mime = $file['mime_type'];
$size = (int)$file['size'];
$filename = basename($file['original_filename']);

// Load file path and stream from filesystem
$stmt = $pdo->prepare('SELECT file_path FROM files WHERE id = ?');
$stmt->execute([$fileId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['file_path'])) {
    http_response_code(404);
    exit('File missing');
}

$baseDir = dirname(__DIR__);
$absolutePath = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['file_path']);

if (!is_file($absolutePath) || !is_readable($absolutePath)) {
    http_response_code(404);
    exit('File missing');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('X-Content-Type-Options: nosniff');
$disposition = $isDownload ? 'attachment' : 'inline';
header("Content-Disposition: $disposition; filename=\"$filename\"; filename*=UTF-8''" . rawurlencode($filename));

readfile($absolutePath);
exit;
?>
