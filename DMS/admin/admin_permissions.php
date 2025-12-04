<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../api/file_templates.php');
    exit();
}

$expectsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
$fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
$assignmentMode = $_POST['assignment_mode'] ?? 'user_type';
$userTypeId = isset($_POST['user_type_id']) ? (int)$_POST['user_type_id'] : 0;
$userEmailRaw = trim($_POST['user_email'] ?? '');
$canDownload = isset($_POST['can_download']) ? 1 : 0;

$respond = function(array $payload, int $status = 200) use ($expectsJson) {
    if ($expectsJson) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($payload);
        exit();
    }

    if ($status >= 400) {
        $message = isset($payload['message']) ? urlencode($payload['message']) : 'error';
        header('Location: ../api/file_templates.php?error=' . $message);
    } else {
        header('Location: ../api/file_templates.php?status=permissions_updated');
    }
    exit();
};

if ($fileId <= 0) {
    $respond(['success' => false, 'message' => 'Invalid file reference.'], 422);
}

$target = null;
$targetLabel = '';

if ($assignmentMode === 'user_type') {
    if ($userTypeId <= 0) {
        $respond(['success' => false, 'message' => 'Please choose a user type.'], 422);
    }
    $stmt = $pdo->prepare('SELECT id, name FROM user_types WHERE id = ?');
    $stmt->execute([$userTypeId]);
    $typeRow = $stmt->fetch();
    if (!$typeRow) {
        $respond(['success' => false, 'message' => 'Selected user type is not available.'], 404);
    }
    $target = ['user_type_id' => (int)$typeRow['id'], 'user_id' => null];
    $targetLabel = $typeRow['name'];
} elseif ($assignmentMode === 'user_email') {
    if (!filter_var($userEmailRaw, FILTER_VALIDATE_EMAIL)) {
        $respond(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
    }
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = ?');
    $stmt->execute([$userEmailRaw]);
    $userRow = $stmt->fetch();
    if (!$userRow) {
        $respond(['success' => false, 'message' => 'No user found for the provided email.'], 404);
    }
    $target = ['user_type_id' => null, 'user_id' => (int)$userRow['id'], 'user_name' => $userRow['name'], 'user_email' => $userRow['email']];
    $targetLabel = $userRow['email'];
} else {
    $respond(['success' => false, 'message' => 'Unknown assignment mode.'], 422);
}

if (!$target) {
    $respond(['success' => false, 'message' => 'Unable to resolve permission target.'], 422);
}

$pdo->beginTransaction();
try {
    // Only keep the latest assignment to avoid conflicting visibility rules.
    $pdo->prepare('DELETE FROM file_permissions WHERE file_id = ?')->execute([$fileId]);

    if (!empty($target['user_type_id'])) {
        $stmt = $pdo->prepare('INSERT INTO file_permissions (file_id, user_type_id, can_download) VALUES (?,?,?)');
        $stmt->execute([$fileId, $target['user_type_id'], $canDownload]);
    } elseif (!empty($target['user_id'])) {
        $stmt = $pdo->prepare('INSERT INTO file_permissions (file_id, user_id, can_download) VALUES (?,?,?)');
        $stmt->execute([$fileId, $target['user_id'], $canDownload]);
    }

    $auditDetails = [
        'assignment_mode' => $assignmentMode,
        'target' => $targetLabel,
        'can_download' => $canDownload === 1
    ];
    $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)');
    $stmt->execute([
        $_SESSION['user_id'],
        'permissions_update',
        'file',
        $fileId,
        json_encode($auditDetails)
    ]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Permission update error: ' . $e->getMessage());
    $respond(['success' => false, 'message' => 'Failed to save permissions.'], 500);
}

$respond([
    'success' => true,
    'assignment_mode' => $assignmentMode,
    'can_download' => $canDownload === 1,
    'label' => $targetLabel
]);
?>
