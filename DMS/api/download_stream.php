<?php
// Compatibility wrapper for "download_stream.php" (dashboard_template.php uses this).
// Delegates all parameters to document_stream.php so both view and download work.

if (!isset($_GET['id']) && !isset($_GET['patient_file_id'])) {
    http_response_code(400);
    exit('No file specified.');
}

// Leave download param as-is if present; otherwise default to inline view.
// If dashboard/template passes &download=1 it will be respected.
if (!isset($_GET['download'])) {
    $_GET['download'] = '0';
}

require_once __DIR__ . '/document_stream.php';