<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    $query = $connection->prepare("SELECT SUM(stock) AS total FROM medicines");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "total" => $result['total'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
