<?php
/**
 * Save Invoice as PDF and store in `documents` table
 * Accepts JSON POST: invoice_id, invoice_number, report_html, uploaded_by (optional), description (optional)
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

$invoiceId = $input['invoice_id'] ?? null;
$invoiceNumber = $input['invoice_number'] ?? null;
$reportHtml = $input['report_html'] ?? null;
$uploadedBy = $input['uploaded_by'] ?? null;
$description = $input['description'] ?? null;

if (!$reportHtml) {
    echo json_encode(['status' => 'error', 'message' => 'Missing report_html']);
    exit;
}

// Attempt to load Composer autoload so we can use mPDF if installed
$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Try to create PDF using mPDF
try {
    if (class_exists('\\Mpdf\\Mpdf')) {
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
        $mpdf->WriteHTML($reportHtml);
        $pdfContent = $mpdf->Output('', 'S'); // return string
    } else {
        throw new Exception('mPDF not available. Please install mpdf/mpdf via Composer.');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'PDF generation failed: ' . $e->getMessage()]);
    exit;
}

// Save PDF file to uploads directory (for convenience)
$uploadsDir = __DIR__ . '/../../../uploads/documents';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$safeInvoice = preg_replace('/[^a-zA-Z0-9-_\.]/', '-', ($invoiceNumber ?? 'invoice'));
$filename = sprintf('invoice-%s-%s.pdf', $safeInvoice, time());
$filePath = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
file_put_contents($filePath, $pdfContent);
$fileSize = filesize($filePath);

// Insert into documents DB using POS database connection
require_once __DIR__ . '/../../core/connection.php'; // provides $connection (mysqli) pointed at POS DB

if (!isset($connection) || !$connection) {
    echo json_encode(['status' => 'error', 'message' => 'POS DB connection not available']);
    exit;
}

$mime = 'application/pdf';
$insertSql = 'INSERT INTO documents (filename, mime_type, file_path, uploaded_by, upload_date, file_size, description) VALUES (?, ?, ?, ?, NOW(), ?, ?)';
$stmt = $connection->prepare($insertSql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $connection->error]);
    exit;
}

// We will bind a NULL placeholder for blob and send with send_long_data
$null = NULL;
// Ensure uploaded_by is a non-null string — database requires NOT NULL (varchar)
$uploadedByVal = (!empty($uploadedBy) ? $uploadedBy : 'system');
$descriptionVal = ($description !== null ? $description : '');

// types: filename (s), mime (s), file_data (b), uploaded_by (s), file_size (i), description (s)
$stmt->bind_param('ssbiss', $filename, $mime, $null, $uploadedByVal, $fileSize, $descriptionVal);

// send_long_data param index is zero-based; file_data is the 3rd parameter (index 2)
if (!$stmt->send_long_data(2, $pdfContent)) {
    // send_long_data may return false on failure but often returns void; continue
}

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$docId = $connection->insert_id;
$stmt->close();

echo json_encode([
    'status' => 'success',
    'message' => 'Invoice saved to documents (POS DB)',
    'document_id' => $docId,
    'filename' => $filename,
    'path' => $filePath
]);
exit;

?>
