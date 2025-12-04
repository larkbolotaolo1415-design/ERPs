<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$fileId = intval($_GET['id'] ?? 0);
if ($fileId <= 0) {
    $_SESSION['admin_file_delete_status'] = 'failed';
    header('Location: file_templates.php');
    exit();
}

$deleteSuccess = false;
try {
    $stmt = $pdo->prepare('SELECT original_filename FROM files WHERE id = ?');
    $stmt->execute([$fileId]);
    $f = $stmt->fetch();

    if ($f) {
        $pdo->beginTransaction();

        $pdo->prepare('DELETE FROM file_permissions WHERE file_id = ?')->execute([$fileId]);
        $pdo->prepare('DELETE FROM files WHERE id = ?')->execute([$fileId]);

        $pdo->prepare('
            INSERT INTO audit_logs (admin_id, action, target_type, target_id, details)
            VALUES (?,?,?,?,?)
        ')->execute([
            $_SESSION['user_id'], 'delete', 'file', $fileId,
            json_encode(['filename' => $f['original_filename']])
        ]);

        $pdo->commit();
        $deleteSuccess = true;
    }

} catch (Exception $e) {
    try {
        $pdo->rollBack();
    } catch (Exception $_) {
        // TIDY: Silent rollback failure
    }
    $deleteSuccess = false;
}

$_SESSION['admin_file_delete_status'] = $deleteSuccess ? 'success' : 'failed';
header('Location: file_templates.php');
exit();
?>

