<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    // Count patients
    $patientQuery = $connection->query("SELECT COUNT(*) AS total FROM admitted_patients");
    $patientCount = $patientQuery->fetch_assoc()['total'] ?? 0;

    // Count walk-in customers
    $walkinQuery = $connection->query("SELECT COUNT(*) AS total FROM walkin_customers");
    $walkinCount = $walkinQuery->fetch_assoc()['total'] ?? 0;

    // Total = patients + walk-ins
    $totalCustomers = $patientCount + $walkinCount;

    echo json_encode([
        'status' => 'success',
        'patients' => $patientCount,
        'walkins' => $walkinCount,
        'total_customers' => $totalCustomers
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
