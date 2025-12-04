<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // Allow only GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Only GET allowed']);
        exit;
    }

    require_once __DIR__ . '/../includes/db_connect.php';

    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Query parameter (?id=)
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    // -------------------------------------------------------------
    // FETCH SINGLE DOCUMENT
    // -------------------------------------------------------------
    if ($id) {
        $sql = "SELECT id, filename, mime_type, file_path, uploaded_by, upload_date, file_size, description
                FROM documents
                WHERE id = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['status' => 'success', 'data' => null]);
            exit;
        }

        // Cast numeric fields
        $row['id'] = (int)$row['id'];
        $row['file_size'] = (int)$row['file_size'];

        echo json_encode(['status' => 'success', 'data' => $row]);
        exit;
    }

    // -------------------------------------------------------------
    // LIST ALL DOCUMENTS
    // -------------------------------------------------------------
    $sql = "SELECT id, filename, mime_type, file_path, uploaded_by, upload_date, file_size, description
            FROM documents
            ORDER BY upload_date DESC";

    $stmt = $pdo->query($sql);
    $documents = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $documents[] = [
            'id'          => (int)$row['id'],
            'filename'    => $row['filename'],
            'mime_type'   => $row['mime_type'],
            'file_path'   => $row['file_path'],
            'uploaded_by' => $row['uploaded_by'],
            'upload_date' => $row['upload_date'],
            'file_size'   => (int)$row['file_size'],
            'description' => $row['description']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $documents]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
