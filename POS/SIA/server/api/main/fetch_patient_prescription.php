<?php
header('Content-Type: application/json');

// Optional: get patient_id from query parameter
$patientId = $_GET['patient_id'] ?? null;

// Remote PMS API URL
$apiUrl = "http://26.233.226.98/PMS/api/get_prescriptions.php";
if ($patientId) {
    $apiUrl .= "?patient_id=" . urlencode($patientId);
}

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // connection timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // response timeout

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle cURL errors
if ($err) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch prescriptions: $err"
    ]);
    exit;
}

// Handle HTTP errors
if ($httpCode < 200 || $httpCode >= 300) {
    echo json_encode([
        "status" => "error",
        "message" => "PMS API returned HTTP code $httpCode"
    ]);
    exit;
}

// Decode API response
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON received from PMS API",
        "raw_response" => $response
    ]);
    exit;
}

// Optional: map remote medicine_id to local medicine_id
$remoteToLocal = [
    "2" => "101", // Example: remote ID 2 maps to local ID 101
    // Add more mappings if necessary
];

if (isset($data['data']) && is_array($data['data'])) {
    foreach ($data['data'] as &$record) {
        if (isset($record['medicine_id']) && isset($remoteToLocal[$record['medicine_id']])) {
            $record['medicine_id'] = $remoteToLocal[$record['medicine_id']];
        }
    }
}

// Return JSON response
echo json_encode([
    "status" => "success",
    "message" => "Prescriptions retrieved successfully",
    "data" => $data['data'] ?? []
]);
