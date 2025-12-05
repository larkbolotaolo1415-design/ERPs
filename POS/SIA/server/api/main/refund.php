<?php
header('Content-Type: application/json');
require '../../core/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$invoice_number = $data['invoice_number'] ?? '';
$items = $data['items'] ?? [];
$employee_id = $data['employee_id'] ?? '';
$sub_role = $data['sub_role'] ?? '';

if (!$invoice_number || empty($items) || !$employee_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid refund data']);
    exit;
}

// Check if employee is Admin based on sub_role
// Allow refund if sub_role contains "admin" (case-insensitive)
$isAdmin = false;
if (!empty($sub_role)) {
    $subRoleLower = strtolower(trim($sub_role));
    $isAdmin = strpos($subRoleLower, 'admin') !== false;
}

// Fallback: Check local employees table if sub_role not provided
if (!$isAdmin) {
    $stmtRole = $connection->prepare("SELECT user_role, sub_role FROM employees WHERE id = ?");
    $stmtRole->bind_param("s", $employee_id);
    $stmtRole->execute();
    $resRole = $stmtRole->get_result()->fetch_assoc();
    
    if ($resRole) {
        // Check sub_role first, then fallback to user_role
        $checkRole = !empty($resRole['sub_role']) ? $resRole['sub_role'] : ($resRole['user_role'] ?? '');
        $checkRoleLower = strtolower(trim($checkRole));
        $isAdmin = strpos($checkRoleLower, 'admin') !== false || $checkRole === 'Admin';
    }
}

if (!$isAdmin) {
    echo json_encode(['status' => 'error', 'message' => 'Only Admin users can process refunds']);
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
    // Note: employee_id is stored as string (HR system format like "EMP-041")
    // The foreign key constraint fk_refund_employee should be removed from the database
    // since we're using HR system employee IDs that don't exist in local employees table
    try {
        $stmtRefund = $connection->prepare("INSERT INTO refunds (invoice_number, employee_id, date_refunded) VALUES (?, ?, NOW())");
        $stmtRefund->bind_param("ss", $invoice_number, $employee_id);
        $stmtRefund->execute();
    } catch (Exception $refundErr) {
        // If foreign key constraint error, provide helpful message
        if (strpos($refundErr->getMessage(), 'foreign key constraint') !== false) {
            throw new Exception('Database constraint error: Please remove the foreign key constraint fk_refund_employee from the refunds table. See fix_refunds_foreign_key.sql for instructions.');
        }
        throw $refundErr;
    }

    // Delete invoice items
    $stmtDeleteItems = $connection->prepare("DELETE FROM invoice_items WHERE invoice_number = ?");
    $stmtDeleteItems->bind_param("s", $invoice_number);
    $stmtDeleteItems->execute();

    // Mark invoice as refunded
    // Note: refunded_by stores employee_id as string (HR system format)
    $stmtMark = $connection->prepare("UPDATE invoices SET refunded = 1, refunded_by = ?, date_refunded = NOW() WHERE invoice_number = ?");
    $stmtMark->bind_param("ss", $employee_id, $invoice_number);
    $stmtMark->execute();

    $connection->commit();

    echo json_encode(['status' => 'success', 'message' => 'Refund processed successfully']);
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Refund failed: ' . $e->getMessage()]);
}
?>
