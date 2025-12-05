<?php
header('Content-Type: application/json');
require '../../core/connection.php';

try {
    // Count walk-in customers only (patients are managed in PMS)
    $walkinQuery = $connection->query("SELECT COUNT(*) AS total FROM walkin_customers");
    $walkinCount = $walkinQuery->fetch_assoc()['total'] ?? 0;

    // Get patient count from invoices (patients who made purchases)
    $patientQuery = $connection->query("SELECT COUNT(DISTINCT patient_id) AS total FROM invoices WHERE patient_id IS NOT NULL");
    $patientCount = $patientQuery->fetch_assoc()['total'] ?? 0;

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
