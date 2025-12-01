<?php
header("Content-Type: application/json");

// Simulating medicine data from we_caredb system
// This represents data from the we_caredb.medical_records table

$record_id = isset($_GET['record_id']) ? $_GET['record_id'] : null;

// Sample medical records data
$medical_data = [
    "RX001" => [
        [
            "medicine_id" => "MED-001",
            "medicine_name" => "Paracetamol",
            "quantity" => 17,
            "dosage" => "500mg",
            "frequency" => "3 times daily"
        ],
        [
            "medicine_id" => "MED-002",
            "medicine_name" => "Ibuprofen",
            "quantity" => 10,
            "dosage" => "400mg",
            "frequency" => "2 times daily"
        ]
    ],
    "RX002" => [
        [
            "medicine_id" => "MED-003",
            "medicine_name" => "Amoxicillin",
            "quantity" => 14,
            "dosage" => "250mg",
            "frequency" => "3 times daily"
        ],
        [
            "medicine_id" => "MED-004",
            "medicine_name" => "Cough Syrup",
            "quantity" => 1,
            "dosage" => "200ml",
            "frequency" => "As needed"
        ]
    ],
    "RX003" => [
        [
            "medicine_id" => "MED-005",
            "medicine_name" => "Vitamin C",
            "quantity" => 30,
            "dosage" => "1000mg",
            "frequency" => "Once daily"
        ],
        [
            "medicine_id" => "MED-001",
            "medicine_name" => "Paracetamol",
            "quantity" => 5,
            "dosage" => "500mg",
            "frequency" => "2 times daily"
        ]
    ]
];

if ($record_id && isset($medical_data[$record_id])) {
    echo json_encode([
        "status" => "success",
        "record_id" => $record_id,
        "data" => $medical_data[$record_id]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No prescription found for record_id: " . $record_id
    ]);
}

?>
