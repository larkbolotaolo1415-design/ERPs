<?php
header('Content-Type: application/json');
require '../../core/connection.php';

$result = $connection->query("SELECT SUM(quantity) AS total_quantity FROM invoice_items");

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'total_quantity' => (int) $row['total_quantity']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $connection->error
    ]);
}
?>
