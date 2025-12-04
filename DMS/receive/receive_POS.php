<?php
// api_receive_documents.php

// Debug mode (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON response
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../includes/db_connect.php';

// ---------------------------------------------------------
// HARD-CODED URL of the other module providing JSON DATA
// ---------------------------------------------------------
$sourceUrl = "https://example.com/api/export_documents.php"; 
// Replace with your real module URL
// ---------------------------------------------------------

// Fetch JSON from the remote module
$jsonResponse = file_get_contents($sourceUrl);

if ($jsonResponse === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch JSON from source URL"
    ]);
    exit;
}

// Decode JSON
$data = json_decode($jsonResponse, true);

// Validate JSON structure
if (!$data || !isset($data['data']) || !is_array($data['data'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON structure from source"
    ]);
    exit;
}

$documents = $data['data'];

// Uploads folder inside your system
$uploadDir = __DIR__ . '/../uploads/';

// Ensure folder exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO documents 
        (id, filename, mime_type, file_path, uploaded_by, upload_date, file_size, description)
        VALUES 
        (:id, :filename, :mime_type, :file_path, :uploaded_by, :upload_date, :file_size, :description)
        ON DUPLICATE KEY UPDATE
            filename = VALUES(filename),
            mime_type = VALUES(mime_type),
            file_path = VALUES(file_path),
            uploaded_by = VALUES(uploaded_by),
            upload_date = VALUES(upload_date),
            file_size = VALUES(file_size),
            description = VALUES(description)
    ");

    foreach ($documents as $doc) {

        $id = $doc['id'] ?? null;
        $filename = $doc['filename'] ?? '';
        $mime_type = $doc['mime_type'] ?? '';
        $uploaded_by = $doc['uploaded_by'] ?? '';
        $upload_date = $doc['upload_date'] ?? date("Y-m-d H:i:s");
        $file_size = $doc['file_size'] ?? 0;
        $description = $doc['description'] ?? '';

        // Create file_path
        $file_path = "uploads/" . $filename;
        $full_path = $uploadDir . $filename;

        // TIDY: Create placeholder file locally if not exists
        if (!file_exists($full_path)) {
            file_put_contents($full_path, "");
        }

        $stmt->execute([
            ':id' => $id,
            ':filename' => $filename,
            ':mime_type' => $mime_type,
            ':file_path' => $file_path,
            ':uploaded_by' => $uploaded_by,
            ':upload_date' => $upload_date,
            ':file_size' => $file_size,
            ':description' => $description
        ]);
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Documents stored successfully from hardcoded URL"
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
