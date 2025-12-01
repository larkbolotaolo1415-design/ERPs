<?php
header("Content-Type: application/json");

// Simulating prescription data from we_caredb system
// This represents data from the we_caredb.record_prescriptions table
$prescriptions = [
    [
        "record_id" => "RX001",
        "patient_id" => "PAT001",
        "patient_name" => "Juan Dela Cruz",
        "date_issued" => "2025-12-01",
        "status" => "Active"
    ],
    [
        "record_id" => "RX002",
        "patient_id" => "PAT002",
        "patient_name" => "Maria Santos",
        "date_issued" => "2025-11-28",
        "status" => "Active"
    ],
    [
        "record_id" => "RX003",
        "patient_id" => "PAT003",
        "patient_name" => "Carlos Rodriguez",
        "date_issued" => "2025-11-25",
        "status" => "Active"
    ]
];

echo json_encode([
    "status" => "success",
    "data" => $prescriptions
]);

?>
