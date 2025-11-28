<?php
header('Content-Type: application/json');
require '../../core/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$patient_id = $data['patient_id'] ?? null;
$walkin_name = $data['walkin_name'] ?? null;
$employee_id = $data['employee_id'] ?? null;
$subtotal = $data['subtotal'] ?? 0;
$discount_type = $data['discount_type'] ?? 'none';
$discount_amount = $data['discount_amount'] ?? 0;
$tax_amount = $data['tax_amount'] ?? 0;
$total_amount = $data['total_amount'] ?? 0;
$payment_method = $data['payment_method'] ?? 'cash';
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
    exit;
}

$connection->begin_transaction();

try {
    // Generate invoice number
    $year = date('Y');
    $result = $connection->query("SELECT COUNT(*) AS count FROM invoices WHERE YEAR(date_created) = $year");
    $count = $result->fetch_assoc()['count'] ?? 0;
    $invoice_number = sprintf("INV-%d-%04d", $year, $count + 1);

    // Insert walk-in if applicable
    $walkin_id = null;
    if ($walkin_name) {
        $names = explode(" ", $walkin_name, 2);
        $first_name = $names[0];
        $last_name = $names[1] ?? "";
        $stmt = $connection->prepare("INSERT INTO walkin_customers (first_name, last_name, transaction_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $first_name, $last_name);
        $stmt->execute();
        $walkin_id = $connection->insert_id;
    }

    // Validate stock and medicine_id
    $medicineStmt = $connection->prepare("SELECT stock, medicine_name FROM medicines WHERE medicine_id = ?");
    foreach ($items as $item) {
        $medicineStmt->bind_param("s", $item['medicine_id']);
        $medicineStmt->execute();
        $medData = $medicineStmt->get_result()->fetch_assoc();
        if (!$medData) {
            throw new Exception("Medicine ID {$item['medicine_id']} ({$item['medicine_name']}) not found.");
        }
        if ($item['quantity'] > $medData['stock']) {
            throw new Exception("Insufficient stock for {$medData['medicine_name']}. Available: {$medData['stock']}");
        }
    }

    // Insert invoice
    $stmt = $connection->prepare("
        INSERT INTO invoices
        (invoice_number, patient_id, walkin_id, employee_id, subtotal, discount_type, discount_amount, tax_amount, total_amount, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssdsddds",
        $invoice_number,
        $patient_id,
        $walkin_id,
        $employee_id,
        $subtotal,
        $discount_type,
        $discount_amount,
        $tax_amount,
        $total_amount,
        $payment_method
    );
    $stmt->execute();

    // Insert invoice items and update stock
    $itemStmt = $connection->prepare("
        INSERT INTO invoice_items (invoice_number, medicine_id, medicine_name, price, quantity)
        VALUES (?, ?, ?, ?, ?)
    ");
    $updateStockStmt = $connection->prepare("UPDATE medicines SET stock = stock - ? WHERE medicine_id = ?");

    foreach ($items as $item) {
        $itemStmt->bind_param(
            "sssid",
            $invoice_number,
            $item['medicine_id'],
            $item['medicine_name'],
            $item['price'],
            $item['quantity']
        );
        $itemStmt->execute();

        $updateStockStmt->bind_param("is", $item['quantity'], $item['medicine_id']);
        $updateStockStmt->execute();
    }

    $invoice_id = $connection->insert_id;
    $connection->commit();

    echo json_encode([
        'status' => 'success',
        'invoice_number' => $invoice_number,
        'invoice_id' => $invoice_id,
        'walkin_id' => $walkin_id
    ]);

} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
