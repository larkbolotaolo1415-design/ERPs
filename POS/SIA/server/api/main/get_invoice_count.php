<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    $query = $connection->prepare("SELECT COUNT(*) AS invoice_count FROM invoices");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "invoice_count" => $result['invoice_count'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
