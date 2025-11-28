<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    $query = $connection->prepare("SELECT SUM(total_amount) AS revenue FROM invoices");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "revenue" => $result['revenue'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
