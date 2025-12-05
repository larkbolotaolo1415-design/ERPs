<?php
header('Content-Type: application/json');
require '../../core/connection.php';

$invoice_number = $_GET['invoice_number'] ?? '';

if (!$invoice_number) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice number required']);
    exit;
}

// Get invoice
$stmt = $connection->prepare("SELECT * FROM invoices WHERE invoice_number = ?");
$stmt->bind_param("s", $invoice_number);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice not found']);
    exit;
}

// Check if already refunded
if ($invoice['refunded'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice already refunded']);
    exit;
}

// Determine buyer
$buyer_name = '';
if ($invoice['patient_id']) {
    // Patient info is managed in PMS, use patient_id as identifier
    $buyer_name = 'Patient ID: ' . $invoice['patient_id'];
} elseif ($invoice['walkin_id']) {
    $stmtBuyer = $connection->prepare("SELECT first_name, last_name FROM walkin_customers WHERE walkin_id = ?");
    $stmtBuyer->bind_param("i", $invoice['walkin_id']);
    $stmtBuyer->execute();
    $res = $stmtBuyer->get_result()->fetch_assoc();
    $buyer_name = $res['first_name'] . ' ' . $res['last_name'];
} else {
    $buyer_name = 'Unknown';
}

// Get invoice items
$stmtItems = $connection->prepare("
    SELECT ii.medicine_id, m.medicine_name, ii.quantity, ii.price
    FROM invoice_items ii
    JOIN medicines m ON ii.medicine_id = m.medicine_id
    WHERE ii.invoice_number = ?
");
$stmtItems->bind_param("s", $invoice_number);
$stmtItems->execute();
$result = $stmtItems->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'status' => 'success',
    'buyer_name' => $buyer_name,
    'items' => $items
]);
?>
