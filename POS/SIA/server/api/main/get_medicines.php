<?php
require '../../core/connection.php';

header('Content-Type: application/json');

try {
    $query = $connection->prepare("SELECT * FROM medicines ORDER BY medicine_name ASC");
    $query->execute();
    $result = $query->get_result();

    $medicines = [];

    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $medicines
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
