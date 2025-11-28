<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    require '../../core/connection.php';
    
    if (!$connection || $connection->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    if (!isset($_GET['invoice_id'])) {
        throw new Exception("Invoice ID is required");
    }
    
    $invoice_id = intval($_GET['invoice_id']);
    
    // Get invoice
    $invoiceQuery = "SELECT 
                        invoice_id,
                        invoice_number,
                        patient_id,
                        employee_id,
                        subtotal,
                        discount_amount,
                        tax_amount,
                        total_amount,
                        payment_method,
                        date_created
                     FROM invoices 
                     WHERE invoice_id = " . $invoice_id;
    
    $invoiceResult = $connection->query($invoiceQuery);
    
    if (!$invoiceResult) {
        throw new Exception("Query error: " . $connection->error);
    }
    
    if ($invoiceResult->num_rows === 0) {
        throw new Exception("Invoice not found");
    }
    
    $invoice = $invoiceResult->fetch_assoc();
    
    // Get items
    $escapedInvoiceNumber = $connection->real_escape_string($invoice['invoice_number']);
    $itemsQuery = "SELECT medicine_name, price, quantity FROM invoice_items WHERE invoice_number = '$escapedInvoiceNumber'";
    $itemsResult = $connection->query($itemsQuery);
    
    $items = [];
    if ($itemsResult && $itemsResult->num_rows > 0) {
        while ($row = $itemsResult->fetch_assoc()) {
            $items[] = [
                'medicine_name' => $row['medicine_name'],
                'price' => (float)$row['price'],
                'quantity' => (int)$row['quantity'],
                'dosage' => '-'
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'invoice' => [
            'invoice_id' => (int)$invoice['invoice_id'],
            'invoice_number' => $invoice['invoice_number'],
            'date_created' => $invoice['date_created'],
            'patient_id' => $invoice['patient_id'],
            'employee_id' => $invoice['employee_id'],
            'subtotal' => (float)$invoice['subtotal'],
            'discount_amount' => (float)$invoice['discount_amount'],
            'tax_amount' => (float)$invoice['tax_amount'],
            'total_amount' => (float)$invoice['total_amount'],
            'payment_method' => $invoice['payment_method']
        ],
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
