<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$fileId = intval($_GET['id'] ?? 0);
if ($fileId <= 0) {
    http_response_code(400);
    exit('Invalid file id');
}

$stmt = $pdo->prepare('SELECT id, original_filename, mime_type, size, file_path FROM files WHERE id = ?');
$stmt->execute([$fileId]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    exit('File not found');
}

$mime = trim($file['mime_type']);
if (empty($mime)) {
    $ext = strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
    $mimeMap = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';
}

$size = intval($file['size']);
$filename = basename($file['original_filename']);

$baseDir = dirname(__DIR__);
$absolutePath = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file['file_path']);

if (!is_file($absolutePath) || !is_readable($absolutePath)) {
    http_response_code(404);
    exit('File missing');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . $filename . '"');

readfile($absolutePath);
exit;

