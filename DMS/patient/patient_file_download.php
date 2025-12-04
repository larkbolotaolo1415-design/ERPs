<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid');
}

// Load metadata
$stmt = $pdo->prepare('SELECT id, patient_id, original_filename, mime_type, file_size FROM patient_files WHERE id = ?');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) {
    http_response_code(404);
    exit('Not found');
}

$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? '';
$allowed = false;

// Owner patient can download
if ($role === 'patient' && (int)$file['patient_id'] === $userId) {
    $allowed = true;
}

// Assigned doctor can download
if (!$allowed && $role === 'doctor') {
    $chk = $pdo->prepare('SELECT 1 FROM patient_file_access WHERE file_id = ? AND doctor_id = ?');
    $chk->execute([(int)$file['id'], $userId]);
    $allowed = (bool)$chk->fetch();
}

if (!$allowed) {
    http_response_code(403);
    exit('Forbidden');
}

// Audit
try {
    $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
        ->execute([$userId, 'download_patient_file', 'patient_file', $id, json_encode(['filename' => $file['original_filename']])]);
} catch (Exception $e) {
    // TIDY: Silent audit log failure
}

// Resolve file path and stream from filesystem
$stmt = $pdo->prepare('SELECT file_path FROM patient_files WHERE id = ?');
$stmt->execute([$id]);
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

// Headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_filename']) . '"');
header('Content-Length: ' . (string)$file['file_size']);

readfile($absolutePath);
exit();
?>
