<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$userId = (int)$_SESSION['user_id'];

// Determine type: admin document or patient file
$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$patientFileId = isset($_GET['patient_file_id']) ? (int)$_GET['patient_file_id'] : 0;

if ($docId <= 0 && $patientFileId <= 0) {
    http_response_code(400);
    exit('No valid file specified.');
}

$isDownload = isset($_GET['download']) && $_GET['download'] === '1';

/**
 * Stream a file from the filesystem.
 */
function streamFileFromPath(string $absolutePath, string $mime, int $size, string $filename, bool $isDownload): void {
    if (!is_file($absolutePath) || !is_readable($absolutePath)) {
        http_response_code(404);
        exit('File missing');
    }

    $disposition = $isDownload ? 'attachment' : 'inline';

    header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
    header('Content-Length: ' . $size);
    header('X-Content-Type-Options: nosniff');
    header("Content-Disposition: $disposition; filename=\"$filename\"; filename*=UTF-8''" . rawurlencode($filename));

    readfile($absolutePath);
    exit;
}

try {
    if ($docId > 0) {
        // --- Admin Document (file-path storage) ---
        $stmt = $pdo->prepare('SELECT id, filename, mime_type, file_size, file_path FROM documents WHERE id = ?');
        $stmt->execute([$docId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            http_response_code(404);
            exit('Document not found');
        }

        // Audit log
        try {
            $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                ->execute([
                    $userId,
                    $isDownload ? 'download_document' : 'view_document',
                    'document',
                    $docId,
                    json_encode(['filename' => $doc['filename'], 'ts' => date('c')])
                ]);
        } catch (Exception $e) {
            // TIDY: Ignore audit failures
        }

        $baseDir = dirname(__DIR__);
        $path = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $doc['file_path']);
        $filename = basename($doc['filename']);
        $size = (int)$doc['file_size'];

        streamFileFromPath($path, $doc['mime_type'], $size, $filename, $isDownload);
    }

    if ($patientFileId > 0) {
        // --- Patient File (file-path storage) ---
        // Ensure user owns the file or is doctor assigned
        $stmt = $pdo->prepare("
            SELECT pf.id, pf.original_filename AS filename, pf.mime_type, pf.file_size, pf.file_path
            FROM patient_files pf
            LEFT JOIN patient_file_access pfa ON pfa.file_id = pf.id
            WHERE pf.id = :pfid AND (pf.patient_id = :uid OR pfa.doctor_id = :uid)
            GROUP BY pf.id
        ");
        $stmt->execute([':pfid' => $patientFileId, ':uid' => $userId]);
        $pf = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pf) {
            http_response_code(403);
            exit('Access denied');
        }

        // Audit log
        try {
            $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                ->execute([
                    $userId,
                    $isDownload ? 'download_patient_file' : 'view_patient_file',
                    'patient_file',
                    $patientFileId,
                    json_encode(['filename' => $pf['filename'], 'ts' => date('c')])
                ]);
        } catch (Exception $e) {
            // TIDY: Ignore audit failures
        }

        $baseDir = dirname(__DIR__);
        $path = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pf['file_path']);
        $filename = basename($pf['filename']);
        $size = (int)$pf['file_size'];

        streamFileFromPath($path, $pf['mime_type'], $size, $filename, $isDownload);
    }

} catch (Exception $e) {
    http_response_code(500);
    exit('An error occurred while streaming the file.');
}
?>
