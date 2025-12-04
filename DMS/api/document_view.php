<?php
// Wrapper to support legacy "document_view.php" links.
// Streams the requested document or patient file inline via document_stream.php

// Ensure a file id was provided
if (!isset($_GET['id']) && !isset($_GET['patient_file_id'])) {
    http_response_code(400);
    exit('No file specified.');
}

// Force inline (view) disposition
// document_stream.php checks for $_GET['download'] === '1' for attachment
if (!isset($_GET['download'])) {
    $_GET['download'] = '0';
}

require_once __DIR__ . '/document_stream.php';