<?php
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    $_SESSION['patient_upload_status'] = 'invalid';
    header('Location: ../dashboard.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
// Patient should be the uploader; you can adjust role checks if needed
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
    http_response_code(403);
    exit('Forbidden');
}

// Allowed types and limits (match dashboard)
$allowed = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/png',
    'image/jpeg',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
$maxSize = 10 * 1024 * 1024; // 10MB

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['patient_upload_status'] = 'invalid';
    header('Location: ../dashboard.php');
    exit();
}

$tmp = $file['tmp_name'];
$orig = $file['name'];
$size = (int)$file['size'];
if ($size <= 0 || $size > $maxSize) {
    $_SESSION['patient_upload_status'] = 'invalid';
    header('Location: ../dashboard.php');
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $tmp);
finfo_close($finfo);
if (!in_array($mime, $allowed, true)) {
    $_SESSION['patient_upload_status'] = 'invalid';
    header('Location: ../dashboard.php');
    exit();
}

// Description and assigned doctors (optional)
$description = trim($_POST['description'] ?? '');

// Use DB transaction and file-path-based storage
try {
    $pdo->beginTransaction();

    // Ensure uploads/patients directory exists
    $baseDir = dirname(__DIR__);
    $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'patients';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    // Sanitize and generate conflict-safe file name
    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $orig);
    if ($safeName === '' || $safeName === '.' || $safeName === '..') {
        $safeName = 'patient_file';
    }
    $uniqueName = uniqid('pf_', true) . '_' . $safeName;
    $targetPathFs = $uploadDir . DIRECTORY_SEPARATOR . $uniqueName;
    $relativePath = 'uploads/patients/' . $uniqueName;

    if (!move_uploaded_file($tmp, $targetPathFs)) {
        throw new Exception('Failed to move uploaded file');
    }

    $stmt = $pdo->prepare('
        INSERT INTO patient_files (patient_id, file_name, original_filename, file_size, mime_type, file_path, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$userId, $orig, $orig, $size, $mime, $relativePath, $description]);
    $newId = (int)$pdo->lastInsertId();

    // Handle assigned doctors (if any)
    if (!empty($_POST['doctor_ids']) && is_array($_POST['doctor_ids'])) {
        $stmtAcc = $pdo->prepare('INSERT IGNORE INTO patient_file_access (file_id, doctor_id) VALUES (?, ?)');
        $assignedCount = 0;
        foreach ($_POST['doctor_ids'] as $docId) {
            $docId = (int)$docId;
            if ($docId > 0) {
                try {
                    $stmtAcc->execute([$newId, $docId]);
                    $assignedCount++;
                } catch (Exception $e) {
                    error_log("Failed to assign file to doctor $docId: " . $e->getMessage());
                    // Continue with other doctors even if one fails
                }
            }
        }
        // Log if no doctors were assigned (should not happen due to form validation)
        if ($assignedCount === 0) {
            error_log("Warning: File uploaded but no doctors were assigned. File ID: $newId, Doctor IDs: " . json_encode($_POST['doctor_ids']));
        } else {
            error_log("Successfully assigned file $newId to $assignedCount doctor(s)");
        }
    } else {
        // This should not happen due to form validation, but log it
        error_log("Warning: File uploaded without doctor_ids. File ID: $newId");
    }

    // Audit log (optional)
    $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
        ->execute([$userId, 'patient_upload', 'patient_file', $newId, json_encode(['filename' => $orig])]);

    $pdo->commit();

    $_SESSION['patient_upload_status'] = 'success';
    header('Location: ../dashboard.php');
    exit();

} catch (Exception $e) {
    try {
        $pdo->rollBack();
    } catch (Exception $_) {
        // TIDY: Silent rollback failure
    }
    $_SESSION['patient_upload_status'] = 'failed';
    header('Location: ../dashboard.php');
    exit();
}
?>
