<?php
require '../../core/connection.php';
header('Content-Type: application/json');

try {
    $stmt = $connection->prepare(
        "SELECT patient_id, first_name, last_name, room_number, status 
         FROM admitted_patients 
         ORDER BY first_name ASC"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $patients
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
