<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    $query = $connection->prepare("SELECT COUNT(DISTINCT medicine_group) AS groups_count FROM medicines");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "groups_count" => $result['groups_count'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
