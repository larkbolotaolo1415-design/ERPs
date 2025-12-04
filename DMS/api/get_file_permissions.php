<?php
/**
 * Get File Permissions API
 * Returns existing permissions for a file as JSON
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

// TIDY: Get file ID from request
$fileId = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;

if ($fileId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file ID']);
    exit();
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT fp.id,
               fp.user_type_id,
               ut.name AS user_type_name,
               fp.user_id,
               u.name AS user_name,
               u.email AS user_email,
               fp.can_download
        FROM file_permissions fp
        LEFT JOIN user_types ut ON ut.id = fp.user_type_id
        LEFT JOIN users u ON u.id = fp.user_id
        WHERE fp.file_id = ?
        ORDER BY fp.id DESC
    ");
    $stmt->execute([$fileId]);
    $rows = $stmt->fetchAll();

    $assignmentMode = 'user_type';
    $primaryUserType = null;
    $primaryUser = null;
    $assignments = [];

    foreach ($rows as $row) {
        $isUser = !empty($row['user_id']);
        $label = $isUser
            ? trim(($row['user_name'] ?? 'User') . ' (' . ($row['user_email'] ?? 'unknown') . ')')
            : (($row['user_type_name'] ?? 'User Type') . ($row['user_type_id'] ? ' #' . $row['user_type_id'] : ''));
        $assignments[] = [
            'type' => $isUser ? 'user_email' : 'user_type',
            'label' => $label,
            'can_download' => (int)$row['can_download'] === 1
        ];

        if ($isUser && !$primaryUser) {
            $assignmentMode = 'user_email';
            $primaryUser = [
                'id' => (int)$row['user_id'],
                'name' => $row['user_name'],
                'email' => $row['user_email'],
                'can_download' => (int)$row['can_download']
            ];
        } elseif (!$isUser && !$primaryUserType && $assignmentMode !== 'user_email') {
            $assignmentMode = 'user_type';
            $primaryUserType = [
                'id' => (int)$row['user_type_id'],
                'name' => $row['user_type_name'],
                'can_download' => (int)$row['can_download']
            ];
        }
    }

    echo json_encode([
        'assignment_mode' => $assignmentMode,
        'user_type' => $primaryUserType,
        'user' => $primaryUser,
        'assignments' => $assignments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
