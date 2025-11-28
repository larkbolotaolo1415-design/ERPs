<?php
header("Content-Type: application/json");
require "../../core/connection.php";

$query = "SELECT COUNT(*) AS total FROM employees";
$result = $connection->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "total" => (int)$row['total']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Query failed"
    ]);
}
