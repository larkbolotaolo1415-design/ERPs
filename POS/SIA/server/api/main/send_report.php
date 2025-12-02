<?php
/**
 * Send Report via Email
 * Accepts: report_type (sales|invoice), recipient_email, report_html
 * Sends HTML email with embedded PDF or formatted document
 */

header('Content-Type: application/json');

// Include PHPMailer
require_once __DIR__ . '/../../modules/PHPMailer/PHPMailerAutoload.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

// Get JSON payload
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

// Validate required fields
$reportType = $input['report_type'] ?? null;  // 'sales' or 'invoice'
$recipientEmail = $input['recipient_email'] ?? null;
$reportHtml = $input['report_html'] ?? null;
$invoiceId = $input['invoice_id'] ?? null;
$invoiceNumber = $input['invoice_number'] ?? null;

if (!$reportType || !$recipientEmail || !$reportHtml) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: report_type, recipient_email, report_html']);
    exit;
}

// Validate report type
if (!in_array($reportType, ['sales', 'invoice'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid report_type. Must be "sales" or "invoice"']);
    exit;
}

// Validate email
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipient email address']);
    exit;
}

try {
    // Initialize PHPMailer
    $mail = new PHPMailer();
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';  // TODO: Configure with env var or config file
    $mail->Password = 'your-app-password';     // TODO: Configure with env var or config file
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Sender
    $mail->setFrom('pharmaplus@system.local', 'Pharma Plus - Pharmacy Management System');
    
    // Recipient
    $mail->addAddress($recipientEmail);
    
    // Subject based on report type
    if ($reportType === 'invoice') {
        $subject = 'Invoice Report - ' . ($invoiceNumber ?? 'INV-' . date('Ymd-His'));
        $mail->Subject = $subject;
    } else {
        $subject = 'Sales Report - ' . date('Y-m-d H:i:s');
        $mail->Subject = $subject;
    }
    
    // Email body with HTML
    $mail->isHTML(true);
    $mailBody = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .email-container { max-width: 900px; margin: 0 auto; padding: 20px; }
            .email-header { text-align: center; padding: 20px; border-bottom: 2px solid #0066cc; }
            .email-header h1 { color: #0066cc; margin: 0; }
            .email-content { padding: 20px 0; }
            .report-section { page-break-inside: avoid; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Pharma Plus Report</h1>
                <p>' . ($reportType === 'invoice' ? 'Invoice Report' : 'Sales Report') . ' - ' . date('F d, Y H:i A') . '</p>
            </div>
            <div class="email-content">
                ' . $reportHtml . '
            </div>
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center;">
                <p>This is an automated report from Pharma Plus Pharmacy Management System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $mail->Body = $mailBody;
    
    // Optional: Add plain text version
    $mail->AltBody = strip_tags($reportHtml);
    
    // Send email
    if (!$mail->send()) {
        throw new Exception('Email send failed: ' . $mail->ErrorInfo);
    }
    
    // Log the sent report (optional)
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'report_type' => $reportType,
        'recipient' => $recipientEmail,
        'invoice_id' => $invoiceId,
        'invoice_number' => $invoiceNumber,
        'status' => 'sent'
    ];
    
    // TODO: Save log to database or file
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Report sent successfully to ' . $recipientEmail,
        'email' => $recipientEmail,
        'report_type' => $reportType
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to send report: ' . $e->getMessage()
    ]);
    http_response_code(500);
}

?>
