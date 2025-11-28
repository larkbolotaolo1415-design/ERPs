<?php
require '../../core/connection.php';
header('Content-Type: application/json');

try {
    $query = $connection->prepare("SELECT * FROM admin_configuration LIMIT 1");
    $query->execute();
    $result = $query->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "data" => $row
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No configuration found."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
