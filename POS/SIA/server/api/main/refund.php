<?php
header('Content-Type: application/json');
require '../../core/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$invoice_number = $data['invoice_number'] ?? '';
$items = $data['items'] ?? [];
$employee_id = $data['employee_id'] ?? '';

if (!$invoice_number || empty($items) || !$employee_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid refund data']);
    exit;
}

// Check if employee is Admin
$stmtRole = $connection->prepare("SELECT user_role FROM employees WHERE id = ?");
$stmtRole->bind_param("i", $employee_id);
$stmtRole->execute();
$resRole = $stmtRole->get_result()->fetch_assoc();

if (!$resRole || $resRole['user_role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Only Admin can process refunds']);
    exit;
}

// Check if invoice exists and not already refunded
$stmtInvoice = $connection->prepare("SELECT * FROM invoices WHERE invoice_number = ? AND refunded IS NULL");
$stmtInvoice->bind_param("s", $invoice_number);
$stmtInvoice->execute();
$invoice = $stmtInvoice->get_result()->fetch_assoc();

if (!$invoice) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice not found or already refunded']);
    exit;
}

// Begin transaction
$connection->begin_transaction();

try {
    // Update medicines stock
    foreach ($items as $item) {
        $stmtUpdate = $connection->prepare("UPDATE medicines SET stock = stock + ? WHERE medicine_id = ?");
        $stmtUpdate->bind_param("is", $item['quantity'], $item['medicine_id']);
        $stmtUpdate->execute();
    }

    // Insert into refunds table
    $stmtRefund = $connection->prepare("INSERT INTO refunds (invoice_number, employee_id, date_refunded) VALUES (?, ?, NOW())");
    $stmtRefund->bind_param("si", $invoice_number, $employee_id);
    $stmtRefund->execute();

    // Delete invoice items
    $stmtDeleteItems = $connection->prepare("DELETE FROM invoice_items WHERE invoice_number = ?");
    $stmtDeleteItems->bind_param("s", $invoice_number);
    $stmtDeleteItems->execute();

    // Mark invoice as refunded
    $stmtMark = $connection->prepare("UPDATE invoices SET refunded = 1, refunded_by = ?, date_refunded = NOW() WHERE invoice_number = ?");
    $stmtMark->bind_param("is", $employee_id, $invoice_number);
    $stmtMark->execute();

    $connection->commit();

    echo json_encode(['status' => 'success', 'message' => 'Refund processed successfully']);
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Refund failed: ' . $e->getMessage()]);
}
?>
