<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    $query = "
        SELECT medicine_name, SUM(quantity) AS total_sold
        FROM invoice_items
        WHERE medicine_name != '' AND medicine_name != '0'
        GROUP BY medicine_name
        ORDER BY total_sold DESC
        LIMIT 1
    ";

    $result = $connection->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'medicine_name' => $row['medicine_name'],
            'total_sold' => (int)$row['total_sold']
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'medicine_name' => 'N/A',
            'total_sold' => 0
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
