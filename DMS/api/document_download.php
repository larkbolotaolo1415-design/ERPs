<?php
// Wrapper to support legacy "document_download.php" links.
// Streams the requested document or patient file as an attachment via document_stream.php

if (!isset($_GET['id']) && !isset($_GET['patient_file_id'])) {
    http_response_code(400);
    exit('No file specified.');
}

// Force download (attachment)
$_GET['download'] = '1';

require_once __DIR__ . '/document_stream.php';