<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require '../../core/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$invoice = $data['invoice'] ?? null;
$items = $data['items'] ?? [];

if (!$invoice) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice data is required']);
    exit;
}

$invoice_id = intval($invoice['invoice_id'] ?? 0);
$invoice_number = $invoice['invoice_number'] ?? '';

if (!$invoice_id || !$invoice_number) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice ID and Invoice Number are required']);
    exit;
}

$connection->begin_transaction();

try {
    // Verify invoice exists
    $checkStmt = $connection->prepare("SELECT invoice_id, invoice_number FROM invoices WHERE invoice_id = ?");
    $checkStmt->bind_param("i", $invoice_id);
    $checkStmt->execute();
    $existingInvoice = $checkStmt->get_result()->fetch_assoc();
    
    if (!$existingInvoice) {
        throw new Exception("Invoice not found");
    }
    
    // Update invoice (protected fields: invoice_id, invoice_number, date_created, payment_method, patient_id, employee_id are not updated)
    $updateStmt = $connection->prepare("
        UPDATE invoices SET
            subtotal = ?,
            discount_amount = ?,
            tax_amount = ?,
            total_amount = ?
        WHERE invoice_id = ?
    ");
    
    $subtotal = floatval($invoice['subtotal'] ?? 0);
    $discount_amount = floatval($invoice['discount_amount'] ?? 0);
    $tax_amount = floatval($invoice['tax_amount'] ?? 0);
    $total_amount = floatval($invoice['total_amount'] ?? 0);
    
    $updateStmt->bind_param(
        "ddddi",
        $subtotal,
        $discount_amount,
        $tax_amount,
        $total_amount,
        $invoice_id
    );
    $updateStmt->execute();
    
    // Delete existing invoice items
    $deleteStmt = $connection->prepare("DELETE FROM invoice_items WHERE invoice_number = ?");
    $deleteStmt->bind_param("s", $invoice_number);
    $deleteStmt->execute();
    
    // Insert new invoice items
    if (!empty($items)) {
        $itemStmt = $connection->prepare("
            INSERT INTO invoice_items (invoice_number, medicine_id, medicine_name, price, quantity)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            // Try to find medicine_id from medicine_name
            $medicine_name = $item['medicine_name'] ?? '';
            $medicine_id = null;
            
            if ($medicine_name) {
                $medStmt = $connection->prepare("SELECT medicine_id FROM medicines WHERE medicine_name = ? LIMIT 1");
                $medStmt->bind_param("s", $medicine_name);
                $medStmt->execute();
                $medResult = $medStmt->get_result();
                if ($medRow = $medResult->fetch_assoc()) {
                    $medicine_id = $medRow['medicine_id'];
                }
            }
            
            // Use medicine_name as fallback for medicine_id if not found
            if (!$medicine_id) {
                $medicine_id = $medicine_name;
            }
            
            $price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 0);
            
            $itemStmt->bind_param(
                "sssdi",
                $invoice_number,
                $medicine_id,
                $medicine_name,
                $price,
                $quantity
            );
            $itemStmt->execute();
        }
    }
    
    $connection->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Invoice updated successfully',
        'invoice_id' => $invoice_id
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>

