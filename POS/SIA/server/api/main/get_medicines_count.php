<?php
require '../../core/connection.php';

header('Content-Type: application/json');

try {
    $query = $connection->prepare("SELECT COUNT(*) AS total FROM medicines");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "total" => $result['total']
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
