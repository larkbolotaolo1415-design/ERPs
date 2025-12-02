<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // Only allow GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Only GET allowed']);
        exit;
    }

    require '../../core/connection.php';

    if (!$connection || $connection->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Optional query params
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $includeBlob = isset($_GET['include_blob']) && ($_GET['include_blob'] === '1' || strtolower($_GET['include_blob']) === 'true');

    if ($id) {
        // Fetch single document by id
        if ($includeBlob) {
            $sql = 'SELECT id, filename, mime_type, file_data, uploaded_by, upload_date, file_size, description FROM documents WHERE id = ? LIMIT 1';
        } else {
            $sql = 'SELECT id, filename, mime_type, uploaded_by, upload_date, file_size, description FROM documents WHERE id = ? LIMIT 1';
        }

        $stmt = $connection->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed: ' . $connection->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) {
            echo json_encode(['status' => 'success', 'data' => null]);
            exit;
        }

        // If blob included, base64-encode to safely transport binary over JSON
        if ($includeBlob && isset($row['file_data'])) {
            $row['file_data'] = base64_encode($row['file_data']);
        }

        // Cast numeric fields
        if (isset($row['file_size'])) $row['file_size'] = (int)$row['file_size'];
        if (isset($row['id'])) $row['id'] = (int)$row['id'];

        echo json_encode(['status' => 'success', 'data' => $row]);
        exit;
    }

    // No id -> list documents (limit)
    $limit = 200;
    $sql = "SELECT id, filename, mime_type, uploaded_by, upload_date, file_size, description FROM documents ORDER BY upload_date DESC LIMIT ?";
    $stmt = $connection->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $connection->error);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    $docs = [];
    while ($row = $res->fetch_assoc()) {
        $docs[] = [
            'id' => (int)$row['id'],
            'filename' => $row['filename'],
            'mime_type' => $row['mime_type'],
            'uploaded_by' => $row['uploaded_by'],
            'upload_date' => $row['upload_date'],
            'file_size' => isset($row['file_size']) ? (int)$row['file_size'] : null,
            'description' => $row['description']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $docs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>
