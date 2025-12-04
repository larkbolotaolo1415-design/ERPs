<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

// Only patients can delete their own files
if ($userRole !== 'patient') {
    http_response_code(403);
    exit('Forbidden');
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    exit('Invalid request method');
}

$fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
if ($fileId <= 0) {
    http_response_code(400);
    exit('Invalid file ID');
}

try {
    // Verify file belongs to this patient and load file_path for cleanup
    $stmt = $pdo->prepare('SELECT id, original_filename, patient_id, file_path FROM patient_files WHERE id = ? AND patient_id = ?');
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        http_response_code(404);
        exit('File not found or access denied');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Delete file access records first (foreign key constraint)
    $stmt = $pdo->prepare('DELETE FROM patient_file_access WHERE file_id = ?');
    $stmt->execute([$fileId]);

    // Remove physical file if present
    if (!empty($file['file_path'])) {
        $baseDir = dirname(__DIR__);
        $absolutePath = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file['file_path']);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    // Delete the file record
    $stmt = $pdo->prepare('DELETE FROM patient_files WHERE id = ?');
    $stmt->execute([$fileId]);
    
    // Audit log
    try {
        $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
            ->execute([$userId, 'delete_patient_file', 'patient_file', $fileId, json_encode(['filename' => $file['original_filename']])]);
        } catch (Exception $e) {
        // TIDY: Ignore audit failures
    }
    
    $pdo->commit();
    
    // Redirect back with success message
    header('Location: ../dashboard.php?delete=success');
    exit();
    
} catch (Exception $e) {
    try {
        $pdo->rollBack();
    } catch (Exception $_) {
        // TIDY: Silent rollback failure
    }
    http_response_code(500);
    echo "Delete failed: " . htmlspecialchars($e->getMessage());
    exit();
}
?>

